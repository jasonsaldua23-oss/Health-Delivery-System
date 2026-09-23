<?php
require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';

$db = db();
echo "--- ALL APPOINTMENTS ORDER BY ID DESC LIMIT 20 ---\n";
$res = $db->query("SELECT id, appointment_code, patient_id, first_name, last_name, service_name, status, preferred_date, photo_path FROM appointments ORDER BY id DESC LIMIT 20");
while ($r = $res->fetch_assoc()) {
    echo "ID: {$r['id']} | Code: {$r['appointment_code']} | Pat: {$r['patient_id']} ({$r['first_name']} {$r['last_name']}) | Date: {$r['preferred_date']} | Svc: {$r['service_name']} | Status: {$r['status']} | Photo: '{$r['photo_path']}'\n";
}

echo "\n--- CHECKING FOR AH3BSATG OR 6KZ7TPR6 ---\n";
$res2 = $db->query("SELECT * FROM appointments WHERE appointment_code IN ('AH3BSATG', '6KZ7TPR6', 'M7CRSYSG') OR reference_code IN ('AH3BSATG', '6KZ7TPR6', 'M7CRSYSG')");
while ($r = $res2->fetch_assoc()) {
    echo "FOUND: ID: {$r['id']} | Code: {$r['appointment_code']} | Pat: {$r['patient_id']} | Svc: {$r['service_name']} | Date: {$r['preferred_date']} | Photo: '{$r['photo_path']}'\n";
}
