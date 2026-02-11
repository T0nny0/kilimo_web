<?php
require_once '../../includes/session.php';
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

require_auth();

$user_id = $_SESSION['user_id'];

// Get unread notifications
$stmt = $pdo->prepare("
    SELECT * FROM notifications 
    WHERE user_id = ? AND is_read = 0
    ORDER BY created_at DESC
    LIMIT 10
");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();

json_response(true, 'Notifications retrieved', ['notifications' => $notifications]);
?>
