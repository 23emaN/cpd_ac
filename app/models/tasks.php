<?php
require_once '../app/models/Model.php';

class TasksModel extends Model {

    public function insertTask($fiscalId, $taskName, $is_notify_amount) {
        // เปลี่ยนชื่อตารางและชื่อคอลัมน์ให้ตรงกับฐานข้อมูลจริงของคุณนะครับ
        $stmt = $this->pdo->prepare(
            "INSERT INTO tbl_tasks (
                fiscal_id,
                tasks_name,
                is_notify_amount,
                created_at
            ) 
            VALUES (
                :fiscal_id,
                :tasks_name,
                :is_notify_amount,
                NOW()
            )"
        );
        
        return $stmt->execute([
            'fiscal_id' => $fiscalId,
            'tasks_name' => $taskName,
            'is_notify_amount' => $is_notify_amount,
        ]);
    }

    public function getTasksByFiscalId($fiscalId) {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM tbl_tasks WHERE fiscal_id = :fiscal_id ORDER BY tasks_id  ASC"
        );
        $stmt->execute(['fiscal_id' => $fiscalId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
