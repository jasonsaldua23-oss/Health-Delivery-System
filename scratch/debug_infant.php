<?php
require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';

$mockPatientId = 'P-TEST-PHOTO-01';
$appt = [
    'id' => 99999,
    'patient_id' => $mockPatientId,
    'first_name' => 'Maria',
    'last_name' => 'Clara',
    'gender' => 'Female',
    'service_slug' => 'immunization',
    'service_name' => 'Immunization',
    'status' => 'Completed',
    'preferred_date' => date('Y-m-d'),
    'preferred_time' => 'Daily Slot',
    'immunization_relationship' => 'Child',
    'recipient_first_name' => 'Baby',
    'recipient_last_name' => 'Clara',
    'recipient_birth_date' => '2026-01-15',
    'recipient_gender' => 'Female',
    'photo_path' => 'https://example.com/baby.jpg',
    'vaccine_type' => 'BCG',
    'doctor_notes' => 'Notes',
];

try {
    $res = fetch_infant_sub_profiles_by_patient_id($mockPatientId, [$appt]);
    echo "SUCCESS: " . json_encode($res, JSON_PRETTY_PRINT);
} catch (Throwable $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
