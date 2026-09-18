<?php
require_once '../app/models/Model.php';

class MonthlyTaskModal extends Model
{

    public function getReviewUsers()
    {
        $sql = "
            SELECT
                user_id,
                user_firstname,
                user_lastname
            FROM tbl_user
            WHERE delete_at IS NULL
              AND user_status = '1'
            ORDER BY user_firstname ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAvailableMonths($fiscalId)
    {
        $sql = "SELECT DISTINCT CAST(period_month AS UNSIGNED) AS month_num 
                FROM tbl_customer_work_periods 
                WHERE fiscal_year_id = :fiscal_id 
                  AND delete_at IS NULL 
                ORDER BY month_num ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['fiscal_id' => $fiscalId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getMonthlyTaskCustomers($fiscalId)
    {
        $sql = "SELECT DISTINCT
                    c.customer_id,
                    c.customer_name
                FROM tbl_customer_work_periods p
                INNER JOIN tbl_customers c
                    ON p.customer_id = c.customer_id
                LEFT JOIN tbl_fiscal_year_customers fyc
                    ON p.customer_id = fyc.customer_id
                    AND p.fiscal_year_id = fyc.fiscal_id
                WHERE p.fiscal_year_id = :fiscal_id
                  AND p.delete_at IS NULL
                  AND c.delete_at IS NULL
                ORDER BY c.customer_name ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['fiscal_id' => $fiscalId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCaretakersByFiscalId($fiscalId)
    {
        $sql = "SELECT DISTINCT
                    u.user_id,
                    u.user_firstname,
                    u.user_lastname
                FROM tbl_fiscal_year_user fyu
                INNER JOIN tbl_user u
                    ON fyu.user_id = u.user_id
                WHERE fyu.fiscal_id = :fiscal_id
                  AND u.user_status = '1'
                  AND u.is_super_admin = '0'
                  AND u.delete_at IS NULL
                ORDER BY u.user_firstname ASC, u.user_lastname ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['fiscal_id' => $fiscalId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMonthlyTaskStats($fiscalId, $month = null, $customerId = null, $caretakerId = null, $docStatus = null, $taskStatus = null, $taxStatus = null, $paymentStatus = null, $keyword = '')
    {
        $sql = "SELECT
                    COUNT(DISTINCT p.customer_id) AS total_customers,
                    COUNT(DISTINCT CASE WHEN p.doc_status = '1' THEN p.period_id END) AS doc_received,
                    COUNT(DISTINCT CASE WHEN p.tax_status = '1' THEN p.period_id END) AS tax_submitted,
                    COUNT(DISTINCT CASE WHEN p.payment_status = '1' THEN p.period_id END) AS payment_received,
                    COUNT(DISTINCT CASE WHEN (
                        (SELECT COUNT(*)
                         FROM tbl_customer_tasks ct_total
                         WHERE ct_total.period_id = p.period_id
                           AND ct_total.delete_at IS NULL) > 0
                        AND
                        (SELECT COUNT(*)
                         FROM tbl_customer_tasks ct_done
                         WHERE ct_done.period_id = p.period_id
                           AND ct_done.status = '1'
                           AND ct_done.delete_at IS NULL)
                        =
                        (SELECT COUNT(*)
                         FROM tbl_customer_tasks ct_all
                         WHERE ct_all.period_id = p.period_id
                           AND ct_all.delete_at IS NULL)
                    ) THEN p.period_id END) AS completed
                FROM tbl_customer_work_periods p
                INNER JOIN tbl_customers c
                    ON p.customer_id = c.customer_id
                WHERE p.fiscal_year_id = :fiscal_id
                  AND p.delete_at IS NULL
                  AND c.delete_at IS NULL";

        $params = ['fiscal_id' => $fiscalId];

        if ($month !== null && $month !== '') {
            $sql .= " AND (p.period_month = :month OR CAST(p.period_month AS UNSIGNED) = :month_int)";
            $params['month'] = str_pad($month, 2, '0', STR_PAD_LEFT);
            $params['month_int'] = (int) $month;
        }

        if ($customerId !== null && $customerId !== '') {
            $sql .= " AND p.customer_id = :customer_id";
            $params['customer_id'] = (int) $customerId;
        }

        if ($keyword !== '') {
            $sql .= " AND (
                        c.customer_name LIKE :keyword_customer
                        OR EXISTS (
                            SELECT 1
                            FROM tbl_fiscal_year_customers fyc_search
                            LEFT JOIN tbl_user u_search ON fyc_search.user_id = u_search.user_id
                            LEFT JOIN tbl_team t_search ON fyc_search.team_id = t_search.team_id
                            WHERE fyc_search.customer_id = p.customer_id
                              AND fyc_search.fiscal_id = p.fiscal_year_id
                              AND (
                                  CONCAT_WS(' ', u_search.user_firstname, u_search.user_lastname) LIKE :keyword_user
                                  OR t_search.team_name LIKE :keyword_team
                              )
                        )
                    )";
            $searchValue = '%' . $keyword . '%';
            $params['keyword_customer'] = $searchValue;
            $params['keyword_user'] = $searchValue;
            $params['keyword_team'] = $searchValue;
        }

        if ($caretakerId !== null && $caretakerId !== '') {
                        $sql .= " AND EXISTS (
                                                SELECT 1
                                                FROM tbl_fiscal_year_customers fyc_filter
                                                WHERE fyc_filter.customer_id = p.customer_id
                                                    AND fyc_filter.fiscal_id = p.fiscal_year_id
                                                    AND fyc_filter.user_id = :caretaker_id
                                        )";
            $params['caretaker_id'] = (int) $caretakerId;
        }
        if ($docStatus === '1') $sql .= " AND p.doc_status = '1'";
        if ($docStatus === '2') $sql .= " AND p.doc_status <> '1'";
        if ($taxStatus === '1') $sql .= " AND p.tax_status = '1'";
        if ($taxStatus === '2') $sql .= " AND p.tax_status <> '1'";
        if ($paymentStatus === '1') $sql .= " AND p.payment_status = '1'";
        if ($paymentStatus === '2') $sql .= " AND p.payment_status <> '1'";
        if ($taskStatus === '1') {
            $sql .= " AND (SELECT COUNT(*) FROM tbl_customer_tasks ct_filter WHERE ct_filter.period_id = p.period_id AND ct_filter.delete_at IS NULL) > 0
                      AND (SELECT COUNT(*) FROM tbl_customer_tasks ct_filter_done WHERE ct_filter_done.period_id = p.period_id AND ct_filter_done.status = '1' AND ct_filter_done.delete_at IS NULL)
                      = (SELECT COUNT(*) FROM tbl_customer_tasks ct_filter_all WHERE ct_filter_all.period_id = p.period_id AND ct_filter_all.delete_at IS NULL)";
        }
        if ($taskStatus === '2') {
            $sql .= " AND ((SELECT COUNT(*) FROM tbl_customer_tasks ct_filter WHERE ct_filter.period_id = p.period_id AND ct_filter.delete_at IS NULL) = 0
                      OR (SELECT COUNT(*) FROM tbl_customer_tasks ct_filter_done WHERE ct_filter_done.period_id = p.period_id AND ct_filter_done.status = '1' AND ct_filter_done.delete_at IS NULL)
                      < (SELECT COUNT(*) FROM tbl_customer_tasks ct_filter_all WHERE ct_filter_all.period_id = p.period_id AND ct_filter_all.delete_at IS NULL))";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'total_customers' => (int) ($stats['total_customers'] ?? 0),
            'doc_received' => (int) ($stats['doc_received'] ?? 0),
            'completed' => (int) ($stats['completed'] ?? 0),
            'tax_submitted' => (int) ($stats['tax_submitted'] ?? 0),
            'payment_received' => (int) ($stats['payment_received'] ?? 0),
        ];
    }

    public function getMonthlyTasks($fiscalId, $month = null, $userId = null, $customerId = null, $caretakerId = null, $docStatus = null, $taskStatus = null, $taxStatus = null, $paymentStatus = null, $keyword = '')

    {
        $sql = "SELECT
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
            c.cpd_name,
            c.cpa_name,
            fyc.accounts_amount,
            t.team_name,
            u.user_firstname as caretaker_firstname,

            (
                SELECT COUNT(*)
                FROM tbl_customer_tasks ct
                WHERE ct.period_id = p.period_id
                  AND ct.delete_at IS NULL
            ) as total_tasks,

            (
                SELECT COUNT(*)
                FROM tbl_customer_tasks ct
                WHERE ct.period_id = p.period_id
                  AND ct.status = '1'
                  AND ct.delete_at IS NULL
            ) as completed_tasks,

            (
                SELECT COUNT(DISTINCT cmt.customer_tasks_id)
                FROM tbl_comment_tasks cmt
                INNER JOIN tbl_customer_tasks ct2
                    ON cmt.customer_tasks_id = ct2.customer_tasks_id
                WHERE ct2.period_id = p.period_id
                  AND ct2.delete_at IS NULL
                  AND (ct2.status IS NULL OR ct2.status != '1')
                  AND cmt.is_reply = 0
                  AND (
                      SELECT c2.comment_user_id 
                      FROM tbl_comment_tasks c2
                      WHERE c2.customer_tasks_id = cmt.customer_tasks_id
                      ORDER BY c2.create_at DESC
                      LIMIT 1
                  ) != :user_id
            ) as unresolved_issues_count

        FROM tbl_customer_work_periods p

        INNER JOIN tbl_customers c
            ON p.customer_id = c.customer_id

        LEFT JOIN tbl_fiscal_year_customers fyc
            ON p.customer_id = fyc.customer_id
            AND p.fiscal_year_id = fyc.fiscal_id

        LEFT JOIN tbl_user u
            ON fyc.user_id = u.user_id

        LEFT JOIN tbl_team t
            ON fyc.team_id = t.team_id

        WHERE p.fiscal_year_id = :fiscal_id
          AND p.delete_at IS NULL
          AND c.delete_at IS NULL
    ";

        $params = [
            'fiscal_id' => $fiscalId,
            'user_id'   => $userId ?: 0,
        ];

        if ($month !== null && $month !== '') {
            $sql .= "
            AND (
                p.period_month = :month
                OR CAST(p.period_month AS UNSIGNED) = :month_int
            )
        ";

            $params['month'] = str_pad(
                $month,
                2,
                '0',
                STR_PAD_LEFT
            );

            $params['month_int'] = (int) $month;
        }

        if ($customerId !== null && $customerId !== '') {
            $sql .= " AND p.customer_id = :customer_id";
            $params['customer_id'] = (int) $customerId;
        }

        if ($keyword !== '') {
            $sql .= " AND (
                        c.customer_name LIKE :keyword_customer
                        OR CONCAT_WS(' ', u.user_firstname, u.user_lastname) LIKE :keyword_user
                        OR t.team_name LIKE :keyword_team
                    )";
            $searchValue = '%' . $keyword . '%';
            $params['keyword_customer'] = $searchValue;
            $params['keyword_user'] = $searchValue;
            $params['keyword_team'] = $searchValue;
        }

        if ($caretakerId !== null && $caretakerId !== '') {
            $sql .= " AND fyc.user_id = :caretaker_id";
            $params['caretaker_id'] = (int) $caretakerId;
        }
        if ($docStatus === '1') $sql .= " AND p.doc_status = '1'";
        if ($docStatus === '2') $sql .= " AND p.doc_status <> '1'";
        if ($taxStatus === '1') $sql .= " AND p.tax_status = '1'";
        if ($taxStatus === '2') $sql .= " AND p.tax_status <> '1'";
        if ($paymentStatus === '1') $sql .= " AND p.payment_status = '1'";
        if ($paymentStatus === '2') $sql .= " AND p.payment_status <> '1'";
        if ($taskStatus === '1') {
            $sql .= " AND (SELECT COUNT(*) FROM tbl_customer_tasks ct_filter WHERE ct_filter.period_id = p.period_id AND ct_filter.delete_at IS NULL) > 0
                      AND (SELECT COUNT(*) FROM tbl_customer_tasks ct_filter_done WHERE ct_filter_done.period_id = p.period_id AND ct_filter_done.status = '1' AND ct_filter_done.delete_at IS NULL)
                      = (SELECT COUNT(*) FROM tbl_customer_tasks ct_filter_all WHERE ct_filter_all.period_id = p.period_id AND ct_filter_all.delete_at IS NULL)";
        }
        if ($taskStatus === '2') {
            $sql .= " AND ((SELECT COUNT(*) FROM tbl_customer_tasks ct_filter WHERE ct_filter.period_id = p.period_id AND ct_filter.delete_at IS NULL) = 0
                      OR (SELECT COUNT(*) FROM tbl_customer_tasks ct_filter_done WHERE ct_filter_done.period_id = p.period_id AND ct_filter_done.status = '1' AND ct_filter_done.delete_at IS NULL)
                      < (SELECT COUNT(*) FROM tbl_customer_tasks ct_filter_all WHERE ct_filter_all.period_id = p.period_id AND ct_filter_all.delete_at IS NULL))";
        }

        $sql .= " ORDER BY c.created_at ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

   public function getTasksByPeriodId($period_id, $user_id = null)
{


    $sql = " SELECT 
            ct.customer_tasks_id,
            ct.period_id,
            ct.task_id,
            ct.status,
            ct.amount,
            t.tasks_name as task_name,
            t.is_notify_amount,

            (
                SELECT COUNT(DISTINCT c.customer_tasks_id)
                FROM tbl_comment_tasks c
                WHERE c.customer_tasks_id = ct.customer_tasks_id
                  AND (ct.status IS NULL OR ct.status != '1')
                  AND c.is_reply = 0
                  AND (
                      SELECT c2.comment_user_id 
                      FROM tbl_comment_tasks c2
                      WHERE c2.customer_tasks_id = c.customer_tasks_id
                      ORDER BY c2.create_at DESC
                      LIMIT 1
                  ) != :user_id
            ) as unread_comments

        FROM tbl_customer_tasks ct

        INNER JOIN tbl_tasks t
            ON ct.task_id = t.tasks_id

        WHERE ct.period_id = :period_id
          AND ct.delete_at IS NULL
          AND t.delete_at IS NULL

        ORDER BY t.list_order ASC
    ";

    try {

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'period_id' => $period_id,
            'user_id'   => $user_id ?: 0
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $rows;

    } catch (\PDOException $e) {
        throw $e;
    }
}

    public function getPeriodById($periodId)
    {
        $sql = "SELECT * FROM tbl_customer_work_periods WHERE period_id = :period_id";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['period_id' => $periodId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return null;
        }
    }

    public function getCustomerAccountsByPeriodId($periodId)
    {
        $sql = "SELECT
                    ca.account_id,
                    ca.account_name,
                    ca.account_user_name,
                    ca.account_password
                FROM tbl_customer_accounts ca
                INNER JOIN tbl_fiscal_year_customers fyc
                    ON ca.fiscal_year_id = fyc.fiscal_year_id
                INNER JOIN tbl_customer_work_periods p
                    ON ca.customer_id = p.customer_id
                    AND fyc.fiscal_id = p.fiscal_year_id
                WHERE p.period_id = :period_id
                ORDER BY ca.account_id ASC";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['period_id' => $periodId]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return [];
        }
    }


    public function updatePeriodData(int $periodId, array $data)
{
    // Convert flatpickr dates (d/m/Y) to Y-m-d format
    $docDate = !empty($data['doc_date'])
        ? DateTime::createFromFormat('d/m/Y', $data['doc_date'])->format('Y-m-d')
        : null;

    $completedDate = !empty($data['completed_date_1'])
        ? DateTime::createFromFormat('d/m/Y', $data['completed_date_1'])->format('Y-m-d')
        : null;

    $taxDate = !empty($data['tax_date_1'])
        ? DateTime::createFromFormat('d/m/Y', $data['tax_date_1'])->format('Y-m-d')
        : null;

    $completedDate2 = !empty($data['completed_date_2'])
        ? DateTime::createFromFormat('d/m/Y', $data['completed_date_2'])->format('Y-m-d')
        : null;

    $taxDate2 = !empty($data['tax_date_2'])
        ? DateTime::createFromFormat('d/m/Y', $data['tax_date_2'])->format('Y-m-d')
        : null;

    $sql = "UPDATE tbl_customer_work_periods SET
                doc_date = :doc_date,
                completed_date = :completed_date,
                tax_date = :tax_date,
                completed_date2 = :completed_date2,
                tax2_create_at = :tax_date2,

                review1_status = :r1,
                review1_user_id = :review1_user_id,

                review2_status = :r2,
                review2_user_id = :review2_user_id,

                review3_status = :r3,
                review3_user_id = :review3_user_id,

                payment_status = :payment,
                tax_status = :tax

            WHERE period_id = :id";

    $stmt = $this->pdo->prepare($sql);

    $stmt->execute([
        'doc_date' => $docDate,
        'completed_date' => $completedDate,
        'tax_date' => $taxDate,
        'completed_date2' => $completedDate2,
        'tax_date2' => $taxDate2,

        'r1' => $data['review1_status'] ?? '0',
        'review1_user_id' => !empty($data['review1_user_id'])
            ? (int)$data['review1_user_id']
            : null,

        'r2' => $data['review2_status'] ?? '0',
        'review2_user_id' => !empty($data['review2_user_id'])
            ? (int)$data['review2_user_id']
            : null,

        'r3' => $data['review3_status'] ?? '0',
        'review3_user_id' => !empty($data['review3_user_id'])
            ? (int)$data['review3_user_id']
            : null,

        'payment' => $data['payment_status'] ?? '0',
        'tax' => $data['tax_status'] ?? '0',

        'id' => $periodId,
    ]);
}

    public function updateTaskData(int $periodId, array $tasks)
    {
        $sql  = "UPDATE tbl_customer_tasks SET status = :status, amount = :amount WHERE customer_tasks_id = :id AND period_id = :pid";
        $stmt = $this->pdo->prepare($sql);

        foreach ($tasks as $task) {
            $stmt->execute([
                'status' => $task['status'],
                'amount' => $task['amount'] ?? 0,
                'id'     => $task['customer_tasks_id'],
                'pid'    => $periodId,
            ]);
        }
    }

    public function getCommentsByTaskId(int $customerTasksId)
    {
        $sql = "
            SELECT
                c.comment_id,
                c.customer_tasks_id,
                c.comment_user_id,
                c.comment_detail AS comment_text,
                c.create_at,
                TRIM(CONCAT_WS(' ', u.user_firstname, u.user_lastname)) AS user_name,
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

    public function addComment(int $customerTasksId, int $userId, string $commentText, int $isReply = 1)
    {
        $sql = "
            INSERT INTO tbl_comment_tasks (customer_tasks_id, comment_user_id, comment_detail, create_at, is_read, is_reply)
            VALUES (:task_id, :user_id, :comment_text, NOW(), 0, :is_reply)
        ";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'task_id'      => $customerTasksId,
            'user_id'      => $userId,
            'comment_text' => $commentText,
            'is_reply'     => $isReply
        ]);
    }

    public function markCommentsAsRead(int $customerTasksId, int $userId)
    {
        $sql  = "UPDATE tbl_comment_tasks SET is_read = 1 WHERE customer_tasks_id = :task_id AND comment_user_id != :user_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'task_id' => $customerTasksId,
            'user_id' => $userId,
        ]);
    }
}
