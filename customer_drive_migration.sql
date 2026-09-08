-- customer_drive_migration.sql
-- Customer Drive (phase 1: staff-facing only — no guest portal, no share links)
-- Adapted from _export_customer_drive/db/admin_audit (2).sql
--   * FK target changed from tbl_customer -> tbl_customers (real table in this project)
--   * tbl_cd_link and tbl_cd_link_session dropped entirely (phase 2, out of scope)
--   * link_id / source_link_id / guest_label columns kept (nullable, always NULL in
--     phase 1) so phase 2 can be added later without another ALTER TABLE

START TRANSACTION;

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
  `source` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'staff' COMMENT 'staff | guest (guest unused in phase 1)',
  `source_link_id` int DEFAULT NULL COMMENT 'reserved for phase-2 share links, always NULL in phase 1',
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
  `via` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'staff_upload' COMMENT 'staff_upload | restore (guest_upload unused in phase 1)',
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
  `node_id` int DEFAULT NULL COMMENT 'intentionally not FK',
  `link_id` int DEFAULT NULL COMMENT 'reserved for phase 2, always NULL in phase 1, intentionally not FK',
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

COMMIT;
