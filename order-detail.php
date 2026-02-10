<?php
require_once 'includes/session.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

require_auth();

$order_id = $_GET['id'] ?? 0;
$is_new = $_GET['new'] ?? 0;
$user_id = $_SESSION['user_id'];
$is_admin = $_SESSION['role'] === 'admin';

// Get order details
$stmt = $pdo->prepare("
    SELECT o.*, u.username, u.email, u.phone as user_phone, u.full_name
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    http_response_code(404);
    echo "Order not found";
    exit;
}

// Check authorization
if (!$is_admin && $order['user_id'] !== $user_id) {
    http_response_code(403);
    echo "Unauthorized";
    exit;
}

// Get order items
$stmt = $pdo->prepare("
    SELECT oi.*, p.name, p.unit
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();

// Get status history
$stmt = $pdo->prepare("
    SELECT osh.*, u.username
    FROM order_status_history osh
    LEFT JOIN users u ON osh.changed_by = u.id
    WHERE osh.order_id = ?
    ORDER BY osh.created_at DESC
");
$stmt->execute([$order_id]);
$status_history = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?= htmlspecialchars($order['order_number']) ?> - Safi Kilimo</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        .order-detail-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid #eee;
        }
        
        @media (max-width: 768px) {
            .order-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }
        
        .order-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        @media (max-width: 768px) {
            .order-info {
                grid-template-columns: 1fr;
            }
        }
        
        .info-card {
            background: #f9f9f9;
            padding: 1.5rem;
            border-radius: 8px;
            border-left: 4px solid #2d6a4f;
        }
        
        .info-card h3 {
            margin: 0 0 1rem 0;
            font-size: 1rem;
            color: #2d6a4f;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
        }
        
        .info-item label {
            color: #666;
            font-weight: 500;
        }
        
        .info-item span {
            font-weight: 600;
            color: #333;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-approved {
            background: #cfe2ff;
            color: #084298;
        }
        
        .status-completed {
            background: #d1e7dd;
            color: #0f5132;
        }
        
        .status-rejected {
            background: #f8d7da;
            color: #842029;
        }
        
        .items-section {
            background: white;
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .items-section h3 {
            margin: 0 0 1.5rem 0;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .items-table thead {
            background: #f9f9f9;
            border-bottom: 2px solid #ddd;
        }
        
        .items-table th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #333;
        }
        
        .items-table td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }
        
        .items-table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .items-summary {
            display: flex;
            justify-content: flex-end;
            gap: 2rem;
            padding-top: 1rem;
            border-top: 2px solid #eee;
            margin-top: 1rem;
        }
        
        .summary-item {
            display: flex;
            gap: 1rem;
        }
        
        .summary-item label {
            color: #666;
            font-weight: 500;
        }
        
        .summary-item span {
            font-weight: 700;
            color: #2d6a4f;
            min-width: 80px;
            text-align: right;
        }
        
        .timeline {
            background: white;
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .timeline h3 {
            margin: 0 0 1.5rem 0;
        }
        
        .timeline-item {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
            position: relative;
            padding-left: 0;
        }
        
        .timeline-item:last-child {
            margin-bottom: 0;
        }
        
        .timeline-marker {
            width: 40px;
            height: 40px;
            background: #2d6a4f;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            flex-shrink: 0;
            font-size: 1.2rem;
        }
        
        .timeline-content {
            flex: 1;
            padding-top: 0.25rem;
        }
        
        .timeline-date {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 0.25rem;
        }
        
        .timeline-status {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.25rem;
        }
        
        .timeline-note {
            font-size: 0.9rem;
            color: #666;
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            font-size: 0.95rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #2d6a4f;
            color: white;
        }
        
        .btn-primary:hover {
            background: #1d5a3f;
        }
        
        .btn-secondary {
            background: #e0e0e0;
            color: #333;
        }
        
        .btn-secondary:hover {
            background: #d0d0d0;
        }
        
        .btn-success {
            background: #20c997;
            color: white;
        }
        
        .btn-success:hover {
            background: #1aa179;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .success-message {
            background: #d1e7dd;
            border: 1px solid #badbcc;
            color: #0f5132;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        @media (max-width: 768px) {
            .items-table {
                font-size: 0.85rem;
            }
            
            .items-table th,
            .items-table td {
                padding: 0.75rem 0.5rem;
            }
            
            .items-summary {
                flex-direction: column;
                gap: 0.75rem;
            }
        }
    </style>
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
    
    <main class="order-detail-container">
        <?php if ($is_new): ?>
        <div class="success-message">
            ✓ Your order has been placed successfully! We'll notify you once it's approved.
        </div>
        <?php endif; ?>
        
        <div class="order-header">
            <div>
                <h1 style="margin: 0 0 0.5rem 0;">Order #<?= htmlspecialchars($order['order_number']) ?></h1>
                <p style="margin: 0; color: #666; font-size: 0.95rem;">
                    Placed on <?= date('M d, Y at g:i A', strtotime($order['order_date'])) ?>
                </p>
            </div>
            <span class="status-badge status-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span>
        </div>
        
        <?php if ($is_admin): ?>
        <div class="order-info">
            <div class="info-card">
                <h3>Customer Information</h3>
                <div class="info-item">
                    <label>Name:</label>
                    <span><?= htmlspecialchars($order['full_name']) ?></span>
                </div>
                <div class="info-item">
                    <label>Username:</label>
                    <span><?= htmlspecialchars($order['username']) ?></span>
                </div>
                <div class="info-item">
                    <label>Email:</label>
                    <span><?= htmlspecialchars($order['email']) ?></span>
                </div>
                <div class="info-item">
                    <label>Phone:</label>
                    <span><?= htmlspecialchars($order['user_phone']) ?></span>
                </div>
            </div>
            
            <div class="info-card">
                <h3>Delivery Information</h3>
                <div class="info-item">
                    <label>Address:</label>
                    <span><?= htmlspecialchars($order['delivery_address']) ?></span>
                </div>
                <div class="info-item">
                    <label>Contact Phone:</label>
                    <span><?= htmlspecialchars($order['contact_phone']) ?></span>
                </div>
                <div class="info-item">
                    <label>Total Amount:</label>
                    <span><?= format_price($order['total_amount']) ?></span>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="order-info">
            <div class="info-card">
                <h3>Order Summary</h3>
                <div class="info-item">
                    <label>Order Date:</label>
                    <span><?= date('M d, Y', strtotime($order['order_date'])) ?></span>
                </div>
                <div class="info-item">
                    <label>Total Items:</label>
                    <span><?= count($items) ?></span>
                </div>
                <div class="info-item">
                    <label>Status:</label>
                    <span class="status-badge status-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span>
                </div>
            </div>
            
            <div class="info-card">
                <h3>Delivery Details</h3>
                <div class="info-item">
                    <label>Address:</label>
                    <span><?= htmlspecialchars($order['delivery_address']) ?></span>
                </div>
                <div class="info-item">
                    <label>Contact Phone:</label>
                    <span><?= htmlspecialchars($order['contact_phone']) ?></span>
                </div>
                <div class="info-item">
                    <label>Total Amount:</label>
                    <span><?= format_price($order['total_amount']) ?></span>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="items-section">
            <h3>Order Items</h3>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th style="text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td>
                            <span class="status-badge status-<?= $item['type'] === 'buy' ? 'approved' : 'pending' ?>">
                                <?= ucfirst($item['type']) ?>
                                <?php if ($item['type'] === 'rent'): ?>
                                    (<?= $item['rent_days'] ?> days)
                                <?php endif; ?>
                            </span>
                        </td>
                        <td><?= $item['quantity'] ?> × <?= htmlspecialchars($item['unit']) ?></td>
                        <td><?= format_price($item['price']) ?></td>
                        <td style="text-align: right; font-weight: 600;"><?= format_price($item['total_price']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="items-summary">
                <div class="summary-item">
                    <label>Total:</label>
                    <span><?= format_price($order['total_amount']) ?></span>
                </div>
            </div>
        </div>
        
        <?php if (!empty($status_history)): ?>
        <div class="timeline">
            <h3>Order Timeline</h3>
            <?php foreach ($status_history as $index => $history): ?>
            <div class="timeline-item">
                <div class="timeline-marker">
                    <?php
                    $status_icons = [
                        'pending' => '⏳',
                        'approved' => '✓',
                        'completed' => '✓✓',
                        'rejected' => '✗'
                    ];
                    echo $status_icons[$history['status']] ?? $index + 1;
                    ?>
                </div>
                <div class="timeline-content">
                    <div class="timeline-date"><?= date('M d, Y at g:i A', strtotime($history['created_at'])) ?></div>
                    <div class="timeline-status">Status changed to <?= ucfirst($history['status']) ?></div>
                    <?php if (!empty($history['notes'])): ?>
                    <div class="timeline-note">
                        <?= htmlspecialchars($history['notes']) ?>
                        <?php if ($history['changed_by']): ?>
                        <br><small>by <?= htmlspecialchars($history['username']) ?></small>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <?php if ($is_admin && $order['status'] === 'pending'): ?>
        <div class="action-buttons">
            <button class="btn btn-success" onclick="updateOrderStatus('approved', 'Order approved')">
                ✓ Approve Order
            </button>
            <button class="btn btn-danger" onclick="updateOrderStatus('rejected', 'Order rejected')">
                ✗ Reject Order
            </button>
        </div>
        <?php elseif ($is_admin && $order['status'] === 'approved'): ?>
        <div class="action-buttons">
            <button class="btn btn-success" onclick="updateOrderStatus('completed', 'Order completed')">
                ✓ Mark as Completed
            </button>
        </div>
        <?php endif; ?>
        
        <div class="action-buttons" style="margin-bottom: 2rem;">
            <a href="<?= $is_admin ? 'admin/orders.php' : 'orders.php' ?>" class="btn btn-secondary">
                ← Back to Orders
            </a>
        </div>
    </main>
    
    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2024 KilimoSafi - Agricultural Inputs Platform. All rights reserved.</p>
        </div>
    </footer>
    
    <script>
        async function updateOrderStatus(status, notes) {
            if (!confirm(`Mark order as ${status}?`)) return;
            
            try {
                const response = await fetch('api/admin/order-status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        order_id: <?= $order_id ?>,
                        status: status,
                        notes: notes
                    })
                });
                
                const data = await response.json();
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error updating order');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Network error');
            }
        }
    </script>
</body>
</html>
