<?php
session_start();
if (!isset($_SESSION['registrar_logged_in'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    require_once '../config/database.php';
    $database = new Database();
    $db = $database->connect();

    try {
        $query = "DELETE FROM enrollees WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $_GET['id']);
        
        if ($stmt->execute()) {
            header("Location: dashboard.php?success=delete");
        } else {
            header("Location: dashboard.php?error=delete");
        }
    } catch(PDOException $e) {
        header("Location: dashboard.php?error=delete");
    }
} else {
    header("Location: dashboard.php");
}
exit();
