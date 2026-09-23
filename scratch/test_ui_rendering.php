<?php
if (!function_exists('staff_icon')) {
    function staff_icon(string $name): string {
        return "<!-- icon:$name -->";
    }
}
require_once __DIR__ . '/../shared/database.php';

// Include render_patient_profile_body definition from Barangay Health Station/index.php
if (!function_exists('render_patient_profile_body')) {
    $lines = file(__DIR__ . '/../Barangay Health Station/index.php');
    $fnCode = implode('', array_slice($lines, 99, 224));
    eval($fnCode);
}

echo "=== Testing UI Rendering Logic for TB Chest X-Ray ===\n";

// 1. Test render_patient_profile_body with TB appointment having chest x-ray
$mockProfileWithXray = [
    'key' => 'P1',
    'patient_id' => 'P0001',
    'first_name' => 'Leo',
    'last_name' => 'Zacarias',
    'full_name' => 'Leo Zacarias',
    'photo_path' => '',
    'birth_date' => '2000-01-01',
    'age_label' => '26 yrs old',
    'gender' => 'Male',
    'contact_number' => '09123456789',
    'complete_address' => 'Bacolod City',
    'total_completed' => 1,
    'latest_appointment' => [
        'preferred_date' => '2026-09-01',
        'service_name' => 'TB DOTS',
    ],
    'appointments' => [
        [
            'id' => 8,
            'reference_code' => 'BK12345',
            'appointment_code' => 'D6MAZNKW',
            'service_slug' => 'tb',
            'service_name' => 'TB DOTS',
            'preferred_date' => '2026-09-01',
            'preferred_time' => 'Daily Slot',
            'body_temperature' => '37.2',
            'pulse_rate' => '75',
            'respiration_rate' => '15',
            'blood_pressure' => '120/80',
            'chest_xray' => 'Normal / Clear bilateral lung fields',
            'doctor_notes' => 'Follow up in two weeks.',
            'status' => 'Completed',
        ]
    ]
];

$station = ['name' => 'Bata Barangay Health Station', 'barangay' => 'Bata', 'slug' => 'bata'];

$htmlWithXray = render_patient_profile_body($mockProfileWithXray, $station);
assert(str_contains($htmlWithXray, 'Chest X-Ray Result:'), 'HTML contains Chest X-Ray Result label');
assert(str_contains($htmlWithXray, 'Normal / Clear bilateral lung fields'), 'HTML contains actual encoded x-ray value');
assert(str_contains($htmlWithXray, 'TB DOTS'), 'HTML contains TB DOTS badge');
echo "✓ Staff Patient Profile History correctly displays encoded Chest X-Ray result.\n";

// 2. Test render_patient_profile_body with non-TB appointment
$mockProfileDental = [
    'key' => 'P2',
    'patient_id' => 'P0002',
    'first_name' => 'Maria',
    'last_name' => 'Santos',
    'full_name' => 'Maria Santos',
    'photo_path' => '',
    'birth_date' => '1995-05-10',
    'age_label' => '31 yrs old',
    'gender' => 'Female',
    'contact_number' => '09987654321',
    'complete_address' => 'Bacolod City',
    'total_completed' => 1,
    'latest_appointment' => [
        'preferred_date' => '2026-09-02',
        'service_name' => 'Dental Services',
    ],
    'appointments' => [
        [
            'id' => 9,
            'reference_code' => 'BK12346',
            'appointment_code' => '4RFT9WC6',
            'service_slug' => 'dental',
            'service_name' => 'Dental Services',
            'preferred_date' => '2026-09-02',
            'preferred_time' => 'Daily Slot',
            'body_temperature' => '36.5',
            'pulse_rate' => '72',
            'respiration_rate' => '18',
            'blood_pressure' => '120/80',
            'chest_xray' => '',
            'doctor_notes' => 'Tooth extraction done.',
            'status' => 'Completed',
        ]
    ]
];

$htmlDental = render_patient_profile_body($mockProfileDental, $station);
assert(!str_contains($htmlDental, 'profile-xray-badge-box'), 'Dental service does not display Chest X-Ray badge');
echo "✓ Non-TB service does not display Chest X-Ray box.\n";

// 3. Test Staff Index Vitals Encoding markup
$staffIndexContent = file_get_contents(__DIR__ . '/../Barangay Health Station/index.php');
assert(str_contains($staffIndexContent, 'id="queue_chest_xray"'), 'Staff vitals modal has queue_chest_xray field ID');
assert(str_contains($staffIndexContent, 'name="chest_xray"'), 'Staff vitals modal has name="chest_xray" input');
assert(str_contains($staffIndexContent, '$isTbService'), 'Staff vitals modal has $isTbService condition check');
assert(str_contains($staffIndexContent, 'xray-summary-card'), 'Staff view appointment modal has xray-summary-card');
echo "✓ Staff vitals encoding modal and view modals contain chest x-ray markup.\n";

// 4. Test Admin Index markup
$adminIndexContent = file_get_contents(__DIR__ . '/../Admin/index.php');
assert(str_contains($adminIndexContent, '🩻 Chest X-Ray:'), 'Admin history table shows Chest X-Ray tag');
assert(str_contains($adminIndexContent, 'Chest X-Ray Result (TB DOTS)'), 'Admin consultation record modal shows Chest X-Ray Result card');
assert(str_contains($adminIndexContent, 'id="reportVisitXrayCard"'), 'Admin reports modal has reportVisitXrayCard');
assert(str_contains($adminIndexContent, 'data.chest_xray'), 'Admin reports JS populates data.chest_xray');
echo "✓ Admin patient history table and consultation inspection modal contain chest x-ray markup.\n";

echo "\nAll UI rendering tests PASSED successfully!\n";
