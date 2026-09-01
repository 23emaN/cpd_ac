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

        // 2. โหลด Model เพื่อดึงข้อมูลผู้ใช้จากฐานข้อมูล
        require_once '../app/models/UserModel.php';
        $userModel = new UserModel();

        try {
            // ค้นหาผู้ใช้จากฐานข้อมูลผ่าน Model
            $user = $userModel->getUserByUsername($username);

            if ($user) {
                // ตรวจสอบว่าบัญชีถูกระงับหรือไม่ (ถ้า user_status เป็น '1' คือใช้งานได้)
                if ($user['user_status'] !== '1') {
                    echo json_encode(['result' => 0, 'msg' => 'บัญชีผู้ใช้นี้ถูกระงับการใช้งาน']);
                    return;
                }

                // 3. ตรวจสอบรหัสผ่านที่ถูกเข้ารหัสไว้ด้วย password_verify
                if (password_verify($password, $user['user_password'])) {
                    // หากตรงกัน ให้บันทึก Token
                    require_once '../app/core/Utility/Auth.php';
                    $token = \App\Utility\Auth::generateToken($user);
                    
                    // Set cookie for 1 day
                    setcookie('bo_access_token', $token, time() + 86400, '/');

                    echo json_encode(['result' => 1, 'msg' => 'เข้าสู่ระบบสำเร็จ', 'token' => $token]);
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
