<?php

declare(strict_types=1);

require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';
require_once __DIR__ . '/includes/data.php';

$isLoggedIn = isset($_SESSION['patient_id']) && $_SESSION['patient_id'] !== '';
if (!$isLoggedIn) {
    header('Location: index.php');
    exit;
}

$patientId = (string) $_SESSION['patient_id'];
$patientName = (string) ($_SESSION['patient_name'] ?? '');
$patientBarangay = (string) ($_SESSION['patient_barangay'] ?? '');

$barangayOptions = [
    'Alijis', 'Bata', 'Cabug', 'Estefania', 'Granada',
    'Handumanan', 'Mandalagan', 'Mansilingan', 'Pahanocoy',
    'Singcang', 'Sum-Ag', 'Taculing', 'Villamonte',
    'Villa Esperanza', 'Vista Alegre'
];

$purokOptionsByBarangay = bacolod_purok_catalog();

$updateMessage = '';
$updateSuccess = false;

// Handle Account Update POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (($_POST['action'] ?? '') === 'update_account')) {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $updateMessage = 'Security validation failed. Please refresh the page and try again.';
        $passwordError = true;
    } else {
    $firstName = trim((string) ($_POST['first_name'] ?? ''));
    $middleName = trim((string) ($_POST['middle_name'] ?? ''));
    $lastName = trim((string) ($_POST['last_name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $contactNumber = trim((string) ($_POST['contact_number'] ?? ''));
    $birthDate = trim((string) ($_POST['birth_date'] ?? ''));
    $gender = trim((string) ($_POST['gender'] ?? ''));
    
    $changeAddressActive = trim((string) ($_POST['change_address_active'] ?? '0')) === '1';
    $newBarangay = $changeAddressActive ? trim((string) ($_POST['barangay'] ?? '')) : ($patientBarangay ?: (string) ($_SESSION['patient_barangay'] ?? ''));
    $newPurok = $changeAddressActive ? trim((string) ($_POST['purok'] ?? '')) : (string) ($_SESSION['patient_purok'] ?? '');
    $newStreet = $changeAddressActive ? trim((string) ($_POST['street'] ?? '')) : (string) ($_SESSION['patient_street'] ?? '');

    $changePasswordActive = trim((string) ($_POST['change_password_active'] ?? '0')) === '1';
    $newPassword = $changePasswordActive ? trim((string) ($_POST['new_password'] ?? '')) : '';
    $confirmPassword = $changePasswordActive ? trim((string) ($_POST['confirm_password'] ?? '')) : '';

    $passwordError = false;
    if ($changePasswordActive && $newPassword !== '') {
        if (strlen($newPassword) < 6) {
            $updateMessage = 'New password must be at least 6 characters long.';
            $passwordError = true;
        } elseif ($newPassword !== $confirmPassword) {
            $updateMessage = 'New password and confirmation password do not match.';
            $passwordError = true;
        }
    }

    if (!$passwordError) {
        if ($firstName !== '' && $lastName !== '' && $email !== '') {
            $patientBarangay = $newBarangay;
            $purok = $newPurok;
            $street = $newStreet;
            $addressParts = array_filter([$street, $purok, $patientBarangay !== '' ? ('Brgy. ' . $patientBarangay) : '', 'Bacolod City']);
            $completeAddress = implode(', ', $addressParts);

            // Update Session
            $_SESSION['patient_name'] = trim($firstName . ' ' . $lastName);
            $_SESSION['patient_first_name'] = $firstName;
            $_SESSION['patient_middle_name'] = $middleName;
            $_SESSION['patient_last_name'] = $lastName;
            $_SESSION['patient_email'] = $email;
            $_SESSION['patient_contact_number'] = $contactNumber;
            $_SESSION['patient_birth_date'] = $birthDate;
            $_SESSION['patient_gender'] = $gender;
            $_SESSION['patient_barangay'] = $patientBarangay;
            $_SESSION['patient_purok'] = $purok;
            $_SESSION['patient_street'] = $street;
            $_SESSION['patient_complete_address'] = $completeAddress;

            // Upsert database profiles
            try {
                upsert_patient_profile([
                    'patient_id' => $patientId,
                    'first_name' => $firstName,
                    'middle_name' => $middleName,
                    'last_name' => $lastName,
                    'birth_date' => $birthDate,
                    'gender' => $gender,
                    'contact_number' => $contactNumber,
                    'email' => $email,
                    'complete_address' => $completeAddress,
                ]);

                $stationSlugVal = strtolower(str_replace([' ', '-'], '', $patientBarangay));
                if ($changePasswordActive && $newPassword !== '') {
                    $passHash = password_hash($newPassword, PASSWORD_DEFAULT);
                    $stmt = db()->prepare('UPDATE patient_accounts SET first_name = ?, middle_name = ?, last_name = ?, email = ?, contact_number = ?, birth_date = ?, gender = ?, complete_address = ?, station_slug = ?, station_name = ?, password_hash = ? WHERE patient_id = ? OR email = ?');
                    $stmt->bind_param('sssssssssssss', $firstName, $middleName, $lastName, $email, $contactNumber, $birthDate, $gender, $completeAddress, $stationSlugVal, $patientBarangay, $passHash, $patientId, $email);
                    $stmt->execute();
                } else {
                    $stmt = db()->prepare('UPDATE patient_accounts SET first_name = ?, middle_name = ?, last_name = ?, email = ?, contact_number = ?, birth_date = ?, gender = ?, complete_address = ?, station_slug = ?, station_name = ? WHERE patient_id = ? OR email = ?');
                    $stmt->bind_param('ssssssssssss', $firstName, $middleName, $lastName, $email, $contactNumber, $birthDate, $gender, $completeAddress, $stationSlugVal, $patientBarangay, $patientId, $email);
                    $stmt->execute();
                }
            } catch (Throwable $e) {}

            $patientName = (string) $_SESSION['patient_name'];
            $updateSuccess = true;
            $updateMessage = 'Your account and personal details have been updated successfully!';
        } else {
            $updateMessage = 'Please fill in all required personal details.';
        }
    }
    }
}

// Load current patient data
$patientAccount = null;
if (!empty($patientId)) {
    try {
        $stmt = db()->prepare('SELECT * FROM patient_accounts WHERE patient_id = ? OR email = ? LIMIT 1');
        $patientEmail = (string) ($_SESSION['patient_email'] ?? '');
        $stmt->bind_param('ss', $patientId, $patientEmail);
        $stmt->execute();
        $patientAccount = $stmt->get_result()->fetch_assoc();
    } catch (Throwable $e) {}
    
    if (!$patientAccount) {
        try {
            $stmt2 = db()->prepare('SELECT * FROM patient_profiles WHERE UPPER(patient_id) = ? LIMIT 1');
            $stmt2->bind_param('s', $patientId);
            $stmt2->execute();
            $patientAccount = $stmt2->get_result()->fetch_assoc();
        } catch (Throwable $e) {}
    }
}

$rawAddress = (string) ($patientAccount['complete_address'] ?? $_SESSION['patient_complete_address'] ?? '');
$stationName = (string) ($patientAccount['station_name'] ?? $patientAccount['station_slug'] ?? $patientBarangay);
$addrDetails = parse_complete_address($rawAddress, $stationName);

if ($patientBarangay === '' && !empty($addrDetails['barangay'])) {
    $patientBarangay = $addrDetails['barangay'];
}

$firstName = (string) ($patientAccount['first_name'] ?? $_SESSION['patient_first_name'] ?? '');
$middleName = (string) ($patientAccount['middle_name'] ?? $_SESSION['patient_middle_name'] ?? '');
$lastName = (string) ($patientAccount['last_name'] ?? $_SESSION['patient_last_name'] ?? '');
$email = (string) ($patientAccount['email'] ?? $_SESSION['patient_email'] ?? '');
$contactNumber = (string) ($patientAccount['contact_number'] ?? $_SESSION['patient_contact_number'] ?? '');
$birthDate = (string) ($patientAccount['birth_date'] ?? $_SESSION['patient_birth_date'] ?? '');
$gender = (string) ($patientAccount['gender'] ?? $_SESSION['patient_gender'] ?? '');
$purok = (string) ($_SESSION['patient_purok'] ?? $addrDetails['purok'] ?? '');
$street = (string) ($_SESSION['patient_street'] ?? $addrDetails['street'] ?? '');
$completeAddress = $rawAddress !== '' ? $rawAddress : ($patientBarangay !== '' ? ('Brgy. ' . $patientBarangay . ', Bacolod City') : '');

if ($patientName === '') {
    $patientName = trim($firstName . ' ' . $lastName);
}

$contact = contact_details();
$stations = station_catalog();
$stationSlug = '';
$selectedStation = null;

foreach ($stations as $station) {
    if (strcasecmp((string) $station['barangay'], $patientBarangay) === 0) {
        $selectedStation = $station;
        $stationSlug = (string) $station['slug'];
        break;
    }
}

if ($selectedStation === null && !empty($stations)) {
    $selectedStation = $stations[0];
    $stationSlug = (string) $selectedStation['slug'];
    $patientBarangay = (string) $selectedStation['barangay'];
}

$servicesForBarangay = $selectedStation['programs'] ?? [];
$userStationSlug = strtolower($stationSlug);

// Load upcoming events ONLY for the registered barangay
$dbUpcomingEvents = fetch_upcoming_events(['upcoming_only' => true]);
$upcomingEvents = array_map(
    static function (array $event): array {
        $startTime = trim((string) ($event['time_label'] ?? ''));
        $endTime = trim((string) ($event['end_time_label'] ?? ''));
        $timeDisplay = $startTime;
        if ($endTime !== '' && $endTime !== $startTime && stripos($startTime, '-') === false) {
            $timeDisplay = $startTime . ' - ' . $endTime;
        }
        return [
            'icon' => $event['icon'] ?? 'calendar',
            'title' => $event['title'],
            'station' => $event['station_name'],
            'barangay' => $event['station_slug'],
            'description' => $event['description'],
            'date' => date('F j, Y', strtotime((string) $event['event_date'])),
            'time' => $timeDisplay,
            'accent' => $event['accent'] ?? 'mint',
        ];
    },
    array_values(array_filter(
        $dbUpcomingEvents,
        static fn(array $event): bool => strtolower((string) ($event['station_slug'] ?? '')) === $userStationSlug
    ))
);

if (empty($upcomingEvents) && !empty($events)) {
    $upcomingEvents = array_values(array_filter(
        $events,
        static fn(array $event): bool => strtolower((string) ($event['barangay'] ?? '')) === $userStationSlug
    ));
}

// Load Patient Notifications, Follow-ups, and Booked Appointments
$patientEmailVal = (string) ($patientAccount['email'] ?? $_SESSION['patient_email'] ?? '');
$patientNotifications = fetch_patient_appointment_notifications($patientId, $patientEmailVal, $patientName);
$unreadNotifCount = count(array_filter($patientNotifications, static fn(array $n): bool => (int) ($n['is_read'] ?? 0) === 0));
$upcomingFollowUps = fetch_patient_upcoming_follow_ups($patientId, $patientEmailVal, $patientName);
$patientAppointments = fetch_patient_appointments($patientId, $patientEmailVal, $patientName);

$bookedRef = trim((string) ($_GET['booked'] ?? ''));
$justBookedAppt = null;
if ($bookedRef !== '') {
    $justBookedAppt = fetch_appointment_by_reference($bookedRef) ?? fetch_appointment_by_code($bookedRef);
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function iconSvg(string $name): string
{
    $icons = [
        'bell' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'heart' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'syringe' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m18 2 4 4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="m17 7 3-3" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M19 9 8.7 19.3c-.4.4-1 .6-1.6.6H3v-4.1c0-.6.2-1.2.6-1.6L14 3.9" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="m9 11 4 4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="m5 19-3 3" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="m14 4 6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'community' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="9" cy="7" r="4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M22 21v-2a4 4 0 0 0-3-3.87" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M16 3.13a4 4 0 0 1 0 7.75" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'baby' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 12h.01M15 12h.01M10 16c.5.3 1.2.5 2 .5s1.5-.2 2-.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M19 6.3a9 9 0 0 1 1.8 3.9 2 2 0 0 1 0 3.6 9 9 0 0 1-17.6 0 2 2 0 0 1 0-3.6A9 9 0 0 1 12 3c2 0 3.5 1.1 3.5 2.5s-.9 2.5-2 2.5c-.8 0-1.5-.4-1.5-1" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'phone' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 5.15 11.8 19.79 19.79 0 0 1 2.08 3.12 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'map' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s-6-5.33-6-11a6 6 0 1 1 12 0c0 5.67-6 11-6 11Zm0-8.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'calendar' => '<svg viewBox="0 0 24 24" aria-hidden="true"><rect width="18" height="18" x="3" y="4" rx="2" ry="2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><line x1="16" x2="16" y1="2" y2="6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><line x1="8" x2="8" y1="2" y2="6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><line x1="3" x2="21" y1="10" y2="10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'sparkle' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 3 1.9 5.3a2 2 0 0 0 1.2 1.2l5.3 1.9-5.3 1.9a2 2 0 0 0-1.2 1.2l-1.9 5.3-1.9-5.3a2 2 0 0 0-1.2-1.2l-5.3-1.9 5.3-1.9a2 2 0 0 0 1.2-1.2z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'arrow' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14m-5-5 5 5-5 5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'clock' => '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><polyline points="12 6 12 12 16 14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'pulse' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 12h-4l-3 9L9 3l-3 9H2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'stethoscope' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4.8 2.3A.3.3 0 1 0 5 2H4a2 2 0 0 0-2 2v5a6 6 0 0 0 6 6v0a6 6 0 0 0 6-6V4a2 2 0 0 0-2-2h-1a.2.2 0 1 0 .3.3" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M8 15v1a6 6 0 0 0 6 6v0a6 6 0 0 0 6-6v-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="20" cy="10" r="2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'cube' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m21 16-9 5-9-5V8l9-5 9 5v8z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="m3.3 7 8.7 5 8.7-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 22V12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'capsule' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m10.5 20.5-7-7a4.95 4.95 0 0 1 7-7l7 7a4.95 4.95 0 0 1-7 7Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="m8.5 8.5 7 7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'user' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="7" r="4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'home' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><polyline points="9 22 9 12 15 12 15 22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'check-circle' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 12l2 2 4-4m7-1a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'success-mark' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 12a8 8 0 1 1-2.34-5.66" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/><path d="m9.5 12.5 2.2 2.2 5-5.4" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'download' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'printer' => '<svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="6 9 6 2 18 2 18 9" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><rect width="12" height="8" x="6" y="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'shield' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'x' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'check' => '<svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="20 6 9 17 4 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'mail' => '<svg viewBox="0 0 24 24" aria-hidden="true"><rect width="20" height="16" x="2" y="4" rx="2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'lock' => '<svg viewBox="0 0 24 24" aria-hidden="true"><rect width="18" height="11" x="3" y="11" rx="2" ry="2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M7 11V7a5 5 0 0 1 10 0v4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'eye' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'history' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M3 3v5h5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 7v5l4 2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'filter' => '<svg viewBox="0 0 24 24" aria-hidden="true"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    ];

    return $icons[$name] ?? '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (($_POST['action'] ?? '') === 'mark_notif_read')) {
    if (verify_csrf($_POST['csrf_token'] ?? null)) {
        $notifId = (int) ($_POST['notification_id'] ?? 0);
        if ($notifId > 0) {
            mark_appointment_notification_read($notifId);
        }
    }
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (($_POST['action'] ?? '') === 'logout')) {
    if (verify_csrf($_POST['csrf_token'] ?? null)) {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - Bacolod Health Stations</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .dashboard-shell {
            min-height: 100vh;
            background: #f8fafc;
        }

        .dashboard-hero {
            background: linear-gradient(100deg, #10b981 0%, #06b6d4 45%, #2563eb 100%);
            color: #ffffff;
            padding: 48px 0 54px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        }

        .dashboard-hero-inner {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 32px;
        }

        .dashboard-greeting {
            flex: 1;
        }

        .dashboard-tagline {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 18px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(6px);
            color: #ffffff;
            font-size: 0.95rem;
            font-weight: 600;
            margin-bottom: 18px;
        }

        .dashboard-tagline svg {
            width: 18px;
            height: 18px;
        }

        .dashboard-greeting h1 {
            font-size: clamp(2.3rem, 3.8vw, 3.4rem);
            font-weight: 800;
            margin: 0 0 8px 0;
            color: #ffffff;
            line-height: 1.15;
            letter-spacing: -0.02em;
        }

        .dashboard-greeting p {
            font-size: 1.15rem;
            color: rgba(255, 255, 255, 0.95);
            margin: 0;
            font-weight: 500;
        }

        .dashboard-hero-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            justify-content: flex-end;
            margin-bottom: 6px;
        }

        .hero-nav-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 22px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.22);
            border: 1.5px solid rgba(255, 255, 255, 0.4);
            color: #ffffff;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
            font-family: inherit;
        }

        .hero-nav-pill:hover {
            background: rgba(255, 255, 255, 0.35);
            border-color: #ffffff;
            transform: translateY(-2px);
        }

        .dashboard-main-content {
            padding: 44px 0 70px;
        }

        .dashboard-section-block {
            margin-bottom: 56px;
        }

        .section-title-wrap {
            display: flex;
            align-items: center;
            gap: 18px;
            margin-bottom: 30px;
        }

        .section-title-wrap .section-icon.gold {
            display: grid;
            place-items: center;
            width: 62px;
            height: 62px;
            border-radius: 18px;
            background: #f59e0b;
            color: #ffffff;
            flex-shrink: 0;
            box-shadow: 0 8px 18px rgba(245, 158, 11, 0.25);
        }

        .section-title-wrap .section-icon svg {
            width: 30px;
            height: 30px;
        }

        .section-title-copy h2 {
            margin: 0 0 6px 0;
            font-size: clamp(1.85rem, 2.6vw, 2.35rem);
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.02em;
        }

        .section-title-copy p {
            margin: 0;
            font-size: 1.02rem;
            color: #64748b;
        }

        /* Services Cards Grid */
        .services-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 26px;
        }

        .service-card {
            background: #ffffff;
            border: 1.5px solid #edf2f7;
            border-radius: 24px;
            padding: 32px 28px 28px;
            box-shadow: 0 4px 20px rgba(15, 34, 64, 0.04);
            display: flex;
            flex-direction: column;
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.25s ease;
            position: relative;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
        }

        /* Highlights appear ONLY on cursor hover */
        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 16px 36px rgba(16, 185, 129, 0.12), 0 4px 12px rgba(0, 0, 0, 0.04);
            border-color: #34d399;
        }

        /* Neat Service Icons */
        .service-icon {
            display: grid;
            place-items: center;
            width: 54px;
            height: 54px;
            border-radius: 16px;
            margin-bottom: 22px;
            flex-shrink: 0;
            transition: transform 0.2s ease;
        }

        .service-card:hover .service-icon {
            transform: scale(1.06);
        }

        .service-icon.blue {
            background: #eff6ff;
            color: #2563eb;
        }

        .service-icon.mint {
            background: #ecfdf5;
            color: #059669;
        }

        .service-icon.red {
            background: #ef4444;
            color: #ffffff;
        }

        .service-icon.pink {
            background: #ec4899;
            color: #ffffff;
        }

        .service-icon.violet {
            background: #8b5cf6;
            color: #ffffff;
        }

        .service-icon.gold {
            background: #f59e0b;
            color: #ffffff;
        }

        .service-icon.cyan {
            background: #06b6d4;
            color: #ffffff;
        }

        .service-icon.indigo {
            background: #6366f1;
            color: #ffffff;
        }

        .service-icon svg {
            width: 26px;
            height: 26px;
            display: block;
        }

        .service-card h3 {
            margin: 0 0 10px;
            font-size: 1.22rem;
            font-weight: 700;
            color: #111827;
            transition: color 0.2s ease;
        }

        .service-card:hover h3 {
            color: #059669;
        }

        .service-card p {
            margin: 0 0 24px;
            font-size: 0.94rem;
            color: #64748b;
            line-height: 1.5;
            min-height: 2.8em;
        }

        .service-schedule {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            background: #f8fafc;
            color: #334155;
            margin-bottom: 24px;
            margin-top: auto;
            transition: border-color 0.2s ease, background 0.2s ease;
        }

        .service-card:hover .service-schedule {
            border-color: #d1fae5;
            background: #f0fdf4;
        }

        .service-schedule span {
            width: 18px;
            height: 18px;
            color: #10b981;
            display: grid;
            place-items: center;
            flex-shrink: 0;
        }

        .service-schedule strong {
            font-size: 0.92rem;
            font-weight: 600;
            color: #334155;
        }

        .service-book-link {
            color: #059669;
            font-size: 0.96rem;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: color 0.2s ease, transform 0.2s ease;
            cursor: pointer;
        }

        .service-book-link:hover {
            color: #047857;
            transform: translateX(3px);
        }

        /* Events Grid */
        .events-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 26px;
        }

        .event-card {
            display: flex;
            gap: 20px;
            padding: 28px;
            border: 1.5px solid #e8eef5;
            border-radius: 22px;
            background: #ffffff;
            box-shadow: 0 4px 16px rgba(15, 34, 64, 0.03);
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.25s ease;
        }

        .event-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 32px rgba(15, 34, 64, 0.08);
        }

        .event-card.mint:hover { border-color: #10b981; }
        .event-card.blue:hover { border-color: #3b82f6; }
        .event-card.gold:hover { border-color: #f59e0b; }
        .event-card.pink:hover { border-color: #ec4899; }
        .event-card.violet:hover { border-color: #8b5cf6; }

        .event-icon {
            display: grid;
            place-items: center;
            width: 54px;
            height: 54px;
            border-radius: 16px;
            flex-shrink: 0;
            color: #ffffff;
        }

        .event-icon.mint { background: #e8fbf2; color: #059669; }
        .event-icon.blue { background: #eaf2ff; color: #2563eb; }
        .event-icon.pink { background: linear-gradient(180deg, #ff2f96 0%, #e40079 100%); color: #fff; }
        .event-icon.gold { background: #f59e0b; color: #fff; }
        .event-icon.violet { background: #f4ebff; color: #8b5cf6; }

        .event-icon svg {
            width: 24px;
            height: 24px;
            display: block;
        }

        .event-content {
            flex: 1;
        }

        .event-content h3 {
            margin: 0 0 4px 0;
            font-size: 1.2rem;
            font-weight: 700;
            color: #0f172a;
        }

        .event-station {
            color: #2563eb;
            font-weight: 600;
            font-size: 0.92rem;
            margin-bottom: 8px;
        }

        .event-content p {
            margin: 0 0 16px 0;
            color: #64748b;
            font-size: 0.92rem;
            line-height: 1.5;
        }

        .event-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            font-size: 0.88rem;
            color: #475569;
            font-weight: 600;
        }

        .event-meta span {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .empty-state-box {
            background: #ffffff;
            border: 1.5px dashed #cbd5e1;
            border-radius: 20px;
            padding: 48px 24px;
            text-align: center;
            color: #64748b;
            font-size: 1.05rem;
        }

        /* ── Notification Bell & Popover ── */
        .notif-bell-wrap {
            position: relative;
            display: inline-block;
        }

        .hero-nav-pill.notif-bell-btn {
            position: relative;
            gap: 7px;
            cursor: pointer;
        }

        .hero-nav-pill.notif-bell-btn.has-unread {
            background: rgba(255, 255, 255, 0.32);
            border-color: #fde68a;
        }

        .notif-badge-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 20px;
            height: 20px;
            padding: 0 5px;
            border-radius: 999px;
            background: #ef4444;
            color: #ffffff;
            font-size: 0.72rem;
            font-weight: 800;
            line-height: 1;
            box-shadow: 0 2px 6px rgba(239, 68, 68, 0.4);
            animation: badgePulse 2s infinite;
        }

        @keyframes badgePulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.15); }
        }

        .bell-icon-inner svg {
            width: 17px;
            height: 17px;
        }

        .notif-dropdown-popover {
            position: absolute;
            top: calc(100% + 12px);
            right: 0;
            width: min(380px, calc(100vw - 32px));
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 20px 45px -10px rgba(15, 23, 42, 0.25), 0 0 0 1px rgba(15, 23, 42, 0.06);
            z-index: 100;
            display: none;
            animation: popoverFadeIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            color: #1e293b;
            overflow: hidden;
            text-align: left;
        }

        .notif-dropdown-popover.open {
            display: block;
        }

        @keyframes popoverFadeIn {
            from { opacity: 0; transform: translateY(-8px) scale(0.97); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .notif-popover-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            border-bottom: 1.5px solid #f1f5f9;
            background: #f8fafc;
        }

        .notif-popover-title {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .notif-popover-title strong {
            font-size: 1.05rem;
            font-weight: 800;
            color: #0f172a;
        }

        .unread-chip {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 999px;
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
            font-size: 0.72rem;
            font-weight: 700;
        }

        .notif-close-btn {
            background: transparent;
            border: none;
            color: #94a3b8;
            font-size: 1.1rem;
            cursor: pointer;
            padding: 4px;
            border-radius: 6px;
        }
        .notif-close-btn:hover {
            color: #0f172a;
            background: #e2e8f0;
        }

        .notif-popover-list {
            max-height: 360px;
            overflow-y: auto;
            padding: 10px 12px;
        }

        .notif-empty-state {
            padding: 32px 16px;
            text-align: center;
            color: #64748b;
        }
        .notif-empty-icon {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #ecfdf5;
            color: #10b981;
            display: grid;
            place-items: center;
            margin: 0 auto 10px;
        }
        .notif-empty-icon svg {
            width: 24px;
            height: 24px;
        }

        .notif-item-card {
            display: flex;
            gap: 12px;
            padding: 12px 14px;
            border-radius: 14px;
            background: #ffffff;
            border: 1px solid #f1f5f9;
            margin-bottom: 8px;
            transition: all 0.18s ease;
        }

        .notif-item-card:last-child {
            margin-bottom: 0;
        }

        .notif-item-card.is-unread {
            background: #f0fdf4;
            border-color: #bbf7d0;
        }

        .notif-item-card.is-followup {
            border-left: 3.5px solid #059669;
        }

        .notif-item-icon {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: #ecfdf5;
            color: #059669;
            display: grid;
            place-items: center;
            flex-shrink: 0;
        }
        .notif-item-icon svg {
            width: 18px;
            height: 18px;
        }

        .notif-item-content {
            flex: 1;
            min-width: 0;
        }

        .notif-item-title-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 6px;
            margin-bottom: 3px;
        }

        .notif-tag {
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .notif-tag.tag-followup {
            color: #047857;
        }
        .notif-tag.tag-info {
            color: #2563eb;
        }

        .notif-time {
            font-size: 0.72rem;
            color: #94a3b8;
        }

        .notif-item-msg {
            margin: 0;
            font-size: 0.84rem;
            color: #334155;
            line-height: 1.4;
        }

        .btn-mark-read {
            background: transparent;
            border: none;
            padding: 0;
            color: #059669;
            font-size: 0.76rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: underline;
        }
        .btn-mark-read:hover {
            color: #047857;
        }

        /* ── Patient Follow-up Alert Card ── */
        .patient-followup-alert-card {
            background: #ffffff;
            border: 1.5px solid #e8eef5;
            border-radius: 24px;
            box-shadow: 0 4px 16px rgba(15, 34, 64, 0.03);
            display: flex;
            overflow: hidden;
            margin-bottom: 24px;
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.25s ease;
        }

        .patient-followup-alert-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 32px rgba(15, 34, 64, 0.08);
            border-color: #10b981;
        }

        .followup-alert-side-badge {
            background: #f8fafc;
            border-right: 1.5px solid #e8eef5;
            color: #0f172a;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px 20px;
            min-width: 110px;
            flex-shrink: 0;
            text-align: center;
            gap: 10px;
        }

        .followup-calendar-icon {
            display: grid;
            place-items: center;
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: #ecfdf5;
            color: #059669;
        }

        .followup-calendar-icon svg {
            width: 22px;
            height: 22px;
        }

        .followup-days-tag strong {
            display: block;
            font-size: 0.76rem;
            font-weight: 800;
            letter-spacing: 0.05em;
            background: #e2e8f0;
            color: #475569;
            padding: 4px 8px;
            border-radius: 8px;
        }
        .followup-days-tag .today-tag {
            background: #fee2e2;
            color: #dc2626;
        }
        .followup-days-tag .soon-tag {
            background: #fef3c7;
            color: #d97706;
        }

        .followup-alert-main {
            padding: 24px 28px;
            flex: 1;
            min-width: 0;
        }

        .followup-alert-top-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 8px;
        }

        .followup-notice-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: #475569;
            background: #f1f5f9;
            padding: 4px 12px;
            border-radius: 999px;
            border: 1px solid #e2e8f0;
        }

        .live-dot-pulse {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
        }

        .followup-ref-badge {
            font-size: 0.82rem;
            font-weight: 700;
            color: #64748b;
            font-family: monospace;
        }

        .followup-title {
            margin: 0 0 16px 0;
            font-size: 1.35rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.25;
        }

        .followup-meta-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 16px;
        }

        .followup-meta-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
        }

        .followup-meta-item .meta-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: #ecfdf5;
            color: #059669;
            display: grid;
            place-items: center;
            flex-shrink: 0;
        }
        .followup-meta-item .meta-icon svg {
            width: 16px;
            height: 16px;
        }

        .followup-meta-item small {
            display: block;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
        }

        .followup-meta-item strong {
            font-size: 0.92rem;
            font-weight: 700;
            color: #0f172a;
        }

        .followup-notes-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #10b981;
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 12px;
        }

        .notes-box-label {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.78rem;
            font-weight: 800;
            color: #047857;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            margin-bottom: 4px;
        }
        .notes-box-label svg {
            width: 13px;
            height: 13px;
        }

        .followup-notes-box p {
            margin: 0;
            font-size: 0.92rem;
            color: #334155;
            line-height: 1.5;
        }

        .followup-footer-note {
            font-size: 0.84rem;
            color: #64748b;
            font-weight: 500;
        }

        @media (max-width: 800px) {
            .patient-followup-alert-card {
                flex-direction: column;
            }
            .followup-alert-side-badge {
                flex-direction: row;
                padding: 12px 20px;
                justify-content: space-between;
                border-right: none;
                border-bottom: 1.5px solid #e8eef5;
            }
            .followup-meta-grid {
                grid-template-columns: 1fr;
            }
        }

        /* ── Account Modal ────────────────────────────────────────── */
        .account-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(6px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 20px;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 0.25s ease, visibility 0.25s ease;
        }

        .account-modal-overlay.active,
        .account-modal-overlay.open {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        #appointmentSlipModal {
            z-index: 1050;
        }

        #appointmentHistoryModal {
            z-index: 1000;
        }

        .account-modal-card {
            background: #ffffff;
            border-radius: 28px;
            max-width: 660px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            transform: translateY(20px) scale(0.97);
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
        }

        .account-modal-overlay.active .account-modal-card,
        .account-modal-overlay.open .account-modal-card,
        .account-modal-overlay.active .slip-modal-card,
        .account-modal-overlay.open .slip-modal-card,
        .account-modal-overlay.active .history-modal-card,
        .account-modal-overlay.open .history-modal-card {
            transform: translateY(0) scale(1);
        }

        .account-modal-header {
            padding: 26px 32px 20px;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            border-bottom: 1px solid #f1f5f9;
            position: sticky;
            top: 0;
            background: #ffffff;
            z-index: 10;
            border-radius: 28px 28px 0 0;
        }

        .account-header-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .account-header-icon {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: #ffffff;
            display: grid;
            place-items: center;
            box-shadow: 0 8px 18px rgba(16, 185, 129, 0.25);
            flex-shrink: 0;
        }

        .account-header-icon svg {
            width: 26px;
            height: 26px;
        }

        .account-header-left h3 {
            margin: 0 0 4px;
            font-size: 1.4rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.01em;
        }

        .account-header-left p {
            margin: 0;
            font-size: 0.92rem;
            color: #64748b;
        }

        .account-modal-close {
            background: #f1f5f9;
            border: none;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .account-modal-close:hover {
            background: #e2e8f0;
            color: #0f172a;
            transform: rotate(90deg);
        }

        .account-modal-close svg {
            width: 20px;
            height: 20px;
        }

        .account-modal-body {
            padding: 24px 32px 32px;
        }

        .account-section-divider {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 22px 0 16px;
            color: #059669;
            font-size: 0.84rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            padding-bottom: 8px;
            border-bottom: 1.5px solid #f1f5f9;
        }

        .account-section-divider:first-of-type {
            margin-top: 4px;
        }

        .account-section-divider svg {
            width: 16px;
            height: 16px;
        }

        /* Clean Perfectly Aligned Form Grids */
        .form-row-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            margin-bottom: 14px;
        }

        .form-group-item {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 0;
        }

        .form-group-item.full-width {
            grid-column: 1 / -1;
            margin-bottom: 14px;
        }

        .form-group-item > label:not(.gender-radio-card),
        .form-field-label {
            font-size: 0.88rem;
            font-weight: 600;
            color: #334155;
            height: 22px;
            line-height: 22px;
            display: flex;
            align-items: center;
            gap: 4px;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .form-group-item > label:not(.gender-radio-card) .required,
        .form-field-label .required {
            color: #ef4444;
            font-size: 0.9rem;
            line-height: 1;
            font-weight: 700;
        }

        .form-input-field {
            width: 100%;
            height: 46px;
            padding: 10px 16px;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            background: #ffffff;
            font-size: 0.95rem;
            color: #1e293b;
            font-family: inherit;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            box-sizing: border-box;
            display: block;
            line-height: normal;
        }

        select.form-input-field {
            padding-right: 36px;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            background-size: 16px 16px;
            line-height: 1.2;
        }

        .form-input-field:focus {
            border-color: #10b981;
            outline: none;
            box-shadow: 0 0 0 3.5px rgba(16, 185, 129, 0.15);
        }

        .form-input-field[readonly] {
            background: #f8fafc;
            color: #64748b;
            border-color: #e2e8f0;
            cursor: default;
            font-weight: 500;
        }

        /* Gender Radio Cards */
        .gender-radio-options {
            display: flex;
            gap: 12px;
            height: 46px;
            width: 100%;
            box-sizing: border-box;
        }

        .gender-radio-card {
            flex: 1;
            height: 46px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 10px;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            background: #ffffff;
            cursor: pointer;
            transition: all 0.2s ease;
            padding: 0 16px !important;
            font-weight: 600 !important;
            font-size: 0.92rem !important;
            color: #334155 !important;
            user-select: none;
            box-sizing: border-box !important;
            position: relative;
            margin: 0 !important;
        }

        .gender-radio-card input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
            pointer-events: none;
        }

        .custom-radio-circle {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 2px solid #cbd5e1;
            background: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            position: relative;
            transition: all 0.2s ease;
        }

        .gender-radio-card:hover {
            border-color: #059669;
            background: #f0fdf4;
        }

        .gender-radio-card:hover .custom-radio-circle {
            border-color: #059669;
        }

        .gender-radio-card:has(input[type="radio"]:checked),
        .gender-radio-card.selected {
            border-color: #059669 !important;
            background: #eefcf5 !important;
            color: #004d40 !important;
        }

        .gender-radio-card input[type="radio"]:checked ~ .custom-radio-circle,
        .gender-radio-card.selected .custom-radio-circle {
            border-color: #059669 !important;
            background: #ffffff !important;
        }

        .gender-radio-card input[type="radio"]:checked ~ .custom-radio-circle::after,
        .gender-radio-card.selected .custom-radio-circle::after {
            content: '';
            display: block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #059669 !important;
        }

        .form-help-hint {
            font-size: 0.82rem;
            color: #94a3b8;
            margin-top: 2px;
        }

        /* Password with show/hide toggle */
        .input-password-wrap {
            position: relative;
            width: 100%;
        }

        .input-password-wrap .form-input-field {
            padding-right: 44px;
        }

        .btn-toggle-eye {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            display: grid;
            place-items: center;
            padding: 6px;
            border-radius: 6px;
            transition: color 0.2s ease;
        }

        .btn-toggle-eye:hover {
            color: #334155;
        }

        .btn-toggle-eye svg {
            width: 18px;
            height: 18px;
        }

        /* Button Action Box (Collapsed State) */
        .account-action-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 16px 20px;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 16px;
            margin-bottom: 14px;
            transition: border-color 0.2s ease, background 0.2s ease;
        }

        .account-action-box:hover {
            border-color: #cbd5e1;
            background: #f1f5f9;
        }

        .action-box-info strong {
            display: block;
            font-size: 0.95rem;
            color: #1e293b;
            margin-bottom: 2px;
        }

        .action-box-info p {
            margin: 0;
            font-size: 0.88rem;
            color: #64748b;
        }

        .btn-action-toggle {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 18px;
            border-radius: 999px;
            background: #ffffff;
            border: 1.5px solid #10b981;
            color: #059669;
            font-weight: 600;
            font-size: 0.88rem;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
            font-family: inherit;
        }

        .btn-action-toggle svg {
            width: 16px;
            height: 16px;
        }

        .btn-action-toggle:hover {
            background: #059669;
            color: #ffffff;
            border-color: #059669;
            transform: translateY(-1px);
        }

        /* Collapsible Panels */
        .collapsible-panel {
            display: none;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 18px;
            padding: 20px;
            margin-bottom: 16px;
            animation: panelSlideDown 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .collapsible-panel.open {
            display: block;
        }

        .panel-header-inline {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e2e8f0;
        }

        .panel-header-inline strong {
            color: #0f172a;
            font-size: 0.95rem;
        }

        .btn-text-cancel {
            background: transparent;
            border: none;
            color: #ef4444;
            font-weight: 600;
            font-size: 0.88rem;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 6px;
            transition: background 0.2s ease;
        }

        .btn-text-cancel:hover {
            background: #fee2e2;
        }

        .address-change-notice {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 14px;
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-radius: 12px;
            color: #065f46;
            font-size: 0.86rem;
            line-height: 1.45;
            margin-bottom: 16px;
        }

        .address-change-notice .notice-icon svg {
            width: 18px;
            height: 18px;
            color: #059669;
            flex-shrink: 0;
            margin-top: 1px;
        }

        @keyframes panelSlideDown {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .account-modal-footer {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 12px;
            padding-top: 20px;
            border-top: 1px solid #f1f5f9;
            margin-top: 10px;
        }

        .btn-cancel {
            padding: 11px 22px;
            border-radius: 999px;
            border: 1.5px solid #e2e8f0;
            background: #ffffff;
            color: #64748b;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-cancel:hover {
            background: #f8fafc;
            color: #1e293b;
            border-color: #cbd5e1;
        }

        .btn-save-account {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 28px;
            border-radius: 999px;
            border: none;
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: #ffffff;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.3);
            transition: all 0.2s ease;
        }

        .btn-save-account:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
        }

        .btn-save-account svg {
            width: 18px;
            height: 18px;
        }

        /* ══════════════════════════════════════════════════════════════════
           REDESIGNED MODERN TOAST & BOOKED BANNER NOTIFICATIONS
           ══════════════════════════════════════════════════════════════════ */
        .toast-notification-wrap {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 9999999;
            pointer-events: none;
            max-width: calc(100vw - 48px);
        }

        .toast-notification.modern-toast,
        .toast-notification {
            pointer-events: auto;
            min-width: 320px;
            max-width: 440px;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 14px 18px 16px 16px;
            box-shadow: 0 20px 45px -10px rgba(15, 23, 42, 0.2), 0 0 0 1px rgba(16, 185, 129, 0.22);
            border: 1.5px solid #a7f3d0;
            border-left: 5px solid #10b981;
            display: flex;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 0;
            position: relative;
            overflow: hidden;
            animation: slideInDownToast 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            box-sizing: border-box;
        }

        .toast-notification.modern-toast.error-toast {
            border-color: #fecaca;
            border-left-color: #ef4444;
            box-shadow: 0 20px 45px -10px rgba(239, 68, 68, 0.2), 0 0 0 1px rgba(239, 68, 68, 0.22);
        }

        .toast-notification.hide-toast {
            opacity: 0;
            transform: translateY(-16px) scale(0.95);
            pointer-events: none;
        }

        .toast-icon-circle {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: linear-gradient(135deg, #10b981, #059669);
            color: #ffffff;
            display: grid;
            place-items: center;
            box-shadow: 0 6px 14px rgba(16, 185, 129, 0.25);
            flex-shrink: 0;
        }

        .error-toast .toast-icon-circle {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            box-shadow: 0 6px 14px rgba(239, 68, 68, 0.25);
        }

        .toast-icon-circle svg {
            width: 22px;
            height: 22px;
            stroke-width: 2.5;
        }

        .toast-content-wrap {
            flex: 1;
            min-width: 0;
            padding-top: 2px;
        }

        .toast-content-wrap strong {
            display: block;
            font-size: 1.05rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 3px;
            letter-spacing: -0.01em;
        }

        .toast-content-wrap p {
            margin: 0;
            font-size: 0.92rem;
            color: #475569;
            font-weight: 500;
            line-height: 1.45;
        }

        .toast-dismiss-btn {
            background: transparent;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 8px;
            color: #94a3b8;
            cursor: pointer;
            display: grid;
            place-items: center;
            transition: all 0.2s ease;
            margin: -2px -4px 0 0;
            flex-shrink: 0;
        }

        .toast-dismiss-btn:hover {
            background: #f1f5f9;
            color: #0f172a;
        }

        .toast-dismiss-btn svg {
            width: 18px;
            height: 18px;
        }

        .toast-timer-bar {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3.5px;
            background: rgba(0, 0, 0, 0.04);
        }

        .toast-timer-fill {
            height: 100%;
            width: 100%;
            transform-origin: left;
            background: linear-gradient(90deg, #10b981, #059669);
            animation: toastTimer 5s linear forwards;
        }

        .error-toast .toast-timer-fill {
            background: linear-gradient(90deg, #ef4444, #dc2626);
        }

        @keyframes slideInDownToast {
            from { opacity: 0; transform: translateY(-16px) scale(0.97); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* ── MY BOOKED APPOINTMENTS SECTION ── */
        .appointment-just-booked-banner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            padding: 24px 30px;
            background: linear-gradient(135deg, #047857 0%, #059669 45%, #10b981 100%);
            border-radius: 24px;
            color: #ffffff;
            margin-bottom: 36px;
            box-shadow: 0 16px 36px -6px rgba(5, 150, 105, 0.32), 0 0 0 1px rgba(255, 255, 255, 0.15);
            animation: fadeInDown 0.4s cubic-bezier(0.16, 1, 0.3, 1) ease;
            position: relative;
            overflow: hidden;
        }

        .appointment-just-booked-banner::after {
            content: '';
            position: absolute;
            top: -40px;
            right: -40px;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.18) 0%, rgba(255, 255, 255, 0) 70%);
            pointer-events: none;
        }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-14px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .booked-banner-left {
            display: flex;
            align-items: center;
            gap: 20px;
            flex: 1;
        }

        .booked-banner-icon {
            width: 56px;
            height: 56px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.22);
            backdrop-filter: blur(8px);
            display: grid;
            place-items: center;
            color: #ffffff;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .booked-banner-icon svg {
            width: 32px;
            height: 32px;
        }

        .booked-banner-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 3px 10px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(4px);
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        .pulse-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #a7f3d0;
            box-shadow: 0 0 8px #a7f3d0;
            animation: pulseGlow 1.8s infinite;
        }

        @keyframes pulseGlow {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.35); opacity: 0.7; }
        }

        .booked-banner-copy strong {
            display: block;
            font-size: 1.3rem;
            font-weight: 800;
            margin-bottom: 4px;
            letter-spacing: -0.01em;
        }

        .booked-banner-copy p {
            margin: 0;
            font-size: 0.96rem;
            color: rgba(255, 255, 255, 0.94);
            line-height: 1.5;
        }

        .booked-code-chip {
            display: inline-block;
            padding: 1px 8px;
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.25);
            font-weight: 800;
            letter-spacing: 0.03em;
            font-family: inherit;
        }

        .btn-view-slip-banner {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 13px 26px;
            border-radius: 16px;
            background: #ffffff;
            color: #047857;
            font-weight: 700;
            font-size: 0.96rem;
            border: none;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            white-space: nowrap;
            flex-shrink: 0;
        }

        .btn-view-slip-banner:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.18);
            background: #f0fdf4;
            color: #065f46;
        }

        .btn-view-slip-banner svg {
            width: 18px;
            height: 18px;
        }

        .appointments-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 26px;
        }

        .patient-appt-card {
            background: #ffffff;
            border: 1.5px solid #e8eef5;
            border-radius: 22px;
            padding: 26px;
            box-shadow: 0 4px 16px rgba(15, 34, 64, 0.03);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.25s ease;
            position: relative;
        }

        .patient-appt-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 32px rgba(15, 34, 64, 0.08);
            border-color: #10b981;
        }

        .appt-card-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 16px;
        }

        .appt-service-info {
            display: flex;
            align-items: center;
            gap: 14px;
            flex: 1;
        }

        .appt-service-icon {
            display: grid;
            place-items: center;
            width: 52px;
            height: 52px;
            border-radius: 16px;
            flex-shrink: 0;
            color: #ffffff;
        }

        .appt-service-icon svg {
            width: 26px;
            height: 26px;
        }

        .appt-service-copy h3 {
            margin: 0 0 3px;
            font-size: 1.18rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.25;
        }

        .appt-service-copy .appt-station-tag {
            font-size: 0.86rem;
            color: #64748b;
            font-weight: 500;
        }

        .appt-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 14px;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.01em;
            flex-shrink: 0;
            white-space: nowrap;
        }

        .appt-status-pill svg {
            width: 14px;
            height: 14px;
        }

        .appt-status-pill.status-pending {
            background: #fffbeb;
            color: #b45309;
            border: 1px solid #fde68a;
        }

        .appt-status-pill.status-confirmed,
        .appt-status-pill.status-approved {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }

        .appt-status-pill.status-serving {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }

        .appt-status-pill.status-completed {
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .appt-status-pill.status-cancelled {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .appt-id-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
        }

        .appt-id-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 10px;
            background: #f1f5f9;
            color: #334155;
            font-size: 0.88rem;
            font-weight: 700;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            border: 1px solid #e2e8f0;
        }

        .appt-time-relative-chip {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .appt-time-relative-chip.today {
            background: #fef3c7;
            color: #92400e;
        }

        .appt-time-relative-chip.tomorrow {
            background: #e0e7ff;
            color: #3730a3;
        }

        .appt-time-relative-chip.upcoming {
            background: #f1f5f9;
            color: #475569;
        }

        .appt-time-relative-chip.past {
            background: #f3f4f6;
            color: #9ca3af;
        }

        .appt-meta-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            padding: 16px;
            background: #f8fafc;
            border-radius: 16px;
            border: 1px solid #edf2f7;
            margin-bottom: 18px;
        }

        .appt-meta-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .appt-meta-item .meta-icon {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: #ffffff;
            display: grid;
            place-items: center;
            color: #059669;
            flex-shrink: 0;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
            border: 1px solid #e2e8f0;
        }

        .appt-meta-item .meta-icon svg {
            width: 17px;
            height: 17px;
        }

        .appt-meta-item small {
            display: block;
            font-size: 0.74rem;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            margin-bottom: 2px;
        }

        .appt-meta-item strong {
            display: block;
            font-size: 0.92rem;
            color: #0f172a;
            font-weight: 700;
            line-height: 1.3;
        }

        .appt-notes-box {
            padding: 10px 14px;
            border-radius: 12px;
            background: #fff8e7;
            border: 1px solid #fef08a;
            font-size: 0.84rem;
            color: #854d0e;
            margin-bottom: 16px;
            line-height: 1.4;
        }

        .appt-card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            padding-top: 16px;
            border-top: 1px solid #f1f5f9;
            margin-top: auto;
        }

        .btn-view-slip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 18px;
            border-radius: 12px;
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
            font-weight: 700;
            font-size: 0.88rem;
            cursor: pointer;
            transition: all 0.18s ease;
        }

        .btn-view-slip:hover {
            background: #059669;
            color: #ffffff;
            border-color: #059669;
            transform: translateY(-1px);
        }

        .btn-view-slip svg {
            width: 16px;
            height: 16px;
        }

        .btn-quick-download {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 16px;
            border-radius: 12px;
            background: #f8fafc;
            color: #334155;
            border: 1px solid #e2e8f0;
            font-weight: 600;
            font-size: 0.88rem;
            cursor: pointer;
            transition: all 0.18s ease;
        }

        .btn-quick-download:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        .btn-quick-download svg {
            width: 16px;
            height: 16px;
        }

        /* ── RECENT APPOINTMENT & APPOINTMENT HISTORY COMPANION ── */
        .latest-booking-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
            border-radius: 9999px;
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 6px;
        }

        .latest-booking-chip svg {
            width: 13px;
            height: 13px;
            color: #10b981;
        }

        .patient-appt-history-card {
            background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
            border: 1.5px dashed #cbd5e1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .patient-appt-history-card:hover {
            border-color: #10b981;
            border-style: solid;
            transform: translateY(-4px);
            box-shadow: 0 16px 32px rgba(5, 150, 105, 0.08);
        }

        .history-card-badge-label {
            display: inline-block;
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #059669;
            margin-bottom: 2px;
        }

        .history-card-description {
            font-size: 0.88rem;
            color: #64748b;
            line-height: 1.55;
            margin: 12px 0 16px;
        }

        .history-stats-preview-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 18px;
        }

        .history-stat-mini-pill {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 10px 8px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
            transition: all 0.15s ease;
        }

        .patient-appt-history-card:hover .history-stat-mini-pill {
            background: #f0fdf4;
            border-color: #bbf7d0;
        }

        .stat-mini-num {
            display: block;
            font-size: 1.15rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.1;
        }

        .patient-appt-history-card:hover .stat-mini-num {
            color: #047857;
        }

        .stat-mini-label {
            display: block;
            font-size: 0.72rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            margin-top: 3px;
        }

        .history-card-footer {
            padding-top: 16px;
            border-top: 1px solid #f1f5f9;
            margin-top: auto;
        }

        .btn-open-history-modal {
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: 14px;
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: #ffffff;
            border: none;
            font-size: 0.94rem;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.2);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .btn-open-history-modal:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(5, 150, 105, 0.32);
            background: linear-gradient(135deg, #047857 0%, #059669 100%);
        }

        .btn-open-history-modal svg {
            width: 17px;
            height: 17px;
        }

        .btn-open-history-modal .inline-icon svg {
            width: 14px;
            height: 14px;
            transition: transform 0.2s ease;
        }

        .btn-open-history-modal:hover .inline-icon svg {
            transform: translateX(4px);
        }

        /* ── HISTORY MODAL ── */
        .history-modal-card {
            background: #ffffff;
            border-radius: 26px;
            width: min(100% - 32px, 780px);
            max-height: 88vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(15, 34, 64, 0.25);
            transform: translateY(20px) scale(0.97);
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
        }

        .account-modal-overlay.active .history-modal-card {
            transform: translateY(0) scale(1);
        }

        .history-modal-header {
            padding: 22px 28px 18px;
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: #ffffff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
        }

        .history-modal-header-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .history-header-icon {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.2);
            display: grid;
            place-items: center;
            color: #ffffff;
            flex-shrink: 0;
        }

        .history-header-icon svg {
            width: 24px;
            height: 24px;
        }

        .history-modal-header h3 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 800;
            color: #ffffff;
        }

        .history-modal-header p {
            margin: 2px 0 0;
            font-size: 0.82rem;
            color: rgba(255, 255, 255, 0.9);
        }

        .history-modal-tabs-bar {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            overflow-x: auto;
            flex-shrink: 0;
        }

        .history-tab-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 9999px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            color: #475569;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.15s ease;
        }

        .history-tab-btn:hover {
            border-color: #cbd5e1;
            color: #0f172a;
        }

        .history-tab-btn.active {
            background: #ecfdf5;
            color: #047857;
            border-color: #10b981;
            font-weight: 700;
        }

        .history-tab-count {
            padding: 1px 6px;
            border-radius: 9999px;
            font-size: 0.72rem;
            background: #f1f5f9;
            color: #64748b;
        }

        .history-tab-btn.active .history-tab-count {
            background: #a7f3d0;
            color: #065f46;
        }

        .history-modal-body {
            padding: 20px 24px;
            overflow-y: auto;
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .history-item-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            padding: 18px 20px;
            transition: all 0.2s ease;
            position: relative;
        }

        .history-item-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
        }

        .history-item-card.is-latest-item {
            border-color: #86efac;
            background: linear-gradient(180deg, #f0fdf4 0%, #ffffff 40%);
        }

        .history-item-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 12px;
        }

        .history-item-service {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .history-item-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            flex-shrink: 0;
        }

        .history-item-icon svg {
            width: 20px;
            height: 20px;
        }

        .history-item-titles h4 {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
        }

        .history-item-titles span {
            font-size: 0.8rem;
            color: #64748b;
        }

        .history-item-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            background: #f8fafc;
            border-radius: 12px;
            padding: 10px 14px;
            margin: 10px 0;
            border: 1px solid #f1f5f9;
        }

        .history-grid-col small {
            display: block;
            font-size: 0.72rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .history-grid-col strong {
            display: block;
            font-size: 0.88rem;
            color: #1e293b;
        }

        .history-item-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 10px;
            margin-top: 10px;
            border-top: 1px solid #f1f5f9;
        }

        .history-modal-footer {
            padding: 14px 24px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
        }

        .history-footer-count {
            font-size: 0.84rem;
            color: #64748b;
            font-weight: 600;
        }

        /* ── APPOINTMENT SLIP MODAL ── */
        .slip-modal-card {
            background: #ffffff;
            border-radius: 26px;
            width: min(100% - 32px, 600px);
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 25px 60px rgba(15, 34, 64, 0.25);
            transform: translateY(20px) scale(0.97);
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
        }

        .slip-modal-header {
            padding: 24px 28px 20px;
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: #ffffff;
            border-top-left-radius: 26px;
            border-top-right-radius: 26px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .slip-modal-header-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .slip-header-icon {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.2);
            display: grid;
            place-items: center;
            color: #ffffff;
            flex-shrink: 0;
        }

        .slip-header-icon svg {
            width: 24px;
            height: 24px;
        }

        .slip-modal-header h3 {
            margin: 0;
            font-size: 1.22rem;
            font-weight: 800;
            color: #ffffff;
        }

        .slip-modal-header p {
            margin: 2px 0 0;
            font-size: 0.82rem;
            color: rgba(255, 255, 255, 0.9);
        }

        .slip-modal-body {
            padding: 26px 28px;
        }

        .slip-reference-hero {
            text-align: center;
            padding: 16px 20px;
            background: #f0fdf4;
            border: 1.5px dashed #86efac;
            border-radius: 16px;
            margin-bottom: 22px;
        }

        .slip-reference-hero span {
            display: block;
            font-size: 0.8rem;
            font-weight: 700;
            color: #047857;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 3px;
        }

        .slip-reference-hero strong {
            display: block;
            font-size: 1.85rem;
            font-weight: 800;
            color: #065f46;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            letter-spacing: 0.04em;
        }

        .slip-details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .slip-details-table tr {
            border-bottom: 1px solid #f1f5f9;
        }

        .slip-details-table td {
            padding: 11px 4px;
            font-size: 0.92rem;
        }

        .slip-details-table td.label-col {
            color: #64748b;
            font-weight: 600;
            width: 38%;
        }

        .slip-details-table td.val-col {
            color: #0f172a;
            font-weight: 700;
            text-align: right;
        }

        .val-col.status-pill {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: capitalize;
        }
        .val-col.status-pill.status-confirmed { background: #dcfce7; color: #15803d; }
        .val-col.status-pill.status-pending { background: #fef9c3; color: #a16207; }
        .val-col.status-pill.status-serving { background: #e0e7ff; color: #4338ca; }
        .val-col.status-pill.status-completed { background: #ccfbf1; color: #0f766e; }
        .val-col.status-pill.status-cancelled { background: #fee2e2; color: #b91c1c; }

        .slip-instructions-box {
            background: #f8fafc;
            border-radius: 14px;
            padding: 14px 16px;
            margin-bottom: 22px;
            font-size: 0.85rem;
            color: #475569;
            line-height: 1.5;
            border: 1px solid #e2e8f0;
        }

        .slip-instructions-box strong {
            display: block;
            color: #0f172a;
            margin-bottom: 2px;
        }

        .slip-modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            flex-wrap: wrap;
        }

        .btn-print-slip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            border-radius: 12px;
            background: #f1f5f9;
            color: #334155;
            border: 1.5px solid #cbd5e1;
            font-weight: 600;
            font-size: 0.88rem;
            cursor: pointer;
            transition: all 0.18s ease;
        }
        .btn-print-slip:hover {
            background: #e2e8f0;
            color: #0f172a;
        }
        .btn-print-slip svg {
            width: 16px;
            height: 16px;
        }
        .modal-download-btn {
            background: linear-gradient(135deg, #059669, #10b981) !important;
            color: #ffffff !important;
            border: none !important;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
        }
        .modal-download-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.35);
        }
        .modal-done-btn {
            background: #f8fafc !important;
            color: #475569 !important;
            border: 1.5px solid #e2e8f0 !important;
        }
        .modal-done-btn:hover {
            background: #e2e8f0 !important;
            color: #0f172a !important;
        }

        @media (max-width: 1200px) {
            .services-grid,
            .appointments-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 860px) {
            .dashboard-hero-inner {
                flex-direction: column;
                align-items: flex-start;
                gap: 24px;
            }

            .dashboard-hero-actions {
                justify-content: flex-start;
            }

            .events-grid,
            .services-grid,
            .appointments-grid {
                grid-template-columns: 1fr;
            }

            .appointment-just-booked-banner {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }

            .appt-meta-grid {
                grid-template-columns: 1fr;
            }

            .appt-card-footer {
                flex-direction: column;
                align-items: stretch;
            }

            .btn-view-slip,
            .btn-quick-download {
                width: 100%;
                justify-content: center;
            }

            .event-card {
                flex-direction: column;
            }

            .form-row-grid {
                grid-template-columns: 1fr;
            }

            .account-modal-header,
            .account-modal-body {
                padding-left: 20px;
                padding-right: 20px;
            }

            .account-action-box {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body id="top">
<header class="main-header">
    <div class="container nav-bar simple-nav">
        <a class="brand" href="dashboard.php">
            <span class="brand-icon"><?= iconSvg('heart'); ?></span>
            <span class="brand-copy">
                <strong>Bacolod Health Stations</strong>
                <small>Your Community Health Partner</small>
            </span>
        </a>
        <a class="contact-link" href="tel:<?= h($contact['phone']); ?>">
            <span class="inline-icon"><?= iconSvg('phone'); ?></span>
            <span><?= h($contact['phone']); ?></span>
        </a>
    </div>
</header>

<main class="dashboard-shell">
    <section class="dashboard-hero">
        <div class="container dashboard-hero-inner">
            <div class="dashboard-greeting">
                <div class="dashboard-tagline">
                    <span class="inline-icon"><?= iconSvg('sparkle'); ?></span>
                    <span>Your Health, Our Priority</span>
                </div>
                <h1>Welcome, <?= h($patientName); ?></h1>
                <p>Brgy. <?= h($patientBarangay); ?>, Bacolod City</p>
            </div>
            <div class="dashboard-hero-actions">
                <!-- Notification Bell Icon Button & Dropdown Popover -->
                <div class="notif-bell-wrap" id="notifBellWrap">
                    <button type="button" class="hero-nav-pill notif-bell-btn <?= $unreadNotifCount > 0 ? 'has-unread' : ''; ?>" id="notifBellBtn" aria-label="View notifications">
                        <span class="bell-icon-inner"><?= iconSvg('bell'); ?></span>
                        <span>Notifications</span>
                        <?php if ($unreadNotifCount > 0): ?>
                            <span class="notif-badge-count"><?= $unreadNotifCount; ?></span>
                        <?php endif; ?>
                    </button>

                    <!-- Notification Popover Dropdown -->
                    <div class="notif-dropdown-popover" id="notifDropdownPopover">
                        <div class="notif-popover-header">
                            <div class="notif-popover-title">
                                <strong>Notifications</strong>
                                <?php if ($unreadNotifCount > 0): ?>
                                    <span class="unread-chip"><?= $unreadNotifCount; ?> unread</span>
                                <?php endif; ?>
                            </div>
                            <button type="button" class="notif-close-btn" id="closeNotifPopoverBtn">✕</button>
                        </div>
                        <div class="notif-popover-list">
                            <?php if (empty($patientNotifications)): ?>
                                <div class="notif-empty-state">
                                    <div class="notif-empty-icon"><?= iconSvg('check-circle'); ?></div>
                                    <p>You have no notifications at this time.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($patientNotifications as $notif): ?>
                                    <?php $isFollowUp = ($notif['status'] ?? '') === 'Follow-up'; ?>
                                    <div class="notif-item-card <?= (int) ($notif['is_read'] ?? 0) === 0 ? 'is-unread' : ''; ?> <?= $isFollowUp ? 'is-followup' : ''; ?>">
                                        <div class="notif-item-icon">
                                            <?= $isFollowUp ? iconSvg('calendar') : iconSvg('check-circle'); ?>
                                        </div>
                                        <div class="notif-item-content">
                                            <div class="notif-item-title-row">
                                                <span class="notif-tag <?= $isFollowUp ? 'tag-followup' : 'tag-info'; ?>">
                                                    <?= $isFollowUp ? '📅 Follow-up' : 'Notice'; ?>
                                                </span>
                                                <span class="notif-time"><?= date('M j, g:i A', strtotime((string) $notif['created_at'])); ?></span>
                                            </div>
                                            <p class="notif-item-msg"><?= h((string) $notif['message']); ?></p>
                                            <?php if ((int) ($notif['is_read'] ?? 0) === 0): ?>
                                                <form method="post" style="margin-top: 6px;">
                                                    <?= csrf_field(); ?>
                                                    <input type="hidden" name="action" value="mark_notif_read">
                                                    <input type="hidden" name="notification_id" value="<?= (int) $notif['id']; ?>">
                                                    <button type="submit" class="btn-mark-read">Mark as read</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <a href="#appointmentsSection" class="hero-nav-pill <?= count($patientAppointments) > 0 ? 'has-appointments' : ''; ?>">Appointments (<?= count($patientAppointments); ?>)</a>
                <a href="#servicesSection" class="hero-nav-pill">Services</a>
                <a href="#eventsSection" class="hero-nav-pill">Events</a>
                <button type="button" class="hero-nav-pill" id="openAccountModalBtn">
                    <span>Account</span>
                </button>
                <form method="post" style="display: inline; margin: 0;">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="action" value="logout">
                    <button type="submit" class="hero-nav-pill">Logout</button>
                </form>
            </div>
        </div>
    </section>

    <div class="dashboard-main-content">
        <div class="container">
            <?php if ($updateSuccess && $updateMessage !== ''): ?>
                <div class="toast-notification-wrap" id="patientToastWrap">
                    <div class="toast-notification modern-toast success-toast" id="toastNotification" role="alert">
                        <div class="toast-icon-circle"><?= iconSvg('check-circle'); ?></div>
                        <div class="toast-content-wrap">
                            <strong>Profile Updated</strong>
                            <p><?= h($updateMessage); ?></p>
                        </div>
                        <button type="button" class="toast-dismiss-btn" onclick="dismissDashboardToast()" aria-label="Close notification"><?= iconSvg('x'); ?></button>
                        <div class="toast-timer-bar"><div class="toast-timer-fill"></div></div>
                    </div>
                </div>
            <?php elseif (!$updateSuccess && $updateMessage !== ''): ?>
                <div class="toast-notification-wrap" id="patientToastWrap">
                    <div class="toast-notification modern-toast error-toast" id="toastNotification" role="alert">
                        <div class="toast-icon-circle"><?= iconSvg('x'); ?></div>
                        <div class="toast-content-wrap">
                            <strong>Update Incomplete</strong>
                            <p><?= h($updateMessage); ?></p>
                        </div>
                        <button type="button" class="toast-dismiss-btn" onclick="dismissDashboardToast()" aria-label="Close notification"><?= iconSvg('x'); ?></button>
                        <div class="toast-timer-bar"><div class="toast-timer-fill"></div></div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- MY BOOKED APPOINTMENTS SECTION -->
            <section class="dashboard-section-block patient-appointments-section" id="appointmentsSection">
                <div class="section-title-wrap">
                    <div class="section-icon emerald" style="background:#ecfdf5;color:#059669;"><?= iconSvg('calendar'); ?></div>
                    <div class="section-title-copy">
                        <h2>My Booked Appointments</h2>
                        <p>Your scheduled health station appointment, booking details, and consultation history.</p>
                    </div>
                </div>

                <?php if (!empty($patientAppointments)): ?>
                    <?php
                    // Categorize appointments into active/upcoming vs completed/cancelled
                    $activeAppts = [];
                    $pastAppts = [];
                    $historyActiveCount = 0;
                    $historyCompletedCount = 0;
                    $historyCancelledCount = 0;
                    $historyAllCount = count($patientAppointments);

                    foreach ($patientAppointments as $aItem) {
                        $st = strtolower((string) ($aItem['status'] ?? 'pending'));
                        if (in_array($st, ['pending', 'confirmed', 'serving', 'approved'], true)) {
                            $activeAppts[] = $aItem;
                            $historyActiveCount++;
                        } else {
                            $pastAppts[] = $aItem;
                            if ($st === 'completed') {
                                $historyCompletedCount++;
                            } elseif ($st === 'cancelled') {
                                $historyCancelledCount++;
                            }
                        }
                    }

                    // Lifecycle Priority:
                    // 1. If patient has an upcoming/active appointment, display the newest active appointment.
                    // 2. Once that appointment is completed (or if all appointments are done), show the newest completed/past appointment.
                    // 3. The cycle repeats automatically whenever a new booking is made.
                    $isUpcomingFeatured = !empty($activeAppts);
                    $appt = $isUpcomingFeatured ? $activeAppts[0] : $patientAppointments[0];

                    $apptCode = (string) ($appt['appointment_code'] ?? $appt['reference_code'] ?? '');
                    $apptDate = (string) ($appt['preferred_date'] ?? '');
                    $apptTime = (string) ($appt['preferred_time'] ?? 'Daily Slot');
                    $apptService = (string) ($appt['service_name'] ?? 'General Consultation');
                    $apptStation = (string) ($appt['station_name'] ?? ('Barangay ' . $patientBarangay . ' Health Station'));
                    $apptStatus = (string) ($appt['status'] ?? 'Pending');
                    $apptServiceSlug = strtolower(trim((string) ($appt['service_slug'] ?? '')));
                    $apptColor = $serviceCatalog[$apptServiceSlug]['color'] ?? 'mint';
                    $apptIcon = $serviceCatalog[$apptServiceSlug]['icon'] ?? 'calendar';
                    $apptNotes = trim((string) ($appt['notes'] ?? ''));

                    $daysToAppt = (int) ceil((strtotime($apptDate) - strtotime(date('Y-m-d'))) / 86400);
                    $relativeLabel = '';
                    $relativeClass = '';
                    if ($daysToAppt === 0) {
                        $relativeLabel = 'Today';
                        $relativeClass = 'today';
                    } elseif ($daysToAppt === 1) {
                        $relativeLabel = 'Tomorrow';
                        $relativeClass = 'tomorrow';
                    } elseif ($daysToAppt > 1) {
                        $relativeLabel = 'In ' . $daysToAppt . ' days';
                        $relativeClass = 'upcoming';
                    } else {
                        $relativeLabel = 'Past Date';
                        $relativeClass = 'past';
                    }
                    ?>
                    <div class="appointments-grid">
                        <!-- Left Side: Featured Appointment Card (Upcoming or Recent Completed) -->
                        <article class="patient-appt-card">
                            <div>
                                <div class="appt-card-top">
                                    <div class="appt-service-info">
                                        <div class="appt-service-icon <?= h($apptColor); ?>" style="background: <?= $apptColor === 'pink' ? '#fce7f3;color:#ec4899;' : ($apptColor === 'blue' ? '#eff6ff;color:#2563eb;' : ($apptColor === 'gold' ? '#fff8e7;color:#ea8609;' : ($apptColor === 'violet' ? '#f4ebff;color:#8b3dff;' : ($apptColor === 'cyan' ? '#ecf8ff;color:#0ca8d7;' : ($apptColor === 'red' ? '#fee2e2;color:#ef4444;' : '#e8fbf2;color:#059669;'))))); ?>">
                                            <?= iconSvg($apptIcon); ?>
                                        </div>
                                        <div class="appt-service-copy">
                                            <?php if ($isUpcomingFeatured): ?>
                                                <span class="latest-booking-chip"><?= iconSvg('sparkle'); ?> Upcoming Appointment</span>
                                            <?php elseif (strcasecmp($apptStatus, 'Completed') === 0): ?>
                                                <span class="latest-booking-chip" style="background:#f0fdf4; color:#15803d; border-color:#bbf7d0;"><?= iconSvg('check-circle'); ?> Recent Appointment</span>
                                            <?php else: ?>
                                                <span class="latest-booking-chip"><?= iconSvg('sparkle'); ?> Recent Appointment</span>
                                            <?php endif; ?>
                                            <h3><?= h($apptService); ?></h3>
                                            <span class="appt-station-tag"><?= h($apptStation); ?></span>
                                        </div>
                                    </div>

                                    <?php if (strcasecmp($apptStatus, 'Pending') === 0): ?>
                                        <span class="appt-status-pill status-pending"><?= iconSvg('clock'); ?> Pending</span>
                                    <?php elseif (strcasecmp($apptStatus, 'Confirmed') === 0 || strcasecmp($apptStatus, 'Approved') === 0): ?>
                                        <span class="appt-status-pill status-confirmed"><?= iconSvg('check-circle'); ?> Confirmed</span>
                                    <?php elseif (strcasecmp($apptStatus, 'Serving') === 0): ?>
                                        <span class="appt-status-pill status-serving"><?= iconSvg('pulse'); ?> Serving</span>
                                    <?php elseif (strcasecmp($apptStatus, 'Completed') === 0): ?>
                                        <span class="appt-status-pill status-completed"><?= iconSvg('check'); ?> Completed</span>
                                    <?php elseif (strcasecmp($apptStatus, 'Cancelled') === 0): ?>
                                        <span class="appt-status-pill status-cancelled"><?= iconSvg('x'); ?> Cancelled</span>
                                    <?php else: ?>
                                        <span class="appt-status-pill status-pending"><?= iconSvg('clock'); ?> <?= h($apptStatus); ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="appt-id-row">
                                    <span class="appt-id-badge">ID: #<?= h($apptCode); ?></span>
                                    <?php if ($relativeLabel !== ''): ?>
                                        <span class="appt-time-relative-chip <?= $relativeClass; ?>"><?= $relativeLabel; ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="appt-meta-grid">
                                    <div class="appt-meta-item">
                                        <span class="meta-icon"><?= iconSvg('calendar'); ?></span>
                                        <div>
                                            <small><?= $isUpcomingFeatured ? 'Scheduled Date' : 'Consultation Date'; ?></small>
                                            <strong><?= date('D, M j, Y', strtotime($apptDate)); ?></strong>
                                        </div>
                                    </div>
                                    <div class="appt-meta-item">
                                        <span class="meta-icon"><?= iconSvg('clock'); ?></span>
                                        <div>
                                            <small>Time Slot</small>
                                            <strong><?= h($apptTime); ?></strong>
                                        </div>
                                    </div>
                                </div>

                                <?php if ($apptNotes !== ''): ?>
                                    <div class="appt-notes-box">
                                        <strong>Note:</strong> <?= h($apptNotes); ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="appt-card-footer">
                                <button type="button" class="btn-view-slip" data-appt="<?= htmlspecialchars(json_encode($appt), ENT_QUOTES, 'UTF-8'); ?>" onclick="openAppointmentSlipFromElement(this)">
                                    <?= iconSvg('eye'); ?>
                                    <span>View Slip</span>
                                </button>
                                <button type="button" class="btn-quick-download" data-appt="<?= htmlspecialchars(json_encode($appt), ENT_QUOTES, 'UTF-8'); ?>" onclick="downloadAppointmentSlipFromElement(this)">
                                    <?= iconSvg('download'); ?>
                                    <span>Download</span>
                                </button>
                            </div>
                        </article>

                        <!-- Right Side: Appointment History Companion Card (Completed Appointments Only) -->
                        <article class="patient-appt-card patient-appt-history-card" onclick="openAppointmentHistoryModal()">
                            <div>
                                <div class="appt-card-top">
                                    <div class="appt-service-info">
                                        <div class="appt-service-icon" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%); color: #ffffff;">
                                            <?= iconSvg('history'); ?>
                                        </div>
                                        <div class="appt-service-copy">
                                            <span class="history-card-badge-label">Record Archive</span>
                                            <h3>Appointment History</h3>
                                            <span class="appt-station-tag">Completed visits &amp; slips</span>
                                        </div>
                                    </div>
                                    <span class="appt-id-badge" style="background:#ecfdf5; color:#047857; border-color:#a7f3d0;">
                                        <?= $historyCompletedCount; ?> <?= $historyCompletedCount === 1 ? 'Completed' : 'Completed'; ?>
                                    </span>
                                </div>

                                <p class="history-card-description">
                                    Review your past completed consultation records, medical check-up history, and official station slips in one place.
                                </p>

                                <div class="history-stats-preview-grid" style="grid-template-columns: repeat(2, 1fr);">
                                    <div class="history-stat-mini-pill">
                                        <span class="stat-mini-num"><?= $historyCompletedCount; ?></span>
                                        <span class="stat-mini-label">Completed Visits</span>
                                    </div>
                                    <div class="history-stat-mini-pill">
                                        <span class="stat-mini-num"><?= $historyCompletedCount; ?></span>
                                        <span class="stat-mini-label">Official Slips</span>
                                    </div>
                                </div>
                            </div>

                            <div class="appt-card-footer history-card-footer">
                                <button type="button" class="btn-open-history-modal" onclick="openAppointmentHistoryModal()">
                                    <?= iconSvg('history'); ?>
                                    <span>View Completed History (<?= $historyCompletedCount; ?>)</span>
                                    <span class="inline-icon"><?= iconSvg('arrow'); ?></span>
                                </button>
                            </div>
                        </article>
                    </div>
                <?php else: ?>
                    <div class="empty-state-box">
                        <p>You have no active booked appointments at this time.</p>
                        <a href="#servicesSection" class="service-book-link" style="margin-top: 10px; display: inline-flex; align-items: center; gap: 6px;">
                            <span>Book an appointment below</span>
                            <span class="inline-icon"><?= iconSvg('arrow'); ?></span>
                        </a>
                    </div>
                <?php endif; ?>
            </section>

            <!-- UPCOMING FOLLOW-UP CONSULTATIONS NOTIFICATION BANNER -->
            <?php if (!empty($upcomingFollowUps)): ?>
                <section class="patient-followup-banner-section" style="margin-bottom: 38px;">
                    <div class="section-title-wrap" style="margin-bottom: 16px;">
                        <div class="section-icon emerald" style="background:#ecfdf5;color:#059669;"><?= iconSvg('calendar'); ?></div>
                        <div class="section-title-copy">
                            <h2>Scheduled Follow-up Check-ups</h2>
                            <p>Clinical follow-up consultations scheduled for you by your Barangay Health Station.</p>
                        </div>
                    </div>

                    <?php foreach ($upcomingFollowUps as $fu): ?>
                        <?php
                        $fuDate = (string) $fu['follow_up_date'];
                        $fuTime = (string) ($fu['follow_up_time'] ?: 'Morning Session (8:00 AM - 12:00 PM)');
                        $fuNotes = (string) ($fu['follow_up_notes'] ?: 'Follow-up clinical consultation and assessment.');
                        $fuStation = (string) ($fu['station_name'] ?: 'Barangay ' . $patientBarangay . ' Health Station');
                        $fuService = (string) ($fu['service_name'] ?: 'Health Consultation');
                        $fuCode = (string) ($fu['appointment_code'] ?? $fu['reference_code'] ?? '');
                        $daysRemaining = (int) ceil((strtotime($fuDate) - strtotime(date('Y-m-d'))) / 86400);
                        ?>
                        <article class="patient-followup-alert-card">
                            <div class="followup-alert-side-badge">
                                <span class="followup-calendar-icon"><?= iconSvg('calendar'); ?></span>
                                <div class="followup-days-tag">
                                    <?php if ($daysRemaining === 0): ?>
                                        <strong class="today-tag">TODAY</strong>
                                    <?php elseif ($daysRemaining === 1): ?>
                                        <strong class="soon-tag">TOMORROW</strong>
                                    <?php else: ?>
                                        <strong>IN <?= $daysRemaining; ?> DAYS</strong>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="followup-alert-main">
                                <div class="followup-alert-top-row">
                                    <span class="followup-notice-chip">
                                        <span class="live-dot-pulse"></span>
                                        <span>Follow-up Check-up Scheduled</span>
                                    </span>
                                    <?php if ($fuCode !== ''): ?>
                                        <span class="followup-ref-badge">Record #<?= h($fuCode); ?></span>
                                    <?php endif; ?>
                                </div>

                                <h3 class="followup-title">
                                    <?= h($fuService); ?> — Follow-up Consultation
                                </h3>

                                <div class="followup-meta-grid">
                                    <div class="followup-meta-item">
                                        <span class="meta-icon"><?= iconSvg('calendar'); ?></span>
                                        <div>
                                            <small>Follow-up Date</small>
                                            <strong><?= date('l, F j, Y', strtotime($fuDate)); ?></strong>
                                        </div>
                                    </div>
                                    <div class="followup-meta-item">
                                        <span class="meta-icon"><?= iconSvg('home'); ?></span>
                                        <div>
                                            <small>Health Station</small>
                                            <strong><?= h($fuStation); ?></strong>
                                        </div>
                                    </div>
                                </div>

                                <?php if ($fuNotes !== ''): ?>
                                    <div class="followup-notes-box">
                                        <span class="notes-box-label"><?= iconSvg('sparkle'); ?> Doctor / Health Station Instructions:</span>
                                        <p><?= h($fuNotes); ?></p>
                                    </div>
                                <?php endif; ?>

                                <div class="followup-footer-note">
                                    <span>📌 Please bring a valid ID and arrive at the health station during clinic hours on your scheduled date.</span>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>

            <!-- 1. UPCOMING EVENTS (Filtered strictly by the patient's registered barangay) -->
            <section class="dashboard-section-block" id="eventsSection">
                <div class="section-title-wrap">
                    <div class="section-icon gold"><?= iconSvg('sparkle'); ?></div>
                    <div class="section-title-copy">
                        <h2>Upcoming Events</h2>
                        <p>Health programs and announcements for <?= h($patientBarangay); ?> Barangay Health Station.</p>
                    </div>
                </div>

                <?php if (!empty($upcomingEvents)): ?>
                    <div class="events-grid">
                        <?php foreach ($upcomingEvents as $event): ?>
                            <article class="event-card <?= h($event['accent']); ?>">
                                <div class="event-icon <?= h($event['accent']); ?>">
                                    <?= iconSvg($event['icon']); ?>
                                </div>
                                <div class="event-content">
                                    <h3><?= h($event['title']); ?></h3>
                                    <div class="event-station"><?= h($event['station']); ?></div>
                                    <p><?= h($event['description']); ?></p>
                                    <div class="event-meta">
                                        <span><span class="inline-icon"><?= iconSvg('calendar'); ?></span><?= h($event['date']); ?></span>
                                        <span><span class="inline-icon"><?= iconSvg('clock'); ?></span><?= h($event['time']); ?></span>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state-box">
                        <p>No upcoming events scheduled for <?= h($patientBarangay); ?> Barangay Health Station at this time.</p>
                    </div>
                <?php endif; ?>
            </section>

            <!-- 2. HEALTH STATION SERVICES (Placed Below Upcoming Events) -->
            <section class="dashboard-section-block" id="servicesSection">
                <div class="section-title-wrap">
                    <div class="section-icon gold"><?= iconSvg('home'); ?></div>
                    <div class="section-title-copy">
                        <h2><?= h($patientBarangay); ?> Barangay Health Station</h2>
                        <p>Services offered at the barangay registered in your address.</p>
                    </div>
                </div>

                <?php if (!empty($servicesForBarangay)): ?>
                    <div class="services-grid">
                        <?php foreach ($servicesForBarangay as $service): ?>
                            <?php 
                                $scheduleLabel = service_schedule_label($stationSlug, $service['slug']);
                            ?>
                            <a href="index.php?barangay=<?= h(strtolower($stationSlug)); ?>&service=<?= h($service['slug']); ?>" class="service-card">
                                <div class="service-icon <?= h($service['color']); ?>">
                                    <?= iconSvg($service['icon']); ?>
                                </div>
                                <h3><?= h($service['title']); ?></h3>
                                <p><?= h($service['description']); ?></p>
                                <div class="service-schedule">
                                    <span><?= iconSvg('clock'); ?></span>
                                    <strong><?= h($scheduleLabel); ?></strong>
                                </div>
                                <span class="service-book-link">
                                    Book appointment
                                    <span class="inline-icon"><?= iconSvg('arrow'); ?></span>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state-box">
                        <p>No services currently available for this barangay station.</p>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</main>

<!-- ── ACCOUNT MODAL ────────────────────────────────────────── -->
<div class="account-modal-overlay" id="accountModal">
    <div class="account-modal-card" role="dialog" aria-labelledby="accountModalTitle" aria-modal="true">
        <div class="account-modal-header">
            <div class="account-header-left">
                <div class="account-header-icon">
                    <?= iconSvg('user'); ?>
                </div>
                <div>
                    <h3 id="accountModalTitle">My Account</h3>
                    <p>Manage your profile, credentials, and registered health center.</p>
                </div>
            </div>
            <button type="button" class="account-modal-close" id="closeAccountModalBtn" aria-label="Close modal">
                <?= iconSvg('x'); ?>
            </button>
        </div>

        <form method="post" action="dashboard.php" class="account-modal-body" id="accountForm">
            <?= csrf_field(); ?>
            <input type="hidden" name="action" value="update_account">
            <input type="hidden" name="change_password_active" id="changePasswordActive" value="0">
            <input type="hidden" name="change_address_active" id="changeAddressActive" value="0">

            <!-- Section 1: Personal & Contact Information -->
            <div class="account-section-divider">
                <?= iconSvg('user'); ?>
                <span>Personal Information</span>
            </div>

            <div class="form-row-grid">
                <div class="form-group-item">
                    <label for="accFirstName">First Name <span class="required">*</span></label>
                    <input type="text" id="accFirstName" name="first_name" class="form-input-field" value="<?= h($firstName); ?>" required>
                </div>
                <div class="form-group-item">
                    <label for="accLastName">Last Name <span class="required">*</span></label>
                    <input type="text" id="accLastName" name="last_name" class="form-input-field" value="<?= h($lastName); ?>" required>
                </div>
            </div>

            <div class="form-row-grid">
                <div class="form-group-item">
                    <label for="accMiddleName">Middle Name</label>
                    <input type="text" id="accMiddleName" name="middle_name" class="form-input-field" value="<?= h($middleName); ?>" placeholder="Optional">
                </div>
                <div class="form-group-item">
                    <label>Gender <span class="required">*</span></label>
                    <div class="gender-radio-options">
                        <label class="gender-radio-card <?= strcasecmp($gender, 'Male') === 0 ? 'selected' : ''; ?>">
                            <input type="radio" name="gender" value="Male" <?= strcasecmp($gender, 'Male') === 0 ? 'checked' : ''; ?> required>
                            <span class="custom-radio-circle"></span>
                            <span class="radio-label-text">Male</span>
                        </label>
                        <label class="gender-radio-card <?= strcasecmp($gender, 'Female') === 0 ? 'selected' : ''; ?>">
                            <input type="radio" name="gender" value="Female" <?= strcasecmp($gender, 'Female') === 0 ? 'checked' : ''; ?> required>
                            <span class="custom-radio-circle"></span>
                            <span class="radio-label-text">Female</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-row-grid">
                <div class="form-group-item">
                    <label for="accBirthDate">Birthdate <span class="required">*</span></label>
                    <input type="date" id="accBirthDate" name="birth_date" class="form-input-field" value="<?= h($birthDate); ?>" required>
                </div>
                <div class="form-group-item">
                    <label for="accPhone">Contact Number <span class="required">*</span></label>
                    <input type="tel" id="accPhone" name="contact_number" class="form-input-field" value="<?= h($contactNumber); ?>" placeholder="09XXXXXXXXX" required>
                </div>
            </div>

            <div class="form-row-grid">
                <div class="form-group-item">
                    <label for="accEmail">Email Address <span class="required">*</span></label>
                    <input type="email" id="accEmail" name="email" class="form-input-field" value="<?= h($email); ?>" required>
                </div>
                <div class="form-group-item">
                    <label for="accPatientId">Patient ID Number</label>
                    <input type="text" id="accPatientId" class="form-input-field" value="<?= h($patientId); ?>" readonly>
                </div>
            </div>

            <!-- Section 2: Security & Password Section (Button Trigger) -->
            <div class="account-section-divider">
                <?= iconSvg('lock'); ?>
                <span>Account Security</span>
            </div>

            <div class="account-action-box" id="passwordSummaryBox">
                <div class="action-box-info">
                    <strong>Password</strong>
                    <p>•••••••••••• (Account is secured)</p>
                </div>
                <button type="button" class="btn-action-toggle" id="btnTogglePasswordSection">
                    <?= iconSvg('lock'); ?>
                    <span>Change Password</span>
                </button>
            </div>

            <div class="collapsible-panel" id="passwordFieldsPanel">
                <div class="panel-header-inline">
                    <strong>Change Password</strong>
                    <button type="button" class="btn-text-cancel" id="btnCancelPassword">Cancel</button>
                </div>
                <div class="form-row-grid">
                    <div class="form-group-item">
                        <label for="accNewPassword">New Password <span class="required">*</span></label>
                        <div class="input-password-wrap">
                            <input type="password" id="accNewPassword" name="new_password" class="form-input-field" placeholder="At least 6 characters" minlength="6">
                            <button type="button" class="btn-toggle-eye" aria-label="Toggle password visibility">
                                <?= iconSvg('eye'); ?>
                            </button>
                        </div>
                    </div>
                    <div class="form-group-item">
                        <label for="accConfirmPassword">Confirm New Password <span class="required">*</span></label>
                        <div class="input-password-wrap">
                            <input type="password" id="accConfirmPassword" name="confirm_password" class="form-input-field" placeholder="Re-type new password" minlength="6">
                            <button type="button" class="btn-toggle-eye" aria-label="Toggle password visibility">
                                <?= iconSvg('eye'); ?>
                            </button>
                        </div>
                    </div>
                </div>
                <span class="form-help-hint">Your new password must be at least 6 characters long.</span>
            </div>

            <!-- Section 3: Registered Barangay & Address Section (Button Trigger) -->
            <div class="account-section-divider">
                <?= iconSvg('map'); ?>
                <span>Registered Barangay & Address</span>
            </div>

            <div class="account-action-box" id="addressSummaryBox">
                <div class="action-box-info">
                    <strong>Registered in Brgy. <?= h($patientBarangay); ?></strong>
                    <p><?= h($completeAddress !== '' ? $completeAddress : 'Brgy. ' . $patientBarangay . ', Bacolod City'); ?></p>
                </div>
                <button type="button" class="btn-action-toggle" id="btnToggleAddressSection">
                    <?= iconSvg('map'); ?>
                    <span>Change Address</span>
                </button>
            </div>

            <div class="collapsible-panel" id="addressFieldsPanel">
                <div class="panel-header-inline">
                    <strong>Relocated or Moved Address</strong>
                    <button type="button" class="btn-text-cancel" id="btnCancelAddress">Cancel</button>
                </div>
                <div class="address-change-notice">
                    <span class="notice-icon"><?= iconSvg('sparkle'); ?></span>
                    <span>Changing your registered barangay will update your assigned health station services and local announcements.</span>
                </div>
                <div class="form-row-grid">
                    <div class="form-group-item">
                        <label for="accBarangay">New Barangay <span class="required">*</span></label>
                        <select id="accBarangay" name="barangay" class="form-input-field">
                            <?php foreach ($barangayOptions as $bgy): ?>
                                <option value="<?= h($bgy); ?>" <?= strcasecmp($bgy, $patientBarangay) === 0 ? 'selected' : ''; ?>>
                                    Brgy. <?= h($bgy); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group-item">
                        <label for="accPurok">Purok / Zone</label>
                        <select id="accPurok" name="purok" class="form-input-field">
                            <option value="">Select Purok</option>
                            <?php 
                                $currentBarangayPuroks = $purokOptionsByBarangay[$patientBarangay] ?? [];
                                foreach ($currentBarangayPuroks as $pOption): 
                            ?>
                                <option value="<?= h($pOption); ?>" <?= strcasecmp($pOption, $purok) === 0 ? 'selected' : ''; ?>>
                                    <?= h($pOption); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row-grid" style="margin-bottom: 0;">
                    <div class="form-group-item">
                        <label for="accStreet">Street / House No.</label>
                        <input type="text" id="accStreet" name="street" class="form-input-field" value="<?= h($street); ?>" placeholder="e.g. Block 4 Lot 12, Main Street">
                    </div>
                    <div class="form-group-item">
                        <label for="accCity">City / Municipality</label>
                        <input type="text" id="accCity" class="form-input-field" value="Bacolod City" readonly>
                    </div>
                </div>
            </div>

            <div class="account-modal-footer">
                <button type="button" class="btn-cancel" id="cancelAccountBtn">Close</button>
                <button type="submit" class="btn-save-account">
                    <?= iconSvg('check'); ?>
                    <span>Save Changes</span>
                </button>
            </div>
        </form>
    </div>
</div>

<footer class="footer" id="footer">
    <div class="container footer-grid">
        <div>
            <div class="brand footer-brand">
                <span class="brand-icon"><?= iconSvg('heart'); ?></span>
                <span class="brand-copy"><strong>Bacolod Health Stations</strong></span>
            </div>
            <p>Providing quality healthcare services to the communities of Bacolod City.</p>
        </div>
        <div>
            <h3>Contact Information</h3>
            <ul class="footer-list">
                <li><span class="inline-icon"><?= iconSvg('phone'); ?></span><?= h($contact['phone']); ?></li>
                <li><span class="inline-icon"><?= iconSvg('map'); ?></span><?= h($contact['address']); ?></li>
                <li><span class="inline-icon"><?= iconSvg('clock'); ?></span><?= h($contact['hours']); ?></li>
            </ul>
        </div>
        <div>
            <h3>Quick Links</h3>
            <ul class="footer-links">
                <li><a href="#servicesSection">Health Services</a></li>
                <li><a href="#eventsSection">Upcoming Events</a></li>
                <li><a href="#top">Back to Top</a></li>
            </ul>
        </div>
    </div>
    <div class="container footer-bottom">
        <p>&copy; 2026 Bacolod Health Stations. All rights reserved.</p>
    </div>
</footer>

<script src="assets/js/app.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href');
            if (targetId && targetId !== '#') {
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    e.preventDefault();
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });

    // Account Modal logic
    const accountModal = document.getElementById('accountModal');
    const openAccountModalBtn = document.getElementById('openAccountModalBtn');
    const closeAccountModalBtn = document.getElementById('closeAccountModalBtn');
    const cancelAccountBtn = document.getElementById('cancelAccountBtn');

    function openModal() {
        accountModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        accountModal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    if (openAccountModalBtn) {
        openAccountModalBtn.addEventListener('click', openModal);
    }

    if (closeAccountModalBtn) {
        closeAccountModalBtn.addEventListener('click', closeModal);
    }

    if (cancelAccountBtn) {
        cancelAccountBtn.addEventListener('click', closeModal);
    }

    // Close on overlay click
    if (accountModal) {
        accountModal.addEventListener('click', function (e) {
            if (e.target === accountModal) {
                closeModal();
            }
        });
    }

    // Close on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            if (accountModal && accountModal.classList.contains('active')) {
                closeModal();
            }
            const historyModal = document.getElementById('appointmentHistoryModal');
            if (historyModal && historyModal.classList.contains('active')) {
                closeAppointmentHistoryModal();
            }
            const slipModal = document.getElementById('appointmentSlipModal');
            if (slipModal && slipModal.classList.contains('active')) {
                closeAppointmentSlipModal();
            }
        }
    });

    // Toggle Password Fields
    const btnTogglePasswordSection = document.getElementById('btnTogglePasswordSection');
    const btnCancelPassword = document.getElementById('btnCancelPassword');
    const passwordSummaryBox = document.getElementById('passwordSummaryBox');
    const passwordFieldsPanel = document.getElementById('passwordFieldsPanel');
    const changePasswordActive = document.getElementById('changePasswordActive');
    const accNewPassword = document.getElementById('accNewPassword');
    const accConfirmPassword = document.getElementById('accConfirmPassword');

    if (btnTogglePasswordSection) {
        btnTogglePasswordSection.addEventListener('click', function () {
            passwordSummaryBox.style.display = 'none';
            passwordFieldsPanel.classList.add('open');
            changePasswordActive.value = '1';
            accNewPassword.focus();
        });
    }

    if (btnCancelPassword) {
        btnCancelPassword.addEventListener('click', function () {
            passwordFieldsPanel.classList.remove('open');
            passwordSummaryBox.style.display = 'flex';
            changePasswordActive.value = '0';
            accNewPassword.value = '';
            accConfirmPassword.value = '';
        });
    }

    // Toggle Address Fields
    const btnToggleAddressSection = document.getElementById('btnToggleAddressSection');
    const btnCancelAddress = document.getElementById('btnCancelAddress');
    const addressSummaryBox = document.getElementById('addressSummaryBox');
    const addressFieldsPanel = document.getElementById('addressFieldsPanel');
    const changeAddressActive = document.getElementById('changeAddressActive');

    if (btnToggleAddressSection) {
        btnToggleAddressSection.addEventListener('click', function () {
            addressSummaryBox.style.display = 'none';
            addressFieldsPanel.classList.add('open');
            changeAddressActive.value = '1';
        });
    }

    if (btnCancelAddress) {
        btnCancelAddress.addEventListener('click', function () {
            addressFieldsPanel.classList.remove('open');
            addressSummaryBox.style.display = 'flex';
            changeAddressActive.value = '0';
        });
    }

    // Toggle Password Visibility (Eye icon)
    document.querySelectorAll('.btn-toggle-eye').forEach(btn => {
        btn.addEventListener('click', function () {
            const input = this.previousElementSibling;
            if (input && input.tagName === 'INPUT') {
                if (input.type === 'password') {
                    input.type = 'text';
                    this.style.color = '#059669';
                } else {
                    input.type = 'password';
                    this.style.color = '#94a3b8';
                }
            }
        });
    });

    // Gender Radio Sync
    document.querySelectorAll('input[name="gender"]').forEach(radio => {
        radio.addEventListener('change', function () {
            document.querySelectorAll('.gender-radio-card').forEach(card => card.classList.remove('selected'));
            if (this.checked) {
                this.closest('.gender-radio-card')?.classList.add('selected');
            }
        });
    });

    // Dynamic Purok Dropdown by Barangay in Account Modal
    const puroksByBarangay = <?= json_encode($purokOptionsByBarangay, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?: '{}'; ?>;
    const accBarangaySelect = document.getElementById('accBarangay');
    const accPurokSelect = document.getElementById('accPurok');

    if (accBarangaySelect && accPurokSelect) {
        accBarangaySelect.addEventListener('change', function () {
            const selectedBarangay = this.value.trim();
            const purokList = puroksByBarangay[selectedBarangay] || [];
            
            accPurokSelect.innerHTML = '<option value="">Select Purok</option>';
            purokList.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p;
                opt.textContent = p;
                accPurokSelect.appendChild(opt);
            });
        });
    }

    // Auto-hide toast notification after 5 seconds
    window.dismissDashboardToast = function () {
        const toast = document.getElementById('toastNotification');
        const wrap = document.getElementById('patientToastWrap');
        if (toast) {
            toast.classList.add('hide-toast');
            setTimeout(function () {
                if (wrap) wrap.remove();
                else toast.remove();
            }, 450);
        }
    };

    const toastNotification = document.getElementById('toastNotification');
    if (toastNotification) {
        setTimeout(function () {
            window.dismissDashboardToast();
        }, 5000);
    }

    <?php if ($justBookedAppt !== null): ?>
    if (typeof window.showSystemToast === 'function') {
        window.showSystemToast(
            'Your appointment for <?= addslashes((string) $justBookedAppt['service_name']); ?> on <?= date('M j, Y', strtotime((string) $justBookedAppt['preferred_date'])); ?> (<?= addslashes((string) $justBookedAppt['preferred_time']); ?>) has been scheduled.',
            {
                title: 'Appointment Booked',
                type: 'success',
                theme: 'patient',
                badge: '#<?= addslashes((string) ($justBookedAppt['appointment_code'] ?? $justBookedAppt['reference_code'])); ?>',
                duration: 6000
            }
        );
    }
    <?php endif; ?>

    // Notification Bell Popover Toggle
    const notifBellBtn = document.getElementById('notifBellBtn');
    const notifDropdownPopover = document.getElementById('notifDropdownPopover');
    const closeNotifPopoverBtn = document.getElementById('closeNotifPopoverBtn');

    // Notification Popover Interaction (Delegated for Dynamic DOM Updates)
    document.addEventListener('click', function (e) {
        const bellBtn = e.target.closest('#notifBellBtn');
        const closeBtn = e.target.closest('#closeNotifPopoverBtn');
        const popover = document.getElementById('notifDropdownPopover');

        if (bellBtn) {
            e.stopPropagation();
            if (popover) popover.classList.toggle('open');
            return;
        }
        if (closeBtn) {
            e.stopPropagation();
            if (popover) popover.classList.remove('open');
            return;
        }
        if (popover && popover.classList.contains('open')) {
            if (!popover.contains(e.target)) {
                popover.classList.remove('open');
            }
        }
    });

    window.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            const popover = document.getElementById('notifDropdownPopover');
            if (popover && popover.classList.contains('open')) {
                popover.classList.remove('open');
            }
        }
    });

    // Clean URL params if present
    if (window.location.search.includes('booked=') || window.location.search.includes('profile_update=')) {
        try {
            const cleanUrl = window.location.pathname;
            window.history.replaceState({}, document.title, cleanUrl);
        } catch (e) {}
    }

    // ── 5-SECOND BACKGROUND IN-PLACE APPOINTMENT & TABLE LIVE SYNC ──
    // Seamlessly refreshes booked appointments, confirmation statuses, and notifications without reloading the page
    let isPatientSyncing = false;
    setInterval(async function () {
        if (isPatientSyncing) return;
        const activeEl = document.activeElement;
        const isTyping = activeEl && (activeEl.tagName === 'INPUT' || activeEl.tagName === 'TEXTAREA' || activeEl.tagName === 'SELECT');
        const accountModal = document.getElementById('accountModal');
        const isAccountModalOpen = accountModal && (accountModal.classList.contains('open') || accountModal.classList.contains('active'));
        const slipModal = document.getElementById('appointmentSlipModal');
        const isSlipModalOpen = slipModal && (slipModal.classList.contains('open') || slipModal.classList.contains('active'));
        const historyModal = document.getElementById('appointmentHistoryModal');
        const isHistoryModalOpen = historyModal && (historyModal.classList.contains('open') || historyModal.classList.contains('active'));
        const notifDropdown = document.getElementById('notifDropdownPopover');
        const isNotifOpen = notifDropdown && notifDropdown.classList.contains('open');

        if (isTyping || isAccountModalOpen || isSlipModalOpen || isHistoryModalOpen) return;

        try {
            isPatientSyncing = true;
            const res = await fetch(window.location.href, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) return;
            const html = await res.text();
            const parser = new DOMParser();
            const newDoc = parser.parseFromString(html, 'text/html');

            // 1. In-place sync for My Booked Appointments section
            const currentAppts = document.getElementById('appointmentsSection');
            const newAppts = newDoc.getElementById('appointmentsSection');
            if (currentAppts && newAppts && currentAppts.innerHTML !== newAppts.innerHTML) {
                currentAppts.innerHTML = newAppts.innerHTML;
            }

            // 2. In-place sync for Notification Bell & Dropdown (if closed)
            if (!isNotifOpen) {
                const currentNotifWrap = document.getElementById('notifBellWrap');
                const newNotifWrap = newDoc.getElementById('notifBellWrap');
                if (currentNotifWrap && newNotifWrap && currentNotifWrap.innerHTML !== newNotifWrap.innerHTML) {
                    currentNotifWrap.innerHTML = newNotifWrap.innerHTML;
                }
            }

            // 3. In-place sync for Appointment counter in top navigation pill
            const currentNavPill = document.querySelector('a[href="#appointmentsSection"]');
            const newNavPill = newDoc.querySelector('a[href="#appointmentsSection"]');
            if (currentNavPill && newNavPill && currentNavPill.innerHTML !== newNavPill.innerHTML) {
                currentNavPill.innerHTML = newNavPill.innerHTML;
                currentNavPill.className = newNavPill.className;
            }
        } catch (err) {
            console.debug('Patient live sync notice:', err);
        } finally {
            isPatientSyncing = false;
        }
    }, 5000);
});

