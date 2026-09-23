<?php
// app/controllers/BackofficeController.php

class BackofficeController
{
    private $userPayload = null;

    private function checkAuth()
    {
        require_once '../app/models/AuthModel.php';
        $user = \App\models\AuthModel::checkWebAuth();

        if (! $user) {
            // Check if it's an AJAX request (either via header or if it's hitting specific API endpoints)
            $isAjax = false;
            if (! empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                $isAjax = true;
            } elseif (! empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
                $isAjax = true;
            } elseif (isset($_SERVER['REQUEST_URI']) && (strpos($_SERVER['REQUEST_URI'], 'notification/') !== false || strpos($_SERVER['REQUEST_URI'], '/get') !== false)) {
                $isAjax = true;
            }

            if ($isAjax) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['result' => 0, 'msg' => 'Session expired']);
                exit();
            }

            header("Location: " . BASE_URL . "/login");
            exit();
        }

        $this->userPayload = $user;
    }


    /////////////////////////////////////// index ///////////////////////////////////////////////
    public function index()
    {
        // เปิดแสดง Error ทั้งหมดบนหน้าจอ (สำหรับการ Debug)
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');
        error_reporting(E_ALL);

        // 1. ตรวจสอบสิทธิ์ผู้ใช้ก่อน
        $this->checkAuth();

        // 2. รับค่า fiscal_id จาก Session (ตั้งค่ามาจากหน้าหลักผ่าน AJAX)
        $fiscal_id = $_SESSION['fiscal_year_id'] ?? null;

        if (! $fiscal_id) {
            // ถ้าไม่มีรหัสปี ให้เด้งกลับไปหน้าหลัก
            header("Location: " . BASE_URL . "/main");
            exit();
        }

        require_once '../app/models/CompanyModel.php';
        $companyModel = new CompanyModel();
        $userId       = $this->userPayload['user_id'] ?? null;
        $companies    = $companyModel->getAllCompanies($userId);

        // หา company_id ของ fiscal_id ที่กำลังใช้งานอยู่
        $active_company_id  = '';
        $active_fiscal_year = '';
        foreach ($companies as $company) {
            if (isset($company['fiscal_years'])) {
                foreach ($company['fiscal_years'] as $fy) {
                    $fy_id = $fy['fiscal_id'] ?? $fy['id'] ?? '';
                    if ($fy_id == $fiscal_id) {
                        $active_company_id  = $company['company_id'] ?? $company['id'] ?? '';
                        $active_fiscal_year = $fy['fiscal_years'] ?? $fy['working_year'] ?? $fy['year'] ?? '';
                        break 2;
                    }
                }
            }
        }

        // Monthly Stats
        $month    = $_GET['month'] ?? date('m');
        $monthStr = str_pad($month, 2, '0', STR_PAD_LEFT);

        require_once '../app/models/MonthlyDashModel.php';
        $monthlyDashModel = new MonthlyDashModel();
        $monthlyStats     = $monthlyDashModel->getDashboardStats($fiscal_id, $monthStr);

        // Yearly Stats
        require_once '../app/models/closing_Model.php';
        $closingModel = new ClosingModel();
        $closingList  = $closingModel->getClosingByFiscalId($fiscal_id);

        $totalClosing = count($closingList);
        $closingDone  = 0;
        $docDone      = 0;
        $boj5Done     = 0;
        $dbdDone      = 0;
        $pnd50Done    = 0;

        foreach ($closingList as $item) {

            if (((string) ($item['closing_status'] ?? '')) === '1') {
                $closingDone++;
            }

            if (((string) ($item['doc_status'] ?? '')) === '1' || ((string) ($item['audit_status'] ?? '')) === '1') {
                $docDone++;
            }

            if (((string) ($item['boj5_status'] ?? '')) === '1') {
                $boj5Done++;
            }

            if (((string) ($item['dbd_efiling_status'] ?? '')) === '1') {
                $dbdDone++;
            }

            if (((string) ($item['pnd50_status'] ?? '')) === '1') {
                $pnd50Done++;
            }

        }

        $yearlyStats = [
            'total'                 => $totalClosing,
            'closing_completed'     => $closingDone,
            'doc_received'          => $docDone,
            'boj5'                  => $boj5Done,
            'dbd'                   => $dbdDone,
            'pnd50'                 => $pnd50Done,
            'closing_completed_pct' => $totalClosing > 0 ? round(($closingDone / $totalClosing) * 100, 1) : 0,
            'audit_completed_pct'   => $totalClosing > 0 ? round(($docDone / $totalClosing) * 100, 1) : 0,
            'boj5_pct'              => $totalClosing > 0 ? round(($boj5Done / $totalClosing) * 100, 1) : 0,
            'dbd_pct'               => $totalClosing > 0 ? round(($dbdDone / $totalClosing) * 100, 1) : 0,
            'pnd50_pct'             => $totalClosing > 0 ? round(($pnd50Done / $totalClosing) * 100, 1) : 0,
        ];

        // Customer & Caretaker General Stats
        require_once '../app/models/CustomerModal.php';
        $customerModal = new CustomModal();
        $customerStats = $customerModal->getCustomersgid($fiscal_id);
        $caretakers = $customerModal->getCaretakers();
        
        $isSuperAdmin = $this->userPayload['is_super_admin'] ?? '0';
        $attentionStats = $customerModal->getAttentionStats($fiscal_id, $userId, $isSuperAdmin);

        // 3. เตรียมข้อมูลเบื้องต้นสำหรับส่งไปหน้า View (ถ้ามี)
        $data = [
            'title' => 'Account - ภาพรวมสำนักงาน',
            'user' => $this->userPayload,
            'user_id' => $this->userPayload['user_id'] ?? '',
            'firstname' => $this->userPayload['user_firstname'] ?? '',
            'lastname' => $this->userPayload['user_lastname'] ?? '',
            'is_super_admin' => $isSuperAdmin,
            'fiscal_id' => $fiscal_id,
            'companies' => $companies,
            'active_company_id' => $active_company_id,
            'active_fiscal_year' => $active_fiscal_year,
            'selected_month' => $monthStr,
            'monthly_stats' => $monthlyStats,
            'yearly_stats' => $yearlyStats,
            'customer_stats' => $customerStats,
            'caretakers_count' => count($caretakers),
            'attention_stats' => $attentionStats,

        ];

        // 4. ดึงหน้า View มาแสดงผล
        require_once '../app/views/backoffice/index.php';
    }

    /////////////////////////////////////// dashboard_workspace ///////////////////////////////////////////////
    public function dashboard_workspace()
    {
        $this->checkAuth();
        $fiscal_id = $_SESSION['fiscal_year_id'] ?? null;

        if (! $fiscal_id) {
            header("Location: " . BASE_URL . "/main");
            exit();
        }

        require_once '../app/models/CompanyModel.php';
        $companyModel = new CompanyModel();
        $userId       = $this->userPayload['user_id'] ?? null;
        $companies    = $companyModel->getAllCompanies($userId);

        $active_company_id  = '';
        $active_fiscal_year = '';
        foreach ($companies as $company) {
            if (isset($company['fiscal_years'])) {
                foreach ($company['fiscal_years'] as $fy) {
                    $fy_id = $fy['fiscal_id'] ?? $fy['id'] ?? '';
                    if ($fy_id == $fiscal_id) {
                        $active_company_id  = $company['company_id'] ?? $company['id'] ?? '';
                        $active_fiscal_year = $fy['fiscal_years'] ?? $fy['working_year'] ?? $fy['year'] ?? '';
                        break 2;
                    }
                }
            }
        }

        $isSuperAdmin = $this->userPayload['is_super_admin'] ?? '0';

        require_once '../app/models/WorkspaceDashboardModel.php';
        $workspaceModel = new WorkspaceDashboardModel();

        $customerCountData  = $workspaceModel->getCustomerCountByCompany($userId, $isSuperAdmin);
        $accountingFeesData = $workspaceModel->getAccountingFeesByCompany($userId, $isSuperAdmin);
        $annualClosingData  = $workspaceModel->getAnnualClosingByCompany($userId, $isSuperAdmin);
        $registrationData   = $workspaceModel->getRegistrationManagementByCompany($userId, $isSuperAdmin);
        $totalJobsData      = $workspaceModel->getTotalJobsByCompany($userId, $isSuperAdmin);

        $data = [
            'title'              => 'Account - Dashboard Workspace',
            'user'               => $this->userPayload,
            'user_id'            => $userId,
            'firstname'          => $this->userPayload['user_firstname'] ?? '',
            'lastname'           => $this->userPayload['user_lastname'] ?? '',
            'is_super_admin'     => $isSuperAdmin,
            'fiscal_id'          => $fiscal_id,
            'companies'          => $companies,
            'active_company_id'  => $active_company_id,
            'active_fiscal_year' => $active_fiscal_year,
            'ws_customer_data'   => $customerCountData,
            'ws_accounting_fees' => $accountingFeesData,
            'ws_annual_closing'  => $annualClosingData,
            'ws_registration'    => $registrationData,
            'ws_total_jobs'      => $totalJobsData,
        ];

        require_once '../app/views/backoffice/dashboard_workspace.php';
    }

    /////////////////////////////////////// tasks ///////////////////////////////////////////////

    public function tasks()
    {
        // 1. ตรวจสอบสิทธิ์ผู้ใช้ก่อน
        $this->checkAuth();

        // 2. รับค่า fiscal_id จาก Session (ตั้งค่ามาจากหน้าหลักผ่าน AJAX)
        $fiscal_id = $_SESSION['fiscal_year_id'] ?? null;

        if (! $fiscal_id) {
            // ถ้าไม่มีรหัสปี ให้เด้งกลับไปหน้าหลัก
            header("Location: " . BASE_URL . "/main");
            exit();
        }

        require_once '../app/models/CompanyModel.php';
        $companyModel = new CompanyModel();
        $userId       = $this->userPayload['user_id'] ?? null;
        $companies    = $companyModel->getAllCompanies($userId);

        // หา company_id และปีของ fiscal_id ที่กำลังใช้งานอยู่
        $active_company_id  = '';
        $active_fiscal_year = '';
        foreach ($companies as $company) {
            if (isset($company['fiscal_years'])) {
                foreach ($company['fiscal_years'] as $fy) {
                    $fy_id = $fy['fiscal_id'] ?? $fy['id'] ?? '';
                    if ($fy_id == $fiscal_id) {
                        $active_company_id  = $company['company_id'] ?? $company['id'] ?? '';
                        $active_fiscal_year = $fy['fiscal_years'] ?? $fy['working_year'] ?? $fy['year'] ?? '';
                        break 2;
                    }
                }
            }
        }

        // 3. เตรียมข้อมูลเบื้องต้นสำหรับส่งไปหน้า View (ถ้ามี)
        $data = [
            'title'              => 'Account - งานที่ต้องทำ',
            'user'               => $this->userPayload,
            'user_id'            => $this->userPayload['user_id'] ?? '',
            'firstname'          => $this->userPayload['user_firstname'] ?? '',
            'lastname'           => $this->userPayload['user_lastname'] ?? '',
            'is_super_admin'     => $this->userPayload['is_super_admin'] ?? '0',
            'fiscal_id'          => $fiscal_id,
            'companies'          => $companies,
            'active_company_id'  => $active_company_id,
            'active_fiscal_year' => $active_fiscal_year,
        ];

        // Fetch tasks for the current fiscal_id
        require_once '../app/models/tasks.php';
        $taskModel  = new TasksModel();
        $tasks_list = $taskModel->getTasksByFiscalId($fiscal_id);

        $data['tasks_list']  = $tasks_list;
        $data['total_tasks'] = count($tasks_list);

        $req_amount_count = 0;
        foreach ($tasks_list as $t) {

            if ($t['is_notify_amount'] == 1) {
                $req_amount_count++;
            }

        }
        $data['req_amount_count']    = $req_amount_count;
        $data['no_req_amount_count'] = $data['total_tasks'] - $req_amount_count;

        // 4. ดึงหน้า View มาแสดงผล
        require_once '../app/views/backoffice/tasks.php';
    }

    public function addTask()
    {
        $this->checkAuth();

        // 1. รับค่าจากฟอร์ม
        $task_name        = trim($_POST['task_name'] ?? '');
        $is_notify_amount = trim($_POST['is_notify_amount'] ?? '0');
        $fiscal_id        = trim($_POST['fiscal_id'] ?? '');

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
        $tasks_id  = $_POST['tasks_id'] ?? '';
        $direction = $_POST['direction'] ?? '';
        $fiscal_id = $_POST['fiscal_id'] ?? '';

        if (! $tasks_id || ! $direction || ! $fiscal_id) {
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

        if (! $tasks_id) {
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
        $tasks_id         = $_POST['tasks_id'] ?? '';
        $task_name        = $_POST['task_name'] ?? '';
        $is_notify_amount = $_POST['is_notify_amount'] ?? '0';

        if (! $tasks_id || ! $task_name) {
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

        if (! $tasks_id) {
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

    public function employee()
    {
        // 1. ตรวจสอบสิทธิ์ผู้ใช้ก่อน
        $this->checkAuth();

        // 2. รับค่า fiscal_id จาก Session (ตั้งค่ามาจากหน้าหลักผ่าน AJAX)
        $fiscal_id = $_SESSION['fiscal_year_id'] ?? null;

        if (! $fiscal_id) {
            // ถ้าไม่มีรหัสปี ให้เด้งกลับไปหน้าหลัก
            header("Location: " . BASE_URL . "/main");
            exit();
        }

        require_once '../app/models/CompanyModel.php';
        $companyModel = new CompanyModel();
        $userId       = $this->userPayload['user_id'] ?? null;
        $companies    = $companyModel->getAllCompanies($userId);

        // หา company_id และปีของ fiscal_id ที่กำลังใช้งานอยู่
        $active_company_id  = '';
        $active_fiscal_year = '';
        foreach ($companies as $company) {
            if (isset($company['fiscal_years'])) {
                foreach ($company['fiscal_years'] as $fy) {
                    $fy_id = $fy['fiscal_id'] ?? $fy['id'] ?? '';
                    if ($fy_id == $fiscal_id) {
                        $active_company_id  = $company['company_id'] ?? $company['id'] ?? '';
                        $active_fiscal_year = $fy['fiscal_years'] ?? $fy['working_year'] ?? $fy['year'] ?? '';
                        break 2;
                    }
                }
            }
        }

        // 3. เตรียมข้อมูลเบื้องต้นสำหรับส่งไปหน้า View (ถ้ามี)
        $data = [
            'title'              => 'Account - จัดการพนักงาน',
            'user'               => $this->userPayload,
            'user_id'            => $this->userPayload['user_id'] ?? '',
            'firstname'          => $this->userPayload['user_firstname'] ?? '',
            'lastname'           => $this->userPayload['user_lastname'] ?? '',
            'is_super_admin'     => $this->userPayload['is_super_admin'] ?? '0',
            'fiscal_id'          => $fiscal_id,
            'companies'          => $companies,
            'active_company_id'  => $active_company_id,
            'active_fiscal_year' => $active_fiscal_year,
        ];

        // ดึงข้อมูลทีม
        require_once '../app/models/TeamModel.php';
        $teamModel     = new TeamModel();
        $data['teams'] = $teamModel->getAllTeams();

        // ดึงข้อมูลพนักงาน (เฉพาะในบริษัทและปีนี้)
        require_once '../app/models/UserModel.php';
        $userModel         = new UserModel();
        $data['employees'] = $userModel->getEmployeesByFiscalAndCompany($fiscal_id, $active_company_id);

        // ดึงข้อมูลผู้ใช้ทั้งหมดในระบบ (สำหรับ autocomplete พนักงานเก่า)
        $data['all_users'] = $userModel->getAllUsersWithTeams();

        // 4. ดึงหน้า View มาแสดงผล
        require_once '../app/views/backoffice/employee.php';
    }

    public function addEmployee()
    {
        $this->checkAuth();

        // 1. รับค่าจากฟอร์ม
        $fiscal_id      = trim($_POST['fiscal_id'] ?? '');
        $company_id     = trim($_POST['company_id'] ?? '');
        $user_name      = trim($_POST['user_name'] ?? '');
        $user_password  = trim($_POST['user_password'] ?? '');
        $user_firstname = trim($_POST['user_firstname'] ?? '');
        $user_lastname  = trim($_POST['user_lastname'] ?? '');
        $user_email     = trim($_POST['user_email'] ?? '');
        $user_position  = trim($_POST['user_position'] ?? '');
        $team_name      = trim($_POST['team_name'] ?? '');

        // 2. ดักตรวจสอบ (Validation เบื้องต้น)
        if ($user_name === '') {
            echo json_encode(['result' => 0, 'msg' => 'กรุณาระบุชื่อผู้ใช้']);
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

            // ถ้าไม่มีผู้ใช้นี้ในระบบ ต้องบังคับกรอกรหัสผ่าน ชื่อ และนามสกุล
            if (! $existingUser) {
                if ($user_password === '' || $user_firstname === '' || $user_lastname === '') {
                    echo json_encode(['result' => 0, 'msg' => 'กรุณากรอกข้อมูลรหัสผ่าน ชื่อ และนามสกุล สำหรับพนักงานใหม่']);
                    return;
                }
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

            if ($existingUser) {
                $userId = $existingUser['user_id'];

                // เชื่อมพนักงานกับบริษัทและปีทำงาน
                // (ในทางปฏิบัติควรเช็คด้วยว่าเคยเชื่อมหรือยัง เพื่อป้องกัน duplicate keys)
                try {
                    $userModel->linkUserToCompany($userId, $company_id);
                } catch (PDOException $e) { /* Ignore if already linked */}

                try {
                    $userModel->linkUserToFiscalYear($userId, $fiscal_id);
                } catch (PDOException $e) { /* Ignore if already linked */}

                echo json_encode(['result' => 1, 'msg' => 'พบผู้ใช้นี้ในระบบ ทำการเพิ่มสิทธิ์การเข้าถึงบริษัทและปีบัญชีนี้ให้เรียบร้อยแล้ว']);
            } else {
                // บันทึก User ใหม่
                $userData = [
                    'user_name'      => $user_name,
                    'user_password'  => password_hash($user_password, PASSWORD_DEFAULT),
                    'user_firstname' => $user_firstname,
                    'user_lastname'  => $user_lastname,
                    'user_email'     => $user_email,
                    'position'       => $user_position,
                    'team_id'        => $team_id,
                ];
                $newUserId = $userModel->insertUser($userData);

                if ($newUserId) {
                    // เชื่อมพนักงานกับบริษัทและปีทำงาน
                    $userModel->linkUserToCompany($newUserId, $company_id);
                    $userModel->linkUserToFiscalYear($newUserId, $fiscal_id);

                    echo json_encode(['result' => 1, 'msg' => 'เพิ่มพนักงานใหม่เรียบร้อยแล้ว']);
                } else {
                    echo json_encode(['result' => 0, 'msg' => 'ไม่สามารถบันทึกข้อมูลพนักงานได้']);
                }
            }
        } catch (PDOException $e) {
            echo json_encode(['result' => 0, 'msg' => 'เกิดข้อผิดพลาดฐานข้อมูล: ' . $e->getMessage()]);
        }
    }

    public function editEmployee()
    {
        $this->checkAuth();

        $user_id        = trim($_POST['user_id'] ?? '');
        $user_firstname = trim($_POST['user_firstname'] ?? '');

        $user_lastname = trim($_POST['user_lastname'] ?? '');
        $user_email    = trim($_POST['user_email'] ?? '');
        $user_position = trim($_POST['user_position'] ?? '');
        $team_name     = trim($_POST['team_name'] ?? '');
        $user_status   = trim($_POST['user_status'] ?? '');

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
                'user_id'        => $user_id,
                'user_firstname' => $user_firstname,

                'user_lastname'  => $user_lastname,
                'user_email'     => $user_email,
                'position'       => $user_position,
                'team_id'        => $team_id,
                'user_status'    => $user_status,
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

            'data'   => $customers,
        ]);
    }

    /////////////////////////////////////// customer ///////////////////////////////////////////////
    public function customer()
    {
        // 1. ตรวจสอบสิทธิ์ผู้ใช้ก่อน
        $this->checkAuth();

        // 2. รับค่า fiscal_id จาก Session (ตั้งค่ามาจากหน้าหลักผ่าน AJAX)
        $fiscal_id = $_SESSION['fiscal_year_id'] ?? null;

        if (! $fiscal_id) {
            // ถ้าไม่มีรหัสปี ให้เด้งกลับไปหน้าหลัก
            header("Location: " . BASE_URL . "/main");
            exit();
        }

        require_once '../app/models/CompanyModel.php';
        $companyModel = new CompanyModel();
        $userId       = $this->userPayload['user_id'] ?? null;
        $companies    = $companyModel->getAllCompanies($userId);

        // หา company_id ของ fiscal_id ที่กำลังใช้งานอยู่
        $active_company_id  = '';
        $active_fiscal_year = '';
        foreach ($companies as $company) {
            if (isset($company['fiscal_years'])) {
                foreach ($company['fiscal_years'] as $fy) {
                    $fy_id = $fy['fiscal_id'] ?? $fy['id'] ?? '';
                    if ($fy_id == $fiscal_id) {
                        $active_company_id  = $company['company_id'] ?? $company['id'] ?? '';
                        $active_fiscal_year = $fy['fiscal_years'] ?? $fy['working_year'] ?? $fy['year'] ?? '';
                        break 2;
                    }
                }
            }
        }

        require_once '../app/models/CustomerModal.php';
        $customModal = new CustomModal();

        $tasks      = $customModal->getTasks($fiscal_id);
        $caretakers = $customModal->getCaretakers($fiscal_id);
        $customers  = $customModal->getCustomersByFiscalId($fiscal_id);
        $stats      = $customModal->getCustomersgid($fiscal_id);

        $data = [
            'title'              => 'Account - ตั้งค่าลูกค้า',
            'user'               => $this->userPayload,
            'user_id'            => $this->userPayload['user_id'] ?? '',
            'user_firstname'     => $this->userPayload['user_firstname'] ?? '',
            'lastname'           => $this->userPayload['user_lastname'] ?? '',
            'is_super_admin'     => $this->userPayload['is_super_admin'] ?? '0',
            'fiscal_id'          => $fiscal_id,
            'companies'          => $companies,
            'active_company_id'  => $active_company_id,
            'tasks'              => $tasks,
            'caretakers'         => $caretakers,
            'customers'          => $customers,
            'stats'              => $stats,
            'active_fiscal_year' => $active_fiscal_year,
        ];

        // 4. ดึงหน้า View มาแสดงผล
        require_once '../app/views/backoffice/customer.php';
    }

    public function customer_dash()
    {
        $this->checkAuth();

        $fiscal_id = $_SESSION['fiscal_year_id'] ?? null;
        if (! $fiscal_id) {
            header("Location: " . BASE_URL . "/main");
            exit();
        }

        require_once '../app/models/CompanyModel.php';
        $companyModel = new CompanyModel();
        $userId       = $this->userPayload['user_id'] ?? null;
        $companies    = $companyModel->getAllCompanies($userId);

        $active_company_id  = '';
        $active_fiscal_year = '';
        foreach ($companies as $company) {
            foreach ($company['fiscal_years'] ?? [] as $fy) {
                $fy_id = $fy['fiscal_id'] ?? $fy['id'] ?? '';
                if ($fy_id == $fiscal_id) {
                    $active_company_id  = $company['company_id'] ?? $company['id'] ?? '';
                    $active_fiscal_year = $fy['fiscal_years'] ?? $fy['working_year'] ?? $fy['year'] ?? '';
                    break 2;
                }
            }
        }

        require_once '../app/models/CustomerModal.php';
        require_once '../app/models/CustomerDashModel.php';
        $customerModel     = new CustomModal();
        $customerDashModel = new CustomerDashModel();
        $customerWork      = $customerDashModel->getCustomerWorkDashboard($fiscal_id);

        $totalTasks          = array_sum(array_map(static fn($customer) => (int) ($customer['total_tasks'] ?? 0), $customerWork));
        $completedTasks      = array_sum(array_map(static fn($customer) => (int) ($customer['completed_tasks'] ?? 0), $customerWork));
        $totalAccountsAmount = array_sum(array_map(static fn($customer) => (float) ($customer['accounts_amount'] ?? 0), $customerWork));
        $totalClosingAmount  = array_sum(array_map(static fn($customer) => (float) ($customer['closing_amount'] ?? 0), $customerWork));
        $totalAuditingAmount = array_sum(array_map(static fn($customer) => (float) ($customer['auditing_amount'] ?? 0), $customerWork));
        $completedCustomers  = count(array_filter($customerWork, static function ($customer) {
            return (int) ($customer['total_tasks'] ?? 0) > 0
            && (int) ($customer['total_tasks'] ?? 0) === (int) ($customer['completed_tasks'] ?? 0);
        }));

        $data = [
            'title'              => 'Account - แดชบอร์ดลูกค้า',
            'user'               => $this->userPayload,
            'user_id'            => $this->userPayload['user_id'] ?? '',
            'firstname'          => $this->userPayload['user_firstname'] ?? '',
            'lastname'           => $this->userPayload['user_lastname'] ?? '',
            'is_super_admin'     => $this->userPayload['is_super_admin'] ?? '0',
            'fiscal_id'          => $fiscal_id,
            'companies'          => $companies,
            'active_company_id'  => $active_company_id,
            'active_fiscal_year' => $active_fiscal_year,
            'stats'              => $customerModel->getCustomersgid($fiscal_id),
            'customers'          => $customerWork,
            'work_stats'         => [
                'total_customers'       => count($customerWork),
                'total_tasks'           => $totalTasks,
                'completed_tasks'       => $completedTasks,
                'unfinished_tasks'      => max(0, $totalTasks - $completedTasks),
                'total_accounts_amount' => $totalAccountsAmount,
                'total_closing_amount'  => $totalClosingAmount,
                'total_auditing_amount' => $totalAuditingAmount,
                'completed_customers'   => $completedCustomers,
            ],
        ];

        require_once '../app/views/backoffice/customer_dash.php';
    }

    public function customerDashboardDetails()
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->checkAuth();

        $fiscalId   = $_SESSION['fiscal_year_id'] ?? null;
        $customerId = ctype_digit((string) ($_GET['customer_id'] ?? ''))
            ? (int) $_GET['customer_id']
            : 0;
        if (! $fiscalId || $customerId < 1) {
            echo json_encode(['result' => 0, 'msg' => 'ข้อมูลไม่ครบถ้วน'], JSON_UNESCAPED_UNICODE);
            return;
        }

        require_once '../app/models/CustomerDashModel.php';
        $model   = new CustomerDashModel();
        $details = $model->getCustomerWorkDetails($fiscalId, $customerId);

        ob_start();
        $data = [
            'customer_task_details'       => $details,
            'customer_task_customer_name' => $details[0]['customer_name'] ?? 'ลูกค้าที่เลือก',
        ];
        require '../app/views/backoffice/table/customer_task_detail.php';
        $html = ob_get_clean();

        echo json_encode([
            'result' => 1,
            'html'   => $html,
        ], JSON_UNESCAPED_UNICODE);
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

        $fiscal_id     = trim($_POST['fiscal_id'] ?? '');
        $company_id    = trim($_POST['company_id'] ?? '');
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
                $fiscalYearId = $customModal->linkCustomerToFiscalYear($customerId, $fiscal_id, $_POST);

                // Add accounts
                $customModal->insertCustomerAccounts($customerId, $fiscalYearId, $_POST['account_name'] ?? [], $_POST['account_user_name'] ?? [], $_POST['account_password'] ?? []);

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
        $fiscal_id   = $_SESSION['fiscal_year_id'] ?? null;

        if (! $customer_id || ! $fiscal_id) {
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
        $fiscal_id   = trim($_POST['fiscal_id'] ?? $_SESSION['fiscal_id'] ?? $this->userPayload['fiscal_id'] ?? '');
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
                $fiscalYearId = $customModal->getFiscalYearCustomerId($customer_id, $fiscal_id);
                if ($fiscalYearId) {
                    $customModal->deleteCustomerAccounts($customer_id, $fiscalYearId);
                    $customModal->insertCustomerAccounts($customer_id, $fiscalYearId, $_POST['account_name'] ?? [], $_POST['account_user_name'] ?? [], $_POST['account_password'] ?? []);
                }
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

   public function customerDashFilter()
{
    header('Content-Type: application/json; charset=utf-8');
    $this->checkAuth();

    $fiscal_id = $_SESSION['fiscal_year_id'] ?? null;
    if (! $fiscal_id) {
        echo json_encode(['result' => 0, 'msg' => 'ไม่พบปีบัญชีที่ใช้งานอยู่'], JSON_UNESCAPED_UNICODE);
        return;
    }

    $month = null;
    if (isset($_GET['month']) && ctype_digit((string) $_GET['month'])) {
        $month = str_pad($_GET['month'], 2, '0', STR_PAD_LEFT);
    }

    require_once '../app/models/CustomerDashModel.php';
    $customerDashModel = new CustomerDashModel();

    $customerWork = $month
        ? $customerDashModel->getCustomerWorkDashboard($fiscal_id, $month)
        : $customerDashModel->getCustomerWorkDashboard($fiscal_id);

    $totalTasks          = array_sum(array_map(static fn($c) => (int) ($c['total_tasks'] ?? 0), $customerWork));
    $completedTasks      = array_sum(array_map(static fn($c) => (int) ($c['completed_tasks'] ?? 0), $customerWork));
    $totalAccountsAmount = array_sum(array_map(static fn($c) => (float) ($c['accounts_amount'] ?? 0), $customerWork));

    // Match the same $data shape that customer_dash_table.php is rendered with
    // on the normal page load (see BackofficeController::customer_dash()).
    $data = ['customers' => $customerWork];

    ob_start();
    require '../app/views/backoffice/table/customer_dash_table.php';
    $html = ob_get_clean();

    echo json_encode([
        'result' => 1,
        'html'   => $html,
        'stats'  => [
            'total_customers'       => count($customerWork),
            'total_tasks'           => $totalTasks,
            'completed_tasks'       => $completedTasks,
            'unfinished_tasks'      => max(0, $totalTasks - $completedTasks),
            'total_accounts_amount' => number_format($totalAccountsAmount, 2),
        ],
    ], JSON_UNESCAPED_UNICODE);
}

    /////////////////////////////////////// registration_board ///////////////////////////////////////////////

    public function registration_board()
    {
        // 1. ตรวจสอบสิทธิ์ผู้ใช้ก่อน
        $this->checkAuth();

        // 2. รับค่า fiscal_id จาก Session (ตั้งค่ามาจากหน้าหลักผ่าน AJAX)
        $fiscal_id = $_SESSION['fiscal_year_id'] ?? null;

        if (! $fiscal_id) {
            // ถ้าไม่มีรหัสปี ให้เด้งกลับไปหน้าหลัก
            header("Location: " . BASE_URL . "/main");
            exit();
        }

        require_once '../app/models/CompanyModel.php';
        $companyModel = new CompanyModel();
        $userId       = $this->userPayload['user_id'] ?? null;
        $companies    = $companyModel->getAllCompanies($userId);

        // หา company_id และปีของ fiscal_id ที่กำลังใช้งานอยู่
        $active_company_id  = '';
        $active_fiscal_year = '';
        foreach ($companies as $company) {
            if (isset($company['fiscal_years'])) {
                foreach ($company['fiscal_years'] as $fy) {
                    $fy_id = $fy['fiscal_id'] ?? $fy['id'] ?? '';
                    if ($fy_id == $fiscal_id) {
                        $active_company_id  = $company['company_id'] ?? $company['id'] ?? '';
                        $active_fiscal_year = $fy['fiscal_years'] ?? $fy['working_year'] ?? $fy['year'] ?? '';
                        break 2;
                    }
                }
            }
        }

        // 3. เตรียมข้อมูลเบื้องต้นสำหรับส่งไปหน้า View (ถ้ามี)
        $data = [
            'title'              => 'Account - จัดการงานทะเบียน',
            'user'               => $this->userPayload,
            'user_id'            => $this->userPayload['user_id'] ?? '',
            'firstname'          => $this->userPayload['user_firstname'] ?? '',
            'lastname'           => $this->userPayload['user_lastname'] ?? '',
            'is_super_admin'     => $this->userPayload['is_super_admin'] ?? '0',
            'fiscal_id'          => $fiscal_id,
            'companies'          => $companies,
            'active_company_id'  => $active_company_id,
            'active_fiscal_year' => $active_fiscal_year,
        ];

        // ดึงรายชื่อพนักงานของปี/บริษัทนี้ ใช้เป็นตัวเลือก "ผู้รับผิดชอบ" ตอนเพิ่มงานทะเบียน
        require_once '../app/models/UserModel.php';
        $userModel         = new UserModel();
        $data['employees'] = $userModel->getEmployeesByFiscalAndCompany($fiscal_id, $active_company_id);

        // ดึงงานทะเบียนของปีนี้ทั้งหมด แล้วแยกใส่แต่ละคอลัมน์ตามสถานะ
        // status ตาม comment ในตาราง tbl_registration:
        // 0 = รับงานลงทะเบียน, 1 = กำลังทำ, 2 = รอตรวจสอบ, 3 = ตรวจสอบแล้ว,
        // 4 = กำลังไปยื่น, 5 = งานเสร็จเรียบร้อยแล้ว, 6 = เก็บเงินเรียบร้อยแล้ว
        require_once '../app/models/RegistrationModel.php';
        $registrationModel = new RegistrationModel();

        $userId       = (int) ($this->userPayload['user_id'] ?? 0);
        $isSuperAdmin = (int) ($this->userPayload['is_super_admin'] ?? 0);

        $registrationTasks = $registrationModel->getTasksByFiscalId($fiscal_id, $userId, $isSuperAdmin);

        $data['stat_open']              = $registrationModel->countOpen($fiscal_id, $userId, $isSuperAdmin);
        $data['stat_not_overdue']       = $registrationModel->countNotOverdue($fiscal_id, $userId, $isSuperAdmin);
        $data['stat_overdue']           = $registrationModel->countOverdue($fiscal_id, $userId, $isSuperAdmin);
        $data['stat_closed_this_month'] = $registrationModel->countClosedThisMonth($fiscal_id, $userId, $isSuperAdmin);
        $data['stat_closed_last_month'] = $registrationModel->countClosedLastMonth($fiscal_id, $userId, $isSuperAdmin);
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
        if (! $fiscal_id) {
            header('Location: ' . BASE_URL . '/main');
            exit();
        }

        require_once '../app/models/CompanyModel.php';
        $companyModel = new CompanyModel();
        $userId       = $this->userPayload['user_id'] ?? null;
        $companies    = $companyModel->getAllCompanies($userId);

        $active_company_id = '';
        foreach ($companies as $company) {
            if (! isset($company['fiscal_years'])) {
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
        $model    = new PostItModel();
        $fiscalId = (int) $fiscal_id;

        $filters = [
            'q'          => trim($_GET['q'] ?? ''),
            'user_id'    => $_GET['user_id'] ?? '',
            'status'     => $_GET['status'] ?? '',
            'color_code' => $_GET['color'] ?? '',
            'due'        => $_GET['due'] ?? '',
            'sort'       => $_GET['sort'] ?? 'created_desc',
        ];

        $perPage = (int) ($_GET['per_page'] ?? 25);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 25;
        }
        $page = max(1, (int) ($_GET['page'] ?? 1));

        try {
            $stats     = $model->getStats($fiscalId);
            $items     = $model->getList($fiscalId, $filters);
            $assignees = $model->getAssignees($fiscalId);
        } catch (Throwable $e) {
            $stats     = ['total' => 0, 'pending' => 0, 'done' => 0, 'overdue' => 0];
            $items     = [];
            $assignees = [];
        }

        $totalItems = count($items);
        $totalPages = max(1, (int) ceil($totalItems / $perPage));
        if ($page > $totalPages) {
            $page = $totalPages;
        }
        $offset    = ($page - 1) * $perPage;
        $pageItems = array_slice($items, $offset, $perPage);
        $from      = $totalItems === 0 ? 0 : $offset + 1;
        $to        = min($offset + $perPage, $totalItems);

        $data = [
            'title'             => 'Account - Post-it',
            'user'              => $this->userPayload,
            'user_id'           => $this->userPayload['user_id'] ?? '',
            'firstname'         => $this->userPayload['user_firstname'] ?? '',
            'lastname'          => $this->userPayload['user_lastname'] ?? '',
            'is_super_admin'    => $this->userPayload['is_super_admin'] ?? '0',
            'fiscal_id'         => $fiscal_id,
            'companies'         => $companies,
            'active_company_id' => $active_company_id,
            'stats'             => $stats,
            'items'             => $pageItems,
            'assignees'         => $assignees,
            'filters'           => $filters,
            'pagination'        => [
                'page'        => $page,
                'per_page'    => $perPage,
                'total'       => $totalItems,
                'total_pages' => $totalPages,
                'from'        => $from,
                'to'          => $to,
            ],
            'is_draft'          => false,
        ];

        require_once '../app/views/backoffice/post_it.php';
    }

    public function storePostIt()
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->checkAuth();

        $fiscal_id = $_SESSION['fiscal_year_id'] ?? null;
        if (! $fiscal_id) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบปีทำงาน กรุณาเลือกปีก่อน']);
            return;
        }

        $title = trim($_POST['title'] ?? '');
        if ($title === '') {
            echo json_encode(['result' => 0, 'msg' => 'กรุณาระบุหัวข้อ Post-it']);
            return;
        }

        $userIdRaw = trim((string) ($_POST['user_id'] ?? ''));
        $userId    = ($userIdRaw === '' || $userIdRaw === '0') ? null : (int) $userIdRaw;

        $dueDateRaw = trim((string) ($_POST['due_date'] ?? ''));
        $dueDate    = $dueDateRaw === '' ? null : $dueDateRaw;
        if ($dueDate !== null && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate)) {
            echo json_encode(['result' => 0, 'msg' => 'รูปแบบวันที่ไม่ถูกต้อง']);
            return;
        }

        $status = (string) ($_POST['status'] ?? '0');
        if (! in_array($status, ['0', '1'], true)) {
            $status = '0';
        }

        $allowedColors = ['yellow', 'pink', 'blue', 'green', 'purple', 'orange'];
        $colorCode     = (string) ($_POST['color_code'] ?? 'yellow');
        if (! in_array($colorCode, $allowedColors, true)) {
            $colorCode = 'yellow';
        }

        $content       = trim((string) ($_POST['content'] ?? ''));
        $createdUserId = (int) ($this->userPayload['user_id'] ?? 0);
        if ($createdUserId <= 0) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบข้อมูลผู้ใช้']);
            return;
        }

        try {
            require_once '../app/models/PostItModel.php';
            $model  = new PostItModel();
            $postId = $model->create([
                'fiscal_year_id'  => (int) $fiscal_id,
                'title'           => $title,
                'user_id'         => $userId,
                'due_date'        => $dueDate,
                'status'          => $status,
                'content'         => $content,
                'color_code'      => $colorCode,
                'created_user_id' => $createdUserId,
            ]);

            if ($postId > 0) {
                if ($userId && $userId != $createdUserId) {
                    require_once '../app/models/NotificationModel.php';
                    // We must ensure the class is called correctly if namespace is used

                    $notifModel   = new \App\Models\NotificationModel();
                    $creatorName  = trim(($this->userPayload['user_firstname'] ?? '') . ' ' . ($this->userPayload['user_lastname'] ?? ''));
                    $notifMessage = "มีงาน Post-it ใหม่มอบหมายถึงคุณ: " . $creatorName . " เรื่อง " . $title;
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
        if (! $post_id) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบ post_id']);
            return;
        }

        require_once '../app/models/PostItModel.php';
        $model = new PostItModel();

        try {
            $item = $model->findById((int) $post_id);

            if (! $item) {
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

        $postId     = trim($_POST['post_id'] ?? '');
        $title      = trim($_POST['title'] ?? '');
        $assigneeId = trim($_POST['user_id'] ?? ''); // แก้เป็น user_id ตามฟอร์ม HTML
        $dueDate    = trim($_POST['due_date'] ?? '');
        $status     = trim($_POST['status'] ?? '0');
        $content    = trim($_POST['content'] ?? '');
        $colorCode  = trim($_POST['color_code'] ?? 'yellow');

        if (! $postId) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบ post_id']);
            return;
        }

        require_once '../app/models/PostItModel.php';
        $model = new PostItModel();

        try {
            $postIdInt = (int) $postId;

            $updated = $model->update($postIdInt, [
                'title'      => $title,
                'user_id'    => $assigneeId ? (int) $assigneeId : null,
                'due_date'   => $dueDate ?: null,
                'status'     => $status,
                'content'    => $content,
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
        if (! $postId) {
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
        if (! $customer_tasks_id) {
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

        $input             = json_decode(file_get_contents('php://input'), true);
        $customer_tasks_id = $input['customer_tasks_id'] ?? '';
        $comment_text      = trim($input['comment_text'] ?? '');
        $user_id           = $this->userPayload['user_id'] ?? null;

        if (! $customer_tasks_id || $comment_text === '' || ! $user_id) {
            echo json_encode(['result' => 0, 'msg' => 'ข้อมูลไม่ครบถ้วน']);
            return;
        }

        require_once '../app/models/monthly_task_Modal.php';
        $model = new MonthlyTaskModal();

        // ตรวจสอบว่ามีคอมเมนต์ใน Task นี้แล้วหรือยัง ถ้ายังไม่มีให้ถือเป็นประเด็นหลัก (0) ถ้ามีแล้วถือเป็นการตอบกลับ (1)
        $existingComments = $model->getCommentsByTaskId((int) $customer_tasks_id);
        $count = count($existingComments);
        $is_reply = $count > 0 ? 1 : 0;
        error_log("storeMonthlyTaskComment: customer_tasks_id=$customer_tasks_id, count=$count, is_reply=$is_reply");

        try {
            $success = $model->addComment((int) $customer_tasks_id, (int) $user_id, $comment_text, $is_reply);
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

        if (! $fiscal_id) {
            // ถ้าไม่มีรหัสปี ให้เด้งกลับไปหน้าหลัก
            header("Location: " . BASE_URL . "/main");
            exit();
        }

        require_once '../app/models/CompanyModel.php';
        $companyModel = new CompanyModel();
        $userId       = $this->userPayload['user_id'] ?? null;
        $companies    = $companyModel->getAllCompanies($userId);
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
        $closingData  = $closingModel->getClosingByFiscalId($fiscal_id);

        require_once '../app/models/UserModel.php';
        $userModel          = new UserModel();
        $employees          = $userModel->getEmployeesByFiscalAndCompany($fiscal_id, $active_company_id);
        $assignedCaretakers = $closingModel->getCaretakersByFiscalId($fiscal_id);

        $caretakerMap = [];
        if (is_array($employees)) {
            foreach ($employees as $emp) {
                if (! empty($emp['user_id'])) {
                    $caretakerMap[$emp['user_id']] = [
                        'user_id'        => $emp['user_id'],
                        'user_firstname' => $emp['user_firstname'] ?? '',
                        'user_lastname'  => $emp['user_lastname'] ?? '',
                    ];
                }
            }
        }
        if (is_array($assignedCaretakers)) {
            foreach ($assignedCaretakers as $ac) {
                if (! empty($ac['user_id']) && ! isset($caretakerMap[$ac['user_id']])) {
                    $caretakerMap[$ac['user_id']] = [
                        'user_id'        => $ac['user_id'],
                        'user_firstname' => $ac['user_firstname'] ?? '',
                        'user_lastname'  => $ac['user_lastname'] ?? '',
                    ];
                }
            }
        }

        require_once '../app/models/UserModel.php';
        $userModel          = new UserModel();
        $employees          = $userModel->getEmployeesByFiscalAndCompany($fiscal_id, $active_company_id);
        $assignedCaretakers = $closingModel->getCaretakersByFiscalId($fiscal_id);

        $caretakerMap = [];
        if (is_array($employees)) {
            foreach ($employees as $emp) {
                if (! empty($emp['user_id'])) {
                    $caretakerMap[$emp['user_id']] = [
                        'user_id'        => $emp['user_id'],
                        'user_firstname' => $emp['user_firstname'] ?? '',
                        'user_lastname'  => $emp['user_lastname'] ?? '',
                    ];
                }
            }
        }
        if (is_array($assignedCaretakers)) {
            foreach ($assignedCaretakers as $ac) {
                if (! empty($ac['user_id']) && ! isset($caretakerMap[$ac['user_id']])) {
                    $caretakerMap[$ac['user_id']] = [
                        'user_id'        => $ac['user_id'],
                        'user_firstname' => $ac['user_firstname'] ?? '',
                        'user_lastname'  => $ac['user_lastname'] ?? '',
                    ];
                }
            }
        }

        $data = [
            'title'             => 'Account - ปิดงบการเงิน',
            'user'              => $this->userPayload,
            'user_id'           => $this->userPayload['user_id'] ?? '',
            'firstname'         => $this->userPayload['user_firstname'] ?? '',
            'lastname'          => $this->userPayload['user_lastname'] ?? '',
            'is_super_admin'    => $this->userPayload['is_super_admin'] ?? '0',
            'fiscal_id'         => $fiscal_id,
            'companies'         => $companies,

            'active_company_id' => $active_company_id,

            'closing_data'      => $closingData,
            'caretakers'        => array_values($caretakerMap),
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
    $this->checkAuth();

    $fiscal_id = $_SESSION['fiscal_year_id'] ?? null;

    if (! $fiscal_id) {
        header("Location: " . BASE_URL . "/main");
        exit();
    }

    require_once '../app/models/CompanyModel.php';
    $companyModel = new CompanyModel();
    $userId       = $this->userPayload['user_id'] ?? null;
    $companies    = $companyModel->getAllCompanies($userId);

    $active_company_id  = '';
    $active_fiscal_year = '';
    foreach ($companies as $company) {
        if (isset($company['fiscal_years'])) {
            foreach ($company['fiscal_years'] as $fy) {
                $fy_id = $fy['fiscal_id'] ?? $fy['id'] ?? '';
                if ($fy_id == $fiscal_id) {
                    $active_company_id  = $company['company_id'] ?? $company['id'] ?? '';
                    $active_fiscal_year = $fy['fiscal_years'] ?? $fy['working_year'] ?? $fy['year'] ?? '';
                    break 2;
                }
            }
        }
    }

    $month      = $_GET['month'] ?? '09';
    $customerId = isset($_GET['customer_id']) && ctype_digit((string) $_GET['customer_id'])
        ? (int) $_GET['customer_id']
        : null;
    $userId       = $this->userPayload['user_id'] ?? null;
    $isSuperAdmin = (int) ($this->userPayload['is_super_admin'] ?? 0);
    $caretakerId  = ! $isSuperAdmin ? $userId : null;

    require_once '../app/models/monthly_task_Modal.php';
    $monthlyTaskModel = new MonthlyTaskModal();

    $monthly_tasks = $monthlyTaskModel->getMonthlyTasks(
        $fiscal_id,
        $customerId ? null : $month,
        $userId,
        $customerId,
        $caretakerId
    );
    $monthly_task_stats = $monthlyTaskModel->getMonthlyTaskStats(
        $fiscal_id,
        $customerId ? null : $month,
        $customerId,
        $caretakerId
    );
    $monthly_task_customers  = $monthlyTaskModel->getMonthlyTaskCustomers($fiscal_id);
    $monthly_task_caretakers = $monthlyTaskModel->getCaretakersByFiscalId($fiscal_id);
    $review_users            = $monthlyTaskModel->getReviewUsers();
    $available_months        = $monthlyTaskModel->getAvailableMonths($fiscal_id);
    $tax_options             = $monthlyTaskModel->getTaxOptions(); // ✅ แก้ตรงนี้

    $data = [
        'title'                   => 'Account - จัดการงานรายเดือน',
        'user'                    => $this->userPayload,
        'user_id'                 => $this->userPayload['user_id'] ?? '',
        'firstname'               => $this->userPayload['user_firstname'] ?? '',
        'lastname'                => $this->userPayload['user_lastname'] ?? '',
        'is_super_admin'          => $this->userPayload['is_super_admin'] ?? '0',
        'fiscal_id'               => $fiscal_id,
        'companies'               => $companies,
        'active_company_id'       => $active_company_id,
        'active_fiscal_year'      => $active_fiscal_year,
        'monthly_tasks'           => $monthly_tasks,
        'monthly_task_stats'      => $monthly_task_stats,
        'selected_month'          => $month,
        'selected_customer_id'    => $customerId,
        'monthly_task_customers'  => $monthly_task_customers,
        'monthly_task_caretakers' => $monthly_task_caretakers,
        'is_customer_year_view'   => $customerId !== null,
        'review_users'            => $review_users,
        'review1_user_id'         => $this->userPayload['user_id'] ?? null,
        'review2_user_id'         => $this->userPayload['user_id'] ?? null,
        'review3_user_id'         => $this->userPayload['user_id'] ?? null,
        'available_months'        => $available_months,
        'tax_options'             => $tax_options, // ✅ ใส่ในอาร์เรย์ $data
    ];

    require_once '../app/views/backoffice/monthly_task.php';
}

    public function filterMonthlyTasks()
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->checkAuth();

        $fiscalId = $_SESSION['fiscal_year_id'] ?? null;
        if (! $fiscalId) {
            echo json_encode(['status' => 'error', 'message' => 'ไม่พบปีบัญชี']);
            return;
        }

        $month         = $_GET['month'] ?? '';
        $customerId    = ctype_digit((string) ($_GET['customer_id'] ?? '')) ? (int) $_GET['customer_id'] : null;
        $caretakerId   = ctype_digit((string) ($_GET['caretaker_id'] ?? '')) ? (int) $_GET['caretaker_id'] : null;
        $docStatus     = $_GET['doc_status'] ?? '';
        $taskStatus    = $_GET['task_status'] ?? '';
        $taxStatus     = $_GET['tax_status'] ?? '';
        $paymentStatus = $_GET['payment_status'] ?? '';
        $keyword       = trim($_GET['keyword'] ?? '');

        require_once '../app/models/monthly_task_Modal.php';
        $model        = new MonthlyTaskModal();
        $userId       = $this->userPayload['user_id'] ?? null;
        $isSuperAdmin = (int) ($this->userPayload['is_super_admin'] ?? 0);

        if (! $isSuperAdmin && $userId) {
            $caretakerId = $userId;
        }
        $monthlyTasks = $model->getMonthlyTasks(
            $fiscalId,
            $customerId ? null : $month,
            $userId,
            $customerId,
            $caretakerId,
            $docStatus,
            $taskStatus,
            $taxStatus,
            $paymentStatus,
            $keyword
        );
        $stats = $model->getMonthlyTaskStats(
            $fiscalId,
            $customerId ? null : $month,
            $customerId,
            $caretakerId,
            $docStatus,
            $taskStatus,
            $taxStatus,
            $paymentStatus,
            $keyword
        );

        $data = [
            'monthly_tasks'        => $monthlyTasks,
            'active_fiscal_year'   => '',
            'selected_customer_id' => $customerId,
        ];

        ob_start();
        if ($customerId) {
            require '../app/views/backoffice/table/monthly_task_customer.php';
        } else {
            require '../app/views/backoffice/table/mounthly_task.php';
        }
        $tableHtml = ob_get_clean();

        echo json_encode([
            'status' => 'success',
            'html'   => $tableHtml,
            'stats'  => $stats,
        ], JSON_UNESCAPED_UNICODE);
    }

    public function getMonthlyTaskItems()
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->checkAuth();

        $period_id = $_GET['period_id'] ?? '';
        if (! $period_id) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบ period_id']);
            return;
        }

        require_once '../app/models/monthly_task_Modal.php';
        $model   = new MonthlyTaskModal();
        $user_id = $this->userPayload['user_id'] ?? null;
        try {
            $period   = $model->getPeriodById($period_id);
            $tasks    = $model->getTasksByPeriodId($period_id, $user_id);
            $accounts = $model->getCustomerAccountsByPeriodId((int) $period_id);
        } catch (Throwable $e) {
            echo json_encode([
                'result'   => 0,
                'period'   => null,
                'tasks'    => [],
                'accounts' => [],
                'msg'      => 'ไม่สามารถโหลดข้อมูลได้',
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
        if ($period) {
            $period['tax_date_1']       = $period['tax_date'] ?? null;
            $period['completed_date_1'] = $period['completed_date'] ?? null;
            $period['tax_date_2']       = $period['tax2_create_at'] ?? null;
            $period['completed_date_2'] = $period['completed_date2'] ?? null;
        }

        echo json_encode([
            'result'   => 1,
            'period'   => $period,
            'tasks'    => $tasks,
            'accounts' => $accounts,
        ], JSON_UNESCAPED_UNICODE);
    }
    public function updateMonthlyTask()
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->checkAuth();

        $input = json_decode(file_get_contents('php://input'), true);
        if (! $input || empty($input['period_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน']);
            return;
        }

        require_once '../app/models/monthly_task_Modal.php';
        $model = new MonthlyTaskModal();

        $periodId = $input['period_id'];

        $periodData = [
            'doc_date'         => $input['doc_date'] ?? null,
            'completed_date_1' => $input['completed_date_1'] ?? null,
            'tax_date_1'       => $input['tax_date_1'] ?? null,
            'completed_date_2' => $input['completed_date_2'] ?? null,
            'tax_date_2'       => $input['tax_date_2'] ?? null,
            'review1_status'   => $input['review1_status'] ?? '0',
            'review1_user_id'  => $input['review1_user_id'] ?? null,

            'review2_status'   => $input['review2_status'] ?? '0',
            'review2_user_id'  => $input['review2_user_id'] ?? null,

            'review3_status'   => $input['review3_status'] ?? '0',
            'review3_user_id'  => $input['review3_user_id'] ?? null,

            'payment_status'   => $input['payment_status'] ?? '0',
            'tax_status'       => $input['tax_status'] ?? '0',
            'doc_status'       => $input['doc_status'] ?? '0',
        ];

        $tasksData = $input['tasks'] ?? [];

        try {
            $model->updatePeriodData($periodId, $periodData);
            if (! empty($tasksData)) {
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

        if (! $fiscal_id) {
            // ถ้าไม่มีรหัสปี ให้เด้งกลับไปหน้าหลัก
            header("Location: " . BASE_URL . "/main");
            exit();
        }

        require_once '../app/models/CompanyModel.php';
        $companyModel = new CompanyModel();
        $userId       = $this->userPayload['user_id'] ?? null;
        $companies    = $companyModel->getAllCompanies($userId);

        // หา company_id ของ fiscal_id ที่กำลังใช้งานอยู่
        $active_company_id  = '';
        $active_fiscal_year = '';
        foreach ($companies as $company) {
            if (isset($company['fiscal_years'])) {
                foreach ($company['fiscal_years'] as $fy) {
                    $fy_id = $fy['fiscal_id'] ?? $fy['id'] ?? '';
                    if ($fy_id == $fiscal_id) {
                        $active_company_id  = $company['company_id'] ?? $company['id'] ?? '';
                        $active_fiscal_year = $fy['fiscal_years'] ?? $fy['working_year'] ?? $fy['year'] ?? '';
                        break 2;
                    }
                }
            }
        }

        require_once '../app/models/closing_Model.php';
        $closingModel = new ClosingModel();
        $closingList  = $closingModel->getClosingByFiscalId($fiscal_id);

        $totalCustomers   = count($closingList);
        $closingCompleted = 0;
        $docReceived      = 0;
        $boj5Count        = 0;
        $dbdCount         = 0;
        $pnd50Count       = 0;

        $caretakersMap = [];

        foreach ($closingList as $item) {
            $isClosingDone = ((string) ($item['closing_status'] ?? '')) === '1';
            $isDocDone     = ((string) ($item['doc_status'] ?? '')) === '1' || ((string) ($item['audit_status'] ?? '')) === '1';
            $isBoj5Done    = ((string) ($item['boj5_status'] ?? '')) === '1';
            $isDbdDone     = ((string) ($item['dbd_efiling_status'] ?? '')) === '1';
            $isPnd50Done   = ((string) ($item['pnd50_status'] ?? '')) === '1';

            if ($isClosingDone) {
                $closingCompleted++;
            }

            if ($isDocDone) {
                $docReceived++;
            }

            if ($isBoj5Done) {
                $boj5Count++;
            }

            if ($isDbdDone) {
                $dbdCount++;
            }

            if ($isPnd50Done) {
                $pnd50Count++;
            }

            $cName = trim(($item['user_firstname'] ?? '') . ' ' . ($item['user_lastname'] ?? ''));
            if (empty($cName)) {
                $cName = 'ไม่ระบุผู้ดูแล';
            }

            if (! isset($caretakersMap[$cName])) {
                $caretakersMap[$cName] = ['name' => $cName, 'total' => 0, 'completed' => 0];
            }
            $caretakersMap[$cName]['total']++;
            if ($isClosingDone) {
                $caretakersMap[$cName]['completed']++;
            }
        }

        $caretakersList = [];
        foreach ($caretakersMap as $c) {
            $pct              = $c['total'] > 0 ? round(($c['completed'] / $c['total']) * 100, 1) : 0;
            $caretakersList[] = [
                'name'      => $c['name'],
                'total'     => $c['total'],
                'completed' => $c['completed'],
                'pending'   => $c['total'] - $c['completed'],
                'percent'   => $pct,
            ];
        }

        $stats = [
            'total_customers'       => $totalCustomers,
            'closing_completed'     => $closingCompleted,
            'audit_completed'       => $docReceived,
            'boj5'                  => $boj5Count,
            'dbd'                   => $dbdCount,
            'pnd50'                 => $pnd50Count,
            'closing_completed_pct' => $totalCustomers > 0 ? round(($closingCompleted / $totalCustomers) * 100, 1) : 0,
            'audit_completed_pct'   => $totalCustomers > 0 ? round(($docReceived / $totalCustomers) * 100, 1) : 0,
            'boj5_pct'              => $totalCustomers > 0 ? round(($boj5Count / $totalCustomers) * 100, 1) : 0,
            'dbd_pct'               => $totalCustomers > 0 ? round(($dbdCount / $totalCustomers) * 100, 1) : 0,
            'pnd50_pct'             => $totalCustomers > 0 ? round(($pnd50Count / $totalCustomers) * 100, 1) : 0,
            'caretakers'            => $caretakersList,
            'closing_list'          => $closingList,
        ];

        // 3. เตรียมข้อมูลเบื้องต้นสำหรับส่งไปหน้า View
        $data = [
            'title'              => 'Account - แดชบอร์ดรายปี',
            'user'               => $this->userPayload,
            'user_id'            => $this->userPayload['user_id'] ?? '',
            'firstname'          => $this->userPayload['user_firstname'] ?? '',
            'lastname'           => $this->userPayload['user_lastname'] ?? '',
            'is_super_admin'     => $this->userPayload['is_super_admin'] ?? '0',
            'fiscal_id'          => $fiscal_id,
            'companies'          => $companies,
            'active_company_id'  => $active_company_id,
            'active_fiscal_year' => $active_fiscal_year,
            'stats'              => $stats,
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

        if (! $fiscal_id) {
            // ถ้าไม่มีรหัสปี ให้เด้งกลับไปหน้าหลัก
            header("Location: " . BASE_URL . "/main");
            exit();
        }

        require_once '../app/models/CompanyModel.php';
        $companyModel = new CompanyModel();
        $userId       = $this->userPayload['user_id'] ?? null;
        $companies    = $companyModel->getAllCompanies($userId);

        // หา company_id ของ fiscal_id ที่กำลังใช้งานอยู่
        $active_company_id  = '';
        $active_fiscal_year = '';
        foreach ($companies as $company) {
            if (isset($company['fiscal_years'])) {
                foreach ($company['fiscal_years'] as $fy) {
                    $fy_id = $fy['fiscal_id'] ?? $fy['id'] ?? '';
                    if ($fy_id == $fiscal_id) {
                        $active_company_id  = $company['company_id'] ?? $company['id'] ?? '';
                        $active_fiscal_year = $fy['fiscal_years'] ?? $fy['working_year'] ?? $fy['year'] ?? '';
                        break 2;
                    }
                }
            }
        }

        // 3. เตรียมข้อมูลเบื้องต้นสำหรับส่งไปหน้า View
        $month    = $_GET['month'] ?? date('m');
        $monthStr = str_pad($month, 2, '0', STR_PAD_LEFT);

        require_once '../app/models/MonthlyDashModel.php';
        $monthlyDashModel = new MonthlyDashModel();
        $dashboardData    = $monthlyDashModel->getDashboardStats($fiscal_id, $monthStr);

        $data = [
            'title'              => 'Account - แดชบอร์ดรายเดือน',
            'user'               => $this->userPayload,
            'user_id'            => $this->userPayload['user_id'] ?? '',
            'firstname'          => $this->userPayload['user_firstname'] ?? '',
            'lastname'           => $this->userPayload['user_lastname'] ?? '',
            'is_super_admin'     => $this->userPayload['is_super_admin'] ?? '0',
            'fiscal_id'          => $fiscal_id,
            'companies'          => $companies,
            'active_company_id'  => $active_company_id,
            'active_fiscal_year' => $active_fiscal_year,
            'selected_month'     => $monthStr,
            'stats'              => $dashboardData,
        ];

        // 4. ดึงหน้า View มาแสดงผล
        require_once '../app/views/backoffice/monthly_dash.php';
    }

    public function getMonthlyStatsAjax()
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->checkAuth();

        $fiscal_id = $_SESSION['fiscal_year_id'] ?? null;
        $month     = $_GET['month'] ?? date('m');
        $monthStr  = str_pad($month, 2, '0', STR_PAD_LEFT);

        if (! $fiscal_id) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบข้อมูลปีทำงาน']);
            return;
        }

        require_once '../app/models/MonthlyDashModel.php';
        $monthlyDashModel = new MonthlyDashModel();
        $dashboardData    = $monthlyDashModel->getDashboardStats($fiscal_id, $monthStr);

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
            'result'     => 1,
            'month'      => $monthStr,
            'month_name' => $month_names[$monthStr] ?? 'มกราคม',
            'stats'      => $dashboardData,
        ]);
    }

    public function customer_message()
    {
        // 1. ตรวจสอบสิทธิ์ผู้ใช้ก่อน
        $this->checkAuth();

        // 2. รับค่า fiscal_id จาก Session (ตั้งค่ามาจากหน้าหลักผ่าน AJAX)
        $fiscal_id = $_SESSION['fiscal_year_id'] ?? null;

        if (! $fiscal_id) {
            // ถ้าไม่มีรหัสปี ให้เด้งกลับไปหน้าหลัก
            header("Location: " . BASE_URL . "/main");
            exit();
        }

        require_once '../app/models/CompanyModel.php';
        $companyModel = new CompanyModel();
        $userId       = $this->userPayload['user_id'] ?? null;
        $companies    = $companyModel->getAllCompanies($userId);

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
            'title'             => 'Account - ส่งข้อความลูกค้า',
            'user'              => $this->userPayload,
            'user_id'           => $this->userPayload['user_id'] ?? '',
            'firstname'         => $this->userPayload['user_firstname'] ?? '',
            'lastname'          => $this->userPayload['user_lastname'] ?? '',
            'is_super_admin'    => $this->userPayload['is_super_admin'] ?? '0',
            'fiscal_id'         => $fiscal_id,
            'companies'         => $companies,
            'active_company_id' => $active_company_id,
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
        if (! $fiscal_id) {
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
        $name      = trim($_POST['name'] ?? '');
        $fiscal_id = $_SESSION['fiscal_year_id'] ?? null;
        if ($name === '') {
            echo json_encode(['result' => 0, 'msg' => 'กรุณากรอกชื่อประเภทงาน']);
            return;
        }
        if (! $fiscal_id) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบข้อมูลปีทำงาน (Fiscal ID)']);
            return;
        }
        require_once '../app/models/RegistrationTypeModel.php';
        $model  = new RegistrationTypeModel();
        $userId = $this->userPayload['user_id'] ?? null;
        $ok     = $model->insert($name, $userId, $fiscal_id);
        echo json_encode($ok ? ['result' => 1, 'msg' => 'เพิ่มประเภทงานเรียบร้อยแล้ว']
                : ['result' => 0, 'msg' => 'ไม่สามารถบันทึกข้อมูลได้']);
    }

    public function editRegistrationType()
    {
        $this->checkAuth();
        $id        = $_POST['id'] ?? '';
        $name      = trim($_POST['name'] ?? '');
        $fiscal_id = $_SESSION['fiscal_year_id'] ?? null;
        if ($id === '' || $name === '') {
            echo json_encode(['result' => 0, 'msg' => 'ข้อมูลไม่ครบถ้วน']);
            return;
        }
        if (! $fiscal_id) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบข้อมูลปีทำงาน (Fiscal ID)']);
            return;
        }
        require_once '../app/models/RegistrationTypeModel.php';
        $model = new RegistrationTypeModel();
        $ok    = $model->update($id, $name, $fiscal_id);
        echo json_encode($ok ? ['result' => 1, 'msg' => 'แก้ไขประเภทงานเรียบร้อยแล้ว']
                : ['result' => 0, 'msg' => 'ไม่สามารถบันทึกข้อมูลได้']);
    }

    public function deleteRegistrationType()
    {
        $this->checkAuth();
        $id        = $_POST['id'] ?? '';
        $fiscal_id = $_SESSION['fiscal_year_id'] ?? null;
        if ($id === '') {
            echo json_encode(['result' => 0, 'msg' => 'ข้อมูลไม่ครบถ้วน']);
            return;
        }
        if (! $fiscal_id) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบข้อมูลปีทำงาน (Fiscal ID)']);
            return;
        }
        require_once '../app/models/RegistrationTypeModel.php';
        $model  = new RegistrationTypeModel();
        $userId = $this->userPayload['user_id'] ?? null;
        $ok     = $model->softDelete($id, $userId, $fiscal_id);
        echo json_encode($ok ? ['result' => 1, 'msg' => 'ลบประเภทงานเรียบร้อยแล้ว']
                : ['result' => 0, 'msg' => 'ไม่สามารถลบข้อมูลได้']);
    }

    public function addRegistrationTask()
    {
        $this->checkAuth();
        $fiscal_id = $_SESSION['fiscal_year_id'] ?? null;

        if (! $fiscal_id) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบข้อมูลปีทำงาน (Fiscal ID)']);
            return;
        }

        // ฟิลด์ที่ห้ามว่าง (ตรงกับคอลัมน์ NOT NULL ใน tbl_registration)
        $data = [
            'registration_type_id' => trim($_POST['registration_type_id'] ?? ''),
            'customer_name'        => trim($_POST['customer_name'] ?? ''),
            'customer_phone'       => trim($_POST['customer_phone'] ?? ''),
            'contact_person'       => trim($_POST['contact_person'] ?? ''),
            'registration_name'    => trim($_POST['registration_name'] ?? ''),
            'description'          => trim($_POST['description'] ?? ''),
            'service_amount'       => trim($_POST['service_amount'] ?? '0'),
            'urgency_level'        => trim($_POST['urgency_level'] ?? ''),
            'accep_date'           => trim($_POST['accep_date'] ?? ''),
            'due_date'             => trim($_POST['due_date'] ?? ''),
            'assignee_user_id'     => trim($_POST['assignee_user_id'] ?? ''),
            'review_user_id'       => trim($_POST['review_user_id'] ?? ''),
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

        $userId       = (int) ($this->userPayload['user_id'] ?? 0);
        $isSuperAdmin = (int) ($this->userPayload['is_super_admin'] ?? 0);
        $ok           = $model->insert($fiscal_id, $data);

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
        $task  = $model->getById($id);

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
            'customer_name'        => trim($_POST['customer_name'] ?? ''),
            'customer_phone'       => trim($_POST['customer_phone'] ?? ''),
            'contact_person'       => trim($_POST['contact_person'] ?? ''),
            'registration_name'    => trim($_POST['registration_name'] ?? ''),
            'description'          => trim($_POST['description'] ?? ''),
            'service_amount'       => trim($_POST['service_amount'] ?? '0'),
            'urgency_level'        => trim($_POST['urgency_level'] ?? ''),
            'accep_date'           => trim($_POST['accep_date'] ?? ''),
            'due_date'             => trim($_POST['due_date'] ?? ''),
            'assignee_user_id'     => trim($_POST['assignee_user_id'] ?? ''),
            'review_user_id'       => trim($_POST['review_user_id'] ?? ''),
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
        $ok    = $model->update($id, $data);

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
        $model  = new RegistrationModel();
        $userId = $this->userPayload['user_id'] ?? null;
        $ok     = $model->softDelete($id, $userId);

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
        $model  = new RegistrationModel();
        $userId = $this->userPayload['user_id'] ?? null;
        $ok     = $model->closeJob($id, $userId);

        echo json_encode($ok
                ? ['result' => 1, 'msg' => 'ปิด Job และย้ายไปประวัติเรียบร้อยแล้ว']
                : ['result' => 0, 'msg' => 'ไม่สามารถปิด Job ได้ (อาจถูกปิดไปแล้ว)']);
    }

    // ประวัติงานทะเบียนที่ปิดแล้ว — คืน JSON ให้ Modal ประวัติในหน้าบอร์ด (แบ่งหน้า + ค้นหา)
    public function getRegistrationHistory()
    {
        $this->checkAuth();
        $fiscal_id = $_SESSION['fiscal_year_id'] ?? null;

        if (! $fiscal_id) {
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
        $model        = new RegistrationModel();
        $userId       = $this->userPayload['user_id'] ?? null;
        $isSuperAdmin = $this->userPayload['is_super_admin'] ?? 0;
        $summary      = $model->countClosedTasks($fiscal_id, $keyword, $userId, $isSuperAdmin);
        $rows         = $model->getClosedTasks($fiscal_id, $keyword, $perPage, $offset, $userId, $isSuperAdmin);

        echo json_encode([
            'result'       => 1,
            'data'         => $rows,
            'page'         => $page,
            'per_page'     => $perPage,
            'total_count'  => (int) ($summary['total_count'] ?? 0),
            'total_amount' => (float) ($summary['total_amount'] ?? 0),
        ]);
    }

    public function updateRegistrationTaskStatus()
    {
        $this->checkAuth();
        $id            = $_POST['id'] ?? '';
        $status        = $_POST['status'] ?? '';
        $validStatuses = ['0', '1', '2', '3', '4', '5', '6'];

        if ($id === '' || ! in_array($status, $validStatuses, true)) {
            echo json_encode(['result' => 0, 'msg' => 'ข้อมูลไม่ครบถ้วน']);
            return;
        }

        require_once '../app/models/RegistrationModel.php';
        $model = new RegistrationModel();
        $ok    = $model->updateStatus($id, $status);

        echo json_encode($ok
                ? ['result' => 1, 'msg' => 'อัปเดตสถานะเรียบร้อยแล้ว']
                : ['result' => 0, 'msg' => 'ไม่สามารถอัปเดตสถานะได้']);
    }

    public function toggleRegistrationTypeStatus()
    {
        $this->checkAuth();
        $id           = $_POST['id'] ?? '';
        $activeStatus = $_POST['active_status'] ?? '';
        $fiscal_id    = $_SESSION['fiscal_year_id'] ?? null;
        if ($id === '' || ($activeStatus !== '0' && $activeStatus !== '1')) {
            echo json_encode(['result' => 0, 'msg' => 'ข้อมูลไม่ครบถ้วน']);
            return;
        }
        if (! $fiscal_id) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบข้อมูลปีทำงาน (Fiscal ID)']);
            return;
        }
        require_once '../app/models/RegistrationTypeModel.php';
        $model = new RegistrationTypeModel();
        $ok    = $model->setActiveStatus($id, $activeStatus, $fiscal_id);
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
        if (! $fiscal_id) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบข้อมูลปีทำงาน (Fiscal ID)']);
            return;
        }

        require_once '../app/models/RegistrationTaskSettingModel.php';
        $model = new RegistrationTaskSettingModel();

        // เรียก 2 เมธอดจาก model โดยส่ง fiscal_id เข้าไปทั้งคู่
        $settings      = $model->getSettings($fiscal_id);
        $urgencyLevels = $model->getUrgencyLevels($fiscal_id);

        // ห่อผลลัพธ์ทั้งสองก้อนเป็น JSON เดียว ส่งกลับไปให้ฝั่ง JS
        echo json_encode([
            'result' => 1,
            'data'   => [
                'notify_day'     => $settings['notify_day'],
                'urgency_levels' => $urgencyLevels,
            ],
        ]);
    }

    public function saveRegistrationTaskSettings()
    {
        $this->checkAuth();
        $fiscal_id = $_SESSION['fiscal_year_id'] ?? null;

        if (! $fiscal_id) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบข้อมูลปีทำงาน (Fiscal ID)']);
            return;
        }

        // (int) แปลงเป็นเลขจำนวนเต็ม กัน string แปลกๆ หลุดเข้าไปใน DB
        $notifyDays = (int) ($_POST['notify_day'] ?? 7);
        $userId     = $this->userPayload['user_id'] ?? null;

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

    /////////////////////////////////////// notifications ///////////////////////////////////////////////

    public function notifications()
    {
        $this->checkAuth();
        $userId = $this->userPayload['user_id'] ?? null;
        if (! $userId) {
            header("Location: " . BASE_URL . "/login");
            exit();
        }

        require_once '../app/models/NotificationModel.php';
        $notifModel = new \App\Models\NotificationModel();

        $fiscal_id = $_SESSION['fiscal_year_id'] ?? null;
        require_once '../app/models/CompanyModel.php';
        $companyModel = new CompanyModel();
        $companies    = $companyModel->getAllCompanies($userId);

        $active_company_id  = '';
        $active_fiscal_year = '';
        foreach ($companies as $company) {
            if (isset($company['fiscal_years'])) {
                foreach ($company['fiscal_years'] as $fy) {
                    $fy_id = $fy['fiscal_id'] ?? $fy['id'] ?? '';
                    if ($fy_id == $fiscal_id) {
                        $active_company_id  = $company['company_id'] ?? $company['id'] ?? '';
                        $active_fiscal_year = $fy['fiscal_years'] ?? $fy['working_year'] ?? $fy['year'] ?? '';
                        break 2;
                    }
                }
            }
        }

        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        if ($page < 1) {
            $page = 1;
        }

        $perPage = 20;

        $notifications = $notifModel->getAllNotifications($userId, $page, $perPage);
        $totalCount    = $notifModel->getTotalCount($userId);
        $totalPages    = ceil($totalCount / $perPage);

        $data = [
            'title'         => 'Account - การแจ้งเตือนทั้งหมด',
            'firstname'     => $this->userPayload['user_firstname'] ?? '',
            'lastname'      => $this->userPayload['user_lastname'] ?? '',
            'username'      => $this->userPayload['user_name'] ?? '',
            'user_id'       => $userId,
            'is_super_admin'=> $this->userPayload['is_super_admin'] ?? '0',
            'fiscal_id'          => $fiscal_id,
            'active_company_id'  => $active_company_id,
            'active_fiscal_year' => $active_fiscal_year,
            'companies'          => $companies,
            'notifications' => $notifications,
            'pagination'    => [
                'current_page' => $page,
                'total_pages'  => $totalPages,
                'total_items'  => $totalCount,
            ],
        ];

        require_once '../app/views/backoffice/notifications.php';
    }

    public function getNotifications()
    {
        header('Content-Type: application/json; charset=utf-8');

        // Use manual auth check to guarantee JSON response instead of relying on checkAuth()
        // which might redirect to login if headers are stripped.
        require_once '../app/models/AuthModel.php';
        $user = \App\models\AuthModel::checkWebAuth();
        if (! $user) {
            http_response_code(401);
            echo json_encode(['result' => 0, 'msg' => 'Session expired']);
            exit();
        }
        $this->userPayload = $user;

        $userId = $this->userPayload['user_id'] ?? null;
        if (! $userId) {
            echo json_encode(['result' => 0, 'msg' => 'Unauthorized']);
            return;
        }

        require_once '../app/models/NotificationModel.php';
        $notifModel = new \App\Models\NotificationModel();

        $fiscal_id     = $_SESSION['fiscal_year_id'] ?? null;
        $notifications = $notifModel->getUnreadNotifications($userId, 20, $fiscal_id);
        $count         = $notifModel->getUnreadCount($userId, $fiscal_id);

        echo json_encode([
            'result' => 1,
            'count'  => $count,
            'data'   => $notifications,
        ]);
    }

    public function readNotification()
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->checkAuth();

        $userId  = $this->userPayload['user_id'] ?? null;
        $notifId = $_POST['notif_id'] ?? null;

        if (! $userId || ! $notifId) {
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

    public function readAllNotifications()
    {
        header('Content-Type: application/json; charset=utf-8');

        require_once '../app/models/AuthModel.php';
        $user = \App\models\AuthModel::checkWebAuth();
        if (! $user) {
            http_response_code(401);
            echo json_encode(['result' => 0, 'msg' => 'Session expired']);
            exit();
        }
        $this->userPayload = $user;

        $userId = $this->userPayload['user_id'] ?? null;
        if (! $userId) {
            echo json_encode(['result' => 0, 'msg' => 'Unauthorized']);
            return;
        }

        require_once '../app/models/NotificationModel.php';
        $notifModel = new \App\Models\NotificationModel();

        $success = $notifModel->markAllAsRead($userId);

        echo json_encode([
            'result' => $success ? 1 : 0,
            'msg'    => $success ? 'Success' : 'Failed',
        ]);
    }

    public function getVapidPublicKey()
    {
        header('Content-Type: application/json; charset=utf-8');

        // โหลด .env ถ้ายังไม่ได้โหลด
        if (empty($_ENV['VAPID_PUBLIC_KEY']) && class_exists('Dotenv\Dotenv')) {
            \Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2))->safeLoad();
        }

        echo json_encode([
            'publicKey' => $_ENV['VAPID_PUBLIC_KEY'] ?? '',
        ]);
    }

    public function subscribePush()
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->checkAuth();
        $userId = $this->userPayload['user_id'] ?? null;

        if (! $userId) {
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
            $pdo      = \App\config\Connection::getInstance()->getPdo();
            $endpoint = $input['endpoint'];
            $p256dh   = $input['keys']['p256dh'] ?? '';
            $auth     = $input['keys']['auth'] ?? '';

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

    public function unsubscribePush()
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->checkAuth();
        $userId = $this->userPayload['user_id'] ?? null;

        if (! $userId) {
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
            $pdo      = \App\config\Connection::getInstance()->getPdo();
            $endpoint = $input['endpoint'];

            // Delete subscription
            $stmt = $pdo->prepare("DELETE FROM tbl_push_subscriptions WHERE endpoint = ? AND user_id = ?");
            $stmt->execute([$endpoint, $userId]);

            echo json_encode(['result' => 1, 'msg' => 'Unsubscribed']);
        } catch (\Throwable $e) {
            echo json_encode(['result' => 0, 'msg' => $e->getMessage()]);
        }
    }

    private function sendWebPush($userId, $title, $body, $url = '')
    {
        try {
            require_once '../app/config/Connection.php';
            require_once '../vendor/autoload.php';
            $pdo  = \App\config\Connection::getInstance()->getPdo();
            $stmt = $pdo->prepare("SELECT endpoint, p256dh, auth FROM tbl_push_subscriptions WHERE user_id = ?");
            $stmt->execute([$userId]);
            $subs = $stmt->fetchAll();

            if (empty($subs)) {
                return;
            }

            $auth = [
                'VAPID' => [
                    'subject'    => 'mailto:admin@' . ($_SERVER['HTTP_HOST'] ?? 'localhost'),
                    'publicKey'  => $_ENV['VAPID_PUBLIC_KEY'] ?? '',
                    'privateKey' => $_ENV['VAPID_PRIVATE_KEY'] ?? '',
                ],
            ];

            $webPush = new \Minishlink\WebPush\WebPush($auth);

            $payload = json_encode([
                'title' => $title,
                'body'  => $body,
                'url'   => $url ?: ($_ENV['APP_URL'] ?? ''),
            ]);

            foreach ($subs as $sub) {
                $subscription = \Minishlink\WebPush\Subscription::create([
                    'endpoint'  => $sub['endpoint'],
                    'publicKey' => $sub['p256dh'],
                    'authToken' => $sub['auth'],
                ]);
                $webPush->queueNotification($subscription, $payload);
            }

            foreach ($webPush->flush() as $report) {
                if (! $report->isSuccess()) {
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

    /////////////////////////////////////// assign_task ///////////////////////////////////////////////

    public function assign_task()
    {
        // เปิดแสดง Error ทั้งหมดบนหน้าจอ (สำหรับการ Debug)
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');
        error_reporting(E_ALL);
        // 1. ตรวจสอบสิทธิ์ผู้ใช้ก่อน
        $this->checkAuth();
        // 2. รับค่า fiscal_id จาก Session
        $fiscal_id = $_SESSION['fiscal_year_id'] ?? null;

        if (! $fiscal_id) {
            header("Location: " . BASE_URL . "/main");
            exit();
        }

        require_once '../app/models/CompanyModel.php';
        $companyModel = new CompanyModel();
        $userId       = $this->userPayload['user_id'] ?? null;
        $companies    = $companyModel->getAllCompanies($userId);

        // หา company_id ของ fiscal_id ที่กำลังใช้งานอยู่
        $active_company_id  = '';
        $active_fiscal_year = '';
        foreach ($companies as $company) {
            if (isset($company['fiscal_years'])) {
                foreach ($company['fiscal_years'] as $fy) {
                    $fy_id = $fy['fiscal_id'] ?? $fy['id'] ?? '';
                    if ($fy_id == $fiscal_id) {
                        $active_company_id  = $company['company_id'] ?? $company['id'] ?? '';
                        $active_fiscal_year = $fy['fiscal_years'] ?? $fy['working_year'] ?? $fy['year'] ?? '';
                        break 2;
                    }
                }
            }
        }

        require_once '../app/models/UserModel.php';
        $userModel = new UserModel();
        $employees = $userModel->getEmployeesByFiscalAndCompany($fiscal_id, $active_company_id);

        require_once '../app/models/AssignTaskModel.php';
        $assignTaskModel = new AssignTaskModel();

        require_once '../app/models/CustomerModal.php';
        $customerModel = new CustomModal();
        $customers     = $customerModel->getCustomersByFiscalId($fiscal_id);

        // Pagination setup
        $page   = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $limit  = 10;
        $offset = ($page - 1) * $limit;

        // Filters
        $filters = [
            'assignee_id' => $_GET['assignee_id'] ?? '',
            'status'      => $_GET['status'] ?? '',
        ];

        $tasks      = $assignTaskModel->getAssignTasks($active_company_id, $fiscal_id, $filters, $limit, $offset);
        $totalTasks = $assignTaskModel->getAssignTasksCount($active_company_id, $fiscal_id, $filters);
        $totalPages = ceil($totalTasks / $limit);
        $stats      = $assignTaskModel->getAssignTaskStats($active_company_id, $fiscal_id);

        // 3. เตรียมข้อมูล
        $data = [
            'title'              => 'Account - การมอบหมายงาน',
            'user'               => $this->userPayload,
            'user_id'            => $this->userPayload['user_id'] ?? '',
            'firstname'          => $this->userPayload['user_firstname'] ?? '',
            'lastname'           => $this->userPayload['user_lastname'] ?? '',
            'is_super_admin'     => $this->userPayload['is_super_admin'] ?? '0',
            'fiscal_id'          => $fiscal_id,
            'companies'          => $companies,
            'active_company_id'  => $active_company_id,
            'active_fiscal_year' => $active_fiscal_year,
            'employees'          => $employees,
            'customers'          => $customers,
            'tasks'              => $tasks,
            'stats'              => $stats,
            'pagination'         => [
                'current_page' => $page,
                'total_pages'  => $totalPages,
                'total_items'  => $totalTasks,
                'limit'        => $limit,
            ],
            'filters'            => $filters,
        ];

        // 4. ดึงหน้า View มาแสดงผล
        require_once '../app/views/backoffice/assign_task.php';
    }

    public function saveAssignTask()
    {
        // เปิดแสดง Error สำหรับ Debug (ควรปิดตอน Production)
        ini_set('display_errors', '1');
        error_reporting(E_ALL);

        $this->checkAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fiscal_id = $_SESSION['fiscal_year_id'] ?? null;
            $user_id   = $this->userPayload['user_id'] ?? null;

            // ดึง active_company_id จาก logic เดิม (หรือถ้า front-end ส่งมาก็ใช้จาก POST ได้เลย)
            // สำหรับความง่ายในตอนนี้ หากไม่ได้ส่ง company_id มา ให้ดึงจาก company ของ user ที่เกี่ยวกับ fiscal_id
            require_once '../app/models/CompanyModel.php';
            $companyModel      = new CompanyModel();
            $companies         = $companyModel->getAllCompanies($user_id);
            $active_company_id = '';
            foreach ($companies as $company) {
                if (isset($company['fiscal_years'])) {
                    foreach ($company['fiscal_years'] as $fy) {
                        if (($fy['fiscal_id'] ?? $fy['id'] ?? '') == $fiscal_id) {
                            $active_company_id = $company['company_id'] ?? $company['id'] ?? '';
                            break 2;
                        }
                    }
                }
            }

            if (empty($active_company_id) || empty($fiscal_id)) {
                echo json_encode(['result' => 0, 'msg' => 'ไม่พบข้อมูลปีบัญชีหรือบริษัทที่กำลังใช้งาน']);
                exit;
            }

            $assign_title  = $_POST['assign_title'] ?? '';
            $assign_detail = $_POST['assign_detail'] ?? '';
            $assignee_id   = $_POST['user_id'] ?? ''; // จาก Select
            $due_date      = $_POST['due_date'] ?? '';
            $customer_id   = $_POST['customer_id'] ?? null;
            $assign_id     = $_POST['assign_id'] ?? null; // ถ้ามีคือแก้ไข

            if (empty($customer_id)) {
                $customer_id = null;
            }

            if (empty($assign_title) || empty($assignee_id) || empty($due_date)) {
                echo json_encode(['result' => 0, 'msg' => 'กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน']);
                exit;
            }

            require_once '../app/models/AssignTaskModel.php';
            $assignTaskModel = new AssignTaskModel();

            $assign_status = $_POST['assign_status'] ?? 0;

            $data = [
                'company_id'     => $active_company_id,
                'user_id'        => $assignee_id,
                'fiscal_id'      => $fiscal_id,
                'customer_id'    => $customer_id,
                'assign_title'   => $assign_title,
                'assign_detail'  => $assign_detail,
                'due_date'       => $due_date,
                'assign_status'  => $assign_status,
                'create_user_id' => $user_id,
            ];

            try {
                if (! empty($assign_id)) {
                    // อัปเดตข้อมูล
                    $data['assign_id'] = $assign_id;
                    $result            = $assignTaskModel->updateAssignTask($data);
                    $msg               = 'อัปเดตข้อมูลมอบหมายงานสำเร็จ';

                    echo json_encode(['result' => 1, 'msg' => $msg]);
                    exit;
                }

                $assign_id = $assignTaskModel->createAssignTask($data);
                if ($assign_id) {

                    // -- ส่งอีเมลแจ้งเตือน --
                    require_once '../app/models/UserModel.php';
                    $userModelForMail = new UserModel();
                    $assignedUser     = $userModelForMail->getUserById($assignee_id);

                    $emailSent = false;
                    if ($assignedUser && ! empty($assignedUser['user_email'])) {
                        $toEmail = $assignedUser['user_email'];
                        $toName  = trim(($assignedUser['user_firstname'] ?? '') . ' ' . ($assignedUser['user_lastname'] ?? ''));

                        require_once '../app/services/MailService.php';
                        $mailService = new \App\Services\MailService();
                        $emailSent   = $mailService->sendTaskAssignmentEmail($toEmail, $toName, $assign_title, $due_date, $assign_detail);
                    }

                    // -- ส่งแจ้งเตือน Web Push & กระดิ่ง --
                    require_once '../app/models/NotificationModel.php';
                    $notifModel = new \App\Models\NotificationModel();
                    $notifMsg   = "คุณได้รับมอบหมายงานใหม่: {$assign_title} (กำหนดส่ง: {$due_date})";
                    $urlLink    = "/assign_task?assign_id=" . $assign_id;
                    $notifModel->addNotification($assignee_id, 'assign_task', $assign_id, $notifMsg, $urlLink, $fiscal_id);
                    $this->sendWebPush($assignee_id, 'งานใหม่', $notifMsg, '/assign_task');

                    $msg = 'มอบหมายงานสำเร็จ';
                    if (! $emailSent) {
                        $msg .= ' (แต่ไม่สามารถส่งอีเมลแจ้งเตือนได้ อาจตั้งค่า SMTP ไม่ถูกต้องหรือไม่มีอีเมล)';
                    }

                    echo json_encode(['result' => 1, 'msg' => $msg]);
                } else {
                    echo json_encode(['result' => 0, 'msg' => 'ไม่สามารถมอบหมายงานได้']);
                }
            } catch (Exception $e) {
                echo json_encode(['result' => 0, 'msg' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
            }
        }
    }

    /////////////////////////////////////// system_setting ///////////////////////////////////////////////

    public function system_setting()
    {
        $this->checkAuth();
        $fiscal_id = $_SESSION['fiscal_year_id'] ?? null;

        if (! $fiscal_id) {
            header("Location: " . BASE_URL . "/main");
            exit();
        }

        // Only super admin can access this page
        if (! isset($this->userPayload['is_super_admin']) || $this->userPayload['is_super_admin'] != 1) {
            header("Location: " . BASE_URL . "/backoffice");
            exit();
        }

        require_once '../app/models/CompanyModel.php';
        require_once '../app/models/System_setting_Modal.php';

        $companyModel = new CompanyModel();
        $taxModel     = new System_setting_Modal();
        $userId       = $this->userPayload['user_id'] ?? null;
        $companies    = $companyModel->getAllCompanies($userId);

        // ดึงข้อมูล tax options (สร้างตารางก่อนถ้ายังไม่มี)
        try {
            $tax_options = $taxModel->getAll();
        } catch (Exception $e) {
            $tax_options = [];
        }

        $active_company_id  = '';
        $active_fiscal_year = '';
        foreach ($companies as $company) {
            if (isset($company['fiscal_years'])) {
                foreach ($company['fiscal_years'] as $fy) {
                    $fy_id = $fy['fiscal_id'] ?? $fy['id'] ?? '';
                    if ($fy_id == $fiscal_id) {
                        $active_company_id  = $company['company_id'] ?? $company['id'] ?? '';
                        $active_fiscal_year = $fy['fiscal_years'] ?? $fy['working_year'] ?? $fy['year'] ?? '';
                        break 2;
                    }
                }
            }
        }

        $data = [
            'title'              => 'Account - ตั้งค่าระบบ',
            'user'               => $this->userPayload,
            'user_id'            => $this->userPayload['user_id'] ?? '',
            'firstname'          => $this->userPayload['user_firstname'] ?? '',
            'lastname'           => $this->userPayload['user_lastname'] ?? '',
            'user_email'              => $this->userPayload['user_email'] ?? '',
            'username'           => $this->userPayload['user_name'] ?? '',
            'is_super_admin'     => $this->userPayload['is_super_admin'] ?? '0',
            'fiscal_id'          => $fiscal_id,
            'companies'          => $companies,
            'active_company_id'  => $active_company_id,
            'active_fiscal_year' => $active_fiscal_year,
            'tax_options'        => $tax_options,
        ];

        require_once '../app/views/backoffice/system_setting.php';
    }

    public function updateProfile()
    {
        $this->checkAuth();
        if (! isset($this->userPayload['is_super_admin']) || $this->userPayload['is_super_admin'] != 1) {
            echo json_encode(['status' => 'error', 'message' => 'ไม่มีสิทธิ์']);
            return;
        }

        $userId    = (int) ($_POST['user_id'] ?? 0);
        $firstname = trim($_POST['user_firstname'] ?? '');
        $lastname  = trim($_POST['user_lastname'] ?? '');
        $username  = trim($_POST['user_name'] ?? '');
        $email     = trim($_POST['email'] ?? '');

        if ($userId <= 0 || empty($firstname) || empty($username)) {
            echo json_encode(['status' => 'error', 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
            return;
        }

        require_once '../app/models/System_setting_Modal.php';
        $model = new System_setting_Modal();

        try {
            $ok = $model->updateProfile($userId, $firstname, $lastname, $username, $email);
            echo json_encode([
                'status'  => $ok ? 'success' : 'error',
                'message' => $ok ? 'บันทึกข้อมูลส่วนตัวสำเร็จ' : 'เกิดข้อผิดพลาดในการบันทึก'
            ]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function changePassword()
    {
        $this->checkAuth();
        if (! isset($this->userPayload['is_super_admin']) || $this->userPayload['is_super_admin'] != 1) {
            echo json_encode(['status' => 'error', 'message' => 'ไม่มีสิทธิ์']);
            return;
        }

        $userId      = (int) ($this->userPayload['user_id'] ?? 0);
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPass = $_POST['confirm_password'] ?? '';

        if (empty($newPassword)) {
            echo json_encode(['status' => 'error', 'message' => 'กรุณากรอกรหัสผ่านใหม่']);
            return;
        }
        if ($newPassword !== $confirmPass) {
            echo json_encode(['status' => 'error', 'message' => 'รหัสผ่านใหม่ไม่ตรงกัน']);
            return;
        }
        if (strlen($newPassword) < 6) {
            echo json_encode(['status' => 'error', 'message' => 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร']);
            return;
        }

        require_once '../app/models/System_setting_Modal.php';
        $model = new System_setting_Modal();

        try {
            $hashed = password_hash($newPassword, PASSWORD_BCRYPT);
            $ok = $model->changePassword($userId, $hashed);
            echo json_encode([
                'status'  => $ok ? 'success' : 'error',
                'message' => $ok ? 'เปลี่ยนรหัสผ่านสำเร็จ' : 'เกิดข้อผิดพลาดในการบันทึก'
            ]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function saveTaxOption()
    {
        $this->checkAuth();

        if (! isset($this->userPayload['is_super_admin']) || $this->userPayload['is_super_admin'] != 1) {
            echo json_encode(['status' => 'error', 'message' => 'ไม่มีสิทธิ์']);
            return;
        }

        require_once '../app/models/System_setting_Modal.php';
        $taxModel = new System_setting_Modal();

        $id    = (int) ($_POST['option_id'] ?? 0);
        $name  = trim($_POST['option_name'] ?? '');
        $order = (int) ($_POST['list_order'] ?? 0);

        if (empty($name)) {
            echo json_encode(['status' => 'error', 'message' => 'กรุณากรอกชื่อภาษี']);
            return;
        }

        try {
            if ($id > 0) {
                $ok = $taxModel->update($id, $name, $order);
            } else {
                $userId    = (int) ($this->userPayload['user_id'] ?? 0);
                $nextOrder = $taxModel->getNextOrder(); // auto ลำดับถัดจากรายการล่าสุด
                $ok = $taxModel->create($name, $nextOrder, $userId);
            }
            echo json_encode(['status' => $ok ? 'success' : 'error', 'message' => $ok ? 'บันทึกสำเร็จ' : 'เกิดข้อผิดพลาด']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function deleteTaxOption()
    {
        $this->checkAuth();
        if (! isset($this->userPayload['is_super_admin']) || $this->userPayload['is_super_admin'] != 1) {
            echo json_encode(['status' => 'error', 'message' => 'ไม่มีสิทธิ์']);
            return;
        }

        require_once '../app/models/System_setting_Modal.php';
        $taxModel = new System_setting_Modal();

        $id = (int) ($_POST['option_id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'ไม่พบ ID']);
            return;
        }

        try {
            $ok = $taxModel->delete($id);
            echo json_encode(['status' => $ok ? 'success' : 'error', 'message' => $ok ? 'ลบสำเร็จ' : 'เกิดข้อผิดพลาด']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /////////////////////////////////////// issues ///////////////////////////////////////////////
    public function issues()
    {
        $this->checkAuth();

        // 1. ดึงปีบัญชีและบริษัทที่เลือกใน Session (ใช้หลักการเดียวกับหน้าอื่น)
        $fiscal_id = $_SESSION['fiscal_year_id'] ?? null;

        require_once '../app/models/CompanyModel.php';
        $companyModel = new CompanyModel();
        $userId       = $this->userPayload['user_id'] ?? null;
        $companies    = $companyModel->getAllCompanies($userId);

        // หา company_id ของ fiscal_id ที่กำลังใช้งานอยู่
        $active_company_id  = '';
        $active_fiscal_year = '';
        foreach ($companies as $company) {
            if (isset($company['fiscal_years'])) {
                foreach ($company['fiscal_years'] as $fy) {
                    $fy_id = $fy['fiscal_id'] ?? $fy['id'] ?? '';
                    if ($fy_id == $fiscal_id) {
                        $active_company_id  = $company['company_id'] ?? $company['id'] ?? '';
                        $active_fiscal_year = $fy['fiscal_years'] ?? $fy['working_year'] ?? $fy['year'] ?? '';
                        break 2;
                    }
                }
            }
        }

        // 2. ดึงข้อมูลจาก Model (ถ้ามี)
        require_once '../app/models/IssuesModel.php';
        $issuesModel = new IssuesModel();

        $current_user_id = (int) ($this->userPayload['user_id'] ?? 0);
        $is_super_admin  = (int) ($this->userPayload['is_super_admin'] ?? 0);
        $issues          = $issuesModel->getAllIssues($fiscal_id, $current_user_id, $is_super_admin);

        // 3. เตรียมข้อมูลเบื้องต้นสำหรับส่งไปหน้า View
        $data = [
            'title'              => 'Account - ประเด็นคงค้าง',
            'user'               => $this->userPayload,
            'user_id'            => $this->userPayload['user_id'] ?? '',
            'firstname'          => $this->userPayload['user_firstname'] ?? '',
            'lastname'           => $this->userPayload['user_lastname'] ?? '',
            'is_super_admin'     => $this->userPayload['is_super_admin'] ?? '0',
            'companies'          => $companies,
            'fiscal_id'          => $fiscal_id,
            'active_company_id'  => $active_company_id,
            'active_fiscal_year' => $active_fiscal_year,
            'issues'             => $issues,
        ];

        // 4. เรียก View (เดี๋ยวเราต้องไปสร้างไฟล์ app/views/backoffice/issues.php)
        require_once '../app/views/backoffice/issues.php';
    }

    /////////////////////////////////////// manual ///////////////////////////////////////////////

    public function manual()
    {
        $this->checkAuth();

        // 1. ดึงปีบัญชีและบริษัทที่เลือกใน Session (ใช้หลักการเดียวกับหน้าอื่น)
        $fiscal_id = $_SESSION['fiscal_year_id'] ?? null;

        require_once '../app/models/CompanyModel.php';
        $companyModel = new CompanyModel();
        $userId       = $this->userPayload['user_id'] ?? null;
        $companies    = $companyModel->getAllCompanies($userId);

        // หา company_id ของ fiscal_id ที่กำลังใช้งานอยู่
        $active_company_id  = '';
        $active_fiscal_year = '';
        foreach ($companies as $company) {
            if (isset($company['fiscal_years'])) {
                foreach ($company['fiscal_years'] as $fy) {
                    $fy_id = $fy['fiscal_id'] ?? $fy['id'] ?? '';
                    if ($fy_id == $fiscal_id) {
                        $active_company_id  = $company['company_id'] ?? $company['id'] ?? '';
                        $active_fiscal_year = $fy['fiscal_years'] ?? $fy['working_year'] ?? $fy['year'] ?? '';
                        break 2;
                    }
                }
            }
        }

        // 2. ดึงข้อมูลจาก Model (ถ้ามี)
        require_once '../app/models/ManualModel.php';
        $manualModel = new ManualModel();

        $current_user_id = (int) ($this->userPayload['user_id'] ?? 0);
        $is_super_admin  = (int) ($this->userPayload['is_super_admin'] ?? 0);
        $topics          = $manualModel->getAllTopicsWithContents();

        // 3. เตรียมข้อมูลเบื้องต้นสำหรับส่งไปหน้า View
        $data = [
            'title'              => 'Account - ตั้งค่าคู่มือ',
            'user'               => $this->userPayload,
            'user_id'            => $this->userPayload['user_id'] ?? '',
            'firstname'          => $this->userPayload['user_firstname'] ?? '',
            'lastname'           => $this->userPayload['user_lastname'] ?? '',
            'is_super_admin'     => $this->userPayload['is_super_admin'] ?? '0',
            'companies'          => $companies,
            'fiscal_id'          => $fiscal_id,
            'active_company_id'  => $active_company_id,
            'active_fiscal_year' => $active_fiscal_year,
            'topics'             => $topics,
        ];

        // 4. เรียก View (เดี๋ยวเราต้องไปสร้างไฟล์ app/views/backoffice/manual.php)
        require_once '../app/views/backoffice/manual.php';
    }
    public function setting_manual()
    {
        $this->checkAuth();

        // 1. ดึงปีบัญชีและบริษัทที่เลือกใน Session (ใช้หลักการเดียวกับหน้าอื่น)
        $fiscal_id = $_SESSION['fiscal_year_id'] ?? null;

        require_once '../app/models/CompanyModel.php';
        $companyModel = new CompanyModel();
        $userId       = $this->userPayload['user_id'] ?? null;
        $companies    = $companyModel->getAllCompanies($userId);

        // หา company_id ของ fiscal_id ที่กำลังใช้งานอยู่
        $active_company_id  = '';
        $active_fiscal_year = '';
        foreach ($companies as $company) {
            if (isset($company['fiscal_years'])) {
                foreach ($company['fiscal_years'] as $fy) {
                    $fy_id = $fy['fiscal_id'] ?? $fy['id'] ?? '';
                    if ($fy_id == $fiscal_id) {
                        $active_company_id  = $company['company_id'] ?? $company['id'] ?? '';
                        $active_fiscal_year = $fy['fiscal_years'] ?? $fy['working_year'] ?? $fy['year'] ?? '';
                        break 2;
                    }
                }
            }
        }

        // 2. ดึงข้อมูลจาก Model (ถ้ามี)
        require_once '../app/models/ManualModel.php';
        $manualModel = new ManualModel();

        $current_user_id = (int) ($this->userPayload['user_id'] ?? 0);
        $is_super_admin  = (int) ($this->userPayload['is_super_admin'] ?? 0);
        $topics          = $manualModel->getAllTopicsWithContents();

        // 3. เตรียมข้อมูลเบื้องต้นสำหรับส่งไปหน้า View
        $data = [
            'title'              => 'Account - ตั้งค่าคู่มือ',
            'user'               => $this->userPayload,
            'user_id'            => $this->userPayload['user_id'] ?? '',
            'firstname'          => $this->userPayload['user_firstname'] ?? '',
            'lastname'           => $this->userPayload['user_lastname'] ?? '',
            'is_super_admin'     => $this->userPayload['is_super_admin'] ?? '0',
            'companies'          => $companies,
            'fiscal_id'          => $fiscal_id,
            'active_company_id'  => $active_company_id,
            'active_fiscal_year' => $active_fiscal_year,
            'topics'             => $topics,
        ];

        // 4. เรียก View (เดี๋ยวเราต้องไปสร้างไฟล์ app/views/backoffice/manual.php)
        require_once '../app/views/backoffice/manual_form.php';
    }
    public function setting_manual_pages()
    {
        $this->checkAuth();

        // 1. ดึงปีบัญชีและบริษัทที่เลือกใน Session (ใช้หลักการเดียวกับหน้าอื่น)
        $fiscal_id = $_SESSION['fiscal_year_id'] ?? null;

        require_once '../app/models/CompanyModel.php';
        $companyModel = new CompanyModel();
        $userId       = $this->userPayload['user_id'] ?? null;
        $companies    = $companyModel->getAllCompanies($userId);

        // หา company_id ของ fiscal_id ที่กำลังใช้งานอยู่
        $active_company_id  = '';
        $active_fiscal_year = '';
        foreach ($companies as $company) {
            if (isset($company['fiscal_years'])) {
                foreach ($company['fiscal_years'] as $fy) {
                    $fy_id = $fy['fiscal_id'] ?? $fy['id'] ?? '';
                    if ($fy_id == $fiscal_id) {
                        $active_company_id  = $company['company_id'] ?? $company['id'] ?? '';
                        $active_fiscal_year = $fy['fiscal_years'] ?? $fy['working_year'] ?? $fy['year'] ?? '';
                        break 2;
                    }
                }
            }
        }

        // 2. ดึงข้อมูลจาก Model (ถ้ามี)
        require_once '../app/models/ManualModel.php';
        $manualModel = new ManualModel();

        $current_user_id = (int) ($this->userPayload['user_id'] ?? 0);
        $is_super_admin  = (int) ($this->userPayload['is_super_admin'] ?? 0);
        $topics          = $manualModel->getAllTopicsWithContents();

        // 3. เตรียมข้อมูลเบื้องต้นสำหรับส่งไปหน้า View
        $data = [
            'title'              => 'Account - ตั้งค่าคู่มือ',
            'user'               => $this->userPayload,
            'user_id'            => $this->userPayload['user_id'] ?? '',
            'firstname'          => $this->userPayload['user_firstname'] ?? '',
            'lastname'           => $this->userPayload['user_lastname'] ?? '',
            'is_super_admin'     => $this->userPayload['is_super_admin'] ?? '0',
            'companies'          => $companies,
            'fiscal_id'          => $fiscal_id,
            'active_company_id'  => $active_company_id,
            'active_fiscal_year' => $active_fiscal_year,
            'topics'             => $topics,
        ];

        // 4. เรียก View (เดี๋ยวเราต้องไปสร้างไฟล์ app/views/backoffice/manual.php)
        require_once '../app/views/backoffice/manual_setting.php';
    }

    public function save_manual_topic()
    {
        $this->checkAuth();

        header('Content-Type: application/json');

        $name = trim($_POST['topic_name'] ?? '');

        if ($name === '') {
            echo json_encode(['result' => 0, 'msg' => 'กรุณากรอกชื่อ Topic']);
            exit();
        }

        require_once '../app/models/ManualModel.php';
        $manualModel = new ManualModel();

        $newId = $manualModel->insertTopic($name);

        if ($newId) {
            echo json_encode([
                'result'      => 1,
                'id'          => (int) $newId,
                'topics_name' => $name,
            ]);
        } else {
            echo json_encode(['result' => 0, 'msg' => 'ไม่สามารถบันทึกได้']);
        }
        exit();
    }

    public function save_manual_content()
    {
        $this->checkAuth();
        header('Content-Type: application/json');

        $content_id = isset($_POST['content_id']) ? trim($_POST['content_id']) : '';
        $topic_id = isset($_POST['topic_id']) ? (int)$_POST['topic_id'] : 0;
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($title === '' || $description === '') {
            echo json_encode(['result' => 0, 'msg' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
            exit();
        }

        require_once '../app/models/ManualModel.php';
        $manualModel = new ManualModel();

        // Handle Image Upload (Optional)
        $content_image = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $tmp_path = $_FILES['image']['tmp_name'];
            $mime = mime_content_type($tmp_path);
            
            $image = null;
            if ($mime === 'image/jpeg') {
                $image = @imagecreatefromjpeg($tmp_path);
            } elseif ($mime === 'image/png') {
                $image = @imagecreatefrompng($tmp_path);
            } elseif ($mime === 'image/webp') {
                $image = @imagecreatefromwebp($tmp_path);
            }

            if ($image !== false && $image !== null) {
                $webp_tmp = sys_get_temp_dir() . '/' . uniqid('manual_', true) . '.webp';
                
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);
                
                imagewebp($image, $webp_tmp, 80);
                imagedestroy($image);

                require_once '../app/services/AwsS3.php';
                $s3_result = \App\Services\AwsS3::uploadFileByPath($webp_tmp, true, 'manuals');
                if (isset($s3_result['path']) && $s3_result['path']) {
                    $content_image = $s3_result['path'];
                }
                
                @unlink($webp_tmp);
            }
        }

        if ($content_id !== '') {
            $success = $manualModel->updateContent($content_id, $title, $description, $content_image);
        } else {
            $success = $manualModel->insertContent($topic_id, $title, $description, $content_image);
        }

        if ($success) {
            echo json_encode(['result' => 1, 'msg' => 'บันทึกสำเร็จ']);
        } else {
            echo json_encode(['result' => 0, 'msg' => 'ไม่สามารถบันทึกได้']);
        }
        exit();
    }

    public function delete_manual_content()
    {
        $this->checkAuth();
        header('Content-Type: application/json');

        $content_id = isset($_POST['content_id']) ? (int)$_POST['content_id'] : 0;

        if ($content_id === 0) {
            echo json_encode(['result' => 0, 'msg' => 'Invalid ID']);
            exit();
        }

        require_once '../app/models/ManualModel.php';
        $manualModel = new ManualModel();

        if ($manualModel->deleteContent($content_id)) {
            echo json_encode(['result' => 1, 'msg' => 'ลบสำเร็จ']);
        } else {
            echo json_encode(['result' => 0, 'msg' => 'ไม่สามารถลบได้']);
        }
        exit();
    }

    public function update_manual_topic_order()
    {
        $this->checkAuth();
        header('Content-Type: application/json');
        
        $topic_ids = isset($_POST['topic_ids']) ? $_POST['topic_ids'] : [];
        if (!is_array($topic_ids) || empty($topic_ids)) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่มีข้อมูลสำหรับเรียงลำดับ']);
            exit();
        }

        require_once '../app/models/ManualModel.php';
        $manualModel = new ManualModel();
        
        if ($manualModel->updateTopicOrder($topic_ids)) {
            echo json_encode(['result' => 1, 'msg' => 'เรียงลำดับสำเร็จ']);
        } else {
            echo json_encode(['result' => 0, 'msg' => 'ไม่สามารถเรียงลำดับได้']);
        }
        exit();
    }

    public function update_manual_content_order()
    {
        $this->checkAuth();
        header('Content-Type: application/json');
        
        $content_ids = isset($_POST['content_ids']) ? $_POST['content_ids'] : [];
        if (!is_array($content_ids) || empty($content_ids)) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่มีข้อมูลสำหรับเรียงลำดับ']);
            exit();
        }

        require_once '../app/models/ManualModel.php';
        $manualModel = new ManualModel();
        
        if ($manualModel->updateContentOrder($content_ids)) {
            echo json_encode(['result' => 1, 'msg' => 'เรียงลำดับสำเร็จ']);
        } else {
            echo json_encode(['result' => 0, 'msg' => 'ไม่สามารถเรียงลำดับได้']);
        }
        exit();
    }

}
