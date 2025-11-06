<?php
declare(strict_types=1);

namespace App\Config;

use Delight\Auth\Auth as DelightAuth;


/**
 * Auth Helper Class
 *
 * Wrapper for Delight-IM Auth package
 */
class Auth
{
    private static ?DelightAuth $instance = null;

    /**
     * Get Auth instance (singleton)
     * @return DelightAuth
     */
    public static function getInstance(): DelightAuth
    {
        if (self::$instance === null) {
            $db = \Database::getConnection();
            self::$instance = new DelightAuth($db);
        }

        return self::$instance;
    }

    /**
     * Register a new user
     * @param string $email
     * @param string $password
     * @param string|null $username
     * @return int User ID
     * @throws \Exception
     */
    public static function register(string $email, string $password, ?string $username = null): int
    {
        $auth = self::getInstance();

        try {
            $userId = $auth->register($email, $password, $username);
            return $userId;
        } catch (\Delight\Auth\InvalidEmailException $e) {
            throw new \Exception('Invalid email address');
        } catch (\Delight\Auth\InvalidPasswordException $e) {
            throw new \Exception('Invalid password (must be at least 3 characters)');
        } catch (\Delight\Auth\UserAlreadyExistsException $e) {
            throw new \Exception('User with this email already exists');
        } catch (\Delight\Auth\TooManyRequestsException $e) {
            throw new \Exception('Too many registration attempts. Please try again later.');
        }
    }

