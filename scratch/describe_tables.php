<?php
require_once __DIR__ . '/../shared/database.php';
$db = db();
foreach (['patient_profiles', 'patient_accounts', 'infant_profiles', 'appointments', 'immunized_infants'] as $tbl) {
    $res = $db->query("DESCRIBE $tbl");
    echo "=== $tbl ===\n";
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            echo "  " . $r['Field'] . " (" . $r['Type'] . ")\n";
        }
    } else {
        echo "  Table does not exist or error: " . $db->error . "\n";
    }
}
