<?php
require_once '../app/models/Model.php';

class CompanyModel extends Model {

    public function isCompanyNameExists($companyName) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM tbl_companies WHERE company_name = :name");
        $stmt->execute(['name' => $companyName]);
        return $stmt->fetchColumn() > 0;
    }

    public function isCompanyNameExistsExcept($companyName, $excludeCompanyId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM tbl_companies WHERE company_name = :name AND company_id != :exclude_id");
        $stmt->execute([
            'name' => $companyName,
            'exclude_id' => $excludeCompanyId
        ]);
        return $stmt->fetchColumn() > 0;
    }

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
        $isSuperAdmin = 0;

        if ($userId) {
            $stmtUser = $this->pdo->prepare("SELECT is_super_admin FROM tbl_user WHERE user_id = :user_id");
            $stmtUser->execute(['user_id' => $userId]);
            $userData = $stmtUser->fetch(PDO::FETCH_ASSOC);
            if ($userData) {
                $isSuperAdmin = (int)$userData['is_super_admin'];
            }
        }

        if ($isSuperAdmin === 1) {
            // Super Admin: เห็นทุกบริษัท
            $stmt = $this->pdo->prepare("SELECT * FROM tbl_companies ORDER BY created_at DESC");
            $stmt->execute();
        } else if ($userId) {
            // Normal User: เห็นเฉพาะบริษัทที่ตัวเองถูกมอบหมาย (ผ่าน tbl_user_companies)
            $stmt = $this->pdo->prepare("
                SELECT c.* 
                FROM tbl_companies c
                JOIN tbl_user_companies uc ON c.company_id = uc.company_id
                WHERE uc.user_id = :user_id
                GROUP BY c.company_id
                ORDER BY c.created_at DESC
            ");
            $stmt->execute(['user_id' => $userId]);
        } else {
            // Fallback (ถ้าไม่ได้ล็อกอิน หรือไม่มี userId)
            $stmt = $this->pdo->prepare("SELECT * FROM tbl_companies ORDER BY created_at DESC");
            $stmt->execute();
        }

        
        $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($companies as &$company) {
            $cId = $company['company_id'] ?? $company['id'] ?? 0;
            
            if ($isSuperAdmin === 1) {
                // ดึง "ทุกปีทำงาน" ของบริษัทนี้ ที่มี active_status = 1 สำหรับ Super Admin
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
            } else {
                // สำหรับ Normal User ดึงเฉพาะปีบัญชีที่มีสิทธิ์ (ผ่าน tbl_fiscal_year_user)
                $stmtFy = $this->pdo->prepare(
                    "SELECT 
                        tbl_fiscal_years.*,
                        COUNT(tbl_fiscal_year_customers.customer_id) AS customer_count
                    FROM tbl_fiscal_years
                    JOIN tbl_fiscal_year_user fu ON tbl_fiscal_years.fiscal_id = fu.fiscal_id
                    LEFT JOIN tbl_fiscal_year_customers ON tbl_fiscal_years.fiscal_id = tbl_fiscal_year_customers.fiscal_id
                    WHERE tbl_fiscal_years.company_id = :company_id 
                      AND fu.user_id = :user_id
                      AND tbl_fiscal_years.active_status = 1
                    GROUP BY tbl_fiscal_years.fiscal_id
                    ORDER BY tbl_fiscal_years.fiscal_years DESC"
                );
                $stmtFy->execute(['company_id' => $cId, 'user_id' => $userId]);
            }
            
            $company['fiscal_years'] = $stmtFy->fetchAll(PDO::FETCH_ASSOC);
        }

        return $companies;
    }
}
