<?php
declare(strict_types=1);

namespace App\Model;

/**
 * User Model
 *
 * Handles user data operations
 * Works alongside Delight Auth package
 */
class User extends Model
{
    protected string $table = 'users';
    protected string $primaryKey = 'id';

    /**
     * Get all users with their details
     * @return array
     */
    public function getAll(): array
    {
        return $this->findAll(['*'], 'registered DESC');
    }

    /**
     * Get user by ID
     * @param int $id
     * @return array|null
     */
    public function getById(int $id): ?array
    {
        return $this->findById($id);
    }

    /**
     * Find user by email
     * @param string $email
     * @return array|null
     */
    public function findByEmail(string $email): ?array
    {
        return $this->findOne(['email' => $email]);
    }

    /**
     * Find user by username
     * @param string $username
     * @return array|null
     */
    public function findByUsername(string $username): ?array
    {
        return $this->findOne(['username' => $username]);
    }

    /**
     * Get verified users only
     * @return array
     */
    public function getVerifiedUsers(): array
    {
        return $this->findWhere(['verified' => 1], ['*'], 'registered DESC');
    }

    /**
     * Get unverified users
     * @return array
     */
    public function getUnverifiedUsers(): array
    {
        return $this->findWhere(['verified' => 0], ['*'], 'registered DESC');
    }

    /**
     * Get users by status
     * @param int $status
     * @return array
     */
    public function getByStatus(int $status): array
    {
        return $this->findWhere(['status' => $status], ['*'], 'registered DESC');
    }

    /**
     * Search users by email or username
     * @param string $searchTerm
     * @return array
     */
    public function search(string $searchTerm): array
    {
        $sql = "
            SELECT * FROM {$this->table}
            WHERE email LIKE :search OR username LIKE :search
            ORDER BY registered DESC
        ";

        return $this->query($sql, ['search' => "%{$searchTerm}%"]);
    }

    /**
     * Get total user count
     * @return int
     */
    public function getTotalCount(): int
    {
        return $this->count();
    }

    /**
     * Get verified user count
     * @return int
     */
    public function getVerifiedCount(): int
    {
        return $this->count(['verified' => 1]);
    }

    /**
     * Get recently registered users
     * @param int $limit
     * @return array
     */
    public function getRecentUsers(int $limit = 10): array
    {
        return $this->findWhere([], ['*'], 'registered DESC', $limit);
    }

    /**
     * Update user's last login timestamp
     * @param int $userId
     * @return bool
     */
    public function updateLastLogin(int $userId): bool
    {
        return $this->updateById($userId, ['last_login' => time()]) > 0;
    }

    /**
     * Check if email exists
     * @param string $email
     * @return bool
     */
    public function emailExists(string $email): bool
    {
        return $this->findByEmail($email) !== null;
    }

    /**
     * Check if username exists
     * @param string $username
     * @return bool
     */
    public function usernameExists(string $username): bool
    {
        return $this->findByUsername($username) !== null;
    }

    /**
     * Get user with role information
     * @param int $userId
     * @return array|null
     */
    public function getUserWithRole(int $userId): ?array
    {
        $user = $this->findById($userId);

        if (!$user) {
            return null;
        }

        // Decode roles_mask to get role names
        $user['roles'] = $this->getRoleNames($user['roles_mask']);

        return $user;
    }

    /**
     * Get role names from roles_mask
     * @param int $rolesMask
     * @return array
     */
    private function getRoleNames(int $rolesMask): array
    {
        $roles = [];

        // Delight Auth role constants (you can customize these)
        $roleMap = [
            1 => 'Admin',
            2 => 'Staff',
            4 => 'Agent',
            8 => 'Customer',
        ];

        foreach ($roleMap as $mask => $name) {
            if (($rolesMask & $mask) === $mask) {
                $roles[] = $name;
            }
        }

        return $roles;
    }

    /**
     * Get users by role
     * @param int $roleMask
     * @return array
     */
    public function getUsersByRole(int $roleMask): array
    {
        $sql = "
            SELECT * FROM {$this->table}
            WHERE (roles_mask & :role_mask) = :role_mask
            ORDER BY registered DESC
        ";

        return $this->query($sql, ['role_mask' => $roleMask]);
    }

    /**
     * Get user statistics
     * @return array
     */
    public function getStatistics(): array
    {
        return [
            'total' => $this->getTotalCount(),
            'verified' => $this->getVerifiedCount(),
            'unverified' => $this->count(['verified' => 0]),
            'active' => $this->count(['status' => 0]),
        ];
    }
}
