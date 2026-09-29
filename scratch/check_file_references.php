<?php
require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';

$db = db();
$files = glob(__DIR__ . '/../Patients/uploads/*.jpg');

echo "Searching database for references to upload files...\n";
foreach ($files as $f) {
    $base = basename($f);
    $found = false;
    // Check appointments
    $res = $db->query("SELECT id, patient_id, appointment_code, service_name, status, preferred_date, recipient_first_name FROM appointments WHERE photo_path LIKE '%$base%'");
    if ($res && $res->num_rows > 0) {
        $found = true;
        while ($r = $res->fetch_assoc()) {
            echo "FILE $base -> APPOINTMENTS #{$r['id']} (code {$r['appointment_code']}, pat {$r['patient_id']}, recipient {$r['recipient_first_name']}, date {$r['preferred_date']})\n";
        }
    }
    // Check patient_profiles
    $res2 = $db->query("SELECT patient_id, first_name, last_name FROM patient_profiles WHERE photo_path LIKE '%$base%'");
    if ($res2 && $res2->num_rows > 0) {
        $found = true;
        while ($r = $res2->fetch_assoc()) {
            echo "FILE $base -> PATIENT_PROFILES ({$r['patient_id']} {$r['first_name']} {$r['last_name']})\n";
        }
    }
    if (!$found) {
        echo "FILE $base -> NOT FOUND IN DB\n";
    }
}
