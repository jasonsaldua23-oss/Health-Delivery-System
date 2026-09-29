<?php
$files = glob(__DIR__ . '/../Patients/uploads/*');
foreach ($files as $f) {
    $base = basename($f);
    // search in sql files
    $sqlFiles = glob(__DIR__ . '/../*.sql');
    $matches = [];
    foreach ($sqlFiles as $sf) {
        $content = file_get_contents($sf);
        if (strpos($content, $base) !== false) {
            $matches[] = basename($sf);
        }
    }
    if (!empty($matches)) {
        echo "$base found in SQL: " . implode(', ', $matches) . "\n";
    }
}
