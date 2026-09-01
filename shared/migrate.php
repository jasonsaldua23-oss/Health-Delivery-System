<?php

declare(strict_types=1);

/**
 * Health Delivery System - Database Migration Runner
 * 
 * Usage via CLI:
 *   php shared/migrate.php
 * 
 * Usage via Web:
 *   Available in development mode or when authenticated as admin.
 */

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/database.php';

$isCli = (php_sapi_name() === 'cli' || defined('STDIN'));

if (!$isCli) {
    if (APP_ENV === 'production' && !is_admin_authenticated()) {
        http_response_code(403);
        echo '<h1>403 Forbidden</h1><p>Database migrations can only be triggered via CLI in production.</p>';
        exit;
    }
    header('Content-Type: text/plain; charset=UTF-8');
}

echo "=======================================================\n";
echo " Health Delivery System - Database Migration Runner\n";
echo " Environment: " . APP_ENV . "\n";
echo " Database:    " . DB_NAME . " on " . DB_HOST . ":" . DB_PORT . "\n";
echo " Timestamp:   " . date('Y-m-d H:i:s') . "\n";
echo "=======================================================\n\n";

try {
    echo "[1/3] Checking MySQL Server Connection...\n";
    $bootstrap = new mysqli(DB_HOST, DB_USER, DB_PASS, '', DB_PORT);
    $bootstrap->set_charset('utf8mb4');
    $bootstrap->query('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $bootstrap->close();
    echo "      -> Database `" . DB_NAME . "` verified.\n\n";

    echo "[2/3] Connecting to Target Database...\n";
    $connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    $connection->set_charset('utf8mb4');
    echo "      -> Connected successfully.\n\n";

    echo "[3/3] Running Schema Migrations, Indexes, and Seeders...\n";
    $logs = run_database_migrations($connection, true);

    foreach ($logs as $item) {
        echo "      + " . $item . "\n";
    }

    echo "\n=======================================================\n";
    echo " Migration Completed Successfully!\n";
    echo "=======================================================\n";
    exit(0);
} catch (Throwable $e) {
    echo "\n[ERROR] Migration Failed: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
