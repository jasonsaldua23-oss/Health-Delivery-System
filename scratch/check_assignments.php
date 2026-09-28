<?php
require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';

$db = db();
$res = $db->query("SELECT * FROM station_service_assignments WHERE station_slug = 'mandalagan'");
echo "=== MANDALAGAN ASSIGNMENTS ===\n";
while ($r = $res->fetch_assoc()) {
    echo json_encode($r) . "\n";
}

echo "\n=== ALL SERVICES IN CATALOG ===\n";
foreach (service_catalog() as $slug => $s) {
    echo "$slug => {$s['title']}\n";
}
