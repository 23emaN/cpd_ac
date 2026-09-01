<?php
// app/core/Model.php

class Model {
    protected $pdo;

    public function __construct() {
        require_once dirname(__DIR__) . '/config/Connection.php';
        $this->pdo = \App\Config\Connection::getInstance()->getPdo();
    }
}
