<?php
$html = file_get_contents(__DIR__ . '/live_visit_30.html');
// Check consultation form modal
if (preg_match('/<div class="consultation-form-modal"[\s\S]*?<\/div>\s*<\/div>\s*<\/div>/', $html, $m)) {
    echo "Found modal!\n";
}
// Search for img or photo in the consultation modal or whole file
preg_match_all('/<img[^>]+>/', $html, $imgs);
foreach ($imgs[0] as $img) {
    echo "IMG: $img\n";
}

// Search for any mention of photo or upload or jpg
preg_match_all('/[a-zA-Z0-9_\-\.\/]+\.jpe?g/i', $html, $jpgs);
echo "JPGs found: " . implode(', ', array_unique($jpgs[0])) . "\n";

// Search for selectedAdminVisit or photo_path
if (preg_match('/<!-- Visit Consultation Modal -->([\s\S]*?)<\/article>/', $html, $modalM)) {
    echo "Modal block:\n" . substr($modalM[1], 0, 1500) . "\n";
}
