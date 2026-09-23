<?php
foreach ([
    'Patients/uploads/patient_6aa9f83bb24f21.47687780.jpg',
    'Patients/uploads/patient_6aabb60f27e1c0.02536229.jpg',
    'Patients/uploads/patient_6aacd925312254.71921599.jpg',
    'Patients/uploads/patient_6a962ef1ea9091.86311167.jpg',
    'scratch/old_6aac.jpg',
    'scratch/zacarias.png'
] as $f) {
    echo $f . ': ' . (file_exists($f) ? filesize($f) . ' bytes, md5=' . md5_file($f) : 'NOT FOUND') . PHP_EOL;
}
