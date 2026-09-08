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

$selected_month = str_pad((string)($data['selected_month'] ?? date('m')), 2, '0', STR_PAD_LEFT);
$selected_month_name = $month_names[$selected_month] ?? $month_names[(int)$selected_month] ?? 'มกราคม';
$stats = $data['stats'] ?? [
    'total_customers' => 0,
    'doc_received' => 0,
    'completed' => 0,
    'reviewed' => 0,
    'tax_filed' => 0,
    'payment_collected' => 0,
    'doc_received_pct' => 0,
    'completed_pct' => 0,
    'reviewed_pct' => 0,
    'tax_filed_pct' => 0,
    'payment_pct' => 0,
    'caretakers' => [],
    'tasks_list' => []
];
$caretakers = $stats['caretakers'] ?? [];
$total_caretakers_count = count($caretakers);

// 1. นำ Header เข้ามา
require_once dirname(__DIR__) . '/main/header.php';

// 2. นำ Sidebar เข้ามา
require_once dirname(__DIR__) . '/main/sidebar.php';
?>

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
                            <p class="page-subtitle">ภาพรวมระบบ - งานรายเดือน - ปี
                                <?php echo htmlspecialchars($fy_display); ?></p>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-muted small">เดือน</span>
                                <select class="form-select filter-select" id="monthSelect" style="min-width: 120px;">
                                    <?php foreach ($month_names as $m_num => $m_name): ?>
                                        <?php $m_val = str_pad((string)$m_num, 2, '0', STR_PAD_LEFT); ?>
                                        <option value="<?php echo $m_val; ?>" <?php echo ($selected_month == $m_val) ? 'selected' : ''; ?>>
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

                    <style>
                        /* Custom Monthly Dashboard Stats Grid Layout */
                        .stats-grid-monthly {
                            display: grid;
                            gap: 16px;
                            margin-bottom: 24px;
                        }

                        @media (min-width: 992px) {
                            .stats-grid-monthly {
                                grid-template-columns: 200px repeat(3, 1fr);
                                grid-template-rows: repeat(2, 1fr);
                            }

                            .stats-grid-monthly .stat-card-overview {
                                grid-row: 1 / span 2;
                                grid-column: 1;
                                display: flex;
                                flex-direction: column;
                                justify-content: center;
                                align-items: center;
                                text-align: center;
                                padding: 24px 16px;
                                gap: 10px;
                            }

                            .stats-grid-monthly .stat-card-overview .stat-icon {
                                width: 52px;
                                height: 52px;
                                font-size: 26px;
                            }

                            .stats-grid-monthly .stat-card-overview .stat-info {
                                align-items: center;
                            }

                            .stats-grid-monthly .stat-card-overview .stat-label {
                                font-size: 1.05rem;
                                font-weight: 700;
                                color: #1e293b;
                            }

                            .stats-grid-monthly .stat-card-overview .stat-subtext {
                                font-size: 0.78rem;
                                color: #64748b;
                            }
                        }

                        @media (min-width: 768px) and (max-width: 991.98px) {
                            .stats-grid-monthly {
                                grid-template-columns: repeat(3, 1fr);
                            }

                            .stats-grid-monthly .stat-card-overview {
                                grid-column: span 3;
                                justify-content: center;
                            }
                        }

                        @media (max-width: 767.98px) {
                            .stats-grid-monthly {
                                grid-template-columns: repeat(2, 1fr);
                            }

                            .stats-grid-monthly .stat-card-overview {
                                grid-column: span 2;
                                justify-content: center;
                            }
                        }
                    </style>

                    <!-- Stats Grid (7 กล่องสถิติงานรายเดือน) -->
                    <div class="stats-grid stats-grid-monthly">
                        <div class="stat-card stat-card-overview active" data-filter="all">
                            <div class="stat-icon blue">
                                <i class="ri-pie-chart-2-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-label">ภาพรวม</span>
                                <span class="stat-subtext">ระบบทั้งหมด</span>
                            </div>
                        </div>

                        <div class="stat-card" data-filter="total_customers">
                            <div class="stat-icon blue">
                                <i class="ri-user-3-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo number_format($stats['total_customers']); ?></span>
                                <span class="stat-label">ลูกค้าในเดือนนี้</span>
                            </div>
                        </div>

                        <div class="stat-card" data-filter="doc_received">
                            <div class="stat-icon green">
                                <i class="ri-file-text-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo number_format($stats['doc_received']); ?></span>
                                <span class="stat-label">ได้รับเอกสารแล้ว</span>
                            </div>
                        </div>

                        <div class="stat-card" data-filter="completed">
                            <div class="stat-icon purple">
                                <i class="ri-checkbox-circle-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo number_format($stats['completed']); ?></span>
                                <span class="stat-label">ทำเสร็จแล้ว</span>
                            </div>
                        </div>

                        <div class="stat-card" data-filter="tax_filed">
                            <div class="stat-icon yellow">
                                <i class="ri-mail-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo number_format($stats['tax_filed']); ?></span>
                                <span class="stat-label">ยื่นภาษีแล้ว</span>
                            </div>
                        </div>

                        <div class="stat-card" data-filter="payment_collected">
                            <div class="stat-icon green">
                                <i class="ri-wallet-3-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo number_format($stats['payment_collected']); ?></span>
                                <span class="stat-label">เก็บเงินลูกค้าแล้ว</span>
                            </div>
                        </div>

                        <div class="stat-card" data-filter="payment_pending">
                            <div class="stat-icon red">
                                <i class="ri-hand-coin-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo number_format($stats['payment_pending'] ?? max(0, $stats['total_customers'] - $stats['payment_collected'])); ?></span>
                                <span class="stat-label">ยังไม่เก็บเงินลูกค้า</span>
                            </div>
                        </div>
                    </div>
                    <!-- ///////////////////////////////////////////  Data Loading Area -->
                    <div>
                        <!-- Dynamic Customer List Card (แสดงรายชื่อลูกค้าตามสถิติที่เลือก - ซ่อนไว้ก่อนเมื่ออยู่หน้าภาพรวม) -->
                        <div id="dataLoadingAreaCard" class="card dashboard-progress-card mb-4" style="display: none; scroll-margin-top: 100px;">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 border-bottom pb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div>
                                        <div class="d-flex align-items-center gap-2">
                                            <h4 class="card-section-title mb-0" id="dataAreaTitle">ภาพรวมรายชื่อลูกค้าทั้งหมดในเดือนนี้</h4>
                                        </div>
                                    </div>
                                </div>

                                <!-- Search -->
                                <div class="d-flex align-items-center gap-2 flex-wrap ms-auto">
                                    <div class="search-box-wrap" style="min-width: 180px; max-width: 240px;">
                                        <i class="ri-search-line"></i>
                                        <input type="text" id="dashCustomerSearch" class="search-input" placeholder="ค้นชื่อลูกค้า / ผู้ดูแล...">
                                    </div>
                                </div>
                            </div>

                            <!-- Customer Tables (Included from table/monthlydash_table.php) -->
                            <?php include __DIR__ . '/table/monthlydash_table.php'; ?>
                        </div>

                        <!-- Overview Summary Section (ส่วนสรุปภาพรวมทั้งหมด - แสดงเมื่อเลือก 'ภาพรวม') -->
                        <div id="overviewSummarySection">
                        <!-- Summary Progress Card Section (สรุปความคืบหน้า) -->
                        <div class="card dashboard-progress-card mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h4 class="page-title" style="font-size: 1.15rem; font-weight: 700;">สรุปความคืบหน้า
                                    </h4>
                                    <p class="page-subtitle">เดือน
                                        <?php echo htmlspecialchars($selected_month_name); ?> - ปี
                                        <?php echo htmlspecialchars($fy_display); ?> ·
                                        <?php echo number_format($stats['total_customers']); ?> ลูกค้า
                                    </p>
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
                                            <td class="text-center font-monospace text-primary fw-bold">
                                                <?php echo $stats['doc_received_pct']; ?>%
                                            </td>
                                            <td class="text-center fw-bold">
                                                <?php echo $stats['doc_received']; ?>/
                                                <?php echo $stats['total_customers']; ?>
                                            </td>
                                            <?php if (!empty($caretakers)): ?>
                                                <?php foreach ($caretakers as $c): ?>
                                                    <td class="text-center">
                                                        <?php echo $c['doc_received']; ?>
                                                    </td>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <td class="text-center">-</td>
                                            <?php endif; ?>
                                        </tr>
                                        <tr>
                                            <td class="text-start fw-semibold">ทำเสร็จ</td>
                                            <td class="text-center font-monospace text-success fw-bold">
                                                <?php echo $stats['completed_pct']; ?>%
                                            </td>
                                            <td class="text-center fw-bold">
                                                <?php echo $stats['completed']; ?>/
                                                <?php echo $stats['total_customers']; ?>
                                            </td>
                                            <?php if (!empty($caretakers)): ?>
                                                <?php foreach ($caretakers as $c): ?>
                                                    <td class="text-center">
                                                        <?php echo $c['completed']; ?>
                                                    </td>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <td class="text-center">-</td>
                                            <?php endif; ?>
                                        </tr>
                                        <tr>
                                            <td class="text-start fw-semibold">สอบทาน</td>
                                            <td class="text-center font-monospace text-purple fw-bold"
                                                style="color: #a855f7;">
                                                <?php echo $stats['reviewed_pct']; ?>%
                                            </td>
                                            <td class="text-center fw-bold">
                                                <?php echo $stats['reviewed']; ?>/
                                                <?php echo $stats['total_customers']; ?>
                                            </td>
                                            <?php if (!empty($caretakers)): ?>
                                                <?php foreach ($caretakers as $c): ?>
                                                    <td class="text-center">
                                                        <?php echo $c['reviewed']; ?>
                                                    </td>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <td class="text-center">-</td>
                                            <?php endif; ?>
                                        </tr>
                                        <tr>
                                            <td class="text-start fw-semibold">ยื่นภาษี</td>
                                            <td class="text-center font-monospace text-warning fw-bold">
                                                <?php echo $stats['tax_filed_pct']; ?>%
                                            </td>
                                            <td class="text-center fw-bold">
                                                <?php echo $stats['tax_filed']; ?>/
                                                <?php echo $stats['total_customers']; ?>
                                            </td>
                                            <?php if (!empty($caretakers)): ?>
                                                <?php foreach ($caretakers as $c): ?>
                                                    <td class="text-center">
                                                        <?php echo $c['tax_filed']; ?>
                                                    </td>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <td class="text-center">-</td>
                                            <?php endif; ?>
                                        </tr>
                                        <tr>
                                            <td class="text-start fw-semibold">เก็บเงิน</td>
                                            <td class="text-center font-monospace text-success fw-bold">
                                                <?php echo $stats['payment_pct']; ?>%
                                            </td>
                                            <td class="text-center fw-bold">
                                                <?php echo $stats['payment_collected']; ?>/
                                                <?php echo $stats['total_customers']; ?>
                                            </td>
                                            <?php if (!empty($caretakers)): ?>
                                                <?php foreach ($caretakers as $c): ?>
                                                    <td class="text-center">
                                                        <?php echo $c['payment_collected']; ?>
                                                    </td>
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
                                                            <td class="fw-semibold text-dark">
                                                                <?php echo htmlspecialchars($task_item['customer_name']); ?>
                                                            </td>
                                                            <td class="text-muted">
                                                                <?php echo htmlspecialchars($task_item['caretaker_firstname'] ?? '-'); ?>
                                                            </td>
                                                            <td class="text-center">
                                                                <?php if (($task_item['doc_status'] ?? '0') === '1'): ?>
                                                                    <span
                                                                        class="badge bg-success-subtle text-success px-2 py-1">ได้รับแล้ว</span>
                                                                <?php else: ?>
                                                                    <span
                                                                        class="badge bg-warning-subtle text-warning px-2 py-1">ยังไม่ได้รับ</span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div
                                            class="d-flex flex-column align-items-center justify-content-center py-5 my-3 text-muted">
                                            <i class="ri-file-text-line mb-3" style="font-size: 3rem; color: #cbd5e1;"></i>
                                            <span class="small fw-semibold"
                                                style="color: #94a3b8;">ยังไม่มีข้อมูลในเดือนนี้</span>
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
                                                <span class="progress-item-value">
                                                    <?php echo $stats['doc_received']; ?> /
                                                    <?php echo $stats['total_customers']; ?>
                                                </span>
                                                <span class="badge rounded-pill progress-badge-zero">
                                                    <?php echo $stats['doc_received_pct']; ?>%
                                                </span>
                                            </div>
                                        </div>
                                        <div class="progress custom-progress-bar">
                                            <div class="progress-bar bg-primary" role="progressbar"
                                                style="width: <?php echo $stats['doc_received_pct']; ?>%;"
                                                aria-valuenow="<?php echo $stats['doc_received_pct']; ?>"
                                                aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="progress-item-label">ทำเสร็จ</span>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="progress-item-value">
                                                    <?php echo $stats['completed']; ?> /
                                                    <?php echo $stats['total_customers']; ?>
                                                </span>
                                                <span class="badge rounded-pill progress-badge-zero">
                                                    <?php echo $stats['completed_pct']; ?>%
                                                </span>
                                            </div>
                                        </div>
                                        <div class="progress custom-progress-bar">
                                            <div class="progress-bar bg-success" role="progressbar"
                                                style="width: <?php echo $stats['completed_pct']; ?>%;"
                                                aria-valuenow="<?php echo $stats['completed_pct']; ?>" aria-valuemin="0"
                                                aria-valuemax="100"></div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="progress-item-label">สอบทาน</span>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="progress-item-value">
                                                    <?php echo $stats['reviewed']; ?> /
                                                    <?php echo $stats['total_customers']; ?>
                                                </span>
                                                <span class="badge rounded-pill progress-badge-zero">
                                                    <?php echo $stats['reviewed_pct']; ?>%
                                                </span>
                                            </div>
                                        </div>
                                        <div class="progress custom-progress-bar">
                                            <div class="progress-bar" role="progressbar"
                                                style="width: <?php echo $stats['reviewed_pct']; ?>%; background-color: #a855f7;"
                                                aria-valuenow="<?php echo $stats['reviewed_pct']; ?>" aria-valuemin="0"
                                                aria-valuemax="100"></div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="progress-item-label">ยื่นภาษี</span>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="progress-item-value">
                                                    <?php echo $stats['tax_filed']; ?> /
                                                    <?php echo $stats['total_customers']; ?>
                                                </span>
                                                <span class="badge rounded-pill progress-badge-zero">
                                                    <?php echo $stats['tax_filed_pct']; ?>%
                                                </span>
                                            </div>
                                        </div>
                                        <div class="progress custom-progress-bar">
                                            <div class="progress-bar bg-warning" role="progressbar"
                                                style="width: <?php echo $stats['tax_filed_pct']; ?>%;"
                                                aria-valuenow="<?php echo $stats['tax_filed_pct']; ?>" aria-valuemin="0"
                                                aria-valuemax="100"></div>
                                        </div>
                                    </div>

                                    <div class="mb-0">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="progress-item-label">เก็บเงิน</span>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="progress-item-value">
                                                    <?php echo $stats['payment_collected']; ?> /
                                                    <?php echo $stats['total_customers']; ?>
                                                </span>
                                                <span class="badge rounded-pill progress-badge-zero">
                                                    <?php echo $stats['payment_pct']; ?>%
                                                </span>
                                            </div>
                                        </div>
                                        <div class="progress custom-progress-bar">
                                            <div class="progress-bar bg-info" role="progressbar"
                                                style="width: <?php echo $stats['payment_pct']; ?>%;"
                                                aria-valuenow="<?php echo $stats['payment_pct']; ?>" aria-valuemin="0"
                                                aria-valuemax="100"></div>
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
                                                    <div class="user-item-name">
                                                        <?php echo htmlspecialchars($c['name']); ?>
                                                    </div>
                                                    <div class="user-item-sub">เสร็จแล้ว
                                                        <?php echo $c['completed']; ?> จาก
                                                        <?php echo $c['total']; ?> ราย
                                                    </div>
                                                </div>
                                                <div class="col-md-8 col-12">
                                                    <div class="d-flex justify-content-end align-items-center gap-2 mb-1">
                                                        <span class="fw-bold text-secondary small">
                                                            <?php echo $c['percent']; ?>%
                                                        </span>
                                                        <span class="text-muted small">
                                                            <?php echo $c['pending']; ?> รอดำเนินงาน
                                                        </span>
                                                    </div>
                                                    <div class="progress custom-progress-bar-lg">
                                                        <div class="progress-bar bg-success" role="progressbar"
                                                            style="width: <?php echo $c['percent']; ?>%;"
                                                            aria-valuenow="<?php echo $c['percent']; ?>" aria-valuemin="0"
                                                            aria-valuemax="100"></div>
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
                        </div> <!-- End #overviewSummarySection -->

                    </div> <!-- End .main-card-wrapper -->

                </div>


            </div>
        </div>
    </div>
