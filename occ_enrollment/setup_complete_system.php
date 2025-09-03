<?php
/**
 * Complete System Setup Script for OCC Enrollment System
 * This script sets up all necessary database tables in the correct order
 * Run this file once to set up the complete system
 */

require_once 'config/database.php';

echo "<h2>Setting up Complete OCC Enrollment System...</h2>";
echo "<p>This will create all necessary tables in the correct dependency order.</p>";

try {
    // Define the order of table creation (dependencies first)
    $table_files = [
        'database/users_table.sql',
        'database/course_table.sql',
        'database/faculty_table.sql',
        'database/subject_table.sql',
        'database/students_table.sql',
        'database/registrar_tables.sql'
    ];
    
    $success_count = 0;
    $error_count = 0;
    $warnings = [];
    
    foreach ($table_files as $sql_file) {
        if (!file_exists($sql_file)) {
            echo "<p style='color: red;'>Error: SQL file not found: $sql_file</p>";
            $error_count++;
            continue;
        }
        
        echo "<hr>";
        echo "<h3>Processing: " . basename($sql_file) . "</h3>";
        
        $sql_content = file_get_contents($sql_file);
        
        // Split SQL into individual statements
        $statements = array_filter(array_map('trim', explode(';', $sql_content)));
        
        foreach ($statements as $statement) {
            if (empty($statement)) continue;
            
            try {
                // Ensure $pdo is defined and connected
                if (!isset($pdo) || !$pdo instanceof PDO) {
                    // Create a new PDO connection if not already set
                    $database = new Database();
                    $pdo = $database->connect();
                }
                $pdo->exec($statement);
                $success_count++;
                echo "<p style='color: green;'>✓ Executed: " . htmlspecialchars(substr($statement, 0, 50)) . "...</p>";
            } catch (PDOException $e) {
                $error_code = $e->getCode();
                
                // Handle specific error codes gracefully
                if ($error_code == 23000) {
                    // Duplicate key or constraint violation - might be expected
                    $warnings[] = "Warning in " . basename($sql_file) . ": " . substr($statement, 0, 50) . "... - " . $e->getMessage();
                    echo "<p style='color: orange;'>⚠ Warning: " . substr($statement, 0, 50) . "... - Constraint violation (may be expected)</p>";
                } elseif ($error_code == '42S11') {
                    // Index already exists
                    $warnings[] = "Warning in " . basename($sql_file) . ": " . substr($statement, 0, 50) . "... - " . $e->getMessage();
                    echo "<p style='color: orange;'>⚠ Warning: " . substr($statement, 0, 50) . "... - Index already exists</p>";
                } elseif ($error_code == '42S01') {
                    // Table already exists
                    $warnings[] = "Warning in " . basename($sql_file) . ": " . substr($statement, 0, 50) . "... - " . $e->getMessage();
                    echo "<p style='color: orange;'>⚠ Warning: " . substr($statement, 0, 50) . "... - Table already exists</p>";
                } else {
                    $error_count++;
                    echo "<p style='color: red;'>✗ Error: " . substr($statement, 0, 50) . "... - " . $e->getMessage() . " (Code: $error_code)</p>";
                }
            }
        }
    }
    
    echo "<hr>";
    echo "<h3>Setup Complete!</h3>";
    echo "<p>Successfully executed: <strong>$success_count</strong> statements</p>";
    echo "<p>Errors encountered: <strong>$error_count</strong></p>";
    echo "<p>Warnings: <strong>" . count($warnings) . "</strong></p>";
    
    if ($error_count == 0) {
        echo "<p style='color: green; font-weight: bold;'>✅ Complete OCC Enrollment System is ready to use!</p>";
        echo "<p><a href='registrar/dashboard.php'>Go to Registrar Dashboard</a></p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Some errors occurred. Please check the database manually.</p>";
    }
    
    // Show warnings if any
    if (!empty($warnings)) {
        echo "<hr>";
        echo "<h4>Warnings:</h4>";
        echo "<ul>";
        foreach ($warnings as $warning) {
            echo "<li style='color: orange;'>$warning</li>";
        }
        echo "</ul>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Fatal Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>What was created:</h3>";
echo "<ul>";
echo "<li><strong>users</strong> - User authentication and roles</li>";
echo "<li><strong>courses</strong> - Course definitions</li>";
echo "<li><strong>faculty</strong> - Professor information</li>";
echo "<li><strong>subjects</strong> - Subject definitions</li>";
echo "<li><strong>students</strong> - Student information</li>";
echo "<li><strong>sections</strong> - Student sections with capacity limits</li>";
echo "<li><strong>rooms</strong> - Classroom and laboratory rooms</li>";
echo "<li><strong>subject_professor</strong> - Professor-subject assignments</li>";
echo "<li><strong>subject_schedule</strong> - Class schedules with rooms</li>";
echo "<li><strong>student_sections</strong> - Student section assignments</li>";
echo "</ul>";

echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>Access the <a href='registrar/dashboard.php'>Registrar Dashboard</a></li>";
echo "<li>Add students through <a href='registrar/manage_students.php'>Manage Students</a></li>";
echo "<li>Assign professors to subjects through <a href='registrar/assignments.php'>Assignments</a></li>";
echo "<li>Create schedules through <a href='registrar/manage_schedules.php'>Manage Schedules</a></li>";
echo "</ol>";

// Test database connectivity and table existence
echo "<hr>";
echo "<h3>Database Status Check:</h3>";
try {
    $tables = ['users', 'courses', 'faculty', 'subjects', 'students', 'sections', 'rooms', 'subject_professor', 'subject_schedule', 'student_sections'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<p style='color: green;'>✓ Table '$table' exists</p>";
        } else {
            echo "<p style='color: red;'>✗ Table '$table' missing</p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error checking tables: " . $e->getMessage() . "</p>";
}
?>
