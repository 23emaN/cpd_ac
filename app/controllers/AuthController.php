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

        if ($username === '' || $password === '') {
            echo json_encode(['result' => 0, 'msg' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
            return;
        }

        // 2. โหลด Config และเชื่อมต่อ Database
        require_once '../vendor/autoload.php';
        require_once '../app/core/Database/Connection.php';
        $connection = new \App\Database\Connection();
        $pdo = $connection->getPdo();

        try {
            // ค้นหาผู้ใช้จากฐานข้อมูล
            $stmt = $pdo->prepare("SELECT * FROM tbl_user WHERE user_name = :username");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch();

            if ($user) {
                // ตรวจสอบว่าบัญชีถูกระงับหรือไม่ (ถ้า user_status เป็น '1' คือใช้งานได้)
                if ($user['user_status'] !== '1') {
                    echo json_encode(['result' => 0, 'msg' => 'บัญชีผู้ใช้นี้ถูกระงับการใช้งาน']);
                    return;
                }

                // 3. ตรวจสอบรหัสผ่านที่ถูกเข้ารหัสไว้ด้วย password_verify
                if (password_verify($password, $user['user_password'])) {
                    // หากตรงกัน ให้บันทึก Session ไว้ใช้งาน
                    session_start();
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['user_name'] = $user['user_name'];
                    $_SESSION['user_firstname'] = $user['user_firstname'];
                    $_SESSION['user_lastname'] = $user['user_lastname'];
                    $_SESSION['is_super_admin'] = $user['is_super_admin'];

                    echo json_encode(['result' => 1, 'msg' => 'เข้าสู่ระบบสำเร็จ']);
                } else {
                    echo json_encode(['result' => 0, 'msg' => 'รหัสผ่านไม่ถูกต้อง']);
                }
            } else {
                echo json_encode(['result' => 0, 'msg' => 'ไม่พบชื่อผู้ใช้งานนี้ในระบบ']);
            }
        } catch (PDOException $e) {
            echo json_encode(['result' => 0, 'msg' => 'เกิดข้อผิดพลาดของฐานข้อมูล: ' . $e->getMessage()]);
        }
    }
}
