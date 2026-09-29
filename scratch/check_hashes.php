<?php
$files = glob(__DIR__ . '/../Patients/uploads/*');
$hashes = [];
foreach ($files as $f) {
    $h = md5_file($f);
    $base = basename($f);
    $hashes[$h][] = $base;
}
foreach ($hashes as $h => $list) {
    echo "$h:\n";
    foreach ($list as $file) {
        echo "  - $file\n";
    }
}
