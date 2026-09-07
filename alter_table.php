<?php
require_once __DIR__ . '/app/config/Connection.php';

try {
    $pdo = \App\Config\Connection::getInstance()->getPdo();
    $sql = "ALTER TABLE tbl_customer_work_periods
            ADD COLUMN doc_date DATE DEFAULT NULL,
            ADD COLUMN completed_date DATE DEFAULT NULL,
            ADD COLUMN tax_date DATE DEFAULT NULL;";
    $pdo->exec($sql);
    echo "Columns added successfully.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
