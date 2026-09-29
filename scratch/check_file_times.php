<?php
$files = glob(__DIR__ . '/../Patients/uploads/*');
foreach ($files as $f) {
    echo date('Y-m-d H:i:s', filemtime($f)) . " - " . basename($f) . " (" . filesize($f) . " bytes)\n";
}
