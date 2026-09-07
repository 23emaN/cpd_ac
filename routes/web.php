<?php

$requestMethod = $_SERVER['REQUEST_METHOD'];

$routes = [
    'GET' => [
        'login'  => ['AuthController', 'showLogin'],
        'main'   => ['MainController', 'index'],
        'backoffice' => ['BackofficeController', 'index'],
        'tasks' => ['BackofficeController', 'tasks'],
        'customer' => ['BackofficeController', 'customer'],
        'employee' => ['BackofficeController', 'employee'],
        'register_board' => ['BackofficeController', 'register_board'],
        'post_it' => ['BackofficeController', 'postIt'],
        'postit' => ['BackofficeController', 'postIt'],
        'closing' => ['BackofficeController', 'closing'],
        'yearly_dash' => ['BackofficeController', 'yearly_dash'],
        'monthly_dash' => ['BackofficeController', 'monthly_dash'],
        'customer_message' => ['BackofficeController', 'customer_message'],
        'monthly_task' => ['BackofficeController', 'monthly_task'],
        'monthly_task/items' => ['BackofficeController', 'getMonthlyTaskItems'],
        'fiscal_years/get' => ['MainController', 'getFiscalYears'],
        'logout' => ['MainController', 'logout'],
        'customer/get' => ['BackofficeController', 'getCustomer']
    ],
    'POST' => [
        'auth/login'  => ['AuthController', 'processLogin'],
        'company/add' => ['MainController', 'addCompany'],
        'fiscal_years/add' => ['MainController', 'addFiscalYear'],
        'fiscal_years/set_context' => ['MainController', 'setContext'],
        'task/add_task' => ['BackofficeController', 'addTask'],
        'task/move_task' => ['BackofficeController', 'moveTask'],
        'task/get_task' => ['BackofficeController', 'getTask'],
        'task/edit_task' => ['BackofficeController', 'editTask'],
        'task/delete_task' => ['BackofficeController', 'deleteTask'],
        'employee/add' => ['BackofficeController', 'addEmployee'],
        'employee/edit' => ['BackofficeController', 'editEmployee'],
        'employee/delete' => ['BackofficeController', 'deleteEmployee'],
        'post_it/store' => ['BackofficeController', 'storePostIt'],
        'post_it/update' => ['BackofficeController', 'updatePostIt'],
        'post_it/toggle' => ['BackofficeController', 'togglePostItStatus'],
        'post_it/delete' => ['BackofficeController', 'deletePostIt'],
        'customer/add' => ['BackofficeController', 'addCustomer'],
        'customer/edit' => ['BackofficeController', 'editCustomer'],
        'customer/delete' => ['BackofficeController', 'deleteCustomer'],
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

