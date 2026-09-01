<?php

declare(strict_types=1);

require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';

$isLoggedIn = isset($_SESSION['patient_id']) && $_SESSION['patient_id'] !== '';
$hasServiceOrBarangay = !empty($_GET['barangay']) || !empty($_GET['service']) || !empty($_GET['confirmation']) || !empty($_GET['lookup_patient_id']);
if ($isLoggedIn && !$hasServiceOrBarangay) {
    header('Location: dashboard.php');
    exit;
}

$contact = contact_details();
$serviceCatalog = service_catalog();
$stationPrograms = station_program_map();
$stations = station_catalog();
$publicStations = array_values(array_filter(
    $stations,
    static fn(array $station): bool => (string) $station['slug'] !== 'city-health'
));
$barangayOptions = array_map(
    static fn(array $station): string => (string) $station['barangay'],
    $publicStations
);
$purokOptionsByBarangay = [
    'Alijis' => ['Accco Housing','Bayanihan','Celita Village','Dc 1 (Phases 1)','Dc 1 (Phases 2)','Dc 1 (Phases 3)','Dc 2 Rphs','Dc 3 Rphs','Ecc Villas','Gaisano','Himaya','Katilingban','Lote','Maanyag','Maanyag 1','Mahigugma-on','Mainuswagon','Malapitan','Malipayon','Masagana','Mildred Homes','Mt. Carmel Subdivision, Nature\'s','Olympia Village','Pag-asa, Paghida-et','Paghigugma, Progresso','Puentebella','Roadside 1','Roadside 2','Sambag Dubai','Sambag Tinago','San Jose','Score - Paghab','St. Vincent Homes','Torrecampo','Villa Baradas','Daalco Subdivision','Guadalupe Subdivision'],
    'Bata' => ['Sunriser','Maaliwanay','Kametal','Pepsi','Riverside','Masinadyahon','Mahimaya-on','Marapara I','Marapara II','Bayabasan','Tunay','Pag-isa','Sawmill I','Sawmill II','Sawmill III','Andan','Villagracia','Pinetree','Kamunsil','Katilingban','Mainuswagon','Magbinuligay','Sto. Rosario'],
    'Cabug' => ['Bougainvilla','Busay','Golden Rosary','Gumamela','Ipil-Ipil','Kabugwason','Kalayogan','Katipunan','Kawayanan','Lechonan','Lemon Grass','Linya','Madinalag-on','Mainuswagon','Malipayon','Monico Ville','Prosperville','Ilaya','Relota Ville','Rosal','Rosas Pandan','Rose','Santan','Torrecampo','Villa Guillena'],
    'Estefania' => ['Arabay 1','Arabay 2','Arao','Bagong Silang','Bethany Court','Buena Royale','Camelot Residences','Camelot Village','Camingawan Proper','Capitol Hills Subdivision','Celine Homes Subdivision','City Ville Subdivision','Country Homes Subdivision Phase 1','Country Homes Subdivision Phase 2','Country Homes Subdivision Phase 3','East Homes 1','East Homes 2','East Homes 3','Elsa','Escuerdo','Estefania Proper','Flora','Fortune Towne -B','Fortune Towne Subdivision','Glenwood Residences','Goldah','Greensville 1 Subdivision','Greensville 4 Subdivision','Jesusa Heights Subdivision','Kaburihan','Kasoy','La Herencia','Lopues Village','Luisville Subdivision','Mayang','Meadows Of Camelot','Pag-asa','Paho 1','Paho 2','Paraiso','Pedring','Pequiño','Providence Negros','Sagrado 2','Sambag','Sunshine Valley Subdivision','The Palisades','Villa Alexandra 1','Villa Alexandra 2','Villa Angeles','Villa Estefania','Villa Felicidad','Villamar','Villa Soledad','Villa Villeta'],
    'Handumanan' => ['Purok (Zone) 1','Purok (Zone) 2','Purok (Zone) 3','Purok (Zone) 4','Purok (Zone) 5','Purok (Zone) 6','Purok (Zone) 7','Purok (Zone) 8','Purok (Zone) 9','Purok (Zone) 10','Purok (Zone) 11','Purok (Zone) 12','Purok Cadena De Amor','Purok Ceres','Purok Chico','Purok Datiles','Purok Gk','Purok Golden Rosary','Purok Kawayanan 1','Purok Kawayanan 2','Purok Lubi','Purok Lucky Homes','Purok Mabinuligon','Purok Mahogany','Purok Maniville','Purok Narra','Purok Ngo Village','Purok Paghida-et','Purok Paho','Purok Rosebell','Purok San Antonio','Purok San Roque 1','Purok San Roque 2','Purok Saturn Village','Purok St. Ezekiel Moreno','Purok Sto. Domingo','Purok Sto. Nino','Purok Tapulanga Hills','Purok Villasor Village'],
    'Mandalagan' => ['Active','Bulak','Kaburihan','Luhod-Luhod','Sambag','Santol','Trese','Tuburan','Yanson 1','Yanson 2'],
    'Mansilingan' => ['Arceo','Cabalagnan','Carmenville','Encarnacion','Forest Hills','Fortaleza','Gonzaga','Grandville 1','Grandville 2','Grandville 3','Guanzon','Hermelinda','Hillside','Himaya','Jj Gonzaga','Kabugwason','Kahirup A','Kahirup B','Kasilingan 1','Kasilingan 2','Katilingban','Lasalleville','Laurel','Leonville','Lolita Heights','Lupa','Mabinuligon','Maghili-ugyon','Manayaosayao','Matahum','Paghidaet','Paglaum','Paglaum Village','Paraiso','Punay','St. Benilde','Unor Ville'],
    'Pahanocoy' => ['Acacia 1','Acacia 2','Bantud','Firmville','Florenceville','Gold Medal','Hanapbuhay','Mabinuligon','Maghimulat','Maghirupay','Mahigugmaon','Maninihon','Manville Executive Homes','Masinadyahon','Nha 1','Nha 2','Nha 3','Nha 4','Paho','Pta Balas North','Rc','Sp Village','Sta. Antonia','Sto. Niño','Villa Lourdes'],
    'Singcang' => ['Batad','Cadena De Amor','Villa Servando','Neptune','Kaingin','Mars','Sigay','Talaba','Sisi','Grasya','Kabulakan I','Greenplains','Lamperong','Magbinuligay','Ipil-Ipil','San Jose','Sampaguita','Mangga','Santol','Riverside','Tambi Palad','Malipayon','Masanag','Mahigugmaon','Mahinangpon','Mahayhay','Masagana','Katilingban','Paghida-et','Pag-asa','Narra','Molave','Mabolo','Acacia','Chico','Yanson'],
    'Sum-Ag' => ['Purok A.C. Yulo','Purok Brotherhood','Purok Candelaria','Purok Kaisahan','Purok Kbs','Purok Mabinuligon','Purok Masagana','Purok Naminami','Purok Providence','Purok Riverside','Purok San Antonio','Purok San Luis','Purok Sto. Niño','Purok Villa Milagrosa'],
    'Taculing' => ['Bayanihan','B.M.','Cinco','Cory I','Cosmos','Gonzaga','Jardine','Jocson','Kabukira','Kawilihan','Lirio','Malinong','Malipayon','Masagana','Nabali-an','Pagla-um','Paho','Planeta','Progreso','Rio Vista','Riverside','Rosal','Rosas','Santan','Sunflower','Sunrise','Tapulanga','Violeta'],
    'Villamonte' => ['Sabes','Cabachawan','Hervias III','Bayanihan','Goopio','Gonzaga West','Gonzaga East','Purok 7','Consuelo','Cubay','Bugnay','Hervias II','Calantas','Sulom II','Riverbank','Medalla Milagrosa','Pagkakaisa','Banaue','Isla','Gugma','Akishola','Herba Buena','Purok 17','Taal','Amor','Purok 18','Dahlia','Purok 2','Purok Himaya','Purok 5','Samfloma','Purok 15','Purok 16','Sunflower','Mainuwagon','Malvar Cubay'],
    'Villa Esperanza' => [],
    'Vista Alegre' => ['Katilingban','Kawayanan','Kabulakan','Kabutongan','Busay','Inday Oya','Noli Garcia','Kabuguason','Angela Gonzaga','Ff Gonzaga','Kalubihan','Star Apple','Kasantolan 1','Kasantolan 2','Progreso Village I Zone 1','Progreso Village I Zone 2','Progreso Village I Zone 3','Progreso Village I Zone 4','Progreso Village I Zone 5','Progreso Village II','Kapisan','Villa Otto','Villa Nena','Pablo Torre','Lopez'],
];
$events = array_map(
    static function (array $event): array {
        $startTime = trim((string) ($event['time_label'] ?? ''));
        $endTime = trim((string) ($event['end_time_label'] ?? ''));
        $timeDisplay = $startTime;
        if ($endTime !== '' && $endTime !== $startTime && stripos($startTime, '-') === false) {
            $timeDisplay = $startTime . ' - ' . $endTime;
        }
        return [
            'icon' => $event['icon'] ?? 'calendar',
            'title' => $event['title'],
            'station' => $event['station_name'],
            'barangay' => $event['station_slug'],
            'description' => $event['description'],
            'date' => date('F j, Y', strtotime((string) $event['event_date'])),
            'time' => $timeDisplay,
            'accent' => $event['accent'] ?? 'mint',
        ];
    },
    array_values(array_filter(
        fetch_upcoming_events(['upcoming_only' => true]),
        static fn(array $event): bool => (string) ($event['station_slug'] ?? '') !== 'city-health'
    ))
);

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function iconSvg(string $name): string
{
    $icons = [
        'heart' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'syringe' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m14 4 6 6M5 19l7.5-7.5m-3-3L17 16m-9 5-3 0 0-3 9-9 3 3-9 9Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'community' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm8 2a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM3 19a5 5 0 0 1 10 0m3 0v-1a4 4 0 0 1 5 4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'baby' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9.5 7.5c0-1.66 1.34-3 3-3 1.1 0 2.07.6 2.59 1.49M8.5 15a4.5 4.5 0 1 0 8.99 0A4.5 4.5 0 0 0 8.5 15Zm2-1h.01m4.98 0h.01M11 17c.35.48.91.8 1.5.8s1.15-.32 1.5-.8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'phone' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 5.15 11.8 19.79 19.79 0 0 1 2.08 3.12 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'map' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s-6-5.33-6-11a6 6 0 1 1 12 0c0 5.67-6 11-6 11Zm0-8.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'calendar' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 2v4m8-4v4M3 10h18M5 5h14a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'sparkle' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 3 1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8L12 3Zm7 10 .8 2.2L22 16l-2.2.8L19 19l-.8-2.2L16 16l2.2-.8L19 13ZM5 14l.8 2.2L8 17l-2.2.8L5 20l-.8-2.2L2 17l2.2-.8L5 14Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'arrow' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14m-5-5 5 5-5 5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'arrow-right' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14m-5-5 5 5-5 5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'clock' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 8v5l3 2m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'pulse' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12h4l2.2-5 3.6 10 2.6-5H21" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'stethoscope' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4.8 2.3A.3.3 0 1 0 5 2H4a2 2 0 0 0-2 2v5a6 6 0 0 0 6 6v0a6 6 0 0 0 6-6V4a2 2 0 0 0-2-2h-1a.2.2 0 1 0 .3.3" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M8 15v1a6 6 0 0 0 6 6v0a6 6 0 0 0 6-6v-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="20" cy="10" r="2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'cube' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Zm0 0v18m8-13.5-8 4.5-8-4.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'capsule' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m10.5 20.5-7-7a4.95 4.95 0 0 1 7-7l7 7a4.95 4.95 0 0 1-7 7Zm-3-10 9 9" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'chevron-left' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'user' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="7" r="4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'user-plus' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="9" cy="7" r="4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M19 8v6m3-3h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'log-in' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><polyline points="10 17 15 12 10 7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><line x1="15" y1="12" x2="3" y2="12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'camera' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h4l2-2h4l2 2h4v12H4V7Zm8 9a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'check-circle' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 12l2 2 4-4m7-1a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'success-mark' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 12a8 8 0 1 1-2.34-5.66" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/><path d="m9.5 12.5 2.2 2.2 5-5.4" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'mail' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16v12H4V6Zm0 0 8 6 8-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'home' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 10.5 12 3l9 7.5V21H3V10.5Zm6 10v-6h6v6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'shield' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'eye' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Zm10 3a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'edit' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m4 20 4.5-1 9.5-9.5-3.5-3.5L5 15.5 4 20Zm11-13 3.5 3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'save' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2ZM7 3v6h8M7 21v-8h10v8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'close' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>'
    ];

    return $icons[$name] ?? '';
}

