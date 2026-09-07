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

    /* --- Excel Action Buttons (Soft Blue Default, Solid Blue on Hover) --- */
    .btn-excel-action {
        background-color: #EBF4FF;
        color: #007aff;
        border: none;
        border-radius: 10px;
        padding: 8px 15px;
        font-size: 0.80rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .btn-excel-action i {
        color: #007aff;
        font-size: 16px;
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
        grid-template-columns: repeat(4, 1fr);
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

    /* ==================================================
       --- Add Customer Modal (Custom Classes) ---
       ================================================== */
    .modal-content-custom {
        border: none;
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
        background-color: #ffffff;
    }

    /* Header: sticky ให้ค้างด้านบนเสมอ ไม่ว่าจะ scroll body ไปแค่ไหน */
    .modal-header-custom {
        border-bottom: 1px solid #f1f5f9;
        padding: 24px 28px 16px 28px;
        position: sticky;
        top: 0;
        z-index: 10;
        background-color: #ffffff;
        border-radius: 20px 20px 0 0;
    }

    .modal-title-custom {
        font-weight: 800;
        color: #1e293b;
        font-size: 1.25rem;
        margin: 0;
    }

    .modal-close-custom {
        font-size: 0.9rem;
        opacity: 0.4;
    }

    /* Body: มีแต่ตัวนี้เท่านั้นที่ scroll ได้ (จัดการโดย .modal-dialog-scrollable ของ Bootstrap) */
    .modal-body-custom {
        padding: 20px 28px;
        overflow-x: hidden;
    }

    /* Footer: sticky ให้ค้างด้านล่างเสมอ ไม่ว่าจะ scroll body ไปแค่ไหน */
    .modal-footer-custom {
        border-top: 1px solid #f1f5f9;
        padding: 16px 28px;
        gap: 12px;
        justify-content: flex-end;
        position: sticky;
        bottom: 0;
        z-index: 10;
        background-color: #ffffff;
        border-radius: 0 0 20px 20px;
    }

    .modal-dialog-custom {
        max-width: 1000px;
    }

    /* --- Form Elements ภายใน Modal --- */
    .modal-section-title {
        font-weight: 800;
        color: #1e293b;
        font-size: 1.05rem;
        margin-bottom: 16px;
    }

    .modal-section-divider {
        border-top: 1px dashed #e2e8f0;
        margin: 28px 0 24px 0;
    }

    .modal-form-label {
        font-weight: 700;
        font-size: 0.88rem;
        color: #1e293b;
        margin-bottom: 8px;
    }

    .modal-form-label-required {
        font-weight: 1000;
        font-size: 0.88rem;
        color: #1e293b;
        margin-bottom: 8px;
    }

    .modal-required-mark {
        color: #ef4444;
    }

    .modal-form-control {
        background-color: #f8fafc;
        border: 1px solid #f1f5f9;
        border-radius: 12px;
        padding: 12px 16px;
        font-weight: 600;
        color: #1e293b;
        font-size: 0.92rem;
        outline: none;
        box-shadow: none;
    }

    .modal-form-control-highlight {
        background-color: #eff6ff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px 16px;
        font-weight: 600;
        color: #1e293b;
        font-size: 0.92rem;
        outline: none;
        box-shadow: none;
    }

    .modal-form-select {
        background-color: #f8fafc;
        border: 1px solid #f1f5f9;
        border-radius: 12px;
        padding: 12px 36px 12px 16px;
        font-weight: 600;
        color: #1e293b;
        font-size: 0.92rem;
        outline: none;
        box-shadow: none;
        cursor: pointer;
    }

    .modal-input-icon-wrap {
        position: relative;
    }

    .modal-input-with-icon {
        padding-right: 40px;
    }

    .modal-input-icon {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #64748b;
        font-size: 18px;
    }

    .modal-input-icon-static {
        pointer-events: none;
    }

    .modal-input-icon-clickable {
        cursor: pointer;
    }

    .modal-video-btn {
        background-color: #eff6ff;
        color: #3b82f6;
        font-weight: 600;
        border-radius: 8px;
        font-size: 0.8rem;
        border: none;
        padding: 4px 10px;
    }

    .modal-checkbox-item {
        background-color: #ffffff;
        border: 1px solid #f1f5f9;
        border-radius: 12px;
        padding: 12px 16px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .modal-checkbox-input {
        width: 20px;
        height: 20px;
        margin-top: 0;
        margin-right: 12px;
        border-radius: 50%;
        border-color: #cbd5e1;
        background-color: #ffffff;
        box-shadow: none;
        cursor: pointer;
    }

    .modal-checkbox-label {
        font-weight: 700;
        font-size: 0.9rem;
        color: #334155;
    }

    .modal-btn-cancel {
        background-color: #f8fafc;
        color: #334155;
        font-weight: 700;
        border-radius: 12px;
        padding: 10px 24px;
        border: none;
        font-size: 0.92rem;
        transition: all 0.2s ease;
    }

    .modal-btn-save {
        background-color: #007aff;
        color: #ffffff;
        font-weight: 700;
        border-radius: 12px;
        padding: 10px 28px;
        border: none;
        font-size: 0.92rem;
        box-shadow: 0 4px 14px rgba(0, 122, 255, 0.25);
        transition: all 0.2s ease;
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
                            <h2 class="page-title">ลูกค้า</h2>
                            <p class="page-subtitle">ภาพรวม - ลูกค้า - ปี <?php echo htmlspecialchars($selected_year); ?></p>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn-excel-action" onclick="alert('Import Excel')">
                                <i class="ri-file-upload-line"></i>
                                <span>Import Excel</span>
                            </button>
                            <button type="button" class="btn-excel-action" onclick="alert('ส่งออก Excel')">
                                <i class="ri-upload-2-line"></i>
                                <span>ส่งออก Excel</span>
                            </button>
                            <button type="button" class="btn-add-customer" onclick="modal_add_customer()">
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
                                <span class="stat-label">ลูกค้าทั้งหมด</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon green">
                                <i class="ri-wallet-3-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val">0</span>
                                <span class="stat-label">ใช้บริการอยู่</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon purple">
                                <i class="ri-subtract-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val">0</span>
                                <span class="stat-label">เลิกจ้าง</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon yellow">
                                <i class="ri-money-dollar-circle-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val">0</span>
                                <span class="stat-label">ค่าบัญชีต่อเดือน</span>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Toolbar (ค้นหา & ตัวกรองสถานะ) -->
                    <div class="filter-toolbar">
                        <div class="search-box-wrap">
                            <i class="ri-search-line"></i>
                            <input type="text" class="search-input" placeholder="ค้นหาชื่อลูกค้า ผู้ดูแล ทีม">
                        </div>

                        <div class="filter-group">
                            <select class="filter-select">
                                <option value="">ทุกสถานะ</option>
                                <option value="1">ใช้บริการอยู่</option>
                                <option value="0">เลิกจ้าง</option>
                            </select>

                            <select class="filter-select">
                                <option value="">ทุกผู้ดูแล</option>
                                <option value="เมย์">เมย์</option>
                                <option value="ชมพู่">ชมพู่</option>
                            </select>
                        </div>
                    </div>

                    <!-- Customer Table (ตรงตามภาพ 100%) -->
                    <div class="customer-table-wrap">
                        <table class="customer-table">
                            <thead>
                                <tr>
                                    <th class="text-start" style="width: 25%;">ชื่อลูกค้า</th>
                                    <th class="text-center" style="width: 14%;">สถานะ</th>
                                    <th class="text-center" style="width: 12%;">วันสิ้นรอบ</th>
                                    <th class="text-center" style="width: 15%;">ค่าบัญชี</th>
                                    <th class="text-center" style="width: 12%;">ผู้ดูแล</th>
                                    <th class="text-center" style="width: 10%;">ติดต่อ</th>
                                    <th class="text-center" style="width: 12%;">จัดการ</th>
                                </tr>
                            </thead>
                            <!-- <tbody>
                               
                                <tr>
                                    <td class="text-start">
                                        <div class="customer-name-title">AMLAW</div>
                                        <div class="customer-team-sub">ยังไม่ระบุทีม</div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge-service-active">ใช้บริการอยู่</span>
                                    </td>
                                    <td class="text-center text-muted">-</td>
                                    <td class="text-center">
                                        <span class="fee-amount-text">0 บาท</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="caretaker-text">เมย์</span>
                                    </td>
                                    <td class="text-center text-muted">-</td>
                                    <td class="text-center">
                                        <div class="action-btn-cell">
                                            <button type="button" class="btn-action-edit" title="แก้ไข">
                                                <i class="ri-pencil-line"></i>
                                            </button>
                                            <button type="button" class="btn-action-delete" title="ลบ">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                        
                                
                            </tbody> -->
                        </table>
                    </div>

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

<!-- Modal เพิ่มลูกค้าใหม่ (Header/Footer Fixed, มีแต่ Body ที่ Scroll) -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg modal-dialog-custom">
        <div class="modal-content modal-content-custom">

            <!-- Header (Fixed) -->
            <div class="modal-header modal-header-custom">
                <h5 class="modal-title modal-title-custom" id="addCustomerModalLabel">เพิ่มลูกค้าใหม่</h5>
                <button type="button" class="btn-close modal-close-custom" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Body (Scrollable) -->
            <div class="modal-body modal-body-custom">
                <form id="addCustomerForm">
                    <!-- Hidden Fields -->
                    <input type="hidden" name="fiscal_id" value="<?php echo htmlspecialchars($data['fiscal_id'] ?? ''); ?>">
                    <input type="hidden" name="company_id" value="<?php echo htmlspecialchars($data['active_company_id'] ?? ''); ?>">

                    <!-- Section: ข้อมูลทั่วไป -->
                    <div class="mb-4">
                        <h6 class="modal-section-title">ข้อมูลทั่วไป</h6>
                        
                        <!-- ชื่อบริษัท / กิจการ -->
                        <div class="mb-3">
                            <label class="form-label modal-form-label-required">
                                ชื่อบริษัท / กิจการ <span class="modal-required-mark">*</span>
                            </label>
                            <input type="text" class="form-control modal-form-control" name="customer_name" required placeholder="">
                        </div>

                        <!-- 3 คอลัมน์: เดือนที่เริ่มให้บริการ / เดือนสิ้นสุด / สถานะลูกค้า -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label modal-form-label">เดือนที่เริ่มให้บริการ</label>
                                <select class="form-select modal-form-select" name="service_start_date">
                                    <option value="1" selected>มกราคม</option>
                                    <option value="2">กุมภาพันธ์</option>
                                    <option value="3">มีนาคม</option>
                                    <option value="4">เมษายน</option>
                                    <option value="5">พฤษภาคม</option>
                                    <option value="6">มิถุนายน</option>
                                    <option value="7">กรกฎาคม</option>
                                    <option value="8">สิงหาคม</option>
                                    <option value="9">กันยายน</option>
                                    <option value="10">ตุลาคม</option>
                                    <option value="11">พฤศจิกายน</option>
                                    <option value="12">ธันวาคม</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label modal-form-label">เดือนสิ้นสุดการให้บริการ</label>
                                <select class="form-select modal-form-select" name="service_start_end">
                                    <option value="" selected>ยังให้บริการอยู่</option>
                                    <option value="1">มกราคม</option>
                                    <option value="2">กุมภาพันธ์</option>
                                    <option value="3">มีนาคม</option>
                                    <option value="4">เมษายน</option>
                                    <option value="5">พฤษภาคม</option>
                                    <option value="6">มิถุนายน</option>
                                    <option value="7">กรกฎาคม</option>
                                    <option value="8">สิงหาคม</option>
                                    <option value="9">กันยายน</option>
                                    <option value="10">ตุลาคม</option>
                                    <option value="11">พฤศจิกายน</option>
                                    <option value="12">ธันวาคม</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label modal-form-label">สถานะลูกค้า</label>
                                <select class="form-select modal-form-select" name="active_status">
                                    <option value="1" selected>ใช้บริการอยู่</option>
                                    <option value="0">เลิกจ้าง</option>
                                </select>
                            </div>
                        </div>

                        <!-- 2 คอลัมน์: ผู้ดูแล / ทีม -->
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label modal-form-label">ผู้ดูแล</label>
                                <select class="form-select modal-form-select" name="user_id">
                                    <option value="" selected>ยังไม่ระบุผู้ดูแล</option>
                                    <option value="1">เมย์</option>
                                    <option value="2">ชมพู่</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label modal-form-label">ทีม</label>
                                <input type="text" class="form-control modal-form-control" name="team_name" placeholder="เช่น ทีม A">
                            </div>
                        </div>
                    </div>

                    <!-- เส้นประคั่นส่วน -->
                    <div class="modal-section-divider"></div>

                    <!-- Section: ข้อมูลบัญชี -->
                    <div>
                        <h6 class="modal-section-title">ข้อมูลบัญชี</h6>

                        <!-- แถวที่ 1: ปิดงบประจำปี / วันสิ้นรอบบัญชี / ค่าทำบัญชีต่อเดือน -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label modal-form-label">ปิดงบประจำปี</label>
                                <select class="form-select modal-form-select" name="closing_status">
                                    <option value="0" selected>ปิดงบประจำปี</option>
                                    <option value="1">ไม่ปิดงบ</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label modal-form-label">วันสิ้นรอบบัญชี</label>
                                <div class="modal-input-icon-wrap">
                                    <input type="text" class="form-control modal-form-control modal-input-with-icon" name="fiscal_closing_date" value="31/12/2026" placeholder="31/12/2026">
                                    <i class="ri-calendar-line modal-input-icon modal-input-icon-static"></i>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label modal-form-label">ค่าทำบัญชีต่อเดือน</label>
                                <input type="number" step="0.01" class="form-control modal-form-control" name="accounts_amount" value="2000" placeholder="2000">
                            </div>
                        </div>

                        <!-- แถวที่ 2: จด VAT / มีพนักงาน / ประกันสังคม -->
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label modal-form-label">จด VAT</label>
                                <select class="form-select modal-form-select" name="is_vat">
                                    <option value="0" selected>ไม่จด VAT</option>
                                    <option value="1">จด VAT</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label modal-form-label">มีพนักงาน</label>
                                <select class="form-select modal-form-select" name="is_employees">
                                    <option value="0" selected>ไม่มีพนักงาน</option>
                                    <option value="1">มีพนักงาน</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label modal-form-label">ประกันสังคม</label>
                                <select class="form-select modal-form-select" name="is_social_security">
                                    <option value="0" selected>ไม่มีประกันสังคม</option>
                                    <option value="1">มีประกันสังคม</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- เส้นประคั่นส่วน -->
                    <div class="modal-section-divider"></div>

                    <!-- Section: ข้อมูลติดต่อและเอกสาร -->
                    <div>
                        <h6 class="modal-section-title">ข้อมูลติดต่อและเอกสาร</h6>
                        
                        <!-- แถวที่ 1: เบอร์ติดต่อ / อีเมล / LINE ID -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label modal-form-label">เบอร์ติดต่อ</label>
                                <input type="text" class="form-control modal-form-control" name="contact_tel" placeholder="">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label modal-form-label">อีเมล</label>
                                <input type="email" class="form-control modal-form-control" name="contact_email" placeholder="">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label modal-form-label">LINE ID</label>
                                <input type="text" class="form-control modal-form-control-highlight" name="contact_line_id" placeholder="TBacc">
                            </div>
                        </div>

                        <!-- URL เก็บไฟล์เอกสารลูกค้า -->
                        <div class="mb-3">
                            <label class="form-label modal-form-label">URL เก็บไฟล์เอกสารลูกค้า</label>
                            <input type="text" class="form-control modal-form-control" name="doc_url" placeholder="เช่น https://drive.google.com/...">
                        </div>

                        <!-- LINE Group ID / Token -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label modal-form-label mb-0">LINE Group ID / Token</label>
                                <button type="button" class="btn btn-sm modal-video-btn">
                                    <i class="ri-play-circle-line" style="margin-right: 4px;"></i> ดูวิดีโอสอน
                                </button>
                            </div>
                            <input type="text" class="form-control modal-form-control" name="line_token" placeholder="กรอก LINE Group ID หรือ Token สำหรับส่งข้อความ">
                        </div>
                    </div>

                    <!-- เส้นประคั่นส่วน -->
                    <div class="modal-section-divider"></div>

                    <!-- Section: ระบบราชการ -->
                    <div>
                        <h6 class="modal-section-title">ระบบราชการ</h6>
                        
                        <!-- กรมสรรพากร -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label modal-form-label">กรมสรรพากร - User</label>
                                <input type="text" class="form-control modal-form-control" name="rd_user" placeholder="">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label modal-form-label">กรมสรรพากร - Password</label>
                                <div class="modal-input-icon-wrap">
                                    <input type="password" class="form-control modal-form-control-highlight modal-input-with-icon" name="rd_password" placeholder="••••••••">
                                    <i class="ri-eye-line modal-input-icon modal-input-icon-clickable" onclick="const input = this.previousElementSibling; if(input.type === 'password'){ input.type='text'; this.classList.remove('ri-eye-line'); this.classList.add('ri-eye-off-line'); } else { input.type='password'; this.classList.remove('ri-eye-off-line'); this.classList.add('ri-eye-line'); }"></i>
                                </div>
                            </div>
                        </div>

                        <!-- กรมพัฒน์ -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label modal-form-label">กรมพัฒน์ - User</label>
                                <input type="text" class="form-control modal-form-control" name="dbd_user" placeholder="">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label modal-form-label">กรมพัฒน์ - Password</label>
                                <div class="modal-input-icon-wrap">
                                    <input type="password" class="form-control modal-form-control modal-input-with-icon" name="dbd_password" placeholder="">
                                    <i class="ri-eye-line modal-input-icon modal-input-icon-clickable" onclick="const input = this.previousElementSibling; if(input.type === 'password'){ input.type='text'; this.classList.remove('ri-eye-line'); this.classList.add('ri-eye-off-line'); } else { input.type='password'; this.classList.remove('ri-eye-off-line'); this.classList.add('ri-eye-line'); }"></i>
                                </div>
                            </div>
                        </div>

                        <!-- ประกันสังคม -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label modal-form-label">ประกันสังคม - User</label>
                                <input type="text" class="form-control modal-form-control" name="sso_user" placeholder="">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label modal-form-label">ประกันสังคม - Password</label>
                                <div class="modal-input-icon-wrap">
                                    <input type="password" class="form-control modal-form-control modal-input-with-icon" name="sso_password" placeholder="">
                                    <i class="ri-eye-line modal-input-icon modal-input-icon-clickable" onclick="const input = this.previousElementSibling; if(input.type === 'password'){ input.type='text'; this.classList.remove('ri-eye-line'); this.classList.add('ri-eye-off-line'); } else { input.type='password'; this.classList.remove('ri-eye-off-line'); this.classList.add('ri-eye-line'); }"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- เส้นประคั่นส่วน -->
                    <div class="modal-section-divider"></div>

                    <!-- Section: งานรายเดือนที่ไม่ต้องทำ -->
                    <div>
                        <h6 class="modal-section-title">งานรายเดือนที่ไม่ต้องทำ</h6>
                        
                        <!-- Checkboxes Grid -->
                        <div class="row g-3">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <div class="d-flex flex-column gap-2">
                                    <label class="d-flex align-items-center modal-checkbox-item">
                                        <input type="checkbox" name="monthly_skip[]" value="pnd1" class="form-check-input modal-checkbox-input">
                                        <span class="modal-checkbox-label">ภ.ง.ด.1</span>
                                    </label>
                                    
                                    <label class="d-flex align-items-center modal-checkbox-item">
                                        <input type="checkbox" name="monthly_skip[]" value="pnd53" class="form-check-input modal-checkbox-input">
                                        <span class="modal-checkbox-label">ภ.ง.ด.53</span>
                                    </label>
                                    
                                    <label class="d-flex align-items-center modal-checkbox-item">
                                        <input type="checkbox" name="monthly_skip[]" value="pp30" class="form-check-input modal-checkbox-input">
                                        <span class="modal-checkbox-label">ภ.พ.30</span>
                                    </label>
                                    
                                    <label class="d-flex align-items-center modal-checkbox-item">
                                        <input type="checkbox" name="monthly_skip[]" value="sso" class="form-check-input modal-checkbox-input">
                                        <span class="modal-checkbox-label">ประกันสังคม</span>
                                    </label>
                                    
                                    <label class="d-flex align-items-center modal-checkbox-item">
                                        <input type="checkbox" name="monthly_skip[]" value="bbl" class="form-check-input modal-checkbox-input">
                                        <span class="modal-checkbox-label">BBL</span>
                                    </label>
                                    
                                    <label class="d-flex align-items-center modal-checkbox-item">
                                        <input type="checkbox" name="monthly_skip[]" value="uob" class="form-check-input modal-checkbox-input">
                                        <span class="modal-checkbox-label">UOB</span>
                                    </label>
                                    
                                    <label class="d-flex align-items-center modal-checkbox-item">
                                        <input type="checkbox" name="monthly_skip[]" value="scb" class="form-check-input modal-checkbox-input">
                                        <span class="modal-checkbox-label">SCB</span>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Right Column -->
                            <div class="col-md-6">
                                <div class="d-flex flex-column gap-2">
                                    <label class="d-flex align-items-center modal-checkbox-item">
                                        <input type="checkbox" name="monthly_skip[]" value="pnd3" class="form-check-input modal-checkbox-input">
                                        <span class="modal-checkbox-label">ภ.ง.ด.3</span>
                                    </label>
                                    
                                    <label class="d-flex align-items-center modal-checkbox-item">
                                        <input type="checkbox" name="monthly_skip[]" value="pnd54" class="form-check-input modal-checkbox-input">
                                        <span class="modal-checkbox-label">ภ.ง.ด.54</span>
                                    </label>
                                    
                                    <label class="d-flex align-items-center modal-checkbox-item">
                                        <input type="checkbox" name="monthly_skip[]" value="pp36" class="form-check-input modal-checkbox-input">
                                        <span class="modal-checkbox-label">ภ.พ.36</span>
                                    </label>
                                    
                                    <label class="d-flex align-items-center modal-checkbox-item">
                                        <input type="checkbox" name="monthly_skip[]" value="kor_yor_sor" class="form-check-input modal-checkbox-input">
                                        <span class="modal-checkbox-label">ก.ย.ศ.</span>
                                    </label>
                                    
                                    <label class="d-flex align-items-center modal-checkbox-item">
                                        <input type="checkbox" name="monthly_skip[]" value="kbank" class="form-check-input modal-checkbox-input">
                                        <span class="modal-checkbox-label">KBANK</span>
                                    </label>
                                    
                                    <label class="d-flex align-items-center modal-checkbox-item">
                                        <input type="checkbox" name="monthly_skip[]" value="ttb" class="form-check-input modal-checkbox-input">
                                        <span class="modal-checkbox-label">TTB</span>
                                    </label>
                                    
                                    <label class="d-flex align-items-center modal-checkbox-item">
                                        <input type="checkbox" name="monthly_skip[]" value="reconcile_bank" class="form-check-input modal-checkbox-input">
                                        <span class="modal-checkbox-label">กระทบ Bank</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Footer (Fixed) -->
            <div class="modal-footer modal-footer-custom">
                <button type="button" class="btn modal-btn-cancel" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn modal-btn-save" onclick="submitAddCustomer()">บันทึกข้อมูล</button>
            </div>
        </div>
    </div>
</div>

<script>

    function modal_add_customer() {
        // 1. เคลียร์ข้อมูลในฟอร์มเก่าทิ้ง (ถ้ามี)
        const form = document.getElementById('addCustomerForm');
        if(form) {
            form.reset();
        }
        // 2. สั่งโชว์ Modal ผ่าน Vanilla JS ของ Bootstrap
        const modalElement = document.getElementById('addCustomerModal');
        const myModal = new bootstrap.Modal(modalElement);
        myModal.show();
    }   

  
</script>

<?php 
// 3. นำ Footer เข้ามา
require_once dirname(__DIR__) . '/main/footer.php'; 
?>