<?php

declare(strict_types=1);

require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';

ini_set('display_errors', '1');
error_reporting(E_ALL);

echo "=== Testing Infant Sub-Profiles & Dose Counting DB Layer ===\n";

try {
    $db = db();
    // 1. Ensure table exists
    ensure_infant_profiles_table($db);
    echo "[1] infant_profiles table verified.\n";

    // 2. Fetch all appointments to inspect patients with infant bookings
    $res = $db->query("SELECT * FROM appointments");
    $allAppts = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    echo "[2] Total appointments in DB: " . count($allAppts) . "\n";

    // 3. Find patients with infant bookings
    $patientsWithInfants = [];
    $patientsWithoutInfants = [];

    $patRes = $db->query("SELECT * FROM patient_accounts");
    $allPatients = $patRes ? $patRes->fetch_all(MYSQLI_ASSOC) : [];

    foreach ($allPatients as $pat) {
        $patId = (string) ($pat['patient_id'] ?? $pat['id']);
        $hasInfants = patient_has_infant_bookings($patId, $allAppts);
        if ($hasInfants) {
            $patientsWithInfants[] = $pat;
        } else {
            $patientsWithoutInfants[] = $pat;
        }
    }

    echo "[3] Patients with infant immunization bookings: " . count($patientsWithInfants) . "\n";
    echo "[4] Patients WITHOUT infant immunization bookings: " . count($patientsWithoutInfants) . "\n";

    foreach ($patientsWithInfants as $p) {
        echo "  - Parent Patient: ID=" . ($p['id'] ?? '') . ", patient_id=" . ($p['patient_id'] ?? '') . " (" . $p['first_name'] . " " . $p['last_name'] . ")\n";
        $patKey = (string) ($p['patient_id'] ?? $p['id'] ?? '');
        $infants = fetch_infant_sub_profiles_by_patient_id($patKey, $allAppts);
        echo "    Found " . count($infants) . " distinct infant(s):\n";
        foreach ($infants as $inf) {
            echo "      * Infant: " . $inf['full_name'] . " (Age: " . $inf['age_label'] . ", Gender: " . $inf['gender'] . ")\n";
            echo "        Vaccine Counts: " . json_encode($inf['vaccine_counts']) . "\n";
            echo "        Appointments Count: " . count($inf['appointments']) . "\n";
            echo "        Total Doses: " . $inf['total_doses'] . "\n";
            echo "        Latest Photo: " . ($inf['latest_photo'] ?? 'None') . "\n";
        }
    }

    // If no patient has an infant booking, create test appointments for an infant to test end-to-end
    if (empty($patientsWithInfants) && !empty($allPatients)) {
        echo "[5] Creating test infant appointments for patient " . $allPatients[0]['id'] . "...\n";
        $testPat = $allPatients[0];
        $patIdStr = (string) ($testPat['patient_id'] ?? $testPat['id'] ?? '1');
        $stmt = $db->prepare("INSERT INTO appointments (
            patient_id, service_slug, service_name, station_slug, station_name,
            appointment_code, reference_code, preferred_date, preferred_time,
            first_name, last_name, birth_date, gender, contact_number, complete_address,
            recipient_first_name, recipient_last_name, recipient_birth_date, immunization_relationship,
            status, vaccine_type, body_temperature, pulse_rate, respiration_rate, blood_pressure
        ) VALUES (
            ?, 'immunization-services', 'National Immunization Program', 'bata', 'Barangay Bata Health Center',
            'APT-INF-001', 'REF-INF-001', '2026-09-18', '09:00 AM',
            ?, ?, ?, ?, ?, ?,
            'Baby Leo', 'Santos', '2025-11-15', 'Child',
            'Completed', 'Pentavalent (DTP-HepB-Hib)', '36.8', '110', '28', '85/55'
        )");
        $stmt->bind_param(
            'sssssss',
            $patIdStr,
            $testPat['first_name'], $testPat['last_name'], $testPat['birth_date'], $testPat['gender'], $testPat['contact_number'], $testPat['complete_address']
        );
        $stmt->execute();

        // 2nd appointment
        $stmt2 = $db->prepare("INSERT INTO appointments (
            patient_id, service_slug, service_name, station_slug, station_name,
            appointment_code, reference_code, preferred_date, preferred_time,
            first_name, last_name, birth_date, gender, contact_number, complete_address,
            recipient_first_name, recipient_last_name, recipient_birth_date, immunization_relationship,
            status, vaccine_type, body_temperature, pulse_rate, respiration_rate, blood_pressure
        ) VALUES (
            ?, 'immunization-services', 'National Immunization Program', 'bata', 'Barangay Bata Health Center',
            'APT-INF-002', 'REF-INF-002', '2026-08-10', '09:30 AM',
            ?, ?, ?, ?, ?, ?,
            'Baby Leo', 'Santos', '2025-11-15', 'Child',
            'Completed', 'Pentavalent (DTP-HepB-Hib)', '36.6', '112', '26', '88/56'
        )");
        $stmt2->bind_param(
            'sssssss',
            $patIdStr,
            $testPat['first_name'], $testPat['last_name'], $testPat['birth_date'], $testPat['gender'], $testPat['contact_number'], $testPat['complete_address']
        );
        $stmt2->execute();

        // 3rd appointment
        $stmt3 = $db->prepare("INSERT INTO appointments (
            patient_id, service_slug, service_name, station_slug, station_name,
            appointment_code, reference_code, preferred_date, preferred_time,
            first_name, last_name, birth_date, gender, contact_number, complete_address,
            recipient_first_name, recipient_last_name, recipient_birth_date, immunization_relationship,
            status, vaccine_type, body_temperature, pulse_rate, respiration_rate, blood_pressure
        ) VALUES (
            ?, 'immunization-services', 'National Immunization Program', 'bata', 'Barangay Bata Health Center',
            'APT-INF-003', 'REF-INF-003', '2025-12-01', '08:30 AM',
            ?, ?, ?, ?, ?, ?,
            'Baby Leo', 'Santos', '2025-11-15', 'Child',
            'Completed', 'BCG', '36.5', '115', '30', '80/50'
        )");
        $stmt3->bind_param(
            'sssssss',
            $patIdStr,
            $testPat['first_name'], $testPat['last_name'], $testPat['birth_date'], $testPat['gender'], $testPat['contact_number'], $testPat['complete_address']
        );
        $stmt3->execute();

        echo "  -> Created 3 test appointments for infant 'Baby Leo Santos'!\n";

        // Re-verify
        $infants = fetch_infant_sub_profiles_by_patient_id((string) $testPat['id']);
        echo "  -> Re-checked infant sub profiles for parent " . $testPat['id'] . ": " . count($infants) . " found.\n";
        foreach ($infants as $inf) {
            echo "     * Name: " . $inf['full_name'] . "\n";
            echo "       Doses: " . json_encode($inf['vaccine_doses']) . "\n";
            echo "       Total Visits: " . count($inf['appointments']) . "\n";
        }
    }

    // 6. Test save_or_update_infant_profile
    if (!empty($allPatients)) {
        $saveData = [
            'patient_id' => (string) $allPatients[0]['id'],
            'first_name' => 'Baby Leo',
            'last_name' => 'Santos',
            'birth_date' => '2025-11-15',
            'mother_name' => 'Clara Santos',
            'father_name' => 'Juan Santos',
            'station_slug' => 'bata',
            'notes' => 'No known allergies. Healthy infant.',
        ];
        $savedId = save_or_update_infant_profile($saveData);
        echo "[6] save_or_update_infant_profile result ID: " . $savedId . "\n";

        $fetchedProfile = fetch_infant_profile_by_id((int) $savedId);
        echo "    Fetched profile Mother Name: " . ($fetchedProfile['mother_name'] ?? 'None') . "\n";
        echo "    Fetched profile Father Name: " . ($fetchedProfile['father_name'] ?? 'None') . "\n";
    }

    echo "\n=== Backend Database Layer Tests COMPLETED Successfully ===\n";
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
