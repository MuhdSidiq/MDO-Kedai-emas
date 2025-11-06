<?php
declare(strict_types=1);

/**
 * Helper Functions
 *
 * Common utility functions available throughout the application
 */

if (!function_exists('env')) {
    /**
     * Get environment variable
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function env(string $key, $default = null)
    {
        $value = getenv($key);
        return $value !== false ? $value : $default;
    }
}

if (!function_exists('config')) {
    /**
     * Get configuration value
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function config(string $key, $default = null)
    {
        return \App\Config\Config::get($key, $default);
    }
}

if (!function_exists('base_url')) {
    /**
     * Generate base URL
     *
     * @param string $path
     * @return string
     */
    function base_url(string $path = ''): string
    {
        $baseUrl = rtrim(config('APP_URL', 'http://localhost'), '/');
        $path = ltrim($path, '/');
        return $path ? "{$baseUrl}/{$path}" : $baseUrl;
    }
}

if (!function_exists('asset')) {
    /**
     * Generate asset URL
     *
     * @param string $path
     * @return string
     */
    function asset(string $path): string
    {
        return base_url('public/' . ltrim($path, '/'));
    }
}

if (!function_exists('redirect')) {
    /**
     * Redirect to URL
     *
     * @param string $url
     * @param int $statusCode
     * @return void
     */
    function redirect(string $url, int $statusCode = 302): void
    {
        http_response_code($statusCode);
        header("Location: {$url}");
        exit;
    }
}

if (!function_exists('dd')) {
    /**
     * Dump and die (for debugging)
     *
     * @param mixed ...$vars
     * @return void
     */
    function dd(...$vars): void
    {
        echo '<pre>';
        foreach ($vars as $var) {
            var_dump($var);
        }
        echo '</pre>';
        die();
    }
}

if (!function_exists('dump')) {
    /**
     * Dump variable (for debugging)
     *
     * @param mixed ...$vars
     * @return void
     */
    function dump(...$vars): void
    {
        echo '<pre>';
        foreach ($vars as $var) {
            var_dump($var);
        }
        echo '</pre>';
    }
}

if (!function_exists('old')) {
    /**
     * Get old input value (for form repopulation)
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function old(string $key, $default = '')
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['old'][$key] ?? $default;
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Generate CSRF token
     *
     * @return string
     */
    function csrf_token(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Generate CSRF hidden input field
     *
     * @return string
     */
    function csrf_field(): string
    {
        $token = csrf_token();
        return "<input type=\"hidden\" name=\"csrf_token\" value=\"{$token}\">";
    }
}

if (!function_exists('verify_csrf')) {
    /**
     * Verify CSRF token
     *
     * @param string|null $token
     * @return bool
     */
    function verify_csrf(?string $token = null): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $token = $token ?? $_POST['csrf_token'] ?? '';
        $sessionToken = $_SESSION['csrf_token'] ?? '';

        return hash_equals($sessionToken, $token);
    }
}

if (!function_exists('escape')) {
    /**
     * Escape HTML entities
     *
     * @param string $string
     * @return string
     */
    function escape(string $string): string
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('e')) {
    /**
     * Alias for escape()
     *
     * @param string $string
     * @return string
     */
    function e(string $string): string
    {
        return escape($string);
    }
}

if (!function_exists('auth_user')) {
    /**
     * Get authenticated user
     *
     * @return array|null
     */
    function auth_user(): ?array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['user'] ?? null;
    }
}

if (!function_exists('is_authenticated')) {
    /**
     * Check if user is authenticated
     *
     * @return bool
     */
    function is_authenticated(): bool
    {
        return auth_user() !== null;
    }
}

if (!function_exists('flash')) {
    /**
     * Set flash message
     *
     * @param string $type
     * @param string $message
     * @return void
     */
    function flash(string $type, string $message): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }
}

if (!function_exists('get_flash')) {
    /**
     * Get and clear flash message
     *
     * @return array|null
     */
    function get_flash(): ?array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $flash = $_SESSION['flash'] ?? null;
        if ($flash) {
            unset($_SESSION['flash']);
        }
        return $flash;
    }
}

if (!function_exists('format_currency')) {
    /**
     * Format number as currency (Malaysian Ringgit)
     *
     * @param float $amount
     * @param bool $showSymbol
     * @return string
     */
    function format_currency(float $amount, bool $showSymbol = true): string
    {
        $formatted = number_format($amount, 2);
        return $showSymbol ? "RM {$formatted}" : $formatted;
    }
}

if (!function_exists('calculate_gold_price')) {
    /**
     * Calculate gold price with profit margin
     *
     * @param float $basePricePerOunce Price per Troy Ounce in RM
     * @param float $profitMarginPercent Profit margin percentage
     * @return float Price per gram in RM
     */
    function calculate_gold_price(float $basePricePerOunce, float $profitMarginPercent): float
    {
        // 1 Troy Ounce = 31 grams
        $pricePerGram = $basePricePerOunce / 31;

        // Apply profit margin
        $margin = $pricePerGram * ($profitMarginPercent / 100);
        return $pricePerGram + $margin;
    }
}
