<?php
declare(strict_types=1);

require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';

$res = db()->query("SELECT COUNT(*) AS total, MIN(preferred_date) AS min_date, MAX(preferred_date) AS max_date, MIN(created_at) AS min_created, MAX(created_at) AS max_created FROM appointments");
print_r($res->fetch_assoc());

$statusCounts = db()->query("SELECT status, COUNT(*) as cnt FROM appointments GROUP BY status");
while ($r = $statusCounts->fetch_assoc()) {
    echo "Status: " . $r['status'] . " => " . $r['cnt'] . "\n";
}

$sampleAppts = db()->query("SELECT id, reference_code, preferred_date, preferred_time, status, service_name, station_name, created_at FROM appointments ORDER BY id DESC LIMIT 10");
while ($r = $sampleAppts->fetch_assoc()) {
    print_r($r);
}
