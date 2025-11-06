<?php
declare(strict_types=1);

namespace App\Model;

/**
 * Product Model
 *
 * Handles product data and business logic
 */
class Product extends Model
{
    protected string $table = 'product_data';
    protected string $primaryKey = 'id';

    /**
     * Get all products
     * @return array
     */
    public function getAll(): array
    {
        return $this->findAll(['*'], 'timestamps DESC');
    }

    /**
     * Get product by ID
     * @param int $id
     * @return array|null
     */
    public function getById(int $id): ?array
    {
        return $this->findById($id);
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
            return $this->insert([
                'name' => $name,
                'description' => $description,
                'price_per_gram' => $pricePerGram,
                'stock' => $stock,
                'timestamps' => date('Y-m-d H:i:s')
            ]);
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
            $affected = $this->updateById($id, [
                'name' => $name,
                'description' => $description,
                'price_per_gram' => $pricePerGram,
                'stock' => $stock,
                'timestamps' => date('Y-m-d H:i:s')
            ]);

            return $affected > 0;
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
            $affected = $this->deleteById($id);
            return $affected > 0;
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
            $sql = "
                UPDATE {$this->table}
                SET stock = stock + :quantity,
                    timestamps = NOW()
                WHERE {$this->primaryKey} = :id
            ";

            $stmt = $this->db->prepare($sql);
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
        $sql = "
            SELECT id, name, description, price_per_gram, stock, timestamps
            FROM {$this->table}
            WHERE stock <= :threshold
            ORDER BY stock ASC
        ";

        return $this->query($sql, ['threshold' => $threshold]);
    }

    /**
     * Search products by name or description
     * @param string $searchTerm
     * @return array
     */
    public function search(string $searchTerm): array
    {
        $sql = "
            SELECT id, name, description, price_per_gram, stock, timestamps
            FROM {$this->table}
            WHERE name LIKE :search OR description LIKE :search
            ORDER BY timestamps DESC
        ";

        return $this->query($sql, ['search' => "%{$searchTerm}%"]);
    }

    /**
     * Get total product count
     * @return int
     */
    public function getTotalCount(): int
    {
        return $this->count();
    }

    /**
     * Get total stock value (sum of all products * price)
     * @return float
     */
    public function getTotalStockValue(): float
    {
        $sql = "
            SELECT SUM(stock * price_per_gram) as total_value
            FROM {$this->table}
        ";

        $result = $this->queryOne($sql);
        return (float)($result['total_value'] ?? 0);
    }

    /**
     * Get products in stock (stock > 0)
     * @return array
     */
    public function getInStock(): array
    {
        $sql = "
            SELECT id, name, description, price_per_gram, stock, timestamps
            FROM {$this->table}
            WHERE stock > 0
            ORDER BY timestamps DESC
        ";

        return $this->query($sql);
    }

    /**
     * Get out of stock products
     * @return array
     */
    public function getOutOfStock(): array
    {
        return $this->findWhere(['stock' => 0], ['*'], 'timestamps DESC');
    }

    /**
     * Reduce stock by quantity (with validation)
     * @param int $id
     * @param int $quantity
     * @return bool Returns false if insufficient stock
     */
    public function reduceStock(int $id, int $quantity): bool
    {
        $product = $this->findById($id);

        if (!$product) {
            error_log("Product not found: ID {$id}");
            return false;
        }

        if ($product['stock'] < $quantity) {
            error_log("Insufficient stock for product ID {$id}. Available: {$product['stock']}, Requested: {$quantity}");
            return false;
        }

        return $this->updateStock($id, -$quantity);
    }

    /**
     * Add stock by quantity
     * @param int $id
     * @param int $quantity
     * @return bool
     */
    public function addStock(int $id, int $quantity): bool
    {
        if ($quantity <= 0) {
            error_log("Invalid quantity for adding stock: {$quantity}");
            return false;
        }

        return $this->updateStock($id, $quantity);
    }

    /**
     * Check if product is in stock
     * @param int $id
     * @return bool
     */
    public function isInStock(int $id): bool
    {
        $product = $this->findById($id);
        return $product && $product['stock'] > 0;
    }

    /**
     * Get products sorted by price (ascending or descending)
     * @param string $order 'ASC' or 'DESC'
     * @return array
     */
    public function getByPrice(string $order = 'ASC'): array
    {
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
        return $this->findAll(['*'], "price_per_gram {$order}");
    }

    /**
     * Get products sorted by stock level
     * @param string $order 'ASC' or 'DESC'
     * @return array
     */
    public function getByStock(string $order = 'ASC'): array
    {
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
        return $this->findAll(['*'], "stock {$order}");
    }
}
