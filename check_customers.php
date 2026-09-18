<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/config/Connection.php';
$pdo = \App\config\Connection::getInstance()->getPdo();
$stmt = $pdo->query("SHOW COLUMNS FROM tbl_customers");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
