<?php
require __DIR__ . '/../shared/config.php';
require __DIR__ . '/../shared/database.php';
$db = db();
$r = $db->query("SELECT id, patient_id, recipient_first_name, recipient_last_name, photo_path, service_slug, status FROM appointments WHERE (service_slug LIKE '%immuniz%' OR service_slug LIKE '%vaccin%' OR service_name LIKE '%immuniz%' OR service_name LIKE '%vaccin%') AND photo_path IS NOT NULL AND photo_path <> '' ORDER BY id DESC LIMIT 20");
echo "=== Immunization appointments with photos ===\n";
while($row = $r->fetch_assoc()) {
    echo json_encode($row) . "\n";
}

echo "\n=== All appointments with photo_path (any service) ===\n";
$r2 = $db->query("SELECT id, patient_id, recipient_first_name, recipient_last_name, photo_path, service_slug, status FROM appointments WHERE photo_path IS NOT NULL AND photo_path <> '' ORDER BY id DESC LIMIT 20");
while($row = $r2->fetch_assoc()) {
    echo json_encode($row) . "\n";
}
