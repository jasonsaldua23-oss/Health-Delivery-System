<?php

declare(strict_types=1);

/**
 * Health Delivery System - Application Bootstrap
 * Centralized initialization for environment, security, sessions, and error handling.
 */

// 1. Load Environment Variables (.env)
(function () {
    $envFile = dirname(__DIR__) . '/.env';
    if (!file_exists($envFile) || !is_readable($envFile)) {
        return;
    }

    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $val = trim($parts[1]);

            // Strip surrounding quotes if present
            if (preg_match('/^"([\s\S]*)"$/', $val, $m) || preg_match("/^'([\s\S]*)'$/", $val, $m)) {
                $val = $m[1];
            }

            if (!isset($_ENV[$key])) {
                $_ENV[$key] = $val;
            }
            if (!isset($_SERVER[$key])) {
                $_SERVER[$key] = $val;
            }
            putenv("{$key}={$val}");
        }
    }
})();

// 2. Load Configuration Constants
require_once __DIR__ . '/config.php';

// 3. Configure Error Reporting & Logging
$appEnv = defined('APP_ENV') ? APP_ENV : (getenv('APP_ENV') ?: 'production');
$logsDir = dirname(__DIR__) . '/logs';
if (!is_dir($logsDir)) {
    @mkdir($logsDir, 0755, true);
}

if ($appEnv === 'production') {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', $logsDir . '/php_errors.log');
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
} else {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    ini_set('log_errors', '1');
    ini_set('error_log', $logsDir . '/php_errors.log');
    error_reporting(E_ALL);
}

// 4. Secure Session Cookie Initialization
if (session_status() === PHP_SESSION_NONE) {
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)
        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    $sessionCookieParams = [
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax',
    ];

    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params($sessionCookieParams);
    } else {
        session_set_cookie_params(
            $sessionCookieParams['lifetime'],
            $sessionCookieParams['path'] . '; samesite=' . $sessionCookieParams['samesite'],
            $sessionCookieParams['domain'],
            $sessionCookieParams['secure'],
            $sessionCookieParams['httponly']
        );
    }

    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_strict_mode', '1');

    session_start();
}

// 5. CSRF Security Helpers
if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('verify_csrf')) {
    function verify_csrf(?string $token): bool
    {
        if (!is_string($token) || $token === '' || empty($_SESSION['csrf_token'])) {
            return false;
        }

        return hash_equals((string) $_SESSION['csrf_token'], $token);
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
    }
}

// 6. Security Header Helpers
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}
