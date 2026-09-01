<?php
// app/core/Model.php

class Model {
    protected $pdo;

    public function __construct() {
        require_once dirname(__DIR__) . '/core/Database/Connection.php';
        $this->pdo = \App\Database\Connection::getInstance()->getPdo();
    }
}
