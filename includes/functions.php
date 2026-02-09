<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Sanitize input data
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Generate CSRF token
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 */
function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Redirect to a URL
 */
function redirect($url) {
    header("Location: " . SITE_URL . $url);
    exit();
}

/**
 * Generate order number
 */
function generate_order_number() {
    return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

/**
 * Format price with currency
 */
function format_price($price) {
    return 'TSh ' . number_format($price, 2);
}

/**
 * Get user by ID
 */
function get_user_by_id($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

/**
 * Check if username exists
 */
function username_exists($username) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    return $stmt->fetch() !== false;
}

/**
 * Check if email exists
 */
function email_exists($email) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch() !== false;
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Require authentication
 */
function require_auth() {
    if (!is_logged_in()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        redirect('login.php');
    }
}

/**
 * Require admin access
 */
function require_admin() {
    require_auth();
    if (!is_admin()) {
        $_SESSION['error'] = 'Access denied. Admin privileges required.';
        // Redirect non-admins to their own dashboard
        redirect('dashboard.php');
    }
}

/**
 * Get user notifications
 */
function get_user_notifications($user_id, $limit = 10) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT * FROM notifications 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT ?
    ");
    $stmt->execute([$user_id, $limit]);
    return $stmt->fetchAll();
}

/**
 * Get unread notification count
 */
function get_unread_notification_count($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count FROM notifications 
        WHERE user_id = ? AND is_read = 0
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetch()['count'];
}

/**
 * Add notification
 */
function add_notification($user_id, $title, $message, $type = 'system', $reference_id = null) {
    global $pdo;
    $stmt = $pdo->prepare("
        INSERT INTO notifications (user_id, title, message, type, reference_id) 
        VALUES (?, ?, ?, ?, ?)
    ");
    return $stmt->execute([$user_id, $title, $message, $type, $reference_id]);
}

/**
 * JSON response helper
 */
function json_response($success, $message = '', $data = []) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

/**
 * Upload file
 */
function upload_file($file, $directory = 'products') {
    $target_dir = UPLOAD_PATH . $directory . '/';
    
    // Create directory if it doesn't exist
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    
    $filename = time() . '_' . basename($file['name']);
    $target_file = $target_dir . $filename;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Check if image file is actual image
    $check = getimagesize($file['tmp_name']);
    if ($check === false) {
        return ['success' => false, 'message' => 'File is not an image.'];
    }
    
    // Check file size (5MB max)
    if ($file['size'] > 5000000) {
        return ['success' => false, 'message' => 'File is too large.'];
    }
    
    // Allow certain file formats
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($imageFileType, $allowed_types)) {
        return ['success' => false, 'message' => 'Only JPG, JPEG, PNG & GIF files are allowed.'];
    }
    
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return ['success' => true, 'filename' => $filename];
    } else {
        return ['success' => false, 'message' => 'Error uploading file.'];
    }
}

/**
 * Get pagination links
 */
function get_pagination_links($current_page, $total_pages, $url) {
    $links = '<div class="pagination">';
    
    if ($current_page > 1) {
        $links .= '<a href="' . $url . '?page=' . ($current_page - 1) . '">&laquo; Previous</a>';
    }
    
    for ($i = 1; $i <= $total_pages; $i++) {
        if ($i == $current_page) {
            $links .= '<a href="' . $url . '?page=' . $i . '" class="active">' . $i . '</a>';
        } else {
            $links .= '<a href="' . $url . '?page=' . $i . '">' . $i . '</a>';
        }
    }
    
    if ($current_page < $total_pages) {
        $links .= '<a href="' . $url . '?page=' . ($current_page + 1) . '">Next &raquo;</a>';
    }
    
    $links .= '</div>';
    return $links;
}
?>
