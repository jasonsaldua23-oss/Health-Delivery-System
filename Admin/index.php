<?php

declare(strict_types=1);

require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';
$contact = contact_details();
$serviceCatalog = service_catalog();
$stationPrograms = station_program_map();
$stations = station_catalog();

const ADMIN_LOGIN_EMAIL = 'admintest@gmail.com';
const ADMIN_PASSWORD_HASH = '$2y$10$ZYSFCaxq0ETDZAMlMRHW.eVQFoKAEAIguPEBayApiG29bSUmxRM4W';

if (!function_exists('h')) {
    function h(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('admin_icon')) {
    function admin_icon(string $name): string
    {
        $icons = [
            'logo' => '<svg viewBox="0 0 24 24"><path d="M6 3v6a4 4 0 0 0 8 0V3m-6 0h4m6 8a3 3 0 1 0 3 3v-4m-3 7a4 4 0 0 1-4 4h-1a4 4 0 0 1-4-4v-1" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'dashboard' => '<svg viewBox="0 0 24 24"><path d="M4 4h6v6H4V4Zm10 0h6v6h-6V4ZM4 14h6v6H4v-6Zm10 3h6m-6 3h6v-6h-6v6Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'patients' => '<svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2m18 0v-2a4 4 0 0 0-3-3.87M14 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0Zm7 14v-2a4 4 0 0 0-3-3.87M18 3a4 4 0 0 1 0 8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'appointments' => '<svg viewBox="0 0 24 24"><path d="M8 2v4m8-4v4M3 10h18M5 5h14a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'queue' => '<svg viewBox="0 0 24 24"><path d="M4 7h16M4 12h10M4 17h7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'activity' => '<svg viewBox="0 0 24 24"><path d="M3 12h4l2-4 4 8 2-4h4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'budget' => '<svg viewBox="0 0 24 24"><path d="M12 1v22M17 5.5A4 4 0 0 0 13 2h-2a4 4 0 0 0 0 8h2a4 4 0 0 1 0 8h-2a4 4 0 0 1-4-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'reports' => '<svg viewBox="0 0 24 24"><path d="M4 19V5m5 14V9m5 10V3m6 16H2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'users' => '<svg viewBox="0 0 24 24"><path d="M9 13a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-6 8a6 6 0 0 1 12 0m3-10h3m-1.5-1.5v3m-3.8 7.3 1.1 1.1 2.4-2.4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'shield' => '<svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'user' => '<svg viewBox="0 0 24 24"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="7" r="4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'user-outline' => '<svg viewBox="0 0 24 24"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="7" r="4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'user-add' => '<svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="9" cy="7" r="4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M19 8v6m3-3h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'eye' => '<svg viewBox="0 0 24 24"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Zm10 3a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'edit' => '<svg viewBox="0 0 24 24"><path d="m4 20 4.5-1 9.5-9.5-3.5-3.5L5 15.5 4 20Zm11-13 3.5 3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'dots' => '<svg viewBox="0 0 24 24"><path d="M12 5.5a1.5 1.5 0 1 0 0 .01M12 12a1.5 1.5 0 1 0 0 .01M12 18.5a1.5 1.5 0 1 0 0 .01" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'search' => '<svg viewBox="0 0 24 24"><path d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'clock' => '<svg viewBox="0 0 24 24"><path d="M12 8v5l3 2m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'arrow-left' => '<svg viewBox="0 0 24 24"><path d="M19 12H5m6-6-6 6 6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'arrow-right' => '<svg viewBox="0 0 24 24"><path d="M5 12h14m-5-5 5 5-5 5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'plus' => '<svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'download' => '<svg viewBox="0 0 24 24"><path d="M12 3v12m0 0 4-4m-4 4-4-4M5 21h14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'filter' => '<svg viewBox="0 0 24 24"><path d="M3 5h18l-7 8v6l-4-2v-4L3 5Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'map' => '<svg viewBox="0 0 24 24"><path d="M12 21s-6-5.33-6-11a6 6 0 1 1 12 0c0 5.67-6 11-6 11Zm0-8.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'phone' => '<svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 5.15 11.8 19.79 19.79 0 0 1 2.08 3.12 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'check' => '<svg viewBox="0 0 24 24"><path d="m5 12 5 5L20 7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'x' => '<svg viewBox="0 0 24 24"><path d="m6 6 12 12M18 6 6 18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'logout' => '<svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4m7 14 5-5-5-5m5 5H9" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'lock' => '<svg viewBox="0 0 24 24"><path d="M7 11V7a5 5 0 0 1 10 0v4m-12 0h14v10H5V11Zm7 4v2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'mail' => '<svg viewBox="0 0 24 24"><path d="M4 6h16v12H4V6Zm0 0 8 6 8-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'history' => '<svg viewBox="0 0 24 24"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8m0-5v5h5M12 7v5l4 2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',

            // Health Services Clinical Icons
            'syringe' => '<svg viewBox="0 0 24 24"><path d="m14 4 6 6M5 19l7.5-7.5M9.5 8.5 17 16M4 20l3-3M3 21l3-3" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="m8 16 9-9 3 3-9 9-3-3Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'baby' => '<svg viewBox="0 0 24 24"><path d="M9 12h.01M15 12h.01M10 16c.5.5 1.2.8 2 .8s1.5-.3 2-.8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="8" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 4v-2M9 4.5C9.5 3 10.5 2 12 2c1.5 0 2.5 1 3 2.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'heart' => '<svg viewBox="0 0 24 24"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'pulse' => '<svg viewBox="0 0 24 24"><path d="M22 12h-4l-3 9L9 3l-3 9H2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'stethoscope' => '<svg viewBox="0 0 24 24"><path d="M4.5 3v5a4.5 4.5 0 0 0 9 0V3M9 12.5a4.5 4.5 0 0 0 4.5 4.5H15a3 3 0 0 0 3-3v-2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="18" cy="10" r="2.5" fill="none" stroke="currentColor" stroke-width="2"/></svg>',
            'community' => '<svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="9" cy="7" r="4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'cube' => '<svg viewBox="0 0 24 24"><path d="M7 3C4.5 3 3 5 3 8c0 3.5 1.5 7 2.5 10 .8 2.5 2 3 3.5 3s2-2 3-2 1.5 2 3 2 2.7-.5 3.5-3c1-3 2.5-6.5 2.5-10 0-3-1.5-5-4-5-2 0-3.5 1.5-4.5 2C12.5 4.5 11 3 7 3Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'tooth' => '<svg viewBox="0 0 24 24"><path d="M7 3C4.5 3 3 5 3 8c0 3.5 1.5 7 2.5 10 .8 2.5 2 3 3.5 3s2-2 3-2 1.5 2 3 2 2.7-.5 3.5-3c1-3 2.5-6.5 2.5-10 0-3-1.5-5-4-5-2 0-3.5 1.5-4.5 2C12.5 4.5 11 3 7 3Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'capsule' => '<svg viewBox="0 0 24 24"><path d="m10.5 20.5-7-7a4.95 4.95 0 0 1 7-7l7 7a4.95 4.95 0 0 1-7 7Zm-3-10 9 9" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'calendar' => '<svg viewBox="0 0 24 24"><path d="M8 2v4m8-4v4M3 10h18M5 5h14a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="m9 16 2 2 4-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'sparkle' => '<svg viewBox="0 0 24 24"><path d="m12 3 1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8L12 3Zm7 10 .8 2.2L22 16l-2.2.8L19 19l-.8-2.2L16 16l2.2-.8L19 13ZM5 14l.8 2.2L8 17l-2.2.8L5 20l-.8-2.2L2 17l2.2-.8L5 14Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'trash' => '<svg viewBox="0 0 24 24"><path d="M3 6h18m-2 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m-6 5v6m4-6v6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'key' => '<svg viewBox="0 0 24 24"><path d="m21 2-2 2m-1.5 1.5L14 9l-4 4-2-2-4 4 2 2-2 2 2 2 4-4-2-2 4-4 3.5-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="7.5" cy="16.5" r="1.5" fill="none" stroke="currentColor" stroke-width="2"/></svg>',
        ];

        return $icons[$name] ?? ($icons['appointments'] ?? '');
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

if (!function_exists('age_label')) {
    function age_label(array $row): string
    {
        return h((string) ($row['age'] ?? 0)) . 'y / ' . h((string) ($row['gender'] ?? ''));
    }
}

if (!function_exists('status_class')) {
    function status_class(string $status): string
    {
        return strtolower(str_replace(' ', '-', $status));
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('verify_csrf')) {
    function verify_csrf(?string $token): bool
    {
        return is_string($token) && isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

if (!function_exists('is_admin_authenticated')) {
    function is_admin_authenticated(): bool
    {
        return !empty($_SESSION['admin_authenticated']) && is_string($_SESSION['admin_email'] ?? null);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (($_POST['action'] ?? '') === 'logout') && is_admin_authenticated()) {
    if (verify_csrf($_POST['csrf_token'] ?? null)) {
        unset($_SESSION['admin_authenticated'], $_SESSION['admin_email'], $_SESSION['admin_name']);
    }

    header('Location: ../Patients/index.php#portal');
    exit;
}

if (isset($_GET['ajax']) && $_GET['ajax'] === 'station_counts') {
    if (!is_admin_authenticated()) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(fetch_station_counts(), JSON_THROW_ON_ERROR);
    exit;
}

if (isset($_GET['ajax']) && $_GET['ajax'] === 'mark_notification_read') {
    if (!is_admin_authenticated()) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    $notificationId = (int) ($_GET['notification_id'] ?? 0);
    $success = $notificationId > 0 && mark_notification_as_read($notificationId);

    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['success' => $success]);
    exit;
}

if (!is_admin_authenticated()) {
    header('Location: ../Patients/index.php#portal');
    exit;
}

$page = $_GET['page'] ?? 'dashboard';
$stationView = trim((string) ($_GET['station'] ?? ''));
$status = trim((string) ($_GET['status'] ?? ''));
$search = trim((string) ($_GET['search'] ?? ''));
$dateFilter = trim((string) ($_GET['date'] ?? 'both'));
if ($dateFilter === '') {
    $dateFilter = 'both';
}
$programFilter = trim((string) ($_GET['program'] ?? ''));
$queueDateFilter = trim((string) ($_GET['queue_date'] ?? 'today'));
if ($queueDateFilter === '') {
    $queueDateFilter = 'today';
}
$eventStationFilter = trim((string) ($_GET['event_station'] ?? ''));
$adminQueueProgramFilter = trim((string) ($_GET['queue_program'] ?? ''));
$patientViewId = trim((string) ($_GET['patient'] ?? ''));
$patientViewAppointmentId = (int) ($_GET['patient_visit'] ?? 0);
$patientHistoryId = trim((string) ($_GET['patient_history'] ?? ''));
$patientServiceHistoryId = trim((string) ($_GET['service_history'] ?? ''));
$patientStationFilter = trim((string) ($_GET['patient_station'] ?? ''));
$patientGenderFilter = trim((string) ($_GET['patient_gender'] ?? ''));
$selectedAdminVisitId = (int) ($_GET['visit'] ?? 0);
$showUserModal = $page === 'users' && (($_GET['show_user_modal'] ?? '') === '1');
$serviceManagementStation = trim((string) ($_GET['station'] ?? ''));
$adminFlash = (string) ($_SESSION['admin_flash'] ?? '');
unset($_SESSION['admin_flash']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_id'], $_POST['new_status'])) {
    if (verify_csrf($_POST['csrf_token'] ?? null)) {
        $appointmentId = (int) $_POST['appointment_id'];
        $newStatus = trim((string) $_POST['new_status']);
        $appointment = fetch_appointment_by_id($appointmentId);

        if (is_array($appointment) && (string) ($appointment['station_slug'] ?? '') === 'city-health' && in_array($newStatus, ['Confirmed', 'Completed'], true)) {
            if (update_appointment_status($appointmentId, $newStatus, 'city-health')) {
                $_SESSION['admin_flash'] = $newStatus === 'Confirmed'
                    ? 'Appointment confirmed and moved to Queue Management.'
                    : 'Appointment completed and recorded in Patients.';
            }
        }

        if (is_array($appointment) && (string) ($appointment['station_slug'] ?? '') === 'city-health' && $newStatus === 'Serving') {
            if (update_appointment_status($appointmentId, $newStatus, 'city-health')) {
                $_SESSION['admin_flash'] = 'Patient is now being served.';
            }
        }
    }

    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (($_POST['action'] ?? '') === 'create_admin_account')) {
    if (verify_csrf($_POST['csrf_token'] ?? null)) {
        $created = create_admin_account([
            'admin_name' => trim((string) ($_POST['admin_name'] ?? '')),
            'office_name' => trim((string) ($_POST['office_name'] ?? 'Bacolod City Health Office')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'password' => (string) ($_POST['password'] ?? ''),
        ]);
        $_SESSION['admin_flash'] = $created
            ? 'Administrator account created successfully.'
            : 'Unable to create admin account. Please ensure all required fields are filled.';
    }

    header('Location: index.php?page=users');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (($_POST['action'] ?? '') === 'create_staff_account')) {
    if (verify_csrf($_POST['csrf_token'] ?? null)) {
        $created = save_staff_account([
            'station_slug' => trim((string) ($_POST['station_slug'] ?? '')),
            'staff_name' => trim((string) ($_POST['staff_name'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'password' => (string) ($_POST['password'] ?? ''),
        ]);
        $_SESSION['admin_flash'] = $created
            ? 'Station staff account saved successfully.'
            : 'Unable to create staff account. Please verify assigned station and credentials.';
    }

    header('Location: index.php?page=users');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (($_POST['action'] ?? '') === 'delete_user_account')) {
    if (verify_csrf($_POST['csrf_token'] ?? null)) {
        $role = trim((string) ($_POST['user_role'] ?? ''));
        $id = (int) ($_POST['user_id'] ?? 0);

        if ($id > 0) {
            if ($role === 'Admin') {
                $deleted = delete_admin_account($id);
                $_SESSION['admin_flash'] = $deleted
                    ? 'Administrator account removed successfully.'
                    : 'Cannot delete the primary administrator account.';
            } elseif ($role === 'Staff') {
                $deleted = delete_staff_account($id);
                $_SESSION['admin_flash'] = $deleted
                    ? 'Staff account removed successfully.'
                    : 'Unable to remove staff account.';
            }
        }
    }

    header('Location: index.php?page=users');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (($_POST['action'] ?? '') === 'save_station_services')) {
    if (verify_csrf($_POST['csrf_token'] ?? null)) {
        $stationSlug = trim((string) ($_POST['station_slug'] ?? ''));
        $serviceSlugs = is_array($_POST['services'] ?? null) ? $_POST['services'] : [];
        $_SESSION['admin_flash'] = save_station_service_selection($stationSlug, $serviceSlugs)
            ? 'Health station services updated.'
            : 'Unable to update services. Select at least one service.';
    }

    header('Location: index.php?page=services&station=' . urlencode((string) ($_POST['station_slug'] ?? '')));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array(($_POST['action'] ?? ''), ['update_station_capacity', 'update_service_capacity'], true)) {
    if (verify_csrf($_POST['csrf_token'] ?? null)) {
        $stationSlug = trim((string) ($_POST['station_slug'] ?? ''));
        $maxSlots = max(1, (int) ($_POST['max_slots'] ?? 200));

        if ($stationSlug !== '') {
            $updated = update_station_daily_capacity($stationSlug, $maxSlots);
            $_SESSION['admin_flash'] = $updated
                ? 'Station daily booking capacity updated to ' . number_format($maxSlots) . ' slots/day across all services.'
                : 'Unable to update maximum daily slots.';
        }
    }

    header('Location: index.php?page=services&station=' . urlencode((string) ($_POST['station_slug'] ?? '')));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (($_POST['action'] ?? '') === 'create_health_facility')) {
    if (verify_csrf($_POST['csrf_token'] ?? null)) {
        $barangay = trim((string) ($_POST['barangay'] ?? ''));
        $name = trim((string) ($_POST['facility_name'] ?? ''));
        $location = trim((string) ($_POST['location'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $hours = trim((string) ($_POST['hours'] ?? 'Monday - Saturday, 8:00 AM - 5:00 PM'));
        $color = trim((string) ($_POST['color'] ?? 'mint'));
        $services = is_array($_POST['services'] ?? null) ? $_POST['services'] : [];
        $capacities = is_array($_POST['capacities'] ?? null) ? $_POST['capacities'] : [];

        $servicesWithCapacities = [];
        foreach ($services as $srv) {
            $srvSlug = (string) $srv;
            $cap = isset($capacities[$srvSlug]) ? (int) $capacities[$srvSlug] : 200;
            $servicesWithCapacities[$srvSlug] = max(1, $cap);
        }

        if ($barangay !== '') {
            if ($name === '') {
                $name = $barangay . ' Barangay Health Station';
            }
            $created = create_health_facility([
                'barangay' => $barangay,
                'name' => $name,
                'location' => $location,
                'phone' => $phone,
                'hours' => $hours,
                'color' => $color,
            ], $servicesWithCapacities);

            $_SESSION['admin_flash'] = $created
                ? 'New health facility for Barangay ' . $barangay . ' added successfully.'
                : 'Unable to add health facility. Please verify inputs.';
        } else {
            $_SESSION['admin_flash'] = 'Barangay name is required.';
        }
    }

    header('Location: index.php?page=services');
    exit;
}

$stats = appointment_stats();
$stationCounts = fetch_station_counts('Pending');
$stationQueueCounts = fetch_station_queue_counts();
$patients = fetch_unique_patients($search, [
    'station_slug' => $patientStationFilter,
    'gender'       => $patientGenderFilter,
]);
$unreadNotifications = fetch_unread_patient_notifications();
$patientViewAppointment = $patientViewAppointmentId > 0 ? fetch_appointment_by_id($patientViewAppointmentId) : null;
$patientProfile = null;
if ($page === 'patients' && $patientViewId !== '') {
    $patientProfile = fetch_patient_profile($patientViewId);
} elseif ($page === 'patients' && is_array($patientViewAppointment)) {
    $patientProfile = fetch_patient_profile((string) ($patientViewAppointment['patient_id'] ?? ''));
}
$patientHistory = $patientHistoryId !== '' ? fetch_patient_info_history($patientHistoryId) : [];
if ($patientHistoryId !== '' && $patientHistory !== []) {
    foreach ($unreadNotifications as $notif) {
        if ((string) $notif['patient_id'] === $patientHistoryId) {
            mark_notification_as_read((int) $notif['id']);
        }
    }
}
$patientServiceHistoryProfile = $patientServiceHistoryId !== '' ? fetch_patient_profile($patientServiceHistoryId) : null;
$selectedAdminVisit = $selectedAdminVisitId > 0 ? fetch_appointment_by_id($selectedAdminVisitId) : null;
if ($selectedAdminVisit !== null && $patientProfile !== null) {
    $profileVisitIds = array_map(static fn(array $visit): int => (int) ($visit['id'] ?? 0), $patientProfile['visits']);
    if (!in_array((int) ($selectedAdminVisit['id'] ?? 0), $profileVisitIds, true)) {
        $selectedAdminVisit = null;
    }
}
$appointments = fetch_appointments(['station_slug' => $stationView, 'service_slug' => $programFilter, 'status' => $status, 'search' => $search, 'date' => $dateFilter]);
$allStationAppointments = $stationView !== '' ? fetch_appointments(['station_slug' => $stationView, 'date' => $dateFilter]) : [];
$upcomingEvents = fetch_upcoming_events(['upcoming_only' => true]);
$adminAccounts = fetch_admin_accounts();
$staffAccounts = fetch_staff_accounts();
$activities = recent_activity();
$weekly = weekly_chart_data();
$utilization = service_utilization_data();

$reportFrom = trim((string) ($_GET['report_from'] ?? ''));
$reportTo   = trim((string) ($_GET['report_to'] ?? ''));
$reportGender = trim((string) ($_GET['gender'] ?? ''));
$reportAgeGroup = trim((string) ($_GET['age_group'] ?? ''));
$reportStation = trim((string) ($_GET['station_slug'] ?? ''));
$reportService = trim((string) ($_GET['service_slug'] ?? ''));
$reportStatus = trim((string) ($_GET['status_filter'] ?? ''));

if ($reportFrom === '' || $reportTo === '') {
    $reportFrom = date('Y-m-01');
    $reportTo   = date('Y-m-d');
}

$reportFilters = [
    'report_from'   => $reportFrom,
    'report_to'     => $reportTo,
    'gender'        => $reportGender,
    'age_group'     => $reportAgeGroup,
    'station_slug'  => $reportStation,
    'service_slug'  => $reportService,
    'status'        => $reportStatus,
];

if ($page === 'reports' && (($_GET['export'] ?? '') === 'csv')) {
    $exportAppointments = fetch_filtered_report_appointments($reportFilters, 5000);
    $filename = 'health_report_' . date('Ymd_His') . '.csv';
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Appointment Code', 'Reference Code', 'Patient Name', 'Birth Date', 'Gender', 'Contact Number', 'Address', 'Barangay Health Center', 'Service', 'Date', 'Time', 'Status', 'Temperature', 'Pulse', 'Blood Pressure', 'Doctor Notes', 'Created At']);
    foreach ($exportAppointments as $row) {
        fputcsv($output, [
            $row['appointment_code'] ?: $row['reference_code'],
            $row['reference_code'],
            full_name($row),
            $row['birth_date'],
            $row['gender'],
            $row['contact_number'],
            $row['complete_address'],
            $row['station_name'],
            $row['service_name'],
            $row['preferred_date'],
            $row['preferred_time'],
            $row['status'],
            $row['body_temperature'] ?? '',
            $row['pulse_rate'] ?? '',
            $row['blood_pressure'] ?? '',
            $row['doctor_notes'] ?? '',
            $row['created_at'],
        ]);
    }
    fclose($output);
    exit;
}

$reportStats         = report_summary_stats($reportFilters);
$monthlyTrends       = monthly_trends_data($reportFilters);
$demographics        = demographics_breakdown_data($reportFilters);
$stationPerformance  = station_performance_data($reportFilters);
$servicePerformance  = service_performance_data($reportFilters);
$barangayCompletedStats = barangay_completed_analytics($reportFilters);
$reportAppointmentsList = fetch_filtered_report_appointments($reportFilters, 50);
$infoChangeLog       = patient_info_change_log(20);
$activityLog         = fetch_activity_log(30, $reportFrom, $reportTo);
$healthEventsSummary = health_events_summary();

$csrf = csrf_token();

$stationLookup = [];
foreach ($stations as $station) {
    $stationLookup[$station['slug']] = $station;
}

$queueStation = $stationLookup[$stationView] ?? null;
$adminQueueEntries = [];
$queueEntriesForStation = [];
if ($page === 'queue' && $stationView !== '') {
    $queueEntriesForStation = fetch_queue_entries(['station_slug' => $stationView, 'date' => $queueDateFilter, 'search' => $search]);
    if ($programFilter !== '') {
        $adminQueueEntries = array_values(array_filter(
            $queueEntriesForStation,
            static fn(array $item): bool => (string) ($item['service_slug'] ?? '') === $programFilter
        ));
    }
}

$filteredUpcomingEvents = array_values(array_filter(
    $upcomingEvents,
    static function (array $event) use ($eventStationFilter, $search): bool {
        if ($eventStationFilter !== '' && (string) ($event['station_slug'] ?? '') !== $eventStationFilter) {
            return false;
        }
        if ($search !== '') {
            $s = mb_strtolower($search);
            $title = mb_strtolower((string) ($event['title'] ?? ''));
            $desc = mb_strtolower((string) ($event['description'] ?? ''));
            $stName = mb_strtolower((string) ($event['station_name'] ?? ''));
            if (strpos($title, $s) === false && strpos($desc, $s) === false && strpos($stName, $s) === false) {
                return false;
            }
        }
        return true;
    }
));
$appointmentsPageRows = array_values(array_filter(
    $appointments,
    static fn(array $item): bool => in_array((string) ($item['status'] ?? ''), ['Pending', 'Cancelled'], true)
));
$cityHealthAppointments = fetch_appointments(['station_slug' => 'city-health']);
$cityHealthStation = $stationLookup['city-health'] ?? null;
$cityHealthCompletedToday = count(array_filter(
    $cityHealthAppointments,
    static fn(array $item): bool => (string) ($item['status'] ?? '') === 'Completed' && (string) ($item['preferred_date'] ?? '') === date('Y-m-d')
));

if ($page === 'reports' && isset($_GET['export']) && $_GET['export'] === 'csv') {
    $exportFrom = trim((string) ($_GET['report_from'] ?? date('Y-m-01')));
    $exportTo   = trim((string) ($_GET['report_to']   ?? date('Y-m-d')));
    $exportAppts = fetch_appointments(['date_from' => $exportFrom, 'date_to' => $exportTo]);
    if (empty($exportAppts)) {
        $allAppts = fetch_appointments([]);
        $exportAppts = array_filter($allAppts, static function (array $a) use ($exportFrom, $exportTo): bool {
            $d = (string) ($a['preferred_date'] ?? '');
            return $d >= $exportFrom && $d <= $exportTo;
        });
    }

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="health-report-' . $exportFrom . '-to-' . $exportTo . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Reference', 'Patient Name', 'Station', 'Service', 'Preferred Date', 'Status', 'Contact', 'Created At']);
    foreach ($exportAppts as $row) {
        fputcsv($out, [
            $row['reference_code'] ?? '',
            trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')),
            $row['station_name'] ?? '',
            $row['service_name'] ?? '',
            $row['preferred_date'] ?? '',
            $row['status'] ?? '',
            $row['contact_number'] ?? '',
            $row['created_at'] ?? '',
        ]);
    }
    fclose($out);
    exit;
}

if (!function_exists('peso')) {
    function peso(float $value): string
    {
        return 'PHP ' . number_format($value, 2);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/styles.css?v=<?= filemtime(__DIR__ . '/assets/styles.css'); ?>">
</head>
<body>
<div class="admin-shell">
    <aside class="sidebar">
        <div>
            <div class="sidebar-brand"><span class="brand-mark"><?= admin_icon('logo'); ?></span><div><strong>Bacolod City Health</strong><small>Admin Panel</small></div></div>
            <nav class="sidebar-nav">
                <a class="<?= $page === 'dashboard' ? 'active' : ''; ?>" href="?page=dashboard"><?= admin_icon('dashboard'); ?>Dashboard</a>
                <a class="<?= $page === 'patients' ? 'active' : ''; ?>" href="?page=patients"><?= admin_icon('patients'); ?>Patients</a>
                <a class="<?= $page === 'appointments' ? 'active' : ''; ?>" href="?page=appointments"><?= admin_icon('appointments'); ?>Appointments</a>
                <a class="<?= $page === 'queue' ? 'active' : ''; ?>" href="?page=queue"><?= admin_icon('queue'); ?>Queue Management</a>
                <a class="<?= $page === 'services' ? 'active' : ''; ?>" href="?page=services"><?= admin_icon('plus'); ?>Station Services</a>
                <a class="<?= $page === 'events' ? 'active' : ''; ?>" href="?page=events"><?= admin_icon('clock'); ?>Upcoming Events</a>
                <a class="<?= $page === 'users' ? 'active' : ''; ?>" href="?page=users"><?= admin_icon('users'); ?>User Management</a>
                <a class="<?= $page === 'reports' ? 'active' : ''; ?>" href="?page=reports"><?= admin_icon('reports'); ?>Reports</a>
            </nav>
        </div>
    </aside>
    <main class="main-content">
        <header class="admin-header">
            <div class="admin-header-left">
                <div class="admin-status-indicator">
                    <span class="live-pulse"></span>
                    <span class="live-text">City Health Network Online</span>
                </div>
                <div class="admin-date-badge"><?= date('l, F j, Y'); ?></div>
            </div>
            <div class="admin-header-actions">
                <div class="admin-user-badge">
                    <div class="admin-user-avatar">
                        <?= strtoupper(substr((string) ($_SESSION['admin_name'] ?? 'Admin'), 0, 1)); ?>
                    </div>
                    <div class="admin-user-info">
                        <strong><?= h((string) ($_SESSION['admin_name'] ?? 'Admin User')); ?></strong>
                        <span><?= h((string) ($_SESSION['admin_email'] ?? ADMIN_LOGIN_EMAIL)); ?></span>
                    </div>
                </div>
                <form method="post" class="logout-form">
                    <input type="hidden" name="action" value="logout">
                    <input type="hidden" name="csrf_token" value="<?= h($csrf); ?>">
                    <button type="submit" class="logout-button" title="Sign out of admin panel"><?= admin_icon('logout'); ?><span>Sign Out</span></button>
                </form>
            </div>
        </header>

        <?php if ($adminFlash !== ''): ?>
            <?php
            $isAdminFlashError = str_contains(strtolower($adminFlash), 'unable') || str_contains(strtolower($adminFlash), 'failed') || str_contains(strtolower($adminFlash), 'required') || str_contains(strtolower($adminFlash), 'error') || str_contains(strtolower($adminFlash), 'not match');
            ?>
            <div class="flash-toast-wrap" id="adminFlashToast">
                <div class="flash-banner <?= $isAdminFlashError ? 'error' : 'success'; ?>" role="alert">
                    <div class="flash-icon-box">
                        <?php if ($isAdminFlashError): ?>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                        <?php else: ?>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        <?php endif; ?>
                    </div>
                    <div class="flash-content">
                        <strong><?= $isAdminFlashError ? 'System Notice' : 'Admin Control Update'; ?></strong>
                        <p><?= h($adminFlash); ?></p>
                    </div>
                    <button type="button" class="flash-dismiss-btn" onclick="dismissAdminToast()" aria-label="Dismiss notification">×</button>
                    <div class="flash-progress-bar"><div class="flash-progress-fill"></div></div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($page === 'dashboard'): ?>
            <!-- Hero Welcome Card -->
            <section class="dash-hero-card">
                <div class="dash-hero-copy">
                    <span class="dash-hero-badge">Administration Control Panel</span>
                    <h1>Welcome back, <?= h((string) ($_SESSION['admin_name'] ?? 'Admin User')); ?> 👋</h1>
                    <p>Live health center operations, appointment volumes, and program utilization across Bacolod City.</p>
                </div>
                <div class="dash-hero-actions">
                    <a href="?page=services" class="dash-hero-btn primary"><?= admin_icon('plus'); ?> Add Health Center</a>
                    <a href="?page=queue" class="dash-hero-btn secondary"><?= admin_icon('queue'); ?> Live Queue</a>
                    <a href="?page=patients" class="dash-hero-btn secondary"><?= admin_icon('patients'); ?> View Patients</a>
                </div>
            </section>

            <!-- Metrics / KPI Stat Grid -->
            <section class="dash-stat-grid">
                <article class="dash-stat-card theme-emerald">
                    <div class="dash-stat-top">
                        <div class="dash-stat-icon"><?= admin_icon('patients'); ?></div>
                        <span class="dash-stat-tag">Completed Records</span>
                    </div>
                    <div class="dash-stat-body">
                        <h3><?= number_format($stats['total_patients']); ?></h3>
                        <p>Total Registered Patients</p>
                    </div>
                    <div class="dash-stat-footer">
                        <span>Verified patient profiles on record</span>
                    </div>
                </article>

                <article class="dash-stat-card theme-blue">
                    <div class="dash-stat-top">
                        <div class="dash-stat-icon"><?= admin_icon('appointments'); ?></div>
                        <span class="dash-stat-tag">Active Today</span>
                    </div>
                    <div class="dash-stat-body">
                        <h3><?= number_format($stats['appointments_today']); ?></h3>
                        <p>Today's Appointments</p>
                    </div>
                    <div class="dash-stat-footer">
                        <span>Pending, confirmed & queue active</span>
                    </div>
                </article>

                <article class="dash-stat-card theme-indigo">
                    <div class="dash-stat-top">
                        <div class="dash-stat-icon"><?= admin_icon('shield'); ?></div>
                        <span class="dash-stat-tag">Available Programs</span>
                    </div>
                    <div class="dash-stat-body">
                        <h3><?= number_format($stats['active_services']); ?></h3>
                        <p>Active Health Programs</p>
                    </div>
                    <div class="dash-stat-footer">
                        <span>Offered across all barangay stations</span>
                    </div>
                </article>

                <article class="dash-stat-card theme-amber">
                    <div class="dash-stat-top">
                        <div class="dash-stat-icon"><?= admin_icon('clock'); ?></div>
                        <span class="dash-stat-tag">Past 7 Days</span>
                    </div>
                    <div class="dash-stat-body">
                        <h3><?= number_format($stats['online_bookings']); ?></h3>
                        <p>Online Bookings</p>
                    </div>
                    <div class="dash-stat-footer">
                        <span>Recent patient portal submissions</span>
                    </div>
                </article>
            </section>

            <!-- Charts Section (2 Columns) -->
            <section class="dash-charts-grid">
                <!-- Weekly Traffic Card -->
                <article class="panel-card dash-chart-card">
                    <div class="dash-card-head">
                        <div>
                            <h3>Weekly Patient &amp; Appointment Traffic</h3>
                            <p>Daily volume of appointments booked vs completed patient visits</p>
                        </div>
                        <div class="dash-chart-legend">
                            <span class="legend-chip blue" title="Number of appointments booked on this day"><i class="dot"></i> Appointments Booked</span>
                            <span class="legend-chip green" title="Number of patients who completed their appointments on this day"><i class="dot"></i> Completed Visits</span>
                        </div>
                    </div>
                    <div class="dash-chart-wrapper">
                        <?php 
                            $maxVal = max(max($weekly['patients']), max($weekly['appointments']), 10);
                            $chartHeight = 250;
                            $chartWidth = 640;
                            $padding = 45;
                            $plotWidth = $chartWidth - $padding * 2;
                            $plotHeight = $chartHeight - $padding - 20;
                            $xStep = $plotWidth / (count($weekly['days']) - 1);
                            $yScale = $plotHeight / $maxVal;
                            
                            $patientsPoints = [];
                            $appointmentsPoints = [];
                            
                            foreach ($weekly['patients'] as $i => $val) {
                                $x = $padding + $i * $xStep;
                                $y = $chartHeight - $padding + 10 - $val * $yScale;
                                $patientsPoints[] = "$x,$y";
                            }
                            
                            foreach ($weekly['appointments'] as $i => $val) {
                                $x = $padding + $i * $xStep;
                                $y = $chartHeight - $padding + 10 - $val * $yScale;
                                $appointmentsPoints[] = "$x,$y";
                            }
                            
                            $patientsPath = implode(' ', $patientsPoints);
                            $appointmentsPath = implode(' ', $appointmentsPoints);
                            $firstX = $padding;
                            $lastX = $padding + (count($weekly['days']) - 1) * $xStep;
                            $baseY = $chartHeight - $padding + 10;
                            $apptsAreaPath = "M " . $appointmentsPoints[0] . " L " . implode(' L ', $appointmentsPoints) . " L {$lastX},{$baseY} L {$firstX},{$baseY} Z";
                            $patsAreaPath = "M " . $patientsPoints[0] . " L " . implode(' L ', $patientsPoints) . " L {$lastX},{$baseY} L {$firstX},{$baseY} Z";
                        ?>
                        <svg class="patient-traffic-chart" viewBox="0 0 <?= $chartWidth; ?> <?= $chartHeight; ?>">
                            <defs>
                                <linearGradient id="blueAreaGrad" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.16"/>
                                    <stop offset="100%" stop-color="#3b82f6" stop-opacity="0.0"/>
                                </linearGradient>
                                <linearGradient id="greenAreaGrad" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#10b981" stop-opacity="0.18"/>
                                    <stop offset="100%" stop-color="#10b981" stop-opacity="0.0"/>
                                </linearGradient>
                            </defs>

                            <!-- Horizontal Grid lines -->
                            <?php for ($i = 0; $i <= 4; $i++): ?>
                                <?php $lineY = $chartHeight - $padding + 10 - ($maxVal / 4) * $i * $yScale; ?>
                                <line x1="<?= $padding; ?>" y1="<?= $lineY; ?>" x2="<?= $chartWidth - $padding; ?>" y2="<?= $lineY; ?>" stroke="#e2e8f0" stroke-dasharray="3,3" stroke-width="1"/>
                                <text x="<?= $padding - 10; ?>" y="<?= $lineY + 4; ?>" text-anchor="end" font-size="11" font-weight="600" fill="#94a3b8"><?= (int)(($maxVal / 4) * $i); ?></text>
                            <?php endfor; ?>

                            <!-- Area Fills -->
                            <path d="<?= $apptsAreaPath; ?>" fill="url(#blueAreaGrad)"/>
                            <path d="<?= $patsAreaPath; ?>" fill="url(#greenAreaGrad)"/>

                            <!-- Polyline Lines -->
                            <!-- Blue Line: Appointments Booked -->
                            <polyline points="<?= $appointmentsPath; ?>" fill="none" stroke="#3b82f6" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <!-- Green Line: Completed Patient Visits -->
                            <polyline points="<?= $patientsPath; ?>" fill="none" stroke="#10b981" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>

                            <!-- Data dots for Appointments (Blue) -->
                            <?php foreach ($appointmentsPoints as $i => $point): ?>
                                <?php list($x, $y) = explode(',', $point); ?>
                                <circle cx="<?= $x; ?>" cy="<?= $y; ?>" r="5" fill="#ffffff" stroke="#3b82f6" stroke-width="2.5" style="cursor:pointer;transition:transform 0.15s ease;">
                                    <title><?= $weekly['appointments'][$i]; ?> Appointments Booked (<?= $weekly['days'][$i]; ?>)</title>
                                </circle>
                            <?php endforeach; ?>

                            <!-- Data dots for Completed Patients (Green) -->
                            <?php foreach ($patientsPoints as $i => $point): ?>
                                <?php list($x, $y) = explode(',', $point); ?>
                                <circle cx="<?= $x; ?>" cy="<?= $y; ?>" r="5" fill="#ffffff" stroke="#10b981" stroke-width="2.5" style="cursor:pointer;transition:transform 0.15s ease;">
                                    <title><?= $weekly['patients'][$i]; ?> Patients Completed (<?= $weekly['days'][$i]; ?>)</title>
                                </circle>
                            <?php endforeach; ?>

                            <!-- Day labels -->
                            <?php foreach ($weekly['days'] as $i => $day): ?>
                                <text x="<?= $padding + $i * $xStep; ?>" y="<?= $chartHeight - $padding + 32; ?>" text-anchor="middle" font-size="12" font-weight="600" fill="#64748b"><?= h($day); ?></text>
                            <?php endforeach; ?>
                        </svg>
                    </div>
                </article>

                <!-- Service Utilization Card -->
                <article class="panel-card dash-service-util-card">
                    <div class="dash-card-head">
                        <div>
                            <h3>Program Demand Breakdown</h3>
                            <p>Most requested healthcare services across centers</p>
                        </div>
                    </div>
                    <?php 
                        $totalReqs = array_sum($utilization['values']) ?: 1;
                    ?>
                    <div class="dash-service-bars">
                        <?php foreach ($utilization['labels'] as $i => $label): ?>
                            <?php 
                                $val = (int) ($utilization['values'][$i] ?? 0);
                                $pct = round(($val / $totalReqs) * 100);
                            ?>
                            <div class="dash-util-row">
                                <div class="dash-util-meta">
                                    <span class="dash-util-title"><?= h($label); ?></span>
                                    <span class="dash-util-count"><strong><?= $val; ?></strong> bookings (<?= $pct; ?>%)</span>
                                </div>
                                <div class="dash-util-track">
                                    <div class="dash-util-fill" style="width: <?= max(4, $pct); ?>%;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </article>
            </section>

            <!-- Bottom Row: Stations Quick Access & Recent Activity -->
            <section class="dash-bottom-grid">
                <!-- Health Stations Overview -->
                <article class="panel-card dash-stations-card">
                    <div class="dash-card-head">
                        <div>
                            <h3>Health Stations</h3>
                            <p>Barangay facilities and assigned services</p>
                        </div>
                        <a href="?page=services" class="dash-link-btn">View All Stations →</a>
                    </div>
                    <div class="dash-stations-list">
                        <?php 
                            $stationSample = array_slice(array_filter($stations, static fn(array $s): bool => $s['slug'] !== 'city-health'), 0, 4);
                        ?>
                        <?php foreach ($stationSample as $st): ?>
                            <div class="dash-station-item">
                                <div class="dash-station-info">
                                    <div class="dash-station-badge badge-<?= h($st['color']); ?>"><?= strtoupper(substr((string) $st['name'], 0, 1)); ?></div>
                                    <div class="dash-station-text">
                                        <strong><?= h($st['name']); ?></strong>
                                        <span><?= h($st['detail_location']); ?></span>
                                    </div>
                                </div>
                                <div class="dash-station-right">
                                    <span class="dash-prog-count"><?= count($st['programs']); ?> Services</span>
                                    <a href="?page=services&station=<?= h($st['slug']); ?>" class="dash-station-manage-btn" title="Manage Services"><?= admin_icon('edit'); ?> Manage</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </article>

                <!-- Recent Activity Feed -->
                <article class="panel-card dash-activity-card">
                    <div class="dash-card-head">
                        <div>
                            <h3>Recent Activity</h3>
                            <p>Live patient appointments and status changes</p>
                        </div>
                        <a href="?page=appointments" class="dash-link-btn">All Appointments →</a>
                    </div>
                    <div class="dash-activity-list">
                        <?php if ($activities === []): ?>
                            <div class="empty-state">No recent activity recorded yet.</div>
                        <?php else: ?>
                            <?php foreach (array_slice($activities, 0, 5) as $activity): ?>
                                <?php 
                                    $stClass = strtolower(str_replace(' ', '-', (string) ($activity['status'] ?? 'pending')));
                                ?>
                                <div class="dash-activity-row">
                                    <div class="dash-act-avatar <?= h($stClass); ?>">
                                        <?= admin_icon('user-outline'); ?>
                                    </div>
                                    <div class="dash-act-details">
                                        <div class="dash-act-line-1">
                                            <strong><?= h(full_name($activity)); ?></strong>
                                            <span class="status-pill status-<?= h($stClass); ?>"><?= h($activity['status']); ?></span>
                                        </div>
                                        <div class="dash-act-line-2">
                                            <span><?= h($activity['service_name']); ?></span>
                                            <em>•</em>
                                            <span><?= h($activity['station_name']); ?></span>
                                        </div>
                                    </div>
                                    <div class="dash-act-time">
                                        <?= h(date('M j, g:i A', strtotime((string) $activity['created_at']))); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </article>
            </section>
        <?php elseif ($page === 'patients' && ($patientViewId !== '' || $patientViewAppointmentId > 0)): ?>
            <?php 
                $patientFullName = $patientProfile !== null 
                    ? trim($patientProfile['first_name'] . ' ' . $patientProfile['middle_name'] . ' ' . $patientProfile['last_name'])
                    : 'Patient Details';
                $patientHistoryBaseQuery = $patientViewAppointmentId > 0 
                    ? 'patient_visit=' . h((string) $patientViewAppointmentId) 
                    : 'patient=' . h((string) ($patientProfile['patient_id'] ?? ''));
                $initials = $patientProfile !== null 
                    ? strtoupper(substr((string) ($patientProfile['first_name'] ?? 'P'), 0, 1) . substr((string) ($patientProfile['last_name'] ?? 'U'), 0, 1))
                    : 'PT';
            ?>
            <!-- Patient Detail Breadcrumb Bar -->
            <section class="patient-nav-breadcrumb-bar">
                <div class="patient-breadcrumb-left">
                    <a href="?page=patients" class="patient-breadcrumb-link">
                        <?= admin_icon('patients'); ?>
                        <span>Patients Directory</span>
                    </a>
                    <span class="breadcrumb-sep">/</span>
                    <span class="patient-breadcrumb-current"><?= h($patientFullName); ?></span>
                </div>
                <a class="patient-back-btn" href="?page=patients">
                    <?= admin_icon('arrow-left'); ?>
                    <span>Back to Patients</span>
                </a>
            </section>

            <?php if ($patientProfile === null): ?>
                <section class="panel-card empty-state" style="padding: 40px 20px;">
                    <h3>Patient Record Not Found</h3>
                    <p style="color:#64748b;margin-top:6px;">The requested patient record could not be loaded or may have been removed.</p>
                    <a href="?page=patients" class="dash-hero-btn primary" style="margin-top:16px;display:inline-flex;">Return to Directory</a>
                </section>
            <?php else: ?>
                <!-- Hero Patient Profile Card -->
                <section class="panel-card patient-hero-card">
                    <div class="patient-hero-main">
                        <div class="patient-hero-avatar">
                            <?php if (!empty($patientProfile['photo_path'])): ?>
                                <img src="../Patients/<?= h((string) $patientProfile['photo_path']); ?>" alt="<?= h($patientFullName); ?>">
                            <?php else: ?>
                                <span><?= h($initials); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="patient-hero-info">
                            <div class="patient-hero-name-row">
                                <h2><?= h($patientFullName); ?></h2>
                                <span class="patient-id-badge">ID: <?= h((string) $patientProfile['patient_id']); ?></span>
                            </div>
                            <div class="patient-hero-tags">
                                <span class="hero-tag-pill"><?= h((string) $patientProfile['age']); ?> yrs old</span>
                                <span class="hero-tag-pill"><?= h((string) $patientProfile['gender']); ?></span>
                                <span class="hero-tag-pill station"><?= admin_icon('map'); ?><?= h((string) ($patientProfile['visits'][0]['station_name'] ?? 'Bacolod City Health')); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="patient-hero-stats">
                        <div class="hero-stat-box">
                            <span class="hero-stat-label">Total Completed Visits</span>
                            <strong><?= count($patientProfile['visits']); ?> Consultations</strong>
                        </div>
                        <div class="hero-stat-box">
                            <span class="hero-stat-label">Last Consultation</span>
                            <strong><?= !empty($patientProfile['last_visit']) ? h(date('M j, Y', strtotime((string) $patientProfile['last_visit']))) : 'None'; ?></strong>
                        </div>
                    </div>
                </section>

                <!-- Two-Column Information & Clinical History Grid -->
                <section class="patient-detail-grid-modern">
                    <!-- Left Column: Personal & Contact Profile -->
                    <div class="patient-detail-left-col">
                        <article class="panel-card patient-info-section-card">
                            <div class="dash-card-head">
                                <div>
                                    <h3>Personal &amp; Contact Information</h3>
                                    <p>Registered personal details and verified address</p>
                                </div>
                            </div>
                            <div class="patient-info-list">
                                <div class="patient-info-item">
                                    <span class="info-label">Full Name</span>
                                    <strong class="info-value"><?= h($patientFullName); ?></strong>
                                </div>
                                <div class="patient-info-item">
                                    <span class="info-label">Date of Birth &amp; Age</span>
                                    <strong class="info-value"><?= !empty($patientProfile['birth_date']) ? h(date('F j, Y', strtotime((string) $patientProfile['birth_date']))) : 'N/A'; ?> (<?= h((string) $patientProfile['age']); ?> years old)</strong>
                                </div>
                                <div class="patient-info-item">
                                    <span class="info-label">Gender</span>
                                    <strong class="info-value"><?= h((string) $patientProfile['gender']); ?></strong>
                                </div>
                                <div class="patient-info-item">
                                    <span class="info-label">Primary Contact Number</span>
                                    <strong class="info-value" style="color:#2563eb;"><?= h((string) $patientProfile['contact_number']); ?></strong>
                                </div>
                                <div class="patient-info-item">
                                    <span class="info-label">Email Address</span>
                                    <strong class="info-value"><?= !empty($patientProfile['email']) ? h((string) $patientProfile['email']) : '<em style="color:#94a3b8;font-weight:normal;">No email provided</em>'; ?></strong>
                                </div>
                                <div class="patient-info-item full">
                                    <span class="info-label">Registered Residential Address</span>
                                    <strong class="info-value"><?= h((string) $patientProfile['complete_address']); ?></strong>
                                </div>
                            </div>

                            <!-- Historical Changes / Audit Trail -->
                            <?php if (!empty($patientProfile['address_history']) || !empty($patientProfile['contact_history'])): ?>
                                <div class="patient-updates-trail">
                                    <h4>Profile Update History</h4>
                                    <?php foreach (($patientProfile['address_history'] ?? []) as $history): ?>
                                        <div class="update-trail-row">
                                            <span class="trail-badge">Address</span>
                                            <div>
                                                <small>Previous Address:</small>
                                                <p><?= h((string) ($history['old_value'] ?? '')); ?></p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php foreach (($patientProfile['contact_history'] ?? []) as $history): ?>
                                        <div class="update-trail-row">
                                            <span class="trail-badge">Contact</span>
                                            <div>
                                                <small>Previous Contact Number:</small>
                                                <p><?= h((string) ($history['old_value'] ?? '')); ?></p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </article>
                    </div>

                    <!-- Right Column: Completed Consultations & Clinical Visits -->
                    <div class="patient-detail-right-col">
                        <article class="panel-card patient-visits-section-card">
                            <div class="dash-card-head">
                                <div>
                                    <h3>Completed Clinical Consultations</h3>
                                    <p>Historical medical check-ups and program visits</p>
                                </div>
                                <span class="dash-badge-count"><?= count($patientProfile['visits']); ?> Records</span>
                            </div>

                            <?php if ($patientProfile['visits'] === []): ?>
                                <div class="empty-state" style="padding:32px 16px;">
                                    No completed clinical consultations recorded yet for this patient.
                                </div>
                            <?php else: ?>
                                <div class="patient-visits-table-wrap">
                                    <table class="data-table patient-history-table">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Service Program</th>
                                                <th>Health Station</th>
                                                <th>Appt Code</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($patientProfile['visits'] as $visit): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?= h(date('M j, Y', strtotime((string) $visit['preferred_date']))); ?></strong>
                                                        <small style="display:block;color:#94a3b8;"><?= h((string) ($visit['preferred_time'] ?? '')); ?></small>
                                                    </td>
                                                    <td>
                                                        <span class="report-service-tag"><?= h((string) $visit['service_name']); ?></span>
                                                    </td>
                                                    <td><?= h((string) $visit['station_name']); ?></td>
                                                    <td style="font-family:monospace;font-weight:700;color:#3b82f6;">
                                                        #<?= h((string) ($visit['appointment_code'] ?? $visit['reference_code'])); ?>
                                                    </td>
                                                    <td>
                                                        <a class="patient-action-btn view" href="?page=patients&<?= $patientHistoryBaseQuery; ?>&visit=<?= h((string) $visit['id']); ?>" title="View Consultation Form">
                                                            <?= admin_icon('eye'); ?>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </article>
                    </div>
                </section>

                <!-- Clinical Consultation Record Inspection Modal -->
                <?php if ($selectedAdminVisit !== null): ?>
                    <?php 
                        $visitBirthDate = (string) ($selectedAdminVisit['birth_date'] ?? ''); 
                        $visitAge = $visitBirthDate !== '' ? (int) date_diff(new DateTimeImmutable($visitBirthDate), new DateTimeImmutable('today'))->y : 0;
                    ?>
                    <section class="service-modal-overlay report-modal-backdrop" style="display:flex;" onclick="if(event.target===this)window.location.href='?page=patients&<?= $patientHistoryBaseQuery; ?>'">
                        <div class="service-modal-card clinical-modal-card-modern">
                            <div class="report-modal-header">
                                <div class="report-modal-header-left">
                                    <div class="report-modal-icon-badge" style="background:#e8fbf3;color:#0db273;">
                                        <?= admin_icon('check'); ?>
                                    </div>
                                    <div>
                                        <h2>Consultation Record #<?= h((string) ($selectedAdminVisit['appointment_code'] ?? $selectedAdminVisit['reference_code'])); ?></h2>
                                        <p><?= h((string) $selectedAdminVisit['service_name']); ?> &bull; <?= h((string) $selectedAdminVisit['station_name']); ?></p>
                                    </div>
                                </div>
                                <a class="modal-close-btn report-modal-close" href="?page=patients&<?= $patientHistoryBaseQuery; ?>">&times;</a>
                            </div>

                            <div class="report-modal-body clinical-modal-body">
                                <!-- Patient Overview Strip -->
                                <div class="clinical-patient-overview-strip">
                                    <?php if (!empty($selectedAdminVisit['photo_path'])): ?>
                                        <img class="clinical-photo-modern" src="../Patients/<?= h((string) $selectedAdminVisit['photo_path']); ?>" alt="<?= h(full_name($selectedAdminVisit)); ?> photo">
                                    <?php endif; ?>
                                    <div class="clinical-overview-meta">
                                        <h3><?= h(full_name($selectedAdminVisit)); ?></h3>
                                        <div class="clinical-meta-pills">
                                            <span><?= $visitAge; ?> yrs old (<?= h((string) ($selectedAdminVisit['gender'] ?? '')); ?>)</span>
                                            <span>Date: <?= h(date('F j, Y', strtotime((string) $selectedAdminVisit['preferred_date']))); ?> (<?= h((string) $selectedAdminVisit['preferred_time']); ?>)</span>
                                            <span>Contact: <?= h((string) $selectedAdminVisit['contact_number']); ?></span>
                                        </div>
                                        <div class="clinical-address-line">
                                            <?= admin_icon('map'); ?>
                                            <span><?= h((string) $selectedAdminVisit['complete_address']); ?></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Vital Signs Section -->
                                <div class="clinical-vitals-section">
                                    <h4 class="clinical-section-title">Patient Vital Signs &amp; Triage</h4>
                                    <div class="clinical-vitals-grid">
                                        <div class="vital-metric-card">
                                            <span class="vital-label">Body Temperature</span>
                                            <strong class="vital-value"><?= !empty($selectedAdminVisit['body_temperature']) ? h((string) $selectedAdminVisit['body_temperature']) . ' &deg;C' : '<em class="not-set">Not recorded</em>'; ?></strong>
                                        </div>
                                        <div class="vital-metric-card">
                                            <span class="vital-label">Pulse Rate</span>
                                            <strong class="vital-value"><?= !empty($selectedAdminVisit['pulse_rate']) ? h((string) $selectedAdminVisit['pulse_rate']) . ' bpm' : '<em class="not-set">Not recorded</em>'; ?></strong>
                                        </div>
                                        <div class="vital-metric-card">
                                            <span class="vital-label">Respiration Rate</span>
                                            <strong class="vital-value"><?= !empty($selectedAdminVisit['respiration_rate']) ? h((string) $selectedAdminVisit['respiration_rate']) . ' cpm' : '<em class="not-set">Not recorded</em>'; ?></strong>
                                        </div>
                                        <div class="vital-metric-card">
                                            <span class="vital-label">Blood Pressure</span>
                                            <strong class="vital-value"><?= !empty($selectedAdminVisit['blood_pressure']) ? h((string) $selectedAdminVisit['blood_pressure']) : '<em class="not-set">Not recorded</em>'; ?></strong>
                                        </div>
                                    </div>
                                </div>

                                <!-- Doctor's Notes -->
                                <div class="clinical-notes-section">
                                    <h4 class="clinical-section-title">Physician &amp; Clinical Notes</h4>
                                    <div class="clinical-notes-card">
                                        <p><?= !empty($selectedAdminVisit['doctor_notes']) ? nl2br(h((string) $selectedAdminVisit['doctor_notes'])) : '<em>No clinical notes or assessments recorded for this consultation.</em>'; ?></p>
                                    </div>
                                </div>
                            </div>

                            <div class="report-modal-footer">
                                <a href="?page=patients&<?= $patientHistoryBaseQuery; ?>" class="report-btn-secondary" style="text-decoration:none;">Close Record</a>
                                <a href="?page=patients&<?= $patientHistoryBaseQuery; ?>" class="report-btn-primary" style="text-decoration:none;">Back to Patient Profile</a>
                            </div>
                        </div>
                    </section>
                <?php endif; ?>
            <?php endif; ?>

        <?php elseif ($page === 'patients'): ?>
            <!-- Main Patients Directory Page -->
            <section class="page-header action-head reports-page-head">
                <div class="action-head-copy">
                    <h1>Patients &amp; Health Records</h1>
                    <p>Directory of registered patients, consultation history, and verified profile updates</p>
                </div>
                <div class="header-actions">
                    <span class="dash-badge-count" style="font-size:0.9rem;padding:8px 16px;">
                        <?= number_format(count($patients)); ?> Registered Patients
                    </span>
                </div>
            </section>

            <!-- Search & Filter Bar -->
            <section class="panel-card patient-filter-bar-card">
                <form class="patient-filter-form-modern" method="get">
                    <input type="hidden" name="page" value="patients">
                    
                    <div class="patient-search-input-wrap">
                        <?= admin_icon('search'); ?>
                        <input type="text" name="search" value="<?= h($search); ?>" placeholder="Search by patient name, contact number, ID, or address...">
                    </div>

                    <div class="patient-filter-selects">
                        <select name="patient_station" onchange="this.form.submit()">
                            <option value="" <?= $patientStationFilter === '' ? 'selected' : ''; ?>>All Barangay Stations</option>
                            <?php foreach ($stations as $station): ?>
                                <option value="<?= h($station['slug']); ?>" <?= $patientStationFilter === $station['slug'] ? 'selected' : ''; ?>>
                                    <?= h($station['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <select name="patient_gender" onchange="this.form.submit()">
                            <option value="" <?= $patientGenderFilter === '' ? 'selected' : ''; ?>>All Genders</option>
                            <option value="Female" <?= strtolower($patientGenderFilter) === 'female' ? 'selected' : ''; ?>>Female</option>
                            <option value="Male" <?= strtolower($patientGenderFilter) === 'male' ? 'selected' : ''; ?>>Male</option>
                        </select>

                        <button type="submit" class="dash-hero-btn primary mini-btn" style="padding:9px 16px;">Search</button>

                        <?php if ($search !== '' || $patientStationFilter !== '' || $patientGenderFilter !== ''): ?>
                            <a href="?page=patients" class="report-clear-all-btn" style="margin-left:4px;">Clear filters</a>
                        <?php endif; ?>
                    </div>
                </form>
            </section>

            <!-- Patients Directory Table Card -->
            <section class="panel-card patient-table-card-modern">
                <?php if ($patients === []): ?>
                    <div class="empty-state" style="padding: 48px 20px;">
                        <h3>No patients found</h3>
                        <p style="color:#64748b;margin-top:6px;">No patient records match the specified search or filter criteria.</p>
                        <a href="?page=patients" class="dash-hero-btn secondary" style="margin-top:14px;display:inline-flex;">View All Patients</a>
                    </div>
                <?php else: ?>
                    <div class="table-scroll-wrapper">
                        <table class="data-table patient-management-table-modern">
                            <thead>
                                <tr>
                                    <th>Patient Profile</th>
                                    <th>Age / Gender</th>
                                    <th>Health Station &amp; Address</th>
                                    <th>Contact Information</th>
                                    <th>Consultations</th>
                                    <th>Last Visit</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($patients as $patient): ?>
                                    <?php 
                                        $hasNotification = false; 
                                        $notificationField = ''; 
                                        $notificationId = 0; 
                                        foreach ($unreadNotifications as $notif) { 
                                            if ($notif['patient_id'] === $patient['patient_id']) { 
                                                $hasNotification = true; 
                                                $notificationField = $notif['field_updated']; 
                                                $notificationId = (int)$notif['id']; 
                                                break; 
                                            } 
                                        }
                                        $patInitials = strtoupper(substr((string) ($patient['first_name'] ?? 'P'), 0, 1) . substr((string) ($patient['last_name'] ?? 'U'), 0, 1));
                                    ?>
                                    <tr class="<?= $hasNotification ? 'patient-row-highlighted' : ''; ?>" data-notification-id="<?= $hasNotification ? h((string)$notificationId) : ''; ?>">
                                        <td>
                                            <div class="patient-table-user-cell">
                                                <div class="patient-table-avatar"><?= h($patInitials); ?></div>
                                                <div>
                                                    <strong><?= h(full_name($patient)); ?></strong>
                                                    <span class="patient-table-id">ID: <?= h((string) $patient['patient_id']); ?></span>
                                                </div>
                                            </div>
                                            <?php if ($hasNotification): ?>
                                                <div class="patient-change-pill-row">
                                                    <span class="patient-change-chip">
                                                        &bull; Updated <?= h($notificationField); ?>
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div><?= h((string) $patient['age']); ?> yrs</div>
                                            <small style="color:#64748b;"><?= h((string) $patient['gender']); ?></small>
                                        </td>
                                        <td>
                                            <?php if (!empty($patient['station_name'])): ?>
                                                <span class="patient-station-chip"><?= h((string) $patient['station_name']); ?></span>
                                            <?php endif; ?>
                                            <div class="patient-address-snippet"><?= h((string) $patient['complete_address']); ?></div>
                                        </td>
                                        <td>
                                            <div style="font-weight:600;color:#2563eb;"><?= h((string) $patient['contact_number']); ?></div>
                                            <small style="color:#64748b;"><?= !empty($patient['email']) ? h((string) $patient['email']) : 'No email'; ?></small>
                                        </td>
                                        <td>
                                            <span class="patient-visits-badge">
                                                <?= (int) ($patient['total_visits'] ?? 1); ?> <?= ((int) ($patient['total_visits'] ?? 1)) === 1 ? 'Visit' : 'Visits'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div><?= !empty($patient['last_visit']) ? h(date('M j, Y', strtotime((string) $patient['last_visit']))) : 'N/A'; ?></div>
                                        </td>
                                        <td>
                                            <div class="patient-table-action-btns">
                                                <a class="patient-action-btn view" href="?page=patients&patient=<?= h((string) $patient['patient_id']); ?>" title="View Complete Patient Profile">
                                                    <?= admin_icon('eye'); ?>
                                                </a>
                                                <a class="patient-action-btn history" href="?page=patients&service_history=<?= h((string) $patient['patient_id']); ?>" title="View Completed Service History">
                                                    <?= admin_icon('history'); ?>
                                                </a>
                                                <?php if ($hasNotification): ?>
                                                    <a class="patient-action-btn audit" href="?page=patients&patient_history=<?= h((string) $patient['patient_id']); ?>" title="View Profile Update History">
                                                        <?= admin_icon('clock'); ?>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="patient-directory-footer">
                        <span>Showing <?= count($patients); ?> active patient records</span>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Patient Information History Audit Modal -->
            <?php if ($patientHistoryId !== '' && $patientHistory !== []): ?>
                <section class="service-modal-overlay report-modal-backdrop" style="display:flex;" onclick="if(event.target===this)window.location.href='?page=patients'">
                    <div class="service-modal-card" style="max-width:640px;">
                        <div class="report-modal-header">
                            <div class="report-modal-header-left">
                                <div class="report-modal-icon-badge" style="background:#eff6ff;color:#2563eb;">
                                    <?= admin_icon('history'); ?>
                                </div>
                                <div>
                                    <h2>Patient Profile Audit Trail</h2>
                                    <p>Historical record of information changes made to this account</p>
                                </div>
                            </div>
                            <a class="modal-close-btn report-modal-close" href="?page=patients">&times;</a>
                        </div>
                        <div class="report-modal-body">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Field Name</th>
                                        <th>Previous Value</th>
                                        <th>Updated Value</th>
                                        <th>Changed At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($patientHistory as $history): ?>
                                        <tr>
                                            <td><strong><?= h(ucwords(str_replace('_', ' ', (string) $history['field_name']))); ?></strong></td>
                                            <td style="color:#ef4444;"><?= h((string) $history['old_value']); ?></td>
                                            <td style="color:#10b981;font-weight:600;"><?= h((string) $history['new_value']); ?></td>
                                            <td style="font-size:0.82rem;color:#64748b;"><?= h(date('M j, Y g:i A', strtotime((string) $history['changed_at']))); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="report-modal-footer">
                            <a href="?page=patients" class="report-btn-secondary" style="text-decoration:none;">Close</a>
                            <a href="?page=patients" class="report-btn-primary" style="text-decoration:none;">Done</a>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Patient Completed Service History Modal -->
            <?php if ($patientServiceHistoryProfile !== null): ?>
                <section class="service-modal-overlay report-modal-backdrop" style="display:flex;" onclick="if(event.target===this)window.location.href='?page=patients'">
                    <div class="service-modal-card" style="max-width:700px;">
                        <div class="report-modal-header">
                            <div class="report-modal-header-left">
                                <div class="report-modal-icon-badge" style="background:#e8fbf3;color:#0db273;">
                                    <?= admin_icon('appointments'); ?>
                                </div>
                                <div>
                                    <h2><?= h(full_name($patientServiceHistoryProfile)); ?> &bull; Service History</h2>
                                    <p>All completed medical visits and program consultations</p>
                                </div>
                            </div>
                            <a class="modal-close-btn report-modal-close" href="?page=patients">&times;</a>
                        </div>
                        <div class="report-modal-body">
                            <?php if ($patientServiceHistoryProfile['visits'] === []): ?>
                                <div class="empty-state">No completed service consultations found for this patient.</div>
                            <?php else: ?>
                                <table class="data-table patient-history-table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Health Service</th>
                                            <th>Health Station</th>
                                            <th>Appt Code</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($patientServiceHistoryProfile['visits'] as $visit): ?>
                                            <tr>
                                                <td><?= h(date('M j, Y', strtotime((string) $visit['preferred_date']))); ?></td>
                                                <td><strong><?= h((string) $visit['service_name']); ?></strong></td>
                                                <td><?= h((string) $visit['station_name']); ?></td>
                                                <td style="font-family:monospace;font-weight:700;color:#3b82f6;">#<?= h((string) ($visit['appointment_code'] ?? $visit['reference_code'])); ?></td>
                                                <td>
                                                    <a class="patient-action-btn view" href="?page=patients&patient=<?= h((string) $patientServiceHistoryProfile['patient_id']); ?>&visit=<?= h((string) $visit['id']); ?>" title="View Consultation Form">
                                                        <?= admin_icon('eye'); ?>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                        <div class="report-modal-footer">
                            <a href="?page=patients" class="report-btn-secondary" style="text-decoration:none;">Close</a>
                            <a href="?page=patients&patient=<?= h((string) $patientServiceHistoryProfile['patient_id']); ?>" class="report-btn-primary" style="text-decoration:none;">View Full Profile</a>
                        </div>
                    </div>
                </section>
            <?php endif; ?>
        <?php elseif ($page === 'appointments' && $stationView !== ''): ?>
            <?php $station = $stationLookup[$stationView] ?? null; ?>
            <?php $adminCanConfirmHere = $stationView === 'city-health'; ?>
            <section class="page-header">
                <a class="back-admin" href="?page=appointments"><?= admin_icon('arrow-left'); ?>Back to All Health Centers</a>
                <h1><?= h($station['name'] ?? ucfirst($stationView)); ?></h1>
                <p><?= $adminCanConfirmHere ? 'Review and confirm Bacolod City Health bookings before they move to queue' : 'Select a service to view and manage appointment requests for this health center'; ?></p>
            </section>
            <?php if ($station !== null && $programFilter === ''): ?>
                <section class="appt-filter-card" style="margin-bottom: 22px;">
                    <div class="filter-toolbar-header-row">
                        <span class="filter-toolbar-label">
                            <?= admin_icon('clock'); ?>
                            <span>Filter Appointments by Timeframe:</span>
                        </span>
                        <?= render_dual_date_filter('date', $dateFilter, 'admin'); ?>
                    </div>
                </section>
                <section class="services-grid queue-services-grid">
                    <?php foreach ($station['programs'] as $program): ?>
                        <?php
                        $serviceAppointments = array_values(array_filter(
                            $allStationAppointments,
                            static fn(array $item): bool => (string) ($item['service_slug'] ?? '') === $program['slug']
                        ));
                        $servicePendingCount = count(array_filter($serviceAppointments, static fn(array $item): bool => (string) ($item['status'] ?? '') === 'Pending'));
                        $serviceCancelledCount = count(array_filter($serviceAppointments, static fn(array $item): bool => (string) ($item['status'] ?? '') === 'Cancelled'));
                        $serviceTotalCount = count($serviceAppointments);
                        $serviceMeta = $serviceCatalog[$program['slug']] ?? null;
                        ?>
                        <a class="service-card queue-service-card" href="?page=appointments&station=<?= h($stationView); ?>&program=<?= h($program['slug']); ?>&status=<?= h($status); ?>&date=<?= h($dateFilter); ?>">
                            <div class="service-card-top">
                                <div class="service-icon <?= h($serviceMeta['color'] ?? 'mint'); ?>"><?= admin_icon($serviceMeta['icon'] ?? 'appointments'); ?></div>
                                <span class="service-arrow"><?= admin_icon('arrow-right'); ?></span>
                            </div>
                            <h3><?= h($serviceMeta['title'] ?? $program['slug']); ?></h3>
                            <p><?= h($serviceMeta['description'] ?? ''); ?></p>
                            <div class="service-queue-stats">
                                <div class="queue-stat-mini pending"><span><?= admin_icon('clock'); ?></span><strong><?= $servicePendingCount; ?></strong><small>Pending</small></div>
                                <div class="queue-stat-mini cancelled"><span><?= admin_icon('x'); ?></span><strong><?= $serviceCancelledCount; ?></strong><small>Cancelled</small></div>
                                <div class="queue-stat-mini total"><span><?= admin_icon('appointments'); ?></span><strong><?= $serviceTotalCount; ?></strong><small>Total</small></div>
                            </div>
                            <div class="service-action">View Appointments →</div>
                        </a>
                    <?php endforeach; ?>
                </section>
            <?php else: ?>
                <?php
                $currentProgramMeta = $serviceCatalog[$programFilter] ?? null;
                $currentProgramTitle = $currentProgramMeta['title'] ?? ucfirst($programFilter);
                $pendingApptCount = count(array_filter($appointmentsPageRows, static fn(array $item): bool => (string) ($item['status'] ?? '') === 'Pending'));
                $cancelledApptCount = count(array_filter($appointmentsPageRows, static fn(array $item): bool => (string) ($item['status'] ?? '') === 'Cancelled'));
                $totalApptCount = count($appointmentsPageRows);
                ?>
                <!-- Service Detail Top Toolbar & Breadcrumb -->
                <div class="appt-detail-topbar">
                    <div class="appt-breadcrumb-trail">
                        <a href="?page=appointments" class="appt-breadcrumb-item">
                            <?= admin_icon('appointments'); ?>
                            <span>All Health Centers</span>
                        </a>
                        <span class="appt-breadcrumb-sep">/</span>
                        <a href="?page=appointments&station=<?= h($stationView); ?>&status=<?= h($status); ?>&date=<?= h($dateFilter); ?>" class="appt-breadcrumb-item">
                            <span><?= h($station['name'] ?? ucfirst($stationView)); ?></span>
                        </a>
                        <span class="appt-breadcrumb-sep">/</span>
                        <span class="appt-breadcrumb-active"><?= h($currentProgramTitle); ?></span>
                    </div>

                    <a class="appt-back-btn" href="?page=appointments&station=<?= h($stationView); ?>&status=<?= h($status); ?>&date=<?= h($dateFilter); ?>">
                        <?= admin_icon('arrow-left'); ?>
                        <span>Back to Services</span>
                    </a>
                </div>

                <!-- Sleek Filter and Search Bar -->
                <section class="appt-filter-card">
                    <form method="get" class="appt-filter-form">
                        <input type="hidden" name="page" value="appointments">
                        <input type="hidden" name="station" value="<?= h($stationView); ?>">
                        <input type="hidden" name="program" value="<?= h($programFilter); ?>">

                        <div class="appt-search-field">
                            <span class="appt-search-icon"><?= admin_icon('search'); ?></span>
                            <input type="text" name="search" value="<?= h($search); ?>" placeholder="Search by patient name, ID, or phone..." maxlength="30">
                            <?php if ($search !== ''): ?>
                                <a href="?page=appointments&station=<?= h($stationView); ?>&program=<?= h($programFilter); ?>&status=<?= h($status); ?>&date=<?= h($dateFilter); ?>" class="appt-search-clear" title="Clear search">
                                    <?= admin_icon('x'); ?>
                                </a>
                            <?php endif; ?>
                        </div>

                        <div class="appt-filter-dropdowns">
                            <div class="appt-select-wrap">
                                <span class="appt-select-icon"><?= admin_icon('filter'); ?></span>
                                <select name="status" onchange="this.form.submit()">
                                    <option value="">All Statuses</option>
                                    <?php foreach (['Pending', 'Cancelled'] as $option): ?>
                                        <option value="<?= h($option); ?>" <?= $status === $option ? 'selected' : ''; ?>><?= h($option); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <?= render_dual_date_filter('date', $dateFilter, 'admin'); ?>

                            <button type="submit" class="appt-find-btn green-btn">
                                <?= admin_icon('search'); ?>
                                <span>Filter</span>
                            </button>
                        </div>
                    </form>
                </section>

                <!-- Modern Light Stat Cards -->
                <section class="appt-metrics-grid">
                    <article class="appt-metric-card pending">
                        <div class="appt-metric-icon">
                            <?= admin_icon('clock'); ?>
                        </div>
                        <div class="appt-metric-content">
                            <span class="appt-metric-label">Pending Confirmation</span>
                            <strong class="appt-metric-val"><?= number_format($pendingApptCount); ?></strong>
                        </div>
                    </article>

                    <article class="appt-metric-card cancelled">
                        <div class="appt-metric-icon">
                            <?= admin_icon('x'); ?>
                        </div>
                        <div class="appt-metric-content">
                            <span class="appt-metric-label">Cancelled Bookings</span>
                            <strong class="appt-metric-val"><?= number_format($cancelledApptCount); ?></strong>
                        </div>
                    </article>

                    <article class="appt-metric-card total">
                        <div class="appt-metric-icon">
                            <?= admin_icon('appointments'); ?>
                        </div>
                        <div class="appt-metric-content">
                            <span class="appt-metric-label">Total Records</span>
                            <strong class="appt-metric-val"><?= number_format($totalApptCount); ?></strong>
                        </div>
                    </article>
                </section>

                <!-- Modern Appointment Record Cards -->
                <section class="appt-records-stack">
                    <?php if ($appointmentsPageRows === []): ?>
                        <div class="panel-card empty-state appt-empty-box">
                            <div class="appt-empty-icon"><?= admin_icon('appointments'); ?></div>
                            <h3>No appointments found</h3>
                            <p>No appointment records match the current filters or search criteria.</p>
                            <?php if ($search !== '' || $status !== '' || $dateFilter !== ''): ?>
                                <a href="?page=appointments&station=<?= h($stationView); ?>&program=<?= h($programFilter); ?>" class="dash-hero-btn secondary" style="margin-top:14px;display:inline-flex;">Reset Filters</a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <?php foreach ($appointmentsPageRows as $appointment): ?>
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
                                                <?= admin_icon('stethoscope'); ?>
                                                <span><?= h($appointment['service_name']); ?></span>
                                            </span>
                                        </div>
                                        <div class="appt-meta-chips-row">
                                            <span class="appt-meta-chip">
                                                <?= admin_icon('calendar'); ?>
                                                <span><?= h(date('D, M j, Y', strtotime((string) $appointment['preferred_date']))); ?></span>
                                            </span>
                                            <span class="appt-meta-chip">
                                                <?= admin_icon('clock'); ?>
                                                <span><?= h($appointment['preferred_time']); ?></span>
                                            </span>
                                            <?php if (!empty($appointment['contact_number'])): ?>
                                                <span class="appt-meta-chip phone">
                                                    <?= admin_icon('phone'); ?>
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
                                    <?php if ($isPending && $adminCanConfirmHere): ?>
                                        <form method="post" class="appt-confirm-form">
                                            <input type="hidden" name="csrf_token" value="<?= h($csrf); ?>">
                                            <input type="hidden" name="appointment_id" value="<?= h((string) $appointment['id']); ?>">
                                            <input type="hidden" name="new_status" value="Confirmed">
                                            <button class="dash-hero-btn primary" type="submit">
                                                <?= admin_icon('check'); ?>
                                                <span>Confirm Booking</span>
                                            </button>
                                        </form>
                                    <?php elseif ($isPending): ?>
                                        <span class="appt-staff-only-note">
                                            <?= admin_icon('shield'); ?>
                                            <span>Staff confirmation only</span>
                                        </span>
                                    <?php else: ?>
                                        <span class="appt-cancelled-note">Booking Cancelled</span>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
        <?php elseif ($page === 'appointments'): ?>
            <section class="page-header"><h1>Appointments by Barangay</h1><p>Select a health station to view and manage appointments</p></section>
            <section class="station-admin-grid">
                <?php foreach ($stations as $station):
                    if ($station['slug'] === 'city-health') { continue; }
                    $count = 0;
                    foreach ($stationCounts as $row) {
                        if ($row['station_slug'] === $station['slug']) {
                            $count = (int) $row['total'];
                            break;
                        }
                    }
                ?>
                    <a class="station-admin-card" href="?page=appointments&station=<?= h($station['slug']); ?>">
                        <div class="station-admin-image" style="background-image:url('<?= h($station['image']); ?>')">
                            <span class="station-count badge-<?= h($station['color']); ?>" data-station="<?= h($station['slug']); ?>">
                                <?= h((string) $count); ?> Pending Queue<?= $count === 1 ? '' : 's'; ?>
                            </span>
                        </div>
                        <div class="station-admin-body">
                            <h3><?= h($station['name']); ?></h3>
                            <p><?= h($station['detail_location']); ?></p>
                            <span><?= h($station['phone']); ?></span>
                            <small><?= h($station['full_hours']); ?></small>
                        </div>
                    </a>
                <?php endforeach; ?>
            </section>
        <?php elseif ($page === 'queue' && $stationView === ''): ?>
            <section class="page-header">
                <h1>Queue Management by Barangay</h1>
                <p>Select a health station to view and monitor queued services</p>
            </section>
            <section class="station-admin-grid">
                <?php foreach ($stations as $station):
                    if ($station['slug'] === 'city-health') { continue; }
                    $count = 0;
                    foreach ($stationQueueCounts as $row) {
                        if ($row['station_slug'] === $station['slug']) {
                            $count = (int) $row['total'];
                            break;
                        }
                    }
                ?>
                    <a class="station-admin-card" href="?page=queue&station=<?= h($station['slug']); ?>">
                        <div class="station-admin-image" style="background-image:url('<?= h($station['image']); ?>')">
                            <span class="station-count badge-<?= h($station['color']); ?>" data-station="<?= h($station['slug']); ?>">
                                <?= h((string) $count); ?> in Queue
                            </span>
                        </div>
                        <div class="station-admin-body">
                            <h3><?= h($station['name']); ?></h3>
                            <p><?= h($station['detail_location']); ?></p>
                            <span><?= h($station['phone']); ?></span>
                            <small><?= h($station['full_hours']); ?></small>
                        </div>
                    </a>
                <?php endforeach; ?>
            </section>
        <?php elseif ($page === 'queue' && $programFilter === ''): ?>
            <section class="page-header">
                <a class="back-admin" href="?page=queue"><?= admin_icon('arrow-left'); ?>Back to All Health Centers</a>
                <h1><?= h($queueStation['name'] ?? ucfirst($stationView)); ?></h1>
                <p>Select a service to view and monitor the live queue for this health center</p>
            </section>
            <section class="appt-filter-card" style="margin-bottom: 22px;">
                <div class="filter-toolbar-header-row">
                    <span class="filter-toolbar-label">
                        <?= admin_icon('clock'); ?>
                        <span>Filter Queue by Timeframe:</span>
                    </span>
                    <?= render_dual_date_filter('queue_date', $queueDateFilter, 'admin'); ?>
                </div>
            </section>
            <section class="services-grid queue-services-grid">
                <?php foreach (($queueStation['programs'] ?? []) as $program): ?>
                    <?php
                    $serviceQueueEntries = array_values(array_filter(
                        $queueEntriesForStation,
                        static fn(array $item): bool => (string) ($item['service_slug'] ?? '') === $program['slug']
                    ));
                    $serviceWaitingCount = count(array_filter($serviceQueueEntries, static fn(array $item): bool => (string) ($item['status'] ?? '') === 'Confirmed'));
                    $serviceServingCount = count(array_filter($serviceQueueEntries, static fn(array $item): bool => (string) ($item['status'] ?? '') === 'Serving'));
                    $serviceCompletedCount = count(array_filter($serviceQueueEntries, static fn(array $item): bool => (string) ($item['status'] ?? '') === 'Completed'));
                    $serviceMeta = $serviceCatalog[$program['slug']] ?? null;
                    ?>
                    <a class="service-card queue-service-card" href="?page=queue&station=<?= h($stationView); ?>&program=<?= h($program['slug']); ?>&queue_date=<?= h($queueDateFilter); ?>">
                        <div class="service-card-top">
                            <div class="service-icon <?= h($serviceMeta['color'] ?? 'mint'); ?>"><?= admin_icon($serviceMeta['icon'] ?? 'appointments'); ?></div>
                            <span class="service-arrow"><?= admin_icon('arrow-right'); ?></span>
                        </div>
                        <h3><?= h($serviceMeta['title'] ?? $program['slug']); ?></h3>
                        <p><?= h($serviceMeta['description'] ?? ''); ?></p>
                        <div class="service-queue-stats">
                            <div class="queue-stat-mini waiting"><span><?= admin_icon('clock'); ?></span><strong><?= $serviceWaitingCount; ?></strong><small>Waiting</small></div>
                            <div class="queue-stat-mini serving"><span><?= admin_icon('users'); ?></span><strong><?= $serviceServingCount; ?></strong><small>Serving</small></div>
                            <div class="queue-stat-mini completed"><span><?= admin_icon('check'); ?></span><strong><?= $serviceCompletedCount; ?></strong><small>Done</small></div>
                        </div>
                        <div class="service-action">View Queue →</div>
                    </a>
                <?php endforeach; ?>
            </section>
        <?php elseif ($page === 'queue'): ?>
            <?php
            $currentProgramMeta = $serviceCatalog[$programFilter] ?? null;
            $currentProgramTitle = $currentProgramMeta['title'] ?? ucfirst($programFilter);
            $waitingQueueCount = count(array_filter($adminQueueEntries, static fn(array $item): bool => (string) ($item['status'] ?? '') === 'Confirmed'));
            $servingQueueCount = count(array_filter($adminQueueEntries, static fn(array $item): bool => (string) ($item['status'] ?? '') === 'Serving'));
            $completedQueueCount = count(array_filter($adminQueueEntries, static fn(array $item): bool => (string) ($item['status'] ?? '') === 'Completed'));
            $totalQueueCount = count($adminQueueEntries);
            ?>
            <!-- Service Detail Top Toolbar & Breadcrumb -->
            <div class="appt-detail-topbar">
                <div class="appt-breadcrumb-trail">
                    <a href="?page=queue" class="appt-breadcrumb-item">
                        <?= admin_icon('queue'); ?>
                        <span>All Health Centers</span>
                    </a>
                    <span class="appt-breadcrumb-sep">/</span>
                    <a href="?page=queue&station=<?= h($stationView); ?>&queue_date=<?= h($queueDateFilter); ?>" class="appt-breadcrumb-item">
                        <span><?= h($queueStation['name'] ?? ucfirst($stationView)); ?></span>
                    </a>
                    <span class="appt-breadcrumb-sep">/</span>
                    <span class="appt-breadcrumb-active"><?= h($currentProgramTitle); ?></span>
                </div>

                <a class="appt-back-btn" href="?page=queue&station=<?= h($stationView); ?>&queue_date=<?= h($queueDateFilter); ?>">
                    <?= admin_icon('arrow-left'); ?>
                    <span>Back to Services</span>
                </a>
            </div>

            <!-- Sleek Filter and Search Bar -->
            <section class="appt-filter-card">
                <form method="get" class="appt-filter-form">
                    <input type="hidden" name="page" value="queue">
                    <input type="hidden" name="station" value="<?= h($stationView); ?>">
                    <input type="hidden" name="program" value="<?= h($programFilter); ?>">

                    <div class="appt-search-field">
                        <span class="appt-search-icon"><?= admin_icon('search'); ?></span>
                        <input type="text" name="search" value="<?= h($search); ?>" placeholder="Search queue by patient name, ID, or phone..." maxlength="30">
                        <?php if ($search !== ''): ?>
                            <a href="?page=queue&station=<?= h($stationView); ?>&program=<?= h($programFilter); ?>&queue_date=<?= h($queueDateFilter); ?>" class="appt-search-clear" title="Clear search">
                                <?= admin_icon('x'); ?>
                            </a>
                        <?php endif; ?>
                    </div>

                    <div class="appt-filter-dropdowns">
                        <?= render_dual_date_filter('queue_date', $queueDateFilter, 'admin'); ?>

                        <button type="submit" class="appt-find-btn green-btn">
                            <?= admin_icon('search'); ?>
                            <span>Filter</span>
                        </button>
                    </div>
                </form>
            </section>

            <!-- Modern Soft-Tinted Metrics Grid -->
            <section class="appt-metrics-grid">
                <article class="appt-metric-card pending">
                    <div class="appt-metric-icon">
                        <?= admin_icon('clock'); ?>
                    </div>
                    <div class="appt-metric-content">
                        <span class="appt-metric-label">Waiting in Line</span>
                        <strong class="appt-metric-val"><?= number_format($waitingQueueCount); ?></strong>
                    </div>
                </article>

                <article class="appt-metric-card serving" style="background:#ffffff;border:1.5px solid #a5f3fc;">
                    <div class="appt-metric-icon" style="background:#ecfeff;color:#0891b2;">
                        <?= admin_icon('pulse'); ?>
                    </div>
                    <div class="appt-metric-content">
                        <span class="appt-metric-label">Currently Serving</span>
                        <strong class="appt-metric-val" style="color:#0891b2;"><?= number_format($servingQueueCount); ?></strong>
                    </div>
                </article>

                <article class="appt-metric-card completed">
                    <div class="appt-metric-icon">
                        <?= admin_icon('check'); ?>
                    </div>
                    <div class="appt-metric-content">
                        <span class="appt-metric-label">Completed Today</span>
                        <strong class="appt-metric-val"><?= number_format($completedQueueCount); ?></strong>
                    </div>
                </article>
            </section>

            <!-- Live Feed Notice Banner -->
            <div class="admin-queue-notice-bar">
                <div class="queue-notice-left">
                    <span class="queue-notice-icon"><?= admin_icon('shield'); ?></span>
                    <div>
                        <strong>Live Queue Observation Monitor</strong>
                        <p>Real-time queue tracker for <?= h($queueStation['name'] ?? ucfirst($stationView)); ?>. Patient serving and queue progression are operated by on-site station staff.</p>
                    </div>
                </div>
                <div class="queue-notice-badge">
                    <span class="pulse-green-indicator"></span> Live Feed
                </div>
            </div>

            <!-- Modern Patient Queue Stack -->
            <section class="admin-queue-stack-modern">
                <?php if ($adminQueueEntries === []): ?>
                    <div class="panel-card empty-state appt-empty-box">
                        <div class="appt-empty-icon"><?= admin_icon('queue'); ?></div>
                        <h3>No Patients in Queue</h3>
                        <p>There are no active queue records matching the current filters.</p>
                        <?php if ($search !== '' || $queueDateFilter !== 'today'): ?>
                            <a href="?page=queue&station=<?= h($stationView); ?>&program=<?= h($programFilter); ?>" class="dash-hero-btn secondary" style="margin-top:14px;display:inline-flex;">Reset Filters</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <?php foreach ($adminQueueEntries as $index => $appointment): ?>
                        <?php
                        $queueStatus = (string) ($appointment['status'] ?? 'Confirmed');
                        $queueStateClass = $queueStatus === 'Serving' ? 'serving' : ($queueStatus === 'Completed' ? 'completed' : 'waiting');
                        $patientName = full_name($appointment);
                        $apptCode = (string) ($appointment['appointment_code'] ?? $appointment['reference_code'] ?? '');
                        $hasPatientPhoto = trim((string) ($appointment['photo_path'] ?? '')) !== '';
                        $firstLetter = strtoupper(substr((string) ($appointment['first_name'] ?? 'P'), 0, 1));
                        $lastLetter = strtoupper(substr((string) ($appointment['last_name'] ?? 'T'), 0, 1));
                        $initials = $firstLetter . $lastLetter;
                        ?>
                        <article class="admin-queue-card <?= h($queueStateClass); ?>">
                            <!-- Left: FCFS State Box & Status Indicator -->
                            <div class="queue-token-box <?= h($queueStateClass); ?>" title="<?= $queueStatus === 'Serving' ? 'Currently being attended' : ($queueStatus === 'Completed' ? 'Consultation completed' : 'First-Come First-Served Queue'); ?>">
                                <span class="token-fcfs-icon">
                                    <?= $queueStatus === 'Serving' ? admin_icon('pulse') : ($queueStatus === 'Completed' ? admin_icon('check') : admin_icon('clock')); ?>
                                </span>
                                <strong class="queue-token-fcfs-label"><?= $queueStatus === 'Serving' ? 'SERVING' : ($queueStatus === 'Completed' ? 'DONE' : 'FCFS'); ?></strong>
                                <small class="queue-token-fcfs-sub"><?= $queueStatus === 'Serving' ? 'In Room' : ($queueStatus === 'Completed' ? 'Completed' : 'Queued'); ?></small>
                            </div>

                            <!-- Middle: Patient Profile & Information -->
                            <div class="queue-card-main">
                                <div class="queue-patient-row">
                                    <div class="queue-avatar-circle" style="<?= $hasPatientPhoto ? 'background:transparent;border:none;' : ''; ?>">
                                        <?php if ($hasPatientPhoto): ?>
                                            <img src="../Patients/<?= h((string) $appointment['photo_path']); ?>" alt="<?= h($patientName); ?>" style="width:100%;height:100%;object-fit:cover;border-radius:10px;">
                                        <?php else: ?>
                                            <?= h($initials); ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="queue-patient-info">
                                        <div class="queue-patient-title-line">
                                            <h3 class="queue-patient-name"><?= h($patientName); ?></h3>
                                            <?php if ($apptCode !== ''): ?>
                                                <span class="queue-appt-code-badge">#<?= h($apptCode); ?></span>
                                            <?php endif; ?>
                                            <span class="queue-state-badge <?= h($queueStateClass); ?>">
                                                <?= $queueStatus === 'Serving' ? 'Being Served' : ($queueStatus === 'Completed' ? 'Completed' : 'In Queue'); ?>
                                            </span>
                                        </div>
                                        <div class="queue-patient-meta">
                                            <span class="queue-meta-item">
                                                <?= admin_icon('phone'); ?>
                                                <?= h((string) ($appointment['contact_number'] ?? '')); ?>
                                            </span>
                                            <span class="queue-meta-item">
                                                <?= admin_icon('clock'); ?>
                                                <?= h((string) ($appointment['preferred_time'] ?? '')); ?>
                                            </span>
                                            <span class="queue-meta-item">
                                                <?= admin_icon('calendar'); ?>
                                                <?= h(date('M j, Y', strtotime((string) ($appointment['preferred_date'] ?? 'now')))); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="queue-service-strip">
                                    <span class="queue-service-badge">
                                        <?= admin_icon($serviceCatalog[$appointment['service_slug'] ?? '']['icon'] ?? 'appointments'); ?>
                                        <?= h((string) ($appointment['service_name'] ?? 'Health Service')); ?>
                                    </span>
                                    <span class="queue-station-badge">
                                        <?= admin_icon('map'); ?>
                                        <?= h((string) ($appointment['station_name'] ?? $queueStation['name'] ?? 'Health Station')); ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Right: Non-interactive Live Status Tracker -->
                            <div class="queue-card-status-col">
                                <?php if ($queueStatus === 'Serving'): ?>
                                    <div class="queue-live-status-pill serving" title="Patient is currently in consultation with station staff">
                                        <span class="queue-pulse-dot"></span>
                                        <div class="queue-status-text">
                                            <strong>Now Serving</strong>
                                            <small>In Consultation</small>
                                        </div>
                                    </div>
                                    <div class="queue-disabled-action-note">
                                        <?= admin_icon('shield'); ?> Station Staff Control
                                    </div>
                                <?php elseif ($queueStatus === 'Completed'): ?>
                                    <div class="queue-live-status-pill completed" title="Consultation has been completed">
                                        <span class="queue-status-icon-circle"><?= admin_icon('check'); ?></span>
                                        <div class="queue-status-text">
                                            <strong>Completed</strong>
                                            <small>Finished Visit</small>
                                        </div>
                                    </div>
                                    <div class="queue-disabled-action-note muted">
                                        ✓ Recorded in Patients
                                    </div>
                                <?php else: ?>
                                    <div class="queue-live-status-pill waiting" title="Patient is in queue waiting to be called on a first-come, first-served basis">
                                        <span class="queue-status-icon-circle"><?= admin_icon('clock'); ?></span>
                                        <div class="queue-status-text">
                                            <strong>Waiting in Line</strong>
                                            <small>First-Come, First-Served</small>
                                        </div>
                                    </div>
                                    <div class="queue-disabled-action-note">
                                        <?= admin_icon('shield'); ?> Staff Call Only
                                    </div>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>
        <?php elseif ($page === 'services'): ?>
            <?php $selectedServiceStation = $serviceManagementStation !== '' ? ($stationLookup[$serviceManagementStation] ?? null) : null; ?>
            <?php if ($selectedServiceStation === null || $selectedServiceStation['slug'] === 'city-health'): ?>
                <section class="page-header action-head services-page-head">
                    <div class="action-head-copy">
                        <h1>Station Services & Facilities</h1>
                        <p>Manage health services, customize maximum booking slot capacities, or register new health stations.</p>
                    </div>
                    <div class="header-actions">
                        <button type="button" class="green-btn" onclick="openAddFacilityModal()"><?= admin_icon('plus'); ?>Add Health Center</button>
                    </div>
                </section>
                <section class="station-admin-grid">
                    <?php foreach ($stations as $station): if ($station['slug'] === 'city-health') { continue; } ?>
                        <a class="station-admin-card" href="?page=services&station=<?= h($station['slug']); ?>">
                            <div class="station-admin-image" style="background-image:url('<?= h($station['image']); ?>')">
                                <span class="station-count badge-<?= h($station['color']); ?>"><?= h((string) count($station['programs'])); ?> Service<?= count($station['programs']) === 1 ? '' : 's'; ?></span>
                            </div>
                            <div class="station-admin-body">
                                <h3><?= h($station['name']); ?></h3>
                                <p><?= h($station['detail_location']); ?></p>
                                <span><?= h($station['phone']); ?></span>
                                <small><?= h($station['full_hours']); ?></small>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </section>
            <?php else: ?>
                <section class="page-header action-head services-page-head">
                    <div class="action-head-copy">
                        <a class="back-admin" href="?page=services"><?= admin_icon('arrow-left'); ?>Back to All Health Centers</a>
                        <h1><?= h($selectedServiceStation['name']); ?></h1>
                        <p>Manage assigned clinical services and customize maximum booking slot capacities for this health center</p>
                    </div>
                    <div class="header-actions">
                        <button type="button" class="green-btn" onclick="document.getElementById('addServiceModal').style.display='grid'"><?= admin_icon('plus'); ?>Add Service</button>
                        <button type="button" class="blue-btn" id="removeServiceBtn" style="display:none;" onclick="removeSelectedService()"><?= admin_icon('x'); ?>Remove Service</button>
                    </div>
                </section>
                <?php 
                $stationDailyCapacity = fetch_station_daily_capacity((string) $selectedServiceStation['slug']); 
                $serviceSelection = fetch_station_service_selection((string) $selectedServiceStation['slug']);
                $assignedServices = array_filter($serviceSelection, static fn(array $s): bool => !empty($s['assigned']));
                ?>

                <div class="service-station-hero-card">
                    <div class="station-hero-left">
                        <div class="station-hero-icon-badge badge-<?= h($selectedServiceStation['color'] ?? 'mint'); ?>">
                            <?= admin_icon('map'); ?>
                        </div>
                        <div class="station-hero-meta">
                            <h2><?= h($selectedServiceStation['name']); ?></h2>
                            <p><?= h($selectedServiceStation['detail_location']); ?> &bull; <?= h($selectedServiceStation['phone']); ?></p>
                            <div class="station-hero-badges-row">
                                <span class="station-hours-chip"><?= admin_icon('clock'); ?> <?= h($selectedServiceStation['full_hours'] ?? 'Mon - Sat: 8:00 AM - 5:00 PM'); ?></span>
                                <span class="station-srv-count-chip"><?= count($assignedServices); ?> Active Services</span>
                            </div>
                        </div>
                    </div>
                    <div class="station-capacity-highlight-box">
                        <div class="capacity-copy">
                            <span class="capacity-title">Station Daily Booking Capacity</span>
                            <div class="capacity-slots-val">
                                <strong><?= number_format($stationDailyCapacity); ?></strong>
                                <span>Total Slots / Day (All Services)</span>
                            </div>
                        </div>
                        <button type="button" class="alter-station-capacity-btn" onclick="openStationCapacityModal('<?= h((string) $selectedServiceStation['slug']); ?>', '<?= h(addslashes((string) $selectedServiceStation['name'])); ?>', <?= (int) $stationDailyCapacity; ?>)">
                            <?= admin_icon('edit'); ?>
                            <span>Alter Max Slots</span>
                        </button>
                    </div>
                </div>

                <div class="station-services-grid-heading">
                    <div>
                        <h3>Assigned Health Services</h3>
                        <p>Services offered to residents at this barangay health center</p>
                    </div>
                    <span class="services-badge-counter"><?= count($assignedServices); ?> Programs</span>
                </div>

                <div id="servicesContainer" class="services-grid queue-services-grid">
                    <?php if ($assignedServices === []): ?>
                        <div class="panel-card empty-state" style="grid-column:1/-1;">No services assigned to this station yet. Click "Add Service" above to assign services.</div>
                    <?php else: ?>
                        <?php foreach ($assignedServices as $service): ?>
                            <div class="service-card service-selection-card" data-service-slug="<?= h((string) $service['slug']); ?>">
                                <div class="service-card-top">
                                    <div class="service-icon <?= h((string) $service['color']); ?>"><?= admin_icon((string) ($service['icon'] ?? 'appointments')); ?></div>
                                    <div class="service-card-top-actions">
                                        <input type="checkbox" class="service-checkbox" value="<?= h((string) $service['slug']); ?>" onchange="toggleRemoveButton()" title="Select to remove">
                                    </div>
                                </div>
                                <h3><?= h((string) $service['title']); ?></h3>
                                <p><?= h((string) $service['description']); ?></p>
                                <div class="service-card-footer-clean">
                                    <span class="service-duration-pill">
                                        <?= admin_icon('clock'); ?> <?= h((string) ($service['duration'] ?? '30 mins')); ?>
                                    </span>
                                    <span class="service-active-pill">
                                        ● Active
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Add Service Modal -->
                <div id="addServiceModal" style="display:none;" class="service-modal-overlay">
                    <div class="service-modal-card">
                        <div class="modal-head">
                            <h2>Add Service to <?= h($selectedServiceStation['name']); ?></h2>
                            <button type="button" class="modal-close-btn" onclick="document.getElementById('addServiceModal').style.display='none'">×</button>
                        </div>
                        <form method="post" class="service-modal-form">
                            <input type="hidden" name="csrf_token" value="<?= h($csrf); ?>">
                            <input type="hidden" name="action" value="save_station_services">
                            <input type="hidden" name="station_slug" value="<?= h((string) $selectedServiceStation['slug']); ?>">
                            <div class="modal-services-grid">
                                <?php foreach ($serviceSelection as $service): ?>
                                    <?php if (empty($service['assigned'])): ?>
                                        <label class="service-modal-option">
                                            <input type="checkbox" name="services[]" value="<?= h((string) $service['slug']); ?>">
                                            <span class="service-option-icon <?= h((string) $service['color']); ?>"><?= admin_icon((string) ($service['icon'] ?? 'appointments')); ?></span>
                                            <span class="service-option-copy">
                                                <strong><?= h((string) $service['title']); ?></strong>
                                                <small><?= h((string) $service['description']); ?></small>
                                            </span>
                                        </label>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                            <div class="modal-actions">
                                <button type="button" class="blue-btn" onclick="document.getElementById('addServiceModal').style.display='none'">Cancel</button>
                                <button type="submit" class="green-btn"><?= admin_icon('check'); ?>Add Selected Services</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Alter Station Capacity Modal -->
                <div id="editCapacityModal" style="display:none;" class="service-modal-overlay">
                    <div class="service-modal-card capacity-modal-card">
                        <div class="modal-head">
                            <div class="modal-head-title-wrap">
                                <h2>Station Daily Booking Capacity</h2>
                                <p>Set the maximum total bookings allowed per day across all services combined</p>
                            </div>
                            <button type="button" class="modal-close-btn" onclick="document.getElementById('editCapacityModal').style.display='none'">×</button>
                        </div>
                        <form method="post" class="service-modal-form">
                            <input type="hidden" name="csrf_token" value="<?= h($csrf); ?>">
                            <input type="hidden" name="action" value="update_station_capacity">
                            <input type="hidden" name="station_slug" id="capStationSlug" value="<?= h((string) $selectedServiceStation['slug']); ?>">
                            
                            <div class="capacity-modal-station-strip">
                                <div class="capacity-station-icon">
                                    <?= admin_icon('map'); ?>
                                </div>
                                <div>
                                    <strong id="capStationName"><?= h($selectedServiceStation['name']); ?></strong>
                                    <span><?= h($selectedServiceStation['detail_location']); ?></span>
                                </div>
                            </div>

                            <div class="form-group" style="margin-top: 18px;">
                                <label for="capMaxSlots" style="display:block;margin-bottom:8px;font-weight:700;font-size:0.92rem;color:#0f172a;">
                                    Maximum Daily Slots (All Services Combined)
                                </label>
                                <div class="clean-capacity-input-wrap">
                                    <input type="number" name="max_slots" id="capMaxSlots" min="1" max="5000" required>
                                    <span class="capacity-unit-tag">slots / day</span>
                                </div>
                                <div class="capacity-presets-row">
                                    <button type="button" class="capacity-preset-pill" onclick="document.getElementById('capMaxSlots').value=50">50 Slots</button>
                                    <button type="button" class="capacity-preset-pill" onclick="document.getElementById('capMaxSlots').value=100">100 Slots</button>
                                    <button type="button" class="capacity-preset-pill" onclick="document.getElementById('capMaxSlots').value=150">150 Slots</button>
                                    <button type="button" class="capacity-preset-pill" onclick="document.getElementById('capMaxSlots').value=200">200 Slots</button>
                                    <button type="button" class="capacity-preset-pill" onclick="document.getElementById('capMaxSlots').value=300">300 Slots</button>
                                </div>
                                <small style="display:block;margin-top:8px;color:#64748b;font-size:0.84rem;line-height:1.4;">
                                    When total bookings across all services for a specific date reach this maximum number, the health station will be marked as fully booked for that day.
                                </small>
                            </div>

                            <div class="modal-actions" style="margin-top: 24px;">
                                <button type="button" class="report-btn-secondary" onclick="document.getElementById('editCapacityModal').style.display='none'">Cancel</button>
                                <button type="submit" class="report-btn-primary"><?= admin_icon('check'); ?>Save Station Capacity</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Add Facility Modal -->
            <div id="addFacilityModal" style="display:none;" class="service-modal-overlay">
                <div class="service-modal-card facility-modal-card">
                    <div class="modal-head">
                        <h2>Add New Barangay Health Center</h2>
                        <button type="button" class="modal-close-btn" onclick="document.getElementById('addFacilityModal').style.display='none'">×</button>
                    </div>
                    <form method="post" class="service-modal-form">
                        <input type="hidden" name="csrf_token" value="<?= h($csrf); ?>">
                        <input type="hidden" name="action" value="create_health_facility">
                        
                        <div class="facility-form-grid">
                            <div class="form-group">
                                <label>Barangay Name <em style="color:#ef4444;">*</em></label>
                                <input type="text" name="barangay" id="facilityBarangayInput" placeholder="e.g., Banago, Felisa, Tangub" required oninput="autoPopulateFacilityName(this.value)">
                            </div>
                            <div class="form-group">
                                <label>Health Center Name <em style="color:#ef4444;">*</em></label>
                                <input type="text" name="facility_name" id="facilityNameInput" placeholder="e.g., Banago Barangay Health Station" required>
                            </div>
                            <div class="form-group full-width">
                                <label>Detailed Address / Location</label>
                                <input type="text" name="location" id="facilityLocationInput" placeholder="e.g., Prk. San Jose, Brgy. Banago, Bacolod City">
                            </div>
                            <div class="form-group">
                                <label>Contact Phone Number</label>
                                <input type="text" name="phone" placeholder="e.g., (034) 123-4516">
                            </div>
                            <div class="form-group">
                                <label>Operating Hours</label>
                                <input type="text" name="hours" value="Monday - Saturday, 8:00 AM - 5:00 PM">
                            </div>
                            <div class="form-group">
                                <label>Daily Booking Capacity (All Services)</label>
                                <input type="number" name="max_slots" value="200" min="1" max="5000" required placeholder="e.g., 200">
                            </div>
                            <div class="form-group">
                                <label>Theme Color</label>
                                <select name="color">
                                    <option value="mint">Mint Green</option>
                                    <option value="blue">Royal Blue</option>
                                    <option value="violet">Violet</option>
                                    <option value="rose">Rose Pink</option>
                                    <option value="gold">Warm Gold</option>
                                    <option value="cyan">Cyan</option>
                                </select>
                            </div>
                        </div>

                        <div class="facility-services-section">
                            <h3 style="margin:16px 0 6px;font-size:1rem;color:#1e293b;">Services Offered at this Station</h3>
                            <p style="margin:0 0 12px;color:#64748b;font-size:0.86rem;">Select all health services available to residents at this new barangay station.</p>
                            <div class="facility-services-checklist-grid">
                                <?php foreach ($serviceCatalog as $srvSlug => $srv): ?>
                                    <label class="facility-service-option-card">
                                        <input type="checkbox" name="services[]" value="<?= h($srvSlug); ?>" checked>
                                        <span class="srv-badge <?= h($srv['color']); ?>"><?= admin_icon((string) ($srv['icon'] ?? 'appointments')); ?></span>
                                        <span class="srv-option-body">
                                            <strong><?= h($srv['title']); ?></strong>
                                            <small><?= h($srv['description']); ?></small>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="modal-actions" style="margin-top:20px;">
                            <button type="button" class="blue-btn" onclick="document.getElementById('addFacilityModal').style.display='none'">Cancel</button>
                            <button type="submit" class="green-btn"><?= admin_icon('check'); ?>Register Health Center</button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
            function openAddFacilityModal() {
                document.getElementById('addFacilityModal').style.display = 'grid';
            }
            function autoPopulateFacilityName(val) {
                const nameInput = document.getElementById('facilityNameInput');
                const locInput = document.getElementById('facilityLocationInput');
                if (val.trim()) {
                    nameInput.placeholder = val.trim() + ' Barangay Health Station';
                    if (!nameInput.value || nameInput.dataset.autofilled === '1') {
                        nameInput.value = val.trim() + ' Barangay Health Station';
                        nameInput.dataset.autofilled = '1';
                    }
                    if (!locInput.value || locInput.dataset.autofilled === '1') {
                        locInput.value = 'Serving residents of Brgy. ' + val.trim() + ', Bacolod City';
                        locInput.dataset.autofilled = '1';
                    }
                }
            }
            function openStationCapacityModal(stationSlug, stationName, currentSlots) {
                document.getElementById('capStationSlug').value = stationSlug;
                document.getElementById('capStationName').textContent = stationName;
                document.getElementById('capMaxSlots').value = currentSlots || 200;
                document.getElementById('editCapacityModal').style.display = 'grid';
            }
            function openCapacityModal(stationSlug, serviceSlug, serviceTitle, currentSlots) {
                openStationCapacityModal(stationSlug, serviceTitle, currentSlots);
            }
            function toggleRemoveButton() {
                const checkboxes = document.querySelectorAll('.service-checkbox:checked');
                const removeBtn = document.getElementById('removeServiceBtn');
                removeBtn.style.display = checkboxes.length > 0 ? 'inline-flex' : 'none';
            }
            function removeSelectedService() {
                const selected = document.querySelectorAll('.service-checkbox:checked');
                if (selected.length === 0) {
                    alert('Please select a service to remove.');
                    return;
                }
                if (!confirm('Remove selected service(s)?')) return;
                
                const form = document.createElement('form');
                form.method = 'post';
                form.innerHTML = '<input type="hidden" name="csrf_token" value="<?= h($csrf); ?>"><input type="hidden" name="action" value="save_station_services"><input type="hidden" name="station_slug" value="<?= h((string) ($selectedServiceStation['slug'] ?? '')); ?>">';
                
                const assignedSlugs = Array.from(document.querySelectorAll('.service-selection-card[data-service-slug]'))
                    .map(card => card.dataset.serviceSlug)
                    .filter(slug => !Array.from(selected).some(cb => cb.value === slug));
                
                assignedSlugs.forEach(slug => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'services[]';
                    input.value = slug;
                    form.appendChild(input);
                });
                
                document.body.appendChild(form);
                form.submit();
            }
            </script>
        <?php elseif ($page === 'events'): ?>
            <section class="page-header action-head events-page-head">
                <div class="action-head-copy">
                    <h1>Upcoming Events</h1>
                    <p>View scheduled health events and community medical programs across all health centers</p>
                </div>
            </section>

            <!-- Sleek Filter and Search Bar -->
            <section class="appt-filter-card">
                <form class="appt-filter-form" method="get">
                    <input type="hidden" name="page" value="events">

                    <div class="appt-search-field">
                        <span class="appt-search-icon"><?= admin_icon('search'); ?></span>
                        <input type="text" name="search" value="<?= h($search); ?>" placeholder="Search events by title, description, or station..." maxlength="40">
                        <?php if ($search !== ''): ?>
                            <a href="?page=events&event_station=<?= h($eventStationFilter); ?>" class="appt-search-clear" title="Clear search">
                                <?= admin_icon('x'); ?>
                            </a>
                        <?php endif; ?>
                    </div>

                    <div class="appt-filter-dropdowns">
                        <div class="appt-select-wrap">
                            <span class="appt-select-icon"><?= admin_icon('map'); ?></span>
                            <select name="event_station" onchange="this.form.submit()">
                                <option value="" <?= $eventStationFilter === '' ? 'selected' : ''; ?>>All Barangays</option>
                                <?php foreach ($stations as $station): ?>
                                    <option value="<?= h($station['slug']); ?>" <?= $eventStationFilter === $station['slug'] ? 'selected' : ''; ?>><?= h($station['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" class="appt-find-btn green-btn">
                            <?= admin_icon('search'); ?>
                            <span>Filter</span>
                        </button>
                    </div>
                </form>
            </section>

            <section class="admin-event-card-grid">
                <?php if ($filteredUpcomingEvents === []): ?>
                    <div class="panel-card empty-state appt-empty-box" style="grid-column: 1 / -1;">
                        <div class="appt-empty-icon"><?= admin_icon('clock'); ?></div>
                        <h3>No Upcoming Events Found</h3>
                        <p>There are no scheduled health center events matching your filter criteria.</p>
                        <?php if ($eventStationFilter !== '' || $search !== ''): ?>
                            <a href="?page=events" class="dash-hero-btn secondary" style="margin-top:14px;display:inline-flex;">Reset Filters</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <?php foreach ($filteredUpcomingEvents as $event): ?>
                        <article class="admin-event-card">
                            <div class="admin-event-card-header">
                                <div class="admin-event-card-top">
                                    <span class="admin-event-pill"><?= h(ucfirst(str_replace('-', ' ', (string) $event['icon']))); ?></span>
                                </div>
                                <h3><?= h($event['title']); ?></h3>
                            </div>
                            <div class="admin-event-card-body">
                                <p><?= h($event['description']); ?></p>
                                <div class="admin-event-meta-line"><?= admin_icon('calendar'); ?><span><?= h(date('D, M j, Y', strtotime((string) $event['event_date']))); ?></span></div>
                                <div class="admin-event-meta-line"><?= admin_icon('clock'); ?><span><?= h($event['time_label']); ?><?php if (!empty($event['end_time_label'])): ?> - <?= h((string) $event['end_time_label']); ?><?php endif; ?></span></div>
                                <div class="admin-event-meta-line"><?= admin_icon('map'); ?><span><?= h($event['station_name']); ?></span></div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>
        <?php elseif ($page === 'users'): ?>
            <?php
                $healthStationList = array_values(array_filter($stations, static function(array $station): bool {
                    $slug = (string) ($station['slug'] ?? '');
                    $name = (string) ($station['name'] ?? '');
                    $barangay = (string) ($station['barangay'] ?? '');
                    return !in_array($slug, ['city-health', 'bacolod-city-health-office', 'cho'], true)
                        && stripos($name, 'City Health') === false
                        && stripos($barangay, 'City Health') === false;
                }));
                $staffByStation = [];
                foreach ($staffAccounts as $account) {
                    $slug = (string) ($account['station_slug'] ?? '');
                    if ($slug === '') {
                        continue;
                    }
                    $staffByStation[$slug][] = $account;
                }
            ?>
            <!-- Page Header -->
            <section class="page-header action-head user-page-head">
                <div class="action-head-copy">
                    <h1>User Management</h1>
                    <p>Create and manage staff and admin accounts</p>
                </div>
                <div class="header-actions">
                    <button type="button" class="green-btn user-new-btn" id="openUserModalBtn">
                        <?= admin_icon('user-add'); ?>
                        <span>New User</span>
                    </button>
                </div>
            </section>

            <!-- Main Selection Grid: Admin Accounts & Staff Accounts Tiles -->
            <section class="user-selection-grid" id="userSelectionGrid">
                <article class="panel-card user-management-tile user-tile-admin" data-user-panel="admin">
                    <div class="user-tile-body">
                        <div class="user-tile-icon-box admin">
                            <?= admin_icon('shield'); ?>
                        </div>
                        <div class="user-tile-text">
                            <h3>Admin Accounts</h3>
                            <p>View and manage central administrator credentials and system privileges.</p>
                        </div>
                    </div>
                    <div class="user-tile-footer">
                        <span class="user-tile-counter">
                            <strong><?= count($adminAccounts); ?></strong> Account<?= count($adminAccounts) === 1 ? '' : 's'; ?>
                        </span>
                        <span class="user-tile-action-icon">
                            <?= admin_icon('arrow-right'); ?>
                        </span>
                    </div>
                </article>

                <article class="panel-card user-management-tile user-tile-staff" data-user-panel="staff">
                    <div class="user-tile-body">
                        <div class="user-tile-icon-box staff">
                            <?= admin_icon('stethoscope'); ?>
                        </div>
                        <div class="user-tile-text">
                            <h3>Health Station Staff</h3>
                            <p>Browse on-site healthcare personnel across all Barangay Health Stations.</p>
                        </div>
                    </div>
                    <div class="user-tile-footer">
                        <span class="user-tile-counter">
                            <strong><?= count($staffAccounts); ?></strong> Account<?= count($staffAccounts) === 1 ? '' : 's'; ?>
                        </span>
                        <span class="user-tile-action-icon">
                            <?= admin_icon('arrow-right'); ?>
                        </span>
                    </div>
                </article>
            </section>

            <!-- Panel 1: Admin Accounts Panel -->
            <section class="panel-card user-panel hidden" data-panel="admin" id="adminUsersPanel">
                <div class="user-panel-header">
                    <button type="button" class="link-button user-back-btn" data-back="selection">
                        <?= admin_icon('arrow-left'); ?>
                        <span>Back</span>
                    </button>
                    <div class="user-panel-title-wrap">
                        <h2>Admin Accounts</h2>
                        <span class="user-panel-count-badge"><?= count($adminAccounts); ?> Total Admins</span>
                    </div>
                </div>

                <?php if ($adminAccounts === []): ?>
                    <div class="user-empty-state-box">
                        <div class="empty-state-icon admin"><?= admin_icon('shield'); ?></div>
                        <strong>No admin accounts created yet.</strong>
                        <p>Click "New User" to add a new administrator account.</p>
                    </div>
                <?php else: ?>
                    <div class="user-organized-list">
                        <?php foreach ($adminAccounts as $account): 
                            $initials = '';
                            $nameParts = preg_split('/\s+/', trim($account['admin_name'])) ?: [];
                            if (count($nameParts) >= 2) {
                                $initials = strtoupper(substr($nameParts[0], 0, 1) . substr(end($nameParts), 0, 1));
                            } else {
                                $initials = strtoupper(substr($account['admin_name'], 0, 2));
                            }
                        ?>
                            <div class="user-detail-row-card admin-variant">
                                <div class="user-row-left">
                                    <div class="user-avatar-initials admin">
                                        <?= h($initials); ?>
                                    </div>
                                    <div class="user-row-info">
                                        <div class="user-row-header-line">
                                            <strong class="user-name"><?= h($account['admin_name']); ?></strong>
                                            <span class="user-role-tag admin">
                                                <?= admin_icon('shield'); ?> Administrator
                                            </span>
                                        </div>
                                        <div class="user-row-meta-line">
                                            <span class="user-office-tag"><?= admin_icon('map'); ?> <?= h($account['office_name'] ?: 'Bacolod City Health Central Office'); ?></span>
                                            <span class="user-email-tag"><?= admin_icon('mail'); ?> <a href="mailto:<?= h($account['email']); ?>"><?= h($account['email']); ?></a></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="user-row-right">
                                    <span class="user-status-indicator active">
                                        <span class="dot"></span> Active
                                    </span>
                                    <?php if (count($adminAccounts) > 1): ?>
                                        <form method="post" onsubmit="return confirm('Are you sure you want to remove administrator <?= h(addslashes($account['admin_name'])); ?>?');" style="margin:0;">
                                            <input type="hidden" name="csrf_token" value="<?= h($csrf); ?>">
                                            <input type="hidden" name="action" value="delete_user_account">
                                            <input type="hidden" name="user_role" value="Admin">
                                            <input type="hidden" name="user_id" value="<?= (int) ($account['id'] ?? 0); ?>">
                                            <button type="submit" class="user-delete-icon-btn" title="Delete Administrator">
                                                <?= admin_icon('trash'); ?>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Panel 2: Staff Health Stations Grid Panel -->
            <section class="panel-card user-panel hidden" data-panel="staff-stations" id="staffStationsPanel">
                <div class="user-panel-header">
                    <button type="button" class="link-button user-back-btn" data-back="selection">
                        <?= admin_icon('arrow-left'); ?>
                        <span>Back</span>
                    </button>
                    <div class="user-panel-title-wrap">
                        <h2>Health Station Staff</h2>
                        <span class="user-panel-count-badge"><?= count($healthStationList); ?> Health Stations</span>
                    </div>
                </div>
                <div class="user-station-grid">
                    <?php foreach ($healthStationList as $station): ?>
                        <?php $count = count($staffByStation[$station['slug']] ?? []); ?>
                        <article class="user-station-card badge-border-<?= h($station['color'] ?? 'mint'); ?>" data-station="<?= h($station['slug']); ?>">
                            <div class="user-station-card-head">
                                <div class="station-icon-avatar badge-<?= h($station['color'] ?? 'mint'); ?>">
                                    <?= admin_icon('map'); ?>
                                </div>
                                <span class="user-station-staff-pill <?= $count > 0 ? 'active' : 'empty'; ?>">
                                    <strong><?= $count; ?></strong> Staff Account<?= $count === 1 ? '' : 's'; ?>
                                </span>
                            </div>
                            <div class="user-station-card-body">
                                <h3><?= h($station['name']); ?></h3>
                                <p><?= h($station['detail_location']); ?></p>
                            </div>
                            <div class="user-station-card-foot">
                                <span class="station-hours-label"><?= admin_icon('clock'); ?> <?= h($station['full_hours'] ?? 'Mon - Sat, 8AM - 5PM'); ?></span>
                                <span class="station-open-action">View Staff <?= admin_icon('arrow-right'); ?></span>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Panel 3: Staff List for Specific Station Panel -->
            <section class="panel-card user-panel hidden" data-panel="staff-list" id="staffListPanel">
                <div class="user-panel-header">
                    <button type="button" class="link-button user-back-btn" data-back="stations">
                        <?= admin_icon('arrow-left'); ?>
                        <span>Back</span>
                    </button>
                    <div class="user-panel-title-wrap">
                        <h2 id="staff-list-heading">Staff Accounts</h2>
                        <p id="staff-list-subtitle" class="muted-text">Staff assigned to this station</p>
                    </div>
                </div>
                <div class="user-list-container" id="staff-list-container">
                    <?php foreach ($healthStationList as $station): ?>
                        <div class="staff-list-grid hidden" data-staff-list-for="<?= h($station['slug']); ?>">
                            <?php if (empty($staffByStation[$station['slug']])): ?>
                                <div class="user-empty-state-box">
                                    <div class="empty-state-icon staff"><?= admin_icon('user'); ?></div>
                                    <strong>No staff accounts assigned to <?= h($station['name']); ?> yet.</strong>
                                    <p>Click "New User" to add healthcare staff to this station.</p>
                                </div>
                            <?php else: ?>
                                <div class="user-organized-list">
                                    <?php foreach ($staffByStation[$station['slug']] as $account): 
                                        $initials = '';
                                        $nameParts = preg_split('/\s+/', trim($account['staff_name'])) ?: [];
                                        if (count($nameParts) >= 2) {
                                            $initials = strtoupper(substr($nameParts[0], 0, 1) . substr(end($nameParts), 0, 1));
                                        } else {
                                            $initials = strtoupper(substr($account['staff_name'], 0, 2));
                                        }
                                    ?>
                                        <div class="user-detail-row-card staff-variant">
                                            <div class="user-row-left">
                                                <div class="user-avatar-initials staff">
                                                    <?= h($initials); ?>
                                                </div>
                                                <div class="user-row-info">
                                                    <div class="user-row-header-line">
                                                        <strong class="user-name"><?= h($account['staff_name']); ?></strong>
                                                        <span class="user-role-tag staff">
                                                            <?= admin_icon('stethoscope'); ?> Station Staff
                                                        </span>
                                                    </div>
                                                    <div class="user-row-meta-line">
                                                        <span class="user-office-tag"><?= admin_icon('map'); ?> <?= h($account['station_name']); ?></span>
                                                        <span class="user-email-tag"><?= admin_icon('mail'); ?> <a href="mailto:<?= h($account['email']); ?>"><?= h($account['email']); ?></a></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="user-row-right">
                                                <span class="user-status-indicator active">
                                                    <span class="dot"></span> Active
                                                </span>
                                                <form method="post" onsubmit="return confirm('Are you sure you want to remove staff account <?= h(addslashes($account['staff_name'])); ?>?');" style="margin:0;">
                                                    <input type="hidden" name="csrf_token" value="<?= h($csrf); ?>">
                                                    <input type="hidden" name="action" value="delete_user_account">
                                                    <input type="hidden" name="user_role" value="Staff">
                                                    <input type="hidden" name="user_id" value="<?= (int) ($account['id'] ?? 0); ?>">
                                                    <button type="submit" class="user-delete-icon-btn" title="Delete Staff Account">
                                                        <?= admin_icon('trash'); ?>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Create New User Modal Dialog -->
            <div id="userModalBackdrop" class="user-modal-backdrop <?= $showUserModal ? '' : 'hidden'; ?>">
                <div class="user-modal-card" id="userModalCard">
                    <div class="user-modal-head">
                        <div class="user-modal-head-info">
                            <div class="user-modal-head-icon admin" id="modalRoleIndicator">
                                <?= admin_icon('user-add'); ?>
                            </div>
                            <div>
                                <h2>Create New Account</h2>
                                <p class="muted-text">Add an administrator or barangay health station staff</p>
                            </div>
                        </div>
                        <button type="button" class="user-modal-close-btn" id="closeUserModalBtn" title="Close dialog">
                            <?= admin_icon('x'); ?>
                        </button>
                    </div>

                    <form method="post" class="user-create-form" id="user-create-form">
                        <input type="hidden" name="csrf_token" value="<?= h($csrf); ?>">
                        <input type="hidden" name="action" id="create-action" value="create_admin_account">
                        <input type="hidden" name="admin_name" id="admin-name-hidden" value="">
                        <input type="hidden" name="staff_name" id="staff-name-hidden" value="">

                        <!-- Interactive Role Selector Cards -->
                        <div class="modal-role-selector-wrap">
                            <label class="modal-role-option" data-role="Admin">
                                <input type="radio" name="role_choice" value="Admin" checked class="sr-only-radio">
                                <div class="role-selector-card role-card-admin">
                                    <div class="role-selector-icon admin">
                                        <?= admin_icon('shield'); ?>
                                    </div>
                                    <div class="role-selector-text">
                                        <strong>Administrator</strong>
                                        <small>Central Admin Privileges</small>
                                    </div>
                                    <div class="role-selected-dot"></div>
                                </div>
                            </label>

                            <label class="modal-role-option" data-role="Staff">
                                <input type="radio" name="role_choice" value="Staff" class="sr-only-radio">
                                <div class="role-selector-card role-card-staff">
                                    <div class="role-selector-icon staff">
                                        <?= admin_icon('stethoscope'); ?>
                                    </div>
                                    <div class="role-selector-text">
                                        <strong>Station Staff</strong>
                                        <small>Health Center Personnel</small>
                                    </div>
                                    <div class="role-selected-dot"></div>
                                </div>
                            </label>
                        </div>

                        <!-- Form Fields Container -->
                        <div class="user-form-fields-grid">
                            <div class="form-field-group">
                                <label for="user-name">
                                    <span id="name-field-label">Admin Full Name</span>
                                    <div class="field-input-wrapper">
                                        <span class="field-prefix-icon"><?= admin_icon('user'); ?></span>
                                        <input type="text" id="user-name" placeholder="e.g. Dr. Maria Santos" required>
                                    </div>
                                </label>
                            </div>

                            <div class="form-field-group">
                                <label for="user-email-input">
                                    <span>Email Address</span>
                                    <div class="field-input-wrapper">
                                        <span class="field-prefix-icon"><?= admin_icon('mail'); ?></span>
                                        <input type="email" name="email" id="user-email-input" placeholder="e.g. name@bacolodhealth.ph" required>
                                    </div>
                                </label>
                            </div>

                            <div class="form-field-group role-field admin-only" id="admin-office-group">
                                <label for="office-name">
                                    <span>Office / Department</span>
                                    <div class="field-input-wrapper">
                                        <span class="field-prefix-icon"><?= admin_icon('shield'); ?></span>
                                        <input type="text" name="office_name" id="office-name" placeholder="Bacolod City Health Office" value="Bacolod City Health Central Office">
                                    </div>
                                </label>
                            </div>

                            <div class="form-field-group role-field staff-only hidden" id="staff-station-group">
                                <label for="assigned-station">
                                    <span>Assigned Barangay Station</span>
                                    <div class="field-input-wrapper">
                                        <span class="field-prefix-icon"><?= admin_icon('map'); ?></span>
                                        <select name="station_slug" id="assigned-station">
                                            <option value="">Select Barangay Station</option>
                                            <?php foreach ($healthStationList as $station): ?>
                                                <option value="<?= h($station['slug']); ?>"><?= h($station['name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </label>
                            </div>

                            <div class="form-field-group full-width">
                                <label for="user-password-input">
                                    <span>Password</span>
                                    <div class="field-input-wrapper user-password-wrap">
                                        <span class="field-prefix-icon"><?= admin_icon('lock'); ?></span>
                                        <input type="password" name="password" id="user-password-input" placeholder="Create a secure password" required minlength="6">
                                        <button type="button" class="user-pw-toggle" onclick="toggleUserPassword()" title="Toggle visibility">
                                            <?= admin_icon('eye'); ?>
                                        </button>
                                    </div>
                                    <small class="field-hint">Minimum of 6 characters with a combination of letters and numbers</small>
                                </label>
                            </div>
                        </div>

                        <div class="user-modal-footer">
                            <button type="button" class="modal-cancel-btn" id="cancelUserModalBtn">Cancel</button>
                            <button type="submit" class="modal-submit-btn green-btn" id="userSubmitBtn">
                                <?= admin_icon('check'); ?>
                                <span>Create Account</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                (function() {
                    const selectionGrid = document.getElementById('userSelectionGrid');
                    const selectionCards = document.querySelectorAll('[data-user-panel]');
                    const panels = document.querySelectorAll('.user-panel');
                    const panelBackButtons = document.querySelectorAll('[data-back]');
                    const stationCards = document.querySelectorAll('.user-station-card');
                    const staffListHeading = document.getElementById('staff-list-heading');
                    const staffListSubtitle = document.getElementById('staff-list-subtitle');
                    const staffLists = document.querySelectorAll('[data-staff-list-for]');
                    const roleRadios = document.querySelectorAll('input[name="role_choice"]');
                    const nameFieldLabel = document.getElementById('name-field-label');
                    const userNameInput = document.getElementById('user-name');
                    const userEmailInput = document.getElementById('user-email-input');
                    const createActionInput = document.getElementById('create-action');
                    const adminNameHidden = document.getElementById('admin-name-hidden');
                    const staffNameHidden = document.getElementById('staff-name-hidden');
                    const assignedStationField = document.getElementById('assigned-station');
                    const adminOnlyFields = document.querySelectorAll('.admin-only');
                    const staffOnlyFields = document.querySelectorAll('.staff-only');
                    const createForm = document.getElementById('user-create-form');
                    const modalBackdrop = document.getElementById('userModalBackdrop');
                    const modalRoleIndicator = document.getElementById('modalRoleIndicator');
                    const openModalBtn = document.getElementById('openUserModalBtn');
                    const closeModalBtn = document.getElementById('closeUserModalBtn');
                    const cancelModalBtn = document.getElementById('cancelUserModalBtn');

                    const showPanel = (panelName) => {
                        if (selectionGrid) selectionGrid.classList.add('hidden');
                        panels.forEach(panel => panel.classList.add('hidden'));
                        const target = document.querySelector(`[data-panel="${panelName}"]`);
                        if (target) {
                            target.classList.remove('hidden');
                        }
                    };

                    const resetSelection = () => {
                        panels.forEach(panel => panel.classList.add('hidden'));
                        if (selectionGrid) selectionGrid.classList.remove('hidden');
                    };

                    selectionCards.forEach(card => {
                        card.addEventListener('click', () => {
                            const panel = card.dataset.userPanel;
                            if (panel === 'admin') {
                                showPanel('admin');
                            } else if (panel === 'staff') {
                                showPanel('staff-stations');
                            }
                        });
                    });

                    panelBackButtons.forEach(button => {
                        button.addEventListener('click', (e) => {
                            e.preventDefault();
                            const back = button.dataset.back;
                            if (back === 'selection') {
                                resetSelection();
                            } else if (back === 'stations') {
                                showPanel('staff-stations');
                            }
                        });
                    });

                    stationCards.forEach(card => {
                        card.addEventListener('click', () => {
                            const station = card.dataset.station;
                            if (!station) return;
                            showPanel('staff-list');
                            staffLists.forEach(list => {
                                if (list.dataset.staffListFor === station) {
                                    list.classList.remove('hidden');
                                } else {
                                    list.classList.add('hidden');
                                }
                            });
                            const stationName = card.querySelector('h3')?.textContent || 'Staff Accounts';
                            if (staffListHeading) staffListHeading.textContent = stationName + ' Staff';
                            if (staffListSubtitle) staffListSubtitle.textContent = `Assigned personnel for ${stationName}`;
                        });
                    });

                    const setRole = (role) => {
                        if (role === 'Admin') {
                            if (createActionInput) createActionInput.value = 'create_admin_account';
                            if (nameFieldLabel) nameFieldLabel.textContent = 'Admin Full Name';
                            if (userNameInput) userNameInput.placeholder = 'e.g. Dr. Maria Santos';
                            if (userEmailInput) userEmailInput.placeholder = 'e.g. admin@bacolodhealth.ph';
                            adminOnlyFields.forEach(el => el.classList.remove('hidden'));
                            staffOnlyFields.forEach(el => el.classList.add('hidden'));
                            if (assignedStationField) assignedStationField.required = false;
                            if (modalRoleIndicator) {
                                modalRoleIndicator.className = 'user-modal-head-icon admin';
                            }
                        } else {
                            if (createActionInput) createActionInput.value = 'create_staff_account';
                            if (nameFieldLabel) nameFieldLabel.textContent = 'Staff Full Name';
                            if (userNameInput) userNameInput.placeholder = 'e.g. Nurse Juan Dela Cruz';
                            if (userEmailInput) userEmailInput.placeholder = 'e.g. staff-bata@bata.health or leo@bata.health';
                            adminOnlyFields.forEach(el => el.classList.add('hidden'));
                            staffOnlyFields.forEach(el => el.classList.remove('hidden'));
                            if (assignedStationField) assignedStationField.required = true;
                            if (modalRoleIndicator) {
                                modalRoleIndicator.className = 'user-modal-head-icon staff';
                            }
                        }
                    };

                    roleRadios.forEach(radio => {
                        radio.addEventListener('change', () => {
                            if (radio.checked) {
                                setRole(radio.value);
                            }
                        });
                    });

                    if (createForm) {
                        createForm.addEventListener('submit', () => {
                            const checkedRadio = document.querySelector('input[name="role_choice"]:checked');
                            const role = checkedRadio ? checkedRadio.value : 'Admin';
                            const nameValue = userNameInput ? userNameInput.value.trim() : '';
                            if (adminNameHidden) adminNameHidden.value = role === 'Admin' ? nameValue : '';
                            if (staffNameHidden) staffNameHidden.value = role === 'Staff' ? nameValue : '';
                        });
                    }

                    const openModal = () => {
                        if (modalBackdrop) modalBackdrop.classList.remove('hidden');
                    };

                    const closeModal = () => {
                        if (modalBackdrop) modalBackdrop.classList.add('hidden');
                    };

                    if (openModalBtn) openModalBtn.addEventListener('click', openModal);
                    if (closeModalBtn) closeModalBtn.addEventListener('click', closeModal);
                    if (cancelModalBtn) cancelModalBtn.addEventListener('click', closeModal);

                    if (modalBackdrop) {
                        modalBackdrop.addEventListener('click', (e) => {
                            if (e.target === modalBackdrop) {
                                closeModal();
                            }
                        });
                    }

                    document.addEventListener('keydown', (e) => {
                        if (e.key === 'Escape' && modalBackdrop && !modalBackdrop.classList.contains('hidden')) {
                            closeModal();
                        }
                    });
                })();

                function toggleUserPassword() {
                    const pwInput = document.getElementById('user-password-input');
                    if (pwInput) {
                        pwInput.type = pwInput.type === 'password' ? 'text' : 'password';
                    }
                }
            </script>
           <?php else: ?>
            <?php
                $activeFilterCount = 0;
                if ($reportGender !== '' && strtolower($reportGender) !== 'all') $activeFilterCount++;
                if ($reportAgeGroup !== '' && strtolower($reportAgeGroup) !== 'all') $activeFilterCount++;
                if ($reportStation !== '' && strtolower($reportStation) !== 'all') $activeFilterCount++;
                if ($reportService !== '' && strtolower($reportService) !== 'all') $activeFilterCount++;
                if ($reportStatus !== '' && strtolower($reportStatus) !== 'all') $activeFilterCount++;

                $selectedStationName = '';
                if ($reportStation !== '' && isset($stationLookup[$reportStation])) {
                    $selectedStationName = $stationLookup[$reportStation]['name'];
                }

                $selectedServiceName = '';
                if ($reportService !== '' && isset($serviceCatalog[$reportService])) {
                    $selectedServiceName = $serviceCatalog[$reportService]['title'];
                }

                $ageGroupLabels = [
                    '0-12' => 'Infants & Children (0-12y)',
                    'pediatric' => 'Infants & Children (0-12y)',
                    '13-17' => 'Adolescents (13-17y)',
                    'adolescent' => 'Adolescents (13-17y)',
                    '18-59' => 'Adults (18-59y)',
                    'adult' => 'Adults (18-59y)',
                    '60+' => 'Seniors (60y+)',
                    'senior' => 'Seniors (60y+)',
                ];
                $selectedAgeLabel = $ageGroupLabels[$reportAgeGroup] ?? $reportAgeGroup;

                $maxAppts    = max(1, max($monthlyTrends['appointments'] ?: [1]));
                $maxPatients = max(1, max($monthlyTrends['patients'] ?: [1]));
                $maxBarHeight = 160;
            ?>
            <section class="page-header action-head reports-page-head">
                <div class="action-head-copy">
                    <h1>Reports &amp; Health Analytics</h1>
                    <p>Clinical metrics, demographic breakdowns, and station performance reports</p>
                </div>
                <div class="header-actions">
                    <button type="button" class="dash-hero-btn secondary reports-filter-btn" onclick="openReportsFilterModal()">
                        <?= admin_icon('filter'); ?>
                        <span>Filter Reports</span>
                        <?php if ($activeFilterCount > 0): ?>
                            <span class="filter-count-badge"><?= $activeFilterCount; ?></span>
                        <?php endif; ?>
                    </button>
                    <a href="?page=reports&export=csv&<?= http_build_query(array_filter($reportFilters)); ?>" class="dash-hero-btn primary" title="Download matching appointment records as CSV">
                        <?= admin_icon('download'); ?>
                        <span>Export CSV</span>
                    </a>
                </div>
            </section>

            <!-- Active Filter Chips Bar -->
            <section class="report-active-chips-bar">
                <div class="report-chips-label">
                    <span>Active Filters:</span>
                </div>
                <div class="report-chips-list">
                    <div class="report-chip date">
                        <span>📅 <?= h(date('M j, Y', strtotime($reportFrom))); ?> &ndash; <?= h(date('M j, Y', strtotime($reportTo))); ?></span>
                    </div>

                    <?php if ($reportGender !== '' && strtolower($reportGender) !== 'all'): ?>
                        <?php 
                            $removeGenderQuery = $reportFilters; 
                            unset($removeGenderQuery['gender']);
                        ?>
                        <div class="report-chip filter">
                            <span>Gender: <strong><?= h(ucfirst($reportGender)); ?></strong></span>
                            <a href="?page=reports&<?= http_build_query(array_filter($removeGenderQuery)); ?>" title="Remove filter">&times;</a>
                        </div>
                    <?php endif; ?>

                    <?php if ($reportAgeGroup !== '' && strtolower($reportAgeGroup) !== 'all'): ?>
                        <?php 
                            $removeAgeQuery = $reportFilters; 
                            unset($removeAgeQuery['age_group']);
                        ?>
                        <div class="report-chip filter">
                            <span>Age: <strong><?= h($selectedAgeLabel); ?></strong></span>
                            <a href="?page=reports&<?= http_build_query(array_filter($removeAgeQuery)); ?>" title="Remove filter">&times;</a>
                        </div>
                    <?php endif; ?>

                    <?php if ($reportStation !== '' && strtolower($reportStation) !== 'all'): ?>
                        <?php 
                            $removeStationQuery = $reportFilters; 
                            unset($removeStationQuery['station_slug']);
                        ?>
                        <div class="report-chip filter">
                            <span>Station: <strong><?= h($selectedStationName ?: $reportStation); ?></strong></span>
                            <a href="?page=reports&<?= http_build_query(array_filter($removeStationQuery)); ?>" title="Remove filter">&times;</a>
                        </div>
                    <?php endif; ?>

                    <?php if ($reportService !== '' && strtolower($reportService) !== 'all'): ?>
                        <?php 
                            $removeServiceQuery = $reportFilters; 
                            unset($removeServiceQuery['service_slug']);
                        ?>
                        <div class="report-chip filter">
                            <span>Service: <strong><?= h($selectedServiceName ?: $reportService); ?></strong></span>
                            <a href="?page=reports&<?= http_build_query(array_filter($removeServiceQuery)); ?>" title="Remove filter">&times;</a>
                        </div>
                    <?php endif; ?>

                    <?php if ($reportStatus !== '' && strtolower($reportStatus) !== 'all'): ?>
                        <?php 
                            $removeStatusQuery = $reportFilters; 
                            unset($removeStatusQuery['status']);
                        ?>
                        <div class="report-chip filter">
                            <span>Status: <strong><?= h($reportStatus); ?></strong></span>
                            <a href="?page=reports&<?= http_build_query(array_filter($removeStatusQuery)); ?>" title="Remove filter">&times;</a>
                        </div>
                    <?php endif; ?>

                    <?php if ($activeFilterCount > 0): ?>
                        <a href="?page=reports&report_from=<?= h($reportFrom); ?>&report_to=<?= h($reportTo); ?>" class="report-clear-all-btn">Clear all filters</a>
                    <?php endif; ?>
                </div>
            </section>

            <!-- KPI Metric Summary Cards -->
            <section class="dash-stat-grid report-stat-grid">
                <article class="dash-stat-card theme-emerald">
                    <div class="dash-stat-top">
                        <div class="dash-stat-icon"><?= admin_icon('patients'); ?></div>
                        <span class="dash-stat-tag">♀ <?= $demographics['gender']['female']['pct']; ?>% | ♂ <?= $demographics['gender']['male']['pct']; ?>%</span>
                    </div>
                    <div class="dash-stat-body">
                        <h3><?= number_format($reportStats['total_patients']); ?></h3>
                        <p>Patients Served</p>
                    </div>
                    <div class="dash-stat-footer">
                        <span>Unique completed patients in period</span>
                    </div>
                </article>

                <article class="dash-stat-card theme-blue">
                    <div class="dash-stat-top">
                        <div class="dash-stat-icon"><?= admin_icon('appointments'); ?></div>
                        <span class="dash-stat-tag"><?= number_format($reportStats['services_rendered']); ?> Rendered</span>
                    </div>
                    <div class="dash-stat-body">
                        <h3><?= number_format($reportStats['total_bookings']); ?></h3>
                        <p>Total Bookings</p>
                    </div>
                    <div class="dash-stat-footer">
                        <span>All matching appointment records</span>
                    </div>
                </article>

                <article class="dash-stat-card theme-indigo">
                    <div class="dash-stat-top">
                        <div class="dash-stat-icon"><?= admin_icon('check'); ?></div>
                        <span class="dash-stat-tag"><?= number_format($reportStats['completed_count']); ?> Completed</span>
                    </div>
                    <div class="dash-stat-body">
                        <h3><?= $reportStats['utilization_pct']; ?>%</h3>
                        <p>Completion Rate</p>
                    </div>
                    <div class="dash-stat-footer">
                        <span>Fulfilled vs total scheduled bookings</span>
                    </div>
                </article>

                <article class="dash-stat-card theme-amber">
                    <div class="dash-stat-top">
                        <div class="dash-stat-icon"><?= admin_icon('clock'); ?></div>
                        <span class="dash-stat-tag">Over <?= $reportStats['day_count']; ?> Days</span>
                    </div>
                    <div class="dash-stat-body">
                        <h3><?= number_format($reportStats['avg_daily'], 1); ?></h3>
                        <p>Daily Patient Volume</p>
                    </div>
                    <div class="dash-stat-footer">
                        <span>Average daily booking volume</span>
                    </div>
                </article>
            </section>

            <?php
                $maxCompletedCount = max(1, ...array_map(static fn(array $b): int => (int) $b['completed_count'], $barangayCompletedStats));
                $totalCompletedAllStations = array_sum(array_map(static fn(array $b): int => (int) $b['completed_count'], $barangayCompletedStats));
                $totalDemandBookings = array_sum(array_map(static fn(array $s): int => (int) $s['total'], $servicePerformance)) ?: 1;
            ?>

            <!-- Barangay Patient Completion Analytics Graph (Interactive Hover Tooltip) -->
            <section class="panel-card brgy-completion-graph-card">
                <div class="dash-card-head">
                    <div class="brgy-graph-head-left">
                        <div class="brgy-graph-icon-badge">
                            <?= admin_icon('community'); ?>
                        </div>
                        <div>
                            <h3>Completed Appointments by Barangay</h3>
                            <p>Number of patients who have completed their appointments per health station. Hover over any bar to view utilized services.</p>
                        </div>
                    </div>
                    <div class="brgy-graph-meta-right">
                        <div class="brgy-graph-pill">
                            <span class="dot green"></span>
                            <strong><?= number_format($totalCompletedAllStations); ?></strong> Total Completed
                        </div>
                    </div>
                </div>

                <div class="brgy-chart-viewport">
                    <div class="brgy-chart-canvas">
                        <!-- Background Grid Lines -->
                        <div class="brgy-grid-lines">
                            <div class="grid-line"><span><?= $maxCompletedCount; ?></span></div>
                            <div class="grid-line"><span><?= (int) round($maxCompletedCount * 0.75); ?></span></div>
                            <div class="grid-line"><span><?= (int) round($maxCompletedCount * 0.5); ?></span></div>
                            <div class="grid-line"><span><?= (int) round($maxCompletedCount * 0.25); ?></span></div>
                            <div class="grid-line"><span>0</span></div>
                        </div>

                        <!-- Bar Columns Track -->
                        <div class="brgy-bars-track">
                            <?php foreach ($barangayCompletedStats as $idx => $st): ?>
                                <?php
                                    $cCount = (int) $st['completed_count'];
                                    $uPatients = (int) $st['unique_patients'];
                                    $barHeight = $maxCompletedCount > 0 ? round(($cCount / $maxCompletedCount) * 160) : 0;
                                    $hasCompleted = $cCount > 0;
                                    $srvList = $st['services'] ?? [];
                                    // Align tooltip for edge items
                                    $alignClass = $idx < 2 ? 'tooltip-align-left' : ($idx >= count($barangayCompletedStats) - 2 ? 'tooltip-align-right' : 'tooltip-align-center');
                                ?>
                                <div class="brgy-bar-item <?= $hasCompleted ? 'has-data' : 'no-data'; ?> <?= $alignClass; ?>" tabindex="0">
                                    <!-- Value Badge -->
                                    <span class="brgy-bar-val-badge <?= $hasCompleted ? 'active' : ''; ?>">
                                        <?= $cCount; ?>
                                    </span>

                                    <!-- Bar Visual -->
                                    <div class="brgy-bar-pillar-wrap">
                                        <div class="brgy-bar-pillar <?= $hasCompleted ? 'active-fill' : 'empty-fill'; ?>" style="height: <?= max(8, $barHeight); ?>px;">
                                            <?php if ($hasCompleted): ?>
                                                <div class="brgy-bar-gloss"></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Station Label -->
                                    <div class="brgy-bar-label-wrap">
                                        <span class="brgy-bar-name" title="<?= h($st['station_name']); ?>"><?= h($st['barangay_name']); ?></span>
                                    </div>

                                    <!-- Interactive Floating Pop-up Details Card -->
                                    <div class="brgy-hover-card">
                                        <div class="brgy-hover-head">
                                            <div class="brgy-hover-station-name">
                                                <strong><?= h($st['station_name']); ?></strong>
                                                <span class="brgy-hover-badge <?= $hasCompleted ? 'completed' : 'muted'; ?>">
                                                    <?= $hasCompleted ? admin_icon('check') : admin_icon('clock'); ?>
                                                    <?= $cCount; ?> Completed (<?= $uPatients; ?> Patient<?= $uPatients === 1 ? '' : 's'; ?>)
                                                </span>
                                            </div>
                                        </div>

                                        <div class="brgy-hover-body">
                                            <?php if ($hasCompleted && !empty($srvList)): ?>
                                                <div class="brgy-hover-section-title">
                                                    <?= admin_icon('activity'); ?>
                                                    <span>Services Utilized</span>
                                                </div>
                                                <div class="brgy-hover-services-list">
                                                    <?php foreach ($srvList as $srv): ?>
                                                        <?php
                                                            $srvIcon = $srv['icon'] ?? 'appointments';
                                                            $srvColor = $srv['color'] ?? 'mint';
                                                        ?>
                                                        <div class="brgy-hover-srv-item">
                                                            <div class="brgy-hover-srv-row">
                                                                <div class="brgy-hover-srv-info">
                                                                    <span class="brgy-srv-icon-badge <?= h($srvColor); ?>">
                                                                        <?= admin_icon($srvIcon); ?>
                                                                    </span>
                                                                    <span class="brgy-srv-name"><?= h($srv['service_name']); ?></span>
                                                                </div>
                                                                <div class="brgy-hover-srv-stat">
                                                                    <strong><?= $srv['count']; ?></strong>
                                                                    <small>(<?= $srv['pct']; ?>%)</small>
                                                                </div>
                                                            </div>
                                                            <div class="brgy-hover-srv-progress">
                                                                <div class="brgy-hover-srv-bar <?= h($srvColor); ?>" style="width: <?= max(6, $srv['pct']); ?>%;"></div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="brgy-hover-empty">
                                                    <p>No completed appointments in this period</p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Charts Section (Monthly Trends & Program Demand Breakdown) -->
            <section class="dash-charts-grid">
                <article class="panel-card dash-chart-card">
                    <div class="dash-card-head">
                        <div>
                            <h3>Monthly Volume Trends</h3>
                            <p>Appointment volume &amp; unique patient check-ins</p>
                        </div>
                        <div class="dash-chart-legend">
                            <span class="legend-chip blue"><i class="dot"></i> Bookings</span>
                            <span class="legend-chip green"><i class="dot"></i> Patients</span>
                        </div>
                    </div>
                    <?php if (count($monthlyTrends['months']) > 0): ?>
                        <div class="report-monthly-bar-chart">
                            <?php foreach ($monthlyTrends['months'] as $i => $month): ?>
                                <?php
                                    $apptH = (int) round(($monthlyTrends['appointments'][$i] / $maxAppts) * $maxBarHeight);
                                    $patH  = (int) round(($monthlyTrends['patients'][$i] / $maxPatients) * $maxBarHeight);
                                    $apptH = max(6, $apptH);
                                    $patH  = max(6, $patH);
                                ?>
                                <div class="report-bar-col">
                                    <div class="report-bar-pair">
                                        <span class="bar appts" style="height: <?= $apptH; ?>px" title="<?= $monthlyTrends['appointments'][$i]; ?> Bookings">
                                            <small><?= $monthlyTrends['appointments'][$i]; ?></small>
                                        </span>
                                        <span class="bar patients" style="height: <?= $patH; ?>px" title="<?= $monthlyTrends['patients'][$i]; ?> Patients">
                                            <small><?= $monthlyTrends['patients'][$i]; ?></small>
                                        </span>
                                    </div>
                                    <label><?= h($month); ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">No trend records found for this period.</div>
                    <?php endif; ?>
                </article>

                <!-- Program Demand Breakdown Card (Like in Dashboard) -->
                <article class="panel-card dash-service-util-card">
                    <div class="dash-card-head">
                        <div>
                            <h3>Program Demand Breakdown</h3>
                            <p>Most requested healthcare services across centers</p>
                        </div>
                    </div>
                    <div class="dash-service-bars">
                        <?php if (empty($servicePerformance)): ?>
                            <div class="empty-state">No service demand records found for this period.</div>
                        <?php else: ?>
                            <?php foreach ($servicePerformance as $srv): ?>
                                <?php 
                                    $val = (int) $srv['total'];
                                    $completedCount = (int) $srv['completed'];
                                    $pct = round(($val / $totalDemandBookings) * 100);
                                    $srvMeta = $serviceCatalog[$srv['service_slug']] ?? null;
                                    $srvColor = $srvMeta['color'] ?? 'mint';
                                    $srvIcon = $srvMeta['icon'] ?? 'appointments';
                                ?>
                                <div class="dash-util-row">
                                    <div class="dash-util-meta">
                                        <span class="dash-util-title">
                                            <span class="srv-mini-icon <?= h($srvColor); ?>"><?= admin_icon($srvIcon); ?></span>
                                            <?= h($srv['service_name']); ?>
                                        </span>
                                        <span class="dash-util-count">
                                            <strong><?= $val; ?></strong> bookings (<?= $pct; ?>%)
                                            <span class="dash-util-completed-tag"><?= $completedCount; ?> completed</span>
                                        </span>
                                    </div>
                                    <div class="dash-util-track">
                                        <div class="dash-util-fill fill-<?= h($srvColor); ?>" style="width: <?= max(4, $pct); ?>%;"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </article>
            </section>

            <!-- Demographics & Station Performance Analytics -->
            <section class="dash-bottom-grid">
                <article class="panel-card dash-demographics-card">
                    <div class="dash-card-head">
                        <div>
                            <h3>Demographic Distribution</h3>
                            <p>Age group and gender breakdown for filtered period</p>
                        </div>
                    </div>
                    
                    <div class="demo-section">
                        <h4>Age Group Breakdown</h4>
                        <div class="demo-bars-stack">
                            <?php foreach ($demographics['age_groups'] as $key => $ageData): ?>
                                <div class="demo-bar-row">
                                    <div class="demo-bar-meta">
                                        <span><?= h($ageData['label']); ?></span>
                                        <strong><?= $ageData['count']; ?> (<?= $ageData['pct']; ?>%)</strong>
                                    </div>
                                    <div class="demo-bar-track">
                                        <div class="demo-bar-fill age-<?= h($key); ?>" style="width: <?= max(3, $ageData['pct']); ?>%;"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="demo-section" style="margin-top: 18px;">
                        <h4>Gender Share</h4>
                        <div class="demo-gender-grid">
                            <div class="gender-card female">
                                <span class="gender-title">Female</span>
                                <h3><?= $demographics['gender']['female']['count']; ?></h3>
                                <small><?= $demographics['gender']['female']['pct']; ?>% share</small>
                            </div>
                            <div class="gender-card male">
                                <span class="gender-title">Male</span>
                                <h3><?= $demographics['gender']['male']['count']; ?></h3>
                                <small><?= $demographics['gender']['male']['pct']; ?>% share</small>
                            </div>
                            <?php if ($demographics['gender']['other']['count'] > 0): ?>
                                <div class="gender-card other">
                                    <span class="gender-title">Other</span>
                                    <h3><?= $demographics['gender']['other']['count']; ?></h3>
                                    <small><?= $demographics['gender']['other']['pct']; ?>% share</small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>

                <article class="panel-card">
                    <div class="dash-card-head">
                        <div>
                            <h3>Station Performance</h3>
                            <p>Volume and completion metrics by barangay center</p>
                        </div>
                    </div>
                    <?php if (!empty($stationPerformance)): ?>
                        <table class="data-table report-perf-table">
                            <thead>
                                <tr>
                                    <th>Station</th>
                                    <th>Completed</th>
                                    <th>Cancelled</th>
                                    <th>Pending</th>
                                    <th>Total</th>
                                    <th>Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stationPerformance as $sp): ?>
                                    <tr>
                                        <td><strong><?= h($sp['station_name']); ?></strong></td>
                                        <td class="text-green"><?= number_format($sp['completed']); ?></td>
                                        <td class="text-red"><?= number_format($sp['cancelled']); ?></td>
                                        <td class="text-muted"><?= number_format($sp['pending'] + $sp['serving'] + $sp['confirmed']); ?></td>
                                        <td><strong><?= number_format($sp['total']); ?></strong></td>
                                        <td>
                                            <?php $rate = $sp['completion_rate']; ?>
                                            <span class="rate-badge <?= $rate >= 70 ? 'high' : ($rate >= 40 ? 'med' : 'low'); ?>">
                                                <?= $rate; ?>%
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">No station performance records for this filter.</div>
                    <?php endif; ?>
                </article>
            </section>

            <!-- Filtered Appointment Records Table -->
            <section class="panel-card report-appointments-table-card" style="margin-top:20px;">
                <div class="dash-card-head">
                    <div>
                        <h3>Filtered Patient Visits &amp; Appointments</h3>
                        <p>Showing <?= count($reportAppointmentsList); ?> records matching active filter criteria</p>
                    </div>
                </div>
                <?php if ($reportAppointmentsList === []): ?>
                    <div class="empty-state">No appointments match the selected filter combination.</div>
                <?php else: ?>
                    <div class="table-scroll-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Appt #</th>
                                    <th>Patient Name</th>
                                    <th>Age / Gender</th>
                                    <th>Health Station</th>
                                    <th>Service</th>
                                    <th>Date &amp; Time</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reportAppointmentsList as $appt): ?>
                                    <?php 
                                        $apptBirth = (string) ($appt['birth_date'] ?? '');
                                        $apptAge = $apptBirth !== '' ? (int) date_diff(new DateTimeImmutable($apptBirth), new DateTimeImmutable('today'))->y : 0;
                                        $statusClass = strtolower(str_replace(' ', '-', (string) ($appt['status'] ?? 'pending')));
                                    ?>
                                    <tr>
                                        <td style="font-family:monospace;font-weight:700;color:#3b82f6;">
                                            #<?= h((string) ($appt['appointment_code'] ?: $appt['reference_code'])); ?>
                                        </td>
                                        <td>
                                            <strong><?= h(full_name($appt)); ?></strong>
                                            <small style="color:#64748b;"><?= h((string) $appt['contact_number']); ?></small>
                                        </td>
                                        <td><?= $apptAge; ?>y / <?= h((string) ($appt['gender'] ?? '')); ?></td>
                                        <td><?= h((string) $appt['station_name']); ?></td>
                                        <td><span class="report-service-tag"><?= h((string) $appt['service_name']); ?></span></td>
                                        <td>
                                            <div><?= h(date('M j, Y', strtotime((string) $appt['preferred_date']))); ?></div>
                                            <small style="color:#94a3b8;"><?= h((string) $appt['preferred_time']); ?></small>
                                        </td>
                                        <td>
                                            <span class="status-pill status-<?= h($statusClass); ?>"><?= h((string) $appt['status']); ?></span>
                                        </td>
                                        <td>
                                            <button type="button" class="patient-action-btn view" title="View Consultation Details" data-record="<?= htmlspecialchars(json_encode($appt), ENT_QUOTES, 'UTF-8'); ?>" onclick="openReportVisitModal(this)">
                                                <?= admin_icon('eye'); ?>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Multi-Filter Interactive Modal -->
            <div id="reportsFilterModal" style="display:none;" class="service-modal-overlay report-modal-backdrop" onclick="if(event.target===this)closeReportsFilterModal()">
                <div class="service-modal-card report-filter-modal-card">
                    <!-- Modal Header -->
                    <div class="report-modal-header">
                        <div class="report-modal-header-left">
                            <div class="report-modal-icon-badge">
                                <?= admin_icon('filter'); ?>
                            </div>
                            <div>
                                <h2>Filter Reports &amp; Analytics</h2>
                                <p>Select multiple criteria to refine data across all health centers</p>
                            </div>
                        </div>
                        <button type="button" class="modal-close-btn report-modal-close" onclick="closeReportsFilterModal()">&times;</button>
                    </div>

                    <form method="get" action="" class="report-modal-form">
                        <input type="hidden" name="page" value="reports">
                        
                        <div class="report-modal-body">
                            <!-- Group 1: Date Range & Quick Presets -->
                            <div class="report-filter-group">
                                <div class="report-group-title">
                                    <span class="group-num">1</span>
                                    <span>Date Range &amp; Period</span>
                                </div>
                                <div class="date-preset-pills">
                                    <button type="button" class="preset-pill" onclick="setDatePreset('this_month', this)">This Month</button>
                                    <button type="button" class="preset-pill" onclick="setDatePreset('last_30_days', this)">Last 30 Days</button>
                                    <button type="button" class="preset-pill" onclick="setDatePreset('this_year', this)">This Year</button>
                                    <button type="button" class="preset-pill" onclick="setDatePreset('all_time', this)">All Time</button>
                                </div>
                                <div class="filter-two-col">
                                    <div class="clean-field">
                                        <label for="filterReportFrom">Start Date</label>
                                        <div class="clean-input-wrap">
                                            <input type="date" name="report_from" id="filterReportFrom" value="<?= h($reportFrom); ?>" required>
                                        </div>
                                    </div>
                                    <div class="clean-field">
                                        <label for="filterReportTo">End Date</label>
                                        <div class="clean-input-wrap">
                                            <input type="date" name="report_to" id="filterReportTo" value="<?= h($reportTo); ?>" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Group 2: Patient Demographics -->
                            <div class="report-filter-group">
                                <div class="report-group-title">
                                    <span class="group-num">2</span>
                                    <span>Patient Demographics</span>
                                </div>
                                <div class="clean-field">
                                    <label>Gender Demographic</label>
                                    <div class="gender-segmented-control">
                                        <label class="gender-segment-label">
                                            <input type="radio" name="gender" value="" <?= $reportGender === '' ? 'checked' : ''; ?>>
                                            <span class="segment-btn">All Genders</span>
                                        </label>
                                        <label class="gender-segment-label">
                                            <input type="radio" name="gender" value="Female" <?= strtolower($reportGender) === 'female' ? 'checked' : ''; ?>>
                                            <span class="segment-btn">Female</span>
                                        </label>
                                        <label class="gender-segment-label">
                                            <input type="radio" name="gender" value="Male" <?= strtolower($reportGender) === 'male' ? 'checked' : ''; ?>>
                                            <span class="segment-btn">Male</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="clean-field" style="margin-top: 14px;">
                                    <label for="filterAgeGroup">Age Group Bracket</label>
                                    <div class="clean-select-wrap">
                                        <select name="age_group" id="filterAgeGroup">
                                            <option value="" <?= $reportAgeGroup === '' ? 'selected' : ''; ?>>All Age Groups</option>
                                            <option value="0-12" <?= ($reportAgeGroup === '0-12' || $reportAgeGroup === 'pediatric') ? 'selected' : ''; ?>>Infants &amp; Children (0 &ndash; 12 years)</option>
                                            <option value="13-17" <?= ($reportAgeGroup === '13-17' || $reportAgeGroup === 'adolescent') ? 'selected' : ''; ?>>Adolescents (13 &ndash; 17 years)</option>
                                            <option value="18-59" <?= ($reportAgeGroup === '18-59' || $reportAgeGroup === 'adult') ? 'selected' : ''; ?>>Adults (18 &ndash; 59 years)</option>
                                            <option value="60+" <?= ($reportAgeGroup === '60+' || $reportAgeGroup === 'senior') ? 'selected' : ''; ?>>Senior Citizens (60+ years)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Group 3: Location & Health Service -->
                            <div class="report-filter-group">
                                <div class="report-group-title">
                                    <span class="group-num">3</span>
                                    <span>Facility, Service &amp; Status</span>
                                </div>
                                <div class="filter-two-col">
                                    <div class="clean-field">
                                        <label for="filterStation">Barangay Health Center</label>
                                        <div class="clean-select-wrap">
                                            <select name="station_slug" id="filterStation">
                                                <option value="" <?= $reportStation === '' ? 'selected' : ''; ?>>All Health Stations</option>
                                                <?php foreach ($stations as $station): ?>
                                                    <option value="<?= h($station['slug']); ?>" <?= $reportStation === $station['slug'] ? 'selected' : ''; ?>>
                                                        <?= h($station['name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="clean-field">
                                        <label for="filterService">Health Service Program</label>
                                        <div class="clean-select-wrap">
                                            <select name="service_slug" id="filterService">
                                                <option value="" <?= $reportService === '' ? 'selected' : ''; ?>>All Health Services</option>
                                                <?php foreach ($serviceCatalog as $slug => $srv): ?>
                                                    <option value="<?= h($slug); ?>" <?= $reportService === $slug ? 'selected' : ''; ?>>
                                                        <?= h($srv['title']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="clean-field" style="margin-top: 14px;">
                                    <label for="filterStatus">Appointment Status</label>
                                    <div class="clean-select-wrap">
                                        <select name="status_filter" id="filterStatus">
                                            <option value="" <?= $reportStatus === '' ? 'selected' : ''; ?>>All Statuses</option>
                                            <option value="Completed" <?= $reportStatus === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="Confirmed" <?= $reportStatus === 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                            <option value="Serving" <?= $reportStatus === 'Serving' ? 'selected' : ''; ?>>Serving</option>
                                            <option value="Pending" <?= $reportStatus === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="Cancelled" <?= $reportStatus === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="report-modal-footer">
                            <a href="?page=reports" class="report-modal-reset-link">
                                <?= admin_icon('history'); ?>
                                <span>Reset All Filters</span>
                            </a>
                            <div class="report-modal-footer-actions">
                                <button type="button" class="report-btn-secondary" onclick="closeReportsFilterModal()">Cancel</button>
                                <button type="submit" class="report-btn-primary">
                                    <?= admin_icon('check'); ?>
                                    <span>Apply Filters</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Consultation Record Inspection Modal on Reports Page -->
            <div id="reportVisitModal" style="display:none;" class="service-modal-overlay report-modal-backdrop" onclick="if(event.target===this)closeReportVisitModal()">
                <div class="service-modal-card clinical-modal-card-modern">
                    <div class="report-modal-header">
                        <div class="report-modal-header-left">
                            <div class="report-modal-icon-badge" style="background:#e8fbf3;color:#0db273;">
                                <?= admin_icon('check'); ?>
                            </div>
                            <div>
                                <h2 id="reportVisitCode">Consultation Record</h2>
                                <p id="reportVisitServiceStation">-</p>
                            </div>
                        </div>
                        <button type="button" class="modal-close-btn report-modal-close" onclick="closeReportVisitModal()">&times;</button>
                    </div>

                    <div class="report-modal-body clinical-modal-body">
                        <!-- Patient Overview Strip -->
                        <div class="clinical-patient-overview-strip">
                            <div id="reportVisitPhotoWrap" style="display:none;">
                                <img id="reportVisitPhoto" class="clinical-photo-modern" src="" alt="Patient photo">
                            </div>
                            <div id="reportVisitAvatar" class="patient-avatar-circle" style="display:none; width:54px; height:54px; font-size:1.2rem; background:#ecfdf5; color:#059669; border-radius:50%; align-items:center; justify-content:center; font-weight:700; flex-shrink:0;">
                                PT
                            </div>
                            <div class="clinical-overview-meta">
                                <h3 id="reportVisitName">-</h3>
                                <div class="clinical-meta-pills">
                                    <span id="reportVisitDemographics">-</span>
                                    <span id="reportVisitSchedule">-</span>
                                    <span id="reportVisitContact">-</span>
                                    <span id="reportVisitStatus" class="status-pill status-pending">-</span>
                                </div>
                                <div class="clinical-address-line">
                                    <?= admin_icon('map'); ?>
                                    <span id="reportVisitAddress">-</span>
                                </div>
                            </div>
                        </div>

                        <!-- Vital Signs Section -->
                        <div class="clinical-vitals-section">
                            <h4 class="clinical-section-title">Patient Vital Signs &amp; Triage</h4>
                            <div class="clinical-vitals-grid">
                                <div class="vital-metric-card">
                                    <span class="vital-label">Body Temperature</span>
                                    <strong class="vital-value" id="reportVisitTemp"><em class="not-set">Not recorded</em></strong>
                                </div>
                                <div class="vital-metric-card">
                                    <span class="vital-label">Pulse Rate</span>
                                    <strong class="vital-value" id="reportVisitPulse"><em class="not-set">Not recorded</em></strong>
                                </div>
                                <div class="vital-metric-card">
                                    <span class="vital-label">Respiration Rate</span>
                                    <strong class="vital-value" id="reportVisitResp"><em class="not-set">Not recorded</em></strong>
                                </div>
                                <div class="vital-metric-card">
                                    <span class="vital-label">Blood Pressure</span>
                                    <strong class="vital-value" id="reportVisitBp"><em class="not-set">Not recorded</em></strong>
                                </div>
                            </div>
                        </div>

                        <!-- Doctor's Notes -->
                        <div class="clinical-notes-section">
                            <h4 class="clinical-section-title">Physician &amp; Clinical Notes</h4>
                            <div class="clinical-notes-card">
                                <p id="reportVisitNotes"><em>No clinical notes or assessments recorded for this consultation.</em></p>
                            </div>
                        </div>
                    </div>

                    <div class="report-modal-footer" style="justify-content:flex-end;">
                        <button type="button" class="report-btn-secondary" onclick="closeReportVisitModal()">Close Record</button>
                    </div>
                </div>
            </div>

            <script>
            function openReportsFilterModal() {
                const modal = document.getElementById('reportsFilterModal');
                if (modal) {
                    modal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                }
            }
            function closeReportsFilterModal() {
                const modal = document.getElementById('reportsFilterModal');
                if (modal) {
                    modal.style.display = 'none';
                    const visitModal = document.getElementById('reportVisitModal');
                    if (!visitModal || visitModal.style.display === 'none') {
                        document.body.style.overflow = '';
                    }
                }
            }

            function openReportVisitModal(buttonEl) {
                if (!buttonEl) return;
                try {
                    const raw = buttonEl.getAttribute('data-record');
                    const data = JSON.parse(raw || '{}');
                    
                    const modal = document.getElementById('reportVisitModal');
                    if (!modal) return;
                    
                    const code = data.appointment_code || data.reference_code || '';
                    document.getElementById('reportVisitCode').textContent = 'Consultation Record #' + (code ? String(code).replace(/^#/, '') : 'N/A');
                    document.getElementById('reportVisitServiceStation').textContent = (data.service_name || 'General Consultation') + ' • ' + (data.station_name || 'Health Station');
                    
                    const fullName = [data.first_name || '', data.middle_name || '', data.last_name || ''].filter(Boolean).join(' ') || 'Patient';
                    document.getElementById('reportVisitName').textContent = fullName;
                    
                    // Photo / Avatar
                    const photoWrap = document.getElementById('reportVisitPhotoWrap');
                    const photoEl = document.getElementById('reportVisitPhoto');
                    const avatarEl = document.getElementById('reportVisitAvatar');
                    if (data.photo_path) {
                        if (photoEl) photoEl.src = '../Patients/' + data.photo_path;
                        if (photoWrap) photoWrap.style.display = 'block';
                        if (avatarEl) avatarEl.style.display = 'none';
                    } else {
                        if (photoWrap) photoWrap.style.display = 'none';
                        if (avatarEl) {
                            const firstInitial = (data.first_name || 'P')[0] || 'P';
                            const lastInitial = (data.last_name || 'T')[0] || 'T';
                            avatarEl.textContent = (firstInitial + lastInitial).toUpperCase();
                            avatarEl.style.display = 'flex';
                        }
                    }
                    
                    // Age calculation
                    let ageStr = '';
                    if (data.birth_date) {
                        const bDate = new Date(data.birth_date);
                        const today = new Date();
                        let age = today.getFullYear() - bDate.getFullYear();
                        const m = today.getMonth() - bDate.getMonth();
                        if (m < 0 || (m === 0 && today.getDate() < bDate.getDate())) age--;
                        if (age >= 0) ageStr = age + ' yrs old';
                    }
                    const gender = data.gender || '';
                    document.getElementById('reportVisitDemographics').textContent = [ageStr, gender ? '(' + gender + ')' : ''].filter(Boolean).join(' ') || 'Age N/A';
                    
                    // Schedule
                    let dateStr = data.preferred_date || '';
                    if (dateStr) {
                        try {
                            const d = new Date(dateStr + 'T00:00:00');
                            if (!isNaN(d.getTime())) {
                                dateStr = d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                            }
                        } catch(e) {}
                    }
                    document.getElementById('reportVisitSchedule').textContent = 'Date: ' + dateStr + (data.preferred_time ? ' (' + data.preferred_time + ')' : '');
                    document.getElementById('reportVisitContact').textContent = 'Contact: ' + (data.contact_number || 'None');
                    
                    // Status Pill
                    const statusEl = document.getElementById('reportVisitStatus');
                    if (statusEl) {
                        const st = data.status || 'Pending';
                        statusEl.textContent = st;
                        statusEl.className = 'status-pill status-' + st.toLowerCase().replace(/\s+/g, '-');
                    }
                    
                    document.getElementById('reportVisitAddress').textContent = data.complete_address || 'Bacolod City';
                    
                    // Vitals
                    const tempEl = document.getElementById('reportVisitTemp');
                    const pulseEl = document.getElementById('reportVisitPulse');
                    const respEl = document.getElementById('reportVisitResp');
                    const bpEl = document.getElementById('reportVisitBp');
                    
                    tempEl.innerHTML = data.body_temperature ? (data.body_temperature + ' &deg;C') : '<em class="not-set">Not recorded</em>';
                    pulseEl.innerHTML = data.pulse_rate ? (data.pulse_rate + ' bpm') : '<em class="not-set">Not recorded</em>';
                    respEl.innerHTML = data.respiration_rate ? (data.respiration_rate + ' cpm') : '<em class="not-set">Not recorded</em>';
                    bpEl.innerHTML = data.blood_pressure ? data.blood_pressure : '<em class="not-set">Not recorded</em>';
                    
                    // Notes
                    const notesEl = document.getElementById('reportVisitNotes');
                    if (data.doctor_notes && data.doctor_notes.trim()) {
                        notesEl.textContent = data.doctor_notes;
                    } else {
                        notesEl.innerHTML = '<em>No clinical notes or assessments recorded for this consultation.</em>';
                    }
                    
                    modal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                } catch(err) {
                    console.error('Failed to open consultation details:', err);
                }
            }

            function closeReportVisitModal() {
                const modal = document.getElementById('reportVisitModal');
                if (modal) {
                    modal.style.display = 'none';
                    const filterModal = document.getElementById('reportsFilterModal');
                    if (!filterModal || filterModal.style.display === 'none') {
                        document.body.style.overflow = '';
                    }
                }
            }

            // Close modal on Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeReportsFilterModal();
                    closeReportVisitModal();
                }
            });

            function setDatePreset(preset, btn) {
                const fromInput = document.getElementById('filterReportFrom');
                const toInput = document.getElementById('filterReportTo');
                const now = new Date();
                
                document.querySelectorAll('.preset-pill').forEach(el => el.classList.remove('active'));
                if (btn) btn.classList.add('active');

                const formatDate = (d) => {
                    const year = d.getFullYear();
                    const month = String(d.getMonth() + 1).padStart(2, '0');
                    const day = String(d.getDate()).padStart(2, '0');
                    return `${year}-${month}-${day}`;
                };

                if (preset === 'this_month') {
                    const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
                    fromInput.value = formatDate(firstDay);
                    toInput.value = formatDate(now);
                } else if (preset === 'last_30_days') {
                    const past30 = new Date();
                    past30.setDate(now.getDate() - 30);
                    fromInput.value = formatDate(past30);
                    toInput.value = formatDate(now);
                } else if (preset === 'this_year') {
                    const firstDayYear = new Date(now.getFullYear(), 0, 1);
                    fromInput.value = formatDate(firstDayYear);
                    toInput.value = formatDate(now);
                } else if (preset === 'all_time') {
                    fromInput.value = '2024-01-01';
                    toInput.value = formatDate(now);
                }
            }
            </script>
        <?php endif; ?>
    </main>
</div>
<?php if ($page === 'appointments'): ?>
<script>
(function() {
    const updateStationCounts = async () => {
        try {
            const response = await fetch('?page=appointments&ajax=station_counts');
            if (!response.ok) {
                return;
            }

            const data = await response.json();
            const counts = {};
            data.forEach(item => {
                if (item.station_slug) {
                    counts[item.station_slug] = Number(item.total) || 0;
                }
            });

            document.querySelectorAll('.station-count[data-station]').forEach(element => {
                const station = element.dataset.station;
                if (!station || counts[station] === undefined) {
                    return;
                }
                const count = counts[station];
                element.textContent = `${count} Appointment${count === 1 ? '' : 's'}`;
            });
        } catch (error) {
            console.error('Unable to refresh station appointment counts:', error);
        }
    };

    updateStationCounts();
    window.setInterval(updateStationCounts, 30000);
})();
</script>
<?php endif; ?>
<script>
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

(function() {
    const markNotificationRead = (notificationId) => {
        if (!notificationId) return;
        fetch('?ajax=mark_notification_read&notification_id=' + encodeURIComponent(notificationId))
            .catch(() => {});
    };

    const hideNotifications = () => {
        const selectors = [
            '.toast-success',
            '.photo-notice',
            '.notification-row',
            '.notification-message'
        ];
        const notifications = Array.from(new Set(selectors.flatMap((selector) => Array.from(document.querySelectorAll(selector)))));
        if (notifications.length === 0) {
            return;
        }

        // Mark all visible patient notifications as read via AJAX
        document.querySelectorAll('tr[data-notification-id]').forEach((row) => {
            const notifId = row.dataset.notificationId;
            if (notifId) {
                markNotificationRead(notifId);
            }
        });

        window.setTimeout(() => {
            notifications.forEach((element) => {
                element.style.transition = 'opacity 0.35s ease';
                element.style.opacity = '0';
                window.setTimeout(() => {
                    if (element.parentNode) {
                        element.parentNode.removeChild(element);
                    }
                }, 350);
            });
        }, 5000);
    };

    hideNotifications();

    // ── 5-SECOND BACKGROUND TABLE LIVE SYNC FOR ADMIN PORTAL ──
    // Seamlessly updates admin tables, queue states, and metrics without full page reload
    let isAdminSyncing = false;
    const adminSyncSelectors = [
        '.table-scroll-wrapper',
        'table.data-table',
        '.stat-grid',
        '.stat-cards',
        '.station-queue-grid',
        '.admin-overview-grid',
        '.reports-table-wrap'
    ];

    setInterval(async function () {
        if (isAdminSyncing) return;
        const activeEl = document.activeElement;
        const isTyping = activeEl && (activeEl.tagName === 'INPUT' || activeEl.tagName === 'TEXTAREA' || activeEl.tagName === 'SELECT');
        const userModal = document.getElementById('userModal');
        const isUserModalOpen = userModal && userModal.classList.contains('open');
        const reportVisitModal = document.getElementById('reportVisitModal');
        const isReportVisitOpen = reportVisitModal && reportVisitModal.style.display !== 'none';
        const reportsFilterModal = document.getElementById('reportsFilterModal');
        const isReportsFilterOpen = reportsFilterModal && reportsFilterModal.style.display !== 'none';

        if (isTyping || isUserModalOpen || isReportVisitOpen || isReportsFilterOpen) return;

        try {
            isAdminSyncing = true;
            const res = await fetch(window.location.href, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) return;
            const html = await res.text();
            const parser = new DOMParser();
            const newDoc = parser.parseFromString(html, 'text/html');

            adminSyncSelectors.forEach(selector => {
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
            console.debug('Admin live sync notice:', err);
        } finally {
            isAdminSyncing = false;
        }
    }, 5000);
})();

window.dismissAdminToast = function() {
    const toast = document.getElementById('adminFlashToast');
    if (toast) {
        toast.classList.add('hide-toast');
        setTimeout(() => {
            toast.remove();
        }, 350);
    }
};
(function() {
    const toast = document.getElementById('adminFlashToast');
    if (toast) {
        setTimeout(() => {
            window.dismissAdminToast();
        }, 5000);
    }
})();
</script>
</body>
</html>
