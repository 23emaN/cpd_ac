<?php
$accountingFeesData = $data['ws_accounting_fees'] ?? [];
$companiesMap = [];
$yearsSet = [];

foreach ($accountingFeesData as $row) {
    if (empty($row['year'])) continue;
    $cId = $row['company_id'];
    $year = $row['year'];
    $yearsSet[$year] = true;
    
    if (!isset($companiesMap[$cId])) {
        $companiesMap[$cId] = [
            'name' => $row['company_name'],
            'years' => []
        ];
    }
    
    $companiesMap[$cId]['years'][$year] = [
        'accounts' => $row['accounts_amount'],
        'closing' => $row['closing_amount'],
        'auditing' => $row['auditing_amount']
    ];
}

$allYears = array_keys($yearsSet);
sort($allYears);
?>
<div class="status-box-header">
    <h4 class="status-header-h4">ค่าบริการ</h4>
    <div class="status-header-action">
        เลือกปี :
        <select id="feeFilterYear">
            <?php foreach ($allYears as $y): ?>
                <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>
<div class="table-responsive">
    <table class="progress-table" id="feeTable">
        <thead>
            <tr>
                <th>workspace</th>
                <th>ค่าบัญชีต่อเดือน</th>
                <th>ค่าปิดบัญชี</th>
                <th>ค่าสอบบัญชี</th>
                <th style="width: 40px; text-align: center;"><i class="fas fa-caret-down" style="color: #cbd5e1;"></i></th>
            </tr>
        </thead>
        <tbody id="accountingFeesTbody">
            <?php if (empty($companiesMap)): ?>
                <tr>
                    <td colspan="5" style="text-align:center;">ไม่มีข้อมูล</td>
                </tr>
            <?php else: ?>
                <?php foreach ($companiesMap as $c): ?>
                    <tr class="fee-row" data-years='<?php echo htmlspecialchars(json_encode($c['years']), ENT_QUOTES, 'UTF-8'); ?>'>
                        <td class="text-left font-weight-bold"><?php echo htmlspecialchars($c['name']); ?></td>
                        <td class="fee-acc">0.00</td>
                        <td class="fee-close">0.00</td>
                        <td class="fee-audit">0.00</td>
                        <td style="text-align: center;"><i class="fas fa-caret-down" style="color: #cbd5e1;"></i></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    <div id="pagination-feeTable" class="pagination-controls"></div>
</div>

<script>
document.getElementById('feeFilterYear').addEventListener('change', function() {
    const selectedYear = this.value;
    const rows = document.querySelectorAll('.fee-row');
    rows.forEach(row => {
        const yearsData = JSON.parse(row.getAttribute('data-years'));
        if (yearsData[selectedYear]) {
            row.querySelector('.fee-acc').innerText = parseFloat(yearsData[selectedYear].accounts).toLocaleString('en', {minimumFractionDigits: 2});
            row.querySelector('.fee-close').innerText = parseFloat(yearsData[selectedYear].closing).toLocaleString('en', {minimumFractionDigits: 2});
            row.querySelector('.fee-audit').innerText = parseFloat(yearsData[selectedYear].auditing).toLocaleString('en', {minimumFractionDigits: 2});
            row.removeAttribute('data-filtered-hidden');
        } else {
            row.setAttribute('data-filtered-hidden', 'true');
        }
    });
    
    if (typeof applyPagination === 'function') {
        if(tablePaginationState['feeTable']) tablePaginationState['feeTable'].page = 1;
        applyPagination('feeTable', '.fee-row', 5);
    }
});

// Run once on load
document.addEventListener('DOMContentLoaded', function() {
    const feeFilterYear = document.getElementById('feeFilterYear');
    if(feeFilterYear) {
        feeFilterYear.dispatchEvent(new Event('change'));
    }
});
</script>
