<?php

declare(strict_types=1);

require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';

echo "=== Testing HTTP AJAX Endpoints for Forgot Password on http://127.0.0.1:8000 ===\n\n";

function post_api(array $fields): array
{
    $ch = curl_init('http://127.0.0.1:8000/Patients/login-handler.php');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
    $raw = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $json = json_decode((string)$raw, true);
    if ($json === null) {
        throw new Exception("HTTP {$code} Invalid JSON: {$raw}");
    }
    return $json;
}

$connection = db();

// Ensure test patient
save_patient_account([
    'patient_id' => 'TPAT01',
    'email' => 'test.patient.otp@gmail.com',
    'password' => 'InitialPass123!',
    'first_name' => 'Maria',
    'last_name' => 'Santos',
    'birth_date' => '1995-05-15',
    'gender' => 'Female',
    'contact_number' => '09171234567',
    'complete_address' => 'Purok Masinadyahon, Barangay Bata, Bacolod City',
    'station_slug' => 'bata',
    'station_name' => 'Bata Barangay Health Station'
]);

// 1. Patient OTP Request
echo "[1] Testing Patient OTP Request via HTTP...\n";
$res1 = post_api([
    'action' => 'request_password_otp',
    'role' => 'patient',
    'email' => 'test.patient.otp@gmail.com'
]);
echo "Response: " . json_encode($res1) . "\n";
if (!$res1['success']) {
    throw new Exception("Patient OTP request failed");
}
echo "✓ Patient OTP request success.\n";

$resDb = $connection->query("SELECT otp_code FROM password_reset_otps WHERE email = 'test.patient.otp@gmail.com' AND role = 'patient' AND is_used = 0 ORDER BY id DESC LIMIT 1");
$rowDb = $resDb->fetch_assoc();
$patOtp = $rowDb['otp_code'];
echo "Retrieved OTP: {$patOtp}\n";

// 2. Patient OTP Verification and Reset
echo "\n[2] Testing Patient OTP Verification and Reset via HTTP...\n";
$res2 = post_api([
    'action' => 'verify_and_reset_password',
    'role' => 'patient',
    'email' => 'test.patient.otp@gmail.com',
    'otp' => $patOtp,
    'new_password' => 'PatientReset2026!',
    'confirm_password' => 'PatientReset2026!'
]);
echo "Response: " . json_encode($res2) . "\n";
if (!$res2['success']) {
    throw new Exception("Patient OTP reset failed");
}
echo "✓ Patient password reset success.\n";

// 3. Patient Login with New Password
echo "\n[3] Testing Patient Login with New Password...\n";
$res3 = post_api([
    'action' => 'login_patient',
    'email' => 'test.patient.otp@gmail.com',
    'password' => 'PatientReset2026!'
]);
echo "Response: " . json_encode($res3) . "\n";
if (!$res3['success'] || $res3['redirect'] !== 'dashboard.php') {
    throw new Exception("Patient login with new password failed");
}
echo "✓ Patient login with new password verified!\n";

// 4. Staff OTP Request and Reset
echo "\n[4] Testing Staff OTP Request and Reset via HTTP...\n";
$resStaffReq = post_api([
    'action' => 'request_password_otp',
    'role' => 'staff',
    'email' => 'staff-bata@bata.health'
]);
echo "Staff OTP Request Response: " . json_encode($resStaffReq) . "\n";

$resDbStaff = $connection->query("SELECT otp_code FROM password_reset_otps WHERE email = 'staff-bata@bata.health' AND role = 'staff' AND is_used = 0 ORDER BY id DESC LIMIT 1");
$rowDbStaff = $resDbStaff->fetch_assoc();
$staffOtp = $rowDbStaff['otp_code'];

$resStaffReset = post_api([
    'action' => 'verify_and_reset_password',
    'role' => 'staff',
    'email' => 'staff-bata@bata.health',
    'otp' => $staffOtp,
    'new_password' => 'StaffReset2026!',
    'confirm_password' => 'StaffReset2026!'
]);
echo "Staff Reset Response: " . json_encode($resStaffReset) . "\n";

$resStaffLogin = post_api([
    'action' => 'login_staff',
    'email' => 'staff-bata@bata.health',
    'password' => 'StaffReset2026!'
]);
echo "Staff Login Response: " . json_encode($resStaffLogin) . "\n";
if (!$resStaffLogin['success']) {
    throw new Exception("Staff login failed with new password");
}
echo "✓ Staff login with new password verified!\n";

// 5. Admin OTP Request and Reset
echo "\n[5] Testing Admin OTP Request and Reset via HTTP...\n";
$resAdminReq = post_api([
    'action' => 'request_password_otp',
    'role' => 'admin',
    'username' => 'admin'
]);
echo "Admin OTP Request Response: " . json_encode($resAdminReq) . "\n";

$resDbAdmin = $connection->query("SELECT otp_code FROM password_reset_otps WHERE email = 'admintest@gmail.com' AND role = 'admin' AND is_used = 0 ORDER BY id DESC LIMIT 1");
$rowDbAdmin = $resDbAdmin->fetch_assoc();
$adminOtp = $rowDbAdmin['otp_code'];

$resAdminReset = post_api([
    'action' => 'verify_and_reset_password',
    'role' => 'admin',
    'email' => 'admintest@gmail.com',
    'otp' => $adminOtp,
    'new_password' => 'AdminReset2026!',
    'confirm_password' => 'AdminReset2026!'
]);
echo "Admin Reset Response: " . json_encode($resAdminReset) . "\n";

$resAdminLogin = post_api([
    'action' => 'login_admin',
    'username' => 'admin',
    'password' => 'AdminReset2026!'
]);
echo "Admin Login Response: " . json_encode($resAdminLogin) . "\n";
if (!$resAdminLogin['success']) {
    throw new Exception("Admin login failed with new password");
}
echo "✓ Admin login with new password verified!\n";

echo "\n=======================================================\n";
echo "✓ ALL 3 ROLES HTTP OTP & PASSWORD RESETS FULLY VERIFIED!\n";
echo "=======================================================\n";