</div>

<script>
    const allTasksData = <?php echo json_encode($stats['tasks_list'] ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    const baseUrl = "<?php echo BASE_URL; ?>";
    const selectedMonth = "<?php echo $selected_month; ?>";

    const filterConfig = {
        'all': {
            title: 'ภาพรวมระบบและสรุปสถิติงานรายเดือน'
        },
        'total_customers': {
            title: 'รายชื่อลูกค้าทั้งหมดในเดือนนี้'
        },
        'doc_received': {
            title: 'รายชื่อลูกค้าที่ได้รับเอกสารแล้ว'
        },
        'completed': {
            title: 'รายชื่อลูกค้าที่ทำเสร็จแล้ว'
           
        },
        'tax_filed': {
            title: 'รายชื่อลูกค้าที่ยื่นภาษีแล้ว'
           
        },
        'payment_collected': {
            title: 'รายชื่อลูกค้าที่เก็บเงินลูกค้าแล้ว'
            
        },
        'payment_pending': {
            title: 'รายชื่อลูกค้าที่ยังไม่เก็บเงินลูกค้า'
            
        }
    };

    let currentFilter = 'all';

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function renderDashCustomerList(filterType = currentFilter, searchQuery = '') {
        currentFilter = filterType;

        // Highlight top stat cards
        $('.stat-card').removeClass('active');
        $(`.stat-card[data-filter="${filterType}"]`).addClass('active');

        if (filterType === 'all') {
            // เมื่อเลือก 'ภาพรวม': ซ่อนตารางรายชื่อลูกค้า และแสดงเฉพาะส่วนสรุปภาพรวม
            $('#dataLoadingAreaCard').hide();
            $('#overviewSummarySection').stop(true, true).fadeIn(200);
            return;
        } else {
            // เมื่อเลือกหมวดหมู่อื่นๆ: ซ่อนส่วนสรุปภาพรวม และแสดงเฉพาะตารางรายชื่อลูกค้าของหมวดหมู่นั้น
            $('#overviewSummarySection').hide();
            $('#dataLoadingAreaCard').stop(true, true).fadeIn(200);
        }

        const config = filterConfig[filterType] || filterConfig['all'];

        // Filter Tasks
        const filteredTasks = allTasksData.filter(item => {
            if (searchQuery) {
                const q = searchQuery.trim().toLowerCase();
                const cName = (item.customer_name || '').toLowerCase();
                const uName = (item.caretaker_firstname || '').toLowerCase();
                if (!cName.includes(q) && !uName.includes(q)) return false;
            }

            const isDocReceived = String(item.doc_status) === '1';
            const totalTasks = parseInt(item.total_tasks || 0);
            const completedTasks = parseInt(item.completed_tasks || 0);
            const isCompleted = totalTasks > 0 && completedTasks === totalTasks;
            const isTaxFiled = String(item.tax_status) === '1';
            const isPaymentCollected = String(item.payment_status) === '1';
            const isPaymentPending = String(item.payment_status) !== '1';

            if (filterType === 'doc_received') return isDocReceived;
            if (filterType === 'completed') return isCompleted;
            if (filterType === 'tax_filed') return isTaxFiled;
            if (filterType === 'payment_collected') return isPaymentCollected;
            if (filterType === 'payment_pending') return isPaymentPending;
            if (filterType === 'total_customers' || filterType === 'all') return true;
            return true;
        });

        $('#dataAreaTitle').text(config.title);

        // Toggle table section matching filterType
        $('.dash-table-sec').hide();
        $(`#table_sec_${filterType}`).show();

        const tbody = $(`#customerListTbody_${filterType}`);
        tbody.empty();

        if (filteredTasks.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="3" class="text-center py-5 text-muted">
                        <i class="ri-search-2-line mb-2 display-6 text-slate-300 d-block"></i>
                        <span class="fw-semibold">ไม่พบข้อมูลลูกค้าตามเงื่อนไขที่เลือก</span>
                    </td>
                </tr>
            `);
            return;
        }

        filteredTasks.forEach((item, index) => {
            tbody.append(`
                <tr>
                    <td class="text-center font-monospace text-muted small">${index + 1}</td>
                    <td>
                        <div>${escapeHtml(item.customer_name)}</div>
                    </td>
                    <td>
                        <span>${escapeHtml(item.caretaker_firstname || 'ไม่ระบุ')}</span>
                    </td>
                </tr>
            `);
        });
    }

    $(document).ready(function () {
        if ($.fn.select2) {
            $('#monthSelect').select2();
        }

        // Initial render (แสดงหน้าภาพรวมเป็นค่าเริ่มต้น)
        renderDashCustomerList('all');

        // เมื่อเปลี่ยนเดือน (onchange) ให้เปลี่ยนเดือนและกลับไปแสดงหน้าภาพรวม
        $('#monthSelect').on('change', function () {
            var selectedMonth = $(this).val();
            var url = new URL(window.location.href);
            url.searchParams.set('month', selectedMonth);
            url.searchParams.delete('filter');
            window.location.href = url.toString();
        });

        // เมื่อกดคลิกที่กล่อง stat-card
        $(document).on('click', '.stat-card[data-filter]', function () {
            const filterType = $(this).data('filter');
            const searchVal = $('#dashCustomerSearch').val();
            renderDashCustomerList(filterType, searchVal);
        });

        // Search input typing
        $('#dashCustomerSearch').on('input', function () {
            const searchVal = $(this).val();
            renderDashCustomerList(currentFilter, searchVal);
        });
    });
</script>

<?php
// 3. นำ Footer เข้ามา
require_once dirname(__DIR__) . '/main/footer.php';
?>