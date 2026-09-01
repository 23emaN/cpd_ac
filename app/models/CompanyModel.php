<?php
require_once '../app/core/Model.php';

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
        return $stmt->fetchAll();
    }
}
