<?php
$cookieFile = __DIR__ . '/test_cookie.txt';
if (file_exists($cookieFile)) unlink($cookieFile);

$baseUrl = 'http://localhost/Health-Delivery-System-Latest';

echo "1. Logging in as Staff...\n";
$ch = curl_init($baseUrl . '/Patients/login-handler.php');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'action' => 'login_staff',
        'email' => 'staff-bata@bata.health',
        'password' => 'StaffPassword123!',
    ]),
    CURLOPT_COOKIEJAR => $cookieFile,
    CURLOPT_COOKIEFILE => $cookieFile,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => false,
]);
$resp = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Login Response: HTTP $httpCode -> $resp\n";

echo "2. Fetching Barangay Health Station page to get CSRF token...\n";
$ch = curl_init($baseUrl . '/Barangay%20Health%20Station/index.php?page=dashboard');
curl_setopt_array($ch, [
    CURLOPT_COOKIEJAR => $cookieFile,
    CURLOPT_COOKIEFILE => $cookieFile,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => false,
]);
$html = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$redirectUrl = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
curl_close($ch);

echo "BHS Dashboard HTTP Code: $httpCode, Redirect: $redirectUrl\n";
if (!preg_match('/name="csrf_token"\s+value="([^"]+)"/', $html, $matches)) {
    echo "Could not find CSRF token in page HTML!\n";
    echo "First 500 chars of HTML:\n" . substr($html, 0, 500) . "\n";
    exit(1);
}
$csrfToken = $matches[1];
echo "Found CSRF Token: $csrfToken\n";

echo "3. Submitting Staff Account Edit POST request...\n";
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
        'recovery_email' => 'maria.recovery.test@gmail.com',
        'emergency_contact' => 'Juan Santos',
        'emergency_phone' => '09189876543',
        'new_password' => '',
        'confirm_password' => '',
    ]),
    CURLOPT_COOKIEJAR => $cookieFile,
    CURLOPT_COOKIEFILE => $cookieFile,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => false,
]);
$postResp = curl_exec($ch);
$postHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$postRedirect = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
curl_close($ch);

echo "POST Response HTTP Code: $postHttpCode, Redirect: $postRedirect\n";

if ($postHttpCode == 500 || $postHttpCode == 0) {
    echo "CRASH DETECTED ON POST! Body:\n$postResp\n";
}

echo "4. Following redirect to verify post-edit state...\n";
$target = $postRedirect ?: ($baseUrl . '/Barangay%20Health%20Station/index.php?page=dashboard');
$ch = curl_init($target);
curl_setopt_array($ch, [
    CURLOPT_COOKIEJAR => $cookieFile,
    CURLOPT_COOKIEFILE => $cookieFile,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
]);
$finalHtml = curl_exec($ch);
$finalHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Final Page HTTP Code: $finalHttpCode\n";
if (strpos($finalHtml, 'Account details updated successfully') !== false) {
    echo "SUCCESS: Flash message found!\n";
} else {
    echo "Flash message NOT found or redirected elsewhere.\n";
    if (strpos($finalHtml, 'portal') !== false) {
        echo "Redirected to portal (Session lost!).\n";
    }
}
