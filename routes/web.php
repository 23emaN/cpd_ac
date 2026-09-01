<?php

// ตรวจสอบ HTTP Method ปัจจุบัน (GET หรือ POST)
$requestMethod = $_SERVER['REQUEST_METHOD'];

// แยก Route ตาม Method
$routes = [
    'GET' => [
        'login'  => ['AuthController', 'showLogin'],
        'main'   => ['MainController', 'index'],
        'logout' => ['MainController', 'logout']
    ],
    'POST' => [
        'auth/login'  => ['AuthController', 'processLogin'],
        'company/add' => ['MainController', 'addCompany']
    ]
];

if (isset($routes[$requestMethod]) && array_key_exists($url, $routes[$requestMethod])) {
    $controllerName = $routes[$requestMethod][$url][0];
    $methodName = $routes[$requestMethod][$url][1];
    
    require_once "../app/controllers/{$controllerName}.php";
    $controller = new $controllerName();
    $controller->$methodName();
} else {
    // ถ้าไม่พบ Route, พิมพ์ URL ผิด หรือส่งมาผิด Method ให้เด้งไปหน้า Login อัตโนมัติ
    header("Location: " . BASE_URL . "/login");
    exit();
}
