# Autoloading and Configuration Guide

## Overview

Your Kedai Emas application now uses **PSR-4 autoloading** with both **Composer** and a **custom autoloader** for modern PHP development. This guide explains how everything works and how to use it.

---

## Directory Structure

```
emas/
├── app/
│   ├── config/
│   │   ├── Config.php           # Configuration manager
│   │   ├── Router.php           # Router class
│   │   └── connection.php       # Database connection
│   ├── controller/
│   │   ├── Controller.php       # Base controller class
│   │   ├── ProductController.php
│   │   ├── UserController.php
│   │   ├── RoleController.php
│   │   ├── ProfitMarginController.php
│   │   └── ContactController.php
│   ├── model/
│   │   ├── Model.php            # Base model class
│   │   ├── product.php          # Product model
│   │   ├── user.php             # User model
│   │   ├── role.php
│   │   ├── profit_margin.php
│   │   └── contact_submission.php
│   ├── middleware/              # Middleware (for future)
│   └── view/                    # View templates
│       └── layouts/             # Layout files
├── bootstrap/
│   ├── app.php                  # Application bootstrap
│   ├── autoload.php             # PSR-4 autoloader
│   ├── env.php                  # Environment loader
│   └── helpers.php              # Helper functions
├── config/
│   └── routes.php               # Route definitions
├── public/                      # Public assets (CSS, JS, images)
├── vendor/                      # Composer dependencies
├── .env                         # Environment variables
├── .env.example                 # Environment template
├── composer.json                # Composer configuration
├── index.php                    # Application entry point
└── MVC_SETUP.md                 # MVC documentation
```

---

## 1. Autoloading System

### Dual Autoloader Support

Your application supports **two autoloading methods**:

1. **Custom PSR-4 Autoloader** (`bootstrap/autoload.php`) - Built-in, no dependencies
2. **Composer Autoloader** (`vendor/autoload.php`) - Full-featured, with package support

### Custom PSR-4 Autoloader

**Configuration (`bootstrap/autoload.php`):**

```php
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});
```

**Namespace Mapping:**
- `App\Model\*` → `app/model/*.php`
- `App\Controller\*` → `app/controller/*.php`
- `App\Config\*` → `app/config/*.php`
- `App\Middleware\*` → `app/middleware/*.php`

### Composer Autoloader

**Configuration (`composer.json`):**

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "app/"
        },
        "files": [
            "bootstrap/helpers.php"
        ]
    }
}
```

After modifying `composer.json`, run:
```bash
composer dump-autoload
```

### Using Classes with Autoloading

**Before (Manual require):**
```php
require_once 'src/model/product.php';
require_once 'src/controller/ProductController.php';

$product = new Product();
$controller = new ProductController();
```

**After (Autoloading):**
```php
use App\Model\Product;
use App\Controller\ProductController;

$product = new Product();
$controller = new ProductController();
```

No manual `require` needed! The autoloader handles it automatically.

---

## 2. Configuration System

### Config Class (`app/config/Config.php`)

Centralized configuration management with environment variable support.

### Usage Examples

```php
use App\Config\Config;

// Get any config value
$dbHost = Config::get('DB_HOST', '127.0.0.1');

// Get application name
$appName = Config::getAppName(); // "Kedai Emas"

// Get environment
$env = Config::getAppEnv(); // "development" or "production"

// Check environment
if (Config::isDevelopment()) {
    // Development-only code
    error_log("Debug mode enabled");
}

if (Config::isProduction()) {
    // Production-only code
    ini_set('display_errors', '0');
}

// Check debug mode
if (Config::isDebug()) {
    dd($debugData);
}

// Get database configuration
$db = Config::getDatabase();
// Returns: ['host' => '127.0.0.1', 'port' => 3306, 'name' => 'emas', ...]

// Get session configuration
$session = Config::getSession();
// Returns: ['lifetime' => 7200, 'path' => '/', ...]

// Get Metal Price API configuration
$api = Config::getMetalPriceApi();
// Returns: ['api_key' => '...', 'api_url' => 'https://...']

// Get all configuration
$allConfig = Config::all();
```

### Environment Variables (`.env`)

```env
# Application Configuration
APP_NAME="Kedai Emas"
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost
APP_TIMEZONE=Asia/Kuala_Lumpur

# Database Configuration
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=emas
DB_USER=root
DB_PASS=
DB_CHARSET=utf8mb4

# Session Configuration
SESSION_LIFETIME=7200
SESSION_PATH=/
SESSION_DOMAIN=
SESSION_SECURE=false
SESSION_HTTPONLY=true

