<?php
try {
    $m = new mysqli('127.0.0.1', 'root', '', '', 3306);
    echo "Connected to MySQL! Version: " . $m->server_info . PHP_EOL;
    $res = $m->query("SHOW DATABASES");
    while($row = $res->fetch_row()) {
        echo "DB: " . $row[0] . PHP_EOL;
    }
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
