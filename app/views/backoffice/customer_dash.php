<?php
require_once dirname(__DIR__) . '/main/header.php';
require_once dirname(__DIR__) . '/main/sidebar.php';

$customers = $data['customers'] ?? [];
$workStats = $data['work_stats'] ?? [];
$fy_display = $data['active_fiscal_year'] ?? 'ไม่ได้เลือกปี';
$baseUrl = defined('BASE_URL') ? BASE_URL : '/cpd_ac/public';

$totalCustomers = (int) ($workStats['total_customers'] ?? 0);
$totalTasks = (int) ($workStats['total_tasks'] ?? 0);
$completedTasks = (int) ($workStats['completed_tasks'] ?? 0);
$unfinishedTasks = (int) ($workStats['unfinished_tasks'] ?? 0);
$totalAccountsAmount = (float) ($workStats['total_accounts_amount'] ?? 0);
?>

<style>
    .customer-dashboard .dashboard-stat-card .stat-label {
        white-space: normal;
    }

    .customer-dashboard .progress-track {
        width: 150px;
        height: 8px;
        background: #e8eef6;
        border-radius: 999px;
        overflow: hidden;
    }

    .customer-dashboard .progress-value {
        height: 100%;
        background: #2563eb;
        border-radius: inherit;
        transition: width .2s ease;
    }

    .customer-dashboard .progress-text {
        min-width: 42px;
        color: #475569;
        font-size: .8rem;
        font-weight: 700;
    }

    .customer-dashboard .detail-table th,
    .customer-dashboard .detail-table td {
        padding: 8px 10px;
        font-size: .82rem;
    }

    .customer-dashboard .customer-task-detail {
        display: none;
    }
</style>

