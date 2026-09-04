<?php
// app/views/main/sidebar.php

// ตรวจสอบ URL ปัจจุบันสำหรับ Active State
$current_url = $_GET['url'] ?? 'backoffice';
$now_page = trim(strtok($current_url, '/'));

    $overview_pages       = ['backoffice'];
    $monthly_dash_pages   = ['monthly_dashboard'];
    $yearly_dash_pages    = ['yearly_dashboard'];
    $monthly_task_pages   = ['monthly_tasks', 'monthly_task'];
    $closing_pages        = ['closing', 'financial_statement'];
    $registration_pages   = ['registration', 'register_board'];
    $customer_pages       = ['customer', 'customer_add', 'customer_edit'];
    $employee_pages       = ['employee', 'employee_add', 'employee_edit', 'staff'];
    $task_setting_pages   = ['tasks'];
    $message_pages        = ['messages', 'chat', 'customer_message'];
    $postit_pages         = ['post_it', 'postit', 'notes', 'reminders'];
    $system_setting_pages = ['settings', 'setting', 'system_setting'];
    $manual_pages         = ['manual', 'tutorial', 'videos'];
?>

<style>
    /* ปรับแต่ง Sidebar ให้ตรงตามภาพต้นแบบ */
    .sidebar-area {
        background-color: #F7F9FB;
        border-right: 1px solid #edf2f7;
        font-family: 'Kanit', 'Segoe UI', Tahoma, sans-serif;
        width: 300px;
        /* ขยายจาก 240px เป็น 260px เพื่อไม่ให้ข้อความตกขอบ */
        padding-top: 80px;
        /* เพิ่ม padding-top เพื่อหลบแถบ Navbar ด้านบน (ทดแทนปุ่มที่ถูกซ่อนไป) */
    }

    /* ปุ่มภาพรวมสำนักงาน ด้านบนสุด (การ์ดมนขอบสีขาว มีเงาและไอคอนสีฟ้า) */
    .sidebar-top-overview {
        padding: 4px 14px 14px 14px;
    }

    .overview-pill-btn {
        display: flex;
        align-items: center;
        gap: 10px;
        background-color: #ffffff;
        border: 1px solid #f1f5f9;
        border-radius: 12px;
        padding: 10px 14px;
        color: #64748b;
        font-weight: 600;
        font-size: 0.90rem;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .overview-pill-btn:hover {
        background-color: #f8fafc;
        color: #3b82f6;
    }

    .overview-pill-btn.active {
        background-color: #eff6ff;
        /* Changed from #ffffff to match other active menus */
        border-color: #eff6ff;
        color: #0066fe;
        font-weight: 700;
        box-shadow: 0 4px 12px rgba(0, 102, 254, 0.08);
    }

    .overview-pill-btn i {
        font-size: 1.25rem;
        color: inherit;
    }

    .overview-pill-btn.active i {
        color: #0066fe;
    }

    /* หัวข้อหมวดหมู่ */
    .sidebar-area .menu-title {
        margin-top: 16px !important;
        margin-bottom: 6px !important;
        padding: 0 16px !important;
        line-height: 1 !important;
        display: block !important;
    }

    .sidebar-area .menu-title .menu-title-text {
        font-size: 0.78rem !important;
        font-weight: 700 !important;
        color: #1e293b !important;
        letter-spacing: 0.02em !important;
    }

    /* เมนูย่อย */
    .sidebar-area .menu-item {
        display: block !important;
        margin: 2px 0 !important;
        padding: 0 !important;
    }

    .sidebar-area .menu-item .menu-link {
        margin: 2px 10px !important;
        padding: 7px 12px !important;
        border-radius: 8px !important;
        min-height: unset !important;
        height: auto !important;
        display: flex !important;
        align-items: center !important;
        color: #1e293b !important;
        font-size: 0.88rem !important;
        font-weight: 600 !important;
        transition: all 0.15s ease !important;
        text-decoration: none !important;
    }

    .sidebar-area .menu-item .menu-link:hover {
        background-color: #f8fafc !important;
        color: #0066fe !important;
    }

    .sidebar-area .menu-item .menu-link.active,
    .sidebar-area .menu-item.open>.menu-link {
        background-color: #eff6ff !important;
        color: #0066fe !important;
        font-weight: 700 !important;
    }

    /* ไอคอนข้างหน้าเมนู */
    .sidebar-area .menu-item .menu-link .menu-icon {
        font-size: 1.15rem !important;
        margin-right: 10px !important;
        color: #94a3b8 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: color 0.15s ease;
    }

    .sidebar-area .menu-item .menu-link:hover .menu-icon,
    .sidebar-area .menu-item .menu-link.active .menu-icon {
        color: #0066fe !important;
    }

    .sidebar-area .menu-item .menu-link .title {
        font-size: 0.86rem !important;
        line-height: 1.3 !important;
        overflow: hidden;
        text-overflow: ellipsis;
        flex-grow: 1;
        white-space: nowrap;
    }

    /* กฎสำหรับตอนที่หด Sidebar (ย่อเมนู) */
    [sidebar-data-theme="sidebar-hide"] .sidebar-area .menu-title,
    [sidebar-data-theme="sidebar-hide"] .sidebar-area .menu-item .menu-link .title,
    [sidebar-data-theme="sidebar-hide"] .sidebar-top-overview {
        display: none !important;
    }

    [sidebar-data-theme="sidebar-hide"] .sidebar-area .menu-item .menu-link {
        justify-content: center !important;
        padding: 10px !important;
    }

    [sidebar-data-theme="sidebar-hide"] .sidebar-area .menu-item .menu-link .menu-icon {
        margin-right: 0 !important;
        font-size: 1.4rem !important;
    }
</style>

<div class="sidebar-area" id="sidebar-area">

    <!-- 1. ปุ่มภาพรวมสำนักงาน (การ์ดไฮไลท์ด้านบน) -->
    <!-- <div class="sidebar-top-overview">
        <a href="backoffice"
            class="overview-pill-btn <?php echo in_array($now_page, $overview_pages) ? 'active' : '' ?>">
            <i class="ri-home-4-line"></i>
            <span>ภาพรวมสำนักงาน</span>
        </a>
    </div> -->

    <aside id="layout-menu" class="layout-menu menu-vertical menu active" data-simplebar>
        <ul class="menu-inner">
            <!-- หมวดหมู่: งานประจำปี -->


            <li class="menu-item <?php echo in_array($now_page, $overview_pages) ? 'open active' : '' ?>">
                <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/backoffice"
                    class="menu-link <?php echo in_array($now_page, $overview_pages) ? 'active' : '' ?>">
                    <i class="ri-home-4-line menu-icon"></i>
                    <span class="title">ภาพรวมสำนักงาน</span>
                </a>
            </li>
            <li class="menu-title small">
                <span class="menu-title-text">งานประจำปี</span>
            </li>
            <li class="menu-item <?php echo in_array($now_page, $monthly_dash_pages) ? 'open active' : '' ?>">
                <a href="javascript:void(0);"
                    class="menu-link <?php echo in_array($now_page, $monthly_dash_pages) ? 'active' : '' ?>">
                    <i class="ri-bar-chart-grouped-line menu-icon"></i>
                    <span class="title">แดชบอร์ดรายเดือน</span>
                </a>
            </li>

            <li class="menu-item <?php echo in_array($now_page, $yearly_dash_pages) ? 'open active' : '' ?>">
                <a href="javascript:void(0);"
                    class="menu-link <?php echo in_array($now_page, $yearly_dash_pages) ? 'active' : '' ?>">
                    <i class="ri-line-chart-line menu-icon"></i>
                    <span class="title">แดชบอร์ดรายปี</span>
                </a>
            </li>

            <li class="menu-item <?php echo in_array($now_page, $monthly_task_pages) ? 'open active' : '' ?>">
                <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/monthly_task"
                    class="menu-link <?php echo in_array($now_page, $monthly_task_pages) ? 'active' : '' ?>">
                    <i class="ri-calendar-check-line menu-icon"></i>
                    <span class="title">จัดการงานรายเดือน</span>
                </a>
            </li>

            <li class="menu-item <?php echo in_array($now_page, $closing_pages) ? 'open active' : '' ?>">
                <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/closing"
                    class="menu-link <?php echo in_array($now_page, $closing_pages) ? 'active' : '' ?>">
                    <i class="ri-file-text-line menu-icon"></i>
                    <span class="title">ปิดงบการเงิน</span>
                </a>
            </li>

            <li class="menu-item <?php echo in_array($now_page, $registration_pages) ? 'open active' : '' ?>">
                <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/register_board"
                    class="menu-link <?php echo in_array($now_page, $registration_pages) ? 'active' : '' ?>">
                    <i class="ri-file-paper-2-line menu-icon"></i>
                    <span class="title">จัดการงานทะเบียน</span>
                </a>
            </li>

            <!-- หมวดหมู่: จัดการข้อมูล -->
            <li class="menu-title small">
                <span class="menu-title-text">จัดการข้อมูล</span>
            </li>

            <li class="menu-item <?php echo in_array($now_page, $customer_pages) ? 'open active' : '' ?>">
                <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/customer"
                    class="menu-link <?php echo in_array($now_page, $customer_pages) ? 'active' : '' ?>">
                    <i class="ri-user-3-line menu-icon"></i>
                    <span class="title">ลูกค้า</span>
                </a>
            </li>

            <li class="menu-item <?php echo in_array($now_page, $employee_pages) ? 'open active' : '' ?>">
                <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/employee"
                    class="menu-link <?php echo in_array($now_page, $employee_pages) ? 'active' : '' ?>">
                    <i class="ri-team-line menu-icon"></i>
                    <span class="title">พนักงาน</span>
                </a>
            </li>

            <li class="menu-item <?php echo in_array($now_page, $task_setting_pages) ? 'open active' : '' ?>">
                <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/tasks"
                    class="menu-link <?php echo in_array($now_page, $task_setting_pages) ? 'active' : '' ?>">
                    <i class="ri-checkbox-circle-line menu-icon"></i>
                    <span class="title">ตั้งค่างานที่ต้องทำ</span>
                </a>
            </li>

            <!-- หมวดหมู่: สื่อสารและแจ้งเตือน -->
            <li class="menu-title small">
                <span class="menu-title-text">สื่อสารและแจ้งเตือน</span>
            </li>

            <li class="menu-item <?php echo in_array($now_page, $message_pages) ? 'open active' : '' ?>">
                <a href="javascript:void(0);"
                    class="menu-link <?php echo in_array($now_page, $message_pages) ? 'active' : '' ?>">
                    <i class="ri-chat-3-line menu-icon"></i>
                    <span class="title">ส่งข้อความถึงลูกค้า</span>
                </a>
            </li>

            <li class="menu-item <?php echo in_array($now_page, $postit_pages) ? 'open active' : '' ?>">
                <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/post_it"
                    class="menu-link <?php echo in_array($now_page, $postit_pages) ? 'active' : '' ?>">
                    <i class="ri-sticky-note-line menu-icon"></i>
                    <span class="title">Post-it แจ้งเตือน</span>
                </a>
            </li>

            <!-- หมวดหมู่: ตั้งค่าระบบ -->
            <li class="menu-title small">
                <span class="menu-title-text">ตั้งค่าระบบ</span>
            </li>

            <li class="menu-item <?php echo in_array($now_page, $system_setting_pages) ? 'open active' : '' ?>">
                <a href="javascript:void(0);"
                    class="menu-link <?php echo in_array($now_page, $system_setting_pages) ? 'active' : '' ?>">
                    <i class="ri-settings-4-line menu-icon"></i>
                    <span class="title">ตั้งค่าระบบ</span>
                </a>
            </li>

            <!-- หมวดหมู่: คู่มือ -->
            <li class="menu-title small">
                <span class="menu-title-text">คู่มือ</span>
            </li>

            <li class="menu-item <?php echo in_array($now_page, $manual_pages) ? 'open active' : '' ?>">
                <a href="javascript:void(0);"
                    class="menu-link <?php echo in_array($now_page, $manual_pages) ? 'active' : '' ?>">
                    <i class="ri-play-circle-line menu-icon"></i>
                    <span class="title">วิดีโอสอนการใช้งาน</span>
                </a>
            </li>

        </ul>
    </aside>
</div>

<script>
    $(document).ready(function () {
        // ทำให้เวลากดปุ่ม Back/Forward ของ Browser ทำงานได้ถูกต้อง
        $(window).on('popstate', function () {
            window.location.reload();
        });
    });
</script>