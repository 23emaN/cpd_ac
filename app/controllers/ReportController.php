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

    public function customerDashboardExcel()
    {
        $this->checkAuth();

        $fiscalId = $_SESSION['fiscal_year_id'] ?? null;
        if (!$fiscalId) {
            header('Location: ' . BASE_URL . '/customer_dash');
            exit();
        }

        require_once '../app/models/CustomerDashModel.php';
        require_once '../app/reports/CustomerDashReport.php';
        require_once '../app/models/CompanyModel.php';

        $model = new CustomerDashModel();
        $companies = (new CompanyModel())->getAllCompanies($this->userPayload['user_id'] ?? null);
        $companyName = 'ไม่ระบุบริษัท';
        $fiscalYear = 'ไม่ระบุปี';
        foreach ($companies as $company) {
            foreach (($company['fiscal_years'] ?? []) as $fiscal) {
                $currentFiscalId = $fiscal['fiscal_id'] ?? $fiscal['id'] ?? null;
                if ((string) $currentFiscalId === (string) $fiscalId) {
                    $companyName = $company['company_name'] ?? $company['name'] ?? $companyName;
                    $fiscalYear = $fiscal['fiscal_years'] ?? $fiscal['working_year'] ?? $fiscal['year'] ?? $fiscalYear;
                    break 2;
                }
            }
        }

        (new CustomerDashReport())->export(
            $model->getCustomerWorkDashboard($fiscalId),
            $fiscalYear,
            $companyName
        );
    }

    public function monthlyTaskExcel()
    {
        $this->checkAuth();

        $fiscalId = $_SESSION['fiscal_year_id'] ?? null;
        if (!$fiscalId) {
            header('Location: ' . BASE_URL . '/monthly_task');
            exit();
        }

        $customerId = ctype_digit((string) ($_GET['customer_id'] ?? ''))
            ? (int) $_GET['customer_id']
            : 0;
        $month = ctype_digit((string) ($_GET['month'] ?? ''))
            ? (int) $_GET['month']
            : 0;
        $exportMode = $_GET['export_mode'] ?? '';

        if ($exportMode === 'customer_year') {
            $month = 0;
        } elseif ($exportMode === 'monthly') {
            $customerId = 0;
        } else {
            header('Location: ' . BASE_URL . '/monthly_task');
            exit();
        }

        if (($exportMode === 'customer_year' && $customerId === 0)
            || ($exportMode === 'monthly' && ($month < 1 || $month > 12))) {
            header('Location: ' . BASE_URL . '/monthly_task');
            exit();
        }

        $filters = [
            'caretaker_id' => ctype_digit((string) ($_GET['caretaker_id'] ?? '')) ? (int) $_GET['caretaker_id'] : null,
            'doc_status' => $_GET['doc_status'] ?? '',
            'task_status' => $_GET['task_status'] ?? '',
            'tax_status' => $_GET['tax_status'] ?? '',
            'payment_status' => $_GET['payment_status'] ?? '',
            'keyword' => trim($_GET['keyword'] ?? ''),
        ];

        require_once '../app/models/monthly_task_Modal.php';
        require_once '../app/reports/MonthlyTaskReport.php';
        $model = new MonthlyTaskModal();

        require_once '../app/models/CompanyModel.php';
        $companyModel = new CompanyModel();
        $companies = $companyModel->getAllCompanies($this->userPayload['user_id'] ?? null);
        $companyName = 'ไม่ระบุบริษัท';
        $fiscalYear = 'ไม่ระบุปี';
        foreach ($companies as $company) {
            foreach (($company['fiscal_years'] ?? []) as $fiscal) {
                $currentFiscalId = $fiscal['fiscal_id'] ?? $fiscal['id'] ?? null;
                if ((string) $currentFiscalId === (string) $fiscalId) {
                    $companyName = $company['company_name'] ?? 'ไม่ระบุบริษัท';
                    $fiscalYear = $fiscal['fiscal_years'] ?? $fiscal['working_year'] ?? $fiscal['year'] ?? 'ไม่ระบุปี';
                    break 2;
                }
            }
        }

        $customerName = '';
        if ($customerId > 0) {
            $customerList = $model->getMonthlyTaskCustomers($fiscalId);
            foreach ($customerList as $customer) {
                if ((int) $customer['customer_id'] === $customerId) {
                    $customerName = $customer['customer_name'];
                    break;
                }
            }
        }

        $tasks = $model->getMonthlyTasks(
            $fiscalId,
            $month > 0 ? $month : null,
            $this->userPayload['user_id'] ?? null,
            $customerId > 0 ? $customerId : null,
            $filters['caretaker_id'],
            $filters['doc_status'],
            $filters['task_status'],
            $filters['tax_status'],
            $filters['payment_status'],
            $filters['keyword']
        );

        $report = new MonthlyTaskReport();
        if ($exportMode === 'customer_year') {
            $report->exportCustomerYear($tasks, $filters, $customerId, $customerName, $fiscalYear, $companyName);
        }

        $report->exportMonthly($tasks, $filters, $month, $fiscalYear, $companyName);
    }
}
