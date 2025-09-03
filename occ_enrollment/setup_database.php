<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->connect();

echo "<h2>Setting up Email Verification System Database Tables</h2>";

// Create pending_enrollments table
try {
    $pendingTableQuery = "
    CREATE TABLE IF NOT EXISTS pending_enrollments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        verification_token VARCHAR(255) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL,
        lastname VARCHAR(50) NOT NULL,
        firstname VARCHAR(50) NOT NULL,
        middlename VARCHAR(50),
        lrn VARCHAR(20) NOT NULL,
        address TEXT NOT NULL,
        gender CHAR(1) NOT NULL,
        date_of_birth DATE NOT NULL,
        age INT NOT NULL,
        civil_status VARCHAR(20) NOT NULL,
        contact_no VARCHAR(20) NOT NULL,
        last_school VARCHAR(100) NOT NULL,
        school_address TEXT NOT NULL,
        strand VARCHAR(50) NOT NULL,
        preferred_program VARCHAR(20) NOT NULL,
        is_working CHAR(1) NOT NULL DEFAULT 'N',
        employer VARCHAR(100),
        position VARCHAR(100),
        working_hours VARCHAR(50),
        preferred_schedule VARCHAR(20) NOT NULL,
        father_name VARCHAR(100) NOT NULL,
        father_occupation VARCHAR(100),
        father_education VARCHAR(50),
        father_contact VARCHAR(20),
        num_brothers INT DEFAULT 0,
        family_income DECIMAL(10,2) NOT NULL,
        mother_name VARCHAR(100) NOT NULL,
        mother_occupation VARCHAR(100),
        mother_education VARCHAR(50),
        mother_contact VARCHAR(20),
        num_sisters INT DEFAULT 0,
        guardian_name VARCHAR(100) NOT NULL,
        guardian_contact VARCHAR(20) NOT NULL,
        is_verified BOOLEAN DEFAULT FALSE,
        verification_expires TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $db->exec($pendingTableQuery);
    echo "<p style='color: green;'>✅ pending_enrollments table created successfully!</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error creating pending_enrollments table: " . $e->getMessage() . "</p>";
}

// Create entrance_exam_schedules table
try {
    $examTableQuery = "
    CREATE TABLE IF NOT EXISTS entrance_exam_schedules (
        id INT AUTO_INCREMENT PRIMARY KEY,
        enrollee_id INT NOT NULL,
        exam_date DATE NOT NULL,
        exam_time TIME NOT NULL,
        exam_venue VARCHAR(100) NOT NULL DEFAULT 'OCC Campus',
        exam_type VARCHAR(50) NOT NULL DEFAULT 'Entrance Exam',
        status ENUM('scheduled', 'completed', 'passed', 'failed', 'no_show') DEFAULT 'scheduled',
        remarks TEXT,
        scheduled_by INT,
        scheduled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        completed_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (enrollee_id) REFERENCES enrollees(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $db->exec($examTableQuery);
    echo "<p style='color: green;'>✅ entrance_exam_schedules table created successfully!</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error creating entrance_exam_schedules table: " . $e->getMessage() . "</p>";
}

// Create email_logs table if it doesn't exist
try {
    $emailLogsQuery = "
    CREATE TABLE IF NOT EXISTS email_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        recipient_email VARCHAR(100) NOT NULL,
        subject VARCHAR(200) NOT NULL,
        message TEXT NOT NULL,
        status ENUM('sent', 'failed', 'pending') DEFAULT 'pending',
        sent_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $db->exec($emailLogsQuery);
    echo "<p style='color: green;'>✅ email_logs table created successfully!</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error creating email_logs table: " . $e->getMessage() . "</p>";
}

echo "<h3>Setup Complete!</h3>";
echo "<p>The email verification system is now ready to use.</p>";
echo "<p><strong>Next steps:</strong></p>";
echo "<ul>";
echo "<li>1. Test the enrollment form submission</li>";
echo "<li>2. Check that verification emails are sent</li>";
echo "<li>3. Test the verification process</li>";
echo "<li>4. Use the registrar's 'Schedule Exam' feature to schedule entrance exams</li>";
echo "</ul>";
echo "<p><a href='index.php'>Return to Home</a></p>";
?>
