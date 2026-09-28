<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', '');

foreach (['health_delivery_system', 'u763176290_hds'] as $db) {
    echo "================ DB: $db ================\n";
    $mysqli->select_db($db);
    $res = $mysqli->query("SELECT id, appointment_code, first_name, last_name, preferred_date, status, photo_path FROM appointments ORDER BY id DESC LIMIT 10");
    while ($row = $res->fetch_assoc()) {
        echo json_encode($row) . "\n";
    }
}
