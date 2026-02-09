<?php
/**
 * Secure Session Management
 * Initializes and protects session data
 */

require_once __DIR__ . '/../config/database.php';

// Set secure session configuration
ini_set('session.cookie_httponly', 1);           // Prevent JS from accessing cookies
ini_set('session.cookie_secure', 0);             // Set to 1 in production with HTTPS
ini_set('session.use_strict_mode', 1);           // Don't accept uninitialized session IDs
ini_set('session.cookie_samesite', 'Strict');    // Prevent CSRF attacks

// Set session timeout (30 minutes of inactivity)
ini_set('session.gc_maxlifetime', 1800);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Regenerate session ID for security
if (!isset($_SESSION['session_created'])) {
    session_regenerate_id(true);
    $_SESSION['session_created'] = time();
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
} else {
    // Check for session hijacking attempts
    if ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
        session_destroy();
        session_start();
        $_SESSION['error'] = 'Session security violation detected. Please login again.';
        header('Location: ' . SITE_URL . 'login.php');
        exit;
    }
}

// Check session timeout
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > 1800) {
    session_destroy();
    session_start();
    $_SESSION['warning'] = 'Your session has expired. Please login again.';
    header('Location: ' . SITE_URL . 'login.php');
    exit;
}

$_SESSION['last_activity'] = time();
?>