<div class="container-fluid customer-dashboard">
    <div class="main-content d-flex flex-column">
        <div class="content-wrapper">
            <div class="main-page-wrapper">
                <div class="main-card-wrapper">
                    <div class="page-header-box">
                        <div>
                            <h2 class="page-title">แดชบอร์ดลูกค้า</h2>
                            <p class="page-subtitle">
                                ภาพรวมงานของลูกค้า - ปี <?php echo htmlspecialchars($fy_display); ?>
                                <?php if ($data['filter_month'] ?? false): ?>
                                    (เดือน <?php 
                                        $thaiMonths = [
                                            1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
                                            5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
                                            9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
                                        ];
                                        echo $thaiMonths[$data['filter_month']] ?? '';
                                    ?>)
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn-excel-action" onclick="handleExport()" style="background-color: #EBF4FF; color: #007aff; border: none; border-radius: 10px; padding: 8px 16px; display: inline-flex; align-items: center; gap: 6px;">
                                <i class="ri-file-excel-2-line"></i>
                                <span>Export Excel</span>
                            </button>
                        </div>
                    </div>

                    <?php
                    $currentFilterMonth = $data['filter_month'] ?? null;
                    $currentFilterType = $currentFilterMonth ? 'monthly' : 'yearly';
                    ?>
                    
                    <div class="filter-toolbar mb-4">
                        <div class="filter-group" style="display: flex; align-items: center; gap: 10px; flex-wrap: nowrap; width: 100%;">
                            <select class="filter-select" id="filter_type" onchange="toggleFilterMonth()" style="min-width: 200px;">
                                <option value="yearly" <?php echo $currentFilterType === 'yearly' ? 'selected' : ''; ?>>รายปี</option>
                                <option value="monthly" <?php echo $currentFilterType === 'monthly' ? 'selected' : ''; ?>>รายเดือน</option>
                            </select>

                            <select class="filter-select" id="filter_month" style="min-width: 200px; display: <?php echo $currentFilterType === 'monthly' ? 'inline-block' : 'none'; ?>;">
                                <?php
                                $thaiMonths = [
                                    1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
                                    5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
                                    9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
                                ];
                                $selectedMonth = $currentFilterMonth ?: (int)date('m');
                                foreach ($thaiMonths as $m => $mName): ?>
                                    <option value="<?php echo $m; ?>" <?php echo $m === $selectedMonth ? 'selected' : ''; ?>>
                                        <?php echo $mName; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <button type="button" class="btn btn-primary btn-sm" style="height: 38px; border-radius: 8px; display: inline-flex; align-items: center; gap: 5px; padding: 0 16px;" onclick="applyDashboardFilter()">
                                <i class="ri-search-line"></i> ค้นหา
                            </button>
                        </div>
                    </div>

                    <?php $timeLabel = $currentFilterType === 'monthly' ? 'ในเดือนนี้' : 'ในปีนี้'; ?>
                    <div class="stats-grid">
                        <div class="stat-card dashboard-stat-card">
                            <div class="stat-icon blue"><i class="ri-user-3-line"></i></div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo number_format($totalCustomers); ?></span>
                                <span class="stat-label">ลูกค้าที่มีงาน<?php echo $timeLabel; ?></span>
                            </div>
                        </div>
                        <div class="stat-card dashboard-stat-card">
                            <div class="stat-icon blue"><i class="ri-task-line"></i></div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo number_format($totalTasks); ?></span>
                                <span class="stat-label">งานทั้งหมด</span>
                            </div>
                        </div>
                        <div class="stat-card dashboard-stat-card">
                            <div class="stat-icon green"><i class="ri-checkbox-circle-line"></i></div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo number_format($completedTasks); ?></span>
                                <span class="stat-label">งานเสร็จแล้ว</span>
                            </div>
                        </div>
                        <div class="stat-card dashboard-stat-card">
                            <div class="stat-icon yellow"><i class="ri-time-line"></i></div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo number_format($unfinishedTasks); ?></span>
                                <span class="stat-label">งานที่ยังไม่เสร็จ</span>
                            </div>
                        </div>
                        <div class="stat-card dashboard-stat-card">
                            <div class="stat-icon yellow"><i class="ri-wallet-3-line"></i></div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo number_format($totalAccountsAmount, 2); ?></span>
                                <span class="stat-label">ยอดทำบัญชีรวม / เดือน</span>
                            </div>
                        </div>
                    </div>

                    <div class="card dashboard-progress-card" id="customerDashboardList">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h4 class="card-section-title mb-1">รายละเอียดลูกค้าที่มีงาน<?php echo $timeLabel; ?></h4>
                                
                            </div>
                            <!-- <span class="badge rounded-pill px-3 py-2 fw-semibold" style="background-color: #eff6ff; color: #2563eb;">จำนวน <?php echo count($customers); ?> รายการ</span> -->
                        </div>
                        <div class="table-responsive">
                            <?php require_once 'table/customer_dash_table.php'; ?>
                        </div>
                    </div>

                    <div class="card dashboard-progress-card customer-task-detail" id="customerTaskDetail">
                        <div id="customerTaskDetailContent"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (() => {
        const baseUrl = <?php echo json_encode($baseUrl, JSON_UNESCAPED_SLASHES); ?>;
        const getElement = (id) => document.getElementById(id);

        function escapeHtml(value) {
            const htmlEntities = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                "'": '&#039;',
                '"': '&quot;'
            };

            return String(value ?? '-').replace(/[&<>'"]/g, (character) => htmlEntities[character]);
        }

        $(document).ready(function() {
            if ($.fn.select2) {
                $('.select2').select2({
                    width: '100%',
                    dropdownCssClass: 'custom-dropdown'
                });
                
                $('#filter_type').on('change', function() {
                    window.toggleFilterMonth();
                });
            }
        });

        window.toggleFilterMonth = function() {
            const type = $('#filter_type').val();
            const monthSelect = document.getElementById('filter_month');
            if (type === 'monthly') {
                monthSelect.style.display = 'inline-block';
            } else {
                monthSelect.style.display = 'none';
            }
        };

        window.applyDashboardFilter = async function() {
            const searchBtn = document.querySelector('button[onclick="applyDashboardFilter()"]');
            const originalBtnHtml = searchBtn ? searchBtn.innerHTML : '';
            if (searchBtn) {
                searchBtn.innerHTML = 'กำลังโหลด...';
                searchBtn.disabled = true;
            }

            const type = $('#filter_type').val();
            let fetchUrl = new URL(`${baseUrl}/customer_dash/filter`, window.location.origin);
            let pageUrl = new URL(`${baseUrl}/customer_dash`, window.location.origin);
            
            let monthName = '';
            if (type === 'monthly') {
                const month = $('#filter_month').val();
                fetchUrl.searchParams.set('month', month);
                pageUrl.searchParams.set('month', month);
                monthName = $('#filter_month option:selected').text().trim();
            }
            
            const tableContainer = document.querySelector('.table-responsive');
            tableContainer.style.pointerEvents = 'none';
            tableContainer.innerHTML = '<div class="text-center py-5">กำลังโหลดข้อมูล...</div>';
            
            try {
                const response = await fetch(fetchUrl.toString(), {
                    headers: { 'Accept': 'application/json' }
                });
                const payload = await response.json();
                
                if (payload.result) {
                    tableContainer.innerHTML = payload.html;
                    
                    const stats = payload.stats;
                    const statVals = document.querySelectorAll('.stat-val');
                    if (statVals.length >= 5) {
                        statVals[0].textContent = stats.total_customers;
                        const labelText = type === 'monthly' ? 'ลูกค้าที่มีงานในเดือนนี้' : 'ลูกค้าที่มีงานในปีนี้';
                        if (statVals[0].nextElementSibling) {
                            statVals[0].nextElementSibling.textContent = labelText;
                        }
                        
                        statVals[1].textContent = stats.total_tasks;
                        statVals[2].textContent = stats.completed_tasks;
                        statVals[3].textContent = stats.unfinished_tasks;
                        statVals[4].textContent = stats.total_accounts_amount;
                    }
                    
                    const tableTitle = document.querySelector('.card-section-title');
                    if (tableTitle) {
                        tableTitle.textContent = type === 'monthly' ? 'รายละเอียดลูกค้าที่มีงานในเดือนนี้' : 'รายละเอียดลูกค้าที่มีงานในปีนี้';
                    }
                    
                    const pageSubtitle = document.querySelector('.page-subtitle');
                    if (pageSubtitle) {
                        const yearMatch = pageSubtitle.innerHTML.match(/ปี\s+\d+/);
                        if (yearMatch) {
                            if (type === 'monthly') {
                                pageSubtitle.innerHTML = `ภาพรวมงานของลูกค้า - ${yearMatch[0]} (เดือน ${monthName})`;
                            } else {
                                pageSubtitle.innerHTML = `ภาพรวมงานของลูกค้า - ${yearMatch[0]}`;
                            }
                        }
                    }
                    
                    window.history.replaceState({}, '', pageUrl.toString());
                } else {
                    tableContainer.innerHTML = '<div class="text-center text-danger py-4">เกิดข้อผิดพลาด: ' + (payload.msg || 'ไม่สามารถดึงข้อมูลได้') + '</div>';
                }
            } catch (error) {
                console.error(error);
                tableContainer.innerHTML = '<div class="text-center text-danger py-4">เกิดข้อผิดพลาดในการเชื่อมต่อ</div>';
            } finally {
                tableContainer.style.pointerEvents = 'auto';
                if (searchBtn) {
                    searchBtn.innerHTML = originalBtnHtml;
                    searchBtn.disabled = false;
                }
            }
        };

        window.handleExport = function() {
            const type = $('#filter_type').val();
            if (type === 'yearly') {
                // Export สรุปภาพรวม
                let url = new URL(`${baseUrl}/customer_dash/export`, window.location.origin);
                window.location.href = url.toString();
            } else if (type === 'monthly') {
                // Export รายละเอียดแบบแยก Sheet
                const month = $('#filter_month').val();
                window.location.href = `${baseUrl}/customer_dash/export_tasks?type=monthly&month=${month}`;
            }
        };

        function toggleCustomerMonthTasks(month) {
            const detailRow = getElement(`customer-month-tasks-${month}`);
            const actionButton = document.querySelector(
                `.customer-task-month-action[onclick="toggleCustomerMonthTasks(${month})"]`
            );

            if (!detailRow) {
                return;
            }

            const isOpen = detailRow.classList.toggle('is-open');
            actionButton?.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        }

        function showLoadingState(contentElement) {
            contentElement.innerHTML = '<div class="text-muted small">กำลังโหลด...</div>';
        }

        async function fetchCustomerTaskDetail(customerId) {
            const detailsUrl = `${baseUrl}/customer_dash/details?customer_id=${encodeURIComponent(customerId)}`;
            const response = await fetch(detailsUrl, {
                headers: { Accept: 'application/json' }
            });
            const payload = await response.json();

            if (!payload.result) {
                throw new Error(payload.msg || 'โหลดข้อมูลไม่สำเร็จ');
            }

            return payload.html;
        }

        async function openCustomerTaskDetail(customerId) {
            const customerList = getElement('customerDashboardList');
            const taskDetail = getElement('customerTaskDetail');
            const detailContent = getElement('customerTaskDetailContent');

            customerList.style.display = 'none';
            taskDetail.style.display = 'block';
            showLoadingState(detailContent);

            try {
                detailContent.innerHTML = await fetchCustomerTaskDetail(customerId);
            } catch (error) {
                detailContent.innerHTML = `<div class="text-danger small">${escapeHtml(error.message)}</div>`;
            }
        }

        function showCustomerDashboardList() {
            getElement('customerTaskDetail').style.display = 'none';
            getElement('customerDashboardList').style.display = '';
        }

        window.toggleCustomerMonthTasks = toggleCustomerMonthTasks;
        window.openCustomerTaskDetail = openCustomerTaskDetail;
        window.showCustomerDashboardList = showCustomerDashboardList;
    })();
</script>