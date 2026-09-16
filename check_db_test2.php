<?php
require_once 'c:/xampp/htdocs/cpd_ac_production/vendor/autoload.php';
require_once 'c:/xampp/htdocs/cpd_ac_production/app/config/Connection.php';
try {
    $pdo = \App\config\Connection::getInstance()->getPdo();
    $stmt = $pdo->query('SELECT * FROM tbl_notifications ORDER BY notif_id DESC LIMIT 5');
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
