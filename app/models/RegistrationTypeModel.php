<?php
require_once '../app/models/Model.php';

class RegistrationTypeModel extends Model
{


    //sql ดึงมาโชว์//
    public function getAll($fiscalId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT registration_type_id AS id, registration_type_name AS name, active_status
             FROM tbl_registration_type WHERE fiscal_id = :fiscal_id AND delete_at IS NULL ORDER BY registration_type_id ASC"
        );
        $stmt->execute(['fiscal_id' => $fiscalId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //sql toggle active_status//
    public function setActiveStatus($id, $status, $fiscalId)
    {
        $stmt = $this->pdo->prepare(
            "UPDATE tbl_registration_type SET active_status = :status WHERE registration_type_id = :id AND fiscal_id = :fiscal_id"
        );
        return $stmt->execute(['status' => $status, 'id' => $id, 'fiscal_id' => $fiscalId]);
    }

    //sql insert//
    public function insert($name, $userId, $fiscalId)
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO tbl_registration_type (registration_type_name, active_status, created_at, create_user_id, fiscal_id)
             VALUES (:name, '1', NOW(), :user_id, :fiscal_id)"
        );
        return $stmt->execute(['name' => $name, 'user_id' => $userId, 'fiscal_id' => $fiscalId]);
    }

    //sql update//
    public function update($id, $name, $fiscalId)
    {
        $stmt = $this->pdo->prepare(
            "UPDATE tbl_registration_type SET registration_type_name = :name WHERE registration_type_id = :id AND fiscal_id = :fiscal_id"
        );
        return $stmt->execute(['name' => $name, 'id' => $id, 'fiscal_id' => $fiscalId]);
    }

    //sql softDelete//
    public function softDelete($id, $userId, $fiscalId)
    {
        $stmt = $this->pdo->prepare(
            "UPDATE tbl_registration_type SET delete_at = NOW(), delete_user_id = :user_id WHERE registration_type_id = :id AND fiscal_id = :fiscal_id"
        );
        return $stmt->execute(['user_id' => $userId, 'id' => $id, 'fiscal_id' => $fiscalId]);
    }
}
