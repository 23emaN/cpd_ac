<?php

/**
 * ตรวจรหัสผ่านของแขก แล้วเปิดรอบเข้าใช้
 *
 * เป็น endpoint เดียวฝั่งแขกที่ไม่ผ่าน cd_guest_guard() เพราะมันคือตัวสร้าง session เอง
 * จึงต้องระวังเป็นพิเศษ — ทุกอย่างที่นี่คือด่านหน้าที่คนนอกยิงเข้ามาได้ตรง ๆ
 *
 * กติกาที่ห้ามละเมิด (docs/CUSTOMER_DRIVE.md หัวข้อ 8)
 *   1. **ข้อความผิดพลาดเหมือนกันทุกกรณี** — ไม่มีลิงก์ / รหัสผิด / หมดอายุ / ถูกเพิกถอน
 *      ตอบเหมือนกันหมด การแยกบอกเท่ากับยืนยันให้คนที่สุ่ม token ว่าอันไหนมีอยู่จริง
 *   2. **หน่วงเวลาอย่างน้อย 400 มิลลิวินาทีทุกครั้งที่ล้มเหลว** กัน timing attack
 *      ที่ใช้เวลาตอบกลับแยกว่า "ไม่มีลิงก์" (เร็ว) กับ "รหัสผิด" (ช้าเพราะต้อง verify)
 *   3. ผิด 5 ครั้ง → ล็อก 15 นาที · ผิดสะสม 20 ครั้ง → ขึ้นธงแดงให้พนักงานเห็น
 *      **ไม่เพิกถอนอัตโนมัติ** เพราะถ้าทำ ใครก็ปิดลิงก์ของลูกค้าได้ด้วยการเดาผิด
 */

require_once __DIR__ . '/../../config/customer_drive.php';

header('Referrer-Policy: no-referrer');

$token = (string) ($_POST['t'] ?? '');
$pass  = (string) ($_POST['password'] ?? '');

/*
 * ไม่มีชื่อผู้ส่งแล้ว — ลูกค้ากรอกแค่รหัส
 *
 * ป้ายที่บันทึกลงประวัติจึงเป็นชื่อเรื่องของลิงก์แทนชื่อคน
 * ซึ่งยังตอบได้ว่า "ไฟล์นี้เข้ามาทางไหน" แม้จะไม่รู้ว่าใครเป็นคนกด
 */
$label = '';

/**
 * ล้มเหลวแบบเดียวกันเสมอ พร้อมหน่วงเวลาให้เท่ากันทุกเส้นทาง
 *
 * $verified บอกว่าเส้นทางนี้ได้รัน password_verify() ไปแล้วหรือยัง —
 * ถ้ายัง ต้องรันกับ hash หลอกให้เสียเวลาเท่ากัน
 *
 * ทำไมต้องทำ: password_verify() กิน CPU ราว 180 ms เส้นทาง "รหัสผิด"
 * จึงช้ากว่าเส้นทาง "ไม่มี token" อย่างสม่ำเสมอ วัดจากภายนอกได้ว่า token ไหน
 * มีอยู่จริง แล้วไล่สุ่มหา token ที่ใช้งานได้โดยไม่ต้องรู้รหัสผ่านเลย
 * (ทดสอบแล้วต่างกัน 700 ms กับ 520 ms ซึ่งแยกออกชัดเจน)
 */
function auth_deny(bool $verified = false): void
{
    if (!$verified) {
        // hash หลอกที่สร้างครั้งเดียวตอนเขียนโค้ด — verify กับมันเสียเวลาเท่าของจริง
        password_verify('x', '$2y$10$usesomesillystringfore7hnbRJHxXVLeakoG8K30oukPsA.ztMG');
    }

    usleep(400000);   // 400 ms
    cd_json(['result' => '0', 'msn' => CD_GUEST_DENY]);
}

if ($pass === '') {
    // อันนี้บอกตรง ๆ ได้ เพราะไม่ได้เผยอะไรเกี่ยวกับตัวลิงก์เลย
    cd_json(['result' => '0', 'msn' => 'กรุณากรอกรหัสผ่าน']);
}

$link = cd_link_by_token($token);

if (!$link) {
    auth_deny();
}

$state = cd_link_state($link);

if ($state === 'locked') {
    $minutes = max(1, (int) ceil((strtotime((string) $link['locked_until']) - time()) / 60));

    usleep(400000);
    cd_json(['result' => '0', 'msn' => 'กรุณาลองใหม่ในอีก ' . $minutes . ' นาที']);
}

if ($state !== '') {
    auth_deny();
}

global $conn;

