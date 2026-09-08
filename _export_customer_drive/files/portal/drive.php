<?php

/**
 * หน้าคลังไฟล์ของแขก — หลังกรอกรหัสผ่านถูกแล้ว
 *
 * กติกาเหล็กเหมือน index.php: หน้าเดียว งานเดียว ไม่มีเมนู ไม่มีลิงก์ออกไปที่อื่น
 * **ห้ามแตะ localStorage** และห้ามแสดงข้อมูลภายในใด ๆ
 *
 * ตัวหน้าไม่ได้ดึงรายการไฟล์มาเลย — ให้ portal/ajax/list.php เป็นคนบอก
 * เพราะถ้าวาดจาก PHP ตรงนี้ จะต้องมีตรรกะขอบเขตสองชุด (ที่นี่กับที่ list.php)
 * ซึ่งวันหนึ่งจะเพี้ยนออกจากกันแล้วกลายเป็นรูรั่ว
 */

require_once __DIR__ . '/../config/customer_drive.php';

header('Referrer-Policy: no-referrer');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:; connect-src 'self'; form-action 'self'; frame-ancestors 'none'; base-uri 'none'");

/*
 * ตรวจ session แบบไม่ใช้ cd_guest_guard() เพราะตัวนั้นตอบ JSON แล้ว exit
 * หน้านี้ต้องเด้งกลับไปหน้ากรอกรหัสแทน
 */
$token = (string) ($_COOKIE[CD_GUEST_COOKIE] ?? '');
$link = null;

if ($token !== '') {
    global $conn;

    $row = $conn->prepareAndExecute(
        "SELECT s.*, l.token AS link_token FROM tbl_cd_link_session s
         JOIN tbl_cd_link l ON l.link_id = s.link_id
         WHERE s.session_token = ?",
        [$token]
    )->fetch();

    if ($row) {
        $expires = strtotime((string) $row['expires_datetime']);
        $created = strtotime((string) $row['create_datetime']);

        if ($expires >= time() && ($created + CD_GUEST_MAX_AGE) >= time()) {
            $candidate = cd_link_by_id_any((int) $row['link_id']);

            if ($candidate && cd_link_state($candidate) === '') {
                $link = $candidate;
            }
        }
    }
}

if (!$link) {
    // ไม่มี session ที่ใช้ได้ = กลับไปหน้ากรอกรหัส (ไม่บอกว่าเพราะอะไร)
    header('Location: index.php' . ($token !== '' ? '' : ''));
    exit;
}

$https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
$maxUpload = cd_max_upload_bytes();
?>
<!doctype html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <meta name="referrer" content="no-referrer">
    <title>ส่งเอกสาร</title>
    <link rel="icon" type="image/png" href="../images/logo.jpg">
    <link rel="stylesheet" href="../css/portal.css?v=<?php echo filemtime(__DIR__ . '/../css/portal.css'); ?>">
</head>

