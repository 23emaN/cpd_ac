<?php
// app/controllers/AuthController.php

class AuthController {
    
    public function showLogin() {
        $data = [
            'title' => 'เข้าสู่ระบบ (CPDTH)'
        ];
        require_once '../app/views/auth/login.php';
    }

    // เมธอดสำหรับประมวลผลตอนที่ผู้ใช้กดปุ่ม "เข้าสู่ระบบ" (รับค่าจาก Ajax)
    public function processLogin() {
        // 1. รับค่าที่ Ajax ส่งมา (username, password)
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        // 2. ตรวจสอบกับ Database โดยการเรียก Model (ทำในอนาคต)
        // ตัวอย่างจำลองการตอบกลับไปหา Javascript
        if ($username === '' || $password === '') {
            echo json_encode(['result' => 0, 'msg' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
            return;
        }

        // จำลองสถานการณ์: ส่งข้อความกลับไปหา Ajax ว่าระบบหลังบ้านยังไม่เสร็จ
        echo json_encode(['result' => 0, 'msg' => 'ยังไม่ได้เชื่อมต่อ Database']);
    }
}
