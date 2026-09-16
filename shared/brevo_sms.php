<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/config.php';

function sendBrevoSMS(string $phone, string $message, int $appointmentId = 0): bool
{
    $apiKey = defined('BREVO_API_KEY') ? (string) BREVO_API_KEY : (getenv('BREVO_API_KEY') ?: '');
    $logsDir = dirname(__DIR__) . '/logs';
    if (!is_dir($logsDir)) {
        @mkdir($logsDir, 0755, true);
    }
    $logFile = $logsDir . '/sms.log';

    if ($apiKey === '') {
        $logEntry = date('Y-m-d H:i:s') . " | Appointment ID: {$appointmentId} | Phone: {$phone} | HTTP Code: 0 | Response: None | Error: BREVO_API_KEY is not configured or empty\n";
        @file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
        return false;
    }

    // Normalize Philippine phone number format (handling spaces, hyphens, prefixes)
    $cleanPhone = preg_replace('/[^\d+]/', '', trim($phone));
    if (preg_match('/^0(\d{10})$/', $cleanPhone, $matches)) {
        $formattedPhone = '+63' . $matches[1];
    } elseif (preg_match('/^63(\d{10})$/', $cleanPhone, $matches)) {
        $formattedPhone = '+63' . $matches[1];
    } elseif (preg_match('/^9(\d{9})$/', $cleanPhone, $matches)) {
        $formattedPhone = '+639' . $matches[1];
    } elseif (preg_match('/^\+63(\d{10})$/', $cleanPhone, $matches)) {
        $formattedPhone = '+63' . $matches[1];
    } else {
        $formattedPhone = $cleanPhone;
    }

    if ($formattedPhone === '' || strlen($formattedPhone) < 10) {
        $logEntry = date('Y-m-d H:i:s') . " | Appointment ID: {$appointmentId} | Phone: {$phone} | HTTP Code: 0 | Response: None | Error: Invalid recipient phone number format\n";
        @file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
        return false;
    }

    $payload = [
        'sender' => 'HealthSys',
        'recipient' => $formattedPhone,
        'content' => $message
    ];

    if (!function_exists('curl_init')) {
        $logEntry = date('Y-m-d H:i:s') . " | Appointment ID: {$appointmentId} | Phone: {$formattedPhone} | HTTP Code: 0 | Response: None | Error: cURL extension not available\n";
        @file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
        return false;
    }

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://api.brevo.com/v3/transactionalSMS/sms',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_HTTPHEADER => [
            'accept: application/json',
            'api-key: ' . $apiKey,
            'content-type: application/json'
        ],
        CURLOPT_POSTFIELDS => json_encode($payload)
    ]);

    $response = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    $logEntry = date('Y-m-d H:i:s') . " | Appointment ID: {$appointmentId} | Phone: {$formattedPhone} | HTTP Code: {$httpCode} | Response: {$response} | Error: {$error}\n";
    @file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

    return ($httpCode >= 200 && $httpCode < 300);
}
