<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SERVER_NAME'] = 'localhost';
$_GET['page'] = 'reports';

session_start();
$_SESSION['admin_authenticated'] = true;
$_SESSION['admin_email'] = 'admintest@gmail.com';
$_SESSION['admin_name'] = 'Admin User';
$_SESSION['csrf_token'] = 'test';

// Simulate that the catch block ran!
// We can define a global or trigger the exception
$GLOBALS['force_catch'] = true;

ob_start();
try {
    // Modify database or call script
    include __DIR__ . '/../Admin/index.php';
    $output = ob_get_clean();
    echo "Output length: " . strlen($output) . PHP_EOL;
} catch (Throwable $e) {
    ob_end_clean();
    echo "FATAL IN VIEW: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
