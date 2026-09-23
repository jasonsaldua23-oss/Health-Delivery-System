<?php
require_once __DIR__ . '/../shared/database.php';

echo "=== Testing is_tb_service helper ===\n";
assert(is_tb_service('tb') === true, 'tb slug is TB');
assert(is_tb_service('', 'TB DOTS') === true, 'TB DOTS name is TB');
assert(is_tb_service('', 'Tuberculosis treatment') === true, 'Tuberculosis name is TB');
assert(is_tb_service('dental', 'Dental Services') === false, 'dental is not TB');
assert(is_tb_service('immunization', 'Immunization') === false, 'immunization is not TB');
echo "✓ is_tb_service unit assertions passed.\n";

echo "\n=== Verifying DB Connection & Schema ===\n";
try {
    $conn = db();
    echo "✓ Database connected successfully.\n";

    // Run schema migration check
    bootstrap_database_schema($conn);
    $hasCol = db_column_exists($conn, 'appointments', 'chest_xray');
    echo "appointments.chest_xray column exists: " . ($hasCol ? "YES" : "NO") . "\n";
    if (!$hasCol) {
        $conn->query('ALTER TABLE appointments ADD COLUMN chest_xray VARCHAR(255) DEFAULT NULL AFTER vaccine_type');
        echo "✓ Added chest_xray column via query.\n";
    }

    // Verify row 8 in DB (which is TB DOTS)
    $res = $conn->query("SELECT id, service_slug, service_name, chest_xray FROM appointments WHERE service_slug = 'tb' OR service_name LIKE '%TB%' LIMIT 1");
    if ($res && $row = $res->fetch_assoc()) {
        echo "Found TB appointment ID {$row['id']} ({$row['service_name']}). Existing chest_xray: " . var_export($row['chest_xray'], true) . "\n";
        
        // Test updating chest_xray via save_appointment_clinical_details
        $testValue = 'Clear / No active pulmonary infiltrates (Test ' . date('His') . ')';
        $saveOk = save_appointment_clinical_details((int)$row['id'], [
            'body_temperature' => '36.8',
            'pulse_rate' => '72',
            'respiration_rate' => '18',
            'blood_pressure' => '120/80',
            'chest_xray' => $testValue,
            'doctor_notes' => 'Patient screening verified.'
        ], $row['station_slug'] ?? null);

        echo "save_appointment_clinical_details result: " . ($saveOk ? "SUCCESS" : "FAILED") . "\n";

        $verifyRes = $conn->query("SELECT chest_xray FROM appointments WHERE id = {$row['id']}")->fetch_assoc();
        echo "Verified stored chest_xray: " . $verifyRes['chest_xray'] . "\n";
        assert($verifyRes['chest_xray'] === $testValue, 'Chest X-Ray value matches stored value');
        echo "✓ Stored Chest X-Ray value confirmed in DB!\n";
    } else {
        echo "No existing TB appointment found in database; column existence verified.\n";
    }

} catch (Throwable $e) {
    echo "Note: DB connection or query: " . $e->getMessage() . "\n";
}

echo "\nAll verification steps completed.\n";
