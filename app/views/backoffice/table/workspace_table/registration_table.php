<?php
$registrationData = $data['ws_registration'] ?? [];
$companiesMap = [];
$yearsSet = [];

foreach ($registrationData as $row) {
    if (empty($row['year'])) continue;
    $cId = $row['company_id'];
    $year = $row['year'];
    $yearsSet[$year] = true;
    $month = $row['close_month'] ?? '0'; // '0' for jobs not yet closed
    
    if (!isset($companiesMap[$cId])) {
        $companiesMap[$cId] = [
            'name' => $row['company_name'],
            'years' => []
        ];
    }
    
    if (!isset($companiesMap[$cId]['years'][$year])) {
        $companiesMap[$cId]['years'][$year] = [
            'open_jobs' => 0,
            'not_overdue' => 0,
            'overdue' => 0,
            'months' => []
        ];
    }
    
    // Open jobs don't have close_month (or it's NULL/0), we aggregate them per year
    $companiesMap[$cId]['years'][$year]['open_jobs'] += $row['open_jobs'];
    $companiesMap[$cId]['years'][$year]['not_overdue'] += $row['not_overdue'];
    $companiesMap[$cId]['years'][$year]['overdue'] += $row['overdue'];
    
    if ($month > 0) {
        if (!isset($companiesMap[$cId]['years'][$year]['months'][$month])) {
            $companiesMap[$cId]['years'][$year]['months'][$month] = 0;
        }
        $companiesMap[$cId]['years'][$year]['months'][$month] += $row['closed_jobs'];
    }
}

$allYears = array_keys($yearsSet);
sort($allYears);
?>
<div class="status-box-header">
    <h4 class="status-header-h4">จัดการงานทะเบียน</h4>
    <div class="status-header-action" style="display: flex; gap: 8px;">
        <select id="regFilterYear">
            <?php foreach ($allYears as $y): ?>
                <option value="<?php echo $y; ?>">ปี <?php echo $y; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>
<div class="table-responsive">
    <table class="progress-table" id="regTable">
        <thead>
            <tr>
                <th>รายการ</th>
                <th>งานทะเบียนที่เปิดอยู่</th>
                <th>ยังไม่เลยกำหนด</th>
                <th>เลยกำหนด</th>
            </tr>
        </thead>
        <tbody id="regTbody">
            <?php if (!empty($companiesMap)): ?>
                <?php foreach ($companiesMap as $cId => $cData): ?>
                    <?php 
                        $firstYear = !empty($cData['years']) ? array_key_first($cData['years']) : null;
                        $open = $firstYear ? $cData['years'][$firstYear]['open_jobs'] : 0;
                        $notOverdue = $firstYear ? $cData['years'][$firstYear]['not_overdue'] : 0;
                        $overdue = $firstYear ? $cData['years'][$firstYear]['overdue'] : 0;
                        
                        $jsonYears = htmlspecialchars(json_encode($cData['years']), ENT_QUOTES, 'UTF-8');
                    ?>
                    <tr class="reg-row" data-years='<?php echo $jsonYears; ?>'>
                        <td><?php echo htmlspecialchars($cData['name']); ?></td>
                        <td class="reg-open"><?php echo $open; ?></td>
                        <td class="reg-not-overdue"><?php echo $notOverdue; ?></td>
                        <td class="reg-overdue"><?php echo $overdue; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="text-align:center;">ไม่มีข้อมูล</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <div id="pagination-regTable" class="pagination-controls"></div>
</div>

<script>
function updateRegTable() {
    const yearEl = document.getElementById('regFilterYear');
    if(!yearEl) return;
    const year = yearEl.value;
    
    const rows = document.querySelectorAll('.reg-row');
    
    rows.forEach(row => {
        const yearsData = JSON.parse(row.getAttribute('data-years'));
        
        if (!yearsData[year]) {
            row.setAttribute('data-filtered-hidden', 'true');
            return;
        } else {
            row.removeAttribute('data-filtered-hidden');
        }
        
        let open = parseInt(yearsData[year].open_jobs);
        let notOverdue = parseInt(yearsData[year].not_overdue);
        let overdue = parseInt(yearsData[year].overdue);
        
        row.querySelector('.reg-open').innerText = open;
        row.querySelector('.reg-not-overdue').innerText = notOverdue;
        row.querySelector('.reg-overdue').innerText = overdue;
    });
    
    if (typeof applyPagination === 'function') {
        if(tablePaginationState['regTable']) tablePaginationState['regTable'].page = 1;
        applyPagination('regTable', '.reg-row', 5);
    }
}

const regYearSelect = document.getElementById('regFilterYear');
if(regYearSelect) regYearSelect.addEventListener('change', updateRegTable);

// Run once on load
document.addEventListener('DOMContentLoaded', updateRegTable);
</script>
