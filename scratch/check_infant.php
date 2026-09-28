<?php
require_once __DIR__ . '/../shared/database.php';
$db = db();
echo "--- APPOINTMENTS (IMMUNIZATION) ---\n";
$res = $db->query("SELECT id, patient_id, first_name, last_name, service_name, recipient_first_name, recipient_last_name, status, photo_path FROM appointments WHERE service_slug LIKE '%immuniz%' OR service_slug LIKE '%vaccin%' OR service_name LIKE '%immuniz%' OR service_name LIKE '%vaccin%'");
while ($r = $res->fetch_assoc()) {
    echo json_encode($r) . "\n";
}

echo "\n--- INFANT PROFILES ---\n";
$res = $db->query("SHOW TABLES LIKE 'infant_profiles'");
if ($res->num_rows > 0) {
    $res2 = $db->query("SELECT * FROM infant_profiles");
    while ($r = $res2->fetch_assoc()) {
        echo json_encode($r) . "\n";
    }
}

echo "\n--- IMMUNIZED INFANTS ---\n";
$res = $db->query("SHOW TABLES LIKE 'immunized_infants'");
if ($res->num_rows > 0) {
    $res2 = $db->query("SELECT * FROM immunized_infants");
    while ($r = $res2->fetch_assoc()) {
        echo json_encode($r) . "\n";
    }
}

echo "\n--- ALL PATIENTS WITH INFANTS ---\n";
$patients = fetch_patients();
foreach ($patients as $p) {
    $isParent = patient_has_infant_bookings((string) $p['patient_id']);
    if ($isParent) {
        $infs = fetch_infant_sub_profiles_by_patient_id((string) $p['patient_id']);
        echo "Patient: {$p['first_name']} {$p['last_name']} (ID: {$p['patient_id']})\n";
        foreach ($infs as $inf) {
            echo "  Infant: {$inf['full_name']} | Photo: {$inf['photo_path']} | Appts count: " . count($inf['appointments']) . "\n";
            foreach ($inf['appointments'] as $a) {
                echo "    Appt #{$a['id']} Status: {$a['status']} Photo: {$a['photo_path']}\n";
            }
        }
    }
}
