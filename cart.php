<?php
require_once 'includes/session.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

require_auth();

$user_id = $_SESSION['user_id'];

// Get cart items
$stmt = $pdo->prepare("
    SELECT c.*, p.name, p.buy_price, p.rent_price, p.unit
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
    ORDER BY c.added_at DESC
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

$total_amount = 0;
foreach ($cart_items as $item) {
    if ($item['type'] === 'buy') {
        $total_amount += ($item['buy_price'] * $item['quantity']);
    } else {
        $total_amount += ($item['rent_price'] * $item['quantity'] * ($item['rent_days'] ?? 1));
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - KilimoSafi</title>
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
                    <a href="cart.php" class="btn btn-primary">Cart</a>
                </div>
            </nav>
        </div>
    </header>

    <!-- Cart Section -->
    <main class="container" style="padding: var(--spacing-xl) 0; min-height: 60vh;">
        <h1>Your Shopping Cart</h1>

        <?php if (!empty($cart_items)): ?>
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: var(--spacing-xl); margin-top: var(--spacing-xl);">
                <!-- Cart Items -->
                <div>
                    <div class="card">
                        <div class="card-header">
                            <h2 style="margin: 0;">Cart Items (<?= count($cart_items) ?>)</h2>
                        </div>
                        <div class="card-body">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Type</th>
                                        <th>Price</th>
                                        <th>Qty</th>
                                        <th>Subtotal</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cart_items as $item): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($item['name']) ?></td>
                                            <td>
                                                <span class="badge <?= $item['type'] === 'buy' ? 'badge-success' : 'badge-info' ?>">
                                                    <?= ucfirst($item['type']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php 
                                                if ($item['type'] === 'buy') {
                                                    echo format_price($item['buy_price']);
                                                } else {
                                                    echo format_price($item['rent_price']) . '/day';
                                                    if ($item['rent_days']) {
                                                        echo ' × ' . $item['rent_days'] . ' days';
                                                    }
                                                }
                                                ?>
                                            </td>
                                            <td><?= (int)$item['quantity'] . ' ' . htmlspecialchars($item['unit']) ?></td>
                                            <td>
                                                <?php 
                                                if ($item['type'] === 'buy') {
                                                    $subtotal = $item['buy_price'] * $item['quantity'];
                                                } else {
                                                    $subtotal = $item['rent_price'] * $item['quantity'] * ($item['rent_days'] ?? 1);
                                                }
                                                echo format_price($subtotal);
                                                ?>
                                            </td>
                                            <td>
                                                <button onclick="removeFromCart(<?= $item['id'] ?>)" class="btn btn-sm btn-danger">Remove</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div>
                    <div class="card" style="position: sticky; top: var(--spacing-xl);">
                        <div class="card-header">
                            <h3 style="margin: 0;">Order Summary</h3>
                        </div>
                        <div class="card-body">
                            <div style="display: flex; justify-content: space-between; margin-bottom: var(--spacing-md); padding-bottom: var(--spacing-md); border-bottom: 1px solid var(--border-gray);">
                                <span>Subtotal:</span>
                                <span><?= format_price($total_amount) ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: var(--spacing-md); padding-bottom: var(--spacing-md); border-bottom: 1px solid var(--border-gray);">
                                <span>Delivery:</span>
                                <span>Calculated at checkout</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 1.25rem; font-weight: bold; color: var(--secondary-green); margin-bottom: var(--spacing-lg);">
                                <span>Total:</span>
                                <span><?= format_price($total_amount) ?></span>
                            </div>
                            <button onclick="window.location.href='checkout.php'" class="btn btn-primary btn-block btn-lg">
                                Proceed to Checkout
                            </button>
                            <button onclick="window.location.href='products.php'" class="btn btn-secondary btn-block" style="margin-top: var(--spacing-md);">
                                Continue Shopping
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: var(--spacing-2xl);">
                <div style="font-size: 4rem; margin-bottom: var(--spacing-lg);">🛒</div>
                <h2>Your cart is empty</h2>
                <p style="color: var(--light-text); margin-bottom: var(--spacing-lg);">Start shopping by browsing our agricultural inputs</p>
                <a href="products.php" class="btn btn-primary btn-lg">Browse Products</a>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2024 KilimoSafi - Agricultural Inputs Platform. All rights reserved.</p>
        </div>
    </footer>

    <script>
        function removeFromCart(cartId) {
            if (confirm('Remove this item from cart?')) {
                fetch('api/cart/remove.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ cart_id: cartId })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(err => alert('Error: ' + err));
            }
        }
    </script>
</body>
</html>
