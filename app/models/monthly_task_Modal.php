<?php
require_once '../app/models/Model.php';

class MonthlyTaskModal extends Model {

    public function getMonthlyTasks($fiscalId, $month = null) {
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
                (SELECT COUNT(*) FROM tbl_customer_tasks ct WHERE ct.period_id = p.period_id AND ct.status = '1') as completed_tasks
            FROM tbl_customer_work_periods p
            INNER JOIN tbl_customers c ON p.customer_id = c.customer_id
            LEFT JOIN tbl_fiscal_year_customers fyc ON p.customer_id = fyc.customer_id AND p.fiscal_year_id = fyc.fiscal_id
            LEFT JOIN tbl_user u ON fyc.user_id = u.user_id
            LEFT JOIN tbl_team t ON fyc.team_id = t.team_id
            WHERE p.fiscal_year_id = :fiscal_id AND c.delete_at IS NULL
        ";

        $params = ['fiscal_id' => $fiscalId];

        if ($month !== null && $month !== '') {
            $sql .= " AND p.period_month = :month";
            $params['month'] = str_pad($month, 2, '0', STR_PAD_LEFT);
        }

        $sql .= " ORDER BY c.created_at ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTasksByPeriodId($period_id) {
        // ===== DEBUG START =====
        error_log("=== getTasksByPeriodId DEBUG ===");
        error_log("period_id = " . var_export($period_id, true));

        $sql = "
            SELECT 
                ct.customer_tasks_id,
                ct.period_id,
                ct.task_id,
                ct.status,
                t.tasks_name as task_name
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
            exit;
        }
    }
}
