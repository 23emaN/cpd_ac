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
        'registration_board' => ['BackofficeController', 'registration_board'],
        'registration-task/job-types' => ['BackofficeController', 'getRegistrationTypes'],
        'registration-task/settings' => ['BackofficeController', 'getRegistrationTaskSettings'],
        'registration-task/get' => ['BackofficeController', 'getRegistrationTask'],
        'registration-task/history' => ['BackofficeController', 'getRegistrationHistory'],
        'post_it' => ['BackofficeController', 'postIt'],
        'postit' => ['BackofficeController', 'postIt'],
        'dashboard_month' => ['BackofficeController', 'dashboard_month'],
        'fiscal_years/get' => ['MainController', 'getFiscalYears'],
        'logout' => ['MainController', 'logout']
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
        'registration-task/job-types/add'    => ['BackofficeController', 'addRegistrationType'],
        'registration-task/job-types/edit'   => ['BackofficeController', 'editRegistrationType'],
        'registration-task/job-types/delete' => ['BackofficeController', 'deleteRegistrationType'],
        'registration-task/job-types/toggle-status' => ['BackofficeController', 'toggleRegistrationTypeStatus'],
        'registration-task/settings/save' => ['BackofficeController', 'saveRegistrationTaskSettings'],
        'registration-task/add' => ['BackofficeController', 'addRegistrationTask'],
        'registration-task/edit' => ['BackofficeController', 'editRegistrationTask'],
        'registration-task/delete' => ['BackofficeController', 'deleteRegistrationTask'],
        'registration-task/close' => ['BackofficeController', 'closeRegistrationTask'],
        'registration-task/update-status' => ['BackofficeController', 'updateRegistrationTaskStatus'],
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
