<?php
require_once '../../includes/session.php';
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Invalid method');
}

$data = json_decode(file_get_contents('php://input'), true);
$cart_id = $data['cart_id'] ?? null;
$user_id = $_SESSION['user_id'];

if (empty($cart_id)) {
    json_response(false, 'Invalid cart ID');
}

try {
    // Verify cart belongs to user
    $stmt = $pdo->prepare("SELECT user_id FROM cart WHERE id = ?");
    $stmt->execute([$cart_id]);
    $cart = $stmt->fetch();
    
    if (!$cart || $cart['user_id'] != $user_id) {
        json_response(false, 'Unauthorized');
    }
    
    // Delete from cart
    $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ?");
    $stmt->execute([$cart_id]);
    
    json_response(true, 'Item removed from cart');
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    json_response(false, 'Database error');
}
?>
