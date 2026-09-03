<?php
// app/views/backoffice/index.php
$selected_year = $_GET['year'] ?? '2569';
$company_name  = $_GET['company'] ?? 'TEST ACCOUNTING';
$show_company_workspace = true;

// 1. นำ Header เข้ามา
require_once dirname(__DIR__) . '/main/header.php';

// 2. นำ Sidebar เข้ามา
require_once dirname(__DIR__) . '/main/sidebar.php';
?>

<link rel="stylesheet" href="/cpd_ac/public/template/assets/css/sidebar-menu.css">

<style>
    /* --- Dashboard Layout Styles --- */
    .content-wrapper {
        padding: 24px 32px 32px 32px;
        min-height: calc(100vh - 140px);
        font-family: 'Kanit', 'Segoe UI', Tahoma, sans-serif;
    }

    /* Page Header */
    .dashboard-header-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
    }

    .dashboard-title {
        font-size: 1.35rem;
        font-weight: 800;
        color: #1e293b;
        margin: 0 0 2px 0;
    }

    .dashboard-sub {
        font-size: 0.82rem;
        color: #64748b;
        margin: 0;
    }

    .btn-change-year {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        color: #334155;
        padding: 7px 16px;
        border-radius: 8px;
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
        padding: 7px 18px;
        border-radius: 8px;
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
        box-shadow: 0 1px 4px rgba(16, 24, 40, 0.02);
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
        background-color: #ebf5ff;
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
        box-shadow: 0 1px 4px rgba(16, 24, 40, 0.02);
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
        background-color: #ebf5ff;
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

    @media (max-width: 1200px) {
        .stat-cards-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .status-cards-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="container-fluid">
    <div class="main-content d-flex flex-column">

        <div class="content-wrapper">
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
                    <a href="javascript:void(0);" class="btn-manage-month">
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
                        <h3 class="stat-number">2</h3>
                        <h4 class="stat-label-title">ลูกค้าทั้งหมด</h4>
                        <p class="stat-sub-desc">ใช้บริการอยู่ 2 ราย</p>
                    </div>
                </div>
                <!-- Card 2 -->
                <div class="stat-card-item">
                    <div class="stat-icon-circle stat-icon-green">
                        <i class="ri-wallet-3-line"></i>
                    </div>
                    <div>
                        <h3 class="stat-number">99,999</h3>
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
                        <h3 class="stat-number">5</h3>
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
                        <h3 class="stat-number">2</h3>
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
                                <p class="status-header-p">2 ลูกค้าในรอบงานเดือนนี้</p>
                            </div>
                        </div>
                        <a href="javascript:void(0);" class="btn-badge-link-blue">ดูแดชบอร์ดรายเดือน</a>
                    </div>

                    <div class="progress-rows-list">
                        <!-- Row 1 -->
                        <div class="progress-item-line">
                            <div class="progress-item-title">ได้รับเอกสารแล้ว</div>
                            <div class="progress-item-bar"><div class="progress-item-fill" style="width: 0%; background-color: #cbd5e1;"></div></div>
                            <div class="progress-item-percentage">0/2 &nbsp; 0%</div>
                        </div>
                        <!-- Row 2 -->
                        <div class="progress-item-line">
                            <div class="progress-item-title">ทำเสร็จแล้ว</div>
                            <div class="progress-item-bar"><div class="progress-item-fill" style="width: 0%; background-color: #cbd5e1;"></div></div>
                            <div class="progress-item-percentage">0/2 &nbsp; 0%</div>
                        </div>
                        <!-- Row 3 -->
                        <div class="progress-item-line">
                            <div class="progress-item-title">ยื่นภาษีแล้ว</div>
                            <div class="progress-item-bar"><div class="progress-item-fill" style="width: 0%; background-color: #cbd5e1;"></div></div>
                            <div class="progress-item-percentage">0/2 &nbsp; 0%</div>
                        </div>
                        <!-- Row 4 -->
                        <div class="progress-item-line">
                            <div class="progress-item-title">เก็บเงินลูกค้าแล้ว</div>
                            <div class="progress-item-bar"><div class="progress-item-fill" style="width: 0%; background-color: #cbd5e1;"></div></div>
                            <div class="progress-item-percentage">0/2 &nbsp; 0%</div>
                        </div>
                    </div>

                    <a href="javascript:void(0);" class="btn-manage-month btn-card-bottom-action">
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
                                <p class="status-header-p">2 ลูกค้าที่ต้องปิดงบ</p>
                            </div>
                        </div>
                        <a href="javascript:void(0);" class="btn-badge-link-green">ดูแดชบอร์ดรายปี</a>
                    </div>

                    <div class="progress-rows-list">
                        <!-- Row 1 -->
                        <div class="progress-item-line">
                            <div class="progress-item-title">ปิดงบเสร็จแล้ว</div>
                            <div class="progress-item-bar"><div class="progress-item-fill" style="width: 50%; background-color: #8b5cf6;"></div></div>
                            <div class="progress-item-percentage">1/2 &nbsp; 50%</div>
                        </div>
                        <!-- Row 2 -->
                        <div class="progress-item-line">
                            <div class="progress-item-title">ได้รับงบคืนแล้ว</div>
                            <div class="progress-item-bar"><div class="progress-item-fill" style="width: 50%; background-color: #22c55e;"></div></div>
                            <div class="progress-item-percentage">1/2 &nbsp; 50%</div>
                        </div>
                        <!-- Row 3 -->
                        <div class="progress-item-line">
                            <div class="progress-item-title">บอจ. 5 นำส่งแล้ว</div>
                            <div class="progress-item-bar"><div class="progress-item-fill" style="width: 50%; background-color: #3b82f6;"></div></div>
                            <div class="progress-item-percentage">1/2 &nbsp; 50%</div>
                        </div>
                        <!-- Row 4 -->
                        <div class="progress-item-line">
                            <div class="progress-item-title">DBD E-Filing นำส่งแล้ว</div>
                            <div class="progress-item-bar"><div class="progress-item-fill" style="width: 0%; background-color: #cbd5e1;"></div></div>
                            <div class="progress-item-percentage">0/2 &nbsp; 0%</div>
                        </div>
                        <!-- Row 5 -->
                        <div class="progress-item-line">
                            <div class="progress-item-title">ภ.ง.ด.50 นำส่งแล้ว</div>
                            <div class="progress-item-bar"><div class="progress-item-fill" style="width: 0%; background-color: #cbd5e1;"></div></div>
                            <div class="progress-item-percentage">0/2 &nbsp; 0%</div>
                        </div>
                    </div>

                    <a href="javascript:void(0);" class="btn-manage-month btn-card-bottom-action" style="background-color: #16a34a; box-shadow: 0 2px 6px rgba(22, 163, 74, 0.2);">
                        <i class="ri-file-list-3-line"></i> จัดการปิดงบการเงิน
                    </a>
                </div>
            </div>
        </div>

        <!--  Footer เข้ามา -->
        <?php require_once dirname(__DIR__) . '/main/footer.php'; ?>

    </div>
</div>