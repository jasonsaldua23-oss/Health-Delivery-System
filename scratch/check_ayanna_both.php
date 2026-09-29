<?php
$m = new mysqli('127.0.0.1', 'root', '', '', 3306);
foreach (['u763176290_hds', 'health_delivery_system'] as $dbname) {
    echo "=== DATABASE: $dbname ===\n";
    $m->select_db($dbname);

    echo "Appointments with photo_path:\n";
    $res = $m->query("SELECT id, reference_code, appointment_code, patient_id, first_name, last_name, service_name, recipient_first_name, recipient_middle_name, recipient_last_name, photo_path, preferred_date, status, created_at FROM appointments WHERE photo_path IS NOT NULL AND photo_path != ''");
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            echo "  Appt with photo: " . json_encode($r) . "\n";
        }
    }

    echo "Appointments with recipient:\n";
    $res = $m->query("SELECT id, reference_code, appointment_code, patient_id, first_name, last_name, service_name, recipient_first_name, recipient_middle_name, recipient_last_name, photo_path, preferred_date, status, created_at FROM appointments WHERE recipient_first_name IS NOT NULL AND recipient_first_name != ''");
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            echo "  Appt with recipient: " . json_encode($r) . "\n";
        }
    }

    echo "Patient accounts with Mekai:\n";
    $res = $m->query("SELECT * FROM patient_accounts WHERE first_name LIKE '%Mekai%' OR last_name LIKE '%Mekai%' OR email LIKE '%mekai%'");
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            echo "  Account: " . json_encode($r) . "\n";
        }
    }

    echo "Patient profiles with Mekai:\n";
    $res = $m->query("SELECT * FROM patient_profiles WHERE first_name LIKE '%Mekai%' OR last_name LIKE '%Mekai%' OR email LIKE '%mekai%'");
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            echo "  Profile: " . json_encode($r) . "\n";
        }
    }

    echo "Infant profiles:\n";
    $res = $m->query("SELECT * FROM infant_profiles");
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            echo "  Infant profile: " . json_encode($r) . "\n";
        }
    }

    echo "Immunized infants:\n";
    $res = $m->query("SELECT * FROM immunized_infants");
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            echo "  Immunized infant: " . json_encode($r) . "\n";
        }
    }
}
