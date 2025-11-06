<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Base Controller Class
 *
 * Provides common functionality for all controllers
 */
abstract class Controller
{
    /**
     * Load and render a view
     *
     * @param string $view View name (e.g., 'user/profile')
     * @param array $data Data to pass to the view
     * @param string|null $layout Layout file name (null for no layout)
     * @return void
     */
    protected function view(string $view, array $data = [], ?string $layout = 'default'): void
    {
        // Extract data array to variables
        extract($data);

        // Build view path
        $viewPath = __DIR__ . '/../view/' . $view . '.php';

        if (!file_exists($viewPath)) {
            $this->error(404, "View not found: {$view}");
            return;
        }

        // If layout is specified, load it
        if ($layout !== null) {
            $layoutPath = __DIR__ . '/../view/layouts/' . $layout . '.php';

            if (!file_exists($layoutPath)) {
                $this->error(500, "Layout not found: {$layout}");
                return;
            }

            // Capture view content
            ob_start();
            require $viewPath;
            $content = ob_get_clean();

            // Load layout with content
            require $layoutPath;
        } else {
            // Load view without layout
            require $viewPath;
        }
    }

    /**
     * Return JSON response
     *
     * @param mixed $data Data to encode as JSON
     * @param int $statusCode HTTP status code
     * @return void
     */
    protected function json($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_THROW_ON_ERROR);
        exit;
    }

    /**
     * Redirect to another URL
     *
     * @param string $url URL to redirect to
     * @param int $statusCode HTTP status code (301, 302, 303, etc.)
     * @return void
     */
    protected function redirect(string $url, int $statusCode = 302): void
    {
        http_response_code($statusCode);
        header("Location: {$url}");
        exit;
    }

    /**
     * Get request method
     *
     * @return string
     */
    protected function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    /**
     * Check if request is POST
     *
     * @return bool
     */
    protected function isPost(): bool
    {
        return $this->getMethod() === 'POST';
    }

    /**
     * Check if request is GET
     *
     * @return bool
     */
    protected function isGet(): bool
    {
        return $this->getMethod() === 'GET';
    }

    /**
     * Check if request is AJAX
     *
     * @return bool
     */
    protected function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Get POST data
     *
     * @param string|null $key Specific key to get (null for all)
     * @param mixed $default Default value if key doesn't exist
     * @return mixed
     */
    protected function post(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $_POST;
        }

        return $_POST[$key] ?? $default;
    }

    /**
     * Get GET data
     *
     * @param string|null $key Specific key to get (null for all)
     * @param mixed $default Default value if key doesn't exist
     * @return mixed
     */
    protected function get(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $_GET;
        }

        return $_GET[$key] ?? $default;
    }

    /**
     * Get input from both GET and POST
     *
     * @param string $key Key to get
     * @param mixed $default Default value if key doesn't exist
     * @return mixed
     */
    protected function input(string $key, $default = null)
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    /**
     * Validate required fields
     *
     * @param array $required Array of required field names
     * @param array $data Data to validate (defaults to $_POST)
     * @return array Empty if valid, array of missing fields if invalid
     */
    protected function validateRequired(array $required, ?array $data = null): array
    {
        $data = $data ?? $_POST;
        $missing = [];

        foreach ($required as $field) {
            if (!isset($data[$field]) || trim((string)$data[$field]) === '') {
                $missing[] = $field;
            }
        }

        return $missing;
    }

    /**
     * Set flash message in session
     *
     * @param string $type Message type (success, error, warning, info)
     * @param string $message Message content
     * @return void
     */
    protected function flash(string $type, string $message): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message
        ];
    }

    /**
     * Get and clear flash message
     *
     * @return array|null
     */
    protected function getFlash(): ?array
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

    /**
     * Handle error responses
     *
     * @param int $code HTTP status code
     * @param string $message Error message
     * @return void
     */
    protected function error(int $code, string $message): void
    {
        http_response_code($code);

        // If AJAX request, return JSON
        if ($this->isAjax()) {
            $this->json([
                'error' => true,
                'message' => $message,
                'code' => $code
            ], $code);
            return;
        }

        // Otherwise, render error view
        $this->view('errors/' . $code, [
            'code' => $code,
            'message' => $message
        ], null);

        exit;
    }

    /**
     * Sanitize input string
     *
     * @param string $input Input to sanitize
     * @return string
     */
    protected function sanitize(string $input): string
    {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Get authenticated user from session
     *
     * @return array|null
     */
    protected function getUser(): ?array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return $_SESSION['user'] ?? null;
    }

    /**
     * Check if user is authenticated
     *
     * @return bool
     */
    protected function isAuthenticated(): bool
    {
        return $this->getUser() !== null;
    }

    /**
     * Require authentication (redirect to login if not authenticated)
     *
     * @param string $redirectUrl URL to redirect to if not authenticated
     * @return void
     */
    protected function requireAuth(string $redirectUrl = '/login'): void
    {
        if (!$this->isAuthenticated()) {
            $this->flash('error', 'Please log in to access this page');
            $this->redirect($redirectUrl);
        }
    }

    /**
     * Check if user has specific role
     *
     * @param string $role Role name to check
     * @return bool
     */
    protected function hasRole(string $role): bool
    {
        $user = $this->getUser();
        return $user && isset($user['role']) && $user['role'] === $role;
    }

    /**
     * Require specific role (show error if user doesn't have role)
     *
     * @param string $role Required role
     * @return void
     */
    protected function requireRole(string $role): void
    {
        if (!$this->hasRole($role)) {
            $this->error(403, 'You do not have permission to access this page');
        }
    }
}
