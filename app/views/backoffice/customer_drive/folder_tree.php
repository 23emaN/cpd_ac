<?php
// app/views/backoffice/customer_drive/folder_tree.php
// Ported from _export_customer_drive/files/main/ajax/customer_drive/folder_tree.php
// $customer_id/$userId/$raw are set by CustomerDriveController::folderTree().

$customer = cd_customer($customer_id);

if (!$customer) {
    echo '<div class="cd-modal"><div class="modal-header"><h5 class="modal-title">ย้ายไป…</h5>'
        . '<button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>'
        . '<div class="modal-body"><div class="alert alert-warning mb-0">ไม่พบข้อมูลลูกค้า</div></div></div>';
    exit;
}

$moving = array_values(array_unique(array_filter(
    array_map('intval', explode(',', $raw)),
    static fn ($id) => $id > 0
)));

$blocked = [];

foreach ($moving as $id) {
    $blocked[$id] = true;

    foreach (cd_descendant_ids($customer_id, $id, true) as $child) {
        $blocked[$child] = true;
    }
}

$nodes = cd_flatten($customer_id);

$byId = [];

foreach ($nodes as $node) {
    $byId[(int) $node['node_id']] = (string) $node['name'];
}

$movingNames = [];

foreach ($moving as $id) {
    if (isset($byId[$id])) {
        $movingNames[] = $byId[$id];
    }
}

if (count($movingNames) === 1) {
    $movingLabel = $movingNames[0];
} elseif (count($movingNames) > 1) {
    $movingLabel = implode(' · ', array_slice($movingNames, 0, 2))
        . (count($movingNames) > 2 ? ' และอีก ' . (count($movingNames) - 2) . ' รายการ' : '');
} else {
    $movingLabel = count($moving) . ' รายการ';
}
?>
<div class="cd-modal">
<div class="modal-header">
    <div class="d-flex align-items-start gap-2">
        <i class="cd-ic ri-drag-move-2-line"></i>
        <div>
            <h5 class="modal-title">ย้ายไปโฟลเดอร์</h5>
            <span class="cd-modal-sub"><?php echo cd_e($movingLabel); ?></span>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
</div>

<div class="modal-body">
    <div class="cd-picker" id="cd-picker">
        <button type="button" class="cd-pick active" data-folder="">
            <i class="cd-ic fs-18 ri-folder-open-line"></i>
            <span>คลังไฟล์ (ชั้นบนสุด)</span>
        </button>

        <?php
        $shown = 0;

        foreach ($nodes as $node) {
            if ($node['kind'] !== 'folder') {
                continue;
            }

            $id = (int) $node['node_id'];
            $off = isset($blocked[$id]);
            $shown++;
        ?>
            <button type="button" class="cd-pick<?php echo $off ? ' disabled' : ''; ?>"
                data-folder="<?php echo $id; ?>"
                style="padding-left: <?php echo 12 + ((int) $node['depth'] + 1) * 18; ?>px"
                <?php echo $off ? 'disabled title="ย้ายเข้าไปในตัวเองหรือโฟลเดอร์ย่อยของตัวเองไม่ได้"' : ''; ?>>
                <i class="cd-ic fs-18 ri-folder-line"></i>
                <span><?php echo cd_e($node['name']); ?></span>
            </button>
        <?php
        }
        ?>

        <?php if ($shown === 0): ?>
            <div class="text-muted fs-13 px-2 py-3">ยังไม่มีโฟลเดอร์ในคลังนี้ ย้ายได้เฉพาะไปชั้นบนสุด</div>
        <?php endif; ?>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
    <button type="button" class="btn btn-primary" data-cd="pick-confirm">ย้ายมาที่นี่</button>
</div>
</div>
