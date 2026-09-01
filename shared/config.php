<?php

declare(strict_types=1);

/**
 * Database & Application Configuration
 * Dynamically resolves environment variables from .env, PaaS URLs, or server environment with fallback defaults.
 */

// Support standard database URL formats (e.g. Railway, Render, Heroku, Supabase, PlanetScale)
$dbUrl = $_ENV['DATABASE_URL'] ?? $_ENV['MYSQL_URL'] ?? getenv('DATABASE_URL') ?: getenv('MYSQL_URL');
$parsedUrl = is_string($dbUrl) && $dbUrl !== '' ? parse_url($dbUrl) : false;

$envHost = $parsedUrl['host'] ?? $_ENV['DB_HOST'] ?? $_ENV['MYSQLHOST'] ?? $_ENV['MYSQL_HOST'] ?? $_ENV['DATABASE_HOST'] ?? getenv('DB_HOST') ?: getenv('MYSQLHOST') ?: getenv('MYSQL_HOST') ?: getenv('DATABASE_HOST');
$envPort = $parsedUrl['port'] ?? $_ENV['DB_PORT'] ?? $_ENV['MYSQLPORT'] ?? $_ENV['MYSQL_PORT'] ?? $_ENV['DATABASE_PORT'] ?? getenv('DB_PORT') ?: getenv('MYSQLPORT') ?: getenv('MYSQL_PORT') ?: getenv('DATABASE_PORT');
$envUser = $parsedUrl['user'] ?? $_ENV['DB_USER'] ?? $_ENV['MYSQLUSER'] ?? $_ENV['MYSQL_USER'] ?? $_ENV['DATABASE_USER'] ?? getenv('DB_USER') ?: getenv('MYSQLUSER') ?: getenv('MYSQL_USER') ?: getenv('DATABASE_USER');
$envPass = $parsedUrl['pass'] ?? $_ENV['DB_PASS'] ?? $_ENV['MYSQLPASSWORD'] ?? $_ENV['MYSQL_PASSWORD'] ?? $_ENV['DATABASE_PASSWORD'] ?? (getenv('DB_PASS') !== false ? getenv('DB_PASS') : (getenv('MYSQLPASSWORD') !== false ? getenv('MYSQLPASSWORD') : (getenv('MYSQL_PASSWORD') !== false ? getenv('MYSQL_PASSWORD') : null)));
$envName = isset($parsedUrl['path']) ? ltrim($parsedUrl['path'], '/') : ($_ENV['DB_NAME'] ?? $_ENV['MYSQLDATABASE'] ?? $_ENV['MYSQL_DATABASE'] ?? $_ENV['DATABASE_NAME'] ?? getenv('DB_NAME') ?: getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: getenv('DATABASE_NAME'));

if (!defined('DB_HOST')) {
    define('DB_HOST', (string) ($envHost ?: '127.0.0.1'));
}

if (!defined('DB_PORT')) {
    define('DB_PORT', (int) ($envPort ?: 3306));
}

if (!defined('DB_USER')) {
    define('DB_USER', (string) ($envUser ?: 'root'));
}

if (!defined('DB_PASS')) {
    define('DB_PASS', (string) ($envPass !== null ? $envPass : ''));
}

if (!defined('DB_NAME')) {
    define('DB_NAME', (string) ($envName ?: 'health_delivery_system'));
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
