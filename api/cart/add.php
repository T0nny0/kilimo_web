<?php
require_once '../../includes/session.php';
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Invalid request method');
}

$data = json_decode(file_get_contents('php://input'), true);

$product_id = intval($data['product_id'] ?? 0);
$quantity = intval($data['quantity'] ?? 1);
$type = sanitize_input($data['type'] ?? 'buy');
$rent_days = intval($data['rent_days'] ?? 0);

if (!$product_id || $quantity <= 0) {
    json_response(false, 'Invalid product ID or quantity');
}

if (!in_array($type, ['buy', 'rent'])) {
    json_response(false, 'Invalid purchase type');
}

$user_id = $_SESSION['user_id'];

try {
    // Check if product exists and is available
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        json_response(false, 'Product not found');
    }
    
    if ($product['is_available'] == 0) {
        json_response(false, 'Product is not available');
    }
    
    if ($type === 'buy' && $product['quantity_available'] < $quantity) {
        json_response(false, 'Insufficient stock available');
    }
    
    // Check if item already in cart
    $stmt = $pdo->prepare("
        SELECT id, quantity FROM cart 
        WHERE user_id = ? AND product_id = ? AND type = ? AND (rent_days <=> ? OR (rent_days IS NULL AND ? IS NULL))
    ");
    $stmt->execute([$user_id, $product_id, $type, $rent_days]);
    $existing_item = $stmt->fetch();
    
    if ($existing_item) {
        // Update quantity
        $new_quantity = $existing_item['quantity'] + $quantity;
        
        // Check stock for buy items
        if ($type === 'buy' && $product['quantity_available'] < $new_quantity) {
            json_response(false, 'Cannot add. Would exceed available stock');
        }
        
        $update_stmt = $pdo->prepare("
            UPDATE cart SET quantity = ?, added_at = NOW() 
            WHERE id = ?
        ");
        $update_stmt->execute([$new_quantity, $existing_item['id']]);
        
        json_response(true, 'Cart updated successfully', [
            'action' => 'updated',
            'quantity' => $new_quantity
        ]);
    } else {
        // Add new item to cart
        $insert_stmt = $pdo->prepare("
            INSERT INTO cart (user_id, product_id, quantity, type, rent_days, added_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $insert_stmt->execute([$user_id, $product_id, $quantity, $type, $rent_days ?? null]);
        
        json_response(true, 'Item added to cart successfully', [
            'action' => 'added'
        ]);
    }
} catch (PDOException $e) {
    error_log("Cart Add Error: " . $e->getMessage());
    json_response(false, 'Database error occurred');
}
?>

