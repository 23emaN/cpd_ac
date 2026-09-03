<?php
require_once '../app/models/Model.php';

class UserModel extends Model {

    public function getUserByUsername($username) {
        $stmt = $this->pdo->prepare("SELECT * FROM tbl_user WHERE user_name = :username");
        $stmt->execute(['username' => $username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insertUser($data) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO tbl_user (
                user_name,
                user_password, 
                user_firstname, 
                user_lastname, 
                user_status,
                create_at,
                is_super_admin,
                position, 
                team_id
            ) VALUES (
                :user_name,
                :user_password,
                :user_firstname,
                :user_lastname,
                '1', 
                NOW(),
                '0',
                :position, 
                :team_id
            )"
        );
        $stmt->execute([
            'user_name' => $data['user_name'],
            'user_password' => $data['user_password'],
            'user_firstname' => $data['user_firstname'],
            'user_lastname' => $data['user_lastname'],
            'position' => $data['position'] ?? null,
            'team_id' => $data['team_id'] ?? null
        ]);
        return $this->pdo->lastInsertId();
    }

    public function updateUser($data) {
        $stmt = $this->pdo->prepare(
            "UPDATE tbl_user SET 
                user_firstname = :user_firstname, 
                user_lastname = :user_lastname, 
                position = :position, 
                team_id = :team_id
             WHERE user_id = :user_id"
        );
        return $stmt->execute([
            'user_id' => $data['user_id'],
            'user_firstname' => $data['user_firstname'],
            'user_lastname' => $data['user_lastname'],
            'position' => $data['position'] ?? null,
            'team_id' => $data['team_id'] ?? null
        ]);
    }

    public function deleteUserSoft($userId) {
        // ลบข้อมูลที่ผูกอยู่ก่อน (Foreign Key constraints)
        $stmt1 = $this->pdo->prepare("DELETE FROM tbl_user_companies WHERE user_id = :user_id");
        $stmt1->execute(['user_id' => $userId]);

        $stmt2 = $this->pdo->prepare("DELETE FROM tbl_fiscal_year_user WHERE user_id = :user_id");
        $stmt2->execute(['user_id' => $userId]);

        // ลบข้อมูลพนักงานหลัก
        $stmt3 = $this->pdo->prepare("DELETE FROM tbl_user WHERE user_id = :user_id");
        return $stmt3->execute(['user_id' => $userId]);
    }

    public function linkUserToCompany($userId, $companyId) {
        $stmt = $this->pdo->prepare("INSERT INTO tbl_user_companies (user_id, company_id) VALUES (:user_id, :company_id)");
        return $stmt->execute(['user_id' => $userId, 'company_id' => $companyId]);
    }

    public function linkUserToFiscalYear($userId, $fiscalId) {
        $stmt = $this->pdo->prepare("INSERT INTO tbl_fiscal_year_user (user_id, fiscal_id, created_at) VALUES (:user_id, :fiscal_id, NOW())");
        return $stmt->execute(['user_id' => $userId, 'fiscal_id' => $fiscalId]);
    }

    public function getEmployeesByFiscalAndCompany($fiscalId, $companyId) {
        $sql = "
            SELECT u.*, t.team_name 
            FROM tbl_user u
            JOIN tbl_user_companies uc ON u.user_id = uc.user_id
            JOIN tbl_fiscal_year_user fu ON u.user_id = fu.user_id
            LEFT JOIN tbl_team t ON u.team_id = t.team_id
            WHERE fu.fiscal_id = :fiscal_id 
              AND uc.company_id = :company_id
            ORDER BY u.user_firstname ASC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'fiscal_id' => $fiscalId,
            'company_id' => $companyId
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
