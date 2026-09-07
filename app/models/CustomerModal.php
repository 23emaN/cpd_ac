<?php
require_once '../app/models/Model.php';

class CustomModal extends Model
{

    public function getTasks()
    {
        $stmt = $this->pdo->prepare("SELECT tasks_id,
                                            tasks_name
                                    FROM tbl_tasks
                                    WHERE delete_at IS NULL
                                    ORDER BY list_order ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getCaretakers()
    {
        $stmt = $this->pdo->prepare("
            SELECT u.*,
                   t.team_name
            FROM tbl_user u
            LEFT JOIN tbl_team t ON u.team_id = t.team_id
            WHERE u.user_status = 1 AND u.is_super_admin = 0
            ORDER BY u.user_firstname ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getCustomerByName($name)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM tbl_customers WHERE customer_name = :name");
        $stmt->execute(['name' => $name]);
        return $stmt->fetch();
    }

    public function insertCustomer($data)
    {
        // Handle date format from d/m/Y to Y-m-d
        $fiscal_closing_date = null;
        if (! empty($data['fiscal_closing_date'])) {
            $dateParts = explode('/', $data['fiscal_closing_date']);
            if (count($dateParts) == 3) {
                $fiscal_closing_date = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
            }
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO tbl_customers (
                customer_name,
                active_status,
                customer_phone,
                customer_email,
                line_id,
                line_group_token,
                doc_folder_url,
                closing_status,
                fiscal_closing_date,
                is_vat,
                is_employees,
                is_social_security,
                accounts_amount,
                rn_user,
                rn_password,
                dbd_user,
                dbd_password,
                sso_user,
                sso_password,
                created_at
            ) VALUES (
                :customer_name,
                :active_status,
                :contact_tel,
                :contact_email,
                :contact_line_id,
                :line_token,
                :doc_url,
                :closing_status,
                :fiscal_closing_date,
                :is_vat,
                :is_employees,
                :is_social_security,
                :accounts_amount,
                :rd_user,
                :rd_password,
                :dbd_user,
                :dbd_password,
                :sso_user,
                :sso_password,
                NOW()
            )
        ");

        $result = $stmt->execute([
            'customer_name'       => $data['customer_name'] ?? '',
            'active_status'       => $data['active_status'] ?? 1,
            'contact_tel'         => $data['contact_tel'] ?? null,
            'contact_email'       => $data['contact_email'] ?? null,
            'contact_line_id'     => $data['contact_line_id'] ?? null,
            'line_token'          => $data['line_token'] ?? null,
            'doc_url'             => $data['doc_url'] ?? null,
            'closing_status'      => $data['closing_status'] ?? 0,
            'fiscal_closing_date' => $fiscal_closing_date,
            'is_vat'              => $data['is_vat'] ?? 0,
            'is_employees'        => $data['is_employees'] ?? 0,
            'is_social_security'  => $data['is_social_security'] ?? 0,
            'accounts_amount'     => $data['accounts_amount'] ?? 0,
            'rd_user'             => $data['rd_user'] ?? null,
            'rd_password'         => $data['rd_password'] ?? null,
            'dbd_user'            => $data['dbd_user'] ?? null,
            'dbd_password'        => $data['dbd_password'] ?? null,
            'sso_user'            => $data['sso_user'] ?? null,
            'sso_password'        => $data['sso_password'] ?? null,
        ]);

        return $result ? $this->pdo->lastInsertId() : false;
    }

    public function linkCustomerToFiscalYear($customerId, $fiscalId, $data)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO tbl_fiscal_year_customers (
                fiscal_id,
                customer_id,
                service_start_date,
                service_start_end,
                user_id,
                team_id,
                accounts_amount,
                created_at
            ) VALUES (
                :fiscal_id,
                :customer_id,
                :service_start_date,
                :service_start_end,
                :user_id,
                :team_id,
                :accounts_amount,
                NOW()
            )
        ");

        $stmt->execute([
            'fiscal_id'          => $fiscalId,
            'customer_id'        => $customerId,
            'service_start_date' => $data['service_start_date'] ?? null,
            'service_start_end'  => $data['service_start_end'] ?? null,
            'user_id'            => ! empty($data['user_id']) ? $data['user_id'] : null,
            'team_id'            => ! empty($data['team_id']) ? $data['team_id'] : null,
            'accounts_amount'    => $data['accounts_amount'] ?? 0,
        ]);

        return $this->pdo->lastInsertId();
    }

    public function generateWorkPeriodsAndTasks($customerId, $fiscalYearId, $data)
    {
        $startMonth = (int) ($data['service_start_date'] ?? 1);
        $endMonth   = (int) ($data['service_start_end'] ?? 0);

        $monthsToGenerate = [];

        // กำหนดเดือนสิ้นสุด (ถ้าไม่ได้ระบุ, ระบุเป็น "ยังให้บริการอยู่", หรือระบุเป็นเดือนของปีถัดไป ให้จบที่เดือน 12 ของปีนี้)
        $actualEndMonth = 12;
        if ($endMonth > 0 && $endMonth >= $startMonth) {
            $actualEndMonth = $endMonth;
        }

        // สร้างลูปตั้งแต่เดือนที่เริ่ม จนถึงเดือนสิ้นสุด (สูงสุดไม่เกินเดือน 12)
        for ($m = $startMonth; $m <= $actualEndMonth; $m++) {
            $monthsToGenerate[] = $m;
        }

        // 3. บันทึกช่วงเวลาทำงาน (tbl_customer_work_periods) ตามเดือนที่คำนวณได้
        $periods = [];
        foreach ($monthsToGenerate as $month) {
            $stmt = $this->pdo->prepare("
                INSERT INTO tbl_customer_work_periods (
                    customer_id,
                    fiscal_year_id,
                    period_month,
                    doc_status,
                    tax_status,
                    payment_status,
                    created_at,
                    review1_status,
                    review2_status,
                    review3_status
                ) VALUES (
                    :customer_id,
                    :fiscal_year_id,
                    :period_month,
                    '0',
                    '0',
                    '0',
                    NOW(),
                    '0',
                    '0',
                    '0'
                )
            ");
            $stmt->execute([
                'customer_id'    => $customerId,
                'fiscal_year_id' => $fiscalYearId,
                'period_month'   => str_pad($month, 2, '0', STR_PAD_LEFT),
            ]);

            $periods[] = $this->pdo->lastInsertId();
        }

                                                     // 4. บันทึกงานรายเดือน (tbl_customer_tasks)
        $skippedTasks = $data['monthly_skip'] ?? []; // ค่าจาก checkbox งานที่ไม่ต้องทำ
        $allTasks     = $this->getTasks();

        foreach ($allTasks as $task) {
            $taskId = $task['tasks_id'] ?? 0;
            if (empty($taskId)) {
                continue;
            }

            // ถ้ารหัสงานอยู่ใน monthly_skip (งานที่ไม่ต้องทำ) ให้ข้ามไปไม่ต้องสร้าง task ในตาราง
            if (in_array($taskId, $skippedTasks)) {
                continue;
            }

            foreach ($periods as $periodId) {
                $stmt = $this->pdo->prepare("
                    INSERT INTO tbl_customer_tasks (
                        fiscal_year_id, task_id, period_id, status, amount, created_at
                    ) VALUES (
                        :fiscal_year_id, :task_id, :period_id, '0', 0.00, NOW()
                    )
                ");
                $stmt->execute([
                    'fiscal_year_id' => $fiscalYearId,
                    'task_id'        => $taskId,
                    'period_id'      => $periodId,
                ]);
            }
        }
    }

    public function getCustomersByFiscalId($fiscalId)
    {
        $stmt = $this->pdo->prepare("
            SELECT
                c.customer_id,
                c.customer_name,
                c.active_status,
                c.fiscal_closing_date,
                c.customer_phone,
                c.customer_email,
                c.line_id,
                fyc.accounts_amount,
                u.user_firstname as caretaker_firstname,
                u.user_lastname as caretaker_lastname,
                t.team_name
            FROM tbl_fiscal_year_customers fyc
            INNER JOIN tbl_customers c ON fyc.customer_id = c.customer_id
            LEFT JOIN tbl_user u ON fyc.user_id = u.user_id
            LEFT JOIN tbl_team t ON fyc.team_id = t.team_id
            WHERE fyc.fiscal_id = :fiscal_id AND delete_at IS NULL
            ORDER BY c.customer_name ASC
        ");
        $stmt->execute(['fiscal_id' => $fiscalId]);
        return $stmt->fetchAll();
    }

    public function getCustomersgid($fiscalId)
    {
        $stmt = $this->pdo->prepare("
            SELECT
                COUNT(fyc.customer_id) as total_customers,
                SUM(CASE WHEN c.active_status = 1 THEN 1 ELSE 0 END) as active_customers,
                SUM(CASE WHEN c.active_status = 0 THEN 1 ELSE 0 END) as inactive_customers,
                SUM(fyc.accounts_amount) as total_accounts_amount
            FROM tbl_fiscal_year_customers fyc
            INNER JOIN tbl_customers c ON fyc.customer_id = c.customer_id
            WHERE fyc.fiscal_id = :fiscal_id
        ");
        $stmt->execute(['fiscal_id' => $fiscalId]);
        return $stmt->fetch();
    }

    public function getCustomerDetails($customerId, $fiscalId)
    {
        $stmt = $this->pdo->prepare("
            SELECT
                c.*,
                f.service_start_date,
                f.service_start_end,
                f.user_id,
                f.team_id,
                f.accounts_amount as f_accounts_amount
            FROM tbl_customers c
            LEFT JOIN tbl_fiscal_year_customers f ON c.customer_id = f.customer_id AND f.fiscal_id = :fiscal_id
            WHERE c.customer_id = :customer_id
        ");
        $stmt->execute(['customer_id' => $customerId, 'fiscal_id' => $fiscalId]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        if (! $customer) {
            return false;
        }

        if (! empty($customer['fiscal_closing_date'])) {
            $customer['fiscal_closing_date'] = date('d/m/Y', strtotime($customer['fiscal_closing_date']));
        }

        $stmtTasks = $this->pdo->prepare("
            SELECT t.tasks_id
            FROM tbl_tasks t
            WHERE t.tasks_id NOT IN (
                SELECT DISTINCT ct.task_id
                FROM tbl_customer_tasks ct
                JOIN tbl_customer_work_periods p ON ct.period_id = p.period_id
                WHERE p.customer_id = :customer_id AND p.fiscal_year_id = :fiscal_id
            )
        ");
        $stmtTasks->execute(['customer_id' => $customerId, 'fiscal_id' => $fiscalId]);
        $skipped = $stmtTasks->fetchAll(PDO::FETCH_COLUMN);

        $customer['monthly_skip'] = $skipped;

        // --- DEBUG SQL ---
        $customer['debug_query'] = "
            SELECT t.tasks_id
            FROM tbl_tasks t
            WHERE t.tasks_id NOT IN (
                SELECT DISTINCT ct.task_id
                FROM tbl_customer_tasks ct
                JOIN tbl_customer_work_periods p ON ct.period_id = p.period_id
                WHERE p.customer_id = {$customerId} AND p.fiscal_year_id = {$fiscalId}
            )
        ";
        // -----------------

        return $customer;
    }

    public function updateCustomer($data)
    {
        $customerId = $data['customer_id'] ?? 0;
        $fiscalId   = $data['fiscal_id'] ?? 0;

        if (! $customerId || ! $fiscalId) {
            return false;
        }

        $fiscal_closing_date = null;
        if (! empty($data['fiscal_closing_date'])) {
            $dateParts = explode('/', $data['fiscal_closing_date']);
            if (count($dateParts) == 3) {
                $fiscal_closing_date = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
            }
        }

        $stmt1 = $this->pdo->prepare("
            UPDATE tbl_customers SET
                customer_name = :customer_name,
                active_status = :active_status,
                customer_phone = :contact_tel,
                customer_email = :contact_email,
                line_id = :contact_line_id,
                line_group_token = :line_token,
                doc_folder_url = :doc_url,
                closing_status = :closing_status,
                fiscal_closing_date = :fiscal_closing_date,
                is_vat = :is_vat,
                is_employees = :is_employees,
                is_social_security = :is_social_security,
                accounts_amount = :accounts_amount,
                rn_user = :rd_user,
                rn_password = :rd_password,
                dbd_user = :dbd_user,
                dbd_password = :dbd_password,
                sso_user = :sso_user,
                sso_password = :sso_password
            WHERE customer_id = :customer_id
        ");

        $stmt1->execute([
            'customer_name'       => $data['customer_name'] ?? '',
            'active_status'       => $data['active_status'] ?? 1,
            'contact_tel'         => $data['contact_tel'] ?? null,
            'contact_email'       => $data['contact_email'] ?? null,
            'contact_line_id'     => $data['contact_line_id'] ?? null,
            'line_token'          => $data['line_token'] ?? null,
            'doc_url'             => $data['doc_url'] ?? null,
            'closing_status'      => $data['closing_status'] ?? 0,
            'fiscal_closing_date' => $fiscal_closing_date,
            'is_vat'              => $data['is_vat'] ?? 0,
            'is_employees'        => $data['is_employees'] ?? 0,
            'is_social_security'  => $data['is_social_security'] ?? 0,
            'accounts_amount'     => $data['accounts_amount'] ?? 0,
            'rd_user'             => $data['rd_user'] ?? null,
            'rd_password'         => $data['rd_password'] ?? null,
            'dbd_user'            => $data['dbd_user'] ?? null,
            'dbd_password'        => $data['dbd_password'] ?? null,
            'sso_user'            => $data['sso_user'] ?? null,
            'sso_password'        => $data['sso_password'] ?? null,
            'customer_id'         => $customerId,
        ]);

        $stmt2 = $this->pdo->prepare("
            UPDATE tbl_fiscal_year_customers SET
                service_start_date = :service_start_date,
                service_start_end = :service_start_end,
                user_id = :user_id,
                team_id = :team_id,
                accounts_amount = :accounts_amount
            WHERE customer_id = :customer_id AND fiscal_id = :fiscal_id
        ");
        $stmt2->execute([
            'service_start_date' => $data['service_start_date'] ?? null,
            'service_start_end'  => $data['service_start_end'] ?? null,
            'user_id'            => ! empty($data['user_id']) ? $data['user_id'] : null,
            'team_id'            => ! empty($data['team_id']) ? $data['team_id'] : null,
            'accounts_amount'    => $data['accounts_amount'] ?? 0,
            'customer_id'        => $customerId,
            'fiscal_id'          => $fiscalId,
        ]);
        // --- ตรวจสอบและสร้างเดือน (Periods) ที่ยังไม่มี ---
        $startMonth = (int) ($data['service_start_date'] ?? 1);
        $endMonth   = (int) ($data['service_start_end'] ?? 0);

        $actualEndMonth = 12;
        if ($endMonth > 0 && $endMonth >= $startMonth) {
            $actualEndMonth = $endMonth;
        }

        // 1. เดือนที่ควรจะมีทั้งหมดตามที่ตั้งค่า
        $expectedMonths = [];
        for ($m = $startMonth; $m <= $actualEndMonth; $m++) {
            $expectedMonths[] = str_pad($m, 2, '0', STR_PAD_LEFT);
        }

        // 2. เดือนที่มีอยู่แล้วในฐานข้อมูล
        $existingMonthsStmt = $this->pdo->prepare("SELECT period_month FROM tbl_customer_work_periods WHERE customer_id = ? AND fiscal_year_id = ?");
        $existingMonthsStmt->execute([$customerId, $fiscalId]);
        $existingMonths = $existingMonthsStmt->fetchAll(PDO::FETCH_COLUMN);

        // 3. หาเดือนที่หายไป (ยังไม่เคยสร้าง)
        $missingMonths = array_diff($expectedMonths, $existingMonths);

        // 4. สร้างเดือนที่หายไป
        foreach ($missingMonths as $monthStr) {
            $stmt = $this->pdo->prepare("
                INSERT INTO tbl_customer_work_periods (
                    customer_id, fiscal_year_id, period_month,
                    doc_status, tax_status, payment_status, created_at,
                    review1_status, review2_status, review3_status
                ) VALUES (
                    ?, ?, ?, '0', '0', '0', NOW(), '0', '0', '0'
                )
            ");
            $stmt->execute([$customerId, $fiscalId, $monthStr]);
        }
        // ----------------------------------------------------

        $skippedTasks = $data['monthly_skip'] ?? [];

        if (! empty($skippedTasks)) {
            $inQuery = implode(',', array_fill(0, count($skippedTasks), '?'));
            $delStmt = $this->pdo->prepare("
                DELETE ct FROM tbl_customer_tasks ct
                JOIN tbl_customer_work_periods p ON ct.period_id = p.period_id
                WHERE p.customer_id = ? AND p.fiscal_year_id = ?
                AND ct.task_id IN ($inQuery) AND ct.status = '0'
            ");
            $params = array_merge([$customerId, $fiscalId], $skippedTasks);
            $delStmt->execute($params);
        }

        $periodsStmt = $this->pdo->prepare("SELECT period_id FROM tbl_customer_work_periods WHERE customer_id = ? AND fiscal_year_id = ?");
        $periodsStmt->execute([$customerId, $fiscalId]);
        $periods = $periodsStmt->fetchAll(PDO::FETCH_COLUMN);

        if (! empty($periods)) {
            $allTasks = $this->getTasks();
            foreach ($allTasks as $task) {
                $taskId = $task['tasks_id'] ?? 0;
                if (empty($taskId) || in_array($taskId, $skippedTasks)) {
                    continue;
                }

                foreach ($periods as $periodId) {
                    $checkStmt = $this->pdo->prepare("SELECT 1 FROM tbl_customer_tasks WHERE period_id = ? AND task_id = ?");
                    $checkStmt->execute([$periodId, $taskId]);
                    if (! $checkStmt->fetch()) {
                        $insStmt = $this->pdo->prepare("
                            INSERT INTO tbl_customer_tasks (fiscal_year_id, task_id, period_id, status, amount, created_at)
                            VALUES (?, ?, ?, '0', 0.00, NOW())
                        ");
                        $insStmt->execute([$fiscalId, $taskId, $periodId]);
                    }
                }
            }
        }

        return true;
    }

    public function deleteCustomer($customerId, $fiscalId)
    {
        // ใช้คำสั่ง UPDATE สำหรับ Soft Delete (เปลี่ยนคอลัมน์ให้ตรงกับฐานข้อมูล: delete_at)
        $stmt = $this->pdo->prepare("
            UPDATE tbl_customers
            SET delete_at = NOW()
            WHERE customer_id = :customer_id
        ");

        // Execute คำสั่งและส่งคืนค่า true หากทำสำเร็จ
        $success = $stmt->execute(['customer_id' => $customerId]);

        return $success;
    }
}
