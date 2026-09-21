<?php
/**
 * Quick production health check - verifies database connectivity and table row counts.
 * Access via: https://bsns.online/shared/health_check.php
 * DELETE THIS FILE after debugging.
 */
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

$results = ['status' => 'ok', 'timestamp' => date('c'), 'php_version' => PHP_VERSION];

// Check if .env can be loaded
$envPath = __DIR__ . '/../.env';
$results['env_exists'] = file_exists($envPath);

// Try database connection
try {
    require_once __DIR__ . '/bootstrap.php';
    require_once __DIR__ . '/config.php';

    $dbHost = $_ENV['DB_HOST'] ?? '127.0.0.1';
    $dbUser = $_ENV['DB_USER'] ?? '';
    $dbPass = $_ENV['DB_PASS'] ?? '';
    $dbName = $_ENV['DB_NAME'] ?? '';

    $results['db_host'] = $dbHost;
    $results['db_name'] = $dbName;
    $results['db_user'] = substr($dbUser, 0, 8) . '***';

    $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    if ($conn->connect_error) {
        $results['status'] = 'db_connection_error';
        $results['error'] = $conn->connect_error;
        echo json_encode($results, JSON_PRETTY_PRINT);
        exit;
    }

    $results['db_connected'] = true;

    // Check key tables and their row counts
    $tables = [
        'admin_accounts',
        'staff_accounts',
        'patient_accounts',
        'patient_profiles',
        'appointments',
        'upcoming_events',
        'activity_log',
        'health_facilities',
        'station_service_assignments',
    ];

    $results['tables'] = [];
    foreach ($tables as $table) {
        $check = $conn->query("SHOW TABLES LIKE '{$table}'");
        if ($check && $check->num_rows > 0) {
            $countResult = $conn->query("SELECT COUNT(*) as cnt FROM `{$table}`");
            $row = $countResult ? $countResult->fetch_assoc() : null;
            $results['tables'][$table] = [
                'exists' => true,
                'rows' => $row ? (int) $row['cnt'] : 0,
            ];
        } else {
            $results['tables'][$table] = ['exists' => false, 'rows' => 0];
        }
    }

    // Check if analytics functions are available
    require_once __DIR__ . '/database.php';
    $results['functions'] = [
        'weekly_chart_data' => function_exists('weekly_chart_data'),
        'report_summary_stats' => function_exists('report_summary_stats'),
        'demographics_breakdown_data' => function_exists('demographics_breakdown_data'),
        'station_performance_data' => function_exists('station_performance_data'),
        'service_performance_data' => function_exists('service_performance_data'),
        'appointment_stats' => function_exists('appointment_stats'),
        'monthly_trends_data' => function_exists('monthly_trends_data'),
    ];

    // Try calling appointment_stats
    try {
        $stats = appointment_stats();
        $results['appointment_stats_result'] = $stats;
    } catch (Throwable $e) {
        $results['appointment_stats_error'] = $e->getMessage();
    }

    // Try calling weekly_chart_data
    try {
        $weekly = weekly_chart_data();
        $results['weekly_chart_result'] = $weekly;
    } catch (Throwable $e) {
        $results['weekly_chart_error'] = $e->getMessage();
    }

    $conn->close();
} catch (Throwable $e) {
    $results['status'] = 'error';
    $results['error'] = $e->getMessage();
    $results['error_file'] = $e->getFile();
    $results['error_line'] = $e->getLine();
}

echo json_encode($results, JSON_PRETTY_PRINT);
