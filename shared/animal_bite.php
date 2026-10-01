<?php

declare(strict_types=1);

/**
 * Animal Bite service.
 *
 * One exposure (a bite / scratch) is an "animal bite case". A case is treated over a series of
 * appointments following the post-exposure prophylaxis (PEP) schedule: Day 0, Day 3, Day 7, and the
 * conditional Day 14 and Day 28 doses. The first booking opens the case (Day 0); later doses are the
 * follow-up appointments staff schedule from a completed visit, each tagged with its PEP day.
 *
 *  - animal_bite_cases: recipient, history of exposure, condition of the animal, category (I / II / III)
 *  - animal_bite_doses: vaccines given at each visit (type of vaccine, dose, route & site)
 *  - appointments.bite_case_id / appointments.bite_session_day link each visit to its case and PEP day
 */

const DB_TABLE_ANIMAL_BITE_CASES = 'animal_bite_cases';
const DB_TABLE_ANIMAL_BITE_DOSES = 'animal_bite_doses';
const ANIMAL_BITE_SERVICE_SLUG = 'animal-bite';

function is_animal_bite_service(string $serviceSlug, string $serviceName = ''): bool
{
    $slug = strtolower(trim($serviceSlug));
    $name = strtolower(trim($serviceName));

    return $slug === ANIMAL_BITE_SERVICE_SLUG
        || str_contains($slug, 'animal-bite')
        || str_contains($name, 'animal bite');
}

function appointment_is_animal_bite(array $appointment): bool
{
    return is_animal_bite_service((string) ($appointment['service_slug'] ?? ''), (string) ($appointment['service_name'] ?? ''));
}

function animal_bite_relationship_options(): array
{
    return ['Self', 'Parent', 'Guardian'];
}

function animal_bite_exposure_places(): array
{
    return ['House', 'Street', 'Workplace'];
}

function animal_bite_exposure_types(): array
{
    return ['Bite', 'Scratch'];
}

function animal_bite_animal_types(): array
{
    return ['Cat', 'Dog', 'Others'];
}

function animal_bite_animal_conditions(): array
{
    return ['Healthy', 'Sick', 'Lost/Missing', 'Died', 'Sacrifice'];
}

/**
 * WHO exposure categories, shown to staff when classifying the exposure at vitals encoding.
 */
function animal_bite_categories(): array
{
    return [
        'I' => [
            'label' => 'Category I',
            'summary' => 'Touching or feeding the animal, licks on intact skin',
            'action' => 'No vaccine needed. Wash the exposed skin.',
        ],
        'II' => [
            'label' => 'Category II',
            'summary' => 'Nibbling of uncovered skin, minor scratches or abrasions without bleeding',
            'action' => 'Anti-rabies vaccine series.',
        ],
        'III' => [
            'label' => 'Category III',
            'summary' => 'Bites or scratches that break the skin, licks on broken skin or mucous membranes',
            'action' => 'Anti-rabies vaccine series plus rabies immunoglobulin (RIG).',
        ],
    ];
}

function animal_bite_category_needs_vaccine(?string $category): bool
{
    return in_array((string) $category, ['II', 'III'], true);
}

/**
 * Types of vaccine staff may record for a visit. ERIG / HRIG (rabies immunoglobulin) are only for Category III.
 */
function animal_bite_vaccine_options(?string $category): array
{
    $options = ['PVRV', 'PCECV', 'HDVC', 'Tetanus Toxoid', 'TIG/ATS'];
    if ((string) $category === 'III') {
        $options[] = 'ERIG';
        $options[] = 'HRIG';
    }

    return $options;
}

/**
 * Reference dose and route for each type of vaccine. The dose field starts empty; this guide fills its
 * placeholder and the "use standard dose" shortcut. RIG doses depend on body weight (IU per kg).
 */
function animal_bite_vaccine_guide(): array
{
    return [
        'PVRV' => [
            'name' => 'Purified Vero Cell Rabies Vaccine',
            'dose' => '0.1 mL',
            'dose_note' => '0.1 mL per site (2-site intradermal)',
            'route' => 'ID - Left & right deltoid',
            'per_kg' => null,
        ],
        'PCECV' => [
            'name' => 'Purified Chick Embryo Cell Vaccine',
            'dose' => '0.1 mL',
            'dose_note' => '0.1 mL per site (2-site intradermal)',
            'route' => 'ID - Left & right deltoid',
            'per_kg' => null,
        ],
        'HDVC' => [
            'name' => 'Human Diploid Cell Vaccine',
            'dose' => '1.0 mL',
            'dose_note' => '1 vial (1.0 mL) intramuscular',
            'route' => 'IM - Deltoid',
            'per_kg' => null,
        ],
        'Tetanus Toxoid' => [
            'name' => 'Tetanus Toxoid (TT / T-vac)',
            'dose' => '0.5 mL',
            'dose_note' => '0.5 mL intramuscular',
            'route' => 'IM - Left deltoid',
            'per_kg' => null,
        ],
        'TIG/ATS' => [
            'name' => 'Tetanus Immunoglobulin / Anti-Tetanus Serum',
            'dose' => '',
            'dose_note' => 'TIG 250 IU or ATS 1,500 IU, intramuscular',
            'route' => 'IM - Away from the tetanus toxoid site',
            'per_kg' => null,
        ],
        'ERIG' => [
            'name' => 'Equine Rabies Immunoglobulin',
            'dose' => '',
            'dose_note' => '40 IU per kg of body weight',
            'route' => 'Infiltration into & around the wound',
            'per_kg' => 40,
        ],
        'HRIG' => [
            'name' => 'Human Rabies Immunoglobulin',
            'dose' => '',
            'dose_note' => '20 IU per kg of body weight',
            'route' => 'Infiltration into & around the wound',
            'per_kg' => 20,
        ],
    ];
}

/**
 * PEP schedule of one case. Day 14 and Day 28 are conditional.
 */
function animal_bite_schedule(): array
{
    return [
        0 => ['day' => 0, 'label' => 'Day 0', 'required' => true],
        3 => ['day' => 3, 'label' => 'Day 3', 'required' => true],
        7 => ['day' => 7, 'label' => 'Day 7', 'required' => true],
        14 => ['day' => 14, 'label' => 'Day 14', 'required' => false],
        28 => ['day' => 28, 'label' => 'Day 28', 'required' => false],
    ];
}

function appointment_bite_session_day(array $appointment): ?int
{
    $day = $appointment['bite_session_day'] ?? null;
    return ($day === null || $day === '') ? null : (int) $day;
}

function animal_bite_session_label(?int $day): string
{
    $schedule = animal_bite_schedule();
    return ($day !== null && isset($schedule[$day])) ? $schedule[$day]['label'] : 'Visit';
}

/**
 * Why a conditional dose may be needed, based on the condition of the animal.
 */
function animal_bite_conditional_note(int $day, array $case): string
{
    if ($day === 14) {
        return 'Only when the intramuscular (IM) regimen is used.';
    }
    if ($day === 28) {
        $condition = (string) ($case['animal_condition'] ?? '');
        if ($condition !== '' && $condition !== 'Healthy') {
            return 'Recommended: the animal cannot be observed (' . $condition . ').';
        }
        return 'Only if the animal gets sick, dies or goes missing.';
    }

    return '';
}

function animal_bite_animal_label(array $case): string
{
    $type = (string) ($case['animal_type'] ?? '');
    if ($type === 'Others') {
        $other = trim((string) ($case['animal_type_other'] ?? ''));
        return $other !== '' ? $other : 'Other animal';
    }

    return $type !== '' ? $type : 'Not specified';
}

/* -------------------------------------------------------------------------
 * Schema
 * ---------------------------------------------------------------------- */

