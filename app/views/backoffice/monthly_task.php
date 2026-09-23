<?php
    // app/views/backoffice/closing.php
    $selected_year          = $_GET['year'] ?? '2569';
    $company_name           = $_GET['company'] ?? 'TEST ACCOUNTING';
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

    body.modal-open {
        overflow: hidden !important;
    }

    .flatpickr-calendar {
        z-index: 1060 !important;
    }

    .flatpickr-wrapper {
        display: block !important;
        width: 100% !important;
    }

    .main-content {
        padding-top: 0px !important;
    }

    .container-fluid {
        padding-left: 0 !important;
        padding-right: 14px !important;
    }

    .main-page-wrapper {
        padding-top: 0px !important;
        /* padding: 20px 0px !important; */
        min-height: calc(100vh - 72px);
    }

    /* --- Master Card Wrapper --- */
    .main-card-wrapper {
        background-color: #ffffff;
        border-radius: 16px;
        border: 1px solid #edf2f7;
        box-shadow: 0 2px 12px rgba(16, 24, 40, 0.03);
        padding: 24px 20px;
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

    .filter-group .select2-container {
        flex: 1 1 0;
        min-width: 0;
        width: 100% !important;
    }

    /* --- Custom Select2 Pill Design --- */
    .select2-container--default .select2-selection--single {
        background-color: #f8fafc !important;
        border: 1px solid #f1f5f9 !important;
        border-radius: 14px !important;
        height: 42px !important;
        display: flex !important;
        align-items: center !important;
        transition: all 0.2s ease !important;
        box-shadow: none !important;
    }

    .select2-container--default .select2-selection--single:focus,
    .select2-container--default.select2-container--open .select2-selection--single {
        background-color: #ffffff !important;
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #334155 !important;
        font-size: 0.875rem !important;
        font-weight: 500 !important;
        padding-left: 16px !important;
        padding-right: 36px !important;
        line-height: 40px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
        width: 30px !important;
        right: 10px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow b {
        border-color: #64748b transparent transparent transparent !important;
        border-width: 5px 4px 0 4px !important;
    }

    .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
        border-color: transparent transparent #64748b transparent !important;
        border-width: 0 4px 5px 4px !important;
    }

    /* Select2 Dropdown Popup */
    .select2-dropdown {
        border: 1px solid #edf2f7 !important;
        border-radius: 14px !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08) !important;
        overflow: hidden !important;
        z-index: 9999 !important;
        font-size: 0.875rem !important;
        background-color: #ffffff !important;
    }

    .select2-container--default .select2-results__option {
        padding: 10px 16px !important;
        font-weight: 500 !important;
        color: #475569 !important;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #3b82f6 !important;
        color: #ffffff !important;
    }

    .select2-container--default .select2-results__option[aria-selected=true] {
        background-color: #eff6ff !important;
        color: #1d4ed8 !important;
        font-weight: 700 !important;
    }

    .month-filter-disabled {
        opacity: 0.58;
    }

    .month-filter-disabled .select2-container {
        cursor: not-allowed;
    }

    .month-filter-disabled .select2-selection--single {
        background-color: #e2e8f0 !important;
        border-color: #cbd5e1 !important;
        color: #64748b !important;
        box-shadow: none !important;
        cursor: not-allowed !important;
    }

    .month-filter-disabled .select2-selection__rendered,
    .month-filter-disabled .select2-selection__arrow b {
        color: #64748b !important;
    }

    #manageModal .select2-container {
        width: 100% !important;
    }

    #manageModal .select2-container--default .select2-selection--single {
        background-color: #f8fafc !important;
        border: 0 !important;
        border-radius: 8px !important;
        height: 42px !important;
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

    .btn-action-search {
        display: inline-flex;
        align-items: center;
        justify-content: space-between;
        gap: 6px;
        padding: 4px 10px;
        min-width: 80px;
        height: 32px;
        border-radius: 8px;
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #334155;
        font-size: 0.82rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        line-height: 1;
    }

    .btn-action-search:hover {
        background-color: #eff6ff;
        color: #2563eb;
        border-color: #bfdbfe;
        box-shadow: 0 2px 6px rgba(37, 99, 235, 0.12);
    }

    .btn-action-search i {
        font-size: 14px;
        color: #64748b;
        transition: color 0.2s ease;
    }

    .btn-action-search:hover i {
        color: #2563eb;
    }

    .badge-info {
        background-color: #eff6ff;
        color: #2563eb;
        font-size: 0.72rem;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 6px;
        display: inline-block;
        white-space: nowrap;
    }

    .badge-subtext {
        font-size: 0.70rem;
        color: #64748b;
        font-weight: 600;
        margin-top: 3px;
        display: block;
        line-height: 1.2;
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

    /* --- Comment Chat Thread --- */
    .comment-thread-panel {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        margin: 6px 0 16px;
        padding: 14px;
        box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.02);
    }

    .comment-thread-list {
        max-height: 280px;
        overflow-y: auto;
        margin-bottom: 14px;
        padding: 2px 4px 2px 2px;
    }

    .comment-day-divider {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 14px 0;
        color: #94a3b8;
        font-size: 0.7rem;
        font-weight: 700;
    }

    .comment-day-divider::before,
    .comment-day-divider::after {
        content: '';
        height: 1px;
        flex: 1;
        background: #e2e8f0;
    }

    .comment-message {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        margin: 8px 0;
    }

    .comment-message.is-mine {
        align-items: flex-end;
    }

    .comment-content-row {
        display: flex;
        align-items: center;
        gap: 7px;
        width: 100%;
        max-width: 100%;
    }

    .comment-message.is-mine .comment-content-row {
        flex-direction: row-reverse;
    }

    .comment-bubble {
        max-width: min(calc(100% - 72px), 440px);
        padding: 8px 11px 7px;
        border-radius: 14px 14px 14px 4px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 5px rgba(15, 23, 42, 0.04);
    }

    .comment-message.is-mine .comment-bubble {
        border-color: #bfdbfe;
        border-radius: 14px 14px 4px 14px;
        background: #dbeafe;
    }

    .comment-author {
        display: block;
        margin: 0 8px 3px;
        color: #2563eb;
        font-size: 0.7rem;
        font-weight: 800;
    }

    .comment-message.is-mine .comment-author {
        color: #1d4ed8;
    }

    .comment-body {
        color: #334155;
        font-size: 0.82rem;
        line-height: 1.45;
        white-space: pre-wrap;
        word-break: break-word;
    }

    .comment-time {
        display: none;
        flex: 0 0 auto;
        margin: 0 2px;
        color: #94a3b8;
        font-size: 0.64rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .comment-message.is-mine .comment-time {
        color: #64748b;
    }
    .task-status-wrap {
        position: relative;
        display: inline-block;
        width: 130px;
        flex-shrink: 0;
    }

    .task-status-badge {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 7px 12px;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 700;
        pointer-events: none; /* ให้คลิกทะลุไปที่ select ด้านล่าง */
        white-space: nowrap;
    }

.task-status-badge::after {
    content: '';
    width: 0;
    height: 0;
    border-left: 4px solid transparent;
    border-right: 4px solid transparent;
    border-top: 5px solid currentColor;
    margin-left: 6px;
    flex-shrink: 0;
}

.task-status-badge.status-pending {
    background-color: #fef3c7;
    color: #b45309;
}

.task-status-badge.status-done {
    background-color: #dcfce7;
    color: #15803d;
}

/* select ตัวจริง โปร่งใสซ้อนทับ badge ไว้ทั้งหมด ยังคลิก/เปิด dropdown ได้ตามปกติ */
.task-status-select {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
    border: none;
    margin: 0;
    padding: 0;
}
    @media (max-width: 576px) {
        .comment-bubble {
            max-width: calc(100% - 72px);
        }

        .comment-content-row {
            max-width: 100%;
        }
    }
</style>

<div class="container-fluid">
    <div class="main-content d-flex flex-column">
        <div class="content-wrapper">
            <div class="main-page-wrapper">

                <div class="main-card-wrapper" id="mainListContainer">

                    <!-- Page Header Section -->
                    <div class="page-header-box">
                        <div>
                            <h2 class="page-title">จัดการงานรายเดือน</h2>
                            <?php $fy_display = ! empty($data['active_fiscal_year']) ? $data['active_fiscal_year'] : 'ไม่ได้เลือกปี'; ?>
                            <p class="page-subtitle">ภาพรวมระบบ - งานรายเดือน - ปี
                                <?php echo htmlspecialchars($fy_display); ?>
                            </p>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn-excel-action" id="exportMonthlyButton" onclick="exportMonthlyTaskExcel('monthly')" style="display: <?php echo !empty($data['is_customer_year_view']) ? 'none' : 'inline-flex'; ?>;">
                                <i class="ri-upload-2-line"></i>
                                <span>ส่งออก Excel</span>
                            </button>
                            <button type="button" class="btn-excel-action" onclick="exportMonthlyTaskExcel('customer_year')" id="exportCustomerYearButton" style="display: <?php echo !empty($data['is_customer_year_view']) ? 'inline-flex' : 'none'; ?>;">
                                <i class="ri-user-3-line"></i>
                                <span>ส่งออก Excel</span>
                            </button>
                        </div>
                    </div>

                    <!-- Stats Grid -->
                    <?php
                    $monthly_task_stats = $data['monthly_task_stats'] ?? [];
                    $total_customers_count = (int) ($monthly_task_stats['total_customers'] ?? 0);
                    $doc_received_count = (int) ($monthly_task_stats['doc_received'] ?? 0);
                    $completed_count = (int) ($monthly_task_stats['completed'] ?? 0);
                    $tax_count = (int) ($monthly_task_stats['tax_submitted'] ?? 0);
                    $payment_count = (int) ($monthly_task_stats['payment_received'] ?? 0);
                    ?>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon blue">
                                <i class="ri-user-3-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val" id="statTotalCustomers"><?php echo number_format($total_customers_count); ?></span>
                                <span class="stat-label">ลูกค้าในเดือนนี้</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon green">
                                <i class="ri-draft-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val" id="statDocReceived"><?php echo number_format($doc_received_count); ?></span>
                                <span class="stat-label">ได้รับเอกสาร</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon purple">
                                <i class="ri-checkbox-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val" id="statCompleted"><?php echo number_format($completed_count); ?></span>
                                <span class="stat-label">งานเสร็จแล้ว</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon yellow">
                                <i class="ri-mail-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val" id="statTaxSubmitted"><?php echo number_format($tax_count); ?></span>
                                <span class="stat-label">ยื่นภาษีแล้ว</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon green">
                                <i class="ri-wallet-3-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val" id="statPaymentReceived"><?php echo number_format($payment_count); ?></span>
                                <span class="stat-label">ได้รับเงินแล้ว</span>
                            </div>
                        </div>
                    </div>

                    <!-- Month Selector Row (Bootstrap Utility Classes) -->
                    <div class="d-flex align-items-center gap-2 my-4" id="monthlyTaskFilters">
                        <div class="d-flex align-items-center gap-2 <?php echo !empty($data['is_customer_year_view']) ? 'month-filter-disabled' : ''; ?>" id="monthFilterWrap">
                            <span class="me-1">เลือกเดือน:</span>
                            <div class="w-auto" id="monthSelectWrap">
                            <select class="form-select" id="monthSelect" <?php echo !empty($data['is_customer_year_view']) ? 'disabled' : ''; ?>>
                                <?php
                                    $selectedMonth = (int) ($data['selected_month'] ?? date('n'));
                                    $months        = [
                                        1  => 'มกราคม',
                                        2  => 'กุมภาพันธ์',
                                        3  => 'มีนาคม',
                                        4  => 'เมษายน',
                                        5  => 'พฤษภาคม',
                                        6  => 'มิถุนายน',
                                        7  => 'กรกฎาคม',
                                        8  => 'สิงหาคม',
                                        9  => 'กันยายน',
                                        10 => 'ตุลาคม',
                                        11 => 'พฤศจิกายน',
                                        12 => 'ธันวาคม',
                                    ];
                                    
                                    if (!empty($data['is_customer_year_view'])) {
                                        echo '<option value="" selected>ไม่ระบุ</option>';
                                    } else {
                                        foreach ($months as $num => $name) {
                                            $isSelected = ($num === $selectedMonth) ? 'selected' : '';
                                            echo "<option value=\"$num\" $isSelected>$name</option>";
                                        }
                                    }
                                ?>
                            </select>
                            </div>
                        </div>
                        <span class="ms-3 me-1">เลือกลูกค้า:</span>
                        <div class="customer-select-wrap" style="min-width: 200px; max-width: 200px; width: 100%;">
                            <select class="form-select" id="customerSelect">
                                <option value="all" <?php echo empty($data['selected_customer_id']) ? 'selected' : ''; ?>>ทั้งหมด</option>
                                <?php foreach (($data['monthly_task_customers'] ?? []) as $customer): ?>
                                    <option value="<?php echo (int) $customer['customer_id']; ?>"
                                        <?php echo ((int) ($data['selected_customer_id'] ?? 0) === (int) $customer['customer_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($customer['customer_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <!-- Filter Toolbar (ค้นหา & ตัวกรองสถานะ) -->
                    <div class="filter-toolbar mb-3">
                        <div class="search-box-wrap">
                            <i class="ri-search-line"></i>
                            <input type="text" class="search-input" id="monthlyTaskSearch" placeholder="ค้นหาชื่อลูกค้า ผู้ดูแล ทีม">
                        </div>

                        <div class="filter-group mb-4">
                            <select class="form-select filter-select" id="selUser">
                                <option value="">ทุกผู้ดูแล</option>
                                <?php foreach (($data['monthly_task_caretakers'] ?? []) as $user): ?>
                                    <?php $userName = trim(($user['user_firstname'] ?? '') . ' ' . ($user['user_lastname'] ?? '')); ?>
                                    <option value="<?php echo (int) $user['user_id']; ?>">
                                        <?php echo htmlspecialchars($userName ?: 'ไม่ระบุชื่อ'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <select class="form-select filter-select" id="selDocument">
                                <option selected value="">เอกสาร: ทั้งหมด</option>
                                <option value="1">ได้รับเอกสาร</option>
                                <option value="2">ยังไม่ได้รับเอกสาร</option>
                            </select>

                            <select class="form-select filter-select" id="selTask">
                                <option selected value="">งาน: ทั้งหมด</option>
                                <option value="1">เสร็จแล้ว</option>
                                <option value="2">กำลังดำเนินงาน</option>
                            </select>

                            <select class="form-select filter-select" id="selTax">
                                <option selected value="">ภาษี: ทั้งหมด</option>
                                <option value="1">ยื่นแล้ว</option>
                                <option value="2">ยังไม่ได้ยื่น</option>
                            </select>

                            <select class="form-select filter-select" id="selPayment">
                                <option selected value="">เงิน: ทั้งหมด</option>
                                <option value="1">ได้รับเงินแล้ว</option>
                                <option value="2">ยังไม่ได้รับเงิน</option>
                            </select>
                        </div>
                    </div>

                    <!-- Table Container -->

                    <div id="monthlyTaskTable">
                        <?php if (!empty($data['is_customer_year_view'])): ?>
                            <?php include 'table/monthly_task_customer.php'; ?>
                        <?php else: ?>
                            <?php include 'table/mounthly_task.php'; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Detail View Container (Hidden by default) -->
                <div id="monthlyTaskDetailContainer" class="d-none">
                    <?php include 'table/monthly_task_detail.php'; ?>
                </div>

            </div>
        </div>
    </div>


    <!-- 
    <div class="modal fade" id="manageModal" tabindex="-1" aria-labelledby="manageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content"
                style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <div class="modal-header" style="padding: 20px 24px; border-bottom: 1px solid #e2e8f0;">
                    <div>
                        <h5 class="modal-title fw-bold" id="manageModalLabel"
                            style="color: #1e293b; font-size: 1.15rem;">อัปเดตงานรายเดือน</h5>
                        <div class="text-muted mt-1" id="manageModalSubtitle" style="font-size: 0.85rem;">
                             Subtitle will be set dynamically via JavaScript Modal_manage()
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        style="margin-top: -15px; margin-right: -10px;"></button>
                </div>


                <div class="modal-body" style="padding: 24px;">
                    <form id="formManageTask">
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold text-secondary" style="font-size: 0.95rem;">ระบบราชการ</span>
                                <span id="modalAccountCount" class="text-muted fw-semibold" style="font-size: 0.8rem;">- บัญชี</span>
                            </div>
                            <div id="modalAccountList" class="row g-3">
                                <div class="col-12 text-center text-muted py-3" style="font-size: 0.85rem;">กำลังโหลด...</div>
                            </div>
                        </div>

                       
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary"
                                    style="font-size: 0.85rem;">วันที่ได้รับเอกสาร</label>
                                <input type="text" class="form-control bg-light border-0 py-2 text-muted flatpickr-date"
                                    id="modal_doc_date" placeholder="วัน/เดือน/ปี" style="border-radius: 8px; font-size: 0.9rem;">
                            </div>
                            <div class="col-md-6 mt-3 mt-md-0">
                                <label class="form-label fw-semibold text-secondary"
                                    style="font-size: 0.85rem;">วันที่ทำเสร็จ</label>
                                <input type="text" class="form-control bg-light border-0 py-2 text-muted flatpickr-date"
                                    id="modal_completed_date" placeholder="วัน/เดือน/ปี" style="border-radius: 8px; font-size: 0.9rem;">
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label fw-semibold text-secondary mb-0"
                                    style="font-size: 0.85rem;">รายการงานประจำเดือน</label>
                                <span id="modalTaskCount" class="text-muted fw-semibold" style="font-size: 0.8rem;">-งาน</span>
                            </div>

                            <div id="modalTaskList" class="task-list-container"
                                style="max-height: 280px; overflow-y: auto; padding: 0 16px; border-radius: 10px; border: 1px solid #e2e8f0; background-color: #ffffff;">
                               
                            </div>
                        </div>

                       
                        <div class="row">
                           
                            <div class="col-md-6 pe-md-4 border-end">
                                <h6 class="fw-bold mb-3" style="font-size: 0.95rem; color: #334155;">การสอบทาน (Review)</h6>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-secondary" style="font-size: 0.85rem;">ผู้สอบทาน (รีวิว 1)</label>
                                    <select class="form-select bg-light border-0 py-2 text-muted fw-semibold modal-search-select"
                                        id="modal_review1_user_id"
                                        style="border-radius: 8px; font-size: 0.9rem;">

    <option value="">เลือกผู้สอบทาน</option>


    <?php foreach (($data['review_users'] ?? []) as $reviewUser): ?>
        <option value="<?php echo (int)$reviewUser['user_id']; ?>">
            <?php echo htmlspecialchars(
                trim(
                    $reviewUser['user_firstname'] . ' ' .
                    $reviewUser['user_lastname']
                )
            ); ?>
        </option>
    <?php endforeach; ?>

</select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-secondary" style="font-size: 0.85rem;">ผู้สอบทาน (รีวิว 2)</label>
                                    <select class="form-select bg-light border-0 py-2 text-muted fw-semibold modal-search-select"
    id="modal_review2_user_id"
    style="border-radius: 8px; font-size: 0.9rem;">

    <option value="">เลือกผู้สอบทาน</option>


    <?php foreach (($data['review_users'] ?? []) as $reviewUser): ?>
        <option value="<?php echo (int)$reviewUser['user_id']; ?>">
            <?php echo htmlspecialchars(
                trim(
                    $reviewUser['user_firstname'] . ' ' .
                    $reviewUser['user_lastname']
                )
            ); ?>
        </option>
    <?php endforeach; ?>

</select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-secondary" style="font-size: 0.85rem;">ผู้สอบทาน (รีวิว 3)</label>
                                    <select class="form-select bg-light border-0 py-2 text-muted fw-semibold modal-search-select"
    id="modal_review3_user_id"
    style="border-radius: 8px; font-size: 0.9rem;">

    <option value="">เลือกผู้สอบทาน</option>


    <?php foreach (($data['review_users'] ?? []) as $reviewUser): ?>
        <option value="<?php echo (int)$reviewUser['user_id']; ?>">
            <?php echo htmlspecialchars(
                trim(
                    $reviewUser['user_firstname'] . ' ' .
                    $reviewUser['user_lastname']
                )
            ); ?>
        </option>
    <?php endforeach; ?>

</select>
                                </div>
                            </div>

                           
                            <div class="col-md-6 ps-md-4 mt-4 mt-md-0">
                                <h6 class="fw-bold mb-3" style="font-size: 0.95rem; color: #334155;">สถานะเพิ่มเติม</h6>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-secondary" style="font-size: 0.85rem;">สถานะการเก็บเงิน</label>
                                    <select class="form-select bg-light border-0 py-2 text-muted fw-semibold modal-search-select" id="modal_payment_status" style="border-radius: 8px; font-size: 0.9rem;">
                                        <option value="0" selected>ยังไม่ได้รับ</option>
                                        <option value="1">ได้รับเงินแล้ว</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-secondary" style="font-size: 0.85rem;">สถานะการยื่นภาษี</label>
                                    <select class="form-select bg-light border-0 py-2 text-muted fw-semibold modal-search-select" id="modal_tax_status" style="border-radius: 8px; font-size: 0.9rem;">
                                        <option value="0" selected>ยังไม่ได้ยื่น</option>
                                        <option value="1">ยื่นแล้ว</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-secondary" style="font-size: 0.85rem;">วันที่ยื่นภาษี</label>
                                    <input type="text" class="form-control bg-light border-0 py-2 text-muted fw-semibold flatpickr-date" id="modal_tax_date" placeholder="วัน/เดือน/ปี" style="border-radius: 8px; font-size: 0.9rem;">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="modal-footer" style="padding: 16px 24px; border-top: 1px solid #e2e8f0;">
                    <button type="button" class="btn btn-light px-4 fw-semibold" data-bs-dismiss="modal"
                        style="border-radius: 8px; color: #475569; background-color: #f8fafc;">ยกเลิก</button>
                    <button type="button" class="btn btn-primary px-4 fw-semibold" onclick="saveManageTask()"
                        style="border-radius: 8px; background-color: #2563eb; border-color: #2563eb; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);">บันทึกข้อมูล</button>
                </div>
            </div>
        </div>
    </div> 
    -->

    <!-- Flatpickr JS & Thai locale -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>

    <script>
        $(document).ready(function () {
            if (typeof flatpickr !== 'undefined') {
                flatpickr('.flatpickr-date', {
                    dateFormat: 'd/m/Y',
                    locale: typeof flatpickr.l10ns !== 'undefined' && flatpickr.l10ns.th ? flatpickr.l10ns.th : 'default',
                    allowInput: true,
                    static: true,
                    disableMobile: true
                });
            }

            const urlParams = new URLSearchParams(window.location.search);
            const openPeriodId = urlParams.get('open_period_id');
            if (openPeriodId) {
                setTimeout(() => {
                    showTaskDetail(openPeriodId, 'เปิดจากหน้าประเด็นคงค้าง');
                    window.history.replaceState({}, document.title, window.location.pathname);
                }, 300); // small delay to let UI render first
            }

            $('#monthSelect').select2();

            $('#customerSelect').select2({
                placeholder: 'ค้นหาชื่อลูกค้า',
                width: '100%'
            });

            if (<?php echo !empty($data['is_customer_year_view']) ? 'true' : 'false'; ?>) {
                $('#monthFilterWrap').addClass('d-none');
            }

            $('#selUser').select2();
            $('#selDocument').select2();
            $('#selTask').select2();
            $('#selTax').select2();
            $('#selPayment').select2();

            $('.detail-search-select').select2({
                width: '100%',
                minimumResultsForSearch: 0
            });

            $('#monthSelect, #customerSelect, #selUser, #selDocument, #selTask, #selTax, #selPayment')
                .on('change', refreshMonthlyTaskTable);

            let searchTimer;
            $('#monthlyTaskSearch').on('input', function () {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(refreshMonthlyTaskTable, 300);
            });

            $('#customerSelect').on('change', function () {
                const hasCustomer = $(this).val() && $(this).val() !== 'all';
                $('#monthFilterWrap').toggleClass('d-none', hasCustomer);
                $('#monthSelect').prop('disabled', hasCustomer).trigger('change.select2');
                $('#exportCustomerYearButton').toggle(hasCustomer);
                $('#exportMonthlyButton').toggle(!hasCustomer);
            });

            window.GetData = function(page) {
                // Not actually paginating in backend right now, just refresh
                refreshMonthlyTaskTable();
            };

            function refreshMonthlyTaskTable() {
                const customerValue = $('#customerSelect').val();
                const params = new URLSearchParams({
                    month: customerValue === 'all' ? $('#monthSelect').val() : '',
                    customer_id: customerValue === 'all' ? '' : (customerValue || ''),
                    caretaker_id: $('#selUser').val() || '',
                    doc_status: $('#selDocument').val() || '',
                    task_status: $('#selTask').val() || '',
                    tax_status: $('#selTax').val() || '',
                    payment_status: $('#selPayment').val() || '',
                    keyword: $('#monthlyTaskSearch').val().trim(),
                    _t: Date.now()
                });

                $('#monthlyTaskTable').html(`
                    <div class="d-flex justify-content-center align-items-center py-5 text-muted">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        กำลังโหลดข้อมูล...
                    </div>
                `);

                fetch('<?php echo BASE_URL; ?>/monthly_task/filter?' + params.toString(), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status !== 'success') {
                            throw new Error(data.message || 'ไม่สามารถโหลดข้อมูลได้');
                        }

                        $('#monthlyTaskTable').html(data.html);
                        $('#statTotalCustomers').text(Number(data.stats.total_customers || 0).toLocaleString());
                        $('#statDocReceived').text(Number(data.stats.doc_received || 0).toLocaleString());
                        $('#statCompleted').text(Number(data.stats.completed || 0).toLocaleString());
                        $('#statTaxSubmitted').text(Number(data.stats.tax_submitted || 0).toLocaleString());
                        $('#statPaymentReceived').text(Number(data.stats.payment_received || 0).toLocaleString());
                    })
                    .catch(error => {
                        $('#monthlyTaskTable').html('<div class="text-center text-danger py-5">เกิดข้อผิดพลาดในการโหลดข้อมูล</div>');
                        console.error(error);
                    });
            }
        });
// ผูกครั้งเดียว ทำงานได้กับ select ที่ถูกสร้างใหม่ทีหลังด้วย (delegation)
document.addEventListener('change', function (e) {
    if (e.target.classList.contains('task-status-select')) {
        const select = e.target;
        const wrap = select.closest('.task-status-wrap');
        if (!wrap) return;
        const badge = wrap.querySelector('.task-status-badge');
        const isDone = select.value === '1';
        badge.textContent = isDone ? 'เสร็จแล้ว' : 'รอดำเนินการ';
        badge.classList.toggle('status-done', isDone);
        badge.classList.toggle('status-pending', !isDone);
    }
});

        function exportMonthlyTaskExcel(exportMode) {
            const customerValue = $('#customerSelect').val();
            const params = new URLSearchParams({
            export_mode: exportMode,
            month: exportMode === 'monthly' ? ($('#monthSelect').val() || '0') : '0',
            customer_id: exportMode === 'customer_year' ? (customerValue || '0') : '0',
                caretaker_id: $('#selUser').val() || '',
                doc_status: $('#selDocument').val() || '',
                task_status: $('#selTask').val() || '',
                tax_status: $('#selTax').val() || '',
                payment_status: $('#selPayment').val() || '',
                keyword: ($('#monthlyTaskSearch').val() || '').trim()
            });

            window.location.href = '<?php echo BASE_URL; ?>/monthly_task/export?' + params.toString();
        }

        let currentManagePeriodId = null;

        function showTaskDetail(period_id, subtitleStr) {
            currentManagePeriodId = period_id;
            
            $('#mainListContainer').addClass('d-none');
            $('#monthlyTaskDetailContainer').removeClass('d-none');

            if (subtitleStr) {
                const subtitleEl = document.getElementById('manageDetailSubtitle');
                if (subtitleEl) subtitleEl.textContent = subtitleStr;
            }

            // โหลด tasks จาก DB ตาม period_id
            const taskList = document.getElementById('detailTaskList');
            const taskCount = document.getElementById('detailTaskCount');
            const accountList = document.getElementById('detailAccountList');
            const accountCount = document.getElementById('detailAccountCount');

            taskList.innerHTML = '<div class="text-center text-muted py-3" style="font-size:0.85rem;"><i class="ri-loader-4-line"></i> กำลังโหลด...</div>';
            taskCount.textContent = '- งาน';
            accountList.innerHTML = '<div class="col-12 text-center text-muted py-3" style="font-size:0.85rem;"><i class="ri-loader-4-line"></i> กำลังโหลด...</div>';
            accountCount.textContent = '- บัญชี';

            if (!period_id) return;

            fetch('<?php echo BASE_URL; ?>/monthly_task/items?period_id=' + period_id)
                .then(res => res.json())
               .then(data => {
                renderCustomerAccounts(data.accounts || [], accountList, accountCount);

                // Populate modal fields with data.period
                if (data.period) {
                    const setDate = (id, ymd) => {
                        const el = document.getElementById(id);
                        if (!el) return;
                        if (!ymd || ymd === '0000-00-00') {
                            el.value = '';
                            if (el._flatpickr) el._flatpickr.clear();
                        } else {
                            const parts = ymd.split('-');
                            if (parts.length === 3) {
                                const dmy = parts[2] + '/' + parts[1] + '/' + parts[0];
                                el.value = dmy;
                                if (el._flatpickr) el._flatpickr.setDate(dmy);
                            }
                        }
                    };

                    setDate('detail_doc_date', data.period.doc_date);
                    setDate('detail_tax_date_1', data.period.tax_date_1);
                    setDate('detail_completed_date_1', data.period.completed_date_1);
                    setDate('detail_tax_date_2', data.period.tax_date_2);
                    setDate('detail_completed_date_2', data.period.completed_date_2);

                    $('#detail_review1_user_id').val(data.period.review1_user_id || '').trigger('change');
                    $('#detail_review2_user_id').val(data.period.review2_user_id || '').trigger('change');
                    $('#detail_review3_user_id').val(data.period.review3_user_id || '').trigger('change');
                    
                    $('#detail_payment_status').val(data.period.payment_status || '0').trigger('change');
                    $('#detail_tax_status').val(data.period.tax_status || '0').trigger('change');
                    $('#detail_doc_status').val(data.period.doc_status || '0').trigger('change');
                } else {
                    // clear fields if no period data
                    ['detail_doc_date', 'detail_tax_date_1', 'detail_completed_date_1', 'detail_tax_date_2', 'detail_completed_date_2'].forEach(id => {
                        const el = document.getElementById(id);
                        if (el) { el.value = ''; if(el._flatpickr) el._flatpickr.clear(); }
                    });
                    ['detail_review1_user_id', 'detail_review2_user_id', 'detail_review3_user_id'].forEach(id => {
                        const el = document.getElementById(id);
                        if (el) { el.value = ''; $(el).trigger('change'); }
                    });
                    ['detail_payment_status', 'detail_tax_status', 'detail_doc_status'].forEach(id => {
                        const el = document.getElementById(id);
                        if (el) { el.value = '0'; $(el).trigger('change'); }
                    });
                }


                if (data.result !== 1 || !data.tasks.length) {
         taskList.innerHTML = '<div class="text-center text-muted py-3" style="font-size:0.85rem;">ไม่พบรายการงาน</div>';
         taskCount.textContent = '0 งาน';
            return;
        }
    taskCount.textContent = data.tasks.length + ' งาน';
    let html = '';
    data.tasks.forEach((t, idx) => {
    const isLast = idx === data.tasks.length - 1;
    const isNotifyAmount = t.is_notify_amount == 1 || t.is_notify_amount === true;
    const hasComment = !!(t.comment && t.comment.trim() !== '');

   html += `
<div id="task-row-${t.customer_tasks_id}" class="d-flex align-items-center justify-content-center w-100 py-3 ${!isLast ? 'border-bottom' : ''}" style="${!isLast ? 'border-color: #f1f5f9 !important;' : ''}">

    <div class="d-flex align-items-center w-100" style="max-width: 760px;">

        <div class="d-flex align-items-center gap-2" style="flex: 1;">
            <span class="fw-bold" style="font-size:0.88rem; color:#1e293b;">${t.task_name}</span>
            ${isNotifyAmount ? '<span class="badge" style="background-color: #f3e8ff; color: #7c3aed; font-weight: 600; font-size: 0.73rem; padding: 4px 8px; border-radius: 6px;">ระบุจำนวนเงิน</span>' : ''}
        </div>

        <div class="d-flex justify-content-center align-items-center" style="flex: 0 0 auto; margin: 0 20px;">
            <button type="button" class="btn-task-comment position-relative"
                data-customer-tasks-id="${t.customer_tasks_id}"
                data-task-name="${t.task_name}"
                data-comment="${(t.comment || '').replace(/"/g, '&quot;')}"
                title="เพิ่มความคิดเห็น"
                style="width: 32px; height: 32px; border-radius: 8px; border: 1px solid ${hasComment ? '#93c5fd' : '#e2e8f0'}; background-color: ${hasComment ? '#eff6ff' : '#ffffff'}; color: ${hasComment ? '#2563eb' : '#94a3b8'}; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.15s ease; flex-shrink: 0;">
                <i class="ri-chat-3-line" style="font-size: 15px;"></i>
                ${(t.unread_comments && t.unread_comments > 0) ? `<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem; padding: 3px 5px; transform: translate(-30%, -30%) !important;">${t.unread_comments}</span>` : ''}
            </button>
        </div>

        <div class="d-flex align-items-center justify-content-end gap-2" style="flex: 1;">
            ${isNotifyAmount ? `
                <div class="d-flex align-items-center gap-2">
                    <input class="form-check-input flex-shrink-0 task-amount-checkbox" type="checkbox" data-customer-tasks-id="${t.customer_tasks_id}" style="cursor: pointer; width: 30px !important; height: 30px !important; margin: 0; border-radius: 3px !important; margin-top: 0;">
                    <input type="number" class="form-control form-control-sm bg-light border-0 text-muted flex-shrink-0 task-amount-input" data-customer-tasks-id="${t.customer_tasks_id}" placeholder="จำนวนเงิน" value="${(t.amount && t.amount > 0) ? Number(t.amount) : ''}" style="width: 100px; border-radius: 3px !important; height: 30px !important; padding: 7px 12px; font-size: 0.85rem;" oninput="if(this.value && this.value > 0){this.parentElement.nextElementSibling.value='1';}">
                </div>
            ` : ''}

            <div class="task-status-wrap" style="width: 130px; flex-shrink: 0;">
                <span class="task-status-badge ${t.status === '1' ? 'status-done' : 'status-pending'}">
                    ${t.status === '1' ? 'เสร็จแล้ว' : 'รอดำเนินการ'}
                </span>
                <select class="task-status-select"
                        data-customer-tasks-id="${t.customer_tasks_id}">
                    <option value="0" ${t.status !== '1' ? 'selected' : ''}>รอดำเนินการ</option>
                    <option value="1" ${t.status === '1' ? 'selected' : ''}>เสร็จแล้ว</option>
                </select>
            </div>
        </div>

    </div>
</div>`;
});
    taskList.innerHTML = html;

    // Summary Logic
    const updateSummary = () => {
        let sum = 0;
        const allChecked = document.querySelectorAll('.task-amount-checkbox:checked');
        let bottomMostElement = null;

        if (allChecked.length > 0) {
            const lastCb = allChecked[allChecked.length - 1];
            bottomMostElement = document.getElementById(`task-row-${lastCb.dataset.customerTasksId}`);
            
            allChecked.forEach(cb => {
                const taskId = cb.dataset.customerTasksId;
                const input = document.querySelector(`.task-amount-input[data-customer-tasks-id="${taskId}"]`);
                if (input && input.value) {
                    sum += parseFloat(input.value) || 0;
                }
            });
        }

        let summaryRow = document.getElementById('taskAmountSummaryRow');
        
        if (bottomMostElement) {
            if (!summaryRow) {
                summaryRow = document.createElement('div');
                summaryRow.id = 'taskAmountSummaryRow';
                summaryRow.className = 'd-flex align-items-center w-100 py-2 px-3 rounded mb-2 mt-2';
                summaryRow.style.backgroundColor = '#e0f2fe';
                summaryRow.style.border = '1px dashed #7dd3fc';
                summaryRow.innerHTML = `
                    <div class="fw-bold text-center w-100" style="font-size: 0.95rem; color: #0284c7;">
                        รวมยอดที่เลือก: <span id="taskAmountSummaryValue" style="color: #0369a1; font-size: 1.1rem; margin-left: 5px;">0.00</span> บาท
                    </div>
                `;
            }
            bottomMostElement.parentNode.insertBefore(summaryRow, bottomMostElement.nextSibling);
            document.getElementById('taskAmountSummaryValue').textContent = sum.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        } else if (summaryRow) {
            summaryRow.remove();
        }
    };

    document.querySelectorAll('.task-amount-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            updateSummary();
        });
    });

    document.querySelectorAll('.task-amount-input').forEach(input => {
        input.addEventListener('input', function() {
            const cb = document.querySelector(`.task-amount-checkbox[data-customer-tasks-id="${this.dataset.customerTasksId}"]`);
            if (cb && cb.checked) {
                updateSummary();
            }
        });
    });

    document.querySelectorAll('.btn-task-comment').forEach(btn => {
        btn.addEventListener('click', function () {
            const taskId = this.dataset.customerTasksId;
            const taskName = this.dataset.taskName || 'ความคิดเห็น';
            toggleCommentThread(taskId, taskName, this);
        });
    });
        })
        .catch(() => {
            taskList.innerHTML = '<div class="text-center text-danger py-3" style="font-size:0.85rem;">เกิดข้อผิดพลาดในการโหลดข้อมูล</div>';
            accountList.innerHTML = '<div class="col-12 text-center text-danger py-3" style="font-size:0.85rem;">ไม่สามารถโหลดบัญชีได้</div>';
            accountCount.textContent = '0 บัญชี';
        });
    }

    function renderCustomerAccounts(accounts, accountList, accountCount) {
        const accountRows = accounts.length ? accounts : [{
            account_name: 'ไม่ระบุ',
            account_user_name: 'ไม่ระบุ',
            account_password: 'ไม่ระบุ'
        }];

        accountCount.textContent = accountRows.length + ' บัญชี';
        accountList.innerHTML = accountRows.map((account, index) => `
            <div class="col-md-6 col-lg-6">
                <div class="p-2 rounded d-flex justify-content-between align-items-center" style="background-color: #ffffff; border: 1px solid #e2e8f0; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: all 0.2s ease;">
                    
                    <!--  left -->
                    <div class="text-end flex-shrink-0 pe-2" style="color: #e2e8f0;">
                        <i class="ri-shield-user-line" style="font-size: 72px; line-height: 1;"></i>
                    </div>
                    

                    <!--  right -->
                    

                    <div class="pe-4 flex-grow-1">
                        <h6 class="mb-3">${escapeHtml(account.account_name || 'ไม่ระบุ')}</h6>
                        
                        <div class="mb-2">
                            <span class="d-block" style="font-size: 0.75rem;">USERNAME : ${escapeHtml(account.account_user_name || '-')}</span>
                        </div>
                        
                        <div>
                            <span class="d-block" style="font-size: 0.75rem;">PASSWORD : ${escapeHtml(account.account_password || '-')}</span>
                        </div>  
                    </div>
                </div>
            </div>
        `).join('');
    }

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>'"]/g, character => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'
        }[character]));
    }

    function toggleCommentThread(customerTasksId, taskName, triggerElement) {
    const row = triggerElement.closest('.d-flex.align-items-center.w-100');
    const existing = row.nextElementSibling;
    const icon = triggerElement.querySelector('i');

    if (existing && existing.classList.contains('comment-thread-panel')) {
        existing.style.opacity = '0';
        existing.style.transform = 'translateY(-6px)';
        setTimeout(() => existing.remove(), 180);

        if (icon) icon.className = 'ri-chat-3-line';
        return;
    }

    document.querySelectorAll('.comment-thread-panel').forEach(el => el.remove());
    document.querySelectorAll('.btn-task-comment i').forEach(i => {
        i.className = 'ri-chat-3-line';
    });

    if (icon) icon.className = 'ri-arrow-up-s-line';

    const panelHtml = `
        <div class="comment-thread-panel" style="opacity: 0; transform: translateY(-6px); transition: opacity 0.2s ease, transform 0.2s ease;">
            <div class="d-flex align-items-center gap-2 mb-3">
                <div style="width:26px; height:26px; border-radius:50%; background:#2563eb; display:flex; align-items:center; justify-content:center;">
                    <i class="ri-chat-3-fill" style="color:#fff; font-size:0.75rem;"></i>
                </div>
                <span style="font-size:0.82rem; font-weight:800; color:#1e3a8a;">${escapeHtml(taskName)}</span>
                
            </div>
            <div class="comment-thread-list">
                <div class="text-center text-muted py-3" style="font-size:0.8rem;"><i class="ri-loader-4-line"></i> กำลังโหลด...</div>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <input type="text" class="form-control comment-thread-input" 
                    placeholder="พิมพ์ความคิดเห็น..." 
                    style="border-radius:24px; font-size:0.85rem; background:#ffffff; border:1.5px solid #dbeafe; padding: 10px 18px; box-shadow: inset 0 1px 2px rgba(0,0,0,0.02);">
                <button type="button" class="comment-thread-send" 
                    style="width:42px; height:42px; flex-shrink:0; border-radius:50%; border:none; background: linear-gradient(135deg, #3b82f6, #2563eb); color:#fff; display:flex; align-items:center; justify-content:center; cursor:pointer; box-shadow: 0 3px 10px rgba(37,99,235,0.35); transition: transform 0.15s ease, box-shadow 0.15s ease;">
                    <i class="ri-send-plane-fill" style="font-size:1rem;"></i>
                </button>
            </div>
        </div>
    `;

    row.insertAdjacentHTML('afterend', panelHtml);
    const panel = row.nextElementSibling;
    const listEl = panel.querySelector('.comment-thread-list');
    const inputEl = panel.querySelector('.comment-thread-input');
    const sendBtn = panel.querySelector('.comment-thread-send');
    const countBadge = panel.querySelector('.comment-count-badge');

    sendBtn.addEventListener('mouseenter', () => {
        sendBtn.style.transform = 'scale(1.08)';
        sendBtn.style.boxShadow = '0 5px 14px rgba(37,99,235,0.45)';
    });
    sendBtn.addEventListener('mouseleave', () => {
        sendBtn.style.transform = 'scale(1)';
        sendBtn.style.boxShadow = '0 3px 10px rgba(37,99,235,0.35)';
    });

    requestAnimationFrame(() => {
        panel.style.opacity = '1';
        panel.style.transform = 'translateY(0)';
    });

    loadComments(customerTasksId, listEl, countBadge);

    function submitComment() {
        const text = inputEl.value.trim();
        if (!text) return;
        sendBtn.disabled = true;
        sendBtn.style.opacity = '0.6';

        postComment(customerTasksId, text)
            .then(() => {
                inputEl.value = '';
                loadComments(customerTasksId, listEl, countBadge);
                updateCommentBadge(triggerElement, true);
            })
            .catch(() => alert('ส่งความคิดเห็นไม่สำเร็จ'))
            .finally(() => {
                sendBtn.disabled = false;
                sendBtn.style.opacity = '1';
            });
    }

    sendBtn.addEventListener('click', submitComment);
    inputEl.addEventListener('keydown', e => {
        if (e.key === 'Enter') { e.preventDefault(); submitComment(); }
    });
    inputEl.focus();
}

function loadComments(customerTasksId, listEl, countBadge) {
    listEl.innerHTML = '<div class="text-center text-muted py-3" style="font-size:0.8rem;"><i class="ri-loader-4-line"></i> กำลังโหลด...</div>';

    fetch('<?php echo BASE_URL; ?>/monthly_task/comments?customer_tasks_id=' + customerTasksId)
        .then(res => res.json())
        .then(data => {
            const comments = data.comments || [];
            if (countBadge) countBadge.textContent = comments.length + ' ข้อความ';

            if (!comments.length) {
                listEl.innerHTML = `
                    <div class="text-center py-4">
                        <i class="ri-chat-smile-2-line" style="font-size:1.8rem; display:block; margin-bottom:6px; color:#93c5fd;"></i>
                        <span style="font-size:0.8rem; color:#94a3b8; font-weight:500;">ยังไม่มีความคิดเห็น</span>
                    </div>`;
                return;
            }
            let previousDay = '';
            listEl.innerHTML = comments.map(c => {
                const dayKey = String(c.create_at || '').slice(0, 10) || String(c.created_at_display || '').slice(0, 10);
                const dayLabel = formatCommentDate(c.create_at, c.created_at_display);
                const isMine = String(c.comment_user_id) === String(MONTHLY_TASK_CURRENT_USER_ID);
                const dayDivider = dayKey !== previousDay
                    ? `<div class="comment-day-divider"><span>${escapeHtml(dayLabel)}</span></div>`
                    : '';
                previousDay = dayKey;

                return `${dayDivider}
                <div class="comment-message ${isMine ? 'is-mine' : ''}">
                    <span class="comment-author">${escapeHtml(c.user_name || 'ไม่ระบุ')}</span>
                    <div class="comment-content-row">
                        <div class="comment-bubble">
                            <div class="comment-body">${escapeHtml(c.comment_text || '')}</div>
                        </div>
                    </div>
                </div>`;
            }).join('');
            listEl.scrollTop = listEl.scrollHeight;
        })
        .catch(() => {
            listEl.innerHTML = '<div class="text-center text-dark py-3" style="font-size:0.8rem;">ยังไม่มีข้อมูล</div>';
        });
}

const MONTHLY_TASK_CURRENT_USER_ID = <?php echo json_encode((string) ($data['user_id'] ?? '')); ?>;

function formatCommentDate(rawDate, fallback) {
    if (!rawDate) return fallback || 'ไม่ระบุวันที่';
    const date = new Date(String(rawDate).replace(' ', 'T'));
    if (Number.isNaN(date.getTime())) return fallback || 'ไม่ระบุวันที่';
    return date.toLocaleDateString('th-TH', { day: 'numeric', month: 'long', year: 'numeric' });
}

function formatCommentTime(rawDate, fallback) {
    if (!rawDate) return fallback || '';
    const timeMatch = String(rawDate).match(/\b(\d{2}):(\d{2})(?::\d{2})?\b/);
    if (!timeMatch) return fallback || '';
    return `${timeMatch[1]}:${timeMatch[2]} น.`;
}

function postComment(customerTasksId, text) {
    return fetch('<?php echo BASE_URL; ?>/monthly_task/comments/store', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            customer_tasks_id: customerTasksId, 
            comment_text: text,
            is_reply: 1 
        })
    }).then(res => res.json());
}

function updateCommentBadge(button, hasComment) {
    button.style.borderColor = hasComment ? '#93c5fd' : '#e2e8f0';
    button.style.backgroundColor = hasComment ? '#eff6ff' : '#ffffff';
    button.style.color = hasComment ? '#2563eb' : '#94a3b8';
    const icon = button.querySelector('i');
    if (icon && !icon.className.includes('ri-arrow-up')) {
        icon.style.color = hasComment ? '#2563eb' : '#94a3b8';
    }
}

function hideTaskDetail() {
    $('#monthlyTaskDetailContainer').addClass('d-none');
    $('#mainListContainer').removeClass('d-none');
}

function saveTaskDetail() {
    if (!currentManagePeriodId) return;

    const getVal = (id) => { const el = document.getElementById(id); return el ? el.value : ''; };

    const payload = {
    period_id: currentManagePeriodId,

    doc_date: getVal('detail_doc_date'),
    tax_date_1: getVal('detail_tax_date_1'),
    completed_date_1: getVal('detail_completed_date_1'),
    tax_date_2: getVal('detail_tax_date_2'),
    completed_date_2: getVal('detail_completed_date_2'),

    // Review Status
    review1_status: getVal('detail_review1_user_id') ? '1' : '0',
    review2_status: getVal('detail_review2_user_id') ? '1' : '0',
    review3_status: getVal('detail_review3_user_id') ? '1' : '0',

    // Review User ID
    review1_user_id: document.getElementById('detail_review1_user_id').value || null,
    review2_user_id: document.getElementById('detail_review2_user_id').value || null,
    review3_user_id: document.getElementById('detail_review3_user_id').value || null,

    payment_status: document.getElementById('detail_payment_status').value,
    tax_status: document.getElementById('detail_tax_status').value,
    doc_status: document.getElementById('detail_doc_status').value,

    tasks: []
};

    // Collect tasks data
    const taskSelects = document.querySelectorAll('.task-status-select');
    taskSelects.forEach(select => {
        const ctid = select.getAttribute('data-customer-tasks-id');
        const status = select.value;
       const amountInput = document.querySelector(`.task-amount-input[data-customer-tasks-id="${ctid}"]`);
const amount = (amountInput && amountInput.value !== '') ? amountInput.value : 0;
        payload.tasks.push({
            customer_tasks_id: ctid,
            status: status,
            amount: amount
        });
    });

    Swal.fire({
        title: 'กำลังบันทึก...',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    fetch('<?php echo BASE_URL; ?>/monthly_task/update', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'บันทึกสำเร็จ',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                hideTaskDetail();
                if (typeof window.GetData === 'function') {
                    window.GetData();
                } else if (typeof refreshMonthlyTaskTable === 'function') {
                    refreshMonthlyTaskTable();
                } else {
                    location.reload();
                }
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด',
                text: data.message || 'ไม่สามารถบันทึกได้'
            });
        }
    })
    .catch(err => {
        Swal.fire({
            icon: 'error',
            title: 'ข้อผิดพลาดระบบ',
            text: err.message
        });
    });
}


    </script>

    <?php
    require_once dirname(__DIR__) . '/main/footer.php';
    ?>