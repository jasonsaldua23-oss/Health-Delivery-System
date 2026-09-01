<?php

declare(strict_types=1);

$contact = [
    'phone' => '(034) 123-4567',
    'address' => 'Bacolod City Health Office, Bacolod City, Negros Occidental',
    'hours' => 'Monday - Saturday: 8:00 AM - 5:00 PM',
];

$events = [
    [
        'icon' => 'heart',
        'title' => 'Free Medical Consultation',
        'station' => 'Mandalagan Barangay Health Station',
        'barangay' => 'mandalagan',
        'description' => 'General health checkup and consultation with licensed physicians.',
        'date' => 'March 15, 2026',
        'time' => '8:00 AM - 12:00 PM',
        'accent' => 'mint',
    ],
    [
        'icon' => 'syringe',
        'title' => 'Vaccination Drive - Measles',
        'station' => 'Bata Barangay Health Station',
        'barangay' => 'bata',
        'description' => 'Free measles vaccination for children aged 6 months to 5 years.',
        'date' => 'March 18, 2026',
        'time' => '9:00 AM - 3:00 PM',
        'accent' => 'blue',
    ],
    [
        'icon' => 'community',
        'title' => 'Feeding Program',
        'station' => 'Taculing Barangay Health Station',
        'barangay' => 'taculing',
        'description' => 'Nutritious meals and vitamin supplements for malnourished children.',
        'date' => 'March 20, 2026',
        'time' => '10:00 AM - 2:00 PM',
        'accent' => 'gold',
    ],
    [
        'icon' => 'baby',
        'title' => 'Prenatal Care Seminar',
        'station' => 'Singcang Barangay Health Station',
        'barangay' => 'singcang',
        'description' => 'Educational session for expectant mothers with free checkup.',
        'date' => 'March 22, 2026',
        'time' => '1:00 PM - 4:00 PM',
        'accent' => 'pink',
    ],
];

$serviceCatalog = [
    'immunization' => ['slug' => 'immunization', 'icon' => 'syringe', 'title' => 'Immunization Program', 'description' => 'Vaccination for infants and children', 'duration' => '30-45 mins', 'color' => 'blue'],
    'prenatal' => ['slug' => 'prenatal', 'icon' => 'baby', 'title' => 'Prenatal Care', 'description' => 'Maternal health monitoring', 'duration' => '45-60 mins', 'color' => 'pink'],
    'family' => ['slug' => 'family', 'icon' => 'heart', 'title' => 'Family Planning', 'description' => 'Reproductive health services', 'duration' => '30 mins', 'color' => 'red'],
    'tb' => ['slug' => 'tb', 'icon' => 'pulse', 'title' => 'TB DOTS Program', 'description' => 'Tuberculosis treatment', 'duration' => '20 mins', 'color' => 'violet'],
    'consultation' => ['slug' => 'consultation', 'icon' => 'stethoscope', 'title' => 'General Consultation', 'description' => 'Primary healthcare', 'duration' => '30 mins', 'color' => 'mint'],
    'nutrition' => ['slug' => 'nutrition', 'icon' => 'community', 'title' => 'Nutrition Program', 'description' => 'Nutritional assessment', 'duration' => '30 mins', 'color' => 'gold'],
    'dental' => ['slug' => 'dental', 'icon' => 'cube', 'title' => 'Dental Services', 'description' => 'Oral health care', 'duration' => '45 mins', 'color' => 'cyan'],
    'pharmacy' => ['slug' => 'pharmacy', 'icon' => 'capsule', 'title' => 'Pharmacy Services', 'description' => 'Medicine dispensing', 'duration' => '15 mins', 'color' => 'indigo'],
    'checkup' => ['slug' => 'checkup', 'icon' => 'calendar', 'title' => 'Wellness Checkup', 'description' => 'Routine physical assessment', 'duration' => '25 mins', 'color' => 'mint'],
    'maternal' => ['slug' => 'maternal', 'icon' => 'baby', 'title' => 'Maternal Counseling', 'description' => 'Support for expectant mothers', 'duration' => '30 mins', 'color' => 'pink'],
    'pediatric' => ['slug' => 'pediatric', 'icon' => 'heart', 'title' => 'Pediatric Consultation', 'description' => 'Health visits for children', 'duration' => '30 mins', 'color' => 'red'],
    'senior' => ['slug' => 'senior', 'icon' => 'community', 'title' => 'Senior Citizen Care', 'description' => 'Monitoring and maintenance care', 'duration' => '20 mins', 'color' => 'gold'],
];

$stationPrograms = [
    'alijis' => ['consultation', 'family', 'nutrition', 'pharmacy', 'checkup', 'senior', 'dental'],
    'bata' => ['immunization', 'pediatric', 'consultation', 'family', 'nutrition', 'dental', 'pharmacy', 'checkup'],
    'cabug' => ['consultation', 'nutrition', 'family', 'tb', 'pharmacy', 'checkup'],
    'estefania' => ['consultation', 'immunization', 'prenatal', 'family', 'nutrition', 'dental', 'pharmacy', 'senior'],
    'granada' => ['consultation', 'immunization', 'family', 'nutrition', 'dental', 'pharmacy', 'checkup'],
    'handumanan' => ['consultation', 'tb', 'family', 'nutrition', 'pharmacy', 'checkup'],
    'mandalagan' => ['immunization', 'prenatal', 'family', 'tb', 'consultation', 'nutrition', 'dental', 'pharmacy'],
    'mansilingan' => ['consultation', 'immunization', 'prenatal', 'family', 'nutrition', 'dental', 'pharmacy', 'senior'],
    'pahanocoy' => ['consultation', 'family', 'nutrition', 'pharmacy', 'checkup'],
    'singcang' => ['prenatal', 'family', 'consultation', 'nutrition', 'dental', 'pharmacy', 'checkup'],
    'sum-ag' => ['consultation', 'immunization', 'family', 'nutrition', 'pharmacy', 'checkup'],
    'taculing' => ['consultation', 'immunization', 'family', 'nutrition', 'dental', 'pharmacy', 'senior'],
    'villamonte' => ['consultation', 'family', 'nutrition', 'dental', 'pharmacy', 'checkup', 'senior'],
    'villa-esperanza' => ['consultation', 'nutrition', 'family', 'pharmacy', 'checkup'],
    'vista-alegre' => ['consultation', 'immunization', 'family', 'nutrition', 'pharmacy', 'senior'],
];

