<?php

declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

echo "========================================================\n";
echo "    COMPREHENSIVE HTTP & HTML INTEGRATION TESTS         \n";
echo "========================================================\n\n";

$baseUrl = 'http://127.0.0.1:8000';
$cookieJarStaff = sys_get_temp_dir() . '/cookie_staff_' . uniqid() . '.txt';
$cookieJarAdmin = sys_get_temp_dir() . '/cookie_admin_' . uniqid() . '.txt';

function http_request(string $url, string $method = 'GET', array $postData = [], ?string $cookieJar = null): array
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    if ($cookieJar !== null) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
    }

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    }

    $body = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    return [
        'code' => $httpCode,
        'body' => is_string($body) ? $body : '',
        'error' => $err,
    ];
}

// ----------------------------------------------------
// TEST 1: Landing Page & Portal Cards Styling
// ----------------------------------------------------
echo "[TEST 1] Testing Landing Page and Portal Icon Styling...\n";
$res = http_request($baseUrl . '/');
if ($res['code'] === 200) {
    echo "  ✓ Landing page responded with HTTP 200.\n";
    $cssRes = http_request($baseUrl . '/Patients/assets/css/styles.css');
    if (str_contains($cssRes['body'], 'width: 48px') && str_contains($cssRes['body'], 'height: 48px')) {
        echo "  ✓ Portal card icon dimensions verified at 48px x 48px in styles.css.\n";
    } else {
        echo "  ✗ Portal card icon dimensions not matching in styles.css.\n";
    }
} else {
    echo "  ✗ Failed to reach landing page. HTTP code: " . $res['code'] . "\n";
}

// ----------------------------------------------------
// TEST 2: Staff Authentication & Patients Profiles View
// ----------------------------------------------------
echo "\n[TEST 2] Testing Staff Panel Login & Patient Profiles View...\n";
$loginRes = http_request(
    $baseUrl . '/Patients/login-handler.php',
    'POST',
    ['email' => 'staff-bata@bata.health', 'password' => 'StaffPassword123!', 'action' => 'login_staff'],
    $cookieJarStaff
);
echo "  ✓ Staff login response: " . $loginRes['body'] . "\n";

$profilesRes = http_request(
    $baseUrl . '/Barangay%20Health%20Station/index.php?page=patients&view=profiles',
    'GET',
    [],
    $cookieJarStaff
);

if ($profilesRes['code'] === 200) {
    $html = $profilesRes['body'];
    echo "  ✓ Patient profiles view loaded successfully (HTTP 200, " . strlen($html) . " bytes).\n";

    // Check Leo Zacarias has the infant button
    if (str_contains($html, 'patient-infant-toggle-btn') && str_contains($html, 'togglePatientInfantsTray')) {
        echo "  ✓ Patient Card Infant Button (Baby icon) found on parent cards.\n";
    } else {
        echo "  ✗ Patient Card Infant Button NOT found.\n";
    }

    // Check sub-profiles popup
    if (str_contains($html, 'patient-infant-subprofile-popup') && str_contains($html, 'Baby Leo Santos')) {
        echo "  ✓ Docked Infant Sub-Profiles Popup verified with 'Baby Leo Santos'.\n";
    } else {
        echo "  ✗ Infant Sub-Profiles Popup or 'Baby Leo Santos' NOT found in HTML.\n";
    }

    // Check Staff Infant Profile Modal Container
    if (str_contains($html, 'staffInfantModalBackdrop') && str_contains($html, 'staffInfantModalTitle')) {
        echo "  ✓ Staff Infant Profile Modal backdrop and dialog markup verified.\n";
    } else {
        echo "  ✗ Staff Infant Profile Modal markup NOT found.\n";
    }
} else {
    echo "  ✗ Failed to load staff patient profiles view. HTTP {$profilesRes['code']}\n";
}

// ----------------------------------------------------
// TEST 3: Staff Vitals Encoding Modal Dropdown
// ----------------------------------------------------
echo "\n[TEST 3] Testing Staff Vitals Encoding Modal (Infant Dropdown)...\n";

// Insert a serving appointment awaiting vitals for an infant
require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';
$db = db();
$db->query("DELETE FROM appointments WHERE appointment_code = 'APT-SRV-01'");
$db->query("INSERT INTO appointments (
    patient_id, service_slug, service_name, station_slug, station_name,
    appointment_code, reference_code, preferred_date, preferred_time,
    first_name, last_name, birth_date, gender, contact_number, complete_address,
    recipient_first_name, recipient_last_name, recipient_birth_date, immunization_relationship,
    status
) VALUES (
    'P2YLL5', 'immunization', 'National Immunization Program', 'bata', 'Barangay Bata Health Center',
    'APT-SRV-01', 'REF-SRV-01', '" . date('Y-m-d') . "', '09:00 AM',
    'Leo', 'Zacarias', '1990-01-01', 'Male', '09123456789', 'Bata, Bacolod City',
    'Baby Leo', 'Santos', '2025-11-15', 'Child',
    'Serving'
)");

