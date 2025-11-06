<?php
declare(strict_types=1);

/**
 * Application Entry Point
 *
 * This file handles all incoming requests and routes them
 * to the appropriate controller.
 */

// Bootstrap the application
require_once __DIR__ . '/bootstrap/app.php';

use App\Config\Router;
use App\Config\Config;

// Initialize Router
$router = new Router();

// Set base path if app is in a subdirectory
// $router->setBasePath('/emas'); // Uncomment if your app is in a subdirectory

// Load routes
require_once __DIR__ . '/config/routes.php';

// Dispatch the request
try {
    $router->dispatch();
} catch (Throwable $e) {
    // Log error
    error_log("Application Error: " . $e->getMessage());

    // Show error page
    http_response_code(500);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>500 - Server Error</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6 text-center">
                    <h1 class="display-1">500</h1>
                    <h2>Server Error</h2>
                    <p class="lead">An error occurred while processing your request.</p>
                    <?php if (Config::isDevelopment()): ?>
                        <div class="alert alert-danger text-start mt-4">
                            <strong>Error Details:</strong><br>
                            <?= htmlspecialchars($e->getMessage()) ?><br>
                            <small>File: <?= htmlspecialchars($e->getFile()) ?> (Line: <?= $e->getLine() ?>)</small>
                        </div>
                    <?php endif; ?>
                    <a href="/" class="btn btn-primary mt-3">Go to Home</a>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}
