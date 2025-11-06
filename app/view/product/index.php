<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - Kedai Emas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row mb-4">
            <div class="col">
                <h1 class="display-4">Product Management</h1>
                <p class="lead">Manage your gold products and inventory</p>
            </div>
        </div>

        <?php $flash = get_flash(); ?>
        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show" role="alert">
                <strong><?= $flash['type'] === 'error' ? 'Error!' : 'Success!' ?></strong>
                <?= e($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-box-seam"></i> Total Products</h5>
                        <h2><?= $totalCount ?? 0 ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-cash-stack"></i> Total Stock Value</h5>
                        <h2>RM <?= number_format($totalValue ?? 0, 2) ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-clipboard-data"></i> Products Listed</h5>
                        <h2><?= count($products ?? []) ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Add Button -->
        <div class="row mb-3">
            <div class="col-md-6">
                <form action="/products/search" method="GET" class="d-flex">
                    <input type="text" name="q" class="form-control me-2" placeholder="Search products..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-search"></i> Search
                    </button>
                </form>
            </div>
            <div class="col-md-6 text-end">
                <a href="/products/create" class="btn btn-success">
                    <i class="bi bi-plus-circle"></i> Add New Product
                </a>
            </div>
        </div>

        <!-- Products Table -->
        <div class="card">
            <div class="card-body">
                <?php if (empty($products)): ?>
                    <div class="alert alert-info text-center">
                        <i class="bi bi-info-circle"></i> No products found. <a href="/products/create">Add your first product</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Price/Gram (RM)</th>
                                    <th>Stock</th>
                                    <th>Last Updated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($product['id']) ?></td>
                                        <td><strong><?= htmlspecialchars($product['name']) ?></strong></td>
                                        <td><?= htmlspecialchars(substr($product['description'], 0, 50)) . (strlen($product['description']) > 50 ? '...' : '') ?></td>
                                        <td>RM <?= number_format($product['price_per_gram'], 2) ?></td>
                                        <td>
                                            <span class="badge <?= $product['stock'] <= 10 ? 'bg-danger' : ($product['stock'] <= 50 ? 'bg-warning' : 'bg-success') ?>">
                                                <?= htmlspecialchars($product['stock']) ?> units
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($product['timestamps']) ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="/products/<?= $product['id'] ?>" class="btn btn-sm btn-info">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="/products/<?= $product['id'] ?>/edit" class="btn btn-sm btn-warning">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form method="POST" action="/products/<?= $product['id'] ?>/delete" style="display: inline;">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this product?')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-4">
            <a href="/view/dashboard.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
