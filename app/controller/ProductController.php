<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Product;

/**
 * Product Controller
 *
 * Handles product-related operations
 */
class ProductController extends Controller
{
    private Product $productModel;

    public function __construct()
    {
        $this->productModel = new Product();
    }

    /**
     * Display all products (List view)
     */
    public function index(): void
    {
        // $this->requireAuth();

        // Sample hardcoded data for testing
        $products = [
            [
                'id' => 1,
                'name' => 'Gold Bar 999.9',
                'description' => 'Pure gold bar 999.9 fineness, certified by international standards',
                'price_per_gram' => 285.50,
                'stock' => 50,
                'timestamps' => '2024-11-06 10:30:00'
            ],
            [
                'id' => 2,
                'name' => 'Gold Coin 1oz',
                'description' => 'One ounce gold coin, collectible and investment grade',
                'price_per_gram' => 290.00,
                'stock' => 8,
                'timestamps' => '2024-11-05 14:20:00'
            ],
            [
                'id' => 3,
                'name' => 'Gold Bracelet 916',
                'description' => '22K gold bracelet, beautiful design with traditional patterns',
                'price_per_gram' => 270.75,
                'stock' => 120,
                'timestamps' => '2024-11-04 09:15:00'
            ],
            [
                'id' => 4,
                'name' => 'Gold Necklace 750',
                'description' => '18K gold necklace, elegant and durable for daily wear',
                'price_per_gram' => 220.50,
                'stock' => 65,
                'timestamps' => '2024-11-03 16:45:00'
            ],
            [
                'id' => 5,
                'name' => 'Gold Ring 999',
                'description' => 'Pure gold ring, simple and classic design',
                'price_per_gram' => 285.00,
                'stock' => 5,
                'timestamps' => '2024-11-02 11:30:00'
            ],
        ];

        $totalCount = count($products);
        $totalValue = array_reduce($products, fn($sum, $p) => $sum + ($p['price_per_gram'] * $p['stock']), 0);
        $lowStock = array_filter($products, fn($p) => $p['stock'] <= 10);

        $this->view('product/index', [
            'products' => $products,
            'totalCount' => $totalCount,
            'totalValue' => $totalValue,
            'lowStock' => $lowStock
        ]);
    }

    /**
     * Show single product details
     */
    public function show(string $id): void
    {
        $this->requireAuth();

        $productId = filter_var($id, FILTER_VALIDATE_INT);

        if (!$productId) {
            $this->flash('error', 'Invalid product ID');
            $this->redirect('/products');
            return;
        }

        $product = $this->productModel->getById($productId);

        if (!$product) {
            $this->error(404, 'Product not found');
            return;
        }

        $this->view('product/show', [
            'product' => $product
        ]);
    }

    /**
     * Show create product form
     */
    public function createForm(): void
    {
        $this->requireAuth();
        $this->requireRole('Admin');

        $this->view('product/create');
    }

    /**
     * Store a new product
     */
    public function create(): void
    {
        $this->requireAuth();
        $this->requireRole('Admin');

        if (!$this->isPost()) {
            $this->redirect('/products/create');
            return;
        }

        // Validate required fields
        $required = ['name', 'description', 'price_per_gram', 'stock'];
        $missing = $this->validateRequired($required);

        if (!empty($missing)) {
            $this->flash('error', 'Missing required fields: ' . implode(', ', $missing));
            $this->redirect('/products/create');
            return;
        }

        // Sanitize and validate input
        $name = $this->sanitize($this->post('name'));
        $description = $this->sanitize($this->post('description'));
        $pricePerGram = filter_var($this->post('price_per_gram'), FILTER_VALIDATE_FLOAT);
        $stock = filter_var($this->post('stock'), FILTER_VALIDATE_INT);

        // Additional validation
        if ($pricePerGram === false || $pricePerGram <= 0) {
            $this->flash('error', 'Valid price per gram is required');
            $this->redirect('/products/create');
            return;
        }

        if ($stock === false || $stock < 0) {
            $this->flash('error', 'Valid stock quantity is required (must be 0 or greater)');
            $this->redirect('/products/create');
            return;
        }

        // Create product
        $productId = $this->productModel->create($name, $description, $pricePerGram, $stock);

        if ($productId) {
            $this->flash('success', 'Product created successfully!');
            $this->redirect('/products/' . $productId);
        } else {
            $this->flash('error', 'Failed to create product. Please try again.');
            $this->redirect('/products/create');
        }
    }

