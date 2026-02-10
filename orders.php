<?php
require_once 'includes/session.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

require_auth();

$user_id = $_SESSION['user_id'];
$status_filter = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';

// Build query
$query = "SELECT * FROM orders WHERE user_id = ?";
$params = [$user_id];

if (!empty($status_filter)) {
    $query .= " AND status = ?";
    $params[] = $status_filter;
}

$query .= " ORDER BY order_date DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - KilimoSafi</title>
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <nav>
                <a href="index.php" class="logo">Kilimo<span>Safi</span></a>
                <div class="nav-links">
                    <a href="dashboard.php">Dashboard</a>
                    <a href="products.php">Products</a>
                    <a href="orders.php">My Orders</a>
                    <a href="cart.php" class="btn btn-outline">Cart</a>
                </div>
            </nav>
        </div>
    </header>

    <!-- Orders Section -->
    <main class="container" style="padding: var(--spacing-xl) 0;">
        <h1>My Orders</h1>

        <!-- Filters -->
        <div style="margin-bottom: var(--spacing-lg); display: flex; gap: var(--spacing-md); flex-wrap: wrap;">
            <a href="orders.php" class="btn <?= empty($status_filter) ? 'btn-primary' : 'btn-secondary' ?>">All Orders</a>
            <a href="orders.php?status=pending" class="btn <?= $status_filter === 'pending' ? 'btn-primary' : 'btn-secondary' ?>">Pending</a>
            <a href="orders.php?status=approved" class="btn <?= $status_filter === 'approved' ? 'btn-primary' : 'btn-secondary' ?>">Approved</a>
            <a href="orders.php?status=completed" class="btn <?= $status_filter === 'completed' ? 'btn-primary' : 'btn-secondary' ?>">Completed</a>
            <a href="orders.php?status=rejected" class="btn <?= $status_filter === 'rejected' ? 'btn-primary' : 'btn-secondary' ?>">Rejected</a>
        </div>

        <?php if (!empty($orders)): ?>
            <div class="card">
                <div class="card-body" style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Date</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Updated</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><?= htmlspecialchars($order['order_number']) ?></td>
                                    <td><?= date('M d, Y H:i', strtotime($order['order_date'])) ?></td>
                                    <td><?= format_price($order['total_amount']) ?></td>
                                    <td>
                                        <span class="badge badge-<?= strtolower($order['status']) ?>">
                                            <?= ucfirst($order['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($order['updated_at'])) ?></td>
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
                    <div style="font-size: 3rem; margin-bottom: var(--spacing-lg);">📦</div>
                    <h2>No orders found</h2>
                    <p style="color: var(--light-text);">
                        <?= !empty($status_filter) ? 'No ' . htmlspecialchars($status_filter) . ' orders' : 'You haven\'t placed any orders yet' ?>
                    </p>
                    <a href="products.php" class="btn btn-primary" style="margin-top: var(--spacing-lg);">Start Shopping</a>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2024 KilimoSafi - Agricultural Inputs Platform. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
