<?php
$files = glob(__DIR__ . '/../Patients/uploads/*.{jpg,jpeg,png,webp}', GLOB_BRACE);
foreach ($files as $f) {
    $info = getimagesize($f);
    echo basename($f) . ": " . ($info ? "{$info[0]}x{$info[1]} {$info['mime']}" : "unknown") . " (" . filesize($f) . " bytes)\n";
}
