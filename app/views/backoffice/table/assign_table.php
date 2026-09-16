<div class="table-responsive table-container-card">
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>ชื่องาน</th>
                <th>รายละเอียด</th>
                <th>ผู้รับผิดชอบ</th>
                <th>วันที่กำหนดส่ง</th>
                <th class="text-center">สถานะ</th>
                <th class="text-center">จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($data['tasks'])): ?>
                <?php foreach ($data['tasks'] as $task): ?>
                    <?php 
                        $statusClass = '';
                        $statusText = '';
                        $dateClass = '';
                        $dateText = '';
                        
                        $dueDate = new DateTime($task['due_date']);
                        $now = new DateTime();
                        $diff = $now->diff($dueDate);
                        
                        $daysDiff = (int)$diff->format('%R%a'); // + is future, - is past
                        
                        if ($task['assign_status'] == '1') {
                            $statusClass = 'status-normal';
                            $statusText = 'เสร็จสิ้น';
                            $dateText = $dueDate->format('d M Y');
                        } else {
                            if ($daysDiff < 0) {
                                // Overdue
                                $statusClass = 'status-critical';
                                $statusText = 'เลยกำหนด';
                                $dateClass = 'due-date-critical';
                                $dateText = $dueDate->format('d M Y') . " (เลยกำหนด " . abs($daysDiff) . " วัน)";
                            } elseif ($daysDiff <= 5) {
                                // Critical/Warning
                                $statusClass = 'status-warning';
                                $statusText = 'ใกล้ถึงกำหนด';
                                $dateClass = 'due-date-warning';
                                $dateText = $dueDate->format('d M Y') . " (อีก " . $daysDiff . " วัน)";
                            } else {
                                // Normal
                                $statusClass = 'status-normal';
                                $statusText = 'รอดำเนินการ';
                                $dateText = $dueDate->format('d M Y') . " (อีก " . $daysDiff . " วัน)";
                            }
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
                        <td class="text-center" ><span class="status-badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span></td>
                        <td class="text-center">
                            <button type="button" class="btn-action-edit" title="แก้ไข" onclick="modal_edit_assign(<?php echo $task['assign_id']; ?>)">
                                    <i class="ri-pencil-line"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        <i class="ri-inbox-line fs-1 d-block mb-2"></i>
                        ไม่พบข้อมูลการมอบหมายงาน
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
