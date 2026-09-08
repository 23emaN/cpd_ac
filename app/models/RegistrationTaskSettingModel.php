<?php
require_once '../app/models/Model.php';
// require_once ไฟล์ Model.php (คลาสแม่) เพื่อให้ RegistrationTaskSettingModel สืบทอด $this->pdo มาใช้ได้

class RegistrationTaskSettingModel extends Model
{
    // ค่าเริ่มต้นของ 3 ระดับ เก็บไว้เป็น property เดียว ใช้ซ้ำได้ทั้งตอนสร้าง default และตอนเขียนโค้ดฝั่งอื่น
    private $defaultUrgencyLevels = [
        ['urgency_level' => '1', 'label' => 'ปกติ',    'color' => '#94a3b8'],
        ['urgency_level' => '2', 'label' => 'เร่งด่วน', 'color' => '#f97316'],
        ['urgency_level' => '3', 'label' => 'ด่วนมาก', 'color' => '#ef4444'],
    ];

    // ดึงค่า notify_days ของปีบัญชีนี้
    public function getSettings($fiscalId)
    {
        // เตรียมคำสั่ง SQL หาแถว settings ของปีนี้ (prepare = กัน SQL injection)
        $stmt = $this->pdo->prepare("SELECT * FROM tbl_registration_task_setting WHERE fiscal_id = :fiscal_id");
        // ยิงคำสั่งจริง แทนค่า :fiscal_id ด้วยตัวแปรที่รับเข้ามา
        $stmt->execute(['fiscal_id' => $fiscalId]);
        // ดึงผลลัพธ์แถวเดียว (เพราะ unique key รับประกันว่ามีได้แค่ 1 แถวต่อปี)
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // ถ้า $row เป็น false แปลว่ายังไม่เคยมีการตั้งค่าของปีนี้มาก่อน
        if (!$row) {
            // สร้างแถวใหม่ให้ ด้วยค่า default (notify_day = 7)
            $insert = $this->pdo->prepare(
                "INSERT INTO tbl_registration_task_setting (fiscal_id, notify_day) VALUES (:fiscal_id, 7)"
            );
            $insert->execute(['fiscal_id' => $fiscalId]);
            // จำลองผลลัพธ์เป็น array เดียวกับที่ SELECT จะได้ จะได้ return แบบเดียวกันเสมอ
            $row = ['notify_day' => 7];
        }

        return $row;
    }

    // ดึงรายการ 3 ระดับความเร่งด่วนของปีบัญชีนี้
    public function getUrgencyLevels($fiscalId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT urgency_level, label, color FROM tbl_urgency_level
             WHERE fiscal_id = :fiscal_id ORDER BY urgency_level ASC"
        );
        $stmt->execute(['fiscal_id' => $fiscalId]);
        // ดึงหลายแถว (fetchAll ไม่ใช่ fetch เดี่ยว เพราะคาดว่าจะได้ 3 แถว)
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // ถ้าไม่มีแถวเลย (array ว่าง) แปลว่าปีนี้ยังไม่เคยตั้งค่าความเร่งด่วนมาก่อน
        if (!$rows) {
            $insert = $this->pdo->prepare(
                "INSERT INTO tbl_urgency_level (fiscal_id, urgency_level, label, color)
                 VALUES (:fiscal_id, :urgency_level, :label, :color)"
            );
            // วนสร้างทีละแถวจากค่า default ที่เก็บไว้ด้านบน (3 รอบ = 3 แถว)
            foreach ($this->defaultUrgencyLevels as $level) {
                $insert->execute([
                    'fiscal_id' => $fiscalId,
                    'urgency_level' => $level['urgency_level'],
                    'label' => $level['label'],
                    'color' => $level['color'],
                ]);
            }
            // ใช้ค่า default ที่เพิ่ง insert ไปเป็นผลลัพธ์ที่ return เลย ไม่ต้อง SELECT ซ้ำ
            $rows = $this->defaultUrgencyLevels;
        }

        return $rows;
    }

    // อัปเดตแค่ notify_days ของปีนี้
    public function updateNotifyDays($fiscalId, $notifyDays, $userId)
    {
        $stmt = $this->pdo->prepare(
            "UPDATE tbl_registration_task_setting
             SET notify_day = :notify_day, update_at = NOW(), update_user_id = :user_id
             WHERE fiscal_id = :fiscal_id"
        );
        return $stmt->execute([
            'notify_day' => $notifyDays,
            'user_id' => $userId,
            'fiscal_id' => $fiscalId,
        ]);
    }

    // อัปเดต label/color ของ "1 ระดับ" ในปีนี้ (เรียกทีละระดับ รวม 3 ครั้งตอน save)
    public function updateUrgencyLevel($fiscalId, $urgencyLevel, $label, $color)
    {
        $stmt = $this->pdo->prepare(
            "UPDATE tbl_urgency_level
             SET label = :label, color = :color
             WHERE fiscal_id = :fiscal_id AND urgency_level = :urgency_level"
        );
        return $stmt->execute([
            'label' => $label,
            'color' => $color,
            'fiscal_id' => $fiscalId,
            'urgency_level' => $urgencyLevel,
        ]);
    }
}
