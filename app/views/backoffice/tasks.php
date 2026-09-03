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

<style>
    /* --- Year Selection Page Styles --- */
    body {
        background-color: #f8fafc;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .main-page-wrapper {
        padding: 28px 36px;
        min-height: calc(100vh - 72px);
    }

    /* Page Header */
    .page-title-section {
        margin-bottom: 24px;
    }

    .page-main-heading {
        font-size: 1.35rem;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 3px;
        letter-spacing: -0.2px;
    }

    .page-breadcrumb-sub {
        font-size: 0.82rem;
        color: #64748b;
        margin: 0;
    }

    /* Main Container Card */
    .year-container-card {
        background-color: #ffffff;
        border-radius: 16px;
        border: 1px solid #edf2f7;
        box-shadow: 0 2px 12px rgba(16, 24, 40, 0.03);
        padding: 32px;
    }

    /* Section Header */
    .section-header-wrap {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
    }

    .section-title-box {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .section-icon-badge {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background-color: #ebf5ff;
        color: #1e70eb;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
    }

    .section-title-text {
        font-size: 1.15rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 2px;
    }

    .section-subtitle-text {
        font-size: 0.82rem;
        color: #64748b;
        margin: 0;
    }

    .btn-add-year {
        background-color: #0066fe;
        color: #ffffff;
        border: none;
        border-radius: 10px;
        padding: 9px 20px;
        font-size: 0.90rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
        box-shadow: 0 2px 8px rgba(0, 102, 254, 0.2);
    }

    .btn-add-year:hover {
        background-color: #0052cc;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(0, 102, 254, 0.3);
    }

    /* Information Notice Banner */
    .year-notice-banner {
        background-color: #f4f8ff;
        border: 1px dashed #93c5fd;
        border-radius: 12px;
        padding: 18px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 28px;
    }

    .notice-main-text {
        font-size: 0.88rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 4px;
    }

    .notice-sub-text {
        font-size: 0.80rem;
        color: #64748b;
        margin: 0;
    }

    .notice-selected-box {
        text-align: right;
        flex-shrink: 0;
        margin-left: 20px;
    }

    .notice-selected-label {
        font-size: 0.72rem;
        color: #94a3b8;
        margin-bottom: 2px;
        display: block;
    }

    .notice-selected-value {
        font-size: 1.05rem;
        font-weight: 800;
        color: #0066fe;
        display: block;
    }

    /* Year Selection Grid & Add Card */
    .year-card-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 340px));
        gap: 24px;
    }

    /* Fiscal Year Card (ตรงตามภาพ) */
    .fiscal-year-card {
        background-color: #ffffff;
        border: 1px solid #edf2f7;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 290px;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .fiscal-year-card:hover {
        border-color: #bfdbfe;
        box-shadow: 0 8px 24px rgba(37, 99, 235, 0.08);
        transform: translateY(-2px);
    }

    .fy-card-icon-box {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        background-color: #eff6ff;
        color: #2563eb;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }

    .fy-badge-active {
        background-color: #dcfce7;
        color: #16a34a;
        font-weight: 700;
        font-size: 0.78rem;
        padding: 5px 12px;
        border-radius: 8px;
        white-space: nowrap;
    }

    .fy-label-sub {
        font-size: 0.82rem;
        font-weight: 600;
        color: #94a3b8;
        margin: 16px 0 3px 0;
    }

    .fy-val-title {
        font-size: 1.55rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
        line-height: 1.1;
    }

    .fy-divider-dashed {
        border-top: 1px dashed #e2e8f0;
        margin: 16px 0;
    }

    .fy-stat-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .fy-stat-label {
        font-size: 0.85rem;
        color: #64748b;
        font-weight: 600;
    }

    .fy-stat-val {
        font-size: 0.92rem;
        font-weight: 800;
        color: #0f172a;
    }

    .fy-actions-row {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 18px;
    }

    .btn-fy-select {
        flex-grow: 1;
        background-color: #eff6ff;
        color: #2563eb;
        border: none;
        border-radius: 10px;
        padding: 10px 16px;
        font-size: 0.88rem;
        font-weight: 700;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .btn-fy-select:hover {
        background-color: #dbeafe;
        color: #1d4ed8;
    }

    .btn-fy-edit {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        background-color: #f8fafc;
        border: 1px solid #f1f5f9;
        color: #475569;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        flex-shrink: 0;
    }

    .btn-fy-edit:hover {
        background-color: #f1f5f9;
        color: #0f172a;
        border-color: #e2e8f0;
    }

    /* Empty Dashed Add Year Card */
    .year-add-card {
        background-color: #ffffff;
        border: 2px dashed #cbd5e1;
        border-radius: 16px;
        min-height: 290px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s ease;
        padding: 24px;
    }

    .year-add-card:hover {
        border-color: #0066fe;
        background-color: #f4f8ff;
        box-shadow: 0 4px 16px rgba(0, 102, 254, 0.08);
    }

    .year-add-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background-color: #f1f5f9;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        transition: all 0.2s ease;
        margin-bottom: 4px;
    }

    .year-add-card:hover .year-add-icon {
        background-color: #0066fe;
        color: #ffffff;
    }

    .year-add-text {
        font-size: 1.05rem;
        font-weight: 700;
        color: #334155;
        transition: color 0.2s ease;
    }

    .year-add-card:hover .year-add-text {
        color: #0066fe;
    }

    .year-add-subtext {
        font-size: 0.78rem;
        color: #94a3b8;
        transition: color 0.2s ease;
    }

    .year-add-card:hover .year-add-subtext {
        color: #60a5fa;
    }
</style>
<div class="content-wrapper">
<div class="main-page-wrapper">
    <!-- 2. Main Content Card -->
    <div class="year-container-card">
        <div class="page-title-section">
        <h2 class="page-main-heading">เลือกปีทำงาน</h2>
        <p class="page-breadcrumb-sub">ภาพรวมระบบ • เลือกปีทำงาน</p>
    </div>
        <!-- Section Header (Title & Add Button) -->
        <div class="section-header-wrap">
            <div class="section-title-box">
                <div class="section-icon-badge">
                    <i class="ri-calendar-2-line"></i>
                </div>
                <div>
                    <h3 class="section-title-text">เลือกปีที่ต้องการทำงาน</h3>
                    <p class="section-subtitle-text">Accounting</p>
                </div>
            </div>
        </div>

        <!-- Info Notice Banner -->
        <div class="year-notice-banner">
            <div>
                <div class="notice-main-text">ปีทำงานจะเป็นตัวกรองหลักของข้อมูลสำนักงาน</div>
                <p class="notice-sub-text">เมื่อเลือกปีแล้ว ระบบจะใช้ปีนั้นกับหน้าลูกค้า พนักงาน งานรายเดือน และรายงานที่จะเพิ่มต่อไป</p>
            </div>
            <div class="notice-selected-box">
                <span class="notice-selected-label">ปีที่เลือก</span>
                <span class="notice-selected-value">ยังไม่ได้เลือก</span>
            </div>
        </div>

        <!-- Year Card Grid (Empty Add Year State) -->
        <div class="year-card-grid">
            <button type="button" class="year-add-card" onclick="Addyear()">
                <div class="year-add-icon">
                    <i class="ri-add-line"></i>
                </div>
                <span class="year-add-text">เพิ่มปี</span>
                <span class="year-add-subtext">คลิกเพื่อสร้างปีทำงานใหม่</span>
            </button>
        </div>
    </div>
</div>
</div>