<?php
// app/controllers/MainController.php

class MainController
{

    private $userPayload = null;

    // Map URL => ชื่อหน้าภาษาไทย
    private $pageTitles = [
        'main'                => 'หน้าหลัก',
        'backoffice'          => 'แดชบอร์ด',
        'dashboard_workspace' => 'Workspace',
        'tasks'               => 'งานทั้งหมด',
        'customer'            => 'ลูกค้า',
        'employee'            => 'พนักงาน',
        'registration_board'  => 'ทะเบียนงาน',
        'post_it'             => 'Post-it',
        'closing'             => 'ปิดงาน',
        'issues'              => 'ปัญหา',
        'yearly_dash'         => 'รายงานรายปี',
        'monthly_dash'        => 'รายงานรายเดือน',
        'customer_dash'       => 'รายงานลูกค้า',
        'customer_message'    => 'ข้อความลูกค้า',
        'assign_task'         => 'มอบหมายงาน',
        'monthly_task'        => 'งานรายเดือน',
        'customer_drive'      => 'ไดรฟ์ลูกค้า',
        'portal'              => 'Portal',
        'notifications'       => 'การแจ้งเตือน',
        'system_setting'      => 'ตั้งค่าระบบ',
        'manual'              => 'คู่มือ',
        'setting_manual'      => 'จัดการคู่มือ',
    ];

    // ดึงชื่อหน้าจาก URL ปัจจุบัน
    private function getPageTitle(): string
    {
        $uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        // ตัด BASE_URL prefix ออกถ้ามี (กรณี run ใน subfolder)
        $basePath = trim(parse_url(BASE_URL, PHP_URL_PATH) ?? '', '/');
        if ($basePath !== '' && str_starts_with($uri, $basePath . '/')) {
            $uri = substr($uri, strlen($basePath) + 1);
        }
        // ใช้แค่ segment แรก
        $segment = explode('/', $uri)[0];
        return $this->pageTitles[$segment] ?? ucfirst($segment);
    }

