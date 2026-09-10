<?php
$list = [];
if (isset($_POST['data']) && is_array($_POST['data'])) {
    $list = $_POST['data'];
} elseif (isset($customers) && is_array($customers)) {
    $list = $customers;
} elseif (isset($data['customers']) && is_array($data['customers'])) {
    $list = $data['customers'];
}

$total    = (int) ($_POST['total'] ?? count($list));
$page     = max(1, (int) ($_POST['page'] ?? $_GET['page'] ?? 1));
$per_page = max(1, (int) ($_POST['per_page'] ?? $_GET['per_page'] ?? 25));

if ($total == count($list)) {
    $start = ($page - 1) * $per_page;
    $paginated_list = array_slice($list, $start, $per_page);
} else {
    $paginated_list = $list;
}
?>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th class="text-start" style="width: 25%;">ชื่อลูกค้า</th>
                <th class="text-center" style="width: 14%;">สถานะ</th>
                <th class="text-center" style="width: 12%;">วันสิ้นรอบ</th>
                <th class="text-center" style="width: 15%;">ค่าบัญชี</th>
                <th class="text-center" style="width: 12%;">ผู้ดูแล</th>
                <th class="text-center" style="width: 10%;">ติดต่อ</th>
                <th class="text-center" style="width: 12%;">จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($paginated_list)): ?>
                <?php foreach ($paginated_list as $customer): ?>
                    <tr>
                        <td class="text-start">
                            <div class="table-item-title"><?php echo htmlspecialchars($customer['customer_name']); ?></div>
                            <div class="table-item-sub"><?php echo htmlspecialchars($customer['team_name'] ?: 'ยังไม่ระบุทีม'); ?></div>
                        </td>
                        <td class="text-center">
                            <?php if ($customer['active_status'] == 1): ?>
                                <span class="badge-active">ใช้บริการอยู่</span>
                            <?php else: ?>
                                <span class="badge-inactive">เลิกจ้าง</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center text-muted">
                            <?php
                            if (!empty($customer['fiscal_closing_date'])) {
                                echo date('d/m/Y', strtotime($customer['fiscal_closing_date']));
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td class="text-center">
                            <span class="fee-amount-text"><?php echo number_format($customer['accounts_amount'] ?? 0, 2); ?> บาท</span>
                        </td>
                        <td class="text-center">
                            <?php if (!empty($customer['caretaker_firstname'])): ?>
                                <span class="caretaker-text"><?php echo htmlspecialchars($customer['caretaker_firstname']); ?></span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center text-muted">
                            <?php
                            $contacts = [];
                            if (!empty($customer['contact_tel'])) $contacts[] = $customer['contact_tel'];
                            if (!empty($customer['line_id'])) $contacts[] = 'Line: ' . $customer['line_id'];
                            echo !empty($contacts) ? htmlspecialchars(implode(', ', $contacts)) : '-';
                            ?>
                        </td>
                        <td class="text-center">
                            <div class="action-btn-group">
                                <button type="button" class="btn-action-edit" title="แก้ไข" onclick="editCustomer(<?php echo $customer['customer_id']; ?>)">
                                    <i class="ri-pencil-line"></i>
                                </button>
                                <button type="button" class="btn-action-drive" title="คลังไฟล์" onclick="viewCustomerDrive(<?php echo $customer['customer_id']; ?>)">
                                    <i class="ri-folder-line"></i>
                                </button>
                                <button type="button" class="btn-action-delete" title="ลบ" onclick="deleteCustomer(<?php echo $customer['customer_id']; ?>)">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">ยังไม่มีข้อมูลลูกค้า</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if (!empty($paginated_list)): ?>
    <?php include dirname(__DIR__) . '/_pagination.php'; ?>
<?php endif; ?>