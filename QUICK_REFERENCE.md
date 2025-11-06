# Quick Reference - Autoloading & Configuration

## File Locations

```
Old Structure (Deprecated)     →  New Structure (Use This)
─────────────────────────────     ───────────────────────────
model/product.php              →  src/Models/Product.php
controller/ProductController   →  src/Controllers/ProductController.php
config/connection.php          →  src/Config/Database.php
config/Router.php              →  src/Config/Router.php
```

## Common Tasks

### 1. Create New Model

```php
<?php
// File: src/Models/User.php

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
}
```

### 2. Create New Controller

```php
<?php
// File: src/Controllers/UserController.php

namespace App\Controllers;

use App\Models\User;

class UserController
{
    private User $model;

    public function __construct()
    {
        $this->model = new User();
    }

    public function index(): void
    {
        $users = $this->model->getAll();
        require_once view_path('user/index.php');
    }
}
```

### 3. Register Routes

```php
// File: config/routes.php

$router->get('/users', 'App\Controllers\UserController', 'index', 'users.index');
$router->get('/users/:id', 'App\Controllers\UserController', 'show', 'users.show');
$router->post('/users/store', 'App\Controllers\UserController', 'store', 'users.store');
```

### 4. Use Configuration

```php
use App\Config\Config;

// Single value
$dbHost = Config::get('DB_HOST');
$appName = Config::get('APP_NAME', 'Default Name');

// Grouped
$database = Config::database();
$app = Config::app();

// Dot notation
$host = Config::get('database.host');

// Environment checks
if (Config::isDevelopment()) { /* ... */ }
if (Config::isProduction()) { /* ... */ }
```

### 5. Database Operations

```php
use App\Config\Database;

// Get connection
$pdo = Database::getConnection();

// In your model
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

### 6. Use Helper Functions

```php
// Config
$value = env('DB_HOST', 'localhost');
$value = config('app.name');

// Debug
dd($data);          // Dump and die
dump($data);        // Just dump

// URLs
redirect('/products');
$url = url('/products');
$asset = asset('css/style.css');

// Session
$userId = session('user_id');
session('user_id', 123);

// Security
$token = csrf_token();
echo csrf_field();
$clean = sanitize($input);

// Formatting
$price = format_currency(123.45);  // RM 123.45
$date = now('Y-m-d H:i:s');
```

## Import Statements

### Common Imports

```php
<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Config\Config;      // Configuration
use App\Config\Database;    // Database
use App\Models\Product;     // Your models
use PDO;                    // Standard PDO
```

## Composer Commands

```bash
# Installation
composer install            # Install dependencies
composer update            # Update dependencies
composer dump-autoload     # Regenerate autoloader

# Custom Scripts
composer db:setup          # Create database + run migrations
composer db:migrate        # Run migrations only
composer db:seed          # Seed database
composer test             # Run tests
```

## Environment Variables

```env
# .env file format
APP_NAME="Kedai Emas"
APP_ENV=development        # development | production
APP_DEBUG=true            # true | false
APP_URL=http://localhost

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=emas
DB_USER=root
DB_PASS=

SESSION_LIFETIME=7200
SESSION_SECURE=false
```

## Namespace to File Mapping

```
Namespace                          File Path
──────────────────────────────     ─────────────────────────
App\Models\Product                 src/Models/Product.php
App\Controllers\ProductController  src/Controllers/ProductController.php
App\Config\Database                src/Config/Database.php
App\Config\Router                  src/Config/Router.php
```

## Controller Method Signatures

```php
// No parameters
public function index(): void { }

// With URL parameter (/products/:id)
public function show(string $id = ''): void { }

// Multiple parameters (/users/:id/posts/:postId)
public function showPost(string $id = '', string $postId = ''): void { }
```

## View Rendering

```php
// Old way (still works)
require_once __DIR__ . '/../view/product/index.php';

// New way (recommended)
require_once view_path('product/index.php');

// Pass data to view
$products = $this->model->getAll();
$controller = $this;
require_once view_path('product/index.php');
```

## Session Flash Messages

```php
// In controller
$_SESSION['success'] = "Product created!";
$_SESSION['error'] = "Failed to create product";
redirect('/products');

// In view
<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($_SESSION['success']) ?>
        <?php unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>
```

## Error Handling

```php
// Development
if (Config::isDevelopment()) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// Production
if (Config::isProduction()) {
    error_reporting(E_ALL & ~E_DEPRECATED);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}
```

## Routing Patterns

```php
// Basic routes
$router->get('/path', 'Controller', 'method', 'route.name');
$router->post('/path', 'Controller', 'method', 'route.name');
$router->any('/path', 'Controller', 'method', 'route.name');

// With parameters
$router->get('/users/:id', 'UserController', 'show', 'users.show');
$router->get('/posts/:slug', 'PostController', 'show', 'posts.show');
$router->get('/category/:cat/product/:id', 'ProductController', 'show', 'products.show');
```

## Checklist for New Features

- [ ] Create Model in `src/Models/`
- [ ] Create Controller in `src/Controllers/`
- [ ] Add routes in `config/routes.php`
- [ ] Create views in `view/`
- [ ] Test in browser
- [ ] Run `composer dump-autoload` if needed

## Troubleshooting

| Issue | Solution |
|-------|----------|
| "Class not found" | Run `composer dump-autoload` |
| Config not loading | Add `Config::load()` at top |
| Database error | Check `.env` file exists and has correct values |
| Routes not working | Check `.htaccess` file exists |
| Autoloader not working | Run `composer install` |

## File Templates

### Model Template
```php
<?php
declare(strict_types=1);
namespace App\Models;
use App\Config\Database;
use PDO;

class ModelName
{
    private PDO $db;
    public function __construct()
    {
        $this->db = Database::getConnection();
    }
}
```

### Controller Template
```php
<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Models\ModelName;

class ControllerName
{
    private ModelName $model;
    public function __construct()
    {
        $this->model = new ModelName();
    }
    public function index(): void
    {
        $data = $this->model->getAll();
        require_once view_path('folder/index.php');
    }
}
```

---

**Pro Tip:** Always use namespaces in new files and leverage Composer's autoloading!
