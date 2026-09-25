<?php
declare(strict_types=1);

require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';

$passCount = 0;
$failCount = 0;

function assertTest(bool $condition, string $title): void {
    global $passCount, $failCount;
    if ($condition) {
        $passCount++;
        echo "✓ [PASS] {$title}\n";
    } else {
        $failCount++;
        echo "✗ [FAIL] {$title}\n";
    }
}

echo "=== TEST SUITE: INFANT VACCINE DOSE LIMITS & DROPDOWN RESTRICTIONS ===\n\n";

// 1. Verify standard dose limits according to the user's images
$limits = get_standard_vaccine_dose_limits();
assertTest(isset($limits['BCG']) && $limits['BCG'] === 1, 'BCG limit is 1 dose (At birth)');
assertTest(isset($limits['Hepatitis B']) && $limits['Hepatitis B'] === 1, 'Hepatitis B limit is 1 dose (At birth monovalent)');
assertTest(isset($limits['Pentavalent (DTP-HepB-Hib)']) && $limits['Pentavalent (DTP-HepB-Hib)'] === 3, 'Pentavalent limit is 3 doses (6, 10, 14 weeks)');
assertTest(isset($limits['OPV (Oral Polio Vaccine)']) && $limits['OPV (Oral Polio Vaccine)'] === 3, 'OPV limit is 3 doses (6, 10, 14 weeks)');
assertTest(isset($limits['PCV (Pneumococcal Conjugate Vaccine)']) && $limits['PCV (Pneumococcal Conjugate Vaccine)'] === 3, 'PCV limit is 3 doses (6, 10, 14 weeks)');
assertTest(isset($limits['IPV (Inactivated Polio Vaccine)']) && $limits['IPV (Inactivated Polio Vaccine)'] === 2, 'IPV limit is 2 doses (14 weeks injectable, 9 months)');
assertTest(isset($limits['Measles-Rubella (MR) or AMV-1']) && $limits['Measles-Rubella (MR) or AMV-1'] === 1, 'Measles-Rubella (MR) / AMV-1 limit is 1 dose (9 months)');
assertTest(isset($limits['MMR (Measles, Mumps, Rubella)']) && $limits['MMR (Measles, Mumps, Rubella)'] === 1, 'MMR limit is 1 dose (12 months completes primary series)');

// 2. Verify schedule helper
$schedules = get_standard_vaccine_schedules();
assertTest(count($schedules) === 8, 'Schedule exists for all 8 standard vaccines');
assertTest($schedules['BCG']['age'] === 'At Birth' && $schedules['BCG']['limit'] === 1, 'BCG schedule is At Birth with 1 dose');
assertTest($schedules['Pentavalent (DTP-HepB-Hib)']['limit'] === 3, 'Pentavalent schedule has limit 3');

// 3. Verify vaccine name normalization
assertTest(normalize_standard_vaccine_name('BCG') === 'BCG', 'Normalize "BCG" -> "BCG"');
assertTest(normalize_standard_vaccine_name('BCG (Bacillus Calmette-Guérin)') === 'BCG', 'Normalize full BCG -> "BCG"');
assertTest(normalize_standard_vaccine_name('Pentavalent') === 'Pentavalent (DTP-HepB-Hib)', 'Normalize "Pentavalent" -> canonical');
assertTest(normalize_standard_vaccine_name('DTP-HepB-Hib') === 'Pentavalent (DTP-HepB-Hib)', 'Normalize "DTP-HepB-Hib" -> canonical');
assertTest(normalize_standard_vaccine_name('Hepatitis B') === 'Hepatitis B', 'Normalize "Hepatitis B" -> "Hepatitis B"');
assertTest(normalize_standard_vaccine_name('Hep B') === 'Hepatitis B', 'Normalize "Hep B" -> "Hepatitis B"');
assertTest(normalize_standard_vaccine_name('OPV') === 'OPV (Oral Polio Vaccine)', 'Normalize "OPV" -> canonical');
assertTest(normalize_standard_vaccine_name('PCV') === 'PCV (Pneumococcal Conjugate Vaccine)', 'Normalize "PCV" -> canonical');
assertTest(normalize_standard_vaccine_name('IPV') === 'IPV (Inactivated Polio Vaccine)', 'Normalize "IPV" -> canonical');
assertTest(normalize_standard_vaccine_name('Measles-Rubella') === 'Measles-Rubella (MR) or AMV-1', 'Normalize "Measles-Rubella" -> canonical');
assertTest(normalize_standard_vaccine_name('MR') === 'Measles-Rubella (MR) or AMV-1', 'Normalize "MR" -> canonical');
assertTest(normalize_standard_vaccine_name('MMR') === 'MMR (Measles, Mumps, Rubella)', 'Normalize "MMR" -> canonical');

