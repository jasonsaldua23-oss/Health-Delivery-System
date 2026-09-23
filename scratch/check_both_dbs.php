<?php
$m = new mysqli('127.0.0.1', 'root', '', '', 3306);
foreach (['u763176290_hds', 'health_delivery_system'] as $dbname) {
    echo "=== DATABASE: $dbname ===\n";
    $m->select_db($dbname);
    $res = $m->query("SELECT id, appointment_code, patient_id, first_name, last_name, service_name, status, preferred_date, photo_path FROM appointments WHERE patient_id = 'P2YLL5' OR appointment_code = 'AH3BSATG' ORDER BY id DESC");
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            echo "ID: {$r['id']} | Code: {$r['appointment_code']} | Pat: {$r['patient_id']} | Date: {$r['preferred_date']} | Svc: {$r['service_name']} | Status: {$r['status']} | Photo: '{$r['photo_path']}'\n";
        }
    } else {
        echo "Query failed: " . $m->error . "\n";
    }
}
