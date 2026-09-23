<?php
$html = file_get_contents(__DIR__ . '/live_staff_patients.html');
$pos = strpos($html, 'id="patientProfileContent_id_p2yll5"');
$sub = substr($html, $pos);
$end = strpos($sub, '</article>');
$leoSection = substr($sub, 0, $end !== false ? $end + 10 : 10000);

preg_match_all('/<article class="history-timeline-entry-card"[\s\S]*?<\/article>/', $sub, $matches);
echo "Total timeline entries for Leo: " . count($matches[0]) . PHP_EOL;

foreach ($matches[0] as $i => $card) {
    preg_match('/class="appt-code-badge"[^>]*>#?([^<]+)<\/span>/', $card, $mCode);
    preg_match('/<span class="service-pill-tag"[^>]*>([\s\S]*?)<\/span>/', $card, $mSvc);
    preg_match('/<span class="history-date-label"[^>]*>([\s\S]*?)<\/span>/', $card, $mDate);
    preg_match('/<img[^>]+src="([^"]+)"/', $card, $mImg);
    
    $code = trim($mCode[1] ?? 'UNKNOWN');
    $svc = trim(strip_tags($mSvc[1] ?? 'UNKNOWN'));
    $date = trim($mDate[1] ?? 'UNKNOWN');
    $img = $mImg[1] ?? 'NO_IMAGE';
    
    echo "[$i] Code: $code | Date: $date | Service: $svc | Image: $img" . PHP_EOL;
}
