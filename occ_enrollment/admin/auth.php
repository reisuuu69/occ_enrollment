<?php
session_start();
require_once '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    try {
        $database = new Database();
        $db = $database->connect();

        $query = "SELECT * FROM users WHERE username = :username AND role = 'admin' LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = 'admin';
            
            // Update last login
            $update_query = "UPDATE users SET last_login = NOW() WHERE id = :id";
            $update_stmt = $db->prepare($update_query);
            $update_stmt->bindParam(':id', $user['id']);
            $update_stmt->execute();
            
            header("Location: dashboard.php");
            exit();
        }

        header("Location: login.php?error=1");
        exit();
        
    } catch(PDOException $e) {
        header("Location: login.php?error=2");
        exit();
    }
}

// If not POST request, redirect to login
header("Location: login.php");
exit();
?>
