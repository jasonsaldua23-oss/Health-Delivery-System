<?php
$staffHtml = file_get_contents(__DIR__ . '/live_staff_patients.html');
$pos = strpos($staffHtml, 'id="patientProfileContent_id_p2yll5"');
if ($pos !== false) {
    echo substr($staffHtml, $pos + 2500, 4000);
}
