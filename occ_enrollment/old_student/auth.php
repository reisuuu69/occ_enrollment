<?php
session_start();
require_once '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    try {
        $database = new Database();
        $db = $database->connect();
        // 1) Try authenticating against old_students table (username or email)
        $student_lookup = $db->prepare("SELECT * FROM old_students WHERE username = :u OR email = :u LIMIT 1");
        $student_lookup->execute([':u' => $username]);
        $student = $student_lookup->fetch(PDO::FETCH_ASSOC);

        if ($student && password_verify($password, $student['password'])) {
            $_SESSION['student_logged_in'] = true;
            $_SESSION['role'] = 'student';
            $_SESSION['student_id'] = $student['student_id'];
            $_SESSION['student_name'] = ($student['firstname'] ?? '') . ' ' . ($student['lastname'] ?? '');

            // Optionally sync to users table for consistency
            try {
                $userCheck = $db->prepare('SELECT id FROM users WHERE username = :u LIMIT 1');
                $userCheck->execute([':u' => $student['username']]);
                $user = $userCheck->fetch(PDO::FETCH_ASSOC);
                if ($user) {
                    $upd = $db->prepare('UPDATE users SET email = :e, role = "student", status = "active", last_login = NOW() WHERE id = :id');
                    $upd->execute([':e' => $student['email'], ':id' => $user['id']]);
                    $_SESSION['user_id'] = (int)$user['id'];
                } else {
                    $ins = $db->prepare('INSERT INTO users (username, email, password, role, status, last_login) VALUES (:u, :e, :p, "student", "active", NOW())');
                    $ins->execute([':u' => $student['username'], ':e' => $student['email'], ':p' => $student['password']]);
                    $_SESSION['user_id'] = (int)$db->lastInsertId();
                }
            } catch (Throwable $syncError) {
                // Ignore syncing errors; allow login based on old_students
            }

            header("Location: dashboard.php");
            exit();
        }

        // 2) Fallback: authenticate via users table
        $user_query = "SELECT * FROM users WHERE (username = :username OR email = :username) AND role = 'student' AND status = 'active' LIMIT 1";
        $user_stmt = $db->prepare($user_query);
        $user_stmt->bindParam(':username', $username);
        $user_stmt->execute();
        $user = $user_stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Try to map to old_students by username/email
            $fallback_query = "SELECT * FROM old_students WHERE username = :u OR email = :e LIMIT 1";
            $fallback_stmt = $db->prepare($fallback_query);
            $fallback_stmt->execute([':u' => $user['username'], ':e' => $user['email']]);
            $student = $fallback_stmt->fetch(PDO::FETCH_ASSOC);

            $_SESSION['student_logged_in'] = true;
            $_SESSION['role'] = 'student';
            $_SESSION['user_id'] = $user['id'];
            if ($student) {
                $_SESSION['student_id'] = $student['student_id'];
                $_SESSION['student_name'] = ($student['firstname'] ?? '') . ' ' . ($student['lastname'] ?? '');
            } else {
                $_SESSION['student_id'] = null;
                $_SESSION['student_name'] = $user['username'];
            }

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