<body class="pt-app">
    <?php if (!$https): ?>
        <div class="pt-warn">โหมดทดสอบ — หน้านี้ยังไม่ได้ใช้ HTTPS ห้ามใช้กับข้อมูลจริง</div>
    <?php endif; ?>

    <!--
        โครงหน้าแบบเดียวกับ Google Drive: แถบหัวติดบน · แถบคำสั่ง · ตารางไฟล์เต็มความกว้าง

        เหตุผลที่เลิกใช้การ์ดกลางจอแบบเดิม: ของเดิมออกแบบมาสำหรับ "ส่งไฟล์แล้วจบ"
        แต่ลูกค้าใช้ลิงก์เดียวหลายรอบ ทยอยส่งเอกสารตามที่หาเจอ สิ่งที่ต้องเห็นชัดที่สุด
        จึงเป็น "ส่งอะไรไปแล้วบ้าง" ไม่ใช่ปุ่มอัปโหลด — ตารางจึงควรเป็นพระเอกของหน้า
    -->
    <header class="pt-bar">
        <div class="pt-bar-in">
            <img class="pt-bar-logo" src="../images/logo-main.png" alt="">
            <div class="pt-bar-text">
                <h1 class="pt-bar-title"><?php echo cd_e($link['title']); ?></h1>
                <p class="pt-bar-sub">ระบบรับเอกสารของสำนักงานสอบบัญชี</p>
            </div>
            <button class="pt-btn pt-btn-ghost pt-bar-out" type="button" id="pt-leave">ออกจากระบบ</button>
        </div>
    </header>

    <main class="pt-main">
        <?php if (!empty($link['message'])): ?>
            <div class="pt-msg">
                <span class="pt-msg-icon" aria-hidden="true">!</span>
                <div><?php echo nl2br(cd_e($link['message'])); ?></div>
            </div>
        <?php endif; ?>

        <div class="pt-tools">
            <?php if ((string) $link['allow_upload'] === '1'): ?>
                <button class="pt-btn pt-btn-new" type="button" id="pt-pick">
                    <span class="pt-plus" aria-hidden="true">+</span> อัปโหลดไฟล์
                </button>

                <input type="file" id="pt-file" multiple hidden
                    accept=".<?php echo cd_e(implode(',.', cd_allowed_ext('guest'))); ?>">
            <?php endif; ?>

            <div class="pt-tools-note" id="pt-count"></div>
        </div>

        <div class="pt-panel" id="pt-drop">
            <div class="pt-thead" id="pt-thead" hidden>
                <span>ชื่อ</span>
                <span class="pt-col-size">ขนาด</span>
                <span class="pt-col-when">ส่งเมื่อ</span>
                <span class="pt-col-act"></span>
            </div>

            <div class="pt-list" id="pt-list"></div>
        </div>

        <?php if ((string) $link['allow_upload'] === '1'): ?>
            <p class="pt-hint">
                รองรับ <?php echo cd_e(implode(', ', cd_allowed_ext('guest'))); ?>
                · ไฟล์ละไม่เกิน <?php echo cd_e(cd_format_bytes($maxUpload)); ?>
            </p>
        <?php endif; ?>
    </main>

    <!--
        แผงความคืบหน้ามุมล่างขวาแบบ Drive — ไม่ดันเนื้อหาหลักให้ขยับ
        ผู้ใช้จึงยังเห็นรายการไฟล์ของตัวเองระหว่างที่ไฟล์กำลังทยอยขึ้น
    -->
    <div class="pt-uploader" id="pt-uploader" hidden>
        <div class="pt-uploader-head">
            <span id="pt-uploader-title">กำลังส่งไฟล์</span>
            <button type="button" class="pt-uploader-btn" id="pt-uploader-toggle" aria-label="ย่อ/ขยาย">▾</button>
            <button type="button" class="pt-uploader-btn" id="pt-uploader-close" aria-label="ปิด">✕</button>
        </div>
        <div class="pt-queue" id="pt-queue"></div>
    </div>

    <!-- ฉากคลุมตอนลากไฟล์เข้ามา — กินทั้งหน้าจอ จะได้ไม่ต้องเล็งกล่องเล็ก ๆ -->
    <div class="pt-dropzone" id="pt-dropzone" hidden>
        <div class="pt-dropzone-in">
            <div class="pt-dropzone-icon" aria-hidden="true">↥</div>
            <div class="pt-dropzone-text">วางไฟล์เพื่อส่งให้ผู้ตรวจสอบบัญชี</div>
        </div>
    </div>

    <script type="application/json" id="pt-config">
<?php
echo json_encode([
    'mode'      => (string) $link['mode'],
    'canUpload' => (string) $link['allow_upload'] === '1',
    'maxUpload' => $maxUpload,
    'maxText'   => cd_format_bytes($maxUpload),
    'allowed'   => cd_allowed_ext('guest'),
], JSON_UNESCAPED_UNICODE);
?>
    </script>

    <script src="../js/portal.js?v=<?php echo filemtime(__DIR__ . '/../js/portal.js'); ?>"></script>
    <script src="drive-init.js?v=<?php echo filemtime(__DIR__ . '/drive-init.js'); ?>"></script>
</body>

</html>
