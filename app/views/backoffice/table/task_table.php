<?php
// ตั้งค่าที่อยู่ — view fragment: render ตารางงาน

$list = [];
if (isset($_POST['data']) && is_array($_POST['data'])) {
    $list = $_POST['data'];
} elseif (isset($data['tasks_list']) && is_array($data['tasks_list'])) {
    $list = $data['tasks_list'];
}

$total    = (int) ($_POST['total'] ?? count($list));
$page     = max(1, (int) ($_POST['page'] ?? $_GET['page'] ?? 1));
$per_page = max(1, (int) ($_POST['per_page'] ?? $_GET['per_page'] ?? 25));
$from     = $total > 0 ? ($page - 1) * $per_page + 1 : 0;

if ($total == count($list)) {
    $start = ($page - 1) * $per_page;
    $paginated_list = array_slice($list, $start, $per_page);
} else {
    $paginated_list = $list;
}

$esc  = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
?>
<div class="table-header-wrap">
    <h3 class="table-title">รายการงานประจำเดือน</h3>
    <p class="table-subtitle">ทั้งหมด <?php echo number_format($total); ?> รายการ</p>
</div>

<table class="table">
    <thead>
        <tr>
            <th class="text-center" style="width: 10%;">ลำดับ</th>
            <th class="text-start" style="width: 40%;">งาน</th>
            <th class="text-center" style="width: 20%;">ระบุจำนวนเงิน</th>
            <th class="text-center" style="width: 15%;">เลื่อน</th>
            <th class="text-center" style="width: 15%;">จัดการ</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($paginated_list)): ?>
            <?php $n = $from; foreach ($paginated_list as $task): ?>
            <tr>
                <td class="text-center fw-semibold text-secondary"><?php echo $n++ ?></td>
                <td class="text-start">
                    <div class="table-item-title"><?php echo $esc($task['tasks_name'] ?? '') ?></div>
                </td>
                <td class="text-center">
                    <?php if (($task['is_notify_amount'] ?? 0) == 1): ?>
                        <span class="badge-active">YES</span>
                    <?php else: ?>
                        <span class="badge-inactive">NO</span>
                    <?php endif; ?>
                </td>
                <td class="text-center">
                    <div class="action-btn-group">
                        <button type="button" class="btn-action-edit" title="เลื่อนขึ้น" onclick="moveTask(<?php echo $task['tasks_id'] ?>, 'up')"><i class="ri-arrow-up-s-line"></i></button>
                        <button type="button" class="btn-action-edit" title="เลื่อนลง" onclick="moveTask(<?php echo $task['tasks_id'] ?>, 'down')"><i class="ri-arrow-down-s-line"></i></button>
                    </div>
                </td>
                <td class="text-center">
                    <div class="action-btn-group">
                        <button type="button" class="btn-action-edit" title="แก้ไข" onclick="modal_edit(<?php echo $task['tasks_id'] ?>)"><i class="ri-pencil-line"></i></button>
                        <button type="button" class="btn-action-delete" title="ลบ" onclick="delete_task(<?php echo $task['tasks_id'] ?>, '<?php echo $esc($task['tasks_name'] ?? '') ?>')"><i class="ri-delete-bin-line"></i></button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" class="text-center py-5 text-muted fw-medium">
                    <div class="list-empty-icon mb-2">
                        <span class="material-symbols-outlined" aria-hidden="true" style="font-size: 48px; color: #ccc;">inbox</span>
                    </div>
                    <div class="list-empty-title text-muted" style="font-weight: 500;">ไม่พบข้อมูลงานในระบบ</div>
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php if (!empty($paginated_list)): ?>
    <?php include dirname(__DIR__) . '/_pagination.php'; ?>
<?php endif; ?>
