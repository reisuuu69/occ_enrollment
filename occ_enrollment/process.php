<?php
require_once 'config/database.php';
require_once 'includes/EmailHelper_Test.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $database = new Database();
    $db = $database->connect();
    $emailHelper = new EmailHelper_Test($db);

    try {
        // Generate verification token
        $verificationToken = $emailHelper->generateVerificationToken();
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $query = "INSERT INTO pending_enrollments (
            verification_token, email, lastname, firstname, middlename, lrn, address, gender,
            date_of_birth, age, civil_status, contact_no, last_school,
            school_address, strand, preferred_program, is_working, employer,
            position, working_hours, preferred_schedule, father_name,
            father_occupation, father_education, father_contact, num_brothers,
            family_income, mother_name, mother_occupation, mother_education,
            mother_contact, num_sisters, guardian_name, guardian_contact,
            verification_expires
        ) VALUES (
            :token, :email, :lastname, :firstname, :middlename, :lrn, :address, :gender,
            :dob, :age, :civil_status, :contact, :last_school, :school_address,
            :strand, :program, :is_working, :employer, :position, :working_hours,
            :preferred_schedule, :father_name, :father_occupation, :father_education,
            :father_contact, :num_brothers, :family_income, :mother_name,
            :mother_occupation, :mother_education, :mother_contact, :num_sisters,
            :guardian_name, :guardian_contact, :expires
        )";

        $stmt = $db->prepare($query);
        
        // Bind parameters
        $stmt->bindParam(':token', $verificationToken);
        $stmt->bindParam(':email', $_POST['email']);
        $stmt->bindParam(':lastname', $_POST['lastname']);
        $stmt->bindParam(':firstname', $_POST['firstname']);
        $stmt->bindParam(':middlename', $_POST['middlename']);
        $stmt->bindParam(':lrn', $_POST['lrn']);
        $stmt->bindParam(':address', $_POST['address']);
        $stmt->bindParam(':gender', $_POST['gender']);
        $stmt->bindParam(':dob', $_POST['dob']);
        $stmt->bindParam(':age', $_POST['age']);
        $stmt->bindParam(':civil_status', $_POST['civil_status']);
        $stmt->bindParam(':contact', $_POST['contact']);
        $stmt->bindParam(':last_school', $_POST['last_school']);
        $stmt->bindParam(':school_address', $_POST['school_address']);
        $stmt->bindParam(':strand', $_POST['strand']);
        $stmt->bindParam(':program', $_POST['program']);
        $stmt->bindParam(':is_working', $_POST['is_working']);
        $stmt->bindParam(':employer', $_POST['employer']);
        $stmt->bindParam(':position', $_POST['position']);
        $stmt->bindParam(':working_hours', $_POST['working_hours']);
        $stmt->bindParam(':preferred_schedule', $_POST['preferred_schedule']);
        $stmt->bindParam(':father_name', $_POST['father_name']);
        $stmt->bindParam(':father_occupation', $_POST['father_occupation']);
        $stmt->bindParam(':father_education', $_POST['father_education']);
        $stmt->bindParam(':father_contact', $_POST['father_contact']);
        $stmt->bindParam(':num_brothers', $_POST['num_brothers']);
        $stmt->bindParam(':family_income', $_POST['family_income']);
        $stmt->bindParam(':mother_name', $_POST['mother_name']);
        $stmt->bindParam(':mother_occupation', $_POST['mother_occupation']);
        $stmt->bindParam(':mother_education', $_POST['mother_education']);
        $stmt->bindParam(':mother_contact', $_POST['mother_contact']);
        $stmt->bindParam(':num_sisters', $_POST['num_sisters']);
        $stmt->bindParam(':guardian_name', $_POST['guardian_name']);
        $stmt->bindParam(':guardian_contact', $_POST['guardian_contact']);
        $stmt->bindParam(':expires', $expiresAt);

        $stmt->execute();
        
        // Send verification email
        $emailSent = $emailHelper->sendVerificationEmail(
            $_POST['email'], 
            $verificationToken, 
            $_POST['firstname'], 
            $_POST['lastname']
        );
        
        if ($emailSent) {
            header("Location: index.php?status=verification_sent");
        } else {
            header("Location: index.php?status=verification_error");
        }
        exit();
        
    } catch(PDOException $e) {
        header("Location: index.php?status=error&message=" . urlencode($e->getMessage()));
        exit();
    }
}
?>
