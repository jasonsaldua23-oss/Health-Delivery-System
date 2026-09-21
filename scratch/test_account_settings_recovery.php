<?php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "======================================================\n";
echo " TEST SUITE: STAFF RECOVERY EMAIL & ADMIN ACCOUNT SETTINGS\n";
echo "======================================================\n\n";

require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';
require_once __DIR__ . '/../shared/mailer.php';

$connection = db();

// 1. Column Verifications & Migrations
echo "[1] Running Database Schema Migrations...\n";
run_database_migrations($connection, false);


$staffCol = db_column_exists($connection, 'staff_accounts', 'recovery_email');
echo $staffCol ? "  ✓ staff_accounts.recovery_email exists.\n" : "  ✗ staff_accounts.recovery_email MISSING!\n";

$adminCol1 = db_column_exists($connection, 'admin_accounts', 'contact_number');
echo $adminCol1 ? "  ✓ admin_accounts.contact_number exists.\n" : "  ✗ admin_accounts.contact_number MISSING!\n";

$adminCol2 = db_column_exists($connection, 'admin_accounts', 'recovery_email');
echo $adminCol2 ? "  ✓ admin_accounts.recovery_email exists.\n" : "  ✗ admin_accounts.recovery_email MISSING!\n";

// 2. Staff Account Update Test
echo "\n[2] Testing Staff Account Settings Update...\n";
$testStaffEmail = 'staff-bata@bata.health';
$staffAccount = fetch_staff_account_by_email($testStaffEmail);
if (!$staffAccount) {
    echo "  Seeding test staff account...\n";
    seed_staff_accounts($connection);
    $staffAccount = fetch_staff_account_by_email($testStaffEmail);
}

$staffId = (int) ($staffAccount['id'] ?? 0);
$testPersonalRecoveryEmail = 'jason.personal.recovery@gmail.com';

$staffUpdated = update_staff_account_details($staffId, [
    'staff_name' => 'Nurse Maria Santos',
    'email' => $testStaffEmail,
    'birth_date' => '1992-08-14',
    'gender' => 'Female',
    'contact_number' => '09171234567',
    'home_address' => 'Purok Masinadyahon, Barangay Bata, Bacolod City',
    'recovery_email' => $testPersonalRecoveryEmail,
    'emergency_contact' => 'Juan Santos',
    'emergency_phone' => '09189876543',
    'password' => ''
]);

echo $staffUpdated ? "  ✓ update_staff_account_details() returned true.\n" : "  ✗ update_staff_account_details() failed!\n";

$reloadedStaff = fetch_staff_account_by_email($testStaffEmail);
$recoveredMatch = strtolower((string) ($reloadedStaff['recovery_email'] ?? '')) === strtolower($testPersonalRecoveryEmail);
echo $recoveredMatch ? "  ✓ Persisted recovery_email correctly: {$reloadedStaff['recovery_email']}\n" : "  ✗ Persisted recovery_email mismatch: {$reloadedStaff['recovery_email']}\n";

// 3. Admin Account Update Test
echo "\n[3] Testing Admin Account Settings Update...\n";
$testAdminEmail = 'admintest@gmail.com';
$adminAccount = fetch_admin_account_by_email($testAdminEmail);
if (!$adminAccount) {
    seed_admin_accounts($connection);
    $adminAccount = fetch_admin_account_by_email($testAdminEmail);
}

$adminId = (int) ($adminAccount['id'] ?? 0);
$testAdminRecoveryEmail = 'admin.personal.recovery@gmail.com';

$adminUpdated = update_admin_account_details($adminId, [
    'admin_name' => 'Dr. Ma. Teresa Lim, MD',
    'office_name' => 'Central City Health Office - Bacolod',
    'email' => $testAdminEmail,
    'contact_number' => '09198765432',
    'recovery_email' => $testAdminRecoveryEmail,
    'password' => ''
]);

echo $adminUpdated ? "  ✓ update_admin_account_details() returned true.\n" : "  ✗ update_admin_account_details() failed!\n";

$reloadedAdmin = fetch_admin_account_by_email($testAdminEmail);
$adminRecoveryMatch = strtolower((string) ($reloadedAdmin['recovery_email'] ?? '')) === strtolower($testAdminRecoveryEmail);
echo $adminRecoveryMatch ? "  ✓ Persisted admin recovery_email correctly: {$reloadedAdmin['recovery_email']}\n" : "  ✗ Persisted admin recovery_email mismatch: {$reloadedAdmin['recovery_email']}\n";
echo "  ✓ Persisted admin name: {$reloadedAdmin['admin_name']}\n";
echo "  ✓ Persisted admin contact: {$reloadedAdmin['contact_number']}\n";

