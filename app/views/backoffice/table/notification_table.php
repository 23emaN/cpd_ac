                            <table class="table notif-table">
                                <thead>
                                    <tr>
                                        <th class="text-center" width="5%">ลำดับ</th>
                                        <th width="45%">รายละเอียด</th>
                                        <th class="text-center" width="15%">วันที่กำหนดส่ง</th>
                                        <th class="text-center" width="15%">เวลาคงเหลือ</th>
                                        <th class="text-center" width="10%"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (! empty($data['notifications'])): ?>
                                        <?php
                                            $page    = $data['pagination']['current_page'] ?? 1;
                                            $perPage = 20;
                                            $startNo = (($page - 1) * $perPage) + 1;

                                            // Calculate display range for footer
                                            $totalItems = $data['pagination']['total_items'] ?? 0;
                                            $endNo      = min($startNo + count($data['notifications']) - 1, $totalItems);
                                        ?>
                                        <?php foreach ($data['notifications'] as $index => $notif): ?>
                                            <?php
                                                $isUnread = ($notif['is_read'] == 0);
                                                $type     = $notif['task_type'];

                                                if ($type === 'post_it') {
                                                    $title    = 'แจ้งเตือนงานใหม่';
                                                    $typeText = 'มอบหมายงาน';
                                                } else {
                                                    $title    = 'แจ้งเตือนระบบ';
                                                    $typeText = 'ทั่วไป';
                                                }
                                            ?>
                                            <tr class="<?php echo $isUnread ? 'unread' : ''; ?>" id="page-notif-item-<?php echo $notif['notif_id']; ?>">
                                                <td class="text-center"><?php echo $startNo + $index; ?></td>
                                                <td><?php echo htmlspecialchars($notif['message']); ?></td>
                                                <td class="text-center">
                                                    <?php echo !empty($notif['due_date']) ? date('d/m/Y', strtotime($notif['due_date'])) : '-'; ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php
                                                    if (!empty($notif['due_date'])) {
                                                        $dueDateObj = new DateTime($notif['due_date']);
                                                        // ตั้งค่าเวลาให้เป็น 00:00:00 เพื่อให้คำนวณเฉพาะส่วนของวันได้แม่นยำ
                                                        $dueDateObj->setTime(0, 0, 0);
                                                        
                                                        $nowObj = new DateTime();
                                                        $nowObj->setTime(0, 0, 0);
                                                        
                                                        $diffObj = $nowObj->diff($dueDateObj);
                                                        $daysDiff = (int)$diffObj->format('%R%a');
                                                        
                                                        if ($daysDiff < 0) {
                                                            echo '<span class="text-danger fw-bold">เลยกำหนด ' . abs($daysDiff) . ' วัน</span>';
                                                        } elseif ($daysDiff == 0) {
                                                            echo '<span class="text-warning fw-bold">ครบกำหนด (วันนี้)</span>';
                                                        } elseif ($daysDiff <= 5) {
                                                            echo '<span class="text-warning fw-bold">อีก ' . $daysDiff . ' วัน</span>';
                                                        } else {
                                                            echo '<span class="text-muted">อีก ' . $daysDiff . ' วัน</span>';
                                                        }
                                                    } else {
                                                        echo '-';
                                                    }
                                                    ?>
                                                </td>
                                                <td class="text-center notif-status-cell">
                                                    <?php if ($isUnread): ?>
                                                        <button type="button"
                                                                onclick="markPageNotificationRead(<?php echo (int) $notif['notif_id']; ?>)"
                                                                class="btn btn-sm btn-primary">
                                                            รับทราบงาน
                                                        </button>
                                                    <?php else: ?>
                                                        <?php if (!empty($notif['url_link'])): ?>
                                                            <?php $fixedUrl = str_replace('/backoffice/', '/', $notif['url_link']); ?>
                                                            <a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/cpd_ac/public') . $fixedUrl; ?>" 
                                                               class="btn btn-sm btn-outline-info">
                                                                ไปหน้างาน
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="text-muted"><i class="ri-checkbox-circle-fill text-success"></i> รับทราบแล้ว</span>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted">ไม่มีการแจ้งเตือนในขณะนี้</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>

                        <!-- Pagination -->
                        <?php if (! empty($data['notifications'])): ?>
                            <div class="pagination-container">
                                <div class="text-muted">
                                    แสดง <?php echo $startNo; ?>-<?php echo $endNo; ?> จาก <?php echo $totalItems; ?> รายการ
                                </div>

                                <?php if (isset($data['pagination']) && $data['pagination']['total_pages'] > 1): ?>
                                    <nav>
                                        <ul class="pagination mb-0">
                                            <li class="page-item <?php echo($page <= 1) ? 'disabled' : ''; ?>">
                                                <a class="page-link" href="?page=1">หน้าแรก</a>
                                            </li>
                                            <li class="page-item <?php echo($page <= 1) ? 'disabled' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo max(1, $page - 1); ?>">ก่อนหน้า</a>
                                            </li>

                                            <!-- Show a few pages around current -->
                                            <?php
                                                $totalPages = $data['pagination']['total_pages'];
                                                $startPage  = max(1, $page - 2);
                                                $endPage    = min($totalPages, $startPage + 4);
                                                if ($endPage - $startPage < 4) {
                                                    $startPage = max(1, $endPage - 4);
                                                }
                                                for ($i = $startPage; $i <= $endPage; $i++):
                                            ?>
                                                <li class="page-item <?php echo($i == $page) ? 'active' : ''; ?>">
                                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                                </li>
                                            <?php endfor; ?>

                                            <li class="page-item <?php echo($page >= $totalPages) ? 'disabled' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo min($totalPages, $page + 1); ?>">ถัดไป</a>
                                            </li>
                                            <li class="page-item <?php echo($page >= $totalPages) ? 'disabled' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $totalPages; ?>">หน้าสุดท้าย</a>
                                            </li>
                                        </ul>
                                    </nav>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>