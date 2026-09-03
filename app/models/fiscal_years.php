<?php
require_once '../app/models/Model.php';

class FiscalYearsModel extends Model {

    public function insertFiscalYears($companyId, $workingYear, $copyFromYear) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO tbl_fiscal_years (
                company_id,
                fiscal_years,
                create_at
                ) 
            VALUES (
                :company_id,
                :working_year,
                NOW()
            )"
        );
        return $stmt->execute([
            'company_id' => $companyId,
            'working_year' => $workingYear,
        ]);
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