    // ตรวจสอบการ Login ไว้เป็นฟังก์ชันส่วนตัว จะได้ไม่ต้องเขียนซ้ำ
    private function checkAuth()
    {
        require_once '../app/models/AuthModel.php';
        $user = \App\models\AuthModel::checkWebAuth();
        
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

        $fiscal_years = [];
        if (!empty($companies) && isset($companies[0]['fiscal_years'])) {
            $fiscal_years = $companies[0]['fiscal_years'];
        }


        // 2. เตรียมข้อมูลส่งไปที่ View (MVC Pattern)
        $data = [
            'title' => 'Account - ' . $this->getPageTitle(),
            'user_id' => $this->userPayload['user_id'] ?? '',
            'user_name' => $this->userPayload['user_name'] ?? '',
            'firstname' => $this->userPayload['user_firstname'] ?? '',
            'lastname' => $this->userPayload['user_lastname'] ?? '',
            'is_super_admin' => $this->userPayload['is_super_admin'] ?? '0',
            'companies' => $companies,
            'fiscal_years' => $fiscal_years
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

        if ($companyModel->isCompanyNameExists($companyName)) {
            echo json_encode(['result' => 0, 'msg' => 'ชื่อบริษัทนี้มีอยู่ในระบบแล้ว กรุณาใช้ชื่ออื่น']);
            return;
        }

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

    public function editCompany()
    {
        $this->checkAuth();

        $companyId = trim($_POST['company_id'] ?? '');
        $companyName = trim($_POST['company_name'] ?? '');

        if ($companyName === '') {
            echo json_encode(['result' => 0, 'msg' => 'กรุณากรอกชื่อบริษัท']);
            return;
        }

        if ($companyId === '') {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบข้อมูลบริษัท']);
            return;
        }

        require_once '../app/models/CompanyModel.php';
        $companyModel = new CompanyModel();

        if ($companyModel->isCompanyNameExistsExcept($companyName, $companyId)) {
            echo json_encode(['result' => 0, 'msg' => 'ชื่อบริษัทนี้มีอยู่ในระบบแล้ว กรุณาใช้ชื่ออื่น']);
            return;
        }

        try {
            $userId = $this->userPayload['user_id'] ?? null;
            $success = $companyModel->updateCompany($companyId, $companyName, $userId);

            if ($success) {
                echo json_encode(['result' => 1, 'msg' => 'แก้ไขบริษัทเรียบร้อยแล้ว']);
            } else {
                echo json_encode(['result' => 0, 'msg' => 'ไม่สามารถบันทึกข้อมูลได้ หรือไม่มีสิทธิ์แก้ไข']);
            }
        } catch (PDOException $e) {
            echo json_encode(['result' => 0, 'msg' => 'เกิดข้อผิดพลาดของฐานข้อมูล: ' . $e->getMessage()]);
        }
    }

    public function addFiscalYear()
    {
        $this->checkAuth();

        $companyId    = trim($_POST['company_id'] ?? '');
        $workingYear  = trim($_POST['working_year'] ?? '');
        $copyFromYear = trim($_POST['copy_from_year'] ?? '');
        // รับ array ของตัวเลือกที่ต้องการคัดลอก เช่น ['customers', 'employees', 'monthly_jobs']
        $copyOptions  = $_POST['copy_options'] ?? [];
        if (!is_array($copyOptions)) {
            $copyOptions = [];
        }

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
            $newFiscalId = $fiscalYearModel->insertFiscalYears($companyId, $workingYear, $copyFromYear, $copyOptions);

            if ($newFiscalId) {
                echo json_encode(['result' => 1, 'msg' => 'บันทึกปีทำงานเรียบร้อยแล้ว']);
            } else {
                echo json_encode(['result' => 0, 'msg' => 'ไม่สามารถบันทึกข้อมูลได้ (อาจไม่มีบริษัทนี้ในระบบ)']);
            }
        } catch (PDOException $e) {
            echo json_encode(['result' => 0, 'msg' => 'เกิดข้อผิดพลาดฐานข้อมูล: ' . $e->getMessage()]);
        }
    }

    public function editFiscalYear()
    {
        $this->checkAuth();

        $fiscalId    = trim($_POST['fiscal_id'] ?? '');
        $workingYear = trim($_POST['working_year'] ?? '');

        if ($workingYear === '') {
            echo json_encode(['result' => 0, 'msg' => 'กรุณากรอกปี พ.ศ.']);
            return;
        }

        if ($fiscalId === '') {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบข้อมูลปีทำงาน']);
            return;
        }

        require_once '../app/models/fiscal_years.php';
        $fiscalYearModel = new FiscalYearsModel();
        
        try {
            $success = $fiscalYearModel->updateFiscalYear($fiscalId, $workingYear);

            if ($success) {
                echo json_encode(['result' => 1, 'msg' => 'แก้ไขข้อมูลปีทำงานเรียบร้อยแล้ว']);
            } else {
                echo json_encode(['result' => 0, 'msg' => 'ไม่สามารถบันทึกข้อมูลได้']);
            }
        } catch (PDOException $e) {
            echo json_encode(['result' => 0, 'msg' => 'เกิดข้อผิดพลาดฐานข้อมูล: ' . $e->getMessage()]);
        }
    }

    public function getFiscalYears()
    {
        $this->checkAuth();
        $companyId = $_GET['company_id'] ?? null;
        if (!$companyId) {
            echo json_encode(['result' => 0, 'data' => []]);
            return;
        }
        
        $userId = $this->userPayload['user_id'] ?? null;

        require_once '../app/models/fiscal_years.php';
        $fiscalYearModel = new FiscalYearsModel();
        $fiscalYears = $fiscalYearModel->getFiscalYearsByCompany($companyId, $userId);
        
        echo json_encode(['result' => 1, 'data' => $fiscalYears]);
    }

    public function setContext()
    {
        $this->checkAuth();
        
        $fiscal_id = $_POST['fiscal_id'] ?? null;
        if ($fiscal_id) {
            $_SESSION['fiscal_year_id'] = $fiscal_id;
            echo json_encode(['result' => 1, 'msg' => 'Context set']);
        } else {
            echo json_encode(['result' => 0, 'msg' => 'Missing fiscal_id']);
        }
    }

    public function logout()
    {
        // 1. (Optional) Invalidate token in database if we want strictly stateful JWT
        require_once '../app/models/AuthModel.php';
        $jwt = \App\models\AuthModel::bearerToken();
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

