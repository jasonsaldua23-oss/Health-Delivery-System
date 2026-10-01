<?php

declare(strict_types=1);

/**
 * ONE-TIME CLEANUP: remove past infant immunization records so new ones all use the finished
 * infant immunization flow. Delete this file from the server once it has been run.
 *
 * Removes:
 *   - Completed immunization appointments booked for an infant (recipient is not "Self")
 *   - Patient notifications linked to those appointments
 *   - Booking-time infant records (immunized_infants) linked to those appointments, or not linked to any appointment
 *   - All infant profiles (infant_profiles) and manually encoded vaccines (infant_manual_vaccines)
 * Keeps: account holders, "Self" immunizations, every other service, and infant bookings that are not completed yet.
 *
 * Usage:
 *   Browser: log in as admin, open /Admin/cleanup_infant_records.php, review the preview, then confirm.
 *   CLI:     php Admin/cleanup_infant_records.php            (preview)
 *            php Admin/cleanup_infant_records.php --confirm  (delete)
 */

require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';

$isCli = PHP_SAPI === 'cli';

if (!$isCli && (empty($_SESSION['admin_authenticated']) || !is_string($_SESSION['admin_email'] ?? null))) {
    http_response_code(403);
    echo 'Forbidden: log in to the admin portal first.';
    exit;
}

function cleanup_table_exists(mysqli $db, string $table): bool
{
    $safe = $db->real_escape_string($table);
    $res = $db->query("SHOW TABLES LIKE '{$safe}'");
    return $res instanceof mysqli_result && $res->num_rows > 0;
}

/**
 * Collect everything the cleanup would delete, without changing anything.
 */
function cleanup_infant_plan(mysqli $db): array
{
    $appointments = [];
    $res = $db->query(
        "SELECT * FROM " . DB_TABLE_APPOINTMENTS . "
         WHERE status = 'Completed'
           AND (service_slug LIKE '%immuniz%' OR service_slug LIKE '%vaccin%' OR service_name LIKE '%immuniz%' OR service_name LIKE '%vaccin%')
         ORDER BY preferred_date, id"
    );
    while ($row = $res->fetch_assoc()) {
        if (appointment_is_infant_immunization($row)) {
            $appointments[] = $row;
        }
    }
    $apptIds = array_map(static fn(array $a): int => (int) $a['id'], $appointments);
    $apptCodes = array_values(array_filter(array_map(
        static fn(array $a): string => (string) ($a['appointment_code'] ?? ''),
        $appointments
    )));

    $immunized = [];
    if (cleanup_table_exists($db, DB_TABLE_IMMUNIZED_INFANTS)) {
        $res = $db->query("SELECT * FROM " . DB_TABLE_IMMUNIZED_INFANTS);
        while ($row = $res->fetch_assoc()) {
            $linkedId = (int) ($row['appointment_id'] ?? 0);
            $linkedCode = trim((string) ($row['appointment_code'] ?? ''));
            $unlinked = $linkedId === 0 && $linkedCode === '';
            if ($unlinked || in_array($linkedId, $apptIds, true) || ($linkedCode !== '' && in_array($linkedCode, $apptCodes, true))) {
                $immunized[] = $row;
            }
        }
    }

    $notifications = 0;
    if (!empty($apptIds) && cleanup_table_exists($db, DB_TABLE_APPOINTMENT_NOTIFICATIONS)) {
        $idList = implode(',', $apptIds);
        $notifications = (int) $db->query("SELECT COUNT(*) c FROM " . DB_TABLE_APPOINTMENT_NOTIFICATIONS . " WHERE appointment_id IN ({$idList})")->fetch_assoc()['c'];
    }

    $profiles = cleanup_table_exists($db, DB_TABLE_INFANT_PROFILES)
        ? $db->query("SELECT id, patient_id, first_name, last_name, birth_date FROM " . DB_TABLE_INFANT_PROFILES . " ORDER BY patient_id, first_name")->fetch_all(MYSQLI_ASSOC)
        : [];
    $manualVaccines = cleanup_table_exists($db, 'infant_manual_vaccines')
        ? (int) $db->query("SELECT COUNT(*) c FROM infant_manual_vaccines")->fetch_assoc()['c']
        : 0;

    return [
        'appointments' => $appointments,
        'immunized' => $immunized,
        'notifications' => $notifications,
        'profiles' => $profiles,
        'manual_vaccines' => $manualVaccines,
    ];
}

