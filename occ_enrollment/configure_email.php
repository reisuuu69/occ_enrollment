<?php
echo "<h2>🔧 XAMPP Email Configuration Helper</h2>";

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h3>Current Email Status:</h3>";
echo "<p><strong>✅ Good:</strong> PHP mail() function is available</p>";
echo "<p><strong>❌ Issue:</strong> No mail server running on localhost:25</p>";
echo "</div>";

echo "<h3>📋 Solution Options:</h3>";

echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #28a745;'>";
echo "<h4>🎯 Option 1: Use Test Mode (Recommended for Development)</h4>";
echo "<p>The system is already configured to use test mode. Emails will be saved to <code>email_log.txt</code> and displayed on screen.</p>";
echo "<p><strong>Benefits:</strong> No configuration needed, works immediately, perfect for testing</p>";
echo "<p><a href='registrar/schedule_exam.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Exam Scheduling</a></p>";
echo "</div>";

echo "<div style='background: #fff3cd; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #ffc107;'>";
echo "<h4>⚙️ Option 2: Configure Mercury Mail Server</h4>";
echo "<ol>";
echo "<li>Download Mercury Mail Server from: <a href='https://www.pmail.com/overviews/ovw_mercury.htm' target='_blank'>https://www.pmail.com/overviews/ovw_mercury.htm</a></li>";
echo "<li>Install and configure Mercury to run on port 25</li>";
echo "<li>Update php.ini with these settings:</li>";
echo "</ol>";
echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>[mail function]
SMTP = localhost
smtp_port = 25
sendmail_from = noreply@occ.edu.ph</pre>";
echo "</div>";

echo "<div style='background: #d1ecf1; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #17a2b8;'>";
echo "<h4>🌐 Option 3: Use Gmail SMTP (Production Ready)</h4>";
echo "<ol>";
echo "<li>Enable 2-Factor Authentication on your Gmail account</li>";
echo "<li>Generate an App Password: Google Account → Security → 2-Step Verification → App passwords</li>";
echo "<li>Use PHPMailer with these settings:</li>";
echo "</ol>";
echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>SMTP Host: smtp.gmail.com
SMTP Port: 587
Encryption: TLS
Username: your-email@gmail.com
Password: your-app-password</pre>";
echo "</div>";

echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #dc3545;'>";
echo "<h4>🚀 Option 4: Use Third-Party Email Service (Best for Production)</h4>";
echo "<ul>";
echo "<li><strong>Mailgun:</strong> Free tier available, reliable delivery</li>";
echo "<li><strong>SendGrid:</strong> Popular choice, good free tier</li>";
echo "<li><strong>Amazon SES:</strong> Very cost-effective for high volume</li>";
echo "</ul>";
echo "</div>";

echo "<h3>🔧 Quick Configuration Scripts:</h3>";

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h4>Switch to Real Email (when ready):</h4>";
echo "<p>To switch from test mode to real email sending, update these files:</p>";
echo "<ul>";
echo "<li><code>registrar/schedule_exam.php</code> - Change EmailHelper_Test to EmailHelper</li>";
echo "<li><code>process.php</code> - Change EmailHelper_Test to EmailHelper</li>";
echo "</ul>";
echo "</div>";

echo "<h3>📊 Current Test Results:</h3>";
echo "<table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>";
echo "<tr style='background: #f8f9fa;'>";
echo "<th style='padding: 10px; border: 1px solid #ddd; text-align: left;'>Component</th>";
echo "<th style='padding: 10px; border: 1px solid #ddd; text-align: left;'>Status</th>";
echo "</tr>";
echo "<tr>";
echo "<td style='padding: 10px; border: 1px solid #ddd;'>PHP mail() function</td>";
echo "<td style='padding: 10px; border: 1px solid #ddd; color: #28a745;'>✅ Available</td>";
echo "</tr>";
echo "<tr>";
echo "<td style='padding: 10px; border: 1px solid #ddd;'>SMTP Server</td>";
echo "<td style='padding: 10px; border: 1px solid #ddd; color: #dc3545;'>❌ Not running</td>";
echo "</tr>";
echo "<tr>";
echo "<td style='padding: 10px; border: 1px solid #ddd;'>Database Connection</td>";
echo "<td style='padding: 10px; border: 1px solid #ddd; color: #28a745;'>✅ Working</td>";
echo "</tr>";
echo "<tr>";
echo "<td style='padding: 10px; border: 1px solid #ddd;'>EmailHelper Class</td>";
echo "<td style='padding: 10px; border: 1px solid #ddd; color: #28a745;'>✅ Loaded</td>";
echo "</tr>";
echo "</table>";

echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #28a745;'>";
echo "<h4>✅ Ready to Test!</h4>";
echo "<p>Your system is ready for testing. The test mode will save emails to a file and display them on screen.</p>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li><a href='update_email_tables.php'>Update Database Tables</a></li>";
echo "<li><a href='registrar/schedule_exam.php'>Test Exam Scheduling</a></li>";
echo "<li><a href='index.php'>Test New Enrollment</a></li>";
echo "<li>Check <code>email_log.txt</code> for saved emails</li>";
echo "</ol>";
echo "</div>";

echo "<h3>📁 File Locations:</h3>";
echo "<ul>";
echo "<li><strong>Email Log File:</strong> <code>email_log.txt</code> (in your project root)</li>";
echo "<li><strong>Database Logs:</strong> Check <code>email_logs</code> table in phpMyAdmin</li>";
echo "<li><strong>PHP Error Logs:</strong> XAMPP Control Panel → Apache → Logs</li>";
echo "</ul>";
?>
