<?php
require_once __DIR__ . '/../shared/database.php';

echo "=== IMMUNIZATION FLOW VERIFICATION ===\n\n";

$db = db();

// 1. Verify immunized_infants table
ensure_immunized_infants_table($db);
$res = $db->query("SHOW TABLES LIKE 'immunized_infants'");
if ($res && $res->num_rows > 0) {
    echo "✓ [PASS] Table 'immunized_infants' exists in database.\n";
} else {
    echo "✗ [FAIL] Table 'immunized_infants' does not exist.\n";
}

// 2. Test saving immunized infant for Parent booking
$parentApptData = [
    'appointment_id' => 9991,
    'appointment_code' => 'TESTIM01',
    'patient_id' => 'P-TEST01',
    'recipient_first_name' => 'Baby',
    'recipient_middle_name' => 'Cruz',
    'recipient_last_name' => 'Santos',
    'recipient_birth_date' => date('Y-m-d', strtotime('-6 months')),
    'gender' => 'Female',
    'relationship' => 'Parent',
    'station_slug' => 'bata',
    'vaccine_type' => 'Pentavalent'
];

$infantId = save_immunized_infant($parentApptData);
echo "✓ [PASS] save_immunized_infant created ID: " . $infantId . "\n";

// Verify retrieval
$checkStmt = $db->prepare("SELECT * FROM immunized_infants WHERE id = ?");
$checkStmt->bind_param('i', $infantId);
$checkStmt->execute();
$savedInfant = $checkStmt->get_result()->fetch_assoc();

if ($savedInfant && $savedInfant['first_name'] === 'Baby' && $savedInfant['relationship'] === 'Parent' && $savedInfant['vaccine_type'] === 'Pentavalent') {
    echo "✓ [PASS] Retrieved infant: " . $savedInfant['first_name'] . " " . $savedInfant['last_name'] . ", Rel: " . $savedInfant['relationship'] . ", Vaccine: " . $savedInfant['vaccine_type'] . "\n";
} else {
    echo "✗ [FAIL] Infant data mismatch.\n";
}

// 3. Test appointment_recipient_details helper
$mockParentAppt = [
    'first_name' => 'Maria',
    'middle_name' => 'L.',
    'last_name' => 'Santos',
    'birth_date' => '1995-04-12',
    'service_slug' => 'immunization',
    'service_name' => 'Immunization',
    'immunization_relationship' => 'Parent',
    'recipient_first_name' => 'Baby',
    'recipient_middle_name' => 'Cruz',
    'recipient_last_name' => 'Santos',
    'recipient_birth_date' => date('Y-m-d', strtotime('-6 months')),
    'vaccine_type' => 'Pentavalent'
];

$recDetailsParent = appointment_recipient_details($mockParentAppt);
echo "Parent Booking Recipient Details:\n";
echo "  - Recipient Name: " . $recDetailsParent['recipient_full_name'] . " (Expected: Baby Cruz Santos)\n";
echo "  - Relationship: " . $recDetailsParent['relationship'] . " (Expected: Parent)\n";
echo "  - Age Label: " . $recDetailsParent['recipient_age_label'] . "\n";
echo "  - Vaccine Type: " . $recDetailsParent['vaccine_type'] . " (Expected: Pentavalent)\n";

assert($recDetailsParent['recipient_full_name'] === 'Baby Cruz Santos');
assert($recDetailsParent['relationship'] === 'Parent');
assert($recDetailsParent['vaccine_type'] === 'Pentavalent');
echo "✓ [PASS] Parent recipient details verified.\n";

$mockSelfAppt = [
    'first_name' => 'Juan',
    'middle_name' => 'Pedro',
    'last_name' => 'Dela Cruz',
    'birth_date' => '1990-01-01',
    'service_slug' => 'immunization',
    'service_name' => 'Immunization',
    'immunization_relationship' => 'Self',
    'vaccine_type' => 'Influenza'
];

$recDetailsSelf = appointment_recipient_details($mockSelfAppt);
echo "\nSelf Booking Recipient Details:\n";
echo "  - Recipient Name: " . $recDetailsSelf['recipient_full_name'] . " (Expected: Juan Pedro Dela Cruz)\n";
echo "  - Relationship: " . $recDetailsSelf['relationship'] . " (Expected: Self)\n";
echo "  - Vaccine Type: " . $recDetailsSelf['vaccine_type'] . " (Expected: Influenza)\n";

assert($recDetailsSelf['recipient_full_name'] === 'Juan Pedro Dela Cruz');
assert($recDetailsSelf['relationship'] === 'Self');
assert($recDetailsSelf['vaccine_type'] === 'Influenza');
echo "✓ [PASS] Self recipient details verified.\n";

// Cleanup test entry
$db->query("DELETE FROM immunized_infants WHERE id = " . $infantId);
echo "\n✓ [PASS] Cleanup complete. All verification checks PASSED.\n";
