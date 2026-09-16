<?php
/**
 * Script สำหรับตรวจสอบและส่ง Web Push แจ้งเตือนงาน (tbl_assign_task) ที่ใกล้หมดอายุ
 * โดยจะแจ้งเตือนเมื่อเหลือเวลา 5, 4, 3, 2, และ 1 วัน
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/config/Connection.php';

// 1. ตั้งค่า timezone (สำคัญสำหรับการเช็ควันที่)
date_default_timezone_set('Asia/Bangkok');

try {
    $pdo = \App\config\Connection::getInstance()->getPdo();

    // 2. โหลด .env สำหรับ VAPID keys
    if (class_exists('Dotenv\Dotenv')) {
        \Dotenv\Dotenv::createImmutable(__DIR__)->safeLoad();
    }

    $vapidPublic = $_ENV['VAPID_PUBLIC_KEY'] ?? '';
    $vapidPrivate = $_ENV['VAPID_PRIVATE_KEY'] ?? '';
    $appUrl = $_ENV['APP_URL'] ?? 'http://localhost';

    if (empty($vapidPublic) || empty($vapidPrivate)) {
        die("Error: VAPID keys not found in .env\n");
    }

    // 3. ค้นหางานที่ยังไม่เสร็จ (สมมติว่า assign_status = '0') 
    // และ due_date ห่างจากวันนี้ 1, 2, 3, 4, หรือ 5 วัน
    $sql = "
        SELECT 
            t.assign_id, 
            t.assign_title, 
            t.due_date, 
            t.user_id,
            DATEDIFF(t.due_date, CURDATE()) as days_left
        FROM tbl_assign_task t
        WHERE t.assign_status = '0' 
          AND t.due_date IS NOT NULL
          AND DATEDIFF(t.due_date, CURDATE()) IN (1, 2, 3, 4, 5)
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($tasks)) {
        echo "No tasks expiring in 1-5 days.\n";
        exit;
    }

    // 4. ตั้งค่า Web Push
    $auth = [
        'VAPID' => [
            'subject'    => 'mailto:admin@' . ($_SERVER['HTTP_HOST'] ?? 'localhost'),
            'publicKey'  => $vapidPublic,
            'privateKey' => $vapidPrivate,
        ],
    ];
    $webPush = new \Minishlink\WebPush\WebPush($auth);
    $notificationsQueued = 0;

    // 5. เตรียมส่งแจ้งเตือนให้แต่ละงาน
    foreach ($tasks as $task) {
        $userId = $task['user_id'];
        $daysLeft = $task['days_left'];
        $title = "แจ้งเตือนงานใกล้หมดอายุ";
        $body = "งาน '{$task['assign_title']}' จะหมดอายุในอีก {$daysLeft} วัน (กำหนดส่ง: {$task['due_date']})";

        // บันทึกลงตาราง tbl_notifications (สำหรับแสดงในเมนูกระดิ่ง)
        try {
            $notifStmt = $pdo->prepare("
                INSERT INTO tbl_notifications (user_id, task_type, reference_id, message, is_read, created_at)
                VALUES (?, 'assign_task', ?, ?, 0, NOW())
            ");
            $notifStmt->execute([$userId, $task['assign_id'], $body]);
        } catch (Exception $e) {
            error_log("Failed to insert notification into DB: " . $e->getMessage());
        }

        // ค้นหา Subscription ของ User นี้ (สำหรับ Web Push)
        $subStmt = $pdo->prepare("SELECT endpoint, p256dh, auth FROM tbl_push_subscriptions WHERE user_id = ?");
        $subStmt->execute([$userId]);
        $subs = $subStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($subs as $sub) {
            $subscription = \Minishlink\WebPush\Subscription::create([
                'endpoint'  => $sub['endpoint'],
                'publicKey' => $sub['p256dh'],
                'authToken' => $sub['auth'],
            ]);
            
            $payload = json_encode([
                'title' => $title,
                'body'  => $body,
                'url'   => $appUrl . '/assign_task' // หรือ URL ของหน้ารายละเอียดงาน
            ]);

            $webPush->queueNotification($subscription, $payload);
            $notificationsQueued++;
        }
    }

    // 6. ส่ง Web Push รวดเดียวทั้งหมดและจัดการตัวที่ Error
    if ($notificationsQueued > 0) {
        foreach ($webPush->flush() as $report) {
            if (! $report->isSuccess()) {
                // ถ้า User ยกเลิกรับการแจ้งเตือน หรือ Token หมดอายุให้ลบออกจาก Database
                $endpoint = $report->getRequest()->getUri()->__toString();
                if ($report->getResponse() && in_array($report->getResponse()->getStatusCode(), [404, 410])) {
                    $delStmt = $pdo->prepare("DELETE FROM tbl_push_subscriptions WHERE endpoint = ?");
                    $delStmt->execute([$endpoint]);
                }
                error_log("WebPush Failed for {$endpoint}: " . $report->getReason());
            }
        }
        echo "Sent {$notificationsQueued} notifications successfully.\n";
    } else {
        echo "No valid push subscriptions found for the assigned users.\n";
    }

} catch (Exception $e) {
    die("Error: " . $e->getMessage() . "\n");
}
