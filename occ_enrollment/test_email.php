<?php
// Test email functionality
echo "<h2>Email Configuration Test</h2>";

// Check if mail function is available
echo "<h3>1. Mail Function Check:</h3>";
if (function_exists('mail')) {
    echo "✅ PHP mail() function is available<br>";
} else {
    echo "❌ PHP mail() function is NOT available<br>";
}

// Check PHP configuration
echo "<h3>2. PHP Configuration:</h3>";
echo "SMTP: " . ini_get('SMTP') . "<br>";
echo "smtp_port: " . ini_get('smtp_port') . "<br>";
echo "sendmail_path: " . ini_get('sendmail_path') . "<br>";
echo "sendmail_from: " . ini_get('sendmail_from') . "<br>";

// Test simple email
echo "<h3>3. Test Email Send:</h3>";
$to = "test@example.com";
$subject = "Test Email from OCC Enrollment System";
$message = "This is a test email to verify email functionality.";
$headers = "From: noreply@occ.edu.ph\r\n";
$headers .= "Reply-To: registrar@occ.edu.ph\r\n";

$result = mail($to, $subject, $message, $headers);
if ($result) {
    echo "✅ Test email was sent successfully<br>";
} else {
    echo "❌ Test email failed to send<br>";
}

// Check for errors
echo "<h3>4. Error Information:</h3>";
$error = error_get_last();
if ($error) {
    echo "Last error: " . $error['message'] . "<br>";
} else {
    echo "No recent errors found<br>";
}

// Check if we can connect to database
echo "<h3>5. Database Connection Test:</h3>";
try {
    include_once 'config/database.php';
    echo "✅ Database connection successful<br>";
    
    // Test EmailHelper
    include_once 'includes/EmailHelper.php';
    $emailHelper = new EmailHelper($db);
    echo "✅ EmailHelper class loaded successfully<br>";
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
}

echo "<h3>6. Recommendations:</h3>";
echo "<p>If emails are not sending, you need to configure XAMPP's email settings:</p>";
echo "<ol>";
echo "<li>Open XAMPP Control Panel</li>";
echo "<li>Click on 'Config' button for Apache</li>";
echo "<li>Select 'php.ini'</li>";
echo "<li>Find the [mail function] section</li>";
echo "<li>Configure SMTP settings or install a local SMTP server like Mercury</li>";
echo "</ol>";

echo "<p><strong>Alternative Solution:</strong> Use a service like Gmail SMTP or Mailgun for reliable email delivery.</p>";
?>
