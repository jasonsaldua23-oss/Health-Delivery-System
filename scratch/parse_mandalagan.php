<?php
$html = file_get_contents(__DIR__ . '/live_mandalagan.html');
echo "HTML length: " . strlen($html) . "\n";

// Search for any service cards
preg_match_all('/<a class="service-card[^"]*"[^>]*>[\s\S]*?<\/a>/', $html, $matches);
foreach ($matches[0] as $card) {
    if (preg_match('/<h3>(.*?)<\/h3>/', $card, $mTitle)) {
        echo "Service: " . trim($mTitle[1]) . "\n";
    }
    if (preg_match_all('/<div class="queue-stat-mini[^"]*"><span>.*?<\/span><strong>(.*?)<\/strong><small>(.*?)<\/small><\/div>/', $card, $mStats, PREG_SET_ORDER)) {
        foreach ($mStats as $st) {
            echo "  {$st[2]}: {$st[1]}\n";
        }
    }
}
