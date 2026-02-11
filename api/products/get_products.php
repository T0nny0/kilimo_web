<?php
require_once '../../includes/session.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

try {
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? min(max(1, (int)$_GET['limit']), 100) : 12;
    $offset = ($page - 1) * $limit;
    
    $category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
    $search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
    $min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : null;
    $max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : null;
    $type = isset($_GET['type']) ? sanitize_input($_GET['type']) : 'all'; // all, buy, rent
    
    // Build query
    $sql = "SELECT p.*, c.name as category_name, c.icon as category_icon 
            FROM products p 
            JOIN categories c ON p.category_id = c.id 
            WHERE p.is_available = 1";
    $params = [];
    
    if ($category_id) {
        $sql .= " AND p.category_id = ?";
        $params[] = $category_id;
    }
    
    if (!empty($search)) {
        $sql .= " AND (p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?)";
        $search_term = "%$search%";
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
    }
    
    if ($min_price !== null) {
        if ($type === 'buy') {
            $sql .= " AND p.buy_price >= ?";
            $params[] = $min_price;
        } elseif ($type === 'rent') {
            $sql .= " AND p.rent_price >= ?";
            $params[] = $min_price;
        } else {
            $sql .= " AND (p.buy_price >= ? OR p.rent_price >= ?)";
            $params[] = $min_price;
            $params[] = $min_price;
        }
    }
    
    if ($max_price !== null) {
        if ($type === 'buy') {
            $sql .= " AND p.buy_price <= ?";
            $params[] = $max_price;
        } elseif ($type === 'rent') {
            $sql .= " AND p.rent_price <= ?";
            $params[] = $max_price;
        } else {
            $sql .= " AND (p.buy_price <= ? OR p.rent_price <= ?)";
            $params[] = $max_price;
            $params[] = $max_price;
        }
    }
    
    // Apply type filter
    if ($type === 'buy') {
        $sql .= " AND p.buy_price IS NOT NULL";
    } elseif ($type === 'rent') {
        $sql .= " AND p.rent_price IS NOT NULL";
    }
    
    // Get total count
    $count_sql = "SELECT COUNT(*) as total FROM ($sql) as temp";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total = $count_stmt->fetch()['total'];
    
    // Add pagination and sorting
    $sort = isset($_GET['sort']) ? sanitize_input($_GET['sort']) : 'newest';
    switch ($sort) {
        case 'price_low':
            $sql .= " ORDER BY COALESCE(p.buy_price, p.rent_price) ASC";
            break;
        case 'price_high':
            $sql .= " ORDER BY COALESCE(p.buy_price, p.rent_price) DESC";
            break;
        case 'name':
            $sql .= " ORDER BY p.name ASC";
            break;
        case 'newest':
        default:
            $sql .= " ORDER BY p.created_at DESC";
            break;
    }
    
    $sql .= " LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    
    // Format response
    $response = [
        'success' => true,
        'data' => $products,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ];
    
    echo json_encode($response);
    
} catch (PDOException $e) {
    error_log("Get Products Error: " . $e->getMessage());
    json_response(false, 'Failed to fetch products');
}
?>
