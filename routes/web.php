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
        'closing' => ['BackofficeController', 'closing'],
        'yearly_dash' => ['BackofficeController', 'yearly_dash'],
        'monthly_dash' => ['BackofficeController', 'monthly_dash'],
        'customer_message' => ['BackofficeController', 'customer_message'],
        'monthly_task' => ['BackofficeController', 'monthly_task'],
        'monthly_task/items' => ['BackofficeController', 'getMonthlyTaskItems'],
        'monthly_task/comments' => ['BackofficeController', 'getMonthlyTaskComments'],
        'fiscal_years/get' => ['MainController', 'getFiscalYears'],
        'logout' => ['MainController', 'logout'],
        'customer/get' => ['BackofficeController', 'getCustomer'],
        'customer_drive' => ['CustomerDriveController', 'index'],
        'customer_drive/download' => ['CustomerDriveController', 'download'],
        'customer_drive/download_zip' => ['CustomerDriveController', 'downloadZip'],
        'portal' => ['PortalController', 'index'],
        'portal/drive' => ['PortalController', 'drive'],
        'portal/download' => ['PortalController', 'download'],
        'portal/logout' => ['PortalController', 'logout']
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
        'monthly_task/comments/store' => ['BackofficeController', 'storeMonthlyTaskComment'],
        'monthly_task/update' => ['BackofficeController', 'updateMonthlyTask'],
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
        'customer_drive/load_page' => ['CustomerDriveController', 'loadPage'],
        'customer_drive/render_table' => ['CustomerDriveController', 'renderTable'],
        'customer_drive/mkdir' => ['CustomerDriveController', 'mkdir'],
        'customer_drive/upload' => ['CustomerDriveController', 'upload'],
        'customer_drive/delete' => ['CustomerDriveController', 'delete'],
        'customer_drive/move' => ['CustomerDriveController', 'move'],
        'customer_drive/rename' => ['CustomerDriveController', 'rename'],
        'customer_drive/folder_tree' => ['CustomerDriveController', 'folderTree'],
        'customer_drive/render_trash' => ['CustomerDriveController', 'renderTrash'],
        'customer_drive/restore_trash' => ['CustomerDriveController', 'restoreTrash'],
        'customer_drive/trash_purge' => ['CustomerDriveController', 'trashPurge'],
        'customer_drive/versions' => ['CustomerDriveController', 'versions'],
        'customer_drive/restore_version' => ['CustomerDriveController', 'restoreVersion'],
        'customer_drive/activity' => ['CustomerDriveController', 'activity'],
        'customer_drive/link_list' => ['CustomerDriveController', 'linkList'],
        'customer_drive/link_create' => ['CustomerDriveController', 'linkCreate'],
        'customer_drive/link_revoke' => ['CustomerDriveController', 'linkRevoke'],
        'customer_drive/link_reset_password' => ['CustomerDriveController', 'linkResetPassword'],
        'portal/auth' => ['PortalController', 'authenticate'],
        'portal/list' => ['PortalController', 'list'],
        'portal/remove_own' => ['PortalController', 'removeOwn'],
        'portal/upload' => ['PortalController', 'upload'],
        'portal/upload_chunk' => ['PortalController', 'uploadChunk'],
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