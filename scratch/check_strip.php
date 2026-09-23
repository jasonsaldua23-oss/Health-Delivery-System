<?php
$html = file_get_contents(__DIR__ . '/live_visit_30.html');
$pos = strpos($html, 'clinical-patient-overview-strip');
if ($pos !== false) {
    echo substr($html, $pos - 50, 600);
} else {
    echo "clinical-patient-overview-strip NOT found in live_visit_30.html\n";
}
