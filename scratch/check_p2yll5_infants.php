<?php
require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';

$res = fetch_infant_sub_profiles_by_patient_id('P2YLL5');
echo json_encode($res, JSON_PRETTY_PRINT) . "\n";
