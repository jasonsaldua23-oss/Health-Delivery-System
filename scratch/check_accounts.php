<?php
require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';

$db = db();
echo "=== ADMIN ACCOUNTS ===\n";
$res = $db->query("SELECT id, admin_name, email FROM admin_accounts");
while ($r = $res->fetch_assoc()) {
    echo "ID: {$r['id']} | Name: {$r['admin_name']} | Email: {$r['email']}\n";
}

echo "\n=== STAFF ACCOUNTS ===\n";
$res2 = $db->query("SELECT id, staff_name, station_slug, email FROM staff_accounts");
while ($r = $res2->fetch_assoc()) {
    echo "ID: {$r['id']} | Name: {$r['staff_name']} | Station: {$r['station_slug']} | Email: {$r['email']}\n";
}
