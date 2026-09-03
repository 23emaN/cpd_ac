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
</style>

<div class="container-fluid">
    <div class="main-content d-flex flex-column">
        <div class="content-wrapper">
            <div class="main-page-wrapper">
                
                <div class="main-card-wrapper">
                    
                    <!-- Page Header Section -->
                    <div class="page-header-box">
                        <div>
                            <h2 class="page-title">พนักงาน</h2>
                            <?php $fy_display = !empty($data['active_fiscal_year']) ? $data['active_fiscal_year'] : 'ไม่ได้เลือกปี'; ?>
                            <p class="page-subtitle">ภาพรวมระบบ - พนักงาน - ปี <?php echo htmlspecialchars($fy_display); ?></p>
                        </div>
                        <div class="d-flex align-items-end">
                            <button type="button" class="btn-add-customer" onclick="modal_addemployee()">
                                <i class="ri-add-line"></i>
                                <span>เพิ่มพนักงาน</span>
                            </button>
                        </div>
                    </div>

                    <!-- Stats Grid (4 กล่องสถิติ) -->
                    <?php
                        $employees = $data['employees'] ?? [];
                        $totalEmployees = count($employees);
                        $activeEmployees = 0;
                        $inactiveEmployees = 0;
                        $uniqueTeams = [];
                        foreach ($employees as $emp) {
                            if ($emp['user_status'] == '1') {
                                $activeEmployees++;
                            } else {
                                $inactiveEmployees++;
                            }
                            if (!empty($emp['team_name'])) {
                                $uniqueTeams[$emp['team_name']] = true;
                            }
                        }
                        $totalTeams = count($uniqueTeams);
                    ?>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon blue">
                                <i class="ri-checkbox-circle-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo $totalEmployees; ?></span>
                                <span class="stat-label">พนักงานทั้งหมด</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon green">
                                <i class="ri-wallet-3-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo $activeEmployees; ?></span>
                                <span class="stat-label">ยังทำงานอยู่</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon purple">
                                <i class="ri-subtract-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo $inactiveEmployees; ?></span>
                                <span class="stat-label">เลิกจ้าง</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon yellow">
                                <i class="ri-group-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo $totalTeams; ?></span>
                                <span class="stat-label">ทีมทั้งหมด</span>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Toolbar (ค้นหา & ตัวกรองสถานะ) -->
                    <div class="filter-toolbar">
                        <div class="search-box-wrap">
                            <i class="ri-search-line"></i>
                            <input type="text" class="search-input" placeholder="ค้นหาชื่อ ตำแหน่ง ทีม">
                        </div>

                        <div class="filter-group">
                            <select class="filter-select">
                                <option value="">ทุกสถานะ</option>
                                <option value="1">ใช้งานอยู่</option>
                                <option value="0">เลิกจ้าง</option>
                            </select>

                            <select class="filter-select">
                                <option value="">ทุกทีม</option>
                                <?php foreach (array_keys($uniqueTeams) as $teamName): ?>
                                    <option value="<?php echo htmlspecialchars($teamName); ?>"><?php echo htmlspecialchars($teamName); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <?php require_once 'table/employee_table.php'; ?>

                </div> <!-- End .main-card-wrapper -->
            </div>
        </div>
    </div>
</div>

