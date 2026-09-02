<?php

declare(strict_types=1);

require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';

if (!function_exists('h')) {
    function h(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('full_name')) {
    function full_name(array $row): string
    {
        $parts = [
            trim((string) ($row['first_name'] ?? '')),
            trim((string) ($row['middle_name'] ?? '')),
            trim((string) ($row['last_name'] ?? '')),
        ];

        return trim(implode(' ', array_filter($parts)));
    }
}

if (!function_exists('status_class')) {
    function status_class(string $status): string
    {
        return strtolower(str_replace(' ', '-', $status));
    }
}

if (!function_exists('age_label')) {
    function age_label(array $row): string
    {
        $age = isset($row['age']) ? (int) $row['age'] : appointment_age($row);
        return $age !== null && $age > 0 ? $age . ' yrs old' : 'Age unavailable';
    }
}

if (!function_exists('staff_icon')) {
    function staff_icon(string $name): string
    {
        $icons = [
            'logo' => '<svg viewBox="0 0 24 24"><path d="M6 3v6a4 4 0 0 0 8 0V3m-6 0h4m6 8a3 3 0 1 0 3 3v-4m-3 7a4 4 0 0 1-4 4h-1a4 4 0 0 1-4-4v-1" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'dashboard' => '<svg viewBox="0 0 24 24"><path d="M4 4h6v6H4V4Zm10 0h6v6h-6V4ZM4 14h6v6H4v-6Zm10 3h6m-6 3h6v-6h-6v6Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'appointments' => '<svg viewBox="0 0 24 24"><path d="M8 2v4m8-4v4M3 10h18M5 5h14a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'queue' => '<svg viewBox="0 0 24 24"><path d="M4 7h16M4 12h10M4 17h7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'events' => '<svg viewBox="0 0 24 24"><path d="M8 2v4m8-4v4M3 10h18M5 5h14a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 14v4m-2-2h4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'patients' => '<svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2m18 0v-2a4 4 0 0 0-3-3.87M14 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0Zm7 14v-2a4 4 0 0 0-3-3.87M18 3a4 4 0 0 1 0 8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'plus' => '<svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'arrow-left' => '<svg viewBox="0 0 24 24"><path d="M19 12H5m6-6-6 6 6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'arrow-right' => '<svg viewBox="0 0 24 24"><path d="M5 12h14m-5-5 5 5-5 5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'edit' => '<svg viewBox="0 0 24 24"><path d="m4 20 4.5-1 9.5-9.5-3.5-3.5L5 15.5 4 20Zm11-13 3.5 3.5" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'trash' => '<svg viewBox="0 0 24 24"><path d="M3 6h18M8 6V4h8v2m-9 0v13a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V6M10 11v6m4-6v6" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'logout' => '<svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4m7 14 5-5-5-5m5 5H9" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'mail' => '<svg viewBox="0 0 24 24"><path d="M4 6h16v12H4V6Zm0 0 8 6 8-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'lock' => '<svg viewBox="0 0 24 24"><path d="M7 11V7a5 5 0 0 1 10 0v4m-12 0h14v10H5V11Zm7 4v2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'check' => '<svg viewBox="0 0 24 24"><path d="m5 12 5 5L20 7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'camera' => '<svg viewBox="0 0 24 24"><path d="M4 7h4l2-2h4l2 2h4v12H4V7Zm8 9a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'x' => '<svg viewBox="0 0 24 24"><path d="m6 6 12 12M18 6 6 18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'filter' => '<svg viewBox="0 0 24 24"><path d="M3 5h18l-7 8v6l-4-2v-4L3 5Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'pin' => '<svg viewBox="0 0 24 24"><path d="M12 21s-6-5.33-6-11a6 6 0 1 1 12 0c0 5.67-6 11-6 11Zm0-8.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'clock' => '<svg viewBox="0 0 24 24"><path d="M12 8v5l3 2m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'users' => '<svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2m18 0v-2a4 4 0 0 0-3-3.87M14 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0Zm7 14v-2a4 4 0 0 0-3-3.87M18 3a4 4 0 0 1 0 8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'user' => '<svg viewBox="0 0 24 24"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="7" r="4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'eye' => '<svg viewBox="0 0 24 24"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Zm10 3a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'history' => '<svg viewBox="0 0 24 24"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8m0-5v5h5M12 7v5l4 2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'search' => '<svg viewBox="0 0 24 24"><path d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'sparkle' => '<svg viewBox="0 0 24 24"><path d="m12 3 1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8L12 3Zm7 10 .8 2.2L22 16l-2.2.8L19 19l-.8-2.2L16 16l2.2-.8L19 13ZM5 14l.8 2.2L8 17l-2.2.8L5 20l-.8-2.2L2 17l2.2-.8L5 14Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'shield' => '<svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'heart' => '<svg viewBox="0 0 24 24"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'syringe' => '<svg viewBox="0 0 24 24"><path d="m14 4 6 6M5 19l7.5-7.5M9.5 8.5 17 16M4 20l3-3M3 21l3-3" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="m8 16 9-9 3 3-9 9-3-3Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'baby' => '<svg viewBox="0 0 24 24"><path d="M9 12h.01M15 12h.01M10 16c.5.5 1.2.8 2 .8s1.5-.3 2-.8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="8" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 4v-2M9 4.5C9.5 3 10.5 2 12 2c1.5 0 2.5 1 3 2.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'pulse' => '<svg viewBox="0 0 24 24"><path d="M22 12h-4l-3 9L9 3l-3 9H2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'stethoscope' => '<svg viewBox="0 0 24 24"><path d="M4.5 3v5a4.5 4.5 0 0 0 9 0V3M9 12.5a4.5 4.5 0 0 0 4.5 4.5H15a3 3 0 0 0 3-3v-2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="18" cy="10" r="2.5" fill="none" stroke="currentColor" stroke-width="2"/></svg>',
            'community' => '<svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="9" cy="7" r="4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'cube' => '<svg viewBox="0 0 24 24"><path d="M7 3C4.5 3 3 5 3 8c0 3.5 1.5 7 2.5 10 .8 2.5 2 3 3.5 3s2-2 3-2 1.5 2 3 2 2.7-.5 3.5-3c1-3 2.5-6.5 2.5-10 0-3-1.5-5-4-5-2 0-3.5 1.5-4.5 2C12.5 4.5 11 3 7 3Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'capsule' => '<svg viewBox="0 0 24 24"><path d="m10.5 20.5-7-7a4.95 4.95 0 0 1 7-7l7 7a4.95 4.95 0 0 1-7 7Zm-3-10 9 9" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'calendar' => '<svg viewBox="0 0 24 24"><path d="M8 2v4m8-4v4M3 10h18M5 5h14a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="m9 16 2 2 4-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'phone' => '<svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 5.15 11.8 19.79 19.79 0 0 1 2.08 3.12 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'alert' => '<svg viewBox="0 0 24 24"><path d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'alert-circle' => '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 8v4m0 4h.01" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'reports' => '<svg viewBox="0 0 24 24"><path d="M3 3v18h18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="m19 9-5 5-4-4-3 3" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'download' => '<svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4m4-5 5 5 5-5m-5 5V3" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        ];

        return $icons[$name] ?? '';
    }
}

if (!function_exists('csrf_token_staff')) {
    function csrf_token_staff(): string
    {
        if (!isset($_SESSION['staff_csrf_token'])) {
            $_SESSION['staff_csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['staff_csrf_token'];
    }
}

if (!function_exists('verify_staff_csrf')) {
    function verify_staff_csrf(?string $token): bool
    {
        return is_string($token) && isset($_SESSION['staff_csrf_token']) && hash_equals($_SESSION['staff_csrf_token'], $token);
    }
}

if (!function_exists('is_staff_authenticated')) {
    function is_staff_authenticated(): bool
    {
        return !empty($_SESSION['staff_authenticated']) && !empty($_SESSION['staff_email']);
    }
}

if (!function_exists('queue_groups')) {
    function queue_groups(array $entries, string $programFilter): array
    {
        $grouped = [];

        if ($programFilter === '') {
            // Group by service when "All Programs" is selected
            foreach ($entries as $entry) {
                $grouped[$entry['service_name']][] = $entry;
            }
        } else {
            // Group by station (default)
            foreach ($entries as $entry) {
                $grouped[$entry['station_name']][] = $entry;
            }
        }

        return $grouped;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (($_POST['action'] ?? '') === 'logout') && is_staff_authenticated()) {
    if (verify_staff_csrf($_POST['csrf_token'] ?? null)) {
        unset(
            $_SESSION['staff_authenticated'],
            $_SESSION['staff_email'],
            $_SESSION['staff_name'],
            $_SESSION['staff_station_slug'],
            $_SESSION['staff_station_name']
        );
    }

    header('Location: ../Patients/index.php#portal');
    exit;
}

if (!is_staff_authenticated()) {
    header('Location: ../Patients/index.php#portal');
    exit;
}

$staffAccount = fetch_staff_account_by_email((string) $_SESSION['staff_email']);
if (!is_array($staffAccount)) {
    unset(
        $_SESSION['staff_authenticated'],
        $_SESSION['staff_email'],
        $_SESSION['staff_name'],
        $_SESSION['staff_station_slug'],
        $_SESSION['staff_station_name']
    );
    header('Location: ../Patients/index.php#portal');
    exit;
}

$station = fetch_station_by_slug_catalog((string) $staffAccount['station_slug']);
if (!is_array($station)) {
    unset(
        $_SESSION['staff_authenticated'],
        $_SESSION['staff_email'],
        $_SESSION['staff_name'],
        $_SESSION['staff_station_slug'],
        $_SESSION['staff_station_name']
    );
    header('Location: ../Patients/index.php#portal');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (($_POST['action'] ?? '') === 'update_staff_account')) {
    if (verify_staff_csrf($_POST['csrf_token'] ?? null)) {
        $staffName = trim((string) ($_POST['staff_name'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $birthDate = trim((string) ($_POST['birth_date'] ?? ''));
        $gender = trim((string) ($_POST['gender'] ?? ''));
        $contactNumber = trim((string) ($_POST['contact_number'] ?? ''));
        $homeAddress = trim((string) ($_POST['home_address'] ?? ''));
        $emergencyContact = trim((string) ($_POST['emergency_contact'] ?? ''));
        $emergencyPhone = trim((string) ($_POST['emergency_phone'] ?? ''));
        $newPassword = (string) ($_POST['new_password'] ?? '');
        $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

        $hasPasswordChange = $newPassword !== '';
        $passwordValid = true;

        if ($hasPasswordChange) {
            if (strlen($newPassword) < 6) {
                $_SESSION['staff_flash'] = 'New password must be at least 6 characters long.';
                $_SESSION['staff_flash_type'] = 'error';
                $passwordValid = false;
            } elseif ($newPassword !== $confirmPassword) {
                $_SESSION['staff_flash'] = 'New password and confirmation password do not match.';
                $_SESSION['staff_flash_type'] = 'error';
                $passwordValid = false;
            }
        }

        if ($passwordValid) {
            if ($staffName !== '' && $email !== '') {
                $staffId = (int) ($staffAccount['id'] ?? 0);
                $updateData = [
                    'staff_name' => $staffName,
                    'email' => $email,
                    'birth_date' => $birthDate,
                    'gender' => $gender,
                    'contact_number' => $contactNumber,
                    'home_address' => $homeAddress,
                    'emergency_contact' => $emergencyContact,
                    'emergency_phone' => $emergencyPhone,
                    'password' => $hasPasswordChange ? $newPassword : '',
                ];

                if ($staffId > 0 && update_staff_account_details($staffId, $updateData)) {
                    $_SESSION['staff_name'] = $staffName;
                    $_SESSION['staff_email'] = $email;
                    $_SESSION['staff_flash'] = 'Account details updated successfully!';
                    $_SESSION['staff_flash_type'] = 'success';
                } else {
                    $_SESSION['staff_flash'] = 'Unable to update account. That work email may already be in use.';
                    $_SESSION['staff_flash_type'] = 'error';
                }
            } else {
                $_SESSION['staff_flash'] = 'Please fill in both name and work email.';
                $_SESSION['staff_flash_type'] = 'error';
            }
        }
    }

    $returnPage = trim((string) ($_POST['return_page'] ?? 'dashboard'));
    header('Location: index.php?page=' . urlencode($returnPage));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_id'], $_POST['new_status'])) {
    if (verify_staff_csrf($_POST['csrf_token'] ?? null)) {
        $apptId = (int) $_POST['appointment_id'];
        $newStatus = trim((string) $_POST['new_status']);

        if ($newStatus === 'Completed') {
            $targetAppt = fetch_appointment_by_id($apptId);
            if (!is_array($targetAppt) || !appointment_can_complete($targetAppt)) {
                $missingItems = [];
                if (!is_array($targetAppt) || !appointment_has_vitals($targetAppt)) $missingItems[] = 'Vital Signs';
                if (!is_array($targetAppt) || !appointment_has_photo($targetAppt)) $missingItems[] = 'Patient Photo';
                if (!is_array($targetAppt) || !appointment_has_clinical_notes($targetAppt)) $missingItems[] = 'Clinical Notes';
                
                $_SESSION['staff_flash'] = 'Cannot complete consultation yet. Missing: ' . implode(', ', $missingItems) . '.';
                header('Location: ' . $_SERVER['REQUEST_URI']);
                exit;
            }
        }

        update_appointment_status($apptId, $newStatus, (string) $station['slug']);
        $_SESSION['staff_flash'] = match ($newStatus) {
            'Confirmed' => 'Appointment confirmed and moved to Queue Management.',
            'Serving' => 'Patient is now being served.',
            'Completed' => 'Patient completed and added to records.',
            default => 'Appointment status updated.',
        };
    }

    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (($_POST['action'] ?? '') === 'create_event')) {
    if (verify_staff_csrf($_POST['csrf_token'] ?? null)) {
        $created = create_upcoming_event([
            'station_slug' => $station['slug'],
            'title' => trim((string) ($_POST['title'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'event_date' => trim((string) ($_POST['event_date'] ?? '')),
            'time_label' => trim((string) ($_POST['time_label'] ?? '')),
            'end_time_label' => trim((string) ($_POST['end_time_label'] ?? '')),
            'icon' => trim((string) ($_POST['icon'] ?? 'calendar')),
            'accent' => trim((string) ($_POST['accent'] ?? 'mint')),
            'created_by' => $staffAccount['email'],
        ]);
        $_SESSION['staff_flash'] = $created ? 'Upcoming event saved.' : 'Unable to save event. Please complete all fields.';
        if ($created) {
            log_activity('staff', $staffAccount['email'], 'event_created', 'event', '', '', '', $station['slug']);
        }
    }

    header('Location: index.php?page=events');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (($_POST['action'] ?? '') === 'update_event')) {
    if (verify_staff_csrf($_POST['csrf_token'] ?? null)) {
        update_upcoming_event(
            (int) ($_POST['event_id'] ?? 0),
            [
                'title' => trim((string) ($_POST['title'] ?? '')),
                'description' => trim((string) ($_POST['description'] ?? '')),
                'event_date' => trim((string) ($_POST['event_date'] ?? '')),
                'time_label' => trim((string) ($_POST['time_label'] ?? '')),
                'end_time_label' => trim((string) ($_POST['end_time_label'] ?? '')),
                'icon' => trim((string) ($_POST['icon'] ?? 'calendar')),
                'accent' => trim((string) ($_POST['accent'] ?? 'mint')),
            ],
            (string) $station['slug']
        );
        $_SESSION['staff_flash'] = 'Event updated.';
    }

    header('Location: index.php?page=events');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (($_POST['action'] ?? '') === 'delete_event')) {
    if (verify_staff_csrf($_POST['csrf_token'] ?? null)) {
        delete_upcoming_event((int) ($_POST['event_id'] ?? 0), (string) $station['slug']);
        $_SESSION['staff_flash'] = 'Event removed.';
    }

    header('Location: index.php?page=events');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array(($_POST['action'] ?? ''), ['save_clinical_details', 'save_vitals', 'save_clinical_remarks'], true)) {
    if (verify_staff_csrf($_POST['csrf_token'] ?? null)) {
        $appointmentId = (int) ($_POST['appointment_id'] ?? 0);
        $postData = $_POST;
        if (!empty($postData['temperature']) && empty($postData['body_temperature'])) {
            $postData['body_temperature'] = $postData['temperature'];
        }
        if (!empty($postData['pulse']) && empty($postData['pulse_rate'])) {
            $postData['pulse_rate'] = $postData['pulse'];
        }
        if (save_appointment_clinical_details($appointmentId, $postData, (string) $station['slug'])) {
            if (($_POST['action'] ?? '') === 'save_vitals') {
                $_SESSION['staff_flash'] = 'Vital signs recorded successfully.';
            } elseif (($_POST['action'] ?? '') === 'save_clinical_remarks') {
                $_SESSION['staff_flash'] = 'Clinical remarks & doctor notes saved.';
            } else {
                $_SESSION['staff_flash'] = 'Clinical details saved successfully.';
            }
            log_activity('staff', $staffAccount['email'], 'clinical_details_saved', 'appointment', (string) $appointmentId, '', '', $station['slug']);
        } else {
            $_SESSION['staff_flash'] = 'Unable to update that appointment record.';
        }
    }

    $returnUrl = trim((string) ($_POST['return_url'] ?? ''));
    if ($returnUrl !== '') {
        header('Location: ' . $returnUrl);
    } else {
        header('Location: index.php?page=patients');
    }
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (($_POST['action'] ?? '') === 'schedule_follow_up')) {
    if (verify_staff_csrf($_POST['csrf_token'] ?? null)) {
        $appointmentId = (int) ($_POST['appointment_id'] ?? 0);
        $followUpDate = trim((string) ($_POST['follow_up_date'] ?? ''));
        $followUpTime = trim((string) ($_POST['follow_up_time'] ?? ''));
        $followUpNotes = trim((string) ($_POST['follow_up_notes'] ?? ''));

        if ($appointmentId > 0 && $followUpDate !== '') {
            if (schedule_appointment_follow_up($appointmentId, $followUpDate, $followUpTime, $followUpNotes, (string) ($staffAccount['email'] ?? ''))) {
                $_SESSION['staff_flash'] = 'Follow-up consultation scheduled for ' . date('F j, Y', strtotime($followUpDate)) . '. The patient has been notified on their dashboard!';
                log_activity('staff', (string) ($staffAccount['email'] ?? ''), 'follow_up_scheduled', 'appointment', (string) $appointmentId, '', '', (string) $station['slug']);
            } else {
                $_SESSION['staff_flash'] = 'Unable to schedule follow-up. Please try again.';
            }
        } else {
            $_SESSION['staff_flash'] = 'Please choose a valid follow-up date.';
        }
    }

    $returnUrl = trim((string) ($_POST['return_url'] ?? ''));
    if ($returnUrl !== '') {
        header('Location: ' . $returnUrl);
    } else {
        header('Location: index.php?page=patients');
    }
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (($_POST['action'] ?? '') === 'save_photo')) {
    if (verify_staff_csrf($_POST['csrf_token'] ?? null)) {
        $photoAppointmentId = (int) ($_POST['appointment_id'] ?? 0);
        $capturedPhoto = trim((string) ($_POST['captured_photo_data'] ?? ''));

        if ($photoAppointmentId > 0 && $capturedPhoto !== '') {
            if (save_patient_photo_for_appointment($photoAppointmentId, $capturedPhoto, (string) $station['slug'])) {
                $savedAppt = fetch_appointment_by_id($photoAppointmentId);
                $patName = $savedAppt ? full_name($savedAppt) : 'Patient';
                $_SESSION['staff_flash'] = 'Proof photo for ' . $patName . ' saved successfully and linked to medical record!';
                log_activity('staff', (string) ($staffAccount['email'] ?? ''), 'photo_captured', 'appointment', (string) $photoAppointmentId, '', '', (string) $station['slug']);
            } else {
                $_SESSION['staff_flash'] = 'Unable to save the patient photo. Please try again.';
            }
        } else {
            $_SESSION['staff_flash'] = 'Please select a patient appointment and capture a photo.';
        }
    }

    $redirectUrl = '?page=image-capture';
    $prog = trim((string) ($_POST['program'] ?? ''));
    if ($prog !== '') {
        $redirectUrl .= '&program=' . urlencode($prog);
    }
    if (!empty($photoAppointmentId)) {
        $redirectUrl .= '&appointment=' . urlencode((string) $photoAppointmentId);
    }

    header('Location: ' . $redirectUrl);
    exit;
}
$page = $_GET['page'] ?? 'dashboard';
$programFilter = trim((string) ($_GET['program'] ?? ''));
$statusFilter = trim((string) ($_GET['status'] ?? ''));
$dateFilter = trim((string) ($_GET['date'] ?? 'both'));
if ($dateFilter === '') {
    $dateFilter = 'both';
}
$appointmentSearch = trim((string) ($_GET['search'] ?? ''));
$queueDate = trim((string) ($_GET['queue_date'] ?? 'today'));
if ($queueDate === '') {
    $queueDate = 'today';
}
$patientSearch = trim((string) ($_GET['patient_search'] ?? ''));
$captureFilter = trim((string) ($_GET['capture_filter'] ?? ''));
if ($captureFilter === '' || !in_array($captureFilter, ['needs_photo', 'ongoing', 'verified'], true)) {
    $captureFilter = 'needs_photo';
}
$patientDateFilter = trim((string) ($_GET['patient_date'] ?? ''));
$patientStatus = trim((string) ($_GET['patient_status'] ?? 'both'));
if ($patientStatus === '') {
    $patientStatus = 'both';
}
$selectedPatientId = trim((string) ($_GET['patient'] ?? ''));
$selectedVitalsCode = trim((string) ($_GET['encode_vitals'] ?? ''));
$selectedRemarksCode = trim((string) ($_GET['appointment_remarks'] ?? ''));
$selectedRecordCode = trim((string) ($_GET['appointment_record'] ?? ''));
if ($selectedVitalsCode === '' && $selectedRecordCode !== '' && $page === 'queue') {
    $selectedVitalsCode = $selectedRecordCode;
}
if ($selectedRemarksCode === '' && $selectedRecordCode !== '' && $page !== 'queue') {
    $selectedRemarksCode = $selectedRecordCode;
}
$selectedViewRecordCode = trim((string) ($_GET['appointment_view'] ?? ''));
$selectedFollowUpRecordCode = trim((string) ($_GET['appointment_followup'] ?? ''));
$selectedPhotoAppointmentId = (int) ($_GET['appointment'] ?? 0);
$eventEditId = (int) ($_GET['edit_event'] ?? 0);
$showEventModal = (($_GET['show_event_modal'] ?? '') === '1') || $eventEditId > 0;
$flash = (string) ($_SESSION['staff_flash'] ?? '');
unset($_SESSION['staff_flash']);

$appointments = fetch_appointments([
    'station_slug' => $station['slug'],
    'service_slug' => $programFilter,
    'status' => $statusFilter,
    'date' => $dateFilter,
    'search' => $appointmentSearch,
]);
$appointmentsPageEntries = array_values(array_filter(
    $appointments,
    static fn(array $item): bool => in_array((string) ($item['status'] ?? ''), ['Pending', 'Cancelled'], true)
));
$queueEntries = fetch_queue_entries([
    'station_slug' => $station['slug'],
    'service_slug' => $programFilter,
    'date' => $queueDate,
    'search' => $appointmentSearch,
]);
$groupedQueue = queue_groups($queueEntries, $programFilter);
$stationEvents = fetch_upcoming_events([
    'station_slug' => $station['slug'],
    'upcoming_only' => true,
]);
$eventEditing = $eventEditId > 0 ? fetch_upcoming_event_by_id($eventEditId) : null;
$allStationAppointments = fetch_appointments(['station_slug' => $station['slug']]);
$stationPatients = fetch_unique_patients($patientSearch, ['station_slug' => $station['slug']]);
$unreadNotifications = fetch_unread_patient_notifications();
$patientProfile = $selectedPatientId !== '' ? fetch_patient_profile($selectedPatientId) : null;
$clinicalSearchResult = $patientSearch !== '' ? fetch_appointment_by_code_or_search($patientSearch, (string) $station['slug']) : null;
$selectedVitalsAppointment = $selectedVitalsCode !== '' ? fetch_appointment_by_code($selectedVitalsCode, (string) $station['slug']) : null;
if (is_array($selectedVitalsAppointment)) {
    $vApptServing = (string) ($selectedVitalsAppointment['status'] ?? '') === 'Serving';
    $vApptHasVitals = appointment_has_vitals($selectedVitalsAppointment);
    if (!$vApptServing || $vApptHasVitals) {
        $selectedVitalsAppointment = null;
    }
}
$selectedRemarksAppointment = $selectedRemarksCode !== '' ? fetch_appointment_by_code($selectedRemarksCode, (string) $station['slug']) : null;
$selectedClinicalAppointment = $selectedRemarksAppointment ?? ($selectedRecordCode !== '' ? fetch_appointment_by_code($selectedRecordCode, (string) $station['slug']) : null);
$selectedViewAppointment = $selectedViewRecordCode !== '' ? fetch_appointment_by_code($selectedViewRecordCode, (string) $station['slug']) : null;
$selectedFollowUpAppointment = $selectedFollowUpRecordCode !== '' ? fetch_appointment_by_code($selectedFollowUpRecordCode, (string) $station['slug']) : null;
$photoAppointment = $selectedPhotoAppointmentId > 0 ? fetch_appointment_by_id($selectedPhotoAppointmentId) : null;
if (is_array($photoAppointment)) {
    $apptToday = (string) ($photoAppointment['preferred_date'] ?? '') === date('Y-m-d');
    $apptServing = (string) ($photoAppointment['status'] ?? '') === 'Serving';
    $apptHasPhoto = !empty($photoAppointment['photo_path']);
    if ((string) ($photoAppointment['station_slug'] ?? '') !== (string) $station['slug'] || !$apptToday || !$apptServing || $apptHasPhoto) {
        $photoAppointment = null;
    }
}

$activeFrontDeskQueue = array_values(array_filter(
    $allStationAppointments,
    static fn(array $item): bool => in_array((string) ($item['status'] ?? ''), ['Serving', 'Confirmed'], true)
));
$ongoingAwaitingPhoto = array_values(array_filter(
    $activeFrontDeskQueue,
    static fn(array $item): bool => empty($item['photo_path'])
));
$ongoingWithPhoto = array_values(array_filter(
    $activeFrontDeskQueue,
    static fn(array $item): bool => !empty($item['photo_path'])
));
$photosVerifiedTotal = count(array_filter(
    $allStationAppointments,
    static fn(array $item): bool => !empty($item['photo_path'])
));
$stationClinicalAppointments = array_values(array_filter(
    $allStationAppointments,
    static fn(array $item): bool => in_array((string) ($item['status'] ?? ''), ['Confirmed', 'Serving', 'Completed'], true)
));
$patientDateFilteredAppointments = $patientDateFilter === '' ? $stationClinicalAppointments : array_values(array_filter(
    $stationClinicalAppointments,
    static fn(array $item): bool => (string) ($item['preferred_date'] ?? '') === $patientDateFilter
));
if ($patientSearch !== '') {
    $st = strtolower($patientSearch);
    $patientDateFilteredAppointments = array_values(array_filter(
        $patientDateFilteredAppointments,
        static function(array $item) use ($st): bool {
            $name = strtolower(full_name($item));
            $code = strtolower((string) ($item['appointment_code'] ?? $item['reference_code'] ?? ''));
            $phone = strtolower((string) ($item['contact_number'] ?? ''));
            $svc = strtolower((string) ($item['service_name'] ?? ''));
            return str_contains($name, $st) || str_contains($code, $st) || str_contains($phone, $st) || str_contains($svc, $st);
        }
    ));
}
$recentStationAppointments = array_values(array_filter(
    $patientDateFilteredAppointments,
    static fn(array $item): bool => (string) ($item['status'] ?? '') === 'Serving' && !appointment_has_completed_clinical_details($item)
));
$patientStationRecords = array_values(array_filter(
    $patientDateFilteredAppointments,
    static fn(array $item): bool => (string) ($item['status'] ?? '') === 'Completed' && appointment_has_completed_clinical_details($item)
));
$patientRecentEntries = $programFilter === '' ? $recentStationAppointments : array_values(array_filter(
    $recentStationAppointments,
    static fn(array $item): bool => (string) ($item['service_slug'] ?? '') === $programFilter
));
$patientRecordEntries = $programFilter === '' ? $patientStationRecords : array_values(array_filter(
    $patientStationRecords,
    static fn(array $item): bool => (string) ($item['service_slug'] ?? '') === $programFilter
));
$csrf = csrf_token_staff();

$today = date('Y-m-d');
$todayAppointments = array_values(array_filter(
    $allStationAppointments,
    static fn(array $item): bool => $item['preferred_date'] === $today && $item['status'] !== 'Cancelled'
));
$pendingCount = count(array_filter($allStationAppointments, static fn(array $item): bool => $item['status'] === 'Pending'));
$confirmedCount = count(array_filter($allStationAppointments, static fn(array $item): bool => $item['status'] === 'Confirmed'));
$completedCount = count(array_filter($allStationAppointments, static fn(array $item): bool => $item['status'] === 'Completed'));
$completedTodayCount = count(array_filter($todayAppointments, static fn(array $item): bool => $item['status'] === 'Completed'));
$kpiTodayTotal = count($todayAppointments);
$kpiPendingCount = $pendingCount;
$kpiServingCount = count(array_filter($allStationAppointments, static fn(array $item): bool => in_array((string) ($item['status'] ?? ''), ['Confirmed', 'Serving'], true)));
$kpiCompletedCount = $completedTodayCount;
$nextEvent = $stationEvents[0] ?? null;
$scheduleOverview = [
    ['time' => '8:00 AM - 12:00 PM', 'label' => 'Morning Consultations', 'tone' => 'blue'],
    ['time' => '1:00 PM - 5:00 PM', 'label' => 'Afternoon Queue Monitoring', 'tone' => 'violet'],
];
$programs = $station['programs'];
$queueWaitingCount = count(array_filter($queueEntries, static fn(array $item): bool => (string) ($item['status'] ?? '') === 'Confirmed'));
$queueBeingServedCount = count(array_filter($queueEntries, static fn(array $item): bool => (string) ($item['status'] ?? '') === 'Serving'));
$queueCompletedCount = count(array_filter($queueEntries, static fn(array $item): bool => (string) ($item['status'] ?? '') === 'Completed'));
$appointmentsPendingCount = count(array_filter($appointmentsPageEntries, static fn(array $item): bool => (string) ($item['status'] ?? '') === 'Pending'));
$appointmentsCancelledCount = count(array_filter($appointmentsPageEntries, static fn(array $item): bool => (string) ($item['status'] ?? '') === 'Cancelled'));
$appointmentsTotalCount = $programFilter === '' ? count($allStationAppointments) : count(array_filter(
    $allStationAppointments,
    static fn(array $item): bool => (string) ($item['service_slug'] ?? '') === $programFilter
));
$patientsOngoingCount = count($patientRecentEntries);
$patientsRecordedCount = count($patientRecordEntries);
$patientsTotalCount = $patientsOngoingCount + $patientsRecordedCount;

// Synchronize and load unattended appointments & queue audit data
sync_unattended_records((string) $station['slug']);
$unattendedStats = count_unattended_records((string) $station['slug']);
$unattendedApptsList = fetch_unattended_appointments(['station_slug' => (string) $station['slug']]);
$unattendedQueueList = fetch_unattended_queue(['station_slug' => (string) $station['slug']]);
$unattendedApptsCount = (int) ($unattendedStats['appointments'] ?? count($unattendedApptsList));
$unattendedQueueCount = (int) ($unattendedStats['queue'] ?? count($unattendedQueueList));

// Weekly Reports Data Calculation for Staff's Barangay Health Station
$reportWeekOffset = (int) ($_GET['week_offset'] ?? 0);
$reportFromCustom = trim((string) ($_GET['report_from'] ?? ''));
$reportToCustom = trim((string) ($_GET['report_to'] ?? ''));

if ($reportFromCustom !== '' && $reportToCustom !== '') {
    $reportStartDate = $reportFromCustom;
    $reportEndDate = $reportToCustom;
    $reportWeekLabel = date('M j, Y', strtotime($reportStartDate)) . ' - ' . date('M j, Y', strtotime($reportEndDate));
} else {
    $baseDate = new DateTimeImmutable('today');
    if ($reportWeekOffset !== 0) {
        $baseDate = $baseDate->modify(($reportWeekOffset > 0 ? "+{$reportWeekOffset}" : "{$reportWeekOffset}") . ' weeks');
    }
    $dayOfWeek = (int) $baseDate->format('N'); // 1 = Monday, 7 = Sunday
    $weekStartObj = $baseDate->sub(new DateInterval('P' . ($dayOfWeek - 1) . 'D'));
    $weekEndObj = $weekStartObj->add(new DateInterval('P5D')); // Monday to Saturday
    $reportStartDate = $weekStartObj->format('Y-m-d');
    $reportEndDate = $weekEndObj->format('Y-m-d');
    
    if ($reportWeekOffset === 0) {
        $reportWeekLabel = 'This Week (' . $weekStartObj->format('M j') . ' - ' . $weekEndObj->format('M j, Y') . ')';
    } elseif ($reportWeekOffset === -1) {
        $reportWeekLabel = 'Last Week (' . $weekStartObj->format('M j') . ' - ' . $weekEndObj->format('M j, Y') . ')';
    } else {
        $reportWeekLabel = $weekStartObj->format('M j, Y') . ' - ' . $weekEndObj->format('M j, Y');
    }
}

// Fetch all weekly appointments strictly for this station
$stationSlug = (string) $station['slug'];
$stmtReport = db()->prepare(
    'SELECT * FROM appointments 
     WHERE station_slug = ? AND preferred_date BETWEEN ? AND ?
     ORDER BY preferred_date ASC, preferred_time ASC, created_at ASC'
);
$stmtReport->bind_param('sss', $stationSlug, $reportStartDate, $reportEndDate);
$stmtReport->execute();
$weeklyAppointments = $stmtReport->get_result()->fetch_all(MYSQLI_ASSOC);

$weeklyTotalBooked = count($weeklyAppointments);
$weeklyCompleted = count(array_filter($weeklyAppointments, static fn(array $a): bool => (string) ($a['status'] ?? '') === 'Completed'));
$weeklyConfirmed = count(array_filter($weeklyAppointments, static fn(array $a): bool => (string) ($a['status'] ?? '') === 'Confirmed'));
$weeklyServing = count(array_filter($weeklyAppointments, static fn(array $a): bool => (string) ($a['status'] ?? '') === 'Serving'));
$weeklyPending = count(array_filter($weeklyAppointments, static fn(array $a): bool => (string) ($a['status'] ?? '') === 'Pending'));
$weeklyCancelled = count(array_filter($weeklyAppointments, static fn(array $a): bool => (string) ($a['status'] ?? '') === 'Cancelled'));
$weeklyActiveWaiting = $weeklyConfirmed + $weeklyServing;

$stmtUniquePat = db()->prepare(
    'SELECT COUNT(DISTINCT COALESCE(NULLIF(patient_id, ""), CONCAT(first_name, "|", last_name, "|", birth_date))) AS total
     FROM appointments
     WHERE station_slug = ? AND preferred_date BETWEEN ? AND ? AND status <> "Cancelled"'
);
$stmtUniquePat->bind_param('sss', $stationSlug, $reportStartDate, $reportEndDate);
$stmtUniquePat->execute();
$uniquePatRow = $stmtUniquePat->get_result()->fetch_assoc();
$weeklyUniquePatients = (int) ($uniquePatRow['total'] ?? 0);

// Service Breakdown for this station
$weeklyServiceStats = [];
foreach ($station['programs'] as $prog) {
    $slug = $prog['slug'];
    $progAppts = array_values(array_filter($weeklyAppointments, static fn(array $a): bool => (string) ($a['service_slug'] ?? '') === $slug));
    $totalProg = count($progAppts);
    $completedProg = count(array_filter($progAppts, static fn(array $a): bool => (string) ($a['status'] ?? '') === 'Completed'));
    $confirmedProg = count(array_filter($progAppts, static fn(array $a): bool => in_array((string) ($a['status'] ?? ''), ['Confirmed', 'Serving'], true)));
    $pendingProg = count(array_filter($progAppts, static fn(array $a): bool => (string) ($a['status'] ?? '') === 'Pending'));
    $cancelledProg = count(array_filter($progAppts, static fn(array $a): bool => (string) ($a['status'] ?? '') === 'Cancelled'));
    $sharePct = $weeklyTotalBooked > 0 ? round(($totalProg / $weeklyTotalBooked) * 100) : 0;
    
    $weeklyServiceStats[] = [
        'slug' => $slug,
        'title' => $prog['title'],
        'color' => $prog['color'] ?? 'mint',
        'icon' => $prog['icon'] ?? 'appointments',
        'total' => $totalProg,
        'completed' => $completedProg,
        'confirmed' => $confirmedProg,
        'pending' => $pendingProg,
        'cancelled' => $cancelledProg,
        'share_pct' => $sharePct,
    ];
}
usort($weeklyServiceStats, static fn($a, $b) => $b['total'] <=> $a['total']);

// Day-by-Day distribution
$weeklyDayData = [];
$dateCursor = new DateTimeImmutable($reportStartDate);
for ($i = 0; $i < 6; $i++) {
    $curDateStr = $dateCursor->format('Y-m-d');
    $dayName = $dateCursor->format('D');
    $dayAppts = array_values(array_filter($weeklyAppointments, static fn(array $a): bool => (string) ($a['preferred_date'] ?? '') === $curDateStr));
    $dayTotal = count($dayAppts);
    $dayCompleted = count(array_filter($dayAppts, static fn(array $a): bool => (string) ($a['status'] ?? '') === 'Completed'));
    $dayCancelled = count(array_filter($dayAppts, static fn(array $a): bool => (string) ($a['status'] ?? '') === 'Cancelled'));
    
    $weeklyDayData[] = [
        'date' => $curDateStr,
        'day_name' => $dayName,
        'formatted_date' => $dateCursor->format('M j'),
        'total' => $dayTotal,
        'completed' => $dayCompleted,
        'cancelled' => $dayCancelled,
    ];
    $dateCursor = $dateCursor->add(new DateInterval('P1D'));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($station['name']); ?> Staff Panel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/styles.css?v=<?= filemtime(__DIR__ . '/assets/styles.css'); ?>">
</head>
<body>
<header class="staff-topbar">
    <div class="topbar-brand">
        <span class="brand-mark brand-mark-teal"><?= staff_icon('logo'); ?></span>
        <div class="brand-text">
            <strong><?= h($station['name']); ?></strong>
            <small>Operations Center</small>
        </div>
    </div>
    
    <div class="topbar-center">
        <div class="station-status-pill">
            <span class="live-pulse"></span>
            <span class="status-text">Health Center Active</span>
        </div>
        <div class="station-date-badge"><?= date('l, F j, Y'); ?></div>
    </div>
    
    <div class="header-user">
        <div class="staff-user-badge" id="topbarUserChip" style="cursor:pointer;" title="Click to manage account details">
            <div class="staff-avatar-circle">
                <?= strtoupper(substr((string) ($staffAccount['staff_name'] ?? 'S'), 0, 1)); ?>
            </div>
            <div class="staff-user-info">
                <strong><?= h($staffAccount['staff_name']); ?></strong>
                <span><?= h($station['barangay']); ?> Health Staff</span>
            </div>
        </div>
        <div class="topbar-nav-links">
            <form method="post" class="logout-form">
                <input type="hidden" name="action" value="logout">
                <input type="hidden" name="csrf_token" value="<?= h($csrf); ?>">
                <button type="submit" class="topbar-nav-btn logout-btn" title="Sign out of staff portal">
                    <?= staff_icon('logout'); ?>
                    <span>Sign Out</span>
                </button>
            </form>
        </div>
    </div>
</header>
<div class="staff-shell">
    <aside class="staff-sidebar">
        <div>
            <nav class="sidebar-nav">
                <a class="<?= $page === 'dashboard' ? 'active' : ''; ?>" href="?page=dashboard"><?= staff_icon('dashboard'); ?><span>Dashboard</span></a>
                <a class="<?= $page === 'appointments' ? 'active' : ''; ?>" href="?page=appointments"><?= staff_icon('appointments'); ?><span>Appointments</span></a>
                <a class="<?= $page === 'queue' ? 'active' : ''; ?>" href="?page=queue"><?= staff_icon('queue'); ?><span>Queue Management</span></a>
                <a class="<?= $page === 'patients' ? 'active' : ''; ?>" href="?page=patients"><?= staff_icon('patients'); ?><span>Patients</span></a>
                <a class="<?= $page === 'image-capture' ? 'active' : ''; ?>" href="?page=image-capture"><?= staff_icon('camera'); ?><span>Image Capture</span></a>
                <a class="<?= $page === 'events' ? 'active' : ''; ?>" href="?page=events"><?= staff_icon('events'); ?><span>Upcoming Events</span></a>
                <a class="<?= $page === 'reports' ? 'active' : ''; ?>" href="?page=reports"><?= staff_icon('reports'); ?><span>Weekly Reports</span></a>
            </nav>
        </div>
        <div class="sidebar-footer-widget">
            <div class="sidebar-staff-badge">
                <div class="badge-role-tag">Volunteer / Staff</div>
                <strong><?= h($staffAccount['staff_name']); ?></strong>
                <span><?= h($staffAccount['email']); ?></span>
            </div>
            <button type="button" class="sidebar-account-btn" id="sidebarOpenAccountBtn">
                <?= staff_icon('user'); ?>
                <span>Account Settings</span>
            </button>
        </div>
    </aside>

    <main class="staff-main">
        <?php if ($flash !== ''): ?>
            <?php
            $isStaffFlashError = (string) ($_SESSION['staff_flash_type'] ?? '') === 'error' || str_contains(strtolower($flash), 'unable') || str_contains(strtolower($flash), 'cannot') || str_contains(strtolower($flash), 'invalid') || str_contains(strtolower($flash), 'not match') || str_contains(strtolower($flash), 'failed');
            ?>
            <div class="flash-toast-wrap" id="staffFlashToast">
                <div class="flash-banner <?= $isStaffFlashError ? 'error' : 'success'; ?>" role="alert">
                    <div class="flash-icon-box">
                        <?php if ($isStaffFlashError): ?>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                        <?php else: ?>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        <?php endif; ?>
                    </div>
                    <div class="flash-content">
                        <strong><?= $isStaffFlashError ? 'Action Required' : 'Station Update'; ?></strong>
                        <p><?= h($flash); ?></p>
                    </div>
                    <button type="button" class="flash-dismiss-btn" onclick="dismissStaffToast()" aria-label="Dismiss notification">×</button>
                    <div class="flash-progress-bar"><div class="flash-progress-fill"></div></div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($page === 'dashboard'): ?>
            <!-- Hero Greeting Section -->
            <section class="dash-hero-card">
                <div class="dash-hero-copy">
                    <div class="dash-hero-badge">
                        <?= staff_icon('sparkle'); ?>
                        <span>Barangay Health &amp; Volunteer Command</span>
                    </div>
                    <h1>Welcome back, <?= h($staffAccount['staff_name']); ?> 👋</h1>
                    <p><?= h($station['name']); ?> • Serving the healthcare needs of Brgy. <?= h($station['barangay']); ?>, Bacolod City.</p>
                </div>
            </section>

            <!-- Dashboard Stat KPI Cards -->
            <section class="dash-stat-grid">
                <article class="dash-stat-card theme-teal">
                    <div class="dash-stat-top">
                        <div class="dash-stat-icon"><?= staff_icon('pulse'); ?></div>
                        <span class="dash-stat-tag">Today</span>
                    </div>
                    <div class="dash-stat-body">
                        <h3><?= number_format($kpiTodayTotal); ?></h3>
                        <p>Total Bookings</p>
                    </div>
                    <div class="dash-stat-footer">Today's patient volume</div>
                </article>

                <article class="dash-stat-card theme-amber">
                    <div class="dash-stat-top">
                        <div class="dash-stat-icon"><?= staff_icon('clock'); ?></div>
                        <span class="dash-stat-tag">Action Needed</span>
                    </div>
                    <div class="dash-stat-body">
                        <h3><?= number_format($kpiPendingCount); ?></h3>
                        <p>Pending Confirmations</p>
                    </div>
                    <div class="dash-stat-footer">Awaiting staff confirmation</div>
                </article>

                <article class="dash-stat-card theme-blue">
                    <div class="dash-stat-top">
                        <div class="dash-stat-icon"><?= staff_icon('users'); ?></div>
                        <span class="dash-stat-tag">Live Queue</span>
                    </div>
                    <div class="dash-stat-body">
                        <h3><?= number_format($kpiServingCount); ?></h3>
                        <p>In Station Queue</p>
                    </div>
                    <div class="dash-stat-footer">Waiting or being served</div>
                </article>

                <article class="dash-stat-card theme-emerald">
                    <div class="dash-stat-top">
                        <div class="dash-stat-icon"><?= staff_icon('check'); ?></div>
                        <span class="dash-stat-tag">Finished</span>
                    </div>
                    <div class="dash-stat-body">
                        <h3><?= number_format($kpiCompletedCount); ?></h3>
                        <p>Completed Today</p>
                    </div>
                    <div class="dash-stat-footer">Successful consultations</div>
                </article>
            </section>

            <!-- Unattended Operations & Clinical Audit Section -->
            <section class="dash-audit-section">
                <div class="dash-audit-header">
                    <div class="dash-audit-title-group">
                        <span class="card-header-icon" style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca;">
                            <?= staff_icon('alert'); ?>
                        </span>
                        <div>
                            <h2>Station Operations &amp; Unattended Audit</h2>
                            <p>Track patient appointment requests and queues that were left unserved past scheduled dates</p>
                        </div>
                    </div>
                    <?php if ($unattendedApptsCount > 0 || $unattendedQueueCount > 0): ?>
                        <div class="dash-audit-pill amber" style="background:#fee2e2;color:#991b1b;border-color:#fca5a5;">
                            ⚡ <?= ($unattendedApptsCount + $unattendedQueueCount); ?> Total Attention Items
                        </div>
                    <?php else: ?>
                        <div class="dash-audit-pill" style="background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;">
                            ✓ All Past Records Clear
                        </div>
                    <?php endif; ?>
                </div>

                <div class="dash-audit-grid">
                    <!-- Unattended Requests Card -->
                    <div class="dash-audit-card theme-amber" id="cardUnattendedAppts" onclick="openUnattendedModal('appts')" role="button" tabindex="0" title="Click to inspect unattended appointment requests" style="cursor:pointer;">
                        <div class="dash-audit-top">
                            <div class="dash-audit-icon-wrap amber">
                                <?= staff_icon('clock'); ?>
                            </div>
                            <span class="dash-audit-pill amber">Staff Action Missed</span>
                        </div>
                        <div class="dash-audit-body">
                            <h3><?= number_format($unattendedApptsCount); ?></h3>
                            <h4>Unattended Appointment Requests</h4>
                            <p>Patient requests that passed their booked consultation date without staff confirmation or cancellation.</p>
                        </div>
                        <div class="dash-audit-footer">
                            <span>Inspect Patient Requests (<?= $unattendedApptsCount; ?>)</span>
                            <?= staff_icon('arrow-right'); ?>
                        </div>
                    </div>

                    <!-- Unattended Queue Card -->
                    <div class="dash-audit-card theme-orange" id="cardUnattendedQueue" onclick="openUnattendedModal('queue')" role="button" tabindex="0" title="Click to inspect unattended station queues" style="cursor:pointer;">
                        <div class="dash-audit-top">
                            <div class="dash-audit-icon-wrap orange">
                                <?= staff_icon('users'); ?>
                            </div>
                            <span class="dash-audit-pill orange">No-Show / Unserved</span>
                        </div>
                        <div class="dash-audit-body">
                            <h3><?= number_format($unattendedQueueCount); ?></h3>
                            <h4>Unserved Station Queue</h4>
                            <p>Confirmed or queued patients whose scheduled appointment day ended without being marked completed.</p>
                        </div>
                        <div class="dash-audit-footer">
                            <span>Inspect Unserved Queue (<?= $unattendedQueueCount; ?>)</span>
                            <?= staff_icon('arrow-right'); ?>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 2-Column Operational Grid -->
            <div class="dash-two-col-grid">
                <!-- Left Column: Upcoming Event & Today's Schedule Overview -->
                <div class="dash-col-left">
                    <!-- Upcoming Event Banner -->
                    <section class="dashboard-banner">
                        <div class="banner-main">
                            <div class="banner-top-badge">
                                <?= staff_icon('events'); ?>
                                <span>Next Barangay Health Event</span>
                            </div>
                            <?php if ($nextEvent === null): ?>
                                <h2>No Upcoming Events</h2>
                                <p class="banner-subtext">No community outreach or immunization drives scheduled yet for <?= h($station['name']); ?>.</p>
                                <a href="?page=events&show_event_modal=1" class="dash-event-cta-btn"><?= staff_icon('plus'); ?> Schedule New Event</a>
                            <?php else: ?>
                                <h2><?= h($nextEvent['title']); ?></h2>
                                <div class="event-notice-grid">
                                    <div class="event-notice-card">
                                        <span class="notice-label"><?= staff_icon('calendar'); ?> Event Date</span>
                                        <strong><?= h(date('F j, Y', strtotime((string) $nextEvent['event_date']))); ?></strong>
                                    </div>
                                    <div class="event-notice-card">
                                        <span class="notice-label"><?= staff_icon('clock'); ?> Time Schedule</span>
                                        <strong><?= h($nextEvent['time_label']); ?><?php if (!empty($nextEvent['end_time_label'])): ?> - <?= h((string) $nextEvent['end_time_label']); ?><?php endif; ?></strong>
                                    </div>
                                </div>
                                <div class="event-banner-detail"><?= h($nextEvent['description']); ?></div>
                                <div class="banner-footer-actions">
                                    <a href="?page=events" class="dash-event-cta-btn">View All Station Events →</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>

                    <!-- Today's Schedule Overview -->
                    <section class="panel-card schedule-card">
                        <div class="panel-head">
                            <div>
                                <h2>Today's Station Clinical Flow</h2>
                                <p class="panel-subtitle">Operational consultation hours and service delivery windows</p>
                            </div>
                            <span class="schedule-clock-badge"><?= staff_icon('clock'); ?> <?= date('h:i A'); ?></span>
                        </div>
                        <div class="schedule-stack">
                            <?php foreach ($scheduleOverview as $row): ?>
                                <div class="schedule-row <?= h($row['tone']); ?>">
                                    <div class="schedule-row-time">
                                        <?= staff_icon('clock'); ?>
                                        <span><?= h($row['time']); ?></span>
                                    </div>
                                    <strong><?= h($row['label']); ?></strong>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                </div>

                <!-- Right Column: Recent Completed Patient Visits -->
                <div class="dash-col-right">
                    <!-- Recent Completed Patient Visits Card -->
                    <section class="panel-card dash-recent-card">
                        <div class="panel-head">
                            <div>
                                <h2>Recent Patient Visits</h2>
                                <p class="panel-subtitle">Completed consultations and clinical records at this station</p>
                            </div>
                            <a href="?page=patients" class="panel-link">View All Patients →</a>
                        </div>
                        <div class="dash-recent-list">
                            <?php
                                $completedVisits = array_values(array_filter(
                                    $allStationAppointments,
                                    static fn(array $item): bool => (string) ($item['status'] ?? '') === 'Completed'
                                ));
                                $latestCompletedVisits = array_slice($completedVisits, 0, 7);
                            ?>
                            <?php if (empty($latestCompletedVisits)): ?>
                                <div class="empty-state" style="padding:32px 20px;text-align:center;">
                                    <div style="font-size:1.8rem;margin-bottom:8px;">🩺</div>
                                    <strong style="display:block;color:#0f172a;margin-bottom:4px;font-size:0.95rem;">No Completed Visits Yet</strong>
                                    <p style="margin:0;color:#64748b;font-size:0.85rem;">Patient consultations completed in Queue Management will automatically appear here.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($latestCompletedVisits as $act): ?>
                                    <?php
                                        $actInitial = strtoupper(substr((string) ($act['first_name'] ?? 'P'), 0, 1) . substr((string) ($act['last_name'] ?? 'U'), 0, 1));
                                        $actCode = (string) ($act['appointment_code'] ?? $act['reference_code']);
                                        $actHasPhoto = !empty($act['photo_path']);
                                    ?>
                                    <div class="dash-recent-item">
                                        <div class="dash-recent-avatar" style="<?= $actHasPhoto ? 'background:transparent;' : ''; ?>">
                                            <?php if ($actHasPhoto): ?>
                                                <img src="../Patients/<?= h((string) $act['photo_path']); ?>" alt="<?= h(full_name($act)); ?>" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                                            <?php else: ?>
                                                <?= h($actInitial); ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="dash-recent-info">
                                            <div class="dash-recent-name-row">
                                                <strong><?= h(full_name($act)); ?></strong>
                                                <span class="dash-code-tag">#<?= h($actCode); ?></span>
                                            </div>
                                            <span class="dash-recent-meta"><?= h((string) $act['service_name']); ?> • <?= h(date('M j, Y', strtotime((string) $act['preferred_date']))); ?></span>
                                        </div>
                                        <span class="status-pill status-completed">
                                            ✓ Completed
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </section>
                </div>
            </div>
        <?php elseif ($page === 'appointments'): ?>
            <section class="page-hero">
                <h1>Appointments</h1>
                <p>Select a service to view and manage appointment requests.</p>
            </section>
            <?php if ($programFilter === ''): ?>
                <section class="appt-filter-card" style="margin-bottom: 22px;">
                    <div class="filter-toolbar-header-row">
                        <span class="filter-toolbar-label">
                            <?= staff_icon('clock'); ?>
                            <span>Filter Appointments by Timeframe:</span>
                        </span>
                        <?= render_dual_date_filter('date', $dateFilter, 'staff'); ?>
                    </div>
                </section>
                <section class="services-grid queue-services-grid">
                    <?php foreach ($programs as $program): ?>
                        <?php
                        $serviceAppointments = array_values(array_filter(
                            $appointmentsPageEntries,
                            static fn(array $item): bool => (string) ($item['service_slug'] ?? '') === $program['slug']
                        ));
                        $servicePendingCount = count(array_filter($serviceAppointments, static fn(array $item): bool => (string) ($item['status'] ?? '') === 'Pending'));
                        $serviceCancelledCount = count(array_filter($serviceAppointments, static fn(array $item): bool => (string) ($item['status'] ?? '') === 'Cancelled'));
                        $serviceTotalCount = count($serviceAppointments);
                        ?>
                        <a class="service-card queue-service-card" href="?page=appointments&program=<?= h($program['slug']); ?>&status=<?= h($statusFilter); ?>&date=<?= h($dateFilter); ?>">
                            <div class="service-card-top">
                                <div class="service-icon <?= h($program['color']); ?>"><?= staff_icon($program['icon']); ?></div>
                                <span class="service-arrow"><?= staff_icon('arrow-right'); ?></span>
                            </div>
                            <h3><?= h($program['title']); ?></h3>
                            <p><?= h($program['description']); ?></p>
                            <div class="service-queue-stats">
                                <div class="queue-stat-mini pending"><span><?= staff_icon('clock'); ?></span><strong><?= $servicePendingCount; ?></strong><small>Pending</small></div>
                                <div class="queue-stat-mini cancelled"><span><?= staff_icon('x'); ?></span><strong><?= $serviceCancelledCount; ?></strong><small>Cancelled</small></div>
                                <div class="queue-stat-mini total"><span><?= staff_icon('appointments'); ?></span><strong><?= $serviceTotalCount; ?></strong><small>Total</small></div>
                            </div>
                            <div class="service-action">View Appointments →</div>
                        </a>
                    <?php endforeach; ?>
                </section>
            <?php else: ?>
                <?php
                $currentProgram = null;
                foreach ($programs as $p) {
                    if ($p['slug'] === $programFilter) {
                        $currentProgram = $p;
                        break;
                    }
                }
                $currentProgramTitle = $currentProgram['title'] ?? ucfirst($programFilter);
                ?>
                <!-- Service Detail Top Toolbar & Breadcrumb -->
                <div class="appt-detail-topbar">
                    <div class="appt-breadcrumb-trail">
                        <a href="?page=appointments" class="appt-breadcrumb-item">
                            <?= staff_icon('appointments'); ?>
                            <span>All Services</span>
                        </a>
                        <span class="appt-breadcrumb-sep">/</span>
                        <span class="appt-breadcrumb-active"><?= h($currentProgramTitle); ?></span>
                    </div>

                    <a class="appt-back-btn" href="?page=appointments&status=<?= h($statusFilter); ?>&date=<?= h($dateFilter); ?>">
                        <?= staff_icon('arrow-left'); ?>
                        <span>Back to Services</span>
                    </a>
                </div>

                <!-- Sleek Filter and Search Bar -->
                <section class="appt-filter-card">
                    <form method="get" class="appt-filter-form">
                        <input type="hidden" name="page" value="appointments">
                        <input type="hidden" name="program" value="<?= h($programFilter); ?>">

                        <div class="appt-search-field">
                            <span class="appt-search-icon"><?= staff_icon('search'); ?></span>
                            <input type="text" name="search" value="<?= h($appointmentSearch); ?>" placeholder="Search by patient name, ID, or phone..." maxlength="30">
                            <?php if ($appointmentSearch !== ''): ?>
                                <a href="?page=appointments&program=<?= h($programFilter); ?>&status=<?= h($statusFilter); ?>&date=<?= h($dateFilter); ?>" class="appt-search-clear" title="Clear search">
                                    <?= staff_icon('x'); ?>
                                </a>
                            <?php endif; ?>
                        </div>

                        <div class="appt-filter-dropdowns">
                            <div class="appt-select-wrap">
                                <span class="appt-select-icon"><?= staff_icon('filter'); ?></span>
                                <select name="status" onchange="this.form.submit()">
                                    <option value="" <?= $statusFilter === '' ? 'selected' : ''; ?>>All Statuses</option>
                                    <?php foreach (['Pending', 'Confirmed', 'Cancelled'] as $option): ?>
                                        <option value="<?= h($option); ?>" <?= $statusFilter === $option ? 'selected' : ''; ?>><?= h($option); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <?= render_dual_date_filter('date', $dateFilter, 'staff'); ?>

                            <button type="submit" class="appt-find-btn primary-btn slim">
                                <?= staff_icon('search'); ?>
                                <span>Filter</span>
                            </button>
                        </div>
                    </form>
                </section>

                <!-- Modern Light Stat Cards -->
                <section class="appt-metrics-grid">
                    <article class="appt-metric-card pending">
                        <div class="appt-metric-icon">
                            <?= staff_icon('clock'); ?>
                        </div>
                        <div class="appt-metric-content">
                            <span class="appt-metric-label">Pending Confirmation</span>
                            <strong class="appt-metric-val"><?= number_format($appointmentsPendingCount); ?></strong>
                        </div>
                    </article>

                    <article class="appt-metric-card cancelled">
                        <div class="appt-metric-icon">
                            <?= staff_icon('x'); ?>
                        </div>
                        <div class="appt-metric-content">
                            <span class="appt-metric-label">Cancelled Bookings</span>
                            <strong class="appt-metric-val"><?= number_format($appointmentsCancelledCount); ?></strong>
                        </div>
                    </article>

                    <article class="appt-metric-card total">
                        <div class="appt-metric-icon">
                            <?= staff_icon('appointments'); ?>
                        </div>
                        <div class="appt-metric-content">
                            <span class="appt-metric-label">Total Records</span>
                            <strong class="appt-metric-val"><?= number_format($appointmentsTotalCount); ?></strong>
                        </div>
                    </article>
                </section>

                <!-- Modern Appointment Record Cards -->
                <section class="appt-records-stack">
                    <?php if ($appointmentsPageEntries === []): ?>
                        <div class="panel-card empty-state appt-empty-box">
                            <div class="appt-empty-icon"><?= staff_icon('appointments'); ?></div>
                            <h3>No appointments found</h3>
                            <p>No appointment records match the current filters or search criteria.</p>
                            <?php if ($appointmentSearch !== '' || $statusFilter !== '' || $dateFilter !== ''): ?>
                                <a href="?page=appointments&program=<?= h($programFilter); ?>" class="primary-btn slim" style="margin-top:14px;display:inline-flex;">Reset Filters</a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <?php foreach ($appointmentsPageEntries as $appointment): ?>
                            <?php
                            $patInitials = strtoupper(substr((string) ($appointment['first_name'] ?? 'P'), 0, 1) . substr((string) ($appointment['last_name'] ?? 'U'), 0, 1));
                            $apptCode = (string) ($appointment['appointment_code'] ?? $appointment['reference_code'] ?? '');
                            $isPending = (string) ($appointment['status'] ?? '') === 'Pending';
                            ?>
                            <article class="modern-appt-card <?= $isPending ? 'is-pending' : 'is-cancelled'; ?>">
                                <div class="appt-card-left">
                                    <div class="appt-patient-avatar">
                                        <?= h($patInitials); ?>
                                    </div>
                                    <div class="appt-patient-details">
                                        <div class="appt-name-row">
                                            <h3><?= h(full_name($appointment)); ?></h3>
                                            <?php if ($apptCode !== ''): ?>
                                                <span class="appt-code-badge">#<?= h($apptCode); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="appt-service-row">
                                            <span class="appt-service-tag">
                                                <?= staff_icon('appointments'); ?>
                                                <span><?= h($appointment['service_name']); ?></span>
                                            </span>
                                        </div>
                                        <div class="appt-meta-chips-row">
                                            <span class="appt-meta-chip">
                                                <?= staff_icon('clock'); ?>
                                                <span><?= h(date('D, M j, Y', strtotime((string) $appointment['preferred_date']))); ?></span>
                                            </span>
                                            <span class="appt-meta-chip">
                                                <?= staff_icon('clock'); ?>
                                                <span><?= h($appointment['preferred_time']); ?></span>
                                            </span>
                                            <?php if (!empty($appointment['contact_number'])): ?>
                                                <span class="appt-meta-chip phone">
                                                    <span><?= h($appointment['contact_number']); ?></span>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="appt-card-right">
                                    <span class="status-pill status-<?= h(status_class($appointment['status'])); ?>">
                                        <?= $isPending ? '⏳ Pending' : '✕ Cancelled'; ?>
                                    </span>
                                    <?php if ($isPending): ?>
                                        <div class="appt-actions-row">
                                            <form method="post" class="appt-action-form">
                                                <input type="hidden" name="csrf_token" value="<?= h($csrf); ?>">
                                                <input type="hidden" name="appointment_id" value="<?= h((string) $appointment['id']); ?>">
                                                <input type="hidden" name="new_status" value="Confirmed">
                                                <button type="submit" class="appt-action-btn appt-btn-confirm" title="Confirm appointment and send to queue">
                                                    <span class="btn-icon-wrap"><?= staff_icon('check'); ?></span>
                                                    <span>Confirm</span>
                                                </button>
                                            </form>
                                            <form method="post" class="appt-action-form" onsubmit="return confirm('Are you sure you want to cancel this appointment request?');">
                                                <input type="hidden" name="csrf_token" value="<?= h($csrf); ?>">
                                                <input type="hidden" name="appointment_id" value="<?= h((string) $appointment['id']); ?>">
                                                <input type="hidden" name="new_status" value="Cancelled">
                                                <button type="submit" class="appt-action-btn appt-btn-cancel" title="Cancel appointment request">
                                                    <span class="btn-icon-wrap"><?= staff_icon('x'); ?></span>
                                                    <span>Cancel</span>
                                                </button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <span class="appt-cancelled-note">Booking Cancelled</span>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
        <?php elseif ($page === 'queue'): ?>
            <section class="page-hero">
                <h1>Queue Management</h1>
                <p>Select a health service to monitor real-time queue triage, call patients, and manage consultations.</p>
            </section>
            <?php if ($programFilter === ''): ?>
                <section class="appt-filter-card" style="margin-bottom: 22px;">
                    <div class="filter-toolbar-header-row">
                        <span class="filter-toolbar-label">
                            <?= staff_icon('clock'); ?>
                            <span>Filter Queue by Timeframe:</span>
                        </span>
                        <?= render_dual_date_filter('queue_date', $queueDate, 'staff'); ?>
                    </div>
                </section>
                <section class="services-grid queue-services-grid">
                    <?php foreach ($programs as $program): ?>
                        <?php
                        $serviceQueueEntries = array_values(array_filter(
                            $queueEntries,
                            static fn(array $item): bool => (string) ($item['service_slug'] ?? '') === $program['slug']
                        ));
                        $serviceWaitingCount = count(array_filter($serviceQueueEntries, static fn(array $item): bool => (string) ($item['status'] ?? '') === 'Confirmed'));
                        $serviceServingCount = count(array_filter($serviceQueueEntries, static fn(array $item): bool => (string) ($item['status'] ?? '') === 'Serving'));
                        $serviceCompletedCount = count(array_filter($serviceQueueEntries, static fn(array $item): bool => (string) ($item['status'] ?? '') === 'Completed'));
                        $serviceTotalCount = count($serviceQueueEntries);
                        ?>
                        <a class="service-card queue-service-card" href="?page=queue&program=<?= h($program['slug']); ?>&queue_date=<?= h($queueDate); ?>">
                            <div class="service-card-top">
                                <div class="service-icon <?= h($program['color']); ?>"><?= staff_icon($program['icon']); ?></div>
                                <span class="service-arrow"><?= staff_icon('arrow-right'); ?></span>
                            </div>
                            <h3><?= h($program['title']); ?></h3>
                            <p><?= h($program['description']); ?></p>
                            <div class="service-queue-stats">
                                <div class="queue-stat-mini waiting"><span><?= staff_icon('clock'); ?></span><strong><?= $serviceWaitingCount; ?></strong><small>Waiting</small></div>
                                <div class="queue-stat-mini serving"><span><?= staff_icon('users'); ?></span><strong><?= $serviceServingCount; ?></strong><small>Serving</small></div>
                                <div class="queue-stat-mini completed"><span><?= staff_icon('check'); ?></span><strong><?= $serviceCompletedCount; ?></strong><small>Done</small></div>
                            </div>
                            <div class="service-action">Open Station Queue →</div>
                        </a>
                    <?php endforeach; ?>
                </section>
            <?php else: ?>
                <?php
                $currentProgram = null;
                foreach ($programs as $p) {
                    if ($p['slug'] === $programFilter) {
                        $currentProgram = $p;
                        break;
                    }
                }
                $currentProgramTitle = $currentProgram['title'] ?? ucfirst($programFilter);
                ?>
                <!-- Service Detail Top Toolbar & Breadcrumb -->
                <div class="appt-detail-topbar">
                    <div class="appt-breadcrumb-trail">
                        <a href="?page=queue" class="appt-breadcrumb-item">
                            <?= staff_icon('queue'); ?>
                            <span>All Queue Services</span>
                        </a>
                        <span class="appt-breadcrumb-sep">/</span>
                        <span class="appt-breadcrumb-active"><?= h($currentProgramTitle); ?></span>
                    </div>

                    <a class="appt-back-btn" href="?page=queue&queue_date=<?= h($queueDate); ?>">
                        <?= staff_icon('arrow-left'); ?>
                        <span>Back to Services</span>
                    </a>
                </div>

                <!-- Sleek Filter and Search Bar -->
                <section class="appt-filter-card">
                    <form method="get" class="appt-filter-form">
                        <input type="hidden" name="page" value="queue">
                        <input type="hidden" name="program" value="<?= h($programFilter); ?>">

                        <div class="appt-search-field">
                            <span class="appt-search-icon"><?= staff_icon('search'); ?></span>
                            <input type="text" name="search" value="<?= h($appointmentSearch); ?>" placeholder="Search by patient name or appointment ID..." maxlength="30">
                            <?php if ($appointmentSearch !== ''): ?>
                                <a href="?page=queue&program=<?= h($programFilter); ?>&queue_date=<?= h($queueDate); ?>" class="appt-search-clear" title="Clear search">
                                    <?= staff_icon('x'); ?>
                                </a>
                            <?php endif; ?>
                        </div>

                        <div class="appt-filter-dropdowns">
                            <?= render_dual_date_filter('queue_date', $queueDate, 'staff'); ?>

                            <button type="submit" class="appt-find-btn primary-btn slim">
                                <?= staff_icon('search'); ?>
                                <span>Filter</span>
                            </button>
                        </div>
                    </form>
                </section>

                <!-- Modern 3-KPI Stat Cards -->
                <section class="appt-metrics-grid">
                    <article class="appt-metric-card pending">
                        <div class="appt-metric-icon">
                            <?= staff_icon('clock'); ?>
                        </div>
                        <div class="appt-metric-content">
                            <span class="appt-metric-label">Waiting in Line</span>
                            <strong class="appt-metric-val"><?= number_format($queueWaitingCount); ?></strong>
                        </div>
                    </article>

                    <article class="appt-metric-card serving-metric">
                        <div class="appt-metric-icon">
                            <?= staff_icon('users'); ?>
                        </div>
                        <div class="appt-metric-content">
                            <span class="appt-metric-label">Currently Serving</span>
                            <strong class="appt-metric-val"><?= number_format($queueBeingServedCount); ?></strong>
                        </div>
                    </article>

                    <article class="appt-metric-card total">
                        <div class="appt-metric-icon">
                            <?= staff_icon('check'); ?>
                        </div>
                        <div class="appt-metric-content">
                            <span class="appt-metric-label">Completed Today</span>
                            <strong class="appt-metric-val"><?= number_format($queueCompletedCount); ?></strong>
                        </div>
                    </article>
                </section>

                <!-- Modern Queue Record Cards -->
                <section class="queue-records-stack">
                    <?php if ($queueEntries === []): ?>
                        <div class="panel-card empty-state appt-empty-box">
                            <div class="appt-empty-icon"><?= staff_icon('queue'); ?></div>
                            <h3>No patients in queue</h3>
                            <p>No active or scheduled patients found for this service under the selected timeframe.</p>
                            <?php if ($appointmentSearch !== ''): ?>
                                <a href="?page=queue&program=<?= h($programFilter); ?>&queue_date=<?= h($queueDate); ?>" class="primary-btn slim" style="margin-top:14px;display:inline-flex;">Reset Search</a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <?php
                        $today = date('Y-m-d');
                        $groupedByDate = [];
                        foreach ($queueEntries as $row) {
                            $appointmentDate = (string) ($row['preferred_date'] ?? '');
                            $groupedByDate[$appointmentDate][] = $row;
                        }
                        ksort($groupedByDate);
                        $queueGlobalCounter = 1;
                        ?>
                        <?php foreach ($groupedByDate as $dateKey => $dateEntries): ?>
                            <?php
                            $isToday = $dateKey === $today;
                            $isFuture = $dateKey > $today;
                            $dateLabel = $isToday ? "Today's Live Queue (" . date('M j, Y') . ")" : date('l, F j, Y', strtotime($dateKey));
                            ?>
                            <div class="queue-group-header">
                                <span class="queue-date-title"><?= staff_icon('calendar'); ?> <?= h($dateLabel); ?></span>
                                <span class="queue-count-badge"><?= count($dateEntries); ?> patient<?= count($dateEntries) > 1 ? 's' : ''; ?></span>
                            </div>

                            <div class="queue-cards-list">
                                <?php foreach ($dateEntries as $index => $row): ?>
                                    <?php
                                    $queueStatus = (string) ($row['status'] ?? 'Confirmed');
                                    $queueStateClass = $queueStatus === 'Serving' ? 'serving' : ($queueStatus === 'Completed' ? 'completed' : 'waiting');
                                    $hasPatientPhoto = trim((string) ($row['photo_path'] ?? '')) !== '';
                                    $isFaded = $isFuture;
                                    $patInitials = strtoupper(substr((string) ($row['first_name'] ?? 'P'), 0, 1) . substr((string) ($row['last_name'] ?? 'U'), 0, 1));
                                    $apptCode = (string) ($row['appointment_code'] ?? $row['reference_code'] ?? '');
                                    $tokenNumber = sprintf('%02d', $queueGlobalCounter++);
                                    ?>
                                    <article class="modern-queue-card state-<?= h($queueStateClass); ?><?= $isFaded ? ' is-faded' : ''; ?>">
                                        <div class="queue-card-left">
                                            <div class="queue-token-box token-<?= h($queueStateClass); ?>" title="<?= $queueStatus === 'Serving' ? 'Currently being attended' : ($queueStatus === 'Completed' ? 'Consultation completed' : 'First-Come First-Served Queue'); ?>">
                                                <span class="token-fcfs-icon">
                                                    <?= $queueStatus === 'Serving' ? staff_icon('pulse') : ($queueStatus === 'Completed' ? staff_icon('check') : staff_icon('clock')); ?>
                                                </span>
                                                <small class="token-label"><?= $queueStatus === 'Serving' ? 'SERVING' : ($queueStatus === 'Completed' ? 'DONE' : 'FCFS'); ?></small>
                                            </div>

                                            <div class="queue-pat-avatar">
                                                <?= h($patInitials); ?>
                                            </div>

                                            <div class="queue-pat-info">
                                                <div class="queue-pat-name-row">
                                                    <h3><?= h(full_name($row)); ?></h3>
                                                    <?php if ($apptCode !== ''): ?>
                                                        <span class="appt-code-badge">#<?= h($apptCode); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="queue-pat-meta-row">
                                                    <span class="queue-meta-pill service">
                                                        <?= staff_icon('stethoscope'); ?>
                                                        <span><?= h($row['service_name']); ?></span>
                                                    </span>
                                                    <span class="queue-meta-pill time">
                                                        <?= staff_icon('clock'); ?>
                                                        <span><?= h($row['preferred_time'] ?? 'Regular Hours'); ?></span>
                                                    </span>
                                                    <?php if ($hasPatientPhoto): ?>
                                                        <span class="queue-meta-pill photo-ok">
                                                            <?= staff_icon('camera'); ?>
                                                            <span>ID Photo on File</span>
                                                        </span>
                                                    <?php else: ?>
                                                        <a href="?page=image-capture&appointment=<?= h((string) $row['id']); ?><?= !empty($row['service_slug']) ? '&program=' . urlencode((string) $row['service_slug']) : ($programFilter !== '' ? '&program=' . urlencode($programFilter) : ''); ?>" class="queue-meta-pill photo-req" title="Take proof photo at front desk">
                                                            <?= staff_icon('camera'); ?>
                                                            <span>Needs Photo ↗</span>
                                                        </a>
                                                    <?php endif; ?>
                                                    <?php if (!empty($row['contact_number'])): ?>
                                                        <span class="queue-meta-pill phone">
                                                            <?= staff_icon('phone'); ?>
                                                            <span><?= h($row['contact_number']); ?></span>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="queue-card-right">
                                            <span class="status-pill status-queue-<?= h($queueStateClass); ?>">
                                                <?= $queueStatus === 'Serving' ? '⚡ Serving Now' : ($queueStatus === 'Completed' ? '✓ Completed' : '⏳ In Queue'); ?>
                                            </span>

                                            <div class="queue-actions-wrap">
                                                <?php if (!$isFaded): ?>
                                                    <?php if ($queueStatus === 'Confirmed'): ?>
                                                        <form method="post" style="margin:0;">
                                                            <input type="hidden" name="csrf_token" value="<?= h($csrf); ?>">
                                                            <input type="hidden" name="appointment_id" value="<?= h((string) $row['id']); ?>">
                                                            <input type="hidden" name="new_status" value="Serving">
                                                            <button type="submit" class="primary-btn slim queue-call-btn">
                                                                <?= staff_icon('arrow-right'); ?>
                                                                <span>Call / Serve</span>
                                                            </button>
                                                        </form>
                                                    <?php elseif ($queueStatus === 'Serving'): ?>
                                                        <?php
                                                        $rowHasVitals = appointment_has_vitals($row);
                                                        $rowHasPhoto = appointment_has_photo($row);
                                                        $rowHasNotes = appointment_has_clinical_notes($row);
                                                        $rowCanComplete = $rowHasVitals && $rowHasPhoto && $rowHasNotes;

                                                        $missingReqs = [];
                                                        if (!$rowHasVitals) $missingReqs[] = 'Vital Signs';
                                                        if (!$rowHasPhoto) $missingReqs[] = 'Patient Photo';
                                                        if (!$rowHasNotes) $missingReqs[] = 'Clinical Notes';
                                                        $completeDisabledTitle = 'Cannot complete yet. Missing: ' . implode(', ', $missingReqs);
                                                        ?>
                                                        <?php if ($rowCanComplete): ?>
                                                            <form method="post" style="margin:0;">
                                                                <input type="hidden" name="csrf_token" value="<?= h($csrf); ?>">
                                                                <input type="hidden" name="appointment_id" value="<?= h((string) $row['id']); ?>">
                                                                <input type="hidden" name="new_status" value="Completed">
                                                                <button type="submit" class="success-btn slim queue-done-btn" title="Complete consultation">
                                                                    <?= staff_icon('check'); ?>
                                                                    <span>Complete</span>
                                                                </button>
                                                            </form>
                                                        <?php else: ?>
                                                            <button type="button" class="success-btn slim queue-done-btn is-disabled" disabled title="<?= h($completeDisabledTitle); ?>">
                                                                <?= staff_icon('check'); ?>
                                                                <span>Complete</span>
                                                            </button>
                                                        <?php endif; ?>

                                                        <?php if (!$rowHasVitals): ?>
                                                            <a href="?page=queue<?= $programFilter !== '' ? '&program=' . h($programFilter) : ''; ?><?= $queueDate !== '' && $queueDate !== 'today' ? '&queue_date=' . h($queueDate) : ''; ?>&encode_vitals=<?= h((string) $apptCode); ?>" class="queue-vitals-btn" title="Encode vital signs">
                                                                <?= staff_icon('edit'); ?>
                                                                <span>Encode Vitals</span>
                                                            </a>
                                                        <?php else: ?>
                                                            <button type="button" class="queue-vitals-btn is-disabled" disabled title="Vital signs have already been recorded">
                                                                <?= staff_icon('check'); ?>
                                                                <span>Vitals Encoded</span>
                                                            </button>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="queue-scheduled-badge">
                                                        <?= staff_icon('calendar'); ?> Scheduled
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </section>
            <?php endif; ?>

            <!-- Encode Vital Signs Modal (Queue Management) -->
            <?php if ($selectedVitalsAppointment !== null): ?>
                <?php
                $vitalsReturnUrl = '?page=queue' . ($programFilter !== '' ? '&program=' . urlencode($programFilter) : '') . ($queueDate !== '' && $queueDate !== 'today' ? '&queue_date=' . urlencode($queueDate) : '');
                ?>
                <section class="account-modal-backdrop" id="vitalsModalBackdrop">
                    <div class="account-modal-card clinical-dialog-card" role="dialog" aria-modal="true">
                        <div class="account-modal-header">
                            <div class="account-modal-title-group">
                                <span class="account-modal-icon"><?= staff_icon('pulse'); ?></span>
                                <div>
                                    <h2>Encode Vital Signs</h2>
                                    <p>Appointment #<?= h((string) $selectedVitalsAppointment['appointment_code']); ?> • <?= h((string) $selectedVitalsAppointment['service_name']); ?></p>
                                </div>
                            </div>
                            <a class="account-modal-close" href="<?= h($vitalsReturnUrl); ?>" onclick="return window.closeClinicalModal(event, '<?= h($vitalsReturnUrl); ?>');" aria-label="Close modal">×</a>
                        </div>

                        <form method="post" class="account-settings-form">
                            <input type="hidden" name="action" value="save_vitals">
                            <input type="hidden" name="return_url" value="<?= h($vitalsReturnUrl); ?>">
                            <input type="hidden" name="csrf_token" value="<?= h($csrf); ?>">
                            <input type="hidden" name="appointment_id" value="<?= h((string) $selectedVitalsAppointment['id']); ?>">

                            <div class="account-modal-body">
                                <!-- Compact Patient Identity Header Strip -->
                                <div class="patient-modal-banner">
                                    <div class="patient-modal-avatar-wrap">
                                        <?php if ((string) ($selectedVitalsAppointment['photo_path'] ?? '') !== ''): ?>
                                            <img src="../Patients/<?= h((string) $selectedVitalsAppointment['photo_path']); ?>" alt="<?= h(full_name($selectedVitalsAppointment)); ?> photo" class="patient-modal-thumb">
                                        <?php else: ?>
                                            <div class="patient-modal-initials">
                                                <?= strtoupper(substr((string) ($selectedVitalsAppointment['first_name'] ?? 'P'), 0, 1) . substr((string) ($selectedVitalsAppointment['last_name'] ?? 'U'), 0, 1)); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="patient-modal-details">
                                        <div class="patient-modal-title-row">
                                            <h3><?= h(full_name($selectedVitalsAppointment)); ?></h3>
                                            <span class="appt-code-badge">#<?= h((string) $selectedVitalsAppointment['appointment_code']); ?></span>
                                            <?php if ((string) ($selectedVitalsAppointment['photo_path'] ?? '') !== ''): ?>
                                                <span class="queue-id-verified"><?= staff_icon('check'); ?> Verified ID</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="patient-modal-meta-row">
                                            <span class="pat-meta-pill service"><?= staff_icon('stethoscope'); ?> <?= h((string) $selectedVitalsAppointment['service_name']); ?></span>
                                            <span class="pat-meta-pill date"><?= staff_icon('calendar'); ?> <?= h(date('F j, Y', strtotime((string) $selectedVitalsAppointment['preferred_date']))); ?></span>
                                            <span class="pat-meta-pill time"><?= staff_icon('clock'); ?> <?= h((string) ($selectedVitalsAppointment['preferred_time'] ?? 'Regular Hours')); ?></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Patient Demographics Overview -->
                                <div class="clinical-demographics-card">
                                    <div class="demo-item">
                                        <label>Age &amp; Gender</label>
                                        <span><?= h(age_label($selectedVitalsAppointment)); ?> • <?= h((string) ($selectedVitalsAppointment['gender'] ?? 'Not specified')); ?></span>
                                    </div>
                                    <div class="demo-item">
                                        <label>Date of Birth</label>
                                        <span><?= !empty($selectedVitalsAppointment['birth_date']) ? h(date('F j, Y', strtotime((string) $selectedVitalsAppointment['birth_date']))) : 'N/A'; ?></span>
                                    </div>
                                    <div class="demo-item">
                                        <label>Contact Number</label>
                                        <span class="contact-highlight"><?= staff_icon('phone'); ?> <?= h((string) ($selectedVitalsAppointment['contact_number'] ?: 'None provided')); ?></span>
                                    </div>
                                    <div class="demo-item full-width">
                                        <label>Home Address</label>
                                        <span><?= h((string) ($selectedVitalsAppointment['complete_address'] ?: 'Barangay ' . $station['barangay'] . ', Bacolod City')); ?></span>
                                    </div>
                                    <?php if (!empty($selectedVitalsAppointment['notes'])): ?>
                                        <div class="demo-item full-width patient-complaint-box">
                                            <label>Patient Chief Complaint / Booking Notes</label>
                                            <span><?= h((string) $selectedVitalsAppointment['notes']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="account-section-divider">
                                    <?= staff_icon('pulse'); ?>
                                    <span>Vital Signs Measurement</span>
                                </div>

                                <div class="form-row-grid">
                                    <div class="form-group-item">
                                        <label for="queue_body_temp" class="form-field-label">
                                            <span>Body Temperature</span>
                                             <span class="required">*</span>
                                        </label>
                                        <input type="text" id="queue_body_temp" name="body_temperature" value="<?= h((string) ($selectedVitalsAppointment['body_temperature'] ?? '')); ?>" placeholder="e.g. 36.5 °C" required class="form-input-field">
                                    </div>
                                    <div class="form-group-item">
                                        <label for="queue_pulse_rate" class="form-field-label">
                                            <span>Pulse Rate (PR)</span>
                                            <span class="required">*</span>
                                        </label>
                                        <input type="text" id="queue_pulse_rate" name="pulse_rate" value="<?= h((string) ($selectedVitalsAppointment['pulse_rate'] ?? '')); ?>" placeholder="e.g. 78 bpm" required class="form-input-field">
                                    </div>
                                </div>

                                <div class="form-row-grid">
                                    <div class="form-group-item">
                                        <label for="queue_resp_rate" class="form-field-label">
                                            <span>Respiration Rate (RR)</span>
                                            <span class="required">*</span>
                                        </label>
                                        <input type="text" id="queue_resp_rate" name="respiration_rate" value="<?= h((string) ($selectedVitalsAppointment['respiration_rate'] ?? '')); ?>" placeholder="e.g. 18 cpm" required class="form-input-field">
                                    </div>
                                    <div class="form-group-item">
                                        <label for="queue_blood_pres" class="form-field-label">
                                            <span>Blood Pressure (BP)</span>
                                            <span class="required">*</span>
                                        </label>
                                        <input type="text" id="queue_blood_pres" name="blood_pressure" value="<?= h((string) ($selectedVitalsAppointment['blood_pressure'] ?? '')); ?>" placeholder="e.g. 120/80 mmHg" required class="form-input-field">
                                    </div>
                                </div>
                            </div>

                            <div class="account-modal-footer">
                                <a class="clinical-modal-cancel-btn" href="<?= h($vitalsReturnUrl); ?>" onclick="return window.closeClinicalModal(event, '<?= h($vitalsReturnUrl); ?>');">
                                    <?= staff_icon('x'); ?>
                                    <span>Cancel</span>
                                </a>
                                <button type="submit" class="clinical-modal-save-btn">
                                    <?= staff_icon('check'); ?>
                                    <span>Save Vital Signs</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </section>
            <?php endif; ?>
        <?php elseif ($page === 'patients'): ?>
            <section class="page-hero">
                <h1>Patient Records &amp; Clinical Details</h1>
                <p>Select a health service to encode clinical remarks, doctor's notes, and review completed medical histories.</p>
            </section>
            <?php if ($programFilter === ''): ?>
                <!-- Overview Filter Toolbar -->
                <section class="appt-filter-card" style="margin-bottom: 24px;">
                    <form method="get" class="appt-filter-form">
                        <input type="hidden" name="page" value="patients">

                        <div class="appt-search-field">
                            <span class="appt-search-icon"><?= staff_icon('search'); ?></span>
                            <input type="text" name="patient_search" value="<?= h($patientSearch); ?>" placeholder="Search across all programs by name, appointment code, or phone..." maxlength="40">
                            <?php if ($patientSearch !== ''): ?>
                                <a href="?page=patients<?= $patientDateFilter !== '' ? '&patient_date=' . h($patientDateFilter) : ''; ?><?= $patientStatus !== 'both' ? '&patient_status=' . h($patientStatus) : ''; ?>" class="appt-search-clear" title="Clear search">
                                    <?= staff_icon('x'); ?>
                                </a>
                            <?php endif; ?>
                        </div>

                        <div class="appt-filter-dropdowns">
                            <div class="appt-date-input-wrap">
                                <span class="appt-date-input-icon"><?= staff_icon('calendar'); ?></span>
                                <input type="date" name="patient_date" value="<?= h($patientDateFilter); ?>" class="appt-date-picker-input" title="Filter consultation records by date">
                                <?php if ($patientDateFilter !== ''): ?>
                                    <a href="?page=patients<?= $patientSearch !== '' ? '&patient_search=' . h($patientSearch) : ''; ?>" class="appt-date-clear-btn" title="Clear date filter">
                                        <?= staff_icon('x'); ?>
                                    </a>
                                <?php endif; ?>
                            </div>

                            <button type="submit" class="appt-filter-submit-btn">
                                <?= staff_icon('filter'); ?>
                                <span>Filter</span>
                            </button>

                            <?php if ($patientSearch !== '' || $patientDateFilter !== ''): ?>
                                <a href="?page=patients" class="appt-filter-reset-btn" title="Reset all filters">
                                    <?= staff_icon('history'); ?>
                                    <span>Reset</span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </section>

                <?php if ($patientSearch !== ''): ?>
                    <section class="patient-search-result-box" style="margin-bottom: 24px;">
                        <div class="panel-head">
                            <h2>Search Result</h2>
                        </div>
                        <?php if ($clinicalSearchResult === null): ?>
                            <div class="panel-card empty-state">No appointment found matching that ID or search term.</div>
                        <?php else: ?>
                            <?php $searchRecordComplete = appointment_has_completed_clinical_details($clinicalSearchResult); ?>
                            <div class="modern-patient-record-card patient-record-card <?= $searchRecordComplete ? 'is-completed' : 'is-ongoing'; ?>">
                                <div class="pat-card-left">
                                    <div class="pat-card-avatar"><?= strtoupper(substr((string) ($clinicalSearchResult['first_name'] ?? 'P'), 0, 1) . substr((string) ($clinicalSearchResult['last_name'] ?? 'U'), 0, 1)); ?></div>
                                    <div class="pat-card-info">
                                        <div class="pat-card-title-row">
                                            <h3><?= h(full_name($clinicalSearchResult)); ?></h3>
                                            <span class="appt-code-badge">#<?= h((string) $clinicalSearchResult['appointment_code']); ?></span>
                                        </div>
                                        <div class="pat-card-meta-row">
                                            <span class="pat-meta-pill service"><?= staff_icon('stethoscope'); ?> <?= h((string) $clinicalSearchResult['service_name']); ?></span>
                                            <span class="pat-meta-pill date"><?= staff_icon('calendar'); ?> <?= h(date('F j, Y', strtotime((string) $clinicalSearchResult['preferred_date']))); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="pat-card-right">
                                    <span class="status-pill <?= $searchRecordComplete ? 'status-confirmed' : 'status-pending'; ?>"><?= $searchRecordComplete ? '✓ Recorded' : 'Ongoing'; ?></span>
                                    <?php if ($searchRecordComplete): ?>
                                        <a class="view-medical-file-btn" href="?page=patients<?= $patientDateFilter !== '' ? '&patient_date=' . h($patientDateFilter) : ''; ?>&appointment_view=<?= h((string) $clinicalSearchResult['appointment_code']); ?>" title="View detailed clinical record">
                                            <?= staff_icon('eye'); ?>
                                            <span>View Medical File</span>
                                        </a>
                                    <?php else: ?>
                                        <?php $isSearchResultServing = (string) ($clinicalSearchResult['status'] ?? '') === 'Serving'; ?>
                                        <?php if ($isSearchResultServing): ?>
                                            <a class="remarks-btn is-active" href="?page=patients<?= $patientDateFilter !== '' ? '&patient_date=' . h($patientDateFilter) : ''; ?>&appointment_remarks=<?= h((string) $clinicalSearchResult['appointment_code']); ?>" title="Encode doctor remarks & clinical assessment">
                                                <?= staff_icon('edit'); ?>
                                                <span>Remarks</span>
                                            </a>
                                        <?php else: ?>
                                            <button type="button" class="remarks-btn is-disabled" disabled title="Clinical remarks can be encoded once patient consultation begins.">
                                                <?= staff_icon('edit'); ?>
                                                <span>Remarks</span>
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <form method="post" action="?action=save_clinical_details" class="account-settings-form clinical-save-form" style="margin-top:14px;padding:16px;background:#f8fafc;border-radius:12px;border:1px solid #e2e8f0;">
                                <input type="hidden" name="action" value="save_clinical_details">
                                <input type="hidden" name="csrf_token" value="<?= h($csrf); ?>">
                                <input type="hidden" name="appointment_id" value="<?= h((string) $clinicalSearchResult['id']); ?>">
                                <input type="hidden" name="return_url" value="?page=patients&patient_search=<?= urlencode($patientSearch); ?>">
                                <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(140px, 1fr));gap:12px;margin-bottom:12px;">
                                    <div class="form-group-item">
                                        <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px;">Body Temperature (°C)</label>
                                        <input type="text" name="temperature" value="<?= h((string)($clinicalSearchResult['body_temperature'] ?? '36.6')); ?>" class="form-input-field" required>
                                    </div>
                                    <div class="form-group-item">
                                        <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px;">Blood Pressure</label>
                                        <input type="text" name="blood_pressure" value="<?= h((string)($clinicalSearchResult['blood_pressure'] ?? '120/80')); ?>" class="form-input-field" required>
                                    </div>
                                    <div class="form-group-item">
                                        <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px;">Pulse Rate (bpm)</label>
                                        <input type="text" name="pulse" value="<?= h((string)($clinicalSearchResult['pulse_rate'] ?? '75')); ?>" class="form-input-field" required>
                                    </div>
                                </div>
                                <div class="form-group-item" style="margin-bottom:12px;">
                                    <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px;">Doctor's Notes / Clinical Remarks</label>
                                    <textarea name="doctor_notes" rows="2" class="form-input-field" placeholder="Patient clinical evaluation..."><?= h((string)($clinicalSearchResult['doctor_notes'] ?? 'Patient reviewed. Prescribed standard vitamins.')); ?></textarea>
                                </div>
                                <div style="display:flex;gap:10px;align-items:center;">
                                    <button type="submit" class="primary-btn slim">Save Clinical Details</button>
                                    <a class="primary-btn blue-btn slim" href="?page=patients&patient_search=<?= urlencode($patientSearch); ?>&appointment_followup=<?= h((string)$clinicalSearchResult['appointment_code']); ?>">
                                        <span class="btn-icon-wrap"><?= staff_icon('calendar'); ?></span>
                                        <span>Schedule Follow-up</span>
                                    </a>
                                </div>
                            </form>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>

                <section class="services-grid queue-services-grid">
                    <?php foreach ($programs as $program): ?>
                        <?php
                        $serviceRecentEntries = array_values(array_filter(
                            $recentStationAppointments,
                            static fn(array $item): bool => (string) ($item['service_slug'] ?? '') === $program['slug']
                        ));
                        $serviceRecordEntries = array_values(array_filter(
                            $patientStationRecords,
                            static fn(array $item): bool => (string) ($item['service_slug'] ?? '') === $program['slug']
                        ));
                        $ongoingCount = count($serviceRecentEntries);
                        $completedCount = count($serviceRecordEntries);
                        $displayTotal = $ongoingCount + $completedCount;
                        ?>
                        <a class="service-card queue-service-card" href="?page=patients&program=<?= h($program['slug']); ?><?= $patientDateFilter !== '' ? '&patient_date=' . h($patientDateFilter) : ''; ?>">
                            <div class="service-card-top">
                                <div class="service-icon <?= h($program['color']); ?>"><?= staff_icon($program['icon']); ?></div>
                                <span class="service-arrow"><?= staff_icon('arrow-right'); ?></span>
                            </div>
                            <h3><?= h($program['title']); ?></h3>
                            <p><?= h($program['description']); ?></p>
                            <div class="service-queue-stats">
                                <div class="queue-stat-mini serving"><span><?= staff_icon('edit'); ?></span><strong><?= $ongoingCount; ?></strong><small>Ongoing</small></div>
                                <div class="queue-stat-mini completed"><span><?= staff_icon('check'); ?></span><strong><?= $completedCount; ?></strong><small>Completed</small></div>
                                <div class="queue-stat-mini total"><span><?= staff_icon('users'); ?></span><strong><?= $displayTotal; ?></strong><small>Total</small></div>
                            </div>
                            <div class="service-action">View Clinical Files →</div>
                        </a>
                    <?php endforeach; ?>
                </section>
            <?php else: ?>
                <?php
                $currentProgram = null;
                foreach ($programs as $p) {
                    if ($p['slug'] === $programFilter) {
                        $currentProgram = $p;
                        break;
                    }
                }
                $currentProgramTitle = $currentProgram['title'] ?? ucfirst($programFilter);
                ?>
                <!-- Service Detail Top Toolbar & Breadcrumb -->
                <div class="appt-detail-topbar">
                    <div class="appt-breadcrumb-trail">
                        <a href="?page=patients" class="appt-breadcrumb-item">
                            <?= staff_icon('patients'); ?>
                            <span>All Clinical Services</span>
                        </a>
                        <span class="appt-breadcrumb-sep">/</span>
                        <span class="appt-breadcrumb-active"><?= h($currentProgramTitle); ?></span>
                    </div>

                    <a class="appt-back-btn" href="?page=patients<?= $patientDateFilter !== '' ? '&patient_date=' . h($patientDateFilter) : ''; ?>">
                        <?= staff_icon('arrow-left'); ?>
                        <span>Back to Services</span>
                    </a>
                </div>

                <!-- Sleek Filter and Search Bar -->
                <section class="appt-filter-card">
                    <form method="get" class="appt-filter-form" id="patientFilterForm">
                        <input type="hidden" name="page" value="patients">
                        <?php if ($programFilter !== ''): ?>
                            <input type="hidden" name="program" value="<?= h($programFilter); ?>">
                        <?php endif; ?>

                        <div class="appt-search-field">
                            <span class="appt-search-icon"><?= staff_icon('search'); ?></span>
                            <input type="text" name="patient_search" value="<?= h($patientSearch); ?>" placeholder="Search by patient name, appointment code, or phone number..." maxlength="40">
                            <?php if ($patientSearch !== ''): ?>
                                <a href="?page=patients<?= $programFilter !== '' ? '&program=' . h($programFilter) : ''; ?><?= $patientDateFilter !== '' ? '&patient_date=' . h($patientDateFilter) : ''; ?>" class="appt-search-clear" title="Clear search">
                                    <?= staff_icon('x'); ?>
                                </a>
                            <?php endif; ?>
                        </div>

                        <div class="appt-filter-dropdowns">
                            <div class="appt-date-input-wrap">
                                <span class="appt-date-input-icon"><?= staff_icon('calendar'); ?></span>
                                <input type="date" name="patient_date" value="<?= h($patientDateFilter); ?>" class="appt-date-picker-input" title="Filter consultations by date">
                                <?php if ($patientDateFilter !== ''): ?>
                                    <a href="?page=patients<?= $programFilter !== '' ? '&program=' . h($programFilter) : ''; ?><?= $patientSearch !== '' ? '&patient_search=' . h($patientSearch) : ''; ?>" class="appt-date-clear-btn" title="Clear date filter">
                                        <?= staff_icon('x'); ?>
                                    </a>
                                <?php endif; ?>
                            </div>

                            <button type="submit" class="appt-filter-submit-btn">
                                <?= staff_icon('filter'); ?>
                                <span>Filter</span>
                            </button>

                            <?php if ($patientSearch !== '' || $patientDateFilter !== ''): ?>
                                <a href="?page=patients<?= $programFilter !== '' ? '&program=' . h($programFilter) : ''; ?>" class="appt-filter-reset-btn" title="Reset all filters">
                                    <?= staff_icon('history'); ?>
                                    <span>Reset</span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </section>

                <?php if ($patientSearch !== ''): ?>
                    <section class="patient-search-result-box" style="margin-bottom: 24px;">
                        <div class="panel-head">
                            <h2>Search Result</h2>
                        </div>
                        <?php if ($clinicalSearchResult === null): ?>
                            <div class="panel-card empty-state">No appointment found matching that ID or search term.</div>
                        <?php else: ?>
                            <?php $searchRecordComplete = appointment_has_completed_clinical_details($clinicalSearchResult); ?>
                            <div class="modern-patient-record-card <?= $searchRecordComplete ? 'is-completed' : 'is-ongoing'; ?>">
                                <div class="pat-card-left">
                                    <div class="pat-card-avatar"><?= strtoupper(substr((string) ($clinicalSearchResult['first_name'] ?? 'P'), 0, 1) . substr((string) ($clinicalSearchResult['last_name'] ?? 'U'), 0, 1)); ?></div>
                                    <div class="pat-card-info">
                                        <div class="pat-card-title-row">
                                            <h3><?= h(full_name($clinicalSearchResult)); ?></h3>
                                            <span class="appt-code-badge">#<?= h((string) $clinicalSearchResult['appointment_code']); ?></span>
                                        </div>
                                        <div class="pat-card-meta-row">
                                            <span class="pat-meta-pill service"><?= staff_icon('stethoscope'); ?> <?= h((string) $clinicalSearchResult['service_name']); ?></span>
                                            <span class="pat-meta-pill date"><?= staff_icon('calendar'); ?> <?= h(date('F j, Y', strtotime((string) $clinicalSearchResult['preferred_date']))); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="pat-card-right">
                                    <span class="status-pill <?= $searchRecordComplete ? 'status-confirmed' : 'status-pending'; ?>"><?= $searchRecordComplete ? '✓ Recorded' : 'Ongoing'; ?></span>
                                    <?php if ($searchRecordComplete): ?>
                                        <a class="view-medical-file-btn" href="?page=patients<?= $programFilter !== '' ? '&program=' . h($programFilter) : ''; ?><?= $patientDateFilter !== '' ? '&patient_date=' . h($patientDateFilter) : ''; ?>&appointment_view=<?= h((string) $clinicalSearchResult['appointment_code']); ?>" title="View detailed clinical record">
                                            <?= staff_icon('eye'); ?>
                                            <span>View Medical File</span>
                                        </a>
                                    <?php else: ?>
                                        <?php $isSearchResultServing = (string) ($clinicalSearchResult['status'] ?? '') === 'Serving'; ?>
                                        <?php if ($isSearchResultServing): ?>
                                            <a class="remarks-btn is-active" href="?page=patients<?= $programFilter !== '' ? '&program=' . h($programFilter) : ''; ?><?= $patientDateFilter !== '' ? '&patient_date=' . h($patientDateFilter) : ''; ?>&appointment_remarks=<?= h((string) $clinicalSearchResult['appointment_code']); ?>" title="Encode doctor remarks & clinical assessment">
                                                <?= staff_icon('edit'); ?>
                                                <span>Remarks</span>
                                            </a>
                                        <?php else: ?>
                                            <button type="button" class="remarks-btn is-disabled" disabled title="Clinical remarks can be encoded once patient consultation begins.">
                                                <?= staff_icon('edit'); ?>
                                                <span>Remarks</span>
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>

                <!-- Modern 3-KPI Stat Cards -->
                <section class="appt-metrics-grid">
                    <article class="appt-metric-card pending">
                        <div class="appt-metric-icon">
                            <?= staff_icon('edit'); ?>
                        </div>
                        <div class="appt-metric-content">
                            <span class="appt-metric-label">Ongoing / Awaiting Remarks</span>
                            <strong class="appt-metric-val"><?= number_format($patientsOngoingCount); ?></strong>
                        </div>
                    </article>

                    <article class="appt-metric-card total">
                        <div class="appt-metric-icon">
                            <?= staff_icon('check'); ?>
                        </div>
                        <div class="appt-metric-content">
                            <span class="appt-metric-label">Completed Medical Files</span>
                            <strong class="appt-metric-val"><?= number_format($patientsRecordedCount); ?></strong>
                        </div>
                    </article>

                    <article class="appt-metric-card cancelled">
                        <div class="appt-metric-icon">
                            <?= staff_icon('users'); ?>
                        </div>
                        <div class="appt-metric-content">
                            <span class="appt-metric-label">Total Station Records</span>
                            <strong class="appt-metric-val"><?= number_format($patientsTotalCount); ?></strong>
                        </div>
                    </article>
                </section>

                <!-- Section 1: Active & Ongoing Consultations -->
                <section class="patient-section-wrapper">
                    <div class="patient-section-header">
                        <div class="section-title-group">
                            <span class="sec-icon-pill pending"><?= staff_icon('pulse'); ?></span>
                            <div>
                                <h2>Active &amp; Ongoing Consultations</h2>
                                <p>Patients currently in station workflow awaiting clinical remarks &amp; doctor's notes.</p>
                            </div>
                        </div>
                        <span class="patient-section-counter"><?= count($patientRecentEntries); ?> ongoing</span>
                    </div>

                    <div class="patient-cards-stack">
                        <?php if ($patientRecentEntries === []): ?>
                            <div class="panel-card empty-state appt-empty-box">
                                <div class="appt-empty-icon"><?= staff_icon('check'); ?></div>
                                <h3>All ongoing consultations recorded</h3>
                                <p>There are no active serving consultations for this service under the current filter.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($patientRecentEntries as $appointment): ?>
                                <?php
                                $patInitials = strtoupper(substr((string) ($appointment['first_name'] ?? 'P'), 0, 1) . substr((string) ($appointment['last_name'] ?? 'U'), 0, 1));
                                $apptCode = (string) ($appointment['appointment_code'] ?? $appointment['reference_code'] ?? '');
                                $patHasPhoto = !empty($appointment['photo_path']);
                                ?>
                                <article class="modern-patient-record-card is-ongoing">
                                    <div class="pat-card-left">
                                        <div class="pat-card-avatar" style="<?= $patHasPhoto ? 'background:transparent;' : ''; ?>">
                                            <?php if ($patHasPhoto): ?>
                                                <img src="../Patients/<?= h((string) $appointment['photo_path']); ?>" alt="<?= h(full_name($appointment)); ?>" style="width:100%;height:100%;object-fit:cover;border-radius:12px;">
                                            <?php else: ?>
                                                <?= h($patInitials); ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="pat-card-info">
                                            <div class="pat-card-title-row">
                                                <h3><?= h(full_name($appointment)); ?></h3>
                                                <?php if ($apptCode !== ''): ?>
                                                    <span class="appt-code-badge">#<?= h($apptCode); ?></span>
                                                <?php endif; ?>
                                                <?php if ($patHasPhoto): ?>
                                                    <span class="photo-status-badge verified" style="position:static;transform:none;font-size:0.7rem;padding:2px 8px;">✓ Photo Verified</span>
                                                <?php else: ?>
                                                    <span class="photo-status-badge missing" style="position:static;transform:none;font-size:0.7rem;padding:2px 8px;">⚠️ Needs Photo</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="pat-card-meta-row">
                                                <span class="pat-meta-pill service"><?= staff_icon('stethoscope'); ?> <?= h((string) $appointment['service_name']); ?></span>
                                                <span class="pat-meta-pill date"><?= staff_icon('calendar'); ?> <?= h(date('M j, Y', strtotime((string) $appointment['preferred_date']))); ?></span>
                                                <?php if (!empty($appointment['contact_number'])): ?>
                                                    <span class="pat-meta-pill phone"><?= staff_icon('phone'); ?> <?= h((string) $appointment['contact_number']); ?></span>
                                                <?php endif; ?>
                                                <span class="pat-meta-pill demo"><?= h(age_label($appointment)); ?> • <?= h((string) ($appointment['gender'] ?? '')); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="pat-card-right">
                                        <span class="status-pill status-queue-serving">
                                            ⚡ Serving Now
                                        </span>
                                        <a class="remarks-btn is-active" href="?page=patients<?= $programFilter !== '' ? '&program=' . h($programFilter) : ''; ?><?= $patientDateFilter !== '' ? '&patient_date=' . h($patientDateFilter) : ''; ?>&appointment_remarks=<?= h($apptCode); ?>" title="Encode doctor remarks & clinical assessment">
                                            <?= staff_icon('edit'); ?>
                                            <span>Remarks</span>
                                        </a>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Section 2: Recorded Station Medical Records -->
                <section class="patient-section-wrapper" style="margin-top: 32px;">
                    <div class="patient-section-header">
                        <div class="section-title-group">
                            <span class="sec-icon-pill done"><?= staff_icon('stethoscope'); ?></span>
                            <div>
                                <h2>Recorded Station Medical History</h2>
                                <p>Completed patient consultations with saved vital signs and clinical assessment.</p>
                            </div>
                        </div>
                        <span class="patient-section-counter"><?= count($patientRecordEntries); ?> recorded</span>
                    </div>

                    <div class="patient-cards-stack">
                        <?php if ($patientRecordEntries === []): ?>
                            <div class="panel-card empty-state appt-empty-box">
                                <div class="appt-empty-icon"><?= staff_icon('patients'); ?></div>
                                <h3>No completed patient records</h3>
                                <p>No completed station clinical records found for this service yet.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($patientRecordEntries as $record): ?>
                                <?php
                                $patInitials = strtoupper(substr((string) ($record['first_name'] ?? 'P'), 0, 1) . substr((string) ($record['last_name'] ?? 'U'), 0, 1));
                                $apptCode = (string) ($record['appointment_code'] ?? $record['reference_code'] ?? '');
                                $hasTemp = !empty($record['body_temperature']);
                                $hasBP = !empty($record['blood_pressure']);
                                $hasPR = !empty($record['pulse_rate']);
                                ?>
                                <article class="modern-patient-record-card is-completed">
                                    <div class="pat-card-left">
                                        <div class="pat-card-avatar done"><?= h($patInitials); ?></div>
                                        <div class="pat-card-info">
                                            <div class="pat-card-title-row">
                                                <h3><?= h(full_name($record)); ?></h3>
                                                <?php if ($apptCode !== ''): ?>
                                                    <span class="appt-code-badge">#<?= h($apptCode); ?></span>
                                                <?php endif; ?>
                                                <?php if (!empty($record['follow_up_date'])): ?>
                                                    <span class="follow-up-badge-pill" title="Follow-up scheduled for <?= h(date('M j, Y', strtotime((string) $record['follow_up_date']))); ?>">
                                                        <?= staff_icon('calendar'); ?> Follow-up: <?= h(date('M j', strtotime((string) $record['follow_up_date']))); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="pat-card-meta-row">
                                                <span class="pat-meta-pill service"><?= staff_icon('stethoscope'); ?> <?= h((string) $record['service_name']); ?></span>
                                                <span class="pat-meta-pill date"><?= staff_icon('calendar'); ?> <?= h(date('M j, Y', strtotime((string) $record['preferred_date']))); ?></span>
                                                <span class="pat-meta-pill demo"><?= h(age_label($record)); ?> • <?= h((string) ($record['gender'] ?? '')); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="pat-card-right">
                                        <span class="status-pill status-confirmed">✓ Recorded</span>
                                        <div class="pat-card-actions-group">
                                            <a class="view-medical-file-btn" href="?page=patients<?= $programFilter !== '' ? '&program=' . h($programFilter) : ''; ?><?= $patientDateFilter !== '' ? '&patient_date=' . h($patientDateFilter) : ''; ?>&appointment_view=<?= h($apptCode); ?>" title="View detailed clinical record">
                                                <?= staff_icon('eye'); ?>
                                                <span>View Medical File</span>
                                            </a>
                                            <a class="set-followup-btn" href="?page=patients<?= $programFilter !== '' ? '&program=' . h($programFilter) : ''; ?><?= $patientDateFilter !== '' ? '&patient_date=' . h($patientDateFilter) : ''; ?>&appointment_followup=<?= h($apptCode); ?>" title="Set or update follow-up consultation date">
                                                <?= staff_icon('calendar'); ?>
                                                <span><?= !empty($record['follow_up_date']) ? 'Edit Follow-up' : 'Set Follow-up'; ?></span>
                                            </a>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Clinical Remarks & Doctor's Notes Modal -->
            <?php if ($selectedRemarksAppointment !== null): ?>
                <?php
                $isFinishedRecord = ((string) ($selectedRemarksAppointment['status'] ?? '') === 'Completed') || ($selectedRecordCode !== '');
                if ($page === 'reports') {
                    $remarksReturnUrl = '?page=reports' . ($reportWeek !== '' ? '&week=' . urlencode($reportWeek) : '');
                } elseif ($page === 'image-capture') {
                    $remarksReturnUrl = '?page=image-capture' . ($programFilter !== '' ? '&program=' . urlencode($programFilter) : '') . ($captureFilter !== '' ? '&capture_filter=' . urlencode($captureFilter) : '');
                } else {
                    $remarksReturnUrl = '?page=patients' . ($programFilter !== '' ? '&program=' . urlencode($programFilter) : '') . ($patientDateFilter !== '' ? '&patient_date=' . urlencode($patientDateFilter) : '');
                }
                ?>
                <section class="account-modal-backdrop" id="clinicalModalBackdrop">
                    <div class="account-modal-card clinical-dialog-card" role="dialog" aria-modal="true">
                        <div class="account-modal-header">
                            <div class="account-modal-title-group">
                                <span class="account-modal-icon"><?= staff_icon('stethoscope'); ?></span>
                                <div>
                                    <h2><?= $isFinishedRecord ? 'Clinical Record &amp; Doctor\'s Notes' : 'Clinical Remarks &amp; Doctor\'s Notes'; ?></h2>
                                    <p>Appointment #<?= h((string) $selectedRemarksAppointment['appointment_code']); ?> • <?= h((string) $selectedRemarksAppointment['service_name']); ?><?= $isFinishedRecord ? ' (Completed Record)' : ''; ?></p>
                                </div>
                            </div>
                            <a class="account-modal-close" href="<?= h($remarksReturnUrl); ?>" onclick="return window.closeClinicalModal(event, '<?= h($remarksReturnUrl); ?>');" aria-label="Close modal">×</a>
                        </div>

                        <?php if (!$isFinishedRecord): ?>
                            <form method="post" class="account-settings-form">
                                <input type="hidden" name="action" value="save_clinical_remarks">
                                <input type="hidden" name="return_url" value="<?= h($remarksReturnUrl); ?>">
                                <input type="hidden" name="csrf_token" value="<?= h($csrf); ?>">
                                <input type="hidden" name="appointment_id" value="<?= h((string) $selectedRemarksAppointment['id']); ?>">
                        <?php else: ?>
                            <div class="account-settings-form">
                        <?php endif; ?>

                            <div class="account-modal-body">
                                <!-- Compact Patient Identity Header Strip -->
                                <div class="patient-modal-banner">
                                    <div class="patient-modal-avatar-wrap">
                                        <?php if ((string) ($selectedRemarksAppointment['photo_path'] ?? '') !== ''): ?>
                                            <img src="../Patients/<?= h((string) $selectedRemarksAppointment['photo_path']); ?>" alt="<?= h(full_name($selectedRemarksAppointment)); ?> photo" class="patient-modal-thumb">
                                        <?php else: ?>
                                            <div class="patient-modal-initials">
                                                <?= strtoupper(substr((string) ($selectedRemarksAppointment['first_name'] ?? 'P'), 0, 1) . substr((string) ($selectedRemarksAppointment['last_name'] ?? 'U'), 0, 1)); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="patient-modal-details">
                                        <div class="patient-modal-title-row">
                                            <h3><?= h(full_name($selectedRemarksAppointment)); ?></h3>
                                            <span class="appt-code-badge">#<?= h((string) $selectedRemarksAppointment['appointment_code']); ?></span>
                                            <?php if ((string) ($selectedRemarksAppointment['photo_path'] ?? '') !== ''): ?>
                                                <span class="queue-id-verified"><?= staff_icon('check'); ?> Verified ID</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="patient-modal-meta-row">
                                            <span class="pat-meta-pill service"><?= staff_icon('stethoscope'); ?> <?= h((string) $selectedRemarksAppointment['service_name']); ?></span>
                                            <span class="pat-meta-pill date"><?= staff_icon('calendar'); ?> <?= h(date('F j, Y', strtotime((string) $selectedRemarksAppointment['preferred_date']))); ?></span>
                                            <span class="pat-meta-pill time"><?= staff_icon('clock'); ?> <?= h((string) ($selectedRemarksAppointment['preferred_time'] ?? 'Regular Hours')); ?></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Patient Demographics Overview -->
                                <div class="clinical-demographics-card">
                                    <div class="demo-item">
                                        <label>Age &amp; Gender</label>
                                        <span><?= h(age_label($selectedRemarksAppointment)); ?> • <?= h((string) ($selectedRemarksAppointment['gender'] ?? 'Not specified')); ?></span>
                                    </div>
                                    <div class="demo-item">
                                        <label>Date of Birth</label>
                                        <span><?= !empty($selectedRemarksAppointment['birth_date']) ? h(date('F j, Y', strtotime((string) $selectedRemarksAppointment['birth_date']))) : 'N/A'; ?></span>
                                    </div>
                                    <div class="demo-item">
                                        <label>Contact Number</label>
                                        <span class="contact-highlight"><?= staff_icon('phone'); ?> <?= h((string) ($selectedRemarksAppointment['contact_number'] ?: 'None provided')); ?></span>
                                    </div>
                                    <div class="demo-item full-width">
                                        <label>Home Address</label>
                                        <span><?= h((string) ($selectedRemarksAppointment['complete_address'] ?: 'Barangay ' . $station['barangay'] . ', Bacolod City')); ?></span>
                                    </div>
                                    <?php if (!empty($selectedRemarksAppointment['notes'])): ?>
                                        <div class="demo-item full-width patient-complaint-box">
                                            <label>Patient Chief Complaint / Booking Notes</label>
                                            <span><?= h((string) $selectedRemarksAppointment['notes']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="account-section-divider">
                                    <?= staff_icon('pulse'); ?>
                                    <span>Vital Signs (Recorded)</span>
                                </div>

                                <!-- Non-Clickable Vital Signs Display -->
                                <div class="vitals-display-grid readonly-vitals-grid">
                                    <div class="vital-display-box is-readonly">
                                        <div class="vital-box-header">
                                            <span class="vital-box-icon"><?= staff_icon('pulse'); ?></span>
                                            <span class="vital-box-label">Body Temp</span>
                                        </div>
                                        <strong class="vital-box-val"><?= h((string) (($selectedRemarksAppointment['body_temperature'] ?? '') !== '' ? $selectedRemarksAppointment['body_temperature'] : 'Not recorded')); ?></strong>
                                    </div>
                                    <div class="vital-display-box is-readonly">
                                        <div class="vital-box-header">
                                            <span class="vital-box-icon"><?= staff_icon('heart'); ?></span>
                                            <span class="vital-box-label">Pulse Rate (PR)</span>
                                        </div>
                                        <strong class="vital-box-val"><?= h((string) (($selectedRemarksAppointment['pulse_rate'] ?? '') !== '' ? $selectedRemarksAppointment['pulse_rate'] : 'Not recorded')); ?></strong>
                                    </div>
                                    <div class="vital-display-box is-readonly">
                                        <div class="vital-box-header">
                                            <span class="vital-box-icon"><?= staff_icon('sparkle'); ?></span>
                                            <span class="vital-box-label">Respiration (RR)</span>
                                        </div>
                                        <strong class="vital-box-val"><?= h((string) (($selectedRemarksAppointment['respiration_rate'] ?? '') !== '' ? $selectedRemarksAppointment['respiration_rate'] : 'Not recorded')); ?></strong>
                                    </div>
                                    <div class="vital-display-box is-readonly">
                                        <div class="vital-box-header">
                                            <span class="vital-box-icon"><?= staff_icon('stethoscope'); ?></span>
                                            <span class="vital-box-label">Blood Pressure</span>
                                        </div>
                                        <strong class="vital-box-val"><?= h((string) (($selectedRemarksAppointment['blood_pressure'] ?? '') !== '' ? $selectedRemarksAppointment['blood_pressure'] : 'Not recorded')); ?></strong>
                                    </div>
                                </div>

                                <div class="account-section-divider">
                                    <?= staff_icon('edit'); ?>
                                    <span>Doctor's Clinical Notes &amp; Findings</span>
                                </div>

                                <div class="form-group-item">
                                    <label for="doc_notes" class="form-field-label">
                                        <span>Clinical Assessment / Prescription / Findings</span>
                                        <?php if (!$isFinishedRecord): ?>
                                            <span class="required">*</span>
                                        <?php endif; ?>
                                    </label>
                                    <?php if (!$isFinishedRecord): ?>
                                        <textarea id="doc_notes" name="doctor_notes" rows="4" placeholder="Enter clinical diagnosis, doctor recommendations, or prescription instructions..." required class="form-input-field" style="height:auto;padding:12px 16px;resize:vertical;"><?= h((string) ($selectedRemarksAppointment['doctor_notes'] ?? '')); ?></textarea>
                                    <?php else: ?>
                                        <textarea id="doc_notes" name="doctor_notes" rows="4" readonly class="form-input-field" style="height:auto;padding:12px 16px;resize:none;background:#f8fafc;color:#334155;cursor:default;"><?= h((string) (($selectedRemarksAppointment['doctor_notes'] ?? '') !== '' ? $selectedRemarksAppointment['doctor_notes'] : 'No clinical notes recorded.')); ?></textarea>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="account-modal-footer" style="<?= $isFinishedRecord ? 'justify-content:flex-end;' : ''; ?>">
                                <?php if (!$isFinishedRecord): ?>
                                    <a class="clinical-modal-cancel-btn" href="<?= h($remarksReturnUrl); ?>" onclick="return window.closeClinicalModal(event, '<?= h($remarksReturnUrl); ?>');">
                                        <?= staff_icon('x'); ?>
                                        <span>Cancel</span>
                                    </a>
                                    <button type="submit" class="clinical-modal-save-btn">
                                        <?= staff_icon('check'); ?>
                                        <span>Save Clinical Remarks</span>
                                    </button>
                                <?php else: ?>
                                    <a class="primary-btn blue-btn" href="<?= h($remarksReturnUrl); ?>" onclick="return window.closeClinicalModal(event, '<?= h($remarksReturnUrl); ?>');" style="min-width:130px;justify-content:center;">
                                        <?= staff_icon('check'); ?>
                                        <span>Close</span>
                                    </a>
                                <?php endif; ?>
                            </div>

                        <?php if (!$isFinishedRecord): ?>
                            </form>
                        <?php else: ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Clinical View Modal -->
            <?php if ($selectedViewAppointment !== null): ?>
                <?php $hasFollowUp = !empty($selectedViewAppointment['follow_up_date']); ?>
                <section class="account-modal-backdrop" id="viewClinicalModalBackdrop">
                    <div class="account-modal-card clinical-dialog-card" role="dialog" aria-modal="true">
                        <div class="account-modal-header">
                            <div class="account-modal-title-group">
                                <span class="account-modal-icon"><?= staff_icon('shield'); ?></span>
                                <div>
                                    <h2>Station Medical File</h2>
                                    <p>Official Health Record • #<?= h((string) $selectedViewAppointment['appointment_code']); ?></p>
                                </div>
                            </div>
                            <a class="account-modal-close" href="?page=patients<?= $programFilter !== '' ? '&program=' . h($programFilter) : ''; ?><?= $patientDateFilter !== '' ? '&patient_date=' . h($patientDateFilter) : ''; ?>" onclick="return window.closeClinicalModal(event, '?page=patients<?= $programFilter !== '' ? '&program=' . h($programFilter) : ''; ?><?= $patientDateFilter !== '' ? '&patient_date=' . h($patientDateFilter) : ''; ?>');" aria-label="Close modal">×</a>
                        </div>

                        <div class="account-modal-body">
                            <!-- Compact Patient Identity Header Strip -->
                            <div class="patient-modal-banner">
                                <div class="patient-modal-avatar-wrap">
                                    <?php if ((string) ($selectedViewAppointment['photo_path'] ?? '') !== ''): ?>
                                        <img src="../Patients/<?= h((string) $selectedViewAppointment['photo_path']); ?>" alt="<?= h(full_name($selectedViewAppointment)); ?> photo" class="patient-modal-thumb">
                                    <?php else: ?>
                                        <div class="patient-modal-initials">
                                            <?= strtoupper(substr((string) ($selectedViewAppointment['first_name'] ?? 'P'), 0, 1) . substr((string) ($selectedViewAppointment['last_name'] ?? 'U'), 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="patient-modal-details">
                                    <div class="patient-modal-title-row">
                                        <h3><?= h(full_name($selectedViewAppointment)); ?></h3>
                                        <span class="appt-code-badge">#<?= h((string) $selectedViewAppointment['appointment_code']); ?></span>
                                        <?php if ((string) ($selectedViewAppointment['photo_path'] ?? '') !== ''): ?>
                                            <span class="queue-id-verified"><?= staff_icon('check'); ?> Verified ID</span>
                                        <?php else: ?>
                                            <a href="?page=image-capture&appointment=<?= h((string) $selectedViewAppointment['id']); ?><?= !empty($selectedViewAppointment['service_slug']) ? '&program=' . urlencode((string) $selectedViewAppointment['service_slug']) : ($programFilter !== '' ? '&program=' . urlencode($programFilter) : ''); ?>" class="queue-id-needed" title="Capture proof portrait at front desk"><?= staff_icon('camera'); ?> <span>Take Proof Photo</span></a>
                                        <?php endif; ?>
                                        <?php if ($hasFollowUp): ?>
                                            <span class="follow-up-badge-pill"><?= staff_icon('calendar'); ?> Follow-up: <?= h(date('M j, Y', strtotime((string) $selectedViewAppointment['follow_up_date']))); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="patient-modal-meta-row">
                                        <span class="pat-meta-pill service"><?= staff_icon('stethoscope'); ?> <?= h((string) $selectedViewAppointment['service_name']); ?></span>
                                        <span class="pat-meta-pill date"><?= staff_icon('calendar'); ?> <?= h(date('F j, Y', strtotime((string) $selectedViewAppointment['preferred_date']))); ?></span>
                                        <span class="pat-meta-pill time"><?= staff_icon('clock'); ?> <?= h((string) ($selectedViewAppointment['preferred_time'] ?? 'Regular Hours')); ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Scheduled Follow-up Alert Card (if already scheduled) -->
                            <?php if ($hasFollowUp): ?>
                                <div class="follow-up-status-card">
                                    <div class="follow-up-status-header">
                                        <span class="follow-up-status-icon"><?= staff_icon('calendar'); ?></span>
                                        <div>
                                            <strong>Upcoming Follow-up Check-up Scheduled</strong>
                                            <span>The patient has been notified on their portal</span>
                                        </div>
                                    </div>
                                    <div class="follow-up-details-grid">
                                        <div>
                                            <small>Follow-up Date</small>
                                            <strong><?= date('l, F j, Y', strtotime((string) $selectedViewAppointment['follow_up_date'])); ?></strong>
                                        </div>
                                        <div>
                                            <small>Preferred Session / Window</small>
                                            <strong><?= h((string) ($selectedViewAppointment['follow_up_time'] ?: 'Morning (8:00 AM - 12:00 PM)')); ?></strong>
                                        </div>
                                        <?php if (!empty($selectedViewAppointment['follow_up_notes'])): ?>
                                            <div class="full-span">
                                                <small>Reason &amp; Clinical Instructions</small>
                                                <p><?= h((string) $selectedViewAppointment['follow_up_notes']); ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Patient Info & Demographics Card -->
                            <div class="clinical-demographics-card">
                                <div class="demo-item">
                                    <label>Age &amp; Gender</label>
                                    <span><?= h(age_label($selectedViewAppointment)); ?> • <?= h((string) ($selectedViewAppointment['gender'] ?? 'Not specified')); ?></span>
                                </div>
                                <div class="demo-item">
                                    <label>Date of Birth</label>
                                    <span><?= !empty($selectedViewAppointment['birth_date']) ? h(date('F j, Y', strtotime((string) $selectedViewAppointment['birth_date']))) : 'N/A'; ?></span>
                                </div>
                                <div class="demo-item">
                                    <label>Contact Number</label>
                                    <span class="contact-highlight"><?= staff_icon('phone'); ?> <?= h((string) ($selectedViewAppointment['contact_number'] ?: 'None provided')); ?></span>
                                </div>
                                <div class="demo-item full-width">
                                    <label>Home Address</label>
                                    <span><?= h((string) ($selectedViewAppointment['complete_address'] ?: 'Barangay ' . $station['barangay'] . ', Bacolod City')); ?></span>
                                </div>
                                <?php if (!empty($selectedViewAppointment['notes'])): ?>
                                    <div class="demo-item full-width patient-complaint-box">
                                        <label>Patient Chief Complaint / Booking Notes</label>
                                        <span><?= h((string) $selectedViewAppointment['notes']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="account-section-divider">
                                <?= staff_icon('pulse'); ?>
                                <span>Recorded Vital Signs</span>
                            </div>

                            <div class="vitals-display-grid">
                                <div class="vital-display-box">
                                    <div class="vital-box-header">
                                        <span class="vital-box-icon"><?= staff_icon('pulse'); ?></span>
                                        <span class="vital-box-label">Body Temp</span>
                                    </div>
                                    <strong class="vital-box-val"><?= h((string) (($selectedViewAppointment['body_temperature'] ?? '') !== '' ? $selectedViewAppointment['body_temperature'] : 'N/A')); ?></strong>
                                </div>
                                <div class="vital-display-box">
                                    <div class="vital-box-header">
                                        <span class="vital-box-icon"><?= staff_icon('heart'); ?></span>
                                        <span class="vital-box-label">Pulse Rate</span>
                                    </div>
                                    <strong class="vital-box-val"><?= h((string) (($selectedViewAppointment['pulse_rate'] ?? '') !== '' ? $selectedViewAppointment['pulse_rate'] : 'N/A')); ?></strong>
                                </div>
                                <div class="vital-display-box">
                                    <div class="vital-box-header">
                                        <span class="vital-box-icon"><?= staff_icon('sparkle'); ?></span>
                                        <span class="vital-box-label">Respiration</span>
                                    </div>
                                    <strong class="vital-box-val"><?= h((string) (($selectedViewAppointment['respiration_rate'] ?? '') !== '' ? $selectedViewAppointment['respiration_rate'] : 'N/A')); ?></strong>
                                </div>
                                <div class="vital-display-box">
                                    <div class="vital-box-header">
                                        <span class="vital-box-icon"><?= staff_icon('stethoscope'); ?></span>
                                        <span class="vital-box-label">Blood Pressure</span>
                                    </div>
                                    <strong class="vital-box-val"><?= h((string) (($selectedViewAppointment['blood_pressure'] ?? '') !== '' ? $selectedViewAppointment['blood_pressure'] : 'N/A')); ?></strong>
                                </div>
                            </div>

                            <div class="account-section-divider">
                                <?= staff_icon('edit'); ?>
                                <span>Doctor's Assessment &amp; Clinical Notes</span>
                            </div>

                            <div class="doctor-notes-card">
                                <h4><?= staff_icon('stethoscope'); ?> Clinical Assessment &amp; Prescription Instructions</h4>
                                <p><?= nl2br(h((string) (($selectedViewAppointment['doctor_notes'] ?? '') !== '' ? $selectedViewAppointment['doctor_notes'] : 'No clinical notes recorded.'))); ?></p>
                            </div>

                        </div>

                        <div class="account-modal-footer">
                            <a class="primary-btn blue-btn" href="?page=patients<?= $programFilter !== '' ? '&program=' . h($programFilter) : ''; ?><?= $patientDateFilter !== '' ? '&patient_date=' . h($patientDateFilter) : ''; ?>&appointment_followup=<?= h((string) $selectedViewAppointment['appointment_code']); ?>" title="Open dedicated modal to schedule follow-up check-up">
                                <?= staff_icon('calendar'); ?>
                                <span>Schedule Follow-up</span>
                            </a>
                            <a class="ghost-btn" href="?page=patients<?= $programFilter !== '' ? '&program=' . h($programFilter) : ''; ?><?= $patientDateFilter !== '' ? '&patient_date=' . h($patientDateFilter) : ''; ?>" onclick="return window.closeClinicalModal(event, '?page=patients<?= $programFilter !== '' ? '&program=' . h($programFilter) : ''; ?><?= $patientDateFilter !== '' ? '&patient_date=' . h($patientDateFilter) : ''; ?>');">
                                <span>Close Record</span>
                            </a>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Dedicated Follow-up Scheduling Modal Dialog -->
            <?php if ($selectedFollowUpAppointment !== null): ?>
                <?php 
                $fuApptHasFollowUp = !empty($selectedFollowUpAppointment['follow_up_date']); 
                $fuReturnUrl = '?page=patients' . ($programFilter !== '' ? '&program=' . urlencode($programFilter) : '') . ($patientDateFilter !== '' ? '&patient_date=' . urlencode($patientDateFilter) : '');
                ?>
                <section class="account-modal-backdrop" id="followUpModalBackdrop">
                    <div class="account-modal-card clinical-dialog-card followup-dialog-card" role="dialog" aria-modal="true">
                        <div class="account-modal-header">
                            <div class="account-modal-title-group">
                                <span class="account-modal-icon followup-icon"><?= staff_icon('calendar'); ?></span>
                                <div>
                                    <h2><?= $fuApptHasFollowUp ? 'Update Follow-up Check-up' : 'Schedule Follow-up Check-up'; ?></h2>
                                    <p>Appointment #<?= h((string) $selectedFollowUpAppointment['appointment_code']); ?> • <?= h(full_name($selectedFollowUpAppointment)); ?></p>
                                </div>
                            </div>
                            <a class="account-modal-close" href="<?= h($fuReturnUrl); ?>" onclick="return window.closeClinicalModal(event, '<?= h($fuReturnUrl); ?>');" aria-label="Close modal">×</a>
                        </div>

                        <form method="post" action="?action=schedule_follow_up" class="account-settings-form">
                            <input type="hidden" name="action" value="schedule_follow_up">
                            <input type="hidden" name="return_url" value="<?= h($fuReturnUrl); ?>">
                            <input type="hidden" name="csrf_token" value="<?= h($csrf); ?>">
                            <input type="hidden" name="appointment_id" value="<?= h((string) $selectedFollowUpAppointment['id']); ?>">

                            <div class="account-modal-body">
                                <!-- Patient Banner -->
                                <div class="patient-modal-banner">
                                    <div class="patient-modal-avatar-wrap">
                                        <?php if ((string) ($selectedFollowUpAppointment['photo_path'] ?? '') !== ''): ?>
                                            <img src="../Patients/<?= h((string) $selectedFollowUpAppointment['photo_path']); ?>" alt="<?= h(full_name($selectedFollowUpAppointment)); ?> photo" class="patient-modal-thumb">
                                        <?php else: ?>
                                            <div class="patient-modal-initials">
                                                <?= strtoupper(substr((string) ($selectedFollowUpAppointment['first_name'] ?? 'P'), 0, 1) . substr((string) ($selectedFollowUpAppointment['last_name'] ?? 'U'), 0, 1)); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="patient-modal-details">
                                        <div class="patient-modal-title-row">
                                            <h3><?= h(full_name($selectedFollowUpAppointment)); ?></h3>
                                            <span class="appt-code-badge">#<?= h((string) $selectedFollowUpAppointment['appointment_code']); ?></span>
                                            <?php if ($fuApptHasFollowUp): ?>
                                                <span class="follow-up-badge-pill"><?= staff_icon('calendar'); ?> Current: <?= h(date('M j, Y', strtotime((string) $selectedFollowUpAppointment['follow_up_date']))); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="patient-modal-meta-row">
                                            <span class="pat-meta-pill service"><?= staff_icon('stethoscope'); ?> <?= h((string) $selectedFollowUpAppointment['service_name']); ?></span>
                                            <span class="pat-meta-pill demo"><?= h(age_label($selectedFollowUpAppointment)); ?> • <?= h((string) ($selectedFollowUpAppointment['gender'] ?? '')); ?></span>
                                            <span class="pat-meta-pill contact"><?= staff_icon('phone'); ?> <?= h((string) ($selectedFollowUpAppointment['contact_number'] ?: 'No phone')); ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="account-section-divider">
                                    <?= staff_icon('calendar'); ?>
                                    <span>Follow-up Consultation Date &amp; Time</span>
                                </div>

                                <!-- Quick 1-Click Presets -->
                                <div class="followup-presets-row">
                                    <span class="preset-label">Quick Presets:</span>
                                    <button type="button" class="preset-btn" data-days="3">+3 Days</button>
                                    <button type="button" class="preset-btn" data-days="7">+1 Week</button>
                                    <button type="button" class="preset-btn" data-days="14">+2 Weeks</button>
                                    <button type="button" class="preset-btn" data-days="30">+1 Month</button>
                                </div>

                                <div class="form-group-item">
                                    <label for="modal_follow_up_date" class="form-field-label">
                                        <span>Follow-up Consultation Date</span>
                                        <span class="required">*</span>
                                    </label>
                                    <input type="date" id="modal_follow_up_date" name="follow_up_date" value="<?= h((string) ($selectedFollowUpAppointment['follow_up_date'] ?? date('Y-m-d', strtotime('+7 days')))); ?>" min="<?= date('Y-m-d'); ?>" required class="form-input-field">
                                </div>

                                <div class="form-group-item" style="margin-top:10px;">
                                    <label for="modal_follow_up_time" class="form-field-label">
                                        <span>Follow-up Preferred Time</span>
                                    </label>
                                    <input type="text" id="modal_follow_up_time" name="follow_up_time" value="<?= h((string) ($selectedFollowUpAppointment['follow_up_time'] ?? '10:00')); ?>" placeholder="e.g. 10:00 AM" class="form-input-field">
                                </div>

                                <div class="account-section-divider">
                                    <?= staff_icon('edit'); ?>
                                    <span>Reason for Follow-up &amp; Instructions</span>
                                </div>

                                <div class="form-group-item">
                                    <label for="modal_follow_up_notes" class="form-field-label">
                                        <span>Clinical Instructions / Purpose</span>
                                        <span class="required">*</span>
                                    </label>
                                    <textarea id="modal_follow_up_notes" name="follow_up_notes" rows="3" placeholder="e.g. Follow-up blood pressure check, review lab test results, and adjust medication dosage..." required class="form-input-field" style="height:auto;padding:12px 16px;resize:vertical;"><?= h((string) ($selectedFollowUpAppointment['follow_up_notes'] ?? 'Follow-up clinical consultation and assessment.')); ?></textarea>
                                </div>

                                <div class="followup-notify-notice">
                                    <span class="notice-icon"><?= staff_icon('sparkle'); ?></span>
                                    <div>
                                        <strong>Instant Patient Dashboard Notification</strong>
                                        <p>Saving this follow-up will immediately notify the patient on their dashboard and notification center with the date and instructions.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="account-modal-footer followup-modal-footer">
                                <button type="button" class="clinical-modal-cancel-btn followup-cancel-btn" onclick="return window.closeClinicalModal(event, '<?= h($fuReturnUrl); ?>');">
                                    <?= staff_icon('x'); ?>
                                    <span>Cancel</span>
                                </button>
                                <button type="submit" class="clinical-modal-save-btn followup-schedule-btn">
                                    <?= staff_icon('calendar'); ?>
                                    <span><?= $fuApptHasFollowUp ? 'Update &amp; Notify Patient' : 'Schedule &amp; Notify Patient'; ?></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </section>
            <?php endif; ?>
        <?php elseif ($page === 'image-capture'): ?>
            <?php 
            $captureAppointment = $photoAppointment; 
            $allCaptureCandidates = $allStationAppointments;

            // If a service program is selected, filter candidate appointments strictly to that service
            if ($programFilter !== '') {
                $allCaptureCandidates = array_values(array_filter(
                    $allCaptureCandidates,
                    static fn(array $item): bool => (string) ($item['service_slug'] ?? '') === $programFilter
                ));
            }

            if ($patientSearch !== '') {
                $st = strtolower($patientSearch);
                $allCaptureCandidates = array_values(array_filter(
                    $allCaptureCandidates,
                    static function(array $item) use ($st): bool {
                        $name = strtolower(full_name($item));
                        $code = strtolower((string) ($item['appointment_code'] ?? $item['reference_code'] ?? ''));
                        $phone = strtolower((string) ($item['contact_number'] ?? ''));
                        $svc = strtolower((string) ($item['service_name'] ?? ''));
                        return str_contains($name, $st) || str_contains($code, $st) || str_contains($phone, $st) || str_contains($svc, $st);
                    }
                ));
            }

            $todayDate = date('Y-m-d');
            $photosVerifiedTodayAppointments = array_values(array_filter(
                $allCaptureCandidates,
                static fn(array $item): bool => !empty($item['photo_path']) 
                    && (string) ($item['status'] ?? '') === 'Completed' 
                    && (string) ($item['preferred_date'] ?? '') === $todayDate
            ));
            $photosVerifiedTodayTotal = count($photosVerifiedTodayAppointments);

            if ($captureFilter === 'verified') {
                $initialCandidates = $photosVerifiedTodayAppointments;
            } elseif ($captureFilter === 'ongoing') {
                $initialCandidates = array_values(array_filter($allCaptureCandidates, static fn(array $item): bool => in_array((string) ($item['status'] ?? ''), ['Serving', 'Confirmed'], true)));
            } else {
                $captureFilter = 'needs_photo';
                $initialCandidates = array_values(array_filter($allCaptureCandidates, static fn(array $item): bool => empty($item['photo_path'])));
            }
            $displayCandidates = $initialCandidates;
            $ongoingAwaitingPhoto = array_values(array_filter($allCaptureCandidates, static fn(array $item): bool => empty($item['photo_path'])));
            $activeFrontDeskQueue = array_values(array_filter($allCaptureCandidates, static fn(array $item): bool => in_array((string) ($item['status'] ?? ''), ['Serving', 'Confirmed'], true)));
            $photosVerifiedTotal = count(array_filter($allCaptureCandidates, static fn(array $item): bool => !empty($item['photo_path'])));
            ?>
            <section class="image-capture-container">
                <!-- Page Header with Search -->
                <div class="capture-page-header">
                    <div>
                        <h1>Patient Portrait &amp; Identity Verification</h1>
                        <p>Capture live webcam portraits or upload photos for ongoing patient visits to verify attendance and attach to official medical records.</p>
                    </div>
                    <form method="get" class="search-form-header">
                        <input type="hidden" name="page" value="image-capture">
                        <?php if ($programFilter !== ''): ?>
                            <input type="hidden" name="program" value="<?= h($programFilter); ?>">
                        <?php endif; ?>
                        <?php if ($captureFilter !== ''): ?>
                            <input type="hidden" name="capture_filter" value="<?= h($captureFilter); ?>">
                        <?php endif; ?>
                        <div class="header-search-bar">
                            <span class="search-icon"><?= staff_icon('search'); ?></span>
                            <input type="text" name="patient_search" value="<?= h($patientSearch); ?>" placeholder="Search name, ID #, or contact..." maxlength="40">
                            <?php if ($patientSearch !== ''): ?>
                                <a href="?page=image-capture<?= $programFilter !== '' ? '&program=' . h($programFilter) : ''; ?><?= $captureFilter !== '' ? '&capture_filter=' . h($captureFilter) : ''; ?>" class="clear-search-btn" title="Clear Search">×</a>
                            <?php endif; ?>
                            <button type="submit" class="search-submit-btn">Search</button>
                        </div>
                    </form>
                </div>

                <?php if ($programFilter !== ''): ?>
                    <?php
                    $currentProgram = null;
                    foreach ($programs as $p) {
                        if ($p['slug'] === $programFilter) {
                            $currentProgram = $p;
                            break;
                        }
                    }
                    $currentProgramTitle = $currentProgram['title'] ?? ucfirst($programFilter);
                    ?>
                    <div class="appt-detail-topbar" style="margin-bottom: 22px;">
                        <div class="appt-breadcrumb-trail">
                            <a href="?page=image-capture<?= $captureFilter !== '' ? '&capture_filter=' . h($captureFilter) : ''; ?>" class="appt-breadcrumb-item">
                                <?= staff_icon('camera'); ?>
                                <span>All Station Services</span>
                            </a>
                            <span class="appt-breadcrumb-sep">/</span>
                            <span class="appt-breadcrumb-active"><?= h($currentProgramTitle); ?></span>
                        </div>

                        <a class="appt-back-btn" href="?page=image-capture<?= $captureFilter !== '' ? '&capture_filter=' . h($captureFilter) : ''; ?>">
                            <?= staff_icon('arrow-left'); ?>
                            <span>View All Services</span>
                        </a>
                    </div>
                <?php endif; ?>

                <!-- KPI Metric Summary Row -->
                <div class="capture-kpi-grid">
                    <div class="capture-kpi-card">
                        <div class="capture-kpi-icon blue"><?= staff_icon('users'); ?></div>
                        <div class="capture-kpi-info">
                            <strong><?= count($activeFrontDeskQueue); ?> Patients</strong>
                            <span>Front Desk Active Queue</span>
                        </div>
                    </div>
                    <div class="capture-kpi-card <?= count($ongoingAwaitingPhoto) > 0 ? 'urgent' : ''; ?>">
                        <div class="capture-kpi-icon amber"><?= staff_icon('camera'); ?></div>
                        <div class="capture-kpi-info">
                            <strong><?= count($ongoingAwaitingPhoto); ?> Awaiting Photo</strong>
                            <span>⚡ Proof Photo Needed</span>
                        </div>
                    </div>
                    <div class="capture-kpi-card">
                        <div class="capture-kpi-icon green"><?= staff_icon('check'); ?></div>
                        <div class="capture-kpi-info">
                            <strong><?= $photosVerifiedTotal; ?> Verified</strong>
                            <span>Portraits on File</span>
                        </div>
                    </div>
                </div>

                <!-- 2-Column Responsive Studio Workspace Grid -->
                <div class="capture-studio-layout">
                    <!-- Left Column: Selected Patient Profile Card -->
                    <div class="capture-col-patient">
                        <section class="panel-card capture-profile-card">
                            <div class="capture-card-header">
                                <span class="card-header-icon"><?= staff_icon('user'); ?></span>
                                <div>
                                    <h2>Patient Identity Record</h2>
                                    <p><?= $captureAppointment !== null ? 'Active consultation profile' : 'Patient selection & portrait guide'; ?></p>
                                </div>
                            </div>

                            <?php if ($captureAppointment !== null): ?>
                                <?php 
                                $hasPhoto = !empty($captureAppointment['photo_path']);
                                $patInitials = strtoupper(substr((string) ($captureAppointment['first_name'] ?? 'P'), 0, 1) . substr((string) ($captureAppointment['last_name'] ?? 'U'), 0, 1));
                                $apptStatus = (string) ($captureAppointment['status'] ?? 'Pending');
                                ?>
                                <div class="patient-id-card-hero">
                                    <div class="patient-hero-avatar-wrap">
                                        <?php if ($hasPhoto): ?>
                                            <img src="../Patients/<?= h((string) $captureAppointment['photo_path']); ?>" alt="<?= h(full_name($captureAppointment)); ?>" class="patient-hero-photo-img" id="currentPatientPortraitImg">
                                        <?php else: ?>
                                            <div class="patient-hero-avatar-placeholder">
                                                <?= h($patInitials); ?>
                                            </div>
                                        <?php endif; ?>
                                        <span class="photo-status-badge <?= $hasPhoto ? 'verified' : 'missing'; ?>">
                                            <?= $hasPhoto ? '✓ Photo Verified' : '⚠️ Photo Needed'; ?>
                                        </span>
                                    </div>

                                    <div class="patient-hero-main-info">
                                        <h3><?= h(full_name($captureAppointment)); ?></h3>
                                        <div class="hero-tag-row">
                                            <span class="appt-code-badge">#<?= h((string) ($captureAppointment['appointment_code'] ?? $captureAppointment['reference_code'])); ?></span>
                                            <span class="pat-meta-pill service"><?= staff_icon('stethoscope'); ?> <?= h((string) $captureAppointment['service_name']); ?></span>
                                            <span class="status-pill status-queue-<?= $apptStatus === 'Serving' ? 'serving' : ($apptStatus === 'Completed' ? 'completed' : 'waiting'); ?>">
                                                <?= $apptStatus === 'Serving' ? '⚡ Serving Now' : ($apptStatus === 'Completed' ? '✓ Completed' : '⏳ ' . h($apptStatus)); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="patient-details-grid">
                                    <div class="detail-cell">
                                        <span class="detail-cell-label">Age &amp; Gender</span>
                                        <strong><?= h(age_label($captureAppointment)); ?> • <?= h((string) ($captureAppointment['gender'] ?? '')); ?></strong>
                                    </div>
                                    <div class="detail-cell">
                                        <span class="detail-cell-label">Contact Number</span>
                                        <strong><?= h((string) ($captureAppointment['contact_number'] ?: 'No phone provided')); ?></strong>
                                    </div>
                                    <div class="detail-cell">
                                        <span class="detail-cell-label">Consultation Date</span>
                                        <strong><?= h(date('M j, Y', strtotime((string) $captureAppointment['preferred_date']))); ?></strong>
                                    </div>
                                    <div class="detail-cell">
                                        <span class="detail-cell-label">Preferred Session</span>
                                        <strong><?= h((string) ($captureAppointment['preferred_time'] ?? 'Regular Hours')); ?></strong>
                                    </div>
                                    <div class="detail-cell full-span">
                                        <span class="detail-cell-label">Home Address</span>
                                        <strong><?= h((string) ($captureAppointment['complete_address'] ?: 'Brgy. ' . $station['barangay'] . ', Bacolod City')); ?></strong>
                                    </div>
                                    <?php if (!empty($captureAppointment['notes'])): ?>
                                        <div class="detail-cell full-span">
                                            <span class="detail-cell-label">Chief Complaint / Notes</span>
                                            <strong><?= h((string) $captureAppointment['notes']); ?></strong>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Photo Guidelines -->
                                <div class="photo-guidelines-box">
                                    <div class="guidelines-title">
                                        <?= staff_icon('sparkle'); ?>
                                    <span>Portrait Photo Guidelines</span>
                                    </div>
                                    <ul>
                                        <li>Position patient's face centered inside the oval guide frame.</li>
                                        <li>Ensure good ambient lighting with no heavy backlight or glare.</li>
                                        <li>Patient faces directly towards the camera lens with clear view.</li>
                                    </ul>
                                </div>

                                <a href="?page=patients&appointment_record=<?= h((string) ($captureAppointment['appointment_code'] ?? $captureAppointment['reference_code'])); ?>" class="view-patient-file-action-btn" title="Open patient's complete clinical record">
                                    <span class="file-action-icon"><?= staff_icon('eye'); ?></span>
                                    <span>View Medical File in Patients</span>
                                    <span class="file-action-arrow"><?= staff_icon('arrow-right'); ?></span>
                                </a>
                            <?php else: ?>
                                <div class="empty-patient-selection">
                                    <div class="empty-selection-icon"><?= staff_icon('camera'); ?></div>
                                    <h3>Patient Identity Center</h3>
                                    <p>Select an ongoing patient below to capture proof of appearance and link their photo directly to health records.</p>
                                    
                                    <div class="photo-guidelines-box" style="width:100%;margin-top:16px;text-align:left;">
                                        <div class="guidelines-title">
                                            <?= staff_icon('sparkle'); ?>
                                            <span>Quick 3-Step Process</span>
                                        </div>
                                        <ul>
                                            <li><strong>Step 1:</strong> Choose an ongoing patient from the queue directory below.</li>
                                            <li><strong>Step 2:</strong> Turn on live camera to capture portrait.</li>
                                            <li><strong>Step 3:</strong> Click Save to attach to official consultation files.</li>
                                        </ul>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </section>
                    </div>

                    <!-- Right Column: Interactive Studio & Directory -->
                    <div class="capture-col-studio">
                        <section class="panel-card studio-card">
                            <div class="studio-card-header">
                                <div class="studio-header-title">
                                    <span class="card-header-icon studio-icon"><?= staff_icon('camera'); ?></span>
                                    <div>
                                        <h2>Photo Capture Studio</h2>
                                        <?php if ($captureAppointment !== null): ?>
                                            <p>Patient: <strong><?= h(full_name($captureAppointment)); ?></strong> <a href="?page=image-capture<?= $programFilter !== '' ? '&program=' . h($programFilter) : ''; ?><?= $captureFilter !== '' ? '&capture_filter=' . h($captureFilter) : ''; ?>" style="margin-left:8px;color:#ef4444;font-weight:700;font-size:0.8rem;text-decoration:underline;">[✕ Deselect]</a></p>
                                        <?php else: ?>
                                            <p>Live Portrait Studio • Capture Patient Attendance Portrait</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <form method="post" id="captureForm" class="studio-form-wrap">
                                <input type="hidden" name="action" value="save_photo">
                                <?php if ($programFilter !== ''): ?>
                                    <input type="hidden" name="program" value="<?= h($programFilter); ?>">
                                <?php endif; ?>
                                <input type="hidden" name="csrf_token" value="<?= h($csrf); ?>">
                                <input type="hidden" name="captured_photo_data" id="captured_photo_data" value="">

                                <?php if ($captureAppointment !== null): ?>
                                    <input type="hidden" name="appointment_id" value="<?= h((string) $captureAppointment['id']); ?>">
                                <?php else: ?>
                                    <?php 
                                    $studioSelectCandidates = array_values(array_filter($allCaptureCandidates, static fn(array $c): bool => empty($c['photo_path'])));
                                    ?>
                                    <?php if (!empty($studioSelectCandidates)): ?>
                                        <div class="form-group-item" style="background:#eff6ff;padding:14px 18px;border-radius:14px;border:1.5px solid #bfdbfe;">
                                            <label for="select_appointment_id" class="form-field-label" style="color:#1e40af;font-weight:700;">
                                                <span>Select Patient Record to Attach Photo:</span>
                                                <span class="required">*</span>
                                            </label>
                                            <select id="select_appointment_id" name="appointment_id" class="form-input-field" required style="background:#fff;margin-top:6px;" onchange="if(this.value){ window.location.href = '?page=image-capture<?= $programFilter !== '' ? '&program=' . urlencode($programFilter) : ''; ?>&appointment=' + this.value; }">
                                                <option value="">-- Choose Patient Record from Directory --</option>
                                                <?php foreach ($studioSelectCandidates as $c): ?>
                                                    <option value="<?= $c['id']; ?>">
                                                        <?= h(full_name($c)); ?> (#<?= h((string) ($c['appointment_code'] ?? $c['reference_code'])); ?>) - <?= h((string) $c['service_name']); ?> [<?= h((string) $c['status']); ?>]
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    <?php else: ?>
                                        <div class="form-group-item" style="background:#f8fafc;padding:14px 18px;border-radius:14px;border:1.5px dashed #cbd5e1;font-size:0.86rem;color:#64748b;">
                                            <strong>ℹ️ Standalone Portrait Testing Mode</strong>
                                            <p style="margin:4px 0 0;">You can test and capture webcam portraits anytime. Once patient appointments are scheduled, you can select and link photos directly.</p>
                                            <input type="hidden" name="appointment_id" value="0">
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <!-- Live Webcam Viewfinder -->
                                <div class="studio-tab-content" id="cameraTabContent">
                                    <div class="viewfinder-stage-box" id="viewfinderStage">
                                        <!-- Video Stream Element -->
                                        <video id="imageCaptureVideo" autoplay playsinline muted style="display:none;"></video>
                                        <canvas id="imageCaptureCanvas" style="display:none;"></canvas>

                                        <!-- Shutter Flash Overlay -->
                                        <div class="shutter-flash-overlay" id="shutterFlashOverlay"></div>

                                        <!-- Face Guide Overlay -->
                                        <div class="viewfinder-face-guide" id="viewfinderGuide">
                                            <div class="guide-oval"></div>
                                            <span class="guide-label">Center Face Inside Oval</span>
                                        </div>

                                        <!-- Status Badge Overlay -->
                                        <div class="viewfinder-live-badge" id="cameraStatusBadge">
                                            <span class="live-pulse-dot"></span>
                                            <span id="cameraStatusText">Camera Standby</span>
                                        </div>

                                        <!-- Standby Placeholder -->
                                        <div class="viewfinder-off-placeholder" id="cameraOffPlaceholder">
                                            <span class="cam-icon-big"><?= staff_icon('camera'); ?></span>
                                            <p id="cameraOffMsg">Live Camera Standby • In-Person Station Capture</p>
                                            <div class="cam-placeholder-actions">
                                                <button type="button" class="primary-btn blue-btn" id="btnStartCamera">
                                                    <?= staff_icon('camera'); ?>
                                                    <span>Turn On Live Camera</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Camera Device Selector -->
                                    <div class="camera-device-select-bar" id="cameraDeviceSelectBar" style="display:none;margin-top:12px;margin-bottom:6px;align-items:center;gap:10px;background:#f8fafc;padding:10px 14px;border-radius:12px;border:1.5px solid #e2e8f0;">
                                        <label for="cameraDeviceSelect" style="font-size:0.84rem;font-weight:700;color:#475569;display:flex;align-items:center;gap:6px;white-space:nowrap;margin:0;">
                                            <?= staff_icon('camera'); ?>
                                            <span>Attached Camera:</span>
                                        </label>
                                        <select id="cameraDeviceSelect" class="form-input-field" style="flex:1;margin:0;padding:7px 12px;font-size:0.84rem;border-radius:8px;background:#ffffff;height:auto;" title="Select attached computer webcam"></select>
                                    </div>

                                    <!-- Camera Action Toolbar -->
                                    <div class="camera-studio-toolbar" id="cameraToolbar" style="display:none;">
                                        <button type="button" class="studio-stop-btn" id="btnStopCamera">
                                            <?= staff_icon('x'); ?>
                                            <span>Stop Camera</span>
                                        </button>
                                        <button type="button" class="studio-capture-btn" id="btnSnapPhoto">
                                            <span class="shutter-lens-icon"><?= staff_icon('camera'); ?></span>
                                            <span>Capture Photo</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- CAPTURED CONFIRMATION STAGE -->
                                <div class="captured-confirmation-panel" id="capturedConfirmPanel" style="display:none;">
                                    <div class="account-section-divider">
                                        <?= staff_icon('check'); ?>
                                        <span>Captured Portrait Confirmation</span>
                                    </div>

                                    <div class="confirm-preview-row">
                                        <div class="confirm-photo-frame">
                                            <img id="imageCapturePreview" alt="Captured Portrait Preview">
                                        </div>
                                        <div class="confirm-photo-details">
                                            <div class="confirm-badge">
                                                <?= staff_icon('sparkle'); ?>
                                                <span>Portrait Ready to Link</span>
                                            </div>
                                            <h4>Attach Photo to <?= $captureAppointment !== null ? h(full_name($captureAppointment)) : 'Patient Record'; ?></h4>
                                            <p>This portrait will be permanently attached to the patient's official station records and clinical consultation files.</p>
                                            <div class="confirm-actions-row">
                                                <button type="button" class="confirm-retake-btn" id="btnRetakePhoto">
                                                    <?= staff_icon('history'); ?>
                                                    <span>Retake Photo</span>
                                                </button>
                                                <button type="submit" class="confirm-save-btn" id="savePhotoButton">
                                                    <?= staff_icon('check'); ?>
                                                    <span>Save Photo</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </section>

                        <!-- Station Patient & Queue Directory -->
                        <section class="panel-card capture-appointments-panel">
                            <div class="capture-directory-header">
                                <div>
                                    <h2>Station Front Desk &amp; Patient Queue</h2>
                                    <p id="captureDirectoryCount"><?= count($displayCandidates); ?> appointment<?= count($displayCandidates) !== 1 ? 's' : ''; ?> listed in directory</p>
                                </div>
                            </div>

                            <!-- Filter Chips (Needs Photo, In-Queue & Serving, Photo Verified) -->
                            <div class="capture-filter-chips" id="captureFilterChips">
                                <button type="button" class="filter-chip-btn urgent <?= $captureFilter === 'needs_photo' ? 'active' : ''; ?>" data-capture-filter="needs_photo" onclick="return window.applyCaptureFilter(this, event, 'needs_photo');">
                                    <?= staff_icon('camera'); ?>
                                    <span>⚡ Needs Photo (<?= count($ongoingAwaitingPhoto); ?>)</span>
                                </button>
                                <button type="button" class="filter-chip-btn <?= $captureFilter === 'ongoing' ? 'active' : ''; ?>" data-capture-filter="ongoing" onclick="return window.applyCaptureFilter(this, event, 'ongoing');">
                                    <?= staff_icon('pulse'); ?>
                                    <span>In-Queue &amp; Serving (<?= count($activeFrontDeskQueue); ?>)</span>
                                </button>
                                <button type="button" class="filter-chip-btn <?= $captureFilter === 'verified' ? 'active' : ''; ?>" data-capture-filter="verified" onclick="return window.applyCaptureFilter(this, event, 'verified');">
                                    <?= staff_icon('check'); ?>
                                    <span>Photo Verified (<?= $photosVerifiedTodayTotal; ?>)</span>
                                </button>
                            </div>

                            <div class="capture-appointments-grid" id="captureAppointmentsGrid">
                                <div class="panel-card empty-state appt-empty-box" id="captureGridEmptyState" style="padding:28px 20px;text-align:center;grid-column:1/-1;<?= count($displayCandidates) === 0 ? 'display:block;' : 'display:none;'; ?>">
                                    <div class="appt-empty-icon"><?= staff_icon('users'); ?></div>
                                    <h3 style="margin:8px 0 4px;font-size:1.05rem;font-weight:800;color:#0f172a;">No Patient Appointments Matching Filter</h3>
                                    <p style="margin:0;color:#64748b;font-size:0.85rem;">No patient appointments found under this filter.<br>Newly booked appointments and ongoing consultations will automatically appear here.</p>
                                </div>

                                <?php foreach ($allCaptureCandidates as $pat): ?>
                                    <?php 
                                    $isSelected = $captureAppointment !== null && (int) $pat['id'] === (int) $captureAppointment['id'];
                                    $patHasPhoto = !empty($pat['photo_path']);
                                    $initials = strtoupper(substr((string) ($pat['first_name'] ?? 'P'), 0, 1) . substr((string) ($pat['last_name'] ?? 'U'), 0, 1));
                                    $patQueueStatus = (string) ($pat['status'] ?? 'Pending');
                                    $isServing = $patQueueStatus === 'Serving';
                                    $isOngoing = in_array($patQueueStatus, ['Serving', 'Confirmed'], true);
                                    $isApptToday = (string) ($pat['preferred_date'] ?? '') === date('Y-m-d');
                                    $isCompletedToday = $patQueueStatus === 'Completed' && $isApptToday;
                                    $patHasPhotoVerifiedToday = $patHasPhoto && $isCompletedToday;
                                    $canCapture = $isApptToday && $isServing;

                                    $matchesCurrentFilter = false;
                                    if ($captureFilter === 'needs_photo' && !$patHasPhoto) {
                                        $matchesCurrentFilter = true;
                                    } elseif ($captureFilter === 'ongoing' && $isOngoing) {
                                        $matchesCurrentFilter = true;
                                    } elseif ($captureFilter === 'verified' && $patHasPhotoVerifiedToday) {
                                        $matchesCurrentFilter = true;
                                    }
                                    ?>
                                    <div class="capture-pat-item <?= $isSelected ? 'is-active' : ''; ?>" 
                                         data-has-photo="<?= $patHasPhoto ? '1' : '0'; ?>" 
                                         data-is-ongoing="<?= $isOngoing ? '1' : '0'; ?>"
                                         data-is-verified-today="<?= $patHasPhotoVerifiedToday ? '1' : '0'; ?>"
                                         style="<?= $matchesCurrentFilter ? 'display:flex;' : 'display:none;'; ?>">
                                        <div class="capture-pat-avatar">
                                            <?php if ($patHasPhoto): ?>
                                                <img src="../Patients/<?= h((string) $pat['photo_path']); ?>" alt="<?= h(full_name($pat)); ?>">
                                            <?php else: ?>
                                                <div class="capture-pat-init"><?= h($initials); ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="capture-pat-info">
                                            <div class="capture-pat-title-row">
                                                <h4><?= h(full_name($pat)); ?></h4>
                                                <span class="appt-code-badge">#<?= h((string) ($pat['appointment_code'] ?? $pat['reference_code'])); ?></span>
                                                <span class="status-pill status-queue-<?= $isServing ? 'serving' : ($patQueueStatus === 'Completed' ? 'completed' : 'waiting'); ?>">
                                                    <?= $isServing ? '⚡ Serving Now' : ($patQueueStatus === 'Completed' ? '✓ Done' : '⏳ ' . h($patQueueStatus)); ?>
                                                </span>
                                                <?php if ($patHasPhoto): ?>
                                                    <span class="photo-status-badge verified" style="position:static;transform:none;font-size:0.7rem;padding:3px 10px;">✓ Photo on file</span>
                                                <?php else: ?>
                                                    <span class="photo-status-badge missing" style="position:static;transform:none;font-size:0.7rem;padding:3px 10px;">⚠️ Needs Photo</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="capture-pat-meta">
                                                <span><?= staff_icon('stethoscope'); ?> <?= h((string) $pat['service_name']); ?></span>
                                                <span><?= staff_icon('calendar'); ?> <?= h(date('M j, Y', strtotime((string) $pat['preferred_date']))); ?></span>
                                                <span><?= staff_icon('clock'); ?> <?= h((string) ($pat['preferred_time'] ?? 'Regular Hours')); ?></span>
                                                <span><?= h(age_label($pat)); ?> • <?= h((string) $pat['gender']); ?></span>
                                                <?php if (!empty($pat['contact_number'])): ?>
                                                    <span><?= staff_icon('phone'); ?> <?= h((string) $pat['contact_number']); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="capture-pat-actions">
                                            <?php if ($isSelected): ?>
                                                <span class="select-patient-btn is-active-badge" title="Selected for live camera capture">
                                                    <span class="btn-icon-wrap"><?= staff_icon('check'); ?></span>
                                                    <span>Selected</span>
                                                </span>
                                            <?php elseif ($patHasPhoto): ?>
                                                <span class="select-patient-btn is-completed-badge" title="Photo verified and locked to health record">
                                                    <span class="btn-icon-wrap"><?= staff_icon('check'); ?></span>
                                                    <span>Photo Verified</span>
                                                </span>
                                            <?php elseif ($canCapture): ?>
                                                <a class="select-patient-btn" href="?page=image-capture&appointment=<?= h((string) $pat['id']); ?><?= $programFilter !== '' ? '&program=' . urlencode($programFilter) : ''; ?><?= $captureFilter !== '' ? '&capture_filter=' . urlencode($captureFilter) : ''; ?><?= $patientSearch !== '' ? '&patient_search=' . urlencode($patientSearch) : ''; ?>" title="Capture photo for <?= h(full_name($pat)); ?>">
                                                    <span class="btn-icon-wrap"><?= staff_icon('camera'); ?></span>
                                                    <span>Capture Photo</span>
                                                </a>
                                            <?php else: ?>
                                                <?php
                                                $disabledReason = !$isApptToday 
                                                    ? 'Photo capture is only available on appointment date (' . date('M j, Y', strtotime((string) $pat['preferred_date'])) . ')' 
                                                    : 'Photo capture is only available when patient is being served in queue management.';
                                                ?>
                                                <button type="button" class="select-patient-btn is-disabled" disabled title="<?= h($disabledReason); ?>">
                                                    <span class="btn-icon-wrap"><?= staff_icon('camera'); ?></span>
                                                    <span>Capture Photo</span>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    </div>
                </div>
            </section>
        <?php elseif ($page === 'events'): ?>
            <!-- Upcoming Events Header -->
            <div class="events-page-header">
                <div>
                    <h1>Barangay Health Events &amp; Outreach</h1>
                    <p>Schedule vaccination drives, feeding programs, medical missions, and health seminars.</p>
                </div>
                <a class="primary-btn" href="?page=events&show_event_modal=1">
                    <?= staff_icon('plus'); ?>
                    <span>Schedule New Event</span>
                </a>
            </div>

            <!-- Events Cards Grid -->
            <section class="modern-events-grid">
                <?php if ($stationEvents === []): ?>
                    <div class="panel-card empty-state appt-empty-box" style="grid-column: 1 / -1;">
                        <div class="appt-empty-icon"><?= staff_icon('events'); ?></div>
                        <h3>No upcoming events scheduled</h3>
                        <p>No health events or community drives have been scheduled yet for <?= h($station['name']); ?>.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($stationEvents as $event): ?>
                        <?php
                        $eventTime = strtotime((string) $event['event_date']);
                        $eventMonth = date('M', $eventTime);
                        $eventDay = date('j', $eventTime);
                        $eventYear = date('Y', $eventTime);
                        $eventDayName = date('l', $eventTime);
                        $iconType = (string) ($event['icon'] ?? 'calendar');
                        ?>
                        <article class="modern-event-card">
                            <div class="event-card-top-bar">
                                <div class="event-date-block">
                                    <span class="event-month"><?= h($eventMonth); ?></span>
                                    <span class="event-day"><?= h($eventDay); ?></span>
                                    <span class="event-year"><?= h($eventYear); ?></span>
                                </div>

                                <div class="event-card-heading">
                                    <div class="event-category-chip cat-<?= h($iconType); ?>">
                                        <?= staff_icon($iconType); ?>
                                        <span><?= h(ucfirst(str_replace('-', ' ', $iconType))); ?></span>
                                    </div>
                                    <h3><?= h($event['title']); ?></h3>
                                </div>

                                <div class="event-actions-dropdown">
                                    <a href="?page=events&edit_event=<?= h((string) $event['id']); ?>" class="event-action-icon edit" title="Edit Event">
                                        <?= staff_icon('edit'); ?>
                                    </a>
                                    <form method="post" style="margin:0;" onsubmit="return confirm('Are you sure you want to delete this event?');">
                                        <input type="hidden" name="action" value="delete_event">
                                        <input type="hidden" name="csrf_token" value="<?= h($csrf); ?>">
                                        <input type="hidden" name="event_id" value="<?= h((string) $event['id']); ?>">
                                        <button type="submit" class="event-action-icon delete" title="Delete Event">
                                            <?= staff_icon('trash'); ?>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div class="event-card-content">
                                <p class="event-desc"><?= nl2br(h($event['description'])); ?></p>
                            </div>

                            <div class="event-card-footer">
                                <div class="event-footer-item">
                                    <?= staff_icon('clock'); ?>
                                    <span><?= h($event['time_label']); ?><?php if (!empty($event['end_time_label'])): ?> - <?= h((string) $event['end_time_label']); ?><?php endif; ?></span>
                                </div>
                                <div class="event-footer-item">
                                    <?= staff_icon('pin'); ?>
                                    <span><?= h($event['station_name']); ?></span>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>

            <!-- Event Create/Edit Modal -->
            <?php if ($showEventModal): ?>
                <section class="account-modal-backdrop" id="eventModalBackdrop">
                    <div class="account-modal-card" role="dialog" aria-modal="true">
                        <div class="account-modal-header">
                            <div class="account-modal-title-group">
                                <span class="account-modal-icon"><?= staff_icon('calendar'); ?></span>
                                <div>
                                    <h2><?= $eventEditing !== null ? 'Update Health Event' : 'Schedule New Health Event'; ?></h2>
                                    <p>Organize community outreach, immunization, and medical services for <?= h($station['name']); ?>.</p>
                                </div>
                            </div>
                            <a class="account-modal-close" href="?page=events" aria-label="Close modal">×</a>
                        </div>

                        <form method="post" class="account-settings-form">
                            <input type="hidden" name="action" value="<?= $eventEditing !== null ? 'update_event' : 'create_event'; ?>">
                            <input type="hidden" name="csrf_token" value="<?= h($csrf); ?>">
                            <?php if ($eventEditing !== null): ?>
                                <input type="hidden" name="event_id" value="<?= h((string) $eventEditing['id']); ?>">
                            <?php endif; ?>

                            <div class="account-modal-body">
                                <div class="form-group-item">
                                    <label for="event_title_input" class="form-field-label">
                                        <span>Event Title</span>
                                        <span class="required">*</span>
                                    </label>
                                    <input type="text" id="event_title_input" name="title" value="<?= h((string) ($eventEditing['title'] ?? '')); ?>" placeholder="e.g. Community Flu Vaccination Drive" required class="form-input-field">
                                </div>

                                <div class="form-row-grid">
                                    <div class="form-group-item">
                                        <label for="event_date_input" class="form-field-label">
                                            <span>Event Date</span>
                                            <span class="required">*</span>
                                        </label>
                                        <input type="date" id="event_date_input" name="event_date" min="<?= h(date('Y-m-d')); ?>" value="<?= h((string) ($eventEditing['event_date'] ?? '')); ?>" required class="form-input-field">
                                    </div>

                                    <div class="form-group-item">
                                        <label for="event_icon_select" class="form-field-label">
                                            <span>Event Category</span>
                                            <span class="required">*</span>
                                        </label>
                                        <select id="event_icon_select" name="icon" class="form-input-field" required>
                                            <option value="syringe" <?= (($eventEditing['icon'] ?? '') === 'syringe') ? 'selected' : ''; ?>>💉 Vaccination Drive</option>
                                            <option value="community" <?= (($eventEditing['icon'] ?? '') === 'community') ? 'selected' : ''; ?>>🍲 Feeding Program</option>
                                            <option value="heart" <?= (($eventEditing['icon'] ?? '') === 'heart') ? 'selected' : ''; ?>>🩺 Free Medical Consultation</option>
                                            <option value="calendar" <?= (($eventEditing['icon'] ?? '') === 'calendar') ? 'selected' : ''; ?>>📋 Health Seminar / Workshop</option>
                                            <option value="pulse" <?= (($eventEditing['icon'] ?? '') === 'pulse') ? 'selected' : ''; ?>>🩸 Blood Donation / Screening</option>
                                            <option value="other" <?= (($eventEditing['icon'] ?? '') === 'other') ? 'selected' : ''; ?>>✨ General Community Event</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-row-grid">
                                    <div class="form-group-item">
                                        <label for="time_label_input" class="form-field-label">
                                            <span>Start Time</span>
                                            <span class="required">*</span>
                                        </label>
                                        <input type="text" id="time_label_input" name="time_label" value="<?= h((string) ($eventEditing['time_label'] ?? '')); ?>" placeholder="e.g. 08:30 AM" required class="form-input-field">
                                    </div>

                                    <div class="form-group-item">
                                        <label for="end_time_label_input" class="form-field-label">
                                            <span>End Time</span>
                                            <span class="required">*</span>
                                        </label>
                                        <input type="text" id="end_time_label_input" name="end_time_label" value="<?= h((string) ($eventEditing['end_time_label'] ?? '')); ?>" placeholder="e.g. 11:30 AM" required class="form-input-field">
                                    </div>
                                </div>

                                <div class="form-group-item">
                                    <label for="event_desc_input" class="form-field-label">
                                        <span>Event Description</span>
                                        <span class="required">*</span>
                                    </label>
                                    <textarea id="event_desc_input" name="description" rows="4" placeholder="Describe the health event objectives, target barangay puroks, requirements, etc..." required class="form-input-field" style="height:auto;padding:12px 16px;resize:vertical;"><?= h((string) ($eventEditing['description'] ?? '')); ?></textarea>
                                </div>

                                <input type="hidden" name="accent" value="blue">
                            </div>

                            <div class="account-modal-footer">
                                <a class="ghost-btn" href="?page=events">Cancel</a>
                                <button type="submit" class="primary-btn">
                                    <?= staff_icon('check'); ?>
                                    <span><?= $eventEditing !== null ? 'Save Changes' : 'Publish Event'; ?></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </section>
            <?php endif; ?>
        <?php elseif ($page === 'reports'): ?>
            <!-- Weekly Reports Header -->
            <section class="page-hero reports-page-hero">
                <div class="reports-hero-left">
                    <div class="reports-hero-badge">
                        <?= staff_icon('reports'); ?>
                        <span>Barangay Health Station Analytics</span>
                    </div>
                    <h1>Weekly Operational Reports</h1>
                    <p>Performance analytics, service breakdown, and consultation statistics strictly for <strong><?= h($station['name']); ?></strong>.</p>
                </div>
                <div class="reports-hero-actions no-print">
                    <button type="button" class="primary-btn slim reports-print-btn" onclick="window.print()">
                        <?= staff_icon('download'); ?>
                        <span>Print / Export Report</span>
                    </button>
                </div>
            </section>

            <!-- Weekly Range Selector & Filter Toolbar -->
            <section class="appt-filter-card reports-filter-card no-print">
                <div class="reports-filter-left">
                    <span class="reports-filter-title"><?= staff_icon('calendar'); ?> Selected Period: <strong><?= h($reportWeekLabel); ?></strong></span>
                    <div class="reports-quick-pills">
                        <a href="?page=reports&week_offset=0" class="reports-quick-pill <?= ($reportWeekOffset === 0 && $reportFromCustom === '') ? 'is-active' : ''; ?>">This Week</a>
                        <a href="?page=reports&week_offset=-1" class="reports-quick-pill <?= ($reportWeekOffset === -1 && $reportFromCustom === '') ? 'is-active' : ''; ?>">Last Week</a>
                        <a href="?page=reports&week_offset=-2" class="reports-quick-pill <?= ($reportWeekOffset === -2 && $reportFromCustom === '') ? 'is-active' : ''; ?>">2 Weeks Ago</a>
                        <a href="?page=reports&week_offset=-3" class="reports-quick-pill <?= ($reportWeekOffset === -3 && $reportFromCustom === '') ? 'is-active' : ''; ?>">3 Weeks Ago</a>
                    </div>
                </div>

                <form method="get" class="reports-custom-range-form">
                    <input type="hidden" name="page" value="reports">
                    <div class="reports-custom-inputs">
                        <div class="reports-date-input-wrap">
                            <label>From:</label>
                            <input type="date" name="report_from" value="<?= h($reportStartDate); ?>" required>
                        </div>
                        <div class="reports-date-input-wrap">
                            <label>To:</label>
                            <input type="date" name="report_to" value="<?= h($reportEndDate); ?>" required>
                        </div>
                        <button type="submit" class="appt-find-btn primary-btn slim">
                            <?= staff_icon('filter'); ?>
                            <span>Apply</span>
                        </button>
                        <?php if ($reportFromCustom !== '' || $reportToCustom !== ''): ?>
                            <a href="?page=reports" class="ghost-btn slim" title="Reset to This Week">Reset</a>
                        <?php endif; ?>
                    </div>
                </form>
            </section>

            <!-- 5-KPI Executive Metrics Grid -->
            <section class="reports-metrics-grid">
                <article class="reports-metric-card total-pat">
                    <div class="reports-metric-icon">
                        <?= staff_icon('users'); ?>
                    </div>
                    <div class="reports-metric-content">
                        <span class="reports-metric-label">Total Unique Patients</span>
                        <strong class="reports-metric-val"><?= number_format($weeklyUniquePatients); ?></strong>
                        <small class="reports-metric-hint">Individuals served this week</small>
                    </div>
                </article>

                <article class="reports-metric-card booked">
                    <div class="reports-metric-icon">
                        <?= staff_icon('calendar'); ?>
                    </div>
                    <div class="reports-metric-content">
                        <span class="reports-metric-label">Appointments Booked</span>
                        <strong class="reports-metric-val"><?= number_format($weeklyTotalBooked); ?></strong>
                        <small class="reports-metric-hint">Scheduled for <?= h($station['name']); ?></small>
                    </div>
                </article>

                <article class="reports-metric-card completed">
                    <div class="reports-metric-icon">
                        <?= staff_icon('check'); ?>
                    </div>
                    <div class="reports-metric-content">
                        <span class="reports-metric-label">Completed Consultations</span>
                        <strong class="reports-metric-val"><?= number_format($weeklyCompleted); ?></strong>
                        <small class="reports-metric-hint">
                            <?php $compRate = $weeklyTotalBooked > 0 ? round(($weeklyCompleted / $weeklyTotalBooked) * 100) : 0; ?>
                            <span class="reports-rate-badge success"><?= $compRate; ?>% Completed</span>
                        </small>
                    </div>
                </article>

                <article class="reports-metric-card in-queue">
                    <div class="reports-metric-icon">
                        <?= staff_icon('clock'); ?>
                    </div>
                    <div class="reports-metric-content">
                        <span class="reports-metric-label">Confirmed / In Queue</span>
                        <strong class="reports-metric-val"><?= number_format($weeklyActiveWaiting); ?></strong>
                        <small class="reports-metric-hint"><?= $weeklyPending; ?> pending review</small>
                    </div>
                </article>

                <article class="reports-metric-card cancelled">
                    <div class="reports-metric-icon">
                        <?= staff_icon('x'); ?>
                    </div>
                    <div class="reports-metric-content">
                        <span class="reports-metric-label">Cancelled Bookings</span>
                        <strong class="reports-metric-val"><?= number_format($weeklyCancelled); ?></strong>
                        <small class="reports-metric-hint">
                            <?php $cancRate = $weeklyTotalBooked > 0 ? round(($weeklyCancelled / $weeklyTotalBooked) * 100) : 0; ?>
                            <span class="reports-rate-badge danger"><?= $cancRate; ?>% Cancelled</span>
                        </small>
                    </div>
                </article>
            </section>

            <!-- Two-Column Analytics Layout: Service Breakdown & Daily Volume -->
            <div class="reports-two-col-grid">
                <!-- Left: Health Services Breakdown for this Station -->
                <section class="panel-card reports-section-panel">
                    <div class="reports-section-header">
                        <div class="reports-sec-title-wrap">
                            <span class="reports-sec-icon-pill"><?= staff_icon('stethoscope'); ?></span>
                            <div>
                                <h3>Appointments by Health Service</h3>
                                <p>Volume distribution across medical programs offered at <?= h($station['name']); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="reports-services-list">
                        <?php if ($weeklyServiceStats === [] || $weeklyTotalBooked === 0): ?>
                            <div class="reports-empty-inline">
                                <?= staff_icon('alert-circle'); ?>
                                <span>No appointment records recorded for this station during <?= h($reportWeekLabel); ?>.</span>
                            </div>
                        <?php else: ?>
                            <?php foreach ($weeklyServiceStats as $svc): ?>
                                <div class="reports-service-row">
                                    <div class="reports-svc-top">
                                        <div class="reports-svc-info">
                                            <span class="reports-svc-icon-badge <?= h($svc['color']); ?>">
                                                <?= staff_icon($svc['icon']); ?>
                                            </span>
                                            <strong><?= h($svc['title']); ?></strong>
                                        </div>
                                        <div class="reports-svc-stats">
                                            <span class="reports-svc-count"><strong><?= $svc['total']; ?></strong> appt<?= $svc['total'] === 1 ? '' : 's'; ?></span>
                                            <span class="reports-svc-pct"><?= $svc['share_pct']; ?>% share</span>
                                        </div>
                                    </div>

                                    <div class="reports-progress-track">
                                        <div class="reports-progress-fill <?= h($svc['color']); ?>" style="width: <?= max($svc['share_pct'], $svc['total'] > 0 ? 5 : 0); ?>%;"></div>
                                    </div>

                                    <div class="reports-svc-subtags">
                                        <span class="svc-subtag done">✓ <?= $svc['completed']; ?> Completed</span>
                                        <span class="svc-subtag waiting">⏳ <?= $svc['confirmed'] + $svc['pending']; ?> Active/Pending</span>
                                        <?php if ($svc['cancelled'] > 0): ?>
                                            <span class="svc-subtag cancelled">✕ <?= $svc['cancelled']; ?> Cancelled</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Right: Daily Weekly Consultation Volume (Mon - Sat) -->
                <section class="panel-card reports-section-panel">
                    <div class="reports-section-header">
                        <div class="reports-sec-title-wrap">
                            <span class="reports-sec-icon-pill"><?= staff_icon('calendar'); ?></span>
                            <div>
                                <h3>Daily Consultation Volume</h3>
                                <p>Day-by-day appointments and completed visits (Monday to Saturday)</p>
                            </div>
                        </div>
                    </div>

                    <div class="reports-days-grid">
                        <?php foreach ($weeklyDayData as $dayItem): ?>
                            <?php
                            $isDayToday = $dayItem['date'] === date('Y-m-d');
                            ?>
                            <div class="reports-day-card <?= $isDayToday ? 'is-today-day' : ''; ?>">
                                <div class="reports-day-head">
                                    <span class="reports-day-name"><?= h($dayItem['day_name']); ?></span>
                                    <span class="reports-day-date"><?= h($dayItem['formatted_date']); ?></span>
                                </div>
                                <div class="reports-day-nums">
                                    <div class="reports-day-stat-box">
                                        <span class="day-stat-label">Booked</span>
                                        <strong class="day-stat-val booked"><?= $dayItem['total']; ?></strong>
                                    </div>
                                    <div class="reports-day-stat-box">
                                        <span class="day-stat-label">Completed</span>
                                        <strong class="day-stat-val done"><?= $dayItem['completed']; ?></strong>
                                    </div>
                                </div>
                                <?php if ($isDayToday): ?>
                                    <span class="reports-today-marker">Today</span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Station Summary Highlights Box -->
                    <div class="reports-highlight-box">
                        <div class="reports-hl-icon"><?= staff_icon('sparkle'); ?></div>
                        <div class="reports-hl-content">
                            <strong>Weekly Station Summary</strong>
                            <p>During the period of <strong><?= h($reportWeekLabel); ?></strong>, <?= h($station['name']); ?> recorded <strong><?= $weeklyTotalBooked; ?></strong> total appointment booking<?= $weeklyTotalBooked === 1 ? '' : 's'; ?> across <strong><?= count(array_filter($weeklyServiceStats, static fn($s) => $s['total'] > 0)); ?></strong> active health services, serving <strong><?= $weeklyUniquePatients; ?></strong> distinct patient<?= $weeklyUniquePatients === 1 ? '' : 's'; ?> with a <strong><?= $weeklyTotalBooked > 0 ? round(($weeklyCompleted / $weeklyTotalBooked) * 100) : 0; ?>%</strong> consultation completion rate.</p>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Detailed Weekly Appointments Ledger Table -->
            <section class="panel-card reports-ledger-section">
                <div class="reports-section-header">
                    <div class="reports-sec-title-wrap">
                        <span class="reports-sec-icon-pill"><?= staff_icon('appointments'); ?></span>
                        <div>
                            <h3>Weekly Appointments Log</h3>
                            <p>All appointment records logged at <?= h($station['name']); ?> for <?= h($reportWeekLabel); ?></p>
                        </div>
                    </div>
                    <span class="queue-count-badge"><?= count($weeklyAppointments); ?> Record<?= count($weeklyAppointments) === 1 ? '' : 's'; ?></span>
                </div>

                <?php if ($weeklyAppointments === []): ?>
                    <div class="appt-empty-box" style="margin-top:16px;">
                        <div class="appt-empty-icon"><?= staff_icon('calendar'); ?></div>
                        <h3>No appointments found</h3>
                        <p>There are no appointments on record for <?= h($station['name']); ?> in this weekly timeframe.</p>
                    </div>
                <?php else: ?>
                    <div class="reports-table-wrap">
                        <table class="reports-table">
                            <thead>
                                <tr>
                                    <th>Date &amp; Time</th>
                                    <th>Appt Code</th>
                                    <th>Patient Name</th>
                                    <th>Service</th>
                                    <th>Contact</th>
                                    <th>Status</th>
                                    <th class="no-print">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($weeklyAppointments as $row): ?>
                                    <?php
                                    $st = (string) ($row['status'] ?? 'Pending');
                                    $code = (string) ($row['appointment_code'] ?? $row['reference_code'] ?? 'N/A');
                                    $formattedApptDate = date('M j, Y', strtotime((string) $row['preferred_date']));
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="reports-td-datetime">
                                                <strong><?= h($formattedApptDate); ?></strong>
                                                <small><?= h((string) ($row['preferred_time'] ?? 'Regular Hours')); ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="appt-code-badge">#<?= h($code); ?></span>
                                        </td>
                                        <td>
                                            <div class="reports-td-pat">
                                                <strong><?= h(full_name($row)); ?></strong>
                                                <small><?= h(age_label($row)); ?> • <?= h((string) ($row['gender'] ?? '')); ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="queue-meta-pill service">
                                                <?= staff_icon('stethoscope'); ?>
                                                <span><?= h((string) $row['service_name']); ?></span>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="reports-td-phone"><?= h((string) ($row['contact_number'] ?? 'None')); ?></span>
                                        </td>
                                        <td>
                                            <span class="status-pill status-queue-<?= $st === 'Completed' ? 'completed' : ($st === 'Serving' ? 'serving' : ($st === 'Cancelled' ? 'danger' : 'waiting')); ?>">
                                                <?= $st === 'Completed' ? '✓ Completed' : ($st === 'Serving' ? '⚡ Serving' : ($st === 'Cancelled' ? '✕ Cancelled' : '⏳ ' . h($st))); ?>
                                            </span>
                                        </td>
                                        <td class="no-print">
                                            <?php if ($st === 'Completed'): ?>
                                                <?php
                                                $reportUrlParams = ($reportWeekOffset !== 0 ? '&week_offset=' . $reportWeekOffset : '') . ($reportFromCustom !== '' && $reportToCustom !== '' ? '&report_from=' . urlencode($reportFromCustom) . '&report_to=' . urlencode($reportToCustom) : '');
                                                ?>
                                                <a href="?page=reports<?= $reportUrlParams; ?>&appointment_record=<?= h($code); ?>" class="report-record-btn is-active" title="View completed clinical record">
                                                    <?= staff_icon('eye'); ?>
                                                    <span>Record</span>
                                                </a>
                                            <?php else: ?>
                                                <button type="button" class="report-record-btn is-disabled" disabled title="Clinical record becomes accessible once consultation is completed">
                                                    <?= staff_icon('eye'); ?>
                                                    <span>Record</span>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
                <?php if ($selectedRemarksAppointment !== null): ?>
                    <?php
                    $isFinishedRecord = ((string) ($selectedRemarksAppointment['status'] ?? '') === 'Completed') || ($selectedRecordCode !== '');
                    $reportUrlParams = ($reportWeekOffset !== 0 ? '&week_offset=' . $reportWeekOffset : '') . ($reportFromCustom !== '' && $reportToCustom !== '' ? '&report_from=' . urlencode($reportFromCustom) . '&report_to=' . urlencode($reportToCustom) : '');
                    $remarksReturnUrl = '?page=reports' . $reportUrlParams;
                    ?>
                    <section class="account-modal-backdrop" id="clinicalModalBackdrop">
                        <div class="account-modal-card clinical-dialog-card" role="dialog" aria-modal="true">
                            <div class="account-modal-header">
                                <div class="account-modal-title-group">
                                    <span class="account-modal-icon"><?= staff_icon('stethoscope'); ?></span>
                                    <div>
                                        <h2><?= $isFinishedRecord ? 'Clinical Record &amp; Doctor\'s Notes' : 'Clinical Remarks &amp; Doctor\'s Notes'; ?></h2>
                                        <p>Appointment #<?= h((string) $selectedRemarksAppointment['appointment_code']); ?> • <?= h((string) $selectedRemarksAppointment['service_name']); ?><?= $isFinishedRecord ? ' (Completed Record)' : ''; ?></p>
                                    </div>
                                </div>
                                <a class="account-modal-close" href="<?= h($remarksReturnUrl); ?>" onclick="return window.closeClinicalModal(event, '<?= h($remarksReturnUrl); ?>');" aria-label="Close modal">×</a>
                            </div>

                            <div class="account-settings-form">
                                <div class="account-modal-body">
                                    <!-- Compact Patient Identity Header Strip -->
                                    <div class="patient-modal-banner">
                                        <div class="patient-modal-avatar-wrap">
                                            <?php if ((string) ($selectedRemarksAppointment['photo_path'] ?? '') !== ''): ?>
                                                <img src="../Patients/<?= h((string) $selectedRemarksAppointment['photo_path']); ?>" alt="<?= h(full_name($selectedRemarksAppointment)); ?> photo" class="patient-modal-thumb">
                                            <?php else: ?>
                                                <div class="patient-modal-initials">
                                                    <?= strtoupper(substr((string) ($selectedRemarksAppointment['first_name'] ?? 'P'), 0, 1) . substr((string) ($selectedRemarksAppointment['last_name'] ?? 'U'), 0, 1)); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="patient-modal-details">
                                            <div class="patient-modal-title-row">
                                                <h3><?= h(full_name($selectedRemarksAppointment)); ?></h3>
                                                <span class="appt-code-badge">#<?= h((string) $selectedRemarksAppointment['appointment_code']); ?></span>
                                                <?php if ((string) ($selectedRemarksAppointment['photo_path'] ?? '') !== ''): ?>
                                                    <span class="queue-id-verified"><?= staff_icon('check'); ?> Verified ID</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="patient-modal-meta-row">
                                                <span class="pat-meta-pill service"><?= staff_icon('stethoscope'); ?> <?= h((string) $selectedRemarksAppointment['service_name']); ?></span>
                                                <span class="pat-meta-pill date"><?= staff_icon('calendar'); ?> <?= h(date('F j, Y', strtotime((string) $selectedRemarksAppointment['preferred_date']))); ?></span>
                                                <span class="pat-meta-pill time"><?= staff_icon('clock'); ?> <?= h((string) ($selectedRemarksAppointment['preferred_time'] ?? 'Regular Hours')); ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Patient Demographics Overview -->
                                    <div class="clinical-demographics-card">
                                        <div class="demo-item">
                                            <label>Age &amp; Gender</label>
                                            <span><?= h(age_label($selectedRemarksAppointment)); ?> • <?= h((string) ($selectedRemarksAppointment['gender'] ?? 'Not specified')); ?></span>
                                        </div>
                                        <div class="demo-item">
                                            <label>Date of Birth</label>
                                            <span><?= !empty($selectedRemarksAppointment['birth_date']) ? h(date('F j, Y', strtotime((string) $selectedRemarksAppointment['birth_date']))) : 'N/A'; ?></span>
                                        </div>
                                        <div class="demo-item">
                                            <label>Contact Number</label>
                                            <span class="contact-highlight"><?= staff_icon('phone'); ?> <?= h((string) ($selectedRemarksAppointment['contact_number'] ?: 'None provided')); ?></span>
                                        </div>
                                        <div class="demo-item full-width">
                                            <label>Home Address</label>
                                            <span><?= h((string) ($selectedRemarksAppointment['complete_address'] ?: 'Barangay ' . $station['barangay'] . ', Bacolod City')); ?></span>
                                        </div>
                                        <?php if (!empty($selectedRemarksAppointment['notes'])): ?>
                                            <div class="demo-item full-width patient-complaint-box">
                                                <label>Patient Chief Complaint / Booking Notes</label>
                                                <span><?= h((string) $selectedRemarksAppointment['notes']); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="account-section-divider">
                                        <?= staff_icon('pulse'); ?>
                                        <span>Vital Signs (Recorded)</span>
                                    </div>

                                    <!-- Non-Clickable Vital Signs Display -->
                                    <div class="vitals-display-grid readonly-vitals-grid">
                                        <div class="vital-display-box is-readonly">
                                            <div class="vital-box-header">
                                                <span class="vital-box-icon"><?= staff_icon('pulse'); ?></span>
                                                <span class="vital-box-label">Body Temp</span>
                                            </div>
                                            <strong class="vital-box-val"><?= h((string) (($selectedRemarksAppointment['body_temperature'] ?? '') !== '' ? $selectedRemarksAppointment['body_temperature'] : 'Not recorded')); ?></strong>
                                        </div>
                                        <div class="vital-display-box is-readonly">
                                            <div class="vital-box-header">
                                                <span class="vital-box-icon"><?= staff_icon('heart'); ?></span>
                                                <span class="vital-box-label">Pulse Rate (PR)</span>
                                            </div>
                                            <strong class="vital-box-val"><?= h((string) (($selectedRemarksAppointment['pulse_rate'] ?? '') !== '' ? $selectedRemarksAppointment['pulse_rate'] : 'Not recorded')); ?></strong>
                                        </div>
                                        <div class="vital-display-box is-readonly">
                                            <div class="vital-box-header">
                                                <span class="vital-box-icon"><?= staff_icon('sparkle'); ?></span>
                                                <span class="vital-box-label">Respiration (RR)</span>
                                            </div>
                                            <strong class="vital-box-val"><?= h((string) (($selectedRemarksAppointment['respiration_rate'] ?? '') !== '' ? $selectedRemarksAppointment['respiration_rate'] : 'Not recorded')); ?></strong>
                                        </div>
                                        <div class="vital-display-box is-readonly">
                                            <div class="vital-box-header">
                                                <span class="vital-box-icon"><?= staff_icon('stethoscope'); ?></span>
                                                <span class="vital-box-label">Blood Pressure</span>
                                            </div>
                                            <strong class="vital-box-val"><?= h((string) (($selectedRemarksAppointment['blood_pressure'] ?? '') !== '' ? $selectedRemarksAppointment['blood_pressure'] : 'Not recorded')); ?></strong>
                                        </div>
                                    </div>

                                    <div class="account-section-divider">
                                        <?= staff_icon('edit'); ?>
                                        <span>Doctor's Clinical Notes &amp; Findings</span>
                                    </div>

                                    <div class="form-group-item">
                                        <label for="doc_notes_rep" class="form-field-label">
                                            <span>Clinical Assessment / Prescription / Findings</span>
                                        </label>
                                        <textarea id="doc_notes_rep" name="doctor_notes" rows="4" readonly class="form-input-field" style="height:auto;padding:12px 16px;resize:none;background:#f8fafc;color:#334155;cursor:default;"><?= h((string) (($selectedRemarksAppointment['doctor_notes'] ?? '') !== '' ? $selectedRemarksAppointment['doctor_notes'] : 'No clinical notes recorded.')); ?></textarea>
                                    </div>
                                </div>

                                <div class="account-modal-footer" style="justify-content:flex-end;">
                                    <a class="primary-btn blue-btn" href="<?= h($remarksReturnUrl); ?>" onclick="return window.closeClinicalModal(event, '<?= h($remarksReturnUrl); ?>');" style="min-width:130px;justify-content:center;">
                                        <?= staff_icon('check'); ?>
                                        <span>Close</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </section>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </main>
</div>

<!-- Account Settings Modal -->
<div class="account-modal-backdrop" id="accountModal" hidden>
    <div class="account-modal-card" role="dialog" aria-modal="true" aria-labelledby="accountModalTitle">
        <div class="account-modal-header">
            <div class="account-modal-title-group">
                <span class="account-modal-icon"><?= staff_icon('user'); ?></span>
                <div>
                    <h2 id="accountModalTitle">Staff Account Settings</h2>
                    <p>Update your volunteer / staff profile, contact info, and security credentials.</p>
                </div>
            </div>
            <button type="button" class="account-modal-close" id="closeAccountModalBtn" aria-label="Close modal">×</button>
        </div>
        
        <form method="post" class="account-settings-form" id="staffAccountForm">
            <input type="hidden" name="action" value="update_staff_account">
            <input type="hidden" name="csrf_token" value="<?= h($csrf); ?>">
            <input type="hidden" name="return_page" value="<?= h($page); ?>">
            
            <div class="account-modal-body">
                <!-- Personal Information -->
                <div class="account-section-divider">
                    <?= staff_icon('user'); ?>
                    <span>Personal Information</span>
                </div>
                
                <div class="form-group-item">
                    <label for="staff_name_input" class="form-field-label">
                        <span>Staff / Volunteer Full Name</span>
                        <span class="required">*</span>
                    </label>
                    <input type="text" id="staff_name_input" name="staff_name" value="<?= h((string) ($staffAccount['staff_name'] ?? '')); ?>" required placeholder="e.g. Maria Clara Santos" maxlength="100" class="form-input-field">
                </div>

                <div class="form-row-grid">
                    <div class="form-group-item">
                        <label for="staff_birth_date" class="form-field-label">
                            <span>Date of Birth</span>
                        </label>
                        <input type="date" id="staff_birth_date" name="birth_date" value="<?= h((string) ($staffAccount['birth_date'] ?? '')); ?>" max="<?= date('Y-m-d'); ?>" class="form-input-field">
                    </div>
                    
                    <div class="form-group-item">
                        <label for="staff_gender" class="form-field-label">
                            <span>Gender</span>
                        </label>
                        <select id="staff_gender" name="gender" class="form-input-field">
                            <option value="">Select Gender</option>
                            <option value="Female" <?= ((string) ($staffAccount['gender'] ?? '') === 'Female') ? 'selected' : ''; ?>>Female</option>
                            <option value="Male" <?= ((string) ($staffAccount['gender'] ?? '') === 'Male') ? 'selected' : ''; ?>>Male</option>
                            <option value="Other" <?= ((string) ($staffAccount['gender'] ?? '') === 'Other') ? 'selected' : ''; ?>>Other</option>
                            <option value="Prefer not to say" <?= ((string) ($staffAccount['gender'] ?? '') === 'Prefer not to say') ? 'selected' : ''; ?>>Prefer not to say</option>
                        </select>
                    </div>
                </div>

                <!-- Contact & Residential Details -->
                <div class="account-section-divider">
                    <?= staff_icon('phone'); ?>
                    <span>Contact &amp; Home Address</span>
                </div>

                <div class="form-row-grid">
                    <div class="form-group-item">
                        <label for="staff_email_input" class="form-field-label">
                            <span>Work Email Address</span>
                            <span class="required">*</span>
                        </label>
                        <input type="email" id="staff_email_input" name="email" value="<?= h((string) ($staffAccount['email'] ?? '')); ?>" required placeholder="e.g. staff-bata@bata.health" maxlength="150" class="form-input-field">
                    </div>
                    
                    <div class="form-group-item">
                        <label for="staff_contact_input" class="form-field-label">
                            <span>Personal Contact Number</span>
                        </label>
                        <input type="tel" id="staff_contact_input" name="contact_number" value="<?= h((string) ($staffAccount['contact_number'] ?? '')); ?>" placeholder="e.g. 0917 123 4567" maxlength="20" class="form-input-field">
                    </div>
                </div>

                <div class="form-group-item">
                    <label for="staff_home_address" class="form-field-label">
                        <span>Personal Home Address</span>
                    </label>
                    <input type="text" id="staff_home_address" name="home_address" value="<?= h((string) ($staffAccount['home_address'] ?? '')); ?>" placeholder="e.g. Block 12 Lot 4, Villa Angela Subd., Bacolod City" maxlength="255" class="form-input-field">
                    <small class="field-subnote">Enter your personal home residence (separate from your assigned health station).</small>
                </div>

                <!-- Emergency Contact (Optional) -->
                <div class="account-section-divider">
                    <?= staff_icon('heart'); ?>
                    <span>Emergency Contact Information</span>
                </div>

                <div class="form-row-grid">
                    <div class="form-group-item">
                        <label for="staff_emergency_contact" class="form-field-label">
                            <span>Emergency Contact Person</span>
                        </label>
                        <input type="text" id="staff_emergency_contact" name="emergency_contact" value="<?= h((string) ($staffAccount['emergency_contact'] ?? '')); ?>" placeholder="e.g. Juan Santos (Spouse / Parent)" maxlength="100" class="form-input-field">
                    </div>
                    
                    <div class="form-group-item">
                        <label for="staff_emergency_phone" class="form-field-label">
                            <span>Emergency Contact Phone</span>
                        </label>
                        <input type="tel" id="staff_emergency_phone" name="emergency_phone" value="<?= h((string) ($staffAccount['emergency_phone'] ?? '')); ?>" placeholder="e.g. 0918 987 6543" maxlength="20" class="form-input-field">
                    </div>
                </div>

                <!-- Assigned Health Station (Read-only) -->
                <div class="account-section-divider">
                    <?= staff_icon('shield'); ?>
                    <span>Assigned Health Station</span>
                </div>
                
                <div class="form-group-item">
                    <div class="station-readonly-box">
                        <span class="station-pin-icon"><?= staff_icon('pin'); ?></span>
                        <div class="station-info-text">
                            <strong><?= h($station['name']); ?></strong>
                            <span>Barangay <?= h($station['barangay']); ?>, Bacolod City • Health Delivery Network</span>
                        </div>
                    </div>
                </div>
                
                <!-- Security & Password -->
                <div class="account-section-divider">
                    <?= staff_icon('lock'); ?>
                    <span>Security &amp; Password (Optional)</span>
                </div>
                
                <div class="form-row-grid">
                    <div class="form-group-item">
                        <label for="new_password_input" class="form-field-label">
                            <span>New Password</span>
                        </label>
                        <div class="input-password-wrap">
                            <input type="password" id="new_password_input" name="new_password" placeholder="Leave blank to keep current" minlength="6" class="form-input-field">
                            <button type="button" class="btn-toggle-eye" data-target="new_password_input" aria-label="Toggle password visibility">
                                <?= staff_icon('eye'); ?>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group-item">
                        <label for="confirm_password_input" class="form-field-label">
                            <span>Confirm New Password</span>
                        </label>
                        <div class="input-password-wrap">
                            <input type="password" id="confirm_password_input" name="confirm_password" placeholder="Re-enter new password" minlength="6" class="form-input-field">
                            <button type="button" class="btn-toggle-eye" data-target="confirm_password_input" aria-label="Toggle password visibility">
                                <?= staff_icon('eye'); ?>
                            </button>
                        </div>
                    </div>
                </div>
                <p class="form-help-hint">Passwords must be at least 6 characters. Leave blank if you don't wish to change your password.</p>
            </div>
            
            <div class="account-modal-footer">
                <button type="button" class="ghost-btn" id="cancelAccountModalBtn">Cancel</button>
                <button type="submit" class="primary-btn teal-btn">
                    <?= staff_icon('check'); ?>
                    <span>Save Account Details</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- UNATTENDED RECORDS AUDIT MODAL -->
<div class="unattended-modal-backdrop" id="unattendedModal" hidden>
    <div class="unattended-dialog-card">
        <div class="unattended-modal-header">
            <div class="unattended-header-left">
                <div class="unattended-badge-wrap">
                    <span class="unattended-alert-dot"></span>
                    <span class="unattended-eyebrow">Station Compliance &amp; Operations Audit</span>
                </div>
                <h3 id="unattendedModalTitle" class="unattended-modal-title">Unattended Records Inspection</h3>
                <p class="unattended-modal-subtitle">Review past appointment requests and queue entries requiring staff follow-up</p>
            </div>
            <button type="button" class="unattended-close-btn" id="closeUnattendedModalBtn" onclick="closeUnattendedModal()" title="Close modal" aria-label="Close modal">
                <?= staff_icon('x'); ?>
            </button>
        </div>

        <div class="unattended-modal-body">
            <!-- Modal Tabs -->
            <div class="unattended-tab-nav">
                <button type="button" class="unattended-tab-btn active" id="unattendedTabAppts" data-tab="appts">
                    <?= staff_icon('clock'); ?>
                    <span>Unattended Requests (<?= count($unattendedApptsList); ?>)</span>
                </button>
                <button type="button" class="unattended-tab-btn" id="unattendedTabQueue" data-tab="queue">
                    <?= staff_icon('users'); ?>
                    <span>Unserved Queue (<?= count($unattendedQueueList); ?>)</span>
                </button>
            </div>

            <!-- TAB 1: Unattended Appointment Requests -->
            <div class="unattended-tab-pane" id="paneUnattendedAppts">
                <div class="unattended-info-banner amber">
                    <?= staff_icon('alert'); ?>
                    <div>
                        <strong>Staff Action Missed:</strong> These patient appointments reached and passed their scheduled date without being confirmed or cancelled by station staff.
                    </div>
                </div>

                <div class="unattended-search-row">
                    <div class="unattended-search-wrap">
                        <?= staff_icon('search'); ?>
                        <input type="text" class="unattended-search-input" id="searchUnattendedAppts" placeholder="Filter unattended requests by patient name, ref code, or service...">
                    </div>
                </div>

                <div class="unattended-records-stack" id="stackUnattendedAppts">
                    <?php if (empty($unattendedApptsList)): ?>
                        <div class="appt-empty-box">
                            <div class="appt-empty-icon" style="background:#ecfdf5;color:#059669;"><?= staff_icon('check'); ?></div>
                            <h4>No Unattended Appointment Requests</h4>
                            <p>All past appointment requests have been properly confirmed or resolved by staff.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($unattendedApptsList as $item): ?>
                            <div class="unattended-item-card" data-search="<?= h(strtolower(full_name($item) . ' ' . ($item['reference_code'] ?? '') . ' ' . ($item['appointment_code'] ?? '') . ' ' . ($item['service_name'] ?? ''))); ?>">
                                <div class="unattended-item-left">
                                    <div class="unattended-avatar" style="background:#fef3c7;color:#92400e;border-color:#fde68a;">
                                        <?= strtoupper(substr((string) ($item['first_name'] ?? 'P'), 0, 1)); ?>
                                    </div>
                                    <div class="unattended-item-info">
                                        <div class="unattended-item-title-row">
                                            <h4><?= h(full_name($item)); ?></h4>
                                            <span class="unattended-tag-missed">Missed Confirmation</span>
                                            <span class="pill-badge pill-purple" style="font-size:0.75rem;"><?= h((string) $item['service_name']); ?></span>
                                        </div>
                                        <div class="unattended-item-meta">
                                            <span><?= staff_icon('calendar'); ?> Booked Date: <strong><?= h(date('M j, Y', strtotime((string) $item['preferred_date']))); ?></strong> (<?= h((string) $item['preferred_time']); ?>)</span>
                                            <span><?= staff_icon('phone'); ?> <?= h((string) $item['contact_number']); ?></span>
                                            <?php if (!empty($item['reference_code'])): ?>
                                                <span>Ref: <code><?= h((string) $item['reference_code']); ?></code></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="unattended-item-actions">
                                    <?php if (!empty($item['patient_id'])): ?>
                                        <a href="?page=patients&patient_search=<?= urlencode((string) $item['patient_id']); ?>&view_patient_id=<?= urlencode((string) $item['patient_id']); ?>" class="ghost-btn" style="padding:6px 12px;font-size:0.8rem;">
                                            <?= staff_icon('user'); ?> Profile
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- TAB 2: Unserved Queue -->
            <div class="unattended-tab-pane" id="paneUnattendedQueue" style="display:none;">
                <div class="unattended-info-banner orange">
                    <?= staff_icon('alert'); ?>
                    <div>
                        <strong>Patient No-Show or Left Unserved:</strong> These patients had confirmed appointments or entered the station queue, but the scheduled consultation day concluded without completion.
                    </div>
                </div>

                <div class="unattended-search-row">
                    <div class="unattended-search-wrap">
                        <?= staff_icon('search'); ?>
                        <input type="text" class="unattended-search-input" id="searchUnattendedQueue" placeholder="Filter unserved queue by patient name, ref code, or service...">
                    </div>
                </div>

                <div class="unattended-records-stack" id="stackUnattendedQueue">
                    <?php if (empty($unattendedQueueList)): ?>
                        <div class="appt-empty-box">
                            <div class="appt-empty-icon" style="background:#ecfdf5;color:#059669;"><?= staff_icon('check'); ?></div>
                            <h4>No Unserved Queue Entries</h4>
                            <p>All queued patients for past consultation dates were successfully served or attended to.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($unattendedQueueList as $item): ?>
                            <div class="unattended-item-card" data-search="<?= h(strtolower(full_name($item) . ' ' . ($item['reference_code'] ?? '') . ' ' . ($item['appointment_code'] ?? '') . ' ' . ($item['service_name'] ?? ''))); ?>">
                                <div class="unattended-item-left">
                                    <div class="unattended-avatar" style="background:#ffedd5;color:#9a3412;border-color:#fed7aa;">
                                        <?= strtoupper(substr((string) ($item['first_name'] ?? 'P'), 0, 1)); ?>
                                    </div>
                                    <div class="unattended-item-info">
                                        <div class="unattended-item-title-row">
                                            <h4><?= h(full_name($item)); ?></h4>
                                            <span class="unattended-tag-noshow">No-Show / Unserved</span>
                                            <span class="pill-badge pill-purple" style="font-size:0.75rem;"><?= h((string) $item['service_name']); ?></span>
                                            <?php if (!empty($item['appointment_code'])): ?>
                                                <span class="pill-badge pill-green" style="font-size:0.75rem;">#<?= h((string) $item['appointment_code']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="unattended-item-meta">
                                            <span><?= staff_icon('calendar'); ?> Scheduled: <strong><?= h(date('M j, Y', strtotime((string) $item['preferred_date']))); ?></strong> (<?= h((string) $item['preferred_time']); ?>)</span>
                                            <span><?= staff_icon('phone'); ?> <?= h((string) $item['contact_number']); ?></span>
                                            <span>Original Status: <strong><?= h((string) $item['original_status']); ?></strong></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="unattended-item-actions">
                                    <?php if (!empty($item['patient_id'])): ?>
                                        <a href="?page=patients&patient_search=<?= urlencode((string) $item['patient_id']); ?>&view_patient_id=<?= urlencode((string) $item['patient_id']); ?>" class="ghost-btn" style="padding:6px 12px;font-size:0.8rem;">
                                            <?= staff_icon('user'); ?> Profile
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="unattended-modal-footer">
            <div class="unattended-footer-note">
                <?= staff_icon('check'); ?> Auto-synchronized across Barangay Health Station records
            </div>
            <button type="button" class="primary-btn teal-btn" id="closeUnattendedModalBtn2" onclick="closeUnattendedModal()" style="padding:9px 22px;border-radius:12px;">
                <span>Done Reviewing</span>
            </button>
        </div>
    </div>
</div>

<!-- Unattended Modal Script -->
<script>
(function() {
    'use strict';
    const tabAppts = document.getElementById('unattendedTabAppts');
    const tabQueue = document.getElementById('unattendedTabQueue');
    const paneAppts = document.getElementById('paneUnattendedAppts');
    const paneQueue = document.getElementById('paneUnattendedQueue');
    const searchAppts = document.getElementById('searchUnattendedAppts');
    const searchQueue = document.getElementById('searchUnattendedQueue');
    const stackAppts = document.getElementById('stackUnattendedAppts');
    const stackQueue = document.getElementById('stackUnattendedQueue');

    function switchTab(tabName) {
        if (tabName === 'queue') {
            if (tabQueue) tabQueue.classList.add('active');
            if (tabAppts) tabAppts.classList.remove('active');
            if (paneQueue) paneQueue.style.display = 'block';
            if (paneAppts) paneAppts.style.display = 'none';
        } else {
            if (tabAppts) tabAppts.classList.add('active');
            if (tabQueue) tabQueue.classList.remove('active');
            if (paneAppts) paneAppts.style.display = 'block';
            if (paneQueue) paneQueue.style.display = 'none';
        }
    }

    window.openUnattendedModal = function(defaultTab) {
        const modal = document.getElementById('unattendedModal');
        if (!modal) return;
        switchTab(defaultTab || 'appts');
        modal.removeAttribute('hidden');
        document.body.style.overflow = 'hidden';
    };

    window.closeUnattendedModal = function() {
        const modal = document.getElementById('unattendedModal');
        if (!modal) return;
        modal.setAttribute('hidden', '');
        document.body.style.overflow = '';
    };

    document.addEventListener('click', function(e) {
        const cardAppts = e.target.closest('#cardUnattendedAppts');
        const cardQueue = e.target.closest('#cardUnattendedQueue');
        const closeBtn = e.target.closest('#closeUnattendedModalBtn, #closeUnattendedModalBtn2');
        const tabBtn = e.target.closest('.unattended-tab-btn');

        if (cardAppts) {
            e.preventDefault();
            window.openUnattendedModal('appts');
            return;
        }
        if (cardQueue) {
            e.preventDefault();
            window.openUnattendedModal('queue');
            return;
        }
        if (closeBtn) {
            e.preventDefault();
            window.closeUnattendedModal();
            return;
        }
        if (tabBtn) {
            e.preventDefault();
            const tab = tabBtn.getAttribute('data-tab') || 'appts';
            switchTab(tab);
            return;
        }
        const modal = document.getElementById('unattendedModal');
        if (modal && e.target === modal) {
            window.closeUnattendedModal();
        }
    });

    window.addEventListener('keydown', (e) => {
        const modal = document.getElementById('unattendedModal');
        if (e.key === 'Escape' && modal && !modal.hasAttribute('hidden')) {
            window.closeUnattendedModal();
        }
    });

    // Auto-open modal if URL specifies ?view_unattended=...
    if (window.location.search.includes('view_unattended=queue')) {
        window.openUnattendedModal('queue');
    } else if (window.location.search.includes('view_unattended=')) {
        window.openUnattendedModal('appts');
    }

    // Realtime search filters
    function filterStack(inputEl, stackEl) {
        if (!inputEl || !stackEl) return;
        inputEl.addEventListener('input', function() {
            const query = this.value.trim().toLowerCase();
            const cards = stackEl.querySelectorAll('.unattended-item-card');
            cards.forEach(card => {
                const searchData = (card.getAttribute('data-search') || '').toLowerCase();
                card.style.display = (query === '' || searchData.includes(query)) ? 'flex' : 'none';
            });
        });
    }

    filterStack(searchAppts, stackAppts);
    filterStack(searchQueue, stackQueue);
})();
</script>

<script>
(function() {
    'use strict';

    const accountModal = document.getElementById('accountModal');
    const openBtns = [
        document.getElementById('topbarUserChip'),
        document.getElementById('sidebarOpenAccountBtn')
    ].filter(Boolean);

    const closeBtn = document.getElementById('closeAccountModalBtn');
    const cancelBtn = document.getElementById('cancelAccountModalBtn');

    function openAccountModal() {
        if (!accountModal) return;
        accountModal.removeAttribute('hidden');
        document.body.style.overflow = 'hidden';
        const nameInput = document.getElementById('staff_name_input');
        if (nameInput) {
            setTimeout(() => nameInput.focus(), 50);
        }
    }

    function closeAccountModal() {
        if (!accountModal) return;
        accountModal.setAttribute('hidden', '');
        document.body.style.overflow = '';
    }

    openBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            openAccountModal();
        });
    });

    if (closeBtn) {
        closeBtn.addEventListener('click', (e) => {
            e.preventDefault();
            closeAccountModal();
        });
    }

    if (cancelBtn) {
        cancelBtn.addEventListener('click', (e) => {
            e.preventDefault();
            closeAccountModal();
        });
    }

    if (accountModal) {
        accountModal.addEventListener('click', (e) => {
            if (e.target === accountModal) {
                closeAccountModal();
            }
        });
    }

    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && accountModal && !accountModal.hasAttribute('hidden')) {
            closeAccountModal();
        }
    });

    // Password visibility toggle
    document.querySelectorAll('.btn-toggle-eye').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = btn.getAttribute('data-target');
            const targetInput = document.getElementById(targetId);
            if (targetInput) {
                const isPassword = targetInput.type === 'password';
                targetInput.type = isPassword ? 'text' : 'password';
                btn.classList.toggle('active', isPassword);
            }
        });
    });

    // Quick Presets for Follow-up Date Modal
    document.querySelectorAll('.preset-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const days = parseInt(this.getAttribute('data-days'), 10) || 7;
            const targetDate = new Date();
            targetDate.setDate(targetDate.getDate() + days);
            const yyyy = targetDate.getFullYear();
            const mm = String(targetDate.getMonth() + 1).padStart(2, '0');
            const dd = String(targetDate.getDate()).padStart(2, '0');
            const dateInput = document.getElementById('modal_follow_up_date');
            if (dateInput) {
                dateInput.value = `${yyyy}-${mm}-${dd}`;
            }
        });
    });
})();
</script>

<!-- Image Capture Studio Script -->
<script>
(function() {
    'use strict';

    const video = document.getElementById('imageCaptureVideo');
    const canvas = document.getElementById('imageCaptureCanvas');
    const preview = document.getElementById('imageCapturePreview');
    const hiddenDataInput = document.getElementById('captured_photo_data');
    const btnStart = document.getElementById('btnStartCamera');
    const btnStop = document.getElementById('btnStopCamera');
    const btnSnap = document.getElementById('btnSnapPhoto');
    const btnRetake = document.getElementById('btnRetakePhoto');
    const cameraToolbar = document.getElementById('cameraToolbar');
    const cameraOffPlaceholder = document.getElementById('cameraOffPlaceholder');
    const cameraOffMsg = document.getElementById('cameraOffMsg');
    const cameraStatusBadge = document.getElementById('cameraStatusBadge');
    const cameraStatusText = document.getElementById('cameraStatusText');
    const capturedConfirmPanel = document.getElementById('capturedConfirmPanel');
    const shutterFlash = document.getElementById('shutterFlashOverlay');
    const cameraDeviceSelectBar = document.getElementById('cameraDeviceSelectBar');
    const cameraDeviceSelect = document.getElementById('cameraDeviceSelect');

    let currentStream = null;
    let isStartingCamera = false;
    let selectedDeviceId = localStorage.getItem('staff_pc_webcam_device_id') || '';
    let knownVideoDevices = [];

    function isPhoneOrVirtualCamera(label) {
        const l = (label || '').toLowerCase();
        return (
            l.includes('phone') ||
            l.includes('android') ||
            l.includes('iphone') ||
            l.includes('link to windows') ||
            l.includes('phone link') ||
            l.includes('windows virtual') ||
            l.includes('droidcam') ||
            l.includes('iriun') ||
            l.includes('epoccam') ||
            l.includes('camo') ||
            l.includes('ipwebcam') ||
            l.includes('continuity') ||
            l.includes('virtual') ||
            l.includes('wireless') ||
            l.includes('cellular')
        );
    }

    async function enumerateCameraDevices() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.enumerateDevices) return [];
        try {
            const devices = await navigator.mediaDevices.enumerateDevices();
            const videoDevices = devices.filter(d => d.kind === 'videoinput');

            // Strictly filter out any phone link or virtual camera
            const physicalAttached = [];
            videoDevices.forEach((dev, idx) => {
                const label = dev.label || '';
                if (!isPhoneOrVirtualCamera(label)) {
                    physicalAttached.push({
                        deviceId: dev.deviceId,
                        label: label || `Attached Camera ${idx + 1}`
                    });
                }
            });

            // Only use strictly physical attached cameras
            knownVideoDevices = physicalAttached;

            if (knownVideoDevices.length > 0) {
                const hasCurrent = knownVideoDevices.some(d => d.deviceId === selectedDeviceId);
                if (!hasCurrent) {
                    selectedDeviceId = knownVideoDevices[0].deviceId;
                    localStorage.setItem('staff_pc_webcam_device_id', selectedDeviceId);
                }
            } else {
                selectedDeviceId = '';
            }

            // Populate selector UI (strictly physical attached cameras only)
            if (cameraDeviceSelect) {
                cameraDeviceSelect.innerHTML = '';
                knownVideoDevices.forEach((dev, i) => {
                    const opt = document.createElement('option');
                    opt.value = dev.deviceId;
                    opt.textContent = `📷 ${dev.label || 'Attached Webcam ' + (i + 1)}`;
                    if (dev.deviceId === selectedDeviceId) {
                        opt.selected = true;
                    }
                    cameraDeviceSelect.appendChild(opt);
                });

                if (cameraDeviceSelectBar) {
                    cameraDeviceSelectBar.style.display = knownVideoDevices.length > 1 ? 'flex' : 'none';
                }
            }

            return knownVideoDevices;
        } catch (e) {
            console.warn('Unable to enumerate camera devices:', e);
            return [];
        }
    }

    function resetStandbyPlaceholder() {
        if (cameraOffPlaceholder) {
            cameraOffPlaceholder.innerHTML = `
                <span class="cam-icon-big"><?= staff_icon('camera'); ?></span>
                <p id="cameraOffMsg">Live Camera Standby • In-Person Station Capture</p>
                <div class="cam-placeholder-actions">
                    <button type="button" class="primary-btn blue-btn" id="btnStartCamera">
                        <?= staff_icon('camera'); ?>
                        <span>Turn On Live Camera</span>
                    </button>
                </div>
            `;
            attachPlaceholderListeners();
        }
    }

    function attachPlaceholderListeners() {
        const startBtn = document.getElementById('btnStartCamera');
        if (startBtn) {
            startBtn.addEventListener('click', (e) => {
                e.preventDefault();
                startCamera();
            });
        }
    }

    async function getMediaStream() {
        // Build constraints targeting physical attached camera (no facingMode to prevent phone link hijacking)
        const constraintTiers = [];

        if (selectedDeviceId) {
            constraintTiers.push({
                video: {
                    deviceId: { exact: selectedDeviceId },
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                },
                audio: false
            });
            constraintTiers.push({
                video: {
                    deviceId: { exact: selectedDeviceId }
                },
                audio: false
            });
            constraintTiers.push({
                video: {
                    deviceId: { ideal: selectedDeviceId },
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                },
                audio: false
            });
        }

        // Generic PC webcam constraints (without facingMode)
        constraintTiers.push(
            { video: { width: { ideal: 1280 }, height: { ideal: 720 } }, audio: false },
            { video: true, audio: false }
        );

        let lastErr = null;
        for (const constraints of constraintTiers) {
            try {
                const stream = await navigator.mediaDevices.getUserMedia(constraints);
                if (stream) return stream;
            } catch (err) {
                lastErr = err;
                console.warn('Camera constraint tier failed:', constraints, err);
            }
        }
        throw lastErr || new Error('Unable to acquire camera stream from attached webcam.');
    }

    async function startCamera() {
        if (isStartingCamera) return;
        isStartingCamera = true;

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            alert('Webcam access is not supported or not allowed on this browser context. Please ensure you are using HTTPS or localhost.');
            isStartingCamera = false;
            return;
        }

        stopCamera();

        if (cameraOffPlaceholder) {
            cameraOffPlaceholder.style.display = 'flex';
            cameraOffPlaceholder.innerHTML = `
                <div class="cam-loading-spinner"></div>
                <p style="font-weight:700;color:#38bdf8;">Connecting to attached camera...</p>
                <small style="color:#94a3b8;font-size:0.82rem;text-align:center;">Please click Allow if your browser asks for camera permission</small>
            `;
        }
        if (cameraStatusBadge) {
            cameraStatusBadge.classList.add('is-live');
            if (cameraStatusText) cameraStatusText.textContent = 'Connecting...';
        }

        try {
            currentStream = await getMediaStream();

            if (video) {
                video.setAttribute('autoplay', '');
                video.setAttribute('playsinline', '');
                video.muted = true;
                video.style.display = 'block';
                video.srcObject = currentStream;

                await new Promise((resolve) => {
                    if (video.readyState >= 2) {
                        resolve();
                    } else {
                        video.onloadedmetadata = () => resolve();
                        video.oncanplay = () => resolve();
                        setTimeout(resolve, 1500);
                    }
                });

                try {
                    await video.play();
                } catch (playErr) {
                    console.warn('Video play warning:', playErr);
                }
            }

            // Once stream is open, enumerate devices to inspect labels and verify we are not on a phone camera
            const devices = await enumerateCameraDevices();
            const activeTrack = currentStream ? currentStream.getVideoTracks()[0] : null;
            const activeLabel = activeTrack ? activeTrack.label : '';
            const activeDevId = activeTrack && activeTrack.getSettings ? activeTrack.getSettings().deviceId : '';

            // If the active camera is a phone/virtual camera, but we have an attached physical camera available, switch to it!
            if (isPhoneOrVirtualCamera(activeLabel) && devices.length > 0 && activeDevId !== devices[0].deviceId) {
                console.info('Switching from virtual/phone camera to physical attached camera:', devices[0].label);
                selectedDeviceId = devices[0].deviceId;
                localStorage.setItem('staff_pc_webcam_device_id', selectedDeviceId);
                isStartingCamera = false;
                return startCamera();
            }

            if (activeDevId) {
                selectedDeviceId = activeDevId;
                localStorage.setItem('staff_pc_webcam_device_id', selectedDeviceId);
                if (cameraDeviceSelect) {
                    cameraDeviceSelect.value = selectedDeviceId;
                }
            }

            if (cameraOffPlaceholder) cameraOffPlaceholder.style.display = 'none';
            if (cameraToolbar) cameraToolbar.style.display = 'flex';
            if (cameraStatusBadge) {
                cameraStatusBadge.classList.add('is-live');
                if (cameraStatusText) cameraStatusText.textContent = '● LIVE ATTACHED CAMERA';
            }
        } catch (err) {
            console.error('Camera access error:', err);
            resetStandbyPlaceholder();
            if (cameraStatusBadge) {
                cameraStatusBadge.classList.remove('is-live');
                if (cameraStatusText) cameraStatusText.textContent = 'Camera Standby';
            }
            alert('Unable to open attached webcam (' + (err.name || err.message) + ').\n\nPlease check that your computer camera is plugged in, permitted in browser site permissions, and not in use by another application.');
        } finally {
            isStartingCamera = false;
        }
    }

    function stopCamera() {
        if (currentStream) {
            currentStream.getTracks().forEach(track => track.stop());
            currentStream = null;
        }
        if (video) {
            try { video.pause(); } catch(e) {}
            video.srcObject = null;
            video.style.display = 'none';
        }
        resetStandbyPlaceholder();
        if (cameraOffPlaceholder) cameraOffPlaceholder.style.display = 'flex';
        if (cameraToolbar) cameraToolbar.style.display = 'none';
        if (cameraStatusBadge) {
            cameraStatusBadge.classList.remove('is-live');
            if (cameraStatusText) cameraStatusText.textContent = 'Camera Standby';
        }
    }

    if (cameraDeviceSelect) {
        cameraDeviceSelect.addEventListener('change', (e) => {
            selectedDeviceId = e.target.value;
            localStorage.setItem('staff_pc_webcam_device_id', selectedDeviceId);
            if (currentStream) {
                startCamera();
            }
        });
    }

    if (btnStop) {
        btnStop.addEventListener('click', (e) => {
            e.preventDefault();
            stopCamera();
        });
    }

    // Shutter capture
    if (btnSnap && video && canvas) {
        btnSnap.addEventListener('click', (e) => {
            e.preventDefault();
            if (!video.videoWidth) {
                alert('Please wait for the camera feed to activate before capturing.');
                return;
            }

            // Visual Shutter Flash animation
            if (shutterFlash) {
                shutterFlash.classList.remove('shutter-flash-active');
                void shutterFlash.offsetWidth; // trigger reflow
                shutterFlash.classList.add('shutter-flash-active');
            }

            const vWidth = video.videoWidth;
            const vHeight = video.videoHeight;
            const size = Math.min(vWidth, vHeight);
            const startX = (vWidth - size) / 2;
            const startY = (vHeight - size) / 2;

            canvas.width = 600;
            canvas.height = 600;
            const ctx = canvas.getContext('2d');

            // Mirror capture so portrait matches the mirrored viewfinder
            ctx.save();
            ctx.scale(-1, 1);
            ctx.drawImage(video, startX, startY, size, size, -600, 0, 600, 600);
            ctx.restore();

            const dataUrl = canvas.toDataURL('image/jpeg', 0.92);
            setCapturedPhoto(dataUrl);
            stopCamera();
        });
    }

    if (btnRetake) {
        btnRetake.addEventListener('click', (e) => {
            e.preventDefault();
            if (hiddenDataInput) hiddenDataInput.value = '';
            if (preview) preview.src = '';
            if (capturedConfirmPanel) capturedConfirmPanel.style.display = 'none';
            startCamera();
        });
    }

    function setCapturedPhoto(dataUrl) {
        if (hiddenDataInput) hiddenDataInput.value = dataUrl;
        if (preview) preview.src = dataUrl;
        if (capturedConfirmPanel) {
            capturedConfirmPanel.style.display = 'block';
            capturedConfirmPanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    attachPlaceholderListeners();
    enumerateCameraDevices();

    window.addEventListener('beforeunload', () => {
        stopCamera();
    });
})();

// Standalone Image Capture Directory Filter Script (Zero scroll jump + Instant in-place filter)
window.applyCaptureFilter = function(btnElem, evt, filterType) {
    if (evt) {
        evt.preventDefault();
        evt.stopPropagation();
    }
    if (!filterType) return false;

    // Capture current exact scroll position
    const currentScrollY = window.pageYOffset || document.documentElement.scrollTop || window.scrollY || 0;

    // Toggle active class on filter chip buttons
    const filterChipBtns = document.querySelectorAll('#captureFilterChips .filter-chip-btn');
    filterChipBtns.forEach(b => b.classList.remove('active'));
    if (btnElem) {
        btnElem.classList.add('active');
    } else {
        const matchingBtn = document.querySelector(`#captureFilterChips .filter-chip-btn[data-capture-filter="${filterType}"]`);
        if (matchingBtn) matchingBtn.classList.add('active');
    }

    // Filter items in the grid
    const allItems = document.querySelectorAll('#captureAppointmentsGrid .capture-pat-item');
    const emptyBox = document.getElementById('captureGridEmptyState');
    const countElem = document.getElementById('captureDirectoryCount');
    let matchCount = 0;

    allItems.forEach(item => {
        const hasPhoto = item.getAttribute('data-has-photo') === '1';
        const isOngoing = item.getAttribute('data-is-ongoing') === '1';
        const isVerifiedToday = item.getAttribute('data-is-verified-today') === '1';
        let shouldShow = false;

        if (filterType === 'needs_photo') {
            shouldShow = !hasPhoto;
        } else if (filterType === 'ongoing') {
            shouldShow = isOngoing;
        } else if (filterType === 'verified') {
            shouldShow = isVerifiedToday;
        }

        if (shouldShow) {
            item.style.display = 'flex';
            matchCount++;
        } else {
            item.style.display = 'none';
        }
    });

    if (emptyBox) {
        emptyBox.style.display = matchCount === 0 ? 'block' : 'none';
    }
    if (countElem) {
        countElem.textContent = matchCount + ' appointment' + (matchCount !== 1 ? 's' : '') + ' listed in directory';
    }

    // Update select-patient-btn links to carry current filter
    document.querySelectorAll('.select-patient-btn[href]').forEach(a => {
        try {
            const rawHref = a.getAttribute('href') || a.href;
            const u = new URL(rawHref, window.location.href);
            u.searchParams.set('capture_filter', filterType);
            a.setAttribute('href', u.pathname + u.search);
        } catch(err) {}
    });

    // Update browser URL query string without reloading or scrolling
    try {
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('capture_filter', filterType);
        window.history.replaceState(null, '', currentUrl.pathname + currentUrl.search);
    } catch(err) {}

    // Lock scroll position instantly so the page never moves
    window.scrollTo({ top: currentScrollY, behavior: 'instant' });

    return false;
};

window.closeClinicalModal = function(e, returnUrl) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    const currentScrollY = window.pageYOffset || document.documentElement.scrollTop || window.scrollY || 0;
    const modals = document.querySelectorAll('#clinicalModalBackdrop, #viewClinicalModalBackdrop, #followUpModalBackdrop, #vitalsModalBackdrop');
    modals.forEach(m => {
        m.remove();
    });
    try {
        let targetUrl;
        if (returnUrl) {
            targetUrl = new URL(returnUrl, window.location.href);
        } else {
            targetUrl = new URL(window.location.href);
            targetUrl.searchParams.delete('appointment_record');
            targetUrl.searchParams.delete('appointment_remarks');
            targetUrl.searchParams.delete('appointment_view');
            targetUrl.searchParams.delete('appointment_followup');
            targetUrl.searchParams.delete('encode_vitals');
        }
        window.history.replaceState(null, '', targetUrl.pathname + targetUrl.search);
    } catch (err) {}
    window.scrollTo({ top: currentScrollY, behavior: 'instant' });
    return false;
};

// Preserve scroll position when opening records or modals across the station
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.report-record-btn, .remarks-btn, .view-medical-file-btn, .set-followup-btn, .queue-vitals-btn, .appt-action-btn, .queue-call-btn, .queue-done-btn, .confirm-save-btn, .select-patient-btn, .photo-req');
    if (btn) {
        sessionStorage.setItem('station_scroll_pos', window.scrollY);
        if (btn.classList.contains('select-patient-btn') && !btn.classList.contains('is-disabled') && !btn.classList.contains('is-active-badge') && !btn.classList.contains('is-completed-badge')) {
            sessionStorage.setItem('smooth_scroll_to_studio', '1');
        }
    }
    if (e.target && e.target.classList && e.target.classList.contains('account-modal-backdrop')) {
        if (e.target.id === 'clinicalModalBackdrop' || e.target.id === 'viewClinicalModalBackdrop' || e.target.id === 'followUpModalBackdrop' || e.target.id === 'vitalsModalBackdrop') {
            window.closeClinicalModal(e);
        }
    }
});

