<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title><?php echo htmlspecialchars($data['title'] ?? 'CPD ACC - ระบบบริหารสำนักงานบัญชี') ?></title>

    <link rel="icon" type="image/png" href="/cpd_ac/public/assets/images/am-group-logo.png">
    <link rel="apple-touch-icon" href="/cpd_ac/public/assets/images/am-group-logo.png">

    <link rel="stylesheet" href="/cpd_ac/public/template/assets/css/font.css">
    <link rel="stylesheet" href="/cpd_ac/public/template/assets/css/sidebar-menu.css">
    <link rel="stylesheet" href="/cpd_ac/public/template/assets/css/simplebar.css">
    <link rel="stylesheet" href="/cpd_ac/public/template/assets/css/apexcharts.css">
    <link rel="stylesheet" href="/cpd_ac/public/template/assets/css/prism.css">
    <link rel="stylesheet" href="/cpd_ac/public/template/assets/css/rangeslider.css">
    <link rel="stylesheet" href="/cpd_ac/public/template/assets/css/google-icon.css">
    <link rel="stylesheet" href="/cpd_ac/public/template/assets/css/remixicon.css">
    <link rel="stylesheet" href="/cpd_ac/public/template/assets/css/swiper-bundle.min.css">
    <link rel="stylesheet" href="/cpd_ac/public/template/assets/css/fullcalendar.main.css">
    <link rel="stylesheet" href="/cpd_ac/public/template/assets/css/jsvectormap.min.css">
    <link rel="stylesheet" href="/cpd_ac/public/template/assets/css/lightpick.css">
    <link rel="stylesheet" href="/cpd_ac/public/template/assets/css/select2.min.css">
    <link rel="stylesheet" href="/cpd_ac/public/template/assets/css/style.css">
    <link rel="stylesheet" href="/cpd_ac/public/template/assets/css/toastr.min.css">
    <link rel="stylesheet" href="/cpd_ac/public/template/assets/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="/cpd_ac/public/template/assets/css/sweetalert2.min.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <link rel="stylesheet"
        href="/cpd_ac/public/template/assets/css/custom.css?ver=<?php echo @filemtime(dirname(__DIR__, 2) . '/public/template/assets/css/custom.css') ?: time(); ?>">
    <link rel="stylesheet" href="/cpd_ac/public/template/assets/css/web.css">
    <link rel="stylesheet"
        href="/cpd_ac/public/template/assets/css/ui.css?ver=<?php echo @filemtime(dirname(__DIR__, 2) . '/public/template/assets/css/ui.css') ?: time(); ?>">

    <style>
        /* --- CPD ACC Modern Header & Workspace Dropdown --- */
        .acc-topbar {
            background-color: #F7F9FB;
            /* border-bottom: 1px solid #edf2f7; */
            padding: 15px 40px;
            min-height: 68px;
            position: sticky;
            top: 0;
            z-index: 1000;
            display: flex !important;
            flex-grow: 1 !important;
            justify-content: space-between;
            align-items: center;
            font-family: 'Kanit', sans-serif;
            /* box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02); */
        }

        .acc-brand-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            flex-shrink: 0;
            padding-right: 18px;
            border-right: 1px solid #e2e8f0;
        }

        .acc-brand-logo {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        .acc-brand-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .acc-brand-info .acc-brand-title {
            font-size: 1.1rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.2;
            margin: 0;
            letter-spacing: -0.2px;
        }

        .acc-brand-info .acc-brand-subtitle {
            font-size: 0.72rem;
            color: #64748b;
            margin: 0;
            line-height: 1.2;
            font-weight: 500;
        }

        /* Company / Workspace Tab Bar Container */
        .acc-company-container {
            display: flex;
            align-items: center;
            gap: 10px;
            overflow-x: auto;
            max-width: calc(100vw - 480px);
            padding: 4px 6px 4px 16px;
            scrollbar-width: thin;
        }

        .acc-company-container::-webkit-scrollbar {
            height: 3px;
        }

        .acc-company-container::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        /* Workspace Dropdown Container */
        .acc-workspace-dropdown {
            position: relative;
            flex-shrink: 0;
        }

        /* Sleek Modern Workspace Button (ตามภาพต้นแบบ 1) */
        .acc-workspace-btn {
            background-color: #ffffff;
            border: 1.5px solid #edf2f7;
            border-radius: 14px;
            padding: 6px 14px 6px 10px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            text-decoration: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            white-space: nowrap;
            user-select: none;
        }

        .acc-workspace-btn::after {
            display: none !important;
            /* Hide default bootstrap dropdown caret */
        }

        .acc-workspace-btn:hover {
            border-color: #3b82f6;
            background-color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(59, 130, 246, 0.10);
        }

        .acc-workspace-btn.active,
        .acc-workspace-dropdown.show .acc-workspace-btn {
            border-color: #2563eb;
            background-color: #ffffff;
            box-shadow: 0 4px 16px rgba(37, 99, 235, 0.14);
        }

        .acc-workspace-icon {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background-color: #eff6ff;
            color: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 19px;
            flex-shrink: 0;
            transition: all 0.2s ease;
        }

        .acc-workspace-btn:hover .acc-workspace-icon,
        .acc-workspace-btn.active .acc-workspace-icon {
            background-color: #dbeafe;
            color: #1d4ed8;
        }

        .acc-workspace-info {
            display: flex;
            flex-direction: column;
            text-align: left;
            line-height: 1.15;
        }

        .acc-workspace-badge {
            font-size: 0.65rem;
            font-weight: 800;
            color: #94a3b8;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .acc-workspace-name {
            font-size: 0.90rem;
            font-weight: 800;
            color: #0f172a;
            max-width: 170px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .acc-workspace-year {
            font-size: 0.74rem;
            font-weight: 600;
            color: #64748b;
            margin-top: 1px;
        }

        .acc-workspace-arrow {
            color: #64748b;
            font-size: 18px;
            margin-left: 2px;
            transition: transform 0.2s ease, color 0.2s ease;
        }

        .acc-workspace-dropdown.show .acc-workspace-arrow {
            transform: rotate(180deg);
            color: #2563eb;
        }

        /* --- Workspace Dropdown Menu (ตามภาพต้นแบบ 2 - แสดงลอยอยู่ด้านหน้าไม่โดนตัด) --- */
        .acc-workspace-menu {
            border: 1px solid #edf2f7;
            border-radius: 16px;
            box-shadow: 0 16px 48px rgba(15, 23, 42, 0.16);
            padding: 18px;
            min-width: 320px;
            max-width: 360px;
            background: #ffffff;
            z-index: 99999 !important;
            animation: dropdownFadeIn 0.15s ease-out;
        }

        @keyframes dropdownFadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .acc-menu-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .acc-menu-header-icon {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background-color: #eff6ff;
            color: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }

        .acc-menu-title {
            font-size: 0.98rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            line-height: 1.2;
        }

        .acc-menu-subtitle {
            font-size: 0.78rem;
            color: #64748b;
            margin: 3px 0 0 0;
            line-height: 1.2;
            font-weight: 500;
        }

        /* การ์ดปีที่ใช้งานอยู่ (Active Year Card) */
        .acc-active-year-card {
            background-color: #eff6ff;
            border: 1px solid #dbeafe;
            border-radius: 12px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .acc-active-year-card:hover {
            background-color: #e0f0fe;
            border-color: #bfdbfe;
        }

        .acc-active-year-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .acc-active-year-icon {
            width: 32px;
            height: 32px;
            color: #2563eb;
            font-size: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .acc-active-year-info {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        .acc-active-year-label {
            font-size: 0.72rem;
            color: #64748b;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .acc-active-year-val {
            font-size: 1rem;
            font-weight: 800;
            color: #0f172a;
        }

        .acc-active-badge {
            color: #0066fe;
            font-weight: 700;
            font-size: 0.82rem;
            white-space: nowrap;
        }

        /* หมวดหมู่: เลือกปีอื่น */
        .acc-other-years-wrap {
            margin-bottom: 12px;
        }

        .acc-other-years-title {
            font-size: 0.80rem;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 8px;
            padding-left: 2px;
        }

        .acc-other-years-list {
            display: flex;
            flex-direction: column;
            gap: 4px;
            max-height: 175px;
            overflow-y: auto;
            scrollbar-width: thin;
        }

        .acc-other-years-list::-webkit-scrollbar {
            width: 4px;
        }

        .acc-other-years-list::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .acc-other-year-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 10px;
            border-radius: 10px;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .acc-other-year-item:hover {
            background-color: #f8fafc;
            transform: translateX(2px);
        }

        .acc-other-year-icon {
            font-size: 20px;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            flex-shrink: 0;
            transition: color 0.15s ease;
        }

        .acc-other-year-item:hover .acc-other-year-icon {
            color: #2563eb;
        }

        .acc-other-year-info {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        .acc-other-year-val {
            font-size: 0.88rem;
            font-weight: 700;
            color: #1e293b;
            transition: color 0.15s ease;
        }

        .acc-other-year-item:hover .acc-other-year-val {
            color: #2563eb;
        }

        .acc-other-year-sub {
            font-size: 0.74rem;
            color: #94a3b8;
            margin-top: 1px;
        }

        /* ท้ายเมนู: จัดการปีทำงาน */
        .acc-menu-footer {
            border-top: 1px solid #f1f5f9;
            padding-top: 10px;
            margin-top: 8px;
        }

        .acc-manage-year-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            border-radius: 8px;
            text-decoration: none;
            color: #1e293b;
            font-weight: 700;
            font-size: 0.88rem;
            transition: all 0.15s ease;
        }

        .acc-manage-year-btn:hover {
            background-color: #eff6ff;
            color: #2563eb;
        }

        .acc-manage-year-btn i {
            font-size: 18px;
            color: #475569;
            transition: color 0.15s ease;
        }

        .acc-manage-year-btn:hover i {
            color: #2563eb;
        }

        /* ปุ่มเพิ่มบริษัท */
        .acc-add-workspace-btn {
            height: 52px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: #ffffff;
            border: 1.5px dashed #cbd5e1;
            border-radius: 14px;
            padding: 6px 14px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
            color: #475569;
            flex-shrink: 0;
        }

        .acc-add-workspace-btn:hover {
            border-color: #2563eb;
            background-color: #eff6ff;
            color: #2563eb;
            transform: translateY(-1px);
        }

        .acc-add-workspace-icon {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            background-color: #f1f5f9;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            transition: all 0.2s ease;
        }

        .acc-add-workspace-btn:hover .acc-add-workspace-icon {
            background-color: #2563eb;
            color: #ffffff;
        }

        .acc-add-workspace-text {
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.2px;
        }

        /* Right Actions & User Profile */
        .acc-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
        }

        .acc-user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            padding: 4px 10px;
            border-radius: 10px;
            background-color: #f8fafc;
            border: 1px solid #f1f5f9;
            transition: all 0.2s ease;
        }

        .acc-user-profile:hover {
            background-color: #f1f5f9;
            border-color: #e2e8f0;
        }

        .acc-user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.2);
            flex-shrink: 0;
        }

        .acc-user-info {
            display: flex;
            flex-direction: column;
            line-height: 1.15;
            text-align: left;
        }

        .acc-user-name {
            font-size: 0.82rem;
            font-weight: 700;
            color: #1e293b;
            white-space: nowrap;
        }

        .acc-user-role {
            font-size: 0.68rem;
            color: #64748b;
            font-weight: 500;
            white-space: nowrap;
        }

        .acc-logout-btn {
            width: 36px;
            height: 36px;
            /* border-radius: 8px;
            background-color: #fff1f2;
            border: 1px solid #ffe4e6; */
            color: #e11d48;
            font-size: 18px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .acc-logout-btn:hover {
            /* background-color: #e11d48; */
            color: #e11d48;
            /* border-color: #e11d48; */
            /* box-shadow: 0 3px 8px rgba(225, 29, 72, 0.25); */
        }
    </style>
</head>

<body class="boxed-size">

    <div class="preloader" id="preloader">
        <script>
            if (sessionStorage.getItem('cpdth_show_preloader') === '1') {
                sessionStorage.removeItem('cpdth_show_preloader');
            } else {
                var _p = document.getElementById('preloader');
                if (_p) { _p.style.display = 'none'; }
            }
        </script>
        <div class="preloader">
            <div class="waviy position-relative">
                <span class="d-inline-block">C</span>
                <span class="d-inline-block">P</span>
                <span class="d-inline-block">D</span>
                <span class="d-inline-block">T</span>
                <span class="d-inline-block">H</span>
            </div>
        </div>
    </div>

    <header class="acc-topbar">
        <div class="d-flex align-items-center flex-grow-1">
            <!-- Brand Logo & Title -->
            <a href="/cpd_ac/public/main" class="acc-brand-wrap">
                <div class="acc-brand-logo">
                    <img src="/cpd_ac/public/assets/images/G_AM_logo-01.jpg" alt="Cpd Acc Logo">
                </div>
                <div class="acc-brand-info">
                    <h1 class="acc-brand-title">CPD ACC</h1>
                    <p class="acc-brand-subtitle">ระบบบริหารสำนักงานบัญชี</p>
                </div>
            </a>

            <!-- Company / Workspace Dropdown List Container (Loop แสดงบริษัทที่มี) -->
            <div class="acc-company-container">
                <?php if (isset($data['companies']) && !empty($data['companies'])): ?>
                    <?php foreach ($data['companies'] as $index => $company):
                        $companyId = $company['company_id'] ?? $company['id'] ?? '';
                        $companyName = htmlspecialchars($company['company_name'] ?? 'ไม่มีชื่อบริษัท');
                        if (isset($data['active_company_id']) && !empty($data['active_company_id'])) {
                            $isActive = ($companyId == $data['active_company_id']) ? 'active' : '';
                        } else {
                            $isActive = ($index === 0) ? 'active' : '';
                        }

                        // คำนวณปีทำงานที่เปิดใช้งานอยู่
                        $fiscalYears = $company['fiscal_years'] ?? [];
                        $activeYear = 'Demo Year';
                        $activeFiscalId = '';

                        if (!empty($fiscalYears)) {
                            $foundActive = false;
                            
                            // 1. ตรวจสอบว่าปีนี้คือปีที่ผู้ใช้กดเลือกเข้ามาทำงานใน Backoffice หรือไม่
                            if (isset($data['fiscal_id']) && !empty($data['fiscal_id'])) {
                                foreach ($fiscalYears as $fy) {
                                    $fy_id = $fy['fiscal_id'] ?? $fy['id'] ?? '';
                                    if ($fy_id == $data['fiscal_id']) {
                                        $activeYear = $fy['fiscal_years'] ?? $fy['working_year'] ?? $fy['year'] ?? 'Demo Year';
                                        $activeFiscalId = $fy_id;
                                        $foundActive = true;
                                        break;
                                    }
                                }
                            }
                            
                            // 2. ถ้ายังไม่เจอ (ไม่ได้เลือกมา) ให้ใช้ปีที่มีสถานะ active_status เป็น 1 (ปีปัจจุบันของบริษัท)
                            if (!$foundActive) {
                                foreach ($fiscalYears as $fy) {
                                    if (($fy['active_status'] ?? '') === '1') {
                                        $activeYear = $fy['fiscal_years'] ?? $fy['working_year'] ?? $fy['year'] ?? 'Demo Year';
                                        $activeFiscalId = $fy['fiscal_id'] ?? $fy['id'] ?? '';
                                        $foundActive = true;
                                        break;
                                    }
                                }
                            }
                            if (!$foundActive) {
                                $activeYear = $fiscalYears[0]['fiscal_years'] ?? $fiscalYears[0]['working_year'] ?? $fiscalYears[0]['year'] ?? 'Demo Year';
                                $activeFiscalId = $fiscalYears[0]['fiscal_id'] ?? $fiscalYears[0]['id'] ?? '';
                            }
                        }
                        ?>

                        <!-- Workspace Dropdown Pill Button (แสดง Popper strategy fixed เพื่อลอยอยู่ด้านหน้า) -->
                        <div class="dropdown acc-workspace-dropdown" data-company-id="<?= $companyId ?>">
                            <button type="button" class="acc-workspace-btn <?= $isActive ?>"
                                id="wsDropdownBtn_<?= $companyId ?>" data-bs-toggle="dropdown" data-bs-auto-close="true"
                                data-bs-popper-config='{"strategy":"fixed"}' data-bs-boundary="viewport" aria-expanded="false"
                                data-company-id="<?= $companyId ?>" data-company-name="<?= $companyName ?>"
                                data-active-year="<?= $activeYear ?>" data-fiscal-id="<?= $activeFiscalId ?>"
                                onclick="selectCompany(this, '<?= $companyId ?>')">

                                <div class="acc-workspace-icon">
                                    <i class="ri-bank-line"></i>
                                </div>

                                <div class="acc-workspace-info">
                                    <span class="acc-workspace-badge">WORKSPACE</span>
                                    <span class="acc-workspace-name" title="<?= $companyName ?>"><?= $companyName ?></span>
                                    <span class="acc-workspace-year">ปีทำงาน <span
                                            class="ws-year-text"><?= $activeYear ?></span></span>
                                </div>

                                <i class="ri-arrow-down-s-line acc-workspace-arrow"></i>
                            </button>

                            <!-- Dropdown Menu (ตรงตามรูปภาพ 2) -->
                            <div class="dropdown-menu acc-workspace-menu" aria-labelledby="wsDropdownBtn_<?= $companyId ?>">
                                <!-- Header Dropdown -->
                                <div class="acc-menu-header">
                                    <div class="acc-menu-header-icon">
                                        <i class="ri-bank-line"></i>
                                    </div>
                                    <div class="acc-menu-header-text">
                                        <h6 class="acc-menu-title"><?= $companyName ?></h6>
                                        <p class="acc-menu-subtitle">เลือกปีทำงานของสำนักงาน</p>
                                    </div>
                                </div>

                                <!-- การ์ดปีที่ใช้งานอยู่ (Active Year Highlight Card) -->
                                <div class="acc-active-year-card"
                                    onclick="selectFiscalYear('<?= $companyId ?>', '<?= $activeYear ?>', '<?= $activeFiscalId ?>')">
                                    <div class="acc-active-year-left">
                                        <div class="acc-active-year-icon">
                                            <i class="ri-calendar-check-line"></i>
                                        </div>
                                        <div class="acc-active-year-info">
                                            <span class="acc-active-year-label">ปีที่ใช้งานอยู่</span>
                                            <span
                                                class="acc-active-year-val"><?= ($activeYear === 'Demo Year') ? '' : 'ปี ' ?><span
                                                    class="card-active-year-val"><?= $activeYear ?></span></span>
                                        </div>
                                    </div>
                                    <span class="acc-active-badge">กำลังใช้งาน</span>
                                </div>

                                <!-- หมวดหมู่: เลือกปีอื่น -->
                                <div class="acc-other-years-wrap">
                                    <div class="acc-other-years-title">เลือกปีอื่น</div>
                                    <div class="acc-other-years-list" id="otherYearsList_<?= $companyId ?>">
                                        <?php
                                        $hasOtherYears = false;
                                        if (!empty($fiscalYears)):
                                            foreach ($fiscalYears as $fy):
                                                $yVal = $fy['fiscal_years'] ?? $fy['working_year'] ?? $fy['year'] ?? '';
                                                $cCount = $fy['customer_count'] ?? 0;
                                                $fId = $fy['fiscal_id'] ?? $fy['id'] ?? '';
                                                $hasOtherYears = true;
                                                ?>
                                                <a href="javascript:void(0);" class="acc-other-year-item" data-year="<?= $yVal ?>"
                                                    onclick="selectFiscalYear('<?= $companyId ?>', '<?= $yVal ?>', '<?= $fId ?>')">
                                                    <div class="acc-other-year-icon">
                                                        <i class="ri-calendar-line"></i>
                                                    </div>
                                                    <div class="acc-other-year-info">
                                                        <span class="acc-other-year-val">ปี <?= $yVal ?></span>
                                                        <span
                                                            class="acc-other-year-sub"><?= $cCount > 0 ? $cCount . ' ลูกค้า' : 'ปีทำงาน' ?></span>
                                                    </div>
                                                </a>
                                            <?php endforeach; ?>
                                        <?php endif; ?>

                                        <?php if (!$hasOtherYears): ?>
                                            <div class="acc-no-years-sub text-muted px-2 py-1" style="font-size: 0.78rem;">
                                                ไม่มีปีอื่นให้เลือก
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- ท้ายเมนู: จัดการปีทำงาน -->
                                <div class="acc-menu-footer">
                                    <a href="/cpd_ac/public/main" class="acc-manage-year-btn"
                                        onclick="selectCompanyById('<?= $companyId ?>')">
                                        <i class="ri-sound-module-line"></i>
                                        <span>จัดการปีทำงาน</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- ปุ่มเพิ่มบริษัท -->
                <button type="button" class="acc-add-workspace-btn" onclick="Getmodal_add()" title="เพิ่มบริษัท">
                    <div class="acc-add-workspace-icon">
                        <i class="ri-add-line"></i>
                    </div>
                    <span class="acc-add-workspace-text">เพิ่มบริษัท</span>
                </button>
            </div>
        </div>

        <div class="acc-actions">
            <div class="acc-user-profile" title="ข้อมูลผู้ใช้งาน">
                <div class="acc-user-avatar">
                    <i class="ri-user-3-fill"></i>
                </div>
                <div class="acc-user-info d-none d-sm-flex">
                    <span class="acc-user-name">
                        <?php echo htmlspecialchars(trim(($data['firstname'] ?? $_SESSION['user_firstname'] ?? '') . ' ' . ($data['lastname'] ?? $_SESSION['user_lastname'] ?? 'ผู้ใช้งาน'))) ?>
                    </span>
                    <span class="acc-user-role">
                        <?php echo (!empty($data['is_super_admin'] ?? $_SESSION['is_super_admin'] ?? null) && ($data['is_super_admin'] ?? $_SESSION['is_super_admin']) === '1') ? 'ผู้ดูแลระบบ' : 'ผู้ใช้งานระบบ' ?>
                    </span>
                </div>
            </div>

            <!-- Logout Button -->
            <a href="/cpd_ac/public/logout" class="acc-logout-btn" title="ออกจากระบบ">
                <i class="ri-logout-box-r-line"></i>
            </a>
        </div>
    </header>

    <!-- Modal เพิ่มบริษัท -->
    <div class="modal fade" id="addCompanyModal" tabindex="-1" aria-labelledby="addCompanyModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content"
                style="border-radius: 16px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <div class="modal-header" style="border-bottom: 1px solid #f1f5f9; padding: 20px 24px;">
                    <h5 class="modal-title" id="addCompanyModalLabel" style="font-weight: 800; color: #1e293b;">
                        เพิ่มบริษัทใหม่</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="padding: 24px;">
                    <form id="addCompanyForm">
                        <div class="mb-3">
                            <label class="form-label"
                                style="font-weight: 700; font-size: 0.9rem; color: #334155;">ชื่อบริษัท <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="company_name" placeholder="กรอกชื่อบริษัท"
                                style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 14px; font-weight: 600;">
                        </div>
                    </form>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #f1f5f9; padding: 16px 24px;">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                        style="border-radius: 8px; font-weight: 600;">ยกเลิก</button>
                    <button type="button" class="btn btn-primary" onclick="addCompany()"
                        style="border-radius: 8px; font-weight: 700; background-color: #0066fe; border: none; padding: 8px 20px;">บันทึก</button>
                </div>
            </div>
        </div>
    </div>

    <script src="/cpd_ac/public/template/assets/js/jquery-3.1.1.min.js"></script>

    <script>
        // 1. ฟังก์ชันเลือกบริษัท
        function selectCompany(element, companyId) {
            if (!element) return;

            // ถอด active ออกจากทุกปุ่ม Workspace
            document.querySelectorAll('.acc-workspace-btn').forEach(function (b) {
                b.classList.remove('active');
            });

            // กำหนด active ค้างไว้ที่ปุ่มที่เลือก
            element.classList.add('active');

            // ดึงชื่อบริษัทและปี
            const name = element.getAttribute('data-company-name') || (element.querySelector('.acc-workspace-name') ? element.querySelector('.acc-workspace-name').textContent.trim() : '');
            const year = element.getAttribute('data-active-year') || (element.querySelector('.ws-year-text') ? element.querySelector('.ws-year-text').textContent.trim() : 'Demo Year');

            const workspaceNameEl = document.getElementById('currentWorkspaceName');
            if (name && workspaceNameEl) {
                workspaceNameEl.textContent = name;
            }

            // เลื่อน scroll มาที่ปุ่มที่เลือก
            element.scrollIntoView({ behavior: 'smooth', inline: 'nearest', block: 'nearest' });

            // เก็บค่าลง localStorage
            localStorage.setItem('bo_selected_company', companyId);
            localStorage.setItem('bo_selected_year', year);

            // อัปเดตปีที่เลือกในแบนเนอร์หน้า index (ถ้ามี)
            const selectedValEl = document.querySelector('.notice-selected-value');
            if (selectedValEl) {
                selectedValEl.textContent = 'ปี ' + year;
            }

            // โหลดข้อมูลปีใหม่ผ่าน AJAX สำหรับหน้า main index
            if (typeof loadFiscalYears === 'function') {
                loadFiscalYears(companyId);
            }
        }

        function selectCompanyById(companyId) {
            const btn = document.querySelector('.acc-workspace-btn[data-company-id="' + companyId + '"]');
            if (btn) {
                selectCompany(btn, companyId);
            }
        }

        // 2. ฟังก์ชันเลือกปีทำงานจาก Dropdown
        function selectFiscalYear(companyId, year, fiscalId) {
            if (!companyId || !year) return;

            // อัปเดต Text ในปุ่ม Workspace ของบริษัทนั้น
            const wsBtn = document.querySelector('.acc-workspace-btn[data-company-id="' + companyId + '"]');
            if (wsBtn) {
                wsBtn.setAttribute('data-active-year', year);
                if (fiscalId) wsBtn.setAttribute('data-fiscal-id', fiscalId);
                const yrTextEl = wsBtn.querySelector('.ws-year-text');
                if (yrTextEl) yrTextEl.textContent = year;

                // อัปเดตในการ์ดปีที่ใช้งานอยู่
                const dropdownWrapper = wsBtn.closest('.acc-workspace-dropdown');
                if (dropdownWrapper) {
                    const cardActiveVal = dropdownWrapper.querySelector('.card-active-year-val');
                    if (cardActiveVal) cardActiveVal.textContent = year;
                }
            }

            // บันทึกลง localStorage
            localStorage.setItem('bo_selected_company', companyId);
            localStorage.setItem('bo_selected_year', year);
            if (fiscalId) localStorage.setItem('bo_selected_fiscal_id', fiscalId);

            // อัปเดตในหน้าแสดงผลปัจจุบัน
            const selectedValEl = document.querySelector('.notice-selected-value');
            if (selectedValEl) {
                selectedValEl.textContent = 'ปี ' + year;
            }

            // ปิด Dropdown
            const openDropdown = bootstrap.Dropdown.getInstance(wsBtn);
            if (openDropdown) {
                openDropdown.hide();
            }

            // แจ้งเตือนสลับปีสำเร็จ (Toast)
            if (typeof Swal !== 'undefined') {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 1200,
                    timerProgressBar: true
                });
                Toast.fire({
                    icon: 'success',
                    title: 'เลือกปีทำงาน ' + year + ' เรียบร้อยแล้ว'
                });
            }

            // หากมี callback พิเศษสำหรับเปลี่ยนปี
            if (typeof onYearChanged === 'function') {
                onYearChanged(companyId, year, fiscalId);
            }
        }

        function Getmodal_add() {
            const form = document.getElementById('addCompanyForm');
            if (form) {
                form.reset();
            }
            const modalElement = document.getElementById('addCompanyModal');
            const myModal = new bootstrap.Modal(modalElement);
            myModal.show();
        }

        function addCompany() {
            var formData = $('#addCompanyForm').serialize();
            $.ajax({
                type: "POST",
                url: "/cpd_ac/public/company/add",
                data: formData,
                dataType: "json",
                success: function (response) {
                    if (response.result === 1) {
                        const modalElement = document.getElementById('addCompanyModal');
                        if (modalElement) {
                            const modalInstance = bootstrap.Modal.getInstance(modalElement);
                            if (modalInstance) modalInstance.hide();
                        }

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: response.msg || 'บันทึกสำเร็จ',
                                showConfirmButton: false,
                                timer: 1500,
                                timerProgressBar: true
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            alert(response.msg);
                            location.reload();
                        }
                    } else {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'error',
                                title: response.msg || 'ผิดพลาด',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true
                            });
                        } else {
                            alert(response.msg);
                        }
                    }
                },
                error: function (err) {
                    console.error("AJAX Error:", err);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true
                        });
                    } else {
                        alert("เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์");
                    }
                }
            });
        }

        // คืนค่าบริษัทและปีที่เคยเลือกไว้เมื่อเปิดหน้าเว็บ
        $(document).ready(function () {
            let savedCompany = localStorage.getItem('bo_selected_company');
            let savedYear = localStorage.getItem('bo_selected_year');

            if (savedCompany) {
                selectCompanyById(savedCompany);
                if (savedYear) {
                    const wsBtn = document.querySelector('.acc-workspace-btn[data-company-id="' + savedCompany + '"]');
                    if (wsBtn) {
                        const yrTextEl = wsBtn.querySelector('.ws-year-text');
                        if (yrTextEl) yrTextEl.textContent = savedYear;
                    }
                }
            } else {
                let firstCompany = document.querySelector('.acc-workspace-btn');
                if (firstCompany) {
                    let companyId = firstCompany.getAttribute('data-company-id');
                    selectCompany(firstCompany, companyId);
                }
            }
        });
    </script>