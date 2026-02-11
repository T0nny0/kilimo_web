<?php
require_once '../includes/db.php';

// New password for admin
$password = 'KilimoAdmin2026!';
$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
    $stmt->execute([$hash]);
    echo "Admin password has been successfully reset to: " . $password;
} catch (PDOException $e) {
    echo "Error updating password: " . $e->getMessage();
}
?>