<?php
declare(strict_types=1);

namespace App\Model;

use PDO;
use PDOException;

/**
 * Base Model Class
 *
 * Provides common database operations for all models
 */
abstract class Model
{
    protected PDO $db;
    protected string $table;
    protected string $primaryKey = 'id';

    /**
     * Constructor - initializes database connection
     */
    public function __construct()
    {
        $this->db = $this->getConnection();
    }

    /**
     * Get database connection from Database class
     */
    protected function getConnection(): PDO
    {
        require_once __DIR__ . '/../config/connection.php';
        return \Database::getConnection();
    }

    /**
     * Find all records
     *
     * @param array $columns Columns to select
     * @param string $orderBy Order by clause
     * @return array
     */
    public function findAll(array $columns = ['*'], string $orderBy = ''): array
    {
        $cols = implode(', ', $columns);
        $sql = "SELECT {$cols} FROM {$this->table}";

        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Find record by ID
     *
     * @param int $id Record ID
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Find records by condition
     *
     * @param array $conditions WHERE conditions as key-value pairs
     * @param array $columns Columns to select
     * @param string $orderBy Order by clause
     * @param int|null $limit Limit number of results
     * @return array
     */
    public function findWhere(array $conditions, array $columns = ['*'], string $orderBy = '', ?int $limit = null): array
    {
        $cols = implode(', ', $columns);
        $sql = "SELECT {$cols} FROM {$this->table}";

        if (!empty($conditions)) {
            $where = [];
            foreach (array_keys($conditions) as $key) {
                $where[] = "{$key} = :{$key}";
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }

        if ($limit !== null) {
            $sql .= " LIMIT {$limit}";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($conditions);

        return $stmt->fetchAll();
    }

    /**
     * Find one record by condition
     *
     * @param array $conditions WHERE conditions as key-value pairs
     * @return array|null
     */
    public function findOne(array $conditions): ?array
    {
        $results = $this->findWhere($conditions, ['*'], '', 1);
        return $results[0] ?? null;
    }

    /**
     * Insert new record
     *
     * @param array $data Data to insert as key-value pairs
     * @return int Last insert ID
     */
    public function insert(array $data): int
    {
        $columns = array_keys($data);
        $values = array_map(fn($col) => ":{$col}", $columns);

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->table,
            implode(', ', $columns),
            implode(', ', $values)
        );

        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Update record(s)
     *
     * @param array $data Data to update as key-value pairs
     * @param array $conditions WHERE conditions as key-value pairs
     * @return int Number of affected rows
     */
    public function update(array $data, array $conditions): int
    {
        $set = [];
        foreach (array_keys($data) as $key) {
            $set[] = "{$key} = :{$key}";
        }

        $where = [];
        foreach (array_keys($conditions) as $key) {
            $where[] = "{$key} = :where_{$key}";
        }

        $sql = sprintf(
            "UPDATE %s SET %s WHERE %s",
            $this->table,
            implode(', ', $set),
            implode(' AND ', $where)
        );

        // Merge data and conditions with prefixed keys for conditions
        $params = $data;
        foreach ($conditions as $key => $value) {
            $params["where_{$key}"] = $value;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount();
    }

    /**
     * Update record by ID
     *
     * @param int $id Record ID
     * @param array $data Data to update
     * @return int Number of affected rows
     */
    public function updateById(int $id, array $data): int
    {
        return $this->update($data, [$this->primaryKey => $id]);
    }

    /**
     * Delete record(s)
     *
     * @param array $conditions WHERE conditions as key-value pairs
     * @return int Number of affected rows
     */
    public function delete(array $conditions): int
    {
        $where = [];
        foreach (array_keys($conditions) as $key) {
            $where[] = "{$key} = :{$key}";
        }

        $sql = sprintf(
            "DELETE FROM %s WHERE %s",
            $this->table,
            implode(' AND ', $where)
        );

        $stmt = $this->db->prepare($sql);
        $stmt->execute($conditions);

        return $stmt->rowCount();
    }

    /**
     * Delete record by ID
     *
     * @param int $id Record ID
     * @return int Number of affected rows
     */
    public function deleteById(int $id): int
    {
        return $this->delete([$this->primaryKey => $id]);
    }

    /**
     * Count records
     *
     * @param array $conditions WHERE conditions as key-value pairs
     * @return int
     */
    public function count(array $conditions = []): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";

        if (!empty($conditions)) {
            $where = [];
            foreach (array_keys($conditions) as $key) {
                $where[] = "{$key} = :{$key}";
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($conditions);

        $result = $stmt->fetch();
        return (int) $result['count'];
    }

    /**
     * Execute custom query
     *
     * @param string $sql SQL query
     * @param array $params Parameters to bind
     * @return array
     */
    public function query(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Execute custom query and return single row
     *
     * @param string $sql SQL query
     * @param array $params Parameters to bind
     * @return array|null
     */
    public function queryOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Begin transaction
     */
    public function beginTransaction(): bool
    {
        return $this->db->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit(): bool
    {
        return $this->db->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback(): bool
    {
        return $this->db->rollBack();
    }
}
