<?php

/**
 * หน้าคลังไฟล์ของลูกค้าหนึ่งราย
 *
 * ตัวหน้าถูกวาดฝั่งเซิร์ฟเวอร์ทั้งหมดแล้วส่งกลับเป็น HTML ก้อนเดียว
 * js/customer_drive.js อ่านค่าที่ต้องใช้จากบล็อก JSON ท้ายไฟล์ แล้วผูกเหตุการณ์ต่อ
 *
 * ไฟล์นี้คืน HTML ไม่ใช่ JSON จึงใช้ cd_guard() ไม่ได้ (มันตอบ JSON แล้ว exit)
 * ต้องตรวจสิทธิ์เองแล้วพ่นกล่องแจ้งเตือนเป็น HTML แทน
 */

include('../../../config/customer_drive.php');

$customer_id = (int) ($_POST['customer_id'] ?? 0);
$user_id     = (int) ($_POST['user_id'] ?? 0);
$folder_id   = (int) ($_POST['folder_id'] ?? 0);

/** ส่วนหัวหน้า — ใช้ซ้ำทั้งกรณีปกติและกรณี error */
function cd_head(string $title): void
{
?>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <h3 class="mb-0"><?php echo cd_e($title); ?></h3>

        <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb align-items-center mb-0 lh-1">
                <li class="breadcrumb-item">
                    <a href="list_customer.php" class="d-flex align-items-center text-decoration-none">
                        <span class="material-symbols-outlined menu-icon">groups</span>
                        <span class="fw-medium">ลูกค้า</span>
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    <span class="fw-medium">คลังไฟล์</span>
                </li>
            </ol>
        </nav>
    </div>
<?php
}

// ------------------------------------------------------------ ด่าน

$customer = cd_customer($customer_id);

if (!$customer) {
    cd_head('คลังไฟล์ลูกค้า');
    echo '<div class="alert alert-danger" role="alert">ไม่พบข้อมูลลูกค้า</div>';
    exit;
}

if (!cd_can($user_id, 'cdrive_view')) {
    cd_head('คลังไฟล์ลูกค้า');
    echo '<div class="alert alert-warning" role="alert">'
        . 'คุณไม่มีสิทธิ์ดูคลังไฟล์ลูกค้า — ให้ผู้ดูแลระบบเปิดสิทธิ์ <code>cdrive_view</code> '
        . 'ให้ตำแหน่งของคุณที่หน้า "ผู้ใช้งานระบบ"</div>';
    exit;
}

// ------------------------------------------------------------ เตรียมข้อมูล

$storageError = cd_storage_error();
$folder       = cd_folder_at($customer_id, $folder_id ?: null);
$folderId     = $folder ? (int) $folder['node_id'] : null;
$trail        = cd_breadcrumb($customer_id, $folderId);
$perm         = cd_perm_summary($user_id);
$trashCount   = cd_trash_count($customer_id);
$maxUpload    = cd_max_upload_bytes();
$usage        = cd_customer_usage($customer_id);
$me           = cd_user($user_id);

$fiscalYear = (int) date('Y') + 543; // ปีพุทธศักราชปัจจุบัน ใช้เป็นคำแนะนำชื่อโฟลเดอร์
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <h3 class="mb-1"><?php echo cd_e($customer['customer_name']); ?></h3>
        <div class="text-muted fs-13">
            <span class="me-3">รหัส <?php echo cd_e($customer['customer_code'] ?: '—'); ?></span>
            <span>ใช้พื้นที่ <?php echo cd_e(cd_format_bytes($usage)); ?></span>
        </div>
    </div>

    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
        <ol class="breadcrumb align-items-center mb-0 lh-1">
            <li class="breadcrumb-item">
                <a href="list_customer.php" class="d-flex align-items-center text-decoration-none">
                    <span class="material-symbols-outlined menu-icon">groups</span>
                    <span class="fw-medium">ลูกค้า</span>
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="view_customer.php?id=<?php echo (int) $customer_id; ?>" class="text-decoration-none">
                    <span class="fw-medium">ข้อมูลลูกค้า</span>
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                <span class="fw-medium">คลังไฟล์</span>
            </li>
        </ol>
    </nav>
</div>

<?php if ($storageError !== ''): ?>
    <div class="alert alert-danger" role="alert">
        <strong>ที่เก็บไฟล์ใช้งานไม่ได้</strong><br>
        <?php echo cd_e($storageError); ?>
    </div>
<?php endif; ?>