    /**
     * Show edit product form
     */
    public function editForm(string $id): void
    {
        $this->requireAuth();
        $this->requireRole('Admin');

        $productId = filter_var($id, FILTER_VALIDATE_INT);

        if (!$productId) {
            $this->flash('error', 'Invalid product ID');
            $this->redirect('/products');
            return;
        }

        $product = $this->productModel->getById($productId);

        if (!$product) {
            $this->flash('error', 'Product not found');
            $this->redirect('/products');
            return;
        }

        $this->view('product/edit', [
            'product' => $product
        ]);
    }

    /**
     * Update an existing product
     */
    public function update(string $id): void
    {
        $this->requireAuth();
        $this->requireRole('Admin');

        if (!$this->isPost()) {
            $this->redirect('/products');
            return;
        }

        $productId = filter_var($id, FILTER_VALIDATE_INT);

        if (!$productId) {
            $this->flash('error', 'Invalid product ID');
            $this->redirect('/products');
            return;
        }

        // Validate required fields
        $required = ['name', 'description', 'price_per_gram', 'stock'];
        $missing = $this->validateRequired($required);

        if (!empty($missing)) {
            $this->flash('error', 'Missing required fields: ' . implode(', ', $missing));
            $this->redirect('/products/' . $productId . '/edit');
            return;
        }

        // Sanitize and validate input
        $name = $this->sanitize($this->post('name'));
        $description = $this->sanitize($this->post('description'));
        $pricePerGram = filter_var($this->post('price_per_gram'), FILTER_VALIDATE_FLOAT);
        $stock = filter_var($this->post('stock'), FILTER_VALIDATE_INT);

        // Additional validation
        if ($pricePerGram === false || $pricePerGram <= 0) {
            $this->flash('error', 'Valid price per gram is required');
            $this->redirect('/products/' . $productId . '/edit');
            return;
        }

        if ($stock === false || $stock < 0) {
            $this->flash('error', 'Valid stock quantity is required (must be 0 or greater)');
            $this->redirect('/products/' . $productId . '/edit');
            return;
        }

        // Update product
        $success = $this->productModel->update($productId, $name, $description, $pricePerGram, $stock);

        if ($success) {
            $this->flash('success', 'Product updated successfully!');
            $this->redirect('/products/' . $productId);
        } else {
            $this->flash('error', 'Failed to update product. Please try again.');
            $this->redirect('/products/' . $productId . '/edit');
        }
    }

    /**
     * Delete a product
     */
    public function delete(string $id): void
    {
        $this->requireAuth();
        $this->requireRole('Admin');

        if (!$this->isPost()) {
            $this->flash('error', 'Invalid request method');
            $this->redirect('/products');
            return;
        }

        $productId = filter_var($id, FILTER_VALIDATE_INT);

        if (!$productId) {
            $this->flash('error', 'Invalid product ID');
            $this->redirect('/products');
            return;
        }

        $success = $this->productModel->delete($productId);

        if ($success) {
            $this->flash('success', 'Product deleted successfully!');
        } else {
            $this->flash('error', 'Failed to delete product. Please try again.');
        }

        $this->redirect('/products');
    }

    /**
     * Search products
     */
    public function search(): void
    {
        $this->requireAuth();

        $searchTerm = $this->sanitize($this->get('q', ''));

        if (empty($searchTerm)) {
            $this->redirect('/products');
            return;
        }

        $products = $this->productModel->search($searchTerm);
        $totalCount = $this->productModel->getTotalCount();
        $totalValue = $this->productModel->getTotalStockValue();

        $this->view('product/index', [
            'products' => $products,
            'totalCount' => $totalCount,
            'totalValue' => $totalValue,
            'searchTerm' => $searchTerm
        ]);
    }

