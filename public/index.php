<?php
session_start();

// 0. โหลด Composer Autoload
require_once dirname(__DIR__) . '/vendor/autoload.php';

// 1. กำหนดค่า BASE_URL แบบไดนามิก เพื่อให้เรียกใช้ได้ทั้งโปรเจค
$baseUrl = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if ($baseUrl === '/') $baseUrl = '';
define('BASE_URL', $baseUrl);

// 2. อ่านเส้นทาง URL จาก REQUEST_URI โดยตรง (ไม่พึ่งพา .htaccess ซึ่ง nginx/Herd ไม่รองรับ)
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$url = trim(substr($requestPath, strlen(BASE_URL)), '/');
if ($url === '') $url = 'home';

// 2. ส่งต่อหน้าที่ให้ routes/web.php ไปแยกทางให้ (Router)
require_once '../routes/web.php';

