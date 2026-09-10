<?php

class ReportController
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

    /**
     * ดึงข้อมูลลูกค้าจาก Model แล้วส่งต่อให้ CustomerReport ทำการออกไฟล์ Excel
     */
    public function customerExcel()
    {
        $this->checkAuth();

        $fiscal_id = $_SESSION['fiscal_year_id'] ?? null;
        if (!$fiscal_id) {
            header("Location: " . BASE_URL . "/customer");
            exit();
        }

        // 1. รับค่าตัวกรองจาก Request (GET)
        $filters = [
            'status'  => $_GET['status'] ?? '',
            'user_id' => $_GET['user_id'] ?? '',
            'keyword' => trim($_GET['keyword'] ?? ''),
        ];

        // 2. เรียก Model เพื่อ Query ดึงข้อมูลลูกค้า
        require_once '../app/models/CustomerModal.php';
        $customModal = new CustomModal();
        $customers = $customModal->getCustomersByFiscalId($fiscal_id, $filters);

        // 3. ส่งข้อมูล $customers ที่ได้ ไปให้ CustomerReport เพื่อประกอบร่างเป็นไฟล์ Excel
        require_once '../app/reports/CustomerReport.php';
        $customerReport = new CustomerReport();
        $customerReport->export($customers);
    }
}
