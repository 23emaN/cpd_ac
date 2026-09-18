<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/app/config/Connection.php';

// Bypass auth and load notifications
$pdo = \App\config\Connection::getInstance()->getPdo();
$userId = '1'; // Assuming user 1 exists

require_once dirname(__DIR__) . '/app/models/NotificationModel.php';
$notifModel = new \App\Models\NotificationModel();

$notifications = $notifModel->getUnreadNotifications($userId, 20);
$count = $notifModel->getUnreadCount($userId);

echo json_encode([
    'result' => 1,
    'count' => $count,
    'data' => $notifications,
]);
