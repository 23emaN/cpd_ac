<?php
$annualClosingData = $data['ws_annual_closing'] ?? [];
$companiesMap = [];
$yearsSet = [];

foreach ($annualClosingData as $row) {
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
        'closed' => $row['closed_count'],
        'pending' => $row['pending_count']
    ];
}

$allYears = array_keys($yearsSet);
sort($allYears);
?>
<div class="status-box-header">
    <h4 class="status-header-h4">ปิดงบประจำปี</h4>
    <div class="status-header-action">
        เลือกปี :
        <select id="closingFilterYear">
            <?php foreach ($allYears as $y): ?>
                <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>
<div class="table-responsive">
    <table class="progress-table" id="closingTable">
        <thead>
            <tr>
                <th>รายการ</th>
                <th>ปิดงบสำเร็จ</th>
                <th>ยังไม่ปิดงบ</th>
                <th>ความต่างจากปีก่อนหน้า %</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($companiesMap)): ?>
                <tr>
                    <td colspan="4" style="text-align:center;">ไม่มีข้อมูล</td>
                </tr>
            <?php else: ?>
                <?php foreach ($companiesMap as $c): ?>
                    <tr class="closing-row" data-years='<?php echo htmlspecialchars(json_encode($c['years']), ENT_QUOTES, 'UTF-8'); ?>'>
                        <td class="text-left font-weight-bold"><?php echo htmlspecialchars($c['name']); ?></td>
                        <td class="closing-success">0</td>
                        <td class="closing-pending">0</td>
                        <td class="closing-diff">-</td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    <div id="pagination-closingTable" class="pagination-controls"></div>
</div>

<script>
document.getElementById('closingFilterYear').addEventListener('change', function() {
    const selectedYear = this.value;
    const prevYear = (parseInt(selectedYear) - 1).toString();
    const rows = document.querySelectorAll('.closing-row');
    
    rows.forEach(row => {
        const yearsData = JSON.parse(row.getAttribute('data-years'));
        
        if (!yearsData[selectedYear]) {
            row.setAttribute('data-filtered-hidden', 'true');
            return;
        } else {
            row.removeAttribute('data-filtered-hidden');
        }
        
        let currClosed = parseInt(yearsData[selectedYear].closed);
        let currPending = parseInt(yearsData[selectedYear].pending);
        let prevClosed = 0, prevPending = 0;
        
        if (yearsData[prevYear]) {
            prevClosed = parseInt(yearsData[prevYear].closed);
            prevPending = parseInt(yearsData[prevYear].pending);
        }
        
        row.querySelector('.closing-success').innerText = currClosed;
        row.querySelector('.closing-pending').innerText = currPending;
        
        const diffCell = row.querySelector('.closing-diff');
        
        let currTotal = currClosed + currPending;
        let prevTotal = prevClosed + prevPending;
        
        if (prevTotal > 0) {
            let currRate = currTotal > 0 ? (currClosed / currTotal) * 100 : 0;
            let prevRate = (prevClosed / prevTotal) * 100;
            
            let diffPercent = currRate - prevRate;
            
            if (diffPercent > 0) {
                diffCell.innerHTML = '<span style="color: #22c55e;">+' + diffPercent.toFixed(0) + '%</span>';
            } else if (diffPercent < 0) {
                diffCell.innerHTML = '<span style="color: #ef4444;">' + diffPercent.toFixed(0) + '%</span>';
            } else {
                diffCell.innerHTML = '<span style="color: #64748b;">0%</span>';
            }
        } else {
            diffCell.innerHTML = '<span style="color: #64748b;">-</span>';
        }
    });
    
    if (typeof applyPagination === 'function') {
        if(tablePaginationState['closingTable']) tablePaginationState['closingTable'].page = 1;
        applyPagination('closingTable', '.closing-row', 5);
    }
});

// Run once on load
document.addEventListener('DOMContentLoaded', function() {
    const closingFilterYear = document.getElementById('closingFilterYear');
    if(closingFilterYear) {
        closingFilterYear.dispatchEvent(new Event('change'));
    }
});
</script>
