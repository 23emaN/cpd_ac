                    <div class="table-wrap">                   
                        <table class="table" style="min-width: 1250px;">
                            <thead>
                                <tr>
                                    <th rowspan="2" class="text-center" style="width: 60px; min-width: 60px; max-width: 60px; position: sticky; left: 0; z-index: 3; background-color: #ffffff; border-right: 1px solid #e2e8f0; white-space: nowrap; vertical-align: middle;">ลำดับ</th>
                                    <th rowspan="2" class="text-start" style="width: 250px; min-width: 250px; max-width: 250px; position: sticky; left: 60px; z-index: 3; background-color: #ffffff; border-right: 2px solid #e2e8f0; box-shadow: 2px 0 5px -2px rgba(0,0,0,0.1); white-space: nowrap; vertical-align: middle;">ลูกค้า</th>
                                    <th rowspan="2" class="text-center" style="width: 8%;">ผู้ทำบัญชี</th>
                                    <th rowspan="2" class="text-center" style="width: 8%;">ผู้สอบบัญชี</th>
                                    <th rowspan="2" class="text-center" style="width: 8%;"></th>
                                    <th rowspan="2" class="text-center" style="width: 8%;">ผู้ดูแล</th>
                                    <th rowspan="2" class="text-center" style="width: 9%;">เอกสาร</th>
                                    <th rowspan="2" class="text-center" style="width: 10%;">งานประจำเดือน</th>
                                    <th colspan="3" class="text-center"
                                        style="border-bottom: 1px solid #e2e8f0; background-color: #f8fafc; color: #475569; font-weight: 800;">
                                        รีวิว</th>
                                    <th rowspan="2" class="text-center" style="width: 8%;">ยื่นภาษี</th>
                                    <th rowspan="2" class="text-center" style="width: 9%;">เก็บเงิน</th>
                                    <th rowspan="2" class="text-center" style="width: 7%;">จัดการ</th>
                                </tr>
                                <tr>
                                    <th class="text-center" style="width: 8%; background-color: #f8fafc;">ผู้รีวิว 1
                                    </th>
                                    <th class="text-center" style="width: 8%; background-color: #f8fafc;">ผู้รีวิว 2
                                    </th>
                                    <th class="text-center" style="width: 8%; background-color: #f8fafc;">ผู้รีวิว 3
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php 
                                $list = [];
                                if (isset($_POST['data']) && is_array($_POST['data'])) {
                                    $list = $_POST['data'];
                                } elseif (isset($data['monthly_tasks']) && is_array($data['monthly_tasks'])) {
                                    $list = $data['monthly_tasks'];
                                }
                                $total = (int)($_POST['total'] ?? count($list));
                                $page = max(1, (int)($_POST['page'] ?? $_GET['page'] ?? 1));
                                $per_page = max(1, (int)($_POST['per_page'] ?? $_GET['per_page'] ?? 25));
                                $start = ($page - 1) * $per_page;

                                if ($total == count($list)) {
                                    $paginated_list = array_slice($list, $start, $per_page);
                                } else {
                                    $paginated_list = $list;
                                }
                            ?>
                            <?php if (! empty($paginated_list)): ?>
                                <?php foreach ($paginated_list as $index => $task): ?>
                                    <tr>
                                <?php
                                    $m = (int)($task['period_month'] ?? 0);
                                    $monthNames = [1=>'มกราคม',
                                                   2=>'กุมภาพันธ์',
                                                   3=>'มีนาคม',
                                                   4=>'เมษายน',
                                                   5=>'พฤษภาคม',
                                                   6=>'มิถุนายน',
                                                   7=>'กรกฎาคม',
                                                   8=>'สิงหาคม',
                                                   9=>'กันยายน',
                                                   10=>'ตุลาคม',
                                                   11=>'พฤศจิกายน',
                                                   12=>'ธันวาคม'
                                                ];
                                                $mName = $monthNames[$m] ?? '';
                                                $yName = !empty($data['active_fiscal_year']) ? $data['active_fiscal_year'] : '';
                                                $subtitle = $task['customer_name'] . ' · ' . $mName . ' ปี ' . $yName;
                                        ?>
                                    <td class="text-center fw-semibold text-secondary" style="width: 60px; min-width: 60px; max-width: 60px; position: sticky; left: 0; z-index: 2; background-color: #ffffff; border-right: 1px solid #e2e8f0; white-space: nowrap; vertical-align: middle;">
                                        <?php echo $index + 1; ?>
                                    </td>
                                    <td class="text-start" style="width: 250px; min-width: 250px; max-width: 250px; position: sticky; left: 60px; z-index: 2; background-color: #ffffff; border-right: 2px solid #e2e8f0; box-shadow: 2px 0 5px -2px rgba(0,0,0,0.1); white-space: nowrap; vertical-align: middle;">
                                        <div class="table-item-title"><?php echo htmlspecialchars($task['customer_name']); ?></div>
                                        <span class="caretaker-text"><?php echo htmlspecialchars($task['team_name']); ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span class="caretaker-text">ทดสอบ</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="caretaker-text">ทดสอบ</span>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!empty($task['unread_comments']) && $task['unread_comments'] > 0): ?>
                                            <span class="badge bg-danger" style="cursor: pointer;" title="มี <?php echo $task['unread_comments']; ?> ความคิดเห็นที่ยังไม่ได้อ่าน"
                                            onclick="Modal_manage(<?php echo (int)$task['period_id']; ?>,
                                            '<?php echo htmlspecialchars($subtitle ?? '', ENT_QUOTES); ?>')">
                                                <?php echo $task['unread_comments']; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="caretaker-text"><?php echo htmlspecialchars($task['caretaker_firstname'] ?? '-'); ?></span>
                                    </td>

                                    <!-- สถานะเอกสาร -->
                                    <td class="text-center">
                                        <?php if ($task['doc_status'] === '1'): ?>
                                            <span class="badge-active" style="background-color: #e8fbf0; color: #10b981;">ได้รับเอกสาร</span>
                                        <?php else: ?>
                                            <!-- สีเหลืองพาสเทลตามรูป -->
                                            <span class="badge-active" style="background-color: #fef9c3; color: #ca8a04;">ยังไม่ได้รับเอกสาร</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- สถานะงานย่อย -->
                                    <td class="text-center">
                                        <?php
                                            $total     = $task['total_tasks'];
                                            $completed = $task['completed_tasks'];
                                            $isAllDone = ($total > 0 && $total == $completed);
                                        ?>
                                        <?php if ($isAllDone): ?>
                                            <span class="badge-active" style="background-color: #e8fbf0; color: #10b981;">เสร็จสิ้น</span>
                                        <?php else: ?>
                                            <span class="badge-active" style="background-color: #eff6ff; color: #2563eb;">กำลังดำเนินงาน</span>
                                        <?php endif; ?>
                                        <div class="badge-subtext text-muted small mt-1"><?php echo $completed; ?>/<?php echo $total; ?> งาน</div>
                                    </td>
                                    
                                    <!-- รีวิว 1 -->
                                    <td class="text-center">
                                        <?php if ($task['review1_status'] === '1'): ?>
                                            <!-- สีเขียวพาสเทลตามรูป -->
                                            <span class="badge-active" style="background-color: #e8fbf0; color: #10b981;">รีวิวแล้ว</span>
                                        <?php else: ?>
                                            <span class="badge-active" style="background-color: #fef2f2; color: #ef4444;">รอรีวิว</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- รีวิว 2 -->
                                    <td class="text-center">
                                        <?php if ($task['review2_status'] === '1'): ?>
                                            <span class="badge-active" style="background-color: #e8fbf0; color: #10b981;">รีวิวแล้ว</span>
                                        <?php else: ?>
                                            <span class="badge-active" style="background-color: #fef2f2; color: #ef4444;">รอรีวิว</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- รีวิว 3 -->
                                    <td class="text-center">
                                        <?php if ($task['review3_status'] === '1'): ?>
                                            <span class="badge-active" style="background-color: #e8fbf0; color: #10b981;">รีวิวแล้ว</span>
                                        <?php else: ?>
                                            <span class="badge-active" style="background-color: #fef2f2; color: #ef4444;">รอรีวิว</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- ภาษี -->
                                    <td class="text-center">
                                        <?php if ($task['tax_status'] === '1'): ?>
                                            <span class="badge-active" style="background-color: #e8fbf0; color: #10b981;">ยื่นแล้ว</span>
                                        <?php else: ?>
                                            <!-- สีแดง/ชมพูพาสเทลตามรูป (ยังไม่ได้ยื่น) -->
                                            <span class="badge-active" style="background-color: #fef2f2; color: #ef4444;">ยังไม่ได้ยื่น</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- การชำระเงิน -->
                                    <td class="text-center">
                                        <?php if ($task['payment_status'] === '1'): ?>
                                            <!-- สีเขียวพาสเทลตามรูป (ได้รับเงินแล้ว) -->
                                            <span class="badge-active" style="background-color: #e8fbf0; color: #10b981;">ได้รับเงินแล้ว</span>
                                        <?php else: ?>
                                            <span class="badge-active" style="background-color: #fef2f2; color: #ef4444;">ยังไม่ได้รับเงิน</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-center">
                                        <div class="action-btn-group">
                                            <button type="button" class="btn-action-edit" title="ดูรายละเอียด/แก้ไข" onclick="Modal_manage(<?php echo (int)$task['period_id']; ?>, '<?php echo htmlspecialchars($subtitle, ENT_QUOTES); ?>')"><i class="ri-pencil-line"></i></button>
                                            <button type="button" class="btn-action-message" title="กล่องจดหมาย/ข้อความ"><i class="ri-mail-line"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="14" class="text-center py-4 text-muted">ไม่พบข้อมูลลูกค้าในเดือนนี้</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                        <?php if (! empty($paginated_list)): ?>
                            <?php include dirname(__DIR__) . '/_pagination.php'; ?>
                        <?php endif; ?>
