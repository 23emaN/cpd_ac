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

        // 4. ดึงหน้า View มาแสดงผล
        require_once '../app/views/backoffice/tasks.php';
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
