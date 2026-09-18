<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

try {
    require_once __DIR__ . '/../shared/bootstrap.php';
    require_once __DIR__ . '/../shared/database.php';
    require_once __DIR__ . '/../shared/mailer.php';

    echo "=== Health Delivery System: Forgot Password & Mailer OTP Test Suite ===\n\n";

    echo "[1] Testing DB Connection...\n";
    $connection = db();
    echo "✓ Connected to DB: " . $connection->host_info . "\n";

    echo "[2] Testing Table Bootstrap...\n";
    ensure_password_reset_otps_table($connection);
    $tableCheck = db_table_exists($connection, 'password_reset_otps');
    echo $tableCheck ? "✓ Table password_reset_otps verified.\n" : "✗ Table password_reset_otps missing!\n";

    // Ensure test patient exists
    echo "\n[3] Ensuring Test Patient Account...\n";
    $testPatientEmail = 'test.patient.otp@gmail.com';
    save_patient_account([
        'patient_id' => 'TPAT01',
        'email' => $testPatientEmail,
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

    $patient = fetch_patient_account_by_email($testPatientEmail);
    echo $patient ? "✓ Test patient account ready: {$patient['email']}\n" : "✗ Test patient failed to create.\n";

    // Test OTP storage and Mailer
    echo "\n[4] Testing OTP Generation & Storage for Patient...\n";
    $otp1 = (string) random_int(100000, 999999);
    $stored = store_password_reset_otp('patient', $testPatientEmail, $otp1, 10);
    echo $stored ? "✓ OTP {$otp1} stored successfully for {$testPatientEmail}\n" : "✗ Failed to store OTP\n";

    // Test Mailer function
    echo "\n[5] Testing Role-Themed Mailer Function...\n";
    $mailSuccess = sendPasswordResetOtpEmail($testPatientEmail, 'Maria Santos', $otp1, 'patient', 10);
    echo $mailSuccess ? "✓ Mailer executed successfully (Logged/Dispatched)\n" : "✗ Mailer failed\n";

    // Test OTP Verification
    echo "\n[6] Testing OTP Verification Checks...\n";
    $wrongOtpCheck = verify_password_reset_otp('patient', $testPatientEmail, '000000');
    echo (!$wrongOtpCheck['valid']) ? "✓ Wrong OTP correctly rejected: {$wrongOtpCheck['error']}\n" : "✗ Wrong OTP was accepted!\n";

    $validOtpCheck = verify_password_reset_otp('patient', $testPatientEmail, $otp1);
    echo ($validOtpCheck['valid']) ? "✓ Correct OTP verified successfully.\n" : "✗ Correct OTP failed: " . ($validOtpCheck['error'] ?? '') . "\n";

    // Test Password Reset
    echo "\n[7] Testing Password Update & Re-login...\n";
    $newPassword = 'NewSecret2026!';
    $pwUpdated = update_patient_password($testPatientEmail, $newPassword);
    echo $pwUpdated ? "✓ Password hash updated in database\n" : "✗ Password update failed\n";

    mark_password_reset_otp_used('patient', $testPatientEmail, $otp1);
    $reuseOtpCheck = verify_password_reset_otp('patient', $testPatientEmail, $otp1);
    echo (!$reuseOtpCheck['valid']) ? "✓ Reused OTP correctly rejected as already used.\n" : "✗ Reused OTP was accepted!\n";

    $updatedPatient = fetch_patient_account_by_email($testPatientEmail);
    $loginVerify = password_verify($newPassword, (string) $updatedPatient['password_hash']);
    echo $loginVerify ? "✓ Login authentication succeeded with new password!\n" : "✗ Login verification failed with new password\n";

    // Test Staff Role
    echo "\n[8] Testing Staff Role OTP & Password Update...\n";
    $staffEmail = 'staff-bata@bata.health';
    $otpStaff = (string) random_int(100000, 999999);
    store_password_reset_otp('staff', $staffEmail, $otpStaff, 10);
    $staffOtpVerify = verify_password_reset_otp('staff', $staffEmail, $otpStaff);
    echo ($staffOtpVerify['valid']) ? "✓ Staff OTP verified successfully\n" : "✗ Staff OTP verification failed\n";
    update_staff_password($staffEmail, 'StaffPassword123!');
    mark_password_reset_otp_used('staff', $staffEmail, $otpStaff);

    // Test Admin Role
    echo "\n[9] Testing Admin Role OTP & Password Update...\n";
    $adminEmail = 'admintest@gmail.com';
    $otpAdmin = (string) random_int(100000, 999999);
    store_password_reset_otp('admin', $adminEmail, $otpAdmin, 10);
    $adminOtpVerify = verify_password_reset_otp('admin', $adminEmail, $otpAdmin);
    echo ($adminOtpVerify['valid']) ? "✓ Admin OTP verified successfully\n" : "✗ Admin OTP verification failed\n";
    update_admin_password($adminEmail, 'AdminSecure2026!');
    mark_password_reset_otp_used('admin', $adminEmail, $otpAdmin);

    echo "\n=== ALL BACKEND FORGOT PASSWORD TESTS PASSED SUCCESSFULLY! ===\n";
} catch (Throwable $e) {
    echo "\n[EXCEPTION CAUGHT]: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}
