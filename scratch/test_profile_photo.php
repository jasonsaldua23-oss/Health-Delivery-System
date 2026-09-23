<?php
require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';

$prof = fetch_patient_profile('P2YLL5');
echo "Patient profile for P2YLL5:\n";
echo "photo_path in profile: '" . ($prof['photo_path'] ?? 'NULL') . "'\n";
echo "Resolved staff URL: '" . resolve_patient_photo_url((string)($prof['photo_path'] ?? ''), 'staff') . "'\n";
echo "Resolved admin URL: '" . resolve_patient_photo_url((string)($prof['photo_path'] ?? ''), 'admin') . "'\n";
echo "Visits count: " . count($prof['visits'] ?? []) . "\n";
foreach ($prof['visits'] as $v) {
    echo "  Visit ID: {$v['id']} | Service: {$v['service_name']} | Date: {$v['preferred_date']} | Status: {$v['status']} | Photo: '{$v['photo_path']}' | Resolved: '" . resolve_patient_photo_url((string)($v['photo_path'] ?? ''), 'staff') . "'\n";
}
