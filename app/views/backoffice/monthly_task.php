<?php
// app/views/backoffice/closing.php
$selected_year = $_GET['year'] ?? '2569';
$company_name = $_GET['company'] ?? 'TEST ACCOUNTING';
$show_company_workspace = true;

// 1. นำ Header เข้ามา
require_once dirname(__DIR__) . '/main/header.php';

// 2. นำ Sidebar เข้ามา
require_once dirname(__DIR__) . '/main/sidebar.php';
?>

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

    /* --- Header Section --- */
    .page-header-box {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 14px;
    }

    .page-title {
        color: #1e293b;
        margin-bottom: 3px;
        letter-spacing: -0.2px;
    }

    .page-subtitle {
        font-size: 0.78rem;
        color: #94a3b8;
        font-weight: 500;
        margin: 0;
    }

    /* --- Excel Action Button --- */
    .btn-excel-action {
        background-color: #EBF4FF;
        color: #007aff;
        border: none;
        border-radius: 10px;
        padding: 8px 16px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .btn-excel-action i {
        color: #007aff;
        transition: color 0.2s ease;
    }

    .btn-excel-action:hover {
        background-color: #007aff;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(0, 122, 255, 0.25);
    }

    .btn-excel-action:hover i {
        color: #ffffff !important;
    }

    /* --- Stats Grid --- */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    @media (max-width: 1200px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
    }

    .stat-card {
        background-color: #ffffff;
        border: 1px solid #edf2f7;
        border-radius: 12px;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        gap: 14px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    }

    .stat-icon {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .stat-icon.blue {
        background-color: #eff6ff;
        color: #3b82f6;
        border: 1px solid #dbeafe;
    }

    .stat-icon.green {
        background-color: #f0fdf4;
        color: #22c55e;
        border: 1px solid #dcfce7;
    }

    .stat-icon.purple {
        background-color: #faf5ff;
        color: #a855f7;
        border: 1px solid #f3e8ff;
    }

    .stat-icon.yellow {
        background-color: #fefce8;
        color: #ca8a04;
        border: 1px solid #fef08a;
    }

    .stat-info {
        display: flex;
        flex-direction: column;
    }

    .stat-val {
        font-size: 1.20rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.1;
        margin-bottom: 2px;
    }

    .stat-label {
        color: #64748b;
    }

    /* --- Filter Toolbar --- */
    .filter-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        gap: 14px;
        flex-wrap: wrap;
    }

    .search-box-wrap {
        position: relative;
        flex: 1;
        max-width: 360px;
    }

    .search-box-wrap i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
    }

    .search-input {
        width: 100%;
        background-color: #f8fafc;
        border: 1px solid #f1f5f9;
        border-radius: 10px;
        padding: 9px 12px 9px 38px;
        color: #334155;
        font-family: inherit;
        outline: none;
        transition: all 0.2s ease;
    }

    .search-input:focus {
        background-color: #ffffff;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .search-input::placeholder {
        color: #94a3b8;
    }

    .filter-group {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        margin-bottom: 20px;
    }

    .filter-select {
        flex: 1 1 0;
        width: 100%;
        min-width: 0;
        background-color: #f8fafc;
        border: 1px solid #f1f5f9;
        border-radius: 10px;
        padding: 9px 32px 9px 14px;
        color: #475569;
        cursor: pointer;
        outline: none;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' width='16' height='16' fill='%2364748b'%3E%3Cpath d='M11.9997 13.1716L16.9495 8.22168L18.3637 9.63589L11.9997 16L5.63574 9.63589L7.04996 8.22168L11.9997 13.1716Z'%3E%3C/path%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        transition: all 0.2s ease;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .filter-select:focus {
        background-color: #ffffff;
        border-color: #3b82f6;
    }

    /* --- Standard Table Styles --- */
    .table-custom {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table-custom th {
        font-size: 0.75rem;
        font-weight: 700;
        color: #94a3b8;
        padding: 12px 14px;
        border-bottom: 1px solid #f1f5f9;
        white-space: nowrap;
    }

    .table-custom td {
        padding: 14px 14px;
        border-bottom: 1px dashed #f1f5f9;
        vertical-align: middle;
    }

    .table-custom tr:last-child td {
        border-bottom: none;
    }

    /* Action Buttons */
    .btn-action {
        width: 30px;
        height: 30px;
        border-radius: 7px;
        background-color: #ffffff;
        border: 1px solid #edf2f7;
        color: #64748b;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-action:hover {
        background-color: #eff6ff;
        color: #2563eb;
        border-color: #bfdbfe;
    }

    /* --- Pagination Section --- */
    .pagination-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 20px;
        padding-top: 16px;
        border-top: 1px solid #f8fafc;
        flex-wrap: wrap;
        gap: 14px;
    }

    .per-page-select {
        background-color: #f8fafc;
        border: 1px solid #f1f5f9;
        border-radius: 7px;
        padding: 3px 24px 3px 8px;
        color: #334155;
        outline: none;
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' width='14' height='14' fill='%2364748b'%3E%3Cpath d='M11.9997 13.1716L16.9495 8.22168L18.3637 9.63589L11.9997 16L5.63574 9.63589L7.04996 8.22168L11.9997 13.1716Z'%3E%3C/path%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 6px center;
    }

    .page-btn {
        width: 28px;
        height: 28px;
        border-radius: 7px;
        border: 1px solid transparent;
        background-color: transparent;
        color: #64748b;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .page-btn:hover {
        background-color: #f1f5f9;
        color: #0f172a;
    }

    .page-btn.active {
        background-color: #007aff;
        color: #ffffff;
    }
