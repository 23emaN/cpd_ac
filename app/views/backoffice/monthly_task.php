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
</style>

<div class="container-fluid">
    <div class="main-content d-flex flex-column">
        <div class="content-wrapper">
            <div class="main-page-wrapper">

                <div class="main-card-wrapper">

                    <!-- Page Header Section -->
                    <div class="page-header-box">
                        <div>
                            <h2 class="page-title">จัดการงานรายเดือน</h2>
                            <?php $fy_display = !empty($data['active_fiscal_year']) ? $data['active_fiscal_year'] : 'ไม่ได้เลือกปี'; ?>
                            <p class="page-subtitle">ภาพรวมระบบ - งานรายเดือน - ปี
                                <?php echo htmlspecialchars($fy_display); ?>
                            </p>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn-excel-action">
                                <i class="ri-upload-2-line"></i>
                                <span>ส่งออก Excel</span>
                            </button>
                        </div>
                    </div>

                    <!-- Stats Grid -->
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
                                <span class="stat-val">1</span>
                                <span class="stat-label">ได้รับเอกสาร</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon purple">
                                <i class="ri-checkbox-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val">1</span>
                                <span class="stat-label">งานเสร็จแล้ว</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon yellow">
                                <i class="ri-mail-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val">1</span>
                                <span class="stat-label">ยื่นภาษีแล้ว</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon green">
                                <i class="ri-wallet-3-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val">1</span>
                                <span class="stat-label">ได้รับเงินแล้ว</span>
                            </div>
                        </div>
                    </div>

                    <!-- Month Selector Row (Bootstrap Utility Classes) -->
                    <div class="d-flex align-items-center gap-2 my-4">
                        <span class=" me-1">เลือกเดือน:</span>
                        <div class="w-auto">
                            <select class="form-select" id="monthSelect">
                                <?php
                                $selectedMonth = (int) ($data['selected_month'] ?? date('n'));
                                $months = [
                                    1 => 'มกราคม',
                                    2 => 'กุมภาพันธ์',
                                    3 => 'มีนาคม',
                                    4 => 'เมษายน',
                                    5 => 'พฤษภาคม',
                                    6 => 'มิถุนายน',
                                    7 => 'กรกฎาคม',
                                    8 => 'สิงหาคม',
                                    9 => 'กันยายน',
                                    10 => 'ตุลาคม',
                                    11 => 'พฤศจิกายน',
                                    12 => 'ธันวาคม',
                                ];
                                foreach ($months as $num => $name) {
                                    $isSelected = ($num === $selectedMonth) ? 'selected' : '';
                                    echo "<option value=\"$num\" $isSelected>$name</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <!-- Filter Toolbar (ค้นหา & ตัวกรองสถานะ) -->
                    <div class="filter-toolbar mb-3">
                        <div class="search-box-wrap">
                            <i class="ri-search-line"></i>
                            <input type="text" class="search-input" placeholder="ค้นหาชื่อลูกค้า ผู้ดูแล ทีม">
                        </div>

                        <div class="filter-group mb-4">
                            <?php
                            $users = ['เมย์', 'ชมพู่', 'นิว'];
                            ?>
                            <select class="form-select filter-select" id="selUser">
                                <option value="">ทุกผู้ดูแล</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?php echo htmlspecialchars($user); ?>">
                                        <?php echo htmlspecialchars($user); ?>
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

                    <?php include 'table/mounthly_task.php'; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="manageModal" tabindex="-1" aria-labelledby="manageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content"
                style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <div class="modal-header" style="padding: 20px 24px; border-bottom: 1px solid #e2e8f0;">
                    <div>
                        <h5 class="modal-title fw-bold" id="manageModalLabel"
                            style="color: #1e293b; font-size: 1.15rem;">อัปเดตงานรายเดือน</h5>
                        <div class="text-muted mt-1" id="manageModalSubtitle" style="font-size: 0.85rem;">
                            <?php echo htmlspecialchars($task['customer_name']); ?>
                            <?php echo htmlspecialchars($task['month_year']); ?>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        style="margin-top: -15px; margin-right: -10px;"></button>
                </div>

                <div class="modal-body" style="padding: 24px;">
                    <form id="formManageTask">
                        <!-- Row 1: Dates -->
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
                                <!-- Task items will be populated by JS -->
                            </div>
                        </div>


                        <!-- Row 3: Reviewer & Payment -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary"
                                    style="font-size: 0.85rem;">ผู้สอบทาน (รีวิว 1)</label>
                                <select class="form-select bg-light border-0 py-2 text-muted fw-semibold"
                                    style="border-radius: 8px; font-size: 0.9rem;">
                                    <option value="" selected>ยังไม่ได้รีวิว</option>
                                    <option value="1">ชมพู่</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary"
                                    style="font-size: 0.85rem;">ผู้สอบทาน (รีวิว2)</label>
                                <select class="form-select bg-light border-0 py-2 text-muted fw-semibold"
                                    style="border-radius: 8px; font-size: 0.9rem;">
                                    <option value="" selected>ยังไม่ได้รีวิว</option>
                                    <option value="1">ชมพู่</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary"
                                    style="font-size: 0.85rem;">ผู้สอบทาน (รีวิว3)</label>
                                <select class="form-select bg-light border-0 py-2 text-muted fw-semibold"
                                    style="border-radius: 8px; font-size: 0.9rem;">
                                    <option value="" selected>ยังไม่ได้รีวิว</option>
                                    <option value="1">ชมพู่</option>
                                </select>
                            </div>

                        </div>

                        <!-- Row 4: Tax Status & Date -->
                        <div class="row">
                            <div class="col-md-6 mt-3 mt-md-0">
                                <label class="form-label fw-semibold text-secondary"
                                    style="font-size: 0.85rem;">สถานะการเก็บเงิน</label>
                                <select class="form-select bg-light border-0 py-2 text-muted fw-semibold"
                                    style="border-radius: 8px; font-size: 0.9rem;">
                                    <option value="0" selected>ยังไม่ได้รับ</option>
                                    <option value="1">ได้รับเงินแล้ว</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary"
                                    style="font-size: 0.85rem;">สถานะการยื่นภาษี</label>
                                <select class="form-select bg-light border-0 py-2 text-muted fw-semibold"
                                    style="border-radius: 8px; font-size: 0.9rem;">
                                    <option value="0" selected>ยังไม่ได้ยื่น</option>
                                    <option value="1">ยื่นแล้ว</option>
                                </select>
                            </div>
                            <div class="col-md-6 mt-3 mt-md-3">
                                <label class="form-label fw-semibold text-secondary"
                                    style="font-size: 0.85rem;">วันที่ยื่นภาษี</label>
                                <input type="text" class="form-control bg-light border-0 py-2 text-muted fw-semibold flatpickr-date"
                                    id="modal_tax_date" placeholder="วัน/เดือน/ปี" style="border-radius: 8px; font-size: 0.9rem;">
                            </div>
                        </div>
                    </form>
                </div>

                <div class="modal-footer" style="padding: 16px 24px; border-top: 1px solid #e2e8f0;">
                    <button type="button" class="btn btn-light px-4 fw-semibold" data-bs-dismiss="modal"
                        style="border-radius: 8px; color: #475569; background-color: #f8fafc;">ยกเลิก</button>
                    <button type="button" class="btn btn-primary px-4 fw-semibold"
                        style="border-radius: 8px; background-color: #2563eb; border-color: #2563eb; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);">บันทึกข้อมูล</button>
                </div>
            </div>
        </div>
    </div>

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

            $('#monthSelect').select2().on('change', function () {
                var selectedMonth = $(this).val();
                // รีเฟรชหน้าและส่ง param month ไปทาง URL
                window.location.href = '<?php echo BASE_URL; ?>/monthly_task?month=' + selectedMonth;
            });

            $('#selUser').select2();
            $('#selDocument').select2();
            $('#selTask').select2();
            $('#selTax').select2();
            $('#selPayment').select2();
        });

        function Modal_manage(period_id, subtitleStr) {
            const modalElement = document.getElementById('manageModal');
            const myModal = new bootstrap.Modal(modalElement);

            if (subtitleStr) {
                document.getElementById('manageModalSubtitle').textContent = subtitleStr;
            }

            myModal.show();

            // โหลด tasks จาก DB ตาม period_id
            const taskList = document.getElementById('modalTaskList');
            const taskCount = document.getElementById('modalTaskCount');

            taskList.innerHTML = '<div class="text-center text-muted py-3" style="font-size:0.85rem;"><i class="ri-loader-4-line"></i> กำลังโหลด...</div>';
            taskCount.textContent = '- งาน';

            if (!period_id) return;

            fetch('<?php echo BASE_URL; ?>/monthly_task/items?period_id=' + period_id)
                .then(res => res.json())
               .then(data => {
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
        <div class="d-flex justify-content-between align-items-center w-100 py-3 ${!isLast ? 'border-bottom' : ''}" style="${!isLast ? 'border-color: #f1f5f9 !important;' : ''}">
            <!-- Left side: Task Name & Badge -->
            <div class="d-flex align-items-center gap-2">
                <span class="fw-bold" style="font-size:0.88rem; color:#1e293b;">${t.task_name}</span>
                ${isNotifyAmount ? '<span class="badge" style="background-color: #f3e8ff; color: #7c3aed; font-weight: 600; font-size: 0.73rem; padding: 4px 8px; border-radius: 6px;">ระบุจำนวนเงิน</span>' : ''}
            </div>

            <!-- Middle: Comment Button -->
            <button type="button" class="btn-task-comment"
                    data-customer-tasks-id="${t.customer_tasks_id}"
                    data-comment="${(t.comment || '').replace(/"/g, '&quot;')}"
                    title="เพิ่มความคิดเห็น"
                    style="width: 32px; height: 32px; border-radius: 8px; border: 1px solid ${hasComment ? '#93c5fd' : '#e2e8f0'}; background-color: ${hasComment ? '#eff6ff' : '#ffffff'}; color: ${hasComment ? '#2563eb' : '#94a3b8'}; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.15s ease; flex-shrink: 0;">
                <i class="ri-chat-3-line" style="font-size: 15px;"></i>
            </button>

            <!-- Right side: Select dropdown & Amount Input -->
            <div class="d-flex align-items-center gap-2">
                <select class="form-select-sm bg-light border-0 fw-semibold text-secondary flex-shrink-0"
                        data-customer-tasks-id="${t.customer_tasks_id}"
                        style="border-radius: 8px; padding: 7px 12px; font-size: 0.85rem;width:130px;">
                    <option value="0" ${t.status !== '1' ? 'selected' : ''}>รอดำเนินการ</option>
                    <option value="1" ${t.status === '1' ? 'selected' : ''}>เสร็จแล้ว</option>
                </select>
                ${isNotifyAmount ? `<input type="number" class="form-control form-control-sm bg-light border-0 text-muted flex-shrink-0" placeholder="จำนวนเงิน" value="${t.amount || ''}" style="width: 130px; border-radius: 8px; padding: 7px 12px; font-size: 0.85rem;">` : ''}
            </div>
        </div>`;
    });
    taskList.innerHTML = html;

            // Bind comment button click event after rendering
            document.querySelectorAll('.btn-task-comment').forEach(btn => {
                btn.addEventListener('click', function () {
                    const taskId = this.dataset.customerTasksId;
                    toggleCommentThread(taskId, this);
                });
            });
        })
        .catch(() => {
            taskList.innerHTML = '<div class="text-center text-danger py-3" style="font-size:0.85rem;">เกิดข้อผิดพลาดในการโหลดข้อมูล</div>';
        });
    }

    function toggleCommentThread(customerTasksId, triggerElement) {
        const row = triggerElement.closest('.d-flex.justify-content-between');
        const existing = row.nextElementSibling;

        // ถ้า thread ของแถวนี้เปิดอยู่แล้ว ให้ปิด (toggle)
        if (existing && existing.classList.contains('comment-thread-panel')) {
            existing.remove();
            return;
        }

        // ปิด thread ของแถวอื่นที่เปิดค้างอยู่ (เปิดได้ทีละแถว)
        document.querySelectorAll('.comment-thread-panel').forEach(el => el.remove());

        const panelHtml = `
            <div class="comment-thread-panel" style="padding: 12px 0 16px; border-bottom: 1px dashed #f1f5f9; background:#fafbfc;">
                <div class="comment-thread-list" style="max-height: 200px; overflow-y:auto; margin-bottom: 10px; padding: 0 12px;">
                    <div class="text-center text-muted py-2" style="font-size:0.8rem;"><i class="ri-loader-4-line"></i> กำลังโหลด...</div>
                </div>
                <div class="d-flex gap-2" style="padding: 0 12px;">
                    <input type="text" class="form-control form-control-sm comment-thread-input" 
                        placeholder="แสดงความคิดเห็น..." 
                        style="border-radius:8px; font-size:0.84rem; background:#ffffff; border:1px solid #e2e8f0;">
                    <button type="button" class="btn btn-sm btn-primary comment-thread-send" style="border-radius:8px; font-size:0.84rem; flex-shrink:0;">ส่ง</button>
                </div>
            </div>
        `;

        row.insertAdjacentHTML('afterend', panelHtml);
        const panel = row.nextElementSibling;
        const listEl = panel.querySelector('.comment-thread-list');
        const inputEl = panel.querySelector('.comment-thread-input');
        const sendBtn = panel.querySelector('.comment-thread-send');

        loadComments(customerTasksId, listEl);

        function submitComment() {
            const text = inputEl.value.trim();
            if (!text) return;
            sendBtn.disabled = true;

            postComment(customerTasksId, text)
                .then(() => {
                    inputEl.value = '';
                    loadComments(customerTasksId, listEl);
                    updateCommentBadge(triggerElement, true);
                })
                .catch(() => alert('ส่งความคิดเห็นไม่สำเร็จ'))
                .finally(() => { sendBtn.disabled = false; });
        }

        sendBtn.addEventListener('click', submitComment);
        inputEl.addEventListener('keydown', e => {
            if (e.key === 'Enter') { e.preventDefault(); submitComment(); }
        });
    }

    function loadComments(customerTasksId, listEl) {
        listEl.innerHTML = '<div class="text-center text-muted py-2" style="font-size:0.8rem;"><i class="ri-loader-4-line"></i> กำลังโหลด...</div>';

        fetch('<?php echo BASE_URL; ?>/monthly_task/comments?customer_tasks_id=' + customerTasksId)
            .then(res => res.json())
            .then(data => {
                if (!data.comments || !data.comments.length) {
                    listEl.innerHTML = '<div class="text-center text-muted py-2" style="font-size:0.8rem;">ยังไม่มีความคิดเห็น</div>';
                    return;
                }
                listEl.innerHTML = data.comments.map(c => `
                    <div class="d-flex gap-2 mb-2">
                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center flex-shrink-0" 
                             style="width:26px; height:26px; font-size:0.7rem; font-weight:700; color:#64748b;">
                            ${(c.user_name || '?').charAt(0).toUpperCase()}
                        </div>
                        <div class="flex-grow-1" style="background:#fff; border:1px solid #f1f5f9; border-radius:10px; padding:6px 10px;">
                            <div class="d-flex justify-content-between">
                                <span style="font-size:0.78rem; font-weight:700; color:#334155;">${c.user_name || 'ไม่ระบุ'}</span>
                                <span style="font-size:0.7rem; color:#94a3b8;">${c.created_at_display || ''}</span>
                            </div>
                            <div style="font-size:0.82rem; color:#475569;">${c.comment_text}</div>
                        </div>
                    </div>
                `).join('');
                listEl.scrollTop = listEl.scrollHeight;
            })
            .catch(() => {
                listEl.innerHTML = '<div class="text-center text-danger py-2" style="font-size:0.8rem;">โหลดความคิดเห็นไม่สำเร็จ</div>';
            });
    }

    function postComment(customerTasksId, text) {
        return fetch('<?php echo BASE_URL; ?>/monthly_task/comments/store', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ customer_tasks_id: customerTasksId, comment_text: text })
        }).then(res => res.json());
    }

    function updateCommentBadge(button, hasComment) {
        button.style.borderColor = hasComment ? '#93c5fd' : '#e2e8f0';
        button.style.backgroundColor = hasComment ? '#eff6ff' : '#ffffff';
        button.style.color = hasComment ? '#2563eb' : '#94a3b8';
        const icon = button.querySelector('i');
        if (icon) icon.style.color = hasComment ? '#2563eb' : '#94a3b8';
    }
    </script>

    <?php
    // 3. นำ Footer เข้ามา
    require_once dirname(__DIR__) . '/main/footer.php';
    ?>