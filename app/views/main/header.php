<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title><?php echo htmlspecialchars($data['title'] ?? 'CPD ACC - ระบบบริหารสำนักงานบัญชี') ?></title>

    <link rel="icon" type="image/png"
        href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/assets/images/am-group-logo.png">
    <link rel="apple-touch-icon"
        href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/assets/images/am-group-logo.png">

    <link rel="stylesheet"
        href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/template/assets/css/font.css">
    <link rel="stylesheet"
        href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/template/assets/css/sidebar-menu.css">
    <link rel="stylesheet"
        href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/template/assets/css/simplebar.css">
    <link rel="stylesheet"
        href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/template/assets/css/apexcharts.css">
    <link rel="stylesheet"
        href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/template/assets/css/prism.css">
    <link rel="stylesheet"
        href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/template/assets/css/rangeslider.css">
    <link rel="stylesheet"
        href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/template/assets/css/google-icon.css">
    <link rel="stylesheet"
        href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/template/assets/css/remixicon.css">
    <link rel="stylesheet"
        href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/template/assets/css/swiper-bundle.min.css">
    <link rel="stylesheet"
        href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/template/assets/css/fullcalendar.main.css">
    <link rel="stylesheet"
        href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/template/assets/css/jsvectormap.min.css">
    <link rel="stylesheet"
        href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/template/assets/css/lightpick.css">
    <link rel="stylesheet"
        href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/template/assets/css/select2.min.css">
    <link rel="stylesheet"
        href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/template/assets/css/style.css">
    <link rel="stylesheet"
        href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/template/assets/css/toastr.min.css">
    <link rel="stylesheet"
        href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/template/assets/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet"
        href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/template/assets/css/sweetalert2.min.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <link rel="stylesheet"
        href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/template/assets/css/custom.css?ver=<?php echo @filemtime(dirname(__DIR__, 2) . '/public/template/assets/css/custom.css') ?: time(); ?>">
    <link rel="stylesheet"
        href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/template/assets/css/web.css">
    <link rel="stylesheet"
        href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/template/assets/css/ui.css?ver=<?php echo @filemtime(dirname(__DIR__, 2) . '/public/template/assets/css/ui.css') ?: time(); ?>">

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
            align-items: stretch;
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
            display: flex;
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
            height: 56px;
            min-height: 56px;
            box-sizing: border-box;
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
            height: 56px;
            min-height: 56px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background-color: #ffffff;
            border: 1.5px dashed #cbd5e1;
            border-radius: 14px;
            padding: 0 16px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
            color: #475569;
            flex-shrink: 0;
            box-sizing: border-box;
        }

        .acc-add-workspace-btn:hover {
            border-color: #2563eb;
            background-color: #eff6ff;
            color: #2563eb;
            transform: translateY(-1px);
        }

        .acc-add-workspace-icon {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background-color: #f1f5f9;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .acc-add-workspace-icon i {
            color: #64748b;
            transition: color 0.2s ease;
        }

        .acc-add-workspace-btn:hover .acc-add-workspace-icon {
            background-color: #2563eb;
            color: #ffffff !important;
        }

        .acc-add-workspace-btn:hover .acc-add-workspace-icon i {
            color: #ffffff !important;
        }

        .acc-add-workspace-text {
            font-size: 0.88rem;
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

        /* --- Sidebar Toggle Button --- */
        .header-burger-menu {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background-color: #ffffff;
            border: 1.5px solid #edf2f7;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2563eb;
            font-size: 22px;
            cursor: pointer;
            margin-left: 16px;
            margin-right: 8px;
            transition: all 0.2s ease;
            flex-shrink: 0;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
        }

        .header-burger-menu:hover {
            border-color: #3b82f6;
            background-color: #eff6ff;
            color: #1d4ed8;
            transform: translateY(-1px);
        }

        /* --- Global Keen/Metronic Modal & Form Utilities --- */
        .mw-550px {
            max-width: 550px !important;
        }

        .mw-600px {
            max-width: 600px !important;
        }

        .mw-650px {
            max-width: 650px !important;
        }

        .mw-700px {
            max-width: 700px !important;
        }

        .mw-800px {
            max-width: 800px !important;
        }

        /* ==================================================
           --- Global Modal Custom Styling (Standard CPD ACC) ---
           ================================================== */
        .modal-content,
        .modal-content-custom {
            border: none !important;
            border-radius: 20px !important;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08) !important;
            background-color: #ffffff !important;
        }

        .modal-header,
        .modal-header-custom {
            border-bottom: 1px solid #f1f5f9 !important;
            padding: 24px 28px 16px 28px !important;
            position: sticky;
            top: 0;
            z-index: 10;
            background-color: #ffffff;
            border-radius: 20px 20px 0 0;
        }

        .modal-title,
        .modal-title-custom {
            font-weight: 800;
            color: #1e293b;
            font-size: 1.25rem;
            margin: 0;
        }

        .modal-close-custom,
        .modal-header .btn-close {
            font-size: 0.9rem;
            opacity: 0.5;
        }

        .modal-body,
        .modal-body-custom {
            padding: 24px 28px !important;
            overflow-x: hidden;
        }

        .modal-footer,
        .modal-footer-custom {
            border-top: 1px solid #f1f5f9 !important;
            padding: 16px 28px !important;
            gap: 12px;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            position: sticky;
            bottom: 0;
            z-index: 10;
            background-color: #ffffff;
            border-radius: 0 0 20px 20px;
        }

        .modal-btn-cancel {
            background-color: #f8fafc !important;
            color: #334155 !important;
            font-weight: 700 !important;
            border-radius: 12px !important;
            padding: 10px 24px !important;
            border: 1px solid #e2e8f0 !important;
            font-size: 0.92rem !important;
            transition: all 0.2s ease !important;
        }

        .modal-btn-cancel:hover {
            background-color: #e2e8f0 !important;
            color: #0f172a !important;
        }

        .modal-btn-save {
            background-color: #007aff !important;
            color: #ffffff !important;
            font-weight: 700 !important;
            border-radius: 12px !important;
            padding: 10px 28px !important;
            border: none !important;
            font-size: 0.92rem !important;
            box-shadow: 0 4px 14px rgba(0, 122, 255, 0.25) !important;
            transition: all 0.2s ease !important;
        }

        .modal-btn-save:hover {
            background-color: #0066cc !important;
            color: #ffffff !important;
        }

        .modal-section-title {
            margin-bottom: 16px;
        }

        .modal-section-divider {
            border-top: 1px dashed #e2e8f0;
            margin: 24px 0 20px 0;
        }

        .modal-form-label,
        .modal-body label {
            margin-bottom: 8px;
            display: block;
        }

        .modal-form-control,
        .modal-form-select,
        .modal-body .form-control,
        .modal-body .form-select {
            background-color: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 12px !important;
            padding: 12px 16px !important;
            outline: none !important;
            box-shadow: none !important;
            width: 100%;
        }

        .modal-form-control:focus,
        .modal-form-select:focus,
        .flatpickr-input-custom:focus,
        .modal-body .form-control:focus,
        .modal-body .form-select:focus {
            background-color: #ffffff !important;
            border-color: #007aff !important;
            box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.1) !important;
        }

        .modal-form-control:disabled,
        .modal-form-control[disabled],
        .modal-form-select:disabled,
        .modal-body .form-control:disabled,
        .modal-body .form-control[disabled],
        .modal-body .form-select:disabled {
            background-color: #f1f5f9 !important;
            border: 1px solid #e2e8f0 !important;
            color: #94a3b8 !important;
            cursor: not-allowed !important;
        }

        /* --- Flatpickr Date Input & Icon Styling --- */
        .flatpickr-wrapper,
        .modal-input-icon-wrap {
            width: 100% !important;
            display: block !important;
            position: relative !important;
        }

        input.flatpickr-date,
        input.flatpickr-input,
        .flatpickr-input-custom {
            background-color: #ffffff !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='4' width='18' height='18' rx='2' ry='2'%3E%3C/rect%3E%3Cline x1='16' y1='2' x2='16' y2='6'%3E%3C/line%3E%3Cline x1='8' y1='2' x2='8' y2='6'%3E%3C/line%3E%3Cline x1='3' y1='10' x2='21' y2='10'%3E%3C/line%3E%3C/svg%3E") !important;
            background-repeat: no-repeat !important;
            background-position: right 14px center !important;
            background-size: 18px 18px !important;
            padding-right: 40px !important;
            cursor: pointer !important;
        }

        .modal-input-icon-wrap i,
        .flatpickr-wrapper i {
            position: absolute !important;
            right: 14px !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            color: #64748b !important;
            font-size: 18px !important;
            pointer-events: none !important;
            z-index: 5 !important;
        }

        .modal-content-keen {
            border: none !important;
            border-radius: 16px !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important;
            background-color: #ffffff;
        }

        .modal-header-keen {
            border-bottom: 1px solid #f1f5f9 !important;
            padding: 20px 28px !important;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-title-keen {
            font-weight: 600;
            color: #1e293b;
            font-size: 1.15rem;
            margin: 0;
        }

        .modal-body-keen {
            padding: 24px 28px !important;
        }

        .modal-footer-keen {
            border-top: 1px solid #f1f5f9 !important;
            padding: 16px 28px !important;
            gap: 12px;
            display: flex;
            justify-content: flex-end;
            align-items: center;
        }

        .form-label-keen {
            font-weight: 500;
            color: #334155;
            font-size: 0.90rem;
            margin-bottom: 8px;
            display: block;
        }

        .form-label-keen.required::after,
        .form-label-keen .req-star {
            content: " *";
            color: #ef4444;
        }

        .form-control-solid,
        .form-select-solid {
            background-color: #f8fafc !important;
            border: 1px solid #f1f5f9 !important;
            border-radius: 10px !important;
            padding: 12px 16px !important;
            font-weight: 500 !important;
            font-size: 0.90rem !important;
            color: #334155 !important;
            box-shadow: none !important;
            transition: all 0.2s ease !important;
        }

        .form-control-solid:focus,
        .form-select-solid:focus {
            background-color: #ffffff !important;
            border-color: #0066fe !important;
            box-shadow: 0 0 0 3px rgba(0, 102, 254, 0.1) !important;
        }

        .btn-light-keen {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 500;
            border-radius: 8px;
            padding: 10px 20px;
            border: none;
            font-size: 0.90rem;
            transition: all 0.2s ease;
        }

        .btn-light-keen:hover {
            background-color: #f1f5f9;
            color: #1e293b;
        }

        .btn-primary-keen {
            background-color: #0066fe;
            color: #ffffff;
            font-weight: 500;
            border-radius: 8px;
            padding: 10px 22px;
            border: none;
            font-size: 0.90rem;
            box-shadow: 0 4px 12px rgba(0, 102, 254, 0.2);
            transition: all 0.2s ease;
        }

        .btn-primary-keen:hover {
            background-color: #0052cc;
            color: #ffffff;
        }

        /* --- Master Page Layout & Card Wrapper --- */
        @media only screen and (min-width: 1200px) {
            body:not([sidebar-data-theme="sidebar-hide"]) .sidebar-area {
                width: 250px !important;
            }

            body:not([sidebar-data-theme="sidebar-hide"]) .main-content {
                padding-left: 250px !important;
                padding-right: 0 !important;
                padding-top: 0 !important;
            }
        }

        body[sidebar-data-theme="sidebar-hide"] .sidebar-area {
            width: 80px !important;
        }

        body[sidebar-data-theme="sidebar-hide"] .main-content {
            padding-left: 80px !important;
            padding-right: 0 !important;
            padding-top: 0 !important;
        }

        .container-fluid {
            padding-left: 0 !important;
            padding-right: 14px !important;
        }

        .main-page-wrapper {
            padding-top: 0px !important;
            padding: 20px 0px !important;
            min-height: calc(100vh - 72px);
        }

        .main-card-wrapper {
            background-color: #ffffff;
            border-radius: 16px;
            border: 1px solid #edf2f7;
            box-shadow: 0 2px 12px rgba(16, 24, 40, 0.03);
            padding: 24px 20px;
        }

        .page-header-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 14px;
        }

        .page-title {
            font-size: 1.15rem;
            font-weight: 800;
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

        /* --- Master Action Buttons --- */
        .btn-excel-action {
            background-color: #EBF4FF;
            color: #007aff;
            border: none;
            border-radius: 10px;
            padding: 8px 15px;
            font-size: 0.80rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-excel-action i {
            color: #007aff;
            font-size: 16px;
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

        .btn-add-action {
            background-color: #007aff;
            color: #ffffff;
            border: none;
            border-radius: 10px;
            padding: 8px 18px;
            font-size: 0.80rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 4px 12px rgba(0, 122, 255, 0.25);
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-add-action i {
            color: #ffffff !important;
            font-size: 15px;
        }

        .btn-add-action:hover {
            background-color: #0062cc;
            color: #ffffff !important;
            box-shadow: 0 6px 16px rgba(0, 122, 255, 0.35);
        }

        /* --- Stats Grid --- */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
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
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            user-select: none;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.06);
            border-color: #cbd5e1;
        }

        .stat-card.active {
            border-color: #3b82f6 !important;
            background-color: #eff6ff !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15), 0 4px 12px rgba(59, 130, 246, 0.08) !important;
        }

        .stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 19px;
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

        .stat-icon.red {
            background-color: #fef2f2;
            color: #ef4444;
            border: 1px solid #fee2e2;
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
            font-size: 0.75rem;
            color: #64748b;
            font-weight: 500;
        }

        /* --- Filter Toolbar --- */
        .filter-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            gap: 14px;
            flex-wrap: wrap;
        }

        .search-box-wrap {
            position: relative;
            flex: 1;
            max-width: 360px;
        }

        .search-box-wrap i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 16px;
        }

        .search-input {
            width: 100%;
            background-color: #f8fafc;
            border: 1px solid #f1f5f9;
            border-radius: 10px;
            padding: 9px 12px 9px 38px;
            font-size: 0.80rem;
            color: #334155;
            font-family: inherit;
            outline: none;
            transition: all 0.2s ease;
        }

        .search-input:focus {
            background-color: #ffffff;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }



        .filter-select {
            background-color: #f8fafc;
            border: 1px solid #f1f5f9;
            border-radius: 10px;
            padding: 9px 32px 9px 14px;
            font-size: 0.80rem;
            font-weight: 600;
            color: #475569;
            cursor: pointer;
            outline: none;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' width='16' height='16' fill='%2364748b'%3E%3Cpath d='M11.9997 13.1716L16.9495 8.22168L18.3637 9.63589L11.9997 16L5.63574 9.63589L7.04996 8.22168L11.9997 13.1716Z'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            min-width: 130px;
            transition: all 0.2s ease;
        }

        .filter-select:focus {
            background-color: #ffffff;
            border-color: #3b82f6;
        }

        /* --- Universal Master Table Styles --- */
        .table-container-card {
            background-color: #ffffff;
            border: 1px solid #edf2f7;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
        }

        .table-header-wrap {
            margin-bottom: 20px;
        }

        .table-title {
            font-size: 1.15rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 4px;
        }

        .table-subtitle {
            font-size: 0.85rem;
            color: #94a3b8;
            font-weight: 500;
            margin: 0;
        }

        .table-wrap {
            overflow-x: auto;
            width: 100%;
            -webkit-overflow-scrolling: touch;
        }

        .table-wrap::-webkit-scrollbar {
            height: 6px;
        }

        .table-wrap::-webkit-scrollbar-track {
            background: #f8fafc;
            border-radius: 10px;
        }

        .table-wrap::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .table-wrap::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 0;
        }

        .table th {
            font-size: 0.75rem;
            font-weight: 700;
            color: #94a3b8;
            padding: 10px 14px;
            border-bottom: 1px solid #f1f5f9;
            white-space: nowrap;
            background-color: transparent;
            vertical-align: middle;
        }

        .table th.text-start {
            text-align: left;
        }

        .table th.text-center {
            text-align: center;
        }

        .table th.text-end {
            text-align: right;
        }

        .table td {
            padding: 14px 14px;
            border-bottom: 1px dashed #f1f5f9;
            vertical-align: middle;
            font-size: 0.80rem;
            color: #475569;
            background-color: transparent;
            white-space: nowrap;
        }

        .table tr:last-child td {
            border-bottom: none;
        }

        /* Generic Table Helpers */
        .table-item-title {
            font-size: 0.84rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 2px;
        }

        .table-item-sub {
            font-size: 0.72rem;
            color: #94a3b8;
            font-weight: 500;
        }

        .badge-active {
            background-color: #ecfdf5;
            color: #10b981;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 6px;
            display: inline-block;
            white-space: nowrap;
        }

        .badge-inactive {
            background-color: #f1f5f9;
            color: #64748b;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 6px;
            display: inline-block;
            white-space: nowrap;
        }

        .badge-warning {
            background-color: #fffbe6;
            color: #d48806;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 6px;
            display: inline-block;
            white-space: nowrap;
        }

        .badge-info {
            background-color: #eff6ff;
            color: #2563eb;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 6px;
            display: inline-block;
            white-space: nowrap;
        }

        .badge-subtext {
            font-size: 0.70rem;
            color: #64748b;
            font-weight: 600;
            margin-top: 3px;
            display: block;
            line-height: 1.2;
        }

        .fee-amount-text {
            font-weight: 800;
            color: #0f172a;
            font-size: 0.82rem;
        }

        .caretaker-text {
            font-weight: 600;
            color: #334155;
            font-size: 0.80rem;
        }

        /* Generic Action Buttons */
        .action-btn-group {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .btn-action-edit {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #475569;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-action-edit:hover {
            background-color: #ffffff;
            color: #2563eb;
            border-color: #bfdbfe;

        }

        .btn-action-message {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background-color: #eff6ff;
            border: 1px solid #dbeafe;
            color: #2563eb;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-action-message:hover {
            background-color: #dbeafe;
            color: #2563eb;
            border-color: #bfdbfe;
        }

        .btn-action-message:hover i {
            color: #2563eb !important;
        }

        .btn-action-drive {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background-color: #fff7ed;
            border: 1px solid #fed7aa;
            color: #ea580c;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-action-drive:hover {
            background-color: #fed7aa;
            color: #c2410c;
            border-color: #fdba74;
        }

        .btn-action-delete {
            width: 32px;
            height: 32px;
            border-radius: 7px;
            background-color: #fee2e2;
            border: 1px solid #fecaca;
            color: #ef4444;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-action-delete:hover {
            background-color: #fca5a5;
            color: #dc2626;
            border-color: #f87171;
        }

        /* --- Pagination Toolbar --- */
        .pagination-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid #f8fafc;
            flex-wrap: wrap;
            gap: 14px;
        }

        .per-page-wrap {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.78rem;
            color: #64748b;
            font-weight: 500;
        }

        .per-page-select {
            background-color: #f8fafc;
            border: 1px solid #f1f5f9;
            border-radius: 7px;
            padding: 3px 24px 3px 8px;
            font-size: 0.78rem;
            font-weight: 700;
            color: #334155;
            outline: none;
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' width='14' height='14' fill='%2364748b'%3E%3Cpath d='M11.9997 13.1716L16.9495 8.22168L18.3637 9.63589L11.9997 16L5.63574 9.63589L7.04996 8.22168L11.9997 13.1716Z'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 6px center;
        }

        .pagination-info {
            font-size: 0.78rem;
            color: #64748b;
            font-weight: 500;
        }

        .pagination-nav {
            display: flex;
            align-items: center;
            gap: 3px;
        }

        .page-btn {
            width: 28px;
            height: 28px;
            border-radius: 7px;
            border: 1px solid transparent;
            background-color: transparent;
            color: #64748b;
            font-size: 0.78rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .page-btn:hover {
            background-color: #f1f5f9;
            color: #0f172a;
        }



        /* --- SweetAlert2 Custom Styling (ขนาดกะทัดรัด) --- */
        .swal2-popup:not(.swal2-toast) {
            width: 360px !important;
            max-width: 90vw !important;
            padding: 24px 20px !important;
            border-radius: 16px !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.10) !important;
            font-family: 'Kanit', sans-serif !important;
        }

        .swal2-popup:not(.swal2-toast) .swal2-icon {
            transform: scale(0.85) !important;
            margin: 8px auto 0 auto !important;
        }

        .swal2-popup:not(.swal2-toast) .swal2-title {
            font-size: 17px !important;
            font-weight: 700 !important;
            color: #1e293b !important;
            margin-top: 8px !important;
            margin-bottom: 6px !important;
        }

        .swal2-popup:not(.swal2-toast) .swal2-html-container {
            font-size: 13px !important;
            font-weight: 500 !important;
            color: #475569 !important;
            margin: 6px 0 14px 0 !important;
        }

        .swal2-actions {
            gap: 8px !important;
            margin-top: 12px !important;
        }

        .swal2-styled {
            padding: 7px 18px !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            border-radius: 8px !important;
            box-shadow: none !important;
        }

        .swal2-styled.swal2-confirm {
            background-color: #e11d48 !important;
            color: #ffffff !important;
        }

        .swal2-styled.swal2-cancel {
            background-color: #64748b !important;
            color: #ffffff !important;
        }

        /* --- Toast Specific Sleek Styling --- */
        .swal2-container.swal2-top-end .swal2-toast,
        .swal2-toast {
            width: auto !important;
            min-width: 240px !important;
            max-width: 380px !important;
            padding: 10px 16px !important;
            border-radius: 12px !important;
            background: #ffffff !important;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.12) !important;
            border: 1px solid #f1f5f9 !important;
            display: flex !important;
            align-items: center !important;
            font-family: 'Kanit', sans-serif !important;
        }

        .swal2-toast .swal2-icon {
            transform: scale(0.75) !important;
            margin: 0 10px 0 0 !important;
            flex-shrink: 0 !important;
        }

        .swal2-toast .swal2-title {
            font-size: 0.88rem !important;
            font-weight: 600 !important;
            color: #1e293b !important;
            margin: 0 !important;
            padding: 0 !important;
            line-height: 1.3 !important;
        }

        .swal2-toast .swal2-timer-progress-bar {
            background: #3b82f6 !important;
            height: 3px !important;
            border-radius: 0 0 12px 12px !important;
        }

        /* --- Dashboard Progress Cards & Custom Elements --- */
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


        .select2-container--default.select2-container--disabled .select2-selection--single {
            background-color: #f1f5f9 !important;
            border: 1px solid #e2e8f0 !important;
            color: #94a3b8 !important;
            cursor: not-allowed !important;
        }

        /* Select2 Form Validation State */
        .is-invalid+.select2-container .select2-selection--single,
        .was-validated select:invalid+.select2-container .select2-selection--single {
            border-color: #ef4444 !important;
        }


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
        /* ===== Add Company Form ===== */

