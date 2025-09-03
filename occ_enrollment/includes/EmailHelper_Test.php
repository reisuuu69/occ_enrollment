<?php

class EmailHelper_Test {
    private $db;
    private $emailLogFile = 'email_log.txt';
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Send enrollment verification email (TEST VERSION - saves to file)
     */
    public function sendVerificationEmail($email, $token, $firstName, $lastName) {
        $subject = "Verify Your Enrollment Application - OCC Enrollment System";
        
        $verificationLink = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/verify_enrollment.php?token=" . $token;
        
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .btn { display: inline-block; background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>OCC Enrollment System</h2>
                    <p>Email Verification Required</p>
                </div>
                <div class='content'>
                    <h3>Hello {$firstName} {$lastName},</h3>
                    <p>Thank you for submitting your enrollment application to OCC. To complete your enrollment process, please verify your email address by clicking the button below:</p>
                    
                    <div style='text-align: center;'>
                        <a href='{$verificationLink}' class='btn'>Verify My Enrollment</a>
                    </div>
                    
                    <p><strong>Important:</strong> This verification link will expire in 24 hours. If you don't verify your email within this time, you'll need to submit your application again.</p>
                    
                    <p>If the button above doesn't work, you can copy and paste this link into your browser:</p>
                    <p style='word-break: break-all; color: #667eea;'>{$verificationLink}</p>
                    
                    <p>If you didn't submit an enrollment application, please ignore this email.</p>
                    
                    <p>Best regards,<br>
                    OCC Enrollment Team</p>
                </div>
                <div class='footer'>
                    <p>This is an automated message. Please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return $this->sendEmail($email, $subject, $message);
    }
    
    /**
     * Send entrance exam notification email (TEST VERSION - saves to file)
     */
    public function sendEntranceExamNotification($email, $firstName, $lastName, $examDate, $examTime, $examVenue) {
        $subject = "Entrance Exam Schedule - OCC Enrollment System";
        
        $formattedDate = date('F d, Y', strtotime($examDate));
        $formattedTime = date('h:i A', strtotime($examTime));
        
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .exam-details { background: white; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #667eea; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>OCC Enrollment System</h2>
                    <p>Entrance Exam Schedule</p>
                </div>
                <div class='content'>
                    <h3>Hello {$firstName} {$lastName},</h3>
                    <p>Congratulations! Your enrollment application has been verified and approved. We have scheduled your entrance exam as follows:</p>
                    
                    <div class='exam-details'>
                        <h4>📅 Exam Details:</h4>
                        <p><strong>Date:</strong> {$formattedDate}</p>
                        <p><strong>Time:</strong> {$formattedTime}</p>
                        <p><strong>Venue:</strong> {$examVenue}</p>
                    </div>
                    
                    <h4>📋 What to Bring:</h4>
                    <ul>
                        <li>Valid ID (School ID, Government ID, or Birth Certificate)</li>
                        <li>2x2 ID Picture (2 copies)</li>
                        <li>Ballpen and Pencil</li>
                        <li>Calculator (if needed)</li>
                    </ul>
                    
                    <h4>⚠️ Important Reminders:</h4>
                    <ul>
                        <li>Please arrive 30 minutes before the scheduled time</li>
                        <li>Dress appropriately and comfortably</li>
                        <li>Bring your own snacks and water</li>
                        <li>Mobile phones and other electronic devices are not allowed during the exam</li>
                    </ul>
                    
                    <p>If you have any questions or need to reschedule, please contact the registrar's office immediately.</p>
                    
                    <p>Good luck on your entrance exam!</p>
                    
                    <p>Best regards,<br>
                    OCC Enrollment Team</p>
                </div>
                <div class='footer'>
                    <p>This is an automated message. Please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return $this->sendEmail($email, $subject, $message);
    }
    
    /**
     * Save email to file instead of sending (TEST VERSION)
     */
    private function sendEmail($to, $subject, $message) {
        $timestamp = date('Y-m-d H:i:s');
        $emailContent = "
=== EMAIL LOG ENTRY ===
Timestamp: {$timestamp}
To: {$to}
Subject: {$subject}
From: OCC Enrollment System <noreply@occ.edu.ph>
Reply-To: registrar@occ.edu.ph

Message:
{$message}

=== END EMAIL ===

";
        
        // Save to file
        $result = file_put_contents($this->emailLogFile, $emailContent, FILE_APPEND);
        
        // Log to database
        $this->logEmail($to, $subject, $message, 'saved_to_file');
        
        // Display on screen for testing
        echo "<div style='border: 2px solid #667eea; background: #f8f9fa; padding: 20px; margin: 20px; border-radius: 10px;'>";
        echo "<h3 style='color: #667eea; margin-bottom: 15px;'>📧 Email Saved (Test Mode)</h3>";
        echo "<p><strong>To:</strong> {$to}</p>";
        echo "<p><strong>Subject:</strong> {$subject}</p>";
        echo "<p><strong>Timestamp:</strong> {$timestamp}</p>";
        echo "<p><strong>Status:</strong> Email saved to file: {$this->emailLogFile}</p>";
        echo "<details style='margin-top: 15px;'>";
        echo "<summary style='cursor: pointer; color: #667eea; font-weight: bold;'>View Email Content</summary>";
        echo "<div style='margin-top: 10px; padding: 15px; background: white; border-radius: 5px;'>";
        echo $message;
        echo "</div>";
        echo "</details>";
        echo "</div>";
        
        return $result !== false;
    }
    
    /**
     * Log email attempts
     */
    private function logEmail($recipient, $subject, $message, $status = 'saved_to_file', $error_message = null) {
        try {
            $query = "INSERT INTO email_logs (recipient_email, subject, message, status, error_message, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$recipient, $subject, $message, $status, $error_message]);
        } catch (Exception $e) {
            error_log("EmailHelper_Test: Failed to log email: " . $e->getMessage());
        }
    }
    
    /**
     * Generate verification token
     */
    public function generateVerificationToken() {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * Check if verification token is valid and not expired
     */
    public function isValidToken($token) {
        try {
            $query = "SELECT * FROM pending_enrollments WHERE verification_token = ? AND is_verified = 0 AND verification_expires > NOW()";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$token]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get email log file path
     */
    public function getEmailLogFile() {
        return $this->emailLogFile;
    }
}
?>
