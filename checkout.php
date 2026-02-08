<?php
require_once 'includes/session.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

require_auth();

$user_id = $_SESSION['user_id'];

// Get cart items with product details
$stmt = $pdo->prepare("
    SELECT c.*, p.id as product_id, p.name, p.buy_price, p.rent_price, p.unit
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
    ORDER BY c.added_at DESC
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

if (empty($cart_items)) {
    redirect('cart.php');
}

// Calculate total
$total_amount = 0;
foreach ($cart_items as $item) {
    if ($item['type'] === 'buy') {
        $item_total = $item['buy_price'] * $item['quantity'];
    } else {
        $item_total = $item['rent_price'] * $item['quantity'] * ($item['rent_days'] ?? 1);
    }
    $total_amount += $item_total;
}

// Get user info for pre-fill
$stmt = $pdo->prepare("SELECT phone, address FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Safi Kilimo</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        .checkout-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 1rem;
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 2rem;
        }
        
        @media (max-width: 768px) {
            .checkout-container {
                grid-template-columns: 1fr;
            }
            .order-summary {
                order: -1;
            }
        }
        
        .order-summary {
            background: #f9f9f9;
            border-radius: 8px;
            padding: 1.5rem;
            height: fit-content;
            position: sticky;
            top: 20px;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
            font-size: 0.95rem;
        }
        
        .summary-item:last-child {
            border: none;
            padding-bottom: 0;
        }
        
        .summary-total {
            display: flex;
            justify-content: space-between;
            font-size: 1.3rem;
            font-weight: bold;
            color: #2d6a4f;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 2px solid #2d6a4f;
        }
        
        .checkout-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #333;
        }
        
        .form-group input,
        .form-group textarea {
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            font-family: inherit;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #2d6a4f;
            box-shadow: 0 0 0 3px rgba(45, 106, 79, 0.1);
        }
        
        .cart-items-review {
            background: white;
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .review-item {
            display: flex;
            gap: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
            align-items: center;
        }
        
        .review-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        
        .review-item-details {
            flex: 1;
        }
        
        .review-item-name {
            font-weight: 500;
            margin-bottom: 0.25rem;
        }
        
        .review-item-info {
            font-size: 0.85rem;
            color: #666;
        }
        
        .review-item-price {
            text-align: right;
            font-weight: 600;
            color: #2d6a4f;
            min-width: 80px;
        }
        
        .btn-submit {
            padding: 1rem;
            background: #2d6a4f;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn-submit:hover {
            background: #1d5a3f;
        }
        
        .btn-submit:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .btn-back {
            display: inline-block;
            color: #2d6a4f;
            text-decoration: none;
            font-weight: 500;
            margin-top: 1rem;
        }
        
        .btn-back:hover {
            text-decoration: underline;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            display: none;
        }
        
        .alert.show {
            display: block;
        }
        
        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
        
        .alert-success {
            background: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }
        
        .loading {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #fff;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
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
    
    <main class="checkout-container">
        <div>
            <h1>Checkout</h1>
            
            <div class="alert alert-error" id="alert"></div>
            
            <div class="cart-items-review">
                <h3 style="margin-top: 0; margin-bottom: 1.5rem;">Order Summary</h3>
                <?php foreach ($cart_items as $item):
                    if ($item['type'] === 'buy') {
                        $item_price = $item['buy_price'];
                        $item_total = $item_price * $item['quantity'];
                        $type_label = 'Buy';
                    } else {
                        $item_price = $item['rent_price'];
                        $days = $item['rent_days'] ?? 1;
                        $item_total = $item_price * $item['quantity'] * $days;
                        $type_label = "Rent for {$days} days";
                    }
                ?>
                <div class="review-item">
                    <div class="review-item-details">
                        <div class="review-item-name"><?= htmlspecialchars($item['name']) ?></div>
                        <div class="review-item-info">
                            Qty: <?= $item['quantity'] ?> × <?= htmlspecialchars($item['unit']) ?> | 
                            <?= $type_label ?> | 
                            Unit: <?= format_price($item_price) ?>
                        </div>
                    </div>
                    <div class="review-item-price"><?= format_price($item_total) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <form class="checkout-form" id="checkoutForm">
                <div class="form-group">
                    <label for="delivery_address">Delivery Address *</label>
                    <textarea id="delivery_address" name="delivery_address" required><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="contact_phone">Contact Phone *</label>
                    <input type="tel" id="contact_phone" name="contact_phone" required 
                           value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                           pattern="[0-9\-\+\s]+" placeholder="e.g., +254712345678">
                </div>
                
                <div class="form-group">
                    <label for="notes">Order Notes (Optional)</label>
                    <textarea id="notes" name="notes" placeholder="Any special instructions..."></textarea>
                </div>
                
                <button type="submit" class="btn-submit" id="submitBtn">
                    <span id="btnText">Place Order</span>
                </button>
            </form>
            
            <a href="cart.php" class="btn-back">← Back to Cart</a>
        </div>
        
        <div class="order-summary">
            <h3 style="margin-top: 0; margin-bottom: 1.5rem;">Order Total</h3>
            <div class="summary-item">
                <span>Subtotal</span>
                <span><?= format_price($total_amount) ?></span>
            </div>
            <div class="summary-item">
                <span>Delivery</span>
                <span>Included</span>
            </div>
            <div class="summary-total">
                <span>Total</span>
                <span><?= format_price($total_amount) ?></span>
            </div>
            <p style="font-size: 0.85rem; color: #666; margin-top: 1rem; margin-bottom: 0;">
                ✓ Free delivery on all orders<br>
                ✓ Payment on delivery accepted
            </p>
        </div>
    </main>
    
    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2024 KilimoSafi - Agricultural Inputs Platform. All rights reserved.</p>
        </div>
    </footer>
    
    <script>
        document.getElementById('checkoutForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            const alert = document.getElementById('alert');
            
            const formData = {
                delivery_address: document.getElementById('delivery_address').value,
                contact_phone: document.getElementById('contact_phone').value,
                notes: document.getElementById('notes').value
            };
            
            submitBtn.disabled = true;
            btnText.innerHTML = '<span class="loading"></span> Processing...';
            
            try {
                const response = await fetch('api/orders/create.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Redirect to order confirmation
                    window.location.href = `order-detail.php?id=${data.data.order_id}&new=1`;
                } else {
                    alert.textContent = data.message || 'Error placing order';
                    alert.classList.add('show');
                    submitBtn.disabled = false;
                    btnText.textContent = 'Place Order';
                }
            } catch (error) {
                console.error('Error:', error);
                alert.textContent = 'Network error. Please try again.';
                alert.classList.add('show');
                submitBtn.disabled = false;
                btnText.textContent = 'Place Order';
            }
        });
    </script>
</body>
</html>
