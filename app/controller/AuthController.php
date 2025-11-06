<?php
declare(strict_types=1);

namespace App\Controller;

use App\Config\Auth;

/**
 * Auth Controller
 *
 * Handles authentication (login, register, logout) using Delight-IM Auth
 */
class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin(): void
    {
        // Redirect if already logged in
        if (Auth::isLoggedIn()) {
            $this->redirect('/dashboard');
            return;
        }

        $this->view('auth/login', [], null);
    }

    /**
     * Handle login
     */
    public function login(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/login');
            return;
        }

        // Validate required fields
        $required = ['email', 'password'];
        $missing = $this->validateRequired($required);

        if (!empty($missing)) {
            $this->flash('error', 'Email and password are required');
            $this->redirect('/login');
            return;
        }

        $email = $this->sanitize($this->post('email'));
        $password = $this->post('password');
        $remember = $this->post('remember') === '1';

        try {
            // Remember for 30 days if checked
            $rememberDuration = $remember ? (60 * 60 * 24 * 30) : 0;

            Auth::login($email, $password, $rememberDuration);

            $this->flash('success', 'Welcome back!');
            $this->redirect('/dashboard');
        } catch (\Exception $e) {
            $this->flash('error', $e->getMessage());
            $this->redirect('/login');
        }
    }

    /**
     * Show registration form
     */
    public function showRegister(): void
    {
        // Redirect if already logged in
        if (Auth::isLoggedIn()) {
            $this->redirect('/dashboard');
            return;
        }

        $this->view('auth/register', [], null);
    }

    /**
     * Handle registration
     */
    public function register(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/register');
            return;
        }

        // Validate required fields
        $required = ['email', 'password', 'username'];
        $missing = $this->validateRequired($required);

        if (!empty($missing)) {
            $this->flash('error', 'All fields are required');
            $this->redirect('/register');
            return;
        }

        $email = $this->sanitize($this->post('email'));
        $password = $this->post('password');
        $confirmPassword = $this->post('confirm_password');
        $username = $this->sanitize($this->post('username'));

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flash('error', 'Invalid email address');
            $this->redirect('/register');
            return;
        }

        // Validate password
        if (strlen($password) < 8) {
            $this->flash('error', 'Password must be at least 8 characters');
            $this->redirect('/register');
            return;
        }

        // Check password confirmation
        if ($password !== $confirmPassword) {
            $this->flash('error', 'Passwords do not match');
            $this->redirect('/register');
            return;
        }

        try {
            $userId = Auth::register($email, $password, $username);

            $this->flash('success', 'Registration successful! Please check your email to verify your account.');
            $this->redirect('/login');
        } catch (\Exception $e) {
            $this->flash('error', $e->getMessage());
            $this->redirect('/register');
        }
    }

    /**
     * Logout
     */
    public function logout(): void
    {
        Auth::logout();
        $this->flash('success', 'You have been logged out');
        $this->redirect('/login');
    }

    /**
     * Show forgot password form
     */
    public function showForgotPassword(): void
    {
        if (Auth::isLoggedIn()) {
            $this->redirect('/dashboard');
            return;
        }

        $this->view('auth/forgot-password', [], null);
    }

    /**
     * Handle forgot password
     */
    public function forgotPassword(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/forgot-password');
            return;
        }

        $email = $this->sanitize($this->post('email'));

        if (empty($email)) {
            $this->flash('error', 'Email is required');
            $this->redirect('/forgot-password');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flash('error', 'Invalid email address');
            $this->redirect('/forgot-password');
            return;
        }

        try {
            Auth::forgotPassword($email, function ($selector, $token) use ($email) {
                // In production, send email here
                // For now, we'll just log it
                error_log("Password reset for {$email}: selector={$selector}, token={$token}");

                // You can use this URL for password reset
                $resetUrl = base_url("/reset-password?selector={$selector}&token={$token}");
                error_log("Reset URL: {$resetUrl}");
            });

            $this->flash('success', 'Password reset instructions have been sent to your email');
            $this->redirect('/login');
        } catch (\Exception $e) {
            $this->flash('error', $e->getMessage());
            $this->redirect('/forgot-password');
        }
    }

    /**
     * Show reset password form
     */
    public function showResetPassword(): void
    {
        if (Auth::isLoggedIn()) {
            $this->redirect('/dashboard');
            return;
        }

        $selector = $this->get('selector', '');
        $token = $this->get('token', '');

        if (empty($selector) || empty($token)) {
            $this->flash('error', 'Invalid reset link');
            $this->redirect('/login');
            return;
        }

        $this->view('auth/reset-password', [
            'selector' => $selector,
            'token' => $token
        ], null);
    }

    /**
     * Handle reset password
     */
    public function resetPassword(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/login');
            return;
        }

        $selector = $this->post('selector', '');
        $token = $this->post('token', '');
        $password = $this->post('password');
        $confirmPassword = $this->post('confirm_password');

        if (empty($selector) || empty($token)) {
            $this->flash('error', 'Invalid reset link');
            $this->redirect('/login');
            return;
        }

        if (empty($password) || empty($confirmPassword)) {
            $this->flash('error', 'Both password fields are required');
            $this->redirect("/reset-password?selector={$selector}&token={$token}");
            return;
        }

        if ($password !== $confirmPassword) {
            $this->flash('error', 'Passwords do not match');
            $this->redirect("/reset-password?selector={$selector}&token={$token}");
            return;
        }

        if (strlen($password) < 8) {
            $this->flash('error', 'Password must be at least 8 characters');
            $this->redirect("/reset-password?selector={$selector}&token={$token}");
            return;
        }

        try {
            Auth::resetPassword($selector, $token, $password);

            $this->flash('success', 'Password reset successful! You can now login with your new password.');
            $this->redirect('/login');
        } catch (\Exception $e) {
            $this->flash('error', $e->getMessage());
            $this->redirect("/reset-password?selector={$selector}&token={$token}");
        }
    }
}
