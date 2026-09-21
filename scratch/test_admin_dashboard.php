<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SERVER_NAME'] = 'bsns.online';
$_GET['page'] = 'dashboard';
$_SESSION['admin_authenticated'] = true;
$_SESSION['admin_email'] = 'admintest@gmail.com';
$_SESSION['admin_name'] = 'admin';

ob_start();
try {
    require __DIR__ . '/../Admin/index.php';
    $output = ob_get_clean();
    echo "SUCCESS: Admin dashboard rendered, output length: " . strlen($output) . "\n";
} catch (Throwable $e) {
    ob_end_clean();
    echo "ERROR: " . get_class($e) . ": " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}
