<?php
require 'vendor/autoload.php';
require 'app/models/Model.php';
class A extends Model {
    public function q() {
        $stmt = $this->pdo->query('SHOW COLUMNS FROM tbl_customers');
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}
$a = new A();
$a->q();
