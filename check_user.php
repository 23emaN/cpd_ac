<?php
require 'app/config/config.php';
$pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASS);
$stmt = $pdo->query("SELECT user_id, user_firstname FROM tbl_user WHERE user_id = 1");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
