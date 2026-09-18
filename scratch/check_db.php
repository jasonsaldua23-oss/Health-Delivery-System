<?php
require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';

$db = db();
echo "=== APPOINTMENTS WITH RECIPIENTS OR IMMUNIZATION ===\n";
$res = $db->query("SELECT id, patient_id, service_slug, service_name, appointment_code, vaccine_type, recipient_first_name, recipient_last_name, doctor_notes, status FROM appointments WHERE recipient_first_name IS NOT NULL AND recipient_first_name != '' OR service_slug LIKE '%immuniz%' OR service_slug LIKE '%vaccin%'");
while ($r = $res->fetch_assoc()) {
    echo "ID: {$r['id']} | Patient: {$r['patient_id']} | Service: {$r['service_name']} | Recipient: {$r['recipient_first_name']} {$r['recipient_last_name']} | Vaccine: '{$r['vaccine_type']}' | Status: {$r['status']} | Notes: {$r['doctor_notes']}\n";
}

echo "\n=== IMMUNIZED_INFANTS ===\n";
$res2 = $db->query("SELECT * FROM immunized_infants");
while ($r2 = $res2->fetch_assoc()) {
    print_r($r2);
}

echo "\n=== INFANT_PROFILES ===\n";
$res3 = $db->query("SELECT * FROM infant_profiles");
while ($r3 = $res3->fetch_assoc()) {
    print_r($r3);
}