$stations = [
    ['barangay' => 'Alijis', 'slug' => 'alijis', 'services' => 7, 'phone' => '(034) 123-4501', 'color' => 'rose', 'image' => 'https://images.unsplash.com/photo-1579684385127-1ef15d508118?auto=format&fit=crop&w=900&q=80'],
    ['barangay' => 'Bata', 'slug' => 'bata', 'services' => 8, 'phone' => '(034) 123-4502', 'color' => 'violet', 'image' => 'https://images.unsplash.com/photo-1586773860418-d37222d8fce3?auto=format&fit=crop&w=900&q=80'],
    ['barangay' => 'Cabug', 'slug' => 'cabug', 'services' => 6, 'phone' => '(034) 123-4503', 'color' => 'cyan', 'image' => 'https://images.unsplash.com/photo-1516549655169-df83a0774514?auto=format&fit=crop&w=900&q=80'],
    ['barangay' => 'Estefania', 'slug' => 'estefania', 'services' => 8, 'phone' => '(034) 123-4504', 'color' => 'gold', 'image' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=900&q=80'],
    ['barangay' => 'Granada', 'slug' => 'granada', 'services' => 7, 'phone' => '(034) 123-4505', 'color' => 'mint', 'image' => 'https://images.unsplash.com/photo-1512678080530-7760d81faba6?auto=format&fit=crop&w=900&q=80'],
    ['barangay' => 'Handumanan', 'slug' => 'handumanan', 'services' => 6, 'phone' => '(034) 123-4506', 'color' => 'blue', 'image' => 'https://images.unsplash.com/photo-1505751172876-fa1923c5c528?auto=format&fit=crop&w=900&q=80'],
    ['barangay' => 'Mandalagan', 'slug' => 'mandalagan', 'services' => 8, 'phone' => '(034) 123-4507', 'color' => 'mint', 'image' => 'https://images.unsplash.com/photo-1587351021759-3e566b3db4f1?auto=format&fit=crop&w=900&q=80'],
    ['barangay' => 'Mansilingan', 'slug' => 'mansilingan', 'services' => 8, 'phone' => '(034) 123-4508', 'color' => 'gold', 'image' => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=900&q=80'],
    ['barangay' => 'Pahanocoy', 'slug' => 'pahanocoy', 'services' => 5, 'phone' => '(034) 123-4509', 'color' => 'cyan', 'image' => 'https://images.unsplash.com/photo-1530026405186-ed1f139313f8?auto=format&fit=crop&w=900&q=80'],
    ['barangay' => 'Singcang', 'slug' => 'singcang', 'services' => 7, 'phone' => '(034) 123-4510', 'color' => 'rose', 'image' => 'https://images.unsplash.com/photo-1579154204601-01588f351e67?auto=format&fit=crop&w=900&q=80'],
    ['barangay' => 'Sum-Ag', 'slug' => 'sum-ag', 'services' => 6, 'phone' => '(034) 123-4511', 'color' => 'blue', 'image' => 'https://images.unsplash.com/photo-1516549655669-df83a0774514?auto=format&fit=crop&w=900&q=80'],
    ['barangay' => 'Taculing', 'slug' => 'taculing', 'services' => 7, 'phone' => '(034) 123-4512', 'color' => 'violet', 'image' => 'https://images.unsplash.com/photo-1584515933487-779824d29309?auto=format&fit=crop&w=900&q=80'],
    ['barangay' => 'Villamonte', 'slug' => 'villamonte', 'services' => 7, 'phone' => '(034) 123-4513', 'color' => 'rose', 'image' => 'https://images.unsplash.com/photo-1579684385127-1ef15d508118?auto=format&fit=crop&w=900&q=80'],
    ['barangay' => 'Villa Esperanza', 'slug' => 'villa-esperanza', 'services' => 5, 'phone' => '(034) 123-4514', 'color' => 'gold', 'image' => 'https://images.unsplash.com/photo-1504439468489-c8920d796a29?auto=format&fit=crop&w=900&q=80'],
    ['barangay' => 'Vista Alegre', 'slug' => 'vista-alegre', 'services' => 6, 'phone' => '(034) 123-4515', 'color' => 'mint', 'image' => 'https://images.unsplash.com/photo-1516574187841-cb9cc2ca948b?auto=format&fit=crop&w=900&q=80'],
];

foreach ($stations as $index => &$station) {
    $station['name'] = $station['barangay'] . ' Barangay Health Station';
    $station['location'] = 'Serving residents of Brgy. ' . $station['barangay'] . ', Bacolod City';
    $station['detail_location'] = 'Brgy. ' . $station['barangay'] . ', Bacolod City';
    $station['hours'] = 'Mon-Sat, 8AM-5PM';
    $station['full_hours'] = 'Monday - Saturday, 8:00 AM - 5:00 PM';
    $station['anchor'] = 'station-' . ($index + 1);
    $station['programs'] = array_map(static fn($key) => $serviceCatalog[$key], $stationPrograms[$station['slug']]);
}
unset($station);
