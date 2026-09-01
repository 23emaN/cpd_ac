<?php
// app/controllers/MainController.php

class MainController
{

    public function index()
    {
        // 1. ตรวจสอบว่าผู้ใช้ Login หรือยัง?
        session_start();
        if (!isset($_SESSION['user_id'])) {
            // ถ้ายังไม่ Login ให้เด้งกลับไปหน้า login
            header("Location: /cpd_ac/public/login");
            exit();
        }

        // 2. เตรียมข้อมูลส่งไปที่ View (MVC Pattern)
        $data = [
            'title' => 'CPD ACC - ระบบบริหารสำนักงานบัญชี',
            'user_id' => $_SESSION['user_id'] ?? '',
            'user_name' => $_SESSION['user_name'] ?? '',
            'firstname' => $_SESSION['user_firstname'] ?? '',
            'lastname' => $_SESSION['user_lastname'] ?? '',
            'is_super_admin' => $_SESSION['is_super_admin'] ?? '0'
        ];

        // 3. เรียก View มาแสดงผล
        require_once '../app/views/main/index.php';
    }

    public function logout()
    {
        session_start();
        session_destroy();
        header("Location: /cpd_ac/public/login");
        exit();
    }


}
