<?php
// app/models/customer_drive/cd_db.php

function cd_pdo(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        require_once dirname(__DIR__, 2) . '/config/Connection.php';
        $pdo = \App\Config\Connection::getInstance()->getPdo();
    }

    return $pdo;
}

function cd_query(string $sql, array $params = []): PDOStatement
{
    $stmt = cd_pdo()->prepare($sql);
    $stmt->execute($params);

    return $stmt;
}
