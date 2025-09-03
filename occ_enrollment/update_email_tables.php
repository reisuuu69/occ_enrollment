<?php
require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->connect();
    
    echo "<h2>Updating Email Logs Table</h2>";
    
    // Check if email_logs table exists
    $checkTable = $db->query("SHOW TABLES LIKE 'email_logs'");
    if ($checkTable->rowCount() == 0) {
        // Create the table
        $createTable = "
        CREATE TABLE email_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            recipient_email VARCHAR(100) NOT NULL,
            subject VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            status ENUM('attempting', 'sent', 'failed', 'saved_to_file') DEFAULT 'sent',
            error_message TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        
        $db->exec($createTable);
        echo "✅ Created email_logs table<br>";
    } else {
        // Check if new columns exist
        $columns = $db->query("SHOW COLUMNS FROM email_logs")->fetchAll(PDO::FETCH_COLUMN);
        
        if (!in_array('error_message', $columns)) {
            $db->exec("ALTER TABLE email_logs ADD COLUMN error_message TEXT NULL AFTER status");
            echo "✅ Added error_message column<br>";
        }
        
        if (!in_array('created_at', $columns)) {
            $db->exec("ALTER TABLE email_logs ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER error_message");
            echo "✅ Added created_at column<br>";
        }
        
        if (!in_array('updated_at', $columns)) {
            $db->exec("ALTER TABLE email_logs ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at");
            echo "✅ Added updated_at column<br>";
        }
        
        // Update status enum if needed
        $db->exec("ALTER TABLE email_logs MODIFY COLUMN status ENUM('attempting', 'sent', 'failed', 'saved_to_file') DEFAULT 'sent'");
        echo "✅ Updated status enum<br>";
    }
    
    // Check if pending_enrollments table exists
    $checkPending = $db->query("SHOW TABLES LIKE 'pending_enrollments'");
    if ($checkPending->rowCount() == 0) {
        echo "⚠️ pending_enrollments table does not exist. Please run setup_database.php first.<br>";
    } else {
        echo "✅ pending_enrollments table exists<br>";
    }
    
    // Check if entrance_exam_schedules table exists
    $checkExam = $db->query("SHOW TABLES LIKE 'entrance_exam_schedules'");
    if ($checkExam->rowCount() == 0) {
        echo "⚠️ entrance_exam_schedules table does not exist. Please run setup_database.php first.<br>";
    } else {
        echo "✅ entrance_exam_schedules table exists<br>";
    }
    
    echo "<br><h3>✅ Email tables updated successfully!</h3>";
    echo "<p>You can now test the email functionality:</p>";
    echo "<ul>";
    echo "<li><a href='test_email.php'>Run Email Test</a></li>";
    echo "<li><a href='registrar/schedule_exam.php'>Schedule Entrance Exam (Test Mode)</a></li>";
    echo "<li><a href='index.php'>Submit New Enrollment (Test Mode)</a></li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<h3>❌ Error updating tables:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
