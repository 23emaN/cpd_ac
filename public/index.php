<?php
// public/index.php - หน้าต่างบานแรกของระบบ (Front Controller)

// 0. โหลด Composer Autoload
require_once dirname(__DIR__) . '/vendor/autoload.php';

// 1. รับค่า url ที่ถูกส่งมาจากไฟล์ .htaccess
$url = isset($_GET['url']) ? $_GET['url'] : 'home';

// 2. ส่งต่อหน้าที่ให้ routes/web.php ไปแยกทางให้ (Router)
require_once '../routes/web.php';

