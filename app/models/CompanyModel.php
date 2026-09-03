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

    public function getAllCompanies($userId = null) {
        if ($userId) {
            $stmt = $this->pdo->prepare("SELECT * FROM tbl_companies WHERE user_id = :user_id ORDER BY created_at DESC");
            $stmt->execute(['user_id' => $userId]);
        } else {
            $stmt = $this->pdo->prepare("SELECT * FROM tbl_companies ORDER BY created_at DESC");
            $stmt->execute();
        }
        $companies = $stmt->fetchAll();

        foreach ($companies as &$company) {
            $cId = $company['company_id'] ?? $company['id'] ?? 0;
            $stmtFy = $this->pdo->prepare(
                "SELECT 
                    tbl_fiscal_years.*,
                    COUNT(tbl_fiscal_year_customers.customer_id) AS customer_count
                FROM tbl_fiscal_years
                LEFT JOIN tbl_fiscal_year_customers ON tbl_fiscal_years.fiscal_id = tbl_fiscal_year_customers.fiscal_id
                WHERE tbl_fiscal_years.company_id = :company_id
                GROUP BY tbl_fiscal_years.fiscal_id
                ORDER BY tbl_fiscal_years.fiscal_years DESC"
            );
            $stmtFy->execute(['company_id' => $cId]);
            $company['fiscal_years'] = $stmtFy->fetchAll();
        }

        return $companies;
    }
}