    /**
     * Register and automatically verify user
     * @param string $email
     * @param string $password
     * @param string|null $username
     * @return int User ID
     * @throws \Exception
     */
    public static function registerAndVerify(string $email, string $password, ?string $username = null): int
    {
        $auth = self::getInstance();

        try {
            $userId = $auth->registerWithUniqueUsername($email, $password, $username);
            // Auto-verify for admin panel
            $auth->admin()->createUser($email, $password, $username);
            return $userId;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Login user
     * @param string $email
     * @param string $password
     * @param int $rememberDuration Remember duration in seconds (0 = don't remember)
     * @return bool
     * @throws \Exception
     */
    public static function login(string $email, string $password, int $rememberDuration = 0): bool
    {
        $auth = self::getInstance();

        try {
            if ($rememberDuration > 0) {
                $auth->login($email, $password, $rememberDuration);
            } else {
                $auth->login($email, $password);
            }
            return true;
        } catch (\Delight\Auth\InvalidEmailException $e) {
            throw new \Exception('Invalid email address');
        } catch (\Delight\Auth\InvalidPasswordException $e) {
            throw new \Exception('Invalid password');
        } catch (\Delight\Auth\EmailNotVerifiedException $e) {
            throw new \Exception('Email not verified. Please check your inbox.');
        } catch (\Delight\Auth\TooManyRequestsException $e) {
            throw new \Exception('Too many login attempts. Please try again later.');
        }
    }

    /**
     * Logout current user
     */
    public static function logout(): void
    {
        $auth = self::getInstance();
        $auth->logOut();
    }

    /**
     * Check if user is logged in
     * @return bool
     */
    public static function isLoggedIn(): bool
    {
        $auth = self::getInstance();
        return $auth->isLoggedIn();
    }

    /**
     * Get current user ID
     * @return int|null
     */
    public static function getUserId(): ?int
    {
        $auth = self::getInstance();
        return $auth->getUserId();
    }

    /**
     * Get current user email
     * @return string|null
     */
    public static function getEmail(): ?string
    {
        $auth = self::getInstance();
        return $auth->getEmail();
    }

    /**
     * Get current username
     * @return string|null
     */
    public static function getUsername(): ?string
    {
        $auth = self::getInstance();
        return $auth->getUsername();
    }

    /**
     * Check if user has role
     * @param int $role Role constant
     * @return bool
     */
    public static function hasRole(int $role): bool
    {
        $auth = self::getInstance();
        return $auth->hasRole($role);
    }

    /**
     * Check if user has any of the roles
     * @param array $roles Array of role constants
     * @return bool
     */
    public static function hasAnyRole(array $roles): bool
    {
        $auth = self::getInstance();
        return $auth->hasAnyRole(...$roles);
    }

    /**
     * Check if user has all of the roles
     * @param array $roles Array of role constants
     * @return bool
     */
    public static function hasAllRoles(array $roles): bool
    {
        $auth = self::getInstance();
        return $auth->hasAllRoles(...$roles);
    }

    /**
     * Add role to user
     * @param int $userId
     * @param int $role
     * @throws \Exception
     */
    public static function addRole(int $userId, int $role): void
    {
        $auth = self::getInstance();
        try {
            $auth->admin()->addRoleForUserById($userId, $role);
        } catch (\Exception $e) {
            throw new \Exception('Failed to add role: ' . $e->getMessage());
        }
    }

    /**
     * Remove role from user
     * @param int $userId
     * @param int $role
     * @throws \Exception
     */
    public static function removeRole(int $userId, int $role): void
    {
        $auth = self::getInstance();
        try {
            $auth->admin()->removeRoleForUserById($userId, $role);
        } catch (\Exception $e) {
            throw new \Exception('Failed to remove role: ' . $e->getMessage());
        }
    }

    /**
     * Change user password
     * @param string $oldPassword
     * @param string $newPassword
     * @throws \Exception
     */
    public static function changePassword(string $oldPassword, string $newPassword): void
    {
        $auth = self::getInstance();
        try {
            $auth->changePassword($oldPassword, $newPassword);
        } catch (\Delight\Auth\NotLoggedInException $e) {
            throw new \Exception('Not logged in');
        } catch (\Delight\Auth\InvalidPasswordException $e) {
            throw new \Exception('Invalid password (must be at least 3 characters)');
        } catch (\Delight\Auth\TooManyRequestsException $e) {
            throw new \Exception('Too many requests. Please try again later.');
        }
    }

    /**
     * Request password reset
     * @param string $email
     * @param callable $callback Callback to send reset email
     * @throws \Exception
     */
    public static function forgotPassword(string $email, callable $callback): void
    {
        $auth = self::getInstance();
        try {
            $auth->forgotPassword($email, $callback);
        } catch (\Delight\Auth\InvalidEmailException $e) {
            throw new \Exception('Invalid email address');
        } catch (\Delight\Auth\EmailNotVerifiedException $e) {
            throw new \Exception('Email not verified');
        } catch (\Delight\Auth\TooManyRequestsException $e) {
            throw new \Exception('Too many requests. Please try again later.');
        }
    }

    /**
     * Reset password with token
     * @param string $selector
     * @param string $token
     * @param string $newPassword
     * @throws \Exception
     */
    public static function resetPassword(string $selector, string $token, string $newPassword): void
    {
        $auth = self::getInstance();
        try {
            $auth->resetPassword($selector, $token, $newPassword);
        } catch (\Delight\Auth\InvalidSelectorTokenPairException $e) {
            throw new \Exception('Invalid token');
        } catch (\Delight\Auth\TokenExpiredException $e) {
            throw new \Exception('Token has expired');
        } catch (\Delight\Auth\InvalidPasswordException $e) {
            throw new \Exception('Invalid password (must be at least 3 characters)');
        } catch (\Delight\Auth\TooManyRequestsException $e) {
            throw new \Exception('Too many requests. Please try again later.');
        }
    }

    /**
     * Get admin interface
     * @return \Delight\Auth\Administration
     */
    public static function admin(): \Delight\Auth\Administration
    {
        $auth = self::getInstance();
        return $auth->admin();
    }

    /**
     * Delete user by ID
     * @param int $userId
     * @throws \Exception
     */
    public static function deleteUser(int $userId): void
    {
        $auth = self::getInstance();
        try {
            $auth->admin()->deleteUserById($userId);
        } catch (\Exception $e) {
            throw new \Exception('Failed to delete user: ' . $e->getMessage());
        }
    }
}