# API Configuration (Metal Price API)
METAL_PRICE_API_KEY=
METAL_PRICE_API_URL=https://api.metalpriceapi.com/v1
```

---

## 3. Base Model Class

### Model Class (`app/model/Model.php`)

Abstract base class providing common database operations for all models.

### Available Methods

```php
use App\Model\Model;

// CRUD Operations
$model->findAll();                        // Get all records
$model->findById($id);                    // Find by primary key
$model->findWhere($conditions);           // Find by conditions
$model->findOne($conditions);             // Find single record
$model->insert($data);                    // Insert new record
$model->update($data, $conditions);       // Update records
$model->updateById($id, $data);          // Update by ID
$model->delete($conditions);              // Delete records
$model->deleteById($id);                  // Delete by ID
$model->count($conditions);               // Count records

// Custom Queries
$model->query($sql, $params);             // Execute custom query
$model->queryOne($sql, $params);          // Execute and get one result

// Transactions
$model->beginTransaction();
$model->commit();
$model->rollback();
```

### Creating a Model

```php
<?php
declare(strict_types=1);

namespace App\Model;

class Product extends Model
{
    protected string $table = 'product_data';
    protected string $primaryKey = 'id';

    public function findByName(string $name): array
    {
        return $this->findWhere(['name' => $name]);
    }

    public function getLowStock(int $threshold = 10): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE stock <= :threshold";
        return $this->query($sql, ['threshold' => $threshold]);
    }
}
```

**File Location:** `app/model/Product.php`

---

## 4. Base Controller Class

### Controller Class (`app/controller/Controller.php`)

Abstract base class providing common functionality for all controllers.

### Available Methods

```php
use App\Controller\Controller;

// View Rendering
$this->view('product/index', $data);              // Render view
$this->view('product/show', $data, 'admin');      // Render with custom layout
$this->view('product/show', $data, null);         // Render without layout

// Response Methods
$this->json($data, 200);                          // Return JSON response
$this->redirect('/products');                      // Redirect to URL
$this->error(404, 'Not found');                   // Show error page

// Request Helpers
$this->getMethod();                               // Get HTTP method
$this->isPost();                                  // Check if POST
$this->isGet();                                   // Check if GET
$this->isAjax();                                  // Check if AJAX

// Input Retrieval
$this->post('name');                              // Get POST data
$this->get('id');                                 // Get GET data
$this->input('search');                           // Get from POST or GET
$this->post();                                    // Get all POST data
$this->get();                                     // Get all GET data

// Validation
$missing = $this->validateRequired(['name', 'email'], $_POST);

// Flash Messages
$this->flash('success', 'Product created!');
$this->flash('error', 'Validation failed');
$flash = $this->getFlash();

// Input Sanitization
$clean = $this->sanitize($userInput);

// Authentication & Authorization
$user = $this->getUser();                         // Get authenticated user
$this->isAuthenticated();                         // Check if logged in
$this->requireAuth('/login');                     // Require login
$this->hasRole('Admin');                          // Check role
$this->requireRole('Admin');                      // Require role
```

### Creating a Controller

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Product;

class ProductController extends Controller
{
    private Product $productModel;

    public function __construct()
    {
        $this->productModel = new Product();
    }

    public function index(): void
    {
        $this->requireAuth();

        $products = $this->productModel->getAll();

        $this->view('product/index', [
            'products' => $products
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();
        $this->requireRole('Admin');

        if (!$this->isPost()) {
            $this->redirect('/products/create');
            return;
        }

        $name = $this->sanitize($this->post('name'));
        $description = $this->sanitize($this->post('description'));

        $id = $this->productModel->create($name, $description, 0, 0);

        if ($id) {
            $this->flash('success', 'Product created!');
            $this->redirect('/products/' . $id);
        } else {
            $this->flash('error', 'Failed to create product');
            $this->redirect('/products/create');
        }
    }
}
```

**File Location:** `app/controller/ProductController.php`

---

## 5. Router System

### Router Class (`app/config/Router.php`)

RESTful routing system with dynamic parameters and controller dispatching.

### Defining Routes (`config/routes.php`)

