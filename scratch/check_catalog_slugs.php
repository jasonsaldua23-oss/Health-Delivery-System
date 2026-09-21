<?php
require_once __DIR__ . '/../shared/database.php';

$res = db()->query("SELECT id, staff_name, email, station_slug, station_name FROM staff_accounts");
while ($row = $res->fetch_assoc()) {
    $station = fetch_station_by_slug_catalog($row['station_slug']);
    if (!$station) {
        echo "MISMATCH! Staff ID {$row['id']} ({$row['staff_name']}, {$row['email']}) has station_slug '{$row['station_slug']}' which is NOT in catalog!\n";
    } else {
        echo "OK: Staff ID {$row['id']} ({$row['email']}) -> station {$station['name']}\n";
    }
}