function fullName(array $appointment): string
{
    $parts = [
        trim((string) ($appointment['first_name'] ?? '')),
        trim((string) ($appointment['middle_name'] ?? '')),
        trim((string) ($appointment['last_name'] ?? '')),
    ];

    return trim(implode(' ', array_filter($parts)));
}

$selectedSlug = isset($_GET['barangay']) ? strtolower(trim((string) $_GET['barangay'])) : '';
$selectedServiceSlug = isset($_GET['service']) ? strtolower(trim((string) $_GET['service'])) : '';
$confirmationRef = trim((string) ($_GET['confirmation'] ?? ''));
$selectedStation = null;
$selectedProgram = null;
$confirmedAppointment = $confirmationRef !== '' ? fetch_appointment_by_reference($confirmationRef) : null;
$availabilityData = null;
$errors = [];
$profileUpdateMessage = (string) ($_GET['profile_update'] ?? '');
$formData = [
    'patient_id_number' => '',
    'first_name' => '',
    'middle_name' => '',
    'last_name' => '',
    'birth_date' => '',
    'gender' => '',
    'contact_number' => '',
    'email' => '',
    'complete_address' => '',
    'immunization_relationship' => '',
    'preferred_date' => '',
    'preferred_time' => '',
    'notes' => '',
];

if ($isLoggedIn) {
    $patientId = (string) $_SESSION['patient_id'];
    $formData['patient_id_number'] = $patientId;

    $patientAccount = null;
    try {
        $stmt = db()->prepare('SELECT * FROM patient_accounts WHERE patient_id = ? OR email = ? LIMIT 1');
        $patientEmail = (string) ($_SESSION['patient_email'] ?? '');
        $stmt->bind_param('ss', $patientId, $patientEmail);
        $stmt->execute();
        $patientAccount = $stmt->get_result()->fetch_assoc();
    } catch (Throwable $e) {}

    $patientProfile = fetch_patient_profile_by_patient_id($patientId);

    $sessionName = (string) ($_SESSION['patient_name'] ?? '');
    $nameParts = explode(' ', $sessionName);
    $defFirst = $nameParts[0] ?? '';
    $defLast = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : '';

    $formData['first_name'] = (string) ($_SESSION['patient_first_name'] ?? $patientAccount['first_name'] ?? $patientProfile['first_name'] ?? $defFirst);
    $formData['middle_name'] = (string) ($_SESSION['patient_middle_name'] ?? $patientAccount['middle_name'] ?? $patientProfile['middle_name'] ?? '');
    $formData['last_name'] = (string) ($_SESSION['patient_last_name'] ?? $patientAccount['last_name'] ?? $patientProfile['last_name'] ?? $defLast);
    $formData['birth_date'] = (string) ($_SESSION['patient_birth_date'] ?? $patientAccount['birth_date'] ?? $patientProfile['birth_date'] ?? '');
    $formData['gender'] = (string) ($_SESSION['patient_gender'] ?? $patientAccount['gender'] ?? $patientProfile['gender'] ?? '');
    $formData['contact_number'] = (string) ($_SESSION['patient_contact_number'] ?? $patientAccount['contact_number'] ?? $patientProfile['contact_number'] ?? '');
    $formData['email'] = (string) ($_SESSION['patient_email'] ?? $patientAccount['email'] ?? $patientProfile['email'] ?? '');
    $formData['complete_address'] = (string) ($_SESSION['patient_complete_address'] ?? $patientAccount['complete_address'] ?? $patientProfile['complete_address'] ?? '');
}

