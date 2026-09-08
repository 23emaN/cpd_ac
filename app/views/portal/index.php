<?php
// app/views/portal/index.php
// Ported from _export_customer_drive/files/portal/index.php
// $link/$state/$lockMinutes/$usable/$https set by PortalController::index().
$baseUrl = defined('BASE_URL') ? BASE_URL : '/cpd_ac/public';
?>
<!doctype html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <meta name="referrer" content="no-referrer">
    <title>ส่งเอกสาร</title>
    <link rel="icon" type="image/png" href="<?php echo $baseUrl; ?>/assets/images/am-group-logo.png">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/portal/portal.css?v=<?php echo filemtime(__DIR__ . '/../../../public/portal/portal.css'); ?>">
</head>

<body>
    <?php if (!$https): ?>
        <div class="pt-warn">โหมดทดสอบ — หน้านี้ยังไม่ได้ใช้ HTTPS ห้ามใช้กับข้อมูลจริง</div>
    <?php endif; ?>

    <main class="pt-wrap">
        <div class="pt-card">
            <img class="pt-logo" src="<?php echo $baseUrl; ?>/assets/images/G_AM_logo-01.jpg" alt="">

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
        <script src="<?php echo $baseUrl; ?>/portal/portal.js?v=<?php echo filemtime(__DIR__ . '/../../../public/portal/portal.js'); ?>"
            data-api="<?php echo cd_e($baseUrl . '/portal/'); ?>"></script>
        <script src="<?php echo $baseUrl; ?>/portal/auth-init.js?v=<?php echo filemtime(__DIR__ . '/../../../public/portal/auth-init.js'); ?>"></script>
    <?php endif; ?>
</body>

</html>
