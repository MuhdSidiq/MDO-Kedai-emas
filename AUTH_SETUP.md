# Delight-IM Auth Integration Guide

## Overview

Your Kedai Emas application now uses the **Delight-IM Auth** package for secure authentication and user management. This professional authentication solution provides login, registration, password reset, email verification, and role-based access control.

---

## Installation

The Delight-IM Auth package is already installed via Composer:

```json
{
    "require": {
        "delight-im/auth": "^9.0"
    }
}
```

---

## Database Setup

### 1. Run the Auth SQL Migration

The Delight Auth package requires its own database tables. Run this SQL file:

```bash
mysql -u root -p emas < vendor/delight-im/auth/Database/MySQL.sql
```

Or import it manually in phpMyAdmin/MySQL Workbench.

### Tables Created:
- `users` - User accounts
- `users_confirmations` - Email verification tokens
- `users_remembered` - "Remember me" tokens
- `users_resets` - Password reset tokens
- `users_throttling` - Rate limiting
- `users_2fa` - Two-factor authentication (optional)
- `users_audit_log` - Security audit log

---

## Architecture

### Files Created

```
app/
├── config/
│   └── Auth.php              # Auth wrapper class
├── controller/
│   ├── AuthController.php    # Login/Register/Logout
│   └── UserController.php    # User management (CRUD)
└── model/
    └── user.php              # User model
```

---

## Auth Helper Class (`app/config/Auth.php`)

Wrapper for Delight Auth with simplified methods.

### Basic Usage

```php
use App\Config\Auth;

// Check if logged in
if (Auth::isLoggedIn()) {
    // User is authenticated
}

// Get current user ID
$userId = Auth::getUserId();

// Get current user email
$email = Auth::getEmail();

// Get current username
$username = Auth::getUsername();
```

### Registration

```php
use App\Config\Auth;

try {
    $userId = Auth::register($email, $password, $username);
    // User registered successfully
} catch (\Exception $e) {
    // Handle error: $e->getMessage()
}
```

### Login

```php
use App\Config\Auth;

try {
    // Regular login
    Auth::login($email, $password);

    // Login with "Remember Me" (30 days)
    Auth::login($email, $password, 60 * 60 * 24 * 30);

    // Success
} catch (\Exception $e) {
    // Handle error: $e->getMessage()
}
```

### Logout

```php
use App\Config\Auth;

Auth::logout();
```

### Role Management

```php
use App\Config\Auth;
use Delight\Auth\Role;

// Check if user has role
if (Auth::hasRole(Role::ADMIN)) {
    // User is admin
}

// Add role to user
Auth::addRole($userId, Role::ADMIN);

// Remove role from user
Auth::removeRole($userId, Role::ADMIN);

// Check multiple roles
if (Auth::hasAnyRole([Role::ADMIN, Role::MODERATOR])) {
    // User is admin OR moderator
}

if (Auth::hasAllRoles([Role::ADMIN, Role::MODERATOR])) {
    // User is admin AND moderator
}
```

### Password Management

```php
use App\Config\Auth;

// Change password (user must be logged in)
try {
    Auth::changePassword($oldPassword, $newPassword);
} catch (\Exception $e) {
    // Handle error
}

// Forgot password
try {
    Auth::forgotPassword($email, function($selector, $token) {
        // Send email with reset link
        $resetUrl = "https://yoursite.com/reset-password?selector={$selector}&token={$token}";
        // mail($email, 'Reset Password', $resetUrl);
    });
} catch (\Exception $e) {
    // Handle error
}

// Reset password with token
try {
    Auth::resetPassword($selector, $token, $newPassword);
} catch (\Exception $e) {
    // Handle error
}
```

### Admin Operations

```php
use App\Config\Auth;

// Create user (admin only)
$userId = Auth::admin()->createUser($email, $password, $username);

// Delete user (admin only)
Auth::deleteUser($userId);

// Change user's email (admin only)
Auth::admin()->changeEmailForUserById($userId, $newEmail);

// Change user's password (admin only)
Auth::admin()->changePasswordForUserById($userId, $newPassword);
```

---

## Available Roles

The application uses the following role mapping:

```php
use Delight\Auth\Role;

Role::ADMIN       => 'Admin'        // Full access
Role::MODERATOR   => 'Staff'        // Staff access
Role::SUBSCRIBER  => 'Agent'        // Agent access
Role::DEVELOPER   => 'Customer'     // Customer access
```

