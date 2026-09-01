<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

function sendBrevoSMS(string $phone, string $message, int $appointmentId = 0): bool
{
    $apiKey = defined('BREVO_API_KEY') ? BREVO_API_KEY : (getenv('BREVO_API_KEY') ?: '');
    if ($apiKey === '') {
        return false;
    }

    // Convert Philippine format (09XXXXXXXXX) to international format (+639XXXXXXXXX)
    if (preg_match('/^0(\d{10})$/', $phone, $matches)) {
        $phone = '+63' . $matches[1];
    }

    $payload = [
        'sender' => 'HealthSys',
        'recipient' => $phone,
        'content' => $message
    ];

    if (!function_exists('curl_init')) {
        return false;
    }

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://api.brevo.com/v3/transactionalSMS/sms',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_TIMEOUT => 10,
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

    // Log SMS attempt to protected /logs directory
    $logsDir = dirname(__DIR__) . '/logs';
    if (!is_dir($logsDir)) {
        @mkdir($logsDir, 0755, true);
    }
    $logFile = $logsDir . '/sms.log';
    $logEntry = date('Y-m-d H:i:s') . " | Appointment ID: {$appointmentId} | Phone: {$phone} | HTTP Code: {$httpCode} | Response: {$response} | Error: {$error}\n";
    @file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

    return ($httpCode >= 200 && $httpCode < 300);
}
