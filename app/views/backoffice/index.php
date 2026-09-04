<?php
// app/views/backoffice/index.php
$selected_year = $_GET['year'] ?? '0';
$company_name  = $_GET['company'] ?? 'TEST ACCOUNTING';
$show_company_workspace = true;

// 1. นำ Header เข้ามา
require_once dirname(__DIR__) . '/main/header.php';

// 2. นำ Sidebar เข้ามา
require_once dirname(__DIR__) . '/main/sidebar.php';

$baseUrl = defined('BASE_URL') ? BASE_URL : '/cpd_ac/public';
?>

<link rel="stylesheet" href="/cpd_ac/public/template/assets/css/sidebar-menu.css">

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

    /* Page Header Section */
    .dashboard-header-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 14px;
    }

    .dashboard-title {
        font-size: 1.35rem;
        font-weight: 800;
        color: #1e293b;
        margin: 0 0 3px 0;
        letter-spacing: -0.2px;
    }

    .dashboard-sub {
        font-size: 0.78rem;
        color: #94a3b8;
        font-weight: 500;
        margin: 0;
    }

    .btn-change-year {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        color: #334155;
        padding: 8px 16px;
        border-radius: 10px;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .btn-change-year:hover {
        background-color: #f8fafc;
        border-color: #cbd5e1;
        color: #0f172a;
    }

    .btn-manage-month {
        background-color: #0066fe;
        color: #ffffff;
        border: none;
        padding: 8px 18px;
        border-radius: 10px;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
        transition: all 0.2s ease;
        box-shadow: 0 2px 6px rgba(0, 102, 254, 0.2);
    }

    .btn-manage-month:hover {
        background-color: #0052cc;
        color: #ffffff;
    }

    /* --- 4 Stat Cards Grid --- */
    .stat-cards-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 24px;
    }

    .stat-card-item {
        background: #ffffff;
        border-radius: 14px;
        border: 1px solid #edf2f7;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        display: flex;
        align-items: center;
        gap: 16px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .stat-card-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(16, 24, 40, 0.06);
    }

    .stat-icon-circle {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
    }

    .stat-icon-blue {
        background-color: #eff6ff;
        color: #0066fe;
    }

    .stat-icon-green {
        background-color: #dcfce7;
        color: #16a34a;
    }

    .stat-icon-purple {
        background-color: #f3e8ff;
        color: #9333ea;
    }

    .stat-icon-yellow {
        background-color: #fef3c7;
        color: #d97706;
    }

    .stat-icon-red {
        background-color: #ffe4e6;
        color: #e11d48;
    }

    .stat-number {
        font-size: 1.45rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.1;
        margin: 0;
    }

    .stat-label-title {
        font-size: 0.85rem;
        font-weight: 700;
        color: #334155;
        margin: 3px 0 2px 0;
    }

    .stat-sub-desc {
        font-size: 0.72rem;
        color: #94a3b8;
        margin: 0;
    }

    /* --- 2 Progress / Status Cards Grid --- */
    .status-cards-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 24px;
    }

    .status-box-card {
        background: #ffffff;
        border-radius: 14px;
        border: 1px solid #edf2f7;
        padding: 24px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .status-box-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .status-header-title-box {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .status-header-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .status-header-h4 {
        font-size: 1.05rem;
        font-weight: 800;
        color: #1e293b;
        margin: 0;
    }

    .status-header-p {
        font-size: 0.75rem;
        color: #64748b;
        margin: 0;
    }

    .btn-badge-link-blue {
        background-color: #eff6ff;
        color: #0066fe;
        border: none;
        padding: 5px 12px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .btn-badge-link-blue:hover {
        background-color: #0066fe;
        color: #ffffff;
    }

    .btn-badge-link-green {
        background-color: #dcfce7;
        color: #16a34a;
        border: none;
        padding: 5px 12px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .btn-badge-link-green:hover {
        background-color: #16a34a;
        color: #ffffff;
    }

    /* Progress Rows */
    .progress-rows-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
        margin-bottom: 24px;
    }

    .progress-item-line {
        display: grid;
        grid-template-columns: 140px 1fr 60px;
        align-items: center;
        gap: 16px;
        font-size: 0.82rem;
    }

    .progress-item-title {
        color: #334155;
        font-weight: 500;
        white-space: nowrap;
    }

    .progress-item-bar {
        height: 8px;
        background-color: #f1f5f9;
        border-radius: 999px;
        overflow: hidden;
        width: 100%;
    }

    .progress-item-fill {
        height: 100%;
        border-radius: 999px;
    }

    .progress-item-percentage {
        text-align: right;
        font-weight: 700;
        font-size: 0.75rem;
        color: #0f172a;
        white-space: nowrap;
    }

    .btn-card-bottom-action {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 18px;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
        align-self: flex-start;
        transition: all 0.2s ease;
    }

    /* --- Two Column Grid (Attention & System Status) --- */
    .two-col-grid {
        display: grid;
        grid-template-columns: 1.4fr 1fr;
        gap: 20px;
        margin-bottom: 24px;
    }

    .attention-card,
    .system-status-card,
    .shortcuts-card {
        background: #ffffff;
        border-radius: 14px;
        border: 1px solid #edf2f7;
        padding: 24px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    }

    .card-section-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
    }

    .header-icon-circle {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .header-icon-yellow {
        background-color: #fef3c7;
        color: #d97706;
    }

    .header-icon-blue {
        background-color: #eff6ff;
        color: #0066fe;
    }

    .section-title {
        font-size: 1.05rem;
        font-weight: 800;
        color: #1e293b;
        margin: 0;
    }

    /* Attention Items List */
    .attention-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .attention-item {
        background-color: #ffffff;
        border: 1px solid #f1f5f9;
        border-radius: 12px;
        padding: 12px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: all 0.2s ease;
    }

    .attention-item:hover {
        border-color: #e2e8f0;
        background-color: #f8fafc;
    }

    .attention-item-left {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .attention-icon-box {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .attention-title {
        font-size: 0.88rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0 0 2px 0;
    }

    .attention-sub {
        font-size: 0.74rem;
        color: #64748b;
        margin: 0;
    }

    .count-badge-pill {
        min-width: 22px;
        height: 22px;
        padding: 0 6px;
        border-radius: 999px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 800;
        flex-shrink: 0;
    }

    .count-badge-green {
        background-color: #dcfce7;
        color: #16a34a;
    }

    .count-badge-blue {
        background-color: #eff6ff;
        color: #0066fe;
    }

    .count-badge-purple {
        background-color: #f3e8ff;
        color: #9333ea;
    }

    .count-badge-red {
        background-color: #ffe4e6;
        color: #e11d48;
    }

    /* System Status List */
    .system-status-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .system-status-item {
        background-color: #f8fafc;
        border-radius: 10px;
        padding: 12px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .status-item-title {
        font-size: 0.88rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0 0 2px 0;
    }

    .status-item-sub {
        font-size: 0.73rem;
        color: #64748b;
        margin: 0;
    }

    .status-pill {
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .status-pill-blue {
        background-color: #eff6ff;
        color: #0066fe;
    }

    .status-pill-amber {
        background-color: #fef3c7;
        color: #d97706;
    }

    .status-pill-purple {
        background-color: #f3e8ff;
        color: #9333ea;
    }

    .status-pill-red {
        background-color: #ffe4e6;
        color: #e11d48;
    }

    /* Shortcuts Grid */
    .shortcuts-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
    }

    .shortcut-item-btn {
        background-color: #ffffff;
        border: 1px solid #edf2f7;
        border-radius: 12px;
        padding: 14px 16px;
        display: flex;
        align-items: center;
        gap: 14px;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .shortcut-item-btn:hover {
        border-color: #cbd5e1;
        background-color: #f8fafc;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }

    .shortcut-icon-circle {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .shortcut-text {
        font-size: 0.88rem;
        font-weight: 700;
        color: #1e293b;
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .stat-cards-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .status-cards-grid {
            grid-template-columns: 1fr;
        }

        .two-col-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .shortcuts-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 480px) {
        .shortcuts-grid {
            grid-template-columns: 1fr;
        }

        .stat-cards-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="container-fluid">
    <div class="main-content d-flex flex-column">
        <div class="content-wrapper">
            <div class="main-page-wrapper">
                <div class="main-card-wrapper">

                    <!-- Header Row -->
                    <div class="dashboard-header-row">
                        <div>
                            <h2 class="dashboard-title">ภาพรวมสำนักงาน</h2>
                            <p class="dashboard-sub">ภาพรวมระบบ - ปี <?php echo htmlspecialchars($selected_year); ?></p>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <a href="javascript:void(0);" class="btn-change-year">
                                <i class="ri-calendar-line"></i> เปลี่ยนปีทำงาน
                            </a>
                            <a href="<?php echo $baseUrl; ?>/monthly_task" class="btn-manage-month">
                                <i class="ri-calendar-check-line"></i> จัดการงานเดือนนี้
                            </a>
                        </div>
                    </div>

                    <!-- 4 Stat Cards -->
                    <div class="stat-cards-grid">
                        <!-- Card 1 -->
                        <div class="stat-card-item">
                            <div class="stat-icon-circle stat-icon-blue">
                                <i class="ri-user-line"></i>
                            </div>
                            <div>
                                <h3 class="stat-number">0</h3>
                                <h4 class="stat-label-title">ลูกค้าทั้งหมด</h4>
                                <p class="stat-sub-desc">ใช้บริการอยู่ 0 ราย</p>
                            </div>
                        </div>
                        <!-- Card 2 -->
                        <div class="stat-card-item">
                            <div class="stat-icon-circle stat-icon-green">
                                <i class="ri-wallet-3-line"></i>
                            </div>
                            <div>
                                <h3 class="stat-number">0</h3>
                                <h4 class="stat-label-title">ค่าทำบัญชีต่อเดือน</h4>
                                <p class="stat-sub-desc">จากลูกค้าที่ใช้งานอยู่</p>
                            </div>
                        </div>
                        <!-- Card 3 -->
                        <div class="stat-card-item">
                            <div class="stat-icon-circle stat-icon-purple">
                                <i class="ri-group-line"></i>
                            </div>
                            <div>
                                <h3 class="stat-number">0</h3>
                                <h4 class="stat-label-title">พนักงานที่ทำงานอยู่</h4>
                                <p class="stat-sub-desc">0 ทีม</p>
                            </div>
                        </div>
                        <!-- Card 4 -->
                        <div class="stat-card-item">
                            <div class="stat-icon-circle stat-icon-yellow">
                                <i class="ri-file-list-3-line"></i>
                            </div>
                            <div>
                                <h3 class="stat-number">0</h3>
                                <h4 class="stat-label-title">ลูกค้าปิดงบประจำปี</h4>
                                <p class="stat-sub-desc">ปีทำงาน <?php echo htmlspecialchars($selected_year); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- 2 Status Cards -->
                    <div class="status-cards-grid">
                        <!-- Status Box 1: Monthly -->
                        <div class="status-box-card">
                            <div class="status-box-header">
                                <div class="status-header-title-box">
                                    <div class="status-header-icon stat-icon-blue">
                                        <i class="ri-calendar-event-line"></i>
                                    </div>
                                    <div>
                                        <h4 class="status-header-h4">สถานะงานเดือนกันยายน</h4>
                                        <p class="status-header-p">0 ลูกค้าในรอบงานเดือนนี้</p>
                                    </div>
                                </div>
                                <a href="<?php echo $baseUrl; ?>/monthly_dash" class="btn-badge-link-blue">ดูแดชบอร์ดรายเดือน</a>
                            </div>

                            <div class="progress-rows-list">
                                <!-- Row 1 -->
                                <div class="progress-item-line">
                                    <div class="progress-item-title">ได้รับเอกสารแล้ว</div>
                                    <div class="progress-item-bar"><div class="progress-item-fill"></div></div>
                                    <div class="progress-item-percentage">0/0 &nbsp; 0%</div>
                                </div>
                                <!-- Row 2 -->
                                <div class="progress-item-line">
                                    <div class="progress-item-title">ทำเสร็จแล้ว</div>
                                    <div class="progress-item-bar"><div class="progress-item-fill"></div></div>
                                    <div class="progress-item-percentage">0/0 &nbsp; 0%</div>
                                </div>
                                <!-- Row 3 -->
                                <div class="progress-item-line">
                                    <div class="progress-item-title">ยื่นภาษีแล้ว</div>
                                    <div class="progress-item-bar"><div class="progress-item-fill"></div></div>
                                    <div class="progress-item-percentage">0/0 &nbsp; 0%</div>
                                </div>
                                <!-- Row 4 -->
                                <div class="progress-item-line">
                                    <div class="progress-item-title">เก็บเงินลูกค้าแล้ว</div>
                                    <div class="progress-item-bar"><div class="progress-item-fill"></div></div>
                                    <div class="progress-item-percentage">0/0 &nbsp; 0%</div>
                                </div>
                            </div>

                            <a href="<?php echo $baseUrl; ?>/monthly_task" class="btn-manage-month btn-card-bottom-action">
                                <i class="ri-calendar-check-line"></i> จัดการงานรายเดือน
                            </a>
                        </div>

                        <!-- Status Box 2: Yearly -->
                        <div class="status-box-card">
                            <div class="status-box-header">
                                <div class="status-header-title-box">
                                    <div class="status-header-icon stat-icon-green">
                                        <i class="ri-file-text-line"></i>
                                    </div>
                                    <div>
                                        <h4 class="status-header-h4">สถานะปิดงบรายปี</h4>
                                        <p class="status-header-p">0 ลูกค้าที่ต้องปิดงบ</p>
                                    </div>
                                </div>
                                <a href="<?php echo $baseUrl; ?>/yearly_dash" class="btn-badge-link-green">ดูแดชบอร์ดรายปี</a>
                            </div>

                            <div class="progress-rows-list">
                                <!-- Row 1 -->
                                <div class="progress-item-line">
                                    <div class="progress-item-title">ปิดงบเสร็จแล้ว</div>
                                    <div class="progress-item-bar"><div class="progress-item-fill"></div></div>
                                    <div class="progress-item-percentage">0/0 &nbsp; 0%</div>
                                </div>
                                <!-- Row 2 -->
                                <div class="progress-item-line">
                                    <div class="progress-item-title">ได้รับงบคืนแล้ว</div>
                                    <div class="progress-item-bar"><div class="progress-item-fill"></div></div>
                                    <div class="progress-item-percentage">0/0 &nbsp; 0%</div>
                                </div>
                                <!-- Row 3 -->
                                <div class="progress-item-line">
                                    <div class="progress-item-title">บอจ. 5 นำส่งแล้ว</div>
                                    <div class="progress-item-bar"><div class="progress-item-fill"></div></div>
                                    <div class="progress-item-percentage">0/0 &nbsp; 0%</div>
                                </div>
                                <!-- Row 4 -->
                                <div class="progress-item-line">
                                    <div class="progress-item-title">DBD E-Filing นำส่งแล้ว</div>
                                    <div class="progress-item-bar"><div class="progress-item-fill"></div></div>
                                    <div class="progress-item-percentage">0/0 &nbsp; 0%</div>
                                </div>
                                <!-- Row 5 -->
                                <div class="progress-item-line">
                                    <div class="progress-item-title">ภ.ง.ด.50 นำส่งแล้ว</div>
                                    <div class="progress-item-bar"><div class="progress-item-fill"></div></div>
                                    <div class="progress-item-percentage">0/0 &nbsp; 0%</div>
                                </div>
                            </div>

                            <a href="<?php echo $baseUrl; ?>/closing" class="btn-manage-month btn-card-bottom-action" style="background-color: #16a34a; box-shadow: 0 2px 6px rgba(22, 163, 74, 0.2);">
                                <i class="ri-file-list-3-line"></i> จัดการปิดงบการเงิน
                            </a>
                        </div>
                    </div>

                    <!-- Two Column Grid: Attention Items & System Status -->
                    <div class="two-col-grid">
                        <!-- Left: Attention Items -->
                        <div class="attention-card">
                            <div class="card-section-header">
                                <div class="header-icon-circle header-icon-yellow">
                                    <i class="ri-error-warning-line"></i>
                                </div>
                                <h3 class="section-title">รายการที่ควรรีบดู</h3>
                            </div>

                            <div class="attention-list">
                                <!-- Item 1 -->
                                <div class="attention-item">
                                    <div class="attention-item-left">
                                        <div class="attention-icon-box stat-icon-yellow">
                                            <i class="ri-user-line"></i>
                                        </div>
                                        <div>
                                            <h4 class="attention-title">ลูกค้ายังไม่มีผู้ดูแล</h4>
                                            <p class="attention-sub">ไม่มีรายการค้าง</p>
                                        </div>
                                    </div>
                                    <span class="count-badge-pill count-badge-green">0</span>
                                </div>

                                <!-- Item 2 -->
                                <div class="attention-item">
                                    <div class="attention-item-left">
                                        <div class="attention-icon-box stat-icon-blue">
                                            <i class="ri-calendar-line"></i>
                                        </div>
                                        <div>
                                            <h4 class="attention-title">ลูกค้ายังไม่ได้ตั้งวันสิ้นรอบบัญชี</h4>
                                            <p class="attention-sub">AMLAW, FOLK</p>
                                        </div>
                                    </div>
                                    <span class="count-badge-pill count-badge-blue">2</span>
                                </div>

                                <!-- Item 3 -->
                                <div class="attention-item">
                                    <div class="attention-item-left">
                                        <div class="attention-icon-box stat-icon-purple">
                                            <i class="ri-smartphone-line"></i>
                                        </div>
                                        <div>
                                            <h4 class="attention-title">ลูกค้ายังไม่มีช่องทางติดต่อ</h4>
                                            <p class="attention-sub">AMLAW, FOLK</p>
                                        </div>
                                    </div>
                                    <span class="count-badge-pill count-badge-purple">2</span>
                                </div>

                                <!-- Item 4 -->
                                <div class="attention-item">
                                    <div class="attention-item-left">
                                        <div class="attention-icon-box stat-icon-red">
                                            <i class="ri-sticky-note-line"></i>
                                        </div>
                                        <div>
                                            <h4 class="attention-title">Post-it ที่ยังไม่ได้ดำเนินการ</h4>
                                            <p class="attention-sub">เบิกทดลอง</p>
                                        </div>
                                    </div>
                                    <span class="count-badge-pill count-badge-red">1</span>
                                </div>
                            </div>
                        </div>

                        <!-- Right: System Status -->
                        <div class="system-status-card">
                            <div class="card-section-header">
                                <div class="header-icon-circle header-icon-blue">
                                    <i class="ri-dashboard-line"></i>
                                </div>
                                <h3 class="section-title">สถานะระบบ</h3>
                            </div>

                            <div class="system-status-list">
                                <!-- Status Item 1 -->
                                <div class="system-status-item">
                                    <div>
                                        <h4 class="status-item-title">ปีทำงาน</h4>
                                        <p class="status-item-sub">ข้อมูลลูกค้าหน้าอ้างอิงปีนี้</p>
                                    </div>
                                    <span class="status-pill status-pill-blue">ปี <?php echo htmlspecialchars($selected_year); ?></span>
                                </div>

                                <!-- Status Item 2 -->
                                <div class="system-status-item">
                                    <div>
                                        <h4 class="status-item-title">LINE Token</h4>
                                        <p class="status-item-sub">ใช้สำหรับส่งข้อความถึงลูกค้า</p>
                                    </div>
                                    <span class="status-pill status-pill-amber">ยังไม่ได้ตั้งค่า</span>
                                </div>

                                <!-- Status Item 3 -->
                                <div class="system-status-item">
                                    <div>
                                        <h4 class="status-item-title">งานรายเดือน</h4>
                                        <p class="status-item-sub">รายการ master ของงานที่ทำงาน</p>
                                    </div>
                                    <span class="status-pill status-pill-purple">14 งาน</span>
                                </div>

                                <!-- Status Item 4 -->
                                <div class="system-status-item">
                                    <div>
                                        <h4 class="status-item-title">Post-it ค้าง</h4>
                                        <p class="status-item-sub">เกินกำหนด 0 รายการ</p>
                                    </div>
                                    <span class="status-pill status-pill-red">1 ค้าง</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Shortcuts Section -->
                    <div class="shortcuts-card">
                        <div class="card-section-header">
                            <div class="header-icon-circle header-icon-blue">
                                <i class="ri-compass-3-line"></i>
                            </div>
                            <h3 class="section-title">ทางลัด</h3>
                        </div>

                        <div class="shortcuts-grid">
                            <!-- Shortcut 1 -->
                            <a href="<?php echo $baseUrl; ?>/customer" class="shortcut-item-btn">
                                <div class="shortcut-icon-circle stat-icon-blue">
                                    <i class="ri-user-add-line"></i>
                                </div>
                                <span class="shortcut-text">เพิ่ม/นำเข้าลูกค้า</span>
                            </a>

                            <!-- Shortcut 2 -->
                            <a href="<?php echo $baseUrl; ?>/employee" class="shortcut-item-btn">
                                <div class="shortcut-icon-circle stat-icon-purple">
                                    <i class="ri-group-line"></i>
                                </div>
                                <span class="shortcut-text">จัดการพนักงาน</span>
                            </a>

                            <!-- Shortcut 3 -->
                            <a href="<?php echo $baseUrl; ?>/tasks" class="shortcut-item-btn">
                                <div class="shortcut-icon-circle stat-icon-green">
                                    <i class="ri-checkbox-circle-line"></i>
                                </div>
                                <span class="shortcut-text">ตั้งค่างานที่ต้องทำ</span>
                            </a>

                            <!-- Shortcut 4 -->
                            <a href="<?php echo $baseUrl; ?>/monthly_task" class="shortcut-item-btn">
                                <div class="shortcut-icon-circle stat-icon-yellow">
                                    <i class="ri-calendar-line"></i>
                                </div>
                                <span class="shortcut-text">งานรายเดือน</span>
                            </a>

                            <!-- Shortcut 5 -->
                            <a href="<?php echo $baseUrl; ?>/closing" class="shortcut-item-btn">
                                <div class="shortcut-icon-circle stat-icon-green">
                                    <i class="ri-file-text-line"></i>
                                </div>
                                <span class="shortcut-text">ปิดงบการเงิน</span>
                            </a>

                            <!-- Shortcut 6 -->
                            <a href="javascript:void(0);" class="shortcut-item-btn">
                                <div class="shortcut-icon-circle stat-icon-red">
                                    <i class="ri-sticky-note-line"></i>
                                </div>
                                <span class="shortcut-text">Post-it แจ้งเตือน</span>
                            </a>
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