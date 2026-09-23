<?php
require_once __DIR__ . '/../shared/bootstrap.php';
$m = new mysqli('127.0.0.1', 'root', '', '', 3306);
$res = $m->query("SHOW DATABASES");
while ($row = $res->fetch_row()) {
    echo "DB: " . $row[0] . "\n";
}
