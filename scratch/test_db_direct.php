<?php
$m = @new mysqli('127.0.0.1', 'root', '', 'u763176290_hds');
if ($m->connect_errno) {
    $m = @new mysqli('127.0.0.1', 'root', '', 'u763176290_HDS');
}
if ($m->connect_errno) {
    echo "ERR: " . $m->connect_error . "\n";
    exit;
}
echo "Connected successfully to " . $m->query("SELECT DATABASE()")->fetch_row()[0] . "\n";

$res = $m->query("SELECT id, appointment_code, patient_id, first_name, last_name, service_name, status, recipient_first_name, recipient_middle_name, recipient_last_name, photo_path, preferred_date FROM appointments WHERE recipient_first_name LIKE '%Ayanna%' OR recipient_last_name LIKE '%Fajardo%' OR first_name LIKE '%Ayanna%'");
while ($row = $res->fetch_assoc()) {
    echo json_encode($row, JSON_PRETTY_PRINT) . "\n";
}

$res2 = $m->query("SELECT * FROM infant_profiles WHERE first_name LIKE '%Ayanna%' OR last_name LIKE '%Fajardo%'");
while ($row = $res2->fetch_assoc()) {
    echo "INFANT_PROFILE: " . json_encode($row, JSON_PRETTY_PRINT) . "\n";
}
