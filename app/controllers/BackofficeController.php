<?php
// app/controllers/BackofficeController.php

class BackofficeController
{
    private $userPayload = null;

    private function checkAuth()
    {
        require_once '../app/models/AuthModel.php';
        $user = \App\models\AuthModel::checkWebAuth();

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
        $active_fiscal_year = '';
        foreach ($companies as $company) {
            if (isset($company['fiscal_years'])) {
                foreach ($company['fiscal_years'] as $fy) {
                    $fy_id = $fy['fiscal_id'] ?? $fy['id'] ?? '';
                    if ($fy_id == $fiscal_id) {
                        $active_company_id = $company['company_id'] ?? $company['id'] ?? '';
                        $active_fiscal_year = $fy['fiscal_years'] ?? $fy['working_year'] ?? $fy['year'] ?? '';
                        break 2;
                    }
                }
            }
        }

        // Monthly Stats
        $month = $_GET['month'] ?? date('m');
        $monthStr = str_pad($month, 2, '0', STR_PAD_LEFT);

        require_once '../app/models/MonthlyDashModel.php';
        $monthlyDashModel = new MonthlyDashModel();
        $monthlyStats = $monthlyDashModel->getDashboardStats($fiscal_id, $monthStr);

        // Yearly Stats
        require_once '../app/models/closing_Model.php';
        $closingModel = new ClosingModel();
        $closingList = $closingModel->getClosingByFiscalId($fiscal_id);

        $totalClosing = count($closingList);
        $closingDone = 0;
        $docDone = 0;
        $boj5Done = 0;
        $dbdDone = 0;
        $pnd50Done = 0;

        foreach ($closingList as $item) {
            if (((string) ($item['closing_status'] ?? '')) === '1')
                $closingDone++;
            if (((string) ($item['doc_status'] ?? '')) === '1' || ((string) ($item['audit_status'] ?? '')) === '1')
                $docDone++;
            if (((string) ($item['boj5_status'] ?? '')) === '1')
                $boj5Done++;
            if (((string) ($item['dbd_efiling_status'] ?? '')) === '1')
                $dbdDone++;
            if (((string) ($item['pnd50_status'] ?? '')) === '1')
                $pnd50Done++;
        }

        $yearlyStats = [
            'total' => $totalClosing,
            'closing_completed' => $closingDone,
            'doc_received' => $docDone,
            'boj5' => $boj5Done,
            'dbd' => $dbdDone,
            'pnd50' => $pnd50Done,
            'closing_completed_pct' => $totalClosing > 0 ? round(($closingDone / $totalClosing) * 100, 1) : 0,
            'audit_completed_pct' => $totalClosing > 0 ? round(($docDone / $totalClosing) * 100, 1) : 0,
            'boj5_pct' => $totalClosing > 0 ? round(($boj5Done / $totalClosing) * 100, 1) : 0,
            'dbd_pct' => $totalClosing > 0 ? round(($dbdDone / $totalClosing) * 100, 1) : 0,
            'pnd50_pct' => $totalClosing > 0 ? round(($pnd50Done / $totalClosing) * 100, 1) : 0,
        ];

        // Customer & Caretaker General Stats
        require_once '../app/models/CustomerModal.php';
        $customerModal = new CustomModal();
        $customerStats = $customerModal->getCustomersgid($fiscal_id);
        $caretakers = $customerModal->getCaretakers();

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
            'active_company_id' => $active_company_id,
            'active_fiscal_year' => $active_fiscal_year,
            'selected_month' => $monthStr,
            'monthly_stats' => $monthlyStats,
            'yearly_stats' => $yearlyStats,
            'customer_stats' => $customerStats,
            'caretakers_count' => count($caretakers)
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

        // หา company_id และปีของ fiscal_id ที่กำลังใช้งานอยู่
        $active_company_id = '';
        $active_fiscal_year = '';
        foreach ($companies as $company) {
            if (isset($company['fiscal_years'])) {
                foreach ($company['fiscal_years'] as $fy) {
                    $fy_id = $fy['fiscal_id'] ?? $fy['id'] ?? '';
                    if ($fy_id == $fiscal_id) {
                        $active_company_id = $company['company_id'] ?? $company['id'] ?? '';
                        $active_fiscal_year = $fy['fiscal_years'] ?? $fy['working_year'] ?? $fy['year'] ?? '';
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
            'active_company_id' => $active_company_id,
            'active_fiscal_year' => $active_fiscal_year
        ];

        // Fetch tasks for the current fiscal_id
        require_once '../app/models/tasks.php';
        $taskModel = new TasksModel();
        $tasks_list = $taskModel->getTasksByFiscalId($fiscal_id);

        $data['tasks_list'] = $tasks_list;
        $data['total_tasks'] = count($tasks_list);

        $req_amount_count = 0;
        foreach ($tasks_list as $t) {
            if ($t['is_notify_amount'] == 1)
                $req_amount_count++;
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

    public function moveTask()
    {
        $this->checkAuth();
        $tasks_id = $_POST['tasks_id'] ?? '';
        $direction = $_POST['direction'] ?? '';
        $fiscal_id = $_POST['fiscal_id'] ?? '';

        if (!$tasks_id || !$direction || !$fiscal_id) {
            echo json_encode(['result' => 0, 'msg' => 'ข้อมูลไม่ครบถ้วน']);
            return;
        }

        require_once '../app/models/tasks.php';
        $taskModel = new TasksModel();

        $success = $taskModel->moveTaskOrder($tasks_id, $direction, $fiscal_id);

        if ($success) {
            echo json_encode(['result' => 1, 'msg' => 'เลื่อนลำดับสำเร็จ']);
        } else {
            echo json_encode(['result' => 0, 'msg' => 'ไม่สามารถเลื่อนลำดับได้ (อาจอยู่บนสุดหรือล่างสุดแล้ว)']);
        }
    }

    public function getTask()
    {
        $this->checkAuth();
        $tasks_id = $_POST['tasks_id'] ?? '';

        if (!$tasks_id) {
            echo json_encode(['result' => 0, 'msg' => 'ข้อมูลไม่ครบถ้วน']);
            return;
        }

        require_once '../app/models/tasks.php';
        $taskModel = new TasksModel();

        $task = $taskModel->getTaskById($tasks_id);

        if ($task) {
            echo json_encode(['result' => 1, 'data' => $task]);
        } else {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบข้อมูลงาน']);
        }
    }

    public function editTask()
    {
        $this->checkAuth();
        $tasks_id = $_POST['tasks_id'] ?? '';
        $task_name = $_POST['task_name'] ?? '';
        $is_notify_amount = $_POST['is_notify_amount'] ?? '0';

        if (!$tasks_id || !$task_name) {
            echo json_encode(['result' => 0, 'msg' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
            return;
        }

        require_once '../app/models/tasks.php';
        $taskModel = new TasksModel();

        $success = $taskModel->updateTask($tasks_id, $task_name, $is_notify_amount);

        if ($success) {
            echo json_encode(['result' => 1, 'msg' => 'แก้ไขสำเร็จ']);
        } else {
            echo json_encode(['result' => 0, 'msg' => 'เกิดข้อผิดพลาดในการแก้ไขข้อมูล']);
        }
    }

    public function deleteTask()
    {
        $this->checkAuth();
        $tasks_id = $_POST['tasks_id'] ?? '';

        if (!$tasks_id) {
            echo json_encode(['result' => 0, 'msg' => 'ข้อมูลไม่ครบถ้วน']);
            return;
        }

        require_once '../app/models/tasks.php';
        $taskModel = new TasksModel();

        $success = $taskModel->softDeleteTask($tasks_id);

        if ($success) {
            echo json_encode(['result' => 1, 'msg' => 'ลบข้อมูลสำเร็จ']);
        } else {
            echo json_encode(['result' => 0, 'msg' => 'เกิดข้อผิดพลาดในการลบข้อมูล']);
        }
    }

    /////////////////////////////////////// fiscica /////////////////////////////////////////////// 

    public function employee()
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

        // หา company_id และปีของ fiscal_id ที่กำลังใช้งานอยู่
        $active_company_id = '';
        $active_fiscal_year = '';
        foreach ($companies as $company) {
            if (isset($company['fiscal_years'])) {
                foreach ($company['fiscal_years'] as $fy) {
                    $fy_id = $fy['fiscal_id'] ?? $fy['id'] ?? '';
                    if ($fy_id == $fiscal_id) {
                        $active_company_id = $company['company_id'] ?? $company['id'] ?? '';
                        $active_fiscal_year = $fy['fiscal_years'] ?? $fy['working_year'] ?? $fy['year'] ?? '';
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
            'active_company_id' => $active_company_id,
            'active_fiscal_year' => $active_fiscal_year
        ];

        // ดึงข้อมูลทีม
        require_once '../app/models/TeamModel.php';
        $teamModel = new TeamModel();
        $data['teams'] = $teamModel->getAllTeams();

        // ดึงข้อมูลพนักงาน
        require_once '../app/models/UserModel.php';
        $userModel = new UserModel();
        $data['employees'] = $userModel->getEmployeesByFiscalAndCompany($fiscal_id, $active_company_id);

        // 4. ดึงหน้า View มาแสดงผล
        require_once '../app/views/backoffice/employee.php';
    }

    public function addEmployee()
    {
        $this->checkAuth();

        // 1. รับค่าจากฟอร์ม
        $fiscal_id = trim($_POST['fiscal_id'] ?? '');
        $company_id = trim($_POST['company_id'] ?? '');
        $user_name = trim($_POST['user_name'] ?? '');
        $user_password = trim($_POST['user_password'] ?? '');
        $user_firstname = trim($_POST['user_firstname'] ?? '');
        $user_lastname = trim($_POST['user_lastname'] ?? '');
        $user_position = trim($_POST['user_position'] ?? '');
        $team_name = trim($_POST['team_name'] ?? '');

        // 2. ดักตรวจสอบ (Validation)
        if ($user_name === '' || $user_password === '' || $user_firstname === '' || $user_lastname === '') {
            echo json_encode(['result' => 0, 'msg' => 'กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน']);
            return;
        }

        if ($fiscal_id === '' || $company_id === '') {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบข้อมูลปีทำงานหรือบริษัท']);
            return;
        }

        require_once '../app/models/UserModel.php';
        require_once '../app/models/TeamModel.php';
        $userModel = new UserModel();
        $teamModel = new TeamModel();

        try {
            // เช็คว่า username ซ้ำไหม
            $existingUser = $userModel->getUserByUsername($user_name);
            if ($existingUser) {
                echo json_encode(['result' => 0, 'msg' => 'ชื่อผู้ใช้นี้มีในระบบแล้ว กรุณาใช้ชื่ออื่น']);
                return;
            }

            // จัดการเรื่องทีม
            $team_id = null;
            if ($team_name !== '') {
                $existingTeam = $teamModel->getTeamByName($team_name);
                if ($existingTeam) {
                    $team_id = $existingTeam['team_id'];
                } else {
                    $team_id = $teamModel->addTeam($team_name);
                }
            }

            // บันทึก User
            $userData = [
                'user_name' => $user_name,
                'user_password' => password_hash($user_password, PASSWORD_DEFAULT),
                'user_firstname' => $user_firstname,
                'user_lastname' => $user_lastname,
                'position' => $user_position,
                'team_id' => $team_id
            ];
            $newUserId = $userModel->insertUser($userData);

            if ($newUserId) {
                // เชื่อมพนักงานกับบริษัทและปีทำงาน
                $userModel->linkUserToCompany($newUserId, $company_id);
                $userModel->linkUserToFiscalYear($newUserId, $fiscal_id);

                echo json_encode(['result' => 1, 'msg' => 'เพิ่มพนักงานเรียบร้อยแล้ว']);
            } else {
                echo json_encode(['result' => 0, 'msg' => 'ไม่สามารถบันทึกข้อมูลพนักงานได้']);
            }
        } catch (PDOException $e) {
            echo json_encode(['result' => 0, 'msg' => 'เกิดข้อผิดพลาดฐานข้อมูล: ' . $e->getMessage()]);
        }
    }

    public function editEmployee()
    {
        $this->checkAuth();

        $user_id = trim($_POST['user_id'] ?? '');
        $user_firstname = trim($_POST['user_firstname'] ?? '');
        $user_lastname = trim($_POST['user_lastname'] ?? '');
        $user_position = trim($_POST['user_position'] ?? '');
        $team_name = trim($_POST['team_name'] ?? '');

        if ($user_id === '' || $user_firstname === '' || $user_lastname === '') {
            echo json_encode(['result' => 0, 'msg' => 'กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน']);
            return;
        }

        require_once '../app/models/UserModel.php';
        require_once '../app/models/TeamModel.php';
        $userModel = new UserModel();
        $teamModel = new TeamModel();

        try {
            $team_id = null;
            if ($team_name !== '') {
                $existingTeam = $teamModel->getTeamByName($team_name);
                if ($existingTeam) {
                    $team_id = $existingTeam['team_id'];
                } else {
                    $team_id = $teamModel->addTeam($team_name);
                }
            }

            $userData = [
                'user_id' => $user_id,
                'user_firstname' => $user_firstname,
                'user_lastname' => $user_lastname,
                'position' => $user_position,
                'team_id' => $team_id
            ];

            $success = $userModel->updateUser($userData);

            if ($success) {
                echo json_encode(['result' => 1, 'msg' => 'อัปเดตข้อมูลพนักงานเรียบร้อยแล้ว']);
            } else {
                echo json_encode(['result' => 0, 'msg' => 'ไม่สามารถอัปเดตข้อมูลได้']);
            }
        } catch (PDOException $e) {
            echo json_encode(['result' => 0, 'msg' => 'เกิดข้อผิดพลาดฐานข้อมูล: ' . $e->getMessage()]);
        }
    }

    public function deleteEmployee()
    {
        $this->checkAuth();

        $user_id = trim($_POST['user_id'] ?? '');

        if ($user_id === '') {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบรหัสพนักงาน']);
            return;
        }

        require_once '../app/models/UserModel.php';
        $userModel = new UserModel();

        try {
            $success = $userModel->deleteUserSoft($user_id);

            if ($success) {
                echo json_encode(['result' => 1, 'msg' => 'ลบข้อมูลพนักงานเรียบร้อยแล้ว']);
            } else {
                echo json_encode(['result' => 0, 'msg' => 'ไม่สามารถลบข้อมูลพนักงานได้']);
            }
        } catch (PDOException $e) {
            echo json_encode(['result' => 0, 'msg' => 'เกิดข้อผิดพลาดฐานข้อมูล: ' . $e->getMessage()]);
        }
    }

    public function getEmployeeAssignedCustomers()
    {
        $this->checkAuth();

        $userId = trim($_GET['user_id'] ?? $_POST['user_id'] ?? '');

        if ($userId === '') {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบรหัสพนักงาน', 'data' => []]);
            return;
        }

        require_once '../app/models/UserModel.php';
        $userModel = new UserModel();

        $fiscal_id = $_SESSION['fiscal_year_id'] ?? null;
        $customers = $userModel->getAssignedCustomersByUser($userId, $fiscal_id);

        echo json_encode([
            'result' => 1,
            'data' => $customers
        ]);
    }

    /////////////////////////////////////// customer /////////////////////////////////////////////// 
    public function customer()
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
        $active_fiscal_year = '';
        foreach ($companies as $company) {
            if (isset($company['fiscal_years'])) {
                foreach ($company['fiscal_years'] as $fy) {
                    $fy_id = $fy['fiscal_id'] ?? $fy['id'] ?? '';
                    if ($fy_id == $fiscal_id) {
                        $active_company_id = $company['company_id'] ?? $company['id'] ?? '';
                        $active_fiscal_year = $fy['fiscal_years'] ?? $fy['working_year'] ?? $fy['year'] ?? '';
                        break 2;
                    }
                }
            }
        }

        require_once '../app/models/CustomerModal.php';
        $customModal = new CustomModal();

        $tasks       = $customModal->getTasks($fiscal_id);
        $caretakers  = $customModal->getCaretakers($fiscal_id);
        $customers   = $customModal->getCustomersByFiscalId($fiscal_id);
        $stats       = $customModal->getCustomersgid($fiscal_id);

        $data = [
            'title' => 'ระบบ Backoffice',
            'user' => $this->userPayload,
            'user_id' => $this->userPayload['user_id'] ?? '',
            'user_firstname' => $this->userPayload['user_firstname'] ?? '',
            'lastname' => $this->userPayload['user_lastname'] ?? '',
            'is_super_admin' => $this->userPayload['is_super_admin'] ?? '0',
            'fiscal_id' => $fiscal_id,
            'companies' => $companies,
            'active_company_id' => $active_company_id,
            'tasks' => $tasks,
            'caretakers' => $caretakers,
            'customers' => $customers,
            'stats' => $stats,
            'active_fiscal_year' => $active_fiscal_year
        ];

        // 4. ดึงหน้า View มาแสดงผล
        require_once '../app/views/backoffice/customer.php';
    }



    public function customerFilter()
        {
            $this->checkAuth();

            $fiscal_id = $_SESSION['fiscal_year_id'] ?? null;
            if (! $fiscal_id) {
                header('Content-Type: application/json');
                echo json_encode(['result' => 0, 'msg' => 'ไม่พบปีบัญชีที่ใช้งานอยู่']);
                exit();
            }

            require_once '../app/models/CustomerModal.php';
            $customModal = new CustomModal();

            $filters = [
                'status'  => $_POST['status'] ?? '',
                'user_id' => $_POST['user_id'] ?? '',
                'keyword' => trim($_POST['keyword'] ?? ''),
        ];

        $customers = $customModal->getCustomersByFiscalId($fiscal_id, $filters);

        ob_start();
            $data = ['customers' => $customers];
            require '../app/views/backoffice/table/customer_table.php';
            $html = ob_get_clean();

            header('Content-Type: application/json');
            echo json_encode(['result' => 1, 'html' => $html]);
        exit();
    }



     public function addCustomer()

    {
        $this->checkAuth();

        $fiscal_id = trim($_POST['fiscal_id'] ?? '');
        $company_id = trim($_POST['company_id'] ?? '');
        $customer_name = trim($_POST['customer_name'] ?? '');

        if ($customer_name === '') {
            echo json_encode(['result' => 0, 'msg' => 'กรุณากรอกชื่อลูกค้า']);
            return;
        }

        if ($fiscal_id === '' || $company_id === '') {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบข้อมูลปีทำงานหรือบริษัท']);
            return;
        }

        require_once '../app/models/CustomerModal.php';
        $customModal = new CustomModal();

        try {
            $existingUser = $customModal->getCustomerByName($customer_name, $fiscal_id);
            if ($existingUser) {
                echo json_encode(['result' => 0, 'msg' => 'มีลูกค้ารายนี้อยู่ในระบบของปีทำงานนี้แล้ว']);
                return;
            }

            // 1. Insert into tbl_customers
            $customerId = $customModal->insertCustomer($_POST);

            if ($customerId) {
                // 2. Link to Fiscal Year (tbl_fiscal_year_customers)
                $customModal->linkCustomerToFiscalYear($customerId, $fiscal_id, $_POST);

                // 3 & 4. Generate Work Periods & Tasks
                $customModal->generateWorkPeriodsAndTasks($customerId, $fiscal_id, $_POST);

                echo json_encode(['result' => 1, 'msg' => 'เพิ่มลูกค้าสำเร็จ', 'customer_id' => $customerId]);
            } else {
                echo json_encode(['result' => 0, 'msg' => 'บันทึกลูกค้าไม่สำเร็จ']);
            }

        } catch (Throwable $e) {
            echo json_encode(['result' => 0, 'msg' => 'เกิดข้อผิดพลาดของฐานข้อมูล: ' . $e->getMessage()]);
        }
    }

    public function getCustomer()
    {
        $this->checkAuth();
        $customer_id = $_GET['id'] ?? null;
        $fiscal_id = $_SESSION['fiscal_year_id'] ?? null;

        if (!$customer_id || !$fiscal_id) {
            echo json_encode(['result' => 0, 'msg' => 'ข้อมูลไม่ครบถ้วน']);
            return;
        }

        require_once '../app/models/CustomerModal.php';
        $customModal = new CustomModal();

        try {
            $customer = $customModal->getCustomerDetails($customer_id, $fiscal_id);
            if ($customer) {
                echo json_encode(['result' => 1, 'data' => $customer]);
            } else {
                echo json_encode(['result' => 0, 'msg' => 'ไม่พบข้อมูลลูกค้า']);
            }
        } catch (Throwable $e) {
            echo json_encode(['result' => 0, 'msg' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
        }
    }

    public function editCustomer()
    {
        $this->checkAuth();

        $customer_id = trim($_POST['customer_id'] ?? '');
        $fiscal_id = trim($_POST['fiscal_id'] ?? $_SESSION['fiscal_id'] ?? $this->userPayload['fiscal_id'] ?? '');
        if (empty($_POST['fiscal_id'])) {
            $_POST['fiscal_id'] = $fiscal_id;
        }
        $customer_name = trim($_POST['customer_name'] ?? '');

        if ($customer_id === '' || $customer_name === '') {
            echo json_encode(['result' => 0, 'msg' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
            return;
        }

        require_once '../app/models/CustomerModal.php';
        $customModal = new CustomModal();

        try {
            $success = $customModal->updateCustomer($_POST);
            if ($success) {
                echo json_encode(['result' => 1, 'msg' => 'แก้ไขข้อมูลลูกค้าสำเร็จ']);
            } else {
                echo json_encode(['result' => 0, 'msg' => 'แก้ไขข้อมูลลูกค้าไม่สำเร็จ']);
            }
        } catch (Throwable $e) {
            echo json_encode(['result' => 0, 'msg' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
        }

    }
    public function deleteCustomer()
    {
        $this->checkAuth();

        $customer_id = trim($_POST['customer_id'] ?? '');
        $fiscal_id = trim($_POST['fiscal_id'] ?? '');
        if (empty($fiscal_id) && isset($_SESSION['fiscal_year_id'])) {
            $fiscal_id = $_SESSION['fiscal_year_id'];
        }

        require_once '../app/models/CustomerModal.php';
        $customModal = new CustomModal();

        try {
            $success = $customModal->deleteCustomer($customer_id, $fiscal_id);
            if ($success) {
                echo json_encode(['result' => 1, 'msg' => 'ลบข้อมูลลูกค้าสำเร็จ']);
            } else {
                echo json_encode(['result' => 0, 'msg' => 'ลบข้อมูลลูกค้าไม่สำเร็จ']);
            }
        } catch (Throwable $e) {
            echo json_encode(['result' => 0, 'msg' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
        }

    }



    /////////////////////////////////////// registration_board /////////////////////////////////////////////// 

    public function registration_board()
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

        // หา company_id และปีของ fiscal_id ที่กำลังใช้งานอยู่
        $active_company_id = '';
        $active_fiscal_year = '';
        foreach ($companies as $company) {
            if (isset($company['fiscal_years'])) {
                foreach ($company['fiscal_years'] as $fy) {
                    $fy_id = $fy['fiscal_id'] ?? $fy['id'] ?? '';
                    if ($fy_id == $fiscal_id) {
                        $active_company_id = $company['company_id'] ?? $company['id'] ?? '';
                        $active_fiscal_year = $fy['fiscal_years'] ?? $fy['working_year'] ?? $fy['year'] ?? '';
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
            'active_company_id' => $active_company_id,
            'active_fiscal_year' => $active_fiscal_year
        ];

        // ดึงรายชื่อพนักงานของปี/บริษัทนี้ ใช้เป็นตัวเลือก "ผู้รับผิดชอบ" ตอนเพิ่มงานทะเบียน
        require_once '../app/models/UserModel.php';
        $userModel = new UserModel();
        $data['employees'] = $userModel->getEmployeesByFiscalAndCompany($fiscal_id, $active_company_id);


        // ดึงงานทะเบียนของปีนี้ทั้งหมด แล้วแยกใส่แต่ละคอลัมน์ตามสถานะ
        // status ตาม comment ในตาราง tbl_registration:
        // 0 = รับงานลงทะเบียน, 1 = กำลังทำ, 2 = รอตรวจสอบ, 3 = ตรวจสอบแล้ว,
        // 4 = กำลังไปยื่น, 5 = งานเสร็จเรียบร้อยแล้ว, 6 = เก็บเงินเรียบร้อยแล้ว
        require_once '../app/models/RegistrationModel.php';
        $registrationModel = new RegistrationModel();
        $registrationTasks = $registrationModel->getTasksByFiscalId($fiscal_id);

        $data['stat_open'] = $registrationModel->countOpen($fiscal_id);
        $data['stat_not_overdue'] = $registrationModel->countNotOverdue($fiscal_id);
        $data['stat_overdue'] = $registrationModel->countOverdue($fiscal_id);
        $data['stat_closed_this_month'] = $registrationModel->countClosedThisMonth($fiscal_id);
        $data['stat_closed_last_month'] = $registrationModel->countClosedLastMonth($fiscal_id);
        // countClosedThisMonth/countClosedLastMonth คืน ['total' => ..., 'total_amount' => ...]


        $data['tasks_by_status'] = array_fill_keys(['0', '1', '2', '3', '4', '5', '6'], []);
        foreach ($registrationTasks as $task) {
            if (isset($data['tasks_by_status'][$task['status']])) {
                $data['tasks_by_status'][$task['status']][] = $task;
            }
        }

        // 4. ดึงหน้า View มาแสดงผล
        require_once '../app/views/backoffice/registration_board.php';
    }


    /////////////////////////////////////// postIt /////////////////////////////////////////////// 
    public function postIt()
    {
        $this->checkAuth();

        $fiscal_id = $_SESSION['fiscal_year_id'] ?? null;
        if (!$fiscal_id) {
            header('Location: ' . BASE_URL . '/main');
            exit();
        }

        require_once '../app/models/CompanyModel.php';
        $companyModel = new CompanyModel();
        $userId = $this->userPayload['user_id'] ?? null;
        $companies = $companyModel->getAllCompanies($userId);

        $active_company_id = '';
        foreach ($companies as $company) {
            if (!isset($company['fiscal_years'])) {
                continue;
            }
            foreach ($company['fiscal_years'] as $fy) {
                $fy_id = $fy['fiscal_id'] ?? $fy['id'] ?? '';
                if ((string) $fy_id === (string) $fiscal_id) {
                    $active_company_id = $company['company_id'] ?? $company['id'] ?? '';
                    break 2;
                }
            }
        }

        require_once '../app/models/PostItModel.php';
        $model = new PostItModel();
        $fiscalId = (int) $fiscal_id;

        $filters = [
            'q' => trim($_GET['q'] ?? ''),
            'user_id' => $_GET['user_id'] ?? '',
            'status' => $_GET['status'] ?? '',
            'color_code' => $_GET['color'] ?? '',
            'due' => $_GET['due'] ?? '',
            'sort' => $_GET['sort'] ?? 'created_desc',
        ];

        $perPage = (int) ($_GET['per_page'] ?? 25);
        if (!in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 25;
        }
        $page = max(1, (int) ($_GET['page'] ?? 1));

        try {
            $stats = $model->getStats($fiscalId);
            $items = $model->getList($fiscalId, $filters);
            $assignees = $model->getAssignees($fiscalId);
        } catch (Throwable $e) {
            $stats = ['total' => 0, 'pending' => 0, 'done' => 0, 'overdue' => 0];
            $items = [];
            $assignees = [];
        }

        $totalItems = count($items);
        $totalPages = max(1, (int) ceil($totalItems / $perPage));
        if ($page > $totalPages) {
            $page = $totalPages;
        }
        $offset = ($page - 1) * $perPage;
        $pageItems = array_slice($items, $offset, $perPage);
        $from = $totalItems === 0 ? 0 : $offset + 1;
        $to = min($offset + $perPage, $totalItems);

        $data = [
            'title' => 'Post-it แจ้งเตือน',
            'user' => $this->userPayload,
            'user_id' => $this->userPayload['user_id'] ?? '',
            'firstname' => $this->userPayload['user_firstname'] ?? '',
            'lastname' => $this->userPayload['user_lastname'] ?? '',
            'is_super_admin' => $this->userPayload['is_super_admin'] ?? '0',
            'fiscal_id' => $fiscal_id,
            'companies' => $companies,
            'active_company_id' => $active_company_id,
            'stats' => $stats,
            'items' => $pageItems,
            'assignees' => $assignees,
            'filters' => $filters,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $totalItems,
                'total_pages' => $totalPages,
                'from' => $from,
                'to' => $to,
            ],
            'is_draft' => false,
        ];

        require_once '../app/views/backoffice/post_it.php';
    }

    public function storePostIt()
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->checkAuth();

        $fiscal_id = $_SESSION['fiscal_year_id'] ?? null;
        if (!$fiscal_id) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบปีทำงาน กรุณาเลือกปีก่อน']);
            return;
        }

        $title = trim($_POST['title'] ?? '');
        if ($title === '') {
            echo json_encode(['result' => 0, 'msg' => 'กรุณาระบุหัวข้อ Post-it']);
            return;
        }

        $userIdRaw = trim((string) ($_POST['user_id'] ?? ''));
        $userId = ($userIdRaw === '' || $userIdRaw === '0') ? null : (int) $userIdRaw;

        $dueDateRaw = trim((string) ($_POST['due_date'] ?? ''));
        $dueDate = $dueDateRaw === '' ? null : $dueDateRaw;
        if ($dueDate !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate)) {
            echo json_encode(['result' => 0, 'msg' => 'รูปแบบวันที่ไม่ถูกต้อง']);
            return;
        }

        $status = (string) ($_POST['status'] ?? '0');
        if (!in_array($status, ['0', '1'], true)) {
            $status = '0';
        }

        $allowedColors = ['yellow', 'pink', 'blue', 'green', 'purple', 'orange'];
        $colorCode = (string) ($_POST['color_code'] ?? 'yellow');
        if (!in_array($colorCode, $allowedColors, true)) {
            $colorCode = 'yellow';
        }

        $content = trim((string) ($_POST['content'] ?? ''));
        $createdUserId = (int) ($this->userPayload['user_id'] ?? 0);
        if ($createdUserId <= 0) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบข้อมูลผู้ใช้']);
            return;
        }

        try {
            require_once '../app/models/PostItModel.php';
            $model = new PostItModel();
            $postId = $model->create([
                'fiscal_year_id' => (int) $fiscal_id,
                'title' => $title,
                'user_id' => $userId,
                'due_date' => $dueDate,
                'status' => $status,
                'content' => $content,
                'color_code' => $colorCode,
                'created_user_id' => $createdUserId,
            ]);

            if ($postId > 0) {
                if ($userId && $userId != $createdUserId) {
                    require_once '../app/models/NotificationModel.php';
                    // We must ensure the class is called correctly if namespace is used
                    $notifModel = new \App\Models\NotificationModel();
                    $notifMessage = "มีงาน Post-it ใหม่มอบหมายถึงคุณ: " . $title;
                    $notifModel->addNotification($userId, 'post_it', $postId, $notifMessage);
                    
                    // Trigger Web Push
                    $this->sendWebPush($userId, "มอบหมายงาน Post-it ใหม่", $title, ($_ENV['APP_URL'] ?? '') . "/post_it");
                }
                echo json_encode(['result' => 1, 'msg' => 'บันทึก Post-it เรียบร้อยแล้ว', 'post_id' => $postId]);
            } else {
                echo json_encode(['result' => 0, 'msg' => 'ไม่สามารถบันทึกข้อมูลได้']);
            }
        } catch (Throwable $e) {
            echo json_encode(['result' => 0, 'msg' => 'เกิดข้อผิดพลาดของฐานข้อมูล: ' . $e->getMessage()]);
        }
    }


    public function togglePostItStatus()
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->checkAuth();

        $post_id = trim($_POST['post_id'] ?? '');
        if (!$post_id) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบ post_id']);
            return;
        }

        require_once '../app/models/PostItModel.php';
        $model = new PostItModel();

        try {
            $item = $model->findById((int) $post_id);
            if (!$item) {
                echo json_encode(['result' => 0, 'msg' => 'ไม่พบข้อมูล Post-it']);
                return;
            }

            // สลับสถานะ 0 ↔ 1
            $newStatus = ($item['status'] === '1') ? '0' : '1';
            $model->updateStatus((int) $post_id, $newStatus);

            $label = $newStatus === '1' ? 'เสร็จแล้ว' : 'รอดำเนินการ';
            echo json_encode(['result' => 1, 'msg' => 'เปลี่ยนสถานะเป็น: ' . $label, 'new_status' => $newStatus]);
        } catch (Throwable $e) {
            echo json_encode(['result' => 0, 'msg' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
        }
    }


    public function updatePostIt()
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->checkAuth();

        $postId = trim($_POST['post_id'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $assigneeId = trim($_POST['user_id'] ?? ''); // แก้เป็น user_id ตามฟอร์ม HTML
        $dueDate = trim($_POST['due_date'] ?? '');
        $status = trim($_POST['status'] ?? '0');
        $content = trim($_POST['content'] ?? '');
        $colorCode = trim($_POST['color_code'] ?? 'yellow');

        if (!$postId) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบ post_id']);
            return;
        }

        require_once '../app/models/PostItModel.php';
        $model = new PostItModel();

        try {
            $postIdInt = (int) $postId;
            $updated = $model->update($postIdInt, [
                'title' => $title,
                'user_id' => $assigneeId ? (int) $assigneeId : null,
                'due_date' => $dueDate ?: null,
                'status' => $status,
                'content' => $content,
                'color_code' => $colorCode,
            ]);

            if ($updated) {
                echo json_encode(['result' => 1, 'msg' => 'อัปเดต Post-it เรียบร้อยแล้ว']);
            } else {
                echo json_encode(['result' => 0, 'msg' => 'ไม่สามารถอัปเดตข้อมูลได้']);
            }
        } catch (Throwable $e) {
            echo json_encode(['result' => 0, 'msg' => 'เกิดข้อผิดพลาดของฐานข้อมูล: ' . $e->getMessage()]);
        }
    }

    public function deletePostIt()
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->checkAuth();

        $postId = trim($_POST['post_id'] ?? '');
        if (!$postId) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบ post_id']);
            return;
        }

        require_once '../app/models/PostItModel.php';
        $model = new PostItModel();

        try {
            $deleted = $model->delete((int) $postId);

            if ($deleted) {
                echo json_encode(['result' => 1, 'msg' => 'ลบ Post-it เรียบร้อยแล้ว']);
            } else {
                echo json_encode(['result' => 0, 'msg' => 'ไม่สามารถลบข้อมูลได้']);
            }
        } catch (Throwable $e) {
            echo json_encode(['result' => 0, 'msg' => 'เกิดข้อผิดพลาดของฐานข้อมูล: ' . $e->getMessage()]);
        }
    }

    public function getMonthlyTaskComments()
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->checkAuth();

        $customer_tasks_id = $_GET['customer_tasks_id'] ?? '';
        if (!$customer_tasks_id) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบข้อมูลงาน']);
            return;
        }

        require_once '../app/models/monthly_task_Modal.php';
        $model = new MonthlyTaskModal();

        try {
            // Mark comments as read before fetching them
            $user_id = $this->userPayload['user_id'] ?? null;
            if ($user_id) {
                $model->markCommentsAsRead((int) $customer_tasks_id, (int) $user_id);
            }

            $comments = $model->getCommentsByTaskId((int) $customer_tasks_id);
            echo json_encode(['result' => 1, 'comments' => $comments]);
        } catch (Throwable $e) {
            echo json_encode(['result' => 0, 'msg' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
        }
    }

    public function storeMonthlyTaskComment()
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->checkAuth();

        $input = json_decode(file_get_contents('php://input'), true);
        $customer_tasks_id = $input['customer_tasks_id'] ?? '';
        $comment_text = trim($input['comment_text'] ?? '');
        $user_id = $this->userPayload['user_id'] ?? null;

        if (!$customer_tasks_id || $comment_text === '' || !$user_id) {
            echo json_encode(['result' => 0, 'msg' => 'ข้อมูลไม่ครบถ้วน']);
            return;
        }

        require_once '../app/models/monthly_task_Modal.php';
        $model = new MonthlyTaskModal();

        try {
            $success = $model->addComment((int) $customer_tasks_id, (int) $user_id, $comment_text);
            if ($success) {
                echo json_encode(['result' => 1, 'msg' => 'บันทึกความคิดเห็นสำเร็จ']);
            } else {
                echo json_encode(['result' => 0, 'msg' => 'บันทึกไม่สำเร็จ']);
            }
        } catch (Throwable $e) {
            echo json_encode(['result' => 0, 'msg' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
        }
    }




    /////////////////////////////////////// closing ///////////////////////////////////////////////
    public function closing()
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
        // หา company_id ของ fiscal_id ที่กำลังใช้งานอยู่ และจำนวนลูกค้าของปีบัญชีนั้น

        $active_company_id = '';
        foreach ($companies as $company) {
            if (isset($company['fiscal_years'])) {
                foreach ($company['fiscal_years'] as $fy) {
                    $fy_id = $fy['fiscal_id'] ?? $fy['id'] ?? '';
                    if ($fy_id == $fiscal_id) {
                        $active_company_id = $company['company_id'] ?? $company['id'] ?? '';


                        $customer_count = (int) ($fy['customer_count'] ?? 0);


                        break 2;
                    }
                }
            }
        }
        // 3. เตรียมข้อมูลเบื้องต้นสำหรับส่งไปหน้า View (ถ้ามี)
        require_once '../app/models/closing_Model.php';
        $closingModel = new ClosingModel();
        $closingData = $closingModel->getClosingByFiscalId($fiscal_id);

        require_once '../app/models/UserModel.php';
        $userModel = new UserModel();
        $employees = $userModel->getEmployeesByFiscalAndCompany($fiscal_id, $active_company_id);
        $assignedCaretakers = $closingModel->getCaretakersByFiscalId($fiscal_id);

        $caretakerMap = [];
        if (is_array($employees)) {
            foreach ($employees as $emp) {
                if (!empty($emp['user_id'])) {
                    $caretakerMap[$emp['user_id']] = [
                        'user_id' => $emp['user_id'],
                        'user_firstname' => $emp['user_firstname'] ?? '',
                        'user_lastname' => $emp['user_lastname'] ?? ''
                    ];
                }
            }
        }
        if (is_array($assignedCaretakers)) {
            foreach ($assignedCaretakers as $ac) {
                if (!empty($ac['user_id']) && !isset($caretakerMap[$ac['user_id']])) {
                    $caretakerMap[$ac['user_id']] = [
                        'user_id' => $ac['user_id'],
                        'user_firstname' => $ac['user_firstname'] ?? '',
                        'user_lastname' => $ac['user_lastname'] ?? ''
                    ];
                }
            }
        }

        $data = [
            'title' => 'ระบบ Backoffice',
            'user' => $this->userPayload,
            'user_id' => $this->userPayload['user_id'] ?? '',
            'firstname' => $this->userPayload['user_firstname'] ?? '',
            'lastname' => $this->userPayload['user_lastname'] ?? '',
            'is_super_admin' => $this->userPayload['is_super_admin'] ?? '0',
            'fiscal_id' => $fiscal_id,
            'companies' => $companies,

            'active_company_id' => $active_company_id,
            'closing_data' => $closingData,
            'caretakers' => array_values($caretakerMap)
        ];

        // 4. ดึงหน้า View มาแสดงผล
        require_once '../app/views/backoffice/closing.php';
    }

    public function updateClosing()
    {
        $this->checkAuth();

        require_once '../app/models/closing_Model.php';
        $closingModel = new ClosingModel();

        try {
            $result = $closingModel->updateClosingData($_POST);

            if ($result) {
                echo json_encode(['result' => 1, 'msg' => 'อัปเดตข้อมูลสำเร็จ']);
            } else {
                echo json_encode(['result' => 0, 'msg' => 'อัปเดตข้อมูลไม่สำเร็จ หรือไม่มีการเปลี่ยนแปลง']);
            }
        } catch (Throwable $e) {
            echo json_encode(['result' => 0, 'msg' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
        }
    }


    /////////////////////////////////////// monthly_task /////////////////////////////////////////////// 
    public function monthly_task()
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
        $active_fiscal_year = '';
        foreach ($companies as $company) {
            if (isset($company['fiscal_years'])) {
                foreach ($company['fiscal_years'] as $fy) {
                    $fy_id = $fy['fiscal_id'] ?? $fy['id'] ?? '';
                    if ($fy_id == $fiscal_id) {
                        $active_company_id = $company['company_id'] ?? $company['id'] ?? '';
                        $active_fiscal_year = $fy['fiscal_years'] ?? $fy['working_year'] ?? $fy['year'] ?? '';
                        break 2;
                    }
                }
            }
        }

        // 3. เตรียมข้อมูลเบื้องต้นสำหรับส่งไปหน้า View (ถ้ามี)
        $month = $_GET['month'] ?? '09'; // Default to month 09 or current month
        require_once '../app/models/monthly_task_Modal.php';
        $monthlyTaskModel = new MonthlyTaskModal();
        $monthly_tasks = $monthlyTaskModel->getMonthlyTasks($fiscal_id, $month, $userId);

        require_once '../app/models/UserModel.php';
        $userModel = new UserModel();
        $employees = [];
        if ($active_company_id) {
            $employees = $userModel->getEmployeesByFiscalAndCompany($fiscal_id, $active_company_id);
        }

        $data = [
            'title' => 'ระบบ Backoffice',
            'user' => $this->userPayload,
            'user_id' => $this->userPayload['user_id'] ?? '',
            'firstname' => $this->userPayload['user_firstname'] ?? '',
            'lastname' => $this->userPayload['user_lastname'] ?? '',
            'is_super_admin' => $this->userPayload['is_super_admin'] ?? '0',
            'fiscal_id' => $fiscal_id,
            'companies' => $companies,
            'active_company_id' => $active_company_id,
            'active_fiscal_year' => $active_fiscal_year,
            'monthly_tasks' => $monthly_tasks,
            'selected_month' => $month,
            'employees' => $employees
        ];

        // 4. ดึงหน้า View มาแสดงผล
        require_once '../app/views/backoffice/monthly_task.php';
    }

    public function getMonthlyTaskItems()
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->checkAuth();

        $period_id = $_GET['period_id'] ?? '';
        if (!$period_id) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบ period_id']);
            return;
        }

        require_once '../app/models/monthly_task_Modal.php';
        $model = new MonthlyTaskModal();
        $user_id = $this->userPayload['user_id'] ?? null;
        $tasks = $model->getTasksByPeriodId($period_id, $user_id);

        echo json_encode(['result' => 1, 'tasks' => $tasks]);
    }
    public function updateMonthlyTask()
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->checkAuth();

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || empty($input['period_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน']);
            return;
        }

        require_once '../app/models/monthly_task_Modal.php';
        $model = new MonthlyTaskModal();

        $periodId = $input['period_id'];

        $periodData = [
            'doc_date' => $input['doc_date'] ?? null,
            'completed_date' => $input['completed_date'] ?? null,
            'tax_date' => $input['tax_date'] ?? null,
            'review1_user_id' => $input['review1_user_id'] ?? '0',
            'review2_user_id' => $input['review2_user_id'] ?? '0',
            'review3_user_id' => $input['review3_user_id'] ?? '0',
            'payment_status' => $input['payment_status'] ?? '0',
            'tax_status' => $input['tax_status'] ?? '0'
        ];

        $tasksData = $input['tasks'] ?? [];

        try {
            $model->updatePeriodData($periodId, $periodData);
            if (!empty($tasksData)) {
                $model->updateTaskData($periodId, $tasksData);
            }
            echo json_encode(['status' => 'success', 'message' => 'บันทึกสำเร็จ']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /////////////////////////////////////// yearly_dash /////////////////////////////////////////////// 
    public function yearly_dash()
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
        $active_fiscal_year = '';
        foreach ($companies as $company) {
            if (isset($company['fiscal_years'])) {
                foreach ($company['fiscal_years'] as $fy) {
                    $fy_id = $fy['fiscal_id'] ?? $fy['id'] ?? '';
                    if ($fy_id == $fiscal_id) {
                        $active_company_id = $company['company_id'] ?? $company['id'] ?? '';
                        $active_fiscal_year = $fy['fiscal_years'] ?? $fy['working_year'] ?? $fy['year'] ?? '';
                        break 2;
                    }
                }
            }
        }

        require_once '../app/models/closing_Model.php';
        $closingModel = new ClosingModel();
        $closingList = $closingModel->getClosingByFiscalId($fiscal_id);

        $totalCustomers = count($closingList);
        $closingCompleted = 0;
        $docReceived = 0;
        $boj5Count = 0;
        $dbdCount = 0;
        $pnd50Count = 0;

        $caretakersMap = [];

        foreach ($closingList as $item) {
            $isClosingDone = ((string) ($item['closing_status'] ?? '')) === '1';
            $isDocDone = ((string) ($item['doc_status'] ?? '')) === '1' || ((string) ($item['audit_status'] ?? '')) === '1';
            $isBoj5Done = ((string) ($item['boj5_status'] ?? '')) === '1';
            $isDbdDone = ((string) ($item['dbd_efiling_status'] ?? '')) === '1';
            $isPnd50Done = ((string) ($item['pnd50_status'] ?? '')) === '1';

            if ($isClosingDone)
                $closingCompleted++;
            if ($isDocDone)
                $docReceived++;
            if ($isBoj5Done)
                $boj5Count++;
            if ($isDbdDone)
                $dbdCount++;
            if ($isPnd50Done)
                $pnd50Count++;

            $cName = trim(($item['user_firstname'] ?? '') . ' ' . ($item['user_lastname'] ?? ''));
            if (empty($cName))
                $cName = 'ไม่ระบุผู้ดูแล';

            if (!isset($caretakersMap[$cName])) {
                $caretakersMap[$cName] = ['name' => $cName, 'total' => 0, 'completed' => 0];
            }
            $caretakersMap[$cName]['total']++;
            if ($isClosingDone) {
                $caretakersMap[$cName]['completed']++;
            }
        }

        $caretakersList = [];
        foreach ($caretakersMap as $c) {
            $pct = $c['total'] > 0 ? round(($c['completed'] / $c['total']) * 100, 1) : 0;
            $caretakersList[] = [
                'name' => $c['name'],
                'total' => $c['total'],
                'completed' => $c['completed'],
                'pending' => $c['total'] - $c['completed'],
                'percent' => $pct
            ];
        }

        $stats = [
            'total_customers' => $totalCustomers,
            'closing_completed' => $closingCompleted,
            'audit_completed' => $docReceived,
            'boj5' => $boj5Count,
            'dbd' => $dbdCount,
            'pnd50' => $pnd50Count,
            'closing_completed_pct' => $totalCustomers > 0 ? round(($closingCompleted / $totalCustomers) * 100, 1) : 0,
            'audit_completed_pct' => $totalCustomers > 0 ? round(($docReceived / $totalCustomers) * 100, 1) : 0,
            'boj5_pct' => $totalCustomers > 0 ? round(($boj5Count / $totalCustomers) * 100, 1) : 0,
            'dbd_pct' => $totalCustomers > 0 ? round(($dbdCount / $totalCustomers) * 100, 1) : 0,
            'pnd50_pct' => $totalCustomers > 0 ? round(($pnd50Count / $totalCustomers) * 100, 1) : 0,
            'caretakers' => $caretakersList,
            'closing_list' => $closingList
        ];

        // 3. เตรียมข้อมูลเบื้องต้นสำหรับส่งไปหน้า View
        $data = [
            'title' => 'ระบบ Backoffice',
            'user' => $this->userPayload,
            'user_id' => $this->userPayload['user_id'] ?? '',
            'firstname' => $this->userPayload['user_firstname'] ?? '',
            'lastname' => $this->userPayload['user_lastname'] ?? '',
            'is_super_admin' => $this->userPayload['is_super_admin'] ?? '0',
            'fiscal_id' => $fiscal_id,
            'companies' => $companies,
            'active_company_id' => $active_company_id,
            'active_fiscal_year' => $active_fiscal_year,
            'stats' => $stats
        ];

        // 4. ดึงหน้า View มาแสดงผล
        require_once '../app/views/backoffice/yearly_dash.php';
    }

    /////////////////////////////////////// monthly_dash /////////////////////////////////////////////// 
    public function monthly_dash()
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
        $active_fiscal_year = '';
        foreach ($companies as $company) {
            if (isset($company['fiscal_years'])) {
                foreach ($company['fiscal_years'] as $fy) {
                    $fy_id = $fy['fiscal_id'] ?? $fy['id'] ?? '';
                    if ($fy_id == $fiscal_id) {
                        $active_company_id = $company['company_id'] ?? $company['id'] ?? '';
                        $active_fiscal_year = $fy['fiscal_years'] ?? $fy['working_year'] ?? $fy['year'] ?? '';
                        break 2;
                    }
                }
            }
        }

        // 3. เตรียมข้อมูลเบื้องต้นสำหรับส่งไปหน้า View
        $month = $_GET['month'] ?? date('m');
        $monthStr = str_pad($month, 2, '0', STR_PAD_LEFT);

        require_once '../app/models/MonthlyDashModel.php';
        $monthlyDashModel = new MonthlyDashModel();
        $dashboardData = $monthlyDashModel->getDashboardStats($fiscal_id, $monthStr);

        $data = [
            'title' => 'ระบบ Backoffice',
            'user' => $this->userPayload,
            'user_id' => $this->userPayload['user_id'] ?? '',
            'firstname' => $this->userPayload['user_firstname'] ?? '',
            'lastname' => $this->userPayload['user_lastname'] ?? '',
            'is_super_admin' => $this->userPayload['is_super_admin'] ?? '0',
            'fiscal_id' => $fiscal_id,
            'companies' => $companies,
            'active_company_id' => $active_company_id,
            'active_fiscal_year' => $active_fiscal_year,
            'selected_month' => $monthStr,
            'stats' => $dashboardData
        ];

        // 4. ดึงหน้า View มาแสดงผล
        require_once '../app/views/backoffice/monthly_dash.php';
    }

    public function getMonthlyStatsAjax()
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->checkAuth();

        $fiscal_id = $_SESSION['fiscal_year_id'] ?? null;
        $month = $_GET['month'] ?? date('m');
        $monthStr = str_pad($month, 2, '0', STR_PAD_LEFT);

        if (!$fiscal_id) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบข้อมูลปีทำงาน']);
            return;
        }

        require_once '../app/models/MonthlyDashModel.php';
        $monthlyDashModel = new MonthlyDashModel();
        $dashboardData = $monthlyDashModel->getDashboardStats($fiscal_id, $monthStr);

        $month_names = [
            '01' => 'มกราคม',
            '02' => 'กุมภาพันธ์',
            '03' => 'มีนาคม',
            '04' => 'เมษายน',
            '05' => 'พฤษภาคม',
            '06' => 'มิถุนายน',
            '07' => 'กรกฎาคม',
            '08' => 'สิงหาคม',
            '09' => 'กันยายน',
            '10' => 'ตุลาคม',
            '11' => 'พฤศจิกายน',
            '12' => 'ธันวาคม',
        ];

        echo json_encode([
            'result' => 1,
            'month' => $monthStr,
            'month_name' => $month_names[$monthStr] ?? 'มกราคม',
            'stats' => $dashboardData
        ]);
    }

    public function customer_message()
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
        require_once '../app/views/backoffice/customer_message.php';
    }

    //// NOTPANGJIT ////
    //// ตั้งค่าประเภทงาน ////
    public function getRegistrationTypes()
    {
        $this->checkAuth();
        $fiscal_id = $_SESSION['fiscal_year_id'] ?? null;
        if (!$fiscal_id) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบข้อมูลปีทำงาน (Fiscal ID)']);
            return;
        }
        require_once '../app/models/RegistrationTypeModel.php';
        $model = new RegistrationTypeModel();
        echo json_encode(['result' => 1, 'data' => $model->getAll($fiscal_id)]);
    }

    public function addRegistrationType()
    {
        $this->checkAuth();
        $name = trim($_POST['name'] ?? '');
        $fiscal_id = $_SESSION['fiscal_year_id'] ?? null;
        if ($name === '') {
            echo json_encode(['result' => 0, 'msg' => 'กรุณากรอกชื่อประเภทงาน']);
            return;
        }
        if (!$fiscal_id) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบข้อมูลปีทำงาน (Fiscal ID)']);
            return;
        }
        require_once '../app/models/RegistrationTypeModel.php';
        $model = new RegistrationTypeModel();
        $userId = $this->userPayload['user_id'] ?? null;
        $ok = $model->insert($name, $userId, $fiscal_id);
        echo json_encode($ok ? ['result' => 1, 'msg' => 'เพิ่มประเภทงานเรียบร้อยแล้ว']
            : ['result' => 0, 'msg' => 'ไม่สามารถบันทึกข้อมูลได้']);
    }

    public function editRegistrationType()
    {
        $this->checkAuth();
        $id = $_POST['id'] ?? '';
        $name = trim($_POST['name'] ?? '');
        $fiscal_id = $_SESSION['fiscal_year_id'] ?? null;
        if ($id === '' || $name === '') {
            echo json_encode(['result' => 0, 'msg' => 'ข้อมูลไม่ครบถ้วน']);
            return;
        }
        if (!$fiscal_id) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบข้อมูลปีทำงาน (Fiscal ID)']);
            return;
        }
        require_once '../app/models/RegistrationTypeModel.php';
        $model = new RegistrationTypeModel();
        $ok = $model->update($id, $name, $fiscal_id);
        echo json_encode($ok ? ['result' => 1, 'msg' => 'แก้ไขประเภทงานเรียบร้อยแล้ว']
            : ['result' => 0, 'msg' => 'ไม่สามารถบันทึกข้อมูลได้']);
    }

    public function deleteRegistrationType()
    {
        $this->checkAuth();
        $id = $_POST['id'] ?? '';
        $fiscal_id = $_SESSION['fiscal_year_id'] ?? null;
        if ($id === '') {
            echo json_encode(['result' => 0, 'msg' => 'ข้อมูลไม่ครบถ้วน']);
            return;
        }
        if (!$fiscal_id) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบข้อมูลปีทำงาน (Fiscal ID)']);
            return;
        }
        require_once '../app/models/RegistrationTypeModel.php';
        $model = new RegistrationTypeModel();
        $userId = $this->userPayload['user_id'] ?? null;
        $ok = $model->softDelete($id, $userId, $fiscal_id);
        echo json_encode($ok ? ['result' => 1, 'msg' => 'ลบประเภทงานเรียบร้อยแล้ว']
            : ['result' => 0, 'msg' => 'ไม่สามารถลบข้อมูลได้']);
    }

    public function addRegistrationTask()
    {
        $this->checkAuth();
        $fiscal_id = $_SESSION['fiscal_year_id'] ?? null;

        if (!$fiscal_id) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบข้อมูลปีทำงาน (Fiscal ID)']);
            return;
        }

        // ฟิลด์ที่ห้ามว่าง (ตรงกับคอลัมน์ NOT NULL ใน tbl_registration)
        $data = [
            'registration_type_id' => trim($_POST['registration_type_id'] ?? ''),
            'customer_name' => trim($_POST['customer_name'] ?? ''),
            'customer_phone' => trim($_POST['customer_phone'] ?? ''),
            'contact_person' => trim($_POST['contact_person'] ?? ''),
            'registration_name' => trim($_POST['registration_name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'service_amount' => trim($_POST['service_amount'] ?? '0'),
            'urgency_level' => trim($_POST['urgency_level'] ?? ''),
            'accep_date' => trim($_POST['accep_date'] ?? ''),
            'due_date' => trim($_POST['due_date'] ?? ''),
            'assignee_user_id' => trim($_POST['assignee_user_id'] ?? ''),
            'review_user_id' => trim($_POST['review_user_id'] ?? ''),
        ];

        $required = ['registration_type_id', 'customer_name', 'customer_phone', 'contact_person', 'registration_name', 'description', 'assignee_user_id'];
        foreach ($required as $field) {
            if ($data[$field] === '') {
                echo json_encode(['result' => 0, 'msg' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
                return;
            }
        }

        require_once '../app/models/RegistrationModel.php';
        $model = new RegistrationModel();
        $ok = $model->insert($fiscal_id, $data);

        echo json_encode($ok
            ? ['result' => 1, 'msg' => 'เพิ่มงานทะเบียนเรียบร้อยแล้ว']
            : ['result' => 0, 'msg' => 'ไม่สามารถบันทึกข้อมูลได้']);
    }

    public function getRegistrationTask()
    {
        $this->checkAuth();
        $id = $_GET['id'] ?? $_POST['id'] ?? '';

        if ($id === '') {
            echo json_encode(['result' => 0, 'msg' => 'ข้อมูลไม่ครบถ้วน']);
            return;
        }

        require_once '../app/models/RegistrationModel.php';
        $model = new RegistrationModel();
        $task = $model->getById($id);

        echo json_encode($task
            ? ['result' => 1, 'data' => $task]
            : ['result' => 0, 'msg' => 'ไม่พบข้อมูลงานทะเบียน']);
    }

    public function editRegistrationTask()
    {
        $this->checkAuth();
        $id = $_POST['id'] ?? '';

        if ($id === '') {
            echo json_encode(['result' => 0, 'msg' => 'ข้อมูลไม่ครบถ้วน']);
            return;
        }

        // ฟิลด์ที่ห้ามว่าง (ตรงกับคอลัมน์ NOT NULL ใน tbl_registration) — ชุดเดียวกับตอนเพิ่ม
        $data = [
            'registration_type_id' => trim($_POST['registration_type_id'] ?? ''),
            'customer_name' => trim($_POST['customer_name'] ?? ''),
            'customer_phone' => trim($_POST['customer_phone'] ?? ''),
            'contact_person' => trim($_POST['contact_person'] ?? ''),
            'registration_name' => trim($_POST['registration_name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'service_amount' => trim($_POST['service_amount'] ?? '0'),
            'urgency_level' => trim($_POST['urgency_level'] ?? ''),
            'accep_date' => trim($_POST['accep_date'] ?? ''),
            'due_date' => trim($_POST['due_date'] ?? ''),
            'assignee_user_id' => trim($_POST['assignee_user_id'] ?? ''),
            'review_user_id' => trim($_POST['review_user_id'] ?? ''),
        ];

        $required = ['registration_type_id', 'customer_name', 'customer_phone', 'contact_person', 'registration_name', 'description', 'assignee_user_id'];
        foreach ($required as $field) {
            if ($data[$field] === '') {
                echo json_encode(['result' => 0, 'msg' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
                return;
            }
        }

        require_once '../app/models/RegistrationModel.php';
        $model = new RegistrationModel();
        $ok = $model->update($id, $data);

        echo json_encode($ok
            ? ['result' => 1, 'msg' => 'แก้ไขงานทะเบียนเรียบร้อยแล้ว']
            : ['result' => 0, 'msg' => 'ไม่สามารถบันทึกข้อมูลได้']);
    }

    public function deleteRegistrationTask()
    {
        $this->checkAuth();
        $id = $_POST['id'] ?? '';

        if ($id === '') {
            echo json_encode(['result' => 0, 'msg' => 'ข้อมูลไม่ครบถ้วน']);
            return;
        }

        require_once '../app/models/RegistrationModel.php';
        $model = new RegistrationModel();
        $userId = $this->userPayload['user_id'] ?? null;
        $ok = $model->softDelete($id, $userId);

        echo json_encode($ok
            ? ['result' => 1, 'msg' => 'ลบงานทะเบียนเรียบร้อยแล้ว']
            : ['result' => 0, 'msg' => 'ไม่สามารถลบข้อมูลได้']);
    }

    // ปิด Job — ย้ายงานไปเก็บเป็นประวัติ (ทำเครื่องหมาย closed_at ไม่ได้ลบทิ้ง)
    public function closeRegistrationTask()
    {
        $this->checkAuth();
        $id = $_POST['id'] ?? '';

        if ($id === '') {
            echo json_encode(['result' => 0, 'msg' => 'ข้อมูลไม่ครบถ้วน']);
            return;
        }

        require_once '../app/models/RegistrationModel.php';
        $model = new RegistrationModel();
        $userId = $this->userPayload['user_id'] ?? null;
        $ok = $model->closeJob($id, $userId);

        echo json_encode($ok
            ? ['result' => 1, 'msg' => 'ปิด Job และย้ายไปประวัติเรียบร้อยแล้ว']
            : ['result' => 0, 'msg' => 'ไม่สามารถปิด Job ได้ (อาจถูกปิดไปแล้ว)']);
    }

    // ประวัติงานทะเบียนที่ปิดแล้ว — คืน JSON ให้ Modal ประวัติในหน้าบอร์ด (แบ่งหน้า + ค้นหา)
    public function getRegistrationHistory()
    {
        $this->checkAuth();
        $fiscal_id = $_SESSION['fiscal_year_id'] ?? null;

        if (!$fiscal_id) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบข้อมูลปีทำงาน (Fiscal ID)', 'data' => [], 'total_count' => 0, 'total_amount' => 0]);
            return;
        }

        $keyword = trim($_GET['keyword'] ?? '');
        $perPage = (int) ($_GET['per_page'] ?? 25);
        if ($perPage < 1) {
            $perPage = 25;
        }
        $page = (int) ($_GET['page'] ?? 1);
        if ($page < 1) {
            $page = 1;
        }
        $offset = ($page - 1) * $perPage;

        require_once '../app/models/RegistrationModel.php';
        $model = new RegistrationModel();
        $summary = $model->countClosedTasks($fiscal_id, $keyword);
        $rows = $model->getClosedTasks($fiscal_id, $keyword, $perPage, $offset);

        echo json_encode([
            'result' => 1,
            'data' => $rows,
            'page' => $page,
            'per_page' => $perPage,
            'total_count' => (int) ($summary['total_count'] ?? 0),
            'total_amount' => (float) ($summary['total_amount'] ?? 0),
        ]);
    }

    public function updateRegistrationTaskStatus()
    {
        $this->checkAuth();
        $id = $_POST['id'] ?? '';
        $status = $_POST['status'] ?? '';
        $validStatuses = ['0', '1', '2', '3', '4', '5', '6'];

        if ($id === '' || !in_array($status, $validStatuses, true)) {
            echo json_encode(['result' => 0, 'msg' => 'ข้อมูลไม่ครบถ้วน']);
            return;
        }

        require_once '../app/models/RegistrationModel.php';
        $model = new RegistrationModel();
        $ok = $model->updateStatus($id, $status);

        echo json_encode($ok
            ? ['result' => 1, 'msg' => 'อัปเดตสถานะเรียบร้อยแล้ว']
            : ['result' => 0, 'msg' => 'ไม่สามารถอัปเดตสถานะได้']);
    }

    public function toggleRegistrationTypeStatus()
    {
        $this->checkAuth();
        $id = $_POST['id'] ?? '';
        $activeStatus = $_POST['active_status'] ?? '';
        $fiscal_id = $_SESSION['fiscal_year_id'] ?? null;
        if ($id === '' || ($activeStatus !== '0' && $activeStatus !== '1')) {
            echo json_encode(['result' => 0, 'msg' => 'ข้อมูลไม่ครบถ้วน']);
            return;
        }
        if (!$fiscal_id) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบข้อมูลปีทำงาน (Fiscal ID)']);
            return;
        }
        require_once '../app/models/RegistrationTypeModel.php';
        $model = new RegistrationTypeModel();
        $ok = $model->setActiveStatus($id, $activeStatus, $fiscal_id);
        echo json_encode($ok ? ['result' => 1, 'msg' => 'เปลี่ยนสถานะเรียบร้อยแล้ว']
            : ['result' => 0, 'msg' => 'ไม่สามารถเปลี่ยนสถานะได้']);
    }


    //// ตั้งค่างานทะเบียน ////
    public function getRegistrationTaskSettings()
    {
        // เช็ค login ก่อนเสมอ (แพทเทิร์นเดียวกับทุกเมธอดในไฟล์นี้)
        $this->checkAuth();

        // อ่านปีบัญชีปัจจุบันจาก session (คีย์ต้องเป็น 'fiscal_year_id' เท่านั้น ตามที่ทั้งระบบใช้)
        $fiscal_id = $_SESSION['fiscal_year_id'] ?? null;

        // ถ้าไม่มีปีบัญชีอยู่ใน session เลย ตอบ error กลับไปเป็น JSON ทันที ไม่ต้องไปแตะ DB
        if (!$fiscal_id) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบข้อมูลปีทำงาน (Fiscal ID)']);
            return;
        }

        require_once '../app/models/RegistrationTaskSettingModel.php';
        $model = new RegistrationTaskSettingModel();

        // เรียก 2 เมธอดจาก model โดยส่ง fiscal_id เข้าไปทั้งคู่
        $settings = $model->getSettings($fiscal_id);
        $urgencyLevels = $model->getUrgencyLevels($fiscal_id);

        // ห่อผลลัพธ์ทั้งสองก้อนเป็น JSON เดียว ส่งกลับไปให้ฝั่ง JS
        echo json_encode([
            'result' => 1,
            'data' => [
                'notify_day' => $settings['notify_day'],
                'urgency_levels' => $urgencyLevels,
            ],
        ]);
    }

    public function saveRegistrationTaskSettings()
    {
        $this->checkAuth();
        $fiscal_id = $_SESSION['fiscal_year_id'] ?? null;

        if (!$fiscal_id) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบข้อมูลปีทำงาน (Fiscal ID)']);
            return;
        }

        // (int) แปลงเป็นเลขจำนวนเต็ม กัน string แปลกๆ หลุดเข้าไปใน DB
        $notifyDays = (int) ($_POST['notify_day'] ?? 7);
        $userId = $this->userPayload['user_id'] ?? null;

        require_once '../app/models/RegistrationTaskSettingModel.php';
        $model = new RegistrationTaskSettingModel();

        // บันทึกทีละส่วน แล้วเอาผล true/false มา "และ" กันไว้ (&&) ถ้าจุดไหนพังจุดเดียว $ok จะกลายเป็น false ทั้งหมด
        $ok = $model->updateNotifyDays($fiscal_id, $notifyDays, $userId);
        $ok = $ok && $model->updateUrgencyLevel($fiscal_id, '1', trim($_POST['normal_label'] ?? 'ปกติ'), trim($_POST['normal_color'] ?? '#94a3b8'));
        $ok = $ok && $model->updateUrgencyLevel($fiscal_id, '2', trim($_POST['urgent_label'] ?? 'เร่งด่วน'), trim($_POST['urgent_color'] ?? '#f97316'));
        $ok = $ok && $model->updateUrgencyLevel($fiscal_id, '3', trim($_POST['very_urgent_label'] ?? 'ด่วนมาก'), trim($_POST['very_urgent_color'] ?? '#ef4444'));

        echo json_encode($ok
            ? ['result' => 1, 'msg' => 'บันทึกตั้งค่าเรียบร้อยแล้ว']
            : ['result' => 0, 'msg' => 'ไม่สามารถบันทึกข้อมูลได้']);
    }

    public function getNotifications()
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->checkAuth();
        $userId = $this->userPayload['user_id'] ?? null;
        if (!$userId) {
            echo json_encode(['result' => 0, 'msg' => 'Unauthorized']);
            return;
        }

        require_once '../app/models/NotificationModel.php';
        $notifModel = new \App\Models\NotificationModel();
        
        $notifications = $notifModel->getUnreadNotifications($userId, 20);
        $count = $notifModel->getUnreadCount($userId);

        echo json_encode([
            'result' => 1,
            'count' => $count,
            'data' => $notifications
        ]);
    }

    public function readNotification()
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->checkAuth();
        $userId = $this->userPayload['user_id'] ?? null;
        $notifId = $_POST['notif_id'] ?? null;

        if (!$userId || !$notifId) {
            echo json_encode(['result' => 0, 'msg' => 'Invalid Request']);
            return;
        }

        require_once '../app/models/NotificationModel.php';
        $notifModel = new \App\Models\NotificationModel();
        
        $success = $notifModel->markAsRead($notifId, $userId);

        if ($success) {
            echo json_encode(['result' => 1, 'msg' => 'Success']);
        } else {
            echo json_encode(['result' => 0, 'msg' => 'Failed to mark as read']);
        }
    }

    public function getVapidPublicKey()
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'publicKey' => $_ENV['VAPID_PUBLIC_KEY'] ?? ''
        ]);
    }