### Role Constants (for bitwise operations)

```php
Role::ADMIN       = 1   // 0001
Role::MODERATOR   = 2   // 0010
Role::SUBSCRIBER  = 4   // 0100
Role::DEVELOPER   = 8   // 1000
```

Users can have multiple roles using bitwise OR:
```php
$roles_mask = Role::ADMIN | Role::MODERATOR; // 3 (0011)
```

---

## AuthController (`app/controller/AuthController.php`)

Handles public authentication pages.

### Routes

```php
// Login
GET  /login           → AuthController@showLogin
POST /login           → AuthController@login

// Registration
GET  /register        → AuthController@showRegister
POST /register        → AuthController@register

// Logout
GET  /logout          → AuthController@logout

// Forgot Password
GET  /forgot-password → AuthController@showForgotPassword
POST /forgot-password → AuthController@forgotPassword

// Reset Password
GET  /reset-password  → AuthController@showResetPassword
POST /reset-password  → AuthController@resetPassword
```

### Features

- ✅ Email/Password login
- ✅ "Remember Me" functionality
- ✅ User registration with email verification
- ✅ Password reset via email
- ✅ Input validation and sanitization
- ✅ Flash messages for user feedback
- ✅ Rate limiting (built into Delight Auth)

---

## UserController (`app/controller/UserController.php`)

Handles user management (admin panel).

### Routes

```php
// List users
GET  /users                      → UserController@index (Admin only)

// View user
GET  /users/{id}                 → UserController@show

// Create user
GET  /users/create               → UserController@createForm (Admin only)
POST /users                      → UserController@create (Admin only)

// Edit user
GET  /users/{id}/edit            → UserController@editForm
POST /users/{id}                 → UserController@update

// Delete user
POST /users/{id}/delete          → UserController@delete (Admin only)

// Search users
GET  /users/search?q={term}      → UserController@search (Admin only)

// Change password
GET  /users/{id}/change-password → UserController@changePasswordForm
POST /users/{id}/change-password → UserController@changePassword

// Toggle verification (AJAX)
POST /users/{id}/verify          → UserController@toggleVerification (Admin only)
```

### Features

- ✅ Full CRUD operations
- ✅ Role assignment and management
- ✅ User search functionality
- ✅ Password management
- ✅ Email verification toggle
- ✅ User statistics
- ✅ Permission checks (users can edit themselves, admins can edit all)

---

## User Model (`app/model/user.php`)

### Available Methods

```php
use App\Model\User;

$userModel = new User();

// Basic CRUD (inherited from Model base class)
$users = $userModel->getAll();
$user = $userModel->getById($id);
$user = $userModel->findByEmail($email);
$user = $userModel->findByUsername($username);

// Filtering
$verified = $userModel->getVerifiedUsers();
$unverified = $userModel->getUnverifiedUsers();
$byStatus = $userModel->getByStatus($status);

// Search
$results = $userModel->search($searchTerm);

// Statistics
$stats = $userModel->getStatistics();
// Returns: ['total' => 50, 'verified' => 45, 'unverified' => 5, 'active' => 48]

$count = $userModel->getTotalCount();
$verified = $userModel->getVerifiedCount();

// Recent users
$recent = $userModel->getRecentUsers(10);

// Roles
$user = $userModel->getUserWithRole($userId);
// Returns user with 'roles' array: ['roles' => ['Admin', 'Staff']]

$admins = $userModel->getUsersByRole(Role::ADMIN);

// Utilities
$exists = $userModel->emailExists($email);
$exists = $userModel->usernameExists($username);
$userModel->updateLastLogin($userId);
```

---

## Using in Controllers

### Require Authentication

```php
namespace App\Controller;

class DashboardController extends Controller
{
    public function index(): void
    {
        // Require user to be logged in
        $this->requireAuth();

        // Your code here
    }
}
```

### Require Specific Role

```php
public function adminPanel(): void
{
    $this->requireAuth();
    $this->requireRole('Admin');

    // Only admins can access this
}
```

### Check Role Manually

```php
public function dashboard(): void
{
    $this->requireAuth();

    if ($this->hasRole('Admin')) {
        // Show admin dashboard
    } else {
        // Show regular dashboard
    }
}
```

### Get Current User

