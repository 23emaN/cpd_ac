<?php
$host = '127.0.0.1';
$db = 'cpd_ac';
$user = 'root';
$pass = '';

echo "Host: $host, DB: $db, User: $user, Pass: (empty)\n";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    echo "Connected successfully!\n";
    
    $sql = "
    CREATE TABLE IF NOT EXISTS tbl_notification_settings (
        user_id VARCHAR(50) NOT NULL PRIMARY KEY,
        notify_days_advance INT NOT NULL DEFAULT 5,
        notify_email VARCHAR(255) NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE IF NOT EXISTS tbl_notifications (
        notif_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id VARCHAR(50) NOT NULL,
        task_type VARCHAR(50) NOT NULL,
        reference_id INT NULL,
        message TEXT NOT NULL,
        is_read TINYINT(1) NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        KEY user_id_idx (user_id),
        KEY is_read_idx (is_read)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $pdo->exec($sql);
    echo "Tables created successfully.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
