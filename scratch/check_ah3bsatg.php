<?php
$staffHtml = file_get_contents(__DIR__ . '/live_staff_patients.html');
$pos = strpos($staffHtml, '#AH3BSATG');
if ($pos !== false) {
    echo substr($staffHtml, $pos + 3200, 2000);
}