function create_animal_bite_cases_table(mysqli $connection, string $engine = 'InnoDB'): void
{
    $connection->query(
        'CREATE TABLE IF NOT EXISTS animal_bite_cases (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            case_code VARCHAR(20) NOT NULL UNIQUE,
            patient_id VARCHAR(32) DEFAULT NULL,
            station_slug VARCHAR(100) DEFAULT NULL,
            origin_appointment_id INT UNSIGNED DEFAULT NULL,
            relationship VARCHAR(50) NOT NULL DEFAULT "Self",
            recipient_first_name VARCHAR(100) DEFAULT NULL,
            recipient_middle_name VARCHAR(100) DEFAULT NULL,
            recipient_last_name VARCHAR(100) DEFAULT NULL,
            recipient_birth_date DATE DEFAULT NULL,
            recipient_gender VARCHAR(30) DEFAULT NULL,
            exposure_date DATE DEFAULT NULL,
            exposure_place VARCHAR(30) DEFAULT NULL,
            exposure_type VARCHAR(30) DEFAULT NULL,
            animal_type VARCHAR(30) DEFAULT NULL,
            animal_type_other VARCHAR(100) DEFAULT NULL,
            animal_condition VARCHAR(30) DEFAULT NULL,
            category VARCHAR(5) DEFAULT NULL,
            category_set_at TIMESTAMP NULL DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_bite_case_patient (patient_id),
            INDEX idx_bite_case_station (station_slug),
            INDEX idx_bite_case_origin (origin_appointment_id)
        ) ENGINE=' . $engine . ' DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci'
    );
}

function create_animal_bite_doses_table(mysqli $connection, string $engine = 'InnoDB'): void
{
    $connection->query(
        'CREATE TABLE IF NOT EXISTS animal_bite_doses (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            case_id INT UNSIGNED NOT NULL,
            appointment_id INT UNSIGNED NOT NULL,
            session_day TINYINT UNSIGNED DEFAULT NULL,
            vaccine_type VARCHAR(50) NOT NULL,
            dose VARCHAR(60) NOT NULL,
            route_site VARCHAR(150) NOT NULL,
            recorded_by VARCHAR(150) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_bite_dose_case (case_id),
            INDEX idx_bite_dose_appointment (appointment_id)
        ) ENGINE=' . $engine . ' DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci'
    );
}

function ensure_animal_bite_schema(mysqli $connection): void
{
    if (!db_table_exists($connection, DB_TABLE_ANIMAL_BITE_CASES)) {
        create_animal_bite_cases_table($connection);
    }
    if (!db_table_exists($connection, DB_TABLE_ANIMAL_BITE_DOSES)) {
        create_animal_bite_doses_table($connection);
    }
    if (!db_column_exists($connection, 'appointments', 'bite_case_id')) {
        try {
            $connection->query('ALTER TABLE appointments ADD COLUMN bite_case_id INT UNSIGNED DEFAULT NULL, ADD INDEX idx_bite_case_id (bite_case_id)');
        } catch (Throwable $e) {}
    }
    if (!db_column_exists($connection, 'appointments', 'bite_session_day')) {
        try {
            $connection->query('ALTER TABLE appointments ADD COLUMN bite_session_day TINYINT UNSIGNED DEFAULT NULL AFTER bite_case_id');
        } catch (Throwable $e) {}
    }
}

function animal_bite_schema_ready(): bool
{
    static $ready = null;
    if ($ready === true) {
        return true;
    }

    try {
        $connection = db();
        if (!db_table_exists($connection, DB_TABLE_ANIMAL_BITE_CASES)
            || !db_table_exists($connection, DB_TABLE_ANIMAL_BITE_DOSES)
            || !db_column_exists($connection, 'appointments', 'bite_case_id')
            || !db_column_exists($connection, 'appointments', 'bite_session_day')) {
            ensure_animal_bite_schema($connection);
        }
        $ready = db_column_exists($connection, 'appointments', 'bite_session_day');
    } catch (Throwable $e) {
        $ready = false;
    }

    return $ready;
}

/* -------------------------------------------------------------------------
 * Data access
 * ---------------------------------------------------------------------- */

/**
 * Opens a case at booking time and links the booked appointment to it as the Day 0 visit.
 */
