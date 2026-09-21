<?php
require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';

echo "=== 1. Direct database helper verification ===\n";

// Staff account test
$staff = fetch_staff_account_by_email('staff-bata@bata.health');
if (!$staff) {
    echo "FAIL: Staff account not found.\n";
    exit(1);
}
echo "✓ Fetched staff: ID {$staff['id']}, Name {$staff['staff_name']}\n";

$updateStaffRes = update_staff_account_details((int)$staff['id'], [
    'staff_name' => 'Nurse Maria Santos',
    'email' => 'staff-bata@bata.health',
    'birth_date' => '1992-08-14',
    'gender' => 'Female',
    'contact_number' => '09171234567',
    'home_address' => 'Purok Masinadyahon, Barangay Bata, Bacolod City',
    'recovery_email' => 'nurse.maria.recovery@gmail.com',
    'emergency_contact' => 'Juan Santos',
    'emergency_phone' => '09189876543',
    'password' => '',
]);
echo $updateStaffRes ? "✓ update_staff_account_details succeeded.\n" : "FAIL: update_staff_account_details failed.\n";

// Admin updating staff account test
$adminUpdateStaffRes = update_staff_account_by_admin((int)$staff['id'], [
    'station_slug' => 'bata',
    'staff_name' => 'Nurse Maria Santos',
    'email' => 'staff-bata@bata.health',
    'recovery_email' => 'nurse.maria.recovery@gmail.com',
    'contact_number' => '09171234567',
    'emergency_phone' => '09189876543',
    'password' => '',
]);
echo $adminUpdateStaffRes ? "✓ update_staff_account_by_admin succeeded.\n" : "FAIL: update_staff_account_by_admin failed.\n";

// Admin account update test
$admin = fetch_admin_account_by_email('admintest@gmail.com');
$updateAdminRes = update_admin_account_details((int)$admin['id'], [
    'admin_name' => 'Dr. Ma. Teresa Lim, MD',
    'office_name' => 'Central City Health Office - Bacolod',
    'email' => 'admintest@gmail.com',
    'contact_number' => '09198765432',
    'recovery_email' => 'admin.personal.recovery@gmail.com',
    'password' => '',
]);
echo $updateAdminRes ? "✓ update_admin_account_details succeeded.\n" : "FAIL: update_admin_account_details failed.\n";

echo "\n=== 2. HTTP End-to-End Staff Edit POST Simulation ===\n";
$cookieFile = __DIR__ . '/test_cookie_e2e.txt';
if (file_exists($cookieFile)) unlink($cookieFile);

$baseUrl = 'http://localhost/Health-Delivery-System-Latest';

// Staff Login
$ch = curl_init($baseUrl . '/Patients/login-handler.php');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'action' => 'login_staff',
        'email' => 'staff-bata@bata.health',
        'password' => 'StaffBata2026!',
    ]),
    CURLOPT_COOKIEJAR => $cookieFile,
    CURLOPT_COOKIEFILE => $cookieFile,
    CURLOPT_RETURNTRANSFER => true,
]);
$resp = curl_exec($ch);
curl_close($ch);
$loginJson = json_decode($resp, true);
echo "Staff login result: " . ($loginJson['success'] ? 'SUCCESS' : 'FAILED: ' . $resp) . "\n";

// Get BHS page & CSRF
$ch = curl_init($baseUrl . '/Barangay%20Health%20Station/index.php?page=dashboard');
curl_setopt_array($ch, [
    CURLOPT_COOKIEJAR => $cookieFile,
    CURLOPT_COOKIEFILE => $cookieFile,
    CURLOPT_RETURNTRANSFER => true,
]);
$bhsHtml = curl_exec($ch);
curl_close($ch);

preg_match('/name="csrf_token"\s+value="([^"]+)"/', $bhsHtml, $csrfMatches);
$csrfToken = $csrfMatches[1] ?? '';
echo "CSRF Token extracted: " . ($csrfToken ? "YES (" . substr($csrfToken, 0, 10) . "...)" : "NO") . "\n";

