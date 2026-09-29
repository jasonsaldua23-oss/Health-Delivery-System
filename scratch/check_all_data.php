<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';

$db = db();

echo "=== ALL APPOINTMENTS ===\n";
$res = $db->query("SELECT id, appointment_code, patient_id, first_name, last_name, service_name, recipient_first_name, recipient_middle_name, recipient_last_name, photo_path, status, preferred_date, created_at FROM appointments");
while ($r = $res->fetch_assoc()) {
    echo json_encode($r) . "\n";
}

try {
    echo "\n=== ALL PATIENTS ===\n";
    $res = $db->query("SELECT * FROM patient_profiles LIMIT 10");
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            echo json_encode($r) . "\n";
        }
    } else {
        echo "Query failed: " . $db->error . "\n";
    }

    echo "\n=== ALL INFANT PROFILES ===\n";
    $res = $db->query("SELECT * FROM infant_profiles");
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            echo json_encode($r) . "\n";
        }
    }

    echo "\n=== ALL IMMUNIZED INFANTS ===\n";
    $res = $db->query("SELECT * FROM immunized_infants");
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            echo json_encode($r) . "\n";
        }
    }

    echo "\n=== ALL FILES IN Patients/uploads/ ===\n";
    $files = glob(__DIR__ . '/../Patients/uploads/*');
    foreach ($files as $f) {
        echo basename($f) . " (" . filesize($f) . " bytes)\n";
    }

    echo "\n=== ALL FILES IN assets/images/ ===\n";
    $files = glob(__DIR__ . '/../assets/images/*');
    foreach ($files as $f) {
        if (!is_dir($f)) {
            echo basename($f) . " (" . filesize($f) . " bytes)\n";
        }
    }
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString() . "\n";
}

