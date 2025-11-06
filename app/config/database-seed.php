<?php
declare(strict_types=1);

require_once __DIR__ . '/connection.php';

Database::ensureDatabaseExists();
$pdo = Database::getConnection();

$pdo->beginTransaction();
try {

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

    $margins = [
        ['name' => 'Agent', 'rate' => 10],
        ['name' => 'Staff', 'rate' => 15],
        ['name' => 'Customer', 'rate' => 20],
    ];
    $marginNameToId = [];
    foreach ($margins as $m) {
        $stmt = $pdo->prepare('SELECT id FROM profit_margin WHERE name = :name LIMIT 1');
        $stmt->execute([':name' => $m['name']]);
        $row = $stmt->fetch();
        if ($row) {
            $marginNameToId[$m['name']] = (int) $row['id'];
            // Optionally update rate if different
            $pdo->prepare('UPDATE profit_margin SET rate = :rate WHERE id = :id')->execute([
                ':rate' => $m['rate'],
                ':id' => $row['id'],
            ]);
        } else {
            $pdo->prepare('INSERT INTO profit_margin (name, rate) VALUES (:name, :rate)')
                ->execute([':name' => $m['name'], ':rate' => $m['rate']]);
            $marginNameToId[$m['name']] = (int) $pdo->lastInsertId();
        }
    }

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
            ':last_time' => 'User', 
            ':email' => $defaultAdminEmail,
            ':passwords' => $hashed,
            ':is_verified' => 1,
            ':roles_id' => $roleNameToId['admin'],
            ':profit_rate_id' => $profitMarginId,
        ]);
    }

    // Create default Agent user
    $defaultAgentEmail = 'agent@example.com';
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $defaultAgentEmail]);
    $exists = $stmt->fetch();
    if (!$exists) {
        $hashed = password_hash('password', PASSWORD_BCRYPT);
        $insert = $pdo->prepare(
            'INSERT INTO users (first_name, last_time, email, passwords, is_verified, roles_id, profit_rate_id)
             VALUES (:first_name, :last_time, :email, :passwords, :is_verified, :roles_id, :profit_rate_id)'
        );
        $insert->execute([
            ':first_name' => 'Agent',
            ':last_time' => 'User',
            ':email' => $defaultAgentEmail,
            ':passwords' => $hashed,
            ':is_verified' => 1,
            ':roles_id' => $roleNameToId['agent'],
            ':profit_rate_id' => $marginNameToId['Agent'] ?? $profitMarginId,
        ]);
    }

    // Create default Staff user
    $defaultStaffEmail = 'staff@example.com';
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $defaultStaffEmail]);
    $exists = $stmt->fetch();
    if (!$exists) {
        $hashed = password_hash('password', PASSWORD_BCRYPT);
        $insert = $pdo->prepare(
            'INSERT INTO users (first_name, last_time, email, passwords, is_verified, roles_id, profit_rate_id)
             VALUES (:first_name, :last_time, :email, :passwords, :is_verified, :roles_id, :profit_rate_id)'
        );
        $insert->execute([
            ':first_name' => 'Staff',
            ':last_time' => 'User',
            ':email' => $defaultStaffEmail,
            ':passwords' => $hashed,
            ':is_verified' => 1,
            ':roles_id' => $roleNameToId['staff'],
            ':profit_rate_id' => $marginNameToId['Staff'] ?? $profitMarginId,
        ]);
    }

    // Create default Customer user
    $defaultCustomerEmail = 'customer@example.com';
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $defaultCustomerEmail]);
    $exists = $stmt->fetch();
    if (!$exists) {
        $hashed = password_hash('password', PASSWORD_BCRYPT);
        $insert = $pdo->prepare(
            'INSERT INTO users (first_name, last_time, email, passwords, is_verified, roles_id, profit_rate_id)
             VALUES (:first_name, :last_time, :email, :passwords, :is_verified, :roles_id, :profit_rate_id)'
        );
        $insert->execute([
            ':first_name' => 'Customer',
            ':last_time' => 'User',
            ':email' => $defaultCustomerEmail,
            ':passwords' => $hashed,
            ':is_verified' => 1,
            ':roles_id' => $roleNameToId['customer'],
            ':profit_rate_id' => $marginNameToId['Customer'] ?? $profitMarginId,
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


