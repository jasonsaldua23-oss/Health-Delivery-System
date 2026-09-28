<?php
require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';
$db = db();

echo "=== APPOINTMENTS WITH RECIPIENTS ===\n";
$res = $db->query("SELECT id, patient_id, recipient_first_name, recipient_last_name, recipient_birth_date, photo_path, status, service_name, preferred_date FROM appointments WHERE recipient_first_name IS NOT NULL AND recipient_first_name != ''");
$rows = $res->fetch_all(MYSQLI_ASSOC);
foreach ($rows as $r) {
    echo "ID: {$r['id']} | Patient: {$r['patient_id']} | Infant: {$r['recipient_first_name']} {$r['recipient_last_name']} | Status: {$r['status']} | Date: {$r['preferred_date']} | Photo: {$r['photo_path']}\n";
}

echo "\n=== ALL INFANT PROFILES TABLE ===\n";
$res2 = $db->query("SELECT * FROM infant_profiles");
$rows2 = $res2->fetch_all(MYSQLI_ASSOC);
foreach ($rows2 as $r) {
    echo "ID: {$r['id']} | Patient: {$r['patient_id']} | Infant: {$r['first_name']} {$r['last_name']} | Photo: " . ($r['photo_path'] ?? 'N/A') . "\n";
}

echo "\n=== ALL IMMUNIZED INFANTS TABLE ===\n";
$res3 = $db->query("SELECT * FROM immunized_infants");
$rows3 = $res3->fetch_all(MYSQLI_ASSOC);
foreach ($rows3 as $r) {
    echo "ID: {$r['id']} | Patient: {$r['patient_id']} | Infant: {$r['first_name']} {$r['last_name']} | Appt: {$r['appointment_code']}\n";
}
