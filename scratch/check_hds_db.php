<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', 'health_delivery_system');
$res = $mysqli->query("SELECT id, patient_id, appointment_code, photo_path, service_name, recipient_first_name, recipient_last_name, first_name, last_name, status, preferred_date FROM appointments ORDER BY id ASC");
echo "Total appointments in health_delivery_system: " . $res->num_rows . "\n";
while ($r = $res->fetch_assoc()) {
    echo "APPT #{$r['id']}: Code={$r['appointment_code']} PatID={$r['patient_id']} Recipient={$r['recipient_first_name']} {$r['recipient_last_name']} Patient={$r['first_name']} {$r['last_name']} Service={$r['service_name']} Photo={$r['photo_path']} Status={$r['status']} Date={$r['preferred_date']}\n";
}
