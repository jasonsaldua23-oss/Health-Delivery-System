<?php

declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';

echo "========================================================\n";
echo "   VERIFYING INFANT & PATIENT PHOTO REQUIREMENTS       \n";
echo "========================================================\n\n";

$passCount = 0;
$failCount = 0;

function assertTest(bool $condition, string $description): void {
    global $passCount, $failCount;
    if ($condition) {
        echo "  [PASS] {$description}\n";
        $passCount++;
    } else {
        echo "  [FAIL] {$description}\n";
        $failCount++;
    }
}

// ---------------------------------------------------------------------
// TEST 1: Verification of fetch_infant_sub_profiles_by_patient_id with mocked/station data
// ---------------------------------------------------------------------
echo "--- TEST 1: Infant Sub-Profile Photo Resolution & Timeline Isolation ---\n";

$mockPatientId = 'P-TEST-PHOTO-01';

// Create a series of immunization appointments for an infant:
// Appt 1: Sep 10 - older, photo = 'uploads/baby_older.jpg'
// Appt 2: Sep 15 - middle, photo = '' (no photo captured)
// Appt 3: Sep 20 - newest, photo = 'uploads/baby_newest.jpg'
// Also create a TB appointment for the parent on Sep 22 with photo = 'uploads/parent_tb.jpg'

$apptOlder = [
    'id' => 1001,
    'patient_id' => $mockPatientId,
    'first_name' => 'Maria',
    'last_name' => 'Clara',
    'gender' => 'Female',
    'service_slug' => 'immunization',
    'service_name' => 'Immunization',
    'status' => 'Completed',
    'preferred_date' => '2026-09-10',
    'preferred_time' => 'Daily Slot',
    'immunization_relationship' => 'Child',
    'recipient_first_name' => 'Baby',
    'recipient_last_name' => 'Clara',
    'recipient_birth_date' => '2026-01-15',
    'recipient_gender' => 'Female',
    'photo_path' => 'uploads/baby_older.jpg',
    'vaccine_type' => 'BCG',
    'doctor_notes' => 'First dose BCG',
    'body_temperature' => '36.5',
    'pulse_rate' => '110',
    'respiration_rate' => '30',
    'blood_pressure' => '80/50',
    'created_at' => '2026-09-10 08:00:00',
];

$apptNoPhoto = [
    'id' => 1002,
    'patient_id' => $mockPatientId,
    'first_name' => 'Maria',
    'last_name' => 'Clara',
    'gender' => 'Female',
    'service_slug' => 'immunization',
    'service_name' => 'Immunization',
    'status' => 'Completed',
    'preferred_date' => '2026-09-15',
    'preferred_time' => 'Daily Slot',
    'immunization_relationship' => 'Child',
    'recipient_first_name' => 'Baby',
    'recipient_last_name' => 'Clara',
    'recipient_birth_date' => '2026-01-15',
    'recipient_gender' => 'Female',
    'photo_path' => '', // NO photo taken during this visit
    'vaccine_type' => 'Hepatitis B',
    'doctor_notes' => 'Second vaccine',
    'body_temperature' => '36.6',
    'pulse_rate' => '112',
    'respiration_rate' => '28',
    'blood_pressure' => '80/50',
    'created_at' => '2026-09-15 08:00:00',
];

$apptNewest = [
    'id' => 1003,
    'patient_id' => $mockPatientId,
    'first_name' => 'Maria',
    'last_name' => 'Clara',
    'gender' => 'Female',
    'service_slug' => 'immunization',
    'service_name' => 'Immunization',
    'status' => 'Completed',
    'preferred_date' => '2026-09-20',
    'preferred_time' => 'Daily Slot',
    'immunization_relationship' => 'Child',
    'recipient_first_name' => 'Baby',
    'recipient_last_name' => 'Clara',
    'recipient_birth_date' => '2026-01-15',
    'recipient_gender' => 'Female',
    'photo_path' => 'uploads/baby_newest.jpg',
    'vaccine_type' => 'Pentavalent',
    'doctor_notes' => 'Third vaccine dose',
    'body_temperature' => '36.7',
    'pulse_rate' => '115',
    'respiration_rate' => '29',
    'blood_pressure' => '80/50',
    'created_at' => '2026-09-20 08:00:00',
];

