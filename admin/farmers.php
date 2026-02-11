<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_admin();

// Get all farmers
$stmt = $pdo->query("
    SELECT u.*, 
           COUNT(DISTINCT o.id) as total_orders,
           COALESCE(SUM(o.total_amount), 0) as total_spent
    FROM users u
    LEFT JOIN orders o ON u.id = o.user_id AND o.status = 'completed'
    WHERE u.role = 'farmer'
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
$farmers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Farmers - KilimoSafi Admin</title>
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
        <h1>Manage Farmers</h1>

        <?php if (!empty($farmers)): ?>
            <div class="card">
                <div class="card-body" style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Location</th>
                                <th>Orders</th>
                                <th>Total Spent</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($farmers as $farmer): ?>
                                <tr>
                                    <td><?= htmlspecialchars($farmer['full_name']) ?></td>
                                    <td><?= htmlspecialchars($farmer['email']) ?></td>
                                    <td><?= htmlspecialchars($farmer['phone'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars(substr($farmer['address'] ?? '-', 0, 30)) ?></td>
                                    <td><?= (int)$farmer['total_orders'] ?></td>
                                    <td><?= format_price($farmer['total_spent']) ?></td>
                                    <td><?= date('M d, Y', strtotime($farmer['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body" style="text-align: center; padding: var(--spacing-2xl);">
                    <h2>No farmers registered yet</h2>
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