// ------------------------------------------------------------ ตรวจรหัสผ่าน

if (!password_verify($pass, (string) $link['password_hash'])) {
    $fails = (int) $link['fail_count'] + 1;

    try {
        if ($fails % CD_LINK_FAIL_LOCK === 0) {
            // ครบรอบล็อก — ล็อกชั่วคราว แต่ตัวนับสะสมยังเดินต่อเพื่อให้ธงแดงทำงาน
            $conn->prepareAndExecute(
                "UPDATE tbl_cd_link
                 SET fail_count = ?, locked_until = DATE_ADD(NOW(), INTERVAL ? MINUTE)
                 WHERE link_id = ?",
                [$fails, CD_LINK_LOCK_MINUTES, (int) $link['link_id']]
            );
        } else {
            $conn->prepareAndExecute(
                "UPDATE tbl_cd_link SET fail_count = ? WHERE link_id = ?",
                [$fails, (int) $link['link_id']]
            );
        }
    } catch (Throwable $e) {
        error_log('auth fail_count: ' . $e->getMessage());
    }

    cd_activity(
        (int) $link['customer_id'],
        'link_auth_fail',
        null,
        'กรอกรหัสผิดครั้งที่ ' . $fails,
        'guest',
        null,
        $label,
        (int) $link['link_id']
    );

    if ($fails % CD_LINK_FAIL_LOCK === 0) {
        usleep(400000);
        cd_json(['result' => '0', 'msn' => 'กรุณาลองใหม่ในอีก ' . CD_LINK_LOCK_MINUTES . ' นาที']);
    }

    auth_deny(true);
}

// ------------------------------------------------------------ ผ่านแล้ว

try {
    /*
     * ล้างตัวนับผิดเมื่อเข้าได้สำเร็จ — คนที่รู้รหัสจริงพิมพ์ผิดสองสามครั้ง
     * ต้องไม่สะสมจนไปโดนธงแดงร่วมกับคนที่กำลังเดารหัสอยู่จริง ๆ
     */
    $conn->prepareAndExecute(
        "UPDATE tbl_cd_link
         SET fail_count = 0, locked_until = NULL, last_open_datetime = NOW()
         WHERE link_id = ?",
        [(int) $link['link_id']]
    );
} catch (Throwable $e) {
    error_log('auth reset: ' . $e->getMessage());
}

$first = (string) $link['notified_first_open'] !== '1';

// ใช้ชื่อเรื่องของลิงก์เป็นป้ายกำกับ เพราะไม่มีชื่อคนให้ใช้แล้ว
$label = mb_substr((string) $link['title'], 0, 120, 'UTF-8');

cd_guest_start($link, $label);

cd_activity(
    (int) $link['customer_id'],
    'link_open',
    null,
    $first ? 'เปิดลิงก์ครั้งแรก' : 'เปิดลิงก์',
    'guest',
    null,
    $label,
    (int) $link['link_id']
);

/*
 * แจ้งเตือน **เฉพาะการเปิดครั้งแรก** ของลิงก์ใบนั้น
 *
 * ทำไมไม่แจ้งทุกครั้ง: ลูกค้าเปิดลิงก์เดิมซ้ำได้ทั้งวันระหว่างทยอยส่งเอกสาร
 * ถ้าแจ้งทุกครั้ง กระดิ่งจะเต็มไปด้วย "เปิดลิงก์แล้ว" ที่ไม่มีข้อมูลอะไรใหม่
 * แล้วแจ้งเตือนที่สำคัญจริง ๆ (มีไฟล์เข้ามา) จะจมหายไปในนั้น
 *
 * ครั้งแรกมีค่าเพราะมันตอบว่า "ลิงก์ที่ส่งไปถึงมือลูกค้าแล้วและใช้ได้จริง"
 * ซึ่งเป็นสิ่งที่คนส่งลิงก์อยากรู้
 *
 * ปักธงก่อนแจ้ง เพื่อไม่ให้แจ้งซ้ำถ้าคำขอสองอันเข้ามาพร้อมกัน
 */
if ($first) {
    try {
        $conn->prepareAndExecute(
            "UPDATE tbl_cd_link SET notified_first_open = '1'
             WHERE link_id = ? AND notified_first_open <> '1'",
            [(int) $link['link_id']]
        );

        cd_notify((int) $link['customer_id'], 'เปิดลิงก์ครั้งแรกแล้ว', $link['title'], null);
    } catch (Throwable $e) {
        error_log('auth notify: ' . $e->getMessage());
    }
}

cd_ok(['msn' => 'เข้าสู่คลังไฟล์แล้ว']);
