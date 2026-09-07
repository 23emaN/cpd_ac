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
        grid-template-columns: repeat(6, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    @media (max-width: 1400px) {
        .stats-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 576px) {
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

    /* --- Dashboard Progress Cards --- */
    .dashboard-progress-card {
        background-color: #ffffff;
        border-radius: 16px;
        border: 1px solid #edf2f7;
        padding: 24px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    }

    .card-header-icon {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }

    .card-header-icon.purple {
        background-color: #faf5ff;
        color: #a855f7;
    }

    .card-header-icon.green {
        background-color: #f0fdf4;
        color: #22c55e;
    }

    .card-section-title {
        color: #1e293b;
        font-size: 1.05rem;
        font-weight: 700;
        margin: 0;
    }

    .progress-item-label {
        font-size: 0.875rem;
        font-weight: 600;
        color: #64748b;
    }

    .progress-item-value {
        font-size: 0.875rem;
        font-weight: 700;
        color: #0f172a;
    }

    .progress-badge-zero {
        background-color: #f1f5f9;
        color: #64748b;
        font-weight: 600;
        font-size: 0.75rem;
        padding: 4px 10px;
    }

    .custom-progress-bar {
        height: 8px;
        background-color: #f1f5f9;
        border-radius: 10px;
    }

    .custom-progress-bar-lg {
        height: 10px;
        background-color: #f1f5f9;
        border-radius: 10px;
    }

    .user-item-name {
        font-size: 0.95rem;
        font-weight: 700;
        color: #0f172a;
    }

    .user-item-sub {
        font-size: 0.78rem;
        color: #64748b;
    }

    .user-item-status-text {
        font-size: 0.75rem;
        color: #94a3b8;
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
                            <h4 class="page-title">แดชบอร์ด - งานรายปี
                                </h2>
                                <p class="page-subtitle">ภาพรวม - แดชบอร์ดรายปี - ปี...
                                    <!-- <?php echo htmlspecialchars($selected_year); ?></p> -->
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn-excel-action">
                                <i class="ri-upload-2-line"></i>
                                <span>ส่งออก Excel</span>
                            </button>
                        </div>
                    </div>

                    <!-- Stats Grid (6 กล่องสถิติการปิดงบ) -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon blue">
                                <i class="ri-user-3-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val">0</span>
                                <span class="stat-label">ลูกค้าปิดงบ</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon purple">
                                <i class="ri-checkbox-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val">0</span>
                                <span class="stat-label">ปิดงบเสร็จ</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon green">
                                <i class="ri-draft-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val">0</span>
                                <span class="stat-label">ได้รับงบคืน</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon blue">
                                <i class="ri-file-paper-2-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val">0</span>
                                <span class="stat-label">บอจ. 5</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon yellow">
                                <i class="ri-draft-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val">0</span>
                                <span class="stat-label">DBD</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon green">
                                <i class="ri-file-check-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val">0</span>
                                <span class="stat-label">ภ.ง.ด.50</span>
                            </div>
                        </div>
                    </div>

                    <!-- Summary Progress Card Section -->
                    <div class="card dashboard-progress-card mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h4 class="page-title">สรุปความคืบหน้าปิดงบการเงิน</h4>
                                <p class="page-subtitle" style="font-size: 0.875rem;">ปี - · - ลูกค้า</p>
                            </div>
                            <div>
                                <span class="badge rounded-pill px-3 py-2 fw-semibold"
                                    style="background-color: #eff6ff; color: #2563eb; font-size: 0.85rem;">
                                    - ผู้ดูแล
                                </span>
                            </div>
                        </div>

                        <!-- Progress Summary Table -->
                        <div class="table-responsive">
                            <table class="table-custom">
                                <thead>
                                    <tr>
                                        <th class="text-start" style="width: 40%;">รายการ</th>
                                        <th class="text-center" style="width: 15%;">%</th>
                                        <th class="text-center" style="width: 15%;">รวม</th>
                                        <th class="text-center" style="width: 15%;">พนักงาน ก</th>
                                        <th class="text-center" style="width: 15%;">พนักงาน ข</th>

                                    </tr>
                                </thead>

                            </table>
                        </div>

                    </div>

                    <!-- Cards Section: ความคืบหน้าภาพรวม & ปริมาณงานแยกตามผู้ดูแล -->
                    <div class="row g-4 mb-4">
                        <!-- Card 1: ความคืบหน้าภาพรวม -->
                        <div class="col-lg-6 col-12">
                            <div class="card dashboard-progress-card h-100">
                                <div class="d-flex align-items-center gap-3 mb-4">
                                    <div class="card-header-icon purple">
                                        <i class="ri-bar-chart-fill"></i>
                                    </div>
                                    <h5 class="card-section-title">ความคืบหน้าภาพรวม</h5>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="progress-item-label">ปิดงบเสร็จ</span>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="progress-item-value">0 / 0</span>
                                            <span class="badge rounded-pill progress-badge-zero">0%</span>
                                        </div>
                                    </div>
                                    <div class="progress custom-progress-bar">
                                        <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="progress-item-label">ได้รับงบคืน</span>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="progress-item-value">0 / 0</span>
                                            <span class="badge rounded-pill progress-badge-zero">0%</span>
                                        </div>
                                    </div>
                                    <div class="progress custom-progress-bar">
                                        <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="progress-item-label">บอจ. 5</span>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="progress-item-value">0 / 0</span>
                                            <span class="badge rounded-pill progress-badge-zero">0%</span>
                                        </div>
                                    </div>
                                    <div class="progress custom-progress-bar">
                                        <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="progress-item-label">DBD E-Filing</span>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="progress-item-value">0 / 0</span>
                                            <span class="badge rounded-pill progress-badge-zero">0%</span>
                                        </div>
                                    </div>
                                    <div class="progress custom-progress-bar">
                                        <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>

                                <div class="mb-0">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="progress-item-label">ภ.ง.ด.50</span>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="progress-item-value">0 / 0</span>
                                            <span class="badge rounded-pill progress-badge-zero">0%</span>
                                        </div>
                                    </div>
                                    <div class="progress custom-progress-bar">
                                        <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card 2: ปริมาณงานแยกตามผู้ดูแล -->
                        <div class="col-lg-6 col-12">
                            <div class="card dashboard-progress-card h-100">
                                <div class="d-flex align-items-center gap-3 mb-4">
                                    <div class="card-header-icon green">
                                        <i class="ri-user-shared-line"></i>
                                    </div>
                                    <h5 class="card-section-title">ปริมาณงานแยกตามผู้ดูแล</h5>
                                </div>

                                <div class="mb-4">
                                    <div class="row align-items-center">
                                        <div class="col-md-5 col-12 mb-2 mb-md-0">
                                            <div class="user-item-name">พนักงาน ก</div>
                                            <div class="user-item-sub">0 ปิดงบเสร็จจาก 0 ราย</div>
                                        </div>
                                        <div class="col-md-7 col-12">
                                            <div class="d-flex justify-content-end align-items-center gap-2 mb-1">
                                                <span class="fw-bold text-secondary small">0%</span>
                                                <span class="text-muted small">0</span>
                                            </div>
                                            <div class="progress custom-progress-bar-lg">
                                                <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <div class="text-end user-item-status-text mt-1">ปิดงบ</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="row align-items-center">
                                        <div class="col-md-5 col-12 mb-2 mb-md-0">
                                            <div class="user-item-name">พนักงาน ข</div>
                                            <div class="user-item-sub">0 ปิดงบเสร็จจาก 0 ราย</div>
                                        </div>
                                        <div class="col-md-7 col-12">
                                            <div class="d-flex justify-content-end align-items-center gap-2 mb-1">
                                                <span class="fw-bold text-secondary small">0%</span>
                                                <span class="text-muted small">0</span>
                                            </div>
                                            <div class="progress custom-progress-bar-lg">
                                                <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <div class="text-end user-item-status-text mt-1">ปิดงบ</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
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