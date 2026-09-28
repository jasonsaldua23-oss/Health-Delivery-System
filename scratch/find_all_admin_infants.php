<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';

$patients = fetch_unique_patients();
echo "Total patients: " . count($patients) . "\n";

foreach ($patients as $p) {
    $pid = (string) ($p['patient_id'] ?? '');
    $isParent = patient_has_infant_bookings($pid);
    $infants = $isParent ? fetch_infant_sub_profiles_by_patient_id($pid) : [];
    if (!empty($infants)) {
        echo "========================================\n";
        echo "Patient: {$p['first_name']} {$p['last_name']} (ID: $pid)\n";
        foreach ($infants as $inf) {
            echo "  Infant: {$inf['full_name']} (DOB: {$inf['birth_date']})\n";
            echo "    photo_path: '{$inf['photo_path']}'\n";
            echo "    latest_photo: '{$inf['latest_photo']}'\n";
            echo "    Appointments count: " . count($inf['appointments']) . "\n";
            foreach ($inf['appointments'] as $appt) {
                echo "      Appt #{$appt['id']} Code: {$appt['appointment_code']}, Date: {$appt['preferred_date']}, Photo: '{$appt['photo_path']}'\n";
            }
        }
    }
}
