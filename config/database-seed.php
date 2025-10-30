<?php
declare(strict_types=1);

require_once __DIR__ . '/connection.php';

// Ensure DB exists and connect
Database::ensureDatabaseExists();
$pdo = Database::getConnection();

$pdo->beginTransaction();
try {
    // Ensure default profit margin (needed for FK in users)
    $profitMarginId = null;
    $stmt = $pdo->prepare('SELECT id FROM profit_margin WHERE name = :name LIMIT 1');
    $stmt->execute([':name' => 'Default']);
    $row = $stmt->fetch();
    if ($row) {
        $profitMarginId = (int) $row['id'];
    } else {
        $pdo->prepare('INSERT INTO profit_margin (name, rate) VALUES (:name, :rate)')
            ->execute([':name' => 'Default', ':rate' => 0]);
        $profitMarginId = (int) $pdo->lastInsertId();
    }

    // Seed roles
    $roleNames = ['admin', 'agent', 'staff', 'customer'];
    $roleNameToId = [];
    foreach ($roleNames as $roleName) {
        $stmt = $pdo->prepare('SELECT id FROM roles WHERE name = :name LIMIT 1');
        $stmt->execute([':name' => $roleName]);
        $row = $stmt->fetch();
        if ($row) {
            $roleNameToId[$roleName] = (int) $row['id'];
        } else {
            $pdo->prepare('INSERT INTO roles (name) VALUES (:name)')->execute([':name' => $roleName]);
            $roleNameToId[$roleName] = (int) $pdo->lastInsertId();
        }
    }

    // Create default admin user if not exists
    $defaultAdminEmail = 'admin@example.com';
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $defaultAdminEmail]);
    $exists = $stmt->fetch();
    if (!$exists) {
        $hashed = password_hash('password', PASSWORD_BCRYPT);
        $insert = $pdo->prepare(
            'INSERT INTO users (first_name, last_time, email, passwords, is_verified, roles_id, profit_rate_id)
             VALUES (:first_name, :last_time, :email, :passwords, :is_verified, :roles_id, :profit_rate_id)'
        );
        $insert->execute([
            ':first_name' => 'Admin',
            ':last_time' => 'User', // matches current schema field name
            ':email' => $defaultAdminEmail,
            ':passwords' => $hashed,
            ':is_verified' => 1,
            ':roles_id' => $roleNameToId['admin'],
            ':profit_rate_id' => $profitMarginId,
        ]);
    }

    $pdo->commit();
    if (PHP_SAPI === 'cli') {
        fwrite(STDOUT, "Database seed completed.\n");
    }
} catch (Throwable $e) {
    $pdo->rollBack();
    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, 'Seed failed: ' . $e->getMessage() . "\n");
    }
    throw $e;
}


