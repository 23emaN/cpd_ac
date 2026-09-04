<?php

$requestMethod = $_SERVER['REQUEST_METHOD'];

$routes = [
    'GET' => [
        'login'  => ['AuthController', 'showLogin'],
        'main'   => ['MainController', 'index'],
        'backoffice' => ['BackofficeController', 'index'],
        'tasks' => ['BackofficeController', 'tasks'],
        'post_it' => ['BackofficeController', 'postIt'],
        'postit' => ['BackofficeController', 'postIt'],
        'fiscal_years/get' => ['MainController', 'getFiscalYears'],
        'logout' => ['MainController', 'logout']
    ],
    'POST' => [
        'auth/login'  => ['AuthController', 'processLogin'],
        'company/add' => ['MainController', 'addCompany'],
        'fiscal_years/add' => ['MainController', 'addFiscalYear'],
        'fiscal_years/set_context' => ['MainController', 'setContext'],
        'post_it/store' => ['BackofficeController', 'storePostIt'],
    ]
];

if (isset($routes[$requestMethod]) && array_key_exists($url, $routes[$requestMethod])) {
    $controllerName = $routes[$requestMethod][$url][0];
    $methodName = $routes[$requestMethod][$url][1];
    
    require_once "../app/controllers/{$controllerName}.php";
    $controller = new $controllerName();
    $controller->$methodName();
} else {
    header("Location: " . BASE_URL . "/login");
    exit();
}