```php
use App\Config\Auth;

public function profile(): void
{
    $this->requireAuth();

    $userId = Auth::getUserId();
    $email = Auth::getEmail();
    $username = Auth::getUsername();

    // Load full user data
    $userModel = new User();
    $user = $userModel->getUserWithRole($userId);

    $this->view('profile', ['user' => $user]);
}
```

---

## Security Features

### Built-in Protection

✅ **Password Hashing** - bcrypt with automatic salt
✅ **SQL Injection Prevention** - Prepared statements (PDO)
✅ **XSS Protection** - Input sanitization
✅ **CSRF Protection** - Use `csrf_field()` in forms
✅ **Rate Limiting** - Automatic throttling of login attempts
✅ **Session Security** - Secure session management
✅ **Password Reset** - Secure token-based reset
✅ **Email Verification** - Prevent fake accounts
✅ **Remember Me** - Secure persistent login
✅ **Force Logout** - Admin can force user logout

### Best Practices

1. **Always validate input** - Use `sanitize()` and `validateRequired()`
2. **Use CSRF tokens** - Add `csrf_field()` to all forms
3. **Check permissions** - Use `requireAuth()` and `requireRole()`
4. **Sanitize output** - Use `escape()` or `e()` in views
5. **Log sensitive actions** - Use audit log for security events
6. **Use HTTPS in production** - Set `SESSION_SECURE=true` in `.env`

---

## Environment Configuration

Add to your `.env` file:

```env
# Session Security (for production)
SESSION_SECURE=true
SESSION_HTTPONLY=true
SESSION_LIFETIME=7200

# App Environment
APP_ENV=production
APP_DEBUG=false
```

---

## Example: Complete Login Flow

### 1. Login Form View (`app/view/auth/login.php`)

```php
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2>Login</h2>

                <?php $flash = get_flash(); ?>
                <?php if ($flash): ?>
                    <div class="alert alert-<?= $flash['type'] ?>">
                        <?= escape($flash['message']) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="/login">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" name="remember" value="1" class="form-check-input">
                        <label class="form-check-label">Remember Me</label>
                    </div>

                    <button type="submit" class="btn btn-primary">Login</button>
                    <a href="/forgot-password" class="btn btn-link">Forgot Password?</a>
                </form>

                <p class="mt-3">Don't have an account? <a href="/register">Register</a></p>
            </div>
        </div>
    </div>
</body>
</html>
```

### 2. Route Definition (`config/routes.php`)

```php
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
```

### 3. Controller Already Handles Everything!

The `AuthController@login` method handles:
- Input validation
- Authentication
- Remember me
- Error handling
- Redirects

---

## Testing Authentication

### Create Test User (via PHP CLI or seed script)

```php
<?php
require_once 'bootstrap/app.php';

use App\Config\Auth;
use Delight\Auth\Role;

// Create admin user
$userId = Auth::admin()->createUser(
    'admin@example.com',
    'password123',
    'admin'
);

// Add admin role
Auth::addRole($userId, Role::ADMIN);

echo "Admin user created with ID: {$userId}\n";
```

---

## Troubleshooting

### "Class 'Delight\Auth\Auth' not found"

**Solution:** Run `composer install` to install dependencies.

### Database tables don't exist

**Solution:** Import the Auth SQL schema:
```bash
mysql -u root -p emas < vendor/delight-im/auth/Database/MySQL.sql
```

### "Too many requests" error

**Solution:** Clear throttling table or wait. This is rate limiting protection.

```sql
DELETE FROM users_throttling WHERE expires < UNIX_TIMESTAMP();
```

### User can't login after registration

**Solution:** User email must be verified. Either:
1. Check `users` table and set `verified = 1`
2. Use `Auth::admin()->createUser()` which auto-verifies

---

## Summary

Your Kedai Emas application now has:

✅ **Secure Authentication** - Industry-standard Delight-IM Auth
✅ **User Management** - Full CRUD with role-based access
✅ **Role-Based Access Control** - Admin, Staff, Agent, Customer roles
✅ **Password Management** - Change password, forgot password, reset
✅ **Email Verification** - Secure account verification
✅ **Remember Me** - Persistent login for 30 days
✅ **Rate Limiting** - Protection against brute force attacks
✅ **Admin Interface** - Complete user management panel
✅ **Audit Logging** - Track security events
✅ **Session Security** - Secure session handling

You're now ready to build a secure, professional authentication system! 🔐
