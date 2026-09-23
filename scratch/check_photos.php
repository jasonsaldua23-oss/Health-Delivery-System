<?php
require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';

$db = db();
$res = $db->query("SELECT id, patient_id, service_slug, immunization_relationship, recipient_first_name, recipient_last_name, photo_path, preferred_date, status, created_at FROM appointments ORDER BY id DESC LIMIT 50");
$rows = [];
while ($row = $res->fetch_assoc()) {
    $rows[] = $row;
}
echo json_encode($rows, JSON_PRETTY_PRINT);
