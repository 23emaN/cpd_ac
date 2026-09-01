<?php
// app/controllers/MainController.php

class MainController
{

    private $userPayload = null;

    // ตรวจสอบการ Login ไว้เป็นฟังก์ชันส่วนตัว จะได้ไม่ต้องเขียนซ้ำ
    private function checkAuth()
    {
        require_once '../app/core/Utility/Auth.php';
        $user = \App\Utility\Auth::checkWebAuth();
        
        if (!$user) {
            // ถ้าเช็ค Token ไม่ผ่าน ให้เด้งไปหน้า Login
            header("Location: " . BASE_URL . "/login");
            exit();
        }

        // เก็บข้อมูล user ไว้ใช้ใน Class
        $this->userPayload = $user;
    }

    public function index()
    {
        $this->checkAuth();

        require_once '../app/models/CompanyModel.php';
        $companyModel = new CompanyModel();
        $userId = $this->userPayload['user_id'] ?? null;
        $companies = $companyModel->getAllCompanies($userId);

        // 2. เตรียมข้อมูลส่งไปที่ View (MVC Pattern)
        $data = [
            'title' => 'CPD ACC - ระบบบริหารสำนักงานบัญชี',
            'user_id' => $this->userPayload['user_id'] ?? '',
            'user_name' => $this->userPayload['user_name'] ?? '',
            'firstname' => $this->userPayload['user_firstname'] ?? '',
            'lastname' => $this->userPayload['user_lastname'] ?? '',
            'is_super_admin' => $this->userPayload['is_super_admin'] ?? '0',
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
            $userId = $this->userPayload['user_id'] ?? null;
            $success = $companyModel->insertCompany($companyName, $userId);

            if ($success) {
                echo json_encode(['result' => 1, 'msg' => 'เพิ่มบริษัทเรียบร้อยแล้ว']);
            } else {
                echo json_encode(['result' => 0, 'msg' => 'ไม่สามารถบันทึกข้อมูลได้']);
            }
        } catch (PDOException $e) {
            echo json_encode(['result' => 0, 'msg' => 'เกิดข้อผิดพลาดของฐานข้อมูล: ' . $e->getMessage()]);
        }
    }

    public function addFiscalYear()
    {
        $this->checkAuth();

        $companyId = trim($_POST['company_id'] ?? '');
        $workingYear = trim($_POST['working_year'] ?? '');
        $copyFromYear = trim($_POST['copy_from_year'] ?? '');
        
        if ($workingYear === '') {
            echo json_encode(['result' => 0, 'msg' => 'กรุณากรอกปี พ.ศ.']);
            return;
        }

        if ($companyId === '') {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบข้อมูลบริษัท กรุณาเลือกบริษัทก่อน']);
            return;
        }

        require_once '../app/models/fiscal_years.php';
        $fiscalYearModel = new FiscalYearsModel();
        
        try {
            $success = $fiscalYearModel->insertFiscalYears($companyId, $workingYear, $copyFromYear);

            if ($success) {
                echo json_encode(['result' => 1, 'msg' => 'บันทึกปีทำงานเรียบร้อยแล้ว']);
            } else {
                echo json_encode(['result' => 0, 'msg' => 'ไม่สามารถบันทึกข้อมูลได้']);
            }
        } catch (PDOException $e) {
            echo json_encode(['result' => 0, 'msg' => 'เกิดข้อผิดพลาดฐานข้อมูล: ' . $e->getMessage()]);
        }
    }

    public function logout()
    {
        // 1. (Optional) Invalidate token in database if we want strictly stateful JWT
        require_once '../app/core/Utility/Auth.php';
        $jwt = \App\Utility\Auth::bearerToken();
        if ($jwt !== '') {
            try {
                $secretKey = $_ENV['JWT_SECRET'] ?? '';
                $token = \Firebase\JWT\JWT::decode($jwt, new \Firebase\JWT\Key($secretKey, 'HS256'));
                if (!empty($token->jti)) {
                    $db = (new \App\Database\Connection())->getPdo();
                    $stmt = $db->prepare("UPDATE tbl_login_token SET end_datetime = NOW() WHERE token_code = :jti");
                    $stmt->execute([':jti' => $token->jti]);
                }
            } catch (\Throwable $e) {
                // Ignore error on logout
            }
        }

        // 2. ลบ Cookie
        setcookie('bo_access_token', '', time() - 3600, '/');

        // กลับไปหน้า Login
        header("Location: " . BASE_URL . "/login");
        exit();
    }
}
