<?php
require_once '../app/core/Model.php';

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
        $stmt = $this->pdo->prepare("SELECT * FROM tbl_fiscal_years WHERE company_id = :company_id ORDER BY fiscal_years DESC");
        $stmt->execute(['company_id' => $companyId]);
        return $stmt->fetchAll();
    }
}
