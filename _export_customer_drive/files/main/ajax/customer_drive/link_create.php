<?php

/**
 * สร้างลิงก์แชร์ใหม่
 *
 * **นี่คือ endpoint ที่อันตรายที่สุดในระบบทั้งหมด** — มันเปิดทางให้คนนอก
 * เข้าถึงเอกสารของลูกค้าได้ จึงต้องมีสิทธิ์ `cdrive_manage_link` ซึ่งจงใจ
 * ไม่ผูกให้ตำแหน่งไหนอัตโนมัติ (sql/003)
 *
 * รหัสผ่านคืนกลับไปเป็นข้อความธรรมดา **ครั้งเดียวตรงนี้เท่านั้น**
 * เพราะฐานข้อมูลเก็บเป็น password_hash() ซึ่งเอาคืนไม่ได้อีกเลย
 * ถ้าลูกค้าทำหาย ต้อง "ตั้งรหัสใหม่" ไม่ใช่ "ดูรหัสเดิม"
 */

include('../../../config/customer_drive.php');

$customer_id = (int) ($_POST['customer_id'] ?? 0);
$user_id     = (int) ($_POST['user_id'] ?? 0);
$title       = cd_safe_name((string) ($_POST['title'] ?? ''));
$message     = trim((string) ($_POST['message'] ?? ''));
$mode        = ($_POST['mode'] ?? 'collect') === 'share' ? 'share' : 'collect';
$root_id     = (int) ($_POST['root_node_id'] ?? 0);
$password    = (string) ($_POST['password'] ?? '');
$days        = (int) ($_POST['days'] ?? 0);
$maxUploads  = (int) ($_POST['max_uploads'] ?? 0);
$allowDl     = ($_POST['allow_download'] ?? '0') === '1';
$allowUp     = ($_POST['allow_upload'] ?? '0') === '1';

cd_guard($customer_id, $user_id, 'cdrive_manage_link');

if ($title === '') {
    cd_fail('กรุณาตั้งชื่อเรื่องของลิงก์ เช่น "ขอเอกสารปิดงบ 2568"');
}

/*
 * บังคับแค่ความยาวขั้นต่ำ ไม่มีบัญชีดำ — ตัดสินใจแล้วและยอมรับความเสี่ยง
 * (docs/CUSTOMER_DRIVE.md หัวข้อ 14 ข้อ 1)
 *
 * ด่านที่เหลืออยู่คือการล็อก 15 นาทีหลังผิด 5 ครั้ง ซึ่งเป็นสิ่งเดียวที่กัน
 * การเดารหัสง่าย ๆ ได้ — **ห้ามตัดออกไม่ว่ากรณีใด**
 */
/*
 * ยาวเท่านี้พอดี ไม่ใช่อย่างน้อย — หน้ากรอกของแขกเป็นช่องแยกตัวละกล่อง
 * ซึ่งมีจำนวนช่องตายตัว รหัสที่ยาวกว่านั้นจะกรอกไม่ได้เลย
 */
if (mb_strlen($password, 'UTF-8') !== CD_LINK_PASSWORD_LEN) {
    cd_fail('รหัสผ่านต้องยาว ' . CD_LINK_PASSWORD_LEN . ' ตัวอักษรพอดี');
}

// โฟลเดอร์ปลายทางต้องเป็นโฟลเดอร์จริงในคลังของลูกค้ารายนี้
$root = null;

if ($root_id > 0) {
    $root = cd_folder_at($customer_id, $root_id);

    if (!$root) {
        cd_fail('โฟลเดอร์ปลายทางต้องเป็นโฟลเดอร์ในคลังของลูกค้ารายนี้');
    }
}

$days = $days > 0 ? min($days, 365) : (int) set_get('cd_link_default_days');
$maxUploads = $maxUploads > 0 ? min($maxUploads, 10000) : (int) set_get('cd_link_default_max_uploads');

/*
 * โหมด collect ห้ามดาวน์โหลดเด็ดขาด ไม่ว่าฝั่งหน้าเว็บจะส่งอะไรมา
 *
 * collect แปลว่า "กล่องรับเอกสาร" — ถ้าโหลดได้ด้วย มันก็คือการแชร์โฟลเดอร์
 * ซึ่งเป็นคนละอย่างกับที่คนกดสร้างลิงก์ตั้งใจ
 */
$allowDownload = ($mode === 'share' && $allowDl) ? '1' : '0';

/*
 * ในทางกลับกัน โหมด share ต้อง "อ่านอย่างเดียว" เป็นค่าตั้งต้น
 *
 * เหตุผล: การส่งรายงานให้ลูกค้าไม่ควรเปิดช่องเขียนกลับเข้าโฟลเดอร์เดียวกัน
 * ไปด้วยโดยที่คนกดสร้างลิงก์ไม่ได้ตั้งใจ ใครถือลิงก์นั้นจะหย่อนไฟล์อะไรก็ได้
 * ลงในโฟลเดอร์ที่สำนักงานถือว่าเป็นของขาออก
 *
 * ส่วนโหมด collect บังคับ '1' เสมอ เพราะลิงก์รับเอกสารที่รับไม่ได้ไม่มีความหมาย
 */
$allowUpload = $mode === 'collect' ? '1' : ($allowUp ? '1' : '0');

global $conn;

$token = cd_token();

$conn->beginTransaction();

try {
    $conn->prepareAndExecute(
        "INSERT INTO tbl_cd_link
            (customer_id, token, title, message, mode, root_node_id,
             allow_upload, allow_download, password_hash, expires_datetime,
             max_uploads, create_by)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL ? DAY), ?, ?)",
        [
            $customer_id,
            $token,
            mb_substr($title, 0, 200, 'UTF-8'),
            $message !== '' ? mb_substr($message, 0, 2000, 'UTF-8') : null,
            $mode,
            $root ? (int) $root['node_id'] : null,
            $allowUpload,
            $allowDownload,
            password_hash($password, PASSWORD_DEFAULT),
            $days,
            $maxUploads,
            $user_id,
        ]
    );

    $link_id = (int) $conn->get_insert_id();

    $conn->commit();
} catch (Throwable $e) {
    $conn->rollback();
    cd_fail('สร้างลิงก์ไม่สำเร็จ: ' . $e->getMessage());
}

cd_activity($customer_id, 'link_create', $root ? (int) $root['node_id'] : null, $title, 'staff', $user_id, null, $link_id);
cd_log($user_id, 'สร้างลิงก์แชร์คลังไฟล์ลูกค้า: ' . $title);

cd_ok([
    'msn'      => 'สร้างลิงก์แล้ว',
    'link_id'  => $link_id,
    'url'      => cd_portal_url($token),
    // ครั้งเดียวที่รหัสผ่านออกจากเซิร์ฟเวอร์เป็นข้อความธรรมดา
    'password' => $password,
]);
