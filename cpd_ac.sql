-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 03, 2026 at 04:09 AM
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
-- Table structure for table `tbl_customer`
--

CREATE TABLE `tbl_customer` (
  `customer_id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL COMMENT 'ชื่อบริษัท/กิจการ',
  `service_start_date` year(4) NOT NULL COMMENT 'เดือนที่เริ่มให้บิรการ',
  `service_start_end` year(4) NOT NULL COMMENT 'เดือนที่สิ้นสุดการให้บริการ',
  `active_status` varchar(1) NOT NULL DEFAULT '1',
  `customer_phone` int(10) DEFAULT NULL COMMENT 'เบอร์มือถือ',
  `customer_email` varchar(50) DEFAULT NULL COMMENT 'email',
  `line_id` varchar(50) DEFAULT NULL COMMENT 'line id',
  `doc_folder_url` varchar(255) DEFAULT NULL COMMENT 'ลิง๕ืไว้ให้ลูกค้าส่งเอกสาร',
  `line_group_token` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_customer_accounts`
--

CREATE TABLE `tbl_customer_accounts` (
  `accounts` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `closing_status` varchar(1) NOT NULL DEFAULT '0' COMMENT '0 = ปิดงบประจำปี / 1 = ไม่ปิดงบ',
  `fiscal_closing_date` date NOT NULL COMMENT 'วันสิ้นรอบบัญชี',
  `is_vat` varchar(1) NOT NULL DEFAULT '0' COMMENT '0 = จด vat / 1 = ไม่จด vat',
  `is_employees` varchar(1) NOT NULL DEFAULT '0' COMMENT '0 = ไม่มีพนักงาน / 1 = มีพนักงาน',
  `is_social_security` varchar(1) NOT NULL DEFAULT '0' COMMENT '0 = ไม่มีประกันสังคม / 1 = มีประกันสังคม',
  `rn_user` varchar(50) DEFAULT NULL COMMENT 'user สรรมพากร',
  `rn_password` varchar(50) DEFAULT NULL COMMENT 'password สรรมพากร',
  `dbd_user` varchar(50) DEFAULT NULL COMMENT 'user กรมพัฒน์',
  `dbd_password` varchar(50) DEFAULT NULL COMMENT 'password กรมพัฒน์',
  `sso_user` varchar(50) DEFAULT NULL COMMENT 'user ประกันสังคม',
  `sso_password` varchar(50) DEFAULT NULL COMMENT 'password ประกันสังคม'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_customer_tasks`
--

CREATE TABLE `tbl_customer_tasks` (
  `customer_tasks_id` int(11) NOT NULL,
  `fiscal_year_id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL
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
(1, 1, '2670', '', '2026-09-01 15:40:25');

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
  `created_at` datetime NOT NULL
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
(9, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-03 18:52:05', NULL, '2026-09-02 23:52:05', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_post_it`
--

CREATE TABLE `tbl_post_it` (
  `post_id` int(11) NOT NULL,
  `fiscal_year_id` int(11) NOT NULL,
  `title` text NOT NULL COMMENT 'หัวข้องาน',
  `user_id` int(11) NOT NULL,
  `due_date` date NOT NULL COMMENT 'กำหนดส่ง',
  `status` varchar(1) NOT NULL COMMENT '0 = รอดำเนินการ / 1 = ปิดงานแล้ว',
  `content` text NOT NULL COMMENT 'เนื้อหางาน',
  `color_code` varchar(100) NOT NULL COMMENT 'รหัสสี',
  `created_user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_tasks`
--

CREATE TABLE `tbl_tasks` (
  `tasks_id` int(11) NOT NULL,
  `fiscal_id` int(11) NOT NULL,
  `tasks_name` text NOT NULL,
  `is_notify_amount` varchar(1) NOT NULL COMMENT 'ต้องระบุจำนวนเงินสำหรับแจ้งยอดผ่าน LINE ลูกค้า',
  `created_at` datetime NOT NULL
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
(1, 'cpdth12345@am-amaudit.com', '$2y$10$sxnYoO0UfANJ7wTwbqxkR.O4jjSUGwaxlpNjL7CrhXSJFKisR4sba', 'admin', 'cpdth', '1', '2026-09-01 04:23:56', '1', NULL, NULL);

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
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_companies`
--
ALTER TABLE `tbl_companies`
  ADD PRIMARY KEY (`company_id`);

--
-- Indexes for table `tbl_customer`
--
ALTER TABLE `tbl_customer`
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
-- Indexes for table `tbl_tasks`
--
ALTER TABLE `tbl_tasks`
  ADD PRIMARY KEY (`tasks_id`);

--
-- Indexes for table `tbl_user`
--
ALTER TABLE `tbl_user`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_companies`
--
ALTER TABLE `tbl_companies`
  MODIFY `company_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_customer`
--
ALTER TABLE `tbl_customer`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_customer_accounts`
--
ALTER TABLE `tbl_customer_accounts`
  MODIFY `accounts` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_customer_tasks`
--
ALTER TABLE `tbl_customer_tasks`
  MODIFY `customer_tasks_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_fiscal_years`
--
ALTER TABLE `tbl_fiscal_years`
  MODIFY `fiscal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
  MODIFY `token_code` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `tbl_post_it`
--
ALTER TABLE `tbl_post_it`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_tasks`
--
ALTER TABLE `tbl_tasks`
  MODIFY `tasks_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_user`
--
ALTER TABLE `tbl_user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
