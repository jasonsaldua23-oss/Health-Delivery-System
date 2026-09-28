<?php
$services = ['immunization', 'prenatal', 'family', 'tb', 'consultation'];
foreach ($services as $srv) {
    $url = "https://bsns.online/Admin/index.php?page=appointments&station=mandalagan&program={$srv}&status=&date=both";
    $cmd = "curl.exe -b scratch/live_admin_cookie.txt -s \"$url\"";
    $out = shell_exec($cmd);
    
    // Check for appointments
    preg_match_all('/<article class="appt-card-modern[^"]*"[^>]*>[\s\S]*?<\/article>/', $out, $appts);
    $count = count($appts[0]);
    
    // Check for empty state
    $hasEmpty = strpos($out, 'empty-state') !== false;
    
    // Check for count in badges
    preg_match('/<span class="status-pill[^"]*">(.*?)<\/span>/', $out, $mStatus);
    
    echo "Service: {$srv} | Appt cards found: {$count} | Has empty-state: " . ($hasEmpty ? 'yes' : 'no') . "\n";
    if ($count > 0) {
        foreach ($appts[0] as $a) {
            echo "   " . strip_tags($a) . "\n";
        }
    }
}
