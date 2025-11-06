<?php
declare(strict_types=1);

/**
 * Application Routes Configuration
 *
 * Define all application routes here.
 * Routes support parameters using :paramName syntax
 * Example: /product/:id will match /product/123 and pass id=123 to the controller
 */

// Home/Dashboard Routes
$router->get('/', 'DashboardController', 'index', 'home');
$router->get('/dashboard', 'DashboardController', 'index', 'dashboard');

// Product Routes
$router->get('/products', 'App\Controllers\ProductController', 'index', 'products.index');
$router->get('/products/create', 'App\Controllers\ProductController', 'create', 'products.create');
$router->post('/products/store', 'App\Controllers\ProductController', 'store', 'products.store');
$router->get('/products/:id/edit', 'App\Controllers\ProductController', 'edit', 'products.edit');
$router->post('/products/:id/update', 'App\Controllers\ProductController', 'update', 'products.update');
$router->get('/products/:id/delete', 'App\Controllers\ProductController', 'delete', 'products.delete');
$router->get('/products/search', 'App\Controllers\ProductController', 'search', 'products.search');

// User Routes (to be implemented)
$router->get('/users', 'UserController', 'index', 'users.index');
$router->get('/users/create', 'UserController', 'create', 'users.create');
$router->post('/users/store', 'UserController', 'store', 'users.store');
$router->get('/users/:id/edit', 'UserController', 'edit', 'users.edit');
$router->post('/users/:id/update', 'UserController', 'update', 'users.update');
$router->get('/users/:id/delete', 'UserController', 'delete', 'users.delete');

// Auth Routes (to be implemented)
$router->get('/login', 'AuthController', 'showLogin', 'login');
$router->post('/login', 'AuthController', 'doLogin', 'login.post');
$router->get('/register', 'AuthController', 'showRegister', 'register');
$router->post('/register', 'AuthController', 'doRegister', 'register.post');
$router->get('/logout', 'AuthController', 'logout', 'logout');

// Contact Routes (to be implemented)
$router->get('/contacts', 'ContactController', 'index', 'contacts.index');
$router->get('/contacts/:id', 'ContactController', 'show', 'contacts.show');
$router->get('/contacts/:id/delete', 'ContactController', 'delete', 'contacts.delete');

// Role Routes (to be implemented)
$router->get('/roles', 'RoleController', 'index', 'roles.index');
$router->get('/roles/create', 'RoleController', 'create', 'roles.create');
$router->post('/roles/store', 'RoleController', 'store', 'roles.store');

// Profit Margin Routes (to be implemented)
$router->get('/profit-margins', 'ProfitMarginController', 'index', 'profit-margins.index');
$router->get('/profit-margins/create', 'ProfitMarginController', 'create', 'profit-margins.create');
$router->post('/profit-margins/store', 'ProfitMarginController', 'store', 'profit-margins.store');

// API Routes (for AJAX requests)
$router->get('/api/gold-price', 'ApiController', 'getGoldPrice', 'api.gold-price');
$router->get('/api/products', 'ApiController', 'getProducts', 'api.products');
