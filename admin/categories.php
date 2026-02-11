<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_admin();

// Get all categories
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - KilimoSafi Admin</title>
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
            <h1>Manage Categories</h1>
            <button onclick="showCategoryForm()" class="btn btn-primary">Add New Category</button>
        </div>

        <?php if (!empty($categories)): ?>
            <div class="card">
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: var(--spacing-lg);">
                        <?php foreach ($categories as $category): ?>
                            <div class="card" style="text-align: center;">
                                <div style="font-size: 2rem; margin: var(--spacing-md) 0;">
                                    <?= htmlspecialchars($category['icon']) ?>
                                </div>
                                <h3><?= htmlspecialchars($category['name']) ?></h3>
                                <p style="font-size: 0.9rem; color: var(--light-text);">
                                    <?= htmlspecialchars(substr($category['description'] ?? '', 0, 50)) ?>
                                </p>
                                <div style="display: flex; gap: var(--spacing-sm); margin-top: var(--spacing-md);">
                                    <button class="btn btn-sm btn-secondary" style="flex: 1;">Edit</button>
                                    <button class="btn btn-sm btn-danger" style="flex: 1;">Delete</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body" style="text-align: center; padding: var(--spacing-2xl);">
                    <h2>No categories yet</h2>
                    <button onclick="showCategoryForm()" class="btn btn-primary" style="margin-top: var(--spacing-lg);">Create First Category</button>
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
        function showCategoryForm() {
            alert('Category form - Add/Edit categories functionality');
        }
    </script>
</body>
</html>
