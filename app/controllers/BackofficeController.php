<?php
// app/controllers/BackofficeController.php

class BackofficeController
{
    private $userPayload = null;

    private function checkAuth()
    {
        require_once '../app/models/AuthModel.php';
        $user = \App\Models\AuthModel::checkWebAuth();
        
        if (!$user) {
            header("Location: " . BASE_URL . "/login");
            exit();
        }

        $this->userPayload = $user;
    }

    /////////////////////////////////////// index /////////////////////////////////////////////// 
    public function index()
    {
        // 1. ตรวจสอบสิทธิ์ผู้ใช้ก่อน
        $this->checkAuth();

        // 2. รับค่า fiscal_id จาก Session (ตั้งค่ามาจากหน้าหลักผ่าน AJAX)
        $fiscal_id = $_SESSION['fiscal_year_id'] ?? null;

        if (!$fiscal_id) {
            // ถ้าไม่มีรหัสปี ให้เด้งกลับไปหน้าหลัก
            header("Location: " . BASE_URL . "/main");
            exit();
        }

        require_once '../app/models/CompanyModel.php';
        $companyModel = new CompanyModel();
        $userId = $this->userPayload['user_id'] ?? null;
        $companies = $companyModel->getAllCompanies($userId);

        // หา company_id ของ fiscal_id ที่กำลังใช้งานอยู่
        $active_company_id = '';
        foreach ($companies as $company) {
            if (isset($company['fiscal_years'])) {
                foreach ($company['fiscal_years'] as $fy) {
                    $fy_id = $fy['fiscal_id'] ?? $fy['id'] ?? '';
                    if ($fy_id == $fiscal_id) {
                        $active_company_id = $company['company_id'] ?? $company['id'] ?? '';
                        break 2;
                    }
                }
            }
        }

        // 3. เตรียมข้อมูลเบื้องต้นสำหรับส่งไปหน้า View (ถ้ามี)
        $data = [
            'title' => 'ระบบ Backoffice',
            'user' => $this->userPayload,
            'user_id' => $this->userPayload['user_id'] ?? '',
            'firstname' => $this->userPayload['user_firstname'] ?? '',
            'lastname' => $this->userPayload['user_lastname'] ?? '',
            'is_super_admin' => $this->userPayload['is_super_admin'] ?? '0',
            'fiscal_id' => $fiscal_id,
            'companies' => $companies,
            'active_company_id' => $active_company_id
        ];

        // 4. ดึงหน้า View มาแสดงผล
        require_once '../app/views/backoffice/index.php';
    }

    /////////////////////////////////////// tasks /////////////////////////////////////////////// 
    public function tasks()
    {
        // 1. ตรวจสอบสิทธิ์ผู้ใช้ก่อน
        $this->checkAuth();

        // 2. รับค่า fiscal_id จาก Session (ตั้งค่ามาจากหน้าหลักผ่าน AJAX)
        $fiscal_id = $_SESSION['fiscal_year_id'] ?? null;

        if (!$fiscal_id) {
            // ถ้าไม่มีรหัสปี ให้เด้งกลับไปหน้าหลัก
            header("Location: " . BASE_URL . "/main");
            exit();
        }

        require_once '../app/models/CompanyModel.php';
        $companyModel = new CompanyModel();
        $userId = $this->userPayload['user_id'] ?? null;
        $companies = $companyModel->getAllCompanies($userId);

        // หา company_id ของ fiscal_id ที่กำลังใช้งานอยู่
        $active_company_id = '';
        foreach ($companies as $company) {
            if (isset($company['fiscal_years'])) {
                foreach ($company['fiscal_years'] as $fy) {
                    $fy_id = $fy['fiscal_id'] ?? $fy['id'] ?? '';
                    if ($fy_id == $fiscal_id) {
                        $active_company_id = $company['company_id'] ?? $company['id'] ?? '';
                        break 2;
                    }
                }
            }
        }

        // 3. เตรียมข้อมูลเบื้องต้นสำหรับส่งไปหน้า View (ถ้ามี)
        $data = [
            'title' => 'ระบบ Backoffice',
            'user' => $this->userPayload,
            'user_id' => $this->userPayload['user_id'] ?? '',
            'firstname' => $this->userPayload['user_firstname'] ?? '',
            'lastname' => $this->userPayload['user_lastname'] ?? '',
            'is_super_admin' => $this->userPayload['is_super_admin'] ?? '0',
            'fiscal_id' => $fiscal_id,
            'companies' => $companies,
            'active_company_id' => $active_company_id
        ];

        // Fetch tasks for the current fiscal_id
        require_once '../app/models/tasks.php';
        $taskModel = new TasksModel();
        $tasks_list = $taskModel->getTasksByFiscalId($fiscal_id);

        $data['tasks_list'] = $tasks_list;
        $data['total_tasks'] = count($tasks_list);
        
        $req_amount_count = 0;
        foreach($tasks_list as $t) {
            if ($t['is_notify_amount'] == 1) $req_amount_count++;
        }
        $data['req_amount_count'] = $req_amount_count;
        $data['no_req_amount_count'] = $data['total_tasks'] - $req_amount_count;

        // 4. ดึงหน้า View มาแสดงผล
        require_once '../app/views/backoffice/tasks.php';
    }


    public function addTask()
    {
        $this->checkAuth();

        // 1. รับค่าจากฟอร์ม
        $task_name = trim($_POST['task_name'] ?? '');
        $is_notify_amount = trim($_POST['is_notify_amount'] ?? '0');
        $fiscal_id = trim($_POST['fiscal_id'] ?? '');
        
        // 2. ดักตรวจสอบ (Validation)
        if ($task_name === '') {
            echo json_encode(['result' => 0, 'msg' => 'กรุณากรอกชื่องาน']);
            return;
        }

        if ($fiscal_id === '') {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบข้อมูลปีทำงาน (Fiscal ID)']);
            return;
        }

        // 3. เรียกใช้งาน TasksModel
        require_once '../app/models/tasks.php';
        $taskModel = new TasksModel();
        
        try {
            // ส่งค่าไปบันทึก
            $success = $taskModel->insertTask($fiscal_id, $task_name, $is_notify_amount);

            if ($success) {
                echo json_encode(['result' => 1, 'msg' => 'เพิ่มงานเรียบร้อยแล้ว']);
            } else {
                echo json_encode(['result' => 0, 'msg' => 'ไม่สามารถบันทึกข้อมูลได้']);
            }
        } catch (PDOException $e) {
            echo json_encode(['result' => 0, 'msg' => 'เกิดข้อผิดพลาดฐานข้อมูล: ' . $e->getMessage()]);
        }
    }
}
