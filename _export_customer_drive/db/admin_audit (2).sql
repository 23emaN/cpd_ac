-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 08, 2026 at 04:00 AM
-- Server version: 8.0.46
-- PHP Version: 8.4.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `admin_audit`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_cd_activity`
--

CREATE TABLE `tbl_cd_activity` (
  `activity_id` int NOT NULL,
  `customer_id` int NOT NULL,
  `node_id` int DEFAULT NULL COMMENT 'ไม่ผูก FK โดยตั้งใจ',
  `link_id` int DEFAULT NULL COMMENT 'ไม่ผูก FK โดยตั้งใจ',
  `actor_type` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'staff' COMMENT 'staff | guest | system',
  `actor_user_id` int DEFAULT NULL,
  `actor_label` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ชื่อแขก',
  `action` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `detail` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ชื่อไฟล์ ฯลฯ',
  `ip` varbinary(16) DEFAULT NULL COMMENT 'บังคับบันทึกเมื่อเป็น link_auth_fail',
  `create_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_cd_link`
--

CREATE TABLE `tbl_cd_link` (
  `link_id` int NOT NULL,
  `customer_id` int NOT NULL,
  `token` char(43) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'random_bytes(32) base64url',
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'เช่น ขอเอกสารปิดงบ 2568',
  `message` text COLLATE utf8mb4_unicode_ci COMMENT 'คำชี้แจงที่ลูกค้าเห็นบนหน้าแขก',
  `mode` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'collect' COMMENT 'collect | share',
  `root_node_id` int DEFAULT NULL COMMENT 'ขอบเขต NULL = ทั้งคลัง',
  `allow_upload` varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1',
  `allow_download` varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT 'โหมด collect ต้องเป็น 0 เสมอ',
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'password_hash() เท่านั้น',
  `expires_datetime` datetime DEFAULT NULL COMMENT 'NULL = ไม่หมดอายุ (ไม่แนะนำ)',
  `max_uploads` int DEFAULT NULL COMMENT 'เพดานจำนวนไฟล์ต่อลิงก์',
  `used_uploads` int NOT NULL DEFAULT '0',
  `revoked` varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `fail_count` int NOT NULL DEFAULT '0' COMMENT 'กรอกรหัสผิดสะสม',
  `locked_until` datetime DEFAULT NULL COMMENT 'ผิด 5 ครั้ง ล็อก 15 นาที',
  `notified_first_open` varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT 'กันแจ้ง ลูกค้าเปิดลิงก์ ซ้ำ',
  `last_open_datetime` datetime DEFAULT NULL,
  `create_by` int NOT NULL,
  `create_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_cd_link_session`
--

CREATE TABLE `tbl_cd_link_session` (
  `session_id` int NOT NULL,
  `link_id` int NOT NULL,
  `session_token` char(43) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'เก็บใน cookie HttpOnly',
  `guest_label` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ชื่อผู้ส่งที่แขกกรอก',
  `ip` varbinary(16) DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expires_datetime` datetime NOT NULL,
  `create_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_cd_node`
--

CREATE TABLE `tbl_cd_node` (
  `node_id` int NOT NULL,
  `customer_id` int NOT NULL,
  `parent_id` int DEFAULT NULL COMMENT 'NULL = ชั้นบนสุดของคลัง',
  `kind` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'file' COMMENT 'file | folder',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ชื่อที่ผู้ใช้เห็น ไม่ใช่ชื่อบนดิสก์',
  `ext` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'NULL เมื่อเป็นโฟลเดอร์',
  `list_order` int NOT NULL DEFAULT '0' COMMENT '0 = ยังไม่เคยสั่งเรียงเอง',
  `version` int NOT NULL DEFAULT '0',
  `size` bigint NOT NULL DEFAULT '0',
  `sha256` char(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ของเวอร์ชันปัจจุบัน ใช้กันอัปซ้ำ',
  `source` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'staff' COMMENT 'staff | guest',
  `source_link_id` int DEFAULT NULL COMMENT 'มาจากลิงก์แชร์ไหน (ไม่ผูก FK โดยตั้งใจ)',
  `guest_label` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ชื่อที่แขกกรอกตอนอัป',
  `create_by` int DEFAULT NULL COMMENT 'NULL เมื่อผู้สร้างเป็นแขก',
  `create_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_by` int DEFAULT NULL,
  `update_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `delete_by` int DEFAULT NULL,
  `delete_datetime` datetime DEFAULT NULL COMMENT 'ถังขยะ soft delete เก็บ 30 วัน'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_cd_version`
--

CREATE TABLE `tbl_cd_version` (
  `version_id` int NOT NULL,
  `node_id` int NOT NULL,
  `version` int NOT NULL,
  `size` bigint NOT NULL DEFAULT '0',
  `sha256` char(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `via` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'staff_upload' COMMENT 'staff_upload | guest_upload | restore',
  `create_by` int DEFAULT NULL,
  `guest_label` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `create_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_cd_activity`
--
ALTER TABLE `tbl_cd_activity`
  ADD PRIMARY KEY (`activity_id`),
  ADD KEY `idx_cd_activity_feed` (`customer_id`,`create_datetime`),
  ADD KEY `idx_cd_activity_node` (`node_id`),
  ADD KEY `idx_cd_activity_link` (`link_id`,`action`);

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
  ADD KEY `idx_cd_node_parent` (`parent_id`),
  ADD KEY `idx_cd_node_link` (`source_link_id`);

--
-- Indexes for table `tbl_cd_version`
--
ALTER TABLE `tbl_cd_version`
  ADD PRIMARY KEY (`version_id`),
  ADD UNIQUE KEY `uq_cd_version` (`node_id`,`version`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_cd_activity`
--
ALTER TABLE `tbl_cd_activity`
  MODIFY `activity_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_cd_link`
--
ALTER TABLE `tbl_cd_link`
  MODIFY `link_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_cd_link_session`
--
ALTER TABLE `tbl_cd_link_session`
  MODIFY `session_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_cd_node`
--
ALTER TABLE `tbl_cd_node`
  MODIFY `node_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_cd_version`
--
ALTER TABLE `tbl_cd_version`
  MODIFY `version_id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_cd_activity`
--
ALTER TABLE `tbl_cd_activity`
  ADD CONSTRAINT `fk_cd_activity_customer` FOREIGN KEY (`customer_id`) REFERENCES `tbl_customer` (`customer_id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_cd_link`
--
ALTER TABLE `tbl_cd_link`
  ADD CONSTRAINT `fk_cd_link_customer` FOREIGN KEY (`customer_id`) REFERENCES `tbl_customer` (`customer_id`) ON DELETE CASCADE,
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
  ADD CONSTRAINT `fk_cd_node_customer` FOREIGN KEY (`customer_id`) REFERENCES `tbl_customer` (`customer_id`) ON DELETE CASCADE,
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
