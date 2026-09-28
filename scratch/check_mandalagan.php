<?php
require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';

$db = db();
$res = $db->query("SELECT id, appointment_code, patient_id, first_name, last_name, service_slug, service_name, station_slug, station_name, preferred_date, status FROM appointments WHERE station_slug LIKE '%mandalagan%' OR station_name LIKE '%mandalagan%'");
echo "=== MANDALAGAN APPOINTMENTS IN DB ===\n";
while ($r = $res->fetch_assoc()) {
    echo json_encode($r) . "\n";
}

echo "\n=== FETCH_STATION_COUNTS('Pending', 'both') ===\n";
print_r(fetch_station_counts('Pending', 'both'));

echo "\n=== ALL STATIONS COUNT ===\n";
$res2 = $db->query("SELECT station_slug, status, preferred_date, CURDATE() as cur, count(*) FROM appointments GROUP BY station_slug, status, preferred_date");
while ($r = $res2->fetch_assoc()) {
    echo json_encode($r) . "\n";
}
