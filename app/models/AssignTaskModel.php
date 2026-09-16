<?php
require_once '../app/models/Model.php';

class AssignTaskModel extends Model {
    
    public function createAssignTask($data) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO tbl_assign_task (
                company_id, 
                user_id, 
                fiscal_id, 
                assign_title, 
                assign_detail, 
                assign_status, 
                due_date, 
                create_at, 
                create_user_id
            ) VALUES (
                :company_id, 
                :user_id, 
                :fiscal_id, 
                :assign_title, 
                :assign_detail, 
                '0', 
                :due_date, 
                NOW(), 
                :create_user_id
            )"
        );
        
        $stmt->execute([
            'company_id'     => $data['company_id'],
            'user_id'        => $data['user_id'],
            'fiscal_id'      => $data['fiscal_id'],
            'assign_title'   => $data['assign_title'],
            'assign_detail'  => $data['assign_detail'] ?? null,
            'due_date'       => $data['due_date'],
            'create_user_id' => $data['create_user_id']
        ]);
        
        return $this->pdo->lastInsertId();
    }

    public function getAssignTasks($companyId, $fiscalId, $filters = [], $limit = 10, $offset = 0) {
        $sql = "SELECT t.*, u.user_firstname, u.user_lastname 
                FROM tbl_assign_task t
                LEFT JOIN tbl_user u ON t.user_id = u.user_id
                WHERE t.company_id = :company_id AND t.fiscal_id = :fiscal_id";
        
        $params = [
            'company_id' => $companyId,
            'fiscal_id' => $fiscalId
        ];

        if (!empty($filters['assignee_id'])) {
            $sql .= " AND t.user_id = :assignee_id";
            $params['assignee_id'] = $filters['assignee_id'];
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'critical') {
                $sql .= " AND t.assign_status = '0' AND t.due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 5 DAY)";
            } elseif ($filters['status'] === 'overdue') {
                $sql .= " AND t.assign_status = '0' AND t.due_date < CURDATE()";
            } else {
                $sql .= " AND t.assign_status = :status";
                $params['status'] = $filters['status'];
            }
        }

        $sql .= " ORDER BY t.due_date ASC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue(":$key", $val);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAssignTasksCount($companyId, $fiscalId, $filters = []) {
        $sql = "SELECT COUNT(*) as total
                FROM tbl_assign_task t
                WHERE t.company_id = :company_id AND t.fiscal_id = :fiscal_id";
        
        $params = [
            'company_id' => $companyId,
            'fiscal_id' => $fiscalId
        ];

        if (!empty($filters['assignee_id'])) {
            $sql .= " AND t.user_id = :assignee_id";
            $params['assignee_id'] = $filters['assignee_id'];
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'critical') {
                $sql .= " AND t.assign_status = '0' AND t.due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 5 DAY)";
            } elseif ($filters['status'] === 'overdue') {
                $sql .= " AND t.assign_status = '0' AND t.due_date < CURDATE()";
            } else {
                $sql .= " AND t.assign_status = :status";
                $params['status'] = $filters['status'];
            }
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function getAssignTaskStats($companyId, $fiscalId) {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN assign_status = '0' AND due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 5 DAY) THEN 1 ELSE 0 END) as critical,
                    SUM(CASE WHEN assign_status = '0' AND due_date < CURDATE() THEN 1 ELSE 0 END) as overdue,
                    SUM(CASE WHEN assign_status = '1' THEN 1 ELSE 0 END) as completed
                FROM tbl_assign_task
                WHERE company_id = :company_id AND fiscal_id = :fiscal_id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'company_id' => $companyId,
            'fiscal_id' => $fiscalId
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'total' => $result['total'] ?? 0,
            'critical' => $result['critical'] ?? 0,
            'overdue' => $result['overdue'] ?? 0,
            'completed' => $result['completed'] ?? 0
        ];
    }
}