// 4. Test dose limit logic for disabling dropdown options
function simulate_dropdown_options(array $pastCounts, array $limits, array $standardVaccines): array {
    $options = [];
    foreach ($standardVaccines as $vac) {
        if ($vac === 'Others') {
            $options[$vac] = ['disabled' => false, 'label' => 'Others'];
            continue;
        }
        $limit = $limits[$vac] ?? null;
        $taken = (int) ($pastCounts[$vac] ?? 0);
        $isLimitReached = ($limit !== null && $taken >= $limit);
        $options[$vac] = [
            'disabled' => $isLimitReached,
            'taken' => $taken,
            'limit' => $limit,
            'label' => $isLimitReached ? "{$vac} — [Limit Reached: {$taken}/{$limit} doses taken]" : "{$vac} ({$taken}/{$limit} doses taken)"
        ];
    }
    return $options;
}

$standardVaccines = [
    'BCG',
    'Hepatitis B',
    'Pentavalent (DTP-HepB-Hib)',
    'OPV (Oral Polio Vaccine)',
    'PCV (Pneumococcal Conjugate Vaccine)',
    'IPV (Inactivated Polio Vaccine)',
    'Measles-Rubella (MR) or AMV-1',
    'MMR (Measles, Mumps, Rubella)',
    'Others'
];

// Scenario A: Brand new infant (0 doses taken)
$optsNew = simulate_dropdown_options([], $limits, $standardVaccines);
assertTest($optsNew['BCG']['disabled'] === false, 'New infant: BCG is selectable');
assertTest($optsNew['Pentavalent (DTP-HepB-Hib)']['disabled'] === false, 'New infant: Pentavalent is selectable');
assertTest($optsNew['Others']['disabled'] === false, 'New infant: Others is selectable');

// Scenario B: Infant took 1 BCG dose
$optsAfterBCG = simulate_dropdown_options(['BCG' => 1], $limits, $standardVaccines);
assertTest($optsAfterBCG['BCG']['disabled'] === true, 'After 1 BCG dose: BCG is disabled (limit 1 reached)');
assertTest(str_contains($optsAfterBCG['BCG']['label'], 'Limit Reached: 1/1'), 'Label shows Limit Reached: 1/1');
assertTest($optsAfterBCG['Hepatitis B']['disabled'] === false, 'Hepatitis B is still selectable');

// Scenario C: Infant took 2 doses of Pentavalent
$optsAfter2Penta = simulate_dropdown_options(['Pentavalent (DTP-HepB-Hib)' => 2], $limits, $standardVaccines);
assertTest($optsAfter2Penta['Pentavalent (DTP-HepB-Hib)']['disabled'] === false, 'After 2 Pentavalent doses: Pentavalent is still selectable (2/3)');

// Scenario D: Infant took 3 doses of Pentavalent
$optsAfter3Penta = simulate_dropdown_options(['Pentavalent (DTP-HepB-Hib)' => 3], $limits, $standardVaccines);
assertTest($optsAfter3Penta['Pentavalent (DTP-HepB-Hib)']['disabled'] === true, 'After 3 Pentavalent doses: Pentavalent is disabled (limit 3 reached)');
assertTest(str_contains($optsAfter3Penta['Pentavalent (DTP-HepB-Hib)']['label'], 'Limit Reached: 3/3'), 'Label shows Limit Reached: 3/3');

// Scenario E: Infant took 2 doses of IPV
$optsAfter2IPV = simulate_dropdown_options(['IPV (Inactivated Polio Vaccine)' => 2], $limits, $standardVaccines);
assertTest($optsAfter2IPV['IPV (Inactivated Polio Vaccine)']['disabled'] === true, 'After 2 IPV doses: IPV is disabled (limit 2 reached)');

// Scenario F: Infant took 1 dose of MMR
$optsAfter1MMR = simulate_dropdown_options(['MMR (Measles, Mumps, Rubella)' => 1], $limits, $standardVaccines);
assertTest($optsAfter1MMR['MMR (Measles, Mumps, Rubella)']['disabled'] === true, 'After 1 MMR dose: MMR is disabled (limit 1 reached)');

echo "\nSummary: {$passCount} PASSED, {$failCount} FAILED\n";

if ($failCount > 0) {
    exit(1);
}
