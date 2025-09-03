<?php
session_start();
require_once 'config/database.php';
require_once 'includes/EmailHelper.php';

$database = new Database();
$db = $database->connect();
$emailHelper = new EmailHelper($db);

$message = '';
$error = '';
$pendingEnrollment = null;

// Check if token is provided
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $pendingEnrollment = $emailHelper->isValidToken($token);
    
    if (!$pendingEnrollment) {
        $error = "Invalid or expired verification link. Please submit your enrollment application again.";
    }
} else {
    $error = "No verification token provided.";
}

// Handle verification confirmation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_verification'])) {
    try {
        // Move from pending_enrollments to enrollees table
        $query = "INSERT INTO enrollees (
            email, lastname, firstname, middlename, lrn, address, gender,
            date_of_birth, age, civil_status, contact_no, last_school,
            school_address, strand, preferred_program, is_working, employer,
            position, working_hours, preferred_schedule, father_name,
            father_occupation, father_education, father_contact, num_brothers,
            family_income, mother_name, mother_occupation, mother_education,
            mother_contact, num_sisters, guardian_name, guardian_contact
        ) SELECT 
            email, lastname, firstname, middlename, lrn, address, gender,
            date_of_birth, age, civil_status, contact_no, last_school,
            school_address, strand, preferred_program, is_working, employer,
            position, working_hours, preferred_schedule, father_name,
            father_occupation, father_education, father_contact, num_brothers,
            family_income, mother_name, mother_occupation, mother_education,
            mother_contact, num_sisters, guardian_name, guardian_contact
        FROM pending_enrollments WHERE verification_token = ?";
        
        $stmt = $db->prepare($query);
        $stmt->execute([$token]);
        
        // Mark as verified
        $updateQuery = "UPDATE pending_enrollments SET is_verified = 1 WHERE verification_token = ?";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->execute([$token]);
        
        $message = "Your enrollment has been successfully verified! The registrar will review your application and schedule your entrance exam. You will receive an email notification once your exam is scheduled.";
        
    } catch (Exception $e) {
        $error = "An error occurred during verification. Please try again or contact the registrar's office.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Enrollment - OCC Enrollment System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .verification-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 800px;
            text-align: center;
        }

        .verification-header {
            margin-bottom: 2rem;
        }

        .verification-header i {
            font-size: 4rem;
            color: #667eea;
            margin-bottom: 1rem;
        }

        .verification-header h2 {
            color: #333;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .verification-header p {
            color: #666;
            font-size: 1rem;
        }

        .message {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            font-weight: 500;
        }

        .message.success {
            background: rgba(40, 167, 69, 0.1);
            color: #155724;
            border: 1px solid rgba(40, 167, 69, 0.2);
        }

        .message.error {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.2);
        }

        .enrollment-details {
            background: rgba(102, 126, 234, 0.05);
            border-radius: 15px;
            padding: 2rem;
            margin: 2rem 0;
            text-align: left;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .detail-item {
            padding: 1rem;
            background: white;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }

        .detail-label {
            font-size: 0.8rem;
            color: #666;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }

        .detail-value {
            font-size: 1rem;
            color: #333;
            font-weight: 600;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            padding: 1rem 2rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            margin: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }

        .btn-secondary {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn-secondary:hover {
            background: #667eea;
            color: white;
        }

        .verification-footer {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }

        .verification-footer p {
            color: #666;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .verification-container {
                padding: 2rem;
                margin: 1rem;
            }

            .verification-header h2 {
                font-size: 1.5rem;
            }

            .details-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="verification-container">
        <div class="verification-header">
            <i class="fas fa-envelope-open-text"></i>
            <h2>Verify Your Enrollment</h2>
            <p>Please review your information and confirm your enrollment application</p>
        </div>

        <?php if ($message): ?>
            <div class="message success">
                <i class="fas fa-check-circle"></i>
                <?php echo $message; ?>
            </div>
            <div style="text-align: center;">
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-home"></i>
                    Return to Home
                </a>
            </div>
        <?php elseif ($error): ?>
            <div class="message error">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo $error; ?>
            </div>
            <div style="text-align: center;">
                <a href="enrollment_form.php" class="btn btn-primary">
                    <i class="fas fa-edit"></i>
                    Submit New Application
                </a>
            </div>
        <?php elseif ($pendingEnrollment): ?>
            <div class="enrollment-details">
                <h3 style="margin-bottom: 1.5rem; color: #333;">Enrollment Information</h3>
                <div class="details-grid">
                    <div class="detail-item">
                        <div class="detail-label">Full Name</div>
                        <div class="detail-value"><?php echo htmlspecialchars($pendingEnrollment['firstname'] . ' ' . $pendingEnrollment['middlename'] . ' ' . $pendingEnrollment['lastname']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Email</div>
                        <div class="detail-value"><?php echo htmlspecialchars($pendingEnrollment['email']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Contact Number</div>
                        <div class="detail-value"><?php echo htmlspecialchars($pendingEnrollment['contact_no']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Preferred Program</div>
                        <div class="detail-value"><?php echo htmlspecialchars($pendingEnrollment['preferred_program']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Last School Attended</div>
                        <div class="detail-value"><?php echo htmlspecialchars($pendingEnrollment['last_school']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Strand</div>
                        <div class="detail-value"><?php echo htmlspecialchars($pendingEnrollment['strand']); ?></div>
                    </div>
                </div>
            </div>

            <form method="POST" style="margin: 2rem 0;">
                <p style="margin-bottom: 1.5rem; color: #666;">
                    <i class="fas fa-info-circle"></i>
                    Please review your information above. If everything is correct, click "Confirm Enrollment" to proceed.
                </p>
                <button type="submit" name="confirm_verification" class="btn btn-primary">
                    <i class="fas fa-check"></i>
                    Confirm Enrollment
                </button>
                <a href="enrollment_form.php" class="btn btn-secondary">
                    <i class="fas fa-edit"></i>
                    Edit Information
                </a>
            </form>
        <?php endif; ?>

        <div class="verification-footer">
            <p>
                <i class="fas fa-shield-alt"></i>
                Your information is secure and will only be used for enrollment purposes.
            </p>
        </div>
    </div>
</body>
</html>
