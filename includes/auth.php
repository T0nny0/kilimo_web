<?php
require_once __DIR__ . '/functions.php';

/**
 * Check if user is authenticated
 */
function check_auth() {
    if (!is_logged_in()) {
        if (basename($_SERVER['PHP_SELF']) != 'login.php' && 
            basename($_SERVER['PHP_SELF']) != 'register.php') {
            redirect('login.php');
        }
    }
}

/**
 * Logout user
 */
function logout() {
    // Delete the session cookie.
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Unset all of the session variables.
    $_SESSION = array();
    // Finally, destroy the session.
    session_destroy();

    // Redirect to login page with a success message
    redirect('login.php?success_message=' . urlencode("You have been logged out successfully."));
}

/**
 * Validate user credentials
 */
function validate_credentials($username, $password) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    } catch (PDOException $e) {
        error_log("Auth Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Register new user
 */
function register_user($user_data) {
    global $pdo;
    
    try {
        $hashed_password = password_hash($user_data['password'], PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, phone, address) VALUES (?, ?, ?, ?, ?, ?)");
        
        return $stmt->execute([
            $user_data['username'],
            $user_data['email'],
            $hashed_password,
            $user_data['full_name'],
            $user_data['phone'] ?? null,
            $user_data['address'] ?? null
        ]);
        
    } catch (PDOException $e) {
        error_log("Registration Error: " . $e->getMessage());
        return false;
    }
}
?>
