-- customer_drive_migration.sql
-- Customer Drive feature (phase 1: staff drive + phase 2: share-link guest portal)
-- รวม migration ทั้ง 2 เฟสของ session นี้ไว้ไฟล์เดียว เรียงตามลำดับ FK ให้รันได้ตรง ๆ
-- (customer_drive_migration.sql + customer_drive_phase2_migration.sql ที่ root โปรเจกต์)
--
-- ก่อนรัน: ต้องมีตาราง `tbl_customers` และ `tbl_cd_node` (สร้างในไฟล์นี้เอง) อยู่แล้ว
-- ไม่ต้องรันอะไรมาก่อนนอกจากฐานข้อมูล cpd_ac หลักที่มีอยู่แล้วบนเซิร์ฟเวอร์

START TRANSACTION;

-- ============================================================
-- เฟส 1 — คลังไฟล์ฝั่งพนักงาน
-- ============================================================

CREATE TABLE `tbl_cd_node` (
  `node_id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `parent_id` int DEFAULT NULL COMMENT 'NULL = top level of the drive',
  `kind` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'file' COMMENT 'file | folder',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ext` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `list_order` int NOT NULL DEFAULT '0',
  `version` int NOT NULL DEFAULT '0',
  `size` bigint NOT NULL DEFAULT '0',
  `sha256` char(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'staff' COMMENT 'staff | guest',
  `source_link_id` int DEFAULT NULL COMMENT 'มาจากลิงก์แชร์ไหน (ไม่ผูก FK โดยตั้งใจ)',
  `guest_label` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `create_by` int DEFAULT NULL,
  `create_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_by` int DEFAULT NULL,
  `update_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `delete_by` int DEFAULT NULL,
  `delete_datetime` datetime DEFAULT NULL COMMENT 'trash soft-delete, never auto-expires',
  PRIMARY KEY (`node_id`),
  KEY `idx_cd_node_tree` (`customer_id`,`parent_id`,`list_order`),
  KEY `idx_cd_node_live` (`customer_id`,`delete_datetime`),
  KEY `idx_cd_node_parent` (`parent_id`),
  CONSTRAINT `fk_cd_node_customer` FOREIGN KEY (`customer_id`) REFERENCES `tbl_customers` (`customer_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cd_node_parent` FOREIGN KEY (`parent_id`) REFERENCES `tbl_cd_node` (`node_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tbl_cd_version` (
  `version_id` int NOT NULL AUTO_INCREMENT,
  `node_id` int NOT NULL,
  `version` int NOT NULL,
  `size` bigint NOT NULL DEFAULT '0',
  `sha256` char(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `via` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'staff_upload' COMMENT 'staff_upload | guest_upload | restore',
  `create_by` int DEFAULT NULL,
  `guest_label` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `create_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`version_id`),
  UNIQUE KEY `uq_cd_version` (`node_id`,`version`),
  CONSTRAINT `fk_cd_version_node` FOREIGN KEY (`node_id`) REFERENCES `tbl_cd_node` (`node_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tbl_cd_activity` (
  `activity_id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `node_id` int DEFAULT NULL COMMENT 'ไม่ผูก FK โดยตั้งใจ',
  `link_id` int DEFAULT NULL COMMENT 'ไม่ผูก FK โดยตั้งใจ',
  `actor_type` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'staff' COMMENT 'staff | guest | system',
  `actor_user_id` int DEFAULT NULL,
  `actor_label` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `detail` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip` varbinary(16) DEFAULT NULL,
  `create_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`activity_id`),
  KEY `idx_cd_activity_feed` (`customer_id`,`create_datetime`),
  KEY `idx_cd_activity_node` (`node_id`),
  CONSTRAINT `fk_cd_activity_customer` FOREIGN KEY (`customer_id`) REFERENCES `tbl_customers` (`customer_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- เฟส 2 — ลิงก์แชร์ + พอร์ทัลลูกค้า
-- ============================================================

CREATE TABLE `tbl_cd_link` (
  `link_id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `token` char(43) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'random_bytes(32) base64url',
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `mode` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'collect' COMMENT 'collect | share',
  `root_node_id` int DEFAULT NULL COMMENT 'NULL = ทั้งคลัง',
  `allow_upload` varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1',
  `allow_download` varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_datetime` datetime DEFAULT NULL,
  `max_uploads` int DEFAULT NULL,
  `used_uploads` int NOT NULL DEFAULT '0',
  `revoked` varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `fail_count` int NOT NULL DEFAULT '0',
  `locked_until` datetime DEFAULT NULL,
  `notified_first_open` varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `last_open_datetime` datetime DEFAULT NULL,
  `create_by` int NOT NULL,
  `create_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`link_id`),
  UNIQUE KEY `uq_cd_link_token` (`token`),
  KEY `idx_cd_link_customer` (`customer_id`,`revoked`),
  KEY `idx_cd_link_root` (`root_node_id`),
  CONSTRAINT `fk_cd_link_customer` FOREIGN KEY (`customer_id`) REFERENCES `tbl_customers` (`customer_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cd_link_root` FOREIGN KEY (`root_node_id`) REFERENCES `tbl_cd_node` (`node_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tbl_cd_link_session` (
  `session_id` int NOT NULL AUTO_INCREMENT,
  `link_id` int NOT NULL,
  `session_token` char(43) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guest_label` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip` varbinary(16) DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expires_datetime` datetime NOT NULL,
  `create_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`session_id`),
  UNIQUE KEY `uq_cd_session_token` (`session_token`),
  KEY `idx_cd_session_link` (`link_id`,`expires_datetime`),
  CONSTRAINT `fk_cd_session_link` FOREIGN KEY (`link_id`) REFERENCES `tbl_cd_link` (`link_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;
