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

    public function moveTask() {
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

    public function getTask() {
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

    public function editTask() {
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

    public function deleteTask() {
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

      /////////////////////////////////////// employee /////////////////////////////////////////////// 

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
        require_once '../app/views/backoffice/customer.php';
    }

    public function register_board()
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
        require_once '../app/views/backoffice/register_board.php';
    }

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
                echo json_encode(['result' => 1, 'msg' => 'บันทึก Post-it เรียบร้อยแล้ว', 'post_id' => $postId]);
            } else {
                echo json_encode(['result' => 0, 'msg' => 'ไม่สามารถบันทึกข้อมูลได้']);
            }
        } catch (Throwable $e) {
            echo json_encode(['result' => 0, 'msg' => 'เกิดข้อผิดพลาดของฐานข้อมูล: ' . $e->getMessage()]);
        }
    }
}
