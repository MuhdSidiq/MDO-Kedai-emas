<?php
declare(strict_types=1);

/**
 * Application Routes
 *
 * Define all application routes here
 */

use App\Config\Router;

// Assuming $router is passed from index.php

// Home/Landing Page
$router->get('/', 'HomeController@index');

// Authentication Routes
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/register', 'AuthController@showRegister');
$router->post('/register', 'AuthController@register');
$router->get('/logout', 'AuthController@logout');

// Dashboard (requires authentication)
$router->get('/dashboard', 'DashboardController@index');

// User Management Routes
$router->get('/users', 'UserController@index');
$router->get('/users/{id}', 'UserController@show');
$router->post('/users', 'UserController@create');
$router->post('/users/{id}', 'UserController@update');
$router->post('/users/{id}/delete', 'UserController@delete');

// Role Management Routes
$router->get('/roles', 'RoleController@index');
$router->get('/roles/{id}', 'RoleController@show');
$router->post('/roles', 'RoleController@create');
$router->post('/roles/{id}', 'RoleController@update');
$router->post('/roles/{id}/delete', 'RoleController@delete');

// Product Management Routes
// NOTE: Static routes MUST come before dynamic routes to avoid conflicts
$router->get('/products', 'ProductController@index');
$router->get('/products/search', 'ProductController@search');
$router->get('/products/low-stock', 'ProductController@lowStock');
$router->get('/products/out-of-stock', 'ProductController@outOfStock');
$router->get('/products/create', 'ProductController@createForm');
$router->post('/products/create', 'ProductController@create');
$router->get('/products/{id}', 'ProductController@show');
$router->get('/products/{id}/edit', 'ProductController@editForm');
$router->post('/products/{id}/update', 'ProductController@update');
$router->post('/products/{id}/delete', 'ProductController@delete');
$router->post('/products/{id}/stock', 'ProductController@updateStock');
$router->post('/products/{id}/add-stock', 'ProductController@addStock');
$router->post('/products/{id}/reduce-stock', 'ProductController@reduceStock');

// Profit Margin Management Routes
$router->get('/profit-margins', 'ProfitMarginController@index');
$router->get('/profit-margins/{id}', 'ProfitMarginController@show');
$router->post('/profit-margins', 'ProfitMarginController@create');
$router->post('/profit-margins/{id}', 'ProfitMarginController@update');
$router->post('/profit-margins/{id}/delete', 'ProfitMarginController@delete');

// Gold Price Routes
$router->get('/gold-prices', 'GoldPriceController@index');
$router->get('/gold-prices/current', 'GoldPriceController@current');
$router->get('/gold-prices/history', 'GoldPriceController@history');

// Contact Form Routes
$router->get('/contact', 'ContactController@showForm');
$router->post('/contact', 'ContactController@submit');
$router->get('/contact/submissions', 'ContactController@index');
$router->get('/contact/submissions/{id}', 'ContactController@show');

// API Routes
$router->get('/api/gold-prices/current', 'Api\GoldPriceController@current');
$router->get('/api/gold-prices/history', 'Api\GoldPriceController@history');
$router->post('/api/gold-prices/update', 'Api\GoldPriceController@update');

// Custom 404 handler (optional)
// $router->set404('ErrorController@notFound');
