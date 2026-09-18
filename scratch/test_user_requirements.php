<?php

declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';

echo "========================================================\n";
echo "    VERIFYING USER REQUIREMENTS IMPLEMENTATION          \n";
echo "========================================================\n\n";

$db = db();

// ----------------------------------------------------
// TEST 1: Verify Infant Button Has Only Icon (No 'Infants' text)
// ----------------------------------------------------
echo "[TEST 1] Testing Infant Button on Patient Cards (Icon only, no text)...\n";
$staffFile = file_get_contents(__DIR__ . '/../Barangay Health Station/index.php');
if (str_contains($staffFile, 'class="patient-infant-toggle-btn"') && !str_contains($staffFile, '<span>Infants (')) {
    echo "  ✓ Staff patient card infant button contains only the baby icon (text 'Infants' removed).\n";
} else {
    echo "  ✗ Infant button still contains text 'Infants'.\n";
    exit(1);
}

// ----------------------------------------------------
// TEST 2: Verify Sub-Profile Flickering Fix (Live Sync Guard)
// ----------------------------------------------------
echo "\n[TEST 2] Testing Sub-Profile Flickering Fix...\n";
if (str_contains($staffFile, 'hasOpenInfantTray') && str_contains($staffFile, 'if (isCameraActive || isTyping || isModalOpen || isEventModalOpen || isUnattendedModalOpen || isProfileModalOpen || isStaffInfantModalOpen || hasOpenInfantTray) return;')) {
    echo "  ✓ Background live sync script guards against open sub-profile popups (hasOpenInfantTray prevents DOM refresh and flicker).\n";
} else {
    echo "  ✗ hasOpenInfantTray guard missing from live sync script.\n";
    exit(1);
}

// ----------------------------------------------------
// TEST 3: Verify Infant Profile Photo Resolution (Top Avatar & Appointment History)
// ----------------------------------------------------
echo "\n[TEST 3] Testing Infant Profile Photo Resolution...\n";
$infants = fetch_infant_sub_profiles_by_patient_id('P2YLL5');
assert(!empty($infants), 'Infants found for P2YLL5');
$babyLeo = $infants[0];

echo "  ✓ Infant Name: {$babyLeo['full_name']}\n";
echo "  ✓ Top Avatar Photo Path: '{$babyLeo['latest_photo']}'\n";
assert(!empty($babyLeo['latest_photo']), 'Baby Leo has latest_photo resolved from parent/appointments');

$firstAppt = $babyLeo['appointments'][0] ?? null;
assert($firstAppt !== null, 'Infant has appointments');
echo "  ✓ Appointment #{$firstAppt['id']} Photo Path: '{$firstAppt['photo_path']}'\n";
assert(!empty($firstAppt['photo_path']), 'Appointment record has photo_path populated for timeline rendering');

// ----------------------------------------------------
// TEST 4: Verify Infant Immunization Vitals Extra Fields (Height and Weight)
// ----------------------------------------------------
echo "\n[TEST 4] Testing Infant Immunization Vitals Extra Fields (Height and Weight)...\n";
// 1. Check schema in database
$colRes = $db->query("SHOW COLUMNS FROM appointments LIKE 'height'");
assert($colRes->num_rows === 1, 'height column exists in appointments table');
$colRes2 = $db->query("SHOW COLUMNS FROM appointments LIKE 'weight'");
assert($colRes2->num_rows === 1, 'weight column exists in appointments table');
echo "  ✓ Database columns 'height' and 'weight' verified in appointments table.\n";

// 2. Check HTML rendering in Staff Vitals modal
if (str_contains($staffFile, 'name="height"') && str_contains($staffFile, 'name="weight"') && str_contains($staffFile, '$isVaccination && $isNonSelf')) {
    echo "  ✓ Height and Weight input fields verified in Staff Vitals Encoding Modal under infant immunization condition.\n";
} else {
    echo "  ✗ Height and Weight fields missing or not conditional in Staff Vitals modal.\n";
    exit(1);
}

// 3. Test saving Height and Weight via save_appointment_clinical_details
$testApptId = (int) $firstAppt['id'];
$saveOk = save_appointment_clinical_details($testApptId, [
    'body_temperature' => '36.8',
    'pulse_rate' => '110',
    'respiration_rate' => '28',
    'blood_pressure' => '90/60',
    'height' => '68 cm',
    'weight' => '7.5 kg',
    'vaccine_type' => 'Pentavalent (DTP-HepB-Hib)',
    'doctor_notes' => 'Healthy infant, vitals and measurements normal.',
]);

assert($saveOk === true, 'save_appointment_clinical_details succeeded with height and weight');
$recheckAppt = fetch_appointment_by_id($testApptId);
assert($recheckAppt['height'] === '68 cm', 'Height saved correctly');
assert($recheckAppt['weight'] === '7.5 kg', 'Weight saved correctly');
echo "  ✓ Height ('{$recheckAppt['height']}') and Weight ('{$recheckAppt['weight']}') successfully saved and retrieved from DB.\n";

echo "\n========================================================\n";
echo "    ALL 4 USER REQUIREMENTS VERIFIED (100%)!            \n";
echo "========================================================\n";
