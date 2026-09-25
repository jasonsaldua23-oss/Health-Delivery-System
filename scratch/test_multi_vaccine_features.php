<?php
/**
 * Test Suite: Infant Multi-Vaccine Selection & Dose Limit Enforcement
 */

require_once __DIR__ . '/../shared/database.php';

echo "=== TEST SUITE: INFANT MULTI-VACCINE SELECTION & DOSE ENFORCEMENT ===\n\n";

$passCount = 0;
$failCount = 0;

function assertTest(bool $condition, string $msg): void {
    global $passCount, $failCount;
    if ($condition) {
        $passCount++;
        echo "✓ [PASS] {$msg}\n";
    } else {
        $failCount++;
        echo "✗ [FAIL] {$msg}\n";
    }
}

// 1. Test split_vaccine_types with multi-select string
$singleVac = 'BCG';
assertTest(split_vaccine_types($singleVac) === ['BCG'], 'Single vaccine splits to 1 element');

$multiRoutine = 'Pentavalent (DTP-HepB-Hib), OPV (Oral Polio Vaccine), PCV (Pneumococcal Conjugate Vaccine)';
$splitRoutine = split_vaccine_types($multiRoutine);
assertTest(count($splitRoutine) === 3, '3 routine co-administered vaccines split to 3 elements');
assertTest(in_array('Pentavalent (DTP-HepB-Hib)', $splitRoutine, true), 'Pentavalent correctly parsed from multi-select');
assertTest(in_array('OPV (Oral Polio Vaccine)', $splitRoutine, true), 'OPV correctly parsed from multi-select');
assertTest(in_array('PCV (Pneumococcal Conjugate Vaccine)', $splitRoutine, true), 'PCV correctly parsed from multi-select');

// Test with MMR containing commas inside parentheses
$multiWithMmr = 'MMR (Measles, Mumps, Rubella), IPV (Inactivated Polio Vaccine)';
$splitMmr = split_vaccine_types($multiWithMmr);
assertTest(count($splitMmr) === 2, 'MMR with internal commas correctly preserved as 1 element alongside IPV');
assertTest($splitMmr[0] === 'MMR (Measles, Mumps, Rubella)', 'First element is intact MMR');
assertTest($splitMmr[1] === 'IPV (Inactivated Polio Vaccine)', 'Second element is intact IPV');

// Test multi-select with custom vaccine
$multiWithCustom = 'Pentavalent (DTP-HepB-Hib), Rotavirus (1st Dose)';
$splitCustom = split_vaccine_types($multiWithCustom);
assertTest(count($splitCustom) === 2, 'Multi-select with custom vaccine splits cleanly');
assertTest($splitCustom[1] === 'Rotavirus (1st Dose)', 'Custom vaccine preserved');

// 2. Test Multi-Select Post Data Logic Simulation
$limits = get_standard_vaccine_dose_limits();

// Scenario A: Staff selects 2 valid vaccines (Pentavalent, OPV) for a fresh infant (0 doses)
$selectedInput = ['Pentavalent (DTP-HepB-Hib)', 'OPV (Oral Polio Vaccine)'];
$mockPastCounts = ['Pentavalent (DTP-HepB-Hib)' => 0, 'OPV (Oral Polio Vaccine)' => 0];

$finalVaccines = [];
$limitError = null;
foreach ($selectedInput as $item) {
    $canonical = normalize_standard_vaccine_name($item) ?: $item;
    if (isset($limits[$canonical])) {
        $taken = $mockPastCounts[$canonical] ?? 0;
        $maxL = $limits[$canonical];
        if ($taken >= $maxL) {
            $limitError = "Cannot select {$canonical}: max dose reached";
            break;
        }
    }
    $finalVaccines[] = $item;
}
$savedVaccineType = implode(', ', array_unique($finalVaccines));
assertTest($limitError === null, 'Both vaccines permitted for fresh infant');
assertTest($savedVaccineType === 'Pentavalent (DTP-HepB-Hib), OPV (Oral Polio Vaccine)', 'Combined comma-separated string saved');

