<?php
require_once '../app/models/Model.php';

class IssuesModel extends Model {

    public function getAllIssues($fiscal_id, $user_id, $is_super_admin) {
        $sql = "
            SELECT 
                c.comment_id,
                c.customer_tasks_id,
                c.comment_detail AS comment_text,
                c.create_at,
                c.is_read,
                c.is_reply,
                TRIM(CONCAT_WS(' ', u.user_firstname, u.user_lastname)) AS user_name,
                ct.status AS task_status,
                ct.period_id,
                t.tasks_name,
                cust.customer_name,
                cust.customer_id,
                (
                    SELECT comment_user_id 
                    FROM tbl_comment_tasks last_c 
                    WHERE last_c.customer_tasks_id = c.customer_tasks_id 
                    ORDER BY last_c.create_at DESC 
                    LIMIT 1
                ) as latest_reply_user_id,
                (
                    SELECT comment_detail 
                    FROM tbl_comment_tasks last_c 
                    WHERE last_c.customer_tasks_id = c.customer_tasks_id 
                    ORDER BY last_c.create_at DESC 
                    LIMIT 1
                ) as latest_comment_text,
                (
                    SELECT TRIM(CONCAT_WS(' ', u2.user_firstname, u2.user_lastname)) 
                    FROM tbl_comment_tasks last_c 
                    LEFT JOIN tbl_user u2 ON last_c.comment_user_id = u2.user_id
                    WHERE last_c.customer_tasks_id = c.customer_tasks_id 
                    ORDER BY last_c.create_at DESC 
                    LIMIT 1
                ) as latest_user_name,
                (
                    SELECT create_at 
                    FROM tbl_comment_tasks last_c 
                    WHERE last_c.customer_tasks_id = c.customer_tasks_id 
                    ORDER BY last_c.create_at DESC 
                    LIMIT 1
                ) as latest_create_at
            FROM tbl_comment_tasks c
            LEFT JOIN tbl_user u ON c.comment_user_id = u.user_id
            INNER JOIN tbl_customer_tasks ct ON c.customer_tasks_id = ct.customer_tasks_id
            LEFT JOIN tbl_tasks t ON ct.task_id = t.tasks_id
            INNER JOIN tbl_fiscal_year_customers fyc ON ct.fiscal_year_id = fyc.fiscal_year_id
            LEFT JOIN tbl_customers cust ON fyc.customer_id = cust.customer_id
            WHERE fyc.fiscal_id = :fiscal_id
              AND ct.delete_at IS NULL
              AND cust.delete_at IS NULL
              AND c.is_reply = 0
        ";
        
        $params = ['fiscal_id' => $fiscal_id];
        
        if (!$is_super_admin) {
            $sql .= " AND fyc.user_id = :user_id";
            $params['user_id'] = $user_id;
        }

        $sql .= " ORDER BY c.create_at DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $all_issues = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $filtered_issues = [];
        foreach ($all_issues as $issue) {
            if ((int)$issue['latest_reply_user_id'] !== (int)$user_id) {
                $filtered_issues[] = $issue;
            }
        }
        
        return $filtered_issues;
    }
}
