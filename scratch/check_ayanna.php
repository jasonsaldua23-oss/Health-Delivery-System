<?php
require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';

$db = db();
$res = $db->query("SELECT id, reference_code, appointment_code, patient_id, first_name, last_name, service_name, recipient_first_name, recipient_middle_name, recipient_last_name, photo_path, preferred_date, status FROM appointments WHERE recipient_first_name LIKE '%Ayanna%' OR first_name LIKE '%Ayanna%'");
print_r($res->fetch_all(MYSQLI_ASSOC));

$res2 = $db->query("SELECT * FROM infant_profiles WHERE first_name LIKE '%Ayanna%'");
print_r($res2->fetch_all(MYSQLI_ASSOC));
