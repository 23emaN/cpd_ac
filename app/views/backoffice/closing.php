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
        font-family: 'Kanit', "Segoe UI", Tahoma, sans-serif;
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

    /* Fix Flatpickr wrapper width inside flex containers */
    .flatpickr-wrapper {
        display: block !important;
        width: 100% !important;
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
    .filter-container {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-bottom: 24px;
        width: 100%;
    }

    .search-box-wrap {
        position: relative;
        width: 100%;
        max-width: 320px;
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
        padding: 8px 12px 8px 36px;
        font-size: 0.82rem;
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
        flex-wrap: nowrap;
    }

    @media (max-width: 1200px) {
        .filter-group {
            flex-wrap: wrap;
        }
    }

    .filter-group .select2-container {
        flex: 1 1 0 !important;
        min-width: 0 !important;
        width: 100% !important;
    }

    /* Select2 Pill Design matching screenshot */
    .filter-group .select2-container--default .select2-selection--single {
        
        border: 1px solid #f1f5f9 !important;
        border-radius: 10px !important;
        height: 38px !important;
        display: flex !important;
        align-items: center !important;
        transition: all 0.2s ease !important;
        box-shadow: none !important;
    }

    .filter-group .select2-container--default .select2-selection--single:focus,
    .filter-group .select2-container--default.select2-container--open .select2-selection--single {
        background-color: #ffffff !important;
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
    }

    .filter-group .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #1e293b !important;
        font-size: 0.82rem !important;
        font-weight: 600 !important;
        padding-left: 12px !important;
        padding-right: 28px !important;
        line-height: 36px !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }

    .filter-group .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
        width: 24px !important;
        right: 6px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    .filter-group .select2-container--default .select2-selection--single .select2-selection__arrow b {
        border-color: #64748b transparent transparent transparent !important;
        border-width: 5px 4px 0 4px !important;
    }

    .filter-group .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
        border-color: transparent transparent #64748b transparent !important;
        border-width: 0 4px 5px 4px !important;
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
                            <h4 class="page-title">ปิดงบการเงิน</h2>
                                <p class="page-subtitle">ภาพรวม - ปิดงบการเงิน - ปี
                                    <?php echo htmlspecialchars($selected_year); ?>
                                </p>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn-excel-action">
                                <i class="ri-upload-2-line"></i>
                                <span>ส่งออก Excel</span>
                            </button>
                        </div>
                    </div>

                    <!-- Stats Grid (4 กล่องสถิติการปิดงบ) -->
                    <?php
                    $closingRows = $data['closing_data'] ?? [];
                    $cntDocReceived = 0; // ลูกค้าปิดงบ (ได้รับเอกสารแล้ว)
                    $cntClosingDone = 0; // ปิดงบเสร็จ
                    $cntBudgetRefund = 0; // รับงบคืนแล้ว
                    $cntDbdEfiling = 0; // DBD E-Filing ยื่นแล้ว
                    foreach ($closingRows as $r) {
                        if (($r['doc_status'] ?? '0') === '1' && !empty($r['doc_date']))
                            $cntDocReceived++;
                        if (($r['closing_status'] ?? '0') === '1' && !empty($r['closing_date']))
                            $cntClosingDone++;
                        if (!empty($r['budget_refund_date']))
                            $cntBudgetRefund++;
                        if (($r['dbd_efiling_status'] ?? '0') === '1' && !empty($r['dbd_efiling_date']))
                            $cntDbdEfiling++;
                    }
                    ?>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon blue">
                                <i class="ri-file-text-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo $cntDocReceived; ?></span>
                                <span class="stat-label">ลูกค้าปิดงบ</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon green">
                                <i class="ri-checkbox-circle-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo $cntClosingDone; ?></span>
                                <span class="stat-label">ปิดงบเสร็จ</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon purple">
                                <i class="ri-time-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo $cntBudgetRefund; ?></span>
                                <span class="stat-label">รับงบคืนแล้ว</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon yellow">
                                <i class="ri-wallet-3-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo $cntDbdEfiling; ?></span>
                                <span class="stat-label">DBD E-Filing</span>
                            </div>
                        </div>
                    </div>


                    <!-- Filter Toolbar (ค้นหาแถวบน, ตัวกรอง 6 ช่องแถวล่าง) -->
                    <div class="filter-container mb-4">
                        <div class="search-box-wrap">
                            <i class="ri-search-line"></i>
                            <input type="text" class="search-input" placeholder="ค้นหาชื่อลูกค้า ผู้ดูแล รอบบัญชี">
                        </div>

                        <div class="filter-group">
                            <select class="filter-select select2" id="selUserClosing">
                                <option value="">ทุกผู้ดูแล</option>
                                <?php if (!empty($data['caretakers'])): ?>
                                    <?php foreach ($data['caretakers'] as $c): ?>
                                        <option value="<?php echo htmlspecialchars($c['user_id'] ?? ''); ?>">
                                            <?php echo htmlspecialchars(trim(($c['user_firstname'] ?? '') . ' ' . ($c['user_lastname'] ?? ''))); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>

                            <select class="filter-select select2" id="selClosing">
                                <option selected value="">ปิดงบ : ทั้งหมด</option>
                                <option value="1">รอเอกสาร</option>
                                <option value="2">ได้รับเอกสาร</option>
                                <option value="3">เสร็จแล้ว</option>
                            </select>

                            <select class="filter-select select2" id="selAuditor">
                                <option selected value="">ผู้สอบ : ทั้งหมด</option>
                                <option value="1">ยังไม่ได้ตรวจ</option>
                                <option value="2">ตรวจแล้ว</option>
                                <option value="3">ได้รับงานคืนแล้ว</option>
                            </select>

                            <select class="filter-select select2" id="selBoj5">
                                <option selected value="">บอจ5 : ทั้งหมด</option>
                                <option value="1">ยังไม่ได้ยื่น</option>
                                <option value="2">นำส่งแล้ว</option>
                            </select>

                            <select class="filter-select select2" id="selBdb">
                                <option selected value="">BDB : ทั้งหมด</option>
                                <option value="1">ยังไม่ได้ยื่น</option>
                                <option value="2">นำส่งแล้ว</option>
                            </select>

                            <select class="filter-select select2" id="selPnd50">
                                <option selected value="">ภ.ง.ด 50 : ทั้งหมด</option>
                                <option value="1">รอเอกสาร</option>
                                <option value="2">ยังไม่ได้ยื่น</option>
                                <option value="3">นำส่งแล้ว</option>
                            </select>
                        </div>
                    </div>

                    <!-- Table Container -->
                    <?php
                    require_once __DIR__ . '/table/closing_table.php';
                    ?>

                </div> <!-- End .main-card-wrapper -->
            </div>
        </div>
    </div>
