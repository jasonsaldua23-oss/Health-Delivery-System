<?php
require_once __DIR__ . '/../shared/database.php';
$db = get_db_connection();

echo "=== PATIENT DBBF98 / Mekai ===" . PHP_EOL;
$res = $db->query("SELECT * FROM patients WHERE patient_id = 'DBBF98' OR first_name LIKE '%Mekai%'");
while ($row = $res->fetch_assoc()) {
    print_r($row);
}

echo "=== APPOINTMENTS ===" . PHP_EOL;
$res2 = $db->query("SELECT id, reference_code, appointment_code, patient_id, first_name, last_name, recipient_first_name, recipient_last_name, photo_path, created_at, updated_at FROM appointments WHERE patient_id = 'DBBF98' OR recipient_first_name LIKE '%Ayanna%' OR first_name LIKE '%Mekai%'");
while ($row = $res2->fetch_assoc()) {
    print_r($row);
}

echo "=== ALL APPOINTMENTS WITH PHOTOS ===" . PHP_EOL;
$res_p = $db->query("SELECT id, reference_code, appointment_code, patient_id, first_name, last_name, recipient_first_name, recipient_last_name, photo_path, created_at FROM appointments WHERE photo_path IS NOT NULL AND photo_path != ''");
while ($row = $res_p->fetch_assoc()) {
    print_r($row);
}

echo "=== INFANT PROFILES ===" . PHP_EOL;
$res3 = $db->query("SELECT * FROM infant_profiles WHERE patient_id = 'DBBF98' OR first_name LIKE '%Ayanna%'");
if ($res3) {
    while ($row = $res3->fetch_assoc()) {
        print_r($row);
    }
}

echo "=== IMMUNIZED INFANTS ===" . PHP_EOL;
$res4 = $db->query("SELECT * FROM immunized_infants WHERE patient_id = 'DBBF98' OR first_name LIKE '%Ayanna%'");
if ($res4) {
    while ($row = $res4->fetch_assoc()) {
        print_r($row);
    }
}
