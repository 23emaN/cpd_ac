-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 04, 2026 at 09:18 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cpd_ac`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_comment_tasks`
--

CREATE TABLE `tbl_comment_tasks` (
  `comment_id` int(11) NOT NULL,
  `customer_tasks_id` int(11) NOT NULL,
  `comment_user_id` int(11) NOT NULL,
  `comment_detail` text NOT NULL,
  `create_at` datetime NOT NULL
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

--
-- Dumping data for table `tbl_companies`
--

INSERT INTO `tbl_companies` (`company_id`, `company_name`, `user_id`, `active_status`, `created_at`) VALUES
(1, 'Accounting', 1, '0', '2026-09-01 13:46:48'),
(3, 'ทดสอบ', 1, '0', '2026-09-01 14:38:22');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_customers`
--

CREATE TABLE `tbl_customers` (
  `customer_id` int(11) NOT NULL COMMENT 'รหัสลูกค้า (Primary Key)',
  `customer_name` varchar(255) NOT NULL COMMENT 'ชื่อลูกค้า หรือชื่อบริษัท',
  `active_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'สถานะการใช้งาน (1 = ใช้งาน, 0 = ระงับการใช้งาน)',
  `customer_phone` varchar(20) DEFAULT NULL COMMENT 'เบอร์โทรศัพท์ติดต่อ',
  `customer_email` varchar(100) DEFAULT NULL COMMENT 'อีเมลติดต่อ',
  `line_id` varchar(50) DEFAULT NULL COMMENT 'LINE ID ของลูกค้า',
  `line_group_token` varchar(255) DEFAULT NULL COMMENT 'Token ไลน์กลุ่มสำหรับแจ้งเตือนงาน',
  `doc_folder_url` varchar(255) DEFAULT NULL COMMENT 'ลิงก์โฟลเดอร์เก็บเอกสาร (เช่น Google Drive, OneDrive)',
  `closing_status` varchar(1) NOT NULL DEFAULT '0' COMMENT 'สถานะการปิดงบการเงิน',
  `fiscal_closing_date` date DEFAULT NULL COMMENT 'วันสิ้นสุดรอบปีบัญชีของลูกค้า',
  `is_vat` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'จดทะเบียนภาษีมูลค่าเพิ่มหรือไม่ (1 = จด, 0 = ไม่จด)',
  `is_employees` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'มีพนักงาน/การจ่ายเงินเดือนหรือไม่ (1 = มี, 0 = ไม่มี)',
  `is_social_security` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'ขึ้นทะเบียนประกันสังคมหรือไม่ (1 = มี, 0 = ไม่มี)',
  `accounts_amount` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'ยอดค่าบริการทำบัญชี (บาท)',
  `rn_user` varchar(100) DEFAULT NULL COMMENT 'Username กรมสรรพากร',
  `rn_password` varchar(255) DEFAULT NULL COMMENT 'Password กรมสรรพากร',
  `dbd_user` varchar(100) DEFAULT NULL COMMENT 'Username กรมพัฒน์',
  `dbd_password` varchar(255) DEFAULT NULL COMMENT 'Password ระบบ กรมพัฒน์',
  `sso_user` varchar(100) DEFAULT NULL COMMENT 'Username สำนักงานประกันสังคม',
  `sso_password` varchar(255) DEFAULT NULL COMMENT 'Password สำนักงานประกันสังคม ',
  `created_at` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'วันเวลาที่สร้างข้อมูล',
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp() COMMENT 'วันเวลาที่มีการอัปเดตข้อมูลล่าสุด'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ตารางเก็บข้อมูลโปรไฟล์และข้อมูลการทำบัญชีของลูกค้า';

-- --------------------------------------------------------

--
-- Table structure for table `tbl_customer_tasks`
--

CREATE TABLE `tbl_customer_tasks` (
  `customer_tasks_id` int(11) NOT NULL,
  `fiscal_year_id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `period_id` int(11) NOT NULL,
  `status` varchar(1) DEFAULT NULL COMMENT '1 = รอดำเนินการ\r\n2 = เสร็จแล้ว\r\n3 = ไม่เกี่ยวข่อง',
  `amount` decimal(10,2) DEFAULT 0.00 COMMENT 'ยอดเงิน',
  `created_at` datetime NOT NULL,
  `update_at` datetime DEFAULT NULL
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
  `payment_status` varchar(1) NOT NULL DEFAULT '0' COMMENT '0 = ยังไม่ได้เก็บเงิน\r\n1 = เก็บเงินแล้ว',
  `created_at` datetime NOT NULL,
  `review1_status` varchar(1) NOT NULL DEFAULT '0' COMMENT '0 = ยังไม่ได้รีวิว\r\n1 = รีวิวแล้ว',
  `review2_status` varchar(1) NOT NULL DEFAULT '0' COMMENT '0 = ยังไม่ได้รีวิว\r\n1 = รีวิวแล้ว',
  `review3_status` varchar(1) NOT NULL DEFAULT '0' COMMENT '0 = ยังไม่ได้รีวิว\r\n1 = รีวิวแล้ว'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_fiscal_years`
--

CREATE TABLE `tbl_fiscal_years` (
  `fiscal_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `fiscal_years` varchar(50) NOT NULL,
  `active_status` varchar(1) NOT NULL COMMENT '0 = ไม่ใช้งาน\r\n1 = ใช้งาน',
  `create_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_fiscal_years`
--

INSERT INTO `tbl_fiscal_years` (`fiscal_id`, `company_id`, `fiscal_years`, `active_status`, `create_at`) VALUES
(1, 1, '2670', '', '2026-09-01 15:40:25'),
(2, 3, '2569', '', '2026-09-03 09:50:03'),
(3, 1, '2571', '', '2026-09-03 13:41:06');

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
  `accounts_amount` int(11) NOT NULL DEFAULT 0
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

--
-- Dumping data for table `tbl_fiscal_year_user`
--

INSERT INTO `tbl_fiscal_year_user` (`fiscal_employee_id`, `fiscal_id`, `user_id`, `created_at`) VALUES
(1, 3, 2, '2026-09-03 15:50:41'),
(2, 3, 3, '2026-09-03 15:54:13');

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

--
-- Dumping data for table `tbl_login_token`
--

INSERT INTO `tbl_login_token` (`token_code`, `user_id`, `ip_address`, `user_agent`, `expire_datetime`, `end_datetime`, `create_datetime`, `last_active_at`) VALUES
(3, 1, '::1', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36', '2026-09-02 09:57:43', NULL, '2026-09-01 14:57:43', NULL),
(4, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-02 10:02:15', NULL, '2026-09-01 15:02:15', NULL),
(5, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-02 11:29:01', NULL, '2026-09-01 16:29:01', NULL),
(6, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-02 11:30:57', NULL, '2026-09-01 16:30:57', NULL),
(7, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-03 03:29:48', NULL, '2026-09-02 08:29:48', NULL),
(8, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-03 17:20:30', NULL, '2026-09-02 22:20:30', NULL),
(9, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-03 18:52:05', NULL, '2026-09-02 23:52:05', NULL),
(10, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-04 04:12:56', NULL, '2026-09-03 09:12:56', NULL),
(11, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-04 06:34:56', NULL, '2026-09-03 11:34:56', NULL),
(12, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-04 09:20:20', NULL, '2026-09-03 14:20:20', NULL),
(13, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-04 09:32:18', NULL, '2026-09-03 14:32:18', NULL),
(14, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-04 09:32:48', NULL, '2026-09-03 14:32:48', NULL),
(15, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-05 03:12:52', NULL, '2026-09-04 08:12:52', NULL),
(16, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-05 05:20:42', NULL, '2026-09-04 10:20:42', NULL),
(17, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-05 06:13:05', NULL, '2026-09-04 11:13:05', NULL);

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

--
-- Dumping data for table `tbl_post_it`
--

INSERT INTO `tbl_post_it` (`post_id`, `fiscal_year_id`, `title`, `user_id`, `due_date`, `status`, `content`, `color_code`, `created_user_id`, `created_at`) VALUES
(1, 3, 'เทสๆๆๆ', 3, '2026-09-30', '0', 'ทดสอบบบ', 'orange', 1, '2026-09-04 08:53:45');

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
  `registration_no` text NOT NULL
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

--
-- Dumping data for table `tbl_tasks`
--

INSERT INTO `tbl_tasks` (`tasks_id`, `fiscal_id`, `tasks_name`, `is_notify_amount`, `created_at`, `list_order`, `delete_at`) VALUES
(1, 3, 'ภ.ง.ด.1', '1', '2026-09-03 14:14:39', 1, NULL),
(2, 3, 'ภ.ง.ด.3', '1', '2026-09-03 14:15:10', 2, NULL),
(3, 3, 'ภ.ง.ด.53', '1', '2026-09-03 14:15:45', 3, NULL),
(4, 3, 'ภ.ง.ด.54', '1', '2026-09-03 14:17:00', 4, NULL),
(5, 3, 'ภ.พ.30', '1', '2026-09-03 14:52:05', 5, NULL),
(6, 3, 'ภ.พ.36', '1', '2026-09-03 14:55:22', 6, NULL),
(7, 3, 'ประกันสังคม', '0', '2026-09-03 15:04:58', 7, NULL),
(8, 3, 'ก.ย.ศ.', '0', '2026-09-03 15:09:26', 8, NULL),
(9, 3, 'BBL', '0', '2026-09-03 15:19:28', 9, NULL),
(10, 3, 'KBANK', '0', '2026-09-03 15:45:22', 10, NULL),
(11, 3, 'UOB', '0', '2026-09-03 15:45:29', 11, NULL),
(12, 3, 'TTB', '0', '2026-09-03 15:45:35', 12, NULL),
(13, 3, 'SCB', '0', '2026-09-03 15:45:41', 13, NULL),
(14, 3, 'กระทบ Bank', '0', '2026-09-03 15:45:47', 14, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_team`
--

CREATE TABLE `tbl_team` (
  `team_id` int(11) NOT NULL,
  `team_name` text NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_team`
--

INSERT INTO `tbl_team` (`team_id`, `team_name`, `created_at`) VALUES
(2, 'พัฒนา', '2026-09-03 15:54:13'),
(3, 'บัญชี', '2026-09-03 16:11:20');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user`
--

CREATE TABLE `tbl_user` (
  `user_id` int(11) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `user_password` varchar(255) NOT NULL,
  `user_firstname` varchar(100) NOT NULL,
  `user_lastname` varchar(100) NOT NULL,
  `user_status` varchar(1) NOT NULL DEFAULT '1' COMMENT '1=ปกติ, 0=ระงับ',
  `create_at` datetime NOT NULL,
  `is_super_admin` varchar(1) NOT NULL COMMENT '0 = ไม่ใช่ supera dmin admin 1 = super admin',
  `position` text DEFAULT NULL COMMENT 'ตำแหน่ง',
  `team_id` int(11) DEFAULT NULL COMMENT 'รหัสทีม'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_user`
--

INSERT INTO `tbl_user` (`user_id`, `user_name`, `user_password`, `user_firstname`, `user_lastname`, `user_status`, `create_at`, `is_super_admin`, `position`, `team_id`) VALUES
(1, 'cpdth12345@am-amaudit.com', '$2y$10$sxnYoO0UfANJ7wTwbqxkR.O4jjSUGwaxlpNjL7CrhXSJFKisR4sba', 'admin', 'cpdth', '1', '2026-09-01 04:23:56', '1', NULL, NULL),
(3, 'name_test', '$2y$10$Oy6lda8dWkpoHWku5.zuxuNrWoI3GwtRuJ8MSgK11ZVJ8DdgxcnA2', 'ธีรพัฒน์', 'คุชิตา', '1', '2026-09-03 15:54:13', '0', 'ผู้ทดสอบ', 3);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user_companies`
--

CREATE TABLE `tbl_user_companies` (
  `id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_user_companies`
--

INSERT INTO `tbl_user_companies` (`id`, `user_id`, `company_id`) VALUES
(NULL, 2, 1),
(NULL, 3, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_comment_tasks`
--
ALTER TABLE `tbl_comment_tasks`
  ADD PRIMARY KEY (`comment_id`);

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
-- Indexes for table `tbl_post_it`
--
ALTER TABLE `tbl_post_it`
  ADD PRIMARY KEY (`post_id`);

--
-- Indexes for table `tbl_registration`
--
ALTER TABLE `tbl_registration`
  ADD PRIMARY KEY (`registration`);

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
-- Indexes for table `tbl_user`
--
ALTER TABLE `tbl_user`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_comment_tasks`
--
ALTER TABLE `tbl_comment_tasks`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_companies`
--
ALTER TABLE `tbl_companies`
  MODIFY `company_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_customers`
--
ALTER TABLE `tbl_customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'รหัสลูกค้า (Primary Key)';

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
  MODIFY `fiscal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_fiscal_year_customers`
--
ALTER TABLE `tbl_fiscal_year_customers`
  MODIFY `fiscal_year_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_fiscal_year_user`
--
ALTER TABLE `tbl_fiscal_year_user`
  MODIFY `fiscal_employee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_login_token`
--
ALTER TABLE `tbl_login_token`
  MODIFY `token_code` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `tbl_post_it`
--
ALTER TABLE `tbl_post_it`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_registration`
--
ALTER TABLE `tbl_registration`
  MODIFY `registration` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_registration_type`
--
ALTER TABLE `tbl_registration_type`
  MODIFY `registration_type_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_tasks`
--
ALTER TABLE `tbl_tasks`
  MODIFY `tasks_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `tbl_team`
--
ALTER TABLE `tbl_team`
  MODIFY `team_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_user`
--
ALTER TABLE `tbl_user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
