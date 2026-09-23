<?php
$adminHtml = file_get_contents(__DIR__ . '/live_admin_patient_p2yll5.html');
$pos = strpos($adminHtml, '#AH3BSATG');
if ($pos !== false) {
    echo substr($adminHtml, $pos - 400, 1200);
}
