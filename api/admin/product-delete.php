<?php
require_once '../../includes/session.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Invalid request method');
}

$data = json_decode(file_get_contents('php://input'), true);
$product_id = intval($data['product_id'] ?? 0);

if (!$product_id) {
    json_response(false, 'Product ID is required');
}

try {
    // Check if product exists
    $stmt = $pdo->prepare("SELECT id FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    if (!$stmt->fetch()) {
        json_response(false, 'Product not found');
    }
    
    // Delete product
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    
    json_response(true, 'Product deleted successfully');
} catch (Exception $e) {
    error_log("Product Delete Error: " . $e->getMessage());
    json_response(false, 'Failed to delete product');
}
?>
