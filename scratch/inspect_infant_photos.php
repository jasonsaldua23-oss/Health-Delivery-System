<?php
declare(strict_types=1);

require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';

$db = db();

echo "=== ALL APPOINTMENTS WITH PHOTO_PATH ===\n";
$res = $db->query("SELECT id, appointment_code, patient_id, first_name, last_name, service_name, recipient_first_name, recipient_last_name, photo_path, status, preferred_date, created_at FROM appointments WHERE photo_path IS NOT NULL AND photo_path != ''");
if ($res) {
    while ($r = $res->fetch_assoc()) {
        echo json_encode($r) . "\n";
    }
}

echo "\n=== ALL INFANT_PROFILES ===\n";
$res = $db->query("SHOW TABLES LIKE 'infant_profiles'");
if ($res && $res->num_rows > 0) {
    $res2 = $db->query("SELECT * FROM infant_profiles");
    while ($r = $res2->fetch_assoc()) {
        echo json_encode($r) . "\n";
    }
}

echo "\n=== ALL PATIENTS WITH INFANT SUB-PROFILES ===\n";
$patients = fetch_patients();
foreach ($patients as $p) {
    $hasInf = patient_has_infant_bookings((string) $p['patient_id']);
    if ($hasInf) {
        $infs = fetch_infant_sub_profiles_by_patient_id((string) $p['patient_id']);
        echo "Patient: {$p['first_name']} {$p['last_name']} (ID: {$p['patient_id']})\n";
        foreach ($infs as $inf) {
            echo "  Infant: {$inf['full_name']} | photo_path: '{$inf['photo_path']}' | latest_photo: '{$inf['latest_photo']}'\n";
            foreach ($inf['appointments'] as $a) {
                echo "    Appt #{$a['id']} Code: {$a['appointment_code']} Service: {$a['service_name']} Photo: '{$a['photo_path']}'\n";
            }
        }
    }
}

echo "\n=== FILES IN Patients/uploads/ ===\n";
$files = glob(__DIR__ . '/../Patients/uploads/*');
foreach ($files as $f) {
    echo basename($f) . " (" . filesize($f) . " bytes)\n";
}
