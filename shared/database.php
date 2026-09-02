<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/brevo_sms.php';
require_once __DIR__ . '/config.php';


/**
 * Central table registry.
 * Keeping table names here prevents scattered magic strings and makes the
 * database structure easier to maintain.
 */
const DB_TABLE_ADMIN_ACCOUNTS = 'admin_accounts';
const DB_TABLE_STAFF_ACCOUNTS = 'staff_accounts';
const DB_TABLE_PATIENT_ACCOUNTS = 'patient_accounts';
const DB_TABLE_PATIENT_PROFILES = 'patient_profiles';
const DB_TABLE_APPOINTMENTS = 'appointments';
const DB_TABLE_APPOINTMENT_NOTIFICATIONS = 'appointment_status_notifications';
const DB_TABLE_PATIENT_HISTORY = 'patient_info_history';
const DB_TABLE_PATIENT_NOTIFICATIONS = 'patient_update_notifications';
const DB_TABLE_STATION_OPEN_HOURS = 'station_open_hours';
const DB_TABLE_STATION_SERVICE_ASSIGNMENTS = 'station_service_assignments';
const DB_TABLE_UPCOMING_EVENTS = 'upcoming_events';
const DB_TABLE_ACTIVITY_LOG = 'activity_log';
const DB_TABLE_UNATTENDED_APPOINTMENTS = 'unattended_appointments';
const DB_TABLE_UNATTENDED_QUEUE = 'unattended_queue';


function contact_details(): array
{
    return [
        'phone' => '(034) 123-4567',
        'address' => 'Bacolod City, Negros Occidental',
        'hours' => 'Monday - Saturday: 8:00 AM - 5:00 PM',
    ];
}

function service_catalog(): array
{
    return [
        'immunization' => ['slug' => 'immunization', 'icon' => 'syringe', 'title' => 'Immunization', 'description' => 'Vaccination for infants and children', 'duration' => '30-45 mins', 'color' => 'blue'],
        'prenatal' => ['slug' => 'prenatal', 'icon' => 'baby', 'title' => 'Pre-Natal Care', 'description' => 'Maternal health monitoring', 'duration' => '45-60 mins', 'color' => 'pink'],
        'family' => ['slug' => 'family', 'icon' => 'heart', 'title' => 'Family Planning', 'description' => 'Reproductive health services', 'duration' => '30 mins', 'color' => 'red'],
        'tb' => ['slug' => 'tb', 'icon' => 'pulse', 'title' => 'TB DOTS', 'description' => 'Tuberculosis treatment and follow up', 'duration' => '20 mins', 'color' => 'violet'],
        'consultation' => ['slug' => 'consultation', 'icon' => 'stethoscope', 'title' => 'General Consultation', 'description' => 'Primary healthcare', 'duration' => '30 mins', 'color' => 'mint'],
        'nutrition' => ['slug' => 'nutrition', 'icon' => 'community', 'title' => 'Nutrition Program', 'description' => 'Nutritional assessment', 'duration' => '30 mins', 'color' => 'gold'],
        'dental' => ['slug' => 'dental', 'icon' => 'cube', 'title' => 'Dental Services', 'description' => 'Oral health care', 'duration' => '45 mins', 'color' => 'cyan'],
        'pharmacy' => ['slug' => 'pharmacy', 'icon' => 'capsule', 'title' => 'Pharmacy Services', 'description' => 'Medicine dispensing', 'duration' => '15 mins', 'color' => 'indigo'],
        'checkup' => ['slug' => 'checkup', 'icon' => 'calendar', 'title' => 'Wellness Checkup', 'description' => 'Routine physical assessment', 'duration' => '25 mins', 'color' => 'mint'],
        'maternal' => ['slug' => 'maternal', 'icon' => 'baby', 'title' => 'Maternal Counseling', 'description' => 'Support for expectant mothers', 'duration' => '30 mins', 'color' => 'pink'],
        'pediatric' => ['slug' => 'pediatric', 'icon' => 'heart', 'title' => 'Pediatric Consultation', 'description' => 'Health visits for children', 'duration' => '30 mins', 'color' => 'red'],
        'senior' => ['slug' => 'senior', 'icon' => 'community', 'title' => 'Senior Citizen Care', 'description' => 'Monitoring and maintenance care', 'duration' => '20 mins', 'color' => 'gold'],
        'adolescent' => ['slug' => 'adolescent', 'icon' => 'user', 'title' => 'Adolescent Day', 'description' => 'Health services for adolescents', 'duration' => '30 mins', 'color' => 'blue'],
        'flu' => ['slug' => 'flu', 'icon' => 'syringe', 'title' => 'Flu Vaccination', 'description' => 'Influenza vaccination', 'duration' => '15 mins', 'color' => 'cyan'],
    ];
}

function station_program_map(): array
{
    return [
        'alijis' => ['consultation', 'family', 'nutrition', 'pharmacy', 'checkup', 'senior', 'dental'],
        'bata' => ['immunization', 'consultation', 'family', 'prenatal', 'tb', 'senior', 'dental'],
        'cabug' => ['prenatal', 'immunization', 'tb', 'family', 'consultation'],
        'city-health' => ['consultation', 'immunization', 'prenatal', 'family', 'nutrition', 'dental', 'pharmacy', 'checkup', 'maternal', 'pediatric', 'senior'],
        'estefania' => ['consultation', 'immunization', 'prenatal', 'family', 'nutrition', 'dental', 'pharmacy', 'senior'],
        'granada' => ['consultation', 'immunization', 'family', 'nutrition', 'dental', 'pharmacy', 'checkup'],
        'handumanan' => ['consultation', 'tb', 'family', 'nutrition', 'pharmacy', 'checkup'],
        'mandalagan' => ['immunization', 'prenatal', 'family', 'tb', 'consultation', 'adolescent', 'flu'],
        'mansilingan' => ['consultation', 'immunization', 'prenatal', 'family', 'nutrition', 'dental', 'pharmacy', 'senior'],
        'pahanocoy' => ['consultation', 'family', 'nutrition', 'pharmacy', 'checkup'],
        'singcang' => ['prenatal', 'family', 'consultation', 'nutrition', 'dental', 'pharmacy', 'checkup'],
        'sum-ag' => ['consultation', 'immunization', 'family', 'nutrition', 'pharmacy', 'checkup'],
        'taculing' => ['consultation', 'immunization', 'family', 'nutrition', 'dental', 'pharmacy', 'senior'],
        'villamonte' => ['consultation', 'family', 'nutrition', 'dental', 'pharmacy', 'checkup', 'senior'],
        'villa-esperanza' => ['consultation', 'nutrition', 'family', 'pharmacy', 'checkup'],
        'vista-alegre' => ['consultation', 'immunization', 'family', 'nutrition', 'pharmacy', 'senior'],
    ];
}

function bacolod_purok_catalog(): array
{
    return [
        'Alijis' => ['Accco Housing','Bayanihan','Celita Village','Dc 1 (Phases 1)','Dc 1 (Phases 2)','Dc 1 (Phases 3)','Dc 2 Rphs','Dc 3 Rphs','Ecc Villas','Gaisano','Himaya','Katilingban','Lote','Maanyag','Maanyag 1','Mahigugma-on','Mainuswagon','Malapitan','Malipayon','Masagana','Mildred Homes','Mt. Carmel Subdivision, Nature\'s','Olympia Village','Pag-asa, Paghida-et','Paghigugma, Progresso','Puentebella','Roadside 1','Roadside 2','Sambag Dubai','Sambag Tinago','San Jose','Score - Paghab','St. Vincent Homes','Torrecampo','Villa Baradas','Daalco Subdivision','Guadalupe Subdivision'],
        'Bata' => ['Sunriser','Maaliwanay','Kametal','Pepsi','Riverside','Masinadyahon','Mahimaya-on','Marapara I','Marapara II','Bayabasan','Tunay','Pag-isa','Sawmill I','Sawmill II','Sawmill III','Andan','Villagracia','Pinetree','Kamunsil','Katilingban','Mainuswagon','Magbinuligay','Sto. Rosario'],
        'Cabug' => ['Bougainvilla','Busay','Golden Rosary','Gumamela','Ipil-Ipil','Kabugwason','Kalayogan','Katipunan','Kawayanan','Lechonan','Lemon Grass','Linya','Madinalag-on','Mainuswagon','Malipayon','Monico Ville','Prosperville','Ilaya','Relota Ville','Rosal','Rosas Pandan','Rose','Santan','Torrecampo','Villa Guillena'],
        'Estefania' => ['Arabay 1','Arabay 2','Arao','Bagong Silang','Bethany Court','Buena Royale','Camelot Residences','Camelot Village','Camingawan Proper','Capitol Hills Subdivision','Celine Homes Subdivision','City Ville Subdivision','Country Homes Subdivision Phase 1','Country Homes Subdivision Phase 2','Country Homes Subdivision Phase 3','East Homes 1','East Homes 2','East Homes 3','Elsa','Escuerdo','Estefania Proper','Flora','Fortune Towne -B','Fortune Towne Subdivision','Glenwood Residences','Goldah','Greensville 1 Subdivision','Greensville 4 Subdivision','Jesusa Heights Subdivision','Kaburihan','Kasoy','La Herencia','Lopues Village','Luisville Subdivision','Mayang','Meadows Of Camelot','Pag-asa','Paho 1','Paho 2','Paraiso','Pedring','Pequiño','Providence Negros','Sagrado 2','Sambag','Sunshine Valley Subdivision','The Palisades','Villa Alexandra 1','Villa Alexandra 2','Villa Angeles','Villa Estefania','Villa Felicidad','Villamar','Villa Soledad','Villa Villeta'],
        'Granada' => ['Alunan','Carmen','Gargato','Hilado','Hermelinda','Maravilla','Purok 1','Purok 2','Purok 3','Purok 4','Purok 5','Purok 6','Progreso','San Antonio','San Jose','San Miguel','Sta. Clara','Sto. Rosario','Villa Ramos'],
        'Handumanan' => ['Purok (Zone) 1','Purok (Zone) 2','Purok (Zone) 3','Purok (Zone) 4','Purok (Zone) 5','Purok (Zone) 6','Purok (Zone) 7','Purok (Zone) 8','Purok (Zone) 9','Purok (Zone) 10','Purok (Zone) 11','Purok (Zone) 12','Purok Cadena De Amor','Purok Ceres','Purok Chico','Purok Datiles','Purok Gk','Purok Golden Rosary','Purok Kawayanan 1','Purok Kawayanan 2','Purok Lubi','Purok Lucky Homes','Purok Mabinuligon','Purok Mahogany','Purok Maniville','Purok Narra','Purok Ngo Village','Purok Paghida-et','Purok Paho','Purok Rosebell','Purok San Antonio','Purok San Roque 1','Purok San Roque 2','Purok Saturn Village','Purok St. Ezekiel Moreno','Purok Sto. Domingo','Purok Sto. Nino','Purok Tapulanga Hills','Purok Villasor Village'],
        'Mandalagan' => ['Active','Bulak','Kaburihan','Luhod-Luhod','Sambag','Santol','Trese','Tuburan','Yanson 1','Yanson 2'],
        'Mansilingan' => ['Arceo','Cabalagnan','Carmenville','Encarnacion','Forest Hills','Fortaleza','Gonzaga','Grandville 1','Grandville 2','Grandville 3','Guanzon','Hermelinda','Hillside','Himaya','Jj Gonzaga','Kabugwason','Kahirup A','Kahirup B','Kasilingan 1','Kasilingan 2','Katilingban','Lasalleville','Laurel','Leonville','Lolita Heights','Lupa','Mabinuligon','Maghili-ugyon','Manayaosayao','Matahum','Paghidaet','Paglaum','Paglaum Village','Paraiso','Punay','St. Benilde','Unor Ville'],
        'Pahanocoy' => ['Acacia 1','Acacia 2','Bantud','Firmville','Florenceville','Gold Medal','Hanapbuhay','Mabinuligon','Maghimulat','Maghirupay','Mahigugmaon','Maninihon','Manville Executive Homes','Masinadyahon','Nha 1','Nha 2','Nha 3','Nha 4','Paho','Pta Balas North','Rc','Sp Village','Sta. Antonia','Sto. Niño','Villa Lourdes'],
        'Singcang' => ['Batad','Cadena De Amor','Villa Servando','Neptune','Kaingin','Mars','Sigay','Talaba','Sisi','Grasya','Kabulakan I','Greenplains','Lamperong','Magbinuligay','Ipil-Ipil','San Jose','Sampaguita','Mangga','Santol','Riverside','Tambi Palad','Malipayon','Masanag','Mahigugmaon','Mahinangpon','Mahayhay','Masagana','Katilingban','Paghida-et','Pag-asa','Narra','Molave','Mabolo','Acacia','Chico','Yanson'],
        'Sum-Ag' => ['Purok A.C. Yulo','Purok Brotherhood','Purok Candelaria','Purok Kaisahan','Purok Kbs','Purok Mabinuligon','Purok Masagana','Purok Naminami','Purok Providence','Purok Riverside','Purok San Antonio','Purok San Luis','Purok Sto. Niño','Purok Villa Milagrosa'],
        'Taculing' => ['Bayanihan','B.M.','Cinco','Cory I','Cosmos','Gonzaga','Jardine','Jocson','Kabukira','Kawilihan','Lirio','Malinong','Malipayon','Masagana','Nabali-an','Pagla-um','Paho','Planeta','Progreso','Rio Vista','Riverside','Rosal','Rosas','Santan','Sunflower','Sunrise','Tapulanga','Violeta'],
        'Villamonte' => ['Sabes','Cabachawan','Hervias III','Bayanihan','Goopio','Gonzaga West','Gonzaga East','Purok 7','Consuelo','Cubay','Bugnay','Hervias II','Calantas','Sulom II','Riverbank','Medalla Milagrosa','Pagkakaisa','Banaue','Isla','Gugma','Akishola','Herba Buena','Purok 17','Taal','Amor','Purok 18','Dahlia','Purok 2','Purok Himaya','Purok 5','Samfloma','Purok 15','Purok 16','Sunflower','Mainuwagon','Malvar Cubay'],
        'Villa Esperanza' => ['Purok 1', 'Purok 2', 'Purok 3', 'Purok 4', 'Purok 5'],
        'Vista Alegre' => ['Katilingban','Kawayanan','Kabulakan','Kabutongan','Busay','Inday Oya','Noli Garcia','Kabuguason','Angela Gonzaga','Ff Gonzaga','Kalubihan','Star Apple','Kasantolan 1','Kasantolan 2','Progreso Village I Zone 1','Progreso Village I Zone 2','Progreso Village I Zone 3','Progreso Village I Zone 4','Progreso Village I Zone 5','Progreso Village II','Kapisan','Villa Otto','Villa Nena','Pablo Torre','Lopez'],
    ];
}

function parse_complete_address(string $address, ?string $knownBarangay = null, ?string $knownPurok = null): array
{
    $barangayCatalog = [
        'Alijis', 'Bata', 'Cabug', 'Estefania', 'Granada',
        'Handumanan', 'Mandalagan', 'Mansilingan', 'Pahanocoy',
        'Singcang', 'Sum-Ag', 'Taculing', 'Villamonte',
        'Villa Esperanza', 'Vista Alegre'
    ];
    $purokMap = bacolod_purok_catalog();

    $cleanAddress = trim($address);
    if ($cleanAddress === '') {
        return [
            'barangay' => $knownBarangay ?? '',
            'purok' => $knownPurok ?? '',
            'street' => '',
            'complete_address' => ''
        ];
    }

    $detectedBarangay = trim((string) ($knownBarangay ?? ''));
    if ($detectedBarangay === '' || !in_array($detectedBarangay, $barangayCatalog, true)) {
        foreach ($barangayCatalog as $bgy) {
            if (preg_match('/(?:^|[,\s])(?:Brgy\.?|Barangay)?\s*' . preg_quote($bgy, '/') . '(?:\s+Barangay\s+Health\s+Station)?(?:[,\s]|$)/i', $cleanAddress)) {
                $detectedBarangay = $bgy;
                break;
            }
        }
    }

    $detectedPurok = trim((string) ($knownPurok ?? ''));
    $puroksForBarangay = $detectedBarangay !== '' ? ($purokMap[$detectedBarangay] ?? []) : [];
    if ($detectedPurok === '' && !empty($puroksForBarangay)) {
        foreach ($puroksForBarangay as $pOption) {
            if (stripos($cleanAddress, $pOption) !== false) {
                $detectedPurok = $pOption;
                break;
            }
        }
    }

    $rem = $cleanAddress;
    $rem = (string) preg_replace('/,?\s*Bacolod\s+City(?:,?\s*Negros\s+Occidental)?/i', '', $rem);
    if ($detectedBarangay !== '') {
        $rem = (string) preg_replace('/(?:^|[,\s]+)(?:Brgy\.?|Barangay)?\s*' . preg_quote($detectedBarangay, '/') . '(?:\s+Barangay\s+Health\s+Station)?/i', '', $rem);
    }
    $rem = (string) preg_replace('/\b(?:Brgy\.?|Barangay)\b/i', '', $rem);
    if ($detectedPurok !== '') {
        $rem = (string) preg_replace('/(?:^|[,\s]+)(?:Purok|Prk\.?|Zone)?\s*' . preg_quote($detectedPurok, '/') . '/i', '', $rem);
    }
    $rem = (string) preg_replace('/\b(?:Purok|Prk\.?|Zone)\b/i', '', $rem);
    $rem = trim($rem, ", .-#\t\n\r\0\x0B");

    if ($rem === '' || preg_match('/^(?:Brgy\.?|Barangay|Purok|Prk\.?|Zone|Bacolod|City|#|-)$/i', $rem)) {
        $rem = '';
    }

    return [
        'barangay' => $detectedBarangay,
        'purok' => $detectedPurok,
        'street' => $rem,
        'complete_address' => $cleanAddress
    ];
}

function station_service_schedule_map(): array
{
    return [
        'bata' => [
            'consultation' => [
                'label' => 'Every Monday and Friday Morning',
                'days' => [1 => ['Morning'], 5 => ['Morning']],
            ],
            'prenatal' => [
                'label' => 'Every Tuesday Morning and Thursday Afternoon',
                'days' => [2 => ['Morning'], 4 => ['Afternoon']],
            ],
            'immunization' => [
                'label' => 'Every Wednesday',
                'days' => [3 => ['Whole day']],
            ],
            'senior' => [
                'label' => 'Every Thursday Morning',
                'days' => [4 => ['Morning']],
            ],
            'dental' => [
                'label' => 'Every Friday Morning',
                'days' => [5 => ['Morning']],
            ],
            'family' => [
                'label' => 'Every Monday and Friday Afternoon',
                'days' => [1 => ['Afternoon'], 5 => ['Afternoon']],
            ],
            'tb' => [
                'label' => 'Every Tuesday Afternoon',
                'days' => [2 => ['Afternoon']],
            ],
        ],
        'mandalagan' => [
            'tb' => [
                'label' => 'Every Monday Morning and Tuesday Afternoon',
                'days' => [1 => ['Morning'], 2 => ['Afternoon']],
            ],
            'family' => [
                'label' => 'Every Afternoon',
                'days' => [1 => ['Afternoon'], 2 => ['Afternoon'], 3 => ['Afternoon'], 4 => ['Afternoon'], 5 => ['Afternoon'], 6 => ['Afternoon']],
            ],
            'prenatal' => [
                'label' => 'Every Tuesday Morning',
                'days' => [2 => ['Morning']],
            ],
            'immunization' => [
                'label' => 'Every Wednesday',
                'days' => [3 => ['Whole day']],
            ],
            'consultation' => [
                'label' => 'Every Thursday',
                'days' => [4 => ['Whole day']],
            ],
            'adolescent' => [
                'label' => 'Every Friday Morning',
                'days' => [5 => ['Morning']],
            ],
            'flu' => [
                'label' => 'Every Wednesday',
                'days' => [3 => ['Whole day']],
            ],
        ],
    ];
}

function station_service_schedule(string $stationSlug, string $serviceSlug): ?array
{
    $scheduleMap = station_service_schedule_map();

    return $scheduleMap[$stationSlug][$serviceSlug] ?? null;
}

function service_schedule_label(string $stationSlug, string $serviceSlug): string
{
    $schedule = station_service_schedule($stationSlug, $serviceSlug);

    if ($schedule === null) {
        return array_key_exists($stationSlug, station_service_schedule_map()) ? 'Schedule to be announced' : 'Monday - Friday';
    }

    return (string) $schedule['label'];
}

function service_is_scheduled_on_date(string $stationSlug, string $serviceSlug, DateTimeImmutable $date): bool
{
    $schedule = station_service_schedule($stationSlug, $serviceSlug);
    if ($schedule === null) {
        return !array_key_exists($stationSlug, station_service_schedule_map());
    }

    $weekday = (int) $date->format('N');
    $days = $schedule['days'] ?? [];

    return isset($days[$weekday]);
}

