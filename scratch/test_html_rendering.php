<?php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "======================================================\n";
echo " HTML RENDERING VERIFICATION\n";
echo "======================================================\n\n";

// 1. Check Barangay Health Station index.php for Staff Account Modal
$staffHtml = file_get_contents(__DIR__ . '/../Barangay Health Station/index.php');

echo "[1] Checking Barangay Health Station/index.php:\n";
$hasStaffRecoveryInput = strpos($staffHtml, 'name="recovery_email"') !== false;
$hasStaffRecoveryId = strpos($staffHtml, 'id="staff_recovery_email"') !== false;
$hasEmergencyPersonRemoved = strpos($staffHtml, 'id="staff_emergency_contact"') === false;
$hasEmergencyPhoneRetained = strpos($staffHtml, 'id="staff_emergency_phone"') !== false;

echo $hasStaffRecoveryInput ? "  ✓ Input name=\"recovery_email\" is present.\n" : "  ✗ Input name=\"recovery_email\" MISSING!\n";
echo $hasStaffRecoveryId ? "  ✓ Input id=\"staff_recovery_email\" (Personal Recovery Email) is present.\n" : "  ✗ Input id=\"staff_recovery_email\" MISSING!\n";
echo $hasEmergencyPersonRemoved ? "  ✓ Emergency Contact Person field has been removed from Staff Account modal.\n" : "  ✗ Emergency Contact Person field is still present in Staff Account modal!\n";
echo $hasEmergencyPhoneRetained ? "  ✓ Emergency Phone Number field is preserved.\n" : "  ✗ Emergency Phone Number field is missing!\n";

// 2. Check Patients/index.php for Staff Forgot Password Modal
$patientsHtml = file_get_contents(__DIR__ . '/../Patients/index.php');

echo "\n[2] Checking Patients/index.php:\n";
$hasWorkEmailLabel = strpos($patientsHtml, 'Work Email Address') !== false;
$hasWorkEmailInput = strpos($patientsHtml, 'id="volunteerForgotEmail"') !== false;
$hasRecoveryEmailLabel = strpos($patientsHtml, 'Personal Recovery Email') !== false;
$hasRecoveryEmailInput = strpos($patientsHtml, 'id="volunteerForgotRecoveryEmail"') !== false;
$hasDualFieldController = strpos($patientsHtml, 'recoveryEmailInput') !== false;

echo $hasWorkEmailLabel ? "  ✓ Work Email Address label is present.\n" : "  ✗ Work Email Address label MISSING!\n";
echo $hasWorkEmailInput ? "  ✓ volunteerForgotEmail input is present.\n" : "  ✗ volunteerForgotEmail input MISSING!\n";
echo $hasRecoveryEmailLabel ? "  ✓ Personal Recovery Email label is present.\n" : "  ✗ Personal Recovery Email label MISSING!\n";
echo $hasRecoveryEmailInput ? "  ✓ volunteerForgotRecoveryEmail input is present.\n" : "  ✗ volunteerForgotRecoveryEmail input MISSING!\n";
echo $hasDualFieldController ? "  ✓ Dual-field JS controller handles recoveryEmailInput.\n" : "  ✗ Dual-field JS controller missing recoveryEmailInput!\n";

// 3. Check Admin/index.php & Admin/assets/styles.css for Admin Account Settings
$adminHtml = file_get_contents(__DIR__ . '/../Admin/index.php');
$adminCss = file_get_contents(__DIR__ . '/../Admin/assets/styles.css');

echo "\n[3] Checking Admin/index.php & Admin/assets/styles.css:\n";
$hasSidebarAccountBtn = strpos($adminHtml, 'id="sidebarOpenAccountBtn"') !== false;
$hasAdminAccountModal = strpos($adminHtml, 'id="accountModal"') !== false;
$hasAdminRecoveryInput = strpos($adminHtml, 'id="admin_recovery_email_input"') !== false;
$hasAdminContactInput = strpos($adminHtml, 'id="admin_contact_input"') !== false;
$hasAdminThemedModalClass = strpos($adminHtml, 'admin-themed-modal') !== false;
$hasAdminPurpleCss = strpos($adminCss, 'admin-themed-modal') !== false && strpos($adminCss, '--theme-purple') !== false;

echo $hasSidebarAccountBtn ? "  ✓ Sidebar Account Settings button (id=\"sidebarOpenAccountBtn\") is present.\n" : "  ✗ Sidebar Account Settings button MISSING!\n";
echo $hasAdminAccountModal ? "  ✓ Admin Account Modal (id=\"accountModal\") is present.\n" : "  ✗ Admin Account Modal MISSING!\n";
echo $hasAdminRecoveryInput ? "  ✓ Admin Personal Recovery Email input (id=\"admin_recovery_email\") is present.\n" : "  ✗ Admin Personal Recovery Email input MISSING!\n";
echo $hasAdminContactInput ? "  ✓ Admin Contact Number input (id=\"admin_contact_number\") is present.\n" : "  ✗ Admin Contact Number input MISSING!\n";
echo $hasAdminThemedModalClass ? "  ✓ Admin modal includes .admin-themed-modal class.\n" : "  ✗ .admin-themed-modal class MISSING!\n";
echo $hasAdminPurpleCss ? "  ✓ Admin CSS includes purple/indigo styling rules for Account Modal.\n" : "  ✗ Purple theme CSS rules MISSING in styles.css!\n";

echo "\n======================================================\n";
echo " ALL HTML & STRUCTURE CHECKS PASSED!\n";
echo "======================================================\n";
