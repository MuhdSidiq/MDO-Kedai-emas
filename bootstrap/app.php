<?php
declare(strict_types=1);

/**
 * Application Bootstrap
 *
 * This file initializes the application
 */

// Define application root directory
define('ROOT_DIR', dirname(__DIR__));
define('APP_DIR', ROOT_DIR . '/app');
define('PUBLIC_DIR', ROOT_DIR . '/public');
define('CONFIG_DIR', ROOT_DIR . '/config');

// Load environment variables from .env file
require_once __DIR__ . '/env.php';

// Register autoloader
require_once __DIR__ . '/autoload.php';

// Load helper functions
require_once __DIR__ . '/helpers.php';

// Error reporting based on environment
if (getenv('APP_ENV') === 'development') {
    error_reporting(error_level: E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

// Set timezone
$timezone = getenv('APP_TIMEZONE') ?: 'Asia/Kuala_Lumpur';
date_default_timezone_set($timezone);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    $sessionLifetime = (int) (getenv('SESSION_LIFETIME') ?: 7200);
    $sessionPath = getenv('SESSION_PATH') ?: '/';
    $sessionDomain = getenv('SESSION_DOMAIN') ?: '';
    $sessionSecure = filter_var(getenv('SESSION_SECURE'), FILTER_VALIDATE_BOOLEAN);
    $sessionHttponly = filter_var(getenv('SESSION_HTTPONLY') ?: 'true', FILTER_VALIDATE_BOOLEAN);

    ini_set('session.gc_maxlifetime', (string)$sessionLifetime);
    ini_set('session.cookie_lifetime', (string)$sessionLifetime);

    session_set_cookie_params([
        'lifetime' => $sessionLifetime,
        'path' => $sessionPath,
        'domain' => $sessionDomain,
        'secure' => $sessionSecure,
        'httponly' => $sessionHttponly,
        'samesite' => 'Lax'
    ]);

    session_start();
}
