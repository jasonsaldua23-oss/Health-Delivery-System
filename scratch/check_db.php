<?php
require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';

$db = db();
$log = run_database_migrations($db);
echo "Migrations result:\n";
print_r($log);


$resStaff = $db->query("SELECT id, email, password_hash, full_name, station_slug FROM staff_accounts");
while ($s = $resStaff->fetch_assoc()) {
    echo "Staff: {$s['id']} | {$s['email']} | {$s['full_name']} | {$s['station_slug']} | Hash: " . substr($s['password_hash'], 0, 10) . "...\n";
}


echo "\n=== IMMUNIZED_INFANTS ===\n";
$res2 = $db->query("SELECT * FROM immunized_infants");
while ($r2 = $res2->fetch_assoc()) {
    print_r($r2);
}

echo "\n=== APPOINTMENTS COLUMNS ===\n";
$resCols = $db->query("SHOW COLUMNS FROM appointments");
while ($c = $resCols->fetch_assoc()) {
    echo "{$c['Field']} ({$c['Type']})\n";
}


echo "\n=== PATIENTS PHOTOS ===\n";
$resPat = $db->query("SELECT id, patient_id, first_name, last_name, photo_path FROM patients");
while ($rpat = $resPat->fetch_assoc()) {
    echo "ID: {$rpat['id']} | Patient: {$rpat['patient_id']} | Name: {$rpat['first_name']} {$rpat['last_name']} | Photo: '{$rpat['photo_path']}'\n";
}

