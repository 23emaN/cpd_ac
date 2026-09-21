<?php
require_once '../app/models/Model.php';

class System_setting_Modal extends Model
{
    /**
     * ดึงรายการตัวเลือกภาษีทั้งหมด
     */
    public function getAll(): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM tbl_option_tax
            WHERE delete_at IS NULL
            ORDER BY list_order ASC, option_id ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * ดึงลำดับถัดไป (MAX list_order + 1)
     */
    public function getNextOrder(): int
    {
        $stmt = $this->pdo->prepare("
            SELECT COALESCE(MAX(list_order), 0) + 1 AS next_order
            FROM tbl_option_tax
            WHERE delete_at IS NULL
        ");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($row['next_order'] ?? 1);
    }

    /**
     * เพิ่มตัวเลือกภาษีใหม่
     */
    public function create(string $name, int $order = 0, int $userId = 0): bool
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO tbl_option_tax (option_name, list_order, create_user_id, created_at)
            VALUES (:name, :order, :uid, CURDATE())
        ");
        return $stmt->execute([
            ':name'  => trim($name),
            ':order' => $order,
            ':uid'   => $userId,
        ]);
    }

    /**
     * แก้ไขตัวเลือกภาษี
     */
    public function update(int $id, string $name, int $order = 0): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE tbl_option_tax
            SET option_name = :name, list_order = :order
            WHERE option_id = :id AND delete_at IS NULL
        ");
        return $stmt->execute([
            ':name'  => trim($name),
            ':order' => $order,
            ':id'    => $id,
        ]);
    }

    /**
     * ลบแบบ Soft Delete
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE tbl_option_tax SET delete_at = CURDATE() WHERE option_id = :id
        ");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * อัปเดตข้อมูลส่วนตัว Super Admin
     */
    public function updateProfile(int $userId, string $firstname, string $lastname, string $username, string $email): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE tbl_user
            SET user_firstname = :firstname,
                user_lastname  = :lastname,
                user_name      = :username,
                user_email     = :email
            WHERE user_id = :user_id
              AND is_super_admin = '1'
        ");
        return $stmt->execute([
            ':firstname' => trim($firstname),
            ':lastname'  => trim($lastname),
            ':username'  => trim($username),
            ':email'     => trim($email),
            ':user_id'   => $userId,
        ]);
    }

    /**
     * เปลี่ยนรหัสผ่าน Super Admin
     */
    public function changePassword(int $userId, string $hashedPassword): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE tbl_user
            SET user_password = :password
            WHERE user_id = :user_id
              AND is_super_admin = '1'
        ");
        return $stmt->execute([
            ':password' => $hashedPassword,
            ':user_id'  => $userId,
        ]);
    }
}