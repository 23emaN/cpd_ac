<?php
require 'app/config/config.php';
require 'app/models/Model.php';
require 'app/models/monthly_task_Modal.php';

$model = new MonthlyTaskModal();
// We need a valid fiscal_id. Let's get the active one.
$stmt = $model->pdo->query("SELECT fiscal_id FROM tbl_fiscal_year WHERE status = '1' LIMIT 1");
$active = $stmt->fetch(PDO::FETCH_ASSOC);
$fiscalId = $active['fiscal_id'];

$tasks = $model->getMonthlyTasks($fiscalId);
foreach ($tasks as $t) {
    if ($t['review1_status'] == '1' || $t['review2_status'] == '1' || $t['review3_status'] == '1') {
        print_r([
            'customer_name' => $t['customer_name'],
            'month' => $t['period_month'],
            'r1_status' => $t['review1_status'],
            'r1_user_id' => $t['review1_user_id'],
            'r1_firstname' => $t['r1_firstname'],
        ]);
        break;
    }
}
