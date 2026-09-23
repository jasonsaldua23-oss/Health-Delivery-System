<?php
require_once __DIR__ . '/../shared/database.php';
$res = db()->query('SELECT id, body_temperature, pulse_rate, respiration_rate, blood_pressure, height, weight FROM appointments WHERE body_temperature IS NOT NULL LIMIT 10');
if ($res) {
    while ($row = $res->fetch_assoc()) {
        echo json_encode($row) . PHP_EOL;
    }
}
