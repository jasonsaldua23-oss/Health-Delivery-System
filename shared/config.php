<?php

declare(strict_types=1);

/**
 * Database & Application Configuration
 * Dynamically resolves environment variables from .env or server environment with fallback defaults.
 */

if (!defined('DB_HOST')) {
    define('DB_HOST', (string) ($_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: '127.0.0.1'));
}

if (!defined('DB_PORT')) {
    define('DB_PORT', (int) ($_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: 3306));
}

if (!defined('DB_USER')) {
    define('DB_USER', (string) ($_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root'));
}

if (!defined('DB_PASS')) {
    define('DB_PASS', (string) (isset($_ENV['DB_PASS']) ? $_ENV['DB_PASS'] : (getenv('DB_PASS') !== false ? getenv('DB_PASS') : '')));
}

if (!defined('DB_NAME')) {
    define('DB_NAME', (string) ($_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'health_delivery_system'));
}

if (!defined('APP_ENV')) {
    define('APP_ENV', (string) ($_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: 'production'));
}

if (!defined('APP_NAME')) {
    define('APP_NAME', (string) ($_ENV['APP_NAME'] ?? getenv('APP_NAME') ?: 'Health Delivery System'));
}

if (!defined('APP_URL')) {
    define('APP_URL', (string) ($_ENV['APP_URL'] ?? getenv('APP_URL') ?: 'http://localhost/Health-Delivery-System-Latest'));
}

if (!defined('BREVO_API_KEY')) {
    define('BREVO_API_KEY', (string) ($_ENV['BREVO_API_KEY'] ?? getenv('BREVO_API_KEY') ?: ''));
}
