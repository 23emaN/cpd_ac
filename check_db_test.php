<?php
require_once 'c:/xampp/htdocs/cpd_ac_production/vendor/autoload.php';
require_once 'c:/xampp/htdocs/cpd_ac_production/app/config/Connection.php';
$pdo = \App\config\Connection::getInstance()->getPdo();
$stmt = $pdo->query('SELECT * FROM tbl_notifications ORDER BY notif_id DESC LIMIT 5');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
