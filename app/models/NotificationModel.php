<?php
namespace App\Models;

require_once '../app/config/Connection.php';
use App\config\Connection;
use PDO;
use PDOException;

class NotificationModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Connection::getInstance()->getPdo();
    }

    /**
     * Add a new notification
     * 
     * @param string|int $user_id
     * @param string $task_type
     * @param int|null $reference_id
     * @param string $message
     * @return bool
     */
    public function addNotification($user_id, $task_type, $reference_id, $message)
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO tbl_notifications (
                                    user_id, 
                                    task_type, 
                                    reference_id, 
                                    message, 
                                    is_read, 
                                    created_at
                                    )
                                    VALUES (
                                    :user_id, 
                                    :task_type, 
                                    :reference_id, 
                                    :message, 
                                    0, 
                                    NOW()
                                    )
                                ");
            $stmt->execute([
                ':user_id' => $user_id,
                ':task_type' => $task_type,
                ':reference_id' => $reference_id,
                ':message' => $message
            ]);
            return true;
        } catch (PDOException $e) {
            error_log("Add Notification Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get unread notifications for a user
     * 
     * @param string|int $user_id
     * @param int $limit
     * @return array
     */
    public function getUnreadNotifications($user_id, $limit = 10)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM tbl_notifications 
                WHERE user_id = :user_id AND is_read = 0 
                ORDER BY created_at DESC 
                LIMIT :limit
            ");
            // Bind value for limit because PDO sometimes requires it to be an integer explicitly
            $stmt->bindValue(':user_id', $user_id);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Unread Notifications Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Mark a notification as read
     * 
     * @param int $notif_id
     * @param string|int $user_id
     * @return bool
     */
    public function markAsRead($notif_id, $user_id)
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE tbl_notifications 
                SET is_read = 1 
                WHERE notif_id = :notif_id AND user_id = :user_id
            ");
            $stmt->execute([
                ':notif_id' => $notif_id,
                ':user_id' => $user_id
            ]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Mark Notification Read Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mark all notifications as read for a user
     * 
     * @param string|int $user_id
     * @return bool
     */
    public function markAllAsRead($user_id)
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE tbl_notifications 
                SET is_read = 1 
                WHERE user_id = :user_id AND is_read = 0
            ");
            $stmt->execute([':user_id' => $user_id]);
            return true;
        } catch (PDOException $e) {
            error_log("Mark All Notifications Read Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get count of unread notifications
     * 
     * @param string|int $user_id
     * @return int
     */
    public function getUnreadCount($user_id)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as cnt FROM tbl_notifications 
                WHERE user_id = :user_id AND is_read = 0
            ");
            $stmt->execute([':user_id' => $user_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? (int)$row['cnt'] : 0;
        } catch (PDOException $e) {
            error_log("Get Unread Count Error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get all notifications for a user with pagination
     * 
     * @param string|int $user_id
     * @param int $page
     * @param int $per_page
     * @return array
     */
    public function getAllNotifications($user_id, $page = 1, $per_page = 20)
    {
        try {
            $offset = ($page - 1) * $per_page;
            $stmt = $this->pdo->prepare("
                SELECT * FROM tbl_notifications 
                WHERE user_id = :user_id 
                ORDER BY created_at DESC 
                LIMIT :limit OFFSET :offset
            ");
            $stmt->bindValue(':user_id', $user_id);
            $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get All Notifications Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get total count of all notifications for a user
     * 
     * @param string|int $user_id
     * @return int
     */
    public function getTotalCount($user_id)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as cnt FROM tbl_notifications 
                WHERE user_id = :user_id
            ");
            $stmt->execute([':user_id' => $user_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? (int)$row['cnt'] : 0;
        } catch (PDOException $e) {
            error_log("Get Total Count Error: " . $e->getMessage());
            return 0;
        }
    }
}
