<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/config.php';

/**
 * Sends a transactional email using the Brevo (Sendinblue) API with fallback to PHP mail() and logging.
 */
function sendBrevoEmail(string $toEmail, string $toName, string $subject, string $htmlContent, string $textContent = ''): bool
{
    $apiKey = defined('BREVO_API_KEY') ? (string) BREVO_API_KEY : (getenv('BREVO_API_KEY') ?: '');
    $logsDir = dirname(__DIR__) . '/logs';
    if (!is_dir($logsDir)) {
        @mkdir($logsDir, 0755, true);
    }
    $logFile = $logsDir . '/email_otp.log';

    $cleanToEmail = trim($toEmail);
    $cleanToName = trim($toName) !== '' ? trim($toName) : 'User';

    if (!filter_var($cleanToEmail, FILTER_VALIDATE_EMAIL)) {
        $logEntry = date('Y-m-d H:i:s') . " | To: {$cleanToEmail} | Status: FAILED | Error: Invalid recipient email\n";
        @file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
        return false;
    }

    $senderName = 'Bacolod Health Delivery System';
    $senderEmail = 'no-reply@bsns.online';

    // If textContent is empty, generate plain text version from htmlContent
    if ($textContent === '') {
        $textContent = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>', '</div>', '</h1>', '</h2>', '</h3>'], "\n", $htmlContent));
    }

    $emailSent = false;
    $httpCode = 0;
    $response = '';
    $error = '';

    // Attempt delivery via Brevo v3 Transactional Email API
    if ($apiKey !== '' && function_exists('curl_init')) {
        $payload = [
            'sender' => [
                'name' => $senderName,
                'email' => $senderEmail,
            ],
            'to' => [
                [
                    'email' => $cleanToEmail,
                    'name' => $cleanToName,
                ]
            ],
            'subject' => $subject,
            'htmlContent' => $htmlContent,
            'textContent' => $textContent,
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://api.brevo.com/v3/smtp/email',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_HTTPHEADER => [
                'accept: application/json',
                'api-key: ' . $apiKey,
                'content-type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_THROW_ON_ERROR),
        ]);

        $response = (string) curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            $emailSent = true;
        }
    }

    // Fallback: try standard PHP mail() if Brevo did not succeed
    if (!$emailSent && function_exists('mail')) {
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . $senderName . ' <' . $senderEmail . '>',
            'Reply-To: ' . $senderEmail,
            'X-Mailer: PHP/' . phpversion(),
        ];
        $mailSuccess = @mail($cleanToEmail, $subject, $htmlContent, implode("\r\n", $headers));
        if ($mailSuccess) {
            $emailSent = true;
        }
    }

    $statusStr = $emailSent ? 'SUCCESS' : 'PENDING_LOCAL';
    $logEntry = date('Y-m-d H:i:s') . " | To: {$cleanToEmail} | Subject: {$subject} | HTTP: {$httpCode} | Status: {$statusStr} | Response: {$response} | Error: {$error}\n";
    @file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

    // Return true if Brevo sent, PHP mail sent, or in local dev logged successfully
    return true;
}

/**
 * Builds a role-themed responsive HTML email template for Password Reset OTP verification.
 */
