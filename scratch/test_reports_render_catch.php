<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

// Exact catch block variables from lines 787-824 of Admin/index.php:
$page = 'reports';
$reportPeriod = 'annually';
$reportFrom = date('Y-01-01');
$reportTo = date('Y-12-31');
$reportGender = '';
$reportAgeGroup = '';
$reportStation = '';
$reportService = '';
$reportStatus = '';
$reportFilters = ['report_period' => 'annually', 'report_from' => $reportFrom, 'report_to' => $reportTo, 'gender' => '', 'age_group' => '', 'station_slug' => '', 'service_slug' => '', 'status' => ''];
$reportStats = ['total_patients' => 0, 'services_rendered' => 0, 'total_bookings' => 0, 'completed_count' => 0, 'cancelled_count' => 0, 'confirmed_count' => 0, 'pending_count' => 0, 'serving_count' => 0, 'avg_daily' => 0.0, 'utilization_pct' => 0, 'cancellation_pct' => 0, 'day_count' => 1];
$monthlyTrends = ['months' => ['Jan', 'Feb', 'Mar'], 'appointments' => [0, 0, 0], 'patients' => [0, 0, 0]];
$demographics = ['total' => 0, 'gender' => ['female' => ['count' => 0, 'pct' => 0], 'male' => ['count' => 0, 'pct' => 0], 'other' => ['count' => 0, 'pct' => 0]], 'age_groups' => []];
$stationPerformance = [];
$servicePerformance = [];
$barangayCompletedStats = [];
$reportAppointmentsList = [];
$infoChangeLog = [];
$activityLog = [];
$healthEventsSummary = [];

require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';
$stations = station_catalog();
$stationLookup = [];
foreach ($stations as $station) {
    $stationLookup[$station['slug']] = $station;
}
$serviceCatalog = service_catalog();

function admin_icon($name) { return ''; }
function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

// Now extract and execute ONLY the reports view block (lines 4229 to 5176)
$lines = file(__DIR__ . '/../Admin/index.php');
$viewCode = implode('', array_slice($lines, 4229, 5176 - 4229));

ob_start();
try {
    eval('?>' . $viewCode);
    $out = ob_get_clean();
    echo "SUCCESS! Rendered length: " . strlen($out) . PHP_EOL;
} catch (Throwable $e) {
    ob_end_clean();
    echo "FATAL IN REPORTS VIEW: " . $e->getMessage() . " at line " . $e->getLine() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