/**
 * Delete everything in the plan in one transaction; nothing is deleted if any step fails.
 */
function cleanup_infant_execute(mysqli $db, array $plan): array
{
    $apptIds = array_map(static fn(array $a): int => (int) $a['id'], $plan['appointments']);
    $immunizedIds = array_map(static fn(array $r): int => (int) $r['id'], $plan['immunized']);
    $counts = [];

    $db->begin_transaction();
    try {
        if (!empty($apptIds)) {
            $idList = implode(',', $apptIds);
            if (cleanup_table_exists($db, DB_TABLE_APPOINTMENT_NOTIFICATIONS)) {
                $db->query("DELETE FROM " . DB_TABLE_APPOINTMENT_NOTIFICATIONS . " WHERE appointment_id IN ({$idList})");
                $counts['notifications'] = $db->affected_rows;
            }
            $db->query("DELETE FROM " . DB_TABLE_APPOINTMENTS . " WHERE status = 'Completed' AND id IN ({$idList})");
            $counts['appointments'] = $db->affected_rows;
        }
        if (!empty($immunizedIds)) {
            $db->query("DELETE FROM " . DB_TABLE_IMMUNIZED_INFANTS . " WHERE id IN (" . implode(',', $immunizedIds) . ")");
            $counts['immunized'] = $db->affected_rows;
        }
        if (cleanup_table_exists($db, 'infant_manual_vaccines')) {
            $db->query("DELETE FROM infant_manual_vaccines");
            $counts['manual_vaccines'] = $db->affected_rows;
        }
        if (cleanup_table_exists($db, DB_TABLE_INFANT_PROFILES)) {
            $db->query("DELETE FROM " . DB_TABLE_INFANT_PROFILES);
            $counts['profiles'] = $db->affected_rows;
        }
        $db->commit();
    } catch (Throwable $e) {
        $db->rollback();
        throw $e;
    }

    return $counts;
}

$db = db();
$confirmed = $isCli
    ? in_array('--confirm', $argv ?? [], true)
    : ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['confirm'] ?? '') === 'DELETE' && verify_csrf($_POST['csrf_token'] ?? null));

