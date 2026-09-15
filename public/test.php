<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once '../app/config/Connection.php';
    require_once '../app/models/CustomerModal.php';
    $m = new CustomModal();
    var_dump($m->getCustomersgid(1));
    echo "OK\n";
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
} catch (Error $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
