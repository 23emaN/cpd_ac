<?php
require_once '../app/core/Model.php';

class FiscalYearsModel extends Model {

    public function insertFiscalYears($companyId, $workingYear, $copyFromYear) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO tbl_fiscal_years (
                company_id,
                fiscal_years,
                created_at
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
}
