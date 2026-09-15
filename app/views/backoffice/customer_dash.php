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
                            <p class="page-subtitle">ภาพรวมงานของลูกค้า - ปี <?php echo htmlspecialchars($fy_display); ?></p>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn-excel-action"
                                onclick="exportCustomerDashboardExcel()">
                                <i class="ri-file-excel-2-line"></i>
                                <span>Export Excel</span>
                            </button>
                            <!-- <a href="<?php echo $baseUrl; ?>/customer" class="btn-add-action">
                                <i class="ri-list-check-2"></i>
                                <span>จัดการลูกค้า</span>
                            </a> -->
                        </div>
                    </div>

                    <div class="stats-grid">
                        <div class="stat-card dashboard-stat-card">
                            <div class="stat-icon blue"><i class="ri-user-3-line"></i></div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo number_format($totalCustomers); ?></span>
                                <span class="stat-label">ลูกค้าที่มีงานในปีนี้</span>
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
                                <h4 class="card-section-title mb-1">รายละเอียดลูกค้าที่มีงานในปีนี้</h4>
                                
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

        function getElement(elementId) {
            return document.getElementById(elementId);
        }

        function exportCustomerDashboardExcel() {
            window.location.href = `${baseUrl}/customer_dash/export`;
        }

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
            contentElement.innerHTML = '<div class="text-muted small">กำลังโหลดรายละเอียดงาน...</div>';
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

        window.exportCustomerDashboardExcel = exportCustomerDashboardExcel;
        window.toggleCustomerMonthTasks = toggleCustomerMonthTasks;
        window.openCustomerTaskDetail = openCustomerTaskDetail;
        window.showCustomerDashboardList = showCustomerDashboardList;
    })();
</script>