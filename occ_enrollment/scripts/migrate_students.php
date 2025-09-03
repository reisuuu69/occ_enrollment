<?php
// Simple migration script to create student accounts in users table
require_once __DIR__ . '/../config/database.php';

try {
    $database = new Database();
    $db = $database->connect();

    echo "Starting student migration...\n";

    // Get all students from old_students table
    $stmt = $db->query("SELECT username, email, password FROM old_students");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $created = 0;
    $skipped = 0;

    foreach ($students as $student) {
        $username = trim($student['username']);
        $email = trim($student['email']);
        $password = $student['password'];

        if (empty($username) || empty($email) || empty($password)) {
            $skipped++;
            continue;
        }

        // Check if user already exists
        $checkStmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $checkStmt->execute([$username, $email]);
        
        if ($checkStmt->fetch()) {
            $skipped++;
            continue;
        }

        // Insert into users table
        $insertStmt = $db->prepare("INSERT INTO users (username, email, password, role, status) VALUES (?, ?, ?, 'student', 'active')");
        $insertStmt->execute([$username, $email, $password]);
        $created++;

        echo "Created user: $username\n";
    }

    echo "\nMigration complete!\n";
    echo "Created: $created accounts\n";
    echo "Skipped: $skipped accounts\n";
    echo "\nYou can now login with:\n";
    echo "Username: juan2022\n";
    echo "Password: password (the default password from old_students table)\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
