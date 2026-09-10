<?php
$list = [];

if (isset($_POST['data']) && is_array($_POST['data'])) {
    $list = $_POST['data'];
} elseif (isset($data['closing_data']) && is_array($data['closing_data'])) {
    $list = $data['closing_data'];
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
        <?php if (!empty($paginated_list)): ?>
            <?php foreach ($paginated_list as $index => $row): ?>
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

<?php if (!empty($paginated_list)): ?>
    <?php include dirname(__DIR__) . '/_pagination.php'; ?>
<?php endif; ?>
