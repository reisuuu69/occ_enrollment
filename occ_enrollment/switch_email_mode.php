<?php
$action = $_GET['action'] ?? '';

if ($action === 'test') {
    // Switch to test mode
    $files = [
        'registrar/schedule_exam.php' => [
            'require_once \'../includes/EmailHelper.php\';' => 'require_once \'../includes/EmailHelper_Test.php\';',
            'new EmailHelper($db);' => 'new EmailHelper_Test($db);'
        ],
        'process.php' => [
            'require_once \'includes/EmailHelper.php\';' => 'require_once \'includes/EmailHelper_Test.php\';',
            'new EmailHelper($db);' => 'new EmailHelper_Test($db);'
        ]
    ];
    
    $mode = 'Test Mode';
    $description = 'Emails will be saved to email_log.txt and displayed on screen';
    
} elseif ($action === 'real') {
    // Switch to real email mode
    $files = [
        'registrar/schedule_exam.php' => [
            'require_once \'../includes/EmailHelper_Test.php\';' => 'require_once \'../includes/EmailHelper.php\';',
            'new EmailHelper_Test($db);' => 'new EmailHelper($db);'
        ],
        'process.php' => [
            'require_once \'includes/EmailHelper_Test.php\';' => 'require_once \'includes/EmailHelper.php\';',
            'new EmailHelper_Test($db);' => 'new EmailHelper($db);'
        ]
    ];
    
    $mode = 'Real Email Mode';
    $description = 'Emails will be sent using PHP mail() function (requires SMTP server)';
    
} else {
    // Show current status
    $currentMode = 'Unknown';
    $testModeActive = false;
    
    // Check current mode by reading one of the files
    if (file_exists('registrar/schedule_exam.php')) {
        $content = file_get_contents('registrar/schedule_exam.php');
        if (strpos($content, 'EmailHelper_Test') !== false) {
            $currentMode = 'Test Mode';
            $testModeActive = true;
        } else {
            $currentMode = 'Real Email Mode';
            $testModeActive = false;
        }
    }
    
    echo "<h2>📧 Email Mode Switcher</h2>";
    echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h3>Current Mode: <strong>$currentMode</strong></h3>";
    
    if ($testModeActive) {
        echo "<p>✅ Currently in <strong>Test Mode</strong> - Emails are saved to file and displayed on screen</p>";
        echo "<p><a href='?action=real' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Switch to Real Email Mode</a></p>";
    } else {
        echo "<p>⚠️ Currently in <strong>Real Email Mode</strong> - Requires SMTP server configuration</p>";
        echo "<p><a href='?action=test' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Switch to Test Mode</a></p>";
    }
    
    echo "</div>";
    
    echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h4>Mode Descriptions:</h4>";
    echo "<ul>";
    echo "<li><strong>Test Mode:</strong> Perfect for development and testing. Emails are saved to <code>email_log.txt</code> and displayed on screen.</li>";
    echo "<li><strong>Real Email Mode:</strong> Sends actual emails using PHP mail() function. Requires SMTP server configuration.</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div style='background: #fff3cd; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h4>Quick Actions:</h4>";
    echo "<p><a href='test_email.php'>🔍 Run Email Test</a></p>";
    echo "<p><a href='configure_email.php'>⚙️ Email Configuration Guide</a></p>";
    echo "<p><a href='registrar/schedule_exam.php'>📅 Test Exam Scheduling</a></p>";
    echo "<p><a href='index.php'>📝 Test New Enrollment</a></p>";
    echo "</div>";
    
    exit();
}

// Perform the file updates
$updatedFiles = [];
$errors = [];

foreach ($files as $filePath => $replacements) {
    if (!file_exists($filePath)) {
        $errors[] = "File not found: $filePath";
        continue;
    }
    
    $content = file_get_contents($filePath);
    $originalContent = $content;
    
    foreach ($replacements as $search => $replace) {
        $content = str_replace($search, $replace, $content);
    }
    
    if ($content !== $originalContent) {
        if (file_put_contents($filePath, $content)) {
            $updatedFiles[] = $filePath;
        } else {
            $errors[] = "Failed to update: $filePath";
        }
    } else {
        $errors[] = "No changes needed in: $filePath";
    }
}

// Display results
echo "<h2>📧 Email Mode Switched</h2>";
echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #28a745;'>";
echo "<h3>✅ Successfully switched to: <strong>$mode</strong></h3>";
echo "<p>$description</p>";
echo "</div>";

if (!empty($updatedFiles)) {
    echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h4>Updated Files:</h4>";
    echo "<ul>";
    foreach ($updatedFiles as $file) {
        echo "<li>✅ $file</li>";
    }
    echo "</ul>";
    echo "</div>";
}

if (!empty($errors)) {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #dc3545;'>";
    echo "<h4>⚠️ Issues:</h4>";
    echo "<ul>";
    foreach ($errors as $error) {
        echo "<li>$error</li>";
    }
    echo "</ul>";
    echo "</div>";
}

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h4>Next Steps:</h4>";
if ($action === 'test') {
    echo "<ol>";
    echo "<li><a href='registrar/schedule_exam.php'>Test Exam Scheduling</a> - Emails will be saved to file</li>";
    echo "<li><a href='index.php'>Test New Enrollment</a> - Verification emails will be saved</li>";
    echo "<li>Check <code>email_log.txt</code> for saved emails</li>";
    echo "</ol>";
} else {
    echo "<ol>";
    echo "<li>Configure your SMTP server (Mercury, Gmail, or third-party service)</li>";
    echo "<li><a href='test_email.php'>Test email configuration</a></li>";
    echo "<li><a href='registrar/schedule_exam.php'>Test Exam Scheduling</a></li>";
    echo "</ol>";
}
echo "</div>";

echo "<p><a href='switch_email_mode.php' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>← Back to Mode Switcher</a></p>";
?>
