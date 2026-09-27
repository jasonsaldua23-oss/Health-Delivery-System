<?php
require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';

try {
    $db = db();
    echo "Connected successfully to " . DB_NAME . "\n";
    $res = $db->query("SELECT id, recipient_first_name, recipient_last_name, photo_path FROM appointments WHERE recipient_first_name LIKE '%Ayanna%'");
    while ($row = $res->fetch_assoc()) {
        print_r($row);
    }
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
