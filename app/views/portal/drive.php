<?php
// app/views/portal/drive.php
// Ported from _export_customer_drive/files/portal/drive.php
// $link/$https/$maxUpload set by PortalController::drive().
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
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/pt_assets/portal.css?v=<?php echo filemtime(__DIR__ . '/../../../public/pt_assets/portal.css'); ?>">
</head>

<body class="pt-app">
    <?php if (!$https): ?>
        <div class="pt-warn">โหมดทดสอบ — หน้านี้ยังไม่ได้ใช้ HTTPS ห้ามใช้กับข้อมูลจริง</div>
    <?php endif; ?>

    <header class="pt-bar">
        <div class="pt-bar-in">
            <img class="pt-bar-logo" src="<?php echo $baseUrl; ?>/assets/images/G_AM_logo-01.jpg" alt="">
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

    <div class="pt-uploader" id="pt-uploader" hidden>
        <div class="pt-uploader-head">
            <span id="pt-uploader-title">กำลังส่งไฟล์</span>
            <button type="button" class="pt-uploader-btn" id="pt-uploader-toggle" aria-label="ย่อ/ขยาย">▾</button>
            <button type="button" class="pt-uploader-btn" id="pt-uploader-close" aria-label="ปิด">✕</button>
        </div>
        <div class="pt-queue" id="pt-queue"></div>
    </div>

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

    <script src="<?php echo $baseUrl; ?>/pt_assets/portal.js?v=<?php echo filemtime(__DIR__ . '/../../../public/pt_assets/portal.js'); ?>"
        data-api="<?php echo cd_e($baseUrl . '/portal/'); ?>"></script>
    <script src="<?php echo $baseUrl; ?>/pt_assets/drive-init.js?v=<?php echo filemtime(__DIR__ . '/../../../public/pt_assets/drive-init.js'); ?>"></script>
</body>

</html>
