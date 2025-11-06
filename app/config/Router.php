<?php
declare(strict_types=1);

namespace App\Config;


/**
 * Router Class
 *
 * Handles URL routing and dispatching to controllers
 */
class Router
{
    private array $routes = [];
    private string $basePath = '';
    private ?array $notFoundHandler = null;

    /**
     * Set base path for the application (if in subdirectory)
     *
     * @param string $basePath Base path (e.g., '/emas')
     * @return void
     */
    public function setBasePath(string $basePath): void
    {
        $this->basePath = rtrim($basePath, '/');
    }

    /**
     * Add a GET route
     *
     * @param string $path URL path pattern
     * @param string|callable $handler Controller@method or callable
     * @return void
     */
    public function get(string $path, $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    /**
     * Add a POST route
     *
     * @param string $path URL path pattern
     * @param string|callable $handler Controller@method or callable
     * @return void
     */
    public function post(string $path, $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    /**
     * Add a PUT route
     *
     * @param string $path URL path pattern
     * @param string|callable $handler Controller@method or callable
     * @return void
     */
    public function put(string $path, $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    /**
     * Add a DELETE route
     *
     * @param string $path URL path pattern
     * @param string|callable $handler Controller@method or callable
     * @return void
     */
    public function delete(string $path, $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    /**
     * Add a route for any method
     *
     * @param string $path URL path pattern
     * @param string|callable $handler Controller@method or callable
     * @return void
     */
    public function any(string $path, $handler): void
    {
        $this->addRoute('ANY', $path, $handler);
    }

    /**
     * Add multiple methods for the same route
     *
     * @param array $methods HTTP methods
     * @param string $path URL path pattern
     * @param string|callable $handler Controller@method or callable
     * @return void
     */
    public function match(array $methods, string $path, $handler): void
    {
        foreach ($methods as $method) {
            $this->addRoute(strtoupper($method), $path, $handler);
        }
    }

    /**
     * Set custom 404 handler
     *
     * @param string|callable $handler Controller@method or callable
     * @return void
     */
    public function set404($handler): void
    {
        $this->notFoundHandler = $this->parseHandler($handler);
    }

    /**
     * Add route to the routing table
     *
     * @param string $method HTTP method
     * @param string $path URL path pattern
     * @param string|callable $handler Controller@method or callable
     * @return void
     */
    private function addRoute(string $method, string $path, $handler): void
    {
        $path = '/' . trim($path, '/');
        $pattern = $this->convertPathToRegex($path);

        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'pattern' => $pattern,
            'handler' => $this->parseHandler($handler)
        ];
    }

    /**
     * Convert URL path to regex pattern
     * Supports dynamic segments like /user/{id} or /post/{slug}
     *
     * @param string $path URL path
     * @return string Regex pattern
     */
    private function convertPathToRegex(string $path): string
    {
        // Replace {param} with named regex groups
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        // Escape forward slashes
        $pattern = str_replace('/', '\/', $pattern);
        // Add anchors
        return '/^' . $pattern . '$/';
    }

    /**
     * Parse handler string or callable
     *
     * @param string|callable $handler
     * @return array ['type' => 'controller'|'callable', 'value' => mixed]
     */
    private function parseHandler($handler): array
    {
        if (is_callable($handler)) {
            return ['type' => 'callable', 'value' => $handler];
        }

        if (is_string($handler) && strpos($handler, '@') !== false) {
            [$controller, $method] = explode('@', $handler, 2);
            return [
                'type' => 'controller',
                'controller' => $controller,
                'method' => $method
            ];
        }

        throw new \InvalidArgumentException('Invalid route handler format');
    }

    /**
     * Dispatch the request to the appropriate handler
     *
     * @return void
     */
    public function dispatch(): void
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';

        // Remove query string
        $requestUri = strtok($requestUri, '?');

        // Remove base path
        if ($this->basePath && strpos($requestUri, $this->basePath) === 0) {
            $requestUri = substr($requestUri, strlen($this->basePath));
        }

        // Ensure leading slash
        $requestUri = '/' . ltrim($requestUri, '/');

        // Find matching route
        foreach ($this->routes as $route) {
            // Check if method matches (or route accepts ANY method)
            if ($route['method'] !== 'ANY' && $route['method'] !== $requestMethod) {
                continue;
            }

            // Check if path matches
            if (preg_match($route['pattern'], $requestUri, $matches)) {
                // Extract named parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Call handler
                $this->callHandler($route['handler'], $params);
                return;
            }
        }

        // No route found - call 404 handler
        $this->handle404();
    }

    /**
     * Call the route handler
     *
     * @param array $handler Parsed handler
     * @param array $params URL parameters
     * @return void
     */
    private function callHandler(array $handler, array $params = []): void
    {
        if ($handler['type'] === 'callable') {
            call_user_func_array($handler['value'], $params);
            return;
        }

        if ($handler['type'] === 'controller') {
            $controllerClass = 'App\\Controller\\' . $handler['controller'];

            if (!class_exists($controllerClass)) {
                throw new \RuntimeException("Controller not found: {$controllerClass}");
            }

            $controller = new $controllerClass();
            $method = $handler['method'];

            if (!method_exists($controller, $method)) {
                throw new \RuntimeException("Method not found: {$controllerClass}::{$method}");
            }

            call_user_func_array([$controller, $method], $params);
            return;
        }

        throw new \RuntimeException('Invalid handler type');
    }

    /**
     * Handle 404 Not Found
     *
     * @return void
     */
    private function handle404(): void
    {
        http_response_code(404);

        if ($this->notFoundHandler) {
            $this->callHandler($this->notFoundHandler);
            return;
        }

        // Default 404 response
        echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 text-center">
                <h1 class="display-1">404</h1>
                <h2>Page Not Found</h2>
                <p class="lead">The page you are looking for does not exist.</p>
                <a href="/" class="btn btn-primary mt-3">Go to Home</a>
            </div>
        </div>
    </div>
</body>
</html>';
        exit;
    }

    /**
     * Get all registered routes (for debugging)
     *
     * @return array
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}
