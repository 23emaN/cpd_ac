ALTER TABLE tbl_customer_work_periods
ADD COLUMN doc_date DATE DEFAULT NULL COMMENT 'วันที่ได้รับเอกสาร',
ADD COLUMN completed_date DATE DEFAULT NULL COMMENT 'วันที่ทำเสร็จ',
ADD COLUMN tax_date DATE DEFAULT NULL COMMENT 'วันที่ยื่นภาษี';
