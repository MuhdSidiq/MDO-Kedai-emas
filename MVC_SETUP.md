# MVC Structure Documentation

## Overview

This document describes the MVC (Model-View-Controller) architecture implemented for the Kedai Emas application.

## Directory Structure

```
emas/
├── app/
│   ├── config/
│   │   ├── Config.php          # Configuration management class
│   │   ├── Router.php          # URL routing handler
│   │   └── connection.php      # Database connection
│   ├── controller/
│   │   └── Controller.php      # Base controller class
│   ├── model/
│   │   └── Model.php           # Base model class
│   └── view/
│       └── layouts/            # Layout templates
├── bootstrap/
│   ├── app.php                 # Application bootstrap
│   ├── autoload.php            # PSR-4 autoloader
│   ├── env.php                 # Environment loader
│   └── helpers.php             # Helper functions
├── config/
│   └── routes.php              # Application routes
├── public/                     # Public assets
├── index.php                   # Application entry point
└── .env                        # Environment variables
```

## Core Components

### 1. Base Model (`app/model/Model.php`)

Abstract class providing common database operations for all models.

**Key Methods:**
- `findAll()` - Retrieve all records
- `findById($id)` - Find record by primary key
- `findWhere($conditions)` - Find records by conditions
- `findOne($conditions)` - Find single record
- `insert($data)` - Insert new record
- `update($data, $conditions)` - Update records
- `updateById($id, $data)` - Update by ID
- `delete($conditions)` - Delete records
- `deleteById($id)` - Delete by ID
- `count($conditions)` - Count records
- `query($sql, $params)` - Execute custom query
- Transaction methods: `beginTransaction()`, `commit()`, `rollback()`

**Usage Example:**
```php
namespace App\Model;

class User extends Model
{
    protected string $table = 'users';
    protected string $primaryKey = 'id';

    public function findByEmail(string $email): ?array
    {
        return $this->findOne(['email' => $email]);
    }
}
```

### 2. Base Controller (`app/controller/Controller.php`)

Abstract class providing common functionality for all controllers.

**Key Methods:**
- `view($view, $data, $layout)` - Render views
- `json($data, $statusCode)` - Return JSON response
- `redirect($url, $statusCode)` - Redirect to URL
- `isPost()`, `isGet()`, `isAjax()` - Request method checks
- `post($key, $default)` - Get POST data
- `get($key, $default)` - Get GET data
- `input($key, $default)` - Get input from GET/POST
- `validateRequired($fields, $data)` - Validate required fields
- `flash($type, $message)` - Set flash messages
- `error($code, $message)` - Handle errors
- `sanitize($input)` - Sanitize input
- `getUser()`, `isAuthenticated()` - Authentication helpers
- `hasRole($role)`, `requireRole($role)` - Authorization helpers

**Usage Example:**
```php
namespace App\Controller;

class UserController extends Controller
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new \App\Model\User();
    }

    public function index(): void
    {
        $this->requireAuth();
        $users = $this->userModel->findAll();
        $this->view('user/index', ['users' => $users]);
    }
}
```

### 3. Router (`app/config/Router.php`)

Handles URL routing and dispatches requests to controllers.

**Key Methods:**
- `get($path, $handler)` - Register GET route
- `post($path, $handler)` - Register POST route
- `put($path, $handler)` - Register PUT route
- `delete($path, $handler)` - Register DELETE route
- `any($path, $handler)` - Register route for any method
- `match($methods, $path, $handler)` - Register multiple methods
- `set404($handler)` - Set custom 404 handler
- `setBasePath($path)` - Set base path for subdirectory
- `dispatch()` - Dispatch current request

**Features:**
- Dynamic route parameters: `/user/{id}`, `/post/{slug}`
- Regex pattern matching
- Controller method routing: `UserController@show`
- Callable/closure support

**Usage Example:**
```php
// In config/routes.php
$router->get('/users', 'UserController@index');
$router->get('/users/{id}', 'UserController@show');
$router->post('/users', 'UserController@create');
```

### 4. Config Class (`app/config/Config.php`)

Provides easy access to configuration values from environment variables.

**Key Methods:**
- `get($key, $default)` - Get any config value
- `getAppName()` - Get application name
- `getAppEnv()` - Get environment (development/production)
- `isDevelopment()`, `isProduction()` - Environment checks
- `isDebug()` - Check debug mode
- `getDatabase()` - Get database config
- `getSession()` - Get session config
- `getMetalPriceApi()` - Get API config
- `all()` - Get all configuration

### 5. Autoloader (`bootstrap/autoload.php`)

