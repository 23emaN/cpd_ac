<?php
// routes/web.php - รับหน้าที่ตรวจสอบ URL ว่าจะให้ไปที่ Controller ไหน

// ตัวแปร $url ถูกส่งมาจาก public/index.php
if ($url === 'login') {
    // ถ้าคนเข้าลิงก์ /login ให้เรียกใช้ AuthController
    require_once '../app/controllers/AuthController.php';
    $controller = new AuthController();
    $controller->showLogin();
} 
else if ($url === 'auth/login') {
    // ถ้า Ajax ส่งข้อมูล Login มาที่นี่
    require_once '../app/controllers/AuthController.php';
    $controller = new AuthController();
    $controller->processLogin();
}
else {
    // ถ้าเข้าหน้าแรกปกติ หรือพิมพ์ URL ผิด ให้เด้งไปหน้า Login อัตโนมัติ
    header("Location: /new_am/public/login");
    exit();
}