.company-form-group {
    padding: 20px 24px 4px 24px;
    margin: 0;
    width: 100%;
    box-sizing: border-box;
}

.company-form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 700;
    font-size: 0.90rem;
    color: #334155;
}

.company-name-input {
    display: block;
    width: 100% !important;
    height: 42px;
    box-sizing: border-box !important;

    background-color: #f8fafc !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 10px !important;

    padding: 9px 14px !important;
    font-size: 0.90rem !important;
    font-weight: 500 !important;
    color: #334155 !important;

    outline: none !important;
    box-shadow: none !important;

    transition: all 0.2s ease;
}

.company-name-input:focus {
    background-color: #ffffff !important;
    border-color: #007aff !important;
    box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.10) !important;
}

/* ===== Validation Error ===== */

.company-name-input.is-invalid {
    background-color: #fffafa !important;
    border: 1px solid #ef4444 !important;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.10) !important;
}

.company-name-input.is-invalid:focus {
    border-color: #ef4444 !important;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.12) !important;
}

.company-name-error {
    display: none;
    margin-top: 6px;
    color: #ef4444;
    font-size: 0.78rem;
    font-weight: 600;
    line-height: 1.4;
}

.company-name-error.show {
    display: block;
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
            <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/main" class="acc-brand-wrap">
                <div class="acc-brand-logo">
                    <img src="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/assets/images/G_AM_logo-01.jpg"
                        alt="Cpd Acc Logo">
                </div>
                <div class="acc-brand-info">
                    <h1 class="acc-brand-title">CPD ACC</h1>
                    <p class="acc-brand-subtitle">ระบบบริหารสำนักงานบัญชี</p>
                </div>
            </a>

            <!-- Sidebar Toggle Button -->
            <button type="button" id="header-burger-menu" class="header-burger-menu">
                <i class="ri-menu-line"></i>
            </button>

            <!-- Company / Workspace Dropdown List Container (Loop แสดงบริษัทที่มี) -->
            <div class="acc-company-container">
                <?php if (isset($data['companies']) && ! empty($data['companies'])): ?>
                    <?php foreach ($data['companies'] as $index => $company):
                            $companyId   = $company['company_id'] ?? $company['id'] ?? '';
                            $companyName = htmlspecialchars($company['company_name'] ?? 'ไม่มีชื่อบริษัท');
                            if (isset($data['active_company_id']) && ! empty($data['active_company_id'])) {
                                $isActive = ($companyId == $data['active_company_id']) ? 'active' : '';
                            } else {
                                $isActive = ($index === 0) ? 'active' : '';
                            }

                            // คำนวณปีทำงานที่เปิดใช้งานอยู่
                            $fiscalYears    = $company['fiscal_years'] ?? [];
                            $activeYear     = '';
                            $activeFiscalId = '';

                            if (! empty($fiscalYears) && isset($data['fiscal_id']) && ! empty($data['fiscal_id'])) {
                                foreach ($fiscalYears as $fy) {
                                    $fy_id = $fy['fiscal_id'] ?? $fy['id'] ?? '';
                                    if ($fy_id == $data['fiscal_id']) {
                                        $activeYear     = $fy['fiscal_years'] ?? $fy['working_year'] ?? $fy['year'] ?? '';
                                        $activeFiscalId = $fy_id;
                                        break;
                                    }
                                }
                            }
                    ?>

                        <!-- Workspace Dropdown Pill Button (แสดง Popper strategy fixed เพื่อลอยอยู่ด้านหน้า) -->
                        <div class="dropdown acc-workspace-dropdown" data-company-id="<?php echo $companyId ?>">
                            <button type="button" class="acc-workspace-btn <?php echo $isActive ?>"
                                id="wsDropdownBtn_<?php echo $companyId ?>" data-bs-toggle="dropdown" data-bs-auto-close="true"
                                data-bs-popper-config='{"strategy":"fixed"}' data-bs-boundary="viewport" aria-expanded="false"
                                data-company-id="<?php echo $companyId ?>" data-company-name="<?php echo $companyName ?>"
                                data-active-year="<?php echo $activeYear ?>" data-fiscal-id="<?php echo $activeFiscalId ?>"
                                onclick="selectCompany(this, '<?php echo $companyId ?>')">

                                <div class="acc-workspace-icon">
                                    <i class="ri-bank-line"></i>
                                </div>

                                <div class="acc-workspace-info">
                                    <span class="acc-workspace-badge">WORKSPACE</span>
                                    <span class="acc-workspace-name"
                                        title="<?php echo $companyName ?>"><?php echo $companyName ?></span>
                                    <span
                                        class="acc-workspace-year"><?php echo ! empty($activeYear) ? 'ปีทำงาน <span class="ws-year-text">' . $activeYear . '</span>' : '<span class="ws-year-text text-muted">ยังไม่ได้เลือกปี</span>' ?></span>
                                </div>

                                <i class="ri-arrow-down-s-line acc-workspace-arrow"></i>
                            </button>

                            <!-- Dropdown Menu (ตรงตามรูปภาพ 2) -->
                            <div class="dropdown-menu acc-workspace-menu"
                                aria-labelledby="wsDropdownBtn_<?php echo $companyId ?>">
                                <!-- Header Dropdown -->
                                <div class="acc-menu-header">
                                    <div class="acc-menu-header-icon">
                                        <i class="ri-bank-line"></i>
                                    </div>
                                    <div class="acc-menu-header-text">
                                        <h6 class="acc-menu-title"><?php echo $companyName ?></h6>
                                        <p class="acc-menu-subtitle">เลือกปีทำงานของสำนักงาน</p>
                                    </div>
                                </div>

                                <!-- การ์ดปีที่ใช้งานอยู่ (Active Year Highlight Card) -->
                                <div class="acc-active-year-card" <?php echo ! empty($activeYear) ? "onclick=\"selectFiscalYear('$companyId', '$activeYear', '$activeFiscalId')\"" : "" ?>>
                                    <div class="acc-active-year-left">
                                        <div class="acc-active-year-icon">
                                            <i class="ri-calendar-check-line"></i>
                                        </div>
                                        <div class="acc-active-year-info">
                                            <span class="acc-active-year-label">ปีที่ใช้งานอยู่</span>
                                            <span class="acc-active-year-val">
                                                <?php if (! empty($activeYear)): ?>
                                                    ปี <span class="card-active-year-val"><?php echo $activeYear ?></span>
                                                <?php else: ?>
                                                    <span class="card-active-year-val text-muted">ยังไม่ได้เลือกปี</span>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                    </div>
                                    <?php if (! empty($activeYear)): ?>
                                        <span class="acc-active-badge">กำลังใช้งาน</span>
                                    <?php endif; ?>
                                </div>

                                <!-- หมวดหมู่: เลือกปีอื่น -->
                                <div class="acc-other-years-wrap">
                                    <div class="acc-other-years-title">เลือกปีอื่น</div>
                                    <div class="acc-other-years-list" id="otherYearsList_<?php echo $companyId ?>">
                                        <?php
                                            $hasOtherYears = false;
                                            if (! empty($fiscalYears)):
                                                foreach ($fiscalYears as $fy):
                                                    $yVal          = $fy['fiscal_years'] ?? $fy['working_year'] ?? $fy['year'] ?? '';
                                                    $cCount        = $fy['customer_count'] ?? 0;
                                                    $fId           = $fy['fiscal_id'] ?? $fy['id'] ?? '';
                                                    $hasOtherYears = true;
                                        ?>
                                                <a href="javascript:void(0);" class="acc-other-year-item"
                                                    data-year="<?php echo $yVal ?>"
                                                    onclick="selectFiscalYear('<?php echo $companyId ?>', '<?php echo $yVal ?>', '<?php echo $fId ?>')">
                                                    <div class="acc-other-year-icon">
                                                        <i class="ri-calendar-line"></i>
                                                    </div>
                                                    <div class="acc-other-year-info">
                                                        <span class="acc-other-year-val">ปี <?php echo $yVal ?></span>
                                                        <span
                                                            class="acc-other-year-sub"><?php echo $cCount > 0 ? $cCount . ' ลูกค้า' : 'ปีทำงาน' ?></span>
                                                    </div>
                                                </a>
                                            <?php endforeach; ?>
                                        <?php endif; ?>

                                        <?php if (! $hasOtherYears): ?>
                                            <div class="acc-no-years-sub text-muted px-2 py-1" style="font-size: 0.78rem;">
                                                ไม่มีปีอื่นให้เลือก
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- ท้ายเมนู: จัดการปีทำงาน -->
                                <div class="acc-menu-footer">
                                    <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/main"
                                        class="acc-manage-year-btn" onclick="selectCompanyById('<?php echo $companyId ?>')">
                                        <i class="ri-sound-module-line"></i>
                                        <span>แก้ไขข้อมูลบริษัท</span>
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
                        <?php echo(! empty($data['is_super_admin'] ?? $_SESSION['is_super_admin'] ?? null) && ($data['is_super_admin'] ?? $_SESSION['is_super_admin']) === '1') ? 'ผู้ดูแลระบบ' : 'ผู้ใช้งานระบบ' ?>
                    </span>
                </div>
            </div>

            <!-- Logout Button -->
            <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/logout" class="acc-logout-btn"
                title="ออกจากระบบ">
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
               <div class="company-form-group">

    <label for="companyNameInput" class="company-form-label">
        ชื่อบริษัท
        <span class="text-danger">*</span>
    </label>

    <input type="text"
        class="form-control company-name-input"
        id="companyNameInput"
        name="company_name"
        placeholder="กรอกชื่อบริษัท"
        oninput="clearCompanyNameError()">

    <div id="companyNameError" class="company-name-error"></div>

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

    <script
        src="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/template/assets/js/jquery-3.1.1.min.js"></script>

    <script>
        // ตรวจสอบว่าอยู่ในหน้าฝั่ง Backoffice หรือไม่
        function checkIsBackoffice() {
            return Boolean(

                document.querySelector('.sidebar-area') ||
                window.location.pathname.includes('/backoffice') ||
                window.location.pathname.includes('/customer') ||
                window.location.pathname.includes('/employee') ||
                window.location.pathname.includes('/register_board') ||
                window.location.pathname.includes('/closing') ||

                window.location.pathname.includes('/tasks')
            );
        }

        // ฟังก์ชันย้ายตำแหน่งการ์ดบริษัทไปหน้าสุด
        function moveCompanyCardToFront(companyId) {
            if (!companyId) return;
            const container = document.querySelector('.acc-company-container');
            const dropdown = document.querySelector('.acc-workspace-dropdown[data-company-id="' + companyId + '"]');
            if (container && dropdown) {
                container.prepend(dropdown);
                container.scrollTo({ left: 0, behavior: 'smooth' });
            }
        }

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
            const year = element.getAttribute('data-active-year') || '';

            const workspaceNameEl = document.getElementById('currentWorkspaceName');
            if (name && workspaceNameEl) {
                workspaceNameEl.textContent = name;
            }

            // เลื่อน scroll มาที่ปุ่มที่เลือก
            element.scrollIntoView({ behavior: 'smooth', inline: 'nearest', block: 'nearest' });

            // เก็บค่าลง localStorage
            localStorage.setItem('bo_selected_company', companyId);
            if (year) {
                localStorage.setItem('bo_selected_year', year);
            }

            // อัปเดตปีที่เลือกในแบนเนอร์หน้า index (ถ้ามี)
            const selectedValEl = document.querySelector('.notice-selected-value');
            if (selectedValEl) {
                if (year && year !== 'ยังไม่ได้เลือกปี') {
                    selectedValEl.textContent = 'ปี ' + year;
                } else {
                    selectedValEl.textContent = 'ยังไม่ได้เลือก';
                }
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


        function getSafeUrlAfterYearChange() {
            var baseUrl = "<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>";
            var path = window.location.pathname;

            // รายการ path ที่เป็นหน้า "รายละเอียดของลูกค้ารายตัว"
            // เพิ่ม path อื่นๆ ที่ผูกกับ customer_id เฉพาะเจาะจงได้ที่นี่
            var customerScopedPaths = [
                '/customer_drive',   // หน้าคลังไฟล์ลูกค้า (ที่ผูกกับ id)
                '/customer_link'     // หน้าตั้งค่าลิงก์อัปโหลด (ถ้าใช้ path นี้)
            ];

            for (var i = 0; i < customerScopedPaths.length; i++) {
                if (path.indexOf(customerScopedPaths[i]) !== -1) {
                    // พากลับไปหน้าลิสต์ลูกค้า ให้ user เลือกลูกค้าใหม่เองในปีที่เพิ่งสลับ
                    return baseUrl + '/customer';
                }
            }

            // หน้าอื่น ๆ ที่ไม่ได้ผูกกับลูกค้ารายตัว (เช่น หน้า dashboard, หน้าลิสต์ทั่วไป) ให้ reload หน้าเดิมได้ตามปกติ
            return window.location.href;
        }

        // 2. ฟังก์ชันเลือกปีทำงานจาก Dropdown
        function selectFiscalYear(companyId, year, fiscalId) {
            if (!companyId || !year) return;

            const isBackoffice = checkIsBackoffice();

            // อัปเดต Text ในปุ่ม Workspace ของบริษัทนั้น
            const wsBtn = document.querySelector('.acc-workspace-btn[data-company-id="' + companyId + '"]');
            if (wsBtn) {
                wsBtn.setAttribute('data-active-year', year);
                if (fiscalId) wsBtn.setAttribute('data-fiscal-id', fiscalId);
                const yrContainer = wsBtn.querySelector('.acc-workspace-year');
                if (yrContainer) {
                    yrContainer.innerHTML = 'ปีทำงาน <span class="ws-year-text">' + year + '</span>';
                }

                // อัปเดตในการ์ดปีที่ใช้งานอยู่
                const dropdownWrapper = wsBtn.closest('.acc-workspace-dropdown');
                if (dropdownWrapper) {
                    const activeYearValWrapper = dropdownWrapper.querySelector('.acc-active-year-val');
                    if (activeYearValWrapper) {
                        activeYearValWrapper.innerHTML = 'ปี <span class="card-active-year-val">' + year + '</span>';
                    }
                    const activeCard = dropdownWrapper.querySelector('.acc-active-year-card');
                    if (activeCard && !activeCard.querySelector('.acc-active-badge')) {
                        activeCard.insertAdjacentHTML('beforeend', '<span class="acc-active-badge">กำลังใช้งาน</span>');
                    }
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
            if (wsBtn) {
                const openDropdown = bootstrap.Dropdown.getInstance(wsBtn);
                if (openDropdown) {
                    openDropdown.hide();
                }
            }

            // ถ้าอยู่ในหน้า Backoffice: ย้าย Card ไปหน้าสุด และ Switch Session Context
            if (isBackoffice) {
                moveCompanyCardToFront(companyId);

                if (fiscalId) {
                    $.ajax({
                        type: "POST",
                        url: "<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/fiscal_years/set_context",
                        data: { fiscal_id: fiscalId },
                        dataType: "json",
                        success: function (res) {
                        var redirectUrl = getSafeUrlAfterYearChange();

                        if (res && res.result === 1) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'success',
                                    title: 'สลับปีทำงาน ' + year + ' เรียบร้อยแล้ว',
                                    showConfirmButton: false,
                                    timer: 1000
                                }).then(() => {
                                    window.location.href = redirectUrl;
                                });
                            } else {
                                window.location.href = redirectUrl;
                            }
                        } else {
                            window.location.href = redirectUrl;
                        }
                        },
                        error: function () {
                            window.location.reload();
                        }
                    });
                    return;
                }
            }

            // แจ้งเตือนสลับปีสำเร็จ (Toast สำหรับหน้าปกติ)
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
    clearCompanyNameError();
    const modalElement = document.getElementById('addCompanyModal');
    const myModal = new bootstrap.Modal(modalElement);
    myModal.show();
}

        let isSubmittingCompany = false;
