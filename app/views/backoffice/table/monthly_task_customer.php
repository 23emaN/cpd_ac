<?php
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
    12 => 'ธันวาคม'
];
$tasksByMonth = [];
foreach (($data['monthly_tasks'] ?? []) as $task) {
    $tasksByMonth[(int) ($task['period_month'] ?? 0)] = $task;
}
$customerName = $data['monthly_tasks'][0]['customer_name'] ?? 'ลูกค้าที่เลือก';
$teamName = $data['monthly_tasks'][0]['team_name'] ?? 'ไม่ระบุ';
$caretakerName = 'ไม่ระบุ';
foreach ($data['monthly_tasks'] ?? [] as $task) {
    if (!empty($task['caretaker_firstname'])) {
        $caretakerName = $task['caretaker_firstname'];
        break;
    }
}
?>
<div class="table-wrap">
    <table class="table" style="min-width: 1250px;">
        <thead>
            <tr>
                <th class="text-center">เดือน</th>
                <th class="text-start"></th>
                <th class="text-center">ผู้ทำบัญชี</th>
                <th class="text-center">ผู้สอบบัญชี</th>

                <th class="text-center">ผู้ดูแล</th>
                <th class="text-center">เอกสาร</th>
                <th class="text-center">งานประจำเดือน</th>
                <th class="text-center" style="background-color: #f8fafc;">ผู้รีวิว 1</th>
                <th class="text-center" style="background-color: #f8fafc;">ผู้รีวิว 2</th>
                <th class="text-center" style="background-color: #f8fafc;">ผู้รีวิว 3</th>
                <th class="text-center">ยื่นภาษี</th>
                <th class="text-center">เก็บเงิน</th>
            </tr>
        </thead>
        <tbody>
            <?php for ($month = 1; $month <= 12; $month++): ?>
                <?php
                $task = $tasksByMonth[$month] ?? null;
                $subtitle = $customerName . ' · ' . $monthNames[$month] . ' ปี ' . ($data['active_fiscal_year'] ?? '');
                $total = (int) ($task['total_tasks'] ?? 0);
                $completed = (int) ($task['completed_tasks'] ?? 0);
                $status = static function ($value, $doneText, $pendingText) {
                    return $value === '1'
                        ? '<span class="badge-active" style="background-color: #e8fbf0; color: #10b981;">' . $doneText . '</span>'
                        : '<span class="badge-active" style="background-color: #fef2f2; color: #ef4444;">' . $pendingText . '</span>';
                };
                ?>
                <tr>
                    <td class="text-center fw-semibold"><?php echo $monthNames[$month]; ?></td>
                    <td class="text-start">
                        <div class="d-flex align-items-center gap-2">
                            <!-- <div>
                                <div class="table-item-title"><?php echo htmlspecialchars($customerName); ?></div>
                                <span class="caretaker-text"><?php echo htmlspecialchars($teamName); ?></span>
                            </div> -->
                            <?php if ($task): ?>
                                <div class="action-btn-group flex-shrink-0">
                                    <button type="button" class="btn-action-edit" title="ดูรายละเอียด/แก้ไข"
                                        onclick="Modal_manage(<?php echo (int) $task['period_id']; ?>, '<?php echo htmlspecialchars($subtitle, ENT_QUOTES); ?>')"><i
                                            class="ri-pencil-line"></i></button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="text-center"><span class="caretaker-text">ทดสอบ</span></td>
                    <td class="text-center"><span class="caretaker-text">ทดสอบ</span></td>
                    <!-- <td class="text-center">
                        <?php if (!empty($task['unread_comments'])): ?>
                            <span class="badge bg-danger" style="cursor: pointer;" title="มีความคิดเห็นที่ยังไม่ได้อ่าน" onclick="Modal_manage(<?php echo (int) $task['period_id']; ?>, '<?php echo htmlspecialchars($subtitle, ENT_QUOTES); ?>')"><?php echo (int) $task['unread_comments']; ?></span>
                        <?php else: ?>
                            <span class="text-muted">ไม่ระบุ</span>
                        <?php endif; ?>
                    </td> -->
                    <td class="text-center"><span
                            class="caretaker-text"><?php echo htmlspecialchars($caretakerName); ?></span></td>
                    <td class="text-center">
                        <?php echo $task ? (($task['doc_status'] === '1') ? '<span class="badge-active" style="background-color: #e8fbf0; color: #10b981;">ได้รับเอกสาร</span>' : '<span class="badge-active" style="background-color: #fef9c3; color: #ca8a04;">ยังไม่ได้รับเอกสาร</span>') : '<span class="text-muted">ไม่ระบุ</span>'; ?>
                    </td>
                    <td class="text-center">
                        <?php if (!$task): ?>
                            <span class="text-muted">ไม่ระบุ</span>
                        <?php else: ?>
                            <?php echo ($total > 0 && $total === $completed) ? '<span class="badge-active" style="background-color: #e8fbf0; color: #10b981;">เสร็จสิ้น</span>' : '<span class="badge-active" style="background-color: #eff6ff; color: #2563eb;">กำลังดำเนินงาน</span>'; ?>
                            <div class="badge-subtext text-muted small mt-1"><?php echo $completed . '/' . $total; ?> งาน</div>
                        <?php endif; ?>
                    </td>
                    <?php for ($review = 1; $review <= 3; $review++): ?>
                        <td class="text-center">
                            <?php echo $task ? $status($task['review' . $review . '_status'], 'รีวิวแล้ว', 'รอรีวิว') : '<span class="text-muted">ไม่ระบุ</span>'; ?>
                        </td>
                    <?php endfor; ?>
                    <td class="text-center">
                        <?php echo $task ? $status($task['tax_status'], 'ยื่นแล้ว', 'ยังไม่ได้ยื่น') : '<span class="text-muted">ไม่ระบุ</span>'; ?>
                    </td>
                    <td class="text-center">
                        <?php echo $task ? $status($task['payment_status'], 'ได้รับเงินแล้ว', 'ยังไม่ได้รับเงิน') : '<span class="text-muted">ไม่ระบุ</span>'; ?>
                    </td>
                </tr>
            <?php endfor; ?>
        </tbody>
    </table>
</div>