PSR-4 compliant autoloader for the `App\` namespace.

**Namespace Mapping:**
- `App\Model\*` → `app/model/*.php`
- `App\Controller\*` → `app/controller/*.php`
- `App\Config\*` → `app/config/*.php`

### 6. Helper Functions (`bootstrap/helpers.php`)

Global helper functions available throughout the application.

**Available Helpers:**
- `env($key, $default)` - Get environment variable
- `config($key, $default)` - Get configuration value
- `base_url($path)` - Generate base URL
- `asset($path)` - Generate asset URL
- `redirect($url, $statusCode)` - Redirect helper
- `dd(...$vars)` - Dump and die (debugging)
- `dump(...$vars)` - Dump variables (debugging)
- `old($key, $default)` - Get old input for forms
- `csrf_token()` - Generate CSRF token
- `csrf_field()` - Generate CSRF hidden field
- `verify_csrf($token)` - Verify CSRF token
- `escape($string)`, `e($string)` - Escape HTML
- `auth_user()` - Get authenticated user
- `is_authenticated()` - Check if authenticated
- `flash($type, $message)` - Set flash message
- `get_flash()` - Get flash message
- `format_currency($amount)` - Format as RM currency
- `calculate_gold_price($basePrice, $margin)` - Calculate gold price

## Application Flow

1. **Request** → `index.php`
2. **Bootstrap** → `bootstrap/app.php`
   - Load environment variables
   - Register autoloader
   - Load helpers
   - Configure error reporting
   - Start session
3. **Routing** → `config/routes.php`
   - Define all application routes
4. **Dispatch** → `Router->dispatch()`
   - Match URL to route
   - Instantiate controller
   - Call controller method
5. **Controller** → Process request
   - Interact with models
   - Prepare data
   - Render view or return response
6. **View** → Render HTML (optional)
7. **Response** → Send to client

## Creating New Components

### Creating a Model

```php
<?php
namespace App\Model;

class Product extends Model
{
    protected string $table = 'product_data';
    protected string $primaryKey = 'id';

    public function findInStock(): array
    {
        return $this->findWhere(['stock_quantity >' => 0]);
    }

    public function reduceStock(int $productId, int $quantity): bool
    {
        $product = $this->findById($productId);
        if (!$product) return false;

        $newQuantity = $product['stock_quantity'] - $quantity;
        if ($newQuantity < 0) return false;

        $this->updateById($productId, ['stock_quantity' => $newQuantity]);
        return true;
    }
}
```

### Creating a Controller

```php
<?php
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
        $products = $this->productModel->findAll();
        $this->view('product/index', ['products' => $products]);
    }

    public function show(string $id): void
    {
        $product = $this->productModel->findById((int)$id);

        if (!$product) {
            $this->error(404, 'Product not found');
            return;
        }

        $this->view('product/show', ['product' => $product]);
    }

    public function create(): void
    {
        $this->requireAuth();
        $this->requireRole('Admin');

        if ($this->isPost()) {
            $data = [
                'product_name' => $this->sanitize($this->post('product_name')),
                'product_code' => $this->sanitize($this->post('product_code')),
                'weight_grams' => (float)$this->post('weight_grams'),
                'stock_quantity' => (int)$this->post('stock_quantity')
            ];

            $id = $this->productModel->insert($data);
            $this->flash('success', 'Product created successfully');
            $this->redirect('/products/' . $id);
        }

        $this->view('product/create');
    }
}
```

### Adding Routes

```php
// In config/routes.php
$router->get('/products', 'ProductController@index');
$router->get('/products/create', 'ProductController@createForm');
$router->post('/products/create', 'ProductController@create');
$router->get('/products/{id}', 'ProductController@show');
$router->post('/products/{id}/edit', 'ProductController@update');
$router->post('/products/{id}/delete', 'ProductController@delete');
```

## Security Features

1. **CSRF Protection**: Use `csrf_field()` in forms and `verify_csrf()` to validate
2. **Input Sanitization**: Use `sanitize()` method or `escape()`/`e()` helper
3. **SQL Injection Prevention**: PDO with prepared statements in Model class
4. **XSS Prevention**: Automatic escaping with `htmlspecialchars()`
5. **Authentication**: `requireAuth()` and `isAuthenticated()` methods
6. **Authorization**: `requireRole()` and `hasRole()` methods
7. **Session Security**: HTTPOnly, SameSite, and secure cookie settings

## Best Practices

1. **Namespace Everything**: Use `App\` namespace for all classes
2. **Type Declarations**: Use strict types and type hints
3. **Error Handling**: Use try-catch blocks for database operations
4. **Validation**: Always validate user input before processing
5. **Sanitization**: Escape output when rendering in views
6. **MVC Separation**: Keep business logic in models, not controllers
7. **DRY Principle**: Reuse base class methods instead of duplicating code
8. **CSRF Protection**: Always include CSRF tokens in forms
9. **Authentication**: Check authentication before accessing protected routes
10. **Environment Variables**: Use `.env` for configuration, never hardcode

## Troubleshooting

### Autoloader Not Working
- Ensure namespace matches directory structure
- Check file permissions
- Verify class names match file names (case-sensitive)

### Routes Not Working
- Check `.htaccess` for mod_rewrite rules
- Verify base path configuration if in subdirectory
- Ensure route order (specific routes before generic ones)

### Database Connection Failed
- Verify `.env` database credentials
- Ensure MySQL service is running
- Check database exists (use `connection.php --setup`)

### Session Issues
- Check session configuration in `.env`
- Verify session directory permissions
- Clear browser cookies if testing locally

## Next Steps

1. Create specific models for each table (User, Role, Product, etc.)
2. Create controllers for each resource
3. Create view templates with layouts
4. Implement authentication and authorization
5. Integrate Metal Price API
6. Add validation and error handling
7. Create admin dashboard
8. Implement gold price calculator

## Resources

- PSR-4 Autoloading: https://www.php-fig.org/psr/psr-4/
- PDO Documentation: https://www.php.net/manual/en/book.pdo.php
- MVC Pattern: https://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller
