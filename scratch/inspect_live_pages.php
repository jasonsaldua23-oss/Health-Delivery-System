<?php
echo "=== ADMIN PATIENT VIEW (P2YLL5) ===\n";
$adminHtml = file_get_contents(__DIR__ . '/live_admin_patient_p2yll5.html');

// Check avatar box
if (preg_match('/<div class="patient-hero-avatar"[^>]*>([\s\S]*?)<\/div>/', $adminHtml, $m)) {
    echo "Admin Avatar Box:\n" . trim($m[1]) . "\n";
}

// Check consultation rows
if (preg_match_all('/<tr[^>]*>([\s\S]*?)<\/tr>/', $adminHtml, $trMatches)) {
    echo "\nAdmin Table Rows (" . count($trMatches[0]) . "):\n";
    foreach ($trMatches[1] as $idx => $tr) {
        if (str_contains($tr, '<th')) continue;
        preg_match_all('/<td[^>]*>([\s\S]*?)<\/td>/', $tr, $tdMatches);
        $cells = array_map(fn($c) => trim(strip_tags($c)), $tdMatches[1] ?? []);
        echo "Row $idx: " . implode(' | ', $cells) . "\n";
        // Check for modal data-record or photo in this row
        if (preg_match('/data-record="([^"]+)"/', $tr, $recMatch)) {
            $rec = json_decode(htmlspecialchars_decode($recMatch[1]), true);
            echo "   -> Appointment ID: " . ($rec['id'] ?? '') . " | Code: " . ($rec['appointment_code'] ?? '') . " | Svc: " . ($rec['service_name'] ?? '') . " | Photo: '" . ($rec['photo_path'] ?? '') . "'\n";
        }
    }
}

echo "\n=== STAFF PATIENTS VIEW ===\n";
$staffHtml = file_get_contents(__DIR__ . '/live_staff_patients.html');
// Find Leo Taboclaon Zacarias profile card or content
if (preg_match('/id="profileCard_[^"]*"[\s\S]*?Leo Taboclaon Zacarias[\s\S]*?<\/article>/', $staffHtml, $mCard)) {
    echo "Found Profile Card for Leo!\n";
    // Check avatar
    if (preg_match('/<div class="pat-card-avatar[^"]*"[^>]*>([\s\S]*?)<\/div>/', $mCard[0], $mAv)) {
        echo "Card Avatar: " . trim($mAv[1]) . "\n";
    }
}

// Find patientProfileContent for Leo
if (preg_match('/id="patientProfileContent_([^"]+)"[^>]*>([\s\S]*?)<\/div>\s*<\/article>/', $staffHtml, $mContent)) {
    echo "\nFound patientProfileContent for key: " . $mContent[1] . "\n";
    $content = $mContent[2];
    if (preg_match('/<div class="patient-profile-avatar-box"[^>]*>([\s\S]*?)<\/div>/', $content, $mProfAv)) {
        echo "Modal Avatar Box:\n" . trim($mProfAv[1]) . "\n";
    }
    // Check history entries
    if (preg_match_all('/<article class="history-timeline-entry-card"[\s\S]*?<\/article>/', $content, $entries)) {
        echo "History Entries count: " . count($entries[0]) . "\n";
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
