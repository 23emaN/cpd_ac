<?php
require_once '../app/models/Model.php';

class RegistrationModel extends Model
{
    // ดึงงานทะเบียนทั้งหมดของปีนี้ พร้อม join ชื่อประเภทงาน/สี-ป้ายความเร่งด่วน/ชื่อผู้รับผิดชอบ
    public function getTasksByFiscalId($fiscalId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT r.*,
                    rt.registration_type_name,
                    ul.label AS urgency_label, ul.color AS urgency_color,
                    u.user_firstname AS assignee_firstname, u.user_lastname AS assignee_lastname
             FROM tbl_registration r
             LEFT JOIN tbl_registration_type rt ON rt.registration_type_id = r.registration_type_id
             LEFT JOIN tbl_urgency_level ul ON ul.fiscal_id = r.fiscal_id AND ul.urgency_level COLLATE utf8mb4_general_ci = r.urgency_level
             LEFT JOIN tbl_user u ON u.user_id = r.assignee_user_id
             WHERE r.fiscal_id = :fiscal_id AND r.delete_at IS NULL AND r.closed_at IS NULL
             ORDER BY r.due_date ASC, r.registration ASC"
        );
        $stmt->execute(['fiscal_id' => $fiscalId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ดึงงานทะเบียนเดียวมาเติมในฟอร์มแก้ไข (join แบบเดียวกับ getTasksByFiscalId)
    public function getById($id)
    {
        $stmt = $this->pdo->prepare(
            "SELECT r.*,
                    rt.registration_type_name,
                    ul.label AS urgency_label, ul.color AS urgency_color,
                    u.user_firstname AS assignee_firstname, u.user_lastname AS assignee_lastname
             FROM tbl_registration r
             LEFT JOIN tbl_registration_type rt ON rt.registration_type_id = r.registration_type_id
             LEFT JOIN tbl_urgency_level ul ON ul.fiscal_id = r.fiscal_id AND ul.urgency_level COLLATE utf8mb4_general_ci = r.urgency_level
             LEFT JOIN tbl_user u ON u.user_id = r.assignee_user_id
             WHERE r.registration = :id"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // สร้างรหัสงาน (registration_no) ต่อจากลำดับล่าสุดของปีนี้ เช่น REG-001, REG-002, ...
    private function generateRegistrationNo($fiscalId)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) AS total FROM tbl_registration WHERE fiscal_id = :fiscal_id");
        $stmt->execute(['fiscal_id' => $fiscalId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $nextNumber = ((int) ($row['total'] ?? 0)) + 1;

        return 'REG-' . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
    }

    // เพิ่มงานทะเบียนใหม่ สถานะเริ่มต้นเป็น '0' (รับงานลงทะเบียน) เสมอ
    public function insert($fiscalId, $data)
    {
        $registrationNo = $this->generateRegistrationNo($fiscalId);

        $stmt = $this->pdo->prepare(
            "INSERT INTO tbl_registration (
                fiscal_id, registration_type_id, customer_name, customer_phone,
                contact_person, registration_name, status, description,
                service_amount, urgency_level, accep_date, due_date,
                assignee_user_id, review_user_id, registration_no
            ) VALUES (
                :fiscal_id, :registration_type_id, :customer_name, :customer_phone,
                :contact_person, :registration_name, '0', :description,
                :service_amount, :urgency_level, :accep_date, :due_date,
                :assignee_user_id, :review_user_id, :registration_no
            )"
        );

        return $stmt->execute([
            'fiscal_id' => $fiscalId,
            'registration_type_id' => $data['registration_type_id'],
            'customer_name' => $data['customer_name'],
            'customer_phone' => $data['customer_phone'],
            'contact_person' => $data['contact_person'],
            'registration_name' => $data['registration_name'],
            'description' => $data['description'],
            'service_amount' => $data['service_amount'],
            'urgency_level' => $data['urgency_level'] !== '' ? $data['urgency_level'] : null,
            'accep_date' => $data['accep_date'] !== '' ? $data['accep_date'] : null,
            'due_date' => $data['due_date'] !== '' ? $data['due_date'] : null,
            'assignee_user_id' => $data['assignee_user_id'] !== '' ? $data['assignee_user_id'] : null,
            'review_user_id' => $data['review_user_id'] !== '' ? $data['review_user_id'] : null,
            'registration_no' => $registrationNo,
        ]);
    }

    // แก้ไขงานทะเบียนที่มีอยู่แล้ว (ไม่แตะ status/registration_no)
    public function update($id, $data)
    {
        $stmt = $this->pdo->prepare(
            "UPDATE tbl_registration SET
                registration_type_id = :registration_type_id,
                customer_name = :customer_name,
                customer_phone = :customer_phone,
                contact_person = :contact_person,
                registration_name = :registration_name,
                description = :description,
                service_amount = :service_amount,
                urgency_level = :urgency_level,
                accep_date = :accep_date,
                due_date = :due_date,
                assignee_user_id = :assignee_user_id,
                review_user_id = :review_user_id
             WHERE registration = :id"
        );

        return $stmt->execute([
            'registration_type_id' => $data['registration_type_id'],
            'customer_name' => $data['customer_name'],
            'customer_phone' => $data['customer_phone'],
            'contact_person' => $data['contact_person'],
            'registration_name' => $data['registration_name'],
            'description' => $data['description'],
            'service_amount' => $data['service_amount'],
            'urgency_level' => $data['urgency_level'] !== '' ? $data['urgency_level'] : null,
            'accep_date' => $data['accep_date'] !== '' ? $data['accep_date'] : null,
            'due_date' => $data['due_date'] !== '' ? $data['due_date'] : null,
            'assignee_user_id' => $data['assignee_user_id'] !== '' ? $data['assignee_user_id'] : null,
            'review_user_id' => $data['review_user_id'] !== '' ? $data['review_user_id'] : null,
            'id' => $id,
        ]);
    }

    // ลบงานทะเบียนแบบ soft-delete (ตาม pattern เดียวกับ RegistrationTypeModel::softDelete)
    public function softDelete($id, $userId)
    {
        $stmt = $this->pdo->prepare(
            "UPDATE tbl_registration SET delete_at = NOW(), delete_user_id = :user_id WHERE registration = :id"
        );
        return $stmt->execute(['user_id' => $userId, 'id' => $id]);
    }

    // เปลี่ยนสถานะงานทะเบียน (ใช้ตอนลากการ์ดข้ามคอลัมน์บนบอร์ด)
    public function updateStatus($id, $status)
    {
        $stmt = $this->pdo->prepare("UPDATE tbl_registration SET status = :status WHERE registration = :id");
        return $stmt->execute(['status' => $status, 'id' => $id]);
    }

    // ปิด Job — ย้ายงานออกจากบอร์ดไปเก็บเป็นประวัติ (ทำเครื่องหมาย closed_at ไว้ ไม่ได้ลบข้อมูล)
    // ปิดเฉพาะงานที่ยังเปิดอยู่ (closed_at IS NULL) เพื่อกันการกดปิดซ้ำจากหน้าที่ค้าง
    public function closeJob($id, $userId)
    {
        $stmt = $this->pdo->prepare(
            "UPDATE tbl_registration
             SET closed_at = NOW(), close_user_id = :user_id
             WHERE registration = :id AND delete_at IS NULL AND closed_at IS NULL"
        );
        $stmt->execute(['user_id' => $userId, 'id' => $id]);
        return $stmt->rowCount() > 0;
    }

    // นับจำนวนงานที่ปิดแล้วของปีนี้ (ใช้ทำ pagination + ยอดรวมในหน้าประวัติ)
    public function countClosedTasks($fiscalId, $keyword = '')
    {
        $sql = "SELECT COUNT(*) AS total_count, COALESCE(SUM(r.service_amount), 0) AS total_amount
                FROM tbl_registration r
                LEFT JOIN tbl_registration_type rt ON rt.registration_type_id = r.registration_type_id
                LEFT JOIN tbl_user u ON u.user_id = r.assignee_user_id
                WHERE r.fiscal_id = :fiscal_id AND r.delete_at IS NULL AND r.closed_at IS NOT NULL";
        $params = ['fiscal_id' => $fiscalId];
        if ($keyword !== '') {
            $sql .= " AND (r.customer_name LIKE :kw OR r.registration_name LIKE :kw
                           OR rt.registration_type_name LIKE :kw
                           OR CONCAT_WS(' ', u.user_firstname, u.user_lastname) LIKE :kw)";
            $params['kw'] = '%' . $keyword . '%';
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total_count' => 0, 'total_amount' => 0];
    }

    // ดึงประวัติงานที่ปิดแล้วของปีนี้ (เรียงจากที่ปิดล่าสุด) แบบแบ่งหน้า
    public function getClosedTasks($fiscalId, $keyword = '', $limit = 25, $offset = 0)
    {
        $sql = "SELECT r.registration,
                       DATE_FORMAT(r.closed_at, '%d/%m/%Y %H:%i') AS closed_at,
                       r.customer_name,
                       r.registration_name AS task_name,
                       rt.registration_type_name AS task_type,
                       CONCAT_WS(' ', u.user_firstname, u.user_lastname) AS assignee_name,
                       r.service_amount AS amount
                FROM tbl_registration r
                LEFT JOIN tbl_registration_type rt ON rt.registration_type_id = r.registration_type_id
                LEFT JOIN tbl_user u ON u.user_id = r.assignee_user_id
                WHERE r.fiscal_id = :fiscal_id AND r.delete_at IS NULL AND r.closed_at IS NOT NULL";
        $params = ['fiscal_id' => $fiscalId];
        if ($keyword !== '') {
            $sql .= " AND (r.customer_name LIKE :kw OR r.registration_name LIKE :kw
                           OR rt.registration_type_name LIKE :kw
                           OR CONCAT_WS(' ', u.user_firstname, u.user_lastname) LIKE :kw)";
            $params['kw'] = '%' . $keyword . '%';
        }
        $sql .= " ORDER BY r.closed_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        // LIMIT/OFFSET ต้องผูกเป็น integer เสมอ (PDO emulate ปิดอยู่ ห้ามส่งเป็น string)
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    //การ์ด 1 งานทะเบียนที่เปิดอยู่
    public function countOpen($fiscalId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) AS total_open
         FROM tbl_registration
         WHERE fiscal_id = :fiscal_id AND delete_at IS NULL AND closed_at IS NULL"
        );
        $stmt->execute(['fiscal_id' => $fiscalId]);
        return (int) ($stmt->fetch(PDO::FETCH_ASSOC)['total_open'] ?? 0);
    }

    //การ์ด 2 ยังไม่เลยกำหนด
    public function countNotOverdue($fiscalId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) AS total FROM tbl_registration
         WHERE fiscal_id = :fiscal_id AND delete_at IS NULL AND closed_at IS NULL
           AND (due_date IS NULL OR due_date >= CURDATE())"
        );
        $stmt->execute(['fiscal_id' => $fiscalId]);
        return (int) ($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
    }

