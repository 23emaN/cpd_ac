<?php
$customerList = $data['customers'] ?? [];
?>

<table class="table-custom align-middle">
    <thead>
        <tr>
            <th class="text-center" style="width: 70px;">ลำดับ</th>
            <th>ชื่อ</th>
            <th>ผู้ดูแล</th>
            <th class="text-center">ความคืบหน้า</th>
            <th class="text-center">จำนวนงาน</th>
            <th class="text-center">งานที่เสร็จ</th>
            <th class="text-center">ค่าทำบัญชี<br></th>
            <th class="text-center">ค่าปิดบัญชี<br></th>
            <th class="text-center">ค่าสอบบัญชี<br></th>
            <th class="text-center">จัดการ</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($customerList)): ?>
            <?php $timeLabel = isset($_GET['month']) && ctype_digit($_GET['month']) ? 'ในเดือนนี้' : 'ในปีนี้'; ?>
            <tr>
                <td colspan="10" class="text-center text-muted py-4">ยังไม่มีข้อมูลลูกค้าที่มีงาน<?php echo $timeLabel; ?></td>
            </tr>
        <?php else: ?>
            <?php foreach ($customerList as $index => $customer): ?>
                <?php
                $customerId = (int) ($customer['customer_id'] ?? 0);
                $customerName = $customer['customer_name'] ?? '-';
                $caretakerName = trim(
                    ($customer['caretaker_firstname'] ?? '') . ' ' .
                    ($customer['caretaker_lastname'] ?? '')
                ) ?: '-';
                $totalCustomerTasks = (int) ($customer['total_tasks'] ?? 0);
                $completedCustomerTasks = (int) ($customer['completed_tasks'] ?? 0);
                $progress = $totalCustomerTasks > 0
                    ? min(100, (int) round(($completedCustomerTasks / $totalCustomerTasks) * 100))
                    : 0;
                ?>
                <tr>
                    <td class="text-center"><?php echo $index + 1; ?></td>
                    <td class="fw-semibold"><?php echo htmlspecialchars($customerName); ?></td>
                    <td><?php echo htmlspecialchars($caretakerName); ?></td>
                    <td class="text-center">
                        <div class="d-inline-flex align-items-center gap-2"
                            aria-label="ความคืบหน้า <?php echo $progress; ?> เปอร์เซ็นต์">
                            <div class="progress-track" role="progressbar"
                                aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0"
                                aria-valuemax="100">
                                <div class="progress-value" style="width: <?php echo $progress; ?>%;"></div>
                            </div>
                            <span class="progress-text"><?php echo $progress; ?>%</span>
                        </div>
                    </td>
                    <td class="text-center"><?php echo number_format($totalCustomerTasks); ?></td>
                    <td class="text-center"><?php echo number_format($completedCustomerTasks); ?></td>
                    <td class="text-center"><?php echo number_format((float) ($customer['accounts_amount'] ?? 0)); ?></td>
                    <td class="text-center"><?php echo is_null($customer['closing_amount']) ? 'NULL' : number_format((float)$customer['closing_amount']); ?></td>
                    <td class="text-center"><?php echo is_null($customer['auditing_amount']) ? 'NULL' : number_format((float)$customer['auditing_amount']); ?></td>
                    <td class="text-center">
                        <button type="button" class="btn-action-edit manage-customer-btn"
                            onclick="openCustomerTaskDetail(<?php echo $customerId; ?>, '<?php echo htmlspecialchars($customerName, ENT_QUOTES); ?>')"
                            title="ดูรายละเอียดงาน" aria-label="ดูรายละเอียดงาน">
                            <i class="ri-arrow-right-line"></i>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
