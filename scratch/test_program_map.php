<?php
require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';

$programMap = station_program_map_with_assignments();
echo "Mandalagan programMap:\n";
print_r($programMap['mandalagan'] ?? []);

$catalog = service_catalog();
echo "\nCatalog keys:\n";
print_r(array_keys($catalog));