$plan = cleanup_infant_plan($db);
$result = null;
$error = '';
if ($confirmed) {
    try {
        $result = cleanup_infant_execute($db, $plan);
        log_activity('admin', (string) ($_SESSION['admin_email'] ?? 'cli'), 'infant_records_cleared', 'infant', '', '', json_encode($result), '');
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

if ($isCli) {
    echo "Infant record cleanup (" . ($confirmed ? 'DELETE' : 'PREVIEW') . ")\n";
    echo "Completed infant immunization appointments: " . count($plan['appointments']) . "\n";
    foreach ($plan['appointments'] as $a) {
        echo "  #{$a['id']} {$a['appointment_code']} {$a['preferred_date']} patient {$a['patient_id']} -> "
            . trim(($a['recipient_first_name'] ?? '') . ' ' . ($a['recipient_last_name'] ?? '')) . " ({$a['vaccine_type']})\n";
    }
    echo "Linked notifications: {$plan['notifications']}\n";
    echo "Booking-time infant records: " . count($plan['immunized']) . "\n";
    echo "Infant profiles: " . count($plan['profiles']) . "\n";
    echo "Manually encoded vaccines: {$plan['manual_vaccines']}\n";
    if ($error !== '') {
        echo "FAILED, nothing was deleted: {$error}\n";
        exit(1);
    }
    echo $result !== null ? "Deleted: " . json_encode($result) . "\n" : "Nothing deleted. Re-run with --confirm to delete.\n";
    exit(0);
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Infant Record Cleanup</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #f8fafc; color: #0f172a; margin: 0; padding: 24px 16px; }
        main { max-width: 860px; margin: 0 auto; background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 24px; }
        h1 { margin-top: 0; font-size: 1.4rem; }
        table { width: 100%; border-collapse: collapse; font-size: 0.88rem; margin: 8px 0 18px; }
        th, td { text-align: left; padding: 6px 8px; border-bottom: 1px solid #e2e8f0; }
        .summary li { margin: 4px 0; }
        .warn { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 14px; border-radius: 10px; }
        .ok { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px 14px; border-radius: 10px; }
        button { background: #dc2626; color: #fff; border: 0; padding: 10px 18px; border-radius: 10px; font-weight: 700; cursor: pointer; }
        a { color: #2563eb; }
    </style>
</head>
<body>
<main>
    <h1>Infant Record Cleanup</h1>

    <?php if ($error !== ''): ?>
        <p class="warn">The cleanup failed and nothing was deleted: <?= h($error); ?></p>
    <?php elseif ($result !== null): ?>
        <div class="ok">
            <strong>Cleanup complete.</strong>
            Deleted <?= (int) ($result['appointments'] ?? 0); ?> completed infant immunization appointment(s),
            <?= (int) ($result['notifications'] ?? 0); ?> notification(s),
            <?= (int) ($result['immunized'] ?? 0); ?> booking-time infant record(s),
            <?= (int) ($result['profiles'] ?? 0); ?> infant profile(s) and
            <?= (int) ($result['manual_vaccines'] ?? 0); ?> encoded vaccine(s).
            <p style="margin-bottom:0;">Please delete <code>Admin/cleanup_infant_records.php</code> from the server now. <a href="index.php">Back to admin</a></p>
        </div>
    <?php else: ?>
        <p>This preview shows everything that will be <strong>permanently deleted</strong>. Account holders, "Self" immunizations, other services, and infant bookings that are not completed yet are kept.</p>

        <ul class="summary">
            <li><strong><?= count($plan['appointments']); ?></strong> completed infant immunization appointment(s)</li>
            <li><strong><?= (int) $plan['notifications']; ?></strong> patient notification(s) linked to them</li>
            <li><strong><?= count($plan['immunized']); ?></strong> booking-time infant record(s)</li>
            <li><strong><?= count($plan['profiles']); ?></strong> infant profile(s)</li>
            <li><strong><?= (int) $plan['manual_vaccines']; ?></strong> manually encoded vaccine(s)</li>
        </ul>

        <?php if (!empty($plan['appointments'])): ?>
            <h2 style="font-size:1rem;">Appointments</h2>
            <table>
                <tr><th>Code</th><th>Date</th><th>Account holder</th><th>Infant</th><th>Vaccine</th><th>Station</th></tr>
                <?php foreach ($plan['appointments'] as $a): ?>
                    <tr>
                        <td><?= h((string) ($a['appointment_code'] ?: $a['reference_code'])); ?></td>
                        <td><?= h((string) $a['preferred_date']); ?></td>
                        <td><?= h(trim(($a['first_name'] ?? '') . ' ' . ($a['last_name'] ?? ''))); ?> (#<?= h((string) $a['patient_id']); ?>)</td>
                        <td><?= h(trim(($a['recipient_first_name'] ?? '') . ' ' . ($a['recipient_last_name'] ?? ''))); ?></td>
                        <td><?= h((string) ($a['vaccine_type'] ?? '')); ?></td>
                        <td><?= h((string) ($a['station_name'] ?? '')); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

        <?php if (!empty($plan['profiles'])): ?>
            <h2 style="font-size:1rem;">Infant profiles</h2>
            <table>
                <tr><th>Infant</th><th>Birth date</th><th>Account holder</th></tr>
                <?php foreach ($plan['profiles'] as $p): ?>
                    <tr>
                        <td><?= h(trim($p['first_name'] . ' ' . $p['last_name'])); ?></td>
                        <td><?= h((string) $p['birth_date']); ?></td>
                        <td>#<?= h((string) $p['patient_id']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

        <form method="post" onsubmit="return confirm('Permanently delete these infant records? This cannot be undone.');">
            <?= csrf_field(); ?>
            <input type="hidden" name="confirm" value="DELETE">
            <p class="warn">This cannot be undone. Consider exporting a backup of the database (hPanel → Databases → phpMyAdmin → Export) first.</p>
            <button type="submit">Delete these infant records</button>
        </form>
    <?php endif; ?>
</main>
</body>
</html>
