<?php
require_once __DIR__ . '/../shared/database.php';
$db = db();
echo "Current DB: " . $db->query("SELECT DATABASE()")->fetch_row()[0] . "\n";
$res = $db->query("SHOW DATABASES");
echo "Databases:\n";
while ($r = $res->fetch_row()) {
    echo " - " . $r[0] . "\n";
}
