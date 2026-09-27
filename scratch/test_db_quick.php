<?php
require_once __DIR__ . '/../shared/bootstrap.php';
echo "DB_HOST=" . DB_HOST . " DB_PORT=" . DB_PORT . " DB_USER=" . DB_USER . " DB_NAME=" . DB_NAME . "\n";
require_once __DIR__ . '/../shared/database.php';
try {
    $db = db();
    echo "Connected successfully!\n";
    $res = $db->query("SELECT id, appointment_code, patient_id, first_name, last_name, service_name, status, recipient_first_name, recipient_middle_name, recipient_last_name, photo_path, preferred_date FROM appointments WHERE recipient_first_name LIKE '%Ayanna%' OR recipient_last_name LIKE '%Fajardo%' OR first_name LIKE '%Ayanna%'");
    while ($r = $res->fetch_assoc()) {
        echo "Appointment: " . json_encode($r) . "\n";
    }
    $res2 = $db->query("SELECT * FROM infant_profiles WHERE first_name LIKE '%Ayanna%' OR last_name LIKE '%Fajardo%'");
    while ($r = $res2->fetch_assoc()) {
        echo "Infant profile: " . json_encode($r) . "\n";
    }
} catch (Throwable $e) {
    echo "DB error: " . $e->getMessage() . "\n";
}
