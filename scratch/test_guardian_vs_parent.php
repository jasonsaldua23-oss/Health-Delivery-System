<?php

declare(strict_types=1);

require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';

echo "========================================================\n";
echo "    TESTING GUARDIAN VS PARENT ROLE DIFFERENTIATION     \n";
echo "========================================================\n\n";

$db = db();

// Case 1: Resolve roles
echo "[CASE 1] Unit Test: resolve_infant_guardian_role_details()\n";

$g1 = resolve_infant_guardian_role_details('Guardian', 'Maria Ramos', 'Female');
assert($g1['is_guardian'] === true, 'g1 is_guardian');
assert($g1['is_parent'] === false, 'g1 is_parent');
assert($g1['guardian_name'] === 'Maria Ramos', 'g1 guardian_name');
assert($g1['default_mother'] === '', 'g1 default_mother must be empty');
assert($g1['default_father'] === '', 'g1 default_father must be empty');
echo "  ✓ 'Guardian' (Female): Correctly resolved as Guardian without assigning as Mother.\n";

$g2 = resolve_infant_guardian_role_details('Legal Guardian', 'Carlos Dalisay', 'Male');
assert($g2['is_guardian'] === true, 'g2 is_guardian');
assert($g2['is_parent'] === false, 'g2 is_parent');
assert($g2['guardian_name'] === 'Carlos Dalisay', 'g2 guardian_name');
assert($g2['default_father'] === '', 'g2 default_father must be empty');
echo "  ✓ 'Legal Guardian' (Male): Correctly resolved as Guardian without assigning as Father.\n";

$g3 = resolve_infant_guardian_role_details('Aunt / Relative', 'Tessie Lopez', 'Female');
assert($g3['is_guardian'] === true, 'g3 is_guardian');
assert($g3['guardian_name'] === 'Tessie Lopez', 'g3 guardian_name');
echo "  ✓ 'Aunt / Relative': Correctly classified under Guardian role.\n";

$p1 = resolve_infant_guardian_role_details('Mother', 'Elena Gomez', 'Female');
assert($p1['is_parent'] === true, 'p1 is_parent');
assert($p1['is_guardian'] === false, 'p1 is_guardian');
assert($p1['default_mother'] === 'Elena Gomez', 'p1 default_mother');
echo "  ✓ 'Mother': Correctly resolved as Mother.\n";

$p2 = resolve_infant_guardian_role_details('Child', 'Leo Zacarias', 'Male');
assert($p2['is_parent'] === true, 'p2 is_parent');
assert($p2['default_father'] === 'Leo Zacarias', 'p2 default_father');
echo "  ✓ 'Child' (Male Account): Correctly resolved as Father.\n";

// Case 2: Database integration test with an account registered as a Guardian
echo "\n[CASE 2] Database Integration Test: Guardian Account with Infant Booking\n";

// Clean test records
$db->query("DELETE FROM appointments WHERE patient_id = 'P-GDN-TEST' OR reference_code LIKE 'REF-GDN-%'");
$db->query("DELETE FROM infant_profiles WHERE patient_id = 'P-GDN-TEST'");

$refCode = 'REF-GDN-' . uniqid();
$apptCode = 'TEST-GDN-' . uniqid();

try {
    $db->query("INSERT INTO appointments (
        patient_id, service_slug, service_name, station_slug, station_name,
        appointment_code, reference_code, preferred_date, preferred_time,
        first_name, last_name, birth_date, gender, contact_number, complete_address,
        recipient_first_name, recipient_last_name, recipient_birth_date, immunization_relationship,
        status
    ) VALUES (
        'P-GDN-TEST', 'immunization', 'National Immunization Program', 'bata', 'Barangay Bata Health Center',
        '{$apptCode}', '{$refCode}', '" . date('Y-m-d') . "', '10:00 AM',
        'Teresa', 'Montelibano', '1985-05-20', 'Female', '09998887777', 'Bata, Bacolod City',
        'Baby', 'Mateo', '2025-08-10', 'Guardian',
        'Completed'
    )");
} catch (\Throwable $e) {
    echo "SQL ERROR: " . $e->getMessage() . "\n";
    exit(1);
}


$infants = fetch_infant_sub_profiles_by_patient_id('P-GDN-TEST');
if (empty($infants)) {
    echo "NO INFANTS FOUND FOR P-GDN-TEST\n";
    exit(1);
}
$mateo = $infants[0];


assert($mateo['is_guardian'] === true, 'Mateo is_guardian');
assert($mateo['is_parent'] === false, 'Mateo is_parent');
assert($mateo['role_type'] === 'Guardian', 'Mateo role_type');
assert($mateo['role_label'] === 'Registered Guardian', 'Mateo role_label');
assert($mateo['guardian_name'] === 'Teresa Montelibano', 'Mateo guardian_name matches account');
assert($mateo['mother_name'] === '', 'Mateo mother_name should NOT be Teresa Montelibano');
assert($mateo['father_name'] === '', 'Mateo father_name should NOT be Teresa Montelibano');

echo "  ✓ Infant sub-profile under Guardian successfully generated.\n";
echo "    - Full Name: {$mateo['full_name']}\n";
echo "    - Role: {$mateo['role_label']} (is_guardian=true, is_parent=false)\n";
echo "    - Guardian: {$mateo['guardian_name']}\n";
echo "    - Mother Name: '{$mateo['mother_name']}' (Blank - not erroneously filled!)\n";
echo "    - Father Name: '{$mateo['father_name']}' (Blank - not erroneously filled!)\n";

// Case 3: Saving distinct biological mother/father via save_or_update_infant_profile
echo "\n[CASE 3] Updating infant details with distinct biological parents\n";
$savedId = save_or_update_infant_profile([
    'id' => $mateo['id'],
    'patient_id' => 'P-GDN-TEST',
    'first_name' => 'Baby',
    'last_name' => 'Mateo',
    'birth_date' => '2025-08-10',
    'relationship' => 'Guardian',
    'guardian_name' => 'Teresa Montelibano',
    'mother_name' => 'Angelica Rivera',
    'father_name' => 'Marco Montelibano',
    'notes' => 'Guardian is maternal aunt.',
]);

$recheck = fetch_infant_sub_profiles_by_patient_id('P-GDN-TEST')[0];
assert($recheck['is_guardian'] === true, 'Recheck is_guardian');
assert($recheck['guardian_name'] === 'Teresa Montelibano', 'Recheck guardian_name');
assert($recheck['mother_name'] === 'Angelica Rivera', 'Recheck mother_name');
assert($recheck['father_name'] === 'Marco Montelibano', 'Recheck father_name');
assert($recheck['custom_notes'] === 'Guardian is maternal aunt.', 'Recheck custom_notes');

echo "  ✓ Saved and verified distinct Guardian, Mother, and Father.\n";
echo "    - Guardian: {$recheck['guardian_name']}\n";
echo "    - Mother: {$recheck['mother_name']}\n";
echo "    - Father: {$recheck['father_name']}\n";
echo "    - Notes: {$recheck['custom_notes']}\n";

// Clean up test records
$db->query("DELETE FROM appointments WHERE patient_id = 'P-GDN-TEST' OR reference_code LIKE 'REF-GDN-%'");
$db->query("DELETE FROM infant_profiles WHERE patient_id = 'P-GDN-TEST'");

echo "\n========================================================\n";
echo "    ALL GUARDIAN VS PARENT TESTS PASSED (100%)!         \n";
echo "========================================================\n";