foreach ($publicStations as $station) {
    if ($station['slug'] === $selectedSlug) {
        $selectedStation = $station;
        break;
    }
}

if ($selectedStation !== null && $selectedServiceSlug !== '') {
    foreach ($selectedStation['programs'] as $program) {
        if ($program['slug'] === $selectedServiceSlug) {
            $selectedProgram = $program;
            break;
        }
    }
}

if ($selectedStation !== null && $selectedProgram !== null) {
    $availabilityData = fetch_station_service_availability($selectedStation['slug'], $selectedProgram['slug']);
}

if (isset($_GET['lookup_patient_id'])) {
    header('Content-Type: application/json; charset=UTF-8');
    $patientId = (string) $_GET['lookup_patient_id'];
    $profile = fetch_patient_profile_by_patient_id($patientId);

    if ($profile === null) {
        http_response_code(404);
        echo json_encode(['found' => false, 'message' => 'Patient ID not found.'], JSON_THROW_ON_ERROR);
    } else {
        $previousImmunizationRelationship = fetch_previous_immunization_relationship($patientId);
        echo json_encode([
            'found' => true,
            'profile' => [
                'patient_id' => (string) $profile['patient_id'],
                'first_name' => (string) $profile['first_name'],
                'middle_name' => (string) $profile['middle_name'],
                'last_name' => (string) $profile['last_name'],
                'birth_date' => (string) $profile['birth_date'],
                'gender' => (string) $profile['gender'],
                'contact_number' => (string) $profile['contact_number'],
                'email' => (string) $profile['email'],
                'complete_address' => (string) $profile['complete_address'],
                'previous_immunization_relationship' => $previousImmunizationRelationship,
            ],
        ], JSON_THROW_ON_ERROR);
    }
    exit;
}

$isConfirmationPage = is_array($confirmedAppointment);
$isBookingPage = $selectedStation !== null && $selectedProgram !== null && !$isConfirmationPage;
$isDetailPage = $selectedStation !== null && !$isBookingPage && !$isConfirmationPage;
$pageTitle = 'Bacolod Barangay Health Stations';

