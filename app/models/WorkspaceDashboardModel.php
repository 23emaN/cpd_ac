<?php
class WorkspaceDashboardModel extends Model {

    /**
     * ดึงข้อมูลจำนวนลูกค้าในแต่ละบริษัท แบ่งตามปีงบประมาณ
     */
    public function getCustomerCountByCompany($userId, $isSuperAdmin) {
        $userCondition = "";
        $params = [];
        if (!$isSuperAdmin) {
            $userCondition = " INNER JOIN tbl_user_companies uc ON c.company_id = uc.company_id AND uc.user_id = :user_id ";
            $params[':user_id'] = $userId;
        }

        $sql = "
            SELECT 
                c.company_id, 
                c.company_name, 
                fy.fiscal_years as year, 
                COUNT(cust.customer_id) as total_customers
            FROM tbl_companies c
            $userCondition
            LEFT JOIN tbl_fiscal_years fy ON c.company_id = fy.company_id
            LEFT JOIN tbl_fiscal_year_customers fyc ON fy.fiscal_id = fyc.fiscal_id
            LEFT JOIN tbl_customers cust ON fyc.customer_id = cust.customer_id AND cust.delete_at IS NULL
            GROUP BY c.company_id, fy.fiscal_years
            ORDER BY c.company_id, fy.fiscal_years ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * ดึงข้อมูลค่าบริการ (ค่าทำบัญชี, ค่าปิดบัญชี, ค่าสอบบัญชี) ของแต่ละบริษัท
     */
    public function getAccountingFeesByCompany($userId, $isSuperAdmin) {
        $userCondition = "";
        $params = [];
        if (!$isSuperAdmin) {
            $userCondition = " INNER JOIN tbl_user_companies uc ON c.company_id = uc.company_id AND uc.user_id = :user_id ";
            $params[':user_id'] = $userId;
        }

        $sql = "
            SELECT 
                c.company_id, 
                c.company_name,
                fy.fiscal_years as year,
                COALESCE(SUM(fyc.accounts_amount), 0) as accounts_amount,
                COALESCE(SUM(fyc.closing_amount), 0) as closing_amount,
                COALESCE(SUM(fyc.auditing_amount), 0) as auditing_amount
            FROM tbl_companies c
            $userCondition
            LEFT JOIN tbl_fiscal_years fy ON c.company_id = fy.company_id
            LEFT JOIN tbl_fiscal_year_customers fyc ON fy.fiscal_id = fyc.fiscal_id
            LEFT JOIN tbl_customers cust ON fyc.customer_id = cust.customer_id AND cust.delete_at IS NULL
            WHERE cust.customer_id IS NOT NULL OR fyc.customer_id IS NULL
            GROUP BY c.company_id, fy.fiscal_years
            ORDER BY c.company_id, fy.fiscal_years ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * ดึงข้อมูลสถานะปิดงบประจำปี
     */
    public function getAnnualClosingByCompany($userId, $isSuperAdmin) {
        $userCondition = "";
        $params = [];
        if (!$isSuperAdmin) {
            $userCondition = " INNER JOIN tbl_user_companies uc ON c.company_id = uc.company_id AND uc.user_id = :user_id ";
            $params[':user_id'] = $userId;
        }

        $sql = "
            SELECT 
                c.company_id, 
                c.company_name,
                fy.fiscal_years as year,
                SUM(CASE WHEN cl.closing_status = '1' AND cust.customer_id IS NOT NULL THEN 1 ELSE 0 END) as closed_count,
                SUM(CASE WHEN (cl.closing_status != '1' OR cl.closing_status IS NULL) AND cust.customer_id IS NOT NULL THEN 1 ELSE 0 END) as pending_count
            FROM tbl_companies c
            $userCondition
            LEFT JOIN tbl_fiscal_years fy ON c.company_id = fy.company_id
            LEFT JOIN tbl_fiscal_year_customers fyc ON fy.fiscal_id = fyc.fiscal_id
            LEFT JOIN tbl_customers cust ON fyc.customer_id = cust.customer_id AND cust.delete_at IS NULL
            LEFT JOIN tbl_closing_financial cl ON fyc.fiscal_year_id = cl.fiscal_year_id
            GROUP BY c.company_id, fy.fiscal_years
            ORDER BY c.company_id, fy.fiscal_years ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * ดึงข้อมูลงานทะเบียน (เปิดอยู่, ปิดแล้ว, เลยกำหนด)
     */
    public function getRegistrationManagementByCompany($userId, $isSuperAdmin) {
        $userCondition = "";
        $params = [];
        if (!$isSuperAdmin) {
            $userCondition = " INNER JOIN tbl_user_companies uc ON c.company_id = uc.company_id AND uc.user_id = :user_id ";
            $params[':user_id'] = $userId;
        }

        $sql = "
            SELECT 
                c.company_id, 
                c.company_name,
                fy.fiscal_years as year,
                MONTH(reg.closed_at) as close_month,
                SUM(CASE WHEN reg.status != 'ปิดงาน' AND reg.delete_at IS NULL THEN 1 ELSE 0 END) as open_jobs,
                SUM(CASE WHEN reg.status != 'ปิดงาน' AND reg.due_date >= CURDATE() AND reg.delete_at IS NULL THEN 1 ELSE 0 END) as not_overdue,
                SUM(CASE WHEN reg.status != 'ปิดงาน' AND reg.due_date < CURDATE() AND reg.delete_at IS NULL THEN 1 ELSE 0 END) as overdue,
                SUM(CASE WHEN reg.status = 'ปิดงาน' AND reg.delete_at IS NULL THEN 1 ELSE 0 END) as closed_jobs
            FROM tbl_companies c
            $userCondition
            LEFT JOIN tbl_fiscal_years fy ON c.company_id = fy.company_id
            LEFT JOIN tbl_registration reg ON fy.fiscal_id = reg.fiscal_id
            GROUP BY c.company_id, fy.fiscal_years, MONTH(reg.closed_at)
            ORDER BY c.company_id, fy.fiscal_years ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * ดึงข้อมูลจำนวนงานทั้งหมด
     */
    public function getTotalJobsByCompany($userId, $isSuperAdmin) {
        $userCondition = "";
        $params = [];
        if (!$isSuperAdmin) {
            $userCondition = " INNER JOIN tbl_user_companies uc ON c.company_id = uc.company_id AND uc.user_id = :user_id ";
            $params[':user_id'] = $userId;
        }

        $sql = "
            SELECT 
                c.company_id, 
                c.company_name,
                fy.fiscal_years as year,
                COUNT(t.tasks_id) as total_jobs
            FROM tbl_companies c
            $userCondition
            LEFT JOIN tbl_fiscal_years fy ON c.company_id = fy.company_id
            LEFT JOIN tbl_tasks t ON fy.fiscal_id = t.fiscal_id AND t.delete_at IS NULL
            GROUP BY c.company_id, fy.fiscal_years
            ORDER BY c.company_id, fy.fiscal_years ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
