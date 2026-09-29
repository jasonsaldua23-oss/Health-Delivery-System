<?php
require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';
$db = db();
$res = $db->query("SELECT id, reference_code, appointment_code, patient_id, first_name, last_name, service_name, recipient_first_name, recipient_last_name, photo_path, status, preferred_date, created_at FROM appointments ORDER BY id ASC");
while ($r = $res->fetch_assoc()) {
    echo "ID {$r['id']} | Code: {$r['appointment_code']} | Pat: {$r['patient_id']} ({$r['first_name']} {$r['last_name']}) | Recip: '{$r['recipient_first_name']} {$r['recipient_last_name']}' | Svc: {$r['service_name']} | Status: {$r['status']} | Date: {$r['preferred_date']} | Photo: '{$r['photo_path']}'\n";
}
