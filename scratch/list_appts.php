<?php
$m = new mysqli('127.0.0.1', 'root', '', 'u763176290_hds', 3306);
$res = $m->query("SELECT id, appointment_code, patient_id, first_name, last_name, service_name, status, recipient_first_name, recipient_last_name, photo_path FROM appointments ORDER BY id DESC LIMIT 50");
while ($r = $res->fetch_assoc()) {
    echo "ID: {$r['id']} | Code: {$r['appointment_code']} | Pat: {$r['patient_id']} | Name: {$r['first_name']} {$r['last_name']} | Recipient: {$r['recipient_first_name']} {$r['recipient_last_name']} | Photo: '{$r['photo_path']}'\n";
}