```php
use App\Config\Router;

// Assuming $router is initialized in index.php

// GET Routes
$router->get('/', 'HomeController@index');
$router->get('/products', 'ProductController@index');
$router->get('/products/{id}', 'ProductController@show');
$router->get('/products/{id}/edit', 'ProductController@editForm');

// POST Routes
$router->post('/products', 'ProductController@create');
$router->post('/products/{id}', 'ProductController@update');
$router->post('/products/{id}/delete', 'ProductController@delete');

// Multiple Methods
$router->match(['GET', 'POST'], '/contact', 'ContactController@handle');

// Any Method
$router->any('/webhook', 'WebhookController@handle');

// Custom 404
$router->set404('ErrorController@notFound');

// Set Base Path (if in subdirectory)
$router->setBasePath('/emas');
```

### Dynamic Route Parameters

```php
// Route definition
$router->get('/users/{userId}/posts/{postId}', 'PostController@show');

// Controller method
public function show(string $userId, string $postId): void
{
    $user = $this->userModel->findById((int)$userId);
    $post = $this->postModel->findById((int)$postId);
    // ...
}
```

---

## 6. Helper Functions

### Available Helpers (`bootstrap/helpers.php`)

#### Configuration
```php
env('DB_HOST', 'localhost');                // Get environment variable
config('APP_NAME', 'Default');              // Get config value
```

#### URLs
```php
base_url('/products');                      // http://localhost/products
asset('css/style.css');                     // http://localhost/public/css/style.css
redirect('/products', 302);                 // Redirect to URL
```

#### Debugging
```php
dd($variable);                              // Dump and die
dump($variable);                            // Dump variable
```

#### Forms
```php
csrf_token();                               // Generate CSRF token
csrf_field();                               // <input type="hidden" name="csrf_token" ...>
verify_csrf($token);                        // Verify CSRF token
old('name', 'default');                     // Get old input value
```

#### Sanitization
```php
escape($string);                            // Escape HTML
e($string);                                 // Alias for escape()
```

#### Authentication
```php
auth_user();                                // Get authenticated user
is_authenticated();                         // Check if logged in
```

#### Flash Messages
```php
flash('success', 'Product created!');       // Set flash message
$flash = get_flash();                       // Get and clear flash message
```

#### Currency & Gold Price
```php
format_currency(123.45);                    // RM 123.45
format_currency(123.45, false);             // 123.45
calculate_gold_price(8000, 5);              // Calculate with 5% margin
```

---

## 7. Bootstrap Process

### Application Flow

```
1. index.php (Entry Point)
   ├─> bootstrap/app.php
   │   ├─> Load environment variables (bootstrap/env.php)
   │   ├─> Register autoloader (bootstrap/autoload.php)
   │   ├─> Load helper functions (bootstrap/helpers.php)
   │   ├─> Configure error reporting (based on APP_ENV)
   │   ├─> Set timezone (from APP_TIMEZONE)
   │   └─> Start session
   ├─> Initialize Router
   ├─> Load routes (config/routes.php)
   └─> Dispatch request (Router->dispatch())
       ├─> Match URL to route
       ├─> Extract parameters
       ├─> Instantiate controller
       ├─> Call controller method
       └─> Return response
```

### Bootstrap File (`bootstrap/app.php`)

```php
<?php
declare(strict_types=1);

// Define constants
define('ROOT_DIR', dirname(__DIR__));
define('APP_DIR', ROOT_DIR . '/app');
define('PUBLIC_DIR', ROOT_DIR . '/public');
define('CONFIG_DIR', ROOT_DIR . '/config');

// Load environment variables
require_once __DIR__ . '/env.php';

// Register autoloader
require_once __DIR__ . '/autoload.php';

// Load helper functions
require_once __DIR__ . '/helpers.php';

// Error reporting based on environment
if (getenv('APP_ENV') === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

// Set timezone
date_default_timezone_set(getenv('APP_TIMEZONE') ?: 'Asia/Kuala_Lumpur');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
```

### Entry Point (`index.php`)

```php
<?php
declare(strict_types=1);

// Bootstrap the application
require_once __DIR__ . '/bootstrap/app.php';

use App\Config\Router;

// Initialize Router
$router = new Router();

// Load routes
require_once __DIR__ . '/config/routes.php';

// Dispatch the request
try {
    $router->dispatch();
} catch (Throwable $e) {
    // Handle errors
    error_log("Application Error: " . $e->getMessage());
    http_response_code(500);
    echo "An error occurred";
}
```

---

## 8. Composer Commands

### Basic Commands

```bash
# Install dependencies
composer install

# Update dependencies
composer update

# Regenerate autoloader (after adding new classes)
composer dump-autoload

# Optimize autoloader for production
composer dump-autoload --optimize
```

### Custom Scripts (from composer.json)

