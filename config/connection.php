<?php
declare(strict_types=1);

class Database
{
    private static ?\PDO $pdo = null;

    public static function getConfig(): array
    {
        return [
            'host' => getenv('DB_HOST') ?: '127.0.0.1',
            'port' => (int) (getenv('DB_PORT') ?: 3306),
            'name' => getenv('DB_NAME') ?: 'emas',
            'user' => getenv('DB_USER') ?: 'root',
            'pass' => getenv('DB_PASS') ?: '',
            'charset' => 'utf8mb4',
        ];
    }

    public static function getConnection(): \PDO
    {
        if (self::$pdo instanceof \PDO) {
            return self::$pdo;
        }

        $cfg = self::getConfig();
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $cfg['host'], $cfg['port'], $cfg['name'], $cfg['charset']);

        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ];

        self::$pdo = new \PDO($dsn, $cfg['user'], $cfg['pass'], $options);
        return self::$pdo;
    }

    public static function ensureDatabaseExists(): void
    {
        $cfg = self::getConfig();
        $dsnNoDb = sprintf('mysql:host=%s;port=%d;charset=%s', $cfg['host'], $cfg['port'], $cfg['charset']);

        $pdo = new \PDO($dsnNoDb, $cfg['user'], $cfg['pass'], [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        ]);

        $dbName = preg_replace('/[^a-zA-Z0-9_]/', '', $cfg['name']);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }

    public static function runMigrations(string $sqlFilePath): void
    {
        if (!is_file($sqlFilePath)) {
            throw new \RuntimeException("SQL file not found: {$sqlFilePath}");
        }

        $sql = file_get_contents($sqlFilePath);
        if ($sql === false) {
            throw new \RuntimeException("Failed to read SQL file: {$sqlFilePath}");
        }

        $pdo = self::getConnection();
        $pdo->beginTransaction();
        try {
            $statements = self::splitSqlStatements($sql);
            foreach ($statements as $statement) {
                if ($statement === '') {
                    continue;
                }
                $pdo->exec($statement);
            }
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    private static function splitSqlStatements(string $sql): array
    {
        $clean = preg_replace('/\/\*.*?\*\//s', '', $sql) ?? $sql; // remove /* */ comments
        $lines = explode("\n", $clean);
        $buffer = '';
        $statements = [];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '--')) {
                continue;
            }
            $buffer .= $line . "\n";
            if (preg_match('/;\s*$/', $trimmed)) {
                $statements[] = trim($buffer);
                $buffer = '';
            }
        }
        if (trim($buffer) !== '') {
            $statements[] = trim($buffer);
        }
        return $statements;
    }

    public static function resetConnection(): void
    {
        self::$pdo = null;
    }
}

if (PHP_SAPI === 'cli') {
    $args = $argv ?? [];
    $flags = array_slice($args, 1);

    $doCreateDb = in_array('--create-db', $flags, true) || in_array('--setup', $flags, true);
    $doMigrate = in_array('--migrate', $flags, true) || in_array('--setup', $flags, true);

    if ($doCreateDb) {
        Database::ensureDatabaseExists();
    }

    if ($doMigrate) {
        $sqlPath = realpath(__DIR__ . '/../draw-sql.sql') ?: (__DIR__ . '/../draw-sql.sql');
        // Reconnect to the target database after creation
        Database::resetConnection();
        Database::getConnection();
        Database::runMigrations($sqlPath);
    }
}


