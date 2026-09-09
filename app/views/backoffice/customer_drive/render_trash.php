<?php
// app/views/backoffice/customer_drive/render_trash.php
// Ported from _export_customer_drive/files/main/ajax/customer_drive/render_trash.php
// $customer_id/$userId are set by CustomerDriveController::renderTrash().

$customer = cd_customer($customer_id);

if (!$customer) {
    echo '<div class="cd-modal"><div class="modal-header"><h5 class="modal-title">ถังขยะ</h5>'
        . '<button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>'
        . '<div class="modal-body"><div class="alert alert-warning mb-0">ไม่พบข้อมูลลูกค้า</div></div></div>';
    exit;
}

$items = cd_trash_items($customer_id);
?>
<div class="cd-modal">
<div class="modal-header">
    <div class="d-flex align-items-start gap-2">
        <i class="cd-ic ri-delete-bin-line"></i>
        <div>
            <h5 class="modal-title">ถังขยะ</h5>
            <span class="cd-modal-sub"><?php echo cd_e($customer['customer_name']); ?></span>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
</div>

<div class="modal-body">
    <div class="cd-note">
        <i class="cd-ic ri-information-line"></i>
        <div>
            ของในถังขยะ <strong>ไม่ถูกลบอัตโนมัติ</strong> จะอยู่ตรงนี้จนกว่าจะกดกู้คืนหรือลบถาวรเอง<br>
            การลบถาวรจะลบไฟล์ทุกเวอร์ชันออกจากดิสก์และ <strong>กู้กลับไม่ได้</strong>
        </div>
    </div>

    <?php if (!$items): ?>
        <div class="cd-empty">
            <i class="cd-ic cd-empty-icon ri-delete-bin-line"></i>
            <div class="cd-empty-title">ถังขยะว่างเปล่า</div>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>ชื่อ</th>
                        <th class="text-center">ข้างใน</th>
                        <th>ลบเมื่อ</th>
                        <th>ลบโดย</th>
                        <th class="text-end">การกระทำ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item):
                        $isFolder = $item['kind'] === 'folder';
                        $kind = $isFolder ? 'folder' : cd_file_kind((string) $item['ext']);
                    ?>
                        <tr data-trash-id="<?php echo (int) $item['node_id']; ?>">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="cd-icon cd-icon-<?php echo cd_e($kind); ?> cd-ic <?php echo cd_e(cd_icon_for($kind)); ?>"></i>
                                    <span class="cd-name"><?php echo cd_e($item['name']); ?></span>
                                </div>
                            </td>
                            <td class="text-center">
                                <?php echo $item['child_count'] > 0 ? (int) $item['child_count'] . ' รายการ' : '<span class="text-muted">—</span>'; ?>
                            </td>
                            <td class="fs-13"><?php echo cd_e(cd_time($item['delete_datetime'])); ?></td>
                            <td class="fs-13">
                                <?php
                                if ($item['delete_by']) {
                                    echo cd_e(cd_user_name((int) $item['delete_by']));
                                } else {
                                    echo '<span class="cd-chip cd-chip-guest">'
                                        . '<i class="cd-ic fs-14 ri-user-line"></i>ลูกค้า</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <div class="cd-rowactions">
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-cd="trash-restore">กู้คืน</button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-cd="trash-purge">ลบถาวร</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
</div>
</div>
