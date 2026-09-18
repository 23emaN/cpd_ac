<?php
// app/views/backoffice/dashboard_workspace.php
$selected_year = $_GET['year'] ?? '0';
$company_name = $_GET['company'] ?? 'TEST ACCOUNTING';
$show_company_workspace = true;

// 1. นำ Header เข้ามา
require_once dirname(__DIR__) . '/main/header.php';

// 2. นำ Sidebar เข้ามา
require_once dirname(__DIR__) . '/main/sidebar.php';

$baseUrl = defined('BASE_URL') ? BASE_URL : '/cpd_ac/public';
?>

<link rel="stylesheet"
    href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/template/assets/css/sidebar-menu.css">

<style>
    body {
        background-color: #f8fafc;
        font-family: 'Kanit', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .main-content {
        padding-top: 0px !important;
    }

    .main-page-wrapper {
        padding-top: 0px !important;
        padding: 24px 32px;
        min-height: calc(100vh - 72px);
    }

    /* --- Master Card Wrapper --- */
    .main-card-wrapper {
        background-color: #ffffff;
        border-radius: 16px;
        border: 1px solid #edf2f7;
        box-shadow: 0 2px 12px rgba(16, 24, 40, 0.03);
        padding: 32px;
    }

    /* Page Header Section */
    .dashboard-header-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 14px;
    }

    .dashboard-title {
        font-size: 1.35rem;
        font-weight: 800;
        color: #1e293b;
        margin: 0 0 3px 0;
        letter-spacing: -0.2px;
    }

    .dashboard-sub {
        font-size: 0.78rem;
        color: #94a3b8;
        font-weight: 500;
        margin: 0;
    }

    /* --- Workspace Mockup Styles --- */
    .stat-card {
        background: #fff;
        border: 1px solid #edf2f7;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        transition: transform 0.2s ease;
    }

    .stat-card:hover {
        transform: translateY(-3px);
    }

    .stat-title {
        color: #64748b;
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .stat-value {
        font-size: 1.8rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 4px;
    }

    .stat-desc {
        font-size: 0.8rem;
    }

    .workspace-panel {
        background: #fff;
        border: 1px solid #edf2f7;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        height: 100%;
    }

    .panel-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #f1f5f9;
        padding-bottom: 12px;
    }

    .panel-title {
        font-weight: 700;
        margin: 0;
        color: #334155;
    }

    /* --- Customer Table Styles --- */
    .status-box-card {
        background: #ffffff;
        border-radius: 14px;
        border: 1px solid #edf2f7;
        padding: 24px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    .status-box-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 8px;
    }

    .status-header-h4 {
        font-size: 1.15rem;
        font-weight: 700;
        color: #64748b;
        margin: 0;
    }

    .status-header-action {
        display: flex;
        align-items: center;
        gap: 12px;
        color: #64748b;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .status-header-action select {
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        padding: 4px 16px 4px 8px;
        font-weight: 600;
        color: #64748b;
        outline: none;
        background: #fff;
    }

    .progress-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.85rem;
    }

    .progress-table thead th {
        text-align: center;
        color: #94a3b8;
        font-weight: 600;
        font-size: 0.8rem;
        padding: 0 0 12px 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .progress-table thead th:first-child {
        text-align: left;
    }

    .progress-table tbody td {
        padding: 14px 0;
        border-bottom: 1px dashed #f1f5f9;
        color: #94a3b8;
        text-align: center;
        font-weight: 500;
    }

    .progress-table tbody td:first-child {
        text-align: left;
        color: #64748b;
        font-weight: 600;
    }

    .progress-table tbody tr:last-child td {
        border-bottom: none;
    }
</style>

<div class="main-content">
    <div class="main-page-wrapper">
        <div class="main-card-wrapper">
            <div class="dashboard-header-row">
                <div>
                    <h1 class="dashboard-title">Dashboard Workspace</h1>
                    <p class="dashboard-sub">ภาพรวมและพื้นที่ทำงานของระบบ</p>
                </div>
            </div>

            <!-- Customer Table Card -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="status-box-card">
                        <?php require_once __DIR__ . '/table/workspace_table/customer_table.php'; ?>
                    </div>
                </div>
            </div>

            <!-- Accounting Fees & Annual Closing Cards -->
            <div class="row mb-4">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <div class="status-box-card">
                        <?php require_once __DIR__ . '/table/workspace_table/accounting_fees_table.php'; ?>
                    </div>
                </div>

                <!-- Annual Closing Card -->
                <div class="col-lg-6">
                    <div class="status-box-card">
                        <?php require_once __DIR__ . '/table/workspace_table/annual_closing_table.php'; ?>
                    </div>
                </div>
            </div>

            <!-- Registration Management & Total Jobs Cards -->
            <div class="row mb-4">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <div class="status-box-card">
                        <?php require_once __DIR__ . '/table/workspace_table/registration_table.php'; ?>
                    </div>
                </div>

                <!-- Total Jobs Card -->
                <div class="col-lg-6">
                    <div class="status-box-card">
                        <?php require_once __DIR__ . '/table/workspace_table/total_jobs_table.php'; ?>
                    </div>
                </div>
            </div>

            
        </div>
    </div>
</div>

<style>
.pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 16px;
}
.pagination-info {
    font-size: 0.85rem;
    color: #64748b;
}
.pagination-controls {
    display: flex;
    gap: 4px;
}
.pagination-controls button {
    border: 1px solid #cbd5e1;
    background: #fff;
    padding: 6px 12px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.85rem;
    color: #475569;
}
.pagination-controls button:hover:not(:disabled) {
    background: #f1f5f9;
}
.pagination-controls button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
.pagination-controls button.active {
    background: #4f46e5;
    color: #fff;
    border-color: #4f46e5;
}
.empty-dummy-row td {
    color: transparent !important;
    pointer-events: none;
    border-bottom: 1px dashed #f1f5f9 !important;
}
</style>