    /**
     * Update stock (AJAX endpoint)
     */
    public function updateStock(string $id): void
    {
        $this->requireAuth();
        $this->requireRole('Admin');

        if (!$this->isPost() || !$this->isAjax()) {
            $this->json(['error' => true, 'message' => 'Invalid request'], 400);
            return;
        }

        $productId = filter_var($id, FILTER_VALIDATE_INT);

        if (!$productId) {
            $this->json(['error' => true, 'message' => 'Invalid product ID'], 400);
            return;
        }

        $quantity = filter_var($this->post('quantity'), FILTER_VALIDATE_INT);

        if ($quantity === false) {
            $this->json(['error' => true, 'message' => 'Invalid quantity'], 400);
            return;
        }

        $success = $this->productModel->updateStock($productId, $quantity);

        if ($success) {
            $product = $this->productModel->getById($productId);
            $this->json([
                'success' => true,
                'message' => 'Stock updated successfully',
                'product' => $product
            ]);
        } else {
            $this->json(['error' => true, 'message' => 'Failed to update stock'], 500);
        }
    }

    /**
     * Add stock to product
     */
    public function addStock(string $id): void
    {
        $this->requireAuth();
        $this->requireRole('Admin');

        if (!$this->isPost()) {
            $this->flash('error', 'Invalid request method');
            $this->redirect('/products');
            return;
        }

        $productId = filter_var($id, FILTER_VALIDATE_INT);

        if (!$productId) {
            $this->flash('error', 'Invalid product ID');
            $this->redirect('/products');
            return;
        }

        $quantity = filter_var($this->post('quantity'), FILTER_VALIDATE_INT);

        if ($quantity === false || $quantity <= 0) {
            $this->flash('error', 'Valid quantity is required (must be greater than 0)');
            $this->redirect('/products/' . $productId);
            return;
        }

        $success = $this->productModel->addStock($productId, $quantity);

        if ($success) {
            $this->flash('success', "Successfully added {$quantity} units to stock");
        } else {
            $this->flash('error', 'Failed to add stock');
        }

        $this->redirect('/products/' . $productId);
    }

    /**
     * Reduce stock from product
     */
    public function reduceStock(string $id): void
    {
        $this->requireAuth();
        $this->requireRole('Admin');

        if (!$this->isPost()) {
            $this->flash('error', 'Invalid request method');
            $this->redirect('/products');
            return;
        }

        $productId = filter_var($id, FILTER_VALIDATE_INT);

        if (!$productId) {
            $this->flash('error', 'Invalid product ID');
            $this->redirect('/products');
            return;
        }

        $quantity = filter_var($this->post('quantity'), FILTER_VALIDATE_INT);

        if ($quantity === false || $quantity <= 0) {
            $this->flash('error', 'Valid quantity is required (must be greater than 0)');
            $this->redirect('/products/' . $productId);
            return;
        }

        $success = $this->productModel->reduceStock($productId, $quantity);

        if ($success) {
            $this->flash('success', "Successfully reduced {$quantity} units from stock");
        } else {
            $this->flash('error', 'Failed to reduce stock (insufficient quantity or product not found)');
        }

        $this->redirect('/products/' . $productId);
    }

    /**
     * Get low stock products
     */
    public function lowStock(): void
    {
        $this->requireAuth();

        $threshold = filter_var($this->get('threshold', 10), FILTER_VALIDATE_INT);
        $threshold = $threshold ?: 10;

        $products = $this->productModel->getLowStock($threshold);
        $totalCount = count($products);

        $this->view('product/low-stock', [
            'products' => $products,
            'totalCount' => $totalCount,
            'threshold' => $threshold
        ]);
    }

    /**
     * Get out of stock products
     */
    public function outOfStock(): void
    {
        $this->requireAuth();

        $products = $this->productModel->getOutOfStock();
        $totalCount = count($products);

        $this->view('product/out-of-stock', [
            'products' => $products,
            'totalCount' => $totalCount
        ]);
    }
}