<!-- Modal เพิ่มพนักงานใหม่ -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 820px;">
        <div class="modal-content" style="border: none; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.08); background-color: #ffffff;">
            
            <!-- Header -->
            <div class="modal-header" style="border-bottom: none; padding: 24px 28px 12px 28px;">
                <h5 class="modal-title" id="addCustomerModalLabel" style="font-weight: 800; color: #1e293b; font-size: 1.25rem;">เพิ่มลูกค้าใหม่</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.9rem; opacity: 0.4;"></button>
            </div>

            <!-- Body -->
            <div class="modal-body" style="padding: 12px 28px 24px 28px;">
                <form id="addEmployeeForm">
                    <!-- Hidden Fields -->
                    <input type="hidden" name="fiscal_id" value="<?php echo htmlspecialchars($data['fiscal_id'] ?? ''); ?>">
                    <input type="hidden" name="company_id" value="<?php echo htmlspecialchars($data['active_company_id'] ?? ''); ?>">

                    <!-- Section: ข้อมูลทั่วไป -->
                    <div class="mb-4">
                        <!-- ชื่อผู้ใช้ / รหัสผ่าน -->
                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                               ชื่อผู้ใช้ <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" class="form-control" name="user_name" id="user_name" placeholder="ระบุชื่อผู้ใช้" style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; font-weight: 600; color: #1e293b; font-size: 0.92rem; outline: none; box-shadow: none;">
                            <div class="invalid-feedback" style="font-size: 0.85rem; font-weight: 500; margin-top: 6px;">กรุณาระบุชื่อผู้ใช้</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                               รหัสผ่าน <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" class="form-control" name="user_password" id="user_password" placeholder="ระบุรหัสผ่าน" style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; font-weight: 600; color: #1e293b; font-size: 0.92rem; outline: none; box-shadow: none;">
                            <div class="invalid-feedback" style="font-size: 0.85rem; font-weight: 500; margin-top: 6px;">กรุณาระบุรหัสผ่าน</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                               ชื่อพนักงาน <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" class="form-control" name="user_firstname" id="user_firstname" placeholder="ระบุชื่อพนักงาน" style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; font-weight: 600; color: #1e293b; font-size: 0.92rem; outline: none; box-shadow: none;">
                            <div class="invalid-feedback" style="font-size: 0.85rem; font-weight: 500; margin-top: 6px;">กรุณาระบุชื่อพนักงาน</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                               นามสกุล <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" class="form-control" name="user_lastname" id="user_lastname" placeholder="ระบุนามสกุล" style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; font-weight: 600; color: #1e293b; font-size: 0.92rem; outline: none; box-shadow: none;">
                            <div class="invalid-feedback" style="font-size: 0.85rem; font-weight: 500; margin-top: 6px;">กรุณาระบุนามสกุล</div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                                   ตำแหน่ง
                                </label>
                                <input type="text" class="form-control" name="user_position" id="user_position" placeholder="เช่น Senior Accountant" style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; font-weight: 600; color: #1e293b; font-size: 0.92rem; outline: none; box-shadow: none;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                                   ทีม
                                </label>
                                <div class="position-relative dropdown-autocomplete">
                                    <input type="text" class="form-control autocomplete-input" name="team_name" id="team_name" placeholder="เช่น ทีมบัญชี A" style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; font-weight: 500; color: #1e293b; font-size: 0.92rem; outline: none; box-shadow: none;" autocomplete="off">
                                    <ul class="dropdown-menu autocomplete-list w-100 shadow-sm" style="max-height: 200px; overflow-y: auto; padding: 0; margin-top: 4px; border: 1px solid #e2e8f0; border-radius: 8px; position: absolute; z-index: 1050;">
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="modal-footer" style="border-top: none; padding: 12px 28px 28px 28px; gap: 12px; justify-content: flex-end;">
                <button type="button" class="btn" data-bs-dismiss="modal" style="background-color: #f8fafc; color: #334155; font-weight: 700; border-radius: 12px; padding: 10px 24px; border: none; font-size: 0.92rem; transition: all 0.2s ease;">ยกเลิก</button>
                <button type="button" class="btn" onclick="submit_addemployee()" style="background-color: #007aff; color: #ffffff; font-weight: 700; border-radius: 12px; padding: 10px 28px; border: none; font-size: 0.92rem; box-shadow: 0 4px 14px rgba(0,122,255,0.25); transition: all 0.2s ease;">บันทึกข้อมูล</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal แก้ไขพนักงาน -->
