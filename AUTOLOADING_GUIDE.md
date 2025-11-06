# Autoloading and Configuration Guide

## Overview

Your application now uses **PSR-4 autoloading** with **Composer** for modern PHP development. This guide explains how everything works and how to use it.

---

## Directory Structure

```
emas/
├── bootstrap/
│   └── app.php              # Application bootstrap
├── config/
│   ├── routes.php           # Route definitions (old structure)
│   ├── Router.php           # Old router (deprecated)
│   └── connection.php       # Old database (deprecated)
├── src/                     # NEW PSR-4 Structure
│   ├── Config/
│   │   ├── Config.php       # Configuration manager
│   │   ├── Database.php     # Database connection
│   │   └── Router.php       # Router class
│   ├── Controllers/
│   │   └── ProductController.php
│   ├── Models/
│   │   └── Product.php
│   ├── Helpers/
│   │   └── helpers.php      # Utility functions
│   └── Middleware/          # (empty, for future)
├── vendor/                  # Composer dependencies
├── view/                    # Views (unchanged)
├── composer.json            # Composer configuration
├── composer.lock            # Locked dependencies
├── .env                     # Environment variables
├── .env.example             # Environment template
└── index.php                # Application entry point
```

---

## 1. Composer Autoloading

### What is PSR-4?

PSR-4 is a standard that maps PHP namespaces to directory structures:

```
Namespace: App\Controllers\ProductController
File Path: src/Controllers/ProductController.php
```

### Configuration (`composer.json`)

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        },
        "files": [
            "src/Helpers/helpers.php"
        ]
    }
}
```

- `App\` namespace maps to `src/` directory
- `helpers.php` is loaded automatically (utility functions)

### Using Classes with Autoloading

**Before (Manual require):**
```php
require_once 'model/product.php';
require_once 'controller/ProductController.php';

$product = new Product();
$controller = new ProductController();
```

**After (Autoloading):**
```php
use App\Models\Product;
use App\Controllers\ProductController;

$product = new Product();
$controller = new ProductController();
```

No manual `require` needed! Composer handles it.

---

## 2. Configuration System

### Config Class (`src/Config/Config.php`)

Centralized configuration management with:
- Environment variable loading
- Dot notation support
- Type-safe helpers

### Usage Examples

```php
use App\Config\Config;

// Get any config value
$dbHost = Config::get('DB_HOST', '127.0.0.1');

// Get grouped configs
$db = Config::database();
// Returns: ['host' => '...', 'port' => 3306, ...]

$app = Config::app();
// Returns: ['name' => 'Kedai Emas', 'env' => 'development', ...]

// Dot notation
$host = Config::get('database.host');
$appName = Config::get('app.name');

// Check environment
if (Config::isDevelopment()) {
    // Development-only code
}

if (Config::isProduction()) {
    // Production-only code
}
```

### Environment Variables (`.env`)

```env
# Application
APP_NAME="Kedai Emas"
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost

# Database
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=emas
DB_USER=root
DB_PASS=

# Session
SESSION_LIFETIME=7200
SESSION_SECURE=false
```

---

## 3. Database Connection

### Database Class (`src/Config/Database.php`)

Modern database management with:
- Singleton pattern (single connection)
- PDO with prepared statements
- Migration support

### Usage Examples

```php
use App\Config\Database;

// Get connection
$pdo = Database::getConnection();

// Test connection
if (Database::testConnection()) {
    echo "Connected!";
}

// Create database
Database::ensureDatabaseExists();

// Run migrations
Database::runMigrations('draw-sql.sql');
```

### In Models

```php
namespace App\Models;

use App\Config\Database;
use PDO;

