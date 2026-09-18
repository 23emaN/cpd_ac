<?php
$customerData = $data['ws_customer_data'] ?? [];
$companiesMap = [];
$yearsSet = [];

foreach ($customerData as $row) {
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
    $companiesMap[$cId]['years'][$year] = $row['total_customers'];
}

$allYears = array_keys($yearsSet);
sort($allYears);
?>
<div class="status-box-header">
    <h4 class="status-header-h4">จำนวนลูกค้า</h4>
    <div class="status-header-action">
        แสดงปีที่ทำงาน :
        <select id="custStartYear" onchange="filterCustomerYears()">
            <?php foreach ($allYears as $index => $y): ?>
                <option value="<?php echo $y; ?>" <?php echo ($index == 0) ? 'selected' : ''; ?>><?php echo $y; ?></option>
            <?php endforeach; ?>
        </select>
        <span>ถึง</span>
        <select id="custEndYear" onchange="filterCustomerYears()">
            <?php foreach ($allYears as $y): ?>
                <option value="<?php echo $y; ?>" <?php echo ($y == end($allYears)) ? 'selected' : ''; ?>><?php echo $y; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>
<div class="table-responsive">
    <table class="progress-table" id="customerTable">
        <thead>
            <tr>
                <th>workspace</th>
                <?php foreach ($allYears as $y): ?>
                    <th class="cust-col" data-year="<?php echo $y; ?>">ปี <?php echo htmlspecialchars($y); ?></th>
                <?php endforeach; ?>
                <?php if (empty($allYears)): ?>
                    <th>ไม่มีข้อมูลปี</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($companiesMap)): ?>
                <?php foreach ($companiesMap as $cId => $cData): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($cData['name']); ?></td>
                        <?php foreach ($allYears as $y): ?>
                            <td class="cust-col" data-year="<?php echo $y; ?>">
                                <?php echo isset($cData['years'][$y]) && $cData['years'][$y] > 0 ? $cData['years'][$y] : '-'; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="100%" class="text-center">ไม่พบข้อมูล</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <div id="pagination-customerTable" class="pagination-controls"></div>
</div>

<script>
function filterCustomerYears() {
    let startYearEl = document.getElementById('custStartYear');
    let endYearEl = document.getElementById('custEndYear');
    if (!startYearEl || !endYearEl) return;
    
    let startYear = parseInt(startYearEl.value);
    let endYear = parseInt(endYearEl.value);
    
    if (startYear > endYear) {
        let temp = startYear;
        startYear = endYear;
        endYear = temp;
    }
    
    let cols = document.querySelectorAll('.cust-col');
    cols.forEach(col => {
        let year = parseInt(col.getAttribute('data-year'));
        if (year >= startYear && year <= endYear) {
            col.style.display = '';
        } else {
            col.style.display = 'none';
        }
    });

    let rows = document.querySelectorAll('#customerTable tbody tr');
    rows.forEach(row => {
        let hasData = false;
        let cells = row.querySelectorAll('.cust-col');
        cells.forEach(cell => {
            let year = parseInt(cell.getAttribute('data-year'));
            if (year >= startYear && year <= endYear) {
                if (cell.innerText.trim() !== '-') {
                    hasData = true;
                }
            }
        });
        
        // Don't hide the "No data" placeholder row if it exists
        if(row.querySelector('td[colspan]')) return;

        if (hasData) {
            row.removeAttribute('data-filtered-hidden');
        } else {
            row.setAttribute('data-filtered-hidden', 'true');
        }
    });
    
    if (typeof applyPagination === 'function') {
        if(tablePaginationState['customerTable']) tablePaginationState['customerTable'].page = 1;
        // Don't paginate the placeholder row
        applyPagination('customerTable', '#customerTable tbody tr:not(:has(td[colspan]))', 5);
    }
}

document.addEventListener('DOMContentLoaded', filterCustomerYears);
</script>
