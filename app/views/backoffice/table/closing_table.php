<table class="table-custom">
    <thead>
        <tr>
            <th class="text-center" style="width: 4%;">ลำดับ</th>
            <th class="text-start" style="width: 22%;">ลูกค้า</th>
            <th class="text-center" style="width: 9%;">รอบบัญชี</th>
            <th class="text-center" style="width: 9%;">ผู้ดูแล</th>
            <th class="text-center" style="width: 10%;">สถานะปิดงบ</th>
            <th class="text-center" style="width: 10%;">สถานะผู้สอบ</th>
            <th class="text-center" style="width: 9%;">บอจ. 5</th>
            <th class="text-center" style="width: 11%;">DBD E-Filing</th>
            <th class="text-center" style="width: 9%;">ภ.ง.ด.50</th>
            <th class="text-center" style="width: 7%;">จัดการ</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($data['closing_data'])): ?>
            <?php foreach ($data['closing_data'] as $index => $row): ?>
                <tr>
                    <td class="text-center text-secondary"><?php echo $index + 1; ?></td>
                    <td class="text-start">
                        <div class="table-item-title"><?php echo htmlspecialchars($row['customer_name'] ?? ''); ?></div>
                    </td>
                    <td class="text-center">
                        <span class="text-secondary"><?php echo !empty($row['fiscal_closing_date']) ? date('d/m/Y', strtotime($row['fiscal_closing_date'])) : '-'; ?></span>
                    </td>
                    <td class="text-center">
                        <span class="text-secondary"><?php echo htmlspecialchars($row['user_firstname'] ?? '-'); ?></span>
                    </td>

                    <!-- สถานะปิดงบ -->
                    <td class="text-center">
                        <?php
                            $docStatus    = ($row['doc_status']    ?? '0') === '1';
                            $docDate      = !empty($row['doc_date']);
                            $closingStatus= ($row['closing_status'] ?? '0') === '1';
                            $closingDate  = !empty($row['closing_date']);
                        ?>
                        <?php if ($docStatus && $docDate && $closingStatus && $closingDate): ?>
                            <span class="badge-active" style="background-color: #e8fbf0; color: #10b981;">ปิดงบแล้ว</span>
                        <?php elseif ($docStatus && $docDate): ?>
                            <span class="badge-active" style="background-color: #e0f2fe; color: #0284c7;">ได้รับเอกสารแล้ว</span>
                        <?php else: ?>
                            <span class="badge-active" style="background-color: #fef2f2; color: #da1616ff;">รอเอกสาร</span>
                        <?php endif; ?>
                    </td>

                    <!-- สถานะผู้สอบ -->
                    <td class="text-center">
                        <?php
                            $auditStatus      = $row['audit_status']  === '1';
                            $auditDate        = !empty($row['audit_date']);
                            $budgetRefundDate = !empty($row['budget_refund_date']);
                        ?>
                        <?php if ($auditStatus && $auditDate && $budgetRefundDate): ?>
                            <span class="badge-active" style="background-color: #e8fbf0; color: #10b981;">ได้รับงบคืนแล้ว</span>
                        <?php elseif ($auditStatus && $auditDate): ?>
                            <span class="badge-active" style="background-color: #e0f2fe; color: #0284c7;">ตรวจสอบแล้ว</span>
                        <?php else: ?>
                            <span class="badge-active" style="background-color: #fef2f2; color: #da1616ff;">ยังไม่ได้ส่งตรวจ</span>
                        <?php endif; ?>
                    </td>

                    <!-- บอจ. 5 -->
                    <td class="text-center">
                        <?php
                            $boj5Status = $row['boj5_status'] === '1';
                            $boj5Date   = !empty($row['boj5_date']);
                        ?>
                        <?php if ($boj5Status && $boj5Date): ?>
                            <span class="badge-active" style="background-color: #e8fbf0; color: #10b981;">ยื่นแล้ว</span>
                        <?php else: ?>
                            <span class="badge-active" style="background-color: #fef2f2; color: #ef4444;">ยังไม่ยื่น</span>
                        <?php endif; ?>
                    </td>

                    <!-- DBD E-Filing -->
                    <td class="text-center">
                        <?php
                            $dbdStatus = $row['dbd_efiling_status']  === '1';
                            $dbdDate   = !empty($row['dbd_efiling_date']);
                        ?>
                        <?php if ($dbdStatus && $dbdDate): ?>
                            <span class="badge-active" style="background-color: #e8fbf0; color: #10b981;">ยื่นแล้ว</span>
                        <?php else: ?>
                            <span class="badge-active" style="background-color: #fef2f2; color: #ef4444;">ยังไม่ยื่น</span>
                        <?php endif; ?>
                    </td>

                    <!-- ภ.ง.ด.50 -->
                    <td class="text-center">
                        <?php
                            $pnd50Status = $row['pnd50_status'] === '1';
                            $pnd50Date   = !empty($row['pnd50_date']);
                        ?>
                        <?php if ($pnd50Status && $pnd50Date): ?>
                            <span class="badge-active" style="background-color: #e8fbf0; color: #10b981;">ยื่นแล้ว</span>
                        <?php else: ?>
                            <span class="badge-active" style="background-color: #fef2f2; color: #ef4444;">ยังไม่ยื่น</span>
                        <?php endif; ?>
                    </td>

                    <td class="text-center">
                        <div class="action-btn-group">
                            <button type="button" class="btn-action-edit" title="ดูรายละเอียด/แก้ไข" 
                                    data-closing="<?php echo htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8'); ?>"
                                    onclick="openClosingModal(this)">
                                <i class="ri-pencil-line"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="10" class="text-center py-4 text-muted">ไม่พบข้อมูลลูกค้าในปีนี้</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- Pagination Toolbar -->
<div class="pagination-toolbar mt-3 d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center gap-2 text-muted">
        <span>แสดง</span>
        <select class="per-page-select form-select form-select-sm" style="width: auto;">
            <option value="25" selected>25</option>
            <option value="50">50</option>
            <option value="100">100</option>
        </select>
        <span>รายการต่อหน้า</span>
    </div>

    <div class="text-muted">
        รายการที่ 1-<?php echo count($data['closing_data'] ?? []); ?> จาก <?php echo count($data['closing_data'] ?? []); ?>
    </div>

    <div class="d-flex align-items-center gap-1">
        <button type="button" class="btn btn-sm btn-outline-secondary" title="หน้าแรก"><i class="ri-arrow-left-double-line"></i></button>
        <button type="button" class="btn btn-sm btn-outline-secondary" title="ก่อนหน้า"><i class="ri-arrow-left-s-line"></i></button>
        <button type="button" class="btn btn-sm btn-primary active">1</button>
        <button type="button" class="btn btn-sm btn-outline-secondary" title="ถัดไป"><i class="ri-arrow-right-s-line"></i></button>
        <button type="button" class="btn btn-sm btn-outline-secondary" title="หน้าสุดท้าย"><i class="ri-arrow-right-double-line"></i></button>
    </div>
</div>