</style>

<div class="container-fluid">
    <div class="main-content d-flex flex-column">
        <div class="content-wrapper">
            <div class="main-page-wrapper">

                <div class="main-card-wrapper">

                    <!-- Page Header Section -->
                    <div class="page-header-box">
                        <div>
                            <h4 class="page-title">จัดการงานรายเดือน</h2>
                                <p class="page-subtitle">ภาพรวม - จัดการงานรายเดือน - เดือน... ปี...
                                    <!-- <?php echo htmlspecialchars($selected_year); ?></p> -->
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn-excel-action">
                                <i class="ri-upload-2-line"></i>
                                <span>ส่งออก Excel</span>
                            </button>
                        </div>
                    </div>

                    <!-- Stats Grid (กล่องสถิติการจัดการงานรายเดือน) -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon blue">
                                <i class="ri-user-3-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val">2</span>
                                <span class="stat-label">ลูกค้าในเดือนนี้</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon green">
                                <i class="ri-draft-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val">0</span>
                                <span class="stat-label">ได้รับเอกสาร</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon purple">
                                <i class="ri-checkbox-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val">0</span>
                                <span class="stat-label">งานเสร็จแล้ว</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon yellow">
                                <i class="ri-mail-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val">0</span>
                                <span class="stat-label">ยื่นภาษีแล้ว</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon green">
                                <i class="ri-wallet-3-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val">0</span>
                                <span class="stat-label">ได้รับเงินแล้ว</span>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Toolbar (ค้นหา & ตัวกรองสถานะ) -->
                    <div class="filter-toolbar">
                        <div class="search-box-wrap">
                            <i class="ri-search-line"></i>
                            <input type="text" class="search-input" placeholder="ค้นหาชื่อลูกค้า ผู้ดูแล รอบบัญชี">
                        </div>
                    </div>
                    <div class="filter-group">
                        <select class="filter-select">
                            <option value="">ทุกผู้ดูแล</option>
                            <option>A</option>
                            <option>B</option>
                            <option>C</option>

                        </select>

                        <select class="filter-select">
                            <option selected value="">ปิดงบ : ทั้งหมด</option>
                            <option value="1">รอเอกสาร</option>
                            <option value="2">ได้รับเอกสาร</option>
                            <option value="3">เสร็จแล้ว</option>
                        </select>

                        <select class="filter-select">
                            <option selected value="">ผู้สอบ : ทั้งหมด</option>
                            <option value="1">ยังไม่ได้ตรวจ</option>
                            <option value="2">ตรวจแล้ว</option>
                            <option value="3">ได้รับงานคืนแล้ว</option>
                        </select>

                        <select class="filter-select">
                            <option selected value="">บอจ5 : ทั้งหมด</option>
                            <option value="1">ยังไม่ได้ยื่น</option>
                            <option value="2">นำส่งแล้ว</option>
                        </select>

                        <select class="filter-select">
                            <option selected value="">BDB : ทั้งหมด</option>
                            <option value="1">ยังไม่ได้ยื่น</option>
                            <option value="2">นำส่งแล้ว</option>
                        </select>

                        <select class="filter-select">
                            <option selected value="">ภ.ง.ด 50 : ทั้งหมด</option>
                            <option value="1">รอเอกสาร</option>
                            <option value="2">ยังไม่ได้ยื่น</option>
                            <option value="3">นำส่งแล้ว</option>
                        </select>
                    </div>

                    <!-- Table Container -->
                    <div class="table-responsive">
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

                        </table>
                    </div>

                    <!-- Pagination Toolbar -->
                    <div class="pagination-toolbar">
                        <div class="d-flex align-items-center gap-2 text-muted">
                            <span>แสดง</span>
                            <select class="per-page-select">
                                <option value="25" selected>25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                            <span>รายการต่อหน้า</span>
                        </div>

                        <div class="text-muted">
                            รายการที่ 1-1 จาก 1
                        </div>

                        <div class="d-flex align-items-center gap-1">
                            <button type="button" class="page-btn" title="หน้าแรก"><i
                                    class="ri-arrow-left-double-line"></i></button>
                            <button type="button" class="page-btn" title="ก่อนหน้า"><i
                                    class="ri-arrow-left-s-line"></i></button>
                            <button type="button" class="page-btn active">1</button>
                            <button type="button" class="page-btn" title="ถัดไป"><i
                                    class="ri-arrow-right-s-line"></i></button>
                            <button type="button" class="page-btn" title="หน้าสุดท้าย"><i
                                    class="ri-arrow-right-double-line"></i></button>
                        </div>
                    </div>

                </div> <!-- End .main-card-wrapper -->
            </div>
        </div>
    </div>
</div>

<?php
// 3. นำ Footer เข้ามา
require_once dirname(__DIR__) . '/main/footer.php';
?>