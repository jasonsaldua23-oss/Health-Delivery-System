<?php

declare(strict_types=1);

require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/config.php';
require_once __DIR__ . '/../shared/mailer.php';

echo "========================================================\n";
echo " Health Delivery System - SMTP Email Verification Tool  \n";
echo "========================================================\n\n";

$smtpHost = defined('SMTP_HOST') ? (string) SMTP_HOST : '';
$smtpPort = defined('SMTP_PORT') ? (int) SMTP_PORT : 465;
$smtpUser = defined('SMTP_USER') ? (string) SMTP_USER : '';
$smtpPass = defined('SMTP_PASS') ? (string) SMTP_PASS : '';
$mailFrom = defined('MAIL_FROM_ADDRESS') ? (string) MAIL_FROM_ADDRESS : '';

echo "Host:     {$smtpHost}:{$smtpPort}\n";
echo "User:     " . ($smtpUser !== '' ? $smtpUser : '(Not configured)') . "\n";
echo "From:     {$mailFrom}\n";
echo "Password: " . ($smtpPass !== '' && !str_contains($smtpPass, 'your_') ? '•••••••••••••••• (Configured)' : '(Not configured or placeholder)') . "\n\n";

if (str_contains($smtpUser, 'your_') || str_contains($smtpPass, 'your_') || $smtpUser === '' || $smtpPass === '') {
    echo "⚠️  Please update your real email address and password in .env:\n";
    echo "    SMTP_USER=no-reply@bsns.online\n";
    echo "    SMTP_PASS=your_email_account_password\n";
    echo "    MAIL_FROM_ADDRESS=no-reply@bsns.online\n\n";
    exit(1);
}

$recipient = $argv[1] ?? $smtpUser;
echo "Sending test verification OTP to: {$recipient} ...\n";

$testOtp = (string) random_int(100000, 999999);
$sent = sendPasswordResetOtpEmail($recipient, 'Health Delivery Test User', $testOtp, 'patient', 10);

if ($sent) {
    echo "\n🎉 SUCCESS! A test OTP email with code [{$testOtp}] was dispatched to {$recipient}.\n";
    echo "Check your inbox to confirm receipt!\n\n";
} else {
    echo "\n❌ FAILED to send email.\n";
    echo "Error detail: " . get_last_mail_error() . "\n\n";
    echo "Please verify:\n";
    echo "1. The email account exists in Hostinger hPanel -> Emails.\n";
    echo "2. The password in .env matches the password for that email account.\n";
    echo "3. Hostinger SMTP port is 465 (SSL) or 587 (TLS).\n\n";
    exit(1);
}
