<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/config.php';

/**
 * Global tracker for the last email dispatch error.
 */
$GLOBALS['LAST_MAIL_ERROR'] = '';

function get_last_mail_error(): string
{
    return (string) ($GLOBALS['LAST_MAIL_ERROR'] ?? '');
}

/**
 * Sends an email using pure PHP SMTP socket connection (supports SSL, TLS/STARTTLS, AUTH LOGIN).
 */
function sendSmtpEmail(string $toEmail, string $toName, string $subject, string $htmlContent, string $textContent = ''): bool
{
    $host = defined('SMTP_HOST') ? (string) SMTP_HOST : (getenv('SMTP_HOST') ?: '');
    $port = defined('SMTP_PORT') ? (int) SMTP_PORT : (int) (getenv('SMTP_PORT') ?: 587);
    $user = defined('SMTP_USER') ? (string) SMTP_USER : (getenv('SMTP_USER') ?: '');
    $pass = defined('SMTP_PASS') ? (string) SMTP_PASS : (getenv('SMTP_PASS') ?: '');
    $secure = defined('SMTP_SECURE') ? strtolower((string) SMTP_SECURE) : strtolower((string) (getenv('SMTP_SECURE') ?: 'tls'));
    $fromEmail = defined('MAIL_FROM_ADDRESS') && MAIL_FROM_ADDRESS !== '' ? (string) MAIL_FROM_ADDRESS : (getenv('MAIL_FROM_ADDRESS') ?: 'no-reply@bsns.online');
    $fromName = defined('MAIL_FROM_NAME') && MAIL_FROM_NAME !== '' ? (string) MAIL_FROM_NAME : (getenv('MAIL_FROM_NAME') ?: 'Bacolod Health Delivery System');

    if ($host === '' || str_contains($user, 'your_') || str_contains($pass, 'your_') || $user === '' || $pass === '') {
        $GLOBALS['LAST_MAIL_ERROR'] = 'SMTP credentials not configured in .env (please provide your real email address and password).';
        return false;
    }

    $timeout = 10;
    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
        ]
    ]);

    // Build candidates for connection (supporting automatic 587/465 failover)
    $candidates = [
        ['port' => $port, 'secure' => $secure, 'prefix' => ($secure === 'ssl' || $port === 465) ? 'ssl://' : 'tcp://'],
    ];
    if ($port === 587) {
        $candidates[] = ['port' => 465, 'secure' => 'ssl', 'prefix' => 'ssl://'];
    } elseif ($port === 465) {
        $candidates[] = ['port' => 587, 'secure' => 'tls', 'prefix' => 'tcp://'];
    }

    $socket = null;
    $connectErrors = [];
    $activeCandidate = null;

    foreach ($candidates as $cand) {
        $p = $cand['port'];
        $pref = $cand['prefix'];
        $sock = @stream_socket_client($pref . $host . ':' . $p, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $context);
        if ($sock) {
            $socket = $sock;
            $activeCandidate = $cand;
            break;
        }
        $connectErrors[] = "{$p} ({$errstr})";
    }

    if (!$socket || !$activeCandidate) {
        $GLOBALS['LAST_MAIL_ERROR'] = "SMTP Connection failed to {$host} on ports: " . implode(', ', $connectErrors);
        return false;
    }

    $port = $activeCandidate['port'];
    $secure = $activeCandidate['secure'];
    $prefix = $activeCandidate['prefix'];

    stream_set_timeout($socket, $timeout);

    $readResponse = static function ($socket, int $expectedCode): array {
        $response = '';
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        $code = (int) substr($response, 0, 3);
        return ['code' => $code, 'text' => trim($response), 'ok' => ($code === $expectedCode)];
    };

    $sendCommand = static function ($socket, string $cmd, int $expectedCode) use ($readResponse): array {
        fwrite($socket, $cmd . "\r\n");
        return $readResponse($socket, $expectedCode);
    };

    // Greeting (code 220)
    $res = $readResponse($socket, 220);
    if (!$res['ok']) {
        fclose($socket);
        $GLOBALS['LAST_MAIL_ERROR'] = "SMTP Server greeting failed: " . $res['text'];
        return false;
    }

    $clientDomain = gethostname() ?: 'localhost';

    // EHLO
    $res = $sendCommand($socket, 'EHLO ' . $clientDomain, 250);
    if (!$res['ok']) {
        $res = $sendCommand($socket, 'HELO ' . $clientDomain, 250);
        if (!$res['ok']) {
            fclose($socket);
            $GLOBALS['LAST_MAIL_ERROR'] = "SMTP EHLO/HELO failed: " . $res['text'];
            return false;
        }
    }

    // STARTTLS if port 587 or secure === 'tls'
    if (($secure === 'tls' || $port === 587) && $prefix === 'tcp://') {
        $res = $sendCommand($socket, 'STARTTLS', 220);
        if (!$res['ok']) {
            fclose($socket);
            $GLOBALS['LAST_MAIL_ERROR'] = "SMTP STARTTLS failed: " . $res['text'];
            return false;
        }
        $cryptoOk = stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT);
        if (!$cryptoOk) {
            fclose($socket);
            $GLOBALS['LAST_MAIL_ERROR'] = "SMTP TLS encryption handshake failed.";
            return false;
        }
        $res = $sendCommand($socket, 'EHLO ' . $clientDomain, 250);
        if (!$res['ok']) {
            fclose($socket);
            $GLOBALS['LAST_MAIL_ERROR'] = "SMTP post-TLS EHLO failed: " . $res['text'];
            return false;
        }
    }

    // AUTH LOGIN if credentials provided
    if ($user !== '' && $pass !== '') {
        $res = $sendCommand($socket, 'AUTH LOGIN', 334);
        if (!$res['ok']) {
            fclose($socket);
            $GLOBALS['LAST_MAIL_ERROR'] = "SMTP AUTH LOGIN rejected: " . $res['text'];
            return false;
        }
        $res = $sendCommand($socket, base64_encode($user), 334);
        if (!$res['ok']) {
            fclose($socket);
            $GLOBALS['LAST_MAIL_ERROR'] = "SMTP Username rejected: " . $res['text'];
            return false;
        }
        $res = $sendCommand($socket, base64_encode($pass), 235);
        if (!$res['ok']) {
            fclose($socket);
            $GLOBALS['LAST_MAIL_ERROR'] = "SMTP Password authentication failed: " . $res['text'];
            return false;
        }
    }

    // MAIL FROM
    $res = $sendCommand($socket, "MAIL FROM:<{$fromEmail}>", 250);
    if (!$res['ok']) {
        fclose($socket);
        $GLOBALS['LAST_MAIL_ERROR'] = "SMTP MAIL FROM failed: " . $res['text'];
        return false;
    }

    // RCPT TO
    $res = $sendCommand($socket, "RCPT TO:<{$toEmail}>", 250);
    if (!$res['ok']) {
        fclose($socket);
        $GLOBALS['LAST_MAIL_ERROR'] = "SMTP RCPT TO failed: " . $res['text'];
        return false;
    }

    // DATA
    $res = $sendCommand($socket, 'DATA', 354);
    if (!$res['ok']) {
        fclose($socket);
        $GLOBALS['LAST_MAIL_ERROR'] = "SMTP DATA command rejected: " . $res['text'];
        return false;
    }

    $boundary = '=_hds_' . md5((string) microtime(true));
    $date = date('r');
    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $encodedFromName = '=?UTF-8?B?' . base64_encode($fromName) . '?=';
    $encodedToName = '=?UTF-8?B?' . base64_encode($toName) . '?=';

    $msg = "Date: {$date}\r\n";
    $msg .= "From: {$encodedFromName} <{$fromEmail}>\r\n";
    $msg .= "To: {$encodedToName} <{$toEmail}>\r\n";
    $msg .= "Subject: {$encodedSubject}\r\n";
    $msg .= "MIME-Version: 1.0\r\n";
    $msg .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n\r\n";

    if ($textContent !== '') {
        $msg .= "--{$boundary}\r\n";
        $msg .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $msg .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $msg .= chunk_split(base64_encode($textContent)) . "\r\n";
    }

    $msg .= "--{$boundary}\r\n";
    $msg .= "Content-Type: text/html; charset=UTF-8\r\n";
    $msg .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $msg .= chunk_split(base64_encode($htmlContent)) . "\r\n";
    $msg .= "--{$boundary}--\r\n";
    $msg .= ".\r\n";

    fwrite($socket, $msg);
    $res = $readResponse($socket, 250);
    $sendCommand($socket, 'QUIT', 221);
    fclose($socket);

    if (!$res['ok']) {
        $GLOBALS['LAST_MAIL_ERROR'] = "SMTP Message dispatch rejected: " . $res['text'];
        return false;
    }

    return true;
}

