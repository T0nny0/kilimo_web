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
$name = sanitize_input($data['name'] ?? '');
$category_id = intval($data['category_id'] ?? 0);
$description = sanitize_input($data['description'] ?? '');
$unit = sanitize_input($data['unit'] ?? '');
$buy_price = floatval($data['buy_price'] ?? 0);
$rent_price = floatval($data['rent_price'] ?? 0);
$quantity_available = intval($data['quantity_available'] ?? 0);
$min_rent_days = intval($data['min_rent_days'] ?? 1);
$image_url = sanitize_input($data['image_url'] ?? '');
$is_available = intval($data['is_available'] ?? 1);

// Validate required fields
if (empty($name) || $category_id <= 0 || empty($description) || empty($unit) || $buy_price <= 0) {
    json_response(false, 'Please fill in all required fields');
}

try {
    // Check if category exists
    $stmt = $pdo->prepare("SELECT id FROM categories WHERE id = ?");
    $stmt->execute([$category_id]);
    if (!$stmt->fetch()) {
        json_response(false, 'Category not found');
    }
    
    if ($product_id) {
        // Update existing product
        $stmt = $pdo->prepare("
            UPDATE products 
            SET name = ?, category_id = ?, description = ?, unit = ?,
                buy_price = ?, rent_price = ?, quantity_available = ?,
                min_rent_days = ?, image_url = ?, is_available = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([
            $name, $category_id, $description, $unit,
            $buy_price, $rent_price, $quantity_available,
            $min_rent_days, $image_url, $is_available, $product_id
        ]);
        $message = 'Product updated successfully';
    } else {
        // Insert new product
        $stmt = $pdo->prepare("
            INSERT INTO products 
            (name, category_id, description, unit, buy_price, rent_price, 
             quantity_available, min_rent_days, image_url, is_available, created_by, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([
            $name, $category_id, $description, $unit, $buy_price, $rent_price,
            $quantity_available, $min_rent_days, $image_url, $is_available, $_SESSION['user_id']
        ]);
        $message = 'Product added successfully';
    }
    
    json_response(true, $message);
} catch (Exception $e) {
    error_log("Product Save Error: " . $e->getMessage());
    json_response(false, 'Failed to save product: ' . $e->getMessage());
}
?>
