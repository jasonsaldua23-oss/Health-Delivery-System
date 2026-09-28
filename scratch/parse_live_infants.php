<?php
$html = file_get_contents(__DIR__ . '/live_admin_patients.html');
preg_match_all('/openAdminInfantViewer\((\{[\s\S]*?\})\)/', $html, $matches);
echo "Found " . count($matches[1]) . " openAdminInfantViewer calls:\n";

foreach ($matches[1] as $idx => $jsonStr) {
    // Decode HTML entities
    $clean = html_entity_decode($jsonStr, ENT_QUOTES, 'UTF-8');
    $data = json_decode($clean, true);
    if (!$data) {
        echo "Call #$idx could not be parsed: " . substr($clean, 0, 100) . "\n";
        continue;
    }
    echo "--- PATIENT: {$data['patient_name']} (ID: {$data['patient_id']}) ---\n";
    foreach ($data['infants'] as $inf) {
        echo "  Infant: {$inf['full_name']} (ID: " . ($inf['id'] ?? 'none') . ")\n";
        echo "    latest_photo: " . ($inf['latest_photo'] ?? '') . "\n";
        echo "    photo_path: " . ($inf['photo_path'] ?? '') . "\n";
        if (!empty($inf['appointments'])) {
            echo "    Appointments (" . count($inf['appointments']) . "):\n";
            foreach ($inf['appointments'] as $appt) {
                echo "      - Code: " . ($appt['appointment_code'] ?? $appt['reference_code'] ?? '') . " | Date: " . ($appt['preferred_date'] ?? '') . " | Photo: " . ($appt['photo_path'] ?? '') . " | Svc: " . ($appt['service_name'] ?? '') . "\n";
            }
        }
    }
}
