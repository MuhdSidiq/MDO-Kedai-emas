<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Kedai Emas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-warning text-dark">
                        <h3><i class="bi bi-pencil"></i> Edit Product</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($this) && !empty($this->getErrors())): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong>Please fix the following errors:</strong>
                                <ul class="mb-0">
                                    <?php foreach ($this->getErrors() as $error): ?>
                                        <li><?= htmlspecialchars($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (!isset($product) || empty($product)): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle"></i> Product not found or invalid ID.
                            </div>
                            <a href="/products" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Back to Products
                            </a>
                        <?php else: ?>
                            <form action="/products/<?= htmlspecialchars($product['id']) ?>/update" method="POST">

                                <div class="mb-3">
                                    <label class="form-label text-muted">Product ID</label>
                                    <input type="text" class="form-control-plaintext" readonly value="<?= htmlspecialchars($product['id']) ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                                    <input type="text"
                                           class="form-control"
                                           id="name"
                                           name="name"
                                           placeholder="e.g., Gold Bar 24K"
                                           value="<?= htmlspecialchars($_POST['name'] ?? $product['name']) ?>"
                                           required>
                                    <small class="form-text text-muted">Enter a descriptive product name</small>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                                    <textarea class="form-control"
                                              id="description"
                                              name="description"
                                              rows="4"
                                              placeholder="Describe the product details, purity, weight, etc."
                                              required><?= htmlspecialchars($_POST['description'] ?? $product['description']) ?></textarea>
                                    <small class="form-text text-muted">Provide detailed information about the product</small>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="price_per_gram" class="form-label">Price per Gram (RM) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">RM</span>
                                            <input type="number"
                                                   class="form-control"
                                                   id="price_per_gram"
                                                   name="price_per_gram"
                                                   step="0.01"
                                                   min="0"
                                                   placeholder="0.00"
                                                   value="<?= htmlspecialchars($_POST['price_per_gram'] ?? $product['price_per_gram']) ?>"
                                                   required>
                                        </div>
                                        <small class="form-text text-muted">Enter the base price per gram</small>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="stock" class="form-label">Stock Quantity <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="number"
                                                   class="form-control"
                                                   id="stock"
                                                   name="stock"
                                                   min="0"
                                                   placeholder="0"
                                                   value="<?= htmlspecialchars($_POST['stock'] ?? $product['stock']) ?>"
                                                   required>
                                            <span class="input-group-text">units</span>
                                        </div>
                                        <small class="form-text text-muted">Available stock quantity</small>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label text-muted">Last Updated</label>
                                    <input type="text" class="form-control-plaintext" readonly value="<?= htmlspecialchars($product['timestamps']) ?>">
                                </div>

                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle"></i> <strong>Warning:</strong> Updating this product will change its information immediately.
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="/products" class="btn btn-secondary">
                                        <i class="bi bi-x-circle"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-warning">
                                        <i class="bi bi-check-circle"></i> Update Product
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
