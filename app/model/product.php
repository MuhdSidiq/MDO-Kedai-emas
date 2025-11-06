<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/connection.php';

class Product
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Get all products
     * @return array
     */
    public function getAll(): array
    {
        $stmt = $this->db->prepare("
            SELECT id, name, description, price_per_gram, stock, timestamps
            FROM product_data
            ORDER BY timestamps DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get product by ID
     * @param int $id
     * @return array|null
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT id, name, description, price_per_gram, stock, timestamps
            FROM product_data
            WHERE id = :id
        ");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Create a new product
     * @param string $name
     * @param string $description
     * @param float $pricePerGram
     * @param int $stock
     * @return int|false Returns the last insert ID or false on failure
     */
    public function create(string $name, string $description, float $pricePerGram, int $stock): int|false
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO product_data (name, description, price_per_gram, stock, timestamps)
                VALUES (:name, :description, :price_per_gram, :stock, NOW())
            ");

            $result = $stmt->execute([
                'name' => $name,
                'description' => $description,
                'price_per_gram' => $pricePerGram,
                'stock' => $stock
            ]);

            return $result ? (int)$this->db->lastInsertId() : false;
        } catch (\PDOException $e) {
            error_log("Product creation error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update an existing product
     * @param int $id
     * @param string $name
     * @param string $description
     * @param float $pricePerGram
     * @param int $stock
     * @return bool
     */
    public function update(int $id, string $name, string $description, float $pricePerGram, int $stock): bool
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE product_data
                SET name = :name,
                    description = :description,
                    price_per_gram = :price_per_gram,
                    stock = :stock,
                    timestamps = NOW()
                WHERE id = :id
            ");

            return $stmt->execute([
                'id' => $id,
                'name' => $name,
                'description' => $description,
                'price_per_gram' => $pricePerGram,
                'stock' => $stock
            ]);
        } catch (\PDOException $e) {
            error_log("Product update error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a product
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM product_data WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        } catch (\PDOException $e) {
            error_log("Product deletion error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update product stock
     * @param int $id
     * @param int $quantity (can be negative to reduce stock)
     * @return bool
     */
    public function updateStock(int $id, int $quantity): bool
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE product_data
                SET stock = stock + :quantity,
                    timestamps = NOW()
                WHERE id = :id
            ");

            return $stmt->execute([
                'id' => $id,
                'quantity' => $quantity
            ]);
        } catch (\PDOException $e) {
            error_log("Stock update error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get products with low stock (threshold)
     * @param int $threshold
     * @return array
     */
    public function getLowStock(int $threshold = 10): array
    {
        $stmt = $this->db->prepare("
            SELECT id, name, description, price_per_gram, stock, timestamps
            FROM product_data
            WHERE stock <= :threshold
            ORDER BY stock ASC
        ");
        $stmt->execute(['threshold' => $threshold]);
        return $stmt->fetchAll();
    }

    /**
     * Search products by name
     * @param string $searchTerm
     * @return array
     */
    public function search(string $searchTerm): array
    {
        $stmt = $this->db->prepare("
            SELECT id, name, description, price_per_gram, stock, timestamps
            FROM product_data
            WHERE name LIKE :search OR description LIKE :search
            ORDER BY timestamps DESC
        ");
        $stmt->execute(['search' => "%{$searchTerm}%"]);
        return $stmt->fetchAll();
    }

    /**
     * Get total product count
     * @return int
     */
    public function getTotalCount(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM product_data");
        $result = $stmt->fetch();
        return (int)$result['count'];
    }

    /**
     * Get total stock value (sum of all products * price)
     * @return float
     */
    public function getTotalStockValue(): float
    {
        $stmt = $this->db->query("
            SELECT SUM(stock * price_per_gram) as total_value
            FROM product_data
        ");
        $result = $stmt->fetch();
        return (float)($result['total_value'] ?? 0);
    }
}
