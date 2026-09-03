<?php
// ตั้งค่าที่อยู่ — view fragment: render ตารางงาน

$list = [];
if (isset($_POST['data']) && is_array($_POST['data'])) {
    $list = $_POST['data'];
} elseif (isset($data['tasks_list']) && is_array($data['tasks_list'])) {
    $list = $data['tasks_list'];
}

$total    = (int) ($_POST['total'] ?? count($list));
$page     = max(1, (int) ($_POST['page'] ?? 1));
$per_page = max(1, (int) ($_POST['per_page'] ?? 25));
$from     = $total > 0 ? ($page - 1) * $per_page + 1 : 0;

$esc  = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
?>
<div class="table-header-wrap">
    <h3 class="table-title">รายการงานประจำเดือน</h3>
    <p class="table-subtitle">ทั้งหมด <?php echo number_format($total); ?> รายการ</p>
</div>

<table class="task-table">
    <thead>
        <tr>
            <th width="10%">ลำดับ</th>
            <th width="40%">งาน</th>
            <th width="20%">ระบุจำนวนเงิน</th>
            <th width="15%">เลื่อน</th>
            <th width="15%">จัดการ</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($list)): ?>
            <?php $n = $from; foreach ($list as $task): ?>
            <tr>
                <td><?= $n++ ?></td>
                <td><?= $esc($task['tasks_name'] ?? '') ?></td>
                <td>
                    <?php if (($task['is_notify_amount'] ?? 0) == 1): ?>
                        <span class="badge-yes">YES</span>
                    <?php else: ?>
                        <span class="badge-no" style="background-color: #fee2e2; color: #dc2626; font-size: 0.75rem; font-weight: 800; padding: 4px 10px; border-radius: 6px; display: inline-block;">NO</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="action-btn-group">
                        <button type="button" class="btn-icon btn-arrow" title="เลื่อนขึ้น"><i class="ri-arrow-up-s-line"></i></button>
                        <button type="button" class="btn-icon btn-arrow" title="เลื่อนลง"><i class="ri-arrow-down-s-line"></i></button>
                    </div>
                </td>
                <td>
                    <div class="action-btn-group">
                        <button type="button" class="btn-icon btn-edit" title="แก้ไข"><i class="ri-pencil-line"></i></button>
                        <button type="button" class="btn-icon btn-delete" title="ลบ"><i class="ri-delete-bin-line"></i></button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" class="text-center py-5">
                    <div class="list-empty-icon mb-2">
                        <span class="material-symbols-outlined" aria-hidden="true" style="font-size: 48px; color: #ccc;">inbox</span>
                    </div>
                    <div class="list-empty-title text-muted" style="font-weight: 500;">ไม่พบข้อมูลงานในระบบ</div>
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php if (!empty($list)): ?>
    <?php include dirname(__DIR__) . '/_pagination.php'; ?>
<?php endif; ?>