/* ── APPOINTMENT HISTORY MODAL JS ── */
function openAppointmentHistoryModal() {
    const modal = document.getElementById('appointmentHistoryModal');
    if (modal) {
        modal.classList.add('active');
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
}

function closeAppointmentHistoryModal() {
    const modal = document.getElementById('appointmentHistoryModal');
    if (modal) {
        modal.classList.remove('active');
        modal.classList.remove('open');
        const accountModal = document.getElementById('accountModal');
        const slipModal = document.getElementById('appointmentSlipModal');
        const isAccountOpen = accountModal && accountModal.classList.contains('active');
        const isSlipOpen = slipModal && slipModal.classList.contains('active');
        if (!isAccountOpen && !isSlipOpen) {
            document.body.style.overflow = '';
        }
    }
}

function filterHistoryModal(statusFilter, clickedTab) {
    document.querySelectorAll('.history-tab-btn').forEach(btn => btn.classList.remove('active'));
    if (clickedTab) clickedTab.classList.add('active');
    
    const items = document.querySelectorAll('.history-item-card');
    items.forEach(item => {
        const itemStatus = (item.getAttribute('data-status') || '').toLowerCase();
        if (statusFilter === 'all') {
            item.style.display = 'block';
        } else if (statusFilter === 'active') {
            if (['pending', 'confirmed', 'serving', 'approved'].includes(itemStatus)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        } else if (statusFilter === 'completed') {
            item.style.display = itemStatus === 'completed' ? 'block' : 'none';
        } else if (statusFilter === 'cancelled') {
            item.style.display = itemStatus === 'cancelled' ? 'block' : 'none';
        }
    });
}

/* ── APPOINTMENT SLIP MODAL & DOWNLOAD JS ── */
let currentModalApptData = null;

function openAppointmentSlipFromElement(el) {
    if (!el) return;
    try {
        const raw = el.getAttribute('data-appt');
        const data = JSON.parse(raw || '{}');
        openAppointmentSlipModal(data);
    } catch(err) {
        console.error('Failed to parse appointment data:', err);
    }
}

function downloadAppointmentSlipFromElement(el) {
    if (!el) return;
    try {
        const raw = el.getAttribute('data-appt');
        const data = JSON.parse(raw || '{}');
        downloadAppointmentSlipDirectly(data);
    } catch(err) {
        console.error('Failed to download appointment slip:', err);
    }
}

function openAppointmentSlipModal(appt) {
    if (!appt) return;
    currentModalApptData = appt;

    const modal = document.getElementById('appointmentSlipModal');
    const codeEl = document.getElementById('slipModalCode');
    const stationEl = document.getElementById('slipModalStation');
    const serviceEl = document.getElementById('slipModalService');
    const dateEl = document.getElementById('slipModalDate');
    const slotEl = document.getElementById('slipModalSlot');
    const nameEl = document.getElementById('slipModalName');
    const contactEl = document.getElementById('slipModalContact');
    const addressEl = document.getElementById('slipModalAddress');
    const statusEl = document.getElementById('slipModalStatus');

    const apptCode = appt.appointment_code || appt.reference_code || 'N/A';
    const patientFullName = [appt.first_name || '', appt.middle_name || '', appt.last_name || ''].filter(Boolean).join(' ') || 'Patient';
    
    let formattedDate = appt.preferred_date || 'N/A';
    try {
        const d = new Date(appt.preferred_date + 'T00:00:00');
        if (!isNaN(d.getTime())) {
            formattedDate = d.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        }
    } catch(e) {}

    if (codeEl) codeEl.textContent = '#' + String(apptCode).replace(/^#/, '');
    if (stationEl) stationEl.textContent = appt.station_name || 'Barangay Health Station';
    if (serviceEl) serviceEl.textContent = appt.service_name || 'General Health Service';
    if (dateEl) dateEl.textContent = formattedDate;
    if (slotEl) slotEl.textContent = appt.preferred_time || 'Daily Slot';
    if (nameEl) nameEl.textContent = patientFullName;
    if (contactEl) contactEl.textContent = appt.contact_number || 'None provided';
    if (addressEl) addressEl.textContent = appt.complete_address || 'Bacolod City';
    if (statusEl) {
        const st = String(appt.status || 'Pending');
        statusEl.innerHTML = '<span class="val-col status-pill status-' + st.toLowerCase() + '">' + st + '</span>';
    }

    if (modal) {
        modal.classList.add('active');
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
}

function closeAppointmentSlipModal() {
    const modal = document.getElementById('appointmentSlipModal');
    if (modal) {
        modal.classList.remove('active');
        modal.classList.remove('open');
        const accountModal = document.getElementById('accountModal');
        const historyModal = document.getElementById('appointmentHistoryModal');
        const isAccountOpen = accountModal && (accountModal.classList.contains('active') || accountModal.classList.contains('open'));
        const isHistoryOpen = historyModal && (historyModal.classList.contains('active') || historyModal.classList.contains('open'));
        if (!isAccountOpen && !isHistoryOpen) {
            document.body.style.overflow = '';
        }
    }
}

function downloadModalSlip() {
    if (currentModalApptData) {
        downloadAppointmentSlipDirectly(currentModalApptData);
    }
}

function printModalSlip() {
    window.print();
}

function downloadAppointmentSlipDirectly(appt) {
    if (!appt) return;

    const apptCode = appt.appointment_code || appt.reference_code || 'N/A';
    const patientFullName = [appt.first_name || '', appt.middle_name || '', appt.last_name || ''].filter(Boolean).join(' ') || 'Patient';
    
    let formattedDate = appt.preferred_date || 'N/A';
    try {
        const d = new Date(appt.preferred_date + 'T00:00:00');
        if (!isNaN(d.getTime())) {
            formattedDate = d.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        }
    } catch(e) {}

    const canvas = document.createElement('canvas');
    canvas.width = 1400;
    canvas.height = 1000;
    const ctx = canvas.getContext('2d');

    // Background
    ctx.fillStyle = '#f8fafc';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    // Header gradient
    const gradient = ctx.createLinearGradient(100, 80, 1300, 360);
    gradient.addColorStop(0, '#059669');
    gradient.addColorStop(1, '#10b981');
    
    // Header banner box
    ctx.fillStyle = gradient;
    ctx.beginPath();
    ctx.roundRect(80, 60, 1240, 240, [24, 24, 24, 24]);
    ctx.fill();

    ctx.fillStyle = '#ffffff';
    ctx.font = '700 48px Outfit, sans-serif';
    ctx.fillText('Official Appointment Slip', 140, 140);
    ctx.font = '400 24px Outfit, sans-serif';
    ctx.fillText('Bacolod Barangay Health Stations • Community Healthcare Delivery', 140, 185);

    // ID Badge in header
    ctx.fillStyle = 'rgba(255, 255, 255, 0.2)';
    ctx.beginPath();
    ctx.roundRect(140, 215, 480, 60, [14, 14, 14, 14]);
    ctx.fill();

    ctx.fillStyle = '#ffffff';
    ctx.font = '700 22px Outfit, sans-serif';
    ctx.fillText('APPOINTMENT ID: #' + apptCode, 160, 254);

    // Status pill in header
    ctx.fillStyle = '#ffffff';
    ctx.beginPath();
    ctx.roundRect(1000, 100, 260, 50, [25, 25, 25, 25]);
    ctx.fill();
    ctx.fillStyle = '#047857';
    ctx.font = '700 20px Outfit, sans-serif';
    ctx.fillText('STATUS: ' + (appt.status || 'Pending').toUpperCase(), 1030, 133);

    // Main Details Card
    ctx.fillStyle = '#ffffff';
    ctx.beginPath();
    ctx.roundRect(80, 330, 1240, 480, [20, 20, 20, 20]);
    ctx.fill();
    ctx.strokeStyle = '#e2e8f0';
    ctx.lineWidth = 2;
    ctx.stroke();

    ctx.fillStyle = '#0f172a';
    ctx.font = '700 32px Outfit, sans-serif';
    ctx.fillText('Appointment & Patient Information', 130, 400);

    const rows = [
        ['Health Station:', appt.station_name || 'Barangay Health Station'],
        ['Service:', appt.service_name || 'General Consultation'],
        ['Appointment Date:', formattedDate],
        ['Time Slot:', appt.preferred_time || 'Daily Slot'],
        ['Patient Name:', patientFullName],
        ['Contact Number:', appt.contact_number || 'N/A'],
        ['Patient ID:', appt.patient_id || 'Registered Account'],
        ['Registered Address:', appt.complete_address || 'Bacolod City']
    ];

    let rowY = 460;
    rows.forEach((r, idx) => {
        const isLeft = idx % 2 === 0;
        const colX = isLeft ? 130 : 720;
        
        ctx.fillStyle = '#64748b';
        ctx.font = '600 20px Outfit, sans-serif';
        ctx.fillText(r[0], colX, rowY);

        ctx.fillStyle = '#0f172a';
        ctx.font = '700 22px Outfit, sans-serif';
        ctx.fillText(r[1], colX + 220, rowY);

        if (!isLeft) {
            rowY += 65;
        }
    });

    // Footer notice box
    ctx.fillStyle = '#f0fdf4';
    ctx.beginPath();
    ctx.roundRect(80, 840, 1240, 100, [16, 16, 16, 16]);
    ctx.fill();
    ctx.strokeStyle = '#bbf7d0';
    ctx.stroke();

    ctx.fillStyle = '#047857';
    ctx.font = '700 20px Outfit, sans-serif';
    ctx.fillText('📌 Important Reminder:', 120, 880);
    ctx.font = '400 18px Outfit, sans-serif';
    ctx.fillText('Please arrive 10-15 minutes prior to your scheduled slot with a valid ID. Present this slip to the station desk.', 120, 912);

    const link = document.createElement('a');
    link.download = `Appointment-Slip-${apptCode}.png`;
    link.href = canvas.toDataURL('image/png');
    link.click();
}
</script>

<!-- ── APPOINTMENT SLIP MODAL ── -->
<div class="account-modal-overlay" id="appointmentSlipModal" onclick="if(event.target===this)closeAppointmentSlipModal()">
    <div class="slip-modal-card" role="dialog" aria-labelledby="slipModalTitle" aria-modal="true">
        <div class="slip-modal-header">
            <div class="slip-modal-header-left">
                <div class="slip-header-icon">
                    <?= iconSvg('calendar'); ?>
                </div>
                <div>
                    <h3 id="slipModalTitle">Official Appointment Slip</h3>
                    <p>Barangay Health Delivery System • Booking Confirmation</p>
                </div>
            </div>
            <button type="button" class="notif-close-btn" style="color:#fff;" onclick="closeAppointmentSlipModal()" aria-label="Close modal">✕</button>
        </div>

        <div class="slip-modal-body">
            <div class="slip-reference-hero">
                <span>Appointment Confirmation ID</span>
                <strong id="slipModalCode">#APPT-00000</strong>
            </div>

            <table class="slip-details-table">
                <tbody>
                    <tr>
                        <td class="label-col">Health Station</td>
                        <td class="val-col" id="slipModalStation">-</td>
                    </tr>
                    <tr>
                        <td class="label-col">Service Program</td>
                        <td class="val-col" id="slipModalService">-</td>
                    </tr>
                    <tr>
                        <td class="label-col">Appointment Date</td>
                        <td class="val-col" id="slipModalDate">-</td>
                    </tr>
                    <tr>
                        <td class="label-col">Service Slot</td>
                        <td class="val-col" id="slipModalSlot">-</td>
                    </tr>
                    <tr>
                        <td class="label-col">Patient Name</td>
                        <td class="val-col" id="slipModalName">-</td>
                    </tr>
                    <tr>
                        <td class="label-col">Contact Number</td>
                        <td class="val-col" id="slipModalContact">-</td>
                    </tr>
                    <tr>
                        <td class="label-col">Registered Address</td>
                        <td class="val-col" id="slipModalAddress">-</td>
                    </tr>
                    <tr>
                        <td class="label-col">Booking Status</td>
                        <td class="val-col" id="slipModalStatus">-</td>
                    </tr>
                </tbody>
            </table>

            <div class="slip-instructions-box">
                <strong>📌 Important Station Reminder:</strong>
                Please arrive 10-15 minutes prior to your selected schedule slot and present this Appointment Slip or Confirmation ID to the front desk.
            </div>

            <div class="slip-modal-actions">
                <button type="button" class="btn-print-slip" onclick="window.print()">
                    <?= iconSvg('arrow'); ?>
                    <span>Print Slip</span>
                </button>
                <button type="button" class="btn-quick-download modal-download-btn" onclick="downloadModalSlip()">
                    <?= iconSvg('download'); ?>
                    <span>Download Image Slip</span>
                </button>
                <button type="button" class="btn-view-slip modal-done-btn" onclick="closeAppointmentSlipModal()">
                    <span>Done</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ── APPOINTMENT HISTORY MODAL (COMPLETED APPOINTMENTS ONLY) ── -->
<div class="account-modal-overlay" id="appointmentHistoryModal" onclick="if(event.target===this)closeAppointmentHistoryModal()">
    <div class="history-modal-card" role="dialog" aria-labelledby="historyModalTitle" aria-modal="true">
        <div class="history-modal-header">
            <div class="history-modal-header-left">
                <div class="history-header-icon">
                    <?= iconSvg('history'); ?>
                </div>
                <div>
                    <h3 id="historyModalTitle">Completed Appointment History</h3>
                    <p>Archive of your past completed health station check-ups &amp; medical consultations</p>
                </div>
            </div>
            <button type="button" class="notif-close-btn" style="color:#fff;" onclick="closeAppointmentHistoryModal()" aria-label="Close modal">✕</button>
        </div>

        <?php
        $completedAppointments = array_values(array_filter($patientAppointments, static fn(array $a): bool => strcasecmp((string) ($a['status'] ?? ''), 'Completed') === 0));
        $completedCount = count($completedAppointments);
        ?>

        <div class="history-modal-tabs-bar">
            <div class="history-tab-btn active" style="cursor: default;">
                <?= iconSvg('check-circle'); ?>
                <span>Completed Consultations</span>
                <span class="history-tab-count"><?= $completedCount; ?></span>
            </div>
        </div>

        <div class="history-modal-body">
            <?php if (empty($completedAppointments)): ?>
                <div class="empty-state-box" style="margin: 36px 0; text-align: center;">
                    <div style="width: 52px; height: 52px; border-radius: 50%; background: #ecfdf5; color: #059669; display: grid; place-items: center; margin: 0 auto 12px;">
                        <?= iconSvg('check-circle'); ?>
                    </div>
                    <p style="font-size: 1.05rem; font-weight: 700; color: #1e293b; margin-bottom: 6px;">No completed appointments yet</p>
                    <p style="font-size: 0.88rem; color: #64748b; max-width: 440px; margin: 0 auto;">When your scheduled visits are served and completed at the health center, your full consultation history, official slips, and records will be archived here.</p>
                </div>
            <?php else: ?>
                <?php foreach ($completedAppointments as $hIdx => $hAppt): ?>
                    <?php
                    $hCode = (string) ($hAppt['appointment_code'] ?? $hAppt['reference_code'] ?? '');
                    $hDate = (string) ($hAppt['preferred_date'] ?? '');
                    $hTime = (string) ($hAppt['preferred_time'] ?? 'Daily Slot');
                    $hService = (string) ($hAppt['service_name'] ?? 'General Consultation');
                    $hStation = (string) ($hAppt['station_name'] ?? ('Barangay ' . $patientBarangay . ' Health Station'));
                    $hServiceSlug = strtolower(trim((string) ($hAppt['service_slug'] ?? '')));
                    $hColor = $serviceCatalog[$hServiceSlug]['color'] ?? 'mint';
                    $hIcon = $serviceCatalog[$hServiceSlug]['icon'] ?? 'calendar';
                    $hNotes = trim((string) ($hAppt['notes'] ?? ''));
                    ?>
                    <article class="history-item-card" data-status="completed">
                        <div class="history-item-head">
                            <div class="history-item-service">
                                <div class="history-item-icon <?= h($hColor); ?>" style="background: <?= $hColor === 'pink' ? '#fce7f3;color:#ec4899;' : ($hColor === 'blue' ? '#eff6ff;color:#2563eb;' : ($hColor === 'gold' ? '#fff8e7;color:#ea8609;' : ($hColor === 'violet' ? '#f4ebff;color:#8b3dff;' : ($hColor === 'cyan' ? '#ecf8ff;color:#0ca8d7;' : ($hColor === 'red' ? '#fee2e2;color:#ef4444;' : '#e8fbf2;color:#059669;'))))); ?>">
                                    <?= iconSvg($hIcon); ?>
                                </div>
                                <div class="history-item-titles">
                                    <h4>
                                        <?= h($hService); ?>
                                        <?php if ($hIdx === 0): ?>
                                            <span class="latest-booking-chip" style="margin-left: 6px; font-size: 0.68rem; padding: 2px 7px; background:#f0fdf4; color:#15803d; border-color:#bbf7d0;">Latest Completed</span>
                                        <?php endif; ?>
                                    </h4>
                                    <span><?= h($hStation); ?></span>
                                </div>
                            </div>

                            <span class="appt-status-pill status-completed"><?= iconSvg('check'); ?> Completed</span>
                        </div>

                        <div class="appt-id-row">
                            <span class="appt-id-badge">ID: #<?= h($hCode); ?></span>
                        </div>

                        <div class="history-item-grid">
                            <div class="history-grid-col">
                                <small>Consultation Date</small>
                                <strong><?= date('D, M j, Y', strtotime($hDate)); ?></strong>
                            </div>
                            <div class="history-grid-col">
                                <small>Time Slot</small>
                                <strong><?= h($hTime); ?></strong>
                            </div>
                        </div>

                        <?php if ($hNotes !== ''): ?>
                            <div class="appt-notes-box" style="margin-bottom: 10px; padding: 8px 12px; font-size: 0.8rem;">
                                <strong>Note:</strong> <?= h($hNotes); ?>
                            </div>
                        <?php endif; ?>

                        <div class="history-item-footer">
                            <button type="button" class="btn-view-slip" style="padding: 7px 14px; font-size: 0.82rem;" data-appt="<?= htmlspecialchars(json_encode($hAppt), ENT_QUOTES, 'UTF-8'); ?>" onclick="openAppointmentSlipFromElement(this)">
                                <?= iconSvg('eye'); ?>
                                <span>View Slip</span>
                            </button>
                            <button type="button" class="btn-quick-download" style="padding: 7px 14px; font-size: 0.82rem;" data-appt="<?= htmlspecialchars(json_encode($hAppt), ENT_QUOTES, 'UTF-8'); ?>" onclick="downloadAppointmentSlipFromElement(this)">
                                <?= iconSvg('download'); ?>
                                <span>Download</span>
                            </button>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="history-modal-footer">
            <span class="history-footer-count">Showing <?= $completedCount; ?> completed consultation record(s)</span>
            <button type="button" class="btn-view-slip modal-done-btn" onclick="closeAppointmentHistoryModal()">
                <span>Close</span>
            </button>
        </div>
    </div>
</div>
</body>
</html>