function station_seed_rows(): array
{
    return [
        ['barangay' => 'Alijis', 'slug' => 'alijis', 'services' => 7, 'phone' => '(034) 123-4501', 'color' => 'rose', 'image' => 'https://images.unsplash.com/photo-1579684385127-1ef15d508118?auto=format&fit=crop&w=900&q=80'],
        ['barangay' => 'Bata', 'slug' => 'bata', 'services' => 7, 'phone' => '(034) 123-4502', 'color' => 'violet', 'image' => 'https://images.unsplash.com/photo-1586773860418-d37222d8fce3?auto=format&fit=crop&w=900&q=80'],
        ['barangay' => 'Cabug', 'slug' => 'cabug', 'services' => 5, 'phone' => '(034) 123-4503', 'color' => 'cyan', 'image' => 'https://images.unsplash.com/photo-1516549655169-df83a0774514?auto=format&fit=crop&w=900&q=80'],
        ['barangay' => 'City Health', 'slug' => 'city-health', 'services' => 11, 'phone' => '(034) 123-4500', 'color' => 'blue', 'image' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=900&q=80'],
        ['barangay' => 'Estefania', 'slug' => 'estefania', 'services' => 8, 'phone' => '(034) 123-4504', 'color' => 'gold', 'image' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=900&q=80'],
        ['barangay' => 'Granada', 'slug' => 'granada', 'services' => 7, 'phone' => '(034) 123-4505', 'color' => 'mint', 'image' => 'https://images.unsplash.com/photo-1512678080530-7760d81faba6?auto=format&fit=crop&w=900&q=80'],
        ['barangay' => 'Handumanan', 'slug' => 'handumanan', 'services' => 6, 'phone' => '(034) 123-4506', 'color' => 'blue', 'image' => 'https://images.unsplash.com/photo-1505751172876-fa1923c5c528?auto=format&fit=crop&w=900&q=80'],
        ['barangay' => 'Mandalagan', 'slug' => 'mandalagan', 'services' => 7, 'phone' => '(034) 123-4507', 'color' => 'mint', 'image' => 'https://images.unsplash.com/photo-1587351021759-3e566b3db4f1?auto=format&fit=crop&w=900&q=80'],
        ['barangay' => 'Mansilingan', 'slug' => 'mansilingan', 'services' => 8, 'phone' => '(034) 123-4508', 'color' => 'gold', 'image' => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=900&q=80'],
        ['barangay' => 'Pahanocoy', 'slug' => 'pahanocoy', 'services' => 5, 'phone' => '(034) 123-4509', 'color' => 'cyan', 'image' => 'https://images.unsplash.com/photo-1530026405186-ed1f139313f8?auto=format&fit=crop&w=900&q=80'],
        ['barangay' => 'Singcang', 'slug' => 'singcang', 'services' => 7, 'phone' => '(034) 123-4510', 'color' => 'rose', 'image' => 'https://images.unsplash.com/photo-1579154204601-01588f351e67?auto=format&fit=crop&w=900&q=80'],
        ['barangay' => 'Sum-Ag', 'slug' => 'sum-ag', 'services' => 6, 'phone' => '(034) 123-4511', 'color' => 'blue', 'image' => 'https://images.unsplash.com/photo-1516549655669-df83a0774514?auto=format&fit=crop&w=900&q=80'],
        ['barangay' => 'Taculing', 'slug' => 'taculing', 'services' => 7, 'phone' => '(034) 123-4512', 'color' => 'violet', 'image' => 'https://images.unsplash.com/photo-1584515933487-779824d29309?auto=format&fit=crop&w=900&q=80'],
        ['barangay' => 'Villamonte', 'slug' => 'villamonte', 'services' => 7, 'phone' => '(034) 123-4513', 'color' => 'rose', 'image' => 'https://images.unsplash.com/photo-1579684385127-1ef15d508118?auto=format&fit=crop&w=900&q=80'],
        ['barangay' => 'Villa Esperanza', 'slug' => 'villa-esperanza', 'services' => 5, 'phone' => '(034) 123-4514', 'color' => 'gold', 'image' => 'https://images.unsplash.com/photo-1504439468489-c8920d796a29?auto=format&fit=crop&w=900&q=80'],
        ['barangay' => 'Vista Alegre', 'slug' => 'vista-alegre', 'services' => 6, 'phone' => '(034) 123-4515', 'color' => 'mint', 'image' => 'https://images.unsplash.com/photo-1516574187841-cb9cc2ca948b?auto=format&fit=crop&w=900&q=80'],
    ];
}

function fetch_all_station_definitions(): array
{
    $seed = station_seed_rows();
    $bySlug = [];
    foreach ($seed as $st) {
        $bySlug[$st['slug']] = $st;
    }

    if (empty($GLOBALS['health_db_bootstrapping'])) {
        try {
            $res = db()->query('SELECT * FROM health_facilities ORDER BY barangay ASC');
            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    $slug = (string) $row['slug'];
                    $bySlug[$slug] = [
                        'barangay' => (string) $row['barangay'],
                        'slug' => $slug,
                        'name' => (string) $row['name'],
                        'location' => (string) $row['location'],
                        'detail_location' => (string) $row['location'],
                        'phone' => (string) $row['phone'],
                        'color' => (string) ($row['color'] ?: 'mint'),
                        'image' => (string) ($row['image'] ?: 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=900&q=80'),
                        'hours' => (string) ($row['hours'] ?: 'Mon-Sat, 8AM-5PM'),
                        'full_hours' => (string) ($row['hours'] ?: 'Monday - Saturday, 8:00 AM - 5:00 PM'),
                        'services' => 0,
                    ];
                }
            }
        } catch (Throwable $e) {}
    }

    return array_values($bySlug);
}

function station_catalog(bool $useDatabaseAssignments = true): array
{
    $catalog = service_catalog();
    $programMap = $useDatabaseAssignments ? station_program_map_with_assignments() : station_program_map();
    $stationRows = [];

    $stationsList = fetch_all_station_definitions();
    foreach ($stationsList as $index => $station) {
        $barangayLabel = $station['barangay'];
        $isCityHealth = $station['slug'] === 'city-health';
        $station['name'] = $station['name'] ?? ($isCityHealth ? 'Bacolod City Health Office' : $barangayLabel . ' Barangay Health Station');
        $station['location'] = $station['location'] ?? ($isCityHealth
            ? 'Central services for residents of Bacolod City'
            : 'Serving residents of Brgy. ' . $barangayLabel . ', Bacolod City');
        $station['detail_location'] = $station['detail_location'] ?? ($isCityHealth
            ? 'Bacolod City Health Office, Bacolod City'
            : 'Brgy. ' . $barangayLabel . ', Bacolod City');
        $station['full_hours'] = $station['full_hours'] ?? 'Monday - Saturday, 8:00 AM - 5:00 PM';
        $station['hours'] = $station['hours'] ?? 'Mon-Sat, 8AM-5PM';
        $station['anchor'] = 'station-' . ($index + 1);
        $programSlugs = array_values(array_filter(
            $programMap[$station['slug']] ?? [],
            static fn(string $key): bool => isset($catalog[$key])
        ));
        $station['services'] = count($programSlugs);
        $station['programs'] = array_map(static fn(string $key): array => $catalog[$key], $programSlugs);
        $stationRows[] = $station;
    }

    return $stationRows;
}

function station_program_map_with_assignments(): array
{
    $programMap = station_program_map();

    if (!empty($GLOBALS['health_db_bootstrapping'])) {
        return $programMap;
    }

    try {
        $result = db()->query('SELECT station_slug, service_slug FROM station_service_assignments ORDER BY sort_order ASC, service_slug ASC');
    } catch (Throwable $exception) {
        return $programMap;
    }

    $assigned = [];
    while ($row = $result->fetch_assoc()) {
        $assigned[(string) $row['station_slug']][] = (string) $row['service_slug'];
    }

    return $assigned + $programMap;
}

function station_lookup(): array
{
    static $lookup = null;

    if (is_array($lookup)) {
        return $lookup;
    }

    $lookup = [];
    foreach (station_catalog(false) as $station) {
        $lookup[$station['slug']] = $station;
    }

    return $lookup;
}

function fetch_station_by_slug_catalog(string $slug): ?array
{
    $lookup = station_lookup();

    return $lookup[$slug] ?? null;
}

function default_upcoming_event_seed(): array
{
    return [
        [
            'station_slug' => 'city-health',
            'title' => 'Citywide Wellness Caravan',
            'description' => 'One-stop consultations, vital checks, and medicine counseling for walk-in residents.',
            'event_date' => date('Y-m-d', strtotime('+5 days')),
            'time_label' => '8:00 AM - 12:00 PM',
            'icon' => 'heart',
            'accent' => 'blue',
        ],
        [
            'station_slug' => 'bata',
            'title' => 'Child Immunization Day',
            'description' => 'Routine vaccines and growth monitoring for infants and young children.',
            'event_date' => date('Y-m-d', strtotime('+8 days')),
            'time_label' => '9:00 AM - 2:00 PM',
            'icon' => 'syringe',
            'accent' => 'blue',
        ],
        [
            'station_slug' => 'mandalagan',
            'title' => 'Prenatal Checkup Morning',
            'description' => 'Free prenatal consultations, blood pressure screening, and nutrition guidance.',
            'event_date' => date('Y-m-d', strtotime('+11 days')),
            'time_label' => '8:30 AM - 11:30 AM',
            'icon' => 'baby',
            'accent' => 'pink',
        ],
        [
            'station_slug' => 'taculing',
            'title' => 'Community Feeding Program',
            'description' => 'Healthy meals, nutrition assessment, and vitamins for children and seniors.',
            'event_date' => date('Y-m-d', strtotime('+13 days')),
            'time_label' => '10:00 AM - 1:00 PM',
            'icon' => 'community',
            'accent' => 'gold',
        ],
        [
            'station_slug' => 'singcang',
            'title' => 'Family Planning Forum',
            'description' => 'Barangay-based counseling and consultations on reproductive health services.',
            'event_date' => date('Y-m-d', strtotime('+16 days')),
            'time_label' => '1:00 PM - 4:00 PM',
            'icon' => 'heart',
            'accent' => 'mint',
        ],
    ];
}

function default_staff_password_hash(): string
{
    return '$2y$10$krX/duSATPKdF0LwH1mXR.nvExNtPNFZpjFsSLESyT4U/RRL9aoNO';
}

function default_admin_password_hash(): string
{
    return '$2y$10$ZYSFCaxq0ETDZAMlMRHW.eVQFoKAEAIguPEBayApiG29bSUmxRM4W';
}

function db_column_exists(mysqli $connection, string $table, string $column): bool
{
    $safeTable = $connection->real_escape_string($table);
    $safeColumn = $connection->real_escape_string($column);
    $databaseName = $connection->real_escape_string(DB_NAME);

    $result = $connection->query(
        "SELECT 1
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = '{$databaseName}'
           AND TABLE_NAME = '{$safeTable}'
           AND COLUMN_NAME = '{$safeColumn}'
         LIMIT 1"
    );

    return $result instanceof mysqli_result && $result->num_rows > 0;
}

function create_admin_accounts_table(mysqli $connection, string $engine = 'InnoDB'): void
{
    $connection->query(
        'CREATE TABLE IF NOT EXISTS admin_accounts (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            admin_name VARCHAR(150) NOT NULL,
            office_name VARCHAR(255) NOT NULL,
            email VARCHAR(150) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_admin_email (email)
        ) ENGINE=' . $engine . ' DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci'
    );
}

function create_staff_accounts_table(mysqli $connection, string $engine = 'InnoDB'): void
{
    $connection->query(
        'CREATE TABLE IF NOT EXISTS staff_accounts (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            station_slug VARCHAR(100) NOT NULL,
            station_name VARCHAR(255) NOT NULL,
            staff_name VARCHAR(150) NOT NULL,
            email VARCHAR(150) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            birth_date DATE DEFAULT NULL,
            gender VARCHAR(30) DEFAULT NULL,
            contact_number VARCHAR(30) DEFAULT NULL,
            home_address VARCHAR(255) DEFAULT NULL,
            emergency_contact VARCHAR(100) DEFAULT NULL,
            emergency_phone VARCHAR(30) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_station_slug (station_slug),
            INDEX idx_staff_email (email)
        ) ENGINE=' . $engine . ' DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci'
    );
}

function create_upcoming_events_table(mysqli $connection, string $engine = 'InnoDB'): void
{
    $connection->query(
        'CREATE TABLE IF NOT EXISTS upcoming_events (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            station_slug VARCHAR(100) NOT NULL,
            station_name VARCHAR(255) NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            event_date DATE NOT NULL,
            time_label VARCHAR(100) NOT NULL,
            end_time_label VARCHAR(100) DEFAULT NULL,
            icon VARCHAR(50) NOT NULL DEFAULT "calendar",
            accent VARCHAR(50) NOT NULL DEFAULT "mint",
            created_by VARCHAR(150) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_event_station (station_slug),
            INDEX idx_event_date (event_date)
        ) ENGINE=' . $engine . ' DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci'
    );
}

function create_appointments_table(mysqli $connection, string $engine = 'InnoDB'): void
{
    $connection->query(
        'CREATE TABLE IF NOT EXISTS appointments (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            reference_code VARCHAR(20) NOT NULL UNIQUE,
            appointment_code VARCHAR(10) DEFAULT NULL,
            patient_id VARCHAR(32) DEFAULT NULL,
            station_slug VARCHAR(100) NOT NULL,
            station_name VARCHAR(255) NOT NULL,
            service_slug VARCHAR(100) NOT NULL,
            service_name VARCHAR(255) NOT NULL,
            first_name VARCHAR(100) NOT NULL,
            middle_name VARCHAR(100) DEFAULT NULL,
            last_name VARCHAR(100) NOT NULL,
            birth_date DATE NOT NULL,
            gender VARCHAR(30) NOT NULL,
            contact_number VARCHAR(20) NOT NULL,
            email VARCHAR(150) DEFAULT NULL,
            complete_address VARCHAR(255) NOT NULL,
            immunization_relationship VARCHAR(100) DEFAULT NULL,
            preferred_date DATE NOT NULL,
            preferred_time VARCHAR(30) NOT NULL,
            notes TEXT DEFAULT NULL,
            body_temperature VARCHAR(30) DEFAULT NULL,
            pulse_rate VARCHAR(30) DEFAULT NULL,
            respiration_rate VARCHAR(30) DEFAULT NULL,
            blood_pressure VARCHAR(30) DEFAULT NULL,
            doctor_notes TEXT DEFAULT NULL,
            photo_path VARCHAR(255) DEFAULT NULL,
            status VARCHAR(30) NOT NULL DEFAULT "Pending",
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_station_slug (station_slug),
            INDEX idx_service_slug (service_slug),
            INDEX idx_status (status),
            INDEX idx_preferred_date (preferred_date)
        ) ENGINE=' . $engine . ' DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci'
    );
}

function create_patient_info_history_table(mysqli $connection, string $engine = 'InnoDB'): void
{
    $connection->query(
        'CREATE TABLE IF NOT EXISTS patient_info_history (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            patient_id VARCHAR(32) NOT NULL,
            field_name VARCHAR(50) NOT NULL,
            old_value TEXT,
            new_value TEXT,
            changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_patient_history (patient_id),
            INDEX idx_changed_at (changed_at)
        ) ENGINE=' . $engine . ' DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci'
    );
}

function create_patient_update_notifications_table(mysqli $connection, string $engine = 'InnoDB'): void
{
    $connection->query(
        'CREATE TABLE IF NOT EXISTS patient_update_notifications (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            patient_id VARCHAR(32) NOT NULL,
            patient_name VARCHAR(255) NOT NULL,
            field_updated VARCHAR(50) NOT NULL,
            is_read TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_patient_notif (patient_id),
            INDEX idx_is_read (is_read),
            INDEX idx_created_at (created_at)
        ) ENGINE=' . $engine . ' DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci'
    );
}

/** Create patient login/account records. */
function create_patient_accounts_table(mysqli $connection, string $engine = 'InnoDB'): void
{
    $connection->query(
        'CREATE TABLE IF NOT EXISTS ' . DB_TABLE_PATIENT_ACCOUNTS . ' (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            patient_id VARCHAR(32) NOT NULL UNIQUE,
            email VARCHAR(150) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            first_name VARCHAR(100) NOT NULL,
            middle_name VARCHAR(100) DEFAULT NULL,
            last_name VARCHAR(100) NOT NULL,
            birth_date DATE NOT NULL,
            gender VARCHAR(30) NOT NULL,
            contact_number VARCHAR(20) NOT NULL,
            complete_address VARCHAR(255) NOT NULL,
            station_slug VARCHAR(100) DEFAULT NULL,
            station_name VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_patient_email (email),
            INDEX idx_patient_name (last_name, first_name)
        ) ENGINE=' . $engine . ' DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci'
    );
}

/** Create patient-facing appointment status notifications. */
function create_appointment_status_notifications_table(mysqli $connection, string $engine = 'InnoDB'): void
{
    $connection->query(
        'CREATE TABLE IF NOT EXISTS ' . DB_TABLE_APPOINTMENT_NOTIFICATIONS . ' (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            appointment_id INT UNSIGNED NOT NULL,
            reference_code VARCHAR(32) NOT NULL,
            patient_id VARCHAR(32) NOT NULL,
            status VARCHAR(30) NOT NULL,
            message TEXT NOT NULL,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_notification_patient (patient_id),
            INDEX idx_notification_appointment (appointment_id),
            INDEX idx_notification_read (is_read)
        ) ENGINE=' . $engine . ' DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci'
    );
}

function create_patient_profiles_table(mysqli $connection, string $engine = 'InnoDB'): void
{
    $connection->query(
        'CREATE TABLE IF NOT EXISTS patient_profiles (
            patient_id VARCHAR(32) PRIMARY KEY,
            first_name VARCHAR(100) NOT NULL,
            middle_name VARCHAR(100) DEFAULT NULL,
            last_name VARCHAR(100) NOT NULL,
            birth_date DATE NOT NULL,
            gender VARCHAR(30) NOT NULL,
            contact_number VARCHAR(20) NOT NULL,
            email VARCHAR(150) DEFAULT NULL,
            complete_address VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_patient_name (last_name, first_name),
            INDEX idx_patient_contact (contact_number)
        ) ENGINE=' . $engine . ' DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci'
    );
}

function create_station_service_assignments_table(mysqli $connection, string $engine = 'InnoDB'): void
{
    $connection->query(
        'CREATE TABLE IF NOT EXISTS station_service_assignments (
            station_slug VARCHAR(100) NOT NULL,
            service_slug VARCHAR(100) NOT NULL,
            sort_order INT UNSIGNED NOT NULL DEFAULT 0,
            daily_capacity INT UNSIGNED NOT NULL DEFAULT 200,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (station_slug, service_slug),
            INDEX idx_station_services (station_slug),
            INDEX idx_service_station (service_slug)
        ) ENGINE=' . $engine . ' DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci'
    );
}

function create_health_facilities_table(mysqli $connection, string $engine = 'InnoDB'): void
{
    $connection->query(
        'CREATE TABLE IF NOT EXISTS health_facilities (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            slug VARCHAR(100) NOT NULL,
            name VARCHAR(255) NOT NULL,
            barangay VARCHAR(100) NOT NULL,
            location VARCHAR(255) NOT NULL,
            phone VARCHAR(50) NOT NULL,
            color VARCHAR(30) NOT NULL DEFAULT "mint",
            image VARCHAR(255) DEFAULT NULL,
            hours VARCHAR(100) NOT NULL DEFAULT "Monday - Saturday, 8:00 AM - 5:00 PM",
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_facility_slug (slug),
            INDEX idx_facility_barangay (barangay)
        ) ENGINE=' . $engine . ' DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci'
    );
}

function create_station_slot_limits_table(mysqli $connection, string $engine = 'InnoDB'): void
{
    $connection->query(
        'CREATE TABLE IF NOT EXISTS station_slot_limits (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            station_slug VARCHAR(100) NOT NULL,
            service_slug VARCHAR(100) NOT NULL,
            max_slots INT UNSIGNED NOT NULL DEFAULT 200,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_station_service_slots (station_slug, service_slug),
            INDEX idx_station_slots (station_slug)
        ) ENGINE=' . $engine . ' DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci'
    );
}

function create_unattended_appointments_table(mysqli $connection, string $engine = 'InnoDB'): void
{
    $connection->query(
        'CREATE TABLE IF NOT EXISTS ' . DB_TABLE_UNATTENDED_APPOINTMENTS . ' (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            appointment_id INT UNSIGNED NOT NULL,
            reference_code VARCHAR(20) NOT NULL,
            appointment_code VARCHAR(10) DEFAULT NULL,
            patient_id VARCHAR(32) DEFAULT NULL,
            station_slug VARCHAR(100) NOT NULL,
            station_name VARCHAR(255) NOT NULL,
            service_slug VARCHAR(100) NOT NULL,
            service_name VARCHAR(255) NOT NULL,
            first_name VARCHAR(100) NOT NULL,
            middle_name VARCHAR(100) DEFAULT NULL,
            last_name VARCHAR(100) NOT NULL,
            birth_date DATE NOT NULL,
            gender VARCHAR(30) NOT NULL,
            contact_number VARCHAR(20) NOT NULL,
            email VARCHAR(150) DEFAULT NULL,
            complete_address VARCHAR(255) NOT NULL,
            preferred_date DATE NOT NULL,
            preferred_time VARCHAR(30) NOT NULL,
            notes TEXT DEFAULT NULL,
            original_status VARCHAR(30) NOT NULL DEFAULT "Pending",
            reason_unattended VARCHAR(255) NOT NULL DEFAULT "Staff unconfirmed prior to date",
            marked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_unattended_appt_id (appointment_id),
            INDEX idx_unattended_station (station_slug),
            INDEX idx_unattended_date (preferred_date),
            INDEX idx_unattended_patient (patient_id)
        ) ENGINE=' . $engine . ' DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci'
    );
}