class Product
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAll(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM product_data");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
```

---

## 4. Router System

### Router Class (`src/Config/Router.php`)

RESTful routing with:
- HTTP method support (GET, POST, ANY)
- Dynamic parameters (`:id`)
- Named routes
- Automatic controller instantiation

### Defining Routes (`config/routes.php`)

```php
use App\Config\Router;

// GET /products -> ProductController::index()
$router->get('/products', 'App\Controllers\ProductController', 'index', 'products.index');

// GET /products/:id/edit -> ProductController::edit($id)
$router->get('/products/:id/edit', 'App\Controllers\ProductController', 'edit', 'products.edit');

// POST /products/store -> ProductController::store()
$router->post('/products/store', 'App\Controllers\ProductController', 'store', 'products.store');
```

### Using in Controllers

```php
namespace App\Controllers;

class ProductController
{
    // Route: GET /products/:id/edit
    // URL:   /products/123/edit
    public function edit(string $id = ''): void
    {
        // $id automatically receives '123'
        $productId = (int) $id;
        // ...
    }
}
```

---

## 5. Helper Functions

### Available Helpers (`src/Helpers/helpers.php`)

```php
// Configuration
$value = env('DB_HOST', 'localhost');
$value = config('app.name');

// Debugging
dd($variable);           // Dump and die
dump($variable);         // Dump

// Redirect
redirect('/products');
redirect('/products', 301);

// Session
$value = session('user_id');
session('user_id', 123);  // Set value
$all = session();         // Get all

// Forms
$token = csrf_token();
echo csrf_field();  // Outputs: <input type="hidden" name="_token" value="...">

// Utilities
$clean = sanitize($userInput);
$url = url('/products');
$asset = asset('css/style.css');
$path = base_path('logs/app.log');
$date = now('Y-m-d H:i:s');
$price = format_currency(123.45);  // RM 123.45

// Errors
abort(404, 'Not found');
```

---

## 6. Bootstrap Process

### Application Flow

```
1. index.php
   ├─> bootstrap/app.php
   │   ├─> vendor/autoload.php (Composer autoloader)
   │   ├─> Config::load() (Load .env)
   │   ├─> Set timezone
   │   ├─> Configure error reporting
   │   └─> Start session
   ├─> Initialize Router
   ├─> Load routes
   └─> Dispatch request
```

### Bootstrap File (`bootstrap/app.php`)

```php
<?php
declare(strict_types=1);

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Config;
use App\Config\Database;

// Load configuration
Config::load();

// Set timezone
date_default_timezone_set(Config::get('APP_TIMEZONE', 'Asia/Kuala_Lumpur'));

// Error reporting
if (Config::isDevelopment()) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// Start session
session_start();

return true;
```

---

## 7. Creating New Components

### New Model

```php
<?php
declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;

class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAll(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM users");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
```

**File Location:** `src/Models/User.php`

### New Controller

```php
<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;

class UserController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function index(): void
    {
        $users = $this->userModel->getAll();
        require_once view_path('user/index.php');
    }
}
```

**File Location:** `src/Controllers/UserController.php`

### Register Routes

```php
// In config/routes.php
$router->get('/users', 'App\Controllers\UserController', 'index', 'users.index');
```

---

## 8. Composer Commands

```bash
# Install dependencies
composer install

# Update dependencies
composer update

# Regenerate autoloader
composer dump-autoload

# Run database setup
composer db:setup

# Run migrations
composer db:migrate

# Run database seed
composer db:seed

# Run tests (when implemented)
composer test
```

---

## 9. Environment Configuration

### Development vs Production

**Development (`.env`):**
```env
APP_ENV=development
APP_DEBUG=true
DB_HOST=127.0.0.1
```

**Production (`.env`):**
```env
APP_ENV=production
APP_DEBUG=false
DB_HOST=production-db.example.com
SESSION_SECURE=true
```

### Conditional Logic

```php
use App\Config\Config;

if (Config::isDevelopment()) {
    // Show detailed errors
    ini_set('display_errors', '1');
}

if (Config::isProduction()) {
    // Log errors only
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}
```

---

## 10. Migration Guide

### Old Structure → New Structure

| Old | New |
|-----|-----|
| `require_once 'model/product.php'` | `use App\Models\Product;` |
| `require_once 'config/connection.php'` | `use App\Config\Database;` |
| `Database::getConnection()` | `Database::getConnection()` (same) |
| `new Product()` | `new Product()` (same, but namespaced) |
| `getenv('DB_HOST')` | `Config::get('DB_HOST')` |

---

## 11. Best Practices

1. **Always use namespaces** in new files
2. **Use Config class** instead of direct `getenv()`
3. **Use helpers** for common operations
4. **Keep controllers thin** - logic goes in models
5. **Use type hints** for better code quality
6. **Follow PSR-12** coding standards

---

## 12. Troubleshooting

### "Class not found" Error

```bash
# Regenerate autoloader
composer dump-autoload
```

### Config Not Loading

```php
// Manually load in your file
Config::load();
```

### Database Connection Error

```php
// Test connection
if (!Database::testConnection()) {
    die("Cannot connect to database");
}
```

---

## Summary

Your application now has:

✅ **PSR-4 Autoloading** - No more manual `require` statements
✅ **Config Management** - Centralized `.env` configuration
✅ **Modern Database** - PDO with singleton pattern
✅ **RESTful Router** - Clean URLs with dynamic parameters
✅ **Helper Functions** - Utility functions available globally
✅ **Bootstrap System** - Proper initialization
✅ **Composer Integration** - Professional dependency management

You're now ready for professional PHP development! 🚀
