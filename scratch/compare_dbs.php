<?php
$m1 = new mysqli('127.0.0.1', 'root', '', 'u763176290_hds');
$m2 = new mysqli('127.0.0.1', 'root', '', 'health_delivery_system');

echo "=== u763176290_hds appointments count ===\n";
echo $m1->query("SELECT count(*) FROM appointments")->fetch_row()[0] . "\n";
echo "=== health_delivery_system appointments count ===\n";
echo $m2->query("SELECT count(*) FROM appointments")->fetch_row()[0] . "\n";

echo "\nAppointments in health_delivery_system with photo_path or infant/recipient:\n";
$res = $m2->query("SELECT id, appointment_code, patient_id, first_name, last_name, service_name, recipient_first_name, recipient_last_name, photo_path, status, preferred_date FROM appointments WHERE photo_path IS NOT NULL AND photo_path != '' OR recipient_first_name IS NOT NULL");
while ($r = $res->fetch_assoc()) {
    echo json_encode($r) . "\n";
}

echo "\nInfant profiles in health_delivery_system:\n";
$res = $m2->query("SHOW TABLES LIKE 'infant_profiles'");
if ($res->num_rows > 0) {
    $r2 = $m2->query("SELECT * FROM infant_profiles");
    while ($r = $r2->fetch_assoc()) {
        echo json_encode($r) . "\n";
    }
}
