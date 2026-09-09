<?php

/**
 * เนื้อ modal ประวัติเวอร์ชันของไฟล์ใบหนึ่ง
 *
 * เวอร์ชันเก่าทุกใบยังอยู่บนดิสก์และดาวน์โหลดได้ — นั่นคือเหตุผลที่การอัปทับ
 * ชื่อเดิมไม่เคยทำให้ของเดิมหาย
 */

include('../../../config/customer_drive.php');

$customer_id = (int) ($_POST['customer_id'] ?? 0);
$user_id     = (int) ($_POST['user_id'] ?? 0);
$node_id     = (int) ($_POST['node_id'] ?? 0);

$customer = cd_customer($customer_id);

if (!$customer || !cd_can($user_id, 'cdrive_view')) {
    echo '<div class="modal-header"><h5 class="modal-title">ประวัติเวอร์ชัน</h5>'
        . '<button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>'
        . '<div class="modal-body"><div class="alert alert-warning mb-0">ไม่มีสิทธิ์ดูไฟล์นี้</div></div>';
    exit;
}

$node = cd_find_node($customer_id, $node_id);

if (!$node || $node['kind'] !== 'file') {
    echo '<div class="modal-header"><h5 class="modal-title">ประวัติเวอร์ชัน</h5>'
        . '<button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>'
        . '<div class="modal-body"><div class="alert alert-danger mb-0">ไม่พบไฟล์</div></div>';
    exit;
}

$versions = cd_versions($node_id);
$current  = (int) $node['version'];
$canEdit  = cd_can($user_id, 'cdrive_edit');
?>
<div class="modal-header">
    <div class="d-flex align-items-start gap-2">
        <span class="material-symbols-outlined">history</span>
        <div>
            <h5 class="modal-title">ประวัติเวอร์ชัน</h5>
            <span class="cd-modal-sub"><?php echo cd_e($node['name']); ?></span>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
</div>

<div class="modal-body">
    <?php if (!$versions): ?>
        <div class="cd-empty">
            <span class="material-symbols-outlined cd-empty-icon">history</span>
            <div class="cd-empty-title">ยังไม่มีประวัติเวอร์ชัน</div>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>เวอร์ชัน</th>
                        <th>ขนาด</th>
                        <th>ที่มา</th>
                        <th>โดย</th>
                        <th>เมื่อ</th>
                        <th class="text-end">การกระทำ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($versions as $v):
                        $n = (int) $v['version'];
                        $isCurrent = $n === $current;
                        $via = match ((string) $v['via']) {
                            'guest_upload' => 'ลูกค้าส่งมา',
                            'restore'      => 'ย้อนคืนเวอร์ชัน',
                            default        => 'พนักงานอัปโหลด',
                        };
                        $who = $v['guest_label']
                            ? (string) $v['guest_label']
                            : cd_user_name($v['create_by'] ? (int) $v['create_by'] : null);
                    ?>
                        <tr data-version="<?php echo $n; ?>"<?php echo $isCurrent ? ' class="cd-ver-current"' : ''; ?>>
                            <td>
                                <span class="fw-medium">v<?php echo $n; ?></span>
                                <?php if ($isCurrent): ?>
                                    <span class="badge bg-success ms-1">ปัจจุบัน</span>
                                <?php endif; ?>
                            </td>
                            <td class="fs-13"><?php echo cd_e(cd_format_bytes((int) $v['size'])); ?></td>
                            <td class="fs-13"><?php echo cd_e($via); ?></td>
                            <td class="fs-13"><?php echo cd_e($who); ?></td>
                            <td class="fs-13"><?php echo cd_e(cd_time($v['create_datetime'])); ?></td>
                            <td>
                                <div class="cd-rowactions">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-cd="ver-download">ดาวน์โหลด</button>
                                    <?php if ($canEdit && !$isCurrent): ?>
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-cd="ver-restore">ย้อนคืน</button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="cd-foot-note">
            การย้อนคืนไม่ลบเวอร์ชันไหนทิ้ง — มันคัดลอกเวอร์ชันที่เลือกขึ้นมาเป็นเวอร์ชันใหม่ล่าสุด
        </div>
    <?php endif; ?>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
</div>