<div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-labelledby="editEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 820px;">
        <div class="modal-content" style="border: none; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.08); background-color: #ffffff;">
            
            <!-- Header -->
            <div class="modal-header" style="border-bottom: none; padding: 24px 28px 12px 28px;">
                <h5 class="modal-title" id="editEmployeeModalLabel" style="font-weight: 800; color: #1e293b; font-size: 1.25rem;">แก้ไขข้อมูลพนักงาน</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.9rem; opacity: 0.4;"></button>
            </div>

            <!-- Body -->
            <div class="modal-body" style="padding: 12px 28px 24px 28px;">
                <form id="editEmployeeForm">
                    <input type="hidden" name="user_id" id="edit_user_id">
                    
                    <div class="mb-4">
                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                               ชื่อผู้ใช้ <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" class="form-control" name="user_name" id="edit_user_name" placeholder="ระบุชื่อผู้ใช้" style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; font-weight: 600; color: #1e293b; font-size: 0.92rem; outline: none; box-shadow: none;" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                               ชื่อพนักงาน <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" class="form-control" name="user_firstname" id="edit_user_firstname" placeholder="ระบุชื่อพนักงาน" style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; font-weight: 600; color: #1e293b; font-size: 0.92rem; outline: none; box-shadow: none;">
                            <div class="invalid-feedback" style="font-size: 0.85rem; font-weight: 500; margin-top: 6px;">กรุณาระบุชื่อพนักงาน</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                               นามสกุล <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" class="form-control" name="user_lastname" id="edit_user_lastname" placeholder="ระบุนามสกุล" style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; font-weight: 600; color: #1e293b; font-size: 0.92rem; outline: none; box-shadow: none;">
                            <div class="invalid-feedback" style="font-size: 0.85rem; font-weight: 500; margin-top: 6px;">กรุณาระบุนามสกุล</div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                                   ตำแหน่ง
                                </label>
                                <input type="text" class="form-control" name="user_position" id="edit_user_position" placeholder="เช่น Senior Accountant" style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; font-weight: 600; color: #1e293b; font-size: 0.92rem; outline: none; box-shadow: none;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                                   ทีม
                                </label>
                                <div class="position-relative dropdown-autocomplete">
                                    <input type="text" class="form-control autocomplete-input" name="team_name" id="edit_team_name" placeholder="ระบุชื่อทีม" style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; font-weight: 500; color: #1e293b; font-size: 0.92rem; outline: none; box-shadow: none;" autocomplete="off">
                                    <ul class="dropdown-menu autocomplete-list w-100 shadow-sm" style="max-height: 200px; overflow-y: auto; padding: 0; margin-top: 4px; border: 1px solid #e2e8f0; border-radius: 8px; position: absolute; z-index: 1050;">
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="modal-footer" style="border-top: 1px solid #f1f5f9; padding: 20px 28px; display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" class="btn" data-bs-dismiss="modal" style="background-color: #f8fafc; color: #334155; font-weight: 700; border-radius: 12px; padding: 10px 24px; border: none; font-size: 0.92rem; transition: all 0.2s ease;">ยกเลิก</button>
                <button type="button" class="btn" onclick="submit_editemployee()" style="background-color: #007aff; color: #ffffff; font-weight: 700; border-radius: 12px; padding: 10px 28px; border: none; font-size: 0.92rem; box-shadow: 0 4px 14px rgba(0,122,255,0.25); transition: all 0.2s ease;">บันทึกข้อมูล</button>
            </div>
        </div>
    </div>
</div>

<style>
    /* SweetAlert2 Custom Style */
    .custom-swal-popup {
        border-radius: 16px !important;
        padding: 2rem !important;
        box-shadow: 0 20px 40px rgba(0,0,0,0.08) !important;
    }
    .custom-swal-title {
        font-size: 1.2rem !important;
        font-weight: 800 !important;
        color: #1e293b !important;
        margin-bottom: 0.5rem !important;
    }
    .custom-swal-text {
        color: #475569 !important;
        font-size: 0.95rem !important;
        font-weight: 500 !important;
        margin-bottom: 1.5rem !important;
    }
    .custom-swal-actions {
        gap: 12px !important;
        margin-top: 1.5rem !important;
    }
    .custom-swal-confirm {
        background-color: #e11d48 !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        border-radius: 8px !important;
        padding: 10px 24px !important;
        border: none !important;
        font-size: 0.95rem !important;
        transition: all 0.2s ease !important;
    }
    .custom-swal-cancel {
        background-color: #64748b !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        border-radius: 8px !important;
        padding: 10px 24px !important;
        border: none !important;
        font-size: 0.95rem !important;
        transition: all 0.2s ease !important;
    }
    
    /* Autocomplete Style */
    .autocomplete-input:focus {
        border-color: #10b981 !important; /* Green border like the image */
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.1) !important;
    }
    .autocomplete-list li {
        padding: 10px 16px;
        cursor: pointer;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.9rem;
        color: #475569;
        display: flex;
        align-items: center;
    }
    .autocomplete-list li::before {
        content: "•";
        color: #94a3b8;
        font-weight: bold;
        display: inline-block; 
        width: 1em;
        margin-right: 8px;
    }
    .autocomplete-list li:hover {
        background-color: #f8fafc;
        color: #0f172a;
    }
    .autocomplete-list li:last-child {
        border-bottom: none;
    }
