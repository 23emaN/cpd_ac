<?php

require_once '../app/models/Model.php';

class FiscalYearsModel extends Model {

    public function insertFiscalYears($companyId, $workingYear, $copyFromYear, $copyOptions = []) {
        // ตรวจสอบว่ามีบริษัทอยู่จริงหรือไม่
        $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM tbl_companies WHERE company_id = :company_id");
        $checkStmt->execute(['company_id' => $companyId]);
        if ($checkStmt->fetchColumn() == 0) {
            return false;
        }

        $stmt = $this->pdo->prepare(
            "INSERT INTO tbl_fiscal_years (
                company_id,
                fiscal_years,
                active_status,
                create_at
                )
            VALUES (
                :company_id,
                :working_year,
                '1',
                NOW()
            )"
        );
        $result = $stmt->execute([
            'company_id' => $companyId,
            'working_year' => $workingYear,
        ]);

        if (!$result) {
            return false;
        }

        $newFiscalId = $this->pdo->lastInsertId();

        // คัดลอกข้อมูลลูกค้า (chkCustomers)
        if (!empty($copyFromYear) && in_array('customers', $copyOptions)) {
            $this->copyCustomersToNewYear($copyFromYear, $newFiscalId);
        }

        // คัดลอกข้อมูลพนักงาน (chkEmployees)
        if (!empty($copyFromYear) && in_array('employees', $copyOptions)) {
            $this->copyEmployeesToNewYear($copyFromYear, $newFiscalId);
        }

        // คัดลอกตั้งค่างานรายเดือน (chkJobs)
        if (!empty($copyFromYear) && in_array('monthly_jobs', $copyOptions)) {
            $this->copyMonthlyJobsToNewYear($copyFromYear, $newFiscalId);
        }

