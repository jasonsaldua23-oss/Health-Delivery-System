<?php
declare(strict_types=1);

require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';

echo "Testing station images implementation...\n";

$stations = station_catalog();
$stationMap = [];
foreach ($stations as $st) {
    $stationMap[$st['slug']] = $st;
}

$expectedSlugs = ['alijis', 'bata', 'cabug', 'estefania', 'granada'];

foreach ($expectedSlugs as $slug) {
    if (!isset($stationMap[$slug])) {
        echo "FAILED: Station slug '{$slug}' not found in catalog!\n";
        exit(1);
    }

    $image = $stationMap[$slug]['image'];
    echo "Station '{$slug}': image => {$image}\n";

    if (!str_contains($image, $slug)) {
        echo "FAILED: Expected image path to contain '{$slug}', got '{$image}'!\n";
        exit(1);
    }

    // Check if file exists relative to Admin directory
    $adminPath = __DIR__ . '/../Admin/' . $image;
    $realAdminPath = realpath($adminPath);
    if ($realAdminPath === false || !file_exists($realAdminPath)) {
        echo "FAILED: Image file does not resolve from Admin path: {$adminPath}\n";
        exit(1);
    }

    // Check dimensions
    $size = getimagesize($realAdminPath);
    if ($size === false) {
        echo "FAILED: Cannot read image size of {$realAdminPath}\n";
        exit(1);
    }

    echo "  Dimensions: {$size[0]} x {$size[1]} ({$size['mime']})\n";
    if ($size[0] !== 900 || $size[1] !== 600) {
        echo "FAILED: Expected 900x600, got {$size[0]}x{$size[1]}!\n";
        exit(1);
    }
}

echo "\nAll 5 stations successfully configured with exact 900x600 replacement images!\n";
