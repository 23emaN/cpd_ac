<?php
// app/views/backoffice/customer.php
$selected_year = $_GET['year'] ?? '2569';
$company_name  = $_GET['company'] ?? 'TEST ACCOUNTING';
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
        font-size: 1.15rem;
        font-weight: 800;
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

    /* --- Header Action Buttons (Bootstrap Outline Buttons) --- */
    .page-header-box .btn {
        border-radius: 10px;
        
        display: inline-flex;
        align-items: center;
        
        transition: all 0.2s ease;
    }

    .page-header-box .btn i {
        color: inherit !important;
        transition: color 0.2s ease;
    }

    .page-header-box .btn:hover {
        color: #ffffff !important;
    }

    .page-header-box .btn:hover i {
        color: #ffffff !important;
    }

    .btn-add-customer {
        background-color: #007aff;
        color: #ffffff;
        border: none;
        border-radius: 10px;
        padding: 8px 18px;
        font-size: 0.80rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 4px 12px rgba(0, 122, 255, 0.25);
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .btn-add-customer i {
        color: #ffffff !important;
        font-size: 15px;
    }

    .btn-add-customer:hover {
        background-color: #0062cc;
        color: #ffffff !important;
        box-shadow: 0 6px 16px rgba(0, 122, 255, 0.35);
    }

    .btn-add-customer:hover i {
        color: #ffffff !important;
    }

    /* --- Stats Grid (4 Cards) --- */
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
        font-size: 19px;
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
        font-size: 0.75rem;
        color: #64748b;
        font-weight: 500;
    }

    /* --- Filter Toolbar (ค้นหา & ตัวกรองสถานะ) --- */
    .filter-toolbar {
        display: flex;
        align-items: center;
        justify-content: end;
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
        font-size: 16px;
    }

    .search-input {
        width: 100%;
        background-color: #f8fafc;
        border: 1px solid #f1f5f9;
        border-radius: 10px;
        padding: 9px 12px 9px 38px;
        font-size: 0.80rem;
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
    }

    .filter-select {
        background-color: #f8fafc;
        border: 1px solid #f1f5f9;
        border-radius: 10px;
        padding: 9px 32px 9px 14px;
        font-size: 0.80rem;
        font-weight: 600;
        color: #475569;
        cursor: pointer;
        outline: none;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' width='16' height='16' fill='%2364748b'%3E%3Cpath d='M11.9997 13.1716L16.9495 8.22168L18.3637 9.63589L11.9997 16L5.63574 9.63589L7.04996 8.22168L11.9997 13.1716Z'%3E%3C/path%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        min-width: 130px;
        transition: all 0.2s ease;
    }

    .filter-select:focus {
        background-color: #ffffff;
        border-color: #3b82f6;
    }

    /* --- Customer Table Styles --- */
    .customer-table-wrap {
        overflow-x: auto;
    }

    .customer-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .customer-table th {
        font-size: 0.75rem;
        font-weight: 700;
        color: #94a3b8;
        padding: 12px 14px;
        border-bottom: 1px solid #f1f5f9;
        white-space: nowrap;
    }

    .customer-table th.text-start {
        text-align: left;
    }

    .customer-table th.text-center {
        text-align: center;
    }

    .customer-table th.text-end {
        text-align: right;
    }

    .customer-table td {
        padding: 14px 14px;
        border-bottom: 1px dashed #f1f5f9;
        vertical-align: middle;
        font-size: 0.80rem;
    }

    .customer-table tr:last-child td {
        border-bottom: none;
    }

    /* Company & Team Info */
    .customer-name-title {
        font-size: 0.84rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 2px;
    }

    .customer-team-sub {
        font-size: 0.72rem;
        color: #94a3b8;
        font-weight: 500;
    }

    /* Status Badge */
    .badge-service-active {
        background-color: #ecfdf5;
        color: #10b981;
        font-size: 0.70rem;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 6px;
        display: inline-block;
        white-space: nowrap;
    }

    .badge-service-inactive {
        background-color: #fef2f2;
        color: #ef4444;
        font-size: 0.70rem;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 6px;
        display: inline-block;
        white-space: nowrap;
    }

    .fee-amount-text {
        font-weight: 800;
        color: #0f172a;
        font-size: 0.82rem;
    }

    .caretaker-text {
        font-weight: 600;
        color: #334155;
        font-size: 0.80rem;
    }

    /* Action Buttons (Edit & Delete) */
    .action-btn-cell {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .btn-action-edit {
        width: 30px;
        height: 30px;
        border-radius: 7px;
        background-color: #ffffff;
        border: 1px solid #edf2f7;
        color: #64748b;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-action-edit:hover {
        background-color: #eff6ff;
        color: #2563eb;
        border-color: #bfdbfe;
    }

    .btn-action-delete {
        width: 30px;
        height: 30px;
        border-radius: 7px;
        background-color: #fff1f2;
        border: 1px solid #ffe4e6;
        color: #f43f5e;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-action-delete:hover {
        background-color: #ffe4e6;
        color: #e11d48;
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

    .per-page-wrap {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.78rem;
        color: #64748b;
        font-weight: 500;
    }

    .per-page-select {
        background-color: #f8fafc;
        border: 1px solid #f1f5f9;
        border-radius: 7px;
        padding: 3px 24px 3px 8px;
        font-size: 0.78rem;
        font-weight: 700;
        color: #334155;
        outline: none;
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' width='14' height='14' fill='%2364748b'%3E%3Cpath d='M11.9997 13.1716L16.9495 8.22168L18.3637 9.63589L11.9997 16L5.63574 9.63589L7.04996 8.22168L11.9997 13.1716Z'%3E%3C/path%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 6px center;
    }

    .pagination-info {
        font-size: 0.78rem;
        color: #64748b;
        font-weight: 500;
    }

    .pagination-nav {
        display: flex;
        align-items: center;
        gap: 3px;
    }

    .page-btn {
        width: 28px;
        height: 28px;
        border-radius: 7px;
        border: 1px solid transparent;
        background-color: transparent;
        color: #64748b;
        font-size: 0.78rem;
        font-weight: 700;
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
                            <h2 class="page-title">จัดการงานทะเบียน</h2>
                            <p class="page-subtitle">ภาพรวม - จัดการงานทะเบียน</p>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-outline-primary">
                                <i class="ri-history-line"></i>
                                <span>ประวัติงานที่เสร็จแล้ว</span>
                            </button>
                            <button type="button" class="btn btn-outline-secondary">
                                <i class="ri-settings-2-line"></i>
                                <span>ตั้งค่าประเภทงาน</span>
                            </button>
                            <button type="button" class="btn btn-outline-secondary">
                                <i class="ri-upload-2-line"></i>
                                <span>ตั้งค่างานทะเบียน</span>
                            </button>
                            <button type="button" class="btn-add-customer" onclick="AddCustomer()">
                                <i class="ri-add-line"></i>
                                <span>เพิ่มลูกค้า</span>
                            </button>
                        </div>
                    </div>

                    <!-- Stats Grid (4 กล่องสถิติ) -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon blue">
                                <i class="ri-checkbox-circle-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val">0</span>
                                <span class="stat-label">งานทะเบียนที่เปิดอยู่</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon green">
                                <i class="ri-wallet-3-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val">0</span>
                                <span class="stat-label">ยังไม่เลยกำหนด</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon purple">
                                <i class="ri-subtract-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val">0</span>
                                <span class="stat-label">เลยกำหนดส่งงาน</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon yellow">
                                <i class="ri-money-dollar-circle-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val">0</span>
                                <span class="stat-label">ปิดงานเดือนนี้ · 0.00</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon yellow">
                                <i class="ri-money-dollar-circle-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val">0</span>
                                <span class="stat-label" style="white-space: nowrap;">ปิดงานเดือนก่อน · 0.00</span>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Toolbar (ค้นหา & ตัวกรองสถานะ) -->
                    <div class="filter-toolbar">
                        <div class="search-box-wrap">
                            <i class="ri-search-line"></i>
                            <input type="text" class="search-input" placeholder="ค้นหาลูกค้า งาน ผู้รับผิดชอบ">
                        </div>

                        <div class="filter-group">
                            <select class="filter-select">
                                <option value="">ทุกสถานะ</option>
                                <option value="1">ปกติ</option>
                                <option value="2">ด่วน</option>
                                <option value="0">ด่วนมาก</option>
                            </select>

                            <select class="filter-select">
                                <option value="">เรียงลำดับปัจุบัน</option>
                                <option value="1">วันที่รับงาน</option>
                                <option value="2">วันที่กำหนดส่ง</option>
                                <option value="0">ระดับความเร่งด่วน</option>
                            </select>
                        </div>
                    </div>

                    <!-- Board Swimlanes Wrapper -->
                    <div class="d-flex flex-column gap-4">
                        
                        <!-- Section 1: รับงานทะเบียน (0 งาน) -->
                        <div class="board-swimlane-card" style="background-color: #ffffff; border-radius: 16px; border: 1px solid #edf2f7; padding: 24px;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h5 class="mb-0 text-dark">รับงานทะเบียน</h5>
                                    <small class="text-muted">0 งาน</small>
                                </div>
                                <button type="button" class="btn" style="width: 36px; height: 36px; background-color: #eff6ff; color: #007aff; border-radius: 10px; display: flex; align-items: center; justify-content: center; border: none; padding: 0;">
                                    <i class="ri-add-line fs-5"></i>
                                </button>
                            </div>

                            <!-- Empty State (เส้นประ) -->
                            <div style="border: 1.5px dashed #e2e8f0; border-radius: 12px; padding: 36px 20px; text-align: center; color: #94a3b8; background-color: #fafbfc;">
                                ยังไม่มีงานในสถานะนี้
                            </div>
                        </div>

                        <!-- Section 2: กำลังทำ (1 งาน) -->
                        <div class="board-swimlane-card" style="background-color: #ffffff; border-radius: 16px; border: 1px solid #edf2f7; padding: 24px;">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h5 class="mb-0 text-dark">กำลังทำ</h5>
                                    <small class="text-muted">1 งาน</small>
                                </div>
                                <button type="button" class="btn" style="width: 36px; height: 36px; background-color: #eff6ff; color: #007aff; border-radius: 10px; display: flex; align-items: center; justify-content: center; border: none; padding: 0;">
                                    <i class="ri-add-line fs-5"></i>
                                </button>
                            </div>

                            <!-- Cards Container -->
                            <div style="display: flex; flex-wrap: wrap; gap: 16px;">
                                
                                <!-- Task Card -->
                                <div class="register-task-card" style="background-color: #ffffff; border: 1.5px solid #fca5a5; border-radius: 16px; padding: 18px 20px; width: 100%; max-width: 330px; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.04);">
                                    
                                    <!-- Card Top Row: Title & Action Buttons -->
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <div class="text-dark" style="line-height: 1.2;">FOLK</div>
                                            <small class="text-muted">FOLK</small>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <!-- ปุ่ม ปิด Job -->
                                            <button type="button" class="btn btn-sm" style="background-color: #fff1f2; color: #e11d48; border: none; border-radius: 8px; padding: 4px 10px; display: flex; align-items: center; gap: 4px;">
                                                <i class="ri-check-line"></i>
                                                <span>ปิด Job</span>
                                            </button>
                                            <!-- ปุ่ม 3 จุด -->
                                            <button type="button" class="btn btn-sm btn-outline-secondary" style="width: 28px; height: 28px; padding: 0; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #64748b; border-color: #e2e8f0;">
                                                <i class="ri-more-fill"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Badges Row -->
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                                        <span style="background-color: #fee2e2; color: #ef4444; border-radius: 6px; padding: 3px 10px; display: inline-block;">
                                            ด่วนมาก
                                        </span>
                                        <span style="background-color: #f1f5f9; color: #334155; border-radius: 6px; padding: 3px 10px; display: inline-block;">
                                            จดทะเบียนประกันสังคม
                                        </span>
                                        <span style="background-color: #fff1f2; color: #ef4444; border-radius: 6px; padding: 3px 10px; display: inline-block;">
                                            เลยกำหนด
                                        </span>
                                    </div>

                                    <!-- Detail List -->
                                    <div class="d-flex flex-column gap-2 mb-3 text-secondary">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="ri-calendar-line text-muted"></i>
                                            <span>รับงาน 31/08/2026</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="ri-time-line text-muted"></i>
                                            <span>กำหนดส่ง 01/09/2026</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="ri-user-line text-muted"></i>
                                            <span>ชมพู่</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="ri-money-dollar-circle-line text-muted"></i>
                                            <span>8.00</span>
                                        </div>
                                    </div>

                                    <!-- Card Footer: Job Code -->
                                    <div>
                                        <small class="text-muted">รหัสงาน: 001</small>
                                    </div>

                                </div> <!-- End Task Card -->

                            </div>

                        </div>

                    </div> <!-- End Board Swimlanes Wrapper -->

                    <!-- Pagination Toolbar ด้านล่าง -->
                    <div class="pagination-toolbar">
                        <div class="per-page-wrap">
                            <span>แสดง</span>
                            <select class="per-page-select">
                                <option value="25" selected>25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                            <span>รายการต่อหน้า</span>
                        </div>

                        <div class="pagination-info">
                            รายการที่ 1-2 จาก 2
                        </div>

                        <div class="pagination-nav">
                            <button type="button" class="page-btn" title="หน้าแรก"><i class="ri-arrow-left-double-line"></i></button>
                            <button type="button" class="page-btn" title="ก่อนหน้า"><i class="ri-arrow-left-s-line"></i></button>
                            <button type="button" class="page-btn active">1</button>
                            <button type="button" class="page-btn" title="ถัดไป"><i class="ri-arrow-right-s-line"></i></button>
                            <button type="button" class="page-btn" title="หน้าสุดท้าย"><i class="ri-arrow-right-double-line"></i></button>
                        </div>
                    </div>

                </div> <!-- End .main-card-wrapper -->
            </div>
        </div>
    </div>
</div>



<script>


</script>

<?php 
// 3. นำ Footer เข้ามา
require_once dirname(__DIR__) . '/main/footer.php'; 
?>
