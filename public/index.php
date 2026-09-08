<?php
// PHP ใช้ UTC เป็นค่าเริ่มต้นเสมอถ้าไม่ได้ตั้งไว้ ในขณะที่ MySQL ของเซิร์ฟเวอร์นี้
// อยู่โซนเวลาไทย (+07:00) — ทุกคอลัมน์ที่ตั้งค่าด้วย DEFAULT CURRENT_TIMESTAMP/NOW()
// ทั่วทั้งแอปจึงเป็นเวลาไทยอยู่แล้ว ต้องตั้ง PHP ให้ตรงกัน ไม่งั้นทุกจุดที่เทียบ
// time()/date() ฝั่ง PHP กับค่าจากฐานข้อมูล (เช่น เช็กลิงก์แชร์หมดอายุ/ถูกล็อก)
// จะเพี้ยนไป 7 ชั่วโมง
date_default_timezone_set('Asia/Bangkok');

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
