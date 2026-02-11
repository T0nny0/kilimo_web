<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_admin();

// Get all products
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name
    FROM products p
    JOIN categories c ON p.category_id = c.id
    ORDER BY p.created_at DESC
");
$stmt->execute();
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - KilimoSafi Admin</title>
    <link rel="stylesheet" href="../assets/css/main.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <nav>
                <a href="../index.php" class="logo">Kilimo<span>Safi</span> Admin</a>
                <div class="nav-links">
                    <a href="dashboard.php">Dashboard</a>
                    <a href="products.php">Products</a>
                    <a href="categories.php">Categories</a>
                    <a href="orders.php">Orders</a>
                    <a href="farmers.php">Farmers</a>
                    <a href="../api/logout.php" class="btn btn-outline">Logout</a>
                </div>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container" style="padding: var(--spacing-xl) 0;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-xl);">
            <h1>Manage Products</h1>
            <a href="product-form.php" class="btn btn-primary">Add New Product</a>
        </div>

        <?php if (!empty($products)): ?>
            <div class="card">
                <div class="card-body" style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Buy Price</th>
                                <th>Rent Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?= htmlspecialchars($product['name']) ?></td>
                                    <td><?= htmlspecialchars($product['category_name']) ?></td>
                                    <td><?= !empty($product['buy_price']) ? format_price($product['buy_price']) : '-' ?></td>
                                    <td><?= !empty($product['rent_price']) ? format_price($product['rent_price']) . '/day' : '-' ?></td>
                                    <td><?= (int)$product['quantity_available'] ?> <?= htmlspecialchars($product['unit']) ?></td>
                                    <td>
                                        <span class="badge <?= $product['is_available'] ? 'badge-success' : 'badge-danger' ?>">
                                            <?= $product['is_available'] ? 'Available' : 'Unavailable' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="product-form.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
                                        <button onclick="deleteProduct(<?= $product['id'] ?>)" class="btn btn-sm btn-danger">Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body" style="text-align: center; padding: var(--spacing-2xl);">
                    <h2>No products yet</h2>
                    <a href="product-form.php" class="btn btn-primary" style="margin-top: var(--spacing-lg);">Add First Product</a>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2024 KilimoSafi Admin Panel.</p>
        </div>
    </footer>

    <script>
        function deleteProduct(productId) {
            if (confirm('Are you sure you want to delete this product?')) {
                fetch('../api/admin/product-delete.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ product_id: productId })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Product deleted successfully');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        }
    </script>
</body>
</html>
