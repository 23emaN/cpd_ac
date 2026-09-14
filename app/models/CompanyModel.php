<?php
require_once '../app/models/Model.php';

class CompanyModel extends Model {

    public function insertCompany($companyName, $userId) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO tbl_companies (
                company_name,
                user_id,
                created_at
                ) 
            VALUES (
                :name,
                :user_id,
                NOW()
            )"
        );
        return $stmt->execute([
            'name' => $companyName,
            'user_id' => $userId
        ]);
    }

    public function updateCompany($companyId, $companyName, $userId) {
        $stmt = $this->pdo->prepare(
            "UPDATE tbl_companies 
             SET company_name = :name
             WHERE company_id = :company_id AND user_id = :user_id"
        );
        return $stmt->execute([
            'name' => $companyName,
            'company_id' => $companyId,
            'user_id' => $userId
        ]);
    }

    public function getAllCompanies($userId = null) {
        if ($userId) {
            // หาบริษัทจาก fiscal_id ที่ user ผูกไว้ใน tbl_user (ตามที่คุณต้องการ เพื่อหา company_id)
            $stmt = $this->pdo->prepare("
                SELECT c.* 
                FROM tbl_companies c
                JOIN tbl_fiscal_years fy ON c.company_id = fy.company_id
                JOIN tbl_user u ON fy.fiscal_id = u.fiscal_id
                WHERE u.user_id = :user_id AND fy.active_status = 1
                GROUP BY c.company_id
                ORDER BY c.created_at DESC
            ");
            $stmt->execute(['user_id' => $userId]);
        } else {
            $stmt = $this->pdo->prepare("SELECT * FROM tbl_companies ORDER BY created_at DESC");
            $stmt->execute();
        }
        $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($companies as &$company) {
            $cId = $company['company_id'] ?? $company['id'] ?? 0;
            
            // ดึง "ทุกปีทำงาน" ของบริษัทนี้ ที่มี active_status = 1 เพื่อให้ไปแสดงใน dropdown
            $stmtFy = $this->pdo->prepare(
                "SELECT 
                    tbl_fiscal_years.*,
                    COUNT(tbl_fiscal_year_customers.customer_id) AS customer_count
                FROM tbl_fiscal_years
                LEFT JOIN tbl_fiscal_year_customers ON tbl_fiscal_years.fiscal_id = tbl_fiscal_year_customers.fiscal_id
                WHERE tbl_fiscal_years.company_id = :company_id
                  AND tbl_fiscal_years.active_status = 1
                GROUP BY tbl_fiscal_years.fiscal_id
                ORDER BY tbl_fiscal_years.fiscal_years DESC"
            );
            $stmtFy->execute(['company_id' => $cId]);
            
            $company['fiscal_years'] = $stmtFy->fetchAll(PDO::FETCH_ASSOC);
        }

        return $companies;
    }
}