function create_unattended_queue_table(mysqli $connection, string $engine = 'InnoDB'): void
{
    $connection->query(
        'CREATE TABLE IF NOT EXISTS ' . DB_TABLE_UNATTENDED_QUEUE . ' (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            appointment_id INT UNSIGNED NOT NULL,
            reference_code VARCHAR(20) NOT NULL,
            appointment_code VARCHAR(10) DEFAULT NULL,
            patient_id VARCHAR(32) DEFAULT NULL,
            station_slug VARCHAR(100) NOT NULL,
            station_name VARCHAR(255) NOT NULL,
            service_slug VARCHAR(100) NOT NULL,
            service_name VARCHAR(255) NOT NULL,
            first_name VARCHAR(100) NOT NULL,
            middle_name VARCHAR(100) DEFAULT NULL,
            last_name VARCHAR(100) NOT NULL,
            birth_date DATE NOT NULL,
            gender VARCHAR(30) NOT NULL,
            contact_number VARCHAR(20) NOT NULL,
            email VARCHAR(150) DEFAULT NULL,
            complete_address VARCHAR(255) NOT NULL,
            preferred_date DATE NOT NULL,
            preferred_time VARCHAR(30) NOT NULL,
            photo_path VARCHAR(255) DEFAULT NULL,
            notes TEXT DEFAULT NULL,
            original_status VARCHAR(30) NOT NULL DEFAULT "Confirmed",
            reason_unattended VARCHAR(255) NOT NULL DEFAULT "Patient did not show up / Left unserved in queue",
            marked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_unattended_q_id (appointment_id),
            INDEX idx_unattended_q_station (station_slug),
            INDEX idx_unattended_q_date (preferred_date),
            INDEX idx_unattended_q_patient (patient_id)
        ) ENGINE=' . $engine . ' DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci'
    );
}

function create_table_with_engine_fallback(mysqli $connection, string $tableName, callable $createTable): void
{
    try {
        $createTable($connection);
    } catch (mysqli_sql_exception $exception) {
        if (strpos($exception->getMessage(), 'already exists') !== false) {
            return;
        }
        if (strpos($exception->getMessage(), 'Tablespace for table') === false) {
            throw $exception;
        }
        $createTable($connection, 'MyISAM');
    }
}

function ensure_table_is_usable(mysqli $connection, string $tableName, callable $createTable): void
{
    create_table_with_engine_fallback($connection, $tableName, $createTable);
}

function ensure_admin_accounts_table(mysqli $connection): void
{
    ensure_table_is_usable($connection, 'admin_accounts', 'create_admin_accounts_table');
}

function ensure_staff_accounts_table(mysqli $connection): void
{
    ensure_table_is_usable($connection, 'staff_accounts', 'create_staff_accounts_table');
}

function ensure_upcoming_events_table(mysqli $connection): void
{
    ensure_table_is_usable($connection, 'upcoming_events', 'create_upcoming_events_table');
}

function ensure_appointments_table(mysqli $connection): void
{
    ensure_table_is_usable($connection, 'appointments', 'create_appointments_table');
}

function ensure_patient_info_history_table(mysqli $connection): void
{
    ensure_table_is_usable($connection, 'patient_info_history', 'create_patient_info_history_table');
}

function ensure_patient_update_notifications_table(mysqli $connection): void
{
    ensure_table_is_usable($connection, 'patient_update_notifications', 'create_patient_update_notifications_table');
}

function ensure_patient_accounts_table(mysqli $connection): void
{
    ensure_table_is_usable($connection, DB_TABLE_PATIENT_ACCOUNTS, 'create_patient_accounts_table');
}

function ensure_appointment_status_notifications_table(mysqli $connection): void
{
    ensure_table_is_usable($connection, DB_TABLE_APPOINTMENT_NOTIFICATIONS, 'create_appointment_status_notifications_table');
}

function ensure_patient_profiles_table(mysqli $connection): void
{
    ensure_table_is_usable($connection, 'patient_profiles', 'create_patient_profiles_table');
}

function ensure_station_service_assignments_table(mysqli $connection): void
{
    ensure_table_is_usable($connection, 'station_service_assignments', 'create_station_service_assignments_table');
}

function ensure_health_facilities_table(mysqli $connection): void
{
    ensure_table_is_usable($connection, 'health_facilities', 'create_health_facilities_table');
}

function ensure_station_slot_limits_table(mysqli $connection): void
{
    ensure_table_is_usable($connection, 'station_slot_limits', 'create_station_slot_limits_table');
}

function ensure_unattended_appointments_table(mysqli $connection): void
{
    ensure_table_is_usable($connection, DB_TABLE_UNATTENDED_APPOINTMENTS, 'create_unattended_appointments_table');
}

function ensure_unattended_queue_table(mysqli $connection): void
{
    ensure_table_is_usable($connection, DB_TABLE_UNATTENDED_QUEUE, 'create_unattended_queue_table');
}

function run_database_migrations(mysqli $connection, bool $verbose = false): array
{
    $GLOBALS['health_db_bootstrapping'] = true;
    $log = [];

    // Ensure all tables
    ensure_appointments_table($connection);
    ensure_appointment_status_notifications_table($connection);
    ensure_upcoming_events_table($connection);
    ensure_staff_accounts_table($connection);
    ensure_patient_accounts_table($connection);
    ensure_admin_accounts_table($connection);
    ensure_patient_info_history_table($connection);
    ensure_patient_update_notifications_table($connection);
    ensure_patient_profiles_table($connection);
    ensure_station_service_assignments_table($connection);
    ensure_health_facilities_table($connection);
    ensure_station_slot_limits_table($connection);
    ensure_unattended_appointments_table($connection);
    ensure_unattended_queue_table($connection);
    $log[] = 'Core tables verified';

    // Ensure columns
    if (!db_column_exists($connection, 'station_service_assignments', 'daily_capacity')) {
        $connection->query('ALTER TABLE station_service_assignments ADD COLUMN daily_capacity INT UNSIGNED NOT NULL DEFAULT 200 AFTER sort_order');
        $log[] = 'Added station_service_assignments.daily_capacity';
    }

    if (!db_column_exists($connection, 'appointments', 'middle_name')) {
        $connection->query('ALTER TABLE appointments ADD COLUMN middle_name VARCHAR(100) DEFAULT NULL AFTER first_name');
        $log[] = 'Added appointments.middle_name';
    }

    if (!db_column_exists($connection, 'appointments', 'appointment_code')) {
        $connection->query('ALTER TABLE appointments ADD COLUMN appointment_code VARCHAR(10) DEFAULT NULL AFTER reference_code');
        $log[] = 'Added appointments.appointment_code';
    }

    if (!db_column_exists($connection, 'appointments', 'body_temperature')) {
        $connection->query('ALTER TABLE appointments ADD COLUMN body_temperature VARCHAR(30) DEFAULT NULL AFTER notes');
        $log[] = 'Added appointments.body_temperature';
    }

    if (!db_column_exists($connection, 'appointments', 'pulse_rate')) {
        $connection->query('ALTER TABLE appointments ADD COLUMN pulse_rate VARCHAR(30) DEFAULT NULL AFTER body_temperature');
        $log[] = 'Added appointments.pulse_rate';
    }

    if (!db_column_exists($connection, 'appointments', 'respiration_rate')) {
        $connection->query('ALTER TABLE appointments ADD COLUMN respiration_rate VARCHAR(30) DEFAULT NULL AFTER pulse_rate');
        $log[] = 'Added appointments.respiration_rate';
    }

    if (!db_column_exists($connection, 'appointments', 'blood_pressure')) {
        $connection->query('ALTER TABLE appointments ADD COLUMN blood_pressure VARCHAR(30) DEFAULT NULL AFTER respiration_rate');
        $log[] = 'Added appointments.blood_pressure';
    }

    if (!db_column_exists($connection, 'appointments', 'doctor_notes')) {
        $connection->query('ALTER TABLE appointments ADD COLUMN doctor_notes TEXT DEFAULT NULL AFTER blood_pressure');
        $log[] = 'Added appointments.doctor_notes';
    }

    if (!db_column_exists($connection, 'appointments', 'immunization_relationship')) {
        $connection->query('ALTER TABLE appointments ADD COLUMN immunization_relationship VARCHAR(100) DEFAULT NULL AFTER complete_address');
        $log[] = 'Added appointments.immunization_relationship';
    }

    if (!db_column_exists($connection, 'appointments', 'follow_up_date')) {
        $connection->query('ALTER TABLE appointments ADD COLUMN follow_up_date DATE DEFAULT NULL AFTER doctor_notes');
        $log[] = 'Added appointments.follow_up_date';
    }
    if (!db_column_exists($connection, 'appointments', 'follow_up_time')) {
        $connection->query('ALTER TABLE appointments ADD COLUMN follow_up_time VARCHAR(50) DEFAULT NULL AFTER follow_up_date');
        $log[] = 'Added appointments.follow_up_time';
    }
    if (!db_column_exists($connection, 'appointments', 'follow_up_notes')) {
        $connection->query('ALTER TABLE appointments ADD COLUMN follow_up_notes TEXT DEFAULT NULL AFTER follow_up_time');
        $log[] = 'Added appointments.follow_up_notes';
    }
    if (!db_column_exists($connection, 'appointments', 'follow_up_set_at')) {
        $connection->query('ALTER TABLE appointments ADD COLUMN follow_up_set_at TIMESTAMP NULL DEFAULT NULL AFTER follow_up_notes');
        $log[] = 'Added appointments.follow_up_set_at';
    }

    if (!db_column_exists($connection, 'upcoming_events', 'end_time_label')) {
        $connection->query('ALTER TABLE upcoming_events ADD COLUMN end_time_label VARCHAR(100) DEFAULT NULL AFTER time_label');
        $log[] = 'Added upcoming_events.end_time_label';
    }

    if (!db_column_exists($connection, 'staff_accounts', 'birth_date')) {
        $connection->query('ALTER TABLE staff_accounts ADD COLUMN birth_date DATE DEFAULT NULL AFTER password_hash');
        $log[] = 'Added staff_accounts.birth_date';
    }
    if (!db_column_exists($connection, 'staff_accounts', 'gender')) {
        $connection->query('ALTER TABLE staff_accounts ADD COLUMN gender VARCHAR(30) DEFAULT NULL AFTER birth_date');
        $log[] = 'Added staff_accounts.gender';
    }
    if (!db_column_exists($connection, 'staff_accounts', 'contact_number')) {
        $connection->query('ALTER TABLE staff_accounts ADD COLUMN contact_number VARCHAR(30) DEFAULT NULL AFTER gender');
        $log[] = 'Added staff_accounts.contact_number';
    }
    if (!db_column_exists($connection, 'staff_accounts', 'home_address')) {
        $connection->query('ALTER TABLE staff_accounts ADD COLUMN home_address VARCHAR(255) DEFAULT NULL AFTER contact_number');
        $log[] = 'Added staff_accounts.home_address';
    }
    if (!db_column_exists($connection, 'staff_accounts', 'emergency_contact')) {
        $connection->query('ALTER TABLE staff_accounts ADD COLUMN emergency_contact VARCHAR(100) DEFAULT NULL AFTER home_address');
        $log[] = 'Added staff_accounts.emergency_contact';
    }
    if (!db_column_exists($connection, 'staff_accounts', 'emergency_phone')) {
        $connection->query('ALTER TABLE staff_accounts ADD COLUMN emergency_phone VARCHAR(30) DEFAULT NULL AFTER emergency_contact');
        $log[] = 'Added staff_accounts.emergency_phone';
    }

    try {
        $idxRes = $connection->query("SHOW INDEX FROM staff_accounts WHERE Key_name = 'station_slug'");
        if ($idxRes && $idxRes->num_rows > 0) {
            $connection->query("ALTER TABLE staff_accounts DROP INDEX station_slug");
        }
    } catch (Throwable $e) {}

    // Performance Indexes
    $indexes = [
        ['appointments', 'idx_appt_date_status', 'preferred_date, status'],
        ['appointments', 'idx_appt_station_date', 'station_slug, preferred_date'],
        ['appointments', 'idx_appt_patient', 'patient_id'],
        ['patient_accounts', 'idx_pat_email', 'email'],
        ['patient_accounts', 'idx_pat_id', 'patient_id'],
    ];
    foreach ($indexes as [$tbl, $idxName, $cols]) {
        try {
            $chk = $connection->query("SHOW INDEX FROM `{$tbl}` WHERE Key_name = '{$idxName}'");
            if ($chk && $chk->num_rows === 0) {
                $connection->query("ALTER TABLE `{$tbl}` ADD INDEX `{$idxName}` ({$cols})");
                $log[] = "Added index {$idxName} on {$tbl}";
            }
        } catch (Throwable $e) {}
    }

    seed_admin_accounts($connection);
    seed_staff_accounts($connection);
    seed_patient_accounts($connection);
    seed_upcoming_events($connection);
    seed_station_service_assignments($connection);
    backfill_appointment_identity_fields();
    backfill_patient_profiles($connection);
    $GLOBALS['health_db_bootstrapping'] = false;
    $log[] = 'Seeding and backfills completed';

    return $log;
}