function clearCompanyNameError() {
    const input = document.getElementById('companyNameInput');
    const errorEl = document.getElementById('companyNameError');

    if (input) {
        input.classList.remove('is-invalid');
    }

    if (errorEl) {
        errorEl.classList.remove('show');
        errorEl.textContent = '';
    }
}

function showCompanyNameError(message) {
    const input = document.getElementById('companyNameInput');
    const errorEl = document.getElementById('companyNameError');

    if (input) {
        input.classList.add('is-invalid');
        input.focus();
    }

    if (errorEl) {
        errorEl.textContent = message;
        errorEl.classList.add('show');
    }
}

function addCompany() {
    if (isSubmittingCompany) return;

    var companyName = $('input[name="company_name"]').val().trim();
    clearCompanyNameError();

    if (!companyName) {
        showCompanyNameError('กรุณากรอกชื่อบริษัท');
        return;
    }

            isSubmittingCompany = true;
            const submitBtn = $('#addCompanyModal .btn-primary');
            submitBtn.prop('disabled', true).text('กำลังบันทึก...');

            var formData = $('#addCompanyForm').serialize();
            $.ajax({
                type: "POST",
                url: "<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/company/add",
                data: formData,
                dataType: "json",
                success: function (response) {
                    isSubmittingCompany = false;
                    submitBtn.prop('disabled', false).text('บันทึก');

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
                        showCompanyNameError(response.msg || 'ไม่สามารถบันทึกข้อมูลได้');
                    }
                },
                error: function (err) {
                    isSubmittingCompany = false;
                    submitBtn.prop('disabled', false).text('บันทึก');
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

        // คืนค่าบริษัทที่เคยเลือกไว้เมื่อเปิดหน้าเว็บ และจัดตำแหน่งการ์ดให้อยู่หน้าสุดใน Backoffice
        $(document).ready(function () {
            const isBackoffice = checkIsBackoffice();

            if (isBackoffice) {
                // หากอยู่ในฝั่ง Backoffice ให้หาการ์ดที่ Active หรือมีข้อมูลอยู่ใน Session/Server
                let activeBtn = document.querySelector('.acc-workspace-btn.active');
                if (activeBtn) {
                    let activeCompanyId = activeBtn.getAttribute('data-company-id');
                    if (activeCompanyId) {
                        moveCompanyCardToFront(activeCompanyId);
                    }
                }
            } else {
                let savedCompany = localStorage.getItem('bo_selected_company');
                if (savedCompany) {
                    selectCompanyById(savedCompany);
                } else {
                    let firstCompany = document.querySelector('.acc-workspace-btn');
                    if (firstCompany) {
                        let companyId = firstCompany.getAttribute('data-company-id');
                        selectCompany(firstCompany, companyId);
                    }
                }
            }
        });
    </script>