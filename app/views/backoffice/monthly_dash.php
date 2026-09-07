<?php
// app/views/backoffice/monthly_dash.php
$selected_year = $_GET['year'] ?? '2569';
$company_name = $_GET['company'] ?? 'TEST ACCOUNTING';
$show_company_workspace = true;

$month_names = [
    '01' => 'มกราคม',
    '02' => 'กุมภาพันธ์',
    '03' => 'มีนาคม',
    '04' => 'เมษายน',
    '05' => 'พฤษภาคม',
    '06' => 'มิถุนายน',
    '07' => 'กรกฎาคม',
    '08' => 'สิงหาคม',
    '09' => 'กันยายน',
    '10' => 'ตุลาคม',
    '11' => 'พฤศจิกายน',
    '12' => 'ธันวาคม',
];

$selected_month = $data['selected_month'] ?? date('m');
$selected_month_name = $month_names[$selected_month] ?? 'มกราคม';
$stats = $data['stats'] ?? [
    'total_customers'   => 0,
    'doc_received'      => 0,
    'completed'         => 0,
    'reviewed'          => 0,
    'tax_filed'         => 0,
    'payment_collected' => 0,
    'doc_received_pct'  => 0,
    'completed_pct'     => 0,
    'reviewed_pct'      => 0,
    'tax_filed_pct'     => 0,
    'payment_pct'       => 0,
    'caretakers'        => [],
    'tasks_list'        => []
];
$caretakers = $stats['caretakers'] ?? [];
$total_caretakers_count = count($caretakers);

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

    .card {
        background-color: #ffffff;
        border: 1px solid #edf2f7;
        border-radius: 12px;
        padding: 10px 20px;
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

    .card-header-icon.blue {
        background-color: #eff6ff;
        color: #3b82f6;
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
        background-color: #cbd5e1;
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

    .filter-select {
        background-color: #f8fafc;
        border: 1px solid #f1f5f9;
        border-radius: 10px;
        padding: 6px 32px 6px 12px;
        font-size: 0.875rem;
        color: #334155;
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
                            <h2 class="page-title">งานรายเดือน</h2>
                            <?php $fy_display = !empty($data['active_fiscal_year']) ? $data['active_fiscal_year'] : 'ไม่ได้เลือกปี'; ?>
                            <p class="page-subtitle">ภาพรวมระบบ - งานรายเดือน - ปี <?php echo htmlspecialchars($fy_display); ?></p>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-muted small">เดือน</span>
                                <select class="form-select filter-select" id="monthSelect" style="min-width: 120px;">
                                    <?php foreach ($month_names as $m_num => $m_name): ?>
                                        <option value="<?php echo $m_num; ?>" <?php echo ($selected_month === $m_num) ? 'selected' : ''; ?>>
                                            <?php echo $m_name; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="button" class="btn-excel-action">
                                <i class="ri-upload-2-line"></i>
                                <span>ส่งออก Excel</span>
                            </button>
                        </div>
                    </div>

                    <!-- Stats Grid (5 กล่องสถิติงานรายเดือน) -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon blue">
                                <i class="ri-user-3-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo number_format($stats['total_customers']); ?></span>
                                <span class="stat-label">ลูกค้าในเดือนนี้</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon green">
                                <i class="ri-file-text-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo number_format($stats['doc_received']); ?></span>
                                <span class="stat-label">ได้รับเอกสารแล้ว</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon purple">
                                <i class="ri-checkbox-circle-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo number_format($stats['completed']); ?></span>
                                <span class="stat-label">ทำเสร็จแล้ว</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon yellow">
                                <i class="ri-mail-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo number_format($stats['tax_filed']); ?></span>
                                <span class="stat-label">ยื่นภาษีแล้ว</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon green">
                                <i class="ri-wallet-3-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo number_format($stats['payment_collected']); ?></span>
                                <span class="stat-label">เก็บเงินลูกค้าแล้ว</span>
                            </div>
                        </div>
                    </div>

                    <!-- Summary Progress Card Section (สรุปความคืบหน้า) -->
                    <div class="card dashboard-progress-card mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h4 class="page-title" style="font-size: 1.15rem; font-weight: 700;">สรุปความคืบหน้า</h4>
                                <p class="page-subtitle">เดือน <?php echo htmlspecialchars($selected_month_name); ?> - ปี <?php echo htmlspecialchars($fy_display); ?> · <?php echo number_format($stats['total_customers']); ?> ลูกค้า</p>
                            </div>
                            <div>
                                <span class="badge rounded-pill px-3 py-2 fw-semibold"
                                    style="background-color: #eff6ff; color: #2563eb; font-size: 0.85rem;">
                                    <?php echo $total_caretakers_count; ?> ผู้ดูแล
                                </span>
                            </div>
                        </div>

                        <!-- Progress Summary Table -->
                        <div class="table-responsive">
                            <table class="table-custom">
                                <thead>
                                    <tr>
                                        <th class="text-start" style="width: 35%;">รายการ</th>
                                        <th class="text-center" style="width: 15%;">%</th>
                                        <th class="text-center" style="width: 15%;">รวม</th>
                                        <?php if (!empty($caretakers)): ?>
                                            <?php foreach ($caretakers as $c): ?>
                                                <th class="text-center" style="width: <?php echo floor(35 / max(1, count($caretakers))); ?>%;"><?php echo htmlspecialchars($c['name']); ?></th>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <th class="text-center" style="width: 35%;">-</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="text-start fw-semibold">ได้รับเอกสาร</td>
                                        <td class="text-center font-monospace text-primary fw-bold"><?php echo $stats['doc_received_pct']; ?>%</td>
                                        <td class="text-center fw-bold"><?php echo $stats['doc_received']; ?>/<?php echo $stats['total_customers']; ?></td>
                                        <?php if (!empty($caretakers)): ?>
                                            <?php foreach ($caretakers as $c): ?>
                                                <td class="text-center"><?php echo $c['doc_received']; ?></td>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <td class="text-center">-</td>
                                        <?php endif; ?>
                                    </tr>
                                    <tr>
                                        <td class="text-start fw-semibold">ทำเสร็จ</td>
                                        <td class="text-center font-monospace text-success fw-bold"><?php echo $stats['completed_pct']; ?>%</td>
                                        <td class="text-center fw-bold"><?php echo $stats['completed']; ?>/<?php echo $stats['total_customers']; ?></td>
                                        <?php if (!empty($caretakers)): ?>
                                            <?php foreach ($caretakers as $c): ?>
                                                <td class="text-center"><?php echo $c['completed']; ?></td>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <td class="text-center">-</td>
                                        <?php endif; ?>
                                    </tr>
                                    <tr>
                                        <td class="text-start fw-semibold">สอบทาน</td>
                                        <td class="text-center font-monospace text-purple fw-bold" style="color: #a855f7;"><?php echo $stats['reviewed_pct']; ?>%</td>
                                        <td class="text-center fw-bold"><?php echo $stats['reviewed']; ?>/<?php echo $stats['total_customers']; ?></td>
                                        <?php if (!empty($caretakers)): ?>
                                            <?php foreach ($caretakers as $c): ?>
                                                <td class="text-center"><?php echo $c['reviewed']; ?></td>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <td class="text-center">-</td>
                                        <?php endif; ?>
                                    </tr>
                                    <tr>
                                        <td class="text-start fw-semibold">ยื่นภาษี</td>
                                        <td class="text-center font-monospace text-warning fw-bold"><?php echo $stats['tax_filed_pct']; ?>%</td>
                                        <td class="text-center fw-bold"><?php echo $stats['tax_filed']; ?>/<?php echo $stats['total_customers']; ?></td>
                                        <?php if (!empty($caretakers)): ?>
                                            <?php foreach ($caretakers as $c): ?>
                                                <td class="text-center"><?php echo $c['tax_filed']; ?></td>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <td class="text-center">-</td>
                                        <?php endif; ?>
                                    </tr>
                                    <tr>
                                        <td class="text-start fw-semibold">เก็บเงิน</td>
                                        <td class="text-center font-monospace text-success fw-bold"><?php echo $stats['payment_pct']; ?>%</td>
                                        <td class="text-center fw-bold"><?php echo $stats['payment_collected']; ?>/<?php echo $stats['total_customers']; ?></td>
                                        <?php if (!empty($caretakers)): ?>
                                            <?php foreach ($caretakers as $c): ?>
                                                <td class="text-center"><?php echo $c['payment_collected']; ?></td>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <td class="text-center">-</td>
                                        <?php endif; ?>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Cards Section: วันที่รับเอกสาร & ความคืบหน้าภาพรวม -->
                    <div class="row g-4 mb-4">
                        <!-- Card 1: รายการลูกค้าในเดือนนี้ -->
                        <div class="col-lg-6 col-12">
                            <div class="card dashboard-progress-card h-100">
                                <div class="d-flex align-items-center gap-3 mb-4">
                                    <div class="card-header-icon blue">
                                        <i class="ri-calendar-line"></i>
                                    </div>
                                    <h5 class="card-section-title">สถานะเอกสารลูกค้าในเดือนนี้</h5>
                                </div>
                                <?php if (!empty($stats['tasks_list'])): ?>
                                    <div class="table-responsive" style="max-height: 280px; overflow-y: auto;">
                                        <table class="table table-sm align-middle mb-0" style="font-size: 0.85rem;">
                                            <thead>
                                                <tr class="text-muted border-bottom">
                                                    <th>ชื่อลูกค้า</th>
                                                    <th>ผู้ดูแล</th>
                                                    <th class="text-center">เอกสาร</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($stats['tasks_list'] as $task_item): ?>
                                                    <tr>
                                                        <td class="fw-semibold text-dark"><?php echo htmlspecialchars($task_item['customer_name']); ?></td>
                                                        <td class="text-muted"><?php echo htmlspecialchars($task_item['caretaker_firstname'] ?? '-'); ?></td>
                                                        <td class="text-center">
                                                            <?php if (($task_item['doc_status'] ?? '0') === '1'): ?>
                                                                <span class="badge bg-success-subtle text-success px-2 py-1">ได้รับแล้ว</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-warning-subtle text-warning px-2 py-1">ยังไม่ได้รับ</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="d-flex flex-column align-items-center justify-content-center py-5 my-3 text-muted">
                                        <i class="ri-file-text-line mb-3" style="font-size: 3rem; color: #cbd5e1;"></i>
                                        <span class="small fw-semibold" style="color: #94a3b8;">ยังไม่มีข้อมูลในเดือนนี้</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Card 2: ความคืบหน้าภาพรวม -->
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
                                        <span class="progress-item-label">ได้รับเอกสาร</span>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="progress-item-value"><?php echo $stats['doc_received']; ?> / <?php echo $stats['total_customers']; ?></span>
                                            <span class="badge rounded-pill progress-badge-zero"><?php echo $stats['doc_received_pct']; ?>%</span>
                                        </div>
                                    </div>
                                    <div class="progress custom-progress-bar">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $stats['doc_received_pct']; ?>%;" aria-valuenow="<?php echo $stats['doc_received_pct']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="progress-item-label">ทำเสร็จ</span>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="progress-item-value"><?php echo $stats['completed']; ?> / <?php echo $stats['total_customers']; ?></span>
                                            <span class="badge rounded-pill progress-badge-zero"><?php echo $stats['completed_pct']; ?>%</span>
                                        </div>
                                    </div>
                                    <div class="progress custom-progress-bar">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $stats['completed_pct']; ?>%;" aria-valuenow="<?php echo $stats['completed_pct']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="progress-item-label">สอบทาน</span>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="progress-item-value"><?php echo $stats['reviewed']; ?> / <?php echo $stats['total_customers']; ?></span>
                                            <span class="badge rounded-pill progress-badge-zero"><?php echo $stats['reviewed_pct']; ?>%</span>
                                        </div>
                                    </div>
                                    <div class="progress custom-progress-bar">
                                        <div class="progress-bar" role="progressbar" style="width: <?php echo $stats['reviewed_pct']; ?>%; background-color: #a855f7;" aria-valuenow="<?php echo $stats['reviewed_pct']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="progress-item-label">ยื่นภาษี</span>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="progress-item-value"><?php echo $stats['tax_filed']; ?> / <?php echo $stats['total_customers']; ?></span>
                                            <span class="badge rounded-pill progress-badge-zero"><?php echo $stats['tax_filed_pct']; ?>%</span>
                                        </div>
                                    </div>
                                    <div class="progress custom-progress-bar">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo $stats['tax_filed_pct']; ?>%;" aria-valuenow="<?php echo $stats['tax_filed_pct']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>

                                <div class="mb-0">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="progress-item-label">เก็บเงิน</span>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="progress-item-value"><?php echo $stats['payment_collected']; ?> / <?php echo $stats['total_customers']; ?></span>
                                            <span class="badge rounded-pill progress-badge-zero"><?php echo $stats['payment_pct']; ?>%</span>
                                        </div>
                                    </div>
                                    <div class="progress custom-progress-bar">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo $stats['payment_pct']; ?>%;" aria-valuenow="<?php echo $stats['payment_pct']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Card: ปริมาณงานแยกตามผู้ดูแล -->
                    <div class="card dashboard-progress-card mb-4">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="card-header-icon green">
                                <i class="ri-user-shared-line"></i>
                            </div>
                            <h5 class="card-section-title">ปริมาณงานแยกตามผู้ดูแล</h5>
                        </div>

                        <?php if (!empty($caretakers)): ?>
                            <div class="d-flex flex-column gap-3">
                                <?php foreach ($caretakers as $c): ?>
                                    <div class="mb-2">
                                        <div class="row align-items-center">
                                            <div class="col-md-4 col-12 mb-2 mb-md-0">
                                                <div class="user-item-name"><?php echo htmlspecialchars($c['name']); ?></div>
                                                <div class="user-item-sub">เสร็จแล้ว <?php echo $c['completed']; ?> จาก <?php echo $c['total']; ?> ราย</div>
                                            </div>
                                            <div class="col-md-8 col-12">
                                                <div class="d-flex justify-content-end align-items-center gap-2 mb-1">
                                                    <span class="fw-bold text-secondary small"><?php echo $c['percent']; ?>%</span>
                                                    <span class="text-muted small"><?php echo $c['pending']; ?> รอดำเนินงาน</span>
                                                </div>
                                                <div class="progress custom-progress-bar-lg">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $c['percent']; ?>%;" aria-valuenow="<?php echo $c['percent']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                                <div class="text-end user-item-status-text mt-1">งาน</div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4 text-muted small">
                                ยังไม่มีข้อมูลผู้ดูแลในเดือนนี้
                            </div>
                        <?php endif; ?>
                    </div>

                </div> <!-- End .main-card-wrapper -->
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        if ($.fn.select2) {
            $('#monthSelect').select2();
        }

        $('#monthSelect').on('change', function() {
            var selectedMonth = $(this).val();
            var url = new URL(window.location.href);
            url.searchParams.set('month', selectedMonth);
            window.location.href = url.toString();
        });
    });
</script>

<?php
// 3. นำ Footer เข้ามา
require_once dirname(__DIR__) . '/main/footer.php';
?>