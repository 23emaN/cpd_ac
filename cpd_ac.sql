-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 01, 2026 at 06:27 AM
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
  `company_name` varchar(255) NOT NULL COMMENT 'ืชื่อบริษัท',
  `active_status` varchar(1) NOT NULL DEFAULT '0' COMMENT '0 = ไม่ใช้งาน\r\n1 = ใช้งาน',
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_customer`
--

CREATE TABLE `tbl_customer` (
  `customer_id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL COMMENT 'ชื่อบริษัท/กิจการ',
  `service_start_date` year(4) NOT NULL COMMENT 'เดือนที่เริ่มให้บิรการ',
  `service_start_end` year(4) NOT NULL COMMENT 'เดือนที่สิ้นสุดการให้บริการ'
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

-- --------------------------------------------------------

--
-- Table structure for table `tbl_fiscal_year_customers`
--

CREATE TABLE `tbl_fiscal_year_customers` (
  `fiscal_year_id` int(11) NOT NULL,
  `fiscal_id` int(11) NOT NULL COMMENT 'ปีงบประมาณ',
  `user_id` int(11) NOT NULL COMMENT 'ลูกค้า',
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_fiscal_year_employees`
--

CREATE TABLE `tbl_fiscal_year_employees` (
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
-- Table structure for table `tbl_team`
--

CREATE TABLE `tbl_team` (
  `team_id` int(11) NOT NULL,
  `team_name` text NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Indexes for table `tbl_fiscal_year_employees`
--
ALTER TABLE `tbl_fiscal_year_employees`
  ADD PRIMARY KEY (`fiscal_employee_id`);

--
-- Indexes for table `tbl_login_token`
--
ALTER TABLE `tbl_login_token`
  ADD PRIMARY KEY (`token_code`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_companies`
--
ALTER TABLE `tbl_companies`
  MODIFY `company_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_customer`
--
ALTER TABLE `tbl_customer`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `tbl_fiscal_year_employees`
--
ALTER TABLE `tbl_fiscal_year_employees`
  MODIFY `fiscal_employee_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_login_token`
--
ALTER TABLE `tbl_login_token`
  MODIFY `token_code` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
