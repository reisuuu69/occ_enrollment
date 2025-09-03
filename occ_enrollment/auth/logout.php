<?php
session_start();

// Log the logout action if user was logged in
if (isset($_SESSION['user_id'])) {
    require_once '../config/database.php';
    $database = new Database();
    $db = $database->connect();
    
    // Log the logout
    $log_query = "INSERT INTO audit_logs (user_id, action, ip_address, user_agent) VALUES (?, 'logout', ?, ?)";
    $log_stmt = $db->prepare($log_query);
    $log_stmt->execute([$_SESSION['user_id'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]);
}

// Clear all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to home page
header("Location: ../index.php");
exit();
?>