    //การ์ด 3 เลยกำหนดส่งงาน
    public function countOverdue($fiscalId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) AS total_overdue
         FROM tbl_registration
         WHERE fiscal_id = :fiscal_id AND delete_at IS NULL AND closed_at IS NULL
           AND due_date IS NOT NULL AND due_date < CURDATE()"
        );
        $stmt->execute(['fiscal_id' => $fiscalId]);
        return (int) ($stmt->fetch(PDO::FETCH_ASSOC)['total_overdue'] ?? 0);
    }

    //การ์ด 4 ปิดงานเดือนนี้ (จำนวน + ยอดค่าบริการรวม)
    public function countClosedThisMonth($fiscalId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) AS total_closed_this_month, COALESCE(SUM(service_amount), 0) AS total_amount
         FROM tbl_registration
         WHERE fiscal_id = :fiscal_id AND delete_at IS NULL AND closed_at IS NOT NULL AND YEAR(closed_at) = YEAR(CURDATE()) AND MONTH(closed_at) = MONTH(CURDATE())"
        );
        $stmt->execute(['fiscal_id' => $fiscalId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return [
            'total' => (int) ($row['total_closed_this_month'] ?? 0),
            'total_amount' => (float) ($row['total_amount'] ?? 0),
        ];
    }

    //การ์ด 5 ปิดงานเดือนก่อน (จำนวน + ยอดค่าบริการรวม)
    public function countClosedLastMonth($fiscalId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) AS total_closed_last_month, COALESCE(SUM(service_amount), 0) AS total_amount
         FROM tbl_registration
         WHERE fiscal_id = :fiscal_id AND delete_at IS NULL AND closed_at IS NOT NULL AND YEAR(closed_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND MONTH(closed_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))"
        );
        $stmt->execute(['fiscal_id' => $fiscalId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return [
            'total' => (int) ($row['total_closed_last_month'] ?? 0),
            'total_amount' => (float) ($row['total_amount'] ?? 0),
        ];
    }
}
