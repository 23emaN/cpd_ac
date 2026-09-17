<?php
// app/views/main/sidebar.php

// ตรวจสอบ URL ปัจจุบันสำหรับ Active State

$current_url = $_GET['url'] ?? 'backoffice';
$now_page = trim(strtok($current_url, '/'));

$overview_pages = ['backoffice'];
$monthly_dash_pages = ['monthly_dash'];
$customer_dash_pages = ['customer_dash'];
$yearly_dash_pages = ['yearly_dash'];
$monthly_task_pages = ['monthly_tasks', 'monthly_task'];
$closing_pages = ['closing',];
$registration_pages = ['registration', 'registration_board', 'register_board'];
$customer_pages = ['customer', 'customer_add', 'customer_edit'];
$employee_pages = ['employee', 'employee_add', 'employee_edit', 'staff'];
$task_setting_pages = ['tasks'];
$message_pages = ['messages', 'chat', 'customer_message'];
$postit_pages = ['post_it', 'postit', 'notes', 'reminders'];
$system_setting_pages = ['settings', 'setting', 'system_setting'];
$manual_pages = ['manual', 'tutorial', 'videos'];
$assinge_pages = ['assign_task'];
$issues_pages = ['issues', 'outstanding_issues']; // เมนูใหม่: ประเด็นคงค้าง

// Fetch Assign Task & Post-it Count
$assign_task_count = 0;
$post_it_count = 0;
if (isset($_SESSION['fiscal_year_id']) && $_SESSION['fiscal_year_id'] !== '') {
    try {
        require_once dirname(__DIR__) . '/../config/Connection.php';
        $pdo = \App\Config\Connection::getInstance()->getPdo();
        
        $current_user_id = $data['user_id'] ?? null;
        if ($current_user_id) {
            // Assign Task
            if (isset($active_company_id) && $active_company_id !== '') {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM tbl_assign_task WHERE company_id = ? AND fiscal_id = ? AND user_id = ? AND assign_status != '3'");
                $stmt->execute([$active_company_id, $_SESSION['fiscal_year_id'], $current_user_id]);
            } else {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM tbl_assign_task WHERE fiscal_id = ? AND user_id = ? AND assign_status != '3'");
                $stmt->execute([$_SESSION['fiscal_year_id'], $current_user_id]);
            }
            $assign_task_count = $stmt->fetchColumn();

            // Post-it
            $stmt2 = $pdo->prepare("SELECT COUNT(*) FROM tbl_post_it WHERE fiscal_year_id = ? AND user_id = ? AND status = '0'");
            $stmt2->execute([$_SESSION['fiscal_year_id'], $current_user_id]);
            $post_it_count = $stmt2->fetchColumn();
        }
    } catch (Exception $e) {}
}

?>