/**
 * Sends a transactional email using configured SMTP or Brevo API with fallback to PHP mail() and logging.
 */
function sendBrevoEmail(string $toEmail, string $toName, string $subject, string $htmlContent, string $textContent = ''): bool
{
    $cleanToEmail = trim($toEmail);
    $cleanToName = trim($toName) !== '' ? trim($toName) : 'User';

    if (!filter_var($cleanToEmail, FILTER_VALIDATE_EMAIL)) {
        $GLOBALS['LAST_MAIL_ERROR'] = 'Invalid recipient email address.';
        return false;
    }

    $fromEmail = defined('MAIL_FROM_ADDRESS') && MAIL_FROM_ADDRESS !== '' ? (string) MAIL_FROM_ADDRESS : (getenv('MAIL_FROM_ADDRESS') ?: 'no-reply@bsns.online');
    $fromName = defined('MAIL_FROM_NAME') && MAIL_FROM_NAME !== '' ? (string) MAIL_FROM_NAME : (getenv('MAIL_FROM_NAME') ?: 'Bacolod Health Delivery System');

    if ($textContent === '') {
        $textContent = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>', '</div>', '</h1>', '</h2>', '</h3>'], "\n", $htmlContent));
    }

    $logsDir = dirname(__DIR__) . '/logs';
    if (!is_dir($logsDir)) {
        @mkdir($logsDir, 0755, true);
    }
    $logFile = $logsDir . '/email_otp.log';

    $emailSent = false;
    $methodUsed = 'NONE';
    $detail = '';

    // 1. Attempt direct SMTP if SMTP_HOST is configured with non-placeholder credentials
    $smtpHost = defined('SMTP_HOST') ? (string) SMTP_HOST : (getenv('SMTP_HOST') ?: '');
    $smtpUser = defined('SMTP_USER') ? (string) SMTP_USER : (getenv('SMTP_USER') ?: '');
    $smtpPass = defined('SMTP_PASS') ? (string) SMTP_PASS : (getenv('SMTP_PASS') ?: '');
    if ($smtpHost !== '' && !str_contains($smtpUser, 'your_') && !str_contains($smtpPass, 'your_') && $smtpUser !== '' && $smtpPass !== '') {
        $smtpOk = sendSmtpEmail($cleanToEmail, $cleanToName, $subject, $htmlContent, $textContent);
        if ($smtpOk) {
            $emailSent = true;
            $methodUsed = 'SMTP (' . $smtpHost . ')';
        } else {
            $detail .= 'SMTP Error: ' . ($GLOBALS['LAST_MAIL_ERROR'] ?? 'Failed') . '; ';
        }
    }

    // 2. Attempt delivery via Brevo v3 Transactional Email API if not yet sent
    $apiKey = defined('BREVO_API_KEY') ? (string) BREVO_API_KEY : (getenv('BREVO_API_KEY') ?: '');
    if (!$emailSent && $apiKey !== '' && function_exists('curl_init')) {
        $payload = [
            'sender' => [
                'name' => $fromName,
                'email' => $fromEmail,
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
            $methodUsed = 'Brevo API';
        } else {
            if (str_contains($response, 'authorised_ips') || str_contains($response, 'unrecognised IP')) {
                $GLOBALS['LAST_MAIL_ERROR'] = 'Brevo API blocked request: Unrecognised IP address. You must authorize your IP at https://app.brevo.com/security/authorised_ips or disable the IP restriction in Brevo.';
            } else {
                $GLOBALS['LAST_MAIL_ERROR'] = "Brevo API HTTP {$httpCode}: " . ($response ?: $error);
            }
            $detail .= "Brevo API HTTP {$httpCode}: {$response}; ";
        }
    }

    // 3. Fallback: try standard PHP mail() if still not sent
    if (!$emailSent && function_exists('mail')) {
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . $fromName . ' <' . $fromEmail . '>',
            'Reply-To: ' . $fromEmail,
            'X-Mailer: PHP/' . phpversion(),
        ];
        $mailSuccess = @mail($cleanToEmail, $subject, $htmlContent, implode("\r\n", $headers));
        if ($mailSuccess) {
            $emailSent = true;
            $methodUsed = 'PHP mail()';
        } else {
            $detail .= 'PHP mail() failed; ';
        }
    }

    $statusStr = $emailSent ? 'SUCCESS' : 'FAILED';
    $logEntry = date('Y-m-d H:i:s') . " | To: {$cleanToEmail} | Subject: {$subject} | Method: {$methodUsed} | Status: {$statusStr} | Detail: {$detail}\n";
    @file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

    return $emailSent;
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
