<?php
require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';

$db = db();

echo "=== UNATTENDED_QUEUE ===\n";
$res = $db->query("SELECT * FROM unattended_queue");
if ($res) {
    while ($r = $res->fetch_assoc()) {
        echo json_encode($r) . "\n";
    }
}

echo "=== APPOINTMENTS WITH PHOTO_PATH (ANY) ===\n";
$res2 = $db->query("SELECT id, patient_id, appointment_code, photo_path, service_name, recipient_first_name, recipient_last_name, first_name, last_name, status, preferred_date FROM appointments WHERE photo_path IS NOT NULL AND photo_path != ''");
while ($r = $res2->fetch_assoc()) {
    echo json_encode($r) . "\n";
}