<style>
    /* ปรับแต่ง Sidebar ให้ตรงตามภาพต้นแบบ */
    .sidebar-area {
        background-color: #F7F9FB;

        font-family: 'Kanit', 'Segoe UI', Tahoma, sans-serif;

        width: 220px; /* ลดความกว้างของแถบด้านข้างลง */

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
        font-size: 9px; /* ลดขนาดตัวอักษรลง */
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

    }

    .overview-pill-btn i {
        font-size: 1.15rem;
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
        font-size: 0.72rem !important; /* ลดขนาดตัวอักษรลง */
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
        margin: -7px 10px !important;
        padding: 7px 12px !important;
        border-radius: 8px !important;
        min-height: unset !important;
        height: auto !important;
        display: flex !important;
        align-items: center !important;
        color: #1e293b !important;
        font-size: 0.8rem !important; /* ลดขนาดตัวอักษรลง */
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
        font-size: 16px !important;
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
        font-size: 10px !important; /* ลดขนาดตัวอักษรลง */
        line-height: 1 !important;
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

    [sidebar-data-theme="sidebar-hide"] .sidebar-area .menu-item .menu-link,
    [sidebar-data-theme="sidebar-hide"] .sidebar-area .menu-item .menu-link.active,
    [sidebar-data-theme="sidebar-hide"] .sidebar-area .menu-item.open>.menu-link {
        justify-content: center !important;
        padding: 10px !important;
        background-color: transparent !important;

    }

    [sidebar-data-theme="sidebar-hide"] .sidebar-area .menu-item .menu-link .menu-icon {
        margin-right: 0 !important;
        font-size: 1.4rem !important;
    }

    [sidebar-data-theme="sidebar-hide"] .sidebar-area .menu-item .menu-link.active .menu-icon,
    [sidebar-data-theme="sidebar-hide"] .sidebar-area .menu-item .menu-link:hover .menu-icon {
        color: #0066fe !important;
    }

    /* ซ่อนขีดสีม่วง/น้ำเงินด้านข้างแถบเมนู Active */
    .sidebar-area .menu-item .menu-link.active::before,
    .sidebar-area .menu-vertical .menu-item .menu-link.active::before,
    [sidebar-data-theme="sidebar-hide"] .sidebar-area .menu-item .menu-link.active::before {
        display: none !important;
        content: none !important;
    }

    /* --- Mobile Responsive Rules for Sidebar --- */
    @media (max-width: 768px) {
        .sidebar-area {
            position: fixed !important;
            top: 0 !important;
            left: -280px; /* Hide outside viewport by default */
            height: 100% !important;
            min-height: 100vh !important;
            width: 260px !important;
            z-index: 100000 !important; /* Force above header */
            transition: left 0.3s ease;
            box-shadow: 2px 0 10px rgba(0,0,0,0.05);
            padding-top: 20px; /* ลด padding-top เพราะไม่มี topbar บังแล้ว */
            background-color: #ffffff !important;
        }

        /* เมื่อถูกสั่งเปิดโดยคลิก Burger Menu (custom.js จะเปลี่ยน attribute ของ body เป็น sidebar-hide ในมือถือ) */
        body[sidebar-data-theme="sidebar-hide"] .sidebar-area {
            left: 0 !important;
        }

        /* Backdrop พื้นหลังสีดำจางๆ บนมือถือ */
        .sidebar-backdrop {
            display: none !important;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 100% !important;
            min-height: 100vh !important;
            background-color: rgba(0, 0, 0, 0.5) !important;
            z-index: 99999 !important;
            opacity: 0;
            transition: opacity 0.3s ease;
            cursor: pointer;
        }

        body[sidebar-data-theme="sidebar-hide"] .sidebar-backdrop {
            display: block !important;
            opacity: 1 !important;
        }
        
        /* ปิดโหมดซ่อนเมนูเล็ก (Mini sidebar) บนมือถือ ให้โชว์แบบเต็มเสมอเมื่อเปิด */
        [sidebar-data-theme="sidebar-hide"] .sidebar-area,
        body[sidebar-data-theme="sidebar-hide"] .sidebar-area {
            width: 260px !important;
        }
        [sidebar-data-theme="sidebar-hide"] .sidebar-area .menu-title,
        [sidebar-data-theme="sidebar-hide"] .sidebar-area .menu-item .menu-link .title {
            display: block !important;
        }
        [sidebar-data-theme="sidebar-hide"] .sidebar-area .menu-item .menu-link {
            justify-content: flex-start !important;
            padding: 7px 12px !important;
        }
        [sidebar-data-theme="sidebar-hide"] .sidebar-area .menu-item .menu-link .menu-icon {
            margin-right: 10px !important;
            font-size: 1.15rem !important;
        }

        /* ล้างระยะห่างของเนื้อหาหลักที่ถูกดันโดยเมนูด้านซ้ายบนจอคอม */
        body .main-content,
        body[sidebar-data-theme="sidebar-hide"] .main-content,
        body .layout-page,
        body .content-wrapper,
        .main-page-wrapper {
            padding-left: 0 !important;
            margin-left: 0 !important;
            width: 100% !important;
        }
    }
</style>

<div class="sidebar-backdrop" id="sidebar-backdrop" onclick="document.body.setAttribute('sidebar-data-theme', 'sidebar-show');"></div>

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

                <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/monthly_dash"

                    class="menu-link <?php echo in_array($now_page, $monthly_dash_pages) ? 'active' : '' ?>">
                    <i class="ri-bar-chart-grouped-line menu-icon"></i>
                    <span class="title">แดชบอร์ดรายเดือน</span>
                </a>
            </li>

            <li class="menu-item <?php echo in_array($now_page, $customer_dash_pages) ? 'open active' : '' ?>">
                <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/customer_dash"
                    class="menu-link <?php echo in_array($now_page, $customer_dash_pages) ? 'active' : '' ?>">
                    <i class="ri-user-search-line menu-icon"></i>
                    <span class="title">แดชบอร์ดลูกค้า</span>
                </a>
            </li>

            <li class="menu-item <?php echo in_array($now_page, $yearly_dash_pages) ? 'open active' : '' ?>">
                <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/yearly_dash"
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

            <li class="menu-item <?php echo in_array($now_page, $issues_pages) ? 'open active' : '' ?>">
                <a href="javascript:void(0);"
                    class="menu-link <?php echo in_array($now_page, $issues_pages) ? 'active' : '' ?>">
                    <i class="ri-history-line menu-icon"></i>
                    <span class="title">ประเด็นคงค้าง</span>
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
                <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/registration_board"
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
                <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/customer_message"
                    class="menu-link <?php echo in_array($now_page, $message_pages) ? 'active' : '' ?>">
                    <i class="ri-chat-3-line menu-icon"></i>
                    <span class="title">ส่งข้อความถึงลูกค้า</span>
                </a>
            </li>

            <li class="menu-item <?php echo in_array($now_page, $postit_pages) ? 'open active' : '' ?>">
                <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/post_it"
                    class="menu-link <?php echo in_array($now_page, $postit_pages) ? 'active' : '' ?>" style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; overflow: hidden;">
                        <i class="ri-sticky-note-line menu-icon"></i>
                        <span class="title">Post-it แจ้งเตือน</span>
                    </div>
                    <?php if ($post_it_count > 0): ?>
                        <span class="badge bg-danger rounded-pill" style="font-size: 0.65rem; padding: 3px 6px; margin-left: 5px;"><?php echo $post_it_count; ?></span>
                    <?php endif; ?>
                </a>
            </li>

            <!-- หมวดหมู่: ตั้งค่าระบบ -->
            <li class="menu-title small">
                <span class="menu-title-text">การมอบหมายงาน</span>
            </li>

            <li class="menu-item <?php echo in_array($now_page, $assinge_pages) ? 'open active' : '' ?>">
                <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/assign_task"
                    class="menu-link <?php echo in_array($now_page, $assinge_pages) ? 'active' : '' ?>" style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; overflow: hidden;">
                        <i class="ri-user-heart-line menu-icon"></i>
                        <span class="title">การมอบหมายงาน</span>
                    </div>
                    <?php if ($assign_task_count > 0): ?>
                        <span class="badge bg-danger rounded-pill" style="font-size: 0.65rem; padding: 3px 6px; margin-left: 5px;"><?php echo $assign_task_count; ?></span>
                    <?php endif; ?>
                </a>
            </li>

            <!-- หมวดหมู่: ตั้งค่าระบบ -->
           <li class="menu-title small">
                <span class="menu-title-text">ตั้งค่าระบบ</span>
            </li> 

            <li class="menu-item <?php echo in_array($now_page, $system_setting_pages) ? 'open active' : '' ?>">
                <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/system_setting"
                    class="menu-link <?php echo in_array($now_page, $system_setting_pages) ? 'active' : '' ?>">
                    <i class="ri-settings-4-line menu-icon"></i>
                    <span class="title">ตั้งค่าระบบ</span>
                </a>
            </li> 

            <li class="menu-item <?php echo in_array($now_page, $manual_pages) ? 'open active' : '' ?>">
                <a href="javascript:void(0);"
                    class="menu-link <?php echo in_array($now_page, $manual_pages) ? 'active' : '' ?>">
                    <i class="ri-play-circle-line menu-icon"></i>
                    <span class="title">คู่มือ</span>
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

        // Initialize Web Push is now handled by the profile toggle in header.php
    });

</script>