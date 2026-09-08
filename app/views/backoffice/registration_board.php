<?php
// app/views/backoffice/registration_board.php
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

    /* --- Header Action Buttons row: space between each button --- */
    .page-header-actions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    /* --- Header Action Buttons (Bootstrap Outline Buttons) --- */
    .page-header-box .btn {
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 9px 16px;
        font-size: 0.80rem;
        font-weight: 600;
        white-space: nowrap;
        line-height: 1.2;
        transition: all 0.2s ease;
    }

    .page-header-box .btn i {
        color: inherit !important;
        font-size: 15px;
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
        padding: 9px 18px;
        font-size: 0.80rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        white-space: nowrap;
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
        white-space: nowrap;
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
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 16px;
        pointer-events: none;
        z-index: 1;
    }

    .search-input {
        width: 100%;
        box-sizing: border-box;
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 11px 14px 11px 40px;
        font-size: 0.85rem;
        line-height: 1.6;
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

    /* --- Search & Select (ใช้ใน Modal ประวัติงาน) --- */
    .history-search-wrap {
        position: relative;
        width: 100%;
    }

    .history-search-wrap i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 16px;
        pointer-events: none;
        z-index: 1;
    }

    .history-search-input {
        width: 100%;
        box-sizing: border-box;
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 11px 14px 11px 40px;
        font-size: 0.85rem;
        line-height: 1.6;
        font-weight: 600;
        color: #1e293b;
        font-family: inherit;
        outline: none;
        transition: all 0.2s ease;
    }

    .history-search-input:focus {
        background-color: #ffffff;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .history-search-input::placeholder {
        color: #94a3b8;
        font-weight: 500;
    }

    .history-select {
        width: 100%;
        box-sizing: border-box;
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 11px 36px 11px 14px;
        font-size: 0.85rem;
        line-height: 1.6;
        font-weight: 600;
        color: #1e293b;
        cursor: pointer;
        outline: none;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' width='16' height='16' fill='%2364748b'%3E%3Cpath d='M11.9997 13.1716L16.9495 8.22168L18.3637 9.63589L11.9997 16L5.63574 9.63589L7.04996 8.22168L11.9997 13.1716Z'%3E%3C/path%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        transition: all 0.2s ease;
    }

    .history-select:focus {
        background-color: #ffffff;
        border-color: #3b82f6;
    }

    /* --- Modal ตั้งค่าประเภทงานทะเบียน --- */
    .job-type-add-row {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 18px;
    }

    .job-type-input {
        flex: 1;
        min-width: 0;
        box-sizing: border-box;
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 11px 14px;
        font-size: 0.85rem;
        line-height: 1.6;
        font-weight: 600;
        color: #1e293b;
        font-family: inherit;
        outline: none;
        transition: all 0.2s ease;
    }

    .job-type-input:focus {
        background-color: #ffffff;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .job-type-input::placeholder {
        color: #94a3b8;
        font-weight: 500;
    }

    .btn-add-type {
        background-color: #007aff;
        color: #ffffff;
        border: none;
        border-radius: 10px;
        padding: 11px 18px;
        font-size: 0.82rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 4px 12px rgba(0, 122, 255, 0.25);
    }

    .btn-add-type:hover {
        background-color: #0062cc;
        box-shadow: 0 6px 16px rgba(0, 122, 255, 0.35);
    }

    .btn-add-type i {
        font-size: 15px;
    }

    .job-type-list {
        display: flex;
        flex-direction: column;
        max-height: 320px;
        overflow-y: auto;
    }

    .job-type-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 12px 2px;
        border-bottom: 1px dashed #f1f5f9;
    }

    .job-type-row:last-child {
        border-bottom: none;
    }

    .job-type-name {
        font-size: 0.85rem;
        font-weight: 600;
        color: #1e293b;
        word-break: break-word;
    }

    .job-type-actions {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-shrink: 0;
    }

    .job-type-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: none;
        border-radius: 20px;
        padding: 5px 12px;
        font-size: 0.72rem;
        font-weight: 700;
        font-family: inherit;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .job-type-status-badge .job-type-status-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .job-type-status-badge.is-active {
        background-color: #ecfdf5;
        color: #10b981;
    }

    .job-type-status-badge.is-active .job-type-status-dot {
        background-color: #10b981;
    }

    .job-type-status-badge.is-active:hover {
        background-color: #d1fae5;
    }

    .job-type-status-badge.is-inactive {
        background-color: #f1f5f9;
        color: #94a3b8;
    }

    .job-type-status-badge.is-inactive .job-type-status-dot {
        background-color: #94a3b8;
    }

    .job-type-status-badge.is-inactive:hover {
        background-color: #e2e8f0;
    }

    .job-type-status-badge:disabled {
        opacity: 0.6;
        cursor: default;
    }

    .job-type-empty {
        text-align: center;
        color: #94a3b8;
        font-size: 0.85rem;
        padding: 40px 14px;
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

    /* Action Buttons (Edit & Delete — ใช้ในรายการประเภทงาน) */
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

    /* --- Board Swimlanes (สถานะงานทะเบียน) --- */
    .board-swimlane-card {
        background-color: #ffffff;
        border-radius: 16px;
        border: 1px solid #edf2f7;
        padding: 24px;
    }

    .board-swimlane-title {
        color: #1e293b;
    }

    .board-add-btn {
        width: 36px;
        height: 36px;
        background-color: #eff6ff;
        color: #007aff;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        padding: 0;
    }

    .board-empty-state {
        border: 1.5px dashed #e2e8f0;
        border-radius: 12px;
        padding: 36px 20px;
        text-align: center;
        color: #94a3b8;
        background-color: #fafbfc;
    }

    .board-cards-container {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        min-height: 20px;
    }

    .board-cards-container .board-empty-state {
        width: 100%;
    }

    /* --- Drag & Drop (HTML5Sortable) --- */
    .register-task-card {
        cursor: grab;
    }

    .register-task-card.sortable-dragging {
        opacity: 0.4;
    }

    .board-status-lane.sortable-placeholder-active {
        background-color: #eff6ff;
        border-radius: 12px;
    }

    /* --- Task Card --- */
    .register-task-card {
        background-color: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 16px;
        padding: 18px 20px;
        width: 100%;
        max-width: 330px;
        box-shadow: 0 4px 12px rgba(16, 24, 40, 0.03);
    }

    .task-card-title {
        line-height: 1.2;
    }

    .btn-close-job {
        background-color: #fff1f2;
        color: #e11d48;
        border: none;
        border-radius: 8px;
        padding: 4px 10px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .btn-card-more {
        width: 28px;
        height: 28px;
        padding: 0;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        border-color: #e2e8f0;
    }

    .board-badge {
        border-radius: 6px;
        padding: 3px 10px;
        display: inline-block;
    }

    .board-badge-type {
        background-color: #f1f5f9;
        color: #334155;
    }

    .board-badge-overdue {
        background-color: #fff1f2;
        color: #ef4444;
    }

    .board-badge-ontime {
        background-color: #dcfce7;
        color: #16a34a;
    }

    /* --- Modal ตั้งค่างานทะเบียน --- */
    .settings-field-group {
        margin-bottom: 4px;
    }

    .settings-field-label {
        display: block;
        font-size: 0.85rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 8px;
    }

    .settings-hint {
        font-size: 0.75rem;
        color: #94a3b8;
        font-weight: 500;
    }

    .urgency-row {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
    }

    .urgency-code {
        width: 100px;
        flex-shrink: 0;
        font-size: 0.70rem;
        font-weight: 800;
        color: #94a3b8;
        letter-spacing: 0.3px;
    }

    .urgency-color-input {
        width: 60px;
        height: 42px;
        flex-shrink: 0;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 3px;
        cursor: pointer;
        background-color: #f8fafc;
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
                            <?php $fy_display = !empty($data['active_fiscal_year']) ? $data['active_fiscal_year'] : 'ไม่ได้เลือกปี'; ?>
                            <p class="page-subtitle">ภาพรวมระบบ - จัดการงานทะเบียน - ปี <?php echo htmlspecialchars($fy_display); ?></p>
                        </div>
                        <div class="page-header-actions">
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#historyTaskModal">
                                <i class="ri-history-line"></i>
                                <span>ประวัติงานที่เสร็จแล้ว</span>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#jobTypeSettingsModal">
                                <i class="ri-settings-2-line"></i>
                                <span>ตั้งค่าประเภทงาน</span>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#registrationTaskSettingsModal">
                                <i class="ri-upload-2-line"></i>
                                <span>ตั้งค่างานทะเบียน</span>
                            </button>
                            <button type="button" class="btn-add-customer" data-bs-toggle="modal" data-bs-target="#addRegistrationTaskModal">
                                <i class="ri-add-line"></i>
                                <span>เพิ่มงานทะเบียน</span>
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
                                <span class="stat-val"><?php echo $data['stat_open']; ?></span>
                                <span class="stat-label">งานทะเบียนที่เปิดอยู่</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon green">
                                <i class="ri-wallet-3-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo $data['stat_not_overdue']; ?></span>
                                <span class="stat-label">ยังไม่เลยกำหนด</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon purple">
                                <i class="ri-subtract-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo $data['stat_overdue']; ?></span>
                                <span class="stat-label">เลยกำหนดส่งงาน</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon yellow">
                                <i class="ri-money-dollar-circle-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo $data['stat_closed_this_month']['total']; ?></span>
                                <span class="stat-label">ปิดงานเดือนนี้ · <?php echo number_format($data['stat_closed_this_month']['total_amount'], 2); ?></span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon yellow">
                                <i class="ri-money-dollar-circle-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo $data['stat_closed_last_month']['total']; ?></span>
                                <span class="stat-label">ปิดงานเดือนก่อน · <?php echo number_format($data['stat_closed_last_month']['total_amount'], 2); ?></span>
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

                    <?php
                        // ป้ายชื่อคอลัมน์ตาม status ตรงกับ comment ในตาราง tbl_registration เป๊ะๆ
                        $registrationStatusLabels = [
                            '0' => 'รับงานทะเบียน',
                            '1' => 'กำลังทำ',
                            '2' => 'รอตรวจสอบ',
                            '3' => 'ตรวจสอบแล้ว',
                            '4' => 'กำลังไปยื่น',
                            '5' => 'งานเสร็จเรียบร้อยแล้ว',
                            '6' => 'เก็บเงินเรียบร้อยแล้ว',
                        ];
                    ?>

                    <!-- Board Swimlanes Wrapper -->
                    <div class="d-flex flex-column gap-4">
                        <?php foreach ($registrationStatusLabels as $statusCode => $statusLabel): ?>
                            <?php $tasksInLane = $data['tasks_by_status'][$statusCode] ?? []; ?>
                            <div class="board-swimlane-card">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h5 class="mb-0 board-swimlane-title"><?php echo htmlspecialchars($statusLabel); ?></h5>
                                        <small class="text-muted board-lane-count"><?php echo count($tasksInLane); ?> งาน</small>
                                    </div>
                                    <?php if ($statusCode === '0'): ?>
                                        <button type="button" class="btn board-add-btn" data-bs-toggle="modal" data-bs-target="#addRegistrationTaskModal">
                                            <i class="ri-add-line fs-5"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>

                                <!-- คอนเทนเนอร์นี้ต้องอยู่ในหน้าเสมอ (แม้ไม่มีงาน) เพราะเป็นจุดวางการ์ดตอนลาก -->
                                <div class="board-cards-container board-status-lane" data-status="<?php echo htmlspecialchars($statusCode); ?>">
                                    <?php if (empty($tasksInLane)): ?>
                                        <div class="board-empty-state board-lane-empty-msg">
                                            ยังไม่มีงานในสถานะนี้
                                        </div>
                                    <?php endif; ?>
                                    <?php foreach ($tasksInLane as $task): ?>
                                        <?php include 'table/registration_task_card.php'; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
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

<!-- Modal ประวัติงานทะเบียนที่ปิดแล้ว (เทมเพลตเดียวกับ Modal เพิ่มลูกค้าใหม่) -->
<div class="modal fade" id="historyTaskModal" tabindex="-1" aria-labelledby="historyTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 960px;">
        <div class="modal-content" style="border: none; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.08); background-color: #ffffff;">

            <!-- Header -->
            <div class="modal-header" style="border-bottom: none; padding: 24px 28px 12px 28px; align-items: flex-start;">
                <div>
                    <h5 class="modal-title" id="historyTaskModalLabel" style="font-weight: 800; color: #1e293b; font-size: 1.25rem; margin-bottom: 4px;">
                        ประวัติงานทะเบียนที่ปิดแล้ว
                    </h5>
                    <p style="font-size: 0.82rem; color: #94a3b8; font-weight: 500; margin: 0;">
                        ทั้งหมด <span id="historyTotalCount">0</span> งาน
                        &middot;
                        ค่าบริการรวม <span id="historyTotalAmount">0.00</span>
                    </p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.9rem; opacity: 0.4;"></button>
            </div>

            <!-- Body -->
            <div class="modal-body" style="padding: 12px 28px 24px 28px;">

                <!-- Toolbar: ค้นหา + จำนวนต่อหน้า -->
                <div class="row g-3 mb-3">
                    <div class="col-md-8">
                        <div class="history-search-wrap">
                            <i class="ri-search-line"></i>
                            <input type="text" id="historySearchInput" class="history-search-input" placeholder="ค้นหาลูกค้า งาน ผู้รับผิดชอบ">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select id="historyPerPage" class="history-select">
                            <option value="25" selected>25 รายการ</option>
                            <option value="50">50 รายการ</option>
                            <option value="100">100 รายการ</option>
                        </select>
                    </div>
                </div>

                <!-- ตารางประวัติ -->
                <div style="overflow-x: auto;">
                    <table class="table" style="width: 100%; border-collapse: separate; border-spacing: 0; margin-bottom: 0;">
                        <thead>
                            <tr>
                                <th style="text-align: left; font-size: 0.75rem; font-weight: 700; color: #94a3b8; padding: 10px 14px; border-bottom: 1px solid #f1f5f9; white-space: nowrap;">วันที่ปิด</th>
                                <th style="text-align: left; font-size: 0.75rem; font-weight: 700; color: #94a3b8; padding: 10px 14px; border-bottom: 1px solid #f1f5f9; white-space: nowrap;">ลูกค้า</th>
                                <th style="text-align: left; font-size: 0.75rem; font-weight: 700; color: #94a3b8; padding: 10px 14px; border-bottom: 1px solid #f1f5f9; white-space: nowrap;">งานทะเบียน</th>
                                <th style="text-align: left; font-size: 0.75rem; font-weight: 700; color: #94a3b8; padding: 10px 14px; border-bottom: 1px solid #f1f5f9; white-space: nowrap;">ประเภทงาน</th>
                                <th style="text-align: left; font-size: 0.75rem; font-weight: 700; color: #94a3b8; padding: 10px 14px; border-bottom: 1px solid #f1f5f9; white-space: nowrap;">ผู้รับผิดชอบ</th>
                                <th style="text-align: right; font-size: 0.75rem; font-weight: 700; color: #94a3b8; padding: 10px 14px; border-bottom: 1px solid #f1f5f9; white-space: nowrap;">ค่าบริการ</th>
                            </tr>
                        </thead>
                        <tbody id="historyTaskTableBody">
                            <tr>
                                <td colspan="6" style="text-align: center; color: #94a3b8; font-size: 0.85rem; padding: 48px 14px; border-bottom: none;">
                                    ยังไม่มีประวัติงานที่ปิดแล้ว
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="padding-top: 16px;">
                    <div style="font-size: 0.78rem; color: #64748b; font-weight: 500;" id="historyPaginationInfo">
                        แสดง 0-0 จาก 0 รายการ
                    </div>
                    <div class="d-flex align-items-center gap-1" id="historyPaginationNav">
                        <button type="button" class="btn" id="historyPrevBtn" disabled onclick="loadHistoryTasks(historyCurrentPage - 1)"
                            style="background-color: #f1f5f9; color: #94a3b8; border: none; border-radius: 8px; padding: 6px 16px; font-size: 0.78rem; font-weight: 700;">
                            ก่อนหน้า
                        </button>
                        <span id="historyPageNumbers" class="d-flex align-items-center gap-1"></span>
                        <button type="button" class="btn" id="historyNextBtn" disabled onclick="loadHistoryTasks(historyCurrentPage + 1)"
                            style="background-color: #f1f5f9; color: #94a3b8; border: none; border-radius: 8px; padding: 6px 16px; font-size: 0.78rem; font-weight: 700;">
                            ถัดไป
                        </button>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="modal-footer" style="border-top: none; padding: 12px 28px 28px 28px; justify-content: flex-end;">
                <button type="button" class="btn" data-bs-dismiss="modal"
                    style="background-color: #f8fafc; color: #334155; font-weight: 700; border-radius: 12px; padding: 10px 24px; border: none; font-size: 0.92rem; transition: all 0.2s ease;">
                    ปิด
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal ตั้งค่าประเภทงานทะเบียน -->
<div class="modal fade" id="jobTypeSettingsModal" tabindex="-1" aria-labelledby="jobTypeSettingsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
        <div class="modal-content" style="border: none; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.08); background-color: #ffffff;">

            <!-- Header -->
            <div class="modal-header" style="border-bottom: none; padding: 24px 28px 12px 28px; align-items: flex-start;">
                <div>
                    <h5 class="modal-title" id="jobTypeSettingsModalLabel" style="font-weight: 800; color: #1e293b; font-size: 1.25rem; margin-bottom: 4px;">
                        ตั้งค่าประเภทงานทะเบียน
                    </h5>
                    <p style="font-size: 0.82rem; color: #94a3b8; font-weight: 500; margin: 0;">
                        จัดการหมวดหมู่ประเภทงานที่ใช้เลือกตอนสร้างงานทะเบียน
                    </p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.9rem; opacity: 0.4;"></button>
            </div>

            <!-- Body -->
            <div class="modal-body" style="padding: 12px 28px 24px 28px;">

                <!-- แถวเพิ่มประเภทงานใหม่ -->
                <div class="job-type-add-row">
                    <input type="text" id="jobTypeNameInput" class="job-type-input" placeholder="เช่น จดทะเบียนประกันสังคม" maxlength="150">
                    <button type="button" class="btn-add-type" onclick="addJobType()">
                        <i class="ri-add-line"></i>
                        <span>เพิ่ม</span>
                    </button>
                </div>

                <!-- รายการประเภทงาน -->
                <div class="job-type-list" id="jobTypeList">
                    <div class="job-type-empty">กำลังโหลดข้อมูล...</div>
                </div>

            </div>

            <!-- Footer -->
            <div class="modal-footer" style="border-top: none; padding: 12px 28px 28px 28px; justify-content: flex-end;">
                <button type="button" class="btn" data-bs-dismiss="modal"
                    style="background-color: #f8fafc; color: #334155; font-weight: 700; border-radius: 12px; padding: 10px 24px; border: none; font-size: 0.92rem; transition: all 0.2s ease;">
                    ปิด
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal ตั้งค่างานทะเบียน -->
<div class="modal fade" id="registrationTaskSettingsModal" tabindex="-1" aria-labelledby="registrationTaskSettingsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
        <div class="modal-content" style="border: none; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.08); background-color: #ffffff;">

            <!-- Header -->
            <div class="modal-header" style="border-bottom: none; padding: 24px 28px 12px 28px; align-items: flex-start;">
                <h5 class="modal-title" id="registrationTaskSettingsModalLabel" style="font-weight: 800; color: #1e293b; font-size: 1.25rem;">
                    ตั้งค่างานทะเบียน
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.9rem; opacity: 0.4;"></button>
            </div>

            <!-- Body -->
            <div class="modal-body" style="padding: 12px 28px 24px 28px;">

                <div class="settings-field-group">
                    <label class="settings-field-label">แจ้งเตือนงานใกล้ครบกำหนดภายในกี่วัน</label>
                    <input type="number" min="1" id="settingsNotifyDay" class="job-type-input" style="width: 100%;">
                    <small class="settings-hint" id="settingsNotifyDayHint"></small>
                </div>

                <div class="settings-field-label" style="margin-top: 20px; margin-bottom: 12px;">ความเร่งด่วน</div>

                <div class="urgency-row">
                    <span class="urgency-code">NORMAL</span>
                    <input type="text" id="settingsNormalLabel" class="job-type-input">
                    <input type="color" id="settingsNormalColor" class="urgency-color-input">
                </div>
                <div class="urgency-row">
                    <span class="urgency-code">URGENT</span>
                    <input type="text" id="settingsUrgentLabel" class="job-type-input">
                    <input type="color" id="settingsUrgentColor" class="urgency-color-input">
                </div>
                <div class="urgency-row">
                    <span class="urgency-code">VERY_URGENT</span>
                    <input type="text" id="settingsVeryUrgentLabel" class="job-type-input">
                    <input type="color" id="settingsVeryUrgentColor" class="urgency-color-input">
                </div>

                <small class="settings-hint" style="display: block; margin-top: 12px;">สีนี้จะถูกใช้เป็นสีขอบของการ์ดงานทะเบียนตามระดับความเร่งด่วน</small>
            </div>

            <!-- Footer -->
            <div class="modal-footer" style="border-top: none; padding: 12px 28px 28px 28px; justify-content: flex-end;">
                <button type="button" class="btn" data-bs-dismiss="modal"
                    style="background-color: #f8fafc; color: #334155; font-weight: 700; border-radius: 12px; padding: 10px 24px; border: none; font-size: 0.92rem; transition: all 0.2s ease;">
                    ยกเลิก
                </button>
                <button type="button" class="btn" onclick="saveRegistrationTaskSettings()"
                    style="background-color: #007aff; color: #ffffff; font-weight: 700; border-radius: 12px; padding: 10px 24px; border: none; font-size: 0.92rem; box-shadow: 0 4px 14px rgba(0,122,255,0.25); transition: all 0.2s ease;">
                    บันทึกตั้งค่า
                </button>
            </div>
        </div>
    </div>
</div>


<!-- Modal เพิ่มงานทะเบียน -->
<div class="modal fade" id="addRegistrationTaskModal" tabindex="-1" aria-labelledby="addRegistrationTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 640px;">
        <div class="modal-content" style="border: none; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.08); background-color: #ffffff;">

            <!-- Header -->
            <div class="modal-header" style="border-bottom: none; padding: 24px 28px 12px 28px; align-items: flex-start;">
                <h5 class="modal-title" id="addRegistrationTaskModalLabel" style="font-weight: 800; color: #1e293b; font-size: 1.25rem;">
                    เพิ่มงานทะเบียนใหม่
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.9rem; opacity: 0.4;"></button>
            </div>

            <!-- Body -->
            <div class="modal-body" style="padding: 12px 28px 24px 28px;">
                <form id="addRegistrationTaskForm">
                    <h6 style="font-weight: 800; color: #1e293b; font-size: 1.05rem; margin-bottom: 16px;">ข้อมูลลูกค้า</h6>

                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                            ชื่อลูกค้า / บริษัท <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="text" class="form-control" name="customer_name" id="regCustomerName" placeholder="" style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; font-weight: 600; color: #1e293b; font-size: 0.92rem; outline: none; box-shadow: none;">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                                เบอร์ติดต่อ <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" class="form-control" name="customer_phone" id="regCustomerPhone" maxlength="10" placeholder="" style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; font-weight: 600; color: #1e293b; font-size: 0.92rem; outline: none; box-shadow: none;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                                ผู้ติดต่อ <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" class="form-control" name="contact_person" id="regContactPerson" placeholder="" style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; font-weight: 600; color: #1e293b; font-size: 0.92rem; outline: none; box-shadow: none;">
                        </div>
                    </div>

                    <h6 style="font-weight: 800; color: #1e293b; font-size: 1.05rem; margin-bottom: 16px; margin-top: 8px;">ข้อมูลงานทะเบียน</h6>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                                ประเภทงาน <span style="color: #ef4444;">*</span>
                            </label>
                            <select class="form-select" name="registration_type_id" id="regTypeId" style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; font-weight: 600; color: #1e293b; font-size: 0.92rem; outline: none; box-shadow: none;">
                                <option value="">กำลังโหลด...</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                                ชื่องานทะเบียน <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" class="form-control" name="registration_name" id="regName" placeholder="เช่น จดทะเบียนพาณิชย์" style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; font-weight: 600; color: #1e293b; font-size: 0.92rem; outline: none; box-shadow: none;">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                            รายละเอียดงาน <span style="color: #ef4444;">*</span>
                        </label>
                        <textarea class="form-control" name="description" id="regDescription" rows="2" style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; font-weight: 600; color: #1e293b; font-size: 0.92rem; outline: none; box-shadow: none;"></textarea>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                                ค่าบริการ
                            </label>
                            <input type="number" step="0.01" min="0" class="form-control" name="service_amount" id="regServiceAmount" value="0" style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; font-weight: 600; color: #1e293b; font-size: 0.92rem; outline: none; box-shadow: none;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                                ความเร่งด่วน
                            </label>
                            <select class="form-select" name="urgency_level" id="regUrgencyLevel" style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; font-weight: 600; color: #1e293b; font-size: 0.92rem; outline: none; box-shadow: none;">
                                <option value="">กำลังโหลด...</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                                ผู้รับผิดชอบ <span style="color: #ef4444;">*</span>
                            </label>
                            <select class="form-select" name="assignee_user_id" id="regAssignee" style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; font-weight: 600; color: #1e293b; font-size: 0.92rem; outline: none; box-shadow: none;">
                                <option value="">เลือกพนักงาน</option>
                                <?php foreach (($data['employees'] ?? []) as $emp): ?>
                                    <option value="<?php echo htmlspecialchars($emp['user_id']); ?>">
                                        <?php echo htmlspecialchars($emp['user_firstname'] . ' ' . $emp['user_lastname']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                                ผู้ตรวจสอบงาน
                            </label>
                            <select class="form-select" name="review_user_id" id="regReviewer" style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; font-weight: 600; color: #1e293b; font-size: 0.92rem; outline: none; box-shadow: none;">
                                <option value="">ไม่ระบุ</option>
                                <?php foreach (($data['employees'] ?? []) as $emp): ?>
                                    <option value="<?php echo htmlspecialchars($emp['user_id']); ?>">
                                        <?php echo htmlspecialchars($emp['user_firstname'] . ' ' . $emp['user_lastname']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                            รหัสงานทะเบียน
                        </label>
                        <input type="text" class="form-control" id="regNo" placeholder="เช่น REG-001" disabled
                            style="background-color: #f1f5f9; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; font-weight: 600; color: #94a3b8; font-size: 0.92rem; outline: none; box-shadow: none;">
                        <small class="settings-hint" style="display: block; margin-top: 6px;">ระบบจะสร้างรหัสนี้ให้อัตโนมัติตอนบันทึก</small>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                                วันที่รับงาน
                            </label>
                            <input type="date" class="form-control" name="accep_date" id="regAccepDate" style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; font-weight: 600; color: #1e293b; font-size: 0.92rem; outline: none; box-shadow: none;"
                                value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                                กำหนดส่ง
                            </label>
                            <input type="date" class="form-control" name="due_date" id="regDueDate" style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; font-weight: 600; color: #1e293b; font-size: 0.92rem; outline: none; box-shadow: none;">
                        </div>
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="modal-footer" style="border-top: none; padding: 12px 28px 28px 28px; justify-content: flex-end;">
                <button type="button" class="btn" data-bs-dismiss="modal"
                    style="background-color: #f8fafc; color: #334155; font-weight: 700; border-radius: 12px; padding: 10px 24px; border: none; font-size: 0.92rem; transition: all 0.2s ease;">
                    ยกเลิก
                </button>
                <button type="button" class="btn" onclick="saveRegistrationTask()"
                    style="background-color: #007aff; color: #ffffff; font-weight: 700; border-radius: 12px; padding: 10px 24px; border: none; font-size: 0.92rem; box-shadow: 0 4px 14px rgba(0,122,255,0.25); transition: all 0.2s ease;">
                    บันทึกงานทะเบียน
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal แก้ไขงานทะเบียน (โครงเดียวกับ Modal เพิ่ม เปลี่ยนแค่ id เป็น edit_reg_*) -->
<div class="modal fade" id="editRegistrationTaskModal" tabindex="-1" aria-labelledby="editRegistrationTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 640px;">
        <div class="modal-content" style="border: none; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.08); background-color: #ffffff;">

            <!-- Header -->
            <div class="modal-header" style="border-bottom: none; padding: 24px 28px 12px 28px; align-items: flex-start;">
                <h5 class="modal-title" id="editRegistrationTaskModalLabel" style="font-weight: 800; color: #1e293b; font-size: 1.25rem;">
                    แก้ไขงานทะเบียน
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.9rem; opacity: 0.4;"></button>
            </div>

            <!-- Body -->
            <div class="modal-body" style="padding: 12px 28px 24px 28px;">
                <form id="editRegistrationTaskForm">
                    <input type="hidden" id="edit_reg_id">
                    <h6 style="font-weight: 800; color: #1e293b; font-size: 1.05rem; margin-bottom: 16px;">ข้อมูลลูกค้า</h6>

                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                            ชื่อลูกค้า / บริษัท <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="text" class="form-control" id="edit_reg_customer_name" placeholder="" style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; font-weight: 600; color: #1e293b; font-size: 0.92rem; outline: none; box-shadow: none;">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                                เบอร์ติดต่อ <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" class="form-control" id="edit_reg_customer_phone" maxlength="10" placeholder="" style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; font-weight: 600; color: #1e293b; font-size: 0.92rem; outline: none; box-shadow: none;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                                ผู้ติดต่อ <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" class="form-control" id="edit_reg_contact_person" placeholder="" style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; font-weight: 600; color: #1e293b; font-size: 0.92rem; outline: none; box-shadow: none;">
                        </div>
                    </div>

                    <h6 style="font-weight: 800; color: #1e293b; font-size: 1.05rem; margin-bottom: 16px; margin-top: 8px;">ข้อมูลงานทะเบียน</h6>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                                ประเภทงาน <span style="color: #ef4444;">*</span>
                            </label>
                            <select class="form-select" id="edit_reg_type_id" style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; font-weight: 600; color: #1e293b; font-size: 0.92rem; outline: none; box-shadow: none;">
                                <option value="">กำลังโหลด...</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                                ชื่องานทะเบียน <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" class="form-control" id="edit_reg_name" placeholder="เช่น จดทะเบียนพาณิชย์" style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; font-weight: 600; color: #1e293b; font-size: 0.92rem; outline: none; box-shadow: none;">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                            รายละเอียดงาน <span style="color: #ef4444;">*</span>
                        </label>
                        <textarea class="form-control" id="edit_reg_description" rows="2" style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; font-weight: 600; color: #1e293b; font-size: 0.92rem; outline: none; box-shadow: none;"></textarea>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                                ค่าบริการ
                            </label>
                            <input type="number" step="0.01" min="0" class="form-control" id="edit_reg_service_amount" style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; font-weight: 600; color: #1e293b; font-size: 0.92rem; outline: none; box-shadow: none;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                                ความเร่งด่วน
                            </label>
                            <select class="form-select" id="edit_reg_urgency_level" style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; font-weight: 600; color: #1e293b; font-size: 0.92rem; outline: none; box-shadow: none;">
                                <option value="">กำลังโหลด...</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                                ผู้รับผิดชอบ <span style="color: #ef4444;">*</span>
                            </label>
                            <select class="form-select" id="edit_reg_assignee" style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; font-weight: 600; color: #1e293b; font-size: 0.92rem; outline: none; box-shadow: none;">
                                <option value="">เลือกพนักงาน</option>
                                <?php foreach (($data['employees'] ?? []) as $emp): ?>
                                    <option value="<?php echo htmlspecialchars($emp['user_id']); ?>">
                                        <?php echo htmlspecialchars($emp['user_firstname'] . ' ' . $emp['user_lastname']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                                ผู้ตรวจสอบงาน
                            </label>
                            <select class="form-select" id="edit_reg_reviewer" style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; font-weight: 600; color: #1e293b; font-size: 0.92rem; outline: none; box-shadow: none;">
                                <option value="">ไม่ระบุ</option>
                                <?php foreach (($data['employees'] ?? []) as $emp): ?>
                                    <option value="<?php echo htmlspecialchars($emp['user_id']); ?>">
                                        <?php echo htmlspecialchars($emp['user_firstname'] . ' ' . $emp['user_lastname']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                            รหัสงานทะเบียน
                        </label>
                        <input type="text" class="form-control" id="edit_reg_no" disabled
                            style="background-color: #f1f5f9; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; font-weight: 600; color: #94a3b8; font-size: 0.92rem; outline: none; box-shadow: none;">
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                                วันที่รับงาน
                            </label>
                            <input type="date" class="form-control" id="edit_reg_accep_date" style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; font-weight: 600; color: #1e293b; font-size: 0.92rem; outline: none; box-shadow: none;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                                กำหนดส่ง
                            </label>
                            <input type="date" class="form-control" id="edit_reg_due_date" style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; font-weight: 600; color: #1e293b; font-size: 0.92rem; outline: none; box-shadow: none;">
                        </div>
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="modal-footer" style="border-top: none; padding: 12px 28px 28px 28px; justify-content: flex-end;">
                <button type="button" class="btn" data-bs-dismiss="modal"
                    style="background-color: #f8fafc; color: #334155; font-weight: 700; border-radius: 12px; padding: 10px 24px; border: none; font-size: 0.92rem; transition: all 0.2s ease;">
                    ยกเลิก
                </button>
                <button type="button" class="btn" onclick="saveEditRegistrationTask()"
                    style="background-color: #007aff; color: #ffffff; font-weight: 700; border-radius: 12px; padding: 10px 24px; border: none; font-size: 0.92rem; box-shadow: 0 4px 14px rgba(0,122,255,0.25); transition: all 0.2s ease;">
                    บันทึกการแก้ไข
                </button>
            </div>
        </div>
    </div>
</div>

<!-- HTML5Sortable — ใช้ทำลากการ์ดข้ามคอลัมน์สถานะบนบอร์ด -->
<script src="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/template/assets/js/dragdrop.js"></script>

<script>
    // เปิด Modal ประวัติงานทะเบียน
    function openHistoryTaskModal() {
        const modalElement = document.getElementById('historyTaskModal');
        const myModal = new bootstrap.Modal(modalElement);
        myModal.show();
        loadHistoryTasks(1);
    }

    let historyCurrentPage = 1;
    let historyTotalPages = 1;
    let historySearchTimer = null;

    // ค้นหาแบบ debounce เวลาพิมพ์ในช่องค้นหา
    $(document).on('input', '#historySearchInput', function() {
        clearTimeout(historySearchTimer);
        historySearchTimer = setTimeout(function() {
            loadHistoryTasks(1);
        }, 400);
    });

    $(document).on('change', '#historyPerPage', function() {
        loadHistoryTasks(1);
    });

    function loadHistoryTasks(page) {
        if (page < 1) return;
        historyCurrentPage = page;

        const perPage = $('#historyPerPage').val();
        const keyword = $('#historySearchInput').val();

        $('#historyTaskTableBody').html(
            '<tr><td colspan="6" style="text-align:center;color:#94a3b8;font-size:0.85rem;padding:48px 14px;border-bottom:none;">กำลังโหลดข้อมูล...</td></tr>'
        );

        $.ajax({
            url: '<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/registration-task/history',
            method: 'GET',
            data: {
                page: page,
                per_page: perPage,
                keyword: keyword,
                fiscal_id: '<?php echo htmlspecialchars($data['fiscal_id'] ?? ''); ?>',
                company_id: '<?php echo htmlspecialchars($data['active_company_id'] ?? ''); ?>'
            },
            dataType: 'json',
            success: function(response) {
                renderHistoryTable(response.data || []);
                renderHistoryPagination(response);

                $('#historyTotalCount').text(response.total_count ?? 0);
                $('#historyTotalAmount').text(
                    Number(response.total_amount ?? 0).toLocaleString('th-TH', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    })
                );
            },
            error: function() {
                $('#historyTaskTableBody').html(
                    '<tr><td colspan="6" style="text-align:center;color:#94a3b8;font-size:0.85rem;padding:48px 14px;border-bottom:none;">ยังไม่มีประวัติงานที่ปิดแล้ว</td></tr>'
                );
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: 'โหลดประวัติงานไม่สำเร็จ',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            }
        });
    }

    function renderHistoryTable(rows) {
        const tbody = $('#historyTaskTableBody');
        tbody.empty();

        if (!rows.length) {
            tbody.append(
                '<tr><td colspan="6" style="text-align:center;color:#94a3b8;font-size:0.85rem;padding:48px 14px;border-bottom:none;">ยังไม่มีประวัติงานที่ปิดแล้ว</td></tr>'
            );
            return;
        }

        rows.forEach(function(row) {
            const amount = Number(row.amount ?? 0).toLocaleString('th-TH', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            tbody.append(
                '<tr>' +
                '<td style="padding:14px;border-bottom:1px dashed #f1f5f9;font-size:0.8rem;color:#334155;">' + (row.closed_at ?? '') + '</td>' +
                '<td style="padding:14px;border-bottom:1px dashed #f1f5f9;font-size:0.8rem;font-weight:700;color:#0f172a;">' + (row.customer_name ?? '') + '</td>' +
                '<td style="padding:14px;border-bottom:1px dashed #f1f5f9;font-size:0.8rem;color:#334155;">' + (row.task_name ?? '') + '</td>' +
                '<td style="padding:14px;border-bottom:1px dashed #f1f5f9;font-size:0.8rem;color:#334155;">' + (row.task_type ?? '') + '</td>' +
                '<td style="padding:14px;border-bottom:1px dashed #f1f5f9;font-size:0.8rem;color:#334155;">' + (row.assignee_name ?? '') + '</td>' +
                '<td style="padding:14px;border-bottom:1px dashed #f1f5f9;font-size:0.8rem;font-weight:700;color:#0f172a;text-align:right;">' + amount + '</td>' +
                '</tr>'
            );
        });
    }

    function renderHistoryPagination(response) {
        const total = response.total_count ?? 0;
        const perPage = parseInt($('#historyPerPage').val(), 10) || 25;
        historyTotalPages = Math.max(1, Math.ceil(total / perPage));

        const start = total === 0 ? 0 : ((historyCurrentPage - 1) * perPage) + 1;
        const end = Math.min(historyCurrentPage * perPage, total);
        $('#historyPaginationInfo').text('แสดง ' + start + '-' + end + ' จาก ' + total + ' รายการ');

        $('#historyPrevBtn').prop('disabled', historyCurrentPage <= 1)
            .css({
                'background-color': historyCurrentPage <= 1 ? '#f1f5f9' : '#e2e8f0',
                color: historyCurrentPage <= 1 ? '#94a3b8' : '#334155'
            });

        $('#historyNextBtn').prop('disabled', historyCurrentPage >= historyTotalPages)
            .css({
                'background-color': historyCurrentPage >= historyTotalPages ? '#f1f5f9' : '#e2e8f0',
                color: historyCurrentPage >= historyTotalPages ? '#94a3b8' : '#334155'
            });

        const pageNumbers = $('#historyPageNumbers');
        pageNumbers.empty();
        for (let i = 1; i <= historyTotalPages; i++) {
            const isActive = i === historyCurrentPage;
            const btn = $('<button type="button" class="btn"></button>')
                .text(i)
                .css({
                    'background-color': isActive ? '#007aff' : 'transparent',
                    color: isActive ? '#ffffff' : '#64748b',
                    border: 'none',
                    'border-radius': '8px',
                    width: '30px',
                    height: '30px',
                    padding: '0',
                    'font-size': '0.78rem',
                    'font-weight': '700'
                })
                .on('click', function() {
                    loadHistoryTasks(i);
                });
            pageNumbers.append(btn);
        }
    }

    // ========== ตั้งค่าประเภทงานทะเบียน ==========

    // โหลดรายการใหม่ทุกครั้งที่เปิด modal
    document.getElementById('jobTypeSettingsModal').addEventListener('show.bs.modal', function() {
        $('#jobTypeNameInput').val('');
        loadJobTypes();
    });

    // กด Enter ในช่องชื่อ = เพิ่มประเภทงานทันที
    $(document).on('keypress', '#jobTypeNameInput', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            addJobType();
        }
    });

    function loadJobTypes() {
        $('#jobTypeList').html('<div class="job-type-empty">กำลังโหลดข้อมูล...</div>');

        $.ajax({
            // TODO: เปลี่ยน URL ให้ตรงกับ route จริงฝั่ง Controller ที่คืนรายการประเภทงานทะเบียน
            url: '<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/registration-task/job-types',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                renderJobTypesList(response.data || []);
            },
            error: function() {
                $('#jobTypeList').html('<div class="job-type-empty">ยังไม่มีประเภทงาน กรุณาเพิ่มประเภทงานใหม่</div>');
            }
        });
    }

    function renderJobTypesList(rows) {
        const list = $('#jobTypeList');
        list.empty();

        if (!rows.length) {
            list.append('<div class="job-type-empty">ยังไม่มีประเภทงาน กรุณาเพิ่มประเภทงานใหม่</div>');
            return;
        }

        rows.forEach(function(row) {
            const $row = $('<div class="job-type-row"></div>');
            $('<span class="job-type-name"></span>').text(row.name ?? '').appendTo($row);

            const $actions = $('<div class="job-type-actions"></div>');

            // ป้ายสถานะ กดเพื่อสลับเปิด/ปิดใช้งานประเภทงาน
            const isActive = String(row.active_status ?? '1') === '1';
            const $statusBadge = $(
                '<button type="button" class="job-type-status-badge" title="คลิกเพื่อเปลี่ยนสถานะ">' +
                '<span class="job-type-status-dot"></span>' +
                '<span class="job-type-status-text"></span>' +
                '</button>'
            );
            $statusBadge.addClass(isActive ? 'is-active' : 'is-inactive');
            $statusBadge.find('.job-type-status-text').text(isActive ? 'ใช้งานอยู่' : 'ปิดใช้งาน');
            $statusBadge.on('click', function() {
                const $btn = $(this);
                const willBeActive = !$btn.hasClass('is-active');
                toggleJobTypeStatus(row.id, willBeActive, $btn);
            });
            $actions.append($statusBadge);

            $('<button type="button" class="btn-action-edit" title="แก้ไข"><i class="ri-edit-line"></i></button>')
                .on('click', function() {
                    editJobType(row.id, row.name);
                })
                .appendTo($actions);
            $('<button type="button" class="btn-action-delete" title="ลบ"><i class="ri-delete-bin-line"></i></button>')
                .on('click', function() {
                    deleteJobType(row.id);
                })
                .appendTo($actions);

            $row.append($actions);
            list.append($row);
        });
    }

    function addJobType() {
        const name = $('#jobTypeNameInput').val().trim();
        if (!name) {
            $('#jobTypeNameInput').trigger('focus');
            return;
        }

        $.ajax({
            // TODO: เปลี่ยน URL ให้ตรงกับ route จริงฝั่ง Controller สำหรับเพิ่มประเภทงาน
            url: '<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/registration-task/job-types/add',
            method: 'POST',
            data: {
                name: name
            },
            dataType: 'json',
            success: function() {
                $('#jobTypeNameInput').val('').trigger('focus');
                loadJobTypes();
            },
            error: function() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: 'เพิ่มประเภทงานไม่สำเร็จ',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            }
        });
    }

    function editJobType(id, currentName) {
        if (typeof Swal === 'undefined') return;

        Swal.fire({
            title: 'แก้ไขประเภทงาน',
            input: 'text',
            inputValue: currentName,
            showCancelButton: true,
            confirmButtonText: 'บันทึก',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#007aff',
            inputValidator: function(value) {
                if (!value || !value.trim()) {
                    return 'กรุณากรอกชื่อประเภทงาน';
                }
            }
        }).then(function(result) {
            if (!result.isConfirmed) return;

            $.ajax({
                // TODO: เปลี่ยน URL ให้ตรงกับ route จริงฝั่ง Controller สำหรับแก้ไขประเภทงาน
                url: '<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/registration-task/job-types/edit',
                method: 'POST',
                data: {
                    id: id,
                    name: result.value.trim()
                },
                dataType: 'json',
                success: function() {
                    loadJobTypes();
                },
                error: function() {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: 'แก้ไขประเภทงานไม่สำเร็จ',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            });
        });
    }

    function deleteJobType(id) {
        if (typeof Swal === 'undefined') return;

        Swal.fire({
            title: 'ยืนยันการลบประเภทงานนี้?',
            text: 'ประเภทงานที่ถูกลบจะไม่สามารถเลือกใช้งานในงานทะเบียนใหม่ได้อีก',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'ลบ',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#e11d48'
        }).then(function(result) {
            if (!result.isConfirmed) return;

            $.ajax({
                // TODO: เปลี่ยน URL ให้ตรงกับ route จริงฝั่ง Controller สำหรับลบประเภทงาน
                url: '<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/registration-task/job-types/delete',
                method: 'POST',
                data: {
                    id: id
                },
                dataType: 'json',
                success: function() {
                    loadJobTypes();
                },
                error: function() {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: 'ลบประเภทงานไม่สำเร็จ',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            });
        });
    }

    function toggleJobTypeStatus(id, willBeActive, $btn) {
        $btn.prop('disabled', true);

        $.ajax({
            url: '<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/registration-task/job-types/toggle-status',
            method: 'POST',
            data: {
                id: id,
                active_status: willBeActive ? '1' : '0'
            },
            dataType: 'json',
            success: function(response) {
                $btn.prop('disabled', false);
                if (response && response.result === 1) {
                    $btn.toggleClass('is-active', willBeActive).toggleClass('is-inactive', !willBeActive);
                    $btn.find('.job-type-status-text').text(willBeActive ? 'ใช้งานอยู่' : 'ปิดใช้งาน');
                } else {
                    showToggleStatusError(response && response.msg);
                }
            },
            error: function() {
                $btn.prop('disabled', false);
                showToggleStatusError();
            }
        });
    }

    function showToggleStatusError(msg) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: msg || 'เปลี่ยนสถานะไม่สำเร็จ',
                showConfirmButton: false,
                timer: 2000
            });
        }
    }

    // ========== ตั้งค่างานทะเบียน ==========

    // โหลดค่าปัจจุบันทุกครั้งที่เปิด modal
    document.getElementById('registrationTaskSettingsModal').addEventListener('show.bs.modal', function() {
        $.ajax({
            url: '<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/registration-task/settings',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                const s = (response && response.data) || {};
                const notifyDay = s.notify_day ?? 7;
                $('#settingsNotifyDay').val(notifyDay);
                $('#settingsNotifyDayHint').text('งานที่จะครบกำหนดภายใน ' + notifyDay + ' วัน จะถูกแสดงป้าย "ใกล้ครบกำหนด"');

                const byLevel = {};
                (s.urgency_levels || []).forEach(function(l) {
                    byLevel[l.urgency_level] = l;
                });

                $('#settingsNormalLabel').val(byLevel['1'] ? byLevel['1'].label : 'ปกติ');
                $('#settingsNormalColor').val(byLevel['1'] ? byLevel['1'].color : '#94a3b8');
                $('#settingsUrgentLabel').val(byLevel['2'] ? byLevel['2'].label : 'เร่งด่วน');
                $('#settingsUrgentColor').val(byLevel['2'] ? byLevel['2'].color : '#f97316');
                $('#settingsVeryUrgentLabel').val(byLevel['3'] ? byLevel['3'].label : 'ด่วนมาก');
                $('#settingsVeryUrgentColor').val(byLevel['3'] ? byLevel['3'].color : '#ef4444');
            },
            error: function() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: 'โหลดตั้งค่าไม่สำเร็จ',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            }
        });
    });

    // พิมพ์เลขวันแล้วอัปเดตข้อความคำอธิบายทันที
    $(document).on('input', '#settingsNotifyDay', function() {
        $('#settingsNotifyDayHint').text('งานที่จะครบกำหนดภายใน ' + ($(this).val() || 0) + ' วัน จะถูกแสดงป้าย "ใกล้ครบกำหนด"');
    });

    function saveRegistrationTaskSettings() {
        $.ajax({
            url: '<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/registration-task/settings/save',
            method: 'POST',
            data: {
                notify_day: $('#settingsNotifyDay').val(),
                normal_label: $('#settingsNormalLabel').val(),
                normal_color: $('#settingsNormalColor').val(),
                urgent_label: $('#settingsUrgentLabel').val(),
                urgent_color: $('#settingsUrgentColor').val(),
                very_urgent_label: $('#settingsVeryUrgentLabel').val(),
                very_urgent_color: $('#settingsVeryUrgentColor').val()
            },
            dataType: 'json',
            success: function(response) {
                if (response && response.result === 1) {
                    // รีโหลดหน้าใหม่ เพราะการ์ดบนบอร์ดถูก render จาก PHP ตอนโหลดหน้า
                    // ต้องโหลดใหม่ถึงจะเห็นสี/ป้ายความเร่งด่วนที่เพิ่งบันทึกเปลี่ยนไป
                    sessionStorage.setItem('toast_msg', response.msg);
                    sessionStorage.setItem('toast_icon', 'success');
                    location.reload();
                } else if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: (response && response.msg) || 'บันทึกตั้งค่าไม่สำเร็จ',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            },
            error: function() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: 'บันทึกตั้งค่าไม่สำเร็จ',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            }
        });
    }

    // ========== เพิ่มงานทะเบียน ==========

    // เคลียร์ฟอร์ม + โหลดตัวเลือก "ประเภทงาน" และ "ความเร่งด่วน" ใหม่ทุกครั้งที่เปิด modal
    // โหลดตัวเลือกประเภทงานใส่ select ที่ระบุ (ใช้ร่วมกันทั้ง modal เพิ่มและแก้ไข)
    function loadRegistrationTypeOptions(selectId, selectedValue) {
        $.ajax({
            url: '<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/registration-task/job-types',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                const $select = $('#' + selectId);
                $select.empty();
                const rows = (response && response.data) || [];
                if (!rows.length) {
                    $select.append('<option value="">ยังไม่มีประเภทงาน</option>');
                    return;
                }
                $select.append('<option value="">เลือกประเภทงาน</option>');
                rows.forEach(function(row) {
                    $select.append('<option value="' + row.id + '">' + row.name + '</option>');
                });
                if (selectedValue) {
                    $select.val(String(selectedValue));
                }
            },
            error: function() {
                $('#' + selectId).empty().append('<option value="">โหลดประเภทงานไม่สำเร็จ</option>');
            }
        });
    }

    // โหลดตัวเลือกความเร่งด่วนใส่ select ที่ระบุ (ใช้ร่วมกันทั้ง modal เพิ่มและแก้ไข)
    function loadUrgencyLevelOptions(selectId, selectedValue) {
        $.ajax({
            url: '<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/registration-task/settings',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                const $select = $('#' + selectId);
                $select.empty();
                const levels = (response && response.data && response.data.urgency_levels) || [];
                $select.append('<option value="">ไม่ระบุ</option>');
                levels.forEach(function(level) {
                    $select.append('<option value="' + level.urgency_level + '">' + level.label + '</option>');
                });
                if (selectedValue) {
                    $select.val(String(selectedValue));
                }
            },
            error: function() {
                $('#' + selectId).empty().append('<option value="">โหลดข้อมูลไม่สำเร็จ</option>');
            }
        });
    }

    document.getElementById('addRegistrationTaskModal').addEventListener('show.bs.modal', function() {
        document.getElementById('addRegistrationTaskForm').reset();
        loadRegistrationTypeOptions('regTypeId');
        loadUrgencyLevelOptions('regUrgencyLevel');
    });

    function saveRegistrationTask() {
        const customerName = $('#regCustomerName').val().trim();
        const customerPhone = $('#regCustomerPhone').val().trim();
        const contactPerson = $('#regContactPerson').val().trim();
        const registrationTypeId = $('#regTypeId').val();
        const registrationName = $('#regName').val().trim();
        const description = $('#regDescription').val().trim();
        const assigneeUserId = $('#regAssignee').val();

        if (!customerName || !customerPhone || !contactPerson || !registrationTypeId || !registrationName || !description || !assigneeUserId) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: 'กรุณากรอกข้อมูลที่มี * ให้ครบถ้วน',
                    showConfirmButton: false,
                    timer: 2000
                });
            }
            return;
        }

        $.ajax({
            url: '<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/registration-task/add',
            method: 'POST',
            data: {
                customer_name: customerName,
                customer_phone: customerPhone,
                contact_person: contactPerson,
                registration_type_id: registrationTypeId,
                registration_name: registrationName,
                description: description,
                service_amount: $('#regServiceAmount').val(),
                urgency_level: $('#regUrgencyLevel').val(),
                assignee_user_id: assigneeUserId,
                review_user_id: $('#regReviewer').val(),
                accep_date: $('#regAccepDate').val(),
                due_date: $('#regDueDate').val()
            },
            dataType: 'json',
            success: function(response) {
                if (response && response.result === 1) {
                    sessionStorage.setItem('toast_msg', response.msg);
                    sessionStorage.setItem('toast_icon', 'success');
                    location.reload();
                } else if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: (response && response.msg) || 'เพิ่มงานทะเบียนไม่สำเร็จ',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            },
            error: function() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: 'เพิ่มงานทะเบียนไม่สำเร็จ',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            }
        });
    }

    // ========== แก้ไข/ลบงานทะเบียน ==========

    // เปิด modal แก้ไข แล้วดึงข้อมูลงานทะเบียนตัวนั้นมาเติมในฟอร์ม
    function editRegistrationTaskModal(id) {
        $.ajax({
            url: '<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/registration-task/get',
            method: 'GET',
            data: { id: id },
            dataType: 'json',
            success: function(response) {
                if (!response || response.result !== 1) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: (response && response.msg) || 'ไม่พบข้อมูลงานทะเบียน',
                            showConfirmButton: false,
                            timer: 2000
                        });
                    }
                    return;
                }

                const task = response.data;
                $('#edit_reg_id').val(task.registration);
                $('#edit_reg_customer_name').val(task.customer_name);
                $('#edit_reg_customer_phone').val(task.customer_phone);
                $('#edit_reg_contact_person').val(task.contact_person);
                $('#edit_reg_name').val(task.registration_name);
                $('#edit_reg_description').val(task.description);
                $('#edit_reg_service_amount').val(task.service_amount);
                $('#edit_reg_accep_date').val(task.accep_date);
                $('#edit_reg_due_date').val(task.due_date);
                $('#edit_reg_assignee').val(task.assignee_user_id ?? '');
                $('#edit_reg_reviewer').val(task.review_user_id ?? '');
                $('#edit_reg_no').val(task.registration_no);

                loadRegistrationTypeOptions('edit_reg_type_id', task.registration_type_id);
                loadUrgencyLevelOptions('edit_reg_urgency_level', task.urgency_level);

                const modalElement = document.getElementById('editRegistrationTaskModal');
                const myModal = new bootstrap.Modal(modalElement);
                myModal.show();
            },
            error: function() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: 'โหลดข้อมูลงานทะเบียนไม่สำเร็จ',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            }
        });
    }

    function saveEditRegistrationTask() {
        const id = $('#edit_reg_id').val();
        const customerName = $('#edit_reg_customer_name').val().trim();
        const customerPhone = $('#edit_reg_customer_phone').val().trim();
        const contactPerson = $('#edit_reg_contact_person').val().trim();
        const registrationTypeId = $('#edit_reg_type_id').val();
        const registrationName = $('#edit_reg_name').val().trim();
        const description = $('#edit_reg_description').val().trim();
        const assigneeUserId = $('#edit_reg_assignee').val();

        if (!customerName || !customerPhone || !contactPerson || !registrationTypeId || !registrationName || !description || !assigneeUserId) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: 'กรุณากรอกข้อมูลที่มี * ให้ครบถ้วน',
                    showConfirmButton: false,
                    timer: 2000
                });
            }
            return;
        }

        $.ajax({
            url: '<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/registration-task/edit',
            method: 'POST',
            data: {
                id: id,
                customer_name: customerName,
                customer_phone: customerPhone,
                contact_person: contactPerson,
                registration_type_id: registrationTypeId,
                registration_name: registrationName,
                description: description,
                service_amount: $('#edit_reg_service_amount').val(),
                urgency_level: $('#edit_reg_urgency_level').val(),
                assignee_user_id: assigneeUserId,
                review_user_id: $('#edit_reg_reviewer').val(),
                accep_date: $('#edit_reg_accep_date').val(),
                due_date: $('#edit_reg_due_date').val()
            },
            dataType: 'json',
            success: function(response) {
                if (response && response.result === 1) {
                    sessionStorage.setItem('toast_msg', response.msg);
                    sessionStorage.setItem('toast_icon', 'success');
                    location.reload();
                } else if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: (response && response.msg) || 'แก้ไขงานทะเบียนไม่สำเร็จ',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            },
            error: function() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: 'แก้ไขงานทะเบียนไม่สำเร็จ',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            }
        });
    }

    function deleteRegistrationTaskCard(id, name) {
        if (typeof Swal === 'undefined') return;

        Swal.fire({
            icon: 'warning',
            title: 'ลบงานทะเบียนนี้?',
            text: name + ' จะถูกลบออกจากบอร์ด',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'ลบข้อมูล',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: '<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/registration-task/delete',
                method: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    if (response && response.result === 1) {
                        sessionStorage.setItem('toast_msg', response.msg);
                        sessionStorage.setItem('toast_icon', 'success');
                        location.reload();
                    } else {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: (response && response.msg) || 'ลบงานทะเบียนไม่สำเร็จ',
                            showConfirmButton: false,
                            timer: 2000
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: 'ลบงานทะเบียนไม่สำเร็จ',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            });
        });
    }

    // ========== ลากการ์ดย้ายสถานะ (Drag & Drop) ==========

    // ทำให้ทุกคอลัมน์สถานะรับ/ปล่อยการ์ดข้ามกันได้ (acceptFrom ชี้กลับมาที่ selector เดียวกัน)
    sortable('.board-status-lane', {
        items: '.register-task-card',
        acceptFrom: '.board-status-lane'
    });

    // Auto-scroll หน้าจอตอนลากการ์ดเข้าใกล้ขอบบน/ล่างของจอ (บอร์ดมี 7 lane ยาวเกินจอเดียว)
    (function() {
        const EDGE_SIZE = 90; // ระยะจากขอบจอ (px) ที่เริ่มเลื่อนหน้าอัตโนมัติ
        const SCROLL_SPEED = 18; // ความเร็วเลื่อน (px ต่อรอบ)
        let autoScrollTimer = null;

        function stopAutoScroll() {
            clearInterval(autoScrollTimer);
            autoScrollTimer = null;
        }

        document.addEventListener('dragover', function(e) {
            const y = e.clientY;

            if (y < EDGE_SIZE) {
                if (!autoScrollTimer) {
                    autoScrollTimer = setInterval(function() {
                        window.scrollBy(0, -SCROLL_SPEED);
                    }, 16);
                }
            } else if (y > window.innerHeight - EDGE_SIZE) {
                if (!autoScrollTimer) {
                    autoScrollTimer = setInterval(function() {
                        window.scrollBy(0, SCROLL_SPEED);
                    }, 16);
                }
            } else {
                stopAutoScroll();
            }
        });

        document.addEventListener('drop', stopAutoScroll);
        document.addEventListener('dragend', stopAutoScroll);
    })();

    // อัปเดตจำนวน "X งาน" และข้อความ "ยังไม่มีงานในสถานะนี้" ของทุกคอลัมน์ ให้ตรงกับ DOM ปัจจุบัน
    function refreshRegistrationLaneCounts() {
        document.querySelectorAll('.board-status-lane').forEach(function(lane) {
            const cardCount = lane.querySelectorAll('.register-task-card').length;

            const swimlane = lane.closest('.board-swimlane-card');
            const countEl = swimlane ? swimlane.querySelector('.board-lane-count') : null;
            if (countEl) {
                countEl.textContent = cardCount + ' งาน';
            }

            const emptyMsg = lane.querySelector('.board-lane-empty-msg');
            if (cardCount === 0 && !emptyMsg) {
                lane.insertAdjacentHTML('afterbegin', '<div class="board-empty-state board-lane-empty-msg">ยังไม่มีงานในสถานะนี้</div>');
            } else if (cardCount > 0 && emptyMsg) {
                emptyMsg.remove();
            }
        });
    }

    // ทุก .board-status-lane ยิง event นี้ตัวเดียวกัน (bubbling ไม่เกี่ยวข้อง — sortable ผูก event ไว้ที่แต่ละ element)
    document.querySelectorAll('.board-status-lane').forEach(function(lane) {
        lane.addEventListener('sortupdate', function(e) {
            const item = e.detail.item;
            const destinationContainer = e.detail.destination.container;
            const originContainer = e.detail.origin.container;
            const taskId = item.getAttribute('data-task-id');
            const newStatus = destinationContainer.getAttribute('data-status');

            refreshRegistrationLaneCounts();

            // ลากไปลงคอลัมน์เดิม (แค่จัดลำดับในคอลัมน์เดียวกัน) ไม่ต้องยิง API อัปเดตสถานะ
            if (originContainer === destinationContainer) {
                return;
            }

            $.ajax({
                url: '<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/registration-task/update-status',
                method: 'POST',
                data: { id: taskId, status: newStatus },
                dataType: 'json',
                success: function(response) {
                    if (!response || response.result !== 1) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'error',
                                title: (response && response.msg) || 'อัปเดตสถานะไม่สำเร็จ',
                                showConfirmButton: false,
                                timer: 2000
                            });
                        }
                        // ข้อมูลบนจอกับ DB ไม่ตรงกันแล้ว โหลดหน้าใหม่ให้ตรงกับความจริง
                        location.reload();
                    }
                },
                error: function() {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: 'อัปเดตสถานะไม่สำเร็จ',
                            showConfirmButton: false,
                            timer: 2000
                        });
                    }
                    location.reload();
                }
            });
        });
    });

    // ========== ปิด Job (ย้ายไปประวัติงานที่เสร็จแล้ว) ==========
    // ปุ่มอยู่ในการ์ด ผูกแบบ event delegation เผื่อการ์ดถูก render/ย้ายใหม่
    $(document).on('click', '.btn-close-job', function() {
        const card = this.closest('.register-task-card');
        const taskId = card ? card.getAttribute('data-task-id') : null;
        if (!taskId) return;

        if (typeof Swal === 'undefined') return;

        Swal.fire({
            icon: 'warning',
            title: 'ยืนยันปิด Job นี้?',
            text: 'งานจะย้ายไปประวัติงานที่เสร็จแล้ว',
            showCancelButton: true,
            confirmButtonColor: '#007aff',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'ยืนยัน',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: '<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/registration-task/close',
                method: 'POST',
                data: { id: taskId },
                dataType: 'json',
                success: function(response) {
                    if (response && response.result === 1) {
                        sessionStorage.setItem('toast_msg', response.msg);
                        sessionStorage.setItem('toast_icon', 'success');
                        location.reload();
                    } else {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: (response && response.msg) || 'ปิด Job ไม่สำเร็จ',
                            showConfirmButton: false,
                            timer: 2000
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: 'ปิด Job ไม่สำเร็จ',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            });
        });
    });

    // โหลดข้อมูลประวัติทุกครั้งที่เปิด Modal ประวัติงานทะเบียน (ปุ่มเปิดใช้ data-bs-toggle)
    (function() {
        const historyModalEl = document.getElementById('historyTaskModal');
        if (historyModalEl) {
            historyModalEl.addEventListener('shown.bs.modal', function() {
                loadHistoryTasks(1);
            });
        }
    })();
</script>
<?php
// 3. นำ Footer เข้ามา
require_once dirname(__DIR__) . '/main/footer.php';
?>