        return $newFiscalId;
    }

    /**
     * ดึง customer_id ทั้งหมดที่ผูกกับ fiscal_id ที่ระบุ
     */
    public function getCustomersByFiscalYear($fiscalId) {
        $stmt = $this->pdo->prepare(
            "SELECT *
             FROM tbl_fiscal_year_customers
             WHERE fiscal_id = :fiscal_id"
        );
        $stmt->execute(['fiscal_id' => $fiscalId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * คัดลอกลูกค้าจาก fiscal year เดิมไปยัง fiscal year ใหม่
     */
    public function copyCustomersToNewYear($fromFiscalId, $toFiscalId) {
        $customers = $this->getCustomersByFiscalYear($fromFiscalId);

        if (empty($customers)) {
            return true;
        }

        $stmt = $this->pdo->prepare(
            "INSERT INTO tbl_fiscal_year_customers (
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
            )"
        );

        foreach ($customers as $row) {
            $stmt->execute([
                'fiscal_id'          => $toFiscalId,
                'customer_id'        => $row['customer_id'],
                'service_start_date' => $row['service_start_date'] ?? null,
                'service_start_end'  => $row['service_start_end']  ?? null,
                'user_id'            => !empty($row['user_id'])    ? $row['user_id']   : null,
                'team_id'            => !empty($row['team_id'])    ? $row['team_id']   : null,
                'accounts_amount'    => $row['accounts_amount']    ?? 0,
            ]);
        }

        return true;
    }

    /**
     * ดึง user_id ทั้งหมดที่ผูกกับ fiscal_id ที่ระบุ (tbl_fiscal_year_user)
     */
    public function getUsersByFiscalYear($fiscalId) {
        $stmt = $this->pdo->prepare(
            "SELECT user_id
             FROM tbl_fiscal_year_user
             WHERE fiscal_id = :fiscal_id"
        );
        $stmt->execute(['fiscal_id' => $fiscalId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * คัดลอกพนักงานจาก fiscal year เดิมไปยัง fiscal year ใหม่ (tbl_fiscal_year_user)
     */
    public function copyEmployeesToNewYear($fromFiscalId, $toFiscalId) {
        $users = $this->getUsersByFiscalYear($fromFiscalId);

        if (empty($users)) {
            return true;
        }

        $stmt = $this->pdo->prepare(
            "INSERT INTO tbl_fiscal_year_user (user_id, fiscal_id, created_at)
             VALUES (:user_id, :fiscal_id, NOW())"
        );

        foreach ($users as $row) {
            $stmt->execute([
                'user_id'  => $row['user_id'],
                'fiscal_id' => $toFiscalId,
            ]);
        }

        return true;
    }

    /**
     * คัดลอกตั้งค่างานรายเดือน (tbl_customer_work_periods + tbl_customer_tasks)
     * Flow:
     *  1. ดึงคู่ fiscal_year_id + customer_id จาก tbl_fiscal_year_customers ของปีเดิม
     *  2. ดึง work_periods ทั้งหมดของแต่ละ customer จาก fiscal_year_id เดิม
     *  3. INSERT period ใหม่ และเพิ่ม tasks จากปีเดิมไปยัง period ใหม่
     */
    public function copyMonthlyJobsToNewYear($fromFiscalId, $toFiscalId) {
        // 1. ดึงคู่ (fiscal_year_id, customer_id) จาก tbl_fiscal_year_customers ของ fiscal_id (=ปีเดิม)
        $stmtFyc = $this->pdo->prepare(
            "SELECT fiscal_year_id, customer_id
             FROM tbl_fiscal_year_customers
             WHERE fiscal_id = :fiscal_id"
        );
        $stmtFyc->execute(['fiscal_id' => $fromFiscalId]);
        $fycRows = $stmtFyc->fetchAll(PDO::FETCH_ASSOC);

        if (empty($fycRows)) {
            return true;
        }

        // ประเภทคำสั่ง INSERT period ใหม่
        $stmtPeriod = $this->pdo->prepare(
            "INSERT INTO tbl_customer_work_periods (
                customer_id,
                fiscal_year_id,
                period_month,
                doc_status,
                tax_status,
                payment_status,
                review1_status,
                review2_status,
                review3_status,
                created_at
            ) VALUES (
                :customer_id,
                :fiscal_year_id,
                :period_month,
                '0', '0', '0', '0', '0', '0',
                NOW()
            )"
        );

        // ประเภทคำสั่ง INSERT task ใหม่
        $stmtTask = $this->pdo->prepare(
            "INSERT INTO tbl_customer_tasks (
                fiscal_year_id, task_id, period_id, status, amount, created_at
            ) VALUES (
                :fiscal_year_id, :task_id, :period_id, '0', 0.00, NOW()
            )"
        );

        foreach ($fycRows as $fyc) {
            $oldFiscalYearId = $fyc['fiscal_year_id'];
            $customerId      = $fyc['customer_id'];

            // ดึง fiscal_year_id ใหม่ สำหรับ customer นี้จากปีใหม่
            $stmtNewFyc = $this->pdo->prepare(
                "SELECT fiscal_year_id
                 FROM tbl_fiscal_year_customers
                 WHERE fiscal_id = :fiscal_id AND customer_id = :customer_id
                 LIMIT 1"
            );
            $stmtNewFyc->execute([
                'fiscal_id'   => $toFiscalId,
                'customer_id' => $customerId,
            ]);
            $newFycRow = $stmtNewFyc->fetch(PDO::FETCH_ASSOC);

            // ถ้า customer นี้ไม่ได้ถูก copy ไปยังปีใหม่ (ไม่ได้ติ๊ก chkCustomers) ให้ข้าม
            if (empty($newFycRow)) {
                continue;
            }

            $newFiscalYearId = $newFycRow['fiscal_year_id'];

            // 2. ดึง work_periods ของ customer จากปีเดิม
            $stmtOldPeriods = $this->pdo->prepare(
                "SELECT period_id, period_month
                 FROM tbl_customer_work_periods
                 WHERE customer_id = :customer_id
                   AND fiscal_year_id = :fiscal_year_id
                 ORDER BY CAST(period_month AS UNSIGNED) ASC"
            );
            $stmtOldPeriods->execute([
                'customer_id'    => $customerId,
                'fiscal_year_id' => $oldFiscalYearId,
            ]);
            $oldPeriods = $stmtOldPeriods->fetchAll(PDO::FETCH_ASSOC);

            foreach ($oldPeriods as $period) {
                $oldPeriodId = $period['period_id'];

                // 3. INSERT period ใหม่
                $stmtPeriod->execute([
                    'customer_id'    => $customerId,
                    'fiscal_year_id' => $newFiscalYearId,
                    'period_month'   => $period['period_month'],
                ]);
                $newPeriodId = $this->pdo->lastInsertId();

                // 4. ดึง tasks จาก period เดิม
                $stmtOldTasks = $this->pdo->prepare(
                    "SELECT task_id
                     FROM tbl_customer_tasks
                     WHERE period_id = :period_id"
                );
                $stmtOldTasks->execute(['period_id' => $oldPeriodId]);
                $oldTasks = $stmtOldTasks->fetchAll(PDO::FETCH_ASSOC);

                // INSERT task ใหม่ โดยเริ่ม status = 0 ทุกงาน
                foreach ($oldTasks as $task) {
                    $stmtTask->execute([
                        'fiscal_year_id' => $newFiscalYearId,
                        'task_id'        => $task['task_id'],
                        'period_id'      => $newPeriodId,
                    ]);
                }
            }
        }

        return true;
    }

    public function getFiscalYearsByCompany($companyId) {
        $stmt = $this->pdo->prepare(
            "SELECT 
                tbl_fiscal_years.*,
                COUNT(tbl_fiscal_year_customers.customer_id) AS customer_count,
                COALESCE(SUM(tbl_fiscal_year_customers.accounts_amount), 0) AS monthly_fee
            FROM tbl_fiscal_years
            LEFT JOIN tbl_fiscal_year_customers ON tbl_fiscal_years.fiscal_id = tbl_fiscal_year_customers.fiscal_id
            WHERE tbl_fiscal_years.company_id = :company_id
            GROUP BY tbl_fiscal_years.fiscal_id
            ORDER BY tbl_fiscal_years.fiscal_years DESC"
        );
        $stmt->execute(['company_id' => $companyId]);
        return $stmt->fetchAll();
    }
}