</div>


<!-- modal clossing -->
<div class="modal fade" id="modal_clossing" tabindex="-1" aria-labelledby="modal_clossing_Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg modal-dialog-custom">
        <div class="modal-content modal-content-custom"
            style="border-radius: 16px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">

            <!-- Header (Fixed) -->
            <div class="modal-header modal-header-custom border-bottom-0 pb-0 pt-4 px-4">
                <div>
                    <h5 class="modal-title modal-title-custom mb-1" id="modal_clossing_Label"
                        style="font-weight: 700; font-size: 18px; color: #1E293B;">อัปเดตสถานะปิดงบ</h5>
                    <p class="text-muted small mb-0" id="modal_clossing_subtitle"
                        style="font-weight: 500; font-size: 13px; color: #64748B;"></p>
                </div>
                <button type="button" class="btn-close modal-close-custom" data-bs-dismiss="modal" aria-label="Close"
                    style="align-self: flex-start;"></button>
            </div>

            <!-- Body (Scrollable) -->
            <div class="modal-body modal-body-custom pt-4 px-4 pb-2">
                <form id="updateClosingForm">
                    <input type="hidden" name="closing_id" id="closing_id" value="">
                    <input type="hidden" name="customer_id" id="closing_customer_id" value="">
                    <input type="hidden" name="fiscal_year_id" id="closing_fiscal_year_id" value="">

                    <!-- Section: สถานะปิดงบ -->
                    <div class="mb-4">
                        <h6 class="modal-section-title mb-1" style="font-weight: 700; font-size: 15px; color: #0F172A;">
                            สถานะปิดงบ</h6>
                        <p class="text-muted small mb-3" id="closing_status_text" style="color: #94A3B8;">ปัจจุบัน:
                            รอเอกสาร</p>

                        <div class="d-flex align-items-center mb-3">
                            <div class="form-check me-3" style="width: 180px;">
                                <input class="form-check-input" type="checkbox" name="doc_status" id="doc_status"
                                    value="1" style="width: 1.2rem; height: 1.2rem; margin-top: 0.15rem;"
                                    onchange="toggleDateInput(this, 'doc_date')">
                                <label class="form-check-label text-muted" for="doc_status"
                                    style=" font-size: 14px; margin-left: 8px; padding-top: 2px;">
                                    ได้รับเอกสารแล้ว
                                </label>
                            </div>
                            <div class="flex-grow-1 position-relative">
                                <input type="text" class="form-control modal-form-control flatpickr-date"
                                    name="doc_date" id="doc_date" placeholder="31/12/2026"
                                    style="background-color: #F8FAFC; border: none; border-radius: 8px; font-weight: 500; color: #64748B; padding-left: 16px;"
                                    disabled>
                                <i class="ri-calendar-line position-absolute"
                                    style="right: 15px; top: 50%; transform: translateY(-50%); color: #94A3B8; font-size: 18px; pointer-events: none;"></i>
                            </div>
                        </div>

                        <div class="d-flex align-items-center mb-3">
                            <div class="form-check me-3" style="width: 180px;">
                                <input class="form-check-input" type="checkbox" name="closing_status"
                                    id="closing_status" value="1"
                                    style="width: 1.2rem; height: 1.2rem; margin-top: 0.15rem;"
                                    onchange="toggleDateInput(this, 'closing_date')">
                                <label class="form-check-label text-muted" for="closing_status"
                                    style=" font-size: 14px; margin-left: 8px; padding-top: 2px;">
                                    เสร็จแล้ว
                                </label>
                            </div>
                            <div class="flex-grow-1 position-relative">
                                <input type="text" class="form-control modal-form-control flatpickr-date"
                                    name="closing_date" id="closing_date" placeholder="31/12/2026"
                                    style="background-color: #F8FAFC; border: none; border-radius: 8px; font-weight: 500; color: #64748B; padding-left: 16px;"
                                    disabled>
                                <i class="ri-calendar-line position-absolute"
                                    style="right: 15px; top: 50%; transform: translateY(-50%); color: #94A3B8; font-size: 18px; pointer-events: none;"></i>
                            </div>
                        </div>
                    </div>

                    <div class="modal-section-divider my-4" style="border-top: 1px dashed #E2E8F0;"></div>

                    <!-- Section: สถานะผู้สอบ -->
                    <div class="mb-4">
                        <h6 class="modal-section-title mb-1" style="font-weight: 700; font-size: 15px; color: #0F172A;">
                            สถานะผู้สอบ</h6>
                        <p class="text-muted small mb-3" id="audit_status_text" style="color: #94A3B8;">ปัจจุบัน:
                            ยังไม่ได้ส่งตรวจ</p>

                        <div class="d-flex align-items-center mb-3">
                            <div class="form-check me-3" style="width: 180px;">
                                <input class="form-check-input" type="checkbox" name="audit_status" id="audit_status"
                                    value="1" style="width: 1.2rem; height: 1.2rem; margin-top: 0.15rem;"
                                    onchange="toggleDateInput(this, 'audit_date')">
                                <label class="form-check-label text-muted" for="audit_status"
                                    style=" font-size: 14px; margin-left: 8px; padding-top: 2px;">
                                    ส่งตรวจแล้ว
                                </label>
                            </div>
                            <div class="flex-grow-1 position-relative">
                                <input type="text" class="form-control modal-form-control flatpickr-date"
                                    name="audit_date" id="audit_date" placeholder="31/12/2026"
                                    style="background-color: #F8FAFC; border: none; border-radius: 8px; font-weight: 500; color: #64748B; padding-left: 16px;"
                                    disabled>
                                <i class="ri-calendar-line position-absolute"
                                    style="right: 15px; top: 50%; transform: translateY(-50%); color: #94A3B8; font-size: 18px; pointer-events: none;"></i>
                            </div>
                        </div>

                        <div class="d-flex align-items-center mb-3">
                            <div class="form-check me-3" style="width: 180px;">
                                <input class="form-check-input" type="checkbox" name="budget_refund_status"
                                    id="budget_refund_status" value="1"
                                    style="width: 1.2rem; height: 1.2rem; margin-top: 0.15rem;"
                                    onchange="toggleDateInput(this, 'budget_refund_date')">
                                <label class="form-check-label text-muted" for="budget_refund_status"
                                    style=" font-size: 14px; margin-left: 8px; padding-top: 2px;">
                                    ได้รับงบคืนแล้ว
                                </label>
                            </div>
                            <div class="flex-grow-1 position-relative">
                                <input type="text" class="form-control modal-form-control flatpickr-date"
                                    name="budget_refund_date" id="budget_refund_date" placeholder="31/12/2026"
                                    style="background-color: #F8FAFC; border: none; border-radius: 8px; font-weight: 500; color: #64748B; padding-left: 16px;"
                                    disabled>
                                <i class="ri-calendar-line position-absolute"
                                    style="right: 15px; top: 50%; transform: translateY(-50%); color: #94A3B8; font-size: 18px; pointer-events: none;"></i>
                            </div>
                        </div>
                    </div>

                    <div class="modal-section-divider my-4" style="border-top: 1px dashed #E2E8F0;"></div>

                    <!-- Section: เอกสารนำส่ง -->
                    <div class="mb-2">
                        <div class="d-flex align-items-center mb-3">
                            <div class="form-check me-3" style="width: 180px;">
                                <input class="form-check-input" type="checkbox" name="boj5_status" id="boj5_status"
                                    value="1" style="width: 1.2rem; height: 1.2rem; margin-top: 0.15rem;"
                                    onchange="toggleDateInput(this, 'boj5_date')">
                                <label class="form-check-label text-muted" for="boj5_status"
                                    style=" font-size: 14px; margin-left: 8px; padding-top: 2px;">
                                    บอจ. 5 นำส่งแล้ว
                                </label>
                            </div>
                            <div class="flex-grow-1 position-relative">
                                <input type="text" class="form-control modal-form-control flatpickr-date"
                                    name="boj5_date" id="boj5_date" placeholder="31/12/2026"
                                    style="background-color: #F8FAFC; border: none; border-radius: 8px; font-weight: 500; color: #64748B; padding-left: 16px;"
                                    disabled>
                                <i class="ri-calendar-line position-absolute"
                                    style="right: 15px; top: 50%; transform: translateY(-50%); color: #94A3B8; font-size: 18px; pointer-events: none;"></i>
                            </div>
                        </div>

                        <div class="d-flex align-items-center mb-3">
                            <div class="form-check me-3" style="width: 180px;">
                                <input class="form-check-input" type="checkbox" name="dbd_efiling_status"
                                    id="dbd_efiling_status" value="1"
                                    style="width: 1.2rem; height: 1.2rem; margin-top: 0.15rem;"
                                    onchange="toggleDateInput(this, 'dbd_efiling_date')">
                                <label class="form-check-label text-muted" for="dbd_efiling_status"
                                    style=" font-size: 14px; margin-left: 8px; padding-top: 2px;">
                                    DBD E-Filing นำส่งแล้ว
                                </label>
                            </div>
                            <div class="flex-grow-1 position-relative">
                                <input type="text" class="form-control modal-form-control flatpickr-date"
                                    name="dbd_efiling_date" id="dbd_efiling_date" placeholder="31/12/2026"
                                    style="background-color: #F8FAFC; border: none; border-radius: 8px; font-weight: 500; color: #64748B; padding-left: 16px;"
                                    disabled>
                                <i class="ri-calendar-line position-absolute"
                                    style="right: 15px; top: 50%; transform: translateY(-50%); color: #94A3B8; font-size: 18px; pointer-events: none;"></i>
                            </div>
                        </div>

                        <div class="d-flex align-items-center mb-3">
                            <div class="form-check me-3" style="width: 180px;">
                                <input class="form-check-input" type="checkbox" name="pnd50_status" id="pnd50_status"
                                    value="1" style="width: 1.2rem; height: 1.2rem; margin-top: 0.15rem;"
                                    onchange="toggleDateInput(this, 'pnd50_date')">
                                <label class="form-check-label text-muted" for="pnd50_status"
                                    style=" font-size: 14px; margin-left: 8px; padding-top: 2px;">
                                    ภ.ง.ด.50 นำส่งแล้ว
                                </label>
                            </div>
                            <div class="flex-grow-1 position-relative">
                                <input type="text" class="form-control modal-form-control flatpickr-date"
                                    name="pnd50_date" id="pnd50_date" placeholder="31/12/2026"
                                    style="background-color: #F8FAFC; border: none; border-radius: 8px; font-weight: 500; color: #64748B; padding-left: 16px;"
                                    disabled>
                                <i class="ri-calendar-line position-absolute"
                                    style="right: 15px; top: 50%; transform: translateY(-50%); color: #94A3B8; font-size: 18px; pointer-events: none;"></i>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Footer (Fixed) -->
            <div class="modal-footer modal-footer-custom border-top-0 pt-0 px-4 pb-4">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                    style="border-radius: 8px;  padding: 10px 24px; background-color: #F8FAFC; color: #475569; border: none;">ยกเลิก</button>
                <button type="button" class="btn btn-primary" onclick="submitClosing()"
                    style="border-radius: 8px;  padding: 10px 24px; background-color: #2563EB; border: none; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);">บันทึกข้อมูล</button>
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

        if (typeof $.fn.select2 === 'function') {
            $('.select2').select2({
                width: '100%'
            });
        }

        // --- Active Search and Filter Logic ---
        function applyClosingFilters() {
            const q = ($('.search-input').val() || '').trim().toLowerCase();
            const selUser = String($('#selUserClosing').val() || '').trim();
            const selClosing = String($('#selClosing').val() || '').trim();
            const selAuditor = String($('#selAuditor').val() || '').trim();
            const selBoj5 = String($('#selBoj5').val() || '').trim();
            const selBdb = String($('#selBdb').val() || '').trim();
            const selPnd50 = String($('#selPnd50').val() || '').trim();

            let visibleCount = 0;
            $('.table tbody tr.closing-row').each(function () {
                const $tr = $(this);

                const custName = String($tr.attr('data-customer-name') || $tr.data('customer-name') || '').toLowerCase();
                const userName = String($tr.attr('data-user-name') || $tr.data('user-name') || '').toLowerCase();
                const userId = String($tr.attr('data-user-id') || $tr.data('user-id') || '').trim();
                const fiscalDate = String($tr.attr('data-fiscal-date') || $tr.data('fiscal-date') || '').toLowerCase();

                const docStatus = String($tr.attr('data-doc-status') || $tr.data('doc-status') || '0');
                const docDate = String($tr.attr('data-doc-date') || $tr.data('doc-date') || '');
                const closingStatus = String($tr.attr('data-closing-status') || $tr.data('closing-status') || '0');
                const closingDate = String($tr.attr('data-closing-date') || $tr.data('closing-date') || '');
                const auditStatus = String($tr.attr('data-audit-status') || $tr.data('audit-status') || '0');
                const auditDate = String($tr.attr('data-audit-date') || $tr.data('audit-date') || '');
                const budgetRefundDate = String($tr.attr('data-budget-refund-date') || $tr.data('budget-refund-date') || '');
                const boj5Status = String($tr.attr('data-boj5-status') || $tr.data('boj5-status') || '0');
                const boj5Date = String($tr.attr('data-boj5-date') || $tr.data('boj5-date') || '');
                const dbdStatus = String($tr.attr('data-dbd-status') || $tr.data('dbd-status') || '0');
                const dbdDate = String($tr.attr('data-dbd-date') || $tr.data('dbd-date') || '');
                const pnd50Status = String($tr.attr('data-pnd50-status') || $tr.data('pnd50-status') || '0');
                const pnd50Date = String($tr.attr('data-pnd50-date') || $tr.data('pnd50-date') || '');

                // 1. Search Query
                let matchSearch = true;
                if (q) {
                    matchSearch = custName.includes(q) || userName.includes(q) || fiscalDate.includes(q);
                }

                // 2. User/Caretaker Filter
                let matchUser = true;
                if (selUser !== '') {
                    matchUser = (userId === selUser);
                }

                // 3. Closing Status Filter
                // 1 = รอเอกสาร, 2 = ได้รับเอกสาร, 3 = เสร็จแล้ว
                let matchClosing = true;
                if (selClosing === '1') {
                    matchClosing = (docStatus !== '1' || !docDate);
                } else if (selClosing === '2') {
                    matchClosing = (docStatus === '1' && docDate !== '') && (closingStatus !== '1' || !closingDate);
                } else if (selClosing === '3') {
                    matchClosing = (closingStatus === '1' && closingDate !== '');
                }

                // 4. Auditor Filter
                // 1 = ยังไม่ได้ตรวจ, 2 = ตรวจแล้ว, 3 = ได้รับงานคืนแล้ว
                let matchAuditor = true;
                if (selAuditor === '1') {
                    matchAuditor = (auditStatus !== '1' || !auditDate);
                } else if (selAuditor === '2') {
                    matchAuditor = (auditStatus === '1' && auditDate !== '') && (!budgetRefundDate);
                } else if (selAuditor === '3') {
                    matchAuditor = (budgetRefundDate !== '');
                }

                // 5. Boj5 Filter
                // 1 = ยังไม่ได้ยื่น, 2 = นำส่งแล้ว
                let matchBoj5 = true;
                if (selBoj5 === '1') {
                    matchBoj5 = (boj5Status !== '1' || !boj5Date);
                } else if (selBoj5 === '2') {
                    matchBoj5 = (boj5Status === '1' && boj5Date !== '');
                }

                // 6. Bdb / DBD E-Filing Filter
                // 1 = ยังไม่ได้ยื่น, 2 = นำส่งแล้ว
                let matchBdb = true;
                if (selBdb === '1') {
                    matchBdb = (dbdStatus !== '1' || !dbdDate);
                } else if (selBdb === '2') {
                    matchBdb = (dbdStatus === '1' && dbdDate !== '');
                }

                // 7. Pnd50 Filter
                // 1 = รอเอกสาร, 2 = ยังไม่ได้ยื่น, 3 = นำส่งแล้ว
                let matchPnd50 = true;
                if (selPnd50 === '1') {
                    matchPnd50 = (docStatus !== '1' || !docDate);
                } else if (selPnd50 === '2') {
                    matchPnd50 = (docStatus === '1' && docDate !== '') && (pnd50Status !== '1' || !pnd50Date);
                } else if (selPnd50 === '3') {
                    matchPnd50 = (pnd50Status === '1' && pnd50Date !== '');
                }

                if (matchSearch && matchUser && matchClosing && matchAuditor && matchBoj5 && matchBdb && matchPnd50) {
                    $tr.show();
                    visibleCount++;
                } else {
                    $tr.hide();
                }
            });

            $('#noClosingDataRow').toggle(visibleCount === 0);
        }

        $('.search-input').on('keyup input', applyClosingFilters);
        $('.select2').on('change', applyClosingFilters);
    });


    function toggleDateInput(checkbox, dateInputId) {
        const dateInput = document.getElementById(dateInputId);
        if (dateInput) {
            if (checkbox.checked) {
                dateInput.disabled = false;
                // ถ้ายังไม่มีค่า ให้ใส่วันที่ปัจจุบันเป็น default
                if (!dateInput.value) {
                    const today = new Date();
                    const dd = String(today.getDate()).padStart(2, '0');
                    const mm = String(today.getMonth() + 1).padStart(2, '0');
                    const yyyy = today.getFullYear();
                    // ใช้ flatpickr instance เพื่อ set value ให้ถูก format
                    const fp = dateInput._flatpickr;
                    if (fp) {
                        fp.setDate(today, true);
                    } else {
                        dateInput.value = `${dd}/${mm}/${yyyy}`;
                    }
                }
            } else {
                dateInput.disabled = true;
                dateInput.value = ''; // clear value if disabled
                // clear flatpickr instance ด้วย
                const fp = dateInput._flatpickr;
                if (fp) fp.clear();
            }
        }
    }


    function openClosingModal(button) {
        const dataStr = button.getAttribute('data-closing');
        if (!dataStr) return;
        const data = JSON.parse(dataStr);

        // 1. เคลียร์ข้อมูลในฟอร์มเก่าทิ้ง (ถ้ามี)
        const form = document.getElementById('updateClosingForm');
        if (form) form.reset();

        // 2. Map data
        document.getElementById('closing_id').value = data.closing_id || '';
        document.getElementById('closing_customer_id').value = data.customer_id || '';
        document.getElementById('closing_fiscal_year_id').value = data.fiscal_year_id || '';

        const year = data.fiscal_year || (new Date().getFullYear() + 543);
        document.getElementById('modal_clossing_subtitle').innerText = `${data.customer_name || 'ไม่ระบุชื่อ'} · ปี ${year}`;

        // Helper function to format YYYY-MM-DD to DD/MM/YYYY
        const formatDate = (dateStr) => {
            if (!dateStr) return '';
            const parts = dateStr.split('-');
            if (parts.length === 3) return `${parts[2]}/${parts[1]}/${parts[0]}`;
            return dateStr;
        };

        // Helper to safely set checkbox and trigger toggle
        const setStatus = (chkId, dateId, isChecked, dateVal) => {
            const chk = document.getElementById(chkId);
            chk.checked = isChecked;
            toggleDateInput(chk, dateId); // Update disabled state
            if (isChecked) {
                document.getElementById(dateId).value = formatDate(dateVal);
            }
        };

        // สถานะปิดงบ
        setStatus('doc_status', 'doc_date', (data.doc_status == '1'), data.doc_date);
        document.getElementById('closing_status_text').innerText = (data.doc_status == '1') ? 'ปัจจุบัน: ได้รับเอกสารแล้ว' : 'ปัจจุบัน: รอเอกสาร';

        setStatus('closing_status', 'closing_date', (data.closing_status == '1'), data.closing_date);

        // สถานะผู้สอบ
        setStatus('audit_status', 'audit_date', (data.audit_status == '1'), data.audit_date);
        document.getElementById('audit_status_text').innerText = (data.audit_status == '1') ? 'ปัจจุบัน: ส่งตรวจแล้ว' : 'ปัจจุบัน: ยังไม่ได้ส่งตรวจ';

        setStatus('budget_refund_status', 'budget_refund_date', (data.budget_refund_date ? true : false), data.budget_refund_date);

        // เอกสารนำส่ง
        setStatus('boj5_status', 'boj5_date', (data.boj5_status == '1'), data.boj5_date);
        setStatus('dbd_efiling_status', 'dbd_efiling_date', (data.dbd_efiling_status == '1'), data.dbd_efiling_date);
        setStatus('pnd50_status', 'pnd50_date', (data.pnd50_status == '1'), data.pnd50_date);

        // 3. สั่งโชว์ Modal ผ่าน Vanilla JS ของ Bootstrap
        const modalElement = document.getElementById('modal_clossing');
        const myModal = new bootstrap.Modal(modalElement);
        myModal.show();
    }


    let isSubmittingClosing = false;
    function submitClosing() {
        if (isSubmittingClosing) return;
        isSubmittingClosing = true;
        const submitBtn = $('#modal_clossing .modal-footer button:last-child');
        const originalBtnText = submitBtn.text();
        submitBtn.prop('disabled', true).text('กำลังบันทึก...');
        var formData = $('#updateClosingForm').serialize();

        $.ajax({

            url: '/cpd_ac/public/closing/update',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function (response) {
                isSubmittingClosing = false;
                submitBtn.prop('disabled', false).text(originalBtnText);

                if (response.result === 1) {
                    $('#addCustomerModal').modal('hide');
                    if (typeof Swal !== 'undefined') {
                        sessionStorage.setItem('toast_msg', 'บันทึกข้อมูลสำเร็จ');
                        sessionStorage.setItem('toast_icon', 'success');
                        location.reload();
                    } else {
                        alert('บันทึกข้อมูลสำเร็จ');
                        location.reload();
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true
                        });
                        Toast.fire({ icon: 'error', title: response.msg || 'ไม่สามารถเพิ่มลูกค้าได้' });
                    } else {
                        alert(response.msg || 'ไม่สามารถเพิ่มลูกค้าได้');
                    }
                }
            },
            error: function (err) {
                isSubmittingCustomer = false;
                submitBtn.prop('disabled', false).text(originalBtnText);
                console.error("AJAX Error:", err);
                if (typeof Swal !== 'undefined') {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                    Toast.fire({ icon: 'error', title: 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์' });
                } else {
                    alert("เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์");
                }
            }
        });
    }
</script>

<?php
// 3. นำ Footer เข้ามา
require_once dirname(__DIR__) . '/main/footer.php';
?>