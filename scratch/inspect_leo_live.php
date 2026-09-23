<?php
$staffHtml = file_get_contents(__DIR__ . '/live_staff_patients.html');

// Find all patient profile cards and content keys
preg_match_all('/id="profileCard_([^"]*)"[\s\S]*?<h3[^>]*>([^<]+)<\/h3>/', $staffHtml, $cards);
for ($i = 0; $i < count($cards[0]); $i++) {
    echo "Card {$cards[1][$i]}: {$cards[2][$i]}\n";
}

// Find all patientProfileContent divs
preg_match_all('/id="patientProfileContent_([^"]+)"/', $staffHtml, $contentKeys);
echo "\nContent Keys found: " . implode(', ', $contentKeys[1]) . "\n";

// Check Leo specifically:
if (preg_match('/id="patientProfileContent_id_p2yll5"[^>]*>([\s\S]*?)<\/div>\s*<\/article>/', $staffHtml, $m)) {
    echo "\n=== LEO CONTENT (id_p2yll5) ===\n";
    $leoContent = $m[1];
    if (preg_match('/<div class="patient-profile-avatar-box"[^>]*>([\s\S]*?)<\/div>/', $leoContent, $mAv)) {
        echo "Leo Profile Avatar Box:\n" . trim($mAv[1]) . "\n\n";
    }
    // Check history entries
    if (preg_match_all('/<article class="history-timeline-entry-card"[\s\S]*?<\/article>/', $leoContent, $entries)) {
        echo "Leo History Entries count: " . count($entries[0]) . "\n";
        foreach ($entries[0] as $eIdx => $entry) {
            preg_match('/<span class="service-pill-tag"[^>]*>([\s\S]*?)<\/span>/', $entry, $svcM);
            preg_match('/<span class="history-date-label"[^>]*>([\s\S]*?)<\/span>/', $entry, $dateM);
            preg_match('/class="appt-code-badge"[^>]*>#?([^<]+)<\/span>/', $entry, $codeM);
            $hasPhotoCard = str_contains($entry, 'history-visit-photo-card');
            echo "  Entry $eIdx: Code: " . trim($codeM[1] ?? '') . " | Svc: " . trim(strip_tags($svcM[1] ?? '')) . " | Date: " . trim($dateM[1] ?? '') . " | Has Photo Card: " . ($hasPhotoCard ? 'YES' : 'NO') . "\n";
            if ($hasPhotoCard && preg_match('/<div class="history-visit-photo-card"[\s\S]*?<\/div>/', $entry, $phCard)) {
                echo "    Photo Card HTML: " . trim($phCard[0]) . "\n";
            }
        }
    }
}
