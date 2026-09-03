<?php
require_once '../app/models/Model.php';

class TasksModel extends Model {

    public function insertTask($fiscalId, $taskName, $is_notify_amount) {
        // หาค่าลำดับสุดท้ายของปีนี้
        $stmt_order = $this->pdo->prepare("SELECT MAX(list_order) as max_order FROM tbl_tasks WHERE fiscal_id = :fiscal_id");
        $stmt_order->execute(['fiscal_id' => $fiscalId]);
        $row = $stmt_order->fetch(PDO::FETCH_ASSOC);
        $list_order = ($row && $row['max_order'] !== null) ? (int)$row['max_order'] + 1 : 1;

        $stmt = $this->pdo->prepare(
            "INSERT INTO tbl_tasks (
                fiscal_id,
                tasks_name,
                is_notify_amount,
                list_order,
                created_at
            ) 
            VALUES (
                :fiscal_id,
                :tasks_name,
                :is_notify_amount,
                :list_order,
                NOW()
            )"
        );
        
        return $stmt->execute([
            'fiscal_id' => $fiscalId,
            'tasks_name' => $taskName,
            'is_notify_amount' => $is_notify_amount,
            'list_order' => $list_order,
        ]);
    }

    public function getTasksByFiscalId($fiscalId) {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM tbl_tasks WHERE fiscal_id = :fiscal_id AND delete_at IS NULL ORDER BY list_order ASC, tasks_id ASC"
        );
        $stmt->execute(['fiscal_id' => $fiscalId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function softDeleteTask($tasksId) {
        $stmt = $this->pdo->prepare("UPDATE tbl_tasks SET delete_at = NOW() WHERE tasks_id = :id");
        return $stmt->execute(['id' => $tasksId]);
    }

    public function getTaskById($tasksId) {
        $stmt = $this->pdo->prepare("SELECT * FROM tbl_tasks WHERE tasks_id = :id");
        $stmt->execute(['id' => $tasksId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateTask($tasksId, $taskName, $isNotifyAmount) {
        $stmt = $this->pdo->prepare(
            "UPDATE tbl_tasks 
             SET tasks_name = :tasks_name, 
                 is_notify_amount = :is_notify_amount 
             WHERE tasks_id = :id"
        );
        
        return $stmt->execute([
            'tasks_name' => $taskName,
            'is_notify_amount' => $isNotifyAmount,
            'id' => $tasksId
        ]);
    }

    public function moveTaskOrder($tasksId, $direction, $fiscalId) {
        // 1. ดึงข้อมูลลำดับปัจจุบันของงานที่ต้องการเลื่อน
        $stmt = $this->pdo->prepare("SELECT tasks_id, list_order FROM tbl_tasks WHERE tasks_id = :id");
        $stmt->execute(['id' => $tasksId]);
        $current = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$current) return false;
        
        $currentOrder = (int)$current['list_order'];
        
        // 2. ค้นหางานตัวที่ติดกันที่จะสลับที่ด้วย
        if ($direction === 'up') {
            // หาคนที่มี list_order น้อยกว่าตัวปัจจุบัน แต่น้อยกว่าแบบใกล้ที่สุด (MAX)
            $stmt = $this->pdo->prepare("SELECT tasks_id, list_order FROM tbl_tasks WHERE fiscal_id = :f_id AND list_order < :c_order ORDER BY list_order DESC LIMIT 1");
        } else {
            // หาคนที่มี list_order มากกว่าตัวปัจจุบัน แต่มากกว่าแบบใกล้ที่สุด (MIN)
            $stmt = $this->pdo->prepare("SELECT tasks_id, list_order FROM tbl_tasks WHERE fiscal_id = :f_id AND list_order > :c_order ORDER BY list_order ASC LIMIT 1");
        }
        
        $stmt->execute(['f_id' => $fiscalId, 'c_order' => $currentOrder]);
        $adjacent = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$adjacent) return false; // ชนขอบบนสุดหรือขอบล่างสุดแล้ว
        
        // 3. สลับตำแหน่งกัน
        try {
            $this->pdo->beginTransaction();
            
            // เปลี่ยนของตัวปัจจุบันไปเป็น order ของตัวเป้าหมาย
            $update1 = $this->pdo->prepare("UPDATE tbl_tasks SET list_order = :new_order WHERE tasks_id = :id");
            $update1->execute(['new_order' => $adjacent['list_order'], 'id' => $current['tasks_id']]);
            
            // เปลี่ยนของตัวเป้าหมายมาเป็น order เดิมของตัวปัจจุบัน
            $update2 = $this->pdo->prepare("UPDATE tbl_tasks SET list_order = :new_order WHERE tasks_id = :id");
            $update2->execute(['new_order' => $currentOrder, 'id' => $adjacent['tasks_id']]);
            
            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return false;
        }
    }
}
