<?php
require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';

$db = db();

echo "=== ALL APPOINTMENTS IN DB ===\n";
$res = $db->query("SELECT id, patient_id, appointment_code, photo_path, service_name, recipient_first_name, recipient_last_name, first_name, last_name, status, preferred_date FROM appointments WHERE photo_path IS NOT NULL AND photo_path != ''");
while ($r = $res->fetch_assoc()) {
    echo json_encode($r) . "\n";
}

echo "\n=== ALL APPOINTMENTS WITH RECIPIENT ===\n";
$res2 = $db->query("SELECT id, patient_id, appointment_code, photo_path, service_name, recipient_first_name, recipient_last_name, first_name, last_name, status, preferred_date FROM appointments WHERE recipient_first_name IS NOT NULL AND recipient_first_name != ''");
while ($r = $res2->fetch_assoc()) {
    echo json_encode($r) . "\n";
}

echo "\n=== FILES IN Patients/uploads ===\n";
foreach (glob(__DIR__ . '/../Patients/uploads/*') as $f) {
    echo basename($f) . " (" . filesize($f) . " bytes)\n";
}

echo "\n=== FILES IN uploads ===\n";
foreach (glob(__DIR__ . '/../uploads/*') as $f) {
    echo basename($f) . " (" . filesize($f) . " bytes)\n";
}
