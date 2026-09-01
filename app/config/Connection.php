<?php
namespace App\Config;

use PDO;
use PDOException;
use Dotenv\Dotenv;

class Connection
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        // แก้ไข Path ให้ถอยไป 3 ระดับเพื่อไปให้ถึงโฟลเดอร์นอกสุด (cpd_ac)
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../../');
        $dotenv->safeLoad();

        // แก้ชื่อตัวแปรให้ตรงกับในไฟล์ .env
        $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $db = $_ENV['DB_DATABASE'] ?? 'cpd_ac';
        $user = $_ENV['DB_USERNAME'] ?? 'root';
        $pass = $_ENV['DB_PASSWORD'] ?? '';
        $port = $_ENV['DB_PORT'] ?? '3306';
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset;port=$port";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int) $e->getCode());
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getPdo()
    {
        return $this->pdo;
    }
}
