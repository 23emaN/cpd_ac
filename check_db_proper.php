<?php
$host = 'localhost';
$db   = 'cpd_ac';
$user = 'root';
$pass = '';

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Check if user 1 exists
    $stmt = $pdo->query("SELECT user_id, user_firstname FROM tbl_user WHERE user_id IN (1, 4, 5)");
    echo "Users:\n";
    print_r($stmt->fetchAll());
    
    // Check work periods
    $stmt = $pdo->query("
        SELECT 
            p.period_id, 
            p.review1_user_id, 
            r1_user.user_firstname as r1_firstname
        FROM tbl_customer_work_periods p
        LEFT JOIN tbl_user r1_user ON p.review1_user_id = r1_user.user_id
        WHERE p.review1_status = '1'
        LIMIT 5
    ");
    echo "\nWork Periods JOIN:\n";
    print_r($stmt->fetchAll());
    
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
