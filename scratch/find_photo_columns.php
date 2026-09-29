<?php
require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';

$db = db();
$res = $db->query("SHOW TABLES");
while ($row = $res->fetch_row()) {
    $table = $row[0];
    $cRes = $db->query("SHOW COLUMNS FROM `$table`");
    $cols = [];
    while ($c = $cRes->fetch_assoc()) {
        if (preg_match('/photo|image|picture|file|avatar/i', $c['Field'])) {
            $cols[] = $c['Field'];
        }
    }
    if (!empty($cols)) {
        echo "Table: $table -> Columns: " . implode(', ', $cols) . "\n";
    }
}
