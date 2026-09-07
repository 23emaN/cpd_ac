<?php
require_once '../app/models/Model.php';

class MonthlyTaskModal extends Model
{

    public function getMonthlyTasks($fiscalId, $month = null)
    {
        $sql = "
            SELECT 
                p.period_id,
                p.period_month,
                p.doc_status,
                p.tax_status,
                p.payment_status,
                p.review1_status,
                p.review2_status,
                p.review3_status,
                c.customer_id,
                c.customer_name,
                c.rn_user,
                c.dbd_user,
                c.sso_user,
                fyc.accounts_amount,
                t.team_name,
                u.user_firstname as caretaker_firstname,
                (SELECT COUNT(*) FROM tbl_customer_tasks ct WHERE ct.period_id = p.period_id) as total_tasks,
                (SELECT COUNT(*) FROM tbl_customer_tasks ct WHERE ct.period_id = p.period_id AND ct.status = '1') as completed_tasks,
                (SELECT COUNT(*) FROM tbl_comment_tasks cmt INNER JOIN tbl_customer_tasks ct2 ON cmt.customer_tasks_id = ct2.customer_tasks_id WHERE ct2.period_id = p.period_id) as total_comments
            FROM tbl_customer_work_periods p
            INNER JOIN tbl_customers c ON p.customer_id = c.customer_id
            LEFT JOIN tbl_fiscal_year_customers fyc ON p.customer_id = fyc.customer_id AND p.fiscal_year_id = fyc.fiscal_id
            LEFT JOIN tbl_user u ON fyc.user_id = u.user_id
            LEFT JOIN tbl_team t ON fyc.team_id = t.team_id
            WHERE p.fiscal_year_id = :fiscal_id AND c.delete_at IS NULL
        ";

        $params = ['fiscal_id' => $fiscalId];

        if ($month !== null && $month !== '') {
            $sql .= " AND (p.period_month = :month OR CAST(p.period_month AS UNSIGNED) = :month_int)";
            $params['month'] = str_pad($month, 2, '0', STR_PAD_LEFT);
            $params['month_int'] = (int) $month;
        }

        $sql .= " ORDER BY c.created_at ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTasksByPeriodId($period_id)
    {
        // ===== DEBUG START =====
        error_log("=== getTasksByPeriodId DEBUG ===");
        error_log("period_id = " . var_export($period_id, true));

       $sql = "
            SELECT 
                ct.customer_tasks_id,
                ct.period_id,
                ct.task_id,
                ct.status,
                ct.amount,
                t.tasks_name as task_name,
                t.is_notify_amount
            FROM tbl_customer_tasks ct
            INNER JOIN tbl_tasks t ON ct.task_id = t.tasks_id
            WHERE ct.period_id = :period_id
            ORDER BY t.list_order ASC
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['period_id' => $period_id]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            error_log("rows found = " . count($rows));
            error_log("result = " . json_encode($rows, JSON_UNESCAPED_UNICODE));
            // ===== DEBUG END =====

            return $rows;
        } catch (\PDOException $e) {
            error_log("PDOException: " . $e->getMessage());
            // ส่ง error กลับเป็น JSON เพื่อดูใน browser
            header('Content-Type: application/json');
            echo json_encode([
                'result' => 0,
                'debug_error' => $e->getMessage(),
                'debug_period_id' => $period_id,
            ]);
        }
    }
    public function updatePeriodData(int $periodId, array $data) {
        // Convert flatpickr dates (d/m/Y) to Y-m-d format
        $docDate = !empty($data['doc_date']) ? DateTime::createFromFormat('d/m/Y', $data['doc_date'])->format('Y-m-d') : null;
        $completedDate = !empty($data['completed_date']) ? DateTime::createFromFormat('d/m/Y', $data['completed_date'])->format('Y-m-d') : null;
        $taxDate = !empty($data['tax_date']) ? DateTime::createFromFormat('d/m/Y', $data['tax_date'])->format('Y-m-d') : null;

        $sql = "UPDATE tbl_customer_work_periods SET 
                doc_date = :doc_date,
                completed_date = :completed_date,
                tax_date = :tax_date,
                review1_status = :r1,
                review2_status = :r2,
                review3_status = :r3,
                payment_status = :payment,
                tax_status = :tax
                WHERE period_id = :id";
                
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'doc_date' => $docDate,
            'completed_date' => $completedDate,
            'tax_date' => $taxDate,
            'r1' => $data['review1_status'],
            'r2' => $data['review2_status'],
            'r3' => $data['review3_status'],
            'payment' => $data['payment_status'],
            'tax' => $data['tax_status'],
            'id' => $periodId
        ]);
    }

    public function updateTaskData(int $periodId, array $tasks) {
        $sql = "UPDATE tbl_customer_tasks SET status = :status, amount = :amount WHERE customer_tasks_id = :id AND period_id = :pid";
        $stmt = $this->pdo->prepare($sql);
        
        foreach ($tasks as $task) {
            $stmt->execute([
                'status' => $task['status'],
                'amount' => $task['amount'] ?? 0,
                'id' => $task['customer_tasks_id'],
                'pid' => $periodId
            ]);
        }
    }

    public function getCommentsByTaskId(int $customerTasksId) {
        $sql = "
            SELECT 
                c.comment_id,
                c.customer_tasks_id,
                c.comment_user_id,
                c.comment_detail AS comment_text,
                c.create_at,
                u.user_firstname AS user_name,
                DATE_FORMAT(c.create_at, '%d/%m/%Y %H:%i') AS created_at_display
            FROM tbl_comment_tasks c
            LEFT JOIN tbl_user u ON c.comment_user_id = u.user_id
            WHERE c.customer_tasks_id = :task_id
            ORDER BY c.create_at ASC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['task_id' => $customerTasksId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addComment(int $customerTasksId, int $userId, string $commentText) {
        $sql = "
            INSERT INTO tbl_comment_tasks (customer_tasks_id, comment_user_id, comment_detail, create_at)
            VALUES (:task_id, :user_id, :comment_text, NOW())
        ";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'task_id' => $customerTasksId,
            'user_id' => $userId,
            'comment_text' => $commentText
        ]);
    }
}

