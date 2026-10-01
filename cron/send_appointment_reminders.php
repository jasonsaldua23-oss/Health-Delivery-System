<?php

declare(strict_types=1);

/**
 * Automated Cron Script: Send day-of SMS for staff-scheduled follow-up consultations.
 * (Confirm/cancel SMS are sent instantly when staff change an appointment's status.)
 *
 * Usage:
 *   CLI:  php cron/send_appointment_reminders.php
 *   CLI with custom date: php cron/send_appointment_reminders.php 2026-09-08
 *   HTTP: GET /cron/send_appointment_reminders.php?key=YOUR_CRON_KEY
 */

require_once dirname(__DIR__) . '/shared/database.php';

// Optional simple security key check for HTTP invocation
if (PHP_SAPI !== 'cli') {
    $cronKey = defined('CRON_SECRET_KEY') ? CRON_SECRET_KEY : (getenv('CRON_SECRET_KEY') ?: '');
    if ($cronKey !== '') {
        $passedKey = $_GET['key'] ?? $_SERVER['HTTP_X_CRON_KEY'] ?? '';
        if (!hash_equals($cronKey, (string) $passedKey)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Forbidden: Invalid cron key']);
            exit;
        }
    }
}

$targetDate = null;
if (PHP_SAPI === 'cli' && isset($argv[1]) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $argv[1])) {
    $targetDate = $argv[1];
} elseif (isset($_GET['date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $_GET['date'])) {
    $targetDate = (string) $_GET['date'];
}

$result = send_follow_up_day_sms_due($targetDate);

if (PHP_SAPI === 'cli') {
    echo "====================================================\n";
    echo " Health Delivery System - Follow-up Day SMS Cron    \n";
    echo "====================================================\n";
    echo "Follow-up Date:          " . $result['target_date'] . "\n";
    echo "Follow-ups Processed:    " . $result['processed'] . "\n";
    echo "SMS Dispatched:          " . $result['sent'] . "\n";
    echo "Failed / Logged:         " . $result['failed'] . "\n";
    echo "Timestamp:               " . date('Y-m-d H:i:s') . "\n";
    echo "====================================================\n";
    exit(0);
}

header('Content-Type: application/json');
echo json_encode([
    'status' => 'success',
    'timestamp' => date('Y-m-d H:i:s'),
    'summary' => $result
], JSON_PRETTY_PRINT);
