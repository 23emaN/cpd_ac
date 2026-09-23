<?php
require 'app/core/config.php';
$pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASS);
$stmt = $pdo->query('SELECT p.period_id, p.review1_status, p.review1_user_id, u.user_firstname as r1_firstname FROM tbl_customer_work_periods p LEFT JOIN tbl_user u ON p.review1_user_id = u.user_id LIMIT 10');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