    public function subscribePush()
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->checkAuth();
        $userId = $this->userPayload['user_id'] ?? null;
        if (!$userId) {
            echo json_encode(['result' => 0, 'msg' => 'Unauthorized']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input['endpoint'])) {
            echo json_encode(['result' => 0, 'msg' => 'Invalid data']);
            return;
        }

        try {
            require_once '../app/config/Connection.php';
            $pdo = \App\config\Connection::getInstance()->getPdo();
            
            $endpoint = $input['endpoint'];
            $p256dh = $input['keys']['p256dh'] ?? '';
            $auth = $input['keys']['auth'] ?? '';

            // Check if exists
            $stmt = $pdo->prepare("SELECT id FROM tbl_push_subscriptions WHERE endpoint = ?");
            $stmt->execute([$endpoint]);
            
            if ($stmt->fetch()) {
                // Update
                $stmt = $pdo->prepare("UPDATE tbl_push_subscriptions SET user_id = ?, p256dh = ?, auth = ? WHERE endpoint = ?");
                $stmt->execute([$userId, $p256dh, $auth, $endpoint]);
            } else {
                // Insert
                $stmt = $pdo->prepare("INSERT INTO tbl_push_subscriptions (user_id, endpoint, p256dh, auth) VALUES (?, ?, ?, ?)");
                $stmt->execute([$userId, $endpoint, $p256dh, $auth]);
            }

            echo json_encode(['result' => 1, 'msg' => 'Subscribed']);
        } catch (\Throwable $e) {
            echo json_encode(['result' => 0, 'msg' => $e->getMessage()]);
        }
    }

    private function sendWebPush($userId, $title, $body, $url = '')
    {
        try {
            require_once '../app/config/Connection.php';
            require_once '../vendor/autoload.php';
            $pdo = \App\config\Connection::getInstance()->getPdo();
            
            $stmt = $pdo->prepare("SELECT endpoint, p256dh, auth FROM tbl_push_subscriptions WHERE user_id = ?");
            $stmt->execute([$userId]);
            $subs = $stmt->fetchAll();
            
            if (empty($subs)) return;

            $auth = [
                'VAPID' => [
                    'subject' => 'mailto:admin@' . ($_SERVER['HTTP_HOST'] ?? 'localhost'),
                    'publicKey' => $_ENV['VAPID_PUBLIC_KEY'] ?? '',
                    'privateKey' => $_ENV['VAPID_PRIVATE_KEY'] ?? ''
                ]
            ];

            $webPush = new \Minishlink\WebPush\WebPush($auth);

            $payload = json_encode([
                'title' => $title,
                'body' => $body,
                'url' => $url ?: ($_ENV['APP_URL'] ?? '')
            ]);

            foreach ($subs as $sub) {
                $subscription = \Minishlink\WebPush\Subscription::create([
                    'endpoint' => $sub['endpoint'],
                    'publicKey' => $sub['p256dh'],
                    'authToken' => $sub['auth'],
                ]);
                $webPush->queueNotification($subscription, $payload);
            }

            foreach ($webPush->flush() as $report) {
                if (!$report->isSuccess()) {
                    if ($report->getResponse() && in_array($report->getResponse()->getStatusCode(), [404, 410])) {
                        $delStmt = $pdo->prepare("DELETE FROM tbl_push_subscriptions WHERE endpoint = ?");
                        $delStmt->execute([$report->getRequest()->getUri()->__toString()]);
                    }
                }
            }
        } catch (\Throwable $e) {
            error_log("WebPush Error: " . $e->getMessage());
        }
    }
}
