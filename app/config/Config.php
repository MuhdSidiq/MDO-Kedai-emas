<?php
declare(strict_types=1);

namespace App\Config;

/**
 * Configuration Class
 *
 * Provides easy access to configuration values
 */
class Config
{
    /**
     * Get configuration value from environment
     *
     * @param string $key Configuration key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        $value = getenv($key);
        return $value !== false ? $value : $default;
    }

    /**
     * Get application name
     *
     * @return string
     */
    public static function getAppName(): string
    {
        return self::get('APP_NAME', 'Kedai Emas');
    }

    /**
     * Get application environment
     *
     * @return string
     */
    public static function getAppEnv(): string
    {
        return self::get('APP_ENV', 'production');
    }

    /**
     * Check if application is in development mode
     *
     * @return bool
     */
    public static function isDevelopment(): bool
    {
        return self::getAppEnv() === 'development';
    }

    /**
     * Check if application is in production mode
     *
     * @return bool
     */
    public static function isProduction(): bool
    {
        return self::getAppEnv() === 'production';
    }

    /**
     * Check if debug mode is enabled
     *
     * @return bool
     */
    public static function isDebug(): bool
    {
        return filter_var(self::get('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Get application URL
     *
     * @return string
     */
    public static function getAppUrl(): string
    {
        return rtrim(self::get('APP_URL', 'http://localhost'), '/');
    }

    /**
     * Get application timezone
     *
     * @return string
     */
    public static function getTimezone(): string
    {
        return self::get('APP_TIMEZONE', 'Asia/Kuala_Lumpur');
    }

    /**
     * Get database configuration
     *
     * @return array
     */
    public static function getDatabase(): array
    {
        return [
            'host' => self::get('DB_HOST', '127.0.0.1'),
            'port' => (int) self::get('DB_PORT', 3306),
            'name' => self::get('DB_NAME', 'emas'),
            'user' => self::get('DB_USER', 'root'),
            'pass' => self::get('DB_PASS', ''),
            'charset' => self::get('DB_CHARSET', 'utf8mb4')
        ];
    }

    /**
     * Get session configuration
     *
     * @return array
     */
    public static function getSession(): array
    {
        return [
            'lifetime' => (int) self::get('SESSION_LIFETIME', 7200),
            'path' => self::get('SESSION_PATH', '/'),
            'domain' => self::get('SESSION_DOMAIN', ''),
            'secure' => filter_var(self::get('SESSION_SECURE', 'false'), FILTER_VALIDATE_BOOLEAN),
            'httponly' => filter_var(self::get('SESSION_HTTPONLY', 'true'), FILTER_VALIDATE_BOOLEAN)
        ];
    }

    /**
     * Get Metal Price API configuration
     *
     * @return array
     */
    public static function getMetalPriceApi(): array
    {
        return [
            'api_key' => self::get('METAL_PRICE_API_KEY', ''),
            'api_url' => self::get('METAL_PRICE_API_URL', 'https://api.metalpriceapi.com/v1')
        ];
    }

    /**
     * Get all configuration as array
     *
     * @return array
     */
    public static function all(): array
    {
        return [
            'app' => [
                'name' => self::getAppName(),
                'env' => self::getAppEnv(),
                'debug' => self::isDebug(),
                'url' => self::getAppUrl(),
                'timezone' => self::getTimezone()
            ],
            'database' => self::getDatabase(),
            'session' => self::getSession(),
            'metal_price_api' => self::getMetalPriceApi()
        ];
    }
}
