<?php
require_once '../../includes/session.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Invalid request method');
}

$data = json_decode(file_get_contents('php://input'), true);

$order_id = intval($data['order_id'] ?? 0);
$status = sanitize_input($data['status'] ?? '');
$notes = sanitize_input($data['notes'] ?? '');

// Validate
if (!$order_id) {
    json_response(false, 'Order ID is required');
}

$valid_statuses = ['pending', 'approved', 'rejected', 'completed', 'cancelled'];
if (!in_array($status, $valid_statuses)) {
    json_response(false, 'Invalid status');
}

try {
    // Get current order
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    
    if (!$order) {
        json_response(false, 'Order not found');
    }
    
    // Update order status
    $stmt = $pdo->prepare("
        UPDATE orders 
        SET status = ?, updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$status, $order_id]);
    
    // Add to status history
    $stmt = $pdo->prepare("
        INSERT INTO order_status_history 
        (order_id, status, notes, changed_by, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$order_id, $status, $notes, $_SESSION['user_id']]);
    
    // Send notification to farmer
    add_notification($order['user_id'], 'Order Status Updated', 
        "Your order #{$order['order_number']} status changed to " . ucfirst($status),
        'order', $order_id);
    
    json_response(true, 'Order status updated successfully');
} catch (Exception $e) {
    error_log("Order Status Update Error: " . $e->getMessage());
    json_response(false, 'Failed to update order status');
}
?>
