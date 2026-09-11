-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 11, 2026 at 03:21 PM
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

--
-- Dumping data for table `tbl_cd_activity`
--

INSERT INTO `tbl_cd_activity` (`activity_id`, `customer_id`, `node_id`, `link_id`, `actor_type`, `actor_user_id`, `actor_label`, `action`, `detail`, `ip`, `create_datetime`) VALUES
(1, 1, NULL, 1, 'staff', 1, NULL, 'link_create', 'ทดสอบการใส่เอกสารผ่านลิงค์', 0x1b82198d, '2026-09-11 08:53:49'),
(2, 1, NULL, 1, 'guest', NULL, 'ทดสอบการใส่เอกสารผ่านลิงค์', 'link_open', 'เปิดลิงก์ครั้งแรก', 0x1b82198d, '2026-09-11 09:19:26');

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

--
-- Dumping data for table `tbl_cd_link`
--

INSERT INTO `tbl_cd_link` (`link_id`, `customer_id`, `token`, `title`, `message`, `mode`, `root_node_id`, `allow_upload`, `allow_download`, `password_hash`, `expires_datetime`, `max_uploads`, `used_uploads`, `revoked`, `fail_count`, `locked_until`, `notified_first_open`, `last_open_datetime`, `create_by`, `create_datetime`) VALUES
(1, 1, 'a9Idn5deDBrs_m8y6CSRQNU_qpLB4Srk2-_OyR4JEPU', 'ทดสอบการใส่เอกสารผ่านลิงค์', 'ทดสอบลิงค์', 'collect', NULL, '1', '0', '$2y$10$6vL9Cweq3eKYlOTf7pcmhelOhe6dGu0SLvJFqgSiQzLqQ1cztYF0W', '2026-10-11 08:53:49', 100, 0, '0', 0, NULL, '1', '2026-09-11 09:19:26', 1, '2026-09-11 08:53:49');

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

--
-- Dumping data for table `tbl_closing_financial`
--

INSERT INTO `tbl_closing_financial` (`closing_id`, `fiscal_year_id`, `closing_status`, `closing_date`, `doc_status`, `doc_date`, `audit_status`, `audit_date`, `budget_refund_date`, `boj5_status`, `boj5_date`, `dbd_efiling_status`, `dbd_efiling_date`, `pnd50_status`, `pnd50_date`, `created_at`) VALUES
(1, 1, '0', NULL, '0', NULL, '0', NULL, NULL, '0', NULL, '0', NULL, '0', NULL, '2026-09-11 08:50:00'),
(2, 2, '0', NULL, '0', NULL, '0', NULL, NULL, '0', NULL, '0', NULL, '0', NULL, '2026-09-11 09:18:22');

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
  `is_read` varchar(1) NOT NULL DEFAULT '0'
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
(1, 'Accounting', 1, '1', '2026-09-11 08:42:44'),
(2, 'CPD_AC', 2, '0', '2026-09-11 15:19:55');

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
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp() COMMENT 'วันเวลาที่มีการอัปเดตข้อมูลล่าสุด',
  `delete_at` datetime DEFAULT NULL COMMENT 'วันที่มีการลบรายการลูกค้า'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ตารางเก็บข้อมูลโปรไฟล์และข้อมูลการทำบัญชีของลูกค้า';

--
-- Dumping data for table `tbl_customers`
--

