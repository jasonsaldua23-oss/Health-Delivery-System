<?php

$html = file_get_contents(__DIR__ . '/../Patients/index.php');
$css = file_get_contents(__DIR__ . '/../Patients/assets/css/styles.css');

echo "=== Testing Modal Markup & CSS ===" . PHP_EOL;

// 1. Check CSS .hidden-step rule
if (strpos($css, '.hidden-step') !== false && strpos($css, 'display: none !important') !== false) {
    echo "✓ CSS correctly contains .hidden-step display: none !important" . PHP_EOL;
} else {
    echo "✗ Missing CSS .hidden-step rule" . PHP_EOL;
}

// 2. Check Volunteer modal
if (strpos($html, 'id="volunteerLoginView" class="modal-step"') !== false) {
    echo "✓ Volunteer Login View has modal-step" . PHP_EOL;
} else {
    echo "✗ Volunteer Login View missing modal-step" . PHP_EOL;
}

if (strpos($html, 'id="volunteerForgotView" class="modal-step hidden-step"') !== false) {
    echo "✓ Volunteer Forgot View has modal-step hidden-step" . PHP_EOL;
} else {
    echo "✗ Volunteer Forgot View missing modal-step hidden-step" . PHP_EOL;
}

if (strpos($html, 'id="volunteerForgotResetView" class="modal-substep hidden-step"') !== false) {
    echo "✓ Volunteer Forgot Reset View has modal-substep hidden-step" . PHP_EOL;
} else {
    echo "✗ Volunteer Forgot Reset View missing modal-substep hidden-step" . PHP_EOL;
}

// 3. Check Admin modal
if (strpos($html, 'id="adminLoginView" class="modal-step"') !== false) {
    echo "✓ Admin Login View has modal-step" . PHP_EOL;
} else {
    echo "✗ Admin Login View missing modal-step" . PHP_EOL;
}

if (strpos($html, 'id="adminForgotView" class="modal-step hidden-step"') !== false) {
    echo "✓ Admin Forgot View has modal-step hidden-step" . PHP_EOL;
} else {
    echo "✗ Admin Forgot View missing modal-step hidden-step" . PHP_EOL;
}

if (strpos($html, 'id="adminForgotResetView" class="modal-substep hidden-step"') !== false) {
    echo "✓ Admin Forgot Reset View has modal-substep hidden-step" . PHP_EOL;
} else {
    echo "✗ Admin Forgot Reset View missing modal-substep hidden-step" . PHP_EOL;
}

// 4. Check Patient modal
if (strpos($html, 'id="patientForgotStep" class="modal-step hidden-step"') !== false) {
    echo "✓ Patient Forgot Step has modal-step hidden-step" . PHP_EOL;
} else {
    echo "✗ Patient Forgot Step missing modal-step hidden-step" . PHP_EOL;
}

if (strpos($html, 'id="patientForgotResetView" class="modal-substep hidden-step"') !== false) {
    echo "✓ Patient Forgot Reset View has modal-substep hidden-step" . PHP_EOL;
} else {
    echo "✗ Patient Forgot Reset View missing modal-substep hidden-step" . PHP_EOL;
}

echo "=== All checks completed ===" . PHP_EOL;
