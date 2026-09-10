<?php
// view fragment: render ตารางพนักงาน
$list = [];
if (isset($_POST['data']) && is_array($_POST['data'])) {
    $list = $_POST['data'];
} elseif (isset($employees) && is_array($employees)) {
    $list = $employees;
} elseif (isset($data['employees']) && is_array($data['employees'])) {
    $list = $data['employees'];
}

$total    = (int) ($_POST['total'] ?? count($list));
$page     = max(1, (int) ($_POST['page'] ?? 1));
$per_page = max(1, (int) ($_POST['per_page'] ?? 25));
$from     = $total > 0 ? ($page - 1) * $per_page + 1 : 0;
?>

<!-- Employee Table -->
<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th class="text-center" style="width: 5%;">ลำดับ</th>
                <th class="text-start" style="width: 25%;">ชื่อพนักงาน</th>
                <th class="text-center" style="width: 20%;">ตำแหน่ง</th>
                <th class="text-center" style="width: 20%;">ทีม</th>
                <th class="text-center" style="width: 15%;">สถานะ</th>
                <th class="text-center" style="width: 15%;">จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($list)): ?>
                <?php $n = $from; foreach ($list as $index => $emp): ?>
                    <tr>
                        <td class="text-center fw-semibold text-secondary">
                            <?php echo $n++; ?>
                        </td>
                        <td class="text-start">
                            <div class="table-item-title"><?php echo htmlspecialchars($emp['user_firstname'] . ' ' . $emp['user_lastname']); ?></div>
                        </td>
                        <td class="text-center">
                            <div class="fw-medium text-secondary">
                                <?php echo htmlspecialchars(($emp['position'] ?? '') ?: '-'); ?>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="fw-medium text-secondary">
                                <?php echo htmlspecialchars(($emp['team_name'] ?? '') ?: '-'); ?>
                            </div>
                        </td>
                        <td class="text-center">
                            <?php if (($emp['user_status'] ?? '') == '1'): ?>
                                <span class="badge-active">
                                    ยังทำงานอยู่
                                </span>
                            <?php else: ?>
                                <span class="badge-inactive">
                                    เลิกจ้าง
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="action-btn-group">
                                <button type="button" class="btn-action-edit" title="แก้ไข" onclick="edit_employee(<?php echo $emp['user_id']; ?>)">
                                    <i class="ri-pencil-line"></i>
                                </button>
                                <button type="button" class="btn-action-delete" title="ลบ" onclick="delete_employee(<?php echo $emp['user_id']; ?>, '<?php echo htmlspecialchars($emp['user_firstname']); ?>')">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted fw-medium">
                        ยังไม่มีข้อมูลพนักงานในปีนี้
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if (!empty($list)): ?>
    <?php include dirname(__DIR__) . '/_pagination.php'; ?>
<?php endif; ?>
