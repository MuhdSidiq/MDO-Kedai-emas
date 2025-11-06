<?php
declare(strict_types=1);

require_once __DIR__ . '/../model/product.php';

class ProductController
{
    private Product $productModel;
    private array $errors = [];
    private array $successMessages = [];

    public function __construct()
    {
        $this->productModel = new Product();
    }

    /**
     * Display all products (List view)
     */
    public function index(): void
    {
        $this->checkSessionMessages();

        $products = $this->productModel->getAll();
        $totalCount = $this->productModel->getTotalCount();
        $totalValue = $this->productModel->getTotalStockValue();
        $controller = $this;

        require_once __DIR__ . '/../view/product/index.php';
    }

    /**
     * Show create product form
     */
    public function create(): void
    {
        $controller = $this;
        require_once __DIR__ . '/../view/product/create.php';
    }

    /**
     * Store a new product
     */
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/products/create');
            return;
        }

        // Validate input
        $name = $this->sanitize($_POST['name'] ?? '');
        $description = $this->sanitize($_POST['description'] ?? '');
        $pricePerGram = filter_var($_POST['price_per_gram'] ?? 0, FILTER_VALIDATE_FLOAT);
        $stock = filter_var($_POST['stock'] ?? 0, FILTER_VALIDATE_INT);

        if (empty($name)) {
            $this->errors[] = "Product name is required";
        }

        if (empty($description)) {
            $this->errors[] = "Product description is required";
        }

        if ($pricePerGram === false || $pricePerGram <= 0) {
            $this->errors[] = "Valid price per gram is required";
        }

        if ($stock === false || $stock < 0) {
            $this->errors[] = "Valid stock quantity is required";
        }

        // If validation fails, show create form with errors
        if (!empty($this->errors)) {
            $controller = $this;
            require_once __DIR__ . '/../view/product/create.php';
            return;
        }

        // Create product
        $productId = $this->productModel->create($name, $description, $pricePerGram, $stock);

        if ($productId) {
            $_SESSION['success'] = "Product created successfully!";
            $this->redirect('/products');
        } else {
            $this->errors[] = "Failed to create product. Please try again.";
            $controller = $this;
            require_once __DIR__ . '/../view/product/create.php';
        }
    }

    /**
     * Show edit product form
     */
    public function edit(string $id = ''): void
    {
        $id = filter_var($id, FILTER_VALIDATE_INT);

        if (!$id) {
            $_SESSION['error'] = "Invalid product ID";
            $this->redirect('/products');
            return;
        }

        $product = $this->productModel->getById($id);

        if (!$product) {
            $_SESSION['error'] = "Product not found";
            $this->redirect('/products');
            return;
        }

        $controller = $this;
        require_once __DIR__ . '/../view/product/edit.php';
    }

    /**
     * Update an existing product
     */
    public function update(string $id = ''): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/products');
            return;
        }

        $id = filter_var($id, FILTER_VALIDATE_INT);

        if (!$id) {
            $_SESSION['error'] = "Invalid product ID";
            $this->redirect('/products');
            return;
        }

        // Validate input
        $name = $this->sanitize($_POST['name'] ?? '');
        $description = $this->sanitize($_POST['description'] ?? '');
        $pricePerGram = filter_var($_POST['price_per_gram'] ?? 0, FILTER_VALIDATE_FLOAT);
        $stock = filter_var($_POST['stock'] ?? 0, FILTER_VALIDATE_INT);

        if (empty($name)) {
            $this->errors[] = "Product name is required";
        }

        if (empty($description)) {
            $this->errors[] = "Product description is required";
        }

        if ($pricePerGram === false || $pricePerGram <= 0) {
            $this->errors[] = "Valid price per gram is required";
        }

        if ($stock === false || $stock < 0) {
            $this->errors[] = "Valid stock quantity is required";
        }

        // If validation fails, show edit form with errors
        if (!empty($this->errors)) {
            $product = $this->productModel->getById($id);
            $controller = $this;
            require_once __DIR__ . '/../view/product/edit.php';
            return;
        }

        // Update product
        $success = $this->productModel->update($id, $name, $description, $pricePerGram, $stock);

        if ($success) {
            $_SESSION['success'] = "Product updated successfully!";
            $this->redirect('/products');
        } else {
            $this->errors[] = "Failed to update product. Please try again.";
            $product = $this->productModel->getById($id);
            $controller = $this;
            require_once __DIR__ . '/../view/product/edit.php';
        }
    }

    /**
     * Delete a product
     */
    public function delete(string $id = ''): void
    {
        $id = filter_var($id, FILTER_VALIDATE_INT);

        if (!$id) {
            $_SESSION['error'] = "Invalid product ID";
            $this->redirect('/products');
            return;
        }

        $success = $this->productModel->delete($id);

        if ($success) {
            $_SESSION['success'] = "Product deleted successfully!";
        } else {
            $_SESSION['error'] = "Failed to delete product. Please try again.";
        }

        $this->redirect('/products');
    }

    /**
     * Search products
     */
    public function search(): void
    {
        $searchTerm = $this->sanitize($_GET['q'] ?? '');

        if (empty($searchTerm)) {
            $this->redirect('/products');
            return;
        }

        $products = $this->productModel->search($searchTerm);
        $totalCount = $this->productModel->getTotalCount();
        $totalValue = $this->productModel->getTotalStockValue();
        $controller = $this;
        require_once __DIR__ . '/../view/product/index.php';
    }

    /**
     * Get error messages
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get success messages
     */
    public function getSuccessMessages(): array
    {
        return $this->successMessages;
    }

    /**
     * Sanitize user input
     */
    private function sanitize(string $data): string
    {
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Redirect helper
     */
    private function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    /**
     * Check for session messages and add to errors/success
     */
    public function checkSessionMessages(): void
    {
        if (isset($_SESSION['error'])) {
            $this->errors[] = $_SESSION['error'];
            unset($_SESSION['error']);
        }

        if (isset($_SESSION['success'])) {
            $this->successMessages[] = $_SESSION['success'];
            unset($_SESSION['success']);
        }
    }
}
