<?php
require 'app/config/config.php';
$pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASS);
$stmt = $pdo->query('SELECT period_id, review1_status, review1_user_id, review2_status, review2_user_id FROM tbl_customer_work_periods LIMIT 10');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
