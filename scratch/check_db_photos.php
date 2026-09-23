<?php
require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';

$db = db();
$res = $db->query('SELECT id, patient_id, first_name, last_name, service_name, service_slug, status, preferred_date, recipient_first_name, recipient_last_name, photo_path FROM appointments WHERE photo_path IS NOT NULL AND photo_path != "" ORDER BY id DESC');
echo "=== APPOINTMENTS WITH PHOTOS ===\n";
while ($row = $res->fetch_assoc()) {
    echo "ID: {$row['id']} | Patient: {$row['patient_id']} ({$row['first_name']} {$row['last_name']}) | Service: {$row['service_name']} | Recipient: {$row['recipient_first_name']} {$row['recipient_last_name']} | Date: {$row['preferred_date']} | Status: {$row['status']} | Photo: {$row['photo_path']}\n";
}

echo "\n=== ALL APPOINTMENTS FOR IMMUNIZATION ===\n";
$resImm = $db->query('SELECT id, patient_id, first_name, last_name, service_name, status, preferred_date, recipient_first_name, recipient_last_name, photo_path FROM appointments WHERE service_slug LIKE "%immuniz%" OR service_name LIKE "%immuniz%" ORDER BY id DESC');
while ($row = $resImm->fetch_assoc()) {
    echo "ID: {$row['id']} | Patient: {$row['patient_id']} | Recipient: {$row['recipient_first_name']} {$row['recipient_last_name']} | Date: {$row['preferred_date']} | Status: {$row['status']} | Photo: {$row['photo_path']}\n";
}

echo "\n=== IMMUNIZED INFANTS TABLE ===\n";
$resInf = $db->query('SELECT * FROM immunized_infants');
while ($row = $resInf->fetch_assoc()) {
    echo json_encode($row) . "\n";
}

echo "\n=== INFANT PROFILES TABLE ===\n";
$resProf = $db->query('SELECT * FROM infant_profiles');
while ($row = $resProf->fetch_assoc()) {
    echo json_encode($row) . "\n";
}
