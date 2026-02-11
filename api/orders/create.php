<?php
require_once '../../includes/session.php';
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Invalid request method');
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

$delivery_address = sanitize_input($data['delivery_address'] ?? '');
$contact_phone = sanitize_input($data['contact_phone'] ?? '');
$notes = sanitize_input($data['notes'] ?? '');

// Validate
if (empty($delivery_address) || empty($contact_phone)) {
    json_response(false, 'Delivery address and phone are required');
}

try {
    // Get cart items
    $stmt = $pdo->prepare("
        SELECT c.*, p.buy_price, p.rent_price
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll();
    
    if (empty($cart_items)) {
        json_response(false, 'Cart is empty');
    }
    
    // Calculate total
    $total_amount = 0;
    foreach ($cart_items as $item) {
        if ($item['type'] === 'buy') {
            $total_amount += ($item['buy_price'] * $item['quantity']);
        } else {
            $total_amount += ($item['rent_price'] * $item['quantity'] * ($item['rent_days'] ?? 1));
        }
    }
    
    // Create order
    $order_number = generate_order_number();
    $stmt = $pdo->prepare("
        INSERT INTO orders 
        (order_number, user_id, total_amount, status, delivery_address, contact_phone, notes)
        VALUES (?, ?, ?, 'pending', ?, ?, ?)
    ");
    $stmt->execute([$order_number, $user_id, $total_amount, $delivery_address, $contact_phone, $notes]);
    $order_id = $pdo->lastInsertId();
    
    // Add order items
    foreach ($cart_items as $item) {
        if ($item['type'] === 'buy') {
            $price = $item['buy_price'];
            $total_price = $price * $item['quantity'];
        } else {
            $price = $item['rent_price'];
            $total_price = $price * $item['quantity'] * ($item['rent_days'] ?? 1);
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO order_items 
            (order_id, product_id, quantity, price, type, rent_days, total_price)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $price, $item['type'], $item['rent_days'], $total_price]);
    }
    
    // Clear cart
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    
    // Add notification to admin
    $stmt = $pdo->prepare("
        SELECT id FROM users WHERE role = 'admin' LIMIT 1
    ");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if ($admin) {
        add_notification($admin['id'], 'New Order', "New order #$order_number placed by user", 'order', $order_id);
    }
    
    // Add notification to user
    add_notification($user_id, 'Order Placed', "Your order #$order_number has been placed successfully and is pending approval", 'order', $order_id);
    
    json_response(true, 'Order created successfully', ['order_id' => $order_id, 'order_number' => $order_number]);
} catch (Exception $e) {
    error_log("Order Creation Error: " . $e->getMessage());
    json_response(false, 'Failed to create order');
}
?>
