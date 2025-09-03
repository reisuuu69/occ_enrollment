# Email Troubleshooting Guide - OCC Enrollment System

## Problem: Emails are not being sent after scheduling entrance exams

### Step 1: Run the Email Test Script

1. Open your browser and go to: `http://localhost/occ_enrollment/test_email.php`
2. This will show you the current email configuration status
3. Check if the mail() function is available and configured

### Step 2: Configure XAMPP Email Settings

#### Option A: Using Mercury Mail Server (Recommended for Local Development)

1. **Install Mercury Mail Server:**
   - Download Mercury from: https://www.pmail.com/overviews/ovw_mercury.htm
   - Install it on your system

2. **Configure PHP to use Mercury:**
   - Open XAMPP Control Panel
   - Click "Config" button for Apache
   - Select "php.ini"
   - Find the `[mail function]` section
   - Update these settings:
   ```ini
   [mail function]
   SMTP = localhost
   smtp_port = 25
   sendmail_from = noreply@occ.edu.ph
   ```

3. **Configure Mercury:**
   - Open Mercury Mail Server
   - Go to Configuration → Mercury Core Module
   - Set "Local host name" to: `localhost`
   - Go to Configuration → Mercury SMTP Server
   - Make sure it's listening on port 25

#### Option B: Using Gmail SMTP (Recommended for Production)

1. **Enable 2-Factor Authentication on your Gmail account**

2. **Generate an App Password:**
   - Go to Google Account settings
   - Security → 2-Step Verification → App passwords
   - Generate a password for "Mail"

3. **Update the EmailHelper class to use PHPMailer:**
   ```php
   // Install PHPMailer via Composer or download manually
   // Then update the sendEmail method to use SMTP
   ```

### Step 3: Check Email Logs

1. **View Email Logs in Database:**
   ```sql
   SELECT * FROM email_logs ORDER BY created_at DESC LIMIT 10;
   ```

2. **Check PHP Error Logs:**
   - Open XAMPP Control Panel
   - Click "Logs" button for Apache
   - Look for email-related errors

### Step 4: Alternative Solutions

#### Solution 1: Use a Third-Party Email Service

**Mailgun (Recommended):**
1. Sign up for a free Mailgun account
2. Get your API key
3. Update the EmailHelper to use Mailgun's API

**SendGrid:**
1. Sign up for a free SendGrid account
2. Get your API key
3. Update the EmailHelper to use SendGrid's API

#### Solution 2: Use PHPMailer with SMTP

1. **Install PHPMailer:**
   ```bash
   composer require phpmailer/phpmailer
   ```

2. **Update EmailHelper.php:**
   ```php
   use PHPMailer\PHPMailer\PHPMailer;
   use PHPMailer\PHPMailer\Exception;
   
   // Replace the sendEmail method with SMTP configuration
   ```

### Step 5: Quick Fix for Testing

If you need a quick solution for testing, you can modify the system to:

1. **Save emails to a file instead of sending:**
   ```php
   // In EmailHelper.php, replace mail() with:
   file_put_contents('emails.txt', "To: $to\nSubject: $subject\n\n$message\n\n", FILE_APPEND);
   ```

2. **Display email content on screen:**
   ```php
   // For testing purposes, show the email content
   echo "<div style='border: 1px solid #ccc; padding: 20px; margin: 20px;'>";
   echo "<h3>Email that would be sent:</h3>";
   echo "<strong>To:</strong> $to<br>";
   echo "<strong>Subject:</strong> $subject<br>";
   echo "<strong>Message:</strong><br>";
   echo $message;
   echo "</div>";
   ```

### Step 6: Verify Database Tables

Make sure all required tables exist:

```sql
-- Run these SQL commands in phpMyAdmin
SOURCE database/email_logs_table.sql;
SOURCE database/pending_enrollments_table.sql;
SOURCE database/entrance_exam_table.sql;
```

### Common Issues and Solutions

1. **"mail() function not available"**
   - Solution: Install and configure Mercury Mail Server

2. **"Connection refused"**
   - Solution: Check if SMTP server is running on the specified port

3. **"Authentication failed"**
   - Solution: Use correct SMTP credentials or switch to local mail server

4. **"Email sent but not received"**
   - Solution: Check spam folder, verify email address, use a reliable SMTP service

### Testing the Fix

1. Run the test script: `http://localhost/occ_enrollment/test_email.php`
2. Try scheduling an entrance exam
3. Check the email logs in the database
4. Verify the email was received

### Production Recommendations

For a production environment:

1. **Use a reliable email service** (Mailgun, SendGrid, Amazon SES)
2. **Implement email queuing** for better performance
3. **Add email templates** for consistent branding
4. **Set up email monitoring** and alerts
5. **Implement retry logic** for failed emails

### Support

If you continue to have issues:

1. Check the PHP error logs
2. Verify your email configuration
3. Test with a simple email first
4. Consider using a third-party email service for reliability
