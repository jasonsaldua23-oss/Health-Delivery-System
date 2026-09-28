<?php
require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';

$mysqli = new mysqli('127.0.0.1', 'root', '', '');
foreach (['u763176290_hds', 'health_delivery_system'] as $dbName) {
    echo "=== DATABASE: $dbName ===\n";
    $mysqli->select_db($dbName);
    
    // Check appointments
    $res = $mysqli->query("SELECT id, appointment_code, patient_id, first_name, last_name, recipient_first_name, recipient_last_name, photo_path, service_name, created_at FROM appointments WHERE photo_path IS NOT NULL AND photo_path != ''");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            echo "APPT #{$row['id']} Code: {$row['appointment_code']}, Patient: {$row['patient_id']} ({$row['first_name']} {$row['last_name']}), Recipient: {$row['recipient_first_name']} {$row['recipient_last_name']}, Photo: {$row['photo_path']}, Service: {$row['service_name']}\n";
        }
    }
    
    // Check patient_profiles
    $res2 = $mysqli->query("SELECT id, patient_id, first_name, last_name, photo_path FROM patient_profiles WHERE photo_path IS NOT NULL AND photo_path != ''");
    if ($res2) {
        while ($row2 = $res2->fetch_assoc()) {
            echo "PROFILE #{$row2['id']}, Patient: {$row2['patient_id']} ({$row2['first_name']} {$row2['last_name']}), Photo: {$row2['photo_path']}\n";
        }
    }
}