function db(): mysqli
{
    static $connection = null;

    if ($connection instanceof mysqli) {
        return $connection;
    }

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $configsToTry = [
        [
            'host' => DB_HOST,
            'user' => DB_USER,
            'pass' => DB_PASS,
            'name' => DB_NAME,
            'port' => DB_PORT
        ]
    ];

    if (DB_HOST === '127.0.0.1') {
        $configsToTry[] = [
            'host' => 'localhost',
            'user' => DB_USER,
            'pass' => DB_PASS,
            'name' => DB_NAME,
            'port' => DB_PORT
        ];
    } elseif (DB_HOST === 'localhost') {
        $configsToTry[] = [
            'host' => '127.0.0.1',
            'user' => DB_USER,
            'pass' => DB_PASS,
            'name' => DB_NAME,
            'port' => DB_PORT
        ];
    }

    // Local XAMPP/development fallback if production user fails on localhost
    if (in_array(DB_HOST, ['127.0.0.1', 'localhost', '::1'], true) && DB_USER !== 'root') {
        $configsToTry[] = [
            'host' => '127.0.0.1',
            'user' => 'root',
            'pass' => '',
            'name' => DB_NAME,
            'port' => 3306
        ];
        $configsToTry[] = [
            'host' => '127.0.0.1',
            'user' => 'root',
            'pass' => '',
            'name' => 'health_delivery_system',
            'port' => 3306
        ];
    }

    $lastException = null;

    foreach ($configsToTry as $cfg) {
        try {
            $connection = new mysqli($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['name'], $cfg['port']);
            $connection->set_charset('utf8mb4');
            $lastException = null;
            break;
        } catch (mysqli_sql_exception $exception) {
            $lastException = $exception;

            // Unknown database (code 1049) -> Auto-create database & bootstrap
            if ($exception->getCode() === 1049) {
                try {
                    $bootstrap = new mysqli($cfg['host'], $cfg['user'], $cfg['pass'], '', $cfg['port']);
                    $bootstrap->set_charset('utf8mb4');
                    $bootstrap->query('CREATE DATABASE IF NOT EXISTS `' . $cfg['name'] . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
                    $bootstrap->close();

                    $connection = new mysqli($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['name'], $cfg['port']);
                    $connection->set_charset('utf8mb4');
                    run_database_migrations($connection, false);
                    $lastException = null;
                    break;
                } catch (Throwable $bootError) {
                    error_log('Database bootstrap failure on ' . $cfg['host'] . ': ' . $bootError->getMessage());
                    $lastException = $bootError;
                }
            }
        }
    }

    if (!($connection instanceof mysqli)) {
        $errMsg = $lastException !== null ? $lastException->getMessage() : 'Unknown database connection error';
        error_log('Database connection error: ' . $errMsg);
        http_response_code(500);

        $hostSafe = htmlspecialchars(DB_HOST, ENT_QUOTES, 'UTF-8');
        $nameSafe = htmlspecialchars(DB_NAME, ENT_QUOTES, 'UTF-8');
        $userSafe = htmlspecialchars(DB_USER, ENT_QUOTES, 'UTF-8');
        $errSafe = htmlspecialchars($errMsg, ENT_QUOTES, 'UTF-8');

        echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Configuration Notice - Health Delivery System</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: \'Outfit\', sans-serif; background: #f8fafc; color: #0f172a; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 24px; }
        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 20px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); max-width: 580px; width: 100%; padding: 40px 32px; text-align: center; }
        .badge { display: inline-block; background: #fef3c7; color: #b45309; font-weight: 700; font-size: 0.85rem; padding: 6px 14px; border-radius: 9999px; text-transform: uppercase; margin-bottom: 16px; letter-spacing: 0.05em; }
        h1 { font-size: 1.5rem; font-weight: 800; margin-bottom: 12px; }
        p { color: #64748b; font-size: 0.95rem; line-height: 1.6; margin-bottom: 20px; text-align: left; }
        .details-box { background: #f1f5f9; border-radius: 12px; padding: 16px; margin-bottom: 24px; text-align: left; font-size: 0.85rem; font-family: monospace; }
        .details-box div { margin-bottom: 6px; word-break: break-all; }
        .btn { display: inline-block; background: #0284c7; color: #fff; font-weight: 600; padding: 12px 24px; border-radius: 12px; text-decoration: none; transition: background 0.2s; }
        .btn:hover { background: #0369a1; }
    </style>
</head>
<body>
    <div class="card">
        <span class="badge">Database Configuration Required</span>
        <h1>Database Connection Failed</h1>
        <p>The health delivery system is unable to connect to the MySQL database with the current configuration.</p>
        <div class="details-box">
            <div><strong>Host:</strong> ' . $hostSafe . ':' . DB_PORT . '</div>
            <div><strong>Database:</strong> ' . $nameSafe . '</div>
            <div><strong>User:</strong> ' . $userSafe . '</div>
            <div><strong>Message:</strong> ' . $errSafe . '</div>
        </div>
        <p><strong>Deployment Setup Checklist:</strong><br>
        1. Verify that your MySQL server is running.<br>
        2. Create a <code>.env</code> file in the project root with your database credentials (or configure <code>DB_HOST</code>, <code>DB_USER</code>, <code>DB_PASS</code>, <code>DB_NAME</code> in your hosting control panel).<br>
        3. Run <code>php shared/migrate.php</code> in your server terminal.</p>
        <a href="javascript:location.reload()" class="btn">Retry Connection</a>
    </div>
</body>
</html>';
        exit;
    }

    // Auto-check if core tables exist. If missing, auto-migrate seamlessly on first run!
    try {
        $check = $connection->query("SHOW TABLES LIKE 'station_service_assignments'");
        if (!$check || $check->num_rows === 0) {
            run_database_migrations($connection, false);
        }
    } catch (Throwable $e) {
        try {
            run_database_migrations($connection, false);
        } catch (Throwable $ignored) {}
    }

    return $connection;
}


function seed_patient_accounts(mysqli $connection): void
{
    $count = (int) ($connection->query('SELECT COUNT(*) AS total FROM ' . DB_TABLE_PATIENT_ACCOUNTS)->fetch_assoc()['total'] ?? 0);
    if ($count > 0) {
        return;
    }

    $passHash = password_hash('patient123', PASSWORD_DEFAULT);

    $stmt = $connection->prepare(
        'INSERT INTO ' . DB_TABLE_PATIENT_ACCOUNTS . ' 
         (patient_id, email, password_hash, first_name, middle_name, last_name, birth_date, gender, contact_number, complete_address, station_slug, station_name) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE first_name = VALUES(first_name)'
    );

    $defaultPatients = [
        [
            'patient_id' => 'P2YLL5',
            'email' => 'leozcrs17@gmail.com',
            'first_name' => 'Leo',
            'middle_name' => 'G',
            'last_name' => 'Zacarias',
            'birth_date' => '1998-05-17',
            'gender' => 'Male',
            'contact_number' => '09123456789',
            'complete_address' => 'Purok Sunriser, Brgy. Bata, Bacolod City',
            'station_slug' => 'bata',
            'station_name' => 'Bata Barangay Health Station',
        ],
        [
            'patient_id' => '3ACU9D',
            'email' => 'evelyn123@gmail.com',
            'first_name' => 'Evelyn',
            'middle_name' => '',
            'last_name' => 'Zacarias',
            'birth_date' => '1962-08-10',
            'gender' => 'Female',
            'contact_number' => '09281234567',
            'complete_address' => 'Purok Maaliwanay, Brgy. Bata, Bacolod City',
            'station_slug' => 'bata',
            'station_name' => 'Bata Barangay Health Station',
        ],
        [
            'patient_id' => 'HE6JH6',
            'email' => 'juan.delacruz@gmail.com',
            'first_name' => 'Juan',
            'middle_name' => 'M',
            'last_name' => 'Dela Cruz',
            'birth_date' => '1992-03-24',
            'gender' => 'Male',
            'contact_number' => '09191234567',
            'complete_address' => 'Purok Riverside, Brgy. Bata, Bacolod City',
            'station_slug' => 'bata',
            'station_name' => 'Bata Barangay Health Station',
        ],
    ];

    foreach ($defaultPatients as $p) {
        $stmt->bind_param(
            'ssssssssssss',
            $p['patient_id'],
            $p['email'],
            $passHash,
            $p['first_name'],
            $p['middle_name'],
            $p['last_name'],
            $p['birth_date'],
            $p['gender'],
            $p['contact_number'],
            $p['complete_address'],
            $p['station_slug'],
            $p['station_name']
        );
        $stmt->execute();
    }
}

function seed_admin_accounts(mysqli $connection): void
{
    $stmt = $connection->prepare(
        'INSERT IGNORE INTO admin_accounts (admin_name, office_name, email, password_hash)
         VALUES (?, ?, ?, ?)'
    );

    $adminName = 'Admin User';
    $officeName = 'Bacolod City Health Office';
    $email = 'admintest@gmail.com';
    $passwordHash = default_admin_password_hash();
    $stmt->bind_param('ssss', $adminName, $officeName, $email, $passwordHash);
    $stmt->execute();
}

function seed_staff_accounts(mysqli $connection): void
{
    $stmt = $connection->prepare(
        'INSERT INTO staff_accounts (station_slug, station_name, staff_name, email, password_hash)
         VALUES (?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE station_name = VALUES(station_name), staff_name = VALUES(staff_name), password_hash = VALUES(password_hash)'
    );

    $passwordHash = default_staff_password_hash();

    foreach (station_catalog(false) as $station) {
        $stationSlug = (string) $station['slug'];
        if ($stationSlug === 'city-health') {
            continue;
        }
        $stationName = (string) $station['name'];
        $staffName = (string) $station['barangay'] . ' Health Staff';
        $email = 'staff-' . $stationSlug . '@' . $stationSlug . '.health';
        $stmt->bind_param('sssss', $stationSlug, $stationName, $staffName, $email, $passwordHash);
        $stmt->execute();
    }
}

function seed_upcoming_events(mysqli $connection): void
{
    $count = (int) ($connection->query('SELECT COUNT(*) AS total FROM upcoming_events')->fetch_assoc()['total'] ?? 0);
    if ($count > 0) {
        return;
    }

    $stmt = $connection->prepare(
        'INSERT INTO upcoming_events (station_slug, station_name, title, description, event_date, time_label, icon, accent, created_by)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );

    foreach (default_upcoming_event_seed() as $event) {
        $station = fetch_station_by_slug_catalog((string) $event['station_slug']);
        if ($station === null) {
            continue;
        }

        $stationSlug = (string) $station['slug'];
        $stationName = (string) $station['name'];
        $title = (string) $event['title'];
        $description = (string) $event['description'];
        $eventDate = (string) $event['event_date'];
        $timeLabel = (string) $event['time_label'];
        $icon = (string) $event['icon'];
        $accent = (string) $event['accent'];
        $createdBy = 'system-seed';

        $stmt->bind_param('sssssssss', $stationSlug, $stationName, $title, $description, $eventDate, $timeLabel, $icon, $accent, $createdBy);
        $stmt->execute();
    }
}

function seed_station_service_assignments(mysqli $connection): void
{
    $count = (int) ($connection->query('SELECT COUNT(*) AS total FROM station_service_assignments')->fetch_assoc()['total'] ?? 0);
    if ($count > 0) {
        return;
    }

    $stmt = $connection->prepare(
        'INSERT IGNORE INTO station_service_assignments (station_slug, service_slug, sort_order)
         VALUES (?, ?, ?)'
    );

    foreach (station_program_map() as $stationSlug => $serviceSlugs) {
        foreach (array_values($serviceSlugs) as $index => $serviceSlug) {
            $sortOrder = $index + 1;
            $stmt->bind_param('ssi', $stationSlug, $serviceSlug, $sortOrder);
            $stmt->execute();
        }
    }
}

function backfill_patient_profiles(mysqli $connection): void
{
    $result = $connection->query(
        'SELECT a.*
         FROM appointments a
         INNER JOIN (
            SELECT patient_id, MAX(CONCAT(preferred_date, " ", preferred_time, " ", LPAD(id, 10, "0"))) AS latest_key
            FROM appointments
            WHERE patient_id IS NOT NULL AND patient_id <> "" AND status <> "Cancelled"
            GROUP BY patient_id
         ) latest
           ON latest.patient_id = a.patient_id
          AND latest.latest_key = CONCAT(a.preferred_date, " ", a.preferred_time, " ", LPAD(a.id, 10, "0"))
         WHERE NOT EXISTS (
            SELECT 1 FROM patient_profiles p WHERE p.patient_id = a.patient_id
         )'
    );

    $stmt = $connection->prepare(
        'INSERT IGNORE INTO patient_profiles (patient_id, first_name, middle_name, last_name, birth_date, gender, contact_number, email, complete_address)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );

    while ($row = $result->fetch_assoc()) {
        $patientId = (string) ($row['patient_id'] ?? '');
        $firstName = (string) ($row['first_name'] ?? '');
        $middleName = (string) ($row['middle_name'] ?? '');
        $lastName = (string) ($row['last_name'] ?? '');
        $birthDate = (string) ($currentProfile['birth_date'] ?? $row['birth_date'] ?? '');
        $gender = (string) ($row['gender'] ?? '');
        $contactNumber = (string) ($row['contact_number'] ?? '');
        $email = (string) ($row['email'] ?? '');
        $completeAddress = (string) ($row['complete_address'] ?? '');
        $stmt->bind_param('sssssssss', $patientId, $firstName, $middleName, $lastName, $birthDate, $gender, $contactNumber, $email, $completeAddress);
        $stmt->execute();
    }
}

function create_reference_code(): string
{
    return 'BK' . date('ymdHis') . random_int(100, 999);
}

function random_alphanumeric_code(int $length): string
{
    $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $code = '';
    $max = strlen($alphabet) - 1;

    for ($i = 0; $i < $length; $i++) {
        $code .= $alphabet[random_int(0, $max)];
    }

    return $code;
}

function random_patient_id_code(int $length = 6): string
{
    do {
        $code = random_alphanumeric_code($length);
    } while (!preg_match('/[A-Z]/', $code) || !preg_match('/\d/', $code));

    return $code;
}

function find_existing_patient_id(array $patientData): ?string
{
    $firstName = trim((string) ($patientData['first_name'] ?? ''));
    $middleName = trim((string) ($patientData['middle_name'] ?? ''));
    $lastName = trim((string) ($patientData['last_name'] ?? ''));
    $birthDate = trim((string) ($patientData['birth_date'] ?? ''));

    if ($firstName === '' || $lastName === '' || $birthDate === '') {
        return null;
    }

    $stmt = db()->prepare(
        'SELECT patient_id
         FROM appointments
         WHERE patient_id IS NOT NULL
           AND patient_id <> ""
           AND LOWER(TRIM(first_name)) = LOWER(TRIM(?))
           AND LOWER(TRIM(COALESCE(middle_name, ""))) = LOWER(TRIM(?))
           AND LOWER(TRIM(last_name)) = LOWER(TRIM(?))
           AND birth_date = ?
         ORDER BY created_at ASC, id ASC
         LIMIT 1'
    );
    $stmt->bind_param('ssss', $firstName, $middleName, $lastName, $birthDate);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $patientId = strtoupper(trim((string) ($row['patient_id'] ?? '')));

    return $patientId !== '' ? $patientId : null;
}

function appointment_patient_record_key(array $patientData): string
{
    $existingPatientId = find_existing_patient_id($patientData);
    if ($existingPatientId !== null) {
        return $existingPatientId;
    }

    do {
        $patientId = random_patient_id_code(6);
        $stmt = db()->prepare('SELECT id FROM appointments WHERE patient_id = ? LIMIT 1');
        $stmt->bind_param('s', $patientId);
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;
    } while ($exists);

    return $patientId;
}

function create_appointment_code(string $stationSlug, string $serviceSlug, string $preferredDate): ?string
{
    $date = DateTimeImmutable::createFromFormat('Y-m-d', $preferredDate);
    if (!$date || $date->format('Y-m-d') !== $preferredDate || !service_is_scheduled_on_date($stationSlug, $serviceSlug, $date)) {
        return null;
    }

    $stmt = db()->prepare(
        'SELECT appointment_code, status
         FROM appointments
         WHERE station_slug = ?
           AND preferred_date = ?'
    );
    $stmt->bind_param('ss', $stationSlug, $preferredDate);
    $stmt->execute();

    $count = 0;
    foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $row) {
        if ((string) ($row['status'] ?? '') === 'Cancelled') {
            continue;
        }

        $count++;
    }

    if ($count >= 200) {
        return null;
    }

    do {
        $appointmentCode = random_alphanumeric_code(8);
        $lookup = db()->prepare('SELECT id FROM appointments WHERE appointment_code = ? LIMIT 1');
        $lookup->bind_param('s', $appointmentCode);
        $lookup->execute();
        $exists = $lookup->get_result()->num_rows > 0;
    } while ($exists);

    return $appointmentCode;
}

function backfill_appointment_identity_fields(): void
{
    $result = db()->query(
        'SELECT id, station_slug, service_slug, preferred_date, first_name, middle_name, last_name, birth_date, patient_id, appointment_code
         FROM appointments
         ORDER BY preferred_date ASC, created_at ASC, id ASC'
    );

    $usedCodes = [];
    $patientIdMigration = [];
    $updatePatient = db()->prepare('UPDATE appointments SET patient_id = ? WHERE id = ?');
    $updateAppointment = db()->prepare('UPDATE appointments SET appointment_code = ? WHERE id = ?');

    foreach ($result->fetch_all(MYSQLI_ASSOC) as $row) {
        $patientId = trim((string) ($row['patient_id'] ?? ''));
        if ($patientId === '' || preg_match('/^[A-Z]{3}\d{6}$/', $patientId) === 1) {
            $oldPatientId = $patientId;
            $patientId = $oldPatientId !== '' && isset($patientIdMigration[$oldPatientId])
                ? $patientIdMigration[$oldPatientId]
                : appointment_patient_record_key($row);
            if ($oldPatientId !== '') {
                $patientIdMigration[$oldPatientId] = $patientId;
            }
            $appointmentId = (int) $row['id'];
            $updatePatient->bind_param('si', $patientId, $appointmentId);
            $updatePatient->execute();
        }

        $appointmentCode = trim((string) ($row['appointment_code'] ?? ''));
        $scope = implode('|', [
            (string) ($row['station_slug'] ?? ''),
            (string) ($row['service_slug'] ?? ''),
            (string) ($row['preferred_date'] ?? ''),
        ]);
        $usedCodes[$scope] ??= [];

        if ($appointmentCode !== '' && preg_match('/^[A-Z0-9]{8}$/', $appointmentCode) === 1) {
            continue;
        }

        $appointmentCode = create_appointment_code((string) $row['station_slug'], (string) $row['service_slug'], (string) $row['preferred_date']);
        if ($appointmentCode === null) {
            continue;
        }

        $appointmentId = (int) $row['id'];
        $updateAppointment->bind_param('si', $appointmentCode, $appointmentId);
        $updateAppointment->execute();
    }
}

function save_or_fetch_patient_profile(array $patientData): array
{
    $firstName = trim((string) ($patientData['first_name'] ?? ''));
    $middleName = trim((string) ($patientData['middle_name'] ?? ''));
    $lastName = trim((string) ($patientData['last_name'] ?? ''));
    $birthDate = trim((string) ($patientData['birth_date'] ?? ''));
    $gender = trim((string) ($patientData['gender'] ?? ''));
    $contactNumber = trim((string) ($patientData['contact_number'] ?? ''));
    $email = trim((string) ($patientData['email'] ?? ''));
    $completeAddress = trim((string) ($patientData['complete_address'] ?? ''));

    return [
        'patient_id' => appointment_patient_record_key($patientData),
        'first_name' => $firstName,
        'middle_name' => $middleName,
        'last_name' => $lastName,
        'birth_date' => $birthDate,
        'gender' => $gender,
        'contact_number' => $contactNumber,
        'email' => $email,
        'complete_address' => $completeAddress,
        'photo_path' => '',
    ];
}

function upsert_patient_profile(array $patientData): string
{
    $patientId = strtoupper(trim((string) ($patientData['patient_id'] ?? $patientData['patient_id_number'] ?? '')));
    if ($patientId === '') {
        $patientId = appointment_patient_record_key($patientData);
    }

    $firstName = trim((string) ($patientData['first_name'] ?? ''));
    $middleName = trim((string) ($patientData['middle_name'] ?? ''));
    $lastName = trim((string) ($patientData['last_name'] ?? ''));
    $birthDate = trim((string) ($patientData['birth_date'] ?? ''));
    $gender = trim((string) ($patientData['gender'] ?? ''));
    $contactNumber = trim((string) ($patientData['contact_number'] ?? ''));
    $email = trim((string) ($patientData['email'] ?? ''));
    $completeAddress = trim((string) ($patientData['complete_address'] ?? ''));

    $stmt = db()->prepare(
        'INSERT INTO patient_profiles (patient_id, first_name, middle_name, last_name, birth_date, gender, contact_number, email, complete_address)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
            first_name = VALUES(first_name),
            middle_name = VALUES(middle_name),
            last_name = VALUES(last_name),
            birth_date = VALUES(birth_date),
            gender = VALUES(gender),
            contact_number = VALUES(contact_number),
            email = VALUES(email),
            complete_address = VALUES(complete_address)'
    );
    $stmt->bind_param('sssssssss', $patientId, $firstName, $middleName, $lastName, $birthDate, $gender, $contactNumber, $email, $completeAddress);
    $stmt->execute();

    return $patientId;
}

function fetch_patient_current_profile_row(string $patientId): ?array
{
    $patientId = strtoupper(trim($patientId));
    if ($patientId === '') {
        return null;
    }

    $stmt = db()->prepare('SELECT * FROM patient_profiles WHERE UPPER(patient_id) = ? LIMIT 1');
    $stmt->bind_param('s', $patientId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    return $row ?: null;
}

function update_patient_profile_info(string $patientId, array $data): bool
{
    $patientId = strtoupper(trim($patientId));
    $current = fetch_patient_current_profile_row($patientId);
    if ($current === null) {
        $current = fetch_patient_profile_by_patient_id($patientId);
    }
    if ($current === null) {
        return false;
    }

    $next = $current;
    foreach (['first_name', 'middle_name', 'last_name', 'birth_date', 'gender', 'contact_number', 'email', 'complete_address'] as $field) {
        if (array_key_exists($field, $data)) {
            $next[$field] = trim((string) $data[$field]);
        }
    }

    $required = ['first_name', 'last_name', 'birth_date', 'gender', 'contact_number', 'complete_address'];
    foreach ($required as $field) {
        if (trim((string) ($next[$field] ?? '')) === '') {
            return false;
        }
    }

    if (!preg_match('/^09\d{9}$/', (string) $next['contact_number'])) {
        return false;
    }

    $patientName = trim((string) $next['first_name'] . ' ' . (string) $next['middle_name'] . ' ' . (string) $next['last_name']);
    if ((string) ($current['complete_address'] ?? '') !== (string) $next['complete_address']) {
        track_patient_info_change($patientId, 'complete_address', (string) ($current['complete_address'] ?? ''), (string) $next['complete_address']);
        create_patient_update_notification($patientId, $patientName, 'Address');
    }
    if ((string) ($current['contact_number'] ?? '') !== (string) $next['contact_number']) {
        track_patient_info_change($patientId, 'contact_number', (string) ($current['contact_number'] ?? ''), (string) $next['contact_number']);
        create_patient_update_notification($patientId, $patientName, 'Contact Number');
    }

    $next['patient_id'] = $patientId;
    upsert_patient_profile($next);

    return true;
}

function store_patient_photo(string $capturedPhotoData): ?string
{
    if (!preg_match('/^data:image\/(png|jpeg|jpg|webp);base64,/i', $capturedPhotoData, $matches)) {
        return null;
    }

    $binary = base64_decode(substr($capturedPhotoData, strpos($capturedPhotoData, ',') + 1), true);
    if ($binary === false) {
        return null;
    }

    $extMatch = strtolower((string) $matches[1]);
    $extension = in_array($extMatch, ['png', 'jpg', 'jpeg', 'webp'], true) ? ($extMatch === 'jpeg' ? 'jpg' : $extMatch) : 'jpg';
    $uploadDir = dirname(__DIR__) . '/Patients/uploads';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
        return null;
    }

    $fileName = uniqid('patient_', true) . '.' . $extension;
    $destination = $uploadDir . '/' . $fileName;
    if (file_put_contents($destination, $binary) === false) {
        return null;
    }

    return 'uploads/' . $fileName;
}

function save_patient_photo_for_appointment(int $appointmentId, string $capturedPhotoData, ?string $stationScope = null): bool
{
    $appointment = fetch_appointment_by_id($appointmentId);
    if ($appointment === null) {
        return false;
    }

    if ($stationScope !== null && (string) $appointment['station_slug'] !== $stationScope) {
        return false;
    }

    $photoPath = store_patient_photo($capturedPhotoData);
    if ($photoPath === null) {
        return false;
    }

    $stmt = db()->prepare('UPDATE appointments SET photo_path = ? WHERE id = ?');
    $stmt->bind_param('si', $photoPath, $appointmentId);
    $saved = $stmt->execute();

    if ($saved && !empty($appointment['patient_id'])) {
        $pId = (string) $appointment['patient_id'];
        $syncStmt = db()->prepare('UPDATE appointments SET photo_path = ? WHERE patient_id = ? AND (photo_path IS NULL OR photo_path = "")');
        $syncStmt->bind_param('ss', $photoPath, $pId);
        $syncStmt->execute();
    }

    return $saved;
}

function save_patient_photo_for_patient_id(string $patientId, string $capturedPhotoData): bool
{
    $photoPath = store_patient_photo($capturedPhotoData);
    if ($photoPath === null) {
        return false;
    }

    $stmt = db()->prepare('UPDATE appointments SET photo_path = ? WHERE patient_id = ?');
    $stmt->bind_param('ss', $photoPath, $patientId);

    return $stmt->execute();
}

function appointment_time_slots(): array
{
    return [
        'Daily Slot',
    ];
}

function fetch_station_daily_capacity(string $stationSlug): int
{
    if (empty($GLOBALS['health_db_bootstrapping'])) {
        try {
            $stmt = db()->prepare('SELECT max_slots FROM station_slot_limits WHERE station_slug = ? AND (service_slug = "__ALL__" OR service_slug = "") LIMIT 1');
            $stmt->bind_param('s', $stationSlug);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            if ($row && isset($row['max_slots']) && (int) $row['max_slots'] > 0) {
                return (int) $row['max_slots'];
            }
        } catch (Throwable $e) {}

        try {
            $stmt = db()->prepare('SELECT max_slots FROM station_slot_limits WHERE station_slug = ? LIMIT 1');
            $stmt->bind_param('s', $stationSlug);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            if ($row && isset($row['max_slots']) && (int) $row['max_slots'] > 0) {
                return (int) $row['max_slots'];
            }
        } catch (Throwable $e) {}
    }

    return 200;
}

function update_station_daily_capacity(string $stationSlug, int $maxSlots): bool
{
    $maxSlots = max(1, min($maxSlots, 5000));
    try {
        $allKey = '__ALL__';
        $stmt = db()->prepare(
            'INSERT INTO station_slot_limits (station_slug, service_slug, max_slots) 
             VALUES (?, ?, ?) 
             ON DUPLICATE KEY UPDATE max_slots = VALUES(max_slots)'
        );
        $stmt->bind_param('ssi', $stationSlug, $allKey, $maxSlots);
        $stmt->execute();

        try {
            $stmt2 = db()->prepare('UPDATE station_service_assignments SET daily_capacity = ? WHERE station_slug = ?');
            $stmt2->bind_param('is', $maxSlots, $stationSlug);
            $stmt2->execute();
        } catch (Throwable $e) {}

        return true;
    } catch (Throwable $e) {
        return false;
    }
}

function fetch_station_service_capacity(string $stationSlug, string $serviceSlug = ''): int
{
    return fetch_station_daily_capacity($stationSlug);
}

function update_station_service_capacity(string $stationSlug, string $serviceSlug, int $maxSlots): bool
{
    return update_station_daily_capacity($stationSlug, $maxSlots);
}

function create_health_facility(array $data, array $servicesWithCapacities = []): bool
{
    $barangay = trim((string) ($data['barangay'] ?? ''));
    $name = trim((string) ($data['name'] ?? ''));
    $location = trim((string) ($data['location'] ?? ''));
    $phone = trim((string) ($data['phone'] ?? ''));
    $color = trim((string) ($data['color'] ?? 'mint'));
    $image = trim((string) ($data['image'] ?? ''));
    $hours = trim((string) ($data['hours'] ?? 'Monday - Saturday, 8:00 AM - 5:00 PM'));

    if ($barangay === '' || $name === '') {
        return false;
    }

    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $barangay));
    $slug = trim($slug, '-');

    if ($location === '') {
        $location = 'Serving residents of Brgy. ' . $barangay . ', Bacolod City';
    }
    if ($phone === '') {
        $phone = '(034) ' . random_int(100, 999) . '-' . random_int(1000, 9999);
    }
    if ($image === '') {
        $image = 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=900&q=80';
    }

    try {
        $stmt = db()->prepare(
            'INSERT INTO health_facilities (slug, name, barangay, location, phone, color, image, hours)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE 
                name = VALUES(name), 
                barangay = VALUES(barangay), 
                location = VALUES(location), 
                phone = VALUES(phone), 
                color = VALUES(color), 
                image = VALUES(image), 
                hours = VALUES(hours)'
        );
        $stmt->bind_param('ssssssss', $slug, $name, $barangay, $location, $phone, $color, $image, $hours);
        $stmt->execute();

        $stationDailyCapacity = isset($data['max_slots']) ? max(1, (int) $data['max_slots']) : 200;
        update_station_daily_capacity($slug, $stationDailyCapacity);

        if (!empty($servicesWithCapacities)) {
            $serviceSlugs = array_keys($servicesWithCapacities);
            save_station_service_selection($slug, $serviceSlugs);
        } else {
            $defaultServices = ['consultation', 'immunization', 'prenatal', 'family', 'tb', 'adolescent'];
            save_station_service_selection($slug, $defaultServices);
        }

        return true;
    } catch (Throwable $e) {
        return false;
    }
}

function appointment_slot_is_available(string $stationSlug, string $serviceSlug, string $preferredDate, string $preferredTime): bool
{
    if ($preferredDate === '' || $preferredTime === '') {
        return false;
    }

    if (!in_array($preferredTime, appointment_time_slots(), true)) {
        return false;
    }

    $date = DateTimeImmutable::createFromFormat('Y-m-d', $preferredDate);
    if (!$date || $date->format('Y-m-d') !== $preferredDate) {
        return false;
    }

    if ((int) $date->format('N') === 7 || $preferredDate < date('Y-m-d')) {
        return false;
    }

    if (!service_is_scheduled_on_date($stationSlug, $serviceSlug, $date)) {
        return false;
    }

    $stmt = db()->prepare(
        'SELECT COUNT(*) AS total
         FROM appointments
         WHERE station_slug = ?
           AND preferred_date = ?
           AND status <> "Cancelled"'
    );
    $stmt->bind_param('ss', $stationSlug, $preferredDate);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    $maxSlots = fetch_station_service_capacity($stationSlug, $serviceSlug);
    return (int) ($result['total'] ?? 0) < $maxSlots;
}

function fetch_station_service_availability(string $stationSlug, string $serviceSlug, int $daysAhead = 45): array
{
    $daysAhead = max(14, min($daysAhead, 90));
    $today = new DateTimeImmutable('today');
    $endDate = $today->modify('+' . $daysAhead . ' days');

    $slots = appointment_time_slots();
    $occupied = [];

    $stmt = db()->prepare(
        'SELECT preferred_date, COUNT(*) AS total
         FROM appointments
         WHERE station_slug = ?
           AND preferred_date BETWEEN ? AND ?
           AND status <> "Cancelled"
         GROUP BY preferred_date'
    );
    $start = $today->format('Y-m-d');
    $end = $endDate->format('Y-m-d');
    $stmt->bind_param('sss', $stationSlug, $start, $end);
    $stmt->execute();

    foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $row) {
        $occupied[(string) $row['preferred_date']] = (int) $row['total'];
    }

    $maxSlots = fetch_station_service_capacity($stationSlug, $serviceSlug);

    $dates = [];
    for ($cursor = $today; $cursor <= $endDate; $cursor = $cursor->modify('+1 day')) {
        if ((int) $cursor->format('N') === 7) {
            continue;
        }

        if (!service_is_scheduled_on_date($stationSlug, $serviceSlug, $cursor)) {
            continue;
        }

        $dateKey = $cursor->format('Y-m-d');
        $daySlots = [];
        $bookedCount = (int) ($occupied[$dateKey] ?? 0);
        $availableCount = max(0, $maxSlots - $bookedCount);
        $daySlots[] = [
            'value' => 'Daily Slot',
            'label' => $availableCount . ' slots available',
            'available' => $availableCount > 0,
            'availableCount' => $availableCount,
        ];

        $dates[$dateKey] = [
            'date' => $dateKey,
            'dayNumber' => (int) $cursor->format('j'),
            'dayName' => $cursor->format('D'),
            'monthKey' => $cursor->format('Y-m'),
            'fullLabel' => $cursor->format('D, M j'),
            'longLabel' => $cursor->format('F j, Y'),
            'scheduleLabel' => service_schedule_label($stationSlug, $serviceSlug),
            'available' => count(array_filter($daySlots, static fn(array $slot): bool => $slot['available'])) > 0,
            'slots' => $daySlots,
        ];
    }

    $months = [];
    foreach ($dates as $dateInfo) {
        $monthKey = $dateInfo['monthKey'];
        if (!isset($months[$monthKey])) {
            $monthDate = DateTimeImmutable::createFromFormat('Y-m-d', $dateInfo['date']);
            $firstDay = $monthDate->modify('first day of this month');
            $months[$monthKey] = [
                'key' => $monthKey,
                'label' => $firstDay->format('F Y'),
                'firstWeekday' => (int) $firstDay->format('w'),
                'daysInMonth' => (int) $firstDay->format('t'),
            ];
        }
    }

    return [
        'generatedOn' => date('Y-m-d'),
        'months' => array_values($months),
        'dates' => $dates,
        'slotLabels' => $slots,
    ];
}

function fetch_appointment_by_code(string $appointmentCode, ?string $stationScope = null): ?array
{
    $appointmentCode = strtoupper(trim($appointmentCode));
    if ($appointmentCode === '') {
        return null;
    }

    $sql = 'SELECT * FROM appointments WHERE appointment_code = ?';
    $params = [$appointmentCode];
    $types = 's';

    if ($stationScope !== null) {
        $sql .= ' AND station_slug = ?';
        $params[] = $stationScope;
        $types .= 's';
    }

    $sql .= ' LIMIT 1';
    $stmt = db()->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    return $result ?: null;
}

function fetch_appointment_by_code_or_search(string $search, ?string $stationScope = null): ?array
{
    $found = fetch_appointment_by_code($search, $stationScope);
    if ($found !== null) {
        return $found;
    }
    $searchTrimmed = trim($search);
    if ($searchTrimmed === '') {
        return null;
    }
    $searchWild = '%' . $searchTrimmed . '%';
    $sql = 'SELECT * FROM appointments WHERE (CONCAT(first_name, " ", last_name) LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR contact_number LIKE ?)';
    $params = [$searchWild, $searchWild, $searchWild, $searchWild];
    $types = 'ssss';
    if ($stationScope !== null) {
        $sql .= ' AND station_slug = ?';
        $params[] = $stationScope;
        $types .= 's';
    }
    $sql .= ' ORDER BY id DESC LIMIT 1';
    $stmt = db()->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    return $result ?: null;
}

function save_appointment_clinical_details(int $appointmentId, array $data, ?string $stationScope = null): bool
{
    $appointment = fetch_appointment_by_id($appointmentId);
    if ($appointment === null) {
        return false;
    }

    if ($stationScope !== null && (string) $appointment['station_slug'] !== $stationScope) {
        return false;
    }

    $bodyTemperature = array_key_exists('body_temperature', $data) ? trim((string) $data['body_temperature']) : (string) ($appointment['body_temperature'] ?? '');
    $pulseRate = array_key_exists('pulse_rate', $data) ? trim((string) $data['pulse_rate']) : (string) ($appointment['pulse_rate'] ?? '');
    $respirationRate = array_key_exists('respiration_rate', $data) ? trim((string) $data['respiration_rate']) : (string) ($appointment['respiration_rate'] ?? '');
    $bloodPressure = array_key_exists('blood_pressure', $data) ? trim((string) $data['blood_pressure']) : (string) ($appointment['blood_pressure'] ?? '');
    $doctorNotes = array_key_exists('doctor_notes', $data) ? trim((string) $data['doctor_notes']) : (string) ($appointment['doctor_notes'] ?? '');

    $stmt = db()->prepare(
        'UPDATE appointments
         SET body_temperature = ?, pulse_rate = ?, respiration_rate = ?, blood_pressure = ?, doctor_notes = ?
         WHERE id = ?'
    );
    $stmt->bind_param('sssssi', $bodyTemperature, $pulseRate, $respirationRate, $bloodPressure, $doctorNotes, $appointmentId);

    return $stmt->execute();
}

function appointment_has_vitals(array $appointment): bool
{
    foreach (['body_temperature', 'pulse_rate', 'respiration_rate', 'blood_pressure'] as $field) {
        if (trim((string) ($appointment[$field] ?? '')) === '') {
            return false;
        }
    }

    return true;
}

function appointment_has_photo(array $appointment): bool
{
    return trim((string) ($appointment['photo_path'] ?? '')) !== '';
}

function appointment_has_clinical_notes(array $appointment): bool
{
    return trim((string) ($appointment['doctor_notes'] ?? '')) !== '';
}

function appointment_can_complete(array $appointment): bool
{
    return appointment_has_vitals($appointment) && appointment_has_photo($appointment) && appointment_has_clinical_notes($appointment);
}

function appointment_has_completed_clinical_details(array $appointment): bool
{
    foreach (['body_temperature', 'pulse_rate', 'respiration_rate', 'blood_pressure', 'doctor_notes'] as $field) {
        if (trim((string) ($appointment[$field] ?? '')) === '') {
            return false;
        }
    }

    return true;
}

function appointment_age(array $appointment): int
{
    $birthDate = trim((string) ($appointment['birth_date'] ?? ''));
    if ($birthDate === '') {
        return 0;
    }

    try {
        return (int) date_diff(new DateTimeImmutable($birthDate), new DateTimeImmutable('today'))->y;
    } catch (Exception $exception) {
        return 0;
    }
}

function fetch_appointments(array $filters = []): array
{
    $sql = 'SELECT * FROM appointments WHERE 1=1';
    $params = [];
    $types = '';

    if (!empty($filters['station_slug'])) {
        $sql .= ' AND station_slug = ?';
        $params[] = $filters['station_slug'];
        $types .= 's';
    }

    if (!empty($filters['service_slug'])) {
        $sql .= ' AND service_slug = ?';
        $params[] = $filters['service_slug'];
        $types .= 's';
    }

    if (!empty($filters['status'])) {
        $sql .= ' AND status = ?';
        $params[] = $filters['status'];
        $types .= 's';
    }

    if (!empty($filters['date'])) {
        if ($filters['date'] === 'today') {
            $sql .= ' AND preferred_date = CURDATE()';
        } elseif ($filters['date'] === 'upcoming') {
            $sql .= ' AND preferred_date > CURDATE()';
        } elseif ($filters['date'] === 'both' || $filters['date'] === 'all') {
            $sql .= ' AND preferred_date >= CURDATE()';
        }
    }

    if (!empty($filters['search'])) {
        $sql .= ' AND (first_name LIKE ? OR middle_name LIKE ? OR last_name LIKE ? OR appointment_code LIKE ? OR patient_id LIKE ? OR service_name LIKE ? OR contact_number LIKE ?)';
        $search = '%' . $filters['search'] . '%';
        array_push($params, $search, $search, $search, $search, $search, $search, $search);
        $types .= 'sssssss';
    }

    $sql .= ' ORDER BY FIELD(status, "Pending", "Confirmed", "Serving", "Completed", "Cancelled"), preferred_date ASC, preferred_time ASC, created_at DESC';

    $stmt = db()->prepare($sql);
    if ($params !== []) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();

    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function fetch_queue_entries(array $filters = []): array
{
    $sql = 'SELECT * FROM appointments WHERE status IN ("Confirmed", "Serving", "Completed")';
    $params = [];
    $types = '';

    if (!empty($filters['station_slug'])) {
        $sql .= ' AND station_slug = ?';
        $params[] = $filters['station_slug'];
        $types .= 's';
    }

    if (!empty($filters['service_slug'])) {
        $sql .= ' AND service_slug = ?';
        $params[] = $filters['service_slug'];
        $types .= 's';
    }

    if (!empty($filters['date'])) {
        if ($filters['date'] === 'today') {
            $sql .= ' AND preferred_date = CURDATE()';
        } elseif ($filters['date'] === 'upcoming') {
            $sql .= ' AND preferred_date > CURDATE()';
        } elseif ($filters['date'] === 'both' || $filters['date'] === 'all') {
            $sql .= ' AND preferred_date >= CURDATE()';
        }
    }

    if (!empty($filters['search'])) {
        $sql .= ' AND (appointment_code LIKE ? OR reference_code LIKE ? OR patient_id LIKE ?)';
        $search = '%' . $filters['search'] . '%';
        array_push($params, $search, $search, $search);
        $types .= 'sss';
    }

    $sql .= ' ORDER BY station_name ASC, service_name ASC, FIELD(status, "Serving", "Confirmed", "Completed"), preferred_date ASC, preferred_time ASC, created_at ASC';

    $stmt = db()->prepare($sql);
    if ($params !== []) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();

    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

if (!function_exists('h')) {
    function h(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

function render_dual_date_filter(string $paramName, string $currentValue, string $theme = 'staff'): string
{
    $current = strtolower(trim($currentValue));
    if ($current === '') {
        $current = ($paramName === 'queue_date' ? 'today' : 'both');
    }

    $isTodayActive = in_array($current, ['today', 'both', 'all'], true);
    $isUpcomingActive = in_array($current, ['upcoming', 'both', 'all'], true);

    $todayClass = $isTodayActive ? 'is-active' : '';
    $upcomingClass = $isUpcomingActive ? 'is-active' : '';

    $todayChecked = $isTodayActive ? 'checked' : '';
    $upcomingChecked = $isUpcomingActive ? 'checked' : '';

    $todayData = $isTodayActive ? '1' : '0';
    $upcomingData = $isUpcomingActive ? '1' : '0';

    ob_start();
    ?>
    <div class="dual-date-filter dual-date-filter-<?= h($theme); ?>" data-param="<?= h($paramName); ?>" data-today="<?= $todayData; ?>" data-upcoming="<?= $upcomingData; ?>">
        <button type="button" class="dual-date-pill <?= $todayClass; ?>" onclick="toggleDualDateFilter('today', '<?= h($paramName); ?>', event)" title="Toggle appointments / queue for Today">
            <input type="checkbox" name="<?= h($paramName); ?>_today" value="1" <?= $todayChecked; ?> style="display:none;">
            <span class="dual-date-dot"></span>
            <span class="dual-date-text">Today</span>
        </button>
        <button type="button" class="dual-date-pill <?= $upcomingClass; ?>" onclick="toggleDualDateFilter('upcoming', '<?= h($paramName); ?>', event)" title="Toggle appointments / queue for Upcoming dates">
            <input type="checkbox" name="<?= h($paramName); ?>_upcoming" value="1" <?= $upcomingChecked; ?> style="display:none;">
            <span class="dual-date-dot"></span>
            <span class="dual-date-text">Upcoming</span>
        </button>
    </div>
    <?php
    return (string) ob_get_clean();
}

function render_dual_status_filter(
    string $paramName,
    string $currentValue,
    string $theme = 'staff',
    string $label1 = 'Ongoing',
    string $label2 = 'Completed',
    string $val1 = 'ongoing',
    string $val2 = 'completed'
): string {
    $current = strtolower(trim($currentValue));
    if ($current === '') {
        $current = 'both';
    }

    $isVal1Active = in_array($current, [$val1, 'both', 'all'], true);
    $isVal2Active = in_array($current, [$val2, 'both', 'all'], true);

    $class1 = $isVal1Active ? 'is-active' : '';
    $class2 = $isVal2Active ? 'is-active' : '';

    $checked1 = $isVal1Active ? 'checked' : '';
    $checked2 = $isVal2Active ? 'checked' : '';

    $data1 = $isVal1Active ? '1' : '0';
    $data2 = $isVal2Active ? '1' : '0';

    ob_start();
    ?>
    <div class="dual-date-filter dual-date-filter-<?= h($theme); ?>" data-param="<?= h($paramName); ?>" data-val1="<?= h($val1); ?>" data-val2="<?= h($val2); ?>" data-today="<?= $data1; ?>" data-upcoming="<?= $data2; ?>">
        <button type="button" class="dual-date-pill <?= $class1; ?>" onclick="toggleDualStatusFilter('<?= h($val1); ?>', '<?= h($val2); ?>', '<?= h($paramName); ?>', event)" title="Toggle <?= h($label1); ?> records">
            <input type="checkbox" name="<?= h($paramName); ?>_<?= h($val1); ?>" value="1" <?= $checked1; ?> style="display:none;">
            <span class="dual-date-dot"></span>
            <span class="dual-date-text"><?= h($label1); ?></span>
        </button>
        <button type="button" class="dual-date-pill <?= $class2; ?>" onclick="toggleDualStatusFilter('<?= h($val2); ?>', '<?= h($val1); ?>', '<?= h($paramName); ?>', event)" title="Toggle <?= h($label2); ?> records">
            <input type="checkbox" name="<?= h($paramName); ?>_<?= h($val2); ?>" value="1" <?= $checked2; ?> style="display:none;">
            <span class="dual-date-dot"></span>
            <span class="dual-date-text"><?= h($label2); ?></span>
        </button>
    </div>
    <?php
    return (string) ob_get_clean();
}

function fetch_appointment_by_reference(string $referenceCode): ?array
{
    $stmt = db()->prepare('SELECT * FROM appointments WHERE reference_code = ? LIMIT 1');
    $stmt->bind_param('s', $referenceCode);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    return $result ?: null;
}

function fetch_patient_profile_by_patient_id(string $patientId): ?array
{
    $patientId = strtoupper(trim($patientId));
    if ($patientId === '') {
        return null;
    }

    $stmt = db()->prepare(
        'SELECT *
         FROM appointments
         WHERE UPPER(patient_id) = ?
           AND status <> "Cancelled"
         ORDER BY preferred_date DESC, preferred_time DESC, created_at DESC'
    );
    $stmt->bind_param('s', $patientId);
    $stmt->execute();
    $visits = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $profileRow = fetch_patient_current_profile_row($patientId);
    if ($profileRow === null) {
        try {
            $stmtAcc = db()->prepare('SELECT * FROM patient_accounts WHERE UPPER(patient_id) = ? LIMIT 1');
            $stmtAcc->bind_param('s', $patientId);
            $stmtAcc->execute();
            $profileRow = $stmtAcc->get_result()->fetch_assoc();
        } catch (Throwable $e) {}
    }

    if ($visits === [] && $profileRow === null) {
        return null;
    }

    $completedClinicalVisits = array_values(array_filter(
        $visits,
        static fn(array $visit): bool => (string) ($visit['status'] ?? '') === 'Completed' && appointment_has_completed_clinical_details($visit)
    ));

    $profile = $profileRow ?? ($visits[0] ?? []);
    $birthDate = (string) ($profile['birth_date'] ?? '');
    $photoPath = '';
    foreach ($visits as $visit) {
        if (trim((string) ($visit['photo_path'] ?? '')) !== '') {
            $photoPath = (string) $visit['photo_path'];
            break;
        }
    }

    return [
        'patient_id' => $patientId,
        'first_name' => (string) ($profile['first_name'] ?? ''),
        'middle_name' => (string) ($profile['middle_name'] ?? ''),
        'last_name' => (string) ($profile['last_name'] ?? ''),
        'gender' => (string) ($profile['gender'] ?? ''),
        'birth_date' => $birthDate,
        'age' => ($birthDate !== '' && $birthDate !== '0000-00-00') ? (int) date_diff(new DateTimeImmutable($birthDate), new DateTimeImmutable('today'))->y : 0,
        'complete_address' => (string) ($profile['complete_address'] ?? ''),
        'contact_number' => (string) ($profile['contact_number'] ?? ''),
        'email' => (string) ($profile['email'] ?? ''),
        'photo_path' => $photoPath,
        'last_visit' => (string) ($visits[0]['preferred_date'] ?? ''),
        'address_history' => fetch_patient_info_history($patientId, 'complete_address'),
        'contact_history' => fetch_patient_info_history($patientId, 'contact_number'),
        'visits' => $completedClinicalVisits,
    ];
}

function fetch_appointment_by_id(int $appointmentId): ?array
{
    $stmt = db()->prepare('SELECT * FROM appointments WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $appointmentId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    return $result ?: null;
}

function fetch_previous_immunization_relationship(string $patientId): ?string
{
    $patientId = strtoupper(trim($patientId));
    if ($patientId === '') {
        return null;
    }

    $stmt = db()->prepare(
        'SELECT immunization_relationship
         FROM appointments
         WHERE UPPER(patient_id) = ?
           AND service_slug = "immunization"
           AND immunization_relationship IS NOT NULL
           AND immunization_relationship != ""
           AND status <> "Cancelled"
         ORDER BY created_at DESC
         LIMIT 1'
    );
    $stmt->bind_param('s', $patientId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    return $result ? (string) $result['immunization_relationship'] : null;
}

function update_appointment_status(int $appointmentId, string $newStatus, ?string $stationScope = null): bool
{
    $appointment = fetch_appointment_by_id($appointmentId);
    if ($appointment === null) {
        return false;
    }

    if ($stationScope !== null && $appointment['station_slug'] !== $stationScope) {
        return false;
    }

    $currentStatus = (string) $appointment['status'];
    $allowedTransitions = [
        'Pending' => ['Confirmed', 'Cancelled'],
        'Confirmed' => ['Serving', 'Cancelled'],
        'Serving' => ['Completed'],
        'Completed' => [],
        'Cancelled' => [],
    ];

    if (!in_array($newStatus, $allowedTransitions[$currentStatus] ?? [], true)) {
        return false;
    }

    $stmt = db()->prepare('UPDATE appointments SET status = ? WHERE id = ?');
    $stmt->bind_param('si', $newStatus, $appointmentId);
    $result = $stmt->execute();

    if ($result) {
        $patientName = trim(($appointment['first_name'] ?? '') . ' ' . ($appointment['last_name'] ?? ''));
        $phone = $appointment['contact_number'] ?? '';

        if (!empty($phone)) {
            if ($newStatus === 'Confirmed') {
                $message = "Health Delivery System: Hello {$patientName}, your appointment on "
                    . ($appointment['preferred_date'] ?? '')
                    . " at "
                    . ($appointment['preferred_time'] ?? '')
                    . " has been CONFIRMED.";
                sendBrevoSMS($phone, $message, $appointmentId);
            }

            if ($newStatus === 'Cancelled') {
                $message = "Health Delivery System: Hello {$patientName}, your appointment on "
                    . ($appointment['preferred_date'] ?? '')
                    . " has been CANCELLED. Please contact the Barangay Health Station.";
                sendBrevoSMS($phone, $message, $appointmentId);
            }
        }

        $stationSlug = (string)($appointment['station_slug'] ?? '');

        log_activity(
            'staff',
            $stationSlug,
            'status_changed',
            'appointment',
            (string)$appointmentId,
            $currentStatus,
            $newStatus,
            $stationSlug
        );
    }

    return $result;
}

function fetch_station_counts(string $status = 'Pending'): array
{
    $sql = 'SELECT station_slug, station_name, COUNT(*) AS total FROM appointments';
    if ($status === 'all') {
        $sql .= ' WHERE status <> "Cancelled"';
    } elseif ($status !== '') {
        $sql .= ' WHERE status = ?';
    }
    $sql .= ' GROUP BY station_slug, station_name ORDER BY station_name';

    $stmt = db()->prepare($sql);
    if ($status !== '' && $status !== 'all') {
        $stmt->bind_param('s', $status);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    return $rows;
}

function fetch_station_queue_counts(bool $todayOnly = true): array
{
    $dateCondition = $todayOnly ? ' AND preferred_date = CURDATE()' : '';
    $result = db()->query('SELECT station_slug, station_name, COUNT(*) AS total FROM appointments WHERE status IN ("Confirmed", "Serving")' . $dateCondition . ' GROUP BY station_slug, station_name ORDER BY station_name');
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    return $rows;
}

function fetch_unique_patients(string $search = '', array $filters = []): array
{
    $sql = 'SELECT * FROM appointments WHERE status = "Completed"';
    $params = [];
    $types = '';

    if (!empty($filters['station_slug']) && $filters['station_slug'] !== 'all') {
        $sql .= ' AND station_slug = ?';
        $params[] = $filters['station_slug'];
        $types .= 's';
    }

    if (!empty($filters['gender']) && strtolower($filters['gender']) !== 'all') {
        $sql .= ' AND LOWER(gender) = LOWER(?)';
        $params[] = $filters['gender'];
        $types .= 's';
    }

    if ($search !== '') {
        $sql .= ' AND (first_name LIKE ? OR middle_name LIKE ? OR last_name LIKE ? OR contact_number LIKE ? OR patient_id LIKE ? OR appointment_code LIKE ? OR complete_address LIKE ?)';
        $term = '%' . $search . '%';
        array_push($params, $term, $term, $term, $term, $term, $term, $term);
        $types .= 'sssssss';
    }

    $sql .= ' ORDER BY updated_at DESC, preferred_date DESC, preferred_time DESC, created_at DESC';

    $stmt = db()->prepare($sql);
    if ($params !== []) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();

    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $patients = [];
    $visitCounts = [];

    foreach ($rows as $row) {
        if (!appointment_has_completed_clinical_details($row)) {
            continue;
        }

        $patientId = (string) (($row['patient_id'] ?? '') !== '' ? $row['patient_id'] : appointment_patient_record_key($row));
        $visitCounts[$patientId] = ($visitCounts[$patientId] ?? 0) + 1;

        if (isset($patients[$patientId])) {
            continue;
        }

        $currentProfile = fetch_patient_current_profile_row($patientId) ?? $row;
        $birthDate = (string) ($row['birth_date'] ?? '');
        $patients[$patientId] = [
            'patient_id' => $patientId,
            'latest_appointment_id' => (int) ($row['id'] ?? 0),
            'first_name' => (string) ($currentProfile['first_name'] ?? ''),
            'middle_name' => (string) ($currentProfile['middle_name'] ?? ''),
            'last_name' => (string) ($currentProfile['last_name'] ?? ''),
            'gender' => (string) ($currentProfile['gender'] ?? ''),
            'complete_address' => (string) ($currentProfile['complete_address'] ?? ''),
            'contact_number' => (string) ($currentProfile['contact_number'] ?? ''),
            'email' => (string) ($currentProfile['email'] ?? ''),
            'photo_path' => (string) ($row['photo_path'] ?? ''),
            'age' => $birthDate !== '' ? (int) date_diff(new DateTimeImmutable($birthDate), new DateTimeImmutable('today'))->y : 0,
            'last_visit' => (string) ($row['preferred_date'] ?? ''),
            'last_service' => (string) ($row['service_name'] ?? ''),
            'station_name' => (string) ($row['station_name'] ?? ''),
            'station_slug' => (string) ($row['station_slug'] ?? ''),
            'total_visits' => 1,
        ];
    }

    foreach ($patients as $pid => &$pat) {
        $pat['total_visits'] = $visitCounts[$pid] ?? 1;
    }
    unset($pat);

    return array_values($patients);
}

function fetch_patient_profile(string $patientId): ?array
{
    return fetch_patient_profile_by_patient_id($patientId);
}

function fetch_upcoming_events(array $filters = []): array
{
    $sql = 'SELECT * FROM upcoming_events WHERE 1=1';
    $params = [];
    $types = '';

    if (($filters['upcoming_only'] ?? true) === true) {
        $today = date('Y-m-d');
        $sql .= ' AND event_date >= ?';
        $params[] = $today;
        $types .= 's';
    }

    if (!empty($filters['station_slug'])) {
        $sql .= ' AND station_slug = ?';
        $params[] = $filters['station_slug'];
        $types .= 's';
    }

    if (!empty($filters['search'])) {
        $sql .= ' AND (title LIKE ? OR description LIKE ? OR station_name LIKE ?)';
        $term = '%' . $filters['search'] . '%';
        array_push($params, $term, $term, $term);
        $types .= 'sss';
    }

    $sql .= ' ORDER BY event_date ASC, time_label ASC, created_at DESC';

    $stmt = db()->prepare($sql);
    if ($params !== []) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();

    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function create_upcoming_event(array $eventData): bool
{
    $station = fetch_station_by_slug_catalog((string) ($eventData['station_slug'] ?? ''));
    if ($station === null) {
        return false;
    }

    $title = trim((string) ($eventData['title'] ?? ''));
    $description = trim((string) ($eventData['description'] ?? ''));
    $eventDate = trim((string) ($eventData['event_date'] ?? ''));
    $timeLabel = trim((string) ($eventData['time_label'] ?? ''));
    $endTimeLabel = trim((string) ($eventData['end_time_label'] ?? ''));
    if ($endTimeLabel === '') {
        $endTimeLabel = $timeLabel;
    }
    $icon = trim((string) ($eventData['icon'] ?? 'calendar')) ?: 'calendar';
    $accent = trim((string) ($eventData['accent'] ?? 'mint')) ?: 'mint';
    $createdBy = trim((string) ($eventData['created_by'] ?? 'staff-panel'));

    if ($title === '' || $eventDate === '' || $timeLabel === '') {
        return false;
    }

    $stmt = db()->prepare(
        'INSERT INTO upcoming_events (station_slug, station_name, title, description, event_date, time_label, end_time_label, icon, accent, created_by)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );

    $stationSlug = (string) $station['slug'];
    $stationName = (string) $station['name'];
    $stmt->bind_param('ssssssssss', $stationSlug, $stationName, $title, $description, $eventDate, $timeLabel, $endTimeLabel, $icon, $accent, $createdBy);

    return $stmt->execute();
}

function delete_upcoming_event(int $eventId, ?string $stationScope = null): bool
{
    if ($stationScope !== null) {
        $stmt = db()->prepare('DELETE FROM upcoming_events WHERE id = ? AND station_slug = ?');
        $stmt->bind_param('is', $eventId, $stationScope);

        return $stmt->execute();
    }

    $stmt = db()->prepare('DELETE FROM upcoming_events WHERE id = ?');
    $stmt->bind_param('i', $eventId);

    return $stmt->execute();
}

function fetch_upcoming_event_by_id(int $eventId): ?array
{
    $stmt = db()->prepare('SELECT * FROM upcoming_events WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $eventId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    return $result ?: null;
}

function update_upcoming_event(int $eventId, array $eventData, ?string $stationScope = null): bool
{
    $existing = fetch_upcoming_event_by_id($eventId);
    if ($existing === null) {
        return false;
    }

    if ($stationScope !== null && $existing['station_slug'] !== $stationScope) {
        return false;
    }

    $title = trim((string) ($eventData['title'] ?? ''));
    $description = trim((string) ($eventData['description'] ?? ''));
    $eventDate = trim((string) ($eventData['event_date'] ?? ''));
    $timeLabel = trim((string) ($eventData['time_label'] ?? ''));
    $endTimeLabel = trim((string) ($eventData['end_time_label'] ?? ''));
    $icon = trim((string) ($eventData['icon'] ?? $existing['icon'])) ?: (string) $existing['icon'];
    $accent = trim((string) ($eventData['accent'] ?? $existing['accent'])) ?: (string) $existing['accent'];

    if ($title === '' || $description === '' || $eventDate === '' || $timeLabel === '' || $endTimeLabel === '') {
        return false;
    }

    $stmt = db()->prepare(
        'UPDATE upcoming_events
         SET title = ?, description = ?, event_date = ?, time_label = ?, end_time_label = ?, icon = ?, accent = ?
         WHERE id = ?'
    );
    $stmt->bind_param('sssssssi', $title, $description, $eventDate, $timeLabel, $endTimeLabel, $icon, $accent, $eventId);

    return $stmt->execute();
}

function fetch_staff_account_by_email(string $email): ?array
{
    $stmt = db()->prepare('SELECT * FROM staff_accounts WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    return $result ?: null;
}

function fetch_staff_account_by_id(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM staff_accounts WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    return $result ?: null;
}

function update_staff_account_details(int $staffId, array $data): bool
{
    $staffName = trim((string) ($data['staff_name'] ?? ''));
    $email = strtolower(trim((string) ($data['email'] ?? '')));
    $password = (string) ($data['password'] ?? '');
    $birthDate = trim((string) ($data['birth_date'] ?? ''));
    $gender = trim((string) ($data['gender'] ?? ''));
    $contactNumber = trim((string) ($data['contact_number'] ?? ''));
    $homeAddress = trim((string) ($data['home_address'] ?? ''));
    $emergencyContact = trim((string) ($data['emergency_contact'] ?? ''));
    $emergencyPhone = trim((string) ($data['emergency_phone'] ?? ''));

    if ($staffId <= 0 || $staffName === '' || $email === '') {
        return false;
    }

    $stmtCheck = db()->prepare('SELECT id FROM staff_accounts WHERE email = ? AND id <> ? LIMIT 1');
    $stmtCheck->bind_param('si', $email, $staffId);
    $stmtCheck->execute();
    if ($stmtCheck->get_result()->num_rows > 0) {
        return false;
    }

    $birthDateVal = ($birthDate !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthDate)) ? $birthDate : null;

    if ($password !== '') {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = db()->prepare(
            'UPDATE staff_accounts 
             SET staff_name = ?, email = ?, birth_date = ?, gender = ?, contact_number = ?, home_address = ?, emergency_contact = ?, emergency_phone = ?, password_hash = ? 
             WHERE id = ?'
        );
        $stmt->bind_param('sssssssssi', $staffName, $email, $birthDateVal, $gender, $contactNumber, $homeAddress, $emergencyContact, $emergencyPhone, $passwordHash, $staffId);
    } else {
        $stmt = db()->prepare(
            'UPDATE staff_accounts 
             SET staff_name = ?, email = ?, birth_date = ?, gender = ?, contact_number = ?, home_address = ?, emergency_contact = ?, emergency_phone = ? 
             WHERE id = ?'
        );
        $stmt->bind_param('ssssssssi', $staffName, $email, $birthDateVal, $gender, $contactNumber, $homeAddress, $emergencyContact, $emergencyPhone, $staffId);
    }

    return $stmt->execute();
}

function fetch_staff_accounts(): array
{
    $result = db()->query('SELECT id, station_slug, station_name, staff_name, email FROM staff_accounts ORDER BY station_name');
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    return $rows;
}

function save_staff_account(array $accountData): bool
{
    $station = fetch_station_by_slug_catalog((string) ($accountData['station_slug'] ?? ''));
    if ($station === null) {
        return false;
    }

    $staffName = trim((string) ($accountData['staff_name'] ?? ''));
    $email = strtolower(trim((string) ($accountData['email'] ?? '')));
    $password = (string) ($accountData['password'] ?? '');

    if ($staffName === '' || $email === '' || $password === '') {
        return false;
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = db()->prepare(
        'INSERT INTO staff_accounts (station_slug, station_name, staff_name, email, password_hash)
         VALUES (?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE station_name = VALUES(station_name), staff_name = VALUES(staff_name), email = VALUES(email), password_hash = VALUES(password_hash)'
    );

    $stationSlug = (string) $station['slug'];
    $stationName = (string) $station['name'];
    $stmt->bind_param('sssss', $stationSlug, $stationName, $staffName, $email, $passwordHash);

    return $stmt->execute();
}

function fetch_admin_account_by_email(string $email): ?array
{
    $stmt = db()->prepare('SELECT * FROM admin_accounts WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    return $result ?: null;
}

function fetch_admin_account_by_username(string $username): ?array
{
    $stmt = db()->prepare('SELECT * FROM admin_accounts WHERE email = ? OR admin_name = ? LIMIT 1');
    $stmt->bind_param('ss', $username, $username);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    return $result ?: null;
}

function fetch_admin_accounts(): array
{
    $result = db()->query('SELECT id, admin_name, office_name, email FROM admin_accounts ORDER BY admin_name');
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    return $rows;
}

function create_admin_account(array $accountData): bool
{
    $adminName = trim((string) ($accountData['admin_name'] ?? ''));
    $officeName = trim((string) ($accountData['office_name'] ?? ''));
    $email = strtolower(trim((string) ($accountData['email'] ?? '')));
    $password = (string) ($accountData['password'] ?? '');

    if ($adminName === '' || $officeName === '' || $email === '' || $password === '') {
        return false;
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = db()->prepare(
        'INSERT INTO admin_accounts (admin_name, office_name, email, password_hash)
         VALUES (?, ?, ?, ?)'
    );
    $stmt->bind_param('ssss', $adminName, $officeName, $email, $passwordHash);

    return $stmt->execute();
}

function delete_admin_account(int $id): bool
{
    try {
        $count = (int) (db()->query('SELECT COUNT(*) AS total FROM admin_accounts')->fetch_assoc()['total'] ?? 0);
        if ($count <= 1) {
            return false;
        }

        $stmt = db()->prepare('DELETE FROM admin_accounts WHERE id = ?');
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    } catch (Throwable $e) {
        return false;
    }
}

function delete_staff_account(int $id): bool
{
    try {
        $stmt = db()->prepare('DELETE FROM staff_accounts WHERE id = ?');
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    } catch (Throwable $e) {
        return false;
    }
}

function appointment_stats(): array
{
    $patients = db()->query('SELECT COUNT(DISTINCT CONCAT(first_name, "|", COALESCE(middle_name, ""), "|", last_name, "|", contact_number)) AS total FROM appointments WHERE status = "Completed"')->fetch_assoc();
    $today = db()->query('SELECT COUNT(*) AS total FROM appointments WHERE status IN ("Pending", "Confirmed", "Serving")')->fetch_assoc();
    $services = db()->query('SELECT COUNT(DISTINCT service_slug) AS total FROM appointments WHERE status <> "Cancelled"')->fetch_assoc();
    $bookings = db()->query('SELECT COUNT(*) AS total FROM appointments WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND status <> "Cancelled"')->fetch_assoc();

    return [
        'total_patients' => (int) ($patients['total'] ?? 0),
        'appointments_today' => (int) ($today['total'] ?? 0),
        'active_services' => (int) ($services['total'] ?? 0),
        'online_bookings' => (int) ($bookings['total'] ?? 0),
    ];
}

function weekly_chart_data(): array
{
    $weekStart = new DateTimeImmutable('today');
    $dayOfWeek = (int) $weekStart->format('N');
    $weekStart = $weekStart->sub(new DateInterval('P' . ($dayOfWeek - 1) . 'D'));
    $weekEnd = $weekStart->add(new DateInterval('P5D'));

    $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    $dateKeys = [];
    foreach ($days as $index => $label) {
        $dateKeys[$weekStart->add(new DateInterval('P' . $index . 'D'))->format('Y-m-d')] = $label;
    }

    $completedPatients = array_fill_keys($days, 0);
    $bookedAppointments = array_fill_keys($days, 0);

    $startDate = $weekStart->format('Y-m-d');
    $endDate = $weekEnd->format('Y-m-d');

    // 1. Appointments scheduled for each day of the week (preferred_date)
    $stmt1 = db()->prepare(
        'SELECT preferred_date AS day_date, COUNT(*) AS total_count
           FROM appointments
          WHERE status <> "Cancelled"
            AND preferred_date BETWEEN ? AND ?
          GROUP BY preferred_date'
    );
    $stmt1->bind_param('ss', $startDate, $endDate);
    $stmt1->execute();
    $result1 = $stmt1->get_result();

    while ($row = $result1->fetch_assoc()) {
        $date = (string) ($row['day_date'] ?? '');
        if (isset($dateKeys[$date])) {
            $dayLabel = $dateKeys[$date];
            $bookedAppointments[$dayLabel] = (int) ($row['total_count'] ?? 0);
        }
    }

    // 2. Patients who completed their appointments for each day of the week
    $stmt2 = db()->prepare(
        'SELECT preferred_date AS day_date, COUNT(DISTINCT COALESCE(patient_id, CONCAT(first_name, "|", last_name, "|", birth_date))) AS completed_count
           FROM appointments
          WHERE status = "Completed"
            AND preferred_date BETWEEN ? AND ?
          GROUP BY preferred_date'
    );
    $stmt2->bind_param('ss', $startDate, $endDate);
    $stmt2->execute();
    $result2 = $stmt2->get_result();

    while ($row = $result2->fetch_assoc()) {
        $compDate = (string) ($row['day_date'] ?? '');
        if (isset($dateKeys[$compDate])) {
            $dayLabel = $dateKeys[$compDate];
            $completedPatients[$dayLabel] = (int) ($row['completed_count'] ?? 0);
        }
    }

    return [
        'days' => $days,
        'patients' => array_values($completedPatients),
        'appointments' => array_values($bookedAppointments),
    ];
}

function service_utilization_data(): array
{
    $rows = db()->query('SELECT service_name, COUNT(*) AS total FROM appointments WHERE status <> "Cancelled" GROUP BY service_name ORDER BY total DESC LIMIT 5');
    $labels = [];
    $values = [];

    while ($row = $rows->fetch_assoc()) {
        $labels[] = $row['service_name'];
        $values[] = (int) $row['total'];
    }

    if ($labels === []) {
        $labels = ['Immunization', 'Prenatal', 'Consultation'];
        $values = [0, 0, 0];
    }

    return ['labels' => $labels, 'values' => $values];
}

function recent_activity(): array
{
    $rows = db()->query('SELECT first_name, middle_name, last_name, service_name, station_name, created_at, updated_at, status FROM appointments ORDER BY updated_at DESC, created_at DESC LIMIT 10');
    $items = [];
    while ($row = $rows->fetch_assoc()) {
        $items[] = $row;
    }

    return $items;
}

function build_report_filter_sql(array $filters, string $tableAlias = ''): array
{
    $prefix = $tableAlias !== '' ? $tableAlias . '.' : '';
    $conditions = [];
    $params = [];
    $types = '';

    $fromDate = trim((string) ($filters['from_date'] ?? $filters['report_from'] ?? ''));
    $toDate = trim((string) ($filters['to_date'] ?? $filters['report_to'] ?? ''));
    $gender = trim((string) ($filters['gender'] ?? ''));
    $ageGroup = trim((string) ($filters['age_group'] ?? ''));
    $stationSlug = trim((string) ($filters['station_slug'] ?? ''));
    $serviceSlug = trim((string) ($filters['service_slug'] ?? ''));
    $status = trim((string) ($filters['status'] ?? ''));

    if ($fromDate !== '') {
        $conditions[] = "{$prefix}preferred_date >= ?";
        $params[] = $fromDate;
        $types .= 's';
    }
    if ($toDate !== '') {
        $conditions[] = "{$prefix}preferred_date <= ?";
        $params[] = $toDate;
        $types .= 's';
    }
    if ($gender !== '' && strtolower($gender) !== 'all') {
        $conditions[] = "LOWER({$prefix}gender) = LOWER(?)";
        $params[] = $gender;
        $types .= 's';
    }
    if ($ageGroup !== '' && $ageGroup !== 'all') {
        if ($ageGroup === '0-12' || $ageGroup === 'pediatric') {
            $conditions[] = "TIMESTAMPDIFF(YEAR, {$prefix}birth_date, CURDATE()) BETWEEN 0 AND 12";
        } elseif ($ageGroup === '13-17' || $ageGroup === 'adolescent') {
            $conditions[] = "TIMESTAMPDIFF(YEAR, {$prefix}birth_date, CURDATE()) BETWEEN 13 AND 17";
        } elseif ($ageGroup === '18-59' || $ageGroup === 'adult') {
            $conditions[] = "TIMESTAMPDIFF(YEAR, {$prefix}birth_date, CURDATE()) BETWEEN 18 AND 59";
        } elseif ($ageGroup === '60+' || $ageGroup === 'senior') {
            $conditions[] = "TIMESTAMPDIFF(YEAR, {$prefix}birth_date, CURDATE()) >= 60";
        }
    }
    if ($stationSlug !== '' && $stationSlug !== 'all') {
        $conditions[] = "{$prefix}station_slug = ?";
        $params[] = $stationSlug;
        $types .= 's';
    }
    if ($serviceSlug !== '' && $serviceSlug !== 'all') {
        $conditions[] = "{$prefix}service_slug = ?";
        $params[] = $serviceSlug;
        $types .= 's';
    }
    if ($status !== '' && $status !== 'all') {
        $conditions[] = "{$prefix}status = ?";
        $params[] = $status;
        $types .= 's';
    }

    $whereSql = $conditions !== [] ? 'WHERE ' . implode(' AND ', $conditions) : '';

    return [
        'where' => $whereSql,
        'conditions' => $conditions,
        'params' => $params,
        'types' => $types,
    ];
}

function monthly_trends_data($fromDateOrFilters = '', string $toDate = ''): array
{
    $filters = is_array($fromDateOrFilters) ? $fromDateOrFilters : [
        'report_from' => (string) $fromDateOrFilters,
        'report_to'   => $toDate,
    ];

    if (empty($filters['report_from'])) {
        $filters['report_from'] = date('Y-m-d', strtotime('-5 months', strtotime(date('Y-m-01'))));
    }
    if (empty($filters['report_to'])) {
        $filters['report_to'] = date('Y-m-d', strtotime('last day of this month'));
    }

    $builder = build_report_filter_sql($filters);
    $where = $builder['where'] !== '' ? $builder['where'] . ' AND status <> \'Cancelled\'' : 'WHERE status <> \'Cancelled\'';

    $sql = 'SELECT DATE_FORMAT(preferred_date, \'%b\') AS month_label,
                   DATE_FORMAT(preferred_date, \'%Y-%m\') AS month_key,
                   COUNT(*) AS appointments,
                   COUNT(DISTINCT COALESCE(patient_id, CONCAT(first_name, last_name, birth_date))) AS patients
            FROM appointments
            ' . $where . '
            GROUP BY month_key, month_label
            ORDER BY month_key ASC';

    $stmt = db()->prepare($sql);
    if ($builder['params'] !== []) {
        $stmt->bind_param($builder['types'], ...$builder['params']);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $months = [];
    $appointments = [];
    $patients = [];

    while ($row = $result->fetch_assoc()) {
        $months[]       = $row['month_label'];
        $appointments[] = (int) $row['appointments'];
        $patients[]     = (int) $row['patients'];
    }

    if ($months === []) {
        $months       = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
        $appointments = [0, 0, 0, 0, 0, 0];
        $patients     = [0, 0, 0, 0, 0, 0];
    }

    return ['months' => $months, 'appointments' => $appointments, 'patients' => $patients];
}

function station_performance_data(array $filters = []): array
{
    $builder = build_report_filter_sql($filters);
    $where = $builder['where'];

    $sql = 'SELECT station_name, station_slug,
                   SUM(status = \'Completed\')  AS completed,
                   SUM(status = \'Cancelled\')  AS cancelled,
                   SUM(status = \'Pending\')    AS pending,
                   SUM(status = \'Confirmed\')  AS confirmed,
                   SUM(status = \'Serving\')    AS serving,
                   COUNT(*) AS total
            FROM appointments
            ' . $where . '
            GROUP BY station_slug, station_name
            ORDER BY completed DESC, total DESC';

    $stmt = db()->prepare($sql);
    if ($builder['params'] !== []) {
        $stmt->bind_param($builder['types'], ...$builder['params']);
    }
    $stmt->execute();
    $rows = $stmt->get_result();

    $items = [];
    while ($row = $rows->fetch_assoc()) {
        $total    = (int) $row['total'];
        $completed = (int) $row['completed'];
        $items[] = [
            'station_name' => $row['station_name'],
            'station_slug' => $row['station_slug'],
            'completed'    => $completed,
            'cancelled'    => (int) $row['cancelled'],
            'pending'      => (int) $row['pending'],
            'confirmed'    => (int) $row['confirmed'],
            'serving'      => (int) $row['serving'],
            'total'        => $total,
            'completion_rate' => $total > 0 ? round($completed * 100 / $total) : 0,
        ];
    }

    return $items;
}

function service_performance_data(array $filters = []): array
{
    $builder = build_report_filter_sql($filters);
    $where = $builder['where'];

    $sql = 'SELECT service_name, service_slug,
                   SUM(status = \'Completed\')  AS completed,
                   SUM(status = \'Cancelled\')  AS cancelled,
                   SUM(status = \'Pending\')    AS pending,
                   SUM(status = \'Confirmed\')  AS confirmed,
                   SUM(status = \'Serving\')    AS serving,
                   COUNT(*) AS total
            FROM appointments
            ' . $where . '
            GROUP BY service_slug, service_name
            ORDER BY total DESC';

    $stmt = db()->prepare($sql);
    if ($builder['params'] !== []) {
        $stmt->bind_param($builder['types'], ...$builder['params']);
    }
    $stmt->execute();
    $rows = $stmt->get_result();

    $items = [];
    $totalOverall = 0;
    while ($row = $rows->fetch_assoc()) {
        $total = (int) $row['total'];
        $totalOverall += $total;
        $items[] = [
            'service_name' => $row['service_name'],
            'service_slug' => $row['service_slug'],
            'completed'    => (int) $row['completed'],
            'cancelled'    => (int) $row['cancelled'],
            'pending'      => (int) $row['pending'],
            'confirmed'    => (int) $row['confirmed'],
            'serving'      => (int) $row['serving'],
            'total'        => $total,
            'share_pct'    => 0,
        ];
    }

    foreach ($items as &$it) {
        $it['share_pct'] = $totalOverall > 0 ? round(($it['total'] / $totalOverall) * 100) : 0;
    }
    unset($it);

    return $items;
}

function barangay_completed_analytics(array $filters = []): array
{
    $stations = station_catalog();
    $serviceCatalog = service_catalog();

    $completedFilters = $filters;
    $completedFilters['status'] = 'Completed';

    $builder = build_report_filter_sql($completedFilters);
    $where = $builder['where'];

    $sql = "SELECT station_slug, station_name, service_slug, service_name,
                   COUNT(*) AS completed_count,
                   COUNT(DISTINCT COALESCE(patient_id, CONCAT(first_name, '|', last_name, '|', birth_date))) AS unique_patients
            FROM appointments
            {$where}
            GROUP BY station_slug, station_name, service_slug, service_name
            ORDER BY completed_count DESC";

    $stmt = db()->prepare($sql);
    if ($builder['params'] !== []) {
        $stmt->bind_param($builder['types'], ...$builder['params']);
    }
    $stmt->execute();
    $rows = $stmt->get_result();

    $stationData = [];
    while ($r = $rows->fetch_assoc()) {
        $slug = (string) ($r['station_slug'] ?? '');
        if ($slug === '') {
            continue;
        }
        if (!isset($stationData[$slug])) {
            $stationData[$slug] = [
                'completed_total' => 0,
                'unique_patients_total' => 0,
                'services' => [],
            ];
        }
        $c = (int) $r['completed_count'];
        $stationData[$slug]['completed_total'] += $c;
        $stationData[$slug]['unique_patients_total'] += (int) $r['unique_patients'];

        $srvSlug = (string) ($r['service_slug'] ?? '');
        $srvMeta = $serviceCatalog[$srvSlug] ?? null;
        $stationData[$slug]['services'][] = [
            'service_slug' => $srvSlug,
            'service_name' => $r['service_name'] ?: ($srvMeta['title'] ?? ucfirst($srvSlug)),
            'count' => $c,
            'color' => $srvMeta['color'] ?? 'mint',
            'icon' => $srvMeta['icon'] ?? 'appointments',
        ];
    }

    $result = [];
    foreach ($stations as $st) {
        $slug = $st['slug'];
        $data = $stationData[$slug] ?? null;
        $completedTotal = $data ? $data['completed_total'] : 0;
        $uniquePatients = $data ? $data['unique_patients_total'] : 0;
        $services = $data ? $data['services'] : [];

        if ($completedTotal > 0) {
            foreach ($services as &$srv) {
                $srv['pct'] = round(($srv['count'] / $completedTotal) * 100);
            }
            unset($srv);
        }

        $shortName = str_ireplace([' Barangay Health Station', ' Health Station', ' Barangay Health Center'], '', $st['name']);

        $result[] = [
            'station_slug' => $slug,
            'station_name' => $st['name'],
            'barangay_name' => $shortName,
            'color' => $st['color'] ?? 'mint',
            'completed_count' => $completedTotal,
            'unique_patients' => $uniquePatients,
            'services' => $services,
            'top_service' => !empty($services) ? $services[0]['service_name'] : 'None',
        ];
    }

    usort($result, static function (array $a, array $b): int {
        if ($a['completed_count'] !== $b['completed_count']) {
            return $b['completed_count'] <=> $a['completed_count'];
        }
        return strcasecmp($a['barangay_name'], $b['barangay_name']);
    });

    return $result;
}

function demographics_breakdown_data(array $filters = []): array
{
    $builder = build_report_filter_sql($filters);
    $where = $builder['where'];

    $sql = 'SELECT
                SUM(CASE WHEN LOWER(gender) = \'female\' THEN 1 ELSE 0 END) AS count_female,
                SUM(CASE WHEN LOWER(gender) = \'male\' THEN 1 ELSE 0 END) AS count_male,
                SUM(CASE WHEN LOWER(gender) NOT IN (\'female\', \'male\') THEN 1 ELSE 0 END) AS count_other,
                SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 0 AND 12 THEN 1 ELSE 0 END) AS age_pediatric,
                SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 13 AND 17 THEN 1 ELSE 0 END) AS age_adolescent,
                SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 18 AND 59 THEN 1 ELSE 0 END) AS age_adult,
                SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) >= 60 THEN 1 ELSE 0 END) AS age_senior,
                COUNT(*) AS total
            FROM appointments
            ' . $where;

    $stmt = db()->prepare($sql);
    if ($builder['params'] !== []) {
        $stmt->bind_param($builder['types'], ...$builder['params']);
    }
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    $total = (int) ($row['total'] ?? 0);
    $female = (int) ($row['count_female'] ?? 0);
    $male = (int) ($row['count_male'] ?? 0);
    $other = (int) ($row['count_other'] ?? 0);

    $pediatric = (int) ($row['age_pediatric'] ?? 0);
    $adolescent = (int) ($row['age_adolescent'] ?? 0);
    $adult = (int) ($row['age_adult'] ?? 0);
    $senior = (int) ($row['age_senior'] ?? 0);

    return [
        'total' => $total,
        'gender' => [
            'female' => ['count' => $female, 'pct' => $total > 0 ? round($female * 100 / $total) : 0],
            'male'   => ['count' => $male, 'pct' => $total > 0 ? round($male * 100 / $total) : 0],
            'other'  => ['count' => $other, 'pct' => $total > 0 ? round($other * 100 / $total) : 0],
        ],
        'age_groups' => [
            'pediatric'  => ['label' => 'Infants & Children (0-12y)', 'count' => $pediatric, 'pct' => $total > 0 ? round($pediatric * 100 / $total) : 0],
            'adolescent' => ['label' => 'Adolescents (13-17y)', 'count' => $adolescent, 'pct' => $total > 0 ? round($adolescent * 100 / $total) : 0],
            'adult'      => ['label' => 'Adults (18-59y)', 'count' => $adult, 'pct' => $total > 0 ? round($adult * 100 / $total) : 0],
            'senior'     => ['label' => 'Seniors (60y+)', 'count' => $senior, 'pct' => $total > 0 ? round($senior * 100 / $total) : 0],
        ],
    ];
}

function fetch_filtered_report_appointments(array $filters, int $limit = 100): array
{
    $builder = build_report_filter_sql($filters);
    $where = $builder['where'];

    $sql = 'SELECT * FROM appointments
            ' . $where . '
            ORDER BY preferred_date DESC, preferred_time DESC, created_at DESC
            LIMIT ?';

    $params = $builder['params'];
    $params[] = $limit;
    $types = $builder['types'] . 'i';

    $stmt = db()->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function patient_info_change_log(int $limit = 20): array
{
    $stmt = db()->prepare(
        'SELECT h.field_name, h.old_value, h.new_value, h.changed_at,
                CONCAT(p.first_name, \' \', p.last_name) AS patient_name,
                p.patient_id
         FROM patient_info_history h
         JOIN patient_profiles p ON h.patient_id = p.patient_id
         ORDER BY h.changed_at DESC
         LIMIT ?'
    );
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function report_summary_stats($fromDateOrFilters = '', string $toDate = ''): array
{
    $filters = is_array($fromDateOrFilters) ? $fromDateOrFilters : [
        'report_from' => (string) $fromDateOrFilters,
        'report_to'   => $toDate,
    ];

    if (empty($filters['report_from'])) {
        $filters['report_from'] = date('Y-m-01');
    }
    if (empty($filters['report_to'])) {
        $filters['report_to'] = date('Y-m-d');
    }

    $builder = build_report_filter_sql($filters);
    $where = $builder['where'];

    $sqlStats = 'SELECT
                    COUNT(DISTINCT COALESCE(patient_id, CONCAT(first_name, last_name, birth_date))) AS total_unique_patients,
                    SUM(status = \'Completed\') AS completed_count,
                    SUM(status = \'Cancelled\') AS cancelled_count,
                    SUM(status = \'Confirmed\') AS confirmed_count,
                    SUM(status = \'Serving\') AS serving_count,
                    SUM(status = \'Pending\') AS pending_count,
                    COUNT(*) AS total_bookings
                 FROM appointments
                 ' . $where;

    $stmtStats = db()->prepare($sqlStats);
    if ($builder['params'] !== []) {
        $stmtStats->bind_param($builder['types'], ...$builder['params']);
    }
    $stmtStats->execute();
    $statsRow = $stmtStats->get_result()->fetch_assoc();

    $totalBookings = (int) ($statsRow['total_bookings'] ?? 0);
    $completedCount = (int) ($statsRow['completed_count'] ?? 0);
    $cancelledCount = (int) ($statsRow['cancelled_count'] ?? 0);
    $completionRate = $totalBookings > 0 ? round($completedCount * 100 / $totalBookings) : 0;
    $cancellationRate = $totalBookings > 0 ? round($cancelledCount * 100 / $totalBookings) : 0;

    $dateFrom = new DateTimeImmutable((string) $filters['report_from']);
    $dateTo = new DateTimeImmutable((string) $filters['report_to']);
    $dayCount = max(1, $dateFrom->diff($dateTo)->days + 1);
    $avgDaily = round($totalBookings / $dayCount, 1);

    return [
        'total_patients'    => (int) ($statsRow['total_unique_patients'] ?? 0),
        'services_rendered' => $totalBookings - $cancelledCount,
        'total_bookings'    => $totalBookings,
        'completed_count'   => $completedCount,
        'cancelled_count'   => $cancelledCount,
        'confirmed_count'   => (int) ($statsRow['confirmed_count'] ?? 0),
        'pending_count'     => (int) ($statsRow['pending_count'] ?? 0),
        'serving_count'     => (int) ($statsRow['serving_count'] ?? 0),
        'avg_daily'         => $avgDaily,
        'utilization_pct'   => $completionRate,
        'cancellation_pct'  => $cancellationRate,
        'day_count'         => $dayCount,
    ];
}

function health_events_summary(): array
{
    $today = date('Y-m-d');
    $rows = db()->query(
        "SELECT station_name,
                COUNT(*) AS total_events,
                SUM(event_date >= '{$today}') AS upcoming,
                SUM(event_date < '{$today}') AS past
         FROM upcoming_events
         GROUP BY station_slug, station_name
         ORDER BY total_events DESC"
    );
    $items = [];
    while ($row = $rows->fetch_assoc()) {
        $items[] = $row;
    }

    return $items;
}

function create_activity_log_table(mysqli $connection, string $engine = 'InnoDB'): void
{
    $connection->query(
        'CREATE TABLE IF NOT EXISTS activity_log (
            id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            actor_type  ENUM(\'admin\',\'staff\',\'patient\') NOT NULL,
            actor_id    VARCHAR(150) NOT NULL,
            action      VARCHAR(100) NOT NULL,
            target      VARCHAR(100) DEFAULT NULL,
            target_id   VARCHAR(50)  DEFAULT NULL,
            old_status  VARCHAR(50)  DEFAULT NULL,
            new_status  VARCHAR(50)  DEFAULT NULL,
            station_slug VARCHAR(100) DEFAULT NULL,
            created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_actor (actor_type, actor_id),
            INDEX idx_target (target, target_id),
            INDEX idx_created (created_at)
        ) ENGINE=' . $engine . ' DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci'
    );
}

function ensure_activity_log_table(mysqli $connection): void
{
    ensure_table_is_usable($connection, 'activity_log', 'create_activity_log_table');
}

function log_activity(string $actorType, string $actorId, string $action, string $target = '', string $targetId = '', string $oldStatus = '', string $newStatus = '', string $stationSlug = ''): void
{
    ensure_activity_log_table(db());
    $stmt = db()->prepare(
        'INSERT INTO activity_log (actor_type, actor_id, action, target, target_id, old_status, new_status, station_slug)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->bind_param('ssssssss', $actorType, $actorId, $action, $target, $targetId, $oldStatus, $newStatus, $stationSlug);
    $stmt->execute();
}

function fetch_activity_log(int $limit = 30, string $fromDate = '', string $toDate = ''): array
{
    ensure_activity_log_table(db());

    if ($fromDate !== '' && $toDate !== '') {
        $stmt = db()->prepare(
            'SELECT * FROM activity_log
             WHERE DATE(created_at) BETWEEN ? AND ?
             ORDER BY created_at DESC
             LIMIT ?'
        );
        $stmt->bind_param('ssi', $fromDate, $toDate, $limit);
    } else {
        $stmt = db()->prepare('SELECT * FROM activity_log ORDER BY created_at DESC LIMIT ?');
        $stmt->bind_param('i', $limit);
    }

    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function track_patient_info_change(string $patientId, string $fieldName, string $oldValue, string $newValue): void
{
    if ($oldValue === $newValue) {
        return;
    }

    $stmt = db()->prepare(
        'INSERT INTO patient_info_history (patient_id, field_name, old_value, new_value)
         VALUES (?, ?, ?, ?)'
    );
    $stmt->bind_param('ssss', $patientId, $fieldName, $oldValue, $newValue);
    $stmt->execute();
}

function create_patient_update_notification(string $patientId, string $patientName, string $fieldUpdated): void
{
    $stmt = db()->prepare(
        'INSERT INTO patient_update_notifications (patient_id, patient_name, field_updated)
         VALUES (?, ?, ?)'
    );
    $stmt->bind_param('sss', $patientId, $patientName, $fieldUpdated);
    $stmt->execute();
}

function fetch_patient_info_history(string $patientId, ?string $fieldName = null): array
{
    if ($fieldName !== null) {
        $stmt = db()->prepare(
            'SELECT * FROM patient_info_history
             WHERE patient_id = ? AND field_name = ?
             ORDER BY changed_at DESC'
        );
        $stmt->bind_param('ss', $patientId, $fieldName);
    } else {
        $stmt = db()->prepare(
            'SELECT * FROM patient_info_history
             WHERE patient_id = ?
             ORDER BY changed_at DESC'
        );
        $stmt->bind_param('s', $patientId);
    }

    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function fetch_unread_patient_notifications(): array
{
    $result = db()->query(
        'SELECT * FROM patient_update_notifications
         WHERE is_read = 0
         ORDER BY created_at DESC'
    );
    return $result->fetch_all(MYSQLI_ASSOC);
}

function mark_notification_as_read(int $notificationId): bool
{
    $stmt = db()->prepare('UPDATE patient_update_notifications SET is_read = 1 WHERE id = ?');
    $stmt->bind_param('i', $notificationId);
    return $stmt->execute();
}

function fetch_station_service_selection(string $stationSlug): array
{
    $stationSlug = strtolower(trim($stationSlug));
    $catalog = service_catalog();
    $assigned = station_program_map_with_assignments()[$stationSlug] ?? [];
    $assignedLookup = array_flip($assigned);
    $items = [];

    foreach ($catalog as $slug => $service) {
        $items[] = $service + [
            'assigned' => isset($assignedLookup[$slug]),
            'max_slots' => fetch_station_service_capacity($stationSlug, (string) $slug),
        ];
    }

    return $items;
}

function save_station_service_selection(string $stationSlug, array $serviceSlugs): bool
{
    $stationSlug = strtolower(trim($stationSlug));
    if ($stationSlug === '' || fetch_station_by_slug_catalog($stationSlug) === null) {
        return false;
    }

    $catalog = service_catalog();
    $selected = array_values(array_unique(array_filter(
        array_map(static fn($slug): string => strtolower(trim((string) $slug)), $serviceSlugs),
        static fn(string $slug): bool => isset($catalog[$slug])
    )));

    if ($selected === []) {
        return false;
    }

    $connection = db();
    $connection->begin_transaction();
    try {
        $delete = $connection->prepare('DELETE FROM station_service_assignments WHERE station_slug = ?');
        $delete->bind_param('s', $stationSlug);
        $delete->execute();

        $insert = $connection->prepare(
            'INSERT INTO station_service_assignments (station_slug, service_slug, sort_order, daily_capacity)
             VALUES (?, ?, ?, ?)'
        );
        foreach ($selected as $index => $serviceSlug) {
            $sortOrder = $index + 1;
            $cap = fetch_station_service_capacity($stationSlug, (string) $serviceSlug);
            $insert->bind_param('ssii', $stationSlug, $serviceSlug, $sortOrder, $cap);
            $insert->execute();
        }

        $connection->commit();
        return true;
    } catch (Throwable $exception) {
        $connection->rollback();
        return false;
    }
}

/**
 * Schedule a follow-up appointment date and register it as a booked appointment for the patient.
 */
function schedule_appointment_follow_up(
    int $appointmentId,
    string $followUpDate,
    string $followUpTime,
    string $followUpNotes,
    string $scheduledBy = ''
): bool {
    $connection = db();
    $appointment = fetch_appointment_by_id($appointmentId);
    if (!is_array($appointment)) {
        return false;
    }

    $stmt = $connection->prepare(
        'UPDATE appointments 
         SET follow_up_date = ?, follow_up_time = ?, follow_up_notes = ?, follow_up_set_at = NOW() 
         WHERE id = ?'
    );
    $stmt->bind_param('sssi', $followUpDate, $followUpTime, $followUpNotes, $appointmentId);
    $ok = $stmt->execute();
    if (!$ok) {
        return false;
    }

    $parentRef = (string) ($appointment['appointment_code'] ?? $appointment['reference_code'] ?? (string) $appointmentId);
    $parentTag = '[Follow-up for Appointment #' . $parentRef . ']';
    $followUpFullNotes = $followUpNotes !== '' ? $parentTag . ' ' . $followUpNotes : $parentTag;
    $timeVal = $followUpTime !== '' ? $followUpTime : 'Regular Hours';

    // Check if a linked follow-up appointment was already created for this parent consultation
    $checkStmt = $connection->prepare('SELECT id FROM appointments WHERE notes LIKE CONCAT("%", ?, "%") LIMIT 1');
    $checkStmt->bind_param('s', $parentTag);
    $checkStmt->execute();
    $existingFollowUp = $checkStmt->get_result()->fetch_assoc();

    if ($existingFollowUp && !empty($existingFollowUp['id'])) {
        $updateApptStmt = $connection->prepare(
            'UPDATE appointments 
             SET preferred_date = ?, preferred_time = ?, notes = ? 
             WHERE id = ?'
        );
        $fuId = (int) $existingFollowUp['id'];
        $updateApptStmt->bind_param('sssi', $followUpDate, $timeVal, $followUpFullNotes, $fuId);
        $updateApptStmt->execute();
    } else {
        $newRefCode = create_reference_code();
        $stationSlug = (string) ($appointment['station_slug'] ?? '');
        $serviceSlug = (string) ($appointment['service_slug'] ?? '');
        $newApptCode = create_appointment_code($stationSlug, $serviceSlug, $followUpDate) ?? random_alphanumeric_code(8);

        $patientId = (string) ($appointment['patient_id'] ?? '');
        $stationName = (string) ($appointment['station_name'] ?? 'Barangay Health Station');
        $serviceName = (string) ($appointment['service_name'] ?? 'Medical Consultation');
        $firstName = (string) ($appointment['first_name'] ?? '');
        $middleName = (string) ($appointment['middle_name'] ?? '');
        $lastName = (string) ($appointment['last_name'] ?? '');
        $birthDate = (string) ($appointment['birth_date'] ?? '2000-01-01');
        $gender = (string) ($appointment['gender'] ?? 'Not specified');
        $contactNumber = (string) ($appointment['contact_number'] ?? '');
        $email = (string) ($appointment['email'] ?? '');
        $completeAddress = (string) ($appointment['complete_address'] ?? '');
        $immRel = (string) ($appointment['immunization_relationship'] ?? '');
        $photoPath = (string) ($appointment['photo_path'] ?? '');
        $status = 'Confirmed';

        $insertStmt = $connection->prepare(
            'INSERT INTO appointments (
                reference_code, appointment_code, patient_id, 
                station_slug, station_name, service_slug, service_name, 
                first_name, middle_name, last_name, birth_date, gender, 
                contact_number, email, complete_address, immunization_relationship, 
                photo_path, preferred_date, preferred_time, notes, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $insertStmt->bind_param(
            'sssssssssssssssssssss',
            $newRefCode, $newApptCode, $patientId,
            $stationSlug, $stationName, $serviceSlug, $serviceName,
            $firstName, $middleName, $lastName, $birthDate, $gender,
            $contactNumber, $email, $completeAddress, $immRel,
            $photoPath, $followUpDate, $timeVal, $followUpFullNotes, $status
        );
        $insertStmt->execute();
    }

    // Create a patient status notification
    $refCode = (string) ($appointment['appointment_code'] ?? $appointment['reference_code'] ?? '');
    $patientId = (string) ($appointment['patient_id'] ?? '');
    if ($patientId === '') {
        $patientId = (string) ($appointment['email'] ?? $refCode);
    }
    $stationName = (string) ($appointment['station_name'] ?? 'Barangay Health Station');
    $serviceName = (string) ($appointment['service_name'] ?? 'Medical Consultation');
    $formattedDate = date('F j, Y', strtotime($followUpDate));
    
    $notifMsg = "Follow-up Check-up Booked: You have a confirmed follow-up consultation for {$serviceName} at {$stationName} on {$formattedDate}" . ($followUpTime !== '' ? " ({$followUpTime})" : "") . "." . ($followUpNotes !== '' ? " Reason / Notes: {$followUpNotes}" : "");

    try {
        $nStmt = $connection->prepare(
            'INSERT INTO ' . DB_TABLE_APPOINTMENT_NOTIFICATIONS . ' 
             (appointment_id, reference_code, patient_id, status, message, is_read) 
             VALUES (?, ?, ?, "Follow-up", ?, 0)'
        );
        $nStmt->bind_param('isss', $appointmentId, $refCode, $patientId, $notifMsg);
        $nStmt->execute();
    } catch (Throwable $e) {}

    return true;
}

/**
 * Fetch notifications for a patient.
 */
function fetch_patient_appointment_notifications(string $patientId, string $patientEmail = '', string $patientName = ''): array
{
    $connection = db();
    $notifications = [];

    try {
        $pId = trim($patientId);
        $pEmail = trim($patientEmail);

        $sql = 'SELECT n.*, a.station_name, a.service_name, a.preferred_date, a.follow_up_date, a.follow_up_time, a.follow_up_notes, a.first_name, a.last_name 
                FROM ' . DB_TABLE_APPOINTMENT_NOTIFICATIONS . ' n
                LEFT JOIN appointments a ON a.id = n.appointment_id
                WHERE n.patient_id = ? OR (n.patient_id = ? AND ? != "") OR a.patient_id = ? OR (a.email = ? AND ? != "")
                ORDER BY n.id DESC LIMIT 30';
        $stmt = $connection->prepare($sql);
        $stmt->bind_param('ssssss', $pId, $pEmail, $pEmail, $pId, $pEmail, $pEmail);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $notifications[] = $row;
        }
    } catch (Throwable $e) {}

    return $notifications;
}

/**
 * Fetch upcoming active follow-ups for a patient.
 */
function fetch_patient_upcoming_follow_ups(string $patientId, string $patientEmail = '', string $patientName = ''): array
{
    $connection = db();
    $followUps = [];

    try {
        $pId = trim($patientId);
        $pEmail = trim($patientEmail);

        $sql = 'SELECT * FROM appointments 
                WHERE (patient_id = ? OR (email = ? AND ? != "") OR (? != "" AND CONCAT(first_name, " ", last_name) LIKE ?))
                  AND follow_up_date IS NOT NULL 
                  AND follow_up_date >= CURDATE()
                ORDER BY follow_up_date ASC';
        $stmt = $connection->prepare($sql);
        $likeName = '%' . trim($patientName) . '%';
        $stmt->bind_param('sssss', $pId, $pEmail, $pEmail, $patientName, $likeName);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $followUps[] = $row;
        }
    } catch (Throwable $e) {}

    return $followUps;
}

/**
 * Fetch all booked appointments for a patient.
 */
function fetch_patient_appointments(string $patientId, string $patientEmail = '', string $patientName = ''): array
{
    $connection = db();
    $appointments = [];

    try {
        $pId = trim($patientId);
        $pEmail = trim($patientEmail);
        $pName = trim($patientName);

        $sql = 'SELECT * FROM appointments 
                WHERE (patient_id = ? OR (email = ? AND ? != "") OR (? != "" AND CONCAT(first_name, " ", last_name) LIKE ?))
                ORDER BY created_at DESC, id DESC';
        $stmt = $connection->prepare($sql);
        $likeName = '%' . $pName . '%';
        $stmt->bind_param('sssss', $pId, $pEmail, $pEmail, $pName, $likeName);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $appointments[] = $row;
        }
    } catch (Throwable $e) {}

    return $appointments;
}

/**
 * Mark a patient appointment notification as read.
 */
function mark_appointment_notification_read(int $notificationId): bool
{
    $connection = db();
    try {
        $stmt = $connection->prepare('UPDATE ' . DB_TABLE_APPOINTMENT_NOTIFICATIONS . ' SET is_read = 1 WHERE id = ?');
        $stmt->bind_param('i', $notificationId);
        return $stmt->execute();
    } catch (Throwable $e) {
        return false;
    }
}

/**
 * Fetch patient account record by email or patient ID with fallback.
 */
function fetch_patient_account_by_email(string $identifier): ?array
{
    $identifier = trim($identifier);
    if ($identifier === '') {
        return null;
    }

    $connection = db();
    try {
        $stmt = $connection->prepare(
            'SELECT * FROM ' . DB_TABLE_PATIENT_ACCOUNTS . ' 
             WHERE email = ? OR patient_id = ? 
             LIMIT 1'
        );
        $stmt->bind_param('ss', $identifier, $identifier);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        if (is_array($row)) {
            return $row;
        }
    } catch (Throwable $e) {}

    // Fallback lookup in appointments table
    try {
        $stmtAppt = $connection->prepare(
            'SELECT * FROM appointments 
             WHERE email = ? OR patient_id = ? OR appointment_code = ? 
             ORDER BY id DESC LIMIT 1'
        );
        $stmtAppt->bind_param('sss', $identifier, $identifier, $identifier);
        $stmtAppt->execute();
        $resAppt = $stmtAppt->get_result();
        $rowAppt = $resAppt->fetch_assoc();
        if (is_array($rowAppt)) {
            return [
                'id' => $rowAppt['id'],
                'patient_id' => (string) ($rowAppt['patient_id'] ?: ('PID' . $rowAppt['id'])),
                'email' => (string) ($rowAppt['email'] ?: $identifier),
                'password_hash' => password_hash('patient123', PASSWORD_DEFAULT),
                'first_name' => (string) $rowAppt['first_name'],
                'middle_name' => (string) ($rowAppt['middle_name'] ?? ''),
                'last_name' => (string) $rowAppt['last_name'],
                'birth_date' => (string) ($rowAppt['birth_date'] ?? ''),
                'gender' => (string) ($rowAppt['gender'] ?? ''),
                'contact_number' => (string) ($rowAppt['contact_number'] ?? ''),
                'complete_address' => (string) ($rowAppt['complete_address'] ?? ''),
                'station_slug' => (string) ($rowAppt['station_slug'] ?? ''),
                'station_name' => (string) ($rowAppt['station_name'] ?? ''),
            ];
        }
    } catch (Throwable $e) {}

    return null;
}

/**
 * Save or update a patient account record in patient_accounts table.
 */
function save_patient_account(array $data): bool
{
    $connection = db();
    $patientId = trim((string) ($data['patient_id'] ?? ''));
    $email = strtolower(trim((string) ($data['email'] ?? '')));
    $password = (string) ($data['password'] ?? '');
    $passHash = (string) ($data['password_hash'] ?? '');
    if ($passHash === '' && $password !== '') {
        $passHash = password_hash($password, PASSWORD_DEFAULT);
    } elseif ($passHash === '') {
        $passHash = password_hash('patient123', PASSWORD_DEFAULT);
    }

    $firstName = trim((string) ($data['first_name'] ?? ''));
    $middleName = trim((string) ($data['middle_name'] ?? ''));
    $lastName = trim((string) ($data['last_name'] ?? ''));
    $birthDate = trim((string) ($data['birth_date'] ?? $data['birthdate'] ?? ''));
    if ($birthDate === '') {
        $birthDate = '2000-01-01';
    }
    $gender = trim((string) ($data['gender'] ?? ''));
    $contactNumber = trim((string) ($data['contact_number'] ?? $data['phone'] ?? ''));
    $completeAddress = trim((string) ($data['complete_address'] ?? ''));
    $stationSlug = trim((string) ($data['station_slug'] ?? ''));
    $stationName = trim((string) ($data['station_name'] ?? ''));

    if ($patientId === '') {
        $patientId = strtoupper(substr(md5($email . time()), 0, 6));
    }

    try {
        $stmt = $connection->prepare(
            'INSERT INTO ' . DB_TABLE_PATIENT_ACCOUNTS . ' 
             (patient_id, email, password_hash, first_name, middle_name, last_name, birth_date, gender, contact_number, complete_address, station_slug, station_name) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE 
                password_hash = VALUES(password_hash),
                first_name = VALUES(first_name),
                middle_name = VALUES(middle_name),
                last_name = VALUES(last_name),
                birth_date = VALUES(birth_date),
                gender = VALUES(gender),
                contact_number = VALUES(contact_number),
                complete_address = VALUES(complete_address),
                station_slug = VALUES(station_slug),
                station_name = VALUES(station_name)'
        );
        $stmt->bind_param(
            'ssssssssssss',
            $patientId,
            $email,
            $passHash,
            $firstName,
            $middleName,
            $lastName,
            $birthDate,
            $gender,
            $contactNumber,
            $completeAddress,
            $stationSlug,
            $stationName
        );
        return $stmt->execute();
    } catch (Throwable $e) {
        return false;
    }
}

/**
 * Automatically identifies and records unattended appointments and unserved queue records.
 * - Unattended Appointments: status = 'Pending' AND preferred_date < CURDATE()
 * - Unattended Queue: status IN ('Confirmed', 'Serving') AND preferred_date < CURDATE()
 */
function sync_unattended_records(string $stationSlug = ''): array
{
    $connection = db();
    $today = date('Y-m-d');
    $stationFilter = '';
    if ($stationSlug !== '') {
        $safeSlug = $connection->real_escape_string($stationSlug);
        $stationFilter = " AND station_slug = '{$safeSlug}'";
    }

    $syncedAppointments = 0;
    $syncedQueue = 0;

    // 1. Sync Unattended Appointments (Pending appointments past their date)
    $pendingQuery = "
        SELECT * FROM " . DB_TABLE_APPOINTMENTS . "
        WHERE status = 'Pending' AND preferred_date < '{$today}' {$stationFilter}
    ";
    $pendingRes = $connection->query($pendingQuery);

    if ($pendingRes instanceof mysqli_result) {
        while ($row = $pendingRes->fetch_assoc()) {
            $stmt = $connection->prepare("
                INSERT INTO " . DB_TABLE_UNATTENDED_APPOINTMENTS . " (
                    appointment_id, reference_code, appointment_code, patient_id,
                    station_slug, station_name, service_slug, service_name,
                    first_name, middle_name, last_name, birth_date, gender,
                    contact_number, email, complete_address, preferred_date,
                    preferred_time, notes, original_status, reason_unattended
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    reason_unattended = VALUES(reason_unattended),
                    updated_at = CURRENT_TIMESTAMP
            ");
            $apptId = (int) $row['id'];
            $refCode = (string) $row['reference_code'];
            $apptCode = (string) ($row['appointment_code'] ?? '');
            $patId = (string) ($row['patient_id'] ?? '');
            $stnSlug = (string) $row['station_slug'];
            $stnName = (string) $row['station_name'];
            $svcSlug = (string) $row['service_slug'];
            $svcName = (string) $row['service_name'];
            $fName = (string) $row['first_name'];
            $mName = (string) ($row['middle_name'] ?? '');
            $lName = (string) $row['last_name'];
            $bDate = (string) $row['birth_date'];
            $gender = (string) $row['gender'];
            $phone = (string) $row['contact_number'];
            $email = (string) ($row['email'] ?? '');
            $address = (string) $row['complete_address'];
            $prefDate = (string) $row['preferred_date'];
            $prefTime = (string) $row['preferred_time'];
            $notes = (string) ($row['notes'] ?? '');
            $origStatus = (string) $row['status'];
            $reason = 'Staff unconfirmed prior to date';

            $stmt->bind_param(
                'issssssssssssssssssss',
                $apptId, $refCode, $apptCode, $patId,
                $stnSlug, $stnName, $svcSlug, $svcName,
                $fName, $mName, $lName, $bDate, $gender,
                $phone, $email, $address, $prefDate,
                $prefTime, $notes, $origStatus, $reason
            );
            $stmt->execute();
            $syncedAppointments++;
        }
    }

    // 2. Sync Unattended Queue (Confirmed / Serving appointments past their date)
    $queueQuery = "
        SELECT * FROM " . DB_TABLE_APPOINTMENTS . "
        WHERE status IN ('Confirmed', 'Serving') AND preferred_date < '{$today}' {$stationFilter}
    ";
    $queueRes = $connection->query($queueQuery);

    if ($queueRes instanceof mysqli_result) {
        while ($row = $queueRes->fetch_assoc()) {
            $stmt = $connection->prepare("
                INSERT INTO " . DB_TABLE_UNATTENDED_QUEUE . " (
                    appointment_id, reference_code, appointment_code, patient_id,
                    station_slug, station_name, service_slug, service_name,
                    first_name, middle_name, last_name, birth_date, gender,
                    contact_number, email, complete_address, preferred_date,
                    preferred_time, photo_path, notes, original_status, reason_unattended
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    reason_unattended = VALUES(reason_unattended),
                    photo_path = VALUES(photo_path),
                    updated_at = CURRENT_TIMESTAMP
            ");
            $apptId = (int) $row['id'];
            $refCode = (string) $row['reference_code'];
            $apptCode = (string) ($row['appointment_code'] ?? '');
            $patId = (string) ($row['patient_id'] ?? '');
            $stnSlug = (string) $row['station_slug'];
            $stnName = (string) $row['station_name'];
            $svcSlug = (string) $row['service_slug'];
            $svcName = (string) $row['service_name'];
            $fName = (string) $row['first_name'];
            $mName = (string) ($row['middle_name'] ?? '');
            $lName = (string) $row['last_name'];
            $bDate = (string) $row['birth_date'];
            $gender = (string) $row['gender'];
            $phone = (string) $row['contact_number'];
            $email = (string) ($row['email'] ?? '');
            $address = (string) $row['complete_address'];
            $prefDate = (string) $row['preferred_date'];
            $prefTime = (string) $row['preferred_time'];
            $photoPath = (string) ($row['photo_path'] ?? '');
            $notes = (string) ($row['notes'] ?? '');
            $origStatus = (string) $row['status'];
            $reason = 'Patient did not show up / Left unserved in queue';

            $stmt->bind_param(
                'isssssssssssssssssssss',
                $apptId, $refCode, $apptCode, $patId,
                $stnSlug, $stnName, $svcSlug, $svcName,
                $fName, $mName, $lName, $bDate, $gender,
                $phone, $email, $address, $prefDate,
                $prefTime, $photoPath, $notes, $origStatus, $reason
            );
            $stmt->execute();
            $syncedQueue++;
        }
    }

    return [
        'unattended_appointments' => $syncedAppointments,
        'unattended_queue' => $syncedQueue,
    ];
}

function fetch_unattended_appointments(array $filters = []): array
{
    $connection = db();
    $where = [];

    if (!empty($filters['station_slug'])) {
        $safeSlug = $connection->real_escape_string((string) $filters['station_slug']);
        $where[] = "station_slug = '{$safeSlug}'";
    }

    if (!empty($filters['date'])) {
        $safeDate = $connection->real_escape_string((string) $filters['date']);
        $where[] = "preferred_date = '{$safeDate}'";
    }

    if (!empty($filters['search'])) {
        $search = $connection->real_escape_string((string) $filters['search']);
        $where[] = "(first_name LIKE '%{$search}%' OR last_name LIKE '%{$search}%' OR reference_code LIKE '%{$search}%' OR appointment_code LIKE '%{$search}%' OR contact_number LIKE '%{$search}%' OR service_name LIKE '%{$search}%')";
    }

    $sql = "SELECT * FROM " . DB_TABLE_UNATTENDED_APPOINTMENTS;
    if ($where !== []) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }
    $sql .= " ORDER BY preferred_date DESC, id DESC";

    $result = $connection->query($sql);
    $rows = [];
    if ($result instanceof mysqli_result) {
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
    }
    return $rows;
}

function fetch_unattended_queue(array $filters = []): array
{
    $connection = db();
    $where = [];

    if (!empty($filters['station_slug'])) {
        $safeSlug = $connection->real_escape_string((string) $filters['station_slug']);
        $where[] = "station_slug = '{$safeSlug}'";
    }

    if (!empty($filters['date'])) {
        $safeDate = $connection->real_escape_string((string) $filters['date']);
        $where[] = "preferred_date = '{$safeDate}'";
    }

    if (!empty($filters['search'])) {
        $search = $connection->real_escape_string((string) $filters['search']);
        $where[] = "(first_name LIKE '%{$search}%' OR last_name LIKE '%{$search}%' OR reference_code LIKE '%{$search}%' OR appointment_code LIKE '%{$search}%' OR contact_number LIKE '%{$search}%' OR service_name LIKE '%{$search}%')";
    }

    $sql = "SELECT * FROM " . DB_TABLE_UNATTENDED_QUEUE;
    if ($where !== []) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }
    $sql .= " ORDER BY preferred_date DESC, id DESC";

    $result = $connection->query($sql);
    $rows = [];
    if ($result instanceof mysqli_result) {
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
    }
    return $rows;
}

function count_unattended_records(string $stationSlug = ''): array
{
    $connection = db();
    $filter = '';
    if ($stationSlug !== '') {
        $safeSlug = $connection->real_escape_string($stationSlug);
        $filter = " WHERE station_slug = '{$safeSlug}'";
    }

    $apptsCount = 0;
    $queueCount = 0;

    $res1 = $connection->query("SELECT COUNT(*) AS total FROM " . DB_TABLE_UNATTENDED_APPOINTMENTS . $filter);
    if ($res1 instanceof mysqli_result && ($r1 = $res1->fetch_assoc())) {
        $apptsCount = (int) $r1['total'];
    }

    $res2 = $connection->query("SELECT COUNT(*) AS total FROM " . DB_TABLE_UNATTENDED_QUEUE . $filter);
    if ($res2 instanceof mysqli_result && ($r2 = $res2->fetch_assoc())) {
        $queueCount = (int) $r2['total'];
    }

    return [
        'appointments' => $apptsCount,
        'queue' => $queueCount,
        'total' => $apptsCount + $queueCount,
    ];
}


