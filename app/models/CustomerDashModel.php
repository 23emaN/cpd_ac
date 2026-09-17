<?php
require_once '../app/models/Model.php';

class CustomerDashModel extends Model
{
    public function getCustomerWorkDashboard($fiscalId, $month = null)
    {
        $params = ['fiscal_id' => $fiscalId];
        $monthCondition = '';
        if ($month !== null) {
            $monthCondition = ' AND p.period_month = :month ';
            $params['month'] = str_pad($month, 2, '0', STR_PAD_LEFT);
        }

        $sql = "SELECT
                    c.customer_id,
                    c.customer_name,
                    u.user_firstname AS caretaker_firstname,
                    u.user_lastname AS caretaker_lastname,
                    t.team_name,
                    COALESCE(MAX(fyc.accounts_amount), 0) AS accounts_amount,
                    COALESCE(MAX(fyc.closing_amount), 0) AS closing_amount,
                    COALESCE(MAX(fyc.auditing_amount), 0) AS auditing_amount,
                    COUNT(DISTINCT p.period_id) AS work_months,
                    SUM((
                        SELECT COUNT(*)
                        FROM tbl_customer_tasks ct_total
                        WHERE ct_total.period_id = p.period_id
                          AND ct_total.delete_at IS NULL
                    )) AS total_tasks,
                    SUM((
                        SELECT COUNT(*)
                        FROM tbl_customer_tasks ct_done
                        WHERE ct_done.period_id = p.period_id
                          AND ct_done.status = '1'
                          AND ct_done.delete_at IS NULL
                    )) AS completed_tasks,
                    SUM(CASE WHEN (
                        (SELECT COUNT(*) FROM tbl_customer_tasks ct_all
                         WHERE ct_all.period_id = p.period_id AND ct_all.delete_at IS NULL) > 0
                        AND
                        (SELECT COUNT(*) FROM tbl_customer_tasks ct_finished
                         WHERE ct_finished.period_id = p.period_id
                           AND ct_finished.status = '1'
                           AND ct_finished.delete_at IS NULL)
                        =
                        (SELECT COUNT(*) FROM tbl_customer_tasks ct_expected
                         WHERE ct_expected.period_id = p.period_id AND ct_expected.delete_at IS NULL)
                    ) THEN 1 ELSE 0 END) AS completed_months
                FROM tbl_customer_work_periods p
                INNER JOIN tbl_customers c ON p.customer_id = c.customer_id
                LEFT JOIN tbl_fiscal_year_customers fyc
                    ON fyc.customer_id = p.customer_id AND fyc.fiscal_id = p.fiscal_year_id
                LEFT JOIN tbl_user u ON fyc.user_id = u.user_id
                LEFT JOIN tbl_team t ON fyc.team_id = t.team_id
                WHERE p.fiscal_year_id = :fiscal_id
                  $monthCondition
                  AND p.delete_at IS NULL
                  AND c.delete_at IS NULL
                GROUP BY c.customer_id, c.customer_name, u.user_firstname, u.user_lastname, t.team_name
                ORDER BY c.customer_name ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCustomerWorkDetails($fiscalId, $customerId)
    {
        $sql = "SELECT
                    p.period_id,
                    p.period_month,
                    p.doc_status,
                    p.tax_status,
                    p.payment_status,
                    c.customer_name,
                    t.tasks_name,
                    ct.customer_tasks_id,
                    ct.status AS task_status,
                    ct.amount
                FROM tbl_customer_work_periods p
                INNER JOIN tbl_customers c ON p.customer_id = c.customer_id
                LEFT JOIN tbl_customer_tasks ct
                    ON ct.period_id = p.period_id AND ct.delete_at IS NULL
                LEFT JOIN tbl_tasks t ON t.tasks_id = ct.task_id
                WHERE p.fiscal_year_id = :fiscal_id
                  AND p.customer_id = :customer_id
                  AND p.delete_at IS NULL
                  AND c.delete_at IS NULL
                  AND ct.customer_tasks_id IS NOT NULL
                ORDER BY CAST(p.period_month AS UNSIGNED), t.list_order ASC, t.tasks_id ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'fiscal_id' => $fiscalId,
            'customer_id' => (int) $customerId,
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