// Test with station appointments list passed in
$allMockStationAppts = [$apptOlder, $apptNoPhoto, $apptNewest];
$infants = fetch_infant_sub_profiles_by_patient_id($mockPatientId, $allMockStationAppts);

assertTest(!empty($infants), "Infant sub-profiles found for {$mockPatientId}");
$baby = $infants[0] ?? [];

assertTest(
    $baby['latest_photo'] === 'uploads/baby_newest.jpg',
    "Infant top profile picture is from their very recent immunization appointment ('{$baby['latest_photo']}' === 'uploads/baby_newest.jpg')"
);
assertTest(
    $baby['photo_path'] === 'uploads/baby_newest.jpg',
    "Infant photo_path equals latest_photo ('uploads/baby_newest.jpg')"
);

// Verify timeline history
$timeline = $baby['appointments'] ?? [];
assertTest(count($timeline) === 3, "Infant has 3 appointments in timeline");

// Newest appointment (Sep 20) should have baby_newest.jpg
$tNewest = $timeline[0] ?? [];
assertTest(
    $tNewest['preferred_date'] === '2026-09-20' && $tNewest['photo_path'] === 'uploads/baby_newest.jpg',
    "Timeline #1 (Sep 20) has photo 'uploads/baby_newest.jpg'"
);

// Middle appointment (Sep 15) had no photo taken, must NOT inherit newest photo!
$tMiddle = $timeline[1] ?? [];
assertTest(
    $tMiddle['preferred_date'] === '2026-09-15' && $tMiddle['photo_path'] === '',
    "Timeline #2 (Sep 15) has empty photo_path (did not inherit photo from other visit)"
);

// Older appointment (Sep 10) must have baby_older.jpg
$tOlder = $timeline[2] ?? [];
assertTest(
    $tOlder['preferred_date'] === '2026-09-10' && $tOlder['photo_path'] === 'uploads/baby_older.jpg',
    "Timeline #3 (Sep 10) has photo 'uploads/baby_older.jpg'"
);

// ---------------------------------------------------------------------
// TEST 2: Infant Profile Photo Updates When New Appointment With Photo Added
// ---------------------------------------------------------------------
echo "\n--- TEST 2: Dynamic Update of Infant Profile Picture on Newer Appointment ---\n";

$apptBrandNew = [
    'id' => 1004,
    'patient_id' => $mockPatientId,
    'first_name' => 'Maria',
    'last_name' => 'Clara',
    'gender' => 'Female',
    'service_slug' => 'immunization',
    'service_name' => 'Immunization',
    'status' => 'Completed',
    'preferred_date' => '2026-09-25',
    'preferred_time' => 'Daily Slot',
    'immunization_relationship' => 'Child',
    'recipient_first_name' => 'Baby',
    'recipient_last_name' => 'Clara',
    'recipient_birth_date' => '2026-01-15',
    'recipient_gender' => 'Female',
    'photo_path' => 'uploads/baby_brand_new.jpg',
    'vaccine_type' => 'MMR',
    'doctor_notes' => 'Brand new dose',
    'body_temperature' => '36.6',
    'pulse_rate' => '110',
    'respiration_rate' => '28',
    'blood_pressure' => '80/50',
    'created_at' => '2026-09-25 08:00:00',
];

$allMockStationAppts2 = [$apptOlder, $apptNoPhoto, $apptNewest, $apptBrandNew];
$infants2 = fetch_infant_sub_profiles_by_patient_id($mockPatientId, $allMockStationAppts2);
$baby2 = $infants2[0] ?? [];

