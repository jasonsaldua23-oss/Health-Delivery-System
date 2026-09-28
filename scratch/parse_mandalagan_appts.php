<?php
$html = file_get_contents(__DIR__ . '/live_mandalagan.html');
preg_match_all('/#([A-Z0-9]{6,10})/', $html, $m);
echo "Codes found: " . json_encode(array_unique($m[1])) . "\n";

// Let's see if there are any appointment cards or rows
preg_match_all('/appt-card[^\"]*/', $html, $m2);
echo "Appt card classes: " . json_encode(array_unique($m2[0])) . "\n";

// Let's check what services are in the services-grid
preg_match_all('/<a class="service-card[^>]*href="([^"]*)"[^>]*>[\s\S]*?<h3>(.*?)<\/h3>/', $html, $m3, PREG_SET_ORDER);
foreach ($m3 as $row) {
    echo "Service link: {$row[1]} | Title: {$row[2]}\n";
}