INSERT INTO `tbl_customers` (`customer_id`, `customer_name`, `active_status`, `customer_phone`, `customer_email`, `line_id`, `line_group_token`, `doc_folder_url`, `closing_status`, `fiscal_closing_date`, `is_vat`, `is_employees`, `is_social_security`, `accounts_amount`, `rn_user`, `rn_password`, `dbd_user`, `dbd_password`, `sso_user`, `sso_password`, `created_at`, `updated_at`, `delete_at`) VALUES
(1, 'name_test', 1, '0903319035', 'panemoboy@gmail.com', '0965508301', '0965508301', '', '0', '2026-12-31', 1, 1, 1, '3000.00', 'tt', '12345', 'aa', '12345', 'yy', '12345', '2026-09-11 08:50:00', '2026-09-11 14:55:08', NULL),
(2, 'petch_test', 1, '', '', '', '', '', '0', '2026-12-31', 0, 0, 0, '2000.00', '', '', '', '', '', '', '2026-09-11 09:18:22', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_customer_accounts`
--

CREATE TABLE `tbl_customer_accounts` (
  `accounts` int(11) NOT NULL,
  `fiscal_year_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `account_name` text NOT NULL,
  `account_no` varchar(50) NOT NULL,
  `account_pasword` varchar(50) NOT NULL,
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

--
-- Dumping data for table `tbl_customer_tasks`
--

INSERT INTO `tbl_customer_tasks` (`customer_tasks_id`, `fiscal_year_id`, `task_id`, `created_at`, `period_id`, `status`, `amount`, `delete_at`) VALUES
(1, 1, 1, '2026-09-11 08:50:00', 1, '0', '0.00', NULL),
(2, 1, 1, '2026-09-11 08:50:00', 2, '0', '0.00', NULL),
(3, 1, 1, '2026-09-11 08:50:00', 3, '0', '0.00', NULL),
(4, 1, 1, '2026-09-11 08:50:00', 4, '0', '0.00', NULL),
(5, 1, 1, '2026-09-11 08:50:00', 5, '0', '0.00', NULL),
(6, 1, 1, '2026-09-11 08:50:00', 6, '1', '2000.00', NULL),
(7, 1, 1, '2026-09-11 08:50:00', 7, '0', '0.00', NULL),
(8, 1, 1, '2026-09-11 08:50:00', 8, '1', '2000.00', NULL),
(9, 1, 1, '2026-09-11 08:50:00', 9, '0', '0.00', NULL),
(10, 1, 1, '2026-09-11 08:50:00', 10, '0', '0.00', NULL),
(11, 1, 1, '2026-09-11 08:50:00', 11, '0', '0.00', NULL),
(12, 1, 2, '2026-09-11 08:50:00', 1, '0', '0.00', NULL),
(13, 1, 2, '2026-09-11 08:50:00', 2, '0', '0.00', NULL),
(14, 1, 2, '2026-09-11 08:50:00', 3, '0', '0.00', NULL),
(15, 1, 2, '2026-09-11 08:50:00', 4, '0', '0.00', NULL),
(16, 1, 2, '2026-09-11 08:50:00', 5, '0', '0.00', NULL),
(17, 1, 2, '2026-09-11 08:50:00', 6, '1', '2000.00', NULL),
(18, 1, 2, '2026-09-11 08:50:00', 7, '0', '0.00', NULL),
(19, 1, 2, '2026-09-11 08:50:00', 8, '0', '0.00', NULL),
(20, 1, 2, '2026-09-11 08:50:00', 9, '0', '0.00', NULL),
(21, 1, 2, '2026-09-11 08:50:00', 10, '0', '0.00', NULL),
(22, 1, 2, '2026-09-11 08:50:00', 11, '0', '0.00', NULL),
(23, 1, 3, '2026-09-11 08:50:00', 1, '0', '0.00', NULL),
(24, 1, 3, '2026-09-11 08:50:00', 2, '0', '0.00', NULL),
(25, 1, 3, '2026-09-11 08:50:00', 3, '0', '0.00', NULL),
(26, 1, 3, '2026-09-11 08:50:00', 4, '0', '0.00', NULL),
(27, 1, 3, '2026-09-11 08:50:00', 5, '0', '0.00', NULL),
(28, 1, 3, '2026-09-11 08:50:00', 6, '0', '0.00', NULL),
(29, 1, 3, '2026-09-11 08:50:00', 7, '0', '0.00', NULL),
(30, 1, 3, '2026-09-11 08:50:00', 8, '0', '0.00', NULL),
(31, 1, 3, '2026-09-11 08:50:00', 9, '0', '0.00', NULL),
(32, 1, 3, '2026-09-11 08:50:00', 10, '0', '0.00', NULL),
(33, 1, 3, '2026-09-11 08:50:00', 11, '0', '0.00', NULL),
(34, 1, 4, '2026-09-11 08:50:00', 1, '0', '0.00', NULL),
(35, 1, 4, '2026-09-11 08:50:00', 2, '0', '0.00', NULL),
(36, 1, 4, '2026-09-11 08:50:00', 3, '0', '0.00', NULL),
(37, 1, 4, '2026-09-11 08:50:00', 4, '0', '0.00', NULL),
(38, 1, 4, '2026-09-11 08:50:00', 5, '0', '0.00', NULL),
(39, 1, 4, '2026-09-11 08:50:00', 6, '0', '0.00', NULL),
(40, 1, 4, '2026-09-11 08:50:00', 7, '0', '0.00', NULL),
(41, 1, 4, '2026-09-11 08:50:00', 8, '0', '0.00', NULL),
(42, 1, 4, '2026-09-11 08:50:00', 9, '0', '0.00', NULL),
(43, 1, 4, '2026-09-11 08:50:00', 10, '0', '0.00', NULL),
(44, 1, 4, '2026-09-11 08:50:00', 11, '0', '0.00', NULL),
(45, 1, 5, '2026-09-11 08:50:00', 1, '0', '0.00', NULL),
(46, 1, 5, '2026-09-11 08:50:00', 2, '0', '0.00', NULL),
(47, 1, 5, '2026-09-11 08:50:00', 3, '0', '0.00', NULL),
(48, 1, 5, '2026-09-11 08:50:00', 4, '0', '0.00', NULL),
(49, 1, 5, '2026-09-11 08:50:00', 5, '0', '0.00', NULL),
(50, 1, 5, '2026-09-11 08:50:00', 6, '0', '0.00', NULL),
(51, 1, 5, '2026-09-11 08:50:00', 7, '0', '0.00', NULL),
(52, 1, 5, '2026-09-11 08:50:00', 8, '0', '0.00', NULL),
(53, 1, 5, '2026-09-11 08:50:00', 9, '0', '0.00', NULL),
(54, 1, 5, '2026-09-11 08:50:00', 10, '0', '0.00', NULL),
(55, 1, 5, '2026-09-11 08:50:00', 11, '0', '0.00', NULL),
(56, 1, 6, '2026-09-11 08:50:00', 1, '0', '0.00', NULL),
(57, 1, 6, '2026-09-11 08:50:00', 2, '0', '0.00', NULL),
(58, 1, 6, '2026-09-11 08:50:00', 3, '0', '0.00', NULL),
(59, 1, 6, '2026-09-11 08:50:00', 4, '0', '0.00', NULL),
(60, 1, 6, '2026-09-11 08:50:00', 5, '0', '0.00', NULL),
(61, 1, 6, '2026-09-11 08:50:00', 6, '0', '0.00', NULL),
(62, 1, 6, '2026-09-11 08:50:00', 7, '0', '0.00', NULL),
(63, 1, 6, '2026-09-11 08:50:00', 8, '0', '0.00', NULL),
(64, 1, 6, '2026-09-11 08:50:00', 9, '0', '0.00', NULL),
(65, 1, 6, '2026-09-11 08:50:00', 10, '0', '0.00', NULL),
(66, 1, 6, '2026-09-11 08:50:00', 11, '0', '0.00', NULL),
(67, 1, 7, '2026-09-11 08:50:00', 1, '0', '0.00', NULL),
(68, 1, 7, '2026-09-11 08:50:00', 2, '0', '0.00', NULL),
(69, 1, 7, '2026-09-11 08:50:00', 3, '0', '0.00', NULL),
(70, 1, 7, '2026-09-11 08:50:00', 4, '0', '0.00', NULL),
(71, 1, 7, '2026-09-11 08:50:00', 5, '0', '0.00', NULL),
(72, 1, 7, '2026-09-11 08:50:00', 6, '0', '0.00', NULL),
(73, 1, 7, '2026-09-11 08:50:00', 7, '0', '0.00', NULL),
(74, 1, 7, '2026-09-11 08:50:00', 8, '0', '0.00', NULL),
(75, 1, 7, '2026-09-11 08:50:00', 9, '0', '0.00', NULL),
(76, 1, 7, '2026-09-11 08:50:00', 10, '0', '0.00', NULL),
(77, 1, 7, '2026-09-11 08:50:00', 11, '0', '0.00', NULL),
(78, 1, 8, '2026-09-11 08:50:00', 1, '0', '0.00', NULL),
(79, 1, 8, '2026-09-11 08:50:00', 2, '0', '0.00', NULL),
(80, 1, 8, '2026-09-11 08:50:00', 3, '0', '0.00', NULL),
(81, 1, 8, '2026-09-11 08:50:00', 4, '0', '0.00', NULL),
(82, 1, 8, '2026-09-11 08:50:00', 5, '0', '0.00', NULL),
(83, 1, 8, '2026-09-11 08:50:00', 6, '0', '0.00', NULL),
(84, 1, 8, '2026-09-11 08:50:00', 7, '0', '0.00', NULL),
(85, 1, 8, '2026-09-11 08:50:00', 8, '0', '0.00', NULL),
(86, 1, 8, '2026-09-11 08:50:00', 9, '0', '0.00', NULL),
(87, 1, 8, '2026-09-11 08:50:00', 10, '0', '0.00', NULL),
(88, 1, 8, '2026-09-11 08:50:00', 11, '0', '0.00', NULL),
(89, 1, 9, '2026-09-11 08:50:00', 1, '0', '0.00', NULL),
(90, 1, 9, '2026-09-11 08:50:00', 2, '0', '0.00', NULL),
(91, 1, 9, '2026-09-11 08:50:00', 3, '0', '0.00', NULL),
(92, 1, 9, '2026-09-11 08:50:00', 4, '0', '0.00', NULL),
(93, 1, 9, '2026-09-11 08:50:00', 5, '0', '0.00', NULL),
(94, 1, 9, '2026-09-11 08:50:00', 6, '0', '0.00', NULL),
(95, 1, 9, '2026-09-11 08:50:00', 7, '0', '0.00', NULL),
(96, 1, 9, '2026-09-11 08:50:00', 8, '0', '0.00', NULL),
(97, 1, 9, '2026-09-11 08:50:00', 9, '0', '0.00', NULL),
(98, 1, 9, '2026-09-11 08:50:00', 10, '0', '0.00', NULL),
(99, 1, 9, '2026-09-11 08:50:00', 11, '0', '0.00', NULL),
(100, 1, 10, '2026-09-11 08:50:00', 1, '0', '0.00', NULL),
(101, 1, 10, '2026-09-11 08:50:00', 2, '0', '0.00', NULL),
(102, 1, 10, '2026-09-11 08:50:00', 3, '0', '0.00', NULL),
(103, 1, 10, '2026-09-11 08:50:00', 4, '0', '0.00', NULL),
(104, 1, 10, '2026-09-11 08:50:00', 5, '0', '0.00', NULL),
(105, 1, 10, '2026-09-11 08:50:00', 6, '0', '0.00', NULL),
(106, 1, 10, '2026-09-11 08:50:00', 7, '0', '0.00', NULL),
(107, 1, 10, '2026-09-11 08:50:00', 8, '0', '0.00', NULL),
(108, 1, 10, '2026-09-11 08:50:00', 9, '0', '0.00', NULL),
(109, 1, 10, '2026-09-11 08:50:00', 10, '0', '0.00', NULL),
(110, 1, 10, '2026-09-11 08:50:00', 11, '0', '0.00', NULL),
(111, 1, 12, '2026-09-11 08:50:00', 1, '0', '0.00', NULL),
(112, 1, 12, '2026-09-11 08:50:00', 2, '0', '0.00', NULL),
(113, 1, 12, '2026-09-11 08:50:00', 3, '0', '0.00', NULL),
(114, 1, 12, '2026-09-11 08:50:00', 4, '0', '0.00', NULL),
(115, 1, 12, '2026-09-11 08:50:00', 5, '0', '0.00', NULL),
(116, 1, 12, '2026-09-11 08:50:00', 6, '0', '0.00', NULL),
(117, 1, 12, '2026-09-11 08:50:00', 7, '0', '0.00', NULL),
(118, 1, 12, '2026-09-11 08:50:00', 8, '0', '0.00', NULL),
(119, 1, 12, '2026-09-11 08:50:00', 9, '0', '0.00', NULL),
(120, 1, 12, '2026-09-11 08:50:00', 10, '0', '0.00', NULL),
(121, 1, 12, '2026-09-11 08:50:00', 11, '0', '0.00', NULL),
(122, 1, 13, '2026-09-11 08:50:00', 1, '0', '0.00', NULL),
(123, 1, 13, '2026-09-11 08:50:00', 2, '0', '0.00', NULL),
(124, 1, 13, '2026-09-11 08:50:00', 3, '0', '0.00', NULL),
(125, 1, 13, '2026-09-11 08:50:00', 4, '0', '0.00', NULL),
(126, 1, 13, '2026-09-11 08:50:00', 5, '0', '0.00', NULL),
(127, 1, 13, '2026-09-11 08:50:00', 6, '0', '0.00', NULL),
(128, 1, 13, '2026-09-11 08:50:00', 7, '0', '0.00', NULL),
(129, 1, 13, '2026-09-11 08:50:00', 8, '0', '0.00', NULL),
(130, 1, 13, '2026-09-11 08:50:00', 9, '0', '0.00', NULL),
(131, 1, 13, '2026-09-11 08:50:00', 10, '0', '0.00', NULL),
(132, 1, 13, '2026-09-11 08:50:00', 11, '0', '0.00', NULL),
(133, 1, 14, '2026-09-11 08:50:00', 1, '0', '0.00', NULL),
(134, 1, 14, '2026-09-11 08:50:00', 2, '0', '0.00', NULL),
(135, 1, 14, '2026-09-11 08:50:00', 3, '0', '0.00', NULL),
(136, 1, 14, '2026-09-11 08:50:00', 4, '0', '0.00', NULL),
(137, 1, 14, '2026-09-11 08:50:00', 5, '0', '0.00', NULL),
(138, 1, 14, '2026-09-11 08:50:00', 6, '0', '0.00', NULL),
(139, 1, 14, '2026-09-11 08:50:00', 7, '0', '0.00', NULL),
(140, 1, 14, '2026-09-11 08:50:00', 8, '0', '0.00', NULL),
(141, 1, 14, '2026-09-11 08:50:00', 9, '0', '0.00', NULL),
(142, 1, 14, '2026-09-11 08:50:00', 10, '0', '0.00', NULL),
(143, 1, 14, '2026-09-11 08:50:00', 11, '0', '0.00', NULL),
(144, 1, 1, '2026-09-11 09:18:22', 12, '0', '0.00', NULL),
(145, 1, 1, '2026-09-11 09:18:22', 13, '0', '0.00', NULL),
(146, 1, 1, '2026-09-11 09:18:22', 14, '0', '0.00', NULL),
(147, 1, 1, '2026-09-11 09:18:22', 15, '0', '0.00', NULL),
(148, 1, 2, '2026-09-11 09:18:22', 12, '0', '0.00', NULL),
(149, 1, 2, '2026-09-11 09:18:22', 13, '0', '0.00', NULL),
(150, 1, 2, '2026-09-11 09:18:22', 14, '0', '0.00', NULL),
(151, 1, 2, '2026-09-11 09:18:22', 15, '0', '0.00', NULL),
(152, 1, 3, '2026-09-11 09:18:22', 12, '0', '0.00', NULL),
(153, 1, 3, '2026-09-11 09:18:22', 13, '0', '0.00', NULL),
(154, 1, 3, '2026-09-11 09:18:22', 14, '0', '0.00', NULL),
(155, 1, 3, '2026-09-11 09:18:22', 15, '0', '0.00', NULL),
(156, 1, 4, '2026-09-11 09:18:22', 12, '0', '0.00', NULL),
(157, 1, 4, '2026-09-11 09:18:22', 13, '0', '0.00', NULL),
(158, 1, 4, '2026-09-11 09:18:22', 14, '0', '0.00', NULL),
(159, 1, 4, '2026-09-11 09:18:22', 15, '0', '0.00', NULL),
(160, 1, 5, '2026-09-11 09:18:22', 12, '0', '0.00', NULL),
(161, 1, 5, '2026-09-11 09:18:22', 13, '0', '0.00', NULL),
(162, 1, 5, '2026-09-11 09:18:22', 14, '0', '0.00', NULL),
(163, 1, 5, '2026-09-11 09:18:22', 15, '0', '0.00', NULL),
(164, 1, 6, '2026-09-11 09:18:22', 12, '0', '0.00', NULL),
(165, 1, 6, '2026-09-11 09:18:22', 13, '0', '0.00', NULL),
(166, 1, 6, '2026-09-11 09:18:22', 14, '0', '0.00', NULL),
(167, 1, 6, '2026-09-11 09:18:22', 15, '0', '0.00', NULL),
(168, 1, 7, '2026-09-11 09:18:22', 12, '0', '0.00', NULL),
(169, 1, 7, '2026-09-11 09:18:22', 13, '0', '0.00', NULL),
(170, 1, 7, '2026-09-11 09:18:22', 14, '0', '0.00', NULL),
(171, 1, 7, '2026-09-11 09:18:22', 15, '0', '0.00', NULL),
(172, 1, 8, '2026-09-11 09:18:22', 12, '0', '0.00', NULL),
(173, 1, 8, '2026-09-11 09:18:22', 13, '0', '0.00', NULL),
(174, 1, 8, '2026-09-11 09:18:22', 14, '0', '0.00', NULL),
(175, 1, 8, '2026-09-11 09:18:22', 15, '0', '0.00', NULL),
(176, 1, 9, '2026-09-11 09:18:22', 12, '0', '0.00', NULL),
(177, 1, 9, '2026-09-11 09:18:22', 13, '0', '0.00', NULL),
(178, 1, 9, '2026-09-11 09:18:22', 14, '0', '0.00', NULL),
(179, 1, 9, '2026-09-11 09:18:22', 15, '0', '0.00', NULL),
(180, 1, 10, '2026-09-11 09:18:22', 12, '0', '0.00', NULL),
(181, 1, 10, '2026-09-11 09:18:22', 13, '0', '0.00', NULL),
(182, 1, 10, '2026-09-11 09:18:22', 14, '0', '0.00', NULL),
(183, 1, 10, '2026-09-11 09:18:22', 15, '0', '0.00', NULL),
(184, 1, 11, '2026-09-11 09:18:22', 12, '0', '0.00', NULL),
(185, 1, 11, '2026-09-11 09:18:22', 13, '0', '0.00', NULL),
(186, 1, 11, '2026-09-11 09:18:22', 14, '0', '0.00', NULL),
(187, 1, 11, '2026-09-11 09:18:22', 15, '0', '0.00', NULL),
(188, 1, 12, '2026-09-11 09:18:22', 12, '0', '0.00', NULL),
(189, 1, 12, '2026-09-11 09:18:22', 13, '0', '0.00', NULL),
(190, 1, 12, '2026-09-11 09:18:22', 14, '0', '0.00', NULL),
(191, 1, 12, '2026-09-11 09:18:22', 15, '0', '0.00', NULL),
(192, 1, 13, '2026-09-11 09:18:22', 12, '0', '0.00', NULL),
(193, 1, 13, '2026-09-11 09:18:22', 13, '0', '0.00', NULL),
(194, 1, 13, '2026-09-11 09:18:22', 14, '0', '0.00', NULL),
(195, 1, 13, '2026-09-11 09:18:22', 15, '0', '0.00', NULL),
(196, 1, 14, '2026-09-11 09:18:22', 12, '0', '0.00', NULL),
(197, 1, 14, '2026-09-11 09:18:22', 13, '0', '0.00', NULL),
(198, 1, 14, '2026-09-11 09:18:22', 14, '0', '0.00', NULL),
(199, 1, 14, '2026-09-11 09:18:22', 15, '0', '0.00', NULL);

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
  `review1_status` varchar(1) NOT NULL DEFAULT '0' COMMENT '0 = ยังไม่ได้รีวิว\r\n1 = รีวิวแล้ว',
  `review1_user_id` int(11) DEFAULT NULL,
  `review2_status` varchar(1) NOT NULL DEFAULT '0' COMMENT '0 = ยังไม่ได้รีวิว\r\n1 = รีวิวแล้ว',
  `review2_user_id` int(11) DEFAULT NULL,
  `review3_status` varchar(1) NOT NULL DEFAULT '0' COMMENT '0 = ยังไม่ได้รีวิว\r\n1 = รีวิวแล้ว',
  `review3_user_id` int(11) DEFAULT NULL,
  `complate_status` varchar(1) NOT NULL DEFAULT '0' COMMENT 'ทำงานเสร็จทุกขั้นตอน',
  `tax_create_at` datetime DEFAULT NULL COMMENT 'วันที่ยื่นภาษี',
  `doc_date` date DEFAULT NULL COMMENT 'วันที่ได้รับเอกสาร',
  `completed_date` date DEFAULT NULL COMMENT 'วันที่ทำเสร็จ',
  `tax_date` date DEFAULT NULL COMMENT 'วันที่ยื่นภาษี',
  `created_at` datetime NOT NULL,
  `delete_at` datetime DEFAULT NULL COMMENT 'เดือนที่มีการลบ'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_customer_work_periods`
--

INSERT INTO `tbl_customer_work_periods` (`period_id`, `customer_id`, `fiscal_year_id`, `period_month`, `doc_status`, `tax_status`, `payment_status`, `review1_status`, `review1_user_id`, `review2_status`, `review2_user_id`, `review3_status`, `review3_user_id`, `complate_status`, `tax_create_at`, `doc_date`, `completed_date`, `tax_date`, `created_at`, `delete_at`) VALUES
(1, 1, 1, '02', '0', '0', '0', '0', NULL, '0', NULL, '0', NULL, '0', NULL, NULL, NULL, NULL, '2026-09-11 08:50:00', NULL),
(2, 1, 1, '03', '0', '0', '0', '0', NULL, '0', NULL, '0', NULL, '0', NULL, NULL, NULL, NULL, '2026-09-11 08:50:00', NULL),
(3, 1, 1, '04', '0', '0', '0', '0', NULL, '0', NULL, '0', NULL, '0', NULL, NULL, NULL, NULL, '2026-09-11 08:50:00', NULL),
(4, 1, 1, '05', '0', '0', '0', '0', NULL, '0', NULL, '0', NULL, '0', NULL, NULL, NULL, NULL, '2026-09-11 08:50:00', NULL),
(5, 1, 1, '06', '0', '0', '0', '0', NULL, '0', NULL, '0', NULL, '0', NULL, NULL, NULL, NULL, '2026-09-11 08:50:00', NULL),
(6, 1, 1, '07', '0', '0', '0', '0', NULL, '0', NULL, '0', NULL, '0', NULL, NULL, NULL, NULL, '2026-09-11 08:50:00', NULL),
(7, 1, 1, '08', '0', '0', '0', '0', NULL, '0', NULL, '0', NULL, '0', NULL, NULL, NULL, NULL, '2026-09-11 08:50:00', NULL),
(8, 1, 1, '09', '0', '0', '0', '0', NULL, '0', NULL, '0', NULL, '0', NULL, NULL, NULL, NULL, '2026-09-11 08:50:00', NULL),
(9, 1, 1, '10', '0', '0', '0', '0', NULL, '0', NULL, '0', NULL, '0', NULL, NULL, NULL, NULL, '2026-09-11 08:50:00', NULL),
(10, 1, 1, '11', '0', '0', '0', '0', NULL, '0', NULL, '0', NULL, '0', NULL, NULL, NULL, NULL, '2026-09-11 08:50:00', NULL),
(11, 1, 1, '12', '0', '0', '0', '0', NULL, '0', NULL, '0', NULL, '0', NULL, NULL, NULL, NULL, '2026-09-11 08:50:00', NULL),
(12, 2, 1, '09', '0', '0', '0', '0', NULL, '0', NULL, '0', NULL, '0', NULL, NULL, NULL, NULL, '2026-09-11 09:18:22', NULL),
(13, 2, 1, '10', '0', '0', '0', '0', NULL, '0', NULL, '0', NULL, '0', NULL, NULL, NULL, NULL, '2026-09-11 09:18:22', NULL),
(14, 2, 1, '11', '0', '0', '0', '0', NULL, '0', NULL, '0', NULL, '0', NULL, NULL, NULL, NULL, '2026-09-11 09:18:22', NULL),
(15, 2, 1, '12', '0', '0', '0', '0', NULL, '0', NULL, '0', NULL, '0', NULL, NULL, NULL, NULL, '2026-09-11 09:18:22', NULL);

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

--
-- Dumping data for table `tbl_fiscal_years`
--

INSERT INTO `tbl_fiscal_years` (`fiscal_id`, `company_id`, `fiscal_years`, `active_status`, `create_at`) VALUES
(1, 1, '2569', '1', '2026-09-11 08:42:54'),
(2, 1, '2570', '1', '2026-09-11 11:50:03');

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

--
-- Dumping data for table `tbl_fiscal_year_customers`
--

INSERT INTO `tbl_fiscal_year_customers` (`fiscal_year_id`, `fiscal_id`, `customer_id`, `service_start_date`, `service_start_end`, `user_id`, `team_id`, `created_at`, `accounts_amount`) VALUES
(1, 1, 1, '2', '0', 2, 1, '2026-09-11 08:50:00', 3000),
(2, 1, 2, '9', '12', 2, 1, '2026-09-11 09:18:22', 2000);

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
(1, 1, 2, '2026-09-11 08:47:49');

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
(1, 1, '27.130.25.141', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-12 08:26:06', NULL, '2026-09-11 08:26:06', NULL),
(2, 1, '27.145.8.57', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-12 09:11:03', NULL, '2026-09-11 09:11:03', NULL),
(3, 1, '27.145.8.169', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-12 09:36:55', NULL, '2026-09-11 09:36:55', NULL),
(4, 1, '27.130.25.141', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-12 09:47:18', NULL, '2026-09-11 09:47:18', NULL),
(5, 1, '27.145.8.169', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-12 11:25:41', NULL, '2026-09-11 11:25:41', NULL),
(6, 2, '49.237.34.83', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-12 11:38:18', NULL, '2026-09-11 11:38:18', NULL),
(7, 1, '27.145.8.169', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-12 13:01:48', NULL, '2026-09-11 13:01:48', NULL),
(8, 2, '27.145.8.169', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-12 13:28:59', NULL, '2026-09-11 13:28:59', NULL),
(9, 2, '27.145.8.169', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-12 14:03:20', NULL, '2026-09-11 14:03:20', NULL),
(10, 1, '27.145.8.169', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-12 14:47:17', NULL, '2026-09-11 14:47:17', NULL),
(11, 2, '27.145.8.169', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-12 15:18:43', NULL, '2026-09-11 15:18:43', NULL),
(12, 1, '27.145.8.169', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-12 15:20:10', NULL, '2026-09-11 15:20:10', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_notifications`
--

CREATE TABLE `tbl_notifications` (
  `notif_id` int(11) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `task_type` varchar(50) NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_notification_settings`
--

CREATE TABLE `tbl_notification_settings` (
  `user_id` varchar(50) NOT NULL,
  `notify_days_advance` int(11) NOT NULL DEFAULT 5,
  `notify_email` varchar(255) DEFAULT NULL
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

--
-- Dumping data for table `tbl_post_it`
--

INSERT INTO `tbl_post_it` (`post_id`, `fiscal_year_id`, `title`, `user_id`, `due_date`, `status`, `content`, `color_code`, `created_user_id`, `created_at`) VALUES
(1, 1, 'ทดสอบ', 2, '2026-09-30', '0', 'ทดสอบการแจ้งเตือน', 'yellow', 1, '2026-09-11 11:33:26');

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

--
-- Dumping data for table `tbl_registration`
--

INSERT INTO `tbl_registration` (`registration`, `fiscal_id`, `registration_type_id`, `customer_name`, `customer_phone`, `contact_person`, `registration_name`, `status`, `description`, `service_amount`, `urgency_level`, `accep_date`, `due_date`, `assignee_user_id`, `review_user_id`, `registration_no`, `delete_at`, `delete_user_id`, `closed_at`, `close_user_id`) VALUES
(1, 1, 1, 'TEST', '0627103595', 'TEST', 'TEST', '0', 'TEST', '1000.00', '1', '2026-09-11', '2026-09-11', 2, 2, 'REG-001', NULL, NULL, NULL, NULL);

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

--
-- Dumping data for table `tbl_registration_task_setting`
--

INSERT INTO `tbl_registration_task_setting` (`setting_id`, `fiscal_id`, `notify_day`, `update_at`, `update_user_id`) VALUES
(1, 1, 7, NULL, NULL);

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

--
-- Dumping data for table `tbl_registration_type`
--

INSERT INTO `tbl_registration_type` (`registration_type_id`, `registration_type_name`, `active_status`, `created_at`, `create_user_id`, `fiscal_id`, `delete_at`, `delete_user_id`) VALUES
(1, 'TEST', '1', '2026-09-11 10:38:07', 1, 1, NULL, NULL);

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
(1, 1, 'ภ.ง.ด.1', '1', '2026-09-11 08:43:34', 1, NULL),
(2, 1, 'ภ.ง.ด.3', '1', '2026-09-11 08:44:25', 2, NULL),
(3, 1, 'ภ.ง.ด.53', '1', '2026-09-11 08:44:31', 3, NULL),
(4, 1, 'ภ.ง.ด.54', '1', '2026-09-11 08:44:38', 4, NULL),
(5, 1, 'ภ.พ.30', '1', '2026-09-11 08:45:27', 5, NULL),
(6, 1, 'ภ.พ.36', '1', '2026-09-11 08:45:34', 6, NULL),
(7, 1, 'ประกันสังคม', '1', '2026-09-11 08:45:42', 7, NULL),
(8, 1, 'ก.ย.ศ.', '1', '2026-09-11 08:45:56', 8, NULL),
(9, 1, 'BBL', '0', '2026-09-11 08:46:03', 9, NULL),
(10, 1, 'KBANK', '0', '2026-09-11 08:46:36', 10, NULL),
(11, 1, 'UOB', '0', '2026-09-11 08:46:42', 11, NULL),
(12, 1, 'TTB', '0', '2026-09-11 08:46:49', 12, NULL),
(13, 1, 'SCB', '0', '2026-09-11 08:46:56', 13, NULL),
(14, 1, 'กระทบ Bank', '0', '2026-09-11 08:47:04', 14, NULL);

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
(1, 'account', '2026-09-11 08:47:49'),
(2, 'developers', '2026-09-11 09:17:48');

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

--
-- Dumping data for table `tbl_urgency_level`
--

INSERT INTO `tbl_urgency_level` (`urgency_level_id`, `fiscal_id`, `urgency_level`, `label`, `color`) VALUES
(1, 1, '1', 'ปกติ', '#94a3b8'),
(2, 1, '2', 'เร่งด่วน', '#f97316'),
(3, 1, '3', 'ด่วนมาก', '#ef4444');

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
  `delete_at` datetime DEFAULT NULL,
  `is_super_admin` varchar(1) NOT NULL COMMENT '0 = ไม่ใช่ supera dmin admin 1 = super admin',
  `position` text DEFAULT NULL COMMENT 'ตำแหน่ง',
  `team_id` int(11) DEFAULT NULL COMMENT 'รหัสทีม'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_user`
--

INSERT INTO `tbl_user` (`user_id`, `user_name`, `user_password`, `user_firstname`, `user_lastname`, `user_status`, `create_at`, `delete_at`, `is_super_admin`, `position`, `team_id`) VALUES
(1, 'cpdth12345@am-amaudit.com', '$2y$10$sxnYoO0UfANJ7wTwbqxkR.O4jjSUGwaxlpNjL7CrhXSJFKisR4sba', 'admin', 'cpdth', '1', '2026-09-11 08:25:25', NULL, '1', NULL, NULL),
(2, 'name_test', '$2y$10$LJ5bpv7xi.I1hzg/djf1qOKzd.pON/e.KjXuOw1DOJXby8APoWL5W', 'TEERAPAT', 'KHUCHITA', '1', '2026-09-11 08:47:49', NULL, '0', 'account', 1);

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
(NULL, 2, 1);

--
-- Indexes for dumped tables
--

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
  ADD PRIMARY KEY (`accounts`);

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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_cd_activity`
--
ALTER TABLE `tbl_cd_activity`
  MODIFY `activity_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_cd_link`
--
ALTER TABLE `tbl_cd_link`
  MODIFY `link_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_cd_link_session`
--
ALTER TABLE `tbl_cd_link_session`
  MODIFY `session_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
  MODIFY `closing_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_comment_tasks`
--
ALTER TABLE `tbl_comment_tasks`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_companies`
--
ALTER TABLE `tbl_companies`
  MODIFY `company_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_customers`
--
ALTER TABLE `tbl_customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'รหัสลูกค้า (Primary Key)', AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_customer_accounts`
--
ALTER TABLE `tbl_customer_accounts`
  MODIFY `accounts` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_customer_tasks`
--
ALTER TABLE `tbl_customer_tasks`
  MODIFY `customer_tasks_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=200;

--
-- AUTO_INCREMENT for table `tbl_customer_work_periods`
--
ALTER TABLE `tbl_customer_work_periods`
  MODIFY `period_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `tbl_fiscal_years`
--
ALTER TABLE `tbl_fiscal_years`
  MODIFY `fiscal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_fiscal_year_customers`
--
ALTER TABLE `tbl_fiscal_year_customers`
  MODIFY `fiscal_year_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_fiscal_year_user`
--
ALTER TABLE `tbl_fiscal_year_user`
  MODIFY `fiscal_employee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_login_token`
--
ALTER TABLE `tbl_login_token`
  MODIFY `token_code` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `tbl_notifications`
--
ALTER TABLE `tbl_notifications`
  MODIFY `notif_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_post_it`
--
ALTER TABLE `tbl_post_it`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_push_subscriptions`
--
ALTER TABLE `tbl_push_subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_registration`
--
ALTER TABLE `tbl_registration`
  MODIFY `registration` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_registration_task_setting`
--
ALTER TABLE `tbl_registration_task_setting`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_registration_type`
--
ALTER TABLE `tbl_registration_type`
  MODIFY `registration_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_tasks`
--
ALTER TABLE `tbl_tasks`
  MODIFY `tasks_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `tbl_team`
--
ALTER TABLE `tbl_team`
  MODIFY `team_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_urgency_level`
--
ALTER TABLE `tbl_urgency_level`
  MODIFY `urgency_level_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_user`
--
ALTER TABLE `tbl_user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