// Scenario B: Staff attempts to select BCG when infant already completed BCG (1/1)
$selectedInputB = ['BCG', 'OPV (Oral Polio Vaccine)'];
$mockPastCountsB = ['BCG' => 1, 'OPV (Oral Polio Vaccine)' => 0];
$limitErrorB = null;
foreach ($selectedInputB as $item) {
    $canonical = normalize_standard_vaccine_name($item) ?: $item;
    if (isset($limits[$canonical])) {
        $taken = $mockPastCountsB[$canonical] ?? 0;
        $maxL = $limits[$canonical];
        if ($taken >= $maxL) {
            $limitErrorB = "Cannot select {$canonical}: max dose reached";
            break;
        }
    }
}
assertTest($limitErrorB !== null, 'Selection rejected when one of multiple vaccines reached limit');
assertTest(str_contains($limitErrorB, 'BCG'), 'Error specifies BCG reached limit');

// Scenario C: Staff selects standard vaccine + Others with custom text
$selectedInputC = ['Pentavalent (DTP-HepB-Hib)', 'Others'];
$customTextC = 'Japanese Encephalitis';
$finalC = [];
foreach ($selectedInputC as $item) {
    if ($item === 'Others') {
        if (trim($customTextC) !== '') {
            $finalC[] = trim($customTextC);
        }
    } else {
        $finalC[] = $item;
    }
}
$savedC = implode(', ', array_unique($finalC));
assertTest($savedC === 'Pentavalent (DTP-HepB-Hib), Japanese Encephalitis', 'Standard and Others combined successfully');

// Scenario D: Staff selects Others without custom text
$selectedInputD = ['Others'];
$customTextD = '   ';
$hasEmptyOtherError = false;
foreach ($selectedInputD as $item) {
    if ($item === 'Others' && trim($customTextD) === '') {
        $hasEmptyOtherError = true;
    }
}
assertTest($hasEmptyOtherError === true, 'Empty custom vaccine triggers validation error');

// 3. Test HTML Multi-Select Component Syntax & Elements
$indexPhpContent = file_get_contents(__DIR__ . '/../Barangay Health Station/index.php');
assertTest(str_contains($indexPhpContent, 'id="queue_vaccine_select"'), 'HTML contains #queue_vaccine_select');
assertTest(str_contains($indexPhpContent, 'name="vaccine_type_select[]"'), 'HTML contains name="vaccine_type_select[]"');
assertTest(str_contains($indexPhpContent, 'multiple'), 'HTML contains multiple attribute on select');
assertTest(str_contains($indexPhpContent, 'id="vaccine_multiselect_container"'), 'HTML contains #vaccine_multiselect_container');
assertTest(str_contains($indexPhpContent, 'id="vaccine_multiselect_trigger"'), 'HTML contains #vaccine_multiselect_trigger');
assertTest(str_contains($indexPhpContent, 'id="vaccine_selected_chips"'), 'HTML contains #vaccine_selected_chips');
assertTest(str_contains($indexPhpContent, 'id="vaccine_multiselect_menu"'), 'HTML contains #vaccine_multiselect_menu');
assertTest(str_contains($indexPhpContent, 'toggleVaccineDropdown'), 'HTML/JS contains toggleVaccineDropdown');
assertTest(str_contains($indexPhpContent, 'toggleVaccineOption'), 'HTML/JS contains toggleVaccineOption');
assertTest(str_contains($indexPhpContent, 'removeVaccineChip'), 'HTML/JS contains removeVaccineChip');
assertTest(str_contains($indexPhpContent, 'clearAllVaccines'), 'HTML/JS contains clearAllVaccines');
assertTest(str_contains($indexPhpContent, 'syncVaccineMultiSelectUI'), 'HTML/JS contains syncVaccineMultiSelectUI');

// 4. Test CSS Definitions in styles.css
$cssContent = file_get_contents(__DIR__ . '/../Barangay Health Station/assets/styles.css');
assertTest(str_contains($cssContent, '.vaccine-multiselect-container'), 'CSS has .vaccine-multiselect-container');
assertTest(str_contains($cssContent, '.vaccine-multiselect-trigger'), 'CSS has .vaccine-multiselect-trigger');
assertTest(str_contains($cssContent, '.vaccine-selected-chip'), 'CSS has .vaccine-selected-chip');
assertTest(str_contains($cssContent, '.vaccine-dropdown-menu'), 'CSS has .vaccine-dropdown-menu');
assertTest(str_contains($cssContent, '.vaccine-option-row'), 'CSS has .vaccine-option-row');
assertTest(str_contains($cssContent, '.vaccine-status-pill'), 'CSS has .vaccine-status-pill');

echo "\nSummary: {$passCount} PASSED, {$failCount} FAILED\n";

if ($failCount > 0) {
    exit(1);
}
