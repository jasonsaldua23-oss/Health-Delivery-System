<?php
$c = file_get_contents(__DIR__ . '/live_reports_test2.html');
preg_match_all('/data-record="([^"]+)"/', $c, $m);
foreach ($m[1] as $raw) {
    $dec = json_decode(htmlspecialchars_decode($raw), true);
    if (($dec['patient_id'] ?? '') === 'P2YLL5') {
        echo "ID: {$dec['id']} | Code: {$dec['appointment_code']} | Svc: {$dec['service_name']} | Date: {$dec['preferred_date']} | Status: {$dec['status']} | Photo: {$dec['photo_path']}\n";
    }
}
