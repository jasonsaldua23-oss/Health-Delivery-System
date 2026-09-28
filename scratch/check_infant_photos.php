<?php
require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';

$db = db();

foreach (['u763176290_hds', 'health_delivery_system'] as $dbname) {
    echo "=================== DB: $dbname ===================\n";
    $db->select_db($dbname);

    echo "--- infant_profiles ---\n";
    $res = $db->query("SELECT * FROM infant_profiles");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            echo json_encode($row) . "\n";
        }
    } else {
        echo "No infant_profiles table or query failed\n";
    }

    echo "--- appointments with recipient or immunization ---\n";
    $res2 = $db->query("SELECT id, patient_id, appointment_code, photo_path, service_slug, recipient_first_name, recipient_last_name, status, preferred_date FROM appointments WHERE service_slug = 'immunization' OR recipient_first_name IS NOT NULL");
    if ($res2) {
        while ($row = $res2->fetch_assoc()) {
            echo json_encode($row) . "\n";
        }
    }

    echo "--- appointments with photo_path ---\n";
    $res3 = $db->query("SELECT id, patient_id, appointment_code, photo_path, service_slug, recipient_first_name, recipient_last_name, first_name, last_name, status, preferred_date FROM appointments WHERE photo_path IS NOT NULL AND photo_path != ''");
    if ($res3) {
        while ($row = $res3->fetch_assoc()) {
            echo json_encode($row) . "\n";
        }
    }
}
