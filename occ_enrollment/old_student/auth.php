<?php
session_start();
require_once '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    try {
        $database = new Database();
        $db = $database->connect();
        // Authenticate via unified users table for role student and active status
        $user_query = "SELECT * FROM users WHERE username = :username AND role = 'student' AND status = 'active' LIMIT 1";
        $user_stmt = $db->prepare($user_query);
        $user_stmt->bindParam(':username', $username);
        $user_stmt->execute();

        $user = $user_stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Map to old_students record to keep existing dashboard data access
            $student_query = "SELECT * FROM old_students WHERE user_id = :user_id LIMIT 1";
            $student_stmt = $db->prepare($student_query);
            $student_stmt->bindParam(':user_id', $user['id']);
            $student_stmt->execute();
            $student = $student_stmt->fetch(PDO::FETCH_ASSOC);

            // Fallback: if schema doesn't yet have user_id, try by username/email
            if (!$student) {
                $fallback_query = "SELECT * FROM old_students WHERE username = :username OR email = :email LIMIT 1";
                $fallback_stmt = $db->prepare($fallback_query);
                $fallback_stmt->bindParam(':username', $user['username']);
                $fallback_stmt->bindParam(':email', $user['email']);
                $fallback_stmt->execute();
                $student = $fallback_stmt->fetch(PDO::FETCH_ASSOC);
            }

            // Set sessions. Maintain existing keys for compatibility
            $_SESSION['student_logged_in'] = true;
            $_SESSION['role'] = 'student';
            $_SESSION['user_id'] = $user['id'];

            if ($student) {
                $_SESSION['student_id'] = $student['student_id'];
                $_SESSION['student_name'] = ($student['firstname'] ?? '') . ' ' . ($student['lastname'] ?? '');
            } else {
                // Minimal fallback if no old_students row; still allow login
                $_SESSION['student_id'] = null;
                $_SESSION['student_name'] = $user['username'];
            }

            // Update last_login
            $update_query = "UPDATE users SET last_login = NOW() WHERE id = :id";
            $update_stmt = $db->prepare($update_query);
            $update_stmt->bindParam(':id', $user['id']);
            $update_stmt->execute();

            header("Location: dashboard.php");
            exit();
        } else {
            header("Location: login.php?error=1");
            exit();
        }
        
    } catch(PDOException $e) {
        header("Location: login.php?error=2");
        exit();
    }
}

// If not POST request, redirect to login
header("Location: login.php");
exit();
?>
