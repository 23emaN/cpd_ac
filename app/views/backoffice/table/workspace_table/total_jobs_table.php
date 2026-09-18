<?php
$totalJobsData = $data['ws_total_jobs'] ?? [];
$companiesMap = [];
$yearsSet = [];

foreach ($totalJobsData as $row) {
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
    
    $companiesMap[$cId]['years'][$year] = $row['total_jobs'];
}

$allYears = array_keys($yearsSet);
sort($allYears);
?>
<div class="status-box-header">
    <h4 class="status-header-h4">จำนวนงานทั้งหมด</h4>
    <div class="status-header-action">
        เลือกปี :
        <select id="jobsFilterYear">
            <?php foreach ($allYears as $y): ?>
                <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>
<div class="table-responsive">
    <table class="progress-table" id="jobsTable">
        <thead>
            <tr>
                <th>รายการ</th>
                <th>จำนวนงาน</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($companiesMap)): ?>
                <tr>
                    <td colspan="2" style="text-align:center;">ไม่มีข้อมูล</td>
                </tr>
            <?php else: ?>
                <?php foreach ($companiesMap as $cId => $cData): ?>
                    <tr class="jobs-row" data-years='<?php echo htmlspecialchars(json_encode($cData['years']), ENT_QUOTES, 'UTF-8'); ?>'>
                        <td class="text-left font-weight-bold"><?php echo htmlspecialchars($cData['name']); ?></td>
                        <td class="jobs-total">0</td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    <div id="pagination-jobsTable" class="pagination-controls"></div>
</div>

<script>
document.getElementById('jobsFilterYear').addEventListener('change', function() {
    const selectedYear = this.value;
    const rows = document.querySelectorAll('.jobs-row');
    
    rows.forEach(row => {
        const yearsData = JSON.parse(row.getAttribute('data-years'));
        if (yearsData[selectedYear]) {
            row.querySelector('.jobs-total').innerText = parseInt(yearsData[selectedYear]);
            row.removeAttribute('data-filtered-hidden');
        } else {
            row.setAttribute('data-filtered-hidden', 'true');
        }
    });
    
    if (typeof applyPagination === 'function') {
        // Reset page to 1 on filter change
        if(tablePaginationState['jobsTable']) tablePaginationState['jobsTable'].page = 1;
        applyPagination('jobsTable', '.jobs-row', 5);
    }
});

// Run once on load
document.addEventListener('DOMContentLoaded', function() {
    const jobsFilterYear = document.getElementById('jobsFilterYear');
    if(jobsFilterYear) {
        jobsFilterYear.dispatchEvent(new Event('change'));
    }
});
</script>
