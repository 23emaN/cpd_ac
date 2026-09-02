<?php
session_start();

// 0. โหลด Composer Autoload
require_once dirname(__DIR__) . '/vendor/autoload.php';

// 1. กำหนดค่า BASE_URL แบบไดนามิก เพื่อให้เรียกใช้ได้ทั้งโปรเจค
$baseUrl = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if ($baseUrl === '/') $baseUrl = '';
define('BASE_URL', $baseUrl);

// 2. รับค่า url ที่ถูกส่งมาจากไฟล์ .htaccess
$url = isset($_GET['url']) ? $_GET['url'] : 'home';

// 2. ส่งต่อหน้าที่ให้ routes/web.php ไปแยกทางให้ (Router)
require_once '../routes/web.php';

