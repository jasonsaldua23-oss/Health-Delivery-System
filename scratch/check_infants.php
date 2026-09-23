<?php
require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';
$db = db();
$res = $db->query("SELECT station_slug, count(*) as c FROM infant_profiles GROUP BY station_slug");
if ($res) {
    while($r = $res->fetch_assoc()) { echo "infant_profiles: " . $r['station_slug'] . " = " . $r['c'] . PHP_EOL; }
}
$res2 = $db->query("SELECT station_slug, count(*) as c FROM appointments WHERE recipient_first_name IS NOT NULL AND recipient_first_name != '' GROUP BY station_slug");
if ($res2) {
    while($r = $res2->fetch_assoc()) { echo "immunization appts: " . $r['station_slug'] . " = " . $r['c'] . PHP_EOL; }
}
$res3 = $db->query("SELECT email, staff_name, station_slug FROM staff_accounts");
if ($res3) {
    while($r = $res3->fetch_assoc()) { echo "staff: " . $r['email'] . " -> " . $r['station_slug'] . PHP_EOL; }
}
