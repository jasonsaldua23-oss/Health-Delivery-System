<?php
declare(strict_types=1);

require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';

$res = db()->query("SELECT id, first_name, last_name, patient_id, preferred_date, service_slug, service_name, status FROM appointments");
while ($r = $res->fetch_assoc()) {
    print_r($r);
}
