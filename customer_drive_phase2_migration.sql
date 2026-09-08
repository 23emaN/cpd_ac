-- customer_drive_phase2_migration.sql
-- Customer Drive (phase 2: share-link guest portal)
-- Adds tbl_cd_link and tbl_cd_link_session (phase 1 deferred these).
-- Adapted from _export_customer_drive/db/admin_audit (2).sql:
--   * FK target changed from tbl_customer -> tbl_customers

START TRANSACTION;

CREATE TABLE `tbl_cd_link` (
  `link_id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `token` char(43) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'random_bytes(32) base64url',
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `mode` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'collect' COMMENT 'collect | share',
  `root_node_id` int DEFAULT NULL COMMENT 'NULL = whole drive',
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
