<?php
// app/models/PostItModel.php
require_once '../app/models/Model.php';

class PostItModel extends Model
{
    /**
     * สรุปจำนวนตามสถานะ (สำหรับการ์ดสถิติด้านบน)
     */
    public function getStats(?int $fiscalYearId): array
    {
        $stats = [
            'total' => 0,
            'pending' => 0,
            'done' => 0,
            'overdue' => 0,
        ];

        if (!$fiscalYearId) {
            return $stats;
        }

        $sql = "SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN status = '0' AND (due_date IS NULL OR due_date >= CURDATE()) THEN 1 ELSE 0 END) AS pending,
                    SUM(CASE WHEN status = '1' THEN 1 ELSE 0 END) AS done,
                    SUM(CASE WHEN status = '0' AND due_date IS NOT NULL AND due_date < CURDATE() THEN 1 ELSE 0 END) AS overdue
                FROM tbl_post_it
                WHERE fiscal_year_id = :fiscal_year_id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['fiscal_year_id' => $fiscalYearId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'total' => (int) ($row['total'] ?? 0),
            'pending' => (int) ($row['pending'] ?? 0),
            'done' => (int) ($row['done'] ?? 0),
            'overdue' => (int) ($row['overdue'] ?? 0),
        ];
    }

    /**
     * รายการ Post-it + ชื่อผู้รับผิดชอบ
     */
    public function getList(?int $fiscalYearId, array $filters = []): array
    {
        if (!$fiscalYearId) {
            return [];
        }

        $sql = "SELECT
                    p.post_id,
                    p.fiscal_year_id,
                    p.title,
                    p.user_id,
                    p.due_date,
                    p.status,
                    p.content,
                    p.color_code,
                    p.created_user_id,
                    p.created_at,
                    TRIM(CONCAT(COALESCE(u.user_firstname, ''), ' ', COALESCE(u.user_lastname, ''))) AS assignee_name,
                    u.user_name AS assignee_username
                FROM tbl_post_it p
                LEFT JOIN tbl_user u ON u.user_id = p.user_id
                WHERE p.fiscal_year_id = :fiscal_year_id";

        $params = ['fiscal_year_id' => $fiscalYearId];

        if (!empty($filters['q'])) {
            $sql .= " AND (
                p.title LIKE :q
                OR p.content LIKE :q
                OR u.user_firstname LIKE :q
                OR u.user_lastname LIKE :q
                OR u.user_name LIKE :q
            )";
            $params['q'] = '%' . $filters['q'] . '%';
        }

        if (isset($filters['user_id']) && $filters['user_id'] !== '' && $filters['user_id'] !== null) {
            $sql .= " AND p.user_id = :user_id";
            $params['user_id'] = (int) $filters['user_id'];
        }

        if (isset($filters['status']) && $filters['status'] !== '' && $filters['status'] !== null) {
            $sql .= " AND p.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['color_code'])) {
            $sql .= " AND p.color_code = :color_code";
            $params['color_code'] = $filters['color_code'];
        }

        if (!empty($filters['due'])) {
            switch ($filters['due']) {
                case 'today':
                    $sql .= " AND p.due_date = CURDATE()";
                    break;
                case 'week':
                    $sql .= " AND p.due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
                    break;
                case 'overdue':
                    $sql .= " AND p.status = '0' AND p.due_date IS NOT NULL AND p.due_date < CURDATE()";
                    break;
                case 'none':
                    $sql .= " AND p.due_date IS NULL";
                    break;
            }
        }

        $sort = $filters['sort'] ?? 'created_desc';
        switch ($sort) {
            case 'created_asc':
                $sql .= " ORDER BY p.created_at ASC";
                break;
            case 'due_asc':
                $sql .= " ORDER BY p.due_date IS NULL, p.due_date ASC, p.created_at DESC";
                break;
            case 'due_desc':
                $sql .= " ORDER BY p.due_date IS NULL, p.due_date DESC, p.created_at DESC";
                break;
            default:
                $sql .= " ORDER BY p.created_at DESC";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * ผู้รับผิดชอบใน dropdown: คนที่อยู่ในปีงบประมาณนั้น
     * ถ้ายังไม่มีใน tbl_fiscal_year_user ให้ fallback เป็น user ที่สถานะปกติ
     */
    public function getAssignees(?int $fiscalYearId): array
    {
        if ($fiscalYearId) {
            $sql = "SELECT DISTINCT
                        u.user_id,
                        TRIM(CONCAT(COALESCE(u.user_firstname, ''), ' ', COALESCE(u.user_lastname, ''))) AS full_name,
                        u.user_name
                    FROM tbl_fiscal_year_user fyu
                    INNER JOIN tbl_user u ON u.user_id = fyu.user_id
                    WHERE fyu.fiscal_id = :fiscal_id
                      AND u.user_status = '1'
                    ORDER BY full_name ASC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['fiscal_id' => $fiscalYearId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            if (!empty($rows)) {
                return $rows;
            }
        }

        $sql = "SELECT
                    u.user_id,
                    TRIM(CONCAT(COALESCE(u.user_firstname, ''), ' ', COALESCE(u.user_lastname, ''))) AS full_name,
                    u.user_name
                FROM tbl_user u
                WHERE u.user_status = '1'
                ORDER BY full_name ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * บันทึก Post-it ใหม่
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO tbl_post_it
                    (fiscal_year_id, title, user_id, due_date, status, content, color_code, created_user_id, created_at)
                VALUES
                    (:fiscal_year_id, :title, :user_id, :due_date, :status, :content, :color_code, :created_user_id, NOW())";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'fiscal_year_id' => (int) $data['fiscal_year_id'],
            'title' => $data['title'],
            'user_id' => $data['user_id'],
            'due_date' => $data['due_date'],
            'status' => $data['status'],
            'content' => $data['content'],
            'color_code' => $data['color_code'],
            'created_user_id' => (int) $data['created_user_id'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }
}
