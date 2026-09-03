<?php
// app/controllers/PostItController.php

class PostItController
{
    private $userPayload = null;

    private function checkAuth()
    {
        require_once '../app/models/AuthModel.php';
        $user = \App\Models\AuthModel::checkWebAuth();

        if (!$user) {
            header('Location: ' . BASE_URL . '/login');
            exit();
        }

        $this->userPayload = $user;
    }

    private function requireFiscalContext(): array
    {
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

        return [
            'fiscal_id' => $fiscal_id,
            'companies' => $companies,
            'active_company_id' => $active_company_id,
        ];
    }

    /**
     * ข้อมูลตัวอย่างสำหรับดราฟ UI (ใช้เมื่อยังไม่มีข้อมูลใน DB)
     */
    private function getDraftSample(): array
    {
        return [
            'stats' => [
                'total' => 1,
                'pending' => 1,
                'done' => 0,
                'overdue' => 0,
            ],
            'items' => [
                [
                    'post_id' => 0,
                    'title' => 'เบิกของ',
                    'content' => 'อย่าลืมเบิกแม็ก',
                    'assignee_name' => 'มนม',
                    'assignee_username' => 'มนม',
                    'user_id' => 0,
                    'status' => '0',
                    'color_code' => 'yellow',
                    'due_date' => null,
                    'created_at' => '2026-06-19 10:00:00',
                    '_is_draft' => true,
                ],
            ],
            'assignees' => [
                ['user_id' => 0, 'full_name' => 'มนม', 'user_name' => 'มนม'],
            ],
            'is_draft' => true,
        ];
    }

    public function index()
    {
        $this->checkAuth();
        $ctx = $this->requireFiscalContext();
        $fiscalId = (int) $ctx['fiscal_id'];

        require_once '../app/models/PostItModel.php';
        $model = new PostItModel();

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
            $isDraft = empty($items) && empty($filters['q']) && $filters['user_id'] === '' && $filters['status'] === '';
        } catch (Throwable $e) {
            $stats = ['total' => 0, 'pending' => 0, 'done' => 0, 'overdue' => 0];
            $items = [];
            $assignees = [];
            $isDraft = true;
        }

        if ($isDraft) {
            $sample = $this->getDraftSample();
            $stats = $sample['stats'];
            $items = $sample['items'];
            $assignees = $sample['assignees'];
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
            'fiscal_id' => $ctx['fiscal_id'],
            'companies' => $ctx['companies'],
            'active_company_id' => $ctx['active_company_id'],
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
            'is_draft' => $isDraft,
        ];

        require_once '../app/views/post_it/index.php';
    }
}
