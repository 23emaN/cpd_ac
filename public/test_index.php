<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    define('BASE_URL', 'http://localhost');
    require_once '../vendor/autoload.php';
    require_once '../app/config/Connection.php';
    require_once '../app/controllers/BackofficeController.php';

    // Mock checkWebAuth to return a valid user payload
    class MockAuthModel {
        public static function checkWebAuth() {
            return [
                'user_id' => 1,
                'user_firstname' => 'Test',
                'user_lastname' => 'User',
                'is_super_admin' => 0
            ];
        }
    }
    // Trick the autoloader or just override the AuthModel
    // Wait, AuthModel is loaded by require_once inside checkAuth.
    // So let's create a fake AuthModel.php in a temp directory and prepend it to include path?
    // Actually, we can just extend BackofficeController and override checkAuth!

    class TestBackofficeController extends BackofficeController {
        protected function checkAuth() {
            $reflection = new ReflectionClass($this);
            $property = $reflection->getProperty('userPayload');
            $property->setAccessible(true);
            $property->setValue($this, [
                'user_id' => 1,
                'user_firstname' => 'Test',
                'user_lastname' => 'User',
                'is_super_admin' => 0
            ]);
        }
    }

    session_start();
    $_SESSION['fiscal_year_id'] = 1;
    $_GET['month'] = '09';

    $c = new TestBackofficeController();
    
    ob_start();
    $c->index();
    $output = ob_get_clean();
    
    echo "Successfully rendered index method! Output length: " . strlen($output) . "\n";
} catch (Throwable $e) {
    echo "Fatal Error/Exception: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine() . "\n";
}