if ($isConfirmationPage) {
    $pageTitle = 'Booking Confirmed';
} elseif ($isBookingPage) {
    $pageTitle = $selectedProgram['title'] . ' | ' . $selectedStation['name'];
} elseif ($isDetailPage) {
    $pageTitle = $selectedStation['name'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (($_POST['action'] ?? '') === 'update_patient_profile')) {
    $patientId = trim((string) ($_POST['patient_id_number'] ?? ''));
    $updateData = [];
    foreach (['first_name', 'middle_name', 'last_name', 'birth_date', 'gender', 'contact_number', 'email'] as $field) {
        $updateData[$field] = trim((string) ($_POST[$field] ?? ''));
    }

    $purokVal = trim((string) ($_POST['purok'] ?? ''));
    $barangayVal = trim((string) ($_POST['address_barangay'] ?? ''));
    $remainderVal = trim((string) ($_POST['address_remainder'] ?? ''));
    $addressParts = array_values(array_filter([$barangayVal, $purokVal, $remainderVal]));
    if ($addressParts !== []) {
        $addressParts[] = 'Bacolod City';
    }
    $updateData['complete_address'] = implode(', ', $addressParts);

    $profileUpdated = $patientId !== '' && update_patient_profile_info($patientId, $updateData);

    $acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';
    if (stripos($acceptHeader, 'application/json') !== false) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'success' => $profileUpdated,
            'message' => $profileUpdated ? 'Your information has been updated for future bookings.' : 'Unable to update your information. Please check the required fields.',
        ], JSON_THROW_ON_ERROR);
        exit;
    }

    $redirect = '?barangay=' . urlencode((string) ($selectedStation['slug'] ?? '')) . '&service=' . urlencode((string) ($selectedProgram['slug'] ?? '')) . '&profile_update=' . ($profileUpdated ? 'saved' : 'failed');
    header('Location: ' . $redirect);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $selectedStation !== null && $selectedProgram !== null && (($_POST['action'] ?? 'book_appointment') === 'book_appointment')) {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Security token invalid or expired. Please refresh the page and try again.';
    } else {
    foreach ($formData as $key => $value) {
        $formData[$key] = trim((string) ($_POST[$key] ?? ''));
    }

    // Combine address parts into complete_address
    $purokVal = trim((string) ($_POST['purok'] ?? ''));
    $barangayVal = trim((string) ($_POST['address_barangay'] ?? ''));
    $remainderVal = trim((string) ($_POST['address_remainder'] ?? ''));
    $addressParts = array_values(array_filter([$barangayVal, $purokVal, $remainderVal]));
    if ($addressParts !== []) {
        $addressParts[] = 'Bacolod City';
    }
    $formData['complete_address'] = implode(', ', $addressParts);

    // Patient Profile & Identification
    $patientId = '';
    if (!empty($_SESSION['patient_id'])) {
        $patientId = (string) $_SESSION['patient_id'];
    } elseif (!empty($formData['patient_id_number'])) {
        $patientId = (string) $formData['patient_id_number'];
    } else {
        $patientId = appointment_patient_record_key($formData);
    }

    $patientProfile = fetch_patient_profile_by_patient_id($patientId);
    if ($patientProfile !== null) {
        foreach (['first_name', 'middle_name', 'last_name', 'birth_date', 'gender', 'contact_number', 'email', 'complete_address'] as $field) {
            if ($formData[$field] === '') {
                $formData[$field] = trim((string) ($patientProfile[$field] ?? ''));
            }
        }
    }
    $formData['patient_id_number'] = $patientId;

    $required = ['first_name', 'last_name', 'birth_date', 'gender', 'contact_number', 'complete_address', 'preferred_date', 'preferred_time'];
    foreach ($required as $field) {
        if ($formData[$field] === '') {
            $errors[] = 'Please fill in all required fields.';
            break;
        }
    }

    if ($barangayVal === '' || !in_array($barangayVal, $barangayOptions, true)) {
        $errors[] = 'Please select one of the 15 barangay health stations for the address.';
    }

    $validPuroks = $purokOptionsByBarangay[$barangayVal] ?? [];
    if ($validPuroks !== [] && ($purokVal === '' || !in_array($purokVal, $validPuroks, true))) {
        $errors[] = 'Please select a valid purok for the selected barangay.';
    }

    if ($formData['contact_number'] !== '' && !preg_match('/^09\d{9}$/', $formData['contact_number'])) {
        $errors[] = 'Contact number must follow the format 09XXXXXXXXX.';
    }

    if ($selectedProgram['slug'] === 'immunization' && $formData['immunization_relationship'] === '') {
        $errors[] = 'Please provide your relationship to the patient for immunization bookings.';
    }

    if (
        $formData['preferred_date'] !== ''
        && $formData['preferred_time'] !== ''
        && !appointment_slot_is_available($selectedStation['slug'], $selectedProgram['slug'], $formData['preferred_date'], $formData['preferred_time'])
    ) {
        $errors[] = 'The selected day and time are no longer available. Please choose another vacant slot.';
    }

    if ($errors === []) {
        $appointmentCode = create_appointment_code($selectedStation['slug'], $selectedProgram['slug'], $formData['preferred_date']);
        if ($appointmentCode === null) {
            $errors[] = 'This health station has reached the daily limit of 200 patients. Please choose another date.';
        }
    }

    if ($errors === [] && $patientProfile !== null) {
        $oldAddress = trim((string) ($patientProfile['complete_address'] ?? ''));
        $newAddress = trim($formData['complete_address']);
        $oldContact = trim((string) ($patientProfile['contact_number'] ?? ''));
        $newContact = trim($formData['contact_number']);
        $patientName = trim($formData['first_name'] . ' ' . $formData['middle_name'] . ' ' . $formData['last_name']);

        if ($oldAddress !== '' && $newAddress !== '' && $oldAddress !== $newAddress) {
            track_patient_info_change($patientId, 'complete_address', $oldAddress, $newAddress);
            create_patient_update_notification($patientId, $patientName, 'Address');
        }

        if ($oldContact !== '' && $newContact !== '' && $oldContact !== $newContact) {
            track_patient_info_change($patientId, 'contact_number', $oldContact, $newContact);
            create_patient_update_notification($patientId, $patientName, 'Contact Number');
        }
    }

    if ($errors === []) {
        $reference = create_reference_code();
        upsert_patient_profile($formData + ['patient_id' => $patientId]);
        $stmt = db()->prepare('INSERT INTO appointments (reference_code, appointment_code, patient_id, station_slug, station_name, service_slug, service_name, first_name, middle_name, last_name, birth_date, gender, contact_number, email, complete_address, immunization_relationship, preferred_date, preferred_time, notes, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "Pending")');
        $stmt->bind_param(
            'sssssssssssssssssss',
            $reference,
            $appointmentCode,
            $patientId,
            $selectedStation['slug'],
            $selectedStation['name'],
            $selectedProgram['slug'],
            $selectedProgram['title'],
            $formData['first_name'],
            $formData['middle_name'],
            $formData['last_name'],
            $formData['birth_date'],
            $formData['gender'],
            $formData['contact_number'],
            $formData['email'],
            $formData['complete_address'],
            $formData['immunization_relationship'],
            $formData['preferred_date'],
            $formData['preferred_time'],
            $formData['notes']
        );
        $stmt->execute();
        log_activity('patient', $patientId, 'appointment_booked', 'appointment', $reference, '', 'Pending', $selectedStation['slug']);

        header('Location: ?confirmation=' . urlencode($reference));
        exit;
    }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
<header class="main-header">
    <div class="container nav-bar simple-nav">
        <a class="brand" href="<?= $isLoggedIn ? 'dashboard.php' : 'index.php'; ?>">
            <span class="brand-icon"><?= iconSvg('heart'); ?></span>
            <span class="brand-copy">
                <strong>Bacolod Health Centers</strong>
                <small>Your Community Health Partner</small>
            </span>
        </a>
        <a class="contact-link" href="tel:0341234567">
            <span class="inline-icon"><?= iconSvg('phone'); ?></span>
            <span><?= h($contact['phone']); ?></span>
        </a>
    </div>
</header>
<?php if ($isConfirmationPage): ?>
<main>
    <section class="confirmation-shell">
        <div class="toast-success">Booking submitted successfully!</div>
        <div class="container confirmation-wrap">
            <section class="confirmation-hero">
                <div class="confirmation-check"><?= iconSvg('success-mark'); ?></div>
                <h1>Booking Confirmed!</h1>
                <p>Your appointment request has been submitted successfully.</p>
                <div class="reference-box">
                    <span>Appointment ID</span>
                    <strong><?= h((string) ($confirmedAppointment['appointment_code'] ?? $confirmedAppointment['reference_code'])); ?></strong>
                </div>
                <div class="reference-box secondary">
                    <span>Patient ID</span>
                    <strong><?= h((string) ($confirmedAppointment['patient_id'] ?? 'Pending')); ?></strong>
                </div>
            </section>

            <section class="confirmation-card">
                <h2>Appointment Details</h2>
                <div class="detail-grid two-col">
                    <div class="detail-item"><span class="detail-icon mint"><?= iconSvg('map'); ?></span><div><small>Health Station</small><strong><?= h($confirmedAppointment['station_name']); ?></strong></div></div>
                    <div class="detail-item"><span class="detail-icon blue"><?= iconSvg('check-circle'); ?></span><div><small>Service</small><strong><?= h($confirmedAppointment['service_name']); ?></strong></div></div>
                    <div class="detail-item"><span class="detail-icon violet"><?= iconSvg('calendar'); ?></span><div><small>Appointment Date</small><strong><?= h(date('l, F j, Y', strtotime((string) $confirmedAppointment['preferred_date']))); ?></strong></div></div>
                    <div class="detail-item"><span class="detail-icon gold"><?= iconSvg('clock'); ?></span><div><small>Service Slot</small><strong><?= h($confirmedAppointment['preferred_time']); ?></strong></div></div>
                </div>
                <div class="divider"></div>
                <h2>Patient Information</h2>
                <div class="detail-grid two-col patient-details">
                    <div class="detail-line"><span class="inline-icon light-icon"><?= iconSvg('user'); ?></span><div><small>Name</small><strong><?= h(fullName($confirmedAppointment)); ?></strong></div></div>
                    <div class="detail-line"><span class="inline-icon light-icon"><?= iconSvg('phone'); ?></span><div><small>Contact Number</small><strong><?= h($confirmedAppointment['contact_number']); ?></strong></div></div>
                    <div class="detail-line"><span class="inline-icon light-icon"><?= iconSvg('mail'); ?></span><div><small>Email</small><strong><?= h((string) ($confirmedAppointment['email'] ?: 'No email provided')); ?></strong></div></div>
                    <div class="detail-line"><span class="inline-icon light-icon"><?= iconSvg('home'); ?></span><div><small>Address</small><strong><?= h($confirmedAppointment['complete_address']); ?></strong></div></div>
                </div>
            </section>

            <section class="next-steps-card">
                <h2>What Happens Next?</h2>
                <ol>
                    <li>The health station will review your booking request and confirm availability.</li>
                    <li>You will receive a confirmation via SMS or call within 24 hours.</li>
                    <li>Please arrive 10-15 minutes before your scheduled appointment time.</li>
                    <li>Bring a valid ID and any relevant medical records or documents.</li>
                </ol>
            </section>

            <div class="confirmation-actions">
                <a class="home-button" href="<?= $isLoggedIn ? ('dashboard.php?booked=' . urlencode((string) ($confirmedAppointment['appointment_code'] ?? $confirmedAppointment['reference_code']))) : 'index.php'; ?>"><?= $isLoggedIn ? 'Go to My Appointments on Dashboard' : 'Return to Home'; ?></a>
                                <button
                    class="print-button"
                    id="downloadConfirmationButton"
                    type="button"
                    data-reference="<?= h((string) ($confirmedAppointment['appointment_code'] ?? $confirmedAppointment['reference_code'])); ?>"
                    data-patient-id="<?= h((string) ($confirmedAppointment['patient_id'] ?? '')); ?>"
                    data-station="<?= h($confirmedAppointment['station_name']); ?>"
                    data-service="<?= h($confirmedAppointment['service_name']); ?>"
                    data-date="<?= h(date('l, F j, Y', strtotime((string) $confirmedAppointment['preferred_date']))); ?>"
                    data-time="<?= h($confirmedAppointment['preferred_time']); ?>"
                    data-name="<?= h(fullName($confirmedAppointment)); ?>"
                    data-contact="<?= h($confirmedAppointment['contact_number']); ?>"
                    data-email="<?= h((string) ($confirmedAppointment['email'] ?: 'No email provided')); ?>"
                    data-address="<?= h($confirmedAppointment['complete_address']); ?>"
                >Download</button>
            </div>
        </div>
    </section>
</main>
<?php elseif ($isBookingPage): ?>
<main>
    <section class="booking-shell">
        <div class="container booking-wrap">
            <a class="back-link" href="<?= $isLoggedIn ? 'dashboard.php' : ('?barangay=' . h($selectedStation['slug'])); ?>">
                <span class="inline-icon"><?= iconSvg('chevron-left'); ?></span>
                <?= $isLoggedIn ? 'Back to Dashboard' : 'Back to Services'; ?>
            </a>
            <section class="booking-hero">
                <h1>Book an Appointment</h1>
                <div class="booking-meta">
                    <span><span class="inline-icon"><?= iconSvg('map'); ?></span><?= h($selectedStation['name']); ?></span>
                    <span><span class="inline-icon"><?= iconSvg('check-circle'); ?></span><?= h($selectedProgram['title']); ?></span>
                </div>
            </section>
            <section class="booking-card">
                <?php foreach ($errors as $error): ?>
                    <div class="photo-notice amber-box"><?= h($error); ?></div>
                <?php endforeach; ?>
                <?php if ($profileUpdateMessage === 'saved'): ?>
                    <div class="photo-notice success-box">Your information has been updated.</div>
                <?php elseif ($profileUpdateMessage === 'failed'): ?>
                    <div class="photo-notice amber-box">Unable to update your information. Please check the required fields.</div>
                <?php endif; ?>
                <form class="booking-form" id="bookingForm" action="?barangay=<?= h($selectedStation['slug']); ?>&service=<?= h($selectedProgram['slug']); ?>" method="post">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="action" id="bookingAction" value="book_appointment">
                    <input type="hidden" name="patient_id_number" id="patient_id_number" value="<?= h($formData['patient_id_number']); ?>">
                    <!-- CALENDAR MOVED TO TOP: Schedule Availability -->
                    <div class="form-section-title"><span class="section-mini-icon"><?= iconSvg('calendar'); ?></span><h2>Schedule Availability</h2></div>
                    <div class="schedule-picker" data-schedule-picker data-availability='<?= h(json_encode($availabilityData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?: '{}'); ?>'>
                        <div class="schedule-header">
                            <div>
                                <h3>View current health center vacancy.</h3>
                                <p>Choose an open day from the calendar. The available slots count is shown as a note.</p>
                            </div>
                            <div class="schedule-legend" aria-label="Schedule legend">
                                <span><i class="legend-dot available"></i>Available</span>
                                <span><i class="legend-dot booked"></i>Fully Booked</span>
                            </div>
                        </div>
                        <div class="schedule-board">
                            <div class="schedule-calendar-panel">
                                <div class="schedule-month-bar">
                                    <button type="button" class="month-nav" data-month-nav="prev" aria-label="Previous month"><?= iconSvg('chevron-left'); ?></button>
                                    <strong id="scheduleMonthLabel">Select a day</strong>
                                    <button type="button" class="month-nav month-nav-next" data-month-nav="next" aria-label="Next month"><?= iconSvg('arrow'); ?></button>
                                </div>
                                <div class="calendar-weekdays" aria-hidden="true">
                                    <span>Sun</span>
                                    <span>Mon</span>
                                    <span>Tue</span>
                                    <span>Wed</span>
                                    <span>Thu</span>
                                    <span>Fri</span>
                                    <span>Sat</span>
                                </div>
                                <div class="calendar-grid" id="scheduleCalendarGrid"></div>
                            </div>
                            <div class="schedule-times-panel">
                                <div class="times-title">
                                    <h3 id="scheduleTimesLabel">Available Slots</h3>
                                    <p id="scheduleSelectionHint">Select an available day to view the remaining capacity.</p>
                                </div>
                                <div class="time-slots" id="scheduleTimeSlots"></div>
                                <p class="schedule-note">Please come on time for your scheduled appointment.</p>
                            </div>
                        </div>
                        <div class="schedule-selection">
                            <div><small>Selected date</small><strong id="selectedDateLabel"><?= h($formData['preferred_date'] !== '' ? date('l, F j, Y', strtotime($formData['preferred_date'])) : 'None'); ?></strong></div>
                            <div><small>Selected slot</small><strong id="selectedTimeLabel"><?= h($formData['preferred_time'] !== '' ? $formData['preferred_time'] : 'None'); ?></strong></div>
                        </div>
                        <input data-required type="hidden" name="preferred_date" id="preferred_date" value="<?= h($formData['preferred_date']); ?>">
                        <input data-required type="hidden" name="preferred_time" id="preferred_time" value="<?= h($formData['preferred_time']); ?>">
                    </div>
                    <!-- PERSONAL INFORMATION -->
                    <div class="account-info-banner">
                        <span class="inline-icon"><?= iconSvg('sparkle'); ?></span>
                        <div>
                            <strong>Account Information Locked</strong>
                            <p>Your personal details and registered address are loaded from your account. To edit them, please update your <a href="dashboard.php">Account Settings</a>.</p>
                        </div>
                    </div>
                    <div class="form-section-title"><span class="section-mini-icon"><?= iconSvg('user'); ?></span><h2>Personal Information</h2></div>
                    <div class="form-grid two-col three-col-desktop">
                        <label><span>First Name <em>*</em></span><input data-required type="text" name="first_name" value="<?= h($formData['first_name']); ?>" placeholder="Juan" class="form-readonly-input" readonly autocomplete="given-name"></label>
                        <label><span>Middle Name</span><input type="text" name="middle_name" value="<?= h($formData['middle_name']); ?>" placeholder="Santos" class="form-readonly-input" readonly autocomplete="additional-name"></label>
                        <label><span>Last Name <em>*</em></span><input data-required type="text" name="last_name" value="<?= h($formData['last_name']); ?>" placeholder="Dela Cruz" class="form-readonly-input" readonly autocomplete="family-name"></label>
                        <label><span>Date of Birth <em>*</em></span><input data-required type="date" name="birth_date" value="<?= h($formData['birth_date']); ?>" class="form-readonly-input" readonly style="pointer-events: none;"></label>
                        <div class="gender-radio-group">
                            <span class="gender-label">Gender <em>*</em></span>
                            <div class="gender-options locked-options">
                                <label class="radio-option <?= $formData['gender'] === 'Male' ? 'is-selected' : 'is-unselected'; ?>">
                                    <input type="radio" name="gender_display" value="Male" <?= $formData['gender'] === 'Male' ? 'checked' : ''; ?> disabled>
                                    <span class="radio-custom"></span>
                                    <span>Male</span>
                                </label>
                                <label class="radio-option <?= $formData['gender'] === 'Female' ? 'is-selected' : 'is-unselected'; ?>">
                                    <input type="radio" name="gender_display" value="Female" <?= $formData['gender'] === 'Female' ? 'checked' : ''; ?> disabled>
                                    <span class="radio-custom"></span>
                                    <span>Female</span>
                                </label>
                                <input type="hidden" name="gender" value="<?= h($formData['gender']); ?>">
                            </div>
                        </div>
                    </div>
                    <!-- CONTACT INFORMATION -->
                    <div class="form-section-title"><span class="section-mini-icon"><?= iconSvg('phone'); ?></span><h2>Contact Information</h2></div>
                    <div class="form-grid two-col">
                        <label><span>Contact Number <em>*</em></span><input data-required type="text" name="contact_number" value="<?= h($formData['contact_number']); ?>" placeholder="09171234567" class="form-readonly-input" readonly><small>Format: 09XXXXXXXXX</small></label>
                        <label><span>Email Address</span><input type="email" name="email" value="<?= h($formData['email']); ?>" placeholder="juan.delacruz@email.com" class="form-readonly-input" readonly></label>
                        <?php if ($selectedProgram['slug'] === 'immunization'): ?>
                            <label class="full-span"><span>Relationship to Patient <em>*</em></span><input data-required type="text" name="immunization_relationship" value="<?= h($formData['immunization_relationship']); ?>" placeholder="Parent, guardian, or self" class="capitalize-input"></label>
                        <?php endif; ?>
                        <div class="full-span address-group">
                            <span class="address-label">Complete Address <em>*</em></span>
                            <?php
                            $rawAddress = (string) ($formData['complete_address'] ?? '');
                            $knownBarangay = (string) ($_SESSION['patient_barangay'] ?? ($isBookingPage && $selectedStation !== null ? $selectedStation['barangay'] : ''));
                            $knownPurok = (string) ($_SESSION['patient_purok'] ?? '');
                            $knownStreet = (string) ($_SESSION['patient_street'] ?? '');

                            $parsed = parse_complete_address($rawAddress, $knownBarangay, $knownPurok);

                            $selectedAddressBarangay = $knownBarangay !== '' ? $knownBarangay : $parsed['barangay'];
                            $selectedPurok = $knownPurok !== '' ? $knownPurok : $parsed['purok'];
                            $addressRemainder = $knownStreet !== '' ? $knownStreet : $parsed['street'];

                            $selectedPurokOptions = $selectedAddressBarangay !== '' ? ($purokOptionsByBarangay[$selectedAddressBarangay] ?? []) : [];
                            ?>
                            <div class="address-fields" data-puroks-by-barangay='<?= h(json_encode($purokOptionsByBarangay, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?: '{}'); ?>'>
                                <label class="address-barangay-label">
                                    <span>Barangay <em>*</em></span>
                                    <select data-required name="address_barangay_display" id="address_barangay" disabled class="form-readonly-input">
                                        <option value="">Select Barangay</option>
                                        <?php foreach ($barangayOptions as $barangayOption): ?>
                                            <option value="<?= h($barangayOption); ?>" <?= $selectedAddressBarangay === $barangayOption ? 'selected' : ''; ?>><?= h($barangayOption); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                                <input type="hidden" name="address_barangay" value="<?= h($selectedAddressBarangay); ?>">
                                <label class="address-purok-label">
                                    <span>Purok <em>*</em></span>
                                    <select data-required name="purok_display" id="purok_select" disabled class="form-readonly-input">
                                        <option value=""><?= $selectedAddressBarangay === '' ? 'Select Barangay First' : 'Select Purok'; ?></option>
                                        <?php foreach ($selectedPurokOptions as $purokOption): ?>
                                            <option value="<?= h($purokOption); ?>" <?= $selectedPurok === $purokOption ? 'selected' : ''; ?>><?= h($purokOption); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                                <input type="hidden" name="purok" value="<?= h($selectedPurok); ?>">
                                <label class="address-street-label">
                                    <span>Street / House No.</span>
                                    <input type="text" name="address_remainder" id="address_remainder" value="<?= h($addressRemainder); ?>" placeholder="Street or house number" class="form-readonly-input" readonly>
                                </label>
                            </div>
                            <input type="hidden" name="complete_address" id="complete_address" value="<?= h($formData['complete_address']); ?>">
                        </div>
                    </div>
                    <div class="form-grid">
                        <label class="full-span"><span>Additional Notes</span><textarea name="notes" rows="5" placeholder="Any additional information you'd like to share..."><?= h($formData['notes']); ?></textarea></label>
                    </div>
                    <div class="photo-notice amber-box"><strong>Important:</strong> This is a booking request. The health station will confirm your appointment via SMS or call. Please ensure your contact number is correct.</div>
                    <div class="form-actions"><a class="secondary-action link-button" href="<?= $isLoggedIn ? 'dashboard.php' : ('?barangay=' . h($selectedStation['slug'])); ?>">Cancel</a><button id="submitBookingButton" type="submit" class="primary-action is-ready">Submit Booking</button></div>
                </form>
            </section>
        </div>
    </section>
</main>
<?php elseif ($isDetailPage): ?>
<main>
    <section class="detail-backdrop"><div class="container"><a class="back-link" href="index.php#stations"><span class="inline-icon"><?= iconSvg('chevron-left'); ?></span>Back to Health Stations</a></div></section>
    <section class="station-hero" style="background-image: linear-gradient(180deg, rgba(11, 23, 38, 0.08), rgba(11, 23, 38, 0.55)), url('<?= h($selectedStation['image']); ?>');"><div class="container station-hero-inner"><h1><?= h($selectedStation['name']); ?></h1><p><span class="inline-icon"><?= iconSvg('map'); ?></span><?= h($selectedStation['detail_location']); ?></p></div></section>
    <section class="info-strip"><div class="container info-grid"><article class="info-card"><div class="info-icon mint"><?= iconSvg('phone'); ?></div><div><span>Contact Number</span><strong><?= h($selectedStation['phone']); ?></strong></div></article><article class="info-card"><div class="info-icon blue"><?= iconSvg('clock'); ?></div><div><span>Open Hours</span><strong><?= h($selectedStation['full_hours']); ?></strong></div></article></div></section>
    <section class="section services-section"><div class="container"><div class="section-heading left services-heading-block"><div><h2>Available Services</h2><p>Select a service to book your appointment</p></div></div><div class="services-grid"><?php foreach ($selectedStation['programs'] as $program): ?><?php $scheduleLabel = service_schedule_label($selectedStation['slug'], $program['slug']); ?><a class="service-card <?= in_array($program['slug'], ['immunization', 'consultation', 'family'], true) ? 'highlighted' : ''; ?>" href="?barangay=<?= h($selectedStation['slug']); ?>&service=<?= h($program['slug']); ?>"><div class="service-card-top"><div class="service-icon <?= h($program['color']); ?>"><?= iconSvg($program['icon']); ?></div><span class="service-arrow"><?= iconSvg('arrow'); ?></span></div><h3><?= h($program['title']); ?></h3><p><?= h($program['description']); ?></p><div class="service-schedule"><span><?= iconSvg('clock'); ?></span><strong><?= h($scheduleLabel); ?></strong></div><div class="service-action">Click to book -></div></a><?php endforeach; ?></div></div></section>
</main>
<?php else: ?>
<main class="portal-shell" id="top">
    <section class="portal-hero">
        <div class="container portal-hero-inner">
            <div class="portal-hero-content">
                <span class="hero-badge"><span class="inline-icon"><?= iconSvg('sparkle'); ?></span>Your Health, Our Priority</span>
                <h1>Welcome to Bacolod<br>Community Health Centers</h1>
                <p>Access quality healthcare services in your barangay. Book appointments, check schedules, and stay updated with health programs.</p>
                <button type="button" class="portal-button js-portal-scroll">
                    <span>Choose Your Portal</span>
                    <span class="inline-icon"><?= iconSvg('arrow'); ?></span>
                </button>
            </div>
        </div>

    </section>

    <section class="portal-selector" id="portalSelector">
        <div class="container portal-selector-inner">
            <div class="portal-selector-heading">
                <h2>Choose Your Portal</h2>
                <p>Select your role to access the right services</p>
            </div>

            <div class="portal-cards">
                <button type="button" class="portal-card active" data-portal="patient">
                    <div class="portal-card-icon patient-icon"><?= iconSvg('user'); ?></div>
                    <h3>Patient</h3>
                    <p>Book appointments and access healthcare services at your barangay health center</p>
                </button>

                <button type="button" class="portal-card" data-portal="volunteer">
                    <div class="portal-card-icon volunteer-icon"><?= iconSvg('stethoscope'); ?></div>
                    <h3>Volunteer</h3>
                    <p>Manage appointments and queues as an assigned health center staff member</p>
                </button>

                <button type="button" class="portal-card" data-portal="admin">
                    <div class="portal-card-icon admin-icon"><?= iconSvg('shield'); ?></div>
                    <h3>Admin</h3>
                    <p>System administration, analytics, and oversight of all health centers</p>
                </button>
            </div>

        </div>
    </section>

    <!-- Volunteer Portal Modal -->
    <div id="volunteerModal" class="modal-overlay hidden">
        <div class="modal-content volunteer-themed">
            <div class="modal-header">
                <div class="modal-header-left">
                    <div class="modal-header-icon volunteer-icon">
                        <?= iconSvg('stethoscope'); ?>
                    </div>
                    <div>
                        <h2 class="modal-title">Volunteer Login</h2>
                        <p class="modal-subtitle">Health Center Staff Portal</p>
                    </div>
                </div>
                <button type="button" class="modal-close" id="volunteerModalClose">
                    <?= iconSvg('close'); ?>
                </button>
            </div>
            <div class="modal-body">
                <form class="auth-form" id="volunteerLoginForm" novalidate>
                    <div class="field-group">
                        <label for="volunteerEmail">Work Email</label>
                        <div class="input-with-icon">
                            <span class="field-icon"><?= iconSvg('mail'); ?></span>
                            <input id="volunteerEmail" name="volunteer_email" type="email" value="" placeholder="e.g. staff-bata@bata.health or leo@bata.health" required>
                        </div>
                        <small>Use your assigned health station email (e.g., staff-bata@bata.health, leo@bata.health)</small>
                    </div>
                    <div class="field-group">
                        <label for="volunteerPassword">Password</label>
                        <div class="input-with-icon password-field">
                            <span class="field-icon"><?= iconSvg('shield'); ?></span>
                            <input id="volunteerPassword" name="volunteer_password" type="password" value="" placeholder="Enter your password" required>
                            <button type="button" class="toggle-password" aria-label="Show password"><?= iconSvg('eye'); ?></button>
                        </div>
                    </div>
                    <button type="submit" class="auth-submit-btn volunteer-submit">Sign In</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Admin Portal Modal -->
    <div id="adminModal" class="modal-overlay hidden">
        <div class="modal-content admin-themed">
            <div class="modal-header">
                <div class="modal-header-left">
                    <div class="modal-header-icon admin-icon">
                        <?= iconSvg('shield'); ?>
                    </div>
                    <div>
                        <h2 class="modal-title">Admin Login</h2>
                        <p class="modal-subtitle">System Administration Portal</p>
                    </div>
                </div>
                <button type="button" class="modal-close" id="adminModalClose">
                    <?= iconSvg('close'); ?>
                </button>
            </div>
            <div class="modal-body">
                <form class="auth-form" id="adminLoginForm" novalidate>
                    <div class="field-group">
                        <label for="adminUsername">Username</label>
                        <div class="input-with-icon">
                            <span class="field-icon"><?= iconSvg('user'); ?></span>
                            <input id="adminUsername" name="admin_username" type="text" value="" placeholder="admin" required>
                        </div>
                    </div>
                    <div class="field-group">
                        <label for="adminPassword">Password</label>
                        <div class="input-with-icon password-field">
                            <span class="field-icon"><?= iconSvg('lock'); ?></span>
                            <input id="adminPassword" name="admin_password" type="password" value="" placeholder="Enter your password" required>
                            <button type="button" class="toggle-password" aria-label="Show password"><?= iconSvg('eye'); ?></button>
                        </div>
                    </div>
                    <button type="submit" class="auth-submit-btn admin-submit">Sign In</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Patient Portal Modal -->
    <div id="patientModal" class="modal-overlay hidden">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-header-left">
                    <div class="modal-header-icon patient-icon">
                        <?= iconSvg('user'); ?>
                    </div>
                    <div>
                        <h2 class="modal-title" id="modalTitle">Patient Portal</h2>
                        <p class="modal-subtitle" id="modalSubtitle">Bacolod Health Delivery System</p>
                    </div>
                </div>
                <button type="button" class="modal-close" id="modalClose">
                    <?= iconSvg('close'); ?>
                </button>
            </div>

            <div class="modal-body">
                <div id="patientChoice" class="modal-step active-step">
                    <p class="modal-question">Are you booking for the first time, or do you already have a patient account?</p>

                    <button type="button" class="modal-choice-button first-timer">
                        <div class="choice-icon patient-icon">
                            <?= iconSvg('user-plus'); ?>
                        </div>
                        <div class="choice-content">
                            <h3>I am a First Timer</h3>
                            <p>Create a new patient account to get started</p>
                        </div>
                        <div class="choice-arrow">
                            <?= iconSvg('arrow-right'); ?>
                        </div>
                    </button>

                    <button type="button" class="modal-choice-button existing-patient">
                        <div class="choice-icon patient-icon" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8);">
                            <?= iconSvg('log-in'); ?>
                        </div>
                        <div class="choice-content">
                            <h3>I Already Have an Account</h3>
                            <p>Log in to view your health center services</p>
                        </div>
                        <div class="choice-arrow">
                            <?= iconSvg('arrow-right'); ?>
                        </div>
                    </button>
                </div>

                <div id="firstTimerStep" class="modal-step hidden-step" aria-hidden="true">
                    <form class="auth-form" id="firstTimerForm" novalidate>
                        <div class="form-row two-col">
                            <div class="field-group">
                                <label for="firstName">First Name</label>
                                <input id="firstName" name="first_name" type="text" value="" placeholder="" required>
                            </div>
                            <div class="field-group">
                                <label for="middleName">Middle Name</label>
                                <input id="middleName" name="middle_name" type="text" value="" placeholder="">
                            </div>
                        </div>

                        <div class="form-row two-col">
                            <div class="field-group">
                                <label for="lastName">Last Name</label>
                                <input id="lastName" name="last_name" type="text" value="" placeholder="" required>
                            </div>
                            <div class="field-group">
                                <label for="regBirthdate">Date of Birth</label>
                                <input id="regBirthdate" name="birthdate" type="date" value="" required>
                            </div>
                        </div>

                        <div class="form-row two-col">
                            <div class="field-group">
                                <label class="gender-label">Gender <em style="color:#ff4f4f;">*</em></label>
                                <div class="gender-options">
                                    <label class="radio-option">
                                        <input type="radio" name="gender" value="Male" data-required-radio="gender">
                                        <span class="radio-custom"></span>
                                        <span>Male</span>
                                    </label>
                                    <label class="radio-option">
                                        <input type="radio" name="gender" value="Female" data-required-radio="gender">
                                        <span class="radio-custom"></span>
                                        <span>Female</span>
                                    </label>
                                </div>
                            </div>
                            <div class="field-group">
                                <label for="regPhone">Contact Number <em style="color:#ff4f4f;">*</em></label>
                                <input id="regPhone" name="phone" type="tel" value="" placeholder="09XXXXXXXXX" required>
                            </div>
                        </div>

                        <div class="form-row two-col">
                            <div class="field-group">
                                <label for="regEmail">Email</label>
                                <input id="regEmail" name="email" type="email" value="" placeholder="" required>
                            </div>
                            <div class="field-group">
                                <label for="regPassword">Password</label>
                                <div class="input-with-icon password-field">
                                    <input id="regPassword" name="password" type="password" value="" placeholder="" minlength="6" required>
                                    <button type="button" class="toggle-password" aria-label="Show password"><?= iconSvg('eye'); ?></button>
                                </div>
                            </div>
                        </div>

                        <div class="form-row two-col">
                            <div class="field-group">
                                <label for="regBarangay">Barangay</label>
                                <select id="regBarangay" name="barangay" required>
                                    <option value="">Select Barangay</option>
                                    <?php foreach ($barangayOptions as $barangay): ?>
                                        <option value="<?= h($barangay); ?>"><?= h($barangay); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="field-group">
                                <label for="regPurok">Purok / Zone</label>
                                <input id="regPurok" name="purok" type="text" value="" placeholder="Purok or zone" required>
                            </div>
                        </div>

                        <div class="field-group full-width">
                            <label for="regStreet">Street / House No.</label>
                            <input id="regStreet" name="street" type="text" value="" placeholder="" required>
                        </div>

                        <button type="submit" class="auth-submit-btn">Create Account</button>

                        <p class="auth-switch-text">
                            Already have an account?
                            <button type="button" class="text-action" data-go-step="login">Log in here</button>
                        </p>
                    </form>
                </div>

                <div id="loginStep" class="modal-step hidden-step" aria-hidden="true">
                    <div class="auth-step-header">
                        <button type="button" class="back-link" data-go-step="choice">
                            <span class="back-icon"><?= iconSvg('chevron-left'); ?></span>
                            <span>Back</span>
                        </button>
                    </div>

                    <form class="auth-form" id="loginForm" novalidate>
                        <div class="field-group">
                            <label for="loginEmail">Email Address</label>
                            <div class="input-with-icon">
                                <span class="field-icon"><?= iconSvg('mail'); ?></span>
                                <input id="loginEmail" name="login_email" type="email" value="" placeholder="juan@email.com" required>
                            </div>
                        </div>

                        <div class="field-group">
                            <label for="loginPassword">Password</label>
                            <div class="input-with-icon password-field">
                                <span class="field-icon"><?= iconSvg('shield'); ?></span>
                                <input id="loginPassword" name="login_password" type="password" value="" placeholder="Enter your password" required>
                                <button type="button" class="toggle-password" aria-label="Show password"><?= iconSvg('eye'); ?></button>
                            </div>
                        </div>

                        <button type="submit" class="auth-submit-btn">Log In</button>

                        <p class="auth-switch-text">
                            Don’t have an account?
                            <button type="button" class="text-action" data-go-step="firstTimer">Create one</button>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