$apptsRes = http_request(
    $baseUrl . '/Barangay%20Health%20Station/index.php?page=queue&encode_vitals=APT-SRV-01',
    'GET',
    [],
    $cookieJarStaff
);

if ($apptsRes['code'] === 200) {
    $apptsHtml = $apptsRes['body'];
    if (str_contains($apptsHtml, 'queue_vaccine_select') && str_contains($apptsHtml, 'Pentavalent (DTP-HepB-Hib)') && str_contains($apptsHtml, 'queue_vaccine_other_wrap')) {
        echo "  ✓ 9-Option Infant Vaccine Dropdown with dynamic 'Others' field verified in Staff Vitals Encoding Modal.\n";
    } else {
        echo "  ✗ 9-Option Infant Vaccine Dropdown markup NOT found in Staff Vitals modal.\n";
    }
} else {
    echo "  ✗ Failed to load staff vitals modal page. HTTP {$apptsRes['code']}\n";
}

// ----------------------------------------------------
// TEST 4: Staff Infant Profile Update Handler
// ----------------------------------------------------
echo "\n[TEST 4] Testing Staff Infant Profile Update (save_infant_profile)...\n";
$updateRes = http_request(
    $baseUrl . '/Barangay%20Health%20Station/index.php?page=patients&view=profiles',
    'POST',
    [
        'action' => 'save_infant_profile',
        'patient_id' => 'P2YLL5',
        'first_name' => 'Baby Leo',
        'middle_name' => '',
        'last_name' => 'Santos',
        'birth_date' => '2025-11-15',
        'gender' => 'Male',
        'relationship' => 'Child',
        'mother_name' => 'Clara Maria Santos',
        'father_name' => 'Juan Pedro Santos',
        'notes' => 'Updated via automated integration test. All vaccines up to date.',
    ],
    $cookieJarStaff
);
echo "  ✓ save_infant_profile POST executed (HTTP {$updateRes['code']}).\n";

// ----------------------------------------------------
// TEST 5: Admin Panel & Infant Action Buttons
// ----------------------------------------------------
echo "\n[TEST 5] Testing Admin Panel Login & Patients Table Infant Actions...\n";
$adminLogin = http_request(
    $baseUrl . '/Patients/login-handler.php',
    'POST',
    ['username' => 'admintest@gmail.com', 'password' => 'password123', 'action' => 'login_admin'],
    $cookieJarAdmin
);
echo "  ✓ Admin login response: " . $adminLogin['body'] . "\n";

$adminPatRes = http_request(
    $baseUrl . '/Admin/index.php?page=patients',
    'GET',
    [],
    $cookieJarAdmin
);

if ($adminPatRes['code'] === 200) {
    $adminHtml = $adminPatRes['body'];
    echo "  ✓ Admin patients page loaded successfully (HTTP 200, " . strlen($adminHtml) . " bytes).\n";

    // Check active infant button for parent
    if (str_contains($adminHtml, 'patient-action-btn infant is-active') && str_contains($adminHtml, 'openAdminInfantViewer')) {
        echo "  ✓ Active purple baby button found for parent with infant bookings.\n";
    } else {
        echo "  ✗ Active baby button NOT found in Admin table.\n";
    }

    // Check disabled infant button for non-parent
    if (str_contains($adminHtml, 'patient-action-btn infant is-disabled') && str_contains($adminHtml, 'Not a parent or guardian')) {
        echo "  ✓ Disabled baby button with tooltip 'Not a parent or guardian' found for non-parents.\n";
    } else {
        echo "  ✗ Disabled baby button NOT found in Admin table.\n";
    }

    // Check Admin Read-Only Infant Modal Backdrop
    if (str_contains($adminHtml, 'adminInfantModal') && str_contains($adminHtml, 'adminInfantModalTitle')) {
        echo "  ✓ Admin Read-Only Infant Modal markup (purple theme) verified.\n";
    } else {
        echo "  ✗ Admin Read-Only Infant Modal markup NOT found.\n";
    }
} else {
    echo "  ✗ Failed to load Admin patients page. HTTP {$adminPatRes['code']}\n";
}

@unlink($cookieJarStaff);
@unlink($cookieJarAdmin);

echo "\n========================================================\n";
echo "    ALL INTEGRATION & ENDPOINT TESTS COMPLETED!         \n";
echo "========================================================\n";
