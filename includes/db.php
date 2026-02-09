<?php
/**
 * Database Connection Handler
 * Uses PDO for secure database operations with prepared statements
 */

require_once __DIR__ . '/../config/database.php';

try {
    // Create PDO connection
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    // Set timezone for database operations
    $pdo->exec("SET time_zone = '+03:00'"); // East African Time
} catch (PDOException $e) {
    // Log error to file instead of displaying it
    error_log("Database Connection Error: " . $e->getMessage(), 0);
    
    // Display user-friendly error message
    die("Database connection error. Please try again later.");
}
?>
