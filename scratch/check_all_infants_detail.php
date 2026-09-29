<?php
require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';

$db = db();
$db->select_db('u763176290_HDS');

echo "=== APPOINTMENTS WITH APPOINTMENT_FOR OR RECIPIENT OR IMMUNIZATION ===\n";
$res = $db->query("SELECT id, patient_id, appointment_code, photo_path, service_name, service_slug, recipient_first_name, recipient_last_name, first_name, last_name, status, preferred_date FROM appointments WHERE recipient_first_name IS NOT NULL OR service_slug LIKE '%immuniz%' OR service_name LIKE '%immuniz%' OR appointment_for = 'someone_else'");
while ($r = $res->fetch_assoc()) {
    echo json_encode($r) . "\n";
}

echo "\n=== ALL APPOINTMENTS WITH PHOTO_PATH NOT NULL ===\n";
$res2 = $db->query("SELECT id, patient_id, appointment_code, photo_path, service_name, recipient_first_name, recipient_last_name, first_name, last_name, status, preferred_date FROM appointments WHERE photo_path IS NOT NULL AND photo_path != ''");
while ($r = $res2->fetch_assoc()) {
    echo json_encode($r) . "\n";
}

echo "\n=== IMMUNIZED INFANTS TABLE ===\n";
$res3 = $db->query("SELECT * FROM immunized_infants");
if ($res3) {
    while ($r = $res3->fetch_assoc()) {
        echo json_encode($r) . "\n";
    }
}
