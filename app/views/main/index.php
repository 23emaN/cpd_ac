<?php 
// app/views/main/index.php
require_once __DIR__ . '/header.php'; 
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
        grid-template-columns: repeat(auto-fill, minmax(290px, 320px));
        gap: 24px;
    }

    /* Empty Dashed Add Year Card */
    .year-add-card {
        background-color: #ffffff;
        border: 2px dashed #cbd5e1;
        border-radius: 14px;
        min-height: 230px;
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

<div class="main-page-wrapper">
    <!-- 1. Page Header / Breadcrumb (Aligned in the same grid/plane) -->
    <div class="page-title-section">
        <h2 class="page-main-heading">เลือกปีทำงาน</h2>
        <p class="page-breadcrumb-sub">ภาพรวมระบบ • เลือกปีทำงาน</p>
    </div>

    <!-- 2. Main Content Card -->
    <div class="year-container-card">
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
            <!-- <div>
                <button type="button" class="btn-add-year">
                    <i class="ri-add-line fs-5"></i>
                    <span>เพิ่มปี</span>
                </button>
            </div> -->
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
            <a href="javascript:void(0);" class="year-add-card" title="เพิ่มปีทำงาน">
                <div class="year-add-icon">
                    <i class="ri-add-line"></i>
                </div>
                <span class="year-add-text">เพิ่มปี</span>
                <span class="year-add-subtext">คลิกเพื่อสร้างปีทำงานใหม่</span>
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>