<?php endif; ?>
<footer class="footer" id="footer"><div class="container footer-grid"><div><div class="brand footer-brand"><span class="brand-icon"><?= iconSvg('heart'); ?></span><span class="brand-copy"><strong>Bacolod Health Stations</strong></span></div><p>Providing quality healthcare services to the communities of Bacolod City.</p></div><div><h3>Contact Information</h3><ul class="footer-list"><li><span class="inline-icon"><?= iconSvg('phone'); ?></span><?= h($contact['phone']); ?></li><li><span class="inline-icon"><?= iconSvg('map'); ?></span><?= h($contact['address']); ?></li><li><span class="inline-icon"><?= iconSvg('clock'); ?></span><?= h($contact['hours']); ?></li></ul></div><div><h3>Quick Links</h3><ul class="footer-links"><li><a href="index.php#stations">Find a Health Station</a></li><li><a href="index.php">Back to Top</a></li><li><a href="index.php#cta">Get Started</a></li></ul></div></div><div class="container footer-bottom"><p>&copy; 2026 Bacolod Health Stations. All rights reserved.</p></div></footer>
<script src="assets/js/app.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const portalButtons = document.querySelectorAll('.portal-card');
    const scrollTarget = document.querySelector('.js-portal-scroll');
    const selector = document.getElementById('portalSelector');
    const patientModal = document.getElementById('patientModal');
    const volunteerModal = document.getElementById('volunteerModal');
    const adminModal = document.getElementById('adminModal');
    const modalClose = document.getElementById('modalClose');
    const volunteerModalClose = document.getElementById('volunteerModalClose');
    const adminModalClose = document.getElementById('adminModalClose');
    const modalTitle = document.getElementById('modalTitle');
    const modalSubtitle = document.getElementById('modalSubtitle');
    const firstTimerBtn = document.querySelector('.modal-choice-button.first-timer');
    const existingPatientBtn = document.querySelector('.modal-choice-button.existing-patient');
    const choiceStep = document.getElementById('patientChoice');
    const firstTimerStep = document.getElementById('firstTimerStep');
    const loginStep = document.getElementById('loginStep');
    const firstTimerForm = document.getElementById('firstTimerForm');
    const loginForm = document.getElementById('loginForm');
    const volunteerLoginForm = document.getElementById('volunteerLoginForm');
    const adminLoginForm = document.getElementById('adminLoginForm');
    const backButtons = document.querySelectorAll('.back-link, .text-action');

    function setActivePortal(portal) {
        document.querySelectorAll('.portal-card').forEach((button) => {
            const isMatch = button.dataset.portal === portal;
            button.classList.toggle('active', isMatch);
        });
    }

    function showPatientStep(stepName) {
        const stepMap = {
            choice: 'patientChoice',
            firstTimer: 'firstTimerStep',
            login: 'loginStep'
        };

        const activeStepId = stepMap[stepName] || 'patientChoice';
        const steps = [choiceStep, firstTimerStep, loginStep];

        steps.forEach((step) => {
            if (!step) return;
            const isActive = step.id === activeStepId;
            step.classList.toggle('hidden-step', !isActive);
            step.setAttribute('aria-hidden', String(!isActive));
        });

        if (stepName === 'firstTimer') {
            modalTitle.textContent = 'Create Account';
            modalSubtitle.textContent = 'Bacolod Health Delivery System';
        } else if (stepName === 'login') {
            modalTitle.textContent = 'Log In';
            modalSubtitle.textContent = 'Welcome back';
        } else {
            modalTitle.textContent = 'Patient Portal';
            modalSubtitle.textContent = 'Bacolod Health Delivery System';
        }
    }

    function openPatientModal() {
        patientModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        showPatientStep('choice');
    }

    function closePatientModal() {
        patientModal.classList.add('hidden');
        document.body.style.overflow = 'auto';
        showPatientStep('choice');
    }

    function openVolunteerModal() {
        volunteerModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeVolunteerModal() {
        volunteerModal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function openAdminModal() {
        adminModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeAdminModal() {
        adminModal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    portalButtons.forEach((button) => {
        button.addEventListener('click', function () {
            const portal = this.dataset.portal;
            setActivePortal(portal);

            if (portal === 'patient') {
                openPatientModal();
            } else if (portal === 'volunteer') {
                openVolunteerModal();
            } else if (portal === 'admin') {
                openAdminModal();
            }
        });
    });

    modalClose?.addEventListener('click', closePatientModal);
    volunteerModalClose?.addEventListener('click', closeVolunteerModal);
    adminModalClose?.addEventListener('click', closeAdminModal);

    patientModal?.addEventListener('click', function(e) {
        if (e.target === patientModal) {
            closePatientModal();
        }
    });

    volunteerModal?.addEventListener('click', function(e) {
        if (e.target === volunteerModal) {
            closeVolunteerModal();
        }
    });

    adminModal?.addEventListener('click', function(e) {
        if (e.target === adminModal) {
            closeAdminModal();
        }
    });

    firstTimerBtn?.addEventListener('click', function() {
        showPatientStep('firstTimer');
    });

    existingPatientBtn?.addEventListener('click', function() {
        showPatientStep('login');
    });

    document.querySelectorAll('[data-go-step]').forEach((button) => {
        button.addEventListener('click', function () {
            const target = this.dataset.goStep;
            showPatientStep(target);
        });
    });

    firstTimerForm?.addEventListener('submit', function (event) {
        event.preventDefault();
        const password = document.getElementById('regPassword').value.trim();

        if (password.length < 6) {
            window.showSystemToast?.('Password must be at least 6 characters long.', { type: 'error', theme: 'patient', title: 'Invalid Password' });
            return;
        }

        const formData = new FormData(this);
        formData.append('action', 'register_patient');
        
        fetch('login-handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = 'dashboard.php';
            } else {
                window.showSystemToast?.(data.message || 'Registration failed. Please check the required fields.', { type: 'error', theme: 'patient', title: 'Registration Notice' });
            }
        })
        .catch(() => {
            window.location.href = 'dashboard.php';
        });
    });

    loginForm?.addEventListener('submit', function (event) {
        event.preventDefault();
        const email = document.getElementById('loginEmail').value.trim();
        const password = document.getElementById('loginPassword').value.trim();

        if (!email || !password) {
            window.showSystemToast?.('Please enter both your email address and password.', { type: 'warning', theme: 'patient', title: 'Missing Information' });
            return;
        }

        const formData = new FormData();
        formData.append('action', 'login_patient');
        formData.append('email', email);
        formData.append('password', password);
        
        fetch('login-handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = 'dashboard.php';
            } else {
                window.showSystemToast?.(data.message || 'Login failed. Please check your credentials.', { type: 'error', theme: 'patient', title: 'Login Failed' });
            }
        })
        .catch(() => {
            window.location.href = 'dashboard.php';
        });
    });

    volunteerLoginForm?.addEventListener('submit', function (event) {
        event.preventDefault();
        const email = document.getElementById('volunteerEmail').value.trim();
        const password = document.getElementById('volunteerPassword').value.trim();

        if (!email || !password) {
            window.showSystemToast?.('Please enter both your work email and password.', { type: 'warning', theme: 'volunteer', title: 'Missing Information' });
            return;
        }

        const formData = new FormData();
        formData.append('action', 'login_staff');
        formData.append('email', email);
        formData.append('password', password);

        fetch('login-handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect || '../Barangay Health Station/index.php';
            } else {
                window.showSystemToast?.(data.message || 'Staff login failed. Please check your credentials.', { type: 'error', theme: 'volunteer', title: 'Authentication Failed' });
            }
        })
        .catch(() => {
            window.location.href = '../Barangay Health Station/index.php';
        });
    });

    adminLoginForm?.addEventListener('submit', function (event) {
        event.preventDefault();
        const username = document.getElementById('adminUsername').value.trim();
        const password = document.getElementById('adminPassword').value.trim();

        if (!username || !password) {
            window.showSystemToast?.('Please enter both your username and password.', { type: 'warning', theme: 'admin', title: 'Missing Information' });
            return;
        }

        const formData = new FormData();
        formData.append('action', 'login_admin');
        formData.append('username', username);
        formData.append('password', password);

        fetch('login-handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect || '../Admin/index.php?page=dashboard';
            } else {
                window.showSystemToast?.(data.message || 'Invalid admin credentials.', { type: 'error', theme: 'admin', title: 'Authentication Failed' });
            }
        })
        .catch(() => {
            window.location.href = '../Admin/index.php?page=dashboard';
        });
    });

    document.querySelectorAll('.toggle-password').forEach((toggle) => {
        toggle.addEventListener('click', function () {
            const input = this.parentElement.querySelector('input');
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            this.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
        });
    });

    if (scrollTarget && selector) {
        scrollTarget.addEventListener('click', function () {
            selector.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    }
});
</script>
</body>
</html>