<script>
const tablePaginationState = {};

function applyPagination(tableId, rowSelector, itemsPerPage = 5) {
    const table = document.getElementById(tableId);
    if(!table) return;
    
    // Initialize state if not exists
    if(!tablePaginationState[tableId]) {
        tablePaginationState[tableId] = { page: 1 };
    }
    
    // Clear old dummy rows
    const oldDummies = table.querySelectorAll('.empty-dummy-row');
    oldDummies.forEach(d => d.remove());
    
    // Get all rows
    const allRows = Array.from(table.querySelectorAll(rowSelector));
    
    // Filter active rows
    const activeRows = allRows.filter(row => !row.getAttribute('data-filtered-hidden'));
    
    const totalItems = activeRows.length;
    const totalPages = Math.max(1, Math.ceil(totalItems / itemsPerPage));
    
    // Ensure current page is valid
    if(tablePaginationState[tableId].page > totalPages) {
        tablePaginationState[tableId].page = totalPages;
    }
    if(tablePaginationState[tableId].page < 1) {
        tablePaginationState[tableId].page = 1;
    }
    const currPage = tablePaginationState[tableId].page;
    
    // Hide all rows first
    allRows.forEach(row => row.style.display = 'none');
    
    // Show only rows for current page
    const startIdx = (currPage - 1) * itemsPerPage;
    const endIdx = startIdx + itemsPerPage;
    const pageRows = activeRows.slice(startIdx, endIdx);
    
    pageRows.forEach(row => row.style.display = '');
    
    // Add dummy rows to fix height
    const tbody = table.querySelector('tbody');
    if(tbody) {
        // Find how many columns to span
        const firstRealRow = Array.from(tbody.querySelectorAll('tr')).find(r => !r.classList.contains('empty-dummy-row') && !r.querySelector('td[colspan]'));
        const colCount = firstRealRow ? firstRealRow.cells.length : 1;
        const dummiesNeeded = itemsPerPage - pageRows.length;
        
        for(let i=0; i<dummiesNeeded; i++) {
            const tr = document.createElement('tr');
            tr.className = 'empty-dummy-row';
            for(let c=0; c<colCount; c++) {
                const td = document.createElement('td');
                td.innerHTML = '&nbsp;';
                tr.appendChild(td);
            }
            tbody.appendChild(tr);
        }
    }
    
    // Render Pagination Controls
    const controlsContainer = document.getElementById('pagination-' + tableId);
    if(controlsContainer) {
        controlsContainer.innerHTML = '';
        controlsContainer.className = 'pagination-container';
        
        // Info text
        const infoDiv = document.createElement('div');
        infoDiv.className = 'pagination-info';
        const displayStart = totalItems === 0 ? 0 : startIdx + 1;
        const displayEnd = Math.min(startIdx + itemsPerPage, totalItems);
        infoDiv.innerText = `แสดง ${displayStart}-${displayEnd} จาก ${totalItems} รายการ`;
        controlsContainer.appendChild(infoDiv);
        
        // Buttons container
        const btnsDiv = document.createElement('div');
        btnsDiv.className = 'pagination-controls';
        
        const firstBtn = document.createElement('button');
        firstBtn.innerText = 'หน้าแรก';
        firstBtn.disabled = (currPage === 1);
        firstBtn.onclick = function() {
            tablePaginationState[tableId].page = 1;
            applyPagination(tableId, rowSelector, itemsPerPage);
        };
        btnsDiv.appendChild(firstBtn);
        
        const prevBtn = document.createElement('button');
        prevBtn.innerText = 'ก่อนหน้า';
        prevBtn.disabled = (currPage === 1);
        prevBtn.onclick = function() {
            tablePaginationState[tableId].page--;
            applyPagination(tableId, rowSelector, itemsPerPage);
        };
        btnsDiv.appendChild(prevBtn);
        
        const pageBtn = document.createElement('button');
        pageBtn.innerText = currPage;
        pageBtn.className = 'active';
        btnsDiv.appendChild(pageBtn);
        
        const nextBtn = document.createElement('button');
        nextBtn.innerText = 'ถัดไป';
        nextBtn.disabled = (currPage === totalPages || totalPages === 0);
        nextBtn.onclick = function() {
            tablePaginationState[tableId].page++;
            applyPagination(tableId, rowSelector, itemsPerPage);
        };
        btnsDiv.appendChild(nextBtn);
        
        const lastBtn = document.createElement('button');
        lastBtn.innerText = 'หน้าสุดท้าย';
        lastBtn.disabled = (currPage === totalPages || totalPages === 0);
        lastBtn.onclick = function() {
            tablePaginationState[tableId].page = totalPages;
            applyPagination(tableId, rowSelector, itemsPerPage);
        };
        btnsDiv.appendChild(lastBtn);
        
        controlsContainer.appendChild(btnsDiv);
    }
}
</script>

<?php
// 3. นำ Footer เข้ามา
require_once dirname(__DIR__) . '/main/footer.php';
?>