<?php
// app/views/backoffice/yearly_dash.php
$selected_year = $_GET['year'] ?? '2569';
$company_name = $_GET['company'] ?? 'TEST ACCOUNTING';
$show_company_workspace = true;

$stats = $data['stats'] ?? [
    'total_customers' => 0,
    'closing_completed' => 0,
    'audit_completed' => 0,
    'boj5' => 0,
    'dbd' => 0,
    'pnd50' => 0,
    'closing_completed_pct' => 0,
    'audit_completed_pct' => 0,
    'boj5_pct' => 0,
    'dbd_pct' => 0,
    'pnd50_pct' => 0,
    'caretakers' => [],
    'closing_list' => []
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
                            <h2 class="page-title">งานรายปี</h2>
                            <?php $fy_display = !empty($data['active_fiscal_year']) ? $data['active_fiscal_year'] : 'ไม่ได้เลือกปี'; ?>
                            <p class="page-subtitle">ภาพรวมระบบ - งานรายปี - ปี <?php echo htmlspecialchars($fy_display); ?></p>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <button type="button" class="btn-excel-action">
                                <i class="ri-upload-2-line"></i>
                                <span>ส่งออก Excel</span>
                            </button>
                        </div>
                    </div>

                    <style>
                        /* Custom Yearly Dashboard Stats Grid Layout */
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

                    <!-- Stats Grid (7 กล่องสถิติงานรายปี) -->
                    <div class="stats-grid stats-grid-monthly">
                        <div class="stat-card stat-card-overview active" data-filter="all">
                            <div class="stat-icon blue">
                                <i class="ri-pie-chart-2-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-label">ภาพรวม</span>
                                <span class="stat-subtext">ระบบงานรายปี</span>
                            </div>
                        </div>

                        <div class="stat-card" data-filter="total_customers">
                            <div class="stat-icon blue">
                                <i class="ri-user-3-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo number_format($stats['total_customers']); ?></span>
                                <span class="stat-label">ลูกค้าปิดงบ</span>
                            </div>
                        </div>

                        <div class="stat-card" data-filter="closing_completed">
                            <div class="stat-icon purple">
                                <i class="ri-checkbox-circle-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo number_format($stats['closing_completed']); ?></span>
                                <span class="stat-label">ปิดงบเสร็จ</span>
                            </div>
                        </div>

                        <div class="stat-card" data-filter="audit_completed">
                            <div class="stat-icon green">
                                <i class="ri-draft-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo number_format($stats['audit_completed']); ?></span>
                                <span class="stat-label">ได้รับงบคืน</span>
                            </div>
                        </div>

                        <div class="stat-card" data-filter="boj5">
                            <div class="stat-icon blue">
                                <i class="ri-file-paper-2-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo number_format($stats['boj5']); ?></span>
                                <span class="stat-label">บอจ. 5</span>
                            </div>
                        </div>

                        <div class="stat-card" data-filter="dbd">
                            <div class="stat-icon yellow">
                                <i class="ri-draft-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo number_format($stats['dbd']); ?></span>
                                <span class="stat-label">DBD E-Filing</span>
                            </div>
                        </div>

                        <div class="stat-card" data-filter="pnd50">
                            <div class="stat-icon green">
                                <i class="ri-file-check-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo number_format($stats['pnd50']); ?></span>
                                <span class="stat-label">ภ.ง.ด.50</span>
                            </div>
                        </div>
                    </div>

                    <!-- Data Loading Area -->
                    <div>
                        <!-- Dynamic Customer List Card -->
                        <div id="dataLoadingAreaCard" class="card dashboard-progress-card mb-4" style="display: none; scroll-margin-top: 100px;">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 border-bottom pb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div>
                                        <div class="d-flex align-items-center gap-2">
                                            <h4 class="card-section-title mb-0" id="dataAreaTitle">รายชื่อลูกค้าปิดงบทั้งหมดในงานรายปี</h4>
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

                            <!-- Customer Tables (Included from table/yearlydash_table.php) -->
                            <?php include __DIR__ . '/table/yearlydash_table.php'; ?>
                        </div>

                        <!-- Overview Summary Section -->
                        <div id="overviewSummarySection">
                            <!-- Summary Progress Card Section (สรุปความคืบหน้าปิดงบการเงิน) -->
                            <div class="card dashboard-progress-card mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div>
                                        <h4 class="page-title" style="font-size: 1.15rem; font-weight: 700;">สรุปความคืบหน้าปิดงบการเงิน</h4>
                                        <p class="page-subtitle">ปี
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

                                <!-- Cards Section: ความคืบหน้าภาพรวม & ปริมาณงานแยกตามผู้ดูแล -->
                                <div class="row g-4">
                                    <!-- Card 1: ความคืบหน้าภาพรวม -->
                                    <div class="col-lg-6 col-12">
                                        <div class="p-3 border rounded-3 bg-light-subtle h-100">
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
                                                        <span class="progress-item-value"><?php echo $stats['closing_completed']; ?> / <?php echo $stats['total_customers']; ?></span>
                                                        <span class="badge rounded-pill progress-badge-zero"><?php echo $stats['closing_completed_pct']; ?>%</span>
                                                    </div>
                                                </div>
                                                <div class="progress custom-progress-bar">
                                                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $stats['closing_completed_pct']; ?>%;" aria-valuenow="<?php echo $stats['closing_completed_pct']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span class="progress-item-label">ได้รับงบคืน</span>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="progress-item-value"><?php echo $stats['audit_completed']; ?> / <?php echo $stats['total_customers']; ?></span>
                                                        <span class="badge rounded-pill progress-badge-zero"><?php echo $stats['audit_completed_pct']; ?>%</span>
                                                    </div>
                                                </div>
                                                <div class="progress custom-progress-bar">
                                                    <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo $stats['audit_completed_pct']; ?>%;" aria-valuenow="<?php echo $stats['audit_completed_pct']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span class="progress-item-label">บอจ. 5</span>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="progress-item-value"><?php echo $stats['boj5']; ?> / <?php echo $stats['total_customers']; ?></span>
                                                        <span class="badge rounded-pill progress-badge-zero"><?php echo $stats['boj5_pct']; ?>%</span>
                                                    </div>
                                                </div>
                                                <div class="progress custom-progress-bar">
                                                    <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo $stats['boj5_pct']; ?>%;" aria-valuenow="<?php echo $stats['boj5_pct']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span class="progress-item-label">DBD E-Filing</span>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="progress-item-value"><?php echo $stats['dbd']; ?> / <?php echo $stats['total_customers']; ?></span>
                                                        <span class="badge rounded-pill progress-badge-zero"><?php echo $stats['dbd_pct']; ?>%</span>
                                                    </div>
                                                </div>
                                                <div class="progress custom-progress-bar">
                                                    <div class="progress-bar bg-secondary" role="progressbar" style="width: <?php echo $stats['dbd_pct']; ?>%;" aria-valuenow="<?php echo $stats['dbd_pct']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </div>

                                            <div class="mb-0">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span class="progress-item-label">ภ.ง.ด.50</span>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="progress-item-value"><?php echo $stats['pnd50']; ?> / <?php echo $stats['total_customers']; ?></span>
                                                        <span class="badge rounded-pill progress-badge-zero"><?php echo $stats['pnd50_pct']; ?>%</span>
                                                    </div>
                                                </div>
                                                <div class="progress custom-progress-bar">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $stats['pnd50_pct']; ?>%;" aria-valuenow="<?php echo $stats['pnd50_pct']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Card 2: ปริมาณงานแยกตามผู้ดูแล -->
                                    <div class="col-lg-6 col-12">
                                        <div class="p-3 border rounded-3 bg-light-subtle h-100">
                                            <div class="d-flex align-items-center gap-3 mb-4">
                                                <div class="card-header-icon green">
                                                    <i class="ri-user-shared-line"></i>
                                                </div>
                                                <h5 class="card-section-title">ปริมาณงานปิดงบแยกตามผู้ดูแล</h5>
                                            </div>

                                            <?php if (!empty($caretakers)): ?>
                                                <div class="d-flex flex-column gap-3">
                                                    <?php foreach ($caretakers as $c): ?>
                                                        <div class="mb-2">
                                                            <div class="row align-items-center">
                                                                <div class="col-md-5 col-12 mb-2 mb-md-0">
                                                                    <div class="user-item-name">
                                                                        <?php echo htmlspecialchars($c['name']); ?>
                                                                    </div>
                                                                    <div class="user-item-sub">เสร็จแล้ว
                                                                        <?php echo $c['completed']; ?> จาก
                                                                        <?php echo $c['total']; ?> ราย
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-7 col-12">
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
                                                                    <div class="text-end user-item-status-text mt-1">ปิดงบ</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="text-center py-4 text-muted small">
                                                    ยังไม่มีข้อมูลผู้ดูแลในงานรายปี
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div> <!-- End #overviewSummarySection -->

                    </div> <!-- End .main-card-wrapper -->

                </div>

            </div>
        </div>
    </div>
</div>

<script>
    const allClosingData = <?php echo json_encode($stats['closing_list'] ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

    const filterConfigYearly = {
        'all': {
            title: 'ภาพรวมระบบและสรุปสถิติทั่วไปของงานรายปี'
        },
        'total_customers': {
            title: 'รายชื่อลูกค้าปิดงบทั้งหมด'
        },
        'closing_completed': {
            title: 'รายชื่อลูกค้าที่ปิดงบเสร็จแล้ว'
        },
        'audit_completed': {
            title: 'รายชื่อลูกค้าที่ได้รับงบคืนแล้ว'
        },
        'boj5': {
            title: 'รายชื่อลูกค้าที่ทำ บอจ. 5 แล้ว'
        },
        'dbd': {
            title: 'รายชื่อลูกค้าที่ทำ DBD E-Filing แล้ว'
        },
        'pnd50': {
            title: 'รายชื่อลูกค้าที่ยื่น ภ.ง.ด.50 แล้ว'
        }
    };

    let currentFilterYearly = 'all';

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function renderYearlyDashCustomerList(filterType = currentFilterYearly, searchQuery = '') {
        currentFilterYearly = filterType;

        // Highlight stat card
        $('.stat-card').removeClass('active');
        $(`.stat-card[data-filter="${filterType}"]`).addClass('active');

        if (filterType === 'all') {
            $('#dataLoadingAreaCard').hide();
            $('#overviewSummarySection').stop(true, true).fadeIn(200);
            return;
        } else {
            $('#overviewSummarySection').hide();
            $('#dataLoadingAreaCard').stop(true, true).fadeIn(200);
        }

        const config = filterConfigYearly[filterType] || filterConfigYearly['all'];

        // Filter Data
        const filteredTasks = allClosingData.filter(item => {
            if (searchQuery) {
                const q = searchQuery.trim().toLowerCase();
                const cName = (item.customer_name || '').toLowerCase();
                const uName = (item.user_firstname || '').toLowerCase();
                if (!cName.includes(q) && !uName.includes(q)) return false;
            }

            const isClosingDone = String(item.closing_status) === '1';
            const isDocDone = String(item.doc_status) === '1' || String(item.audit_status) === '1';
            const isBoj5Done = String(item.boj5_status) === '1';
            const isDbdDone = String(item.dbd_efiling_status) === '1';
            const isPnd50Done = String(item.pnd50_status) === '1';

            if (filterType === 'closing_completed') return isClosingDone;
            if (filterType === 'audit_completed') return isDocDone;
            if (filterType === 'boj5') return isBoj5Done;
            if (filterType === 'dbd') return isDbdDone;
            if (filterType === 'pnd50') return isPnd50Done;
            if (filterType === 'total_customers' || filterType === 'all') return true;
            return true;
        });

        $('#dataAreaTitle').text(config.title);

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
            const caretakerName = (item.user_firstname || '') + ' ' + (item.user_lastname || '');
            tbody.append(`
                <tr>
                    <td class="text-center font-monospace text-muted small">${index + 1}</td>
                    <td>
                        <div>${escapeHtml(item.customer_name)}</div>
                    </td>
                    <td>
                        <span>${escapeHtml(caretakerName.trim() || 'ไม่ระบุ')}</span>
                    </td>
                </tr>
            `);
        });
    }

    $(document).ready(function () {
        // Initial render
        renderYearlyDashCustomerList('all');

        // Click stat-card
        $(document).on('click', '.stat-card[data-filter]', function () {
            const filterType = $(this).data('filter');
            const searchVal = $('#dashCustomerSearch').val();
            renderYearlyDashCustomerList(filterType, searchVal);
        });

        // Search input typing
        $('#dashCustomerSearch').on('input', function () {
            const searchVal = $(this).val();
            renderYearlyDashCustomerList(currentFilterYearly, searchVal);
        });
    });
</script>

<?php
// 3. นำ Footer เข้ามา
require_once dirname(__DIR__) . '/main/footer.php';
?>