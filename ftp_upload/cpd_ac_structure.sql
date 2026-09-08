-- cpd_ac database structure (tables only, no data)
-- Exported 2026-09-08 03:02:28
SET FOREIGN_KEY_CHECKS=0;

-- --------------------------------------------------------
-- Table: tbl_companies
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tbl_companies`;
CREATE TABLE `tbl_companies` (
  `company_id` int NOT NULL AUTO_INCREMENT,
  `company_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'ชื่อบริษัท',
  `user_id` int NOT NULL,
  `active_status` varchar(1) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0' COMMENT '0 = ไม่ใช้งาน\r\n1 = ใช้งาน',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`company_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table: tbl_customer
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tbl_customer`;
CREATE TABLE `tbl_customer` (
  `customer_id` int NOT NULL AUTO_INCREMENT,
  `customer_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'ชื่อบริษัท/กิจการ',
  `active_status` varchar(1) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '1',
  `customer_phone` int DEFAULT NULL COMMENT 'เบอร์มือถือ',
  `customer_email` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'email',
  `line_id` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'line id',
  `doc_folder_url` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'ลิง๕ืไว้ให้ลูกค้าส่งเอกสาร',
  `line_group_token` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table: tbl_customer_accounts
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tbl_customer_accounts`;
CREATE TABLE `tbl_customer_accounts` (
  `accounts` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `closing_status` varchar(1) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0' COMMENT '0 = ปิดงบประจำปี / 1 = ไม่ปิดงบ',
  `fiscal_closing_date` date NOT NULL COMMENT 'วันสิ้นรอบบัญชี',
  `is_vat` varchar(1) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0' COMMENT '0 = จด vat / 1 = ไม่จด vat',
  `is_employees` varchar(1) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0' COMMENT '0 = ไม่มีพนักงาน / 1 = มีพนักงาน',
  `is_social_security` varchar(1) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0' COMMENT '0 = ไม่มีประกันสังคม / 1 = มีประกันสังคม',
  `rn_user` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'user สรรมพากร',
  `rn_password` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'password สรรมพากร',
  `dbd_user` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'user กรมพัฒน์',
  `dbd_password` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'password กรมพัฒน์',
  `sso_user` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'user ประกันสังคม',
  `sso_password` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'password ประกันสังคม',
  `accounts_amount` int NOT NULL DEFAULT '0' COMMENT 'ค่าทำบัญชีต่อเดือน',
  PRIMARY KEY (`accounts`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table: tbl_customer_tasks
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tbl_customer_tasks`;
CREATE TABLE `tbl_customer_tasks` (
  `customer_tasks_id` int NOT NULL AUTO_INCREMENT,
  `fiscal_year_id` int NOT NULL,
  `task_id` int NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`customer_tasks_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table: tbl_fiscal_year_customers
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tbl_fiscal_year_customers`;
CREATE TABLE `tbl_fiscal_year_customers` (
  `fiscal_year_id` int NOT NULL AUTO_INCREMENT,
  `fiscal_id` int NOT NULL COMMENT 'ปีงบประมาณ',
  `customer_id` int NOT NULL COMMENT 'ลูกค้า',
  `service_start_date` varchar(2) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'เดือนที่เริ่มให้บิรการ\r\n1-12',
  `service_start_end` varchar(2) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'เดือนที่สิ้นสุดการให้บริการ\r\n1-12',
  `user_id` int DEFAULT NULL COMMENT 'พนักงานที่ดูแล',
  `team_id` int DEFAULT NULL COMMENT 'ทีมของพนักงาน',
  `created_at` datetime NOT NULL,
  `accounts_amount` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`fiscal_year_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table: tbl_fiscal_year_user
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tbl_fiscal_year_user`;
CREATE TABLE `tbl_fiscal_year_user` (
  `fiscal_employee_id` int NOT NULL AUTO_INCREMENT,
  `fiscal_id` int NOT NULL,
  `user_id` int NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`fiscal_employee_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table: tbl_fiscal_years
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tbl_fiscal_years`;
CREATE TABLE `tbl_fiscal_years` (
  `fiscal_id` int NOT NULL AUTO_INCREMENT,
  `company_id` int NOT NULL,
  `fiscal_years` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `active_status` varchar(1) COLLATE utf8mb4_general_ci NOT NULL COMMENT '0 = ไม่ใช้งาน\r\n1 = ใช้งาน',
  `create_at` datetime NOT NULL,
  PRIMARY KEY (`fiscal_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table: tbl_login_token
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tbl_login_token`;
CREATE TABLE `tbl_login_token` (
  `token_code` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL COMMENT 'FK เชื่อม tbl_user',
  `ip_address` varchar(20) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'IP ผู้ใช้งาน',
  `user_agent` text COLLATE utf8mb4_general_ci NOT NULL COMMENT 'ข้อมูลเครื่องของผู้ใช้',
  `expire_datetime` datetime NOT NULL COMMENT 'เวลาหมดอายุ ของ token',
  `end_datetime` datetime DEFAULT NULL COMMENT 'เวลา Logout (NULL = ยัง active)',
  `create_datetime` datetime NOT NULL COMMENT 'เวลาที่ถูกสร้าง',
  `last_active_at` datetime DEFAULT NULL COMMENT 'เวลาที่ใช้งานล่าสุด',
  PRIMARY KEY (`token_code`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table: tbl_post_it
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tbl_post_it`;
CREATE TABLE `tbl_post_it` (
  `post_id` int NOT NULL AUTO_INCREMENT,
  `fiscal_year_id` int NOT NULL,
  `title` text COLLATE utf8mb4_general_ci NOT NULL COMMENT 'หัวข้องาน',
  `user_id` int DEFAULT NULL,
  `due_date` date DEFAULT NULL COMMENT 'กำหนดส่ง',
  `status` varchar(1) COLLATE utf8mb4_general_ci NOT NULL COMMENT '0 = รอดำเนินการ / 1 = ปิดงานแล้ว',
  `content` text COLLATE utf8mb4_general_ci NOT NULL COMMENT 'เนื้อหางาน',
  `color_code` varchar(100) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'รหัสสี',
  `created_user_id` int NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`post_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table: tbl_registration
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tbl_registration`;
CREATE TABLE `tbl_registration` (
  `registration` int NOT NULL AUTO_INCREMENT,
  `fiscal_id` int NOT NULL,
  `registration_type_id` int NOT NULL,
  `customer_name` text COLLATE utf8mb4_general_ci NOT NULL,
  `customer_phone` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `contact_person` text COLLATE utf8mb4_general_ci NOT NULL COMMENT 'ผู้ต่อต่อ',
  `registration_name` text COLLATE utf8mb4_general_ci NOT NULL,
  `status` varchar(1) COLLATE utf8mb4_general_ci NOT NULL COMMENT '0 = รับงานลงทะเบียน\r\n1 = กำลังทำ\r\n2 = รอตรวจสอบ\r\n3 = ตรวจสอบแล้ว\r\n4 = กำลังไปยื่น\r\n5 = งานเสร็จเรียบร้อยแล้ว\r\n6 = เก็บเงินเรียบร้อยแล้ว',
  `description` text COLLATE utf8mb4_general_ci NOT NULL COMMENT 'รายละเอียดงาน',
  `service_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'ค่าบริการ',
  `urgency_level` varchar(1) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'ระดับความเร่งด่วน\r\n1 = ปกติ\r\n2 = เร่งด่วน\r\n3 = เร่งด่วนมาก',
  `accep_date` date DEFAULT NULL COMMENT 'วันที่รังาน',
  `due_date` date DEFAULT NULL COMMENT 'กำหนดส่ง',
  `assignee_user_id` int DEFAULT NULL COMMENT 'พนักงานที่รับผิดชอบ',
  `review_user_id` int DEFAULT NULL COMMENT 'ผู้ตรวจสอบ',
  `registration_no` text COLLATE utf8mb4_general_ci NOT NULL,
  `delete_at` datetime DEFAULT NULL,
  `delete_user_id` int DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `close_user_id` int DEFAULT NULL,
  PRIMARY KEY (`registration`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table: tbl_registration_task_setting
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tbl_registration_task_setting`;
CREATE TABLE `tbl_registration_task_setting` (
  `setting_id` int NOT NULL AUTO_INCREMENT,
  `fiscal_id` int NOT NULL,
  `notify_day` int NOT NULL DEFAULT '7' COMMENT 'ครบกำหนดภายใน',
  `update_at` datetime DEFAULT NULL COMMENT 'update ตอนไหน',
  `update_user_id` int DEFAULT NULL COMMENT 'ใครเป็นคนอัพเดท',
  PRIMARY KEY (`setting_id`),
  KEY `fiscal_id` (`fiscal_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------
-- Table: tbl_registration_type
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tbl_registration_type`;
CREATE TABLE `tbl_registration_type` (
  `registration_type_id` int NOT NULL AUTO_INCREMENT,
  `registration_type_name` text COLLATE utf8mb4_general_ci NOT NULL,
  `active_status` varchar(1) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '1' COMMENT 'ใช้งาน / ไม่ใช้งาน',
  `created_at` datetime NOT NULL,
  `create_user_id` int NOT NULL,
  `fiscal_id` int NOT NULL,
  `delete_at` datetime DEFAULT NULL,
  `delete_user_id` int DEFAULT NULL,
  PRIMARY KEY (`registration_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table: tbl_tasks
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tbl_tasks`;
CREATE TABLE `tbl_tasks` (
  `tasks_id` int NOT NULL AUTO_INCREMENT,
  `fiscal_id` int NOT NULL,
  `tasks_name` text COLLATE utf8mb4_general_ci NOT NULL,
  `is_notify_amount` varchar(1) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0' COMMENT 'ต้องระบุจำนวนเงินสำหรับแจ้งยอดผ่าน LINE ลูกค้า',
  `created_at` datetime NOT NULL,
  `list_order` int NOT NULL COMMENT 'ลำดับการแสดงผล',
  `delete_at` datetime DEFAULT NULL,
  PRIMARY KEY (`tasks_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table: tbl_team
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tbl_team`;
CREATE TABLE `tbl_team` (
  `team_id` int NOT NULL AUTO_INCREMENT,
  `team_name` text COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`team_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table: tbl_urgency_level
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tbl_urgency_level`;
CREATE TABLE `tbl_urgency_level` (
  `urgency_level_id` int NOT NULL AUTO_INCREMENT,
  `fiscal_id` int NOT NULL COMMENT 'เพื่อจับ ลูกค้า และ ปี',
  `urgency_level` varchar(1) NOT NULL,
  `label` varchar(50) NOT NULL COMMENT 'ข้อความ',
  `color` varchar(20) NOT NULL COMMENT 'สี',
  PRIMARY KEY (`urgency_level_id`),
  KEY `fiscal_id` (`fiscal_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------
-- Table: tbl_user
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tbl_user`;
CREATE TABLE `tbl_user` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `user_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `user_password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `user_firstname` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `user_lastname` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `user_status` varchar(1) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '1' COMMENT '1=ปกติ, 0=ระงับ',
  `create_at` datetime NOT NULL,
  `is_super_admin` varchar(1) COLLATE utf8mb4_general_ci NOT NULL COMMENT '0 = ไม่ใช่ supera dmin admin 1 = super admin',
  `position` text COLLATE utf8mb4_general_ci COMMENT 'ตำแหน่ง',
  `team_id` int DEFAULT NULL COMMENT 'รหัสทีม',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table: tbl_user_companies
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tbl_user_companies`;
CREATE TABLE `tbl_user_companies` (
  `id` int DEFAULT NULL,
  `user_id` int NOT NULL,
  `company_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET FOREIGN_KEY_CHECKS=1;
