<?php
// Start test script
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/../shared/database.php';

echo "=== Testing Staff Account Edit Logic directly ===\n";

// Fetch an existing staff account
$staff = fetch_staff_account_by_email('staff-bata@bata.health');
if (!$staff) {
    echo "Error: staff-bata@bata.health not found!\n";
    exit(1);
}
echo "Found staff: id={$staff['id']}, name={$staff['staff_name']}, email={$staff['email']}\n";

// Test 1: Updating with existing data and new recovery email
$testData1 = [
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
];

try {
    $res1 = update_staff_account_details((int)$staff['id'], $testData1);
    echo "Test 1 (standard update): " . ($res1 ? "SUCCESS" : "FAILED") . "\n";
} catch (Throwable $e) {
    echo "Test 1 EXCEPTION: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}

// Test 2: Updating with empty optional fields (empty date, empty recovery_email, etc.)
$testData2 = [
    'staff_name' => 'Nurse Maria Santos',
    'email' => 'staff-bata@bata.health',
    'birth_date' => '',
    'gender' => '',
    'contact_number' => '',
    'home_address' => '',
    'recovery_email' => '',
    'emergency_contact' => '',
    'emergency_phone' => '',
    'password' => '',
];

try {
    $res2 = update_staff_account_details((int)$staff['id'], $testData2);
    echo "Test 2 (empty optional fields): " . ($res2 ? "SUCCESS" : "FAILED") . "\n";
} catch (Throwable $e) {
    echo "Test 2 EXCEPTION: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}

// Test 3: Updating with a password change
$testData3 = [
    'staff_name' => 'Nurse Maria Santos',
    'email' => 'staff-bata@bata.health',
    'birth_date' => '1992-08-14',
    'gender' => 'Female',
    'contact_number' => '09171234567',
    'home_address' => 'Purok Masinadyahon, Barangay Bata, Bacolod City',
    'recovery_email' => 'nurse.maria.recovery@gmail.com',
    'emergency_contact' => 'Juan Santos',
    'emergency_phone' => '09189876543',
    'password' => 'newPassword123!',
];

try {
    $res3 = update_staff_account_details((int)$staff['id'], $testData3);
    echo "Test 3 (with password change): " . ($res3 ? "SUCCESS" : "FAILED") . "\n";
} catch (Throwable $e) {
    echo "Test 3 EXCEPTION: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}

// Reset password back
update_staff_password('staff-bata@bata.health', 'StaffBata2026!');
echo "Password reset to original.\n";