</style>
<script>
    const employeesData = <?php echo json_encode($data['employees'] ?? []); ?>;
    const teamsData = <?php echo json_encode($data['teams'] ?? []); ?>;
    let isSubmittingTask = false;

    $(document).ready(function() {
        // Setup Autocomplete
        function renderAutocomplete(inputElem, listElem, query) {
            listElem.empty();
            let matches = teamsData.filter(t => t.team_name.toLowerCase().includes(query.toLowerCase()));
            
            // Limit to 5 items
            matches = matches.slice(0, 5);
            
            if (matches.length > 0) {
                matches.forEach(match => {
                    listElem.append(`<li>${match.team_name}</li>`);
                });
                listElem.show();
            } else {
                listElem.hide();
            }
        }

        $('.autocomplete-input').on('keyup focus', function() {
            let input = $(this);
            let list = input.siblings('.autocomplete-list');
            renderAutocomplete(input, list, input.val());
        });

        // Click item
        $(document).on('click', '.autocomplete-list li', function() {
            let list = $(this).closest('.autocomplete-list');
            let input = list.siblings('.autocomplete-input');
            input.val($(this).text());
            list.hide();
        });

        // Hide when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.dropdown-autocomplete').length) {
                $('.autocomplete-list').hide();
            }
        });
    });



    function modal_addemployee() {
        // 1. เคลียร์ข้อมูลในฟอร์มเก่าทิ้ง (ถ้ามี)
        const form = document.getElementById('addEmployeeForm');
        if(form) {
            form.reset();
        }
        // 2. สั่งโชว์ Modal ผ่าน Vanilla JS ของ Bootstrap
        const modalElement = document.getElementById('addEmployeeModal');
        const myModal = new bootstrap.Modal(modalElement);
        myModal.show();
    }   

    let isSubmittingEmployee = false;
    function submit_addemployee() {
        if (isSubmittingEmployee) return;

        // Get fields
        const userName = $('#user_name').val().trim();
        const userPassword = $('#user_password').val().trim();
        const userFirstname = $('#user_firstname').val().trim();
        const userLastname = $('#user_lastname').val().trim();

        let isValid = true;

        // Validate user_name
        if (!userName) {
            $('#user_name').addClass('is-invalid');
            isValid = false;
        } else {
            $('#user_name').removeClass('is-invalid');
        }

        // Validate user_password
        if (!userPassword) {
            $('#user_password').addClass('is-invalid');
            isValid = false;
        } else {
            $('#user_password').removeClass('is-invalid');
        }

        // Validate user_firstname
        if (!userFirstname) {
            $('#user_firstname').addClass('is-invalid');
            isValid = false;
        } else {
            $('#user_firstname').removeClass('is-invalid');
        }

        // Validate user_lastname
        if (!userLastname) {
            $('#user_lastname').addClass('is-invalid');
            isValid = false;
        } else {
            $('#user_lastname').removeClass('is-invalid');
        }

        if (!isValid) {
            return; // หยุดการทำงานถ้ากรอกไม่ครบ
        }

        isSubmittingEmployee = true;
        const submitBtn = $('#addEmployeeModal .modal-footer button:last-child');
        const originalBtnText = submitBtn.text();
        submitBtn.prop('disabled', true).text('กำลังบันทึก...');

        var formData = $('#addEmployeeForm').serialize();

        $.ajax({
            url: '/cpd_ac/public/employee/add', // หรือ route ที่คุณต้องการใช้
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                isSubmittingEmployee = false;
                submitBtn.prop('disabled', false).text(originalBtnText);

                if (response.result === 1) {
                    $('#addEmployeeModal').modal('hide');
                    if (typeof Swal !== 'undefined') {
                        sessionStorage.setItem('toast_msg', 'เพิ่มพนักงานสำเร็จ');
                        sessionStorage.setItem('toast_icon', 'success');
                        location.reload();
                    } else {
                        alert('เพิ่มพนักงานสำเร็จ');
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
                        Toast.fire({ icon: 'error', title: response.msg });
                    } else {
                        alert(response.msg);
                    }
                }
            },
            error: function(err) {
                isSubmittingEmployee = false;
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

       function edit_employee(userId) {
        // หาข้อมูลพนักงานจาก employeesData
        const emp = employeesData.find(e => e.user_id == userId);
        if (emp) {
            $('#edit_user_id').val(emp.user_id);
            $('#edit_user_name').val(emp.user_name);
            $('#edit_user_firstname').val(emp.user_firstname);
            $('#edit_user_lastname').val(emp.user_lastname);
            $('#edit_user_position').val(emp.position);
            $('#edit_team_name').val(emp.team_name);
            
            const modalElement = document.getElementById('editEmployeeModal');
            const myModal = new bootstrap.Modal(modalElement);
            myModal.show();
        } else {
            console.error("ไม่พบข้อมูลพนักงาน");
        }
    }

    function submit_editemployee() {
        var userId = $('#edit_user_id').val();
        var userFirstname = $('#edit_user_firstname').val().trim();
        var userLastname = $('#edit_user_lastname').val().trim();
        var userPosition = $('#edit_user_position').val().trim();
        var userTeamName = $('#edit_team_name').val().trim();

        let isValid = true;

        if (userFirstname === '') {
            $('#edit_user_firstname').addClass('is-invalid');
            isValid = false;
        } else {
            $('#edit_user_firstname').removeClass('is-invalid');
        }

        if (userLastname === '') {
            $('#edit_user_lastname').addClass('is-invalid');
            isValid = false;
        } else {
            $('#edit_user_lastname').removeClass('is-invalid');
        }

        if (!isValid) {
            return;
        }

        const submitBtn = $('#editEmployeeModal .modal-footer button:last-child');
        const originalBtnText = submitBtn.text();
        submitBtn.prop('disabled', true).text('กำลังอัปเดต...');

        $.ajax({
            url: '<?php echo BASE_URL ?? "/cpd_ac/public"; ?>/employee/edit',
            type: 'POST',
            data: { 
                user_id: userId,
                user_firstname: userFirstname,
                user_lastname: userLastname,
                user_position: userPosition,
                team_name: userTeamName
            },
            dataType: 'json',
            success: function(response) {
                submitBtn.prop('disabled', false).text(originalBtnText);
                if(response.result === 1) {
                    $('#editEmployeeModal').modal('hide');
                    if(typeof Swal !== 'undefined') {
                        sessionStorage.setItem('toast_msg', 'อัปเดตข้อมูลพนักงานสำเร็จ');
                        sessionStorage.setItem('toast_icon', 'success');
                        location.reload(); 
                    } else {
                        alert('อัปเดตข้อมูลพนักงานสำเร็จ');
                        location.reload();
                    }
                } else {
                    if(typeof Swal !== 'undefined') {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true
                        });
                        Toast.fire({ icon: 'error', title: response.msg });
                    } else {
                        alert(response.msg);
                    }
                }
            },
            error: function(err) {
                submitBtn.prop('disabled', false).text(originalBtnText);
                console.error(err);
                if(typeof Swal !== 'undefined') {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                    Toast.fire({ icon: 'error', title: 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์' });
                } else {
                    alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
                }
            }
        });
    }

    function delete_employee(userId, userFirstname) {
        if(typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'ลบข้อมูลพนักงาน?',
                text: userFirstname + ' จะถูกลบออกจากปีทำงานนี้',
                showCancelButton: true,
                confirmButtonText: 'ลบข้อมูล',
                cancelButtonText: 'ยกเลิก',
                reverseButtons: true, // สลับตำแหน่งปุ่มถ้าระบบเอาปุ่มยกเลิกไว้ซ้าย
                buttonsStyling: false,
                customClass: {
                    popup: 'custom-swal-popup',
                    title: 'custom-swal-title',
                    htmlContainer: 'custom-swal-text',
                    actions: 'custom-swal-actions',
                    confirmButton: 'custom-swal-confirm',
                    cancelButton: 'custom-swal-cancel'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    execute_delete(userId);
                }
            });
        } else {
            if(confirm('คุณต้องการลบพนักงาน ' + userFirstname + ' ออกจากปีทำงานนี้หรือไม่?')) {
                execute_delete(userId);
            }
        }
    }

    function execute_delete(userId) {
        $.ajax({
            url: '<?php echo BASE_URL ?? "/cpd_ac/public"; ?>/employee/delete',
            type: 'POST',
            data: { user_id: userId },
            dataType: 'json',
            success: function(response) {
                if(response.result === 1) {
                    if(typeof Swal !== 'undefined') {
                        sessionStorage.setItem('toast_msg', 'ลบพนักงานสำเร็จ');
                        sessionStorage.setItem('toast_icon', 'success');
                        location.reload(); 
                    } else {
                        alert('ลบพนักงานสำเร็จ');
                        location.reload();
                    }
                } else {
                    if(typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'ผิดพลาด',
                            text: response.msg
                        });
                    } else {
                        alert(response.msg);
                    }
                }
            },
            error: function(err) {
                console.error(err);
                if(typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'ผิดพลาด',
                        text: 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์'
                    });
                } else {
                    alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
                }
            }
        });
    }

</script>

<?php 
// 3. นำ Footer เข้ามา
require_once dirname(__DIR__) . '/main/footer.php'; 
?>