```bash
# Database setup (create DB + run migrations)
composer db:setup

# Create database only
composer db:create

# Run migrations only
composer db:migrate

# Run tests (when implemented)
composer test

# Static analysis
composer phpstan

# Check code style
composer phpcs

# Fix code style
composer phpcbf
```

---

## 9. Migration from Old Structure

### Old → New Mapping

| Old | New |
|-----|-----|
| `require_once 'model/product.php'` | `use App\Model\Product;` |
| `require_once 'controller/ProductController.php'` | `use App\Controller\ProductController;` |
| `require_once 'config/connection.php'` | Database included via autoloader |
| `new Product()` | `new Product()` (same, but namespaced) |
| `getenv('DB_HOST')` | `Config::get('DB_HOST')` |
| Manual error checking | `Controller::requireAuth()` |
| Manual input sanitization | `Controller::sanitize()` |
| Manual redirects | `Controller::redirect()` or `redirect()` |

### Updating Old Files

**Before:**
```php
<?php
require_once 'config/connection.php';

class Product {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }
}
```

**After:**
```php
<?php
declare(strict_types=1);

namespace App\Model;

class Product extends Model {
    protected string $table = 'product_data';
    protected string $primaryKey = 'id';

    // Inherits database connection from Model base class
}
```

---

## 10. Best Practices

### General

1. **Always use namespaces** - `namespace App\Model;`
2. **Use strict types** - `declare(strict_types=1);`
3. **Type hint everything** - Function parameters and return types
4. **Use base classes** - Extend `Model` and `Controller`
5. **Keep controllers thin** - Business logic goes in models
6. **Use helpers** - Leverage helper functions for common tasks

### Security

1. **Sanitize input** - Use `sanitize()` or `escape()`
2. **Validate input** - Use `validateRequired()` and custom validation
3. **Use CSRF protection** - `csrf_field()` and `verify_csrf()`
4. **Check authentication** - `requireAuth()` in protected routes
5. **Check authorization** - `requireRole()` for admin actions
6. **Use prepared statements** - Base Model class handles this

### Code Organization

1. **One class per file** - Match filename to class name
2. **Follow PSR-12** - Coding standards
3. **Document your code** - Use PHPDoc comments
4. **Use meaningful names** - Clear, descriptive variable/method names
5. **Keep methods small** - Single responsibility principle

---

## 11. Troubleshooting

### "Class not found" Error

**Solution 1: Regenerate autoloader**
```bash
composer dump-autoload
```

**Solution 2: Check namespace and file location**
```php
// Namespace: App\Model\Product
// File must be: app/model/Product.php (case-sensitive!)
```

**Solution 3: Check class name matches filename**
```php
// File: Product.php
// Class: class Product (must match exactly)
```

### Config Not Loading

**Check .env file exists:**
```bash
ls -la .env
```

**Manually load in your file:**
```php
require_once __DIR__ . '/bootstrap/env.php';
```

### Database Connection Error

**Test connection:**
```php
use App\Config\Config;

$db = Config::getDatabase();
var_dump($db); // Check credentials
```

**Check MySQL service:**
```bash
# macOS
brew services list | grep mysql

# Linux
sudo systemctl status mysql
```

### Routes Not Working

**Check .htaccess exists:**
```apache
# .htaccess in project root
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [QSA,L]
```

**Check route definition:**
```php
// Use correct controller namespace
$router->get('/products', 'ProductController@index');
// Controller must be: App\Controller\ProductController
```

### Session Issues

**Check session configuration:**
```php
// In .env
SESSION_LIFETIME=7200
SESSION_HTTPONLY=true
```

**Clear session data:**
```php
session_destroy();
```

---

## 12. Summary

Your Kedai Emas application now has:

✅ **PSR-4 Autoloading** - Custom autoloader + Composer support
✅ **Base Model Class** - Complete CRUD operations with inheritance
✅ **Base Controller Class** - Authentication, validation, and helpers
✅ **Config Management** - Centralized environment configuration
✅ **Router System** - RESTful routing with dynamic parameters
✅ **Helper Functions** - 20+ utility functions globally available
✅ **Bootstrap System** - Proper application initialization
✅ **MVC Architecture** - Clean separation of concerns
✅ **Security Features** - CSRF, sanitization, auth, and authorization
✅ **Professional Structure** - Industry-standard PHP development

---

## Additional Resources

- **MVC_SETUP.md** - Detailed MVC documentation and examples
- **composer.json** - Dependency and script configuration
- **.env.example** - Environment variable template
- **QUICK_REFERENCE.md** - Quick reference guide

You're now ready for professional PHP development with a solid MVC foundation! 🚀
