<?php

declare(strict_types=1);

function geofence_is_bypassed(): bool
{
    $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
    if ($host === 'localhost' || str_starts_with($host, '127.0.0.1') || str_starts_with($host, '::1')) {
        return true;
    }

    $remote = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    return in_array($remote, ['127.0.0.1', '::1'], true);
}

function geofence_allowed_regions(): array
{
    return [
        [
            'name' => 'Bacolod City',
            'min_lat' => 10.580,
            'max_lat' => 10.795,
            'min_lng' => 122.885,
            'max_lng' => 123.065,
        ],
        [
            'name' => 'Talisay City',
            'min_lat' => 10.655,
            'max_lat' => 10.820,
            'min_lng' => 122.895,
            'max_lng' => 123.040,
        ],
    ];
}

function geofence_point_is_allowed(float $lat, float $lng): bool
{
    foreach (geofence_allowed_regions() as $region) {
        if (
            $lat >= $region['min_lat']
            && $lat <= $region['max_lat']
            && $lng >= $region['min_lng']
            && $lng <= $region['max_lng']
        ) {
            return true;
        }
    }

    return false;
}

function geofence_parse_coordinates(?string $lat, ?string $lng): ?array
{
    if ($lat === null || $lng === null) {
        return null;
    }

    $latValue = filter_var($lat, FILTER_VALIDATE_FLOAT);
    $lngValue = filter_var($lng, FILTER_VALIDATE_FLOAT);

    if ($latValue === false || $lngValue === false) {
        return null;
    }

    return ['lat' => (float) $latValue, 'lng' => (float) $lngValue];
}

function geofence_session_is_verified(): bool
{
    return !empty($_SESSION['geofence_verified']);
}

function geofence_mark_session_verified(float $lat, float $lng): void
{
    $_SESSION['geofence_verified'] = true;
    $_SESSION['geofence_lat'] = $lat;
    $_SESSION['geofence_lng'] = $lng;
    $_SESSION['geofence_verified_at'] = time();
}

function geofence_attempt_verification(?string $lat, ?string $lng): array
{
    if (geofence_is_bypassed()) {
        geofence_mark_session_verified(10.676, 122.951);

        return ['ok' => true, 'message' => ''];
    }

    $coordinates = geofence_parse_coordinates($lat, $lng);
    if ($coordinates === null) {
        return [
            'ok' => false,
            'message' => 'Location is required. Please allow your browser to access your location.',
        ];
    }

    if (!geofence_point_is_allowed($coordinates['lat'], $coordinates['lng'])) {
        return [
            'ok' => false,
            'message' => 'Access is restricted to Bacolod City and Talisay City, Negros Occidental only.',
        ];
    }

    geofence_mark_session_verified($coordinates['lat'], $coordinates['lng']);

    return ['ok' => true, 'message' => ''];
}

function geofence_handle_verify_request(): void
{
    if (($_POST['action'] ?? '') !== 'verify_geofence') {
        return;
    }

    $result = geofence_attempt_verification(
        isset($_POST['geo_lat']) ? (string) $_POST['geo_lat'] : null,
        isset($_POST['geo_lng']) ? (string) $_POST['geo_lng'] : null
    );

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) || str_contains((string) ($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json')) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($result, JSON_THROW_ON_ERROR);
        exit;
    }

    if ($result['ok']) {
        header('Location: ' . (string) ($_POST['redirect_to'] ?? $_SERVER['REQUEST_URI'] ?? 'index.php'));
        exit;
    }

    $_SESSION['geofence_error'] = $result['message'];
    header('Location: ' . (string) ($_SERVER['REQUEST_URI'] ?? 'index.php'));
    exit;
}

function geofence_require_verified_session(string $redirectTo = ''): void
{
    if (geofence_session_is_verified()) {
        return;
    }

    geofence_render_gate($redirectTo !== '' ? $redirectTo : (string) ($_SERVER['REQUEST_URI'] ?? 'index.php'));
    exit;
}

function geofence_render_gate(string $redirectTo, string $title = 'Location Verification'): void
{
    $error = (string) ($_SESSION['geofence_error'] ?? '');
    unset($_SESSION['geofence_error']);
    $safeRedirect = htmlspecialchars($redirectTo, ENT_QUOTES, 'UTF-8');
    $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $safeError = htmlspecialchars($error, ENT_QUOTES, 'UTF-8');
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $safeTitle; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { margin: 0; font-family: 'Outfit', sans-serif; background: linear-gradient(180deg, #f5fbff 0%, #eef5fb 100%); color: #0f2240; }
        .geofence-shell { min-height: 100vh; display: grid; place-items: center; padding: 24px; }
        .geofence-card { width: min(520px, 100%); padding: 32px; border-radius: 24px; background: #fff; border: 1px solid #dbe7f3; box-shadow: 0 18px 40px rgba(15, 34, 64, 0.1); }
        .geofence-card h1 { margin: 0 0 12px; font-size: 1.8rem; }
        .geofence-card p { margin: 0 0 18px; color: #54657d; line-height: 1.55; }
        .geofence-error { margin-bottom: 16px; padding: 12px 14px; border-radius: 12px; background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; }
        .geofence-btn { width: 100%; border: 0; border-radius: 16px; padding: 16px 18px; background: #0db273; color: #fff; font: inherit; font-weight: 700; cursor: pointer; }
        .geofence-btn:disabled { opacity: 0.7; cursor: wait; }
        .geofence-note { margin-top: 14px; font-size: 0.92rem; color: #61758f; }
    </style>
</head>
<body>
    <div class="geofence-shell">
        <div class="geofence-card">
            <h1><?= $safeTitle; ?></h1>
            <p>This system can only be used within <strong>Bacolod City</strong> and <strong>Talisay City</strong>, Negros Occidental. Please allow location access to continue.</p>
            <?php if ($safeError !== ''): ?><div class="geofence-error"><?= $safeError; ?></div><?php endif; ?>
            <form method="post" class="geofence-verify-form" data-geofence-form>
                <input type="hidden" name="action" value="verify_geofence">
                <input type="hidden" name="redirect_to" value="<?= $safeRedirect; ?>">
                <input type="hidden" name="geo_lat" value="">
                <input type="hidden" name="geo_lng" value="">
                <button type="submit" class="geofence-btn">Verify My Location</button>
            </form>
            <p class="geofence-note">Your coordinates are checked once per session and are not stored permanently.</p>
        </div>
    </div>
    <script src="../shared/assets/geofence.js"></script>
</body>
</html>
    <?php
}
