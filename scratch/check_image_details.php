<?php
$files = glob(__DIR__ . '/../Patients/uploads/*');
foreach ($files as $f) {
    $info = getimagesize($f);
    echo basename($f) . ": " . ($info ? "{$info[0]}x{$info[1]} {$info['mime']}" : "not image") . " (" . filesize($f) . " bytes)\n";
}