<div class="card bg-white border-0 rounded-3 mb-4 cd-card" id="cd-app">
    <div class="card-body p-4">

        <!-- แถบคำสั่ง -->
        <div class="cd-toolbar d-flex flex-wrap align-items-center gap-2 mb-3">
            <?php if ($perm['cdrive_upload']): ?>
                <button type="button" class="btn btn-primary d-flex align-items-center gap-1" data-cd="upload-pick">
                    <span class="material-symbols-outlined fs-18">upload</span>
                    <span>อัปโหลด</span>
                </button>
                <button type="button" class="btn btn-outline-primary d-flex align-items-center gap-1" data-cd="mkdir">
                    <span class="material-symbols-outlined fs-18">create_new_folder</span>
                    <span>โฟลเดอร์ใหม่</span>
                </button>

            <?php endif; ?>

            <div class="cd-search position-relative ms-auto">
                <span class="material-symbols-outlined cd-search-icon">search</span>
                <input type="search" class="form-control ps-5" id="cd-search"
                    placeholder="ค้นหาชื่อไฟล์หรือโฟลเดอร์ในคลังนี้" autocomplete="off">
            </div>

            <?php if ($perm['cdrive_manage_link']): ?>
                <button type="button" class="btn btn-outline-secondary d-flex align-items-center gap-1" data-cd="links">
                    <span class="material-symbols-outlined fs-18">link</span>
                    <span class="d-none d-md-inline">ลิงก์แชร์</span>
                </button>
            <?php endif; ?>

            <?php if ($perm['cdrive_view_activity']): ?>
                <button type="button" class="btn btn-outline-secondary d-flex align-items-center gap-1" data-cd="activity">
                    <span class="material-symbols-outlined fs-18">history</span>
                    <span class="d-none d-md-inline">ความเคลื่อนไหว</span>
                </button>
            <?php endif; ?>

            <?php if ($perm['cdrive_delete']): ?>
                <button type="button" class="btn btn-outline-secondary d-flex align-items-center gap-1" data-cd="trash">
                    <span class="material-symbols-outlined fs-18">delete</span>
                    <span class="d-none d-md-inline">ถังขยะ</span>
                    <span class="badge bg-secondary cd-trash-count<?php echo $trashCount ? '' : ' d-none'; ?>"><?php echo (int) $trashCount; ?></span>
                </button>
            <?php endif; ?>
        </div>

        <!-- เส้นทาง -->
        <nav class="cd-crumbs d-flex flex-wrap align-items-center gap-1 mb-3" aria-label="ตำแหน่งในคลังไฟล์">
            <button type="button" class="btn btn-sm cd-crumb<?php echo $folderId === null ? ' active' : ''; ?>"
                data-cd="go" data-folder="">
                <span class="material-symbols-outlined fs-16">folder_open</span>
                คลังไฟล์
            </button>
            <?php foreach ($trail as $i => $step): ?>
                <span class="cd-crumb-sep">/</span>
                <button type="button" class="btn btn-sm cd-crumb<?php echo $i === count($trail) - 1 ? ' active' : ''; ?>"
                    data-cd="go" data-folder="<?php echo (int) $step['node_id']; ?>">
                    <?php echo cd_e($step['name']); ?>
                </button>
            <?php endforeach; ?>
        </nav>

        <!-- ตาราง -->
        <div id="cd-table" class="cd-table-wrap"></div>

    </div>
</div>

<!-- พื้นที่วางไฟล์เต็มหน้า -->
<div class="cd-dropveil" aria-hidden="true">
    <div class="cd-dropveil-inner">
        <span class="material-symbols-outlined">cloud_upload</span>
        <div class="fw-medium">วางไฟล์เพื่ออัปโหลด</div>
        <div class="fs-13 text-muted cd-dropveil-target"></div>
    </div>
</div>

<!-- แถบความคืบหน้ามุมล่างขวา -->
<div class="cd-uploads d-none" id="cd-uploads">
    <div class="cd-uploads-head d-flex align-items-center justify-content-between">
        <span class="cd-uploads-title">กำลังอัปโหลด</span>
        <div class="d-flex align-items-center gap-1">
            <button type="button" class="btn btn-sm btn-link p-0 cd-uploads-toggle" title="ย่อ/ขยาย">
                <span class="material-symbols-outlined fs-18">expand_more</span>
            </button>
            <button type="button" class="btn btn-sm btn-link p-0 cd-uploads-close" title="ปิด">
                <span class="material-symbols-outlined fs-18">close</span>
            </button>
        </div>
    </div>
    <div class="cd-uploads-body"></div>
</div>

<!-- เมนูคลิกขวา -->
<div class="cd-menu d-none" id="cd-menu"></div>

<input type="file" id="cd-file-input" multiple hidden>

<script type="application/json" id="cd-config">
<?php
echo json_encode([
    'customerId'   => (int) $customer['customer_id'],
    'customerName' => (string) $customer['customer_name'],
    'userId'       => $user_id,
    'folderId'     => $folderId,
    'api'          => 'ajax/customer_drive/',
    'perm'         => $perm,
    'me'           => [
        'id'   => $user_id,
        'name' => $me['name'] ?? '',
    ],
    'maxUpload'      => $maxUpload,
    'maxUploadText'  => cd_format_bytes($maxUpload),
    'allowedExt'     => cd_allowed_ext('staff'),
    'accept'         => '.' . implode(',.', cd_allowed_ext('staff')),
    'fiscalYear'     => $fiscalYear,
    'trashCount'     => $trashCount,
    'storageError'   => $storageError,
], JSON_UNESCAPED_UNICODE);
?>
</script>
