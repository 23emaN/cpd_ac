<div class="table-responsive table-container-card">
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>ชื่องาน</th>
                <th>รายละเอียด</th>
                <th>ผู้รับผิดชอบ</th>
                <th>วันที่กำหนดส่ง</th>
                <th class="text-center">สถานะเวลา</th>
                <th class="text-center">สถานะงาน</th>
                <th class="text-center">จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($data['tasks'])): ?>
                <?php foreach ($data['tasks'] as $task): ?>
                    <?php 
                        $timeStatusClass = '';
                        $timeStatusText = '';
                        $dateClass = '';
                        $dateText = '';
                        
                        $dueDate = new DateTime($task['due_date']);
                        $dueDate->setTime(0, 0, 0);
                        
                        $now = new DateTime();
                        $now->setTime(0, 0, 0);
                        
                        $diff = $now->diff($dueDate);
                        
                        $daysDiff = (int)$diff->format('%R%a'); // + is future, - is past
                        
                        if ($task['assign_status'] == '3') { // ถ้าปิดงานแล้ว
                            $timeStatusClass = '';
                            $timeStatusText = '-';
                            $dateText = $dueDate->format('d M Y');
                        } else {
                            if ($daysDiff < 0) {
                                // Overdue
                                $timeStatusClass = 'status-critical';
                                $timeStatusText = 'เลยกำหนด';
                                $dateClass = 'due-date-critical';
                                $dateText = $dueDate->format('d M Y') . " (เลยกำหนด " . abs($daysDiff) . " วัน)";
                            } elseif ($daysDiff == 0) {
                                // Due today
                                $timeStatusClass = 'status-warning';
                                $timeStatusText = 'ครบกำหนด';
                                $dateClass = 'due-date-warning';
                                $dateText = $dueDate->format('d M Y') . " (วันนี้)";
                            } elseif ($daysDiff <= 5) {
                                // Critical/Warning
                                $timeStatusClass = 'status-warning';
                                $timeStatusText = 'ใกล้ถึงกำหนด';
                                $dateClass = 'due-date-warning';
                                $dateText = $dueDate->format('d M Y') . " (อีก " . $daysDiff . " วัน)";
                            } else {
                                // Normal
                                $timeStatusClass = 'status-normal';
                                $timeStatusText = 'อยู่ในกำหนด';
                                $dateText = $dueDate->format('d M Y') . " (อีก " . $daysDiff . " วัน)";
                            }
                        }

                        $jobStatusClass = '';
                        $jobStatusText = '';
                        if ($task['assign_status'] == '0') {
                            $jobStatusClass = 'status-warning';
                            $jobStatusText = 'ดำเนินการ';
                        } elseif ($task['assign_status'] == '1') {
                            $jobStatusClass = 'status-info'; 
                            $jobStatusText = 'รอตรวจ';
                        } elseif ($task['assign_status'] == '3') {
                            $jobStatusClass = 'status-success';
                            $jobStatusText = 'ปิดงาน';
                        } else {
                            $jobStatusClass = 'status-normal';
                            $jobStatusText = 'ไม่ระบุ';
                        }
                    ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($task['assign_title']); ?></strong></td>
                        <td><?php echo htmlspecialchars($task['assign_detail'] ?? '-'); ?></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <span><?php echo htmlspecialchars(($task['user_firstname'] ?? '') . ' ' . ($task['user_lastname'] ?? '')); ?></span>
                            </div>
                        </td>
                        <td class="<?php echo $dateClass; ?>"><?php echo $dateText; ?></td>
                        <td class="text-center">
                            <?php if ($timeStatusText !== '-'): ?>
                                <span class="status-badge <?php echo $timeStatusClass; ?>"><?php echo $timeStatusText; ?></span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><span class="status-badge <?php echo $jobStatusClass; ?>"><?php echo $jobStatusText; ?></span></td>
                        <td class="text-center">
                            <button type="button" class="btn-action-edit" title="แก้ไข" onclick="modal_edit_assign(<?php echo htmlspecialchars(json_encode($task, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8'); ?>)">
                                    <i class="ri-pencil-line"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        <i class="ri-inbox-line fs-1 d-block mb-2"></i>
                        ไม่พบข้อมูลการมอบหมายงาน
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
