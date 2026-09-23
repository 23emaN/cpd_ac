<?php
// Mock public/index.php or just use PDO directly to check the table
$host = '127.0.0.1';
$db   = 'cpd_ac'; // guess
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $stmt = $pdo->query("DESCRIBE tbl_option_tax");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    // If not found, try to find the actual db name
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $stmt = $pdo->query("SHOW DATABASES");
    print_r($stmt->fetchAll(PDO::FETCH_COLUMN));
}
