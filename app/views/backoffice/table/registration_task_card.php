<?php
// รับตัวแปร $task มาจากไฟล์ที่ include เข้ามา (registration_board.php)
$urgencyColor = $task['urgency_color'] ?? null;
$assigneeName = trim(($task['assignee_firstname'] ?? '') . ' ' . ($task['assignee_lastname'] ?? ''));
$isOverdue = !empty($task['due_date']) && strtotime($task['due_date']) < strtotime('today');
$cardStyle = $urgencyColor ? 'border-color: ' . htmlspecialchars($urgencyColor) . ';' : '';
?>
<div class="register-task-card" data-task-id="<?php echo (int) $task['registration']; ?>" style="<?php echo $cardStyle; ?>">

    <!-- Card Top Row: Title & Action Buttons -->
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <div class="text-dark task-card-title"><?php echo htmlspecialchars($task['registration_name']); ?></div>
            <small class="text-muted"><?php echo htmlspecialchars($task['customer_name']); ?></small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <!-- ปุ่ม ปิด Job -->
            <button type="button" class="btn btn-sm btn-close-job">
                <i class="ri-check-line"></i>
                <span>ปิด Job</span>
            </button>
            <!-- ปุ่ม 3 จุด: เมนูแก้ไข/ลบ -->
            <div class="dropdown">
                <button type="button" class="btn btn-sm btn-outline-secondary btn-card-more" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="javascript:void(0)" onclick="editRegistrationTaskModal(<?php echo (int) $task['registration']; ?>)">
                            <i class="ri-edit-line me-1"></i> แก้ไข
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item text-danger" href="javascript:void(0)" onclick="deleteRegistrationTaskCard(<?php echo (int) $task['registration']; ?>, '<?php echo addslashes($task['registration_name']); ?>')">
                            <i class="ri-delete-bin-line me-1"></i> ลบ
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Badges Row -->
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <?php if (!empty($task['urgency_label'])): ?>
            <span class="board-badge" style="background-color: <?php echo htmlspecialchars($urgencyColor ?? '#94a3b8'); ?>; color: #ffffff;">
                <?php echo htmlspecialchars($task['urgency_label']); ?>
            </span>
        <?php endif; ?>
        <?php if (!empty($task['registration_type_name'])): ?>
            <span class="board-badge board-badge-type">
                <?php echo htmlspecialchars($task['registration_type_name']); ?>
            </span>
        <?php endif; ?>
        <?php if (!empty($task['due_date'])): ?>
            <?php if ($isOverdue): ?>
                <span class="board-badge board-badge-overdue">
                    เลยกำหนด
                </span>
            <?php else: ?>
                <span class="board-badge board-badge-ontime">
                    ตามกำหนด
                </span>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Detail List -->
    <div class="d-flex flex-column gap-2 mb-3 text-secondary">
        <?php if (!empty($task['accep_date'])): ?>
            <div class="d-flex align-items-center gap-2">
                <i class="ri-calendar-line text-muted"></i>
                <span>รับงาน <?php echo date('d/m/Y', strtotime($task['accep_date'])); ?></span>
            </div>
        <?php endif; ?>
        <?php if (!empty($task['due_date'])): ?>
            <div class="d-flex align-items-center gap-2">
                <i class="ri-time-line text-muted"></i>
                <span>กำหนดส่ง <?php echo date('d/m/Y', strtotime($task['due_date'])); ?></span>
            </div>
        <?php endif; ?>
        <?php if ($assigneeName !== ''): ?>
            <div class="d-flex align-items-center gap-2">
                <i class="ri-user-line text-muted"></i>
                <span><?php echo htmlspecialchars($assigneeName); ?></span>
            </div>
        <?php endif; ?>
        <div class="d-flex align-items-center gap-2">
            <i class="ri-money-dollar-circle-line text-muted"></i>
            <span><?php echo number_format((float) $task['service_amount'], 2); ?></span>
        </div>
    </div>

    <!-- Card Footer: Job Code -->
    <div>
        <small class="text-muted">รหัสงาน: <?php echo htmlspecialchars($task['registration_no']); ?></small>
    </div>

</div> <!-- End Task Card -->
