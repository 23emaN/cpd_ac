<?php
require_once __DIR__ . '/app/config/Connection.php';

$pdo = \App\Config\Connection::getInstance()->getPdo();

echo "tbl_customer_work_periods:\n";
$stmt = $pdo->query("DESCRIBE tbl_customer_work_periods");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $c) {
    echo $c['Field'] . "\n";
}

echo "\ntbl_customer_tasks:\n";
$stmt2 = $pdo->query("DESCRIBE tbl_customer_tasks");
foreach ($stmt2->fetchAll(PDO::FETCH_ASSOC) as $c) {
    echo $c['Field'] . "\n";
}
