<?php
require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->connect();
    
    echo "<h2>Updating Faculty Table</h2>";
    
    // Check if faculty table exists
    $checkTable = $db->query("SHOW TABLES LIKE 'faculty'");
    if ($checkTable->rowCount() == 0) {
        echo "❌ Faculty table does not exist. Please run the faculty_table.sql first.<br>";
        exit();
    }
    
    // Check if email column exists
    $columns = $db->query("SHOW COLUMNS FROM faculty")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('email', $columns)) {
        $db->exec("ALTER TABLE faculty ADD COLUMN email VARCHAR(100) DEFAULT NULL AFTER specialization");
        echo "✅ Added email column<br>";
    } else {
        echo "✅ Email column already exists<br>";
    }
    
    if (!in_array('contact_number', $columns)) {
        $db->exec("ALTER TABLE faculty ADD COLUMN contact_number VARCHAR(20) DEFAULT NULL AFTER email");
        echo "✅ Added contact_number column<br>";
    } else {
        echo "✅ Contact number column already exists<br>";
    }
    
    // Update existing records with sample data if they don't have email/contact
    $updateQuery = "UPDATE faculty SET 
        email = CASE 
            WHEN email IS NULL OR email = '' THEN CONCAT(LOWER(REPLACE(professor_name, ' ', '.')), '@occ.edu.ph')
            ELSE email 
        END,
        contact_number = CASE 
            WHEN contact_number IS NULL OR contact_number = '' THEN CONCAT('09', LPAD(id, 9, '0'))
            ELSE contact_number 
        END
        WHERE email IS NULL OR email = '' OR contact_number IS NULL OR contact_number = ''";
    
    $result = $db->exec($updateQuery);
    if ($result > 0) {
        echo "✅ Updated $result existing faculty records with sample email and contact data<br>";
    } else {
        echo "✅ All faculty records already have email and contact data<br>";
    }
    
    // Show current faculty data
    echo "<h3>Current Faculty Data:</h3>";
    $facultyQuery = "SELECT * FROM faculty ORDER BY professor_name";
    $facultyStmt = $db->query($facultyQuery);
    $faculty = $facultyStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($faculty)) {
        echo "<p>No faculty records found.</p>";
    } else {
        echo "<table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>";
        echo "<tr style='background: #f8f9fa;'>";
        echo "<th style='padding: 10px; border: 1px solid #ddd; text-align: left;'>Name</th>";
        echo "<th style='padding: 10px; border: 1px solid #ddd; text-align: left;'>Department</th>";
        echo "<th style='padding: 10px; border: 1px solid #ddd; text-align: left;'>Email</th>";
        echo "<th style='padding: 10px; border: 1px solid #ddd; text-align: left;'>Contact</th>";
        echo "</tr>";
        
        foreach ($faculty as $member) {
            echo "<tr>";
            echo "<td style='padding: 10px; border: 1px solid #ddd;'>" . htmlspecialchars($member['professor_name']) . "</td>";
            echo "<td style='padding: 10px; border: 1px solid #ddd;'>" . htmlspecialchars($member['department']) . "</td>";
            echo "<td style='padding: 10px; border: 1px solid #ddd;'>" . htmlspecialchars($member['email'] ?? 'N/A') . "</td>";
            echo "<td style='padding: 10px; border: 1px solid #ddd;'>" . htmlspecialchars($member['contact_number'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #28a745;'>";
    echo "<h3>✅ Faculty table updated successfully!</h3>";
    echo "<p>The undefined array key warnings should now be resolved.</p>";
    echo "<p><a href='registrar/faculty_list.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>View Faculty List</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<h3>❌ Error updating faculty table:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
