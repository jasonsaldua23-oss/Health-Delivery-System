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

ob_start();
try {
    include __DIR__ . '/../Admin/index.php';
    $output = ob_get_clean();
    echo "SUCCESS! Output length: " . strlen($output) . PHP_EOL;
    echo "Has 'Reports & Health Analytics': " . (str_contains($output, 'Reports &amp; Health Analytics') ? 'YES' : 'NO') . PHP_EOL;
} catch (Throwable $e) {
    ob_end_clean();
    echo "CAUGHT EXCEPTION: " . get_class($e) . ": " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