// Submit staff edit form
$ch = curl_init($baseUrl . '/Barangay%20Health%20Station/index.php');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'action' => 'update_staff_account',
        'csrf_token' => $csrfToken,
        'return_page' => 'dashboard',
        'staff_name' => 'Nurse Maria Santos',
        'email' => 'staff-bata@bata.health',
        'birth_date' => '1992-08-14',
        'gender' => 'Female',
        'contact_number' => '09171234567',
        'home_address' => 'Purok Masinadyahon, Barangay Bata, Bacolod City',
        'recovery_email' => 'nurse.maria.recovery@gmail.com',
        'emergency_contact' => 'Juan Santos',
        'emergency_phone' => '09189876543',
        'new_password' => '',
        'confirm_password' => '',
    ]),
    CURLOPT_COOKIEJAR => $cookieFile,
    CURLOPT_COOKIEFILE => $cookieFile,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
]);
$afterPostHtml = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Staff edit POST response HTTP Code: $httpCode\n";
if (strpos($afterPostHtml, 'Account details updated successfully') !== false) {
    echo "✓ SUCCESS: Flash banner 'Account details updated successfully!' displayed cleanly.\n";
} else {
    echo "FAIL: Flash banner not found. Response snippet:\n" . substr(strip_tags($afterPostHtml), 0, 300) . "\n";
}

echo "\n=== 3. HTTP End-to-End Admin Edit Staff POST Simulation ===\n";
$adminCookieFile = __DIR__ . '/test_admin_cookie.txt';
if (file_exists($adminCookieFile)) unlink($adminCookieFile);

// Admin Login
$ch = curl_init($baseUrl . '/Patients/login-handler.php');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'action' => 'login_admin',
        'username' => 'admintest@gmail.com',
        'password' => 'AdminSecure2026!',
    ]),
    CURLOPT_COOKIEJAR => $adminCookieFile,
    CURLOPT_COOKIEFILE => $adminCookieFile,
    CURLOPT_RETURNTRANSFER => true,
]);
$adminLoginResp = curl_exec($ch);
curl_close($ch);
$adminLoginJson = json_decode($adminLoginResp, true);
echo "Admin login result: " . ($adminLoginJson['success'] ? 'SUCCESS' : 'FAILED') . "\n";

// Get Admin page & CSRF
$ch = curl_init($baseUrl . '/Admin/index.php?page=users&user_panel=staff-list&user_station=bata');
curl_setopt_array($ch, [
    CURLOPT_COOKIEJAR => $adminCookieFile,
    CURLOPT_COOKIEFILE => $adminCookieFile,
    CURLOPT_RETURNTRANSFER => true,
]);
$adminHtml = curl_exec($ch);
curl_close($ch);

preg_match('/name="csrf_token"\s+value="([^"]+)"/', $adminHtml, $adminCsrfMatches);
$adminCsrfToken = $adminCsrfMatches[1] ?? '';
echo "Admin CSRF Token extracted: " . ($adminCsrfToken ? "YES (" . substr($adminCsrfToken, 0, 10) . "...)" : "NO") . "\n";

// Submit Admin edit staff form
$ch = curl_init($baseUrl . '/Admin/index.php');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'action' => 'update_staff_account_admin',
        'csrf_token' => $adminCsrfToken,
        'staff_id' => (int)$staff['id'],
        'station_slug' => 'bata',
        'staff_name' => 'Nurse Maria Santos',
        'email' => 'staff-bata@bata.health',
        'recovery_email' => 'nurse.maria.recovery@gmail.com',
        'contact_number' => '09171234567',
        'emergency_phone' => '09189876543',
        'password' => '',
        'user_station_redirect' => 'bata',
    ]),
    CURLOPT_COOKIEJAR => $adminCookieFile,
    CURLOPT_COOKIEFILE => $adminCookieFile,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
]);
$afterAdminPostHtml = curl_exec($ch);
$adminHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Admin edit staff POST response HTTP Code: $adminHttpCode\n";
if (strpos($afterAdminPostHtml, 'updated successfully') !== false) {
    echo "✓ SUCCESS: Flash banner 'Staff account for \"Nurse Maria Santos\" updated successfully.' displayed cleanly.\n";
} else {
    echo "FAIL: Admin Flash banner not found.\n";
}

echo "\nAll Verification tests completed!\n";
