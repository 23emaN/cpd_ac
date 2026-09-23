-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 22, 2026 at 03:25 PM
-- Server version: 10.6.11-MariaDB
-- PHP Version: 8.2.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `admin_account`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_assign_task`
--

CREATE TABLE `tbl_assign_task` (
  `assign_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `fiscal_id` int(11) NOT NULL,
  `assign_title` text DEFAULT NULL,
  `assign_detail` text DEFAULT NULL,
  `assign_status` varchar(1) DEFAULT '0' COMMENT '0.ดำเนินการ\r\n1.รอตรวจ\r\n3.ปิดงาน',
  `due_date` date NOT NULL,
  `create_at` datetime NOT NULL,
  `create_user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_cd_activity`
--

CREATE TABLE `tbl_cd_activity` (
  `activity_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `node_id` int(11) DEFAULT NULL COMMENT 'ไม่ผูก FK โดยตั้งใจ',
  `link_id` int(11) DEFAULT NULL COMMENT 'ไม่ผูก FK โดยตั้งใจ',
  `actor_type` varchar(10) NOT NULL DEFAULT 'staff' COMMENT 'staff | guest | system',
  `actor_user_id` int(11) DEFAULT NULL,
  `actor_label` varchar(120) DEFAULT NULL,
  `action` varchar(40) NOT NULL,
  `detail` varchar(500) DEFAULT NULL,
  `ip` varbinary(16) DEFAULT NULL,
  `create_datetime` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_cd_link`
--

CREATE TABLE `tbl_cd_link` (
  `link_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `token` char(43) NOT NULL COMMENT 'random_bytes(32) base64url',
  `title` varchar(200) NOT NULL,
  `message` text DEFAULT NULL,
  `mode` varchar(10) NOT NULL DEFAULT 'collect' COMMENT 'collect | share',
  `root_node_id` int(11) DEFAULT NULL COMMENT 'NULL = ทั้งคลัง',
  `allow_upload` varchar(1) NOT NULL DEFAULT '1',
  `allow_download` varchar(1) NOT NULL DEFAULT '0',
  `password_hash` varchar(255) NOT NULL,
  `expires_datetime` datetime DEFAULT NULL,
  `max_uploads` int(11) DEFAULT NULL,
  `used_uploads` int(11) NOT NULL DEFAULT 0,
  `revoked` varchar(1) NOT NULL DEFAULT '0',
  `fail_count` int(11) NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `notified_first_open` varchar(1) NOT NULL DEFAULT '0',
  `last_open_datetime` datetime DEFAULT NULL,
  `create_by` int(11) NOT NULL,
  `create_datetime` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_cd_link_session`
--

CREATE TABLE `tbl_cd_link_session` (
  `session_id` int(11) NOT NULL,
  `link_id` int(11) NOT NULL,
  `session_token` char(43) NOT NULL,
  `guest_label` varchar(120) DEFAULT NULL,
  `ip` varbinary(16) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `expires_datetime` datetime NOT NULL,
  `create_datetime` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_cd_node`
--

CREATE TABLE `tbl_cd_node` (
  `node_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL COMMENT 'NULL = top level of the drive',
  `kind` varchar(10) NOT NULL DEFAULT 'file' COMMENT 'file | folder',
  `name` varchar(255) NOT NULL,
  `ext` varchar(10) DEFAULT NULL,
  `list_order` int(11) NOT NULL DEFAULT 0,
  `version` int(11) NOT NULL DEFAULT 0,
  `size` bigint(20) NOT NULL DEFAULT 0,
  `sha256` char(64) DEFAULT NULL,
  `source` varchar(10) NOT NULL DEFAULT 'staff' COMMENT 'staff | guest',
  `source_link_id` int(11) DEFAULT NULL COMMENT 'มาจากลิงก์แชร์ไหน (ไม่ผูก FK โดยตั้งใจ)',
  `guest_label` varchar(120) DEFAULT NULL,
  `create_by` int(11) DEFAULT NULL,
  `create_datetime` datetime NOT NULL DEFAULT current_timestamp(),
  `update_by` int(11) DEFAULT NULL,
  `update_datetime` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `delete_by` int(11) DEFAULT NULL,
  `delete_datetime` datetime DEFAULT NULL COMMENT 'trash soft-delete, never auto-expires'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_cd_version`
--

CREATE TABLE `tbl_cd_version` (
  `version_id` int(11) NOT NULL,
  `node_id` int(11) NOT NULL,
  `version` int(11) NOT NULL,
  `size` bigint(20) NOT NULL DEFAULT 0,
  `sha256` char(64) DEFAULT NULL,
  `via` varchar(20) NOT NULL DEFAULT 'staff_upload' COMMENT 'staff_upload | guest_upload | restore',
  `create_by` int(11) DEFAULT NULL,
  `guest_label` varchar(120) DEFAULT NULL,
  `create_datetime` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_closing_financial`
--

CREATE TABLE `tbl_closing_financial` (
  `closing_id` int(11) NOT NULL,
  `fiscal_year_id` int(11) NOT NULL,
  `closing_status` varchar(1) NOT NULL DEFAULT '0' COMMENT 'สถานะการปิดงบ',
  `closing_date` datetime DEFAULT NULL COMMENT 'วันที่ปิดงบ',
  `doc_status` varchar(1) NOT NULL DEFAULT '0' COMMENT 'สถานะเอกสาร',
  `doc_date` date DEFAULT NULL COMMENT 'วันที่ได้รับเอกสาร',
  `audit_status` varchar(1) NOT NULL DEFAULT '0' COMMENT 'สถานะผู้สอบ',
  `audit_date` date DEFAULT NULL COMMENT 'วันที่ตรวจสอบ',
  `budget_refund_date` date DEFAULT NULL COMMENT 'วันที่ได้รับงบคืน',
  `boj5_status` varchar(1) NOT NULL DEFAULT '0' COMMENT 'สถานะ บอจ. 5 ',
  `boj5_date` date DEFAULT NULL COMMENT 'วันที่นำส่ง บจอ',
  `dbd_efiling_status` varchar(1) NOT NULL DEFAULT '0' COMMENT 'สถานะของ DBD E-Filing',
  `dbd_efiling_date` date DEFAULT NULL COMMENT 'วันที่นำส่ง DBD E-Filing',
  `pnd50_status` varchar(1) NOT NULL DEFAULT '0' COMMENT 'สถานะ ภ.ง.ด.50',
  `pnd50_date` date DEFAULT NULL COMMENT 'วันที่นำส่ง ภ.ง.ด.50',
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_comment_tasks`
--

CREATE TABLE `tbl_comment_tasks` (
  `comment_id` int(11) NOT NULL,
  `customer_tasks_id` int(11) NOT NULL,
  `comment_user_id` int(11) NOT NULL,
  `comment_detail` text NOT NULL,
  `create_at` date NOT NULL,
  `is_read` varchar(1) NOT NULL DEFAULT '0',
  `is_reply` varchar(1) NOT NULL DEFAULT '0' COMMENT 'ตอบกลับ 0=ยังไม่ตอบกลับ,1=ตอบกลับแล้ว'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_companies`
--

CREATE TABLE `tbl_companies` (
  `company_id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL COMMENT 'ชื่อบริษัท',
  `user_id` int(11) NOT NULL,
  `active_status` varchar(1) NOT NULL DEFAULT '0' COMMENT '0 = ไม่ใช้งาน\r\n1 = ใช้งาน',
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_customers`
--

CREATE TABLE `tbl_customers` (
  `customer_id` int(11) NOT NULL COMMENT 'รหัสลูกค้า (Primary Key)',
  `fiscal_id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL COMMENT 'ชื่อลูกค้า หรือชื่อบริษัท',
  `active_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'สถานะการใช้งาน (1 = ใช้งาน, 0 = ระงับการใช้งาน)',
  `customer_phone` varchar(20) DEFAULT NULL COMMENT 'เบอร์โทรศัพท์ติดต่อ',
  `customer_email` varchar(100) DEFAULT NULL COMMENT 'อีเมลติดต่อ',
  `line_id` varchar(50) DEFAULT NULL COMMENT 'LINE ID ของลูกค้า',
  `line_group_token` varchar(255) DEFAULT NULL COMMENT 'Token ไลน์กลุ่มสำหรับแจ้งเตือนงาน',
  `doc_folder_url` varchar(255) DEFAULT NULL COMMENT 'ลิงก์โฟลเดอร์เก็บเอกสาร (เช่น Google Drive, OneDrive)',
  `closing_status` varchar(1) NOT NULL DEFAULT '0' COMMENT 'สถานะการปิดงบการเงิน\r\n0 = ปิดงบรายเดือน\r\n1 = ปิดงบรายปี\r\n2 = ไม่ปิดงบ',
  `fiscal_closing_date` date DEFAULT NULL COMMENT 'วันสิ้นสุดรอบปีบัญชีของลูกค้า',
  `is_vat` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'จดทะเบียนภาษีมูลค่าเพิ่มหรือไม่ (1 = จด, 0 = ไม่จด)',
  `is_employees` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'มีพนักงาน/การจ่ายเงินเดือนหรือไม่ (1 = มี, 0 = ไม่มี)',
  `is_social_security` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'ขึ้นทะเบียนประกันสังคมหรือไม่ (1 = มี, 0 = ไม่มี)',
  `accounts_amount` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'ยอดค่าบริการทำบัญชี (บาท)',
  `created_at` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'วันเวลาที่สร้างข้อมูล',
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp() COMMENT 'วันเวลาที่มีการอัปเดตข้อมูลล่าสุด',
  `delete_at` datetime DEFAULT NULL COMMENT 'วันที่มีการลบรายการลูกค้า',
  `cpd_name` text DEFAULT NULL COMMENT 'ผู้ทำบัญชี',
  `cpa_name` text DEFAULT NULL COMMENT 'ผู้สอบบัญชี'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ตารางเก็บข้อมูลโปรไฟล์และข้อมูลการทำบัญชีของลูกค้า';

-- --------------------------------------------------------

--
-- Table structure for table `tbl_customer_accounts`
--

CREATE TABLE `tbl_customer_accounts` (
  `account_id` int(11) NOT NULL,
  `fiscal_year_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `account_name` text DEFAULT NULL COMMENT 'ชื่อ',
  `account_user_name` varchar(50) DEFAULT NULL COMMENT 'user_name',
  `account_password` varchar(50) DEFAULT NULL COMMENT 'รหัสผ่าน',
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_customer_tasks`
--

CREATE TABLE `tbl_customer_tasks` (
  `customer_tasks_id` int(11) NOT NULL,
  `fiscal_year_id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `period_id` int(11) DEFAULT NULL,
  `status` varchar(1) DEFAULT '0',
  `amount` decimal(10,2) DEFAULT 0.00,
  `delete_at` datetime DEFAULT NULL COMMENT 'เดือนที่มีการลบออก'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_customer_work_periods`
--

CREATE TABLE `tbl_customer_work_periods` (
  `period_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `fiscal_year_id` int(11) NOT NULL,
  `period_month` varchar(2) NOT NULL COMMENT 'เดือน 1-12',
  `doc_status` varchar(1) NOT NULL DEFAULT '0' COMMENT '0 = ยังไม่ได้รับเอกสาร\r\n1 = ได้รับเอกสาร',
  `tax_status` varchar(1) NOT NULL DEFAULT '0' COMMENT '0 = ยังไม่ได้ยื่นภาษี\r\n1 = ยื่นภาษีแล้ว',
  `tax_status2` varchar(1) NOT NULL DEFAULT '0' COMMENT 'ยื่นภาษีรอบที่ 2',
  `payment_status` varchar(1) NOT NULL DEFAULT '0' COMMENT '0 = ยังไม่ได้เก็บเงิน\r\n1 = เก็บเงินแล้ว',
  `review1_status` varchar(1) NOT NULL DEFAULT '0' COMMENT '0 = ยังไม่ได้รีวิว\r\n1 = รีวิวแล้ว',
  `review1_user_id` int(11) DEFAULT NULL,
  `review2_status` varchar(1) NOT NULL DEFAULT '0' COMMENT '0 = ยังไม่ได้รีวิว\r\n1 = รีวิวแล้ว',
  `review2_user_id` int(11) DEFAULT NULL,
  `review3_status` varchar(1) NOT NULL DEFAULT '0' COMMENT '0 = ยังไม่ได้รีวิว\r\n1 = รีวิวแล้ว',
  `review3_user_id` int(11) DEFAULT NULL,
  `complate_status` varchar(1) NOT NULL DEFAULT '0' COMMENT 'ทำงานเสร็จทุกขั้นตอน',
  `tax_create_at` datetime DEFAULT NULL COMMENT 'วันที่ยื่นภาษี',
  `tax2_create_at` date DEFAULT NULL COMMENT 'วันที่ยื่นภาษีรอบที่2',
  `completed_date` date DEFAULT NULL COMMENT 'วันที่ทำเสร็จในการยื่นภาษีรอบที่ 1',
  `completed_date2` date DEFAULT NULL COMMENT 'วันที่ทำเสร็จในการยื่นภาษีรอบที่ 2',
  `doc_date` date DEFAULT NULL COMMENT 'วันที่ได้รับเอกสาร',
  `tax_date` date DEFAULT NULL COMMENT 'วันที่ยื่นภาษี',
  `created_at` datetime NOT NULL,
  `delete_at` datetime DEFAULT NULL COMMENT 'เดือนที่มีการลบ'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_fiscal_years`
--

CREATE TABLE `tbl_fiscal_years` (
  `fiscal_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `fiscal_years` varchar(50) NOT NULL,
  `active_status` varchar(1) NOT NULL DEFAULT '1' COMMENT '0 = ไม่ใช้งาน\r\n1 = ใช้งาน',
  `create_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_fiscal_year_customers`
--

CREATE TABLE `tbl_fiscal_year_customers` (
  `fiscal_year_id` int(11) NOT NULL,
  `fiscal_id` int(11) NOT NULL COMMENT 'ปีงบประมาณ',
  `customer_id` int(11) NOT NULL COMMENT 'ลูกค้า',
  `service_start_date` varchar(2) DEFAULT NULL COMMENT 'เดือนที่เริ่มให้บิรการ\r\n1-12',
  `service_start_end` varchar(2) DEFAULT NULL COMMENT 'เดือนที่สิ้นสุดการให้บริการ\r\n1-12',
  `user_id` int(11) DEFAULT NULL COMMENT 'พนักงานที่ดูแล',
  `team_id` int(11) DEFAULT NULL COMMENT 'ทีมของพนักงาน',
  `created_at` datetime NOT NULL,
  `accounts_amount` int(11) NOT NULL DEFAULT 0 COMMENT 'ค่าทำบัญชี',
  `closing_amount` int(11) DEFAULT 0 COMMENT 'ค่าปิดบัญชี',
  `auditing_amount` int(11) NOT NULL DEFAULT 0 COMMENT 'ค่าสอบบัญชี'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_fiscal_year_user`
--

CREATE TABLE `tbl_fiscal_year_user` (
  `fiscal_employee_id` int(11) NOT NULL,
  `fiscal_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_login_token`
--

CREATE TABLE `tbl_login_token` (
  `token_code` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'FK เชื่อม tbl_user',
  `ip_address` varchar(20) NOT NULL COMMENT 'IP ผู้ใช้งาน',
  `user_agent` text NOT NULL COMMENT 'ข้อมูลเครื่องของผู้ใช้',
  `expire_datetime` datetime NOT NULL COMMENT 'เวลาหมดอายุ ของ token',
  `end_datetime` datetime DEFAULT NULL COMMENT 'เวลา Logout (NULL = ยัง active)',
  `create_datetime` datetime NOT NULL COMMENT 'เวลาที่ถูกสร้าง',
  `last_active_at` datetime DEFAULT NULL COMMENT 'เวลาที่ใช้งานล่าสุด'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_manual_content`
--

CREATE TABLE `tbl_manual_content` (
  `content_id` int(11) NOT NULL,
  `title_name` text DEFAULT NULL COMMENT 'ชื่อหัวข้อรอง',
  `description` text DEFAULT NULL COMMENT 'รายละเอียด',
  `content_image` varchar(255) DEFAULT NULL COMMENT 'รูปภาพประกอบ',
  `topic_id` int(11) NOT NULL,
  `create_at` datetime NOT NULL COMMENT 'วันที่สร้าง'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_manual_topics`
--

CREATE TABLE `tbl_manual_topics` (
  `topic_id` int(11) NOT NULL,
  `topics_name` text NOT NULL COMMENT 'ชื่อหัวข้อหลัก',
  `create_at` datetime NOT NULL COMMENT 'วันที่สร้าง',
  `list_order` int(11) NOT NULL COMMENT 'ลำดับการแสดงผล',
  `active_status` varchar(1) NOT NULL DEFAULT '1' COMMENT '0 = ปิดการใช้งาน\r\n1 = ใช้งานอยู่'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_notifications`
--

CREATE TABLE `tbl_notifications` (
  `notif_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `task_type` varchar(50) NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `url_link` varchar(255) DEFAULT NULL COMMENT 'ลิงค์ที่จะไปหน้านั้น'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_notification_settings`
--

CREATE TABLE `tbl_notification_settings` (
  `user_id` int(11) NOT NULL,
  `notify_days_advance` int(11) NOT NULL DEFAULT 5,
  `notify_email` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_option_tax`
--

CREATE TABLE `tbl_option_tax` (
  `option_id` int(11) NOT NULL,
  `option_name` text NOT NULL,
  `list_order` int(11) NOT NULL,
  `create_user_id` int(11) NOT NULL,
  `created_at` date NOT NULL,
  `delete_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_post_it`
--

CREATE TABLE `tbl_post_it` (
  `post_id` int(11) NOT NULL,
  `fiscal_year_id` int(11) NOT NULL,
  `title` text NOT NULL COMMENT 'หัวข้องาน',
  `user_id` int(11) DEFAULT NULL,
  `due_date` date DEFAULT NULL COMMENT 'กำหนดส่ง',
  `status` varchar(1) NOT NULL COMMENT '0 = รอดำเนินการ / 1 = ปิดงานแล้ว',
  `content` text NOT NULL COMMENT 'เนื้อหางาน',
  `color_code` varchar(100) NOT NULL COMMENT 'รหัสสี',
  `created_user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_push_subscriptions`
--

CREATE TABLE `tbl_push_subscriptions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `endpoint` text NOT NULL,
  `p256dh` varchar(255) NOT NULL,
  `auth` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_registration`
--

CREATE TABLE `tbl_registration` (
  `registration` int(11) NOT NULL,
  `fiscal_id` int(11) NOT NULL,
  `registration_type_id` int(11) NOT NULL,
  `customer_name` text NOT NULL,
  `customer_phone` varchar(10) NOT NULL,
  `contact_person` text NOT NULL COMMENT 'ผู้ต่อต่อ',
  `registration_name` text NOT NULL,
  `status` varchar(1) NOT NULL COMMENT '0 = รับงานลงทะเบียน\r\n1 = กำลังทำ\r\n2 = รอตรวจสอบ\r\n3 = ตรวจสอบแล้ว\r\n4 = กำลังไปยื่น\r\n5 = งานเสร็จเรียบร้อยแล้ว\r\n6 = เก็บเงินเรียบร้อยแล้ว',
  `description` text NOT NULL COMMENT 'รายละเอียดงาน',
  `service_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'ค่าบริการ',
  `urgency_level` varchar(1) DEFAULT NULL COMMENT 'ระดับความเร่งด่วน\r\n1 = ปกติ\r\n2 = เร่งด่วน\r\n3 = เร่งด่วนมาก',
  `accep_date` date DEFAULT NULL COMMENT 'วันที่รังาน',
  `due_date` date DEFAULT NULL COMMENT 'กำหนดส่ง',
  `assignee_user_id` int(11) DEFAULT NULL COMMENT 'พนักงานที่รับผิดชอบ',
  `review_user_id` int(11) DEFAULT NULL COMMENT 'ผู้ตรวจสอบ',
  `registration_no` text NOT NULL,
  `delete_at` datetime DEFAULT NULL,
  `delete_user_id` int(11) DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `close_user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_registration_task_setting`
--

CREATE TABLE `tbl_registration_task_setting` (
  `setting_id` int(11) NOT NULL,
  `fiscal_id` int(11) NOT NULL,
  `notify_day` int(11) NOT NULL DEFAULT 7 COMMENT 'ครบกำหนดภายใน',
  `update_at` datetime DEFAULT NULL COMMENT 'update ตอนไหน',
  `update_user_id` int(11) DEFAULT NULL COMMENT 'ใครเป็นคนอัพเดท'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_registration_type`
--

CREATE TABLE `tbl_registration_type` (
  `registration_type_id` int(11) NOT NULL,
  `registration_type_name` text NOT NULL,
  `active_status` varchar(1) NOT NULL DEFAULT '1' COMMENT 'ใช้งาน / ไม่ใช้งาน',
  `created_at` datetime NOT NULL,
  `create_user_id` int(11) NOT NULL,
  `fiscal_id` int(11) NOT NULL,
  `delete_at` datetime DEFAULT NULL,
  `delete_user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_tasks`
--

CREATE TABLE `tbl_tasks` (
  `tasks_id` int(11) NOT NULL,
  `fiscal_id` int(11) NOT NULL,
  `tasks_name` text NOT NULL,
  `is_notify_amount` varchar(1) NOT NULL DEFAULT '0' COMMENT 'ต้องระบุจำนวนเงินสำหรับแจ้งยอดผ่าน LINE ลูกค้า',
  `created_at` datetime NOT NULL,
  `list_order` int(11) NOT NULL COMMENT 'ลำดับการแสดงผล',
  `delete_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_team`
--

CREATE TABLE `tbl_team` (
  `team_id` int(11) NOT NULL,
  `team_name` text NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_urgency_level`
--

CREATE TABLE `tbl_urgency_level` (
  `urgency_level_id` int(11) NOT NULL,
  `fiscal_id` int(11) NOT NULL COMMENT 'เพื่อจับ ลูกค้า และ ปี',
  `urgency_level` varchar(1) NOT NULL,
  `label` varchar(50) NOT NULL COMMENT 'ข้อความ',
  `color` varchar(20) NOT NULL COMMENT 'สี'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user`
--

CREATE TABLE `tbl_user` (
  `user_id` int(11) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `user_email` varchar(100) DEFAULT NULL,
  `user_password` varchar(255) NOT NULL,
  `user_firstname` varchar(100) NOT NULL,
  `user_lastname` varchar(100) NOT NULL,
  `user_status` varchar(1) NOT NULL DEFAULT '1' COMMENT '1=ปกติ, 0=ระงับ',
  `create_at` datetime NOT NULL,
  `delete_at` datetime DEFAULT NULL,
  `is_super_admin` varchar(1) NOT NULL COMMENT '0 = ไม่ใช่ supera dmin admin 1 = super admin',
  `position` text DEFAULT NULL COMMENT 'ตำแหน่ง',
  `team_id` int(11) DEFAULT NULL COMMENT 'รหัสทีม'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user_companies`
--

CREATE TABLE `tbl_user_companies` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_assign_task`
--
ALTER TABLE `tbl_assign_task`
  ADD PRIMARY KEY (`assign_id`);

--
-- Indexes for table `tbl_cd_activity`
--
ALTER TABLE `tbl_cd_activity`
  ADD PRIMARY KEY (`activity_id`),
  ADD KEY `idx_cd_activity_feed` (`customer_id`,`create_datetime`),
  ADD KEY `idx_cd_activity_node` (`node_id`);

--
-- Indexes for table `tbl_cd_link`
--
ALTER TABLE `tbl_cd_link`
  ADD PRIMARY KEY (`link_id`),
  ADD UNIQUE KEY `uq_cd_link_token` (`token`),
  ADD KEY `idx_cd_link_customer` (`customer_id`,`revoked`),
  ADD KEY `idx_cd_link_root` (`root_node_id`);

--
-- Indexes for table `tbl_cd_link_session`
--
ALTER TABLE `tbl_cd_link_session`
  ADD PRIMARY KEY (`session_id`),
  ADD UNIQUE KEY `uq_cd_session_token` (`session_token`),
  ADD KEY `idx_cd_session_link` (`link_id`,`expires_datetime`);

--
-- Indexes for table `tbl_cd_node`
--
ALTER TABLE `tbl_cd_node`
  ADD PRIMARY KEY (`node_id`),
  ADD KEY `idx_cd_node_tree` (`customer_id`,`parent_id`,`list_order`),
  ADD KEY `idx_cd_node_live` (`customer_id`,`delete_datetime`),
  ADD KEY `idx_cd_node_parent` (`parent_id`);

--
-- Indexes for table `tbl_cd_version`
--
ALTER TABLE `tbl_cd_version`
  ADD PRIMARY KEY (`version_id`),
  ADD UNIQUE KEY `uq_cd_version` (`node_id`,`version`);

--
-- Indexes for table `tbl_closing_financial`
--
ALTER TABLE `tbl_closing_financial`
  ADD PRIMARY KEY (`closing_id`),
  ADD KEY `fiscal_year_id` (`fiscal_year_id`);

--
-- Indexes for table `tbl_comment_tasks`
--
ALTER TABLE `tbl_comment_tasks`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `comment_user_id` (`comment_user_id`),
  ADD KEY `customer_tasks_id` (`customer_tasks_id`);

--
-- Indexes for table `tbl_companies`
--
ALTER TABLE `tbl_companies`
  ADD PRIMARY KEY (`company_id`);

--
-- Indexes for table `tbl_customers`
--
ALTER TABLE `tbl_customers`
  ADD PRIMARY KEY (`customer_id`);

--
-- Indexes for table `tbl_customer_accounts`
--
ALTER TABLE `tbl_customer_accounts`
  ADD PRIMARY KEY (`account_id`);

--
-- Indexes for table `tbl_customer_tasks`
--
ALTER TABLE `tbl_customer_tasks`
  ADD PRIMARY KEY (`customer_tasks_id`);

--
-- Indexes for table `tbl_customer_work_periods`
--
ALTER TABLE `tbl_customer_work_periods`
  ADD PRIMARY KEY (`period_id`);

--
-- Indexes for table `tbl_fiscal_years`
--
ALTER TABLE `tbl_fiscal_years`
  ADD PRIMARY KEY (`fiscal_id`);

--
-- Indexes for table `tbl_fiscal_year_customers`
--
ALTER TABLE `tbl_fiscal_year_customers`
  ADD PRIMARY KEY (`fiscal_year_id`);

--
-- Indexes for table `tbl_fiscal_year_user`
--
ALTER TABLE `tbl_fiscal_year_user`
  ADD PRIMARY KEY (`fiscal_employee_id`);

--
-- Indexes for table `tbl_login_token`
--
ALTER TABLE `tbl_login_token`
  ADD PRIMARY KEY (`token_code`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tbl_manual_content`
--
ALTER TABLE `tbl_manual_content`
  ADD PRIMARY KEY (`content_id`);

--
-- Indexes for table `tbl_manual_topics`
--
ALTER TABLE `tbl_manual_topics`
  ADD PRIMARY KEY (`topic_id`);

--
-- Indexes for table `tbl_notifications`
--
ALTER TABLE `tbl_notifications`
  ADD PRIMARY KEY (`notif_id`),
  ADD KEY `user_id_idx` (`user_id`),
  ADD KEY `is_read_idx` (`is_read`);

--
-- Indexes for table `tbl_notification_settings`
--
ALTER TABLE `tbl_notification_settings`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `tbl_option_tax`
--
ALTER TABLE `tbl_option_tax`
  ADD PRIMARY KEY (`option_id`);

--
-- Indexes for table `tbl_post_it`
--
ALTER TABLE `tbl_post_it`
  ADD PRIMARY KEY (`post_id`);

--
-- Indexes for table `tbl_push_subscriptions`
--
ALTER TABLE `tbl_push_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `tbl_registration`
--
ALTER TABLE `tbl_registration`
  ADD PRIMARY KEY (`registration`);

--
-- Indexes for table `tbl_registration_task_setting`
--
ALTER TABLE `tbl_registration_task_setting`
  ADD PRIMARY KEY (`setting_id`),
  ADD KEY `fiscal_id` (`fiscal_id`);

--
-- Indexes for table `tbl_registration_type`
--
ALTER TABLE `tbl_registration_type`
  ADD PRIMARY KEY (`registration_type_id`);

--
-- Indexes for table `tbl_tasks`
--
ALTER TABLE `tbl_tasks`
  ADD PRIMARY KEY (`tasks_id`);

--
-- Indexes for table `tbl_team`
--
ALTER TABLE `tbl_team`
  ADD PRIMARY KEY (`team_id`);

--
-- Indexes for table `tbl_urgency_level`
--
ALTER TABLE `tbl_urgency_level`
  ADD PRIMARY KEY (`urgency_level_id`),
  ADD KEY `fiscal_id` (`fiscal_id`);

--
-- Indexes for table `tbl_user`
--
ALTER TABLE `tbl_user`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `tbl_user_companies`
--
ALTER TABLE `tbl_user_companies`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_assign_task`
--
ALTER TABLE `tbl_assign_task`
  MODIFY `assign_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_cd_activity`
--
ALTER TABLE `tbl_cd_activity`
  MODIFY `activity_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_cd_link`
--
ALTER TABLE `tbl_cd_link`
  MODIFY `link_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_cd_link_session`
--
ALTER TABLE `tbl_cd_link_session`
  MODIFY `session_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_cd_node`
--
ALTER TABLE `tbl_cd_node`
  MODIFY `node_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_cd_version`
--
ALTER TABLE `tbl_cd_version`
  MODIFY `version_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_closing_financial`
--
ALTER TABLE `tbl_closing_financial`
  MODIFY `closing_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_comment_tasks`
--
ALTER TABLE `tbl_comment_tasks`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_companies`
--
ALTER TABLE `tbl_companies`
  MODIFY `company_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_customers`
--
ALTER TABLE `tbl_customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'รหัสลูกค้า (Primary Key)';

--
-- AUTO_INCREMENT for table `tbl_customer_accounts`
--
ALTER TABLE `tbl_customer_accounts`
  MODIFY `account_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_customer_tasks`
--
ALTER TABLE `tbl_customer_tasks`
  MODIFY `customer_tasks_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_customer_work_periods`
--
ALTER TABLE `tbl_customer_work_periods`
  MODIFY `period_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_fiscal_years`
--
ALTER TABLE `tbl_fiscal_years`
  MODIFY `fiscal_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_fiscal_year_customers`
--
ALTER TABLE `tbl_fiscal_year_customers`
  MODIFY `fiscal_year_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_fiscal_year_user`
--
ALTER TABLE `tbl_fiscal_year_user`
  MODIFY `fiscal_employee_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_login_token`
--
ALTER TABLE `tbl_login_token`
  MODIFY `token_code` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_manual_content`
--
ALTER TABLE `tbl_manual_content`
  MODIFY `content_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_manual_topics`
--
ALTER TABLE `tbl_manual_topics`
  MODIFY `topic_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_notifications`
--
ALTER TABLE `tbl_notifications`
  MODIFY `notif_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_option_tax`
--
ALTER TABLE `tbl_option_tax`
  MODIFY `option_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_post_it`
--
ALTER TABLE `tbl_post_it`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_push_subscriptions`
--
ALTER TABLE `tbl_push_subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_registration`
--
ALTER TABLE `tbl_registration`
  MODIFY `registration` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_registration_task_setting`
--
ALTER TABLE `tbl_registration_task_setting`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_registration_type`
--
ALTER TABLE `tbl_registration_type`
  MODIFY `registration_type_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_tasks`
--
ALTER TABLE `tbl_tasks`
  MODIFY `tasks_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_team`
--
ALTER TABLE `tbl_team`
  MODIFY `team_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_urgency_level`
--
ALTER TABLE `tbl_urgency_level`
  MODIFY `urgency_level_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_user`
--
ALTER TABLE `tbl_user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_user_companies`
--
ALTER TABLE `tbl_user_companies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_cd_activity`
--
ALTER TABLE `tbl_cd_activity`
  ADD CONSTRAINT `fk_cd_activity_customer` FOREIGN KEY (`customer_id`) REFERENCES `tbl_customers` (`customer_id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_cd_link`
--
ALTER TABLE `tbl_cd_link`
  ADD CONSTRAINT `fk_cd_link_customer` FOREIGN KEY (`customer_id`) REFERENCES `tbl_customers` (`customer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cd_link_root` FOREIGN KEY (`root_node_id`) REFERENCES `tbl_cd_node` (`node_id`);

--
-- Constraints for table `tbl_cd_link_session`
--
ALTER TABLE `tbl_cd_link_session`
  ADD CONSTRAINT `fk_cd_session_link` FOREIGN KEY (`link_id`) REFERENCES `tbl_cd_link` (`link_id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_cd_node`
--
ALTER TABLE `tbl_cd_node`
  ADD CONSTRAINT `fk_cd_node_customer` FOREIGN KEY (`customer_id`) REFERENCES `tbl_customers` (`customer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cd_node_parent` FOREIGN KEY (`parent_id`) REFERENCES `tbl_cd_node` (`node_id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_cd_version`
--
ALTER TABLE `tbl_cd_version`
  ADD CONSTRAINT `fk_cd_version_node` FOREIGN KEY (`node_id`) REFERENCES `tbl_cd_node` (`node_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