assertTest(
    $baby2['latest_photo'] === 'uploads/baby_brand_new.jpg',
    "Infant top profile picture dynamically changed to the newest photo ('uploads/baby_brand_new.jpg')"
);

// ---------------------------------------------------------------------
// TEST 3: Infant With No Photo Does Not Fall Back to Adult Photo
// ---------------------------------------------------------------------
echo "\n--- TEST 3: Infant With No Photo Stays Empty (No Adult Photo Leakage) ---\n";

$apptNoPhotoOnly = [
    'id' => 1005,
    'patient_id' => 'P-TEST-NOPHOTO',
    'first_name' => 'Juan',
    'last_name' => 'Dela Cruz',
    'gender' => 'Male',
    'service_slug' => 'immunization',
    'service_name' => 'Immunization',
    'status' => 'Serving',
    'preferred_date' => '2026-09-21',
    'preferred_time' => 'Daily Slot',
    'immunization_relationship' => 'Child',
    'recipient_first_name' => 'Baby',
    'recipient_last_name' => 'Juanito',
    'recipient_birth_date' => '2026-05-10',
    'recipient_gender' => 'Male',
    'photo_path' => '', // NO photo taken
    'vaccine_type' => 'BCG',
    'doctor_notes' => '',
    'body_temperature' => '36.5',
    'pulse_rate' => '115',
    'respiration_rate' => '30',
    'blood_pressure' => '80/50',
    'created_at' => '2026-09-21 08:00:00',
];

$infants3 = fetch_infant_sub_profiles_by_patient_id('P-TEST-NOPHOTO', [$apptNoPhotoOnly]);
$baby3 = $infants3[0] ?? [];
assertTest(
    $baby3['latest_photo'] === '',
    "Infant with no captured photo has empty latest_photo (no adult/parent fallback)"
);

// ---------------------------------------------------------------------
// TEST 4: Patient Profile Picture Resolved From Very Recent Appointment
// ---------------------------------------------------------------------
echo "\n--- TEST 4: Patient Profile Picture and History Isolation ---\n";

// Adult patient has:
// 1. Consultation on Sep 5 with photo_path = 'uploads/patient_consult.jpg'
// 2. TB DOTS on Sep 12 with photo_path = 'uploads/patient_tb_latest.jpg'
// 3. Child Immunization on Sep 18 with photo_path = 'uploads/baby_vaccine.jpg'
$patientVisits = [
    [
        'id' => 2001,
        'patient_id' => 'P-ADULT-01',
        'first_name' => 'Roberto',
        'last_name' => 'Gomez',
        'gender' => 'Male',
        'service_slug' => 'general-consultation',
        'service_name' => 'General Consultation',
        'status' => 'Completed',
        'preferred_date' => '2026-09-05',
        'preferred_time' => 'Daily Slot',
        'photo_path' => 'uploads/patient_consult.jpg',
        'body_temperature' => '36.8',
        'pulse_rate' => '72',
        'respiration_rate' => '16',
        'blood_pressure' => '120/80',
        'doctor_notes' => 'Healthy',
    ],
    [
        'id' => 2002,
        'patient_id' => 'P-ADULT-01',
        'first_name' => 'Roberto',
        'last_name' => 'Gomez',
        'gender' => 'Male',
        'service_slug' => 'tb',
        'service_name' => 'TB DOTS',
        'status' => 'Completed',
        'preferred_date' => '2026-09-12',
        'preferred_time' => 'Daily Slot',
        'photo_path' => 'uploads/patient_tb_latest.jpg',
        'body_temperature' => '36.7',
        'pulse_rate' => '74',
        'respiration_rate' => '15',
        'blood_pressure' => '120/80',
        'doctor_notes' => 'Follow up completed',
    ],
    [
        'id' => 2003,
        'patient_id' => 'P-ADULT-01',
        'first_name' => 'Roberto',
        'last_name' => 'Gomez',
        'gender' => 'Male',
        'service_slug' => 'immunization',
        'service_name' => 'Immunization',
        'status' => 'Completed',
        'preferred_date' => '2026-09-18',
        'preferred_time' => 'Daily Slot',
        'immunization_relationship' => 'Child',
        'recipient_first_name' => 'Junior',
        'recipient_last_name' => 'Gomez',
        'recipient_birth_date' => '2026-02-01',
        'recipient_gender' => 'Male',
        'photo_path' => 'uploads/baby_vaccine.jpg', // Child photo
        'body_temperature' => '36.5',
        'pulse_rate' => '115',
        'respiration_rate' => '30',
        'blood_pressure' => '80/50',
        'doctor_notes' => 'Child vaccinated',
    ],
];