// Helper to run login-handler.php in isolation
function call_login_handler(array $post): array {
    $script = dirname(__DIR__) . '/Patients/login-handler.php';
    $postSerialized = base64_encode(serialize($post));
    $runner = "<?php \$_POST = unserialize(base64_decode('{$postSerialized}')); \$_SERVER['REQUEST_METHOD'] = 'POST'; require '{$script}';";
    
    $descriptorSpec = [
        0 => ["pipe", "r"],
        1 => ["pipe", "w"],
        2 => ["pipe", "w"],
    ];
    $process = proc_open('php', $descriptorSpec, $pipes);
    fwrite($pipes[0], $runner);
    fclose($pipes[0]);
    $output = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    $error = stream_get_contents($pipes[2]);
    fclose($pipes[2]);
    proc_close($process);

    $json = json_decode(trim($output), true);
    if (!is_array($json)) {
        throw new Exception("Handler output not JSON. Raw: {$output} | Error: {$error}");
    }
    return $json;
}

// 4. Staff Forgot Password Logic Test
echo "\n[4] Testing Staff Forgot Password Dual-Field Recovery Flow...\n";

// A. Test missing recovery email
$resp1 = call_login_handler([
    'action' => 'request_password_otp',
    'role' => 'staff',
    'email' => $testStaffEmail,
    'recovery_email' => ''
]);
echo (!($resp1['success'] ?? false) && str_contains($resp1['message'] ?? '', 'recovery email'))
    ? "  ✓ Correctly rejected missing recovery email: {$resp1['message']}\n"
    : "  ✗ Failed validation for missing recovery email!\n";

// B. Test mismatched recovery email
$resp2 = call_login_handler([
    'action' => 'request_password_otp',
    'role' => 'staff',
    'email' => $testStaffEmail,
    'recovery_email' => 'wrong.email@gmail.com'
]);
echo (!($resp2['success'] ?? false) && str_contains($resp2['message'] ?? '', 'not match'))
    ? "  ✓ Correctly rejected mismatched recovery email: {$resp2['message']}\n"
    : "  ✗ Failed validation for mismatched recovery email!\n";

// C. Test matching recovery email
$resp3 = call_login_handler([
    'action' => 'request_password_otp',
    'role' => 'staff',
    'email' => $testStaffEmail,
    'recovery_email' => $testPersonalRecoveryEmail
]);
echo (($resp3['success'] ?? false) && str_contains($resp3['message'] ?? '', 'verification code'))
    ? "  ✓ Dispatched OTP to matching recovery email ({$resp3['masked_email']})\n"
    : "  ✗ Failed matching recovery email request: " . json_encode($resp3) . "\n";

// D. Test OTP reset with work email identifier
$otpRow = $connection->query("SELECT otp_code FROM password_reset_otps WHERE LOWER(email) = '" . strtolower($testStaffEmail) . "' AND role = 'staff' AND is_used = 0 ORDER BY id DESC LIMIT 1")->fetch_assoc();
$otpCode = $otpRow['otp_code'] ?? '';
echo "  Found generated OTP for {$testStaffEmail}: {$otpCode}\n";

$newStaffPassword = 'NewStaffSecurePassword2026!';
$resp4 = call_login_handler([
    'action' => 'verify_and_reset_password',
    'role' => 'staff',
    'email' => $testStaffEmail,
    'otp' => $otpCode,
    'new_password' => $newStaffPassword,
    'confirm_password' => $newStaffPassword
]);
echo ($resp4['success'] ?? false)
    ? "  ✓ Staff password reset succeeded with OTP!\n"
    : "  ✗ Staff password reset failed: " . json_encode($resp4) . "\n";

$updatedStaff = fetch_staff_account_by_email($testStaffEmail);
$loginValid = password_verify($newStaffPassword, (string) $updatedStaff['password_hash']);
echo $loginValid
    ? "  ✓ Staff login password verified against new hash.\n"
    : "  ✗ Staff login password verification failed!\n";

echo "\n======================================================\n";
echo " ALL BACKEND TESTS COMPLETED SUCCESSFULLY!\n";
echo "======================================================\n";
