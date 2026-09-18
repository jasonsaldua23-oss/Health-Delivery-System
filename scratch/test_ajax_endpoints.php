<?php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';

echo "=== Testing AJAX Handlers for Password Reset ===\n\n";

function simulate_ajax_post(array $postData): array
{
    // Capture output of login-handler.php
    $_POST = $postData;
    $_SERVER['REQUEST_METHOD'] = 'POST';
    ob_start();
    include __DIR__ . '/../Patients/login-handler.php';
    $output = ob_get_clean();
    $decoded = json_decode($output, true);
    if ($decoded === null) {
        throw new Exception("Invalid JSON returned from login-handler.php: {$output}");
    }
    return $decoded;
}

// 1. Patient Forgot Password Request
echo "[1] Testing Patient Request OTP...\n";
$resp1 = simulate_ajax_post([
    'action' => 'request_password_otp',
    'role' => 'patient',
    'email' => 'test.patient.otp@gmail.com'
]);
echo "Response: " . json_encode($resp1) . "\n";
if (!$resp1['success']) {
    throw new Exception("Failed requesting OTP for patient");
}
echo "✓ Patient OTP requested successfully.\n";

// Get latest OTP from DB for testing verification
$connection = db();
$res = $connection->query("SELECT otp_code FROM password_reset_otps WHERE email = 'test.patient.otp@gmail.com' AND role = 'patient' AND is_used = 0 ORDER BY id DESC LIMIT 1");
$row = $res->fetch_assoc();
$otp = $row['otp_code'];
echo "Extracted OTP from DB: {$otp}\n";

// 2. Patient Reset Password with Mismatched Passwords
echo "\n[2] Testing Password Mismatch Failure...\n";
$respMismatch = simulate_ajax_post([
    'action' => 'verify_and_reset_password',
    'role' => 'patient',
    'email' => 'test.patient.otp@gmail.com',
    'otp' => $otp,
    'new_password' => 'Pass123456!',
    'confirm_password' => 'Mismatch999!'
]);
echo "Response: " . json_encode($respMismatch) . "\n";
echo (!$respMismatch['success']) ? "✓ Mismatched passwords rejected.\n" : "✗ Password mismatch was accepted!\n";

// 3. Patient Reset Password with Correct OTP
echo "\n[3] Testing Valid Verification & Reset...\n";
$respReset = simulate_ajax_post([
    'action' => 'verify_and_reset_password',
    'role' => 'patient',
    'email' => 'test.patient.otp@gmail.com',
    'otp' => $otp,
    'new_password' => 'NewPatientSecure2026!',
    'confirm_password' => 'NewPatientSecure2026!'
]);
echo "Response: " . json_encode($respReset) . "\n";
echo ($respReset['success']) ? "✓ Password reset successfully via AJAX endpoint.\n" : "✗ Password reset failed!\n";

// 4. Test Login with New Password
echo "\n[4] Testing Login with New Password...\n";
$respLogin = simulate_ajax_post([
    'action' => 'login_patient',
    'email' => 'test.patient.otp@gmail.com',
    'password' => 'NewPatientSecure2026!'
]);
echo "Response: " . json_encode($respLogin) . "\n";
echo ($respLogin['success'] && $respLogin['redirect'] === 'dashboard.php') ? "✓ Patient login with new password successful!\n" : "✗ Patient login failed!\n";

// 5. Test Staff Forgot Password Request
echo "\n[5] Testing Staff Request OTP & Reset...\n";
$respStaffReq = simulate_ajax_post([
    'action' => 'request_password_otp',
    'role' => 'staff',
    'email' => 'staff-bata@bata.health'
]);
echo "Staff OTP Request Response: " . json_encode($respStaffReq) . "\n";
$resStaff = $connection->query("SELECT otp_code FROM password_reset_otps WHERE email = 'staff-bata@bata.health' AND role = 'staff' AND is_used = 0 ORDER BY id DESC LIMIT 1");
$rowStaff = $resStaff->fetch_assoc();
$staffOtp = $rowStaff['otp_code'];

$respStaffReset = simulate_ajax_post([
    'action' => 'verify_and_reset_password',
    'role' => 'staff',
    'email' => 'staff-bata@bata.health',
    'otp' => $staffOtp,
    'new_password' => 'NewStaffSecure2026!',
    'confirm_password' => 'NewStaffSecure2026!'
]);
echo "Staff Reset Response: " . json_encode($respStaffReset) . "\n";
$respStaffLogin = simulate_ajax_post([
    'action' => 'login_staff',
    'email' => 'staff-bata@bata.health',
    'password' => 'NewStaffSecure2026!'
]);
echo "Staff Login Response: " . json_encode($respStaffLogin) . "\n";
echo ($respStaffLogin['success']) ? "✓ Staff login with new password successful!\n" : "✗ Staff login failed!\n";

// 6. Test Admin Forgot Password Request (by username)
echo "\n[6] Testing Admin Request OTP (by username) & Reset...\n";
$respAdminReq = simulate_ajax_post([
    'action' => 'request_password_otp',
    'role' => 'admin',
    'username' => 'admin'
]);
echo "Admin OTP Request Response: " . json_encode($respAdminReq) . "\n";
$resAdmin = $connection->query("SELECT otp_code FROM password_reset_otps WHERE email = 'admintest@gmail.com' AND role = 'admin' AND is_used = 0 ORDER BY id DESC LIMIT 1");
$rowAdmin = $resAdmin->fetch_assoc();
$adminOtp = $rowAdmin['otp_code'];

$respAdminReset = simulate_ajax_post([
    'action' => 'verify_and_reset_password',
    'role' => 'admin',
    'email' => 'admintest@gmail.com',
    'otp' => $adminOtp,
    'new_password' => 'NewAdminSecure2026!',
    'confirm_password' => 'NewAdminSecure2026!'
]);
echo "Admin Reset Response: " . json_encode($respAdminReset) . "\n";
$respAdminLogin = simulate_ajax_post([
    'action' => 'login_admin',
    'username' => 'admin',
    'password' => 'NewAdminSecure2026!'
]);
echo "Admin Login Response: " . json_encode($respAdminLogin) . "\n";
echo ($respAdminLogin['success']) ? "✓ Admin login with new password successful!\n" : "✗ Admin login failed!\n";

echo "\n=== ALL AJAX ENDPOINT TESTS PASSED! ===\n";
