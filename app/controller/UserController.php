<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\User;
use App\Config\Auth;
use Delight\Auth\Role;

/**
 * User Controller
 *
 * Handles user management with Delight-IM Auth package
 */
class UserController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Display all users
     */
    public function index(): void
    {
        $this->requireAuth();
        $this->requireRole('Admin');

        $users = $this->userModel->getAll();
        $statistics = $this->userModel->getStatistics();

        // Add role names to each user
        foreach ($users as &$user) {
            $user['roles'] = $this->getRoleNames($user['roles_mask']);
            $user['status_text'] = $this->getStatusText($user['status']);
        }

        $this->view('user/index', [
            'users' => $users,
            'statistics' => $statistics
        ]);
    }

    /**
     * Show single user details
     */
    public function show(string $id): void
    {
        $this->requireAuth();

        $userId = filter_var($id, FILTER_VALIDATE_INT);

        if (!$userId) {
            $this->flash('error', 'Invalid user ID');
            $this->redirect('/users');
            return;
        }

        // Check permission: admin can view all, users can view themselves
        if (!$this->hasRole('Admin') && Auth::getUserId() !== $userId) {
            $this->error(403, 'You do not have permission to view this user');
            return;
        }

        $user = $this->userModel->getUserWithRole($userId);

        if (!$user) {
            $this->error(404, 'User not found');
            return;
        }

        $user['status_text'] = $this->getStatusText($user['status']);

        $this->view('user/show', [
            'user' => $user
        ]);
    }

    /**
     * Show create user form
     */
    public function createForm(): void
    {
        $this->requireAuth();
        $this->requireRole('Admin');

        $this->view('user/create', [
            'roles' => $this->getAvailableRoles()
        ]);
    }

    /**
     * Create a new user
     */
    public function create(): void
    {
        $this->requireAuth();
        $this->requireRole('Admin');

        if (!$this->isPost()) {
            $this->redirect('/users/create');
            return;
        }

        // Validate required fields
        $required = ['email', 'password', 'username'];
        $missing = $this->validateRequired($required);

        if (!empty($missing)) {
            $this->flash('error', 'Missing required fields: ' . implode(', ', $missing));
            $this->redirect('/users/create');
            return;
        }

        $email = $this->sanitize($this->post('email'));
        $password = $this->post('password');
        $username = $this->sanitize($this->post('username'));
        $roles = $this->post('roles', []);

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flash('error', 'Invalid email address');
            $this->redirect('/users/create');
            return;
        }

        // Validate password strength
        if (strlen($password) < 8) {
            $this->flash('error', 'Password must be at least 8 characters');
            $this->redirect('/users/create');
            return;
        }

        try {
            // Register user with auto-verification
            $userId = Auth::admin()->createUser($email, $password, $username);

            // Add roles
            if (is_array($roles) && !empty($roles)) {
                foreach ($roles as $role) {
                    Auth::addRole($userId, (int)$role);
                }
            }

            $this->flash('success', 'User created successfully!');
            $this->redirect('/users/' . $userId);
        } catch (\Exception $e) {
            $this->flash('error', 'Failed to create user: ' . $e->getMessage());
            $this->redirect('/users/create');
        }
    }

    /**
     * Show edit user form
     */
    public function editForm(string $id): void
    {
        $this->requireAuth();

        $userId = filter_var($id, FILTER_VALIDATE_INT);

        if (!$userId) {
            $this->flash('error', 'Invalid user ID');
            $this->redirect('/users');
            return;
        }

        // Check permission
        if (!$this->hasRole('Admin') && Auth::getUserId() !== $userId) {
            $this->error(403, 'You do not have permission to edit this user');
            return;
        }

        $user = $this->userModel->getUserWithRole($userId);

        if (!$user) {
            $this->flash('error', 'User not found');
            $this->redirect('/users');
            return;
        }

        $this->view('user/edit', [
            'user' => $user,
            'roles' => $this->getAvailableRoles()
        ]);
    }

    /**
     * Update user
     */
    public function update(string $id): void
    {
        $this->requireAuth();

        if (!$this->isPost()) {
            $this->redirect('/users');
            return;
        }

        $userId = filter_var($id, FILTER_VALIDATE_INT);

        if (!$userId) {
            $this->flash('error', 'Invalid user ID');
            $this->redirect('/users');
            return;
        }

        // Check permission
        if (!$this->hasRole('Admin') && Auth::getUserId() !== $userId) {
            $this->error(403, 'You do not have permission to edit this user');
            return;
        }

        $username = $this->sanitize($this->post('username'));
        $email = $this->sanitize($this->post('email'));

        try {
            // Update username and email using admin interface
            if ($this->hasRole('Admin')) {
                // Admin can update everything
                Auth::admin()->changeEmailForUserById($userId, $email);
            }

            // Update username in database directly
            $this->userModel->updateById($userId, [
                'username' => $username
            ]);

            // Update roles if admin
            if ($this->hasRole('Admin')) {
                $roles = $this->post('roles', []);

                // Get current user data
                $user = $this->userModel->getById($userId);

                // Clear existing roles and set new ones
                $currentRolesMask = $user['roles_mask'];

                // Calculate new roles mask
                $newRolesMask = 0;
                if (is_array($roles)) {
                    foreach ($roles as $role) {
                        $newRolesMask |= (int)$role;
                    }
                }

                // Update roles_mask directly
                $this->userModel->updateById($userId, [
                    'roles_mask' => $newRolesMask
                ]);
            }

            $this->flash('success', 'User updated successfully!');
            $this->redirect('/users/' . $userId);
        } catch (\Exception $e) {
            $this->flash('error', 'Failed to update user: ' . $e->getMessage());
            $this->redirect('/users/' . $userId . '/edit');
        }
    }

    /**
     * Delete user
     */
    public function delete(string $id): void
    {
        $this->requireAuth();
        $this->requireRole('Admin');

        if (!$this->isPost()) {
            $this->flash('error', 'Invalid request method');
            $this->redirect('/users');
            return;
        }

        $userId = filter_var($id, FILTER_VALIDATE_INT);

        if (!$userId) {
            $this->flash('error', 'Invalid user ID');
            $this->redirect('/users');
            return;
        }

        // Prevent deleting self
        if (Auth::getUserId() === $userId) {
            $this->flash('error', 'You cannot delete your own account');
            $this->redirect('/users');
            return;
        }

        try {
            Auth::deleteUser($userId);
            $this->flash('success', 'User deleted successfully!');
        } catch (\Exception $e) {
            $this->flash('error', 'Failed to delete user: ' . $e->getMessage());
        }

        $this->redirect('/users');
    }

    /**
     * Search users
     */
    public function search(): void
    {
        $this->requireAuth();
        $this->requireRole('Admin');

        $searchTerm = $this->sanitize($this->get('q', ''));

        if (empty($searchTerm)) {
            $this->redirect('/users');
            return;
        }

        $users = $this->userModel->search($searchTerm);
        $statistics = $this->userModel->getStatistics();

        foreach ($users as &$user) {
            $user['roles'] = $this->getRoleNames($user['roles_mask']);
            $user['status_text'] = $this->getStatusText($user['status']);
        }

        $this->view('user/index', [
            'users' => $users,
            'statistics' => $statistics,
            'searchTerm' => $searchTerm
        ]);
    }

    /**
     * Change password form
     */
    public function changePasswordForm(string $id): void
    {
        $this->requireAuth();

        $userId = filter_var($id, FILTER_VALIDATE_INT);

        if (!$userId) {
            $this->flash('error', 'Invalid user ID');
            $this->redirect('/users');
            return;
        }

        // Check permission
        if (!$this->hasRole('Admin') && Auth::getUserId() !== $userId) {
            $this->error(403, 'You do not have permission to change this password');
            return;
        }

        $user = $this->userModel->getById($userId);

        if (!$user) {
            $this->flash('error', 'User not found');
            $this->redirect('/users');
            return;
        }

        $this->view('user/change-password', [
            'user' => $user
        ]);
    }

    /**
     * Change password
     */
    public function changePassword(string $id): void
    {
        $this->requireAuth();

        if (!$this->isPost()) {
            $this->redirect('/users');
            return;
        }

        $userId = filter_var($id, FILTER_VALIDATE_INT);

        if (!$userId) {
            $this->flash('error', 'Invalid user ID');
            $this->redirect('/users');
            return;
        }

        // Check permission
        $isAdmin = $this->hasRole('Admin');
        $isSelf = Auth::getUserId() === $userId;

        if (!$isAdmin && !$isSelf) {
            $this->error(403, 'You do not have permission to change this password');
            return;
        }

        $newPassword = $this->post('new_password');
        $confirmPassword = $this->post('confirm_password');

        // Validate passwords
        if (empty($newPassword) || empty($confirmPassword)) {
            $this->flash('error', 'Both password fields are required');
            $this->redirect('/users/' . $userId . '/change-password');
            return;
        }

        if ($newPassword !== $confirmPassword) {
            $this->flash('error', 'Passwords do not match');
            $this->redirect('/users/' . $userId . '/change-password');
            return;
        }

        if (strlen($newPassword) < 8) {
            $this->flash('error', 'Password must be at least 8 characters');
            $this->redirect('/users/' . $userId . '/change-password');
            return;
        }

        try {
            if ($isAdmin && !$isSelf) {
                // Admin changing another user's password
                Auth::admin()->changePasswordForUserById($userId, $newPassword);
            } else {
                // User changing their own password
                $oldPassword = $this->post('old_password');
                if (empty($oldPassword)) {
                    $this->flash('error', 'Current password is required');
                    $this->redirect('/users/' . $userId . '/change-password');
                    return;
                }
                Auth::changePassword($oldPassword, $newPassword);
            }

            $this->flash('success', 'Password changed successfully!');
            $this->redirect('/users/' . $userId);
        } catch (\Exception $e) {
            $this->flash('error', 'Failed to change password: ' . $e->getMessage());
            $this->redirect('/users/' . $userId . '/change-password');
        }
    }

    /**
     * Toggle user verification status (admin only)
     */
    public function toggleVerification(string $id): void
    {
        $this->requireAuth();
        $this->requireRole('Admin');

        if (!$this->isPost()) {
            $this->json(['error' => true, 'message' => 'Invalid request'], 400);
            return;
        }

        $userId = filter_var($id, FILTER_VALIDATE_INT);

        if (!$userId) {
            $this->json(['error' => true, 'message' => 'Invalid user ID'], 400);
            return;
        }

        $user = $this->userModel->getById($userId);

        if (!$user) {
            $this->json(['error' => true, 'message' => 'User not found'], 404);
            return;
        }

        $newStatus = $user['verified'] === 1 ? 0 : 1;

        $success = $this->userModel->updateById($userId, ['verified' => $newStatus]);

        if ($success) {
            $this->json([
                'success' => true,
                'verified' => $newStatus,
                'message' => $newStatus === 1 ? 'User verified' : 'User unverified'
            ]);
        } else {
            $this->json(['error' => true, 'message' => 'Failed to update verification status'], 500);
        }
    }

    /**
     * Get available roles
     */
    private function getAvailableRoles(): array
    {
        return [
            Role::ADMIN => 'Admin',
            Role::MODERATOR => 'Staff',
            Role::SUBSCRIBER => 'Agent',
            Role::DEVELOPER => 'Customer',
        ];
    }

    /**
     * Get role names from roles_mask
     */
    private function getRoleNames(int $rolesMask): array
    {
        $roles = [];
        $availableRoles = $this->getAvailableRoles();

        foreach ($availableRoles as $mask => $name) {
            if (($rolesMask & $mask) === $mask) {
                $roles[] = $name;
            }
        }

        return $roles;
    }

    /**
     * Get status text
     */
    private function getStatusText(int $status): string
    {
        return match ($status) {
            0 => 'Normal',
            1 => 'Archived',
            2 => 'Banned',
            3 => 'Locked',
            4 => 'Pending Review',
            5 => 'Suspended',
            default => 'Unknown'
        };
    }
}
