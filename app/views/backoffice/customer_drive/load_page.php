<?php
// app/views/backoffice/customer_drive/load_page.php
// Ported from _export_customer_drive/files/main/ajax/customer_drive/load_page.php
// $customer_id/$userId/$folder_id are set by CustomerDriveController::loadPage().
// No permission system in this project — every logged-in user sees everything,
// so the "ลิงก์แชร์" (share link) toolbar button is dropped entirely (phase 2).

function cd_head(string $title): void
{
?>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <h3 class="mb-0"><?php echo cd_e($title); ?></h3>

        <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb align-items-center mb-0 lh-1">
                <li class="breadcrumb-item">
                    <a href="<?php echo BASE_URL; ?>/customer" class="d-flex align-items-center text-decoration-none">
                        <i class="cd-ic menu-icon ri-group-line"></i>
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

// ------------------------------------------------------------ เตรียมข้อมูล

$storageError = cd_storage_error();
$folder       = cd_folder_at($customer_id, $folder_id ?: null);
$folderId     = $folder ? (int) $folder['node_id'] : null;
$trail        = cd_breadcrumb($customer_id, $folderId);
$trashCount   = cd_trash_count($customer_id);
$maxUpload    = cd_max_upload_bytes();
$usage        = cd_customer_usage($customer_id);
$me           = cd_user($userId);

$fiscalYear = (int) date('Y') + 543;
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <h3 class="mb-1"><?php echo cd_e($customer['customer_name']); ?></h3>
        <div class="text-muted fs-13">
            <span>ใช้พื้นที่ <?php echo cd_e(cd_format_bytes($usage)); ?></span>
        </div>
    </div>

    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
        <ol class="breadcrumb align-items-center mb-0 lh-1">
            <li class="breadcrumb-item">
                <a href="<?php echo BASE_URL; ?>/customer" class="d-flex align-items-center text-decoration-none">
                    <i class="cd-ic menu-icon ri-group-line"></i>
                    <span class="fw-medium">ลูกค้า</span>
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
            <button type="button" class="btn btn-primary d-flex align-items-center gap-1" data-cd="upload-pick">
                <i class="cd-ic fs-18 ri-upload-2-line"></i>
                <span>อัปโหลด</span>
            </button>
            <button type="button" class="btn btn-outline-primary d-flex align-items-center gap-1" data-cd="mkdir">
                <i class="cd-ic fs-18 ri-folder-add-line"></i>
                <span>โฟลเดอร์ใหม่</span>
            </button>

            <!-- <div class="cd-search position-relative ms-auto">
                <i class="cd-ic cd-search-icon ri-search-line"></i>
                <input type="search" class="form-control ps-5" id="cd-search"
                    placeholder="ค้นหาชื่อไฟล์หรือโฟลเดอร์ในคลังนี้" autocomplete="off">
            </div> -->

            <div class="cd-search position-relative ms-auto">
                <i class="cd-ic cd-search-icon ri-search-line position-absolute top-50 start-0 translate-middle-y ms-3 text-muted pe-none z-1"></i>
                <input type="search" class="form-control" id="cd-search" style="padding-left: 2.5rem !important;"
                    placeholder="ค้นหาชื่อไฟล์หรือโฟลเดอร์ในคลังนี้" autocomplete="off">
            </div>

            <?php if ($isSuperAdmin): ?>
                <button type="button" class="btn cd-btn-soft cd-btn-soft-indigo d-flex align-items-center gap-1" data-cd="links">
                    <i class="cd-ic fs-18 ri-link-m"></i>
                    <span class="d-none d-md-inline">ลิงก์แชร์</span>
                </button>
            <?php endif; ?>

            <button type="button" class="btn cd-btn-soft cd-btn-soft-slate d-flex align-items-center gap-1" data-cd="activity">
                <i class="cd-ic fs-18 ri-history-line"></i>
                <span class="d-none d-md-inline">ความเคลื่อนไหว</span>
            </button>

            <button type="button" class="btn cd-btn-soft cd-btn-soft-red d-flex align-items-center gap-1" data-cd="trash">
                <i class="cd-ic fs-18 ri-delete-bin-line"></i>
                <span class="d-none d-md-inline">ถังขยะ</span>
                <span class="badge bg-danger cd-trash-count<?php echo $trashCount ? '' : ' d-none'; ?>"><?php echo (int) $trashCount; ?></span>
            </button>
        </div>

        <!-- เส้นทาง -->
        <nav class="cd-crumbs d-flex flex-wrap align-items-center gap-1 mb-3" aria-label="ตำแหน่งในคลังไฟล์">
            <button type="button" class="btn btn-sm cd-crumb<?php echo $folderId === null ? ' active' : ''; ?>"
                data-cd="go" data-folder="">
                <i class="cd-ic fs-16 ri-folder-open-line"></i>
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
        <i class="cd-ic ri-upload-cloud-2-line"></i>
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
                <i class="cd-ic fs-18 ri-arrow-down-s-line"></i>
            </button>
            <button type="button" class="btn btn-sm btn-link p-0 cd-uploads-close" title="ปิด">
                <i class="cd-ic fs-18 ri-close-line"></i>
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
        'userId'       => $userId,
        'folderId'     => $folderId,
        'api'          => BASE_URL . '/customer_drive/',
        // ไม่มีระบบสิทธิ์ละเอียดในโปรเจกต์นี้ — ล็อกอินแล้วทำได้ทุกอย่าง ยกเว้นจัดการ
        // ลิงก์แชร์ซึ่งจำกัดเฉพาะ is_super_admin (เปิดทางให้คนนอกเข้าถึงเอกสารลูกค้าได้)
        // เก็บ key พวกนี้ไว้เพราะ customer_drive.js อ่าน cfg.perm.* เพื่อซ่อน/โชว์ปุ่มเท่านั้น
        'perm'         => [
            'cdrive_edit'        => true,
            'cdrive_delete'      => true,
            'cdrive_upload'      => true,
            'cdrive_manage_link' => $isSuperAdmin,
        ],
        'me'           => [
            'id'   => $userId,
            'name' => $me['name'] ?? '',
        ],
        'maxUpload'      => $maxUpload,
        'maxUploadText'  => cd_format_bytes($maxUpload),
        'allowedExt'     => cd_allowed_ext(),
        'accept'         => '.' . implode(',.', cd_allowed_ext()),
        'fiscalYear'     => $fiscalYear,
        'trashCount'     => $trashCount,
        'storageError'   => $storageError,
    ], JSON_UNESCAPED_UNICODE);
    ?>
</script>