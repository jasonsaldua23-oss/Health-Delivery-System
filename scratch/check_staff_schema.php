<?php
require_once __DIR__ . '/../shared/database.php';

echo "=== SHOW CREATE TABLE staff_accounts ===\n";
$res = db()->query("SHOW CREATE TABLE staff_accounts");
if ($res) {
    $row = $res->fetch_assoc();
    echo $row['Create Table'] . "\n";
} else {
    echo "Error: " . db()->error . "\n";
}
