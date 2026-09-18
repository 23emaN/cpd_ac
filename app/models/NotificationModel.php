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
    public function addNotification($user_id, $task_type, $reference_id, $message, $url_link = null)
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO tbl_notifications (
                                    user_id,
                                    task_type,
                                    reference_id,
                                    message,
                                    url_link,
                                    is_read,
                                    created_at
                                    )
                                    VALUES (
                                    :user_id,
                                    :task_type,
                                    :reference_id,
                                    :message,
                                    :url_link,
                                    0,
                                    NOW()
                                    )
                                ");
            $stmt->execute([
                ':user_id'      => $user_id,
                ':task_type'    => $task_type,
                ':reference_id' => $reference_id,
                ':message'      => $message,
                ':url_link'     => $url_link,
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
    public function getUnreadNotifications($user_id, $limit = 10, $fiscal_id = null)
    {
        try {
            $sql = "SELECT n.* FROM tbl_notifications n WHERE n.user_id = :user_id AND n.is_read = 0";
            if ($fiscal_id) {
                $sql .= " AND (
                    (n.task_type = 'assign_task' AND EXISTS (SELECT 1 FROM tbl_assign_task a WHERE a.assign_id = n.reference_id AND a.fiscal_id = :fiscal_id1))
                    OR 
                    (n.task_type = 'post_it' AND EXISTS (SELECT 1 FROM tbl_post_it p WHERE p.post_id = n.reference_id AND p.fiscal_year_id = :fiscal_id2))
                    OR 
                    (n.task_type NOT IN ('assign_task', 'post_it'))
                )";
            }
            $sql .= " ORDER BY n.created_at DESC LIMIT :limit";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':user_id', $user_id);
            if ($fiscal_id) {
                $stmt->bindValue(':fiscal_id1', $fiscal_id);
                $stmt->bindValue(':fiscal_id2', $fiscal_id);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

            $debug_msg = date('Y-m-d H:i:s') . " - getUnreadNotifications\n";
            $debug_msg .= "User ID: " . var_export($user_id, true) . "\n";
            $debug_msg .= "Fiscal ID: " . var_export($fiscal_id, true) . "\n";
            $debug_msg .= "SQL: " . $sql . "\n-------------------------\n";
            file_put_contents(__DIR__ . '/../../debug_notif.txt', $debug_msg, FILE_APPEND);

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
                ':user_id'  => $user_id,
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
    public function getUnreadCount($user_id, $fiscal_id = null)
    {
        try {
            $sql = "SELECT COUNT(*) as cnt FROM tbl_notifications n WHERE n.user_id = :user_id AND n.is_read = 0";
            if ($fiscal_id) {
                $sql .= " AND (
                    (n.task_type = 'assign_task' AND EXISTS (SELECT 1 FROM tbl_assign_task a WHERE a.assign_id = n.reference_id AND a.fiscal_id = :fiscal_id1))
                    OR 
                    (n.task_type = 'post_it' AND EXISTS (SELECT 1 FROM tbl_post_it p WHERE p.post_id = n.reference_id AND p.fiscal_year_id = :fiscal_id2))
                    OR 
                    (n.task_type NOT IN ('assign_task', 'post_it'))
                )";
            }
            $stmt = $this->pdo->prepare($sql);
            
            $stmt->bindValue(':user_id', $user_id);
            if ($fiscal_id) {
                $stmt->bindValue(':fiscal_id1', $fiscal_id);
                $stmt->bindValue(':fiscal_id2', $fiscal_id);
            }
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? (int) $row['cnt'] : 0;
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
    public function getAllNotifications($user_id, $page = 1, $per_page = 20, $fiscal_id = null, $read_status = null)
    {
        try {
            $offset = ($page - 1) * $per_page;
            
            $sql = "
                SELECT n.*,
                       CASE
                           WHEN n.task_type = 'assign_task' THEN (SELECT due_date FROM tbl_assign_task WHERE assign_id = n.reference_id LIMIT 1)
                           WHEN n.task_type = 'post_it' THEN (SELECT due_date FROM tbl_post_it WHERE post_id = n.reference_id LIMIT 1)
                           ELSE NULL
                       END as due_date
                FROM tbl_notifications n
                WHERE n.user_id = :user_id
            ";
            
            if ($read_status !== null && $read_status !== '') {
                $sql .= " AND n.is_read = :read_status";
            }

            
            if ($fiscal_id) {
                $sql .= " AND (
                    (n.task_type = 'assign_task' AND EXISTS (SELECT 1 FROM tbl_assign_task a WHERE a.assign_id = n.reference_id AND a.fiscal_id = :fiscal_id1))
                    OR 
                    (n.task_type = 'post_it' AND EXISTS (SELECT 1 FROM tbl_post_it p WHERE p.post_id = n.reference_id AND p.fiscal_year_id = :fiscal_id2))
                    OR 
                    (n.task_type NOT IN ('assign_task', 'post_it'))
                )";
            }
            
            $sql .= " ORDER BY n.created_at DESC LIMIT :limit OFFSET :offset";
            error_log("getAllNotifications executing for user: $user_id, fiscal_id: $fiscal_id");
            error_log("SQL: " . $sql);

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':user_id', $user_id);
            if ($read_status !== null && $read_status !== '') {
                $stmt->bindValue(':read_status', $read_status, PDO::PARAM_INT);
            }
            if ($fiscal_id) {
                $stmt->bindValue(':fiscal_id1', $fiscal_id);
                $stmt->bindValue(':fiscal_id2', $fiscal_id);
            }
            $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

            $debug_msg = date('Y-m-d H:i:s') . " - getAllNotifications\n";
            $debug_msg .= "User ID: " . var_export($user_id, true) . "\n";
            $debug_msg .= "Fiscal ID: " . var_export($fiscal_id, true) . "\n";
            $debug_msg .= "SQL: " . $sql . "\n-------------------------\n";
            file_put_contents(__DIR__ . '/../../debug_notif.txt', $debug_msg, FILE_APPEND);

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
    public function getTotalCount($user_id, $fiscal_id = null, $read_status = null)
    {
        try {
            $sql = "SELECT COUNT(*) as cnt FROM tbl_notifications n WHERE n.user_id = :user_id";
            if ($read_status !== null && $read_status !== '') {
                $sql .= " AND n.is_read = :read_status";
            }
            if ($fiscal_id) {
                $sql .= " AND (
                    (n.task_type = 'assign_task' AND EXISTS (SELECT 1 FROM tbl_assign_task a WHERE a.assign_id = n.reference_id AND a.fiscal_id = :fiscal_id1))
                    OR 
                    (n.task_type = 'post_it' AND EXISTS (SELECT 1 FROM tbl_post_it p WHERE p.post_id = n.reference_id AND p.fiscal_year_id = :fiscal_id2))
                    OR 
                    (n.task_type NOT IN ('assign_task', 'post_it'))
                )";
            }
            $stmt = $this->pdo->prepare($sql);
            
            $stmt->bindValue(':user_id', $user_id);
            if ($read_status !== null && $read_status !== '') {
                $stmt->bindValue(':read_status', $read_status, PDO::PARAM_INT);
            }
            if ($fiscal_id) {
                $stmt->bindValue(':fiscal_id1', $fiscal_id);
                $stmt->bindValue(':fiscal_id2', $fiscal_id);
            }
            
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? (int) $row['cnt'] : 0;
        } catch (PDOException $e) {
            error_log("Get Total Count Error: " . $e->getMessage());
            return 0;
        }
    }
}
