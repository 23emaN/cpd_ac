<?php
$details = $data['customer_task_details'] ?? [];
$customerName = $data['customer_task_customer_name'] ?? 'ลูกค้าที่เลือก';
$monthNames = [
    1 => 'มกราคม',
    2 => 'กุมภาพันธ์',
    3 => 'มีนาคม',
    4 => 'เมษายน',
    5 => 'พฤษภาคม',
    6 => 'มิถุนายน',
    7 => 'กรกฎาคม',
    8 => 'สิงหาคม',
    9 => 'กันยายน',
    10 => 'ตุลาคม',
    11 => 'พฤศจิกายน',
    12 => 'ธันวาคม',
];
$detailsByMonth = [];
foreach ($details as $detail) {
    $month = (int) ($detail['period_month'] ?? 0);
    $detailsByMonth[$month][] = $detail;
}
ksort($detailsByMonth);
?>

<style>

    .btn-action-edit {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background-color: #ffffff;
    border: 0px solid #e2e8f0;
    color: #475569;
    
    }

    .customer-task-detail-row {
        display: none;
    }

    .customer-task-detail-row.is-open {
        display: table-row;
    }

    /* .customer-task-detail-panel {
        padding: 14px 20px;
    } */

    .customer-task-detail-panel .detail-task-list {
        margin: 0;
    }

    .customer-task-detail-panel .detail-task-list li {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 8px 0;
    }

    .customer-task-detail-panel .detail-task-list li:last-child {
        border-bottom: 0;
    }

    .customer-task-month-action[aria-expanded="true"] i {
        transform: rotate(90deg);
    }

    .customer-task-month-action i {
        transition: transform .2s ease;
    }
</style>

<div class="detail-header d-flex align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="card-section-title mb-1">รายละเอียดงานของลูกค้า</h4>
        <!-- <p class="page-subtitle mb-0"><?php echo htmlspecialchars($customerName); ?></p> -->
    </div>
    <button type="button" class="btn-action-edit" onclick="showCustomerDashboardList()" style="white-space: nowrap;"
        title="ย้อนกลับ" aria-label="ย้อนกลับ">
        <i class="ri-arrow-left-line"></i> ย้อนกลับ
    </button>
</div>

<?php if (empty($detailsByMonth)): ?>
    <div class="text-center text-muted py-4">ไม่พบรายการงานของลูกค้านี้</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table-custom detail-table align-middle">
            <thead>
                <tr>
                    <th style="width: 16%;">เดือน</th>
                    <th class="text-center" style="width: 18%;">งานที่เสร็จ / ทั้งหมด</th>
                    <!-- <th>รายละเอียดงาน</th> -->
                    <th class="text-center" style="width: 22%;">สถานะ</th>
                    <!-- <th class="text-center" style="width: 10%;">จัดการ</th> -->
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detailsByMonth as $month => $monthDetails): ?>
                    <?php
                    $totalMonthTasks = count($monthDetails);
                    $completedMonthTasks = count(array_filter($monthDetails, static function ($detail) {
                        return (string) ($detail['task_status'] ?? '0') === '1';
                    }));
                    $monthIsComplete = $totalMonthTasks > 0
                        && $completedMonthTasks === $totalMonthTasks;
                    $firstDetail = $monthDetails[0] ?? [];
                    $monthKey = (int) $month;
                    ?>
                    <tr>
                        <td class="fw-semibold">
                            <?php echo htmlspecialchars($monthNames[$month] ?? '-'); ?>
                        </td>
                        <td class="text-center fw-semibold">
                            <?php echo $completedMonthTasks . '/' . $totalMonthTasks; ?>
                        </td>
                        <!-- <td><?php echo htmlspecialchars($firstDetail['tasks_name'] ?? 'ไม่ระบุชื่องาน'); ?></td> -->
                        <td class="text-center">
                            <?php if ($monthIsComplete): ?>
                                <span class="badge-active" style="background-color: #e8fbf0; color: #10b981;">เสร็จแล้ว</span>
                            <?php else: ?>
                                <span class="badge-active" style="background-color: #fef2f2; color: #ef4444;">ยังไม่เสร็จ</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($totalMonthTasks > 1): ?>
                                <button type="button" class="btn-action-edit customer-task-month-action"
                                    onclick="toggleCustomerMonthTasks(<?php echo $monthKey; ?>)"
                                    aria-expanded="false" title="ดูงานทั้งหมดของเดือนนี้">
                                    <i class="ri-arrow-right-s-line"></i>
                                </button>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php if ($totalMonthTasks > 1): ?>
                        <tr class="customer-task-detail-row" id="customer-month-tasks-<?php echo $monthKey; ?>">
                            <td colspan="1">
                                <div class="customer-task-detail-panel">
                                    <ul class="list-unstyled detail-task-list">
                                        <?php foreach ($monthDetails as $detail): ?>
                                            <li>
                                                <span><?php echo htmlspecialchars($detail['tasks_name'] ?? 'ไม่ระบุชื่องาน'); ?></span>
                                                <?php if (($detail['task_status'] ?? '0') === '1'): ?>
                                                    <span class="badge-active" style="background-color: #e8fbf0; color: #10b981;">เสร็จแล้ว</span>
                                                <?php else: ?>
                                                    <span class="badge-active" style="background-color: #fef2f2; color: #ef4444;">ยังไม่เสร็จ</span>
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