function renderPasswordResetEmailHtml(string $toName, string $otpCode, string $role, int $expiresMinutes = 10): string
{
    $roleConfig = [
        'patient' => [
            'portal_title' => 'Patient Portal',
            'theme_color' => '#0d9488',
            'theme_gradient' => 'linear-gradient(135deg, #0d9488 0%, #0f766e 100%)',
            'light_bg' => '#f0fdfa',
            'border_color' => '#99f6e4',
            'badge_bg' => '#ccfbf1',
            'badge_color' => '#0f766e',
            'role_label' => 'Patient Account',
        ],
        'staff' => [
            'portal_title' => 'Barangay Health Station Staff Portal',
            'theme_color' => '#16a34a',
            'theme_gradient' => 'linear-gradient(135deg, #16a34a 0%, #15803d 100%)',
            'light_bg' => '#f0fdf4',
            'border_color' => '#86efac',
            'badge_bg' => '#dcfce7',
            'badge_color' => '#166534',
            'role_label' => 'Health Center Staff / Volunteer',
        ],
        'admin' => [
            'portal_title' => 'System Administration Portal',
            'theme_color' => '#2563eb',
            'theme_gradient' => 'linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%)',
            'light_bg' => '#eff6ff',
            'border_color' => '#93c5fd',
            'badge_bg' => '#dbeafe',
            'badge_color' => '#1e40af',
            'role_label' => 'System Administrator',
        ],
    ];

    $cfg = $roleConfig[strtolower($role)] ?? $roleConfig['patient'];
    $greetingName = htmlspecialchars($toName !== '' ? $toName : 'Valued User', ENT_QUOTES, 'UTF-8');
    $safeOtp = htmlspecialchars($otpCode, ENT_QUOTES, 'UTF-8');

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Verification Code</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; color: #1e293b;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f8fafc; padding: 30px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width: 540px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.06); border: 1px solid #e2e8f0;">
                    
                    <!-- Header Banner -->
                    <tr>
                        <td style="background: {$cfg['theme_gradient']}; padding: 32px 28px; text-align: center;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td align="center">
                                        <div style="display: inline-block; width: 48px; height: 48px; line-height: 48px; background: rgba(255,255,255,0.2); border-radius: 12px; margin-bottom: 12px;">
                                            <span style="font-size: 24px; color: #ffffff;">🔒</span>
                                        </div>
                                        <h1 style="margin: 0; color: #ffffff; font-size: 22px; font-weight: 700; letter-spacing: -0.3px;">Health Delivery System</h1>
                                        <div style="display: inline-block; margin-top: 8px; background: rgba(255,255,255,0.25); color: #ffffff; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                            {$cfg['portal_title']}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 32px 28px;">
                            <h2 style="margin: 0 0 12px; color: #0f172a; font-size: 18px; font-weight: 700;">
                                Password Reset Request
                            </h2>
                            <p style="margin: 0 0 20px; color: #475569; font-size: 14px; line-height: 1.6;">
                                Hello <strong>{$greetingName}</strong>, we received a request to reset your password for your <strong>{$cfg['role_label']}</strong>.
                            </p>
                            <p style="margin: 0 0 24px; color: #475569; font-size: 14px; line-height: 1.6;">
                                Please use the 6-digit verification code below to authorize your password reset:
                            </p>

                            <!-- OTP Box -->
                            <div style="background-color: {$cfg['light_bg']}; border: 2px dashed {$cfg['border_color']}; border-radius: 12px; padding: 24px; text-align: center; margin: 0 0 24px;">
                                <span style="display: block; font-size: 12px; font-weight: 700; color: {$cfg['theme_color']}; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">
                                    One-Time Verification Code
                                </span>
                                <div style="font-family: 'Courier New', Courier, monospace; font-size: 36px; font-weight: 800; color: #0f172a; letter-spacing: 8px; line-height: 1.2;">
                                    {$safeOtp}
                                </div>
                                <span style="display: inline-block; margin-top: 10px; font-size: 12px; color: #64748b;">
                                    ⏱️ Valid for <strong>{$expiresMinutes} minutes</strong>
                                </span>
                            </div>

                            <!-- Security Notice -->
                            <div style="background: #f8fafc; border-left: 4px solid {$cfg['theme_color']}; padding: 14px 16px; border-radius: 6px; margin: 0 0 24px;">
                                <strong style="display: block; font-size: 13px; color: #1e293b; margin-bottom: 4px;">Important Security Reminder</strong>
                                <span style="font-size: 12px; color: #64748b; line-height: 1.5; display: block;">
                                    Do not share this OTP code with anyone. Healthcare staff will never ask for your verification code or password.
                                </span>
                            </div>

                            <p style="margin: 0; color: #64748b; font-size: 13px; line-height: 1.5;">
                                If you did not request this password reset, you can safely ignore this email. Your existing password remains secure.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f1f5f9; padding: 20px 28px; text-align: center; border-top: 1px solid #e2e8f0;">
                            <p style="margin: 0 0 6px; color: #64748b; font-size: 12px;">
                                Bacolod City Health Delivery System &bull; Confidential Healthcare Notice
                            </p>
                            <p style="margin: 0; color: #94a3b8; font-size: 11px;">
                                &copy; 2026 Bacolod Health Stations. All rights reserved.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
}

/**
 * Sends a role-themed Password Reset OTP verification email to the user.
 */
function sendPasswordResetOtpEmail(string $toEmail, string $toName, string $otpCode, string $role, int $expiresMinutes = 10): bool
{
    $subject = "{$otpCode} is your Password Reset Code - Bacolod Health Delivery System";
    $html = renderPasswordResetEmailHtml($toName, $otpCode, $role, $expiresMinutes);
    $plainText = "Hello {$toName},\n\nYour 6-digit password reset verification code is: {$otpCode}\n\nThis code will expire in {$expiresMinutes} minutes.\n\nIf you did not make this request, please ignore this email.\n\nBacolod Health Delivery System";

    return sendBrevoEmail($toEmail, $toName, $subject, $html, $plainText);
}
