<?php

/**
 * เนื้อ modal ถังขยะ
 *
 * แสดงเฉพาะ "หัว" ของสิ่งที่ถูกลบ — ลบทั้งโฟลเดอร์จะเห็นบรรทัดเดียวพร้อมจำนวนลูก
 * เพราะผู้ใช้ลบไปทีเดียวหนึ่งครั้ง และจะกู้คืนทีเดียวหนึ่งครั้งเหมือนกัน
 *
 * ไม่มีคอลัมน์ "เหลืออีกกี่วัน" เพราะถังขยะของโมดูลนี้ไม่มีวันหมดอายุ
 */

include('../../../config/customer_drive.php');

$customer_id = (int) ($_POST['customer_id'] ?? 0);
$user_id     = (int) ($_POST['user_id'] ?? 0);

$customer = cd_customer($customer_id);

if (!$customer || !cd_can($user_id, 'cdrive_delete')) {
    echo '<div class="modal-header"><h5 class="modal-title">ถังขยะ</h5>'
        . '<button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>'
        . '<div class="modal-body"><div class="alert alert-warning mb-0">คุณไม่มีสิทธิ์จัดการถังขยะ</div></div>';
    exit;
}

$items = cd_trash_items($customer_id);
?>
<div class="modal-header">
    <div class="d-flex align-items-start gap-2">
        <span class="material-symbols-outlined">delete</span>
        <div>
            <h5 class="modal-title">ถังขยะ</h5>
            <span class="cd-modal-sub"><?php echo cd_e($customer['customer_name']); ?></span>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
</div>

<div class="modal-body">
    <div class="cd-note">
        <span class="material-symbols-outlined">info</span>
        <div>
            ของในถังขยะ <strong>ไม่ถูกลบอัตโนมัติ</strong> จะอยู่ตรงนี้จนกว่าจะกดกู้คืนหรือลบถาวรเอง<br>
            การลบถาวรจะลบไฟล์ทุกเวอร์ชันออกจากดิสก์และ <strong>กู้กลับไม่ได้</strong>
        </div>
    </div>

    <?php if (!$items): ?>
        <div class="cd-empty">
            <span class="material-symbols-outlined cd-empty-icon">delete</span>
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
                                    <span class="cd-icon cd-icon-<?php echo cd_e($kind); ?> material-symbols-outlined">
                                        <?php echo cd_e(cd_icon_for($kind)); ?>
                                    </span>
                                    <span class="cd-name"><?php echo cd_e($item['name']); ?></span>
                                </div>
                            </td>
                            <td class="text-center">
                                <?php echo $item['child_count'] > 0 ? (int) $item['child_count'] . ' รายการ' : '<span class="text-muted">—</span>'; ?>
                            </td>
                            <td class="fs-13"><?php echo cd_e(cd_time($item['delete_datetime'])); ?></td>
                            <td class="fs-13">
                                <?php
                                /*
                                 * delete_by ว่าง = ลูกค้าเป็นคนลบเอง
                                 *
                                 * portal/ajax/remove_own.php เขียน delete_by = NULL ตรง ๆ
                                 * เพราะแขกไม่มีแถวใน tbl_user ให้อ้าง ส่วน delete.php ของพนักงาน
                                 * ใส่ user_id เสมอ — ค่าว่างจึงบอกได้แน่ชัดว่าใครทำ
                                 *
                                 * ของเดิมตกไปแสดง "—" ซึ่งอ่านเหมือนข้อมูลหาย ทั้งที่รู้คำตอบอยู่แล้ว
                                 */
                                if ($item['delete_by']) {
                                    echo cd_e(cd_user_name((int) $item['delete_by']));
                                } else {
                                    echo '<span class="cd-chip cd-chip-guest">'
                                        . '<span class="material-symbols-outlined fs-14">person</span>ลูกค้า</span>';
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
