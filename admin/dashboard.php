<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require admin authentication
require_admin();

// Get statistics
$stmt = $pdo->query("SELECT COUNT(*) as total FROM orders WHERE status = 'pending'");
$pending_orders = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM orders WHERE status = 'completed'");
$completed_orders = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'farmer'");
$total_farmers = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
$total_products = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT SUM(total_amount) as total FROM orders WHERE status = 'completed'");
$total_revenue = $stmt->fetch()['total'] ?? 0;

// Get recent orders
$stmt = $pdo->prepare("
    SELECT o.*, u.full_name, COUNT(oi.id) as item_count 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    WHERE o.status != 'cancelled' 
    GROUP BY o.id 
    ORDER BY o.order_date DESC LIMIT 10
");
$stmt->execute();
$recent_orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - KilimoSafi</title>
    <link rel="stylesheet" href="../assets/css/main.css">
</head>
<body>
    <!-- Admin Header -->
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
                    <div class="user-menu">
                        <button class="user-menu-toggle" onclick="toggleUserMenu()" style="background: rgba(255,255,255,0.2); padding: var(--spacing-md) var(--spacing-lg); border-radius: var(--radius-md); border: none; color: white; cursor: pointer;">
                            👤 Admin
                        </button>
                        <div class="dropdown-menu" id="userMenu">
                            <a href="../api/logout.php">Logout</a>
                        </div>
                    </div>
                </div>
            </nav>
        </div>
    </header>

    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="container">
            <h1>Admin Dashboard</h1>
            <p>Manage agricultural inputs and orders</p>
        </div>
    </div>

    <!-- Main Content -->
    <main class="container" style="padding: var(--spacing-xl) 0;">
        <!-- Statistics -->
        <div class="dashboard-stats">
            <div class="stat-card">
                <h3>Pending Orders</h3>
                <div class="stat-value text-warning"><?= $pending_orders ?></div>
                <a href="orders.php?status=pending">Manage →</a>
            </div>

            <div class="stat-card">
                <h3>Completed Orders</h3>
                <div class="stat-value"><?= $completed_orders ?></div>
                <a href="orders.php?status=completed">View →</a>
            </div>

            <div class="stat-card">
                <h3>Total Farmers</h3>
                <div class="stat-value"><?= $total_farmers ?></div>
                <a href="farmers.php">View Farmers →</a>
            </div>

            <div class="stat-card">
                <h3>Total Products</h3>
                <div class="stat-value"><?= $total_products ?></div>
                <a href="products.php">Manage →</a>
            </div>
        </div>

        <!-- Revenue Card -->
        <div class="card" style="margin-bottom: var(--spacing-xl);">
            <div class="card-header">
                <h2 style="margin: 0;">Total Revenue</h2>
            </div>
            <div class="card-body">
                <div style="font-size: 2.5rem; color: var(--secondary-green); font-weight: bold;">
                    <?= format_price($total_revenue) ?>
                </div>
                <p style="color: var(--light-text); margin: 0;">From completed orders</p>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="card">
            <div class="card-header">
                <h2 style="margin: 0;">Recent Orders</h2>
            </div>
            <div class="card-body">
                <?php if (!empty($recent_orders)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_orders as $order): ?>
                                <tr>
                                    <td><?= htmlspecialchars($order['order_number']) ?></td>
                                    <td><?= htmlspecialchars($order['full_name']) ?></td>
                                    <td><?= date('M d, Y', strtotime($order['order_date'])) ?></td>
                                    <td><?= $order['item_count'] ?></td>
                                    <td><?= format_price($order['total_amount']) ?></td>
                                    <td>
                                        <span class="badge badge-<?= strtolower($order['status']) ?>">
                                            <?= ucfirst($order['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="order-detail.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-secondary">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div style="text-align: center; margin-top: var(--spacing-lg);">
                        <a href="orders.php" class="btn btn-secondary">View All Orders</a>
                    </div>
                <?php else: ?>
                    <p style="text-align: center; color: var(--light-text);">No orders yet</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div style="margin-top: var(--spacing-xl);">
            <h3>Quick Actions</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--spacing-lg); margin-top: var(--spacing-lg);">
                <a href="products.php?action=add" class="card" style="text-decoration: none; text-align: center; padding: var(--spacing-xl);">
                    <div style="font-size: 2rem; margin-bottom: var(--spacing-md);">➕</div>
                    <h4>Add Product</h4>
                </a>
                <a href="categories.php" class="card" style="text-decoration: none; text-align: center; padding: var(--spacing-xl);">
                    <div style="font-size: 2rem; margin-bottom: var(--spacing-md);">📂</div>
                    <h4>Manage Categories</h4>
                </a>
                <a href="orders.php" class="card" style="text-decoration: none; text-align: center; padding: var(--spacing-xl);">
                    <div style="font-size: 2rem; margin-bottom: var(--spacing-md);">📦</div>
                    <h4>View Orders</h4>
                </a>
                <a href="farmers.php" class="card" style="text-decoration: none; text-align: center; padding: var(--spacing-xl);">
                    <div style="font-size: 2rem; margin-bottom: var(--spacing-md);">👨‍🌾</div>
                    <h4>Manage Farmers</h4>
                </a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2024 KilimoSafi Admin Panel. All rights reserved.</p>
        </div>
    </footer>

    <script>
        function toggleUserMenu() {
            const menu = document.getElementById('userMenu');
            menu.classList.toggle('active');
        }
    </script>
</body>
</html>
