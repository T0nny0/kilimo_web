<?php
require_once 'includes/session.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Require farmer authentication
require_auth();

$user_id = $_SESSION['user_id'];
$user = get_user_by_id($user_id);

// Get statistics
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM orders WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_orders = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM orders WHERE user_id = ? AND status = 'pending'");
$stmt->execute([$user_id]);
$pending_orders = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM cart WHERE user_id = ?");
$stmt->execute([$user_id]);
$cart_count = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$user_id]);
$unread_notifications = $stmt->fetch()['total'];

// Get unread notifications
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$user_id]);
$recent_notifications = $stmt->fetchAll();

// Get recent orders
$stmt = $pdo->prepare("
    SELECT o.*, COUNT(oi.id) as item_count 
    FROM orders o 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    WHERE o.user_id = ? 
    GROUP BY o.id 
    ORDER BY o.order_date DESC LIMIT 5
");
$stmt->execute([$user_id]);
$recent_orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Dashboard - KilimoSafi</title>
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
                    <a href="cart.php" class="btn btn-outline">Cart (<?= $cart_count ?>)</a>
                    <div class="user-menu">
                        <button class="user-menu-toggle" onclick="toggleUserMenu()">
                            👤 <?= htmlspecialchars($user['full_name']) ?>
                        </button>
                        <div class="dropdown-menu" id="userMenu">
                            <a href="profile.php">Profile</a>
                            <a href="notifications.php">Notifications (<?= $unread_notifications ?>)</a>
                            <a href="api/logout.php">Logout</a>
                        </div>
                    </div>
                </div>
            </nav>
        </div>
    </header>

    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="container">
            <h1>Welcome, <?= htmlspecialchars($user['full_name']) ?>! 🌾</h1>
            <p>Your agricultural inputs marketplace dashboard</p>
        </div>
    </div>

    <!-- Main Content -->
    <main class="container" style="padding: var(--spacing-xl) 0;">
        <!-- Statistics -->
        <div class="dashboard-stats">
            <div class="stat-card">
                <h3>Total Orders</h3>
                <div class="stat-value"><?= $total_orders ?></div>
                <a href="orders.php" style="color: var(--secondary-green);">View Orders →</a>
            </div>

            <div class="stat-card">
                <h3>Pending Orders</h3>
                <div class="stat-value text-warning"><?= $pending_orders ?></div>
                <a href="orders.php?status=pending" style="color: var(--secondary-green);">Check Status →</a>
            </div>

            <div class="stat-card">
                <h3>Shopping Cart</h3>
                <div class="stat-value text-info"><?= $cart_count ?></div>
                <a href="cart.php" style="color: var(--secondary-green);">View Cart →</a>
            </div>

            <div class="stat-card">
                <h3>Notifications</h3>
                <div class="stat-value"><?= $unread_notifications ?></div>
                <a href="notifications.php" style="color: var(--secondary-green);">View All →</a>
            </div>
        </div>

        <!-- Recent Notifications -->
        <?php if (!empty($recent_notifications)): ?>
            <div class="card" style="margin-bottom: var(--spacing-xl);">
                <div class="card-header">
                    <h2 style="margin: 0;">Recent Notifications</h2>
                </div>
                <div class="card-body">
                    <div class="notifications-list">
                        <?php foreach ($recent_notifications as $notification): ?>
                            <div class="notification-item unread">
                                <div class="notification-content">
                                    <h4><?= htmlspecialchars($notification['title']) ?></h4>
                                    <p><?= htmlspecialchars($notification['message']) ?></p>
                                </div>
                                <div class="notification-time">
                                    <?= date('M d, H:i', strtotime($notification['created_at'])) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div style="text-align: center; margin-top: var(--spacing-md);">
                        <a href="notifications.php" class="btn btn-secondary">View All Notifications</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

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
                                    <td><?= date('M d, Y', strtotime($order['order_date'])) ?></td>
                                    <td><?= $order['item_count'] ?> items</td>
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
                <?php else: ?>
                    <p style="text-align: center; color: var(--light-text);">You have no orders yet.</p>
                    <div style="text-align: center; margin-top: var(--spacing-lg);">
                        <a href="products.php" class="btn btn-primary">Start Shopping</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div style="margin-top: var(--spacing-xl); text-align: center;">
            <h3>What would you like to do?</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--spacing-lg); margin-top: var(--spacing-lg);">
                <a href="products.php" class="card" style="text-decoration: none; text-align: center; padding: var(--spacing-xl);">
                    <div style="font-size: 2rem; margin-bottom: var(--spacing-md);">🛒</div>
                    <h4>Browse Products</h4>
                    <p>Explore agricultural inputs</p>
                </a>
                <a href="cart.php" class="card" style="text-decoration: none; text-align: center; padding: var(--spacing-xl);">
                    <div style="font-size: 2rem; margin-bottom: var(--spacing-md);">🛍️</div>
                    <h4>My Cart</h4>
                    <p>Review your selections</p>
                </a>
                <a href="orders.php" class="card" style="text-decoration: none; text-align: center; padding: var(--spacing-xl);">
                    <div style="font-size: 2rem; margin-bottom: var(--spacing-md);">📦</div>
                    <h4>Track Orders</h4>
                    <p>Monitor order status</p>
                </a>
                <a href="profile.php" class="card" style="text-decoration: none; text-align: center; padding: var(--spacing-xl);">
                    <div style="font-size: 2rem; margin-bottom: var(--spacing-md);">👤</div>
                    <h4>My Profile</h4>
                    <p>Manage account settings</p>
                </a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2024 KilimoSafi - Agricultural Inputs Platform. All rights reserved.</p>
            <p>Empowering farmers with quality agricultural inputs</p>
        </div>
    </footer>

    <script>
        function toggleUserMenu() {
            const menu = document.getElementById('userMenu');
            menu.classList.toggle('active');
            
            // Close menu when clicking outside
            document.addEventListener('click', function(event) {
                const userMenu = document.querySelector('.user-menu');
                if (!userMenu.contains(event.target)) {
                    menu.classList.remove('active');
                }
            });
        }
    </script>
</body>
</html>
