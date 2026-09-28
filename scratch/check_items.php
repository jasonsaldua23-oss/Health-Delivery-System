<?php
require_once __DIR__ . '/../shared/database.php';
$db = db();

echo "--- ALL APPOINTMENTS FOR P2YLL5 ---\n";
$res = $db->query("SELECT id, appointment_code, service_name, recipient_first_name, recipient_last_name, photo_path, status, preferred_date FROM appointments WHERE patient_id = 'P2YLL5'");
while ($r = $res->fetch_assoc()) {
    print_r($r);
}

echo "--- ALL APPOINTMENTS WITH NON-EMPTY PHOTO_PATH ---\n";
$res2 = $db->query("SELECT id, patient_id, appointment_code, service_name, recipient_first_name, recipient_last_name, photo_path, status FROM appointments WHERE photo_path IS NOT NULL AND photo_path != ''");
while ($r = $res2->fetch_assoc()) {
    print_r($r);
}

echo "--- ALL PATIENTS WITH NON-EMPTY PHOTO_PATH ---\n";
$res3 = $db->query("SELECT patient_id, first_name, last_name, photo_path FROM patient_profiles WHERE photo_path IS NOT NULL AND photo_path != ''");
while ($r = $res3->fetch_assoc()) {
    print_r($r);
}
