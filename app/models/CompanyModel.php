<?php
require_once '../app/core/Model.php';

class CompanyModel extends Model {

    public function insertCompany($companyName) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO tbl_companies (
                company_name,
                created_at
                ) 
            VALUES (
                :name,
                NOW()
            )"
        );
        return $stmt->execute(['name' => $companyName]);
    }

    public function getAllCompanies() {
        $stmt = $this->pdo->prepare("SELECT * FROM tbl_companies ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
