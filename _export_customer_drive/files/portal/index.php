<?php

/**
 * หน้าแรกของแขก — กรอกชื่อและรหัสผ่านเพื่อเข้าคลังไฟล์
 *
 * กติกาเหล็กของหน้านี้ (docs/CUSTOMER_DRIVE.md หัวข้อ 7)
 *   - หน้าเดียว งานเดียว ไม่มีเมนู ไม่มี sidebar ไม่มีลิงก์ออกไปส่วนอื่นของระบบ
 *   - **ห้ามแตะ localStorage แม้แต่บรรทัดเดียว** เพราะหน้านี้อยู่ origin เดียวกับ
 *     ระบบภายใน ถ้าโดน XSS สคริปต์จะอ่าน user_id ของพนักงานได้
 *   - ห้ามแสดงข้อมูลภายใน — ชื่อลูกค้า ชื่องาน ชื่อพนักงาน ห้ามหลุดออกไป
 *     แสดงได้แค่ชื่อเรื่องของลิงก์และข้อความที่ผู้สร้างเขียนเอง
 *   - ข้อความผิดพลาดเหมือนกันทุกกรณี ห้ามบอกว่าลิงก์ไม่มีหรือหมดอายุ
 */

require_once __DIR__ . '/../config/customer_drive.php';

// ส่งหัวความปลอดภัยจาก PHP ด้วย ไม่พึ่ง .htaccess อย่างเดียว
// เพราะ nginx ไม่อ่าน .htaccess และเราพิสูจน์มาแล้วว่าเรื่องนี้เกิดขึ้นจริงในโปรเจกต์นี้
header('Referrer-Policy: no-referrer');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:; connect-src 'self'; form-action 'self'; frame-ancestors 'none'; base-uri 'none'");

$token = (string) ($_GET['t'] ?? '');
$link  = cd_link_by_token($token);
$state = $link ? cd_link_state($link) : 'missing';

// ถูกล็อกอยู่ = บอกได้ว่าให้รอ เพราะไม่ได้เผยว่าลิงก์มีอยู่จริงหรือรหัสถูกผิด
$lockMinutes = 0;

if ($state === 'locked') {
    $lockMinutes = max(1, (int) ceil((strtotime((string) $link['locked_until']) - time()) / 60));
}

$usable = $state === '';
$https  = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
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

<body>
    <?php if (!$https): ?>
        <div class="pt-warn">โหมดทดสอบ — หน้านี้ยังไม่ได้ใช้ HTTPS ห้ามใช้กับข้อมูลจริง</div>
    <?php endif; ?>

    <main class="pt-wrap">
        <div class="pt-card">
            <img class="pt-logo" src="../images/logo-main.png" alt="">

            <?php if (!$usable && $state !== 'locked'): ?>
                <h1 class="pt-title">ลิงก์นี้ใช้ไม่ได้แล้ว</h1>
                <p class="pt-lead">กรุณาติดต่อผู้ตรวจสอบบัญชีของท่านเพื่อขอลิงก์ใหม่</p>

            <?php elseif ($state === 'locked'): ?>
                <h1 class="pt-title">กรุณาลองใหม่ภายหลัง</h1>
                <p class="pt-lead">มีการกรอกรหัสผ่านผิดหลายครั้ง กรุณาลองใหม่ในอีก <?php echo (int) $lockMinutes; ?> นาที</p>

            <?php else: ?>
                <h1 class="pt-title"><?php echo cd_e($link['title']); ?></h1>

                <?php if (!empty($link['message'])): ?>
                    <p class="pt-lead"><?php echo nl2br(cd_e($link['message'])); ?></p>
                <?php endif; ?>

                <form id="pt-form" autocomplete="off" novalidate>
                    <p class="pt-otp-label">กรอกรหัส <?php echo CD_LINK_PASSWORD_LEN; ?> ตัวที่ได้รับจากผู้ตรวจสอบบัญชี</p>

                    <!--
                        ช่องแยกตัวละกล่องแบบรหัส OTP

                        ทำไมต้องแยกกล่อง: ลูกค้าอ่านรหัสจากข้อความในไลน์แล้วพิมพ์ตาม
                        ช่องเดียวยาว ๆ ที่ปิดตัวอักษรเป็นจุดทำให้ไม่รู้ว่าพิมพ์ไปกี่ตัวแล้ว
                        พิมพ์ผิดก็ไม่รู้ว่าผิดตรงไหน ต้องลบทั้งหมดแล้วเริ่มใหม่
                        และรหัสนี้ผิดได้แค่ 5 ครั้งก่อนโดนล็อก 15 นาที การพิมพ์ผิดจึงแพง

                        ตัวอักษรไม่ปิดบัง เพราะรหัสนี้ใช้ครั้งเดียวกับลิงก์เดียว
                        ไม่ใช่รหัสส่วนตัวที่ต้องกันคนมองข้างหลัง และการเห็นสิ่งที่พิมพ์
                        คือประโยชน์หลักของรูปแบบนี้
                    -->
                    <div class="pt-otp" id="pt-otp">
                        <?php for ($i = 0; $i < CD_LINK_PASSWORD_LEN; $i++): ?>
                            <input class="pt-otp-box" type="text" inputmode="text"
                                maxlength="1" autocomplete="off" autocapitalize="off"
                                autocorrect="off" spellcheck="false"
                                aria-label="รหัสตัวที่ <?php echo $i + 1; ?>">
                        <?php endfor; ?>
                    </div>

                    <div class="pt-error" id="pt-error" hidden></div>

                    <button class="pt-btn" type="submit" id="pt-submit">เข้าสู่คลังไฟล์</button>
                </form>
            <?php endif; ?>
        </div>

        <p class="pt-foot">ระบบรับเอกสารของสำนักงานสอบบัญชี</p>
    </main>

    <?php if ($usable): ?>
        <script src="../js/portal.js?v=<?php echo filemtime(__DIR__ . '/../js/portal.js'); ?>"></script>
        <script src="auth-init.js?v=<?php echo filemtime(__DIR__ . '/auth-init.js'); ?>"></script>
    <?php endif; ?>
</body>

</html>