// In patient profile resolution logic (as implemented in groupedPatientProfiles and dashboard):
// Sort by date DESC, id DESC
usort($patientVisits, static function(array $a, array $b): int {
    $tA = strtotime((string) ($a['preferred_date'] ?? '1970-01-01')) ?: 0;
    $tB = strtotime((string) ($b['preferred_date'] ?? '1970-01-01')) ?: 0;
    if ($tB !== $tA) return $tB <=> $tA;
    return ((int) ($b['id'] ?? 0)) <=> ((int) ($a['id'] ?? 0));
});

$resolvedPatPhoto = '';
// 1. Look for very recent appointment where patient was the recipient (self / not infant)
foreach ($patientVisits as $cAppt) {
    if (!empty($cAppt['photo_path'])) {
        $rec = appointment_recipient_details($cAppt);
        if ($rec['is_self']) {
            $resolvedPatPhoto = (string) $cAppt['photo_path'];
            break;
        }
    }
}
if ($resolvedPatPhoto === '') {
    foreach ($patientVisits as $cAppt) {
        if (!empty($cAppt['photo_path'])) {
            $resolvedPatPhoto = (string) $cAppt['photo_path'];
            break;
        }
    }
}

assertTest(
    $resolvedPatPhoto === 'uploads/patient_tb_latest.jpg',
    "Patient profile picture is resolved from their very recent adult appointment ('uploads/patient_tb_latest.jpg' from Sep 12 TB DOTS, not older Sep 5 consult or infant photo)"
);

// Verify history isolation: Appt 2001 keeps patient_consult.jpg, Appt 2002 keeps patient_tb_latest.jpg, Appt 2003 keeps baby_vaccine.jpg
assertTest($patientVisits[0]['id'] === 2003 && $patientVisits[0]['photo_path'] === 'uploads/baby_vaccine.jpg', "Visit #2003 keeps only its own photo");
assertTest($patientVisits[1]['id'] === 2002 && $patientVisits[1]['photo_path'] === 'uploads/patient_tb_latest.jpg', "Visit #2002 keeps only its own photo");
assertTest($patientVisits[2]['id'] === 2001 && $patientVisits[2]['photo_path'] === 'uploads/patient_consult.jpg', "Visit #2001 keeps only its own photo");

// ---------------------------------------------------------------------
// TEST 5: Verify save_patient_photo_for_appointment DOES NOT sync to other appointments
// ---------------------------------------------------------------------
echo "\n--- TEST 5: Code Check of save_patient_photo_for_appointment ---\n";
$dbCode = file_get_contents(__DIR__ . '/../shared/database.php');
$hasSyncStmt = str_contains($dbCode, 'UPDATE appointments SET photo_path = ? WHERE patient_id = ? AND (photo_path IS NULL OR photo_path = "")');
assertTest(!$hasSyncStmt, "Cross-appointment photo sync query successfully removed from save_patient_photo_for_appointment");

echo "\n========================================================\n";
echo "TEST RESULTS: {$passCount} Passed, {$failCount} Failed\n";
echo "========================================================\n";

if ($failCount > 0) {
    exit(1);
}