// Also preserve scroll position on ANY form submission within staff portal (except logout)
document.addEventListener('submit', function(e) {
    if (e.target && e.target.querySelector('input[name="action"][value="logout"]')) {
        return;
    }
    sessionStorage.setItem('station_scroll_pos', window.scrollY);
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const openModal = document.querySelector('#clinicalModalBackdrop, #viewClinicalModalBackdrop, #followUpModalBackdrop, #vitalsModalBackdrop');
        if (openModal) {
            window.closeClinicalModal(e);
        }
    }
});

// Restore saved scroll position after page load
(function() {
    if ('scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
    }
    const smoothStudio = sessionStorage.getItem('smooth_scroll_to_studio');
    const savedPos = sessionStorage.getItem('station_scroll_pos');

    if (smoothStudio === '1' && savedPos !== null) {
        sessionStorage.removeItem('smooth_scroll_to_studio');
        sessionStorage.removeItem('station_scroll_pos');
        const pos = parseInt(savedPos, 10);
        window.scrollTo({ top: pos, behavior: 'instant' });
        setTimeout(() => {
            const studioCard = document.querySelector('.studio-card') || document.querySelector('.capture-studio-layout') || document.querySelector('.capture-page-header');
            if (studioCard) {
                studioCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } else {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }, 50);
    } else if (savedPos !== null) {
        sessionStorage.removeItem('station_scroll_pos');
        const targetY = parseInt(savedPos, 10);
        window.scrollTo({ top: targetY, behavior: 'instant' });
        window.requestAnimationFrame(() => {
            window.scrollTo({ top: targetY, behavior: 'instant' });
        });
        setTimeout(() => {
            window.scrollTo({ top: targetY, behavior: 'instant' });
        }, 40);
    }
})();

function toggleDualDateFilter(clickedType, paramName, event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    const container = event ? event.target.closest('.dual-date-filter') : document.querySelector(`.dual-date-filter[data-param="${paramName}"]`);
    if (!container) return;

    let todayActive = container.getAttribute('data-today') === '1';
    let upcomingActive = container.getAttribute('data-upcoming') === '1';

    if (clickedType === 'today') {
        if (todayActive && upcomingActive) {
            todayActive = false;
        } else if (!todayActive && upcomingActive) {
            todayActive = true;
        } else if (todayActive && !upcomingActive) {
            todayActive = false;
            upcomingActive = true;
        } else {
            todayActive = true;
        }
    } else if (clickedType === 'upcoming') {
        if (todayActive && upcomingActive) {
            upcomingActive = false;
        } else if (todayActive && !upcomingActive) {
            upcomingActive = true;
        } else if (!todayActive && upcomingActive) {
            upcomingActive = false;
            todayActive = true;
        } else {
            upcomingActive = true;
        }
    }

    let nextVal = 'both';
    if (todayActive && !upcomingActive) {
        nextVal = 'today';
    } else if (!todayActive && upcomingActive) {
        nextVal = 'upcoming';
    } else {
        nextVal = 'both';
    }

    const form = container.closest('form');
    if (form) {
        let input = form.querySelector(`input[name="${paramName}"]`);
        if (!input) {
            input = document.createElement('input');
            input.type = 'hidden';
            input.name = paramName;
            form.appendChild(input);
        }
        input.value = nextVal;
        form.submit();
    } else {
        const url = new URL(window.location.href);
        url.searchParams.set(paramName, nextVal);
        window.location.href = url.toString();
    }
}

function toggleDualStatusFilter(clickedVal, otherVal, paramName, event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    const container = event ? event.target.closest('.dual-date-filter') : document.querySelector(`.dual-date-filter[data-param="${paramName}"]`);
    if (!container) return;

    const val1 = container.getAttribute('data-val1') || 'ongoing';
    const val2 = container.getAttribute('data-val2') || 'completed';

    let active1 = container.getAttribute('data-today') === '1';
    let active2 = container.getAttribute('data-upcoming') === '1';

    if (clickedVal === val1) {
        if (active1 && active2) {
            active1 = false;
        } else if (!active1 && active2) {
            active1 = true;
        } else if (active1 && !active2) {
            active1 = false;
            active2 = true;
        } else {
            active1 = true;
        }
    } else if (clickedVal === val2) {
        if (active1 && active2) {
            active2 = false;
        } else if (active1 && !active2) {
            active2 = true;
        } else if (!active1 && active2) {
            active2 = false;
            active1 = true;
        } else {
            active2 = true;
        }
    }

    let nextVal = 'both';
    if (active1 && !active2) {
        nextVal = val1;
    } else if (!active1 && active2) {
        nextVal = val2;
    } else {
        nextVal = 'both';
    }

    const form = container.closest('form');
    if (form) {
        let input = form.querySelector(`input[name="${paramName}"]`);
        if (!input) {
            input = document.createElement('input');
            input.type = 'hidden';
            input.name = paramName;
            form.appendChild(input);
        }
        input.value = nextVal;
        form.submit();
    } else {
        const url = new URL(window.location.href);
        url.searchParams.set(paramName, nextVal);
        window.location.href = url.toString();
    }
}

(function() {
    const flashBanner = document.querySelector('.flash-banner');
    if (flashBanner) {
        window.setTimeout(() => {
            flashBanner.style.transition = 'opacity 0.35s ease';
            flashBanner.style.opacity = '0';
            window.setTimeout(() => {
                if (flashBanner.parentNode) {
                    flashBanner.parentNode.removeChild(flashBanner);
                }
            }, 350);
        }, 5000);
    }

    // ── 5-SECOND BACKGROUND TABLE & QUEUE LIVE SYNC ──
    // Seamlessly updates tables, patient queues, and appointment statuses without reloading the whole page
    let isStaffSyncing = false;
    const staffSyncSelectors = [
        '.appt-detail-appointments',
        '.appt-metrics-grid',
        '.services-grid.queue-services-grid',
        '.queue-kanban-board',
        '.queue-cards-stack',
        '.patient-cards-stack',
        '.patient-search-result-box',
        '.reports-metrics-grid',
        '.reports-two-col-grid',
        '.reports-days-grid',
        '.reports-ledger-section',
        '.capture-appointments-grid',
        '.capture-kpi-grid',
        '.capture-filter-chips',
        '.dash-stat-grid',
        '.dash-overview-grid',
        '.dash-queue-section',
        '.modern-queue-grid',
        '.modern-events-grid'
    ];

    setInterval(async function () {
        if (isStaffSyncing) return;
        const isCameraActive = document.getElementById('cameraStatusBadge')?.classList.contains('is-live');
        const activeEl = document.activeElement;
        const isTyping = activeEl && (activeEl.tagName === 'INPUT' || activeEl.tagName === 'TEXTAREA' || activeEl.tagName === 'SELECT');
        const staffAccountModal = document.getElementById('staffAccountModal');
        const isModalOpen = staffAccountModal && staffAccountModal.classList.contains('open');
        const eventModal = document.getElementById('eventModal');
        const isEventModalOpen = eventModal && eventModal.classList.contains('open');
        const unattendedModal = document.getElementById('unattendedModal');
        const isUnattendedModalOpen = unattendedModal && !unattendedModal.hasAttribute('hidden');

        if (isCameraActive || isTyping || isModalOpen || isEventModalOpen || isUnattendedModalOpen) return;

        try {
            isStaffSyncing = true;
            const res = await fetch(window.location.href, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) return;
            const html = await res.text();
            const parser = new DOMParser();
            const newDoc = parser.parseFromString(html, 'text/html');

            staffSyncSelectors.forEach(selector => {
                const currentEls = document.querySelectorAll(selector);
                const newEls = newDoc.querySelectorAll(selector);
                if (currentEls.length > 0 && currentEls.length === newEls.length) {
                    currentEls.forEach((curEl, idx) => {
                        const newEl = newEls[idx];
                        if (newEl && curEl.innerHTML !== newEl.innerHTML) {
                            curEl.innerHTML = newEl.innerHTML;
                        }
                    });
                }
            });
        } catch (err) {
            console.debug('Staff live sync notice:', err);
        } finally {
            isStaffSyncing = false;
        }
    }, 5000);
})();

window.dismissStaffToast = function() {
    const toast = document.getElementById('staffFlashToast');
    if (toast) {
        toast.classList.add('hide-toast');
        setTimeout(() => {
            toast.remove();
        }, 350);
    }
};
(function() {
    const toast = document.getElementById('staffFlashToast');
    if (toast) {
        setTimeout(() => {
            window.dismissStaffToast();
        }, 5000);
    }
})();
</script>
</body>
</html>
