<?php
require_once 'app/config/config.php';
require_once 'app/models/monthly_task_Modal.php';
$m = new MonthlyTaskModal();
try {
    $res = $m->getTaxOptions();
    print_r($res);
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
