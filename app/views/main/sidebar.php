<?php
// app/views/main/sidebar.php

// ตรวจสอบ URL ปัจจุบันสำหรับ Active State
$current_url = $_GET['url'] ?? 'backoffice';
$now_page = trim(strtok($current_url, '/'));

$overview_pages       = ['main', 'home', 'backoffice', 'dashboard'];
$monthly_dash_pages   = ['monthly_dashboard'];
$yearly_dash_pages    = ['yearly_dashboard'];
$monthly_task_pages   = ['monthly_tasks', 'tasks'];
$closing_pages        = ['closing', 'financial_statement'];
$registration_pages   = ['registration', 'registration_tasks'];
$customer_pages       = ['customer', 'customer_add', 'customer_edit'];
$employee_pages       = ['employee', 'employee_add', 'employee_edit', 'staff'];
$task_setting_pages   = ['task_settings', 'task_config'];
$message_pages        = ['messages', 'chat', 'customer_message'];
$postit_pages         = ['postit', 'notes', 'reminders'];
$system_setting_pages = ['settings', 'setting', 'system_setting'];
$manual_pages         = ['manual', 'tutorial', 'videos'];
?>

<style>
    /* ปรับแต่ง Sidebar ให้ตรงตามภาพต้นแบบ */
    .sidebar-area {
        background-color: #F7F9FB;
        border-right: 1px solid #edf2f7;
        font-family: 'Kanit', 'Segoe UI', Tahoma, sans-serif;
        width: 240px;
        padding-top: 14px;
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
        border: 1.5px solid #dbeafe;
        border-radius: 12px;
        padding: 10px 14px;
        color: #0066fe;
        font-weight: 700;
        font-size: 0.90rem;
        text-decoration: none;
        box-shadow: 0 2px 8px rgba(0, 102, 254, 0.06);
        transition: all 0.2s ease;
    }

    .overview-pill-btn:hover,
    .overview-pill-btn.active {
        background-color: #eff6ff;
        border-color: #93c5fd;
        color: #0052cc;
    }

    .overview-pill-btn i {
        font-size: 1.25rem;
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
    }
</style>

<div class="sidebar-area" id="sidebar-area">

    <!-- 1. ปุ่มภาพรวมสำนักงาน (การ์ดไฮไลท์ด้านบน) -->
    <div class="sidebar-top-overview">
        <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/backoffice"
            class="overview-pill-btn <?php echo in_array($now_page, $overview_pages) ? 'active' : '' ?>">
            <i class="ri-home-4-line"></i>
            <span>ภาพรวมสำนักงาน</span>
        </a>
    </div>

    <aside id="layout-menu" class="layout-menu menu-vertical menu active" data-simplebar>
        <ul class="menu-inner">

            <li class="menu-item <?php echo in_array($now_page, $monthly_dash_pages) ? 'open active' : '' ?>">
                <a href="javascript:void(0);"
                    class="menu-link <?php echo in_array($now_page, $monthly_dash_pages) ? 'active' : '' ?>">
                    <i class="ri-bar-chart-grouped-line menu-icon"></i>
                    <span class="title">ภาพรวมสำนักงาน</span>
                </a>
            </li>

            <!-- หมวดหมู่: งานประจำปี -->
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
                <a href="javascript:void(0);"
                    class="menu-link <?php echo in_array($now_page, $monthly_task_pages) ? 'active' : '' ?>">
                    <i class="ri-calendar-check-line menu-icon"></i>
                    <span class="title">จัดการงานรายเดือน</span>
                </a>
            </li>

            <li class="menu-item <?php echo in_array($now_page, $closing_pages) ? 'open active' : '' ?>">
                <a href="javascript:void(0);"
                    class="menu-link <?php echo in_array($now_page, $closing_pages) ? 'active' : '' ?>">
                    <i class="ri-file-text-line menu-icon"></i>
                    <span class="title">ปิดงบการเงิน</span>
                </a>
            </li>

            <li class="menu-item <?php echo in_array($now_page, $registration_pages) ? 'open active' : '' ?>">
                <a href="javascript:void(0);"
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
                <a href="javascript:void(0);"
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
                <a href="javascript:void(0);"
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
$(document).ready(function() {
    // ดักจับการคลิกที่ปุ่มใน Sidebar
    $(document).on('click', '.overview-pill-btn', function(e) {
        e.preventDefault(); 
        
        let targetUrl = $(this).attr('href');
        if (!targetUrl || targetUrl === '#' || targetUrl.includes('javascript:')) return;

        // อัปเดตสถานะ Active ของปุ่ม Sidebar
        $('.overview-pill-btn, .sidebar-area .menu-link').removeClass('active');
        $(this).addClass('active');

        // แสดง Loading อ่อนๆ ที่พื้นที่เนื้อหา
        $('.content-wrapper').css('opacity', '0.5');

        // ใช้ AJAX ไปดึงข้อมูลหน้าใหม่มา
        $.ajax({
            url: targetUrl,
            type: 'GET',
            success: function(response) {
                // ค้นหาเฉพาะส่วนเนื้อหา (.content-wrapper) จาก HTML ที่โหลดมาใหม่
                let newContent = $(response).find('.content-wrapper').html();
                
                if (newContent !== undefined) {
                    $('.content-wrapper').html(newContent).css('opacity', '1');
                    window.history.pushState({path: targetUrl}, '', targetUrl);
                } else {
                    window.location.href = targetUrl;
                }
            },
            error: function() {
                window.location.href = targetUrl;
            }
        });
    });

    // ทำให้เวลากดปุ่ม Back/Forward ของ Browser ทำงานได้ถูกต้อง
    $(window).on('popstate', function() {
        window.location.reload();
    });
});
</script>