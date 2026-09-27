<?php
$m = new mysqli('127.0.0.1', 'root', '', '', 3306);
foreach (['u763176290_hds', 'health_delivery_system'] as $db) {
    echo "=== DB: $db ===\n";
    $m->select_db($db);
    $res = $m->query("SELECT id, appointment_code, patient_id, first_name, last_name, service_name, status, recipient_first_name, recipient_last_name, photo_path FROM appointments WHERE recipient_first_name LIKE '%Ayanna%' OR recipient_last_name LIKE '%Fajardo%' OR first_name LIKE '%Ayanna%' OR last_name LIKE '%Fajardo%'");
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            print_r($r);
        }
    }
    $res2 = $m->query("SELECT * FROM infant_profiles");
    if ($res2) {
        while ($r = $res2->fetch_assoc()) {
            echo "Infant profile: " . json_encode($r) . "\n";
        }
    }
}
