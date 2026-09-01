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
            
        </div>

        <!--  Footer เข้ามา -->
        <?php require_once dirname(__DIR__) . '/main/footer.php'; ?>

    </div>
</div>