function create_animal_bite_case(array $data, int $appointmentId): ?int
{
    if (!animal_bite_schema_ready()) {
        return null;
    }

    $connection = db();
    do {
        $caseCode = 'AB' . random_alphanumeric_code(6);
        $lookup = $connection->prepare('SELECT id FROM animal_bite_cases WHERE case_code = ? LIMIT 1');
        $lookup->bind_param('s', $caseCode);
        $lookup->execute();
        $exists = $lookup->get_result()->num_rows > 0;
    } while ($exists);

    $fields = [
        'patient_id' => (string) ($data['patient_id'] ?? ''),
        'station_slug' => (string) ($data['station_slug'] ?? ''),
        'relationship' => (string) ($data['relationship'] ?? 'Self'),
        'recipient_first_name' => (string) ($data['recipient_first_name'] ?? ''),
        'recipient_middle_name' => (string) ($data['recipient_middle_name'] ?? ''),
        'recipient_last_name' => (string) ($data['recipient_last_name'] ?? ''),
        'recipient_gender' => (string) ($data['recipient_gender'] ?? ''),
        'exposure_place' => (string) ($data['exposure_place'] ?? ''),
        'exposure_type' => (string) ($data['exposure_type'] ?? ''),
        'animal_type' => (string) ($data['animal_type'] ?? ''),
        'animal_type_other' => (string) ($data['animal_type_other'] ?? ''),
        'animal_condition' => (string) ($data['animal_condition'] ?? ''),
    ];
    $recipientBirthDate = !empty($data['recipient_birth_date']) ? (string) $data['recipient_birth_date'] : null;
    $exposureDate = !empty($data['exposure_date']) ? (string) $data['exposure_date'] : null;

    try {
        $stmt = $connection->prepare(
            'INSERT INTO animal_bite_cases (
                case_code, patient_id, station_slug, origin_appointment_id, relationship,
                recipient_first_name, recipient_middle_name, recipient_last_name, recipient_birth_date, recipient_gender,
                exposure_date, exposure_place, exposure_type, animal_type, animal_type_other, animal_condition
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param(
            'sssissssssssssss',
            $caseCode,
            $fields['patient_id'],
            $fields['station_slug'],
            $appointmentId,
            $fields['relationship'],
            $fields['recipient_first_name'],
            $fields['recipient_middle_name'],
            $fields['recipient_last_name'],
            $recipientBirthDate,
            $fields['recipient_gender'],
            $exposureDate,
            $fields['exposure_place'],
            $fields['exposure_type'],
            $fields['animal_type'],
            $fields['animal_type_other'],
            $fields['animal_condition']
        );
        $stmt->execute();
        $caseId = (int) $connection->insert_id;

        link_appointment_to_animal_bite_case($appointmentId, $caseId, 0);
        return $caseId;
    } catch (Throwable $e) {
        error_log('create_animal_bite_case error: ' . $e->getMessage());
        return null;
    }
}

function link_appointment_to_animal_bite_case(int $appointmentId, int $caseId, int $sessionDay): bool
{
    if ($appointmentId <= 0 || $caseId <= 0 || !animal_bite_schema_ready()) {
        return false;
    }

    try {
        $stmt = db()->prepare('UPDATE appointments SET bite_case_id = ?, bite_session_day = ? WHERE id = ?');
        $stmt->bind_param('iii', $caseId, $sessionDay, $appointmentId);
        return $stmt->execute();
    } catch (Throwable $e) {
        error_log('link_appointment_to_animal_bite_case error: ' . $e->getMessage());
        return false;
    }
}

function fetch_animal_bite_case(int $caseId): ?array
{
    if ($caseId <= 0 || !animal_bite_schema_ready()) {
        return null;
    }

    try {
        $stmt = db()->prepare('SELECT * FROM animal_bite_cases WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $caseId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    } catch (Throwable $e) {
        return null;
    }
}

function fetch_animal_bite_case_for_appointment(array $appointment): ?array
{
    $caseId = (int) ($appointment['bite_case_id'] ?? 0);
    return $caseId > 0 ? fetch_animal_bite_case($caseId) : null;
}

function save_animal_bite_category(int $caseId, string $category): bool
{
    if ($caseId <= 0 || !array_key_exists($category, animal_bite_categories()) || !animal_bite_schema_ready()) {
        return false;
    }

    try {
        $stmt = db()->prepare('UPDATE animal_bite_cases SET category = ?, category_set_at = NOW() WHERE id = ?');
        $stmt->bind_param('si', $category, $caseId);
        return $stmt->execute();
    } catch (Throwable $e) {
        error_log('save_animal_bite_category error: ' . $e->getMessage());
        return false;
    }
}

function fetch_animal_bite_doses_for_appointment(int $appointmentId): array
{
    if ($appointmentId <= 0 || !animal_bite_schema_ready()) {
        return [];
    }

    try {
        $stmt = db()->prepare('SELECT * FROM animal_bite_doses WHERE appointment_id = ? ORDER BY id ASC');
        $stmt->bind_param('i', $appointmentId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } catch (Throwable $e) {
        return [];
    }
}

/**
 * Replaces the vaccines recorded for one visit.
 * Each row: ['vaccine_type' => ..., 'dose' => ..., 'route_site' => ...]
 */
function save_animal_bite_doses(int $caseId, int $appointmentId, ?int $sessionDay, array $rows, string $recordedBy = ''): bool
{
    if ($caseId <= 0 || $appointmentId <= 0 || !animal_bite_schema_ready()) {
        return false;
    }

    $connection = db();
    $connection->begin_transaction();
    try {
        $delete = $connection->prepare('DELETE FROM animal_bite_doses WHERE appointment_id = ?');
        $delete->bind_param('i', $appointmentId);
        $delete->execute();

        if ($rows !== []) {
            $insert = $connection->prepare(
                'INSERT INTO animal_bite_doses (case_id, appointment_id, session_day, vaccine_type, dose, route_site, recorded_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            foreach ($rows as $row) {
                $vaccine = (string) $row['vaccine_type'];
                $dose = (string) $row['dose'];
                $route = (string) $row['route_site'];
                $insert->bind_param('iiissss', $caseId, $appointmentId, $sessionDay, $vaccine, $dose, $route, $recordedBy);
                $insert->execute();
            }
        }

        $connection->commit();
        return true;
    } catch (Throwable $e) {
        $connection->rollback();
        error_log('save_animal_bite_doses error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Validates the vaccine rows posted from the clinical remarks form.
 * Returns ['rows' => [...], 'error' => ?string]. Fully empty rows are ignored.
 */
function parse_animal_bite_dose_rows(array $post, ?string $category): array
{
    $types = (array) ($post['bite_vaccine_type'] ?? []);
    $doses = (array) ($post['bite_dose'] ?? []);
    $routes = (array) ($post['bite_route_site'] ?? []);
    $allowed = animal_bite_vaccine_options($category);
    $rows = [];

    foreach ($types as $index => $rawType) {
        $type = trim((string) $rawType);
        $dose = trim((string) ($doses[$index] ?? ''));
        $route = trim((string) ($routes[$index] ?? ''));

        if ($type === '' && $dose === '' && $route === '') {
            continue;
        }
        if ($type === '' || $dose === '' || $route === '') {
            return ['rows' => [], 'error' => 'Each vaccine row needs a type of vaccine, dose, and route & site.'];
        }
        if (!in_array($type, $allowed, true)) {
            return ['rows' => [], 'error' => $type . ' cannot be given for Category ' . ($category ?: '-') . ' exposures.'];
        }

        $rows[] = [
            'vaccine_type' => $type,
            'dose' => mb_substr($dose, 0, 60),
            'route_site' => mb_substr($route, 0, 150),
        ];
    }

    if ($rows === []) {
        return ['rows' => [], 'error' => 'Please record at least one vaccine given (type of vaccine, dose, and route & site).'];
    }

    return ['rows' => $rows, 'error' => null];
}

/**
 * All cases of a patient with their visits, recorded vaccines and PEP timeline.
 */
function fetch_animal_bite_cases_for_patient(string $patientId, ?string $stationSlug = null): array
{
    $patientId = trim($patientId);
    if ($patientId === '' || !animal_bite_schema_ready()) {
        return [];
    }

    try {
        $connection = db();
        if ($stationSlug !== null && $stationSlug !== '') {
            $stmt = $connection->prepare('SELECT * FROM animal_bite_cases WHERE patient_id = ? AND station_slug = ? ORDER BY created_at DESC, id DESC');
            $stmt->bind_param('ss', $patientId, $stationSlug);
        } else {
            $stmt = $connection->prepare('SELECT * FROM animal_bite_cases WHERE patient_id = ? ORDER BY created_at DESC, id DESC');
            $stmt->bind_param('s', $patientId);
        }
        $stmt->execute();
        $cases = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        if ($cases === []) {
            return [];
        }

        $caseIds = array_map(static fn(array $c): int => (int) $c['id'], $cases);
        $placeholders = implode(',', array_fill(0, count($caseIds), '?'));
        $idTypes = str_repeat('i', count($caseIds));

        $apptStmt = $connection->prepare("SELECT * FROM appointments WHERE bite_case_id IN ({$placeholders}) ORDER BY preferred_date ASC, id ASC");
        $apptStmt->bind_param($idTypes, ...$caseIds);
        $apptStmt->execute();
        $appointmentsByCase = [];
        foreach ($apptStmt->get_result()->fetch_all(MYSQLI_ASSOC) as $appt) {
            $appointmentsByCase[(int) $appt['bite_case_id']][] = $appt;
        }

        $doseStmt = $connection->prepare("SELECT * FROM animal_bite_doses WHERE case_id IN ({$placeholders}) ORDER BY id ASC");
        $doseStmt->bind_param($idTypes, ...$caseIds);
        $doseStmt->execute();
        $dosesByAppointment = [];
        foreach ($doseStmt->get_result()->fetch_all(MYSQLI_ASSOC) as $dose) {
            $dosesByAppointment[(int) $dose['appointment_id']][] = $dose;
        }

        foreach ($cases as &$case) {
            $case['appointments'] = $appointmentsByCase[(int) $case['id']] ?? [];
            $case['doses_by_appointment'] = $dosesByAppointment;
            $case += animal_bite_case_progress($case);
        }
        unset($case);

        return $cases;
    } catch (Throwable $e) {
        error_log('fetch_animal_bite_cases_for_patient error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Base date of the PEP schedule: the Day 0 visit, else the date of exposure.
 */
function animal_bite_case_base_date(array $case): ?string
{
    foreach ($case['appointments'] ?? [] as $appt) {
        if ((int) ($appt['bite_session_day'] ?? -1) === 0 && (string) ($appt['status'] ?? '') !== 'Cancelled') {
            return (string) $appt['preferred_date'];
        }
    }

    $exposure = (string) ($case['exposure_date'] ?? '');
    return $exposure !== '' ? $exposure : null;
}

/**
 * Builds the session timeline and overall status of a case.
 */
function animal_bite_case_progress(array $case): array
{
    $today = date('Y-m-d');
    $category = (string) ($case['category'] ?? '');
    $baseDate = animal_bite_case_base_date($case);
    $dosesByAppointment = $case['doses_by_appointment'] ?? [];

    // The latest non-cancelled visit per PEP day
    $visitsByDay = [];
    foreach ($case['appointments'] ?? [] as $appt) {
        $day = $appt['bite_session_day'];
        if ($day === null || (string) ($appt['status'] ?? '') === 'Cancelled') {
            continue;
        }
        $visitsByDay[(int) $day] = $appt;
    }

    $sessions = [];
    $completedRequired = 0;
    $totalCompleted = 0;
    foreach (animal_bite_schedule() as $day => $def) {
        $visit = $visitsByDay[$day] ?? null;
        $targetDate = $baseDate !== null ? date('Y-m-d', strtotime($baseDate . ' +' . $day . ' days')) : null;
        $visitStatus = $visit !== null ? (string) ($visit['status'] ?? '') : '';

        if ($visit !== null && $visitStatus === 'Completed') {
            $state = 'completed';
            $totalCompleted++;
            if ($def['required']) {
                $completedRequired++;
            }
        } elseif ($visit !== null) {
            $state = 'scheduled';
        } elseif ($category === 'I' && $day > 0) {
            $state = 'not-needed';
        } elseif (!$def['required']) {
            $state = 'conditional';
        } elseif ($targetDate !== null && $targetDate < $today) {
            $state = 'overdue';
        } else {
            $state = 'due';
        }

        $sessions[] = [
            'day' => $day,
            'label' => $def['label'],
            'required' => $def['required'],
            'state' => $state,
            'target_date' => $targetDate,
            'visit' => $visit,
            'visit_status' => $visitStatus,
            'doses' => $visit !== null ? ($dosesByAppointment[(int) $visit['id']] ?? []) : [],
            'note' => $def['required'] ? '' : animal_bite_conditional_note($day, $case),
        ];
    }

    $allCancelled = ($case['appointments'] ?? []) !== [] && $visitsByDay === [];
    $day28Done = isset($visitsByDay[28]) && (string) $visitsByDay[28]['status'] === 'Completed';
    $hasPending = false;
    foreach ($visitsByDay as $visit) {
        if ((string) $visit['status'] !== 'Completed') {
            $hasPending = true;
        }
    }

    if ($allCancelled) {
        $status = ['key' => 'cancelled', 'label' => 'Cancelled'];
    } elseif ($category === 'I' && $totalCompleted > 0) {
        $status = ['key' => 'closed', 'label' => 'Closed - No vaccine needed'];
    } elseif ($day28Done || ($completedRequired >= 3 && !$hasPending)) {
        $status = ['key' => 'complete', 'label' => $day28Done ? 'Series Completed' : 'Primary Series Completed'];
    } elseif ($totalCompleted > 0) {
        $status = ['key' => 'ongoing', 'label' => 'Ongoing Treatment'];
    } else {
        $status = ['key' => 'awaiting', 'label' => 'Awaiting Day 0'];
    }

    return [
        'sessions' => $sessions,
        'status' => $status,
        'completed_required' => $completedRequired,
        'total_completed' => $totalCompleted,
        'base_date' => $baseDate,
    ];
}

/**
 * PEP days that come after the given visit, for scheduling the next dose.
 * Returns [['day' => 3, 'label' => 'Day 3', 'date' => 'Y-m-d', 'required' => true], ...]
 */
function animal_bite_upcoming_sessions(array $appointment): array
{
    $case = fetch_animal_bite_case_for_appointment($appointment);
    if ($case === null) {
        return [];
    }

    $currentDay = (int) ($appointment['bite_session_day'] ?? 0);
    $case['appointments'] = [];
    try {
        $stmt = db()->prepare('SELECT * FROM appointments WHERE bite_case_id = ? ORDER BY preferred_date ASC, id ASC');
        $caseId = (int) $case['id'];
        $stmt->bind_param('i', $caseId);
        $stmt->execute();
        $case['appointments'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } catch (Throwable $e) {}

    $baseDate = animal_bite_case_base_date($case) ?? (string) ($appointment['preferred_date'] ?? date('Y-m-d'));
    $upcoming = [];
    foreach (animal_bite_schedule() as $day => $def) {
        if ($day <= $currentDay) {
            continue;
        }
        $upcoming[] = [
            'day' => $day,
            'label' => $def['label'],
            'required' => $def['required'],
            'date' => date('Y-m-d', strtotime($baseDate . ' +' . $day . ' days')),
            'note' => $def['required'] ? '' : animal_bite_conditional_note($day, $case),
        ];
    }

    return $upcoming;
}

/* -------------------------------------------------------------------------
 * Rendering
 * ---------------------------------------------------------------------- */

function abc_h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function abc_icon(string $name, int $size = 16): string
{
    $paths = [
        'paw' => '<circle cx="11" cy="4" r="2"/><circle cx="18" cy="8" r="2"/><circle cx="20" cy="16" r="2"/><path d="M9 10a5 5 0 0 1 5 5v3.5a3.5 3.5 0 0 1-6.84 1.045Q6.52 17.48 4.46 16.84A3.5 3.5 0 0 1 5.5 10Z"/>',
        'calendar' => '<path d="M7 3v3m10-3v3M4 9h16M5 5h14a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z"/>',
        'pin' => '<path d="M12 21s-6-5.33-6-11a6 6 0 0 1 12 0c0 5.67-6 11-6 11Z"/><circle cx="12" cy="10" r="2.5"/>',
        'alert' => '<path d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>',
        'heart' => '<path d="M12 20s-7-4.35-7-10a4 4 0 0 1 7-2.65A4 4 0 0 1 19 10c0 5.65-7 10-7 10Z"/>',
        'user' => '<path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/>',
        'syringe' => '<path d="m18 2 4 4M17 7l3-3M19 9 8.7 19.3c-1 1-2.5 1-3.4 0l-.6-.6c-1-1-1-2.5 0-3.4L15 5M9 11l4 4M5 19l-3 3M14 4l6 6"/>',
        'check' => '<path d="M20 6 9 17l-5-5"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'x' => '<path d="M18 6 6 18M6 6l12 12"/>',
        'shield' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
    ];

    return '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width:' . $size . 'px;height:' . $size . 'px;flex-shrink:0;display:inline-block;vertical-align:middle;">' . ($paths[$name] ?? '') . '</svg>';
}

function abc_date(?string $date, string $format = 'M j, Y'): string
{
    $date = trim((string) $date);
    if ($date === '' || $date === '0000-00-00') {
        return '-';
    }
    $ts = strtotime($date);
    return $ts ? date($format, $ts) : '-';
}

function animal_bite_recipient_name(array $case, array $fallbackAppointment = []): string
{
    if (strcasecmp((string) ($case['relationship'] ?? 'Self'), 'Self') === 0 && $fallbackAppointment !== []) {
        return trim(implode(' ', array_filter([
            (string) ($fallbackAppointment['first_name'] ?? ''),
            (string) ($fallbackAppointment['middle_name'] ?? ''),
            (string) ($fallbackAppointment['last_name'] ?? ''),
        ])));
    }

    return trim(implode(' ', array_filter([
        (string) ($case['recipient_first_name'] ?? ''),
        (string) ($case['recipient_middle_name'] ?? ''),
        (string) ($case['recipient_last_name'] ?? ''),
    ])));
}

/**
 * Shared styles of the animal bite UI. Themes: staff (blue) and admin (indigo).
 */
function animal_bite_styles(): string
{
    static $printed = false;
    if ($printed) {
        return '';
    }
    $printed = true;

    return <<<'CSS'
<style>
.abc-theme-staff { --abc-accent: #2563eb; --abc-accent-dark: #1d4ed8; --abc-soft: #eff6ff; --abc-border: #bfdbfe; --abc-ink: #1e3a8a; }
.abc-theme-admin { --abc-accent: #6366f1; --abc-accent-dark: #4f46e5; --abc-soft: #eef2ff; --abc-border: #c7d2fe; --abc-ink: #312e81; }
.abc-section-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin: 22px 0 12px; }
.abc-section-head h4 { margin: 0; display: flex; align-items: center; gap: 8px; font-size: 1rem; font-weight: 800; color: #0f172a; }
.abc-section-head h4 .abc-head-icon { width: 32px; height: 32px; border-radius: 10px; display: inline-grid; place-items: center; background: var(--abc-soft); color: var(--abc-accent); }
.abc-section-head .abc-count { font-size: 0.78rem; font-weight: 700; color: var(--abc-accent-dark); background: var(--abc-soft); border: 1px solid var(--abc-border); padding: 4px 10px; border-radius: 999px; }
.abc-case-stack { display: grid; gap: 16px; }
.abc-case-card { border: 1.5px solid var(--abc-border); border-radius: 16px; background: #fff; overflow: hidden; box-shadow: 0 6px 18px -12px rgba(15, 23, 42, 0.25); }
.abc-case-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; flex-wrap: wrap; padding: 14px 18px; background: linear-gradient(135deg, var(--abc-soft), #ffffff); border-bottom: 1px solid var(--abc-border); }
.abc-case-title { display: flex; align-items: center; gap: 12px; min-width: 0; }
.abc-case-icon { width: 40px; height: 40px; border-radius: 12px; display: grid; place-items: center; background: var(--abc-accent); color: #fff; flex-shrink: 0; }
.abc-case-title strong { display: block; font-size: 1rem; color: #0f172a; }
.abc-case-title small { display: block; color: #64748b; font-size: 0.8rem; margin-top: 2px; }
.abc-case-badges { display: flex; gap: 6px; flex-wrap: wrap; align-items: center; }
.abc-pill { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 999px; font-size: 0.74rem; font-weight: 800; letter-spacing: 0.01em; border: 1px solid transparent; white-space: nowrap; }
.abc-cat-I { background: #f1f5f9; color: #334155; border-color: #cbd5e1; }
.abc-cat-II { background: #fffbeb; color: #92400e; border-color: #fcd34d; }
.abc-cat-III { background: #fef2f2; color: #991b1b; border-color: #fca5a5; }
.abc-cat-none { background: #f8fafc; color: #64748b; border-color: #e2e8f0; border-style: dashed; }
.abc-status-ongoing { background: var(--abc-soft); color: var(--abc-accent-dark); border-color: var(--abc-border); }
.abc-status-complete, .abc-status-closed { background: #ecfdf5; color: #047857; border-color: #6ee7b7; }
.abc-status-awaiting { background: #fff7ed; color: #9a3412; border-color: #fdba74; }
.abc-status-cancelled { background: #f1f5f9; color: #64748b; border-color: #cbd5e1; }
.abc-case-body { padding: 14px 18px 18px; }
.abc-facts { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
.abc-fact { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 8px 12px; min-width: 0; }
.abc-fact small { display: block; font-size: 0.68rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: #64748b; margin-bottom: 2px; }
.abc-fact strong { display: block; font-size: 0.88rem; color: #0f172a; overflow-wrap: anywhere; }
.abc-progress-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin: 16px 0 10px; flex-wrap: wrap; }
.abc-progress-row h5 { margin: 0; font-size: 0.86rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 6px; }
.abc-progress { display: flex; align-items: center; gap: 10px; font-size: 0.78rem; font-weight: 700; color: #475569; }
.abc-progress-track { width: 140px; height: 8px; border-radius: 999px; background: #e2e8f0; overflow: hidden; }
.abc-progress-fill { height: 100%; border-radius: 999px; background: linear-gradient(90deg, var(--abc-accent), var(--abc-accent-dark)); }
.abc-sessions { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 8px; }
.abc-session { position: relative; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 10px 10px 12px; background: #fff; min-width: 0; }
.abc-session-head { display: flex; align-items: center; justify-content: space-between; gap: 6px; margin-bottom: 4px; }
.abc-session-day { font-size: 0.86rem; font-weight: 800; color: #0f172a; }
.abc-session-dot { width: 22px; height: 22px; border-radius: 50%; display: grid; place-items: center; border: 2px solid #cbd5e1; color: #fff; flex-shrink: 0; }
.abc-session-date { font-size: 0.76rem; color: #475569; font-weight: 600; }
.abc-session-state { margin-top: 6px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; }
.abc-session-note { margin-top: 6px; font-size: 0.72rem; color: #64748b; line-height: 1.35; }
.abc-session-doses { margin: 8px 0 0; padding: 0; list-style: none; display: grid; gap: 4px; }
.abc-session-doses li { font-size: 0.72rem; line-height: 1.35; color: #334155; background: #f8fafc; border-radius: 6px; padding: 4px 6px; overflow-wrap: anywhere; }
.abc-session-doses li strong { color: #0f172a; }
.abc-session.is-completed { border-color: #6ee7b7; background: #f0fdf4; }
.abc-session.is-completed .abc-session-dot { background: #10b981; border-color: #10b981; }
.abc-session.is-completed .abc-session-state { color: #047857; }
.abc-session.is-scheduled { border-color: var(--abc-border); background: var(--abc-soft); }
.abc-session.is-scheduled .abc-session-dot { border-color: var(--abc-accent); background: #fff; }
.abc-session.is-scheduled .abc-session-state { color: var(--abc-accent-dark); }
.abc-session.is-due .abc-session-dot { border-color: #f59e0b; }
.abc-session.is-due .abc-session-state { color: #b45309; }
.abc-session.is-overdue { border-color: #fca5a5; background: #fef2f2; }
.abc-session.is-overdue .abc-session-dot { border-color: #ef4444; background: #ef4444; }
.abc-session.is-overdue .abc-session-state { color: #b91c1c; }
.abc-session.is-conditional, .abc-session.is-not-needed { border-style: dashed; background: #fafafa; }
.abc-session.is-conditional .abc-session-state, .abc-session.is-not-needed .abc-session-state { color: #64748b; }
.abc-session-tag { font-size: 0.62rem; font-weight: 800; text-transform: uppercase; color: #64748b; background: #f1f5f9; border-radius: 999px; padding: 2px 6px; }
.abc-footnote { margin-top: 12px; font-size: 0.74rem; color: #64748b; display: flex; gap: 6px; align-items: flex-start; }
.abc-empty { padding: 14px 16px; border: 1.5px dashed var(--abc-border); border-radius: 12px; color: #64748b; font-size: 0.86rem; background: var(--abc-soft); }
/* Visit summary (modals) */
.abc-visit-card { margin-top: 14px; border: 1.5px solid var(--abc-border); border-radius: 12px; background: var(--abc-soft); padding: 14px 16px; }
.abc-visit-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; margin-bottom: 10px; }
.abc-visit-head > div { display: flex; align-items: center; gap: 8px; color: var(--abc-ink); font-weight: 800; font-size: 0.92rem; }
.abc-visit-card .abc-fact { background: #fff; border-color: var(--abc-border); }
.abc-dose-table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 0.84rem; background: #fff; border-radius: 10px; overflow: hidden; }
.abc-dose-table th { text-align: left; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.04em; color: #64748b; background: #f8fafc; padding: 8px 10px; border-bottom: 1px solid #e2e8f0; }
.abc-dose-table td { padding: 8px 10px; border-bottom: 1px solid #f1f5f9; color: #0f172a; }
/* Forms (staff) */
.abc-form-block { margin-top: 18px; border: 1.5px solid var(--abc-border); border-radius: 14px; background: var(--abc-soft); padding: 16px 18px; }
.abc-form-block > label, .abc-form-title { color: var(--abc-ink); font-weight: 800; display: flex; align-items: center; gap: 8px; margin-bottom: 4px; font-size: 0.95rem; }
.abc-form-sub { margin: 0 0 12px; color: #475569; font-size: 0.82rem; }
.abc-cat-options { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
.abc-cat-option { position: relative; display: block; cursor: pointer; }
.abc-cat-option input { position: absolute; opacity: 0; pointer-events: none; }
.abc-cat-option .abc-cat-box { display: block; height: 100%; border: 2px solid #e2e8f0; background: #fff; border-radius: 12px; padding: 12px; transition: border-color 0.15s, box-shadow 0.15s; }
.abc-cat-option .abc-cat-num { display: inline-grid; place-items: center; min-width: 36px; height: 36px; padding: 0 8px; border-radius: 10px; font-weight: 900; font-size: 1rem; margin-bottom: 6px; }
.abc-cat-option strong { display: block; font-size: 0.86rem; color: #0f172a; }
.abc-cat-option small { display: block; font-size: 0.74rem; color: #64748b; line-height: 1.35; margin-top: 3px; }
.abc-cat-option em { display: block; font-style: normal; font-size: 0.72rem; font-weight: 700; margin-top: 6px; }
.abc-cat-option.cat-I .abc-cat-num { background: #f1f5f9; color: #334155; }
.abc-cat-option.cat-II .abc-cat-num { background: #fef3c7; color: #92400e; }
.abc-cat-option.cat-III .abc-cat-num { background: #fee2e2; color: #991b1b; }
.abc-cat-option.cat-I em { color: #475569; } .abc-cat-option.cat-II em { color: #b45309; } .abc-cat-option.cat-III em { color: #b91c1c; }
.abc-cat-option input:checked + .abc-cat-box { border-color: var(--abc-accent); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15); }
.abc-cat-option input:focus-visible + .abc-cat-box { outline: 2px solid var(--abc-accent); outline-offset: 2px; }
.abc-dose-rows { display: grid; gap: 10px; }
.abc-dose-row { display: grid; grid-template-columns: minmax(0, 1.1fr) minmax(0, 0.9fr) minmax(0, 1.3fr) auto; gap: 10px; align-items: start; background: #fff; border: 1px solid var(--abc-border); border-radius: 12px; padding: 12px; }
.abc-dose-row label { display: block; font-size: 0.76rem; font-weight: 700; color: #334155; margin-bottom: 4px; }
.abc-dose-row select, .abc-dose-row input { width: 100%; box-sizing: border-box; }
.abc-dose-hint { display: block; margin-top: 4px; font-size: 0.72rem; color: #64748b; line-height: 1.3; min-height: 1em; }
.abc-use-std { margin-top: 4px; border: 1px solid var(--abc-border); background: var(--abc-soft); color: var(--abc-accent-dark); font-size: 0.7rem; font-weight: 700; border-radius: 999px; padding: 2px 8px; cursor: pointer; }
.abc-row-remove { margin-top: 22px; width: 34px; height: 34px; border-radius: 10px; border: 1px solid #fecaca; background: #fff; color: #dc2626; display: grid; place-items: center; cursor: pointer; }
.abc-row-remove[disabled] { opacity: 0.35; cursor: not-allowed; }
.abc-add-row { margin-top: 10px; display: inline-flex; align-items: center; gap: 6px; border: 1.5px dashed var(--abc-accent); color: var(--abc-accent-dark); background: #fff; border-radius: 10px; padding: 8px 14px; font-weight: 700; font-size: 0.84rem; cursor: pointer; }
.abc-info-note { display: flex; gap: 10px; align-items: flex-start; padding: 12px 14px; border-radius: 12px; background: #f8fafc; border: 1px solid #e2e8f0; color: #334155; font-size: 0.86rem; }
@media (max-width: 860px) {
    .abc-sessions, .abc-facts { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .abc-cat-options { grid-template-columns: 1fr; }
    .abc-dose-row { grid-template-columns: 1fr; }
    .abc-row-remove { margin-top: 0; justify-self: end; }
}
@media (max-width: 480px) { .abc-sessions { grid-template-columns: 1fr; } .abc-facts { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
</style>
CSS;
}

function animal_bite_category_pill(?string $category): string
{
    $category = (string) $category;
    if (!array_key_exists($category, animal_bite_categories())) {
        return '<span class="abc-pill abc-cat-none">Category not yet assessed</span>';
    }

    return '<span class="abc-pill abc-cat-' . abc_h($category) . '">' . abc_icon('alert', 12) . ' Category ' . abc_h($category) . '</span>';
}

/**
 * History of exposure and condition of the animal as a grid of facts.
 */
function animal_bite_exposure_facts_html(array $case, array $appointment = [], bool $includeRecipient = true): string
{
    $facts = [];
    if ($includeRecipient) {
        $relationship = (string) ($case['relationship'] ?? 'Self');
        $name = animal_bite_recipient_name($case, $appointment);
        $facts[] = ['Patient (Bitten)', $name !== '' ? $name . ($relationship !== 'Self' ? ' · ' . $relationship . ' booking' : '') : '-'];
    }
    $facts[] = ['Date of Exposure', abc_date((string) ($case['exposure_date'] ?? ''), 'F j, Y')];
    $facts[] = ['Place of Exposure', (string) ($case['exposure_place'] ?: '-')];
    $facts[] = ['Type of Exposure', (string) ($case['exposure_type'] ?: '-')];
    $facts[] = ['Type of Animal', animal_bite_animal_label($case)];
    $facts[] = ['Condition of Animal', (string) ($case['animal_condition'] ?: '-')];

    $html = '<div class="abc-facts">';
    foreach ($facts as [$label, $value]) {
        $html .= '<div class="abc-fact"><small>' . abc_h($label) . '</small><strong>' . abc_h($value) . '</strong></div>';
    }

    return $html . '</div>';
}

/**
 * Case cards with the PEP timeline, for patient profiles. $theme: 'staff' | 'admin'.
 */
function render_animal_bite_case_cards(array $cases, string $theme = 'staff', array $patient = []): string
{
    if ($cases === []) {
        return '';
    }

    $stateLabels = [
        'completed' => 'Completed',
        'scheduled' => 'Scheduled',
        'due' => 'Upcoming',
        'overdue' => 'Missed / Overdue',
        'conditional' => 'Conditional',
        'not-needed' => 'Not needed',
    ];

    ob_start();
    echo animal_bite_styles();
    ?>
    <div class="abc-theme-<?= abc_h($theme); ?>">
        <div class="abc-section-head">
            <h4><span class="abc-head-icon"><?= abc_icon('paw', 18); ?></span> Animal Bite Cases</h4>
            <span class="abc-count"><?= count($cases); ?> <?= count($cases) === 1 ? 'case' : 'cases'; ?></span>
        </div>
        <div class="abc-case-stack">
            <?php foreach ($cases as $case): ?>
                <?php
                $firstAppt = $case['appointments'][0] ?? $patient;
                $requiredTotal = 3;
                $isCatI = (string) ($case['category'] ?? '') === 'I';
                $progressPct = $isCatI ? 100 : (int) round(min($case['completed_required'], $requiredTotal) / $requiredTotal * 100);
                $stationName = (string) ($firstAppt['station_name'] ?? '');
                ?>
                <article class="abc-case-card">
                    <div class="abc-case-top">
                        <div class="abc-case-title">
                            <span class="abc-case-icon"><?= abc_icon('paw', 20); ?></span>
                            <div>
                                <strong>Case #<?= abc_h((string) $case['case_code']); ?></strong>
                                <small>Opened <?= abc_h(abc_date((string) $case['created_at'], 'M j, Y')); ?><?= $stationName !== '' ? ' · ' . abc_h($stationName) : ''; ?></small>
                            </div>
                        </div>
                        <div class="abc-case-badges">
                            <?= animal_bite_category_pill($case['category'] ?? null); ?>
                            <span class="abc-pill abc-status-<?= abc_h($case['status']['key']); ?>"><?= abc_h($case['status']['label']); ?></span>
                        </div>
                    </div>
                    <div class="abc-case-body">
                        <?= animal_bite_exposure_facts_html($case, $firstAppt); ?>

                        <div class="abc-progress-row">
                            <h5><?= abc_icon('syringe', 15); ?> Post-Exposure Vaccination Schedule</h5>
                            <div class="abc-progress">
                                <div class="abc-progress-track"><div class="abc-progress-fill" style="width: <?= $progressPct; ?>%;"></div></div>
                                <span><?= $isCatI ? 'No series needed' : min($case['completed_required'], $requiredTotal) . ' of ' . $requiredTotal . ' required doses'; ?></span>
                            </div>
                        </div>

                        <div class="abc-sessions">
                            <?php foreach ($case['sessions'] as $session): ?>
                                <?php
                                $visit = $session['visit'];
                                $shownDate = $visit !== null ? (string) $visit['preferred_date'] : (string) ($session['target_date'] ?? '');
                                ?>
                                <div class="abc-session is-<?= abc_h($session['state']); ?>">
                                    <div class="abc-session-head">
                                        <span class="abc-session-day"><?= abc_h($session['label']); ?></span>
                                        <span class="abc-session-dot"><?= $session['state'] === 'completed' ? abc_icon('check', 12) : ''; ?></span>
                                    </div>
                                    <div class="abc-session-date">
                                        <?= $visit === null && $session['state'] !== 'not-needed' && $shownDate !== '' ? 'Target: ' : ''; ?><?= $session['state'] === 'not-needed' ? '-' : abc_h(abc_date($shownDate)); ?>
                                    </div>
                                    <?php if (!$session['required']): ?>
                                        <span class="abc-session-tag">If needed</span>
                                    <?php endif; ?>
                                    <div class="abc-session-state">
                                        <?= abc_h($stateLabels[$session['state']] ?? ''); ?>
                                        <?php if ($session['state'] === 'scheduled' && $session['visit_status'] !== ''): ?>
                                            · <?= abc_h($session['visit_status']); ?>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($session['doses'] !== []): ?>
                                        <ul class="abc-session-doses">
                                            <?php foreach ($session['doses'] as $dose): ?>
                                                <li><strong><?= abc_h((string) $dose['vaccine_type']); ?></strong> · <?= abc_h((string) $dose['dose']); ?><br><?= abc_h((string) $dose['route_site']); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php elseif ($session['note'] !== '' && in_array($session['state'], ['conditional', 'due'], true)): ?>
                                        <div class="abc-session-note"><?= abc_h($session['note']); ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="abc-footnote">
                            <?= abc_icon('clock', 14); ?>
                            <span>Target dates count from the Day 0 visit<?= !empty($case['base_date']) ? ' (' . abc_h(abc_date((string) $case['base_date'], 'F j, Y')) . ')' : ''; ?>. Day 14 and Day 28 are given only when the situation calls for them.</span>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
    <?php

    return (string) ob_get_clean();
}

/**
 * Animal bite details of one visit (vitals / remarks / medical file modals).
 */
function render_animal_bite_visit_summary(array $appointment, string $theme = 'staff', bool $showDoses = true): string
{
    $case = fetch_animal_bite_case_for_appointment($appointment);
    if ($case === null) {
        return '';
    }

    $sessionDay = appointment_bite_session_day($appointment);
    $doses = $showDoses ? fetch_animal_bite_doses_for_appointment((int) $appointment['id']) : [];

    ob_start();
    echo animal_bite_styles();
    ?>
    <div class="abc-theme-<?= abc_h($theme); ?>">
        <div class="abc-visit-card">
            <div class="abc-visit-head">
                <div><?= abc_icon('paw', 18); ?> <span>Animal Bite Case #<?= abc_h((string) $case['case_code']); ?></span></div>
                <div class="abc-case-badges">
                    <span class="abc-pill abc-status-ongoing"><?= abc_icon('calendar', 12); ?> <?= abc_h(animal_bite_session_label($sessionDay)); ?> visit</span>
                    <?= animal_bite_category_pill($case['category'] ?? null); ?>
                </div>
            </div>
            <?= animal_bite_exposure_facts_html($case, $appointment); ?>
            <?php if ($doses !== []): ?>
                <table class="abc-dose-table">
                    <thead><tr><th>Type of Vaccine</th><th>Dose</th><th>Route &amp; Site</th></tr></thead>
                    <tbody>
                        <?php foreach ($doses as $dose): ?>
                            <tr>
                                <td><strong><?= abc_h((string) $dose['vaccine_type']); ?></strong></td>
                                <td><?= abc_h((string) $dose['dose']); ?></td>
                                <td><?= abc_h((string) $dose['route_site']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    <?php

    return (string) ob_get_clean();
}

/**
 * Category picker for the staff vitals modal.
 */
function render_animal_bite_category_field(array $appointment, ?string $oldValue = null): string
{
    $case = fetch_animal_bite_case_for_appointment($appointment);
    $current = $oldValue ?? (string) ($case['category'] ?? '');

    ob_start();
    echo animal_bite_styles();
    ?>
    <div class="abc-theme-staff">
        <div class="abc-form-block form-group-item">
            <span class="abc-form-title"><?= abc_icon('paw', 18); ?> Category of Exposure <span class="required" style="color:#dc2626;">*</span></span>
            <p class="abc-form-sub">Classify the exposure after checking the wound. Categories II and III open the vaccine fields in the clinical remarks.</p>
            <div class="abc-cat-options" role="radiogroup" aria-label="Category of exposure">
                <?php foreach (animal_bite_categories() as $key => $cat): ?>
                    <label class="abc-cat-option cat-<?= abc_h($key); ?>">
                        <input type="radio" name="bite_category" value="<?= abc_h($key); ?>" <?= $current === $key ? 'checked' : ''; ?> required>
                        <span class="abc-cat-box">
                            <span class="abc-cat-num"><?= abc_h($key); ?></span>
                            <strong><?= abc_h($cat['label']); ?></strong>
                            <small><?= abc_h($cat['summary']); ?></small>
                            <em><?= abc_h($cat['action']); ?></em>
                        </span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php

    return (string) ob_get_clean();
}

/**
 * Type of Vaccine / Dose / Route & Site rows for the staff clinical remarks modal (Category II and III).
 * Read-only when $editable is false.
 */
function render_animal_bite_remarks_fields(array $appointment, bool $editable = true): string
{
    $case = fetch_animal_bite_case_for_appointment($appointment);
    if ($case === null) {
        return '';
    }

    $category = (string) ($case['category'] ?? '');
    $doses = fetch_animal_bite_doses_for_appointment((int) $appointment['id']);
    $options = animal_bite_vaccine_options($category);
    $guide = animal_bite_vaccine_guide();
    $weightKg = (float) preg_replace('/[^0-9.]/', '', (string) ($appointment['weight'] ?? ''));

    ob_start();
    echo animal_bite_styles();
    ?>
    <div class="abc-theme-staff">
        <?php if (!animal_bite_category_needs_vaccine($category)): ?>
            <div class="abc-form-block">
                <span class="abc-form-title"><?= abc_icon('paw', 18); ?> Vaccines Given</span>
                <div class="abc-info-note">
                    <?= abc_icon('shield', 18); ?>
                    <div>
                        <?php if ($category === 'I'): ?>
                            <strong>Category I exposure - no vaccine needed.</strong><br>
                            Wash the exposed skin and give health education. Note the advice in the clinical notes below.
                        <?php else: ?>
                            <strong>Category not yet assessed.</strong><br>
                            The category is set while encoding vital signs. Vaccine fields appear for Category II and III.
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php elseif (!$editable): ?>
            <div class="abc-form-block">
                <span class="abc-form-title"><?= abc_icon('syringe', 18); ?> Vaccines Given</span>
                <?php if ($doses === []): ?>
                    <div class="abc-info-note">No vaccines recorded for this visit.</div>
                <?php else: ?>
                    <table class="abc-dose-table">
                        <thead><tr><th>Type of Vaccine</th><th>Dose</th><th>Route &amp; Site</th></tr></thead>
                        <tbody>
                            <?php foreach ($doses as $dose): ?>
                                <tr>
                                    <td><strong><?= abc_h((string) $dose['vaccine_type']); ?></strong></td>
                                    <td><?= abc_h((string) $dose['dose']); ?></td>
                                    <td><?= abc_h((string) $dose['route_site']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <?php $rows = $doses !== [] ? $doses : [['vaccine_type' => '', 'dose' => '', 'route_site' => '']]; ?>
            <div class="abc-form-block" id="abcDoseBlock" data-guide='<?= abc_h(json_encode($guide, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)); ?>' data-weight="<?= $weightKg > 0 ? abc_h((string) $weightKg) : ''; ?>">
                <span class="abc-form-title"><?= abc_icon('syringe', 18); ?> Vaccines Given (<?= abc_h(animal_bite_session_label(appointment_bite_session_day($appointment))); ?>) <span class="required" style="color:#dc2626;">*</span></span>
                <p class="abc-form-sub">
                    Record every vaccine given at this visit.
                    <?= $category === 'III' ? 'Category III: ERIG / HRIG (rabies immunoglobulin) are available.' : 'ERIG / HRIG are only available for Category III.'; ?>
                    The dose hint is a reference; follow the physician's order.
                </p>
                <div class="abc-dose-rows" id="abcDoseRows">
                    <?php foreach ($rows as $index => $row): ?>
                        <div class="abc-dose-row">
                            <div>
                                <label>Type of Vaccine</label>
                                <select name="bite_vaccine_type[]" class="form-input-field abc-vaccine-select" <?= $index === 0 ? 'required' : ''; ?>>
                                    <option value="">-- Select vaccine --</option>
                                    <?php foreach ($options as $option): ?>
                                        <option value="<?= abc_h($option); ?>" <?= (string) $row['vaccine_type'] === $option ? 'selected' : ''; ?>><?= abc_h($option); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <span class="abc-dose-hint abc-vaccine-name"></span>
                            </div>
                            <div>
                                <label>Dose</label>
                                <input type="text" name="bite_dose[]" value="<?= abc_h((string) $row['dose']); ?>" class="form-input-field abc-dose-input" maxlength="60" autocomplete="off" <?= $index === 0 ? 'required' : ''; ?>>
                                <span class="abc-dose-hint abc-dose-note"></span>
                                <button type="button" class="abc-use-std" hidden>Use standard dose</button>
                            </div>
                            <div>
                                <label>Route &amp; Site</label>
                                <input type="text" name="bite_route_site[]" value="<?= abc_h((string) $row['route_site']); ?>" class="form-input-field abc-route-input" maxlength="150" placeholder="e.g. ID - Left & right deltoid" autocomplete="off" <?= $index === 0 ? 'required' : ''; ?>>
                                <span class="abc-dose-hint abc-route-note"></span>
                            </div>
                            <button type="button" class="abc-row-remove" aria-label="Remove vaccine row"><?= abc_icon('x', 14); ?></button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="abc-add-row" id="abcAddDoseRow"><?= abc_icon('plus', 14); ?> Add another vaccine</button>
            </div>
            <script>
            (function () {
                var block = document.getElementById('abcDoseBlock');
                if (!block) return;
                var guide = {};
                try { guide = JSON.parse(block.getAttribute('data-guide') || '{}'); } catch (e) {}
                var weight = parseFloat(block.getAttribute('data-weight') || '');
                var rowsWrap = document.getElementById('abcDoseRows');

                function standardDose(info) {
                    if (!info) return '';
                    if (info.per_kg && weight > 0) {
                        return Math.round(info.per_kg * weight) + ' IU';
                    }
                    return info.dose || '';
                }

                function refreshRow(row) {
                    var select = row.querySelector('.abc-vaccine-select');
                    var info = guide[select.value] || null;
                    var doseInput = row.querySelector('.abc-dose-input');
                    var useBtn = row.querySelector('.abc-use-std');
                    var std = standardDose(info);
                    row.querySelector('.abc-vaccine-name').textContent = info ? info.name : '';
                    var note = info ? info.dose_note : '';
                    if (info && info.per_kg && weight > 0) {
                        note += ' (' + weight + ' kg = ' + std + ')';
                    } else if (info && info.per_kg) {
                        note += ' (no weight recorded)';
                    }
                    row.querySelector('.abc-dose-note').textContent = note;
                    row.querySelector('.abc-route-note').textContent = info ? 'Usual: ' + info.route : '';
                    doseInput.placeholder = std !== '' ? 'e.g. ' + std : (info ? 'Enter dose' : 'Select a vaccine first');
                    row.querySelector('.abc-route-input').placeholder = info ? 'e.g. ' + info.route : 'e.g. ID - Left & right deltoid';
                    useBtn.hidden = std === '' || doseInput.value.trim() === std;
                    useBtn.dataset.value = std;
                }

                function refreshRemoveButtons() {
                    var rows = rowsWrap.querySelectorAll('.abc-dose-row');
                    rows.forEach(function (r) { r.querySelector('.abc-row-remove').disabled = rows.length === 1; });
                }

                function bindRow(row) {
                    row.querySelector('.abc-vaccine-select').addEventListener('change', function () { refreshRow(row); });
                    row.querySelector('.abc-dose-input').addEventListener('input', function () { refreshRow(row); });
                    row.querySelector('.abc-use-std').addEventListener('click', function () {
                        row.querySelector('.abc-dose-input').value = this.dataset.value || '';
                        refreshRow(row);
                    });
                    row.querySelector('.abc-row-remove').addEventListener('click', function () {
                        if (rowsWrap.querySelectorAll('.abc-dose-row').length > 1) {
                            row.remove();
                            refreshRemoveButtons();
                        }
                    });
                    refreshRow(row);
                }

                rowsWrap.querySelectorAll('.abc-dose-row').forEach(bindRow);
                refreshRemoveButtons();

                document.getElementById('abcAddDoseRow').addEventListener('click', function () {
                    var template = rowsWrap.querySelector('.abc-dose-row');
                    var clone = template.cloneNode(true);
                    clone.querySelectorAll('select, input').forEach(function (el) { el.value = ''; el.required = false; });
                    rowsWrap.appendChild(clone);
                    bindRow(clone);
                    refreshRemoveButtons();
                    clone.querySelector('.abc-vaccine-select').focus();
                });
            })();
            </script>
        <?php endif; ?>
    </div>
    <?php

    return (string) ob_get_clean();
}
