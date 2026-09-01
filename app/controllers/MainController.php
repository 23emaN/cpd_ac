<?php
// app/controllers/MainController.php

class MainController
{

    // ตรวจสอบการ Login ไว้เป็นฟังก์ชันส่วนตัว จะได้ไม่ต้องเขียนซ้ำ
    private function checkAuth()
    {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/login");
            exit();
        }
    }

    public function index()
    {
        $this->checkAuth();

        require_once '../app/models/CompanyModel.php';
        $companyModel = new CompanyModel();
        $companies = $companyModel->getAllCompanies();

        // 2. เตรียมข้อมูลส่งไปที่ View (MVC Pattern)
        $data = [
            'title' => 'CPD ACC - ระบบบริหารสำนักงานบัญชี',
            'user_id' => $_SESSION['user_id'] ?? '',
            'user_name' => $_SESSION['user_name'] ?? '',
            'firstname' => $_SESSION['user_firstname'] ?? '',
            'lastname' => $_SESSION['user_lastname'] ?? '',
            'is_super_admin' => $_SESSION['is_super_admin'] ?? '0',
            'companies' => $companies
        ];

        // 3. เรียก View มาแสดงผล
        require_once '../app/views/main/index.php';
    }

    public function addCompany()
    {
        $this->checkAuth();

        $companyName = trim($_POST['company_name'] ?? '');

        if ($companyName === '') {
            echo json_encode(['result' => 0, 'msg' => 'กรุณากรอกชื่อบริษัท']);
            return;
        }

        require_once '../app/models/CompanyModel.php';
        $companyModel = new CompanyModel();

        try {
            $success = $companyModel->insertCompany($companyName);

            if ($success) {
                echo json_encode(['result' => 1, 'msg' => 'เพิ่มบริษัทเรียบร้อยแล้ว']);
            } else {
                echo json_encode(['result' => 0, 'msg' => 'ไม่สามารถบันทึกข้อมูลได้']);
            }
        } catch (PDOException $e) {
            echo json_encode(['result' => 0, 'msg' => 'เกิดข้อผิดพลาดของฐานข้อมูล: ' . $e->getMessage()]);
        }
    }

    public function logout()
    {
        session_start();
        session_destroy();
        header("Location: " . BASE_URL . "/login");
        exit();
    }
}
