<?php

declare(strict_types=1);

require_once __DIR__ . '/../../shared/bootstrap.php';
require_once __DIR__ . '/../../shared/database.php';

$connection = db();

$today = date('Y-m-d');

$connection->query("
    INSERT INTO appointments (
        reference_code, appointment_code, patient_id, station_slug, station_name,
        service_slug, service_name, first_name, last_name, birth_date, gender,
        contact_number, complete_address, preferred_date, preferred_time, status,
        body_temperature, pulse_rate, respiration_rate, blood_pressure, doctor_notes, photo_path
    ) VALUES 
    (
        'REF-TEST-IMM1', 'TC-IMM-1', 'AD00A8', 'bata', 'Bata Barangay Health Station',
        'immunization', 'Immunization & Vaccination', 'Baby', 'Dela Cruz', '2023-01-15', 'Male',
        '09123456789', 'Sunriser, Bata, Bacolod City', '{$today}', '08:30 AM', 'Pending',
        '36.6', '75', '18', '120/80', 'Routine immunization check', 'uploads/patient_6a014e0ee4da13.26109864.jpg'
    ),
    (
        'REF-TEST-IMM2', 'TC-IMM-2', 'AD00A8', 'bata', 'Bata Barangay Health Station',
        'immunization', 'Immunization & Vaccination', 'Toddler', 'Dela Cruz', '2022-05-10', 'Female',
        '09123456789', 'Sunriser, Bata, Bacolod City', '{$today}', '09:00 AM', 'Confirmed',
        '36.6', '75', '18', '120/80', 'Vaccine administered safely', 'uploads/patient_6a014e0ee4da13.26109864.jpg'
    ),
    (
        'REF-TEST-001', 'TC-BATA-1', 'AD00A8', 'bata', 'Bata Barangay Health Station',
        'consultation', 'General Consultation', 'Juan', 'Dela Cruz', '1995-05-15', 'Male',
        '09123456789', 'Sunriser, Bata, Bacolod City', '{$today}', '09:30 AM', 'Pending',
        '36.6', '75', '18', '120/80', 'Initial consultation notes', 'uploads/patient_6a014e0ee4da13.26109864.jpg'
    ),
    (
        'REF-TEST-002', 'TC-BATA-2', 'AD00A8', 'bata', 'Bata Barangay Health Station',
        'consultation', 'General Consultation', 'Juan', 'Dela Cruz', '1995-05-15', 'Male',
        '09123456789', 'Sunriser, Bata, Bacolod City', '{$today}', '10:00 AM', 'Confirmed',
        '36.6', '75', '18', '120/80', 'Triage consultation notes', 'uploads/patient_6a014e0ee4da13.26109864.jpg'
    ),
    (
        'REF-TEST-003', 'TC-BATA-3', 'AD00A8', 'bata', 'Bata Barangay Health Station',
        'consultation', 'General Consultation', 'Juan', 'Dela Cruz', '1995-05-15', 'Male',
        '09123456789', 'Sunriser, Bata, Bacolod City', '{$today}', '10:30 AM', 'Completed',
        '36.6', '75', '18', '120/80', 'Completed consultation notes', 'uploads/patient_6a014e0ee4da13.26109864.jpg'
    )
    ON DUPLICATE KEY UPDATE 
        status = VALUES(status),
        preferred_date = VALUES(preferred_date),
        body_temperature = VALUES(body_temperature),
        pulse_rate = VALUES(pulse_rate),
        respiration_rate = VALUES(respiration_rate),
        blood_pressure = VALUES(blood_pressure),
        doctor_notes = VALUES(doctor_notes),
        photo_path = VALUES(photo_path)
");

echo "Seeded test appointments successfully\n";
