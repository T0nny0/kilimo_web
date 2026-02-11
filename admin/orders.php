<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_admin();

$status_filter = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';

// Get orders
$query = "
    SELECT o.*, u.full_name, COUNT(oi.id) as item_count
    FROM orders o
    JOIN users u ON o.user_id = u.id
    LEFT JOIN order_items oi ON o.id = oi.order_id
";

if (!empty($status_filter)) {
    $query .= " WHERE o.status = ?";
    $stmt = $pdo->prepare($query . " GROUP BY o.id ORDER BY o.order_date DESC");
    $stmt->execute([$status_filter]);
} else {
    $stmt = $pdo->prepare($query . " GROUP BY o.id ORDER BY o.order_date DESC");
    $stmt->execute();
}

$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - KilimoSafi Admin</title>
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
        <h1>Manage Orders</h1>

        <!-- Filters -->
        <div style="margin-bottom: var(--spacing-lg); display: flex; gap: var(--spacing-md); flex-wrap: wrap;">
            <a href="orders.php" class="btn <?= empty($status_filter) ? 'btn-primary' : 'btn-secondary' ?>">All Orders</a>
            <a href="orders.php?status=pending" class="btn <?= $status_filter === 'pending' ? 'btn-primary' : 'btn-secondary' ?>">Pending</a>
            <a href="orders.php?status=approved" class="btn <?= $status_filter === 'approved' ? 'btn-primary' : 'btn-secondary' ?>">Approved</a>
            <a href="orders.php?status=completed" class="btn <?= $status_filter === 'completed' ? 'btn-primary' : 'btn-secondary' ?>">Completed</a>
        </div>

        <?php if (!empty($orders)): ?>
            <div class="card">
                <div class="card-body" style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><?= htmlspecialchars($order['order_number']) ?></td>
                                    <td><?= htmlspecialchars($order['full_name']) ?></td>
                                    <td><?= date('M d, Y', strtotime($order['order_date'])) ?></td>
                                    <td><?= (int)$order['item_count'] ?></td>
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
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body" style="text-align: center; padding: var(--spacing-2xl);">
                    <h2>No orders found</h2>
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
</body>
</html>
