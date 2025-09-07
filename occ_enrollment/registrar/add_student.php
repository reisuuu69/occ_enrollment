<?php
session_start();
if (!isset($_SESSION['registrar_logged_in'])) {
    header("Location: login.php");
    exit();
}

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $database = new Database();
        $db = $database->connect();

        $query = "INSERT INTO enrollees (
            email, lastname, firstname, middlename, lrn, address,
            gender, date_of_birth, age, civil_status, contact_no,
            last_school, school_address, strand, preferred_program,
            is_working, employer, position, working_hours,
            preferred_schedule, father_name, father_occupation,
            father_education, father_contact, num_brothers,
            family_income, mother_name, mother_occupation,
            mother_education, mother_contact, num_sisters,
            guardian_name, guardian_contact
        ) VALUES (
            :email, :lastname, :firstname, :middlename, :lrn, :address,
            :gender, :dob, :age, :civil_status, :contact,
            :last_school, :school_address, :strand, :program,
            :is_working, :employer, :position, :working_hours,
            :preferred_schedule, :father_name, :father_occupation,
            :father_education, :father_contact, :num_brothers,
            :family_income, :mother_name, :mother_occupation,
            :mother_education, :mother_contact, :num_sisters,
            :guardian_name, :guardian_contact
        )";

        $stmt = $db->prepare($query);
        
        // Bind all parameters
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

        if ($stmt->execute()) {
            header("Location: dashboard.php?success=add");
        } else {
            throw new Exception("Error adding student");
        }
    } catch (Exception $e) {
        header("Location: add_student.php?error=1");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Student</title>
    
</head>`n<body>
    <div class="container">
        <h2>Add New Student</h2>
        <form method="POST">
            <div class="form-section">
                <h3>Personal Information</h3>
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Last Name:</label>
                    <input type="text" name="lastname" required>
                </div>
                <div class="form-group">
                    <label>First Name:</label>
                    <input type="text" name="firstname" required>
                </div>
                <div class="form-group">
                    <label>Middle Name:</label>
                    <input type="text" name="middlename">
                </div>
                <div class="form-group">
                    <label>LRN:</label>
                    <input type="text" name="lrn" required>
                </div>
                <div class="form-group">
                    <label>Address:</label>
                    <input type="text" name="address" required>
                </div>
                <div class="form-group">
                    <label>Gender:</label>
                    <select name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="M">Male</option>
                        <option value="F">Female</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Date of Birth:</label>
                    <input type="date" name="dob" required>
                </div>
                <div class="form-group">
                    <label>Age:</label>
                    <input type="number" name="age" required min="0">
                </div>
                <div class="form-group">
                    <label>Civil Status:</label>
                    <select name="civil_status" required>
                        <option value="">Select Status</option>
                        <option value="Single">Single</option>
                        <option value="Married">Married</option>
                        <option value="Widowed">Widowed</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Contact Number:</label>
                    <input type="text" name="contact" required>
                </div>
            </div>

            <div class="form-section">
                <h3>Educational Information</h3>
                <div class="form-group">
                    <label>Last School Attended:</label>
                    <input type="text" name="last_school" required>
                </div>
                <div class="form-group">
                    <label>School Address:</label>
                    <input type="text" name="school_address" required>
                </div>
                <div class="form-group">
                    <label>Senior High School Strand:</label>
                    <input type="text" name="strand" required>
                </div>
                <div class="form-group">
                    <label>Preferred Program:</label>
                    <select name="program" required>
                        <option value="">Select Program</option>
                        <option value="BSE">BSE</option>
                        <option value="BSIS">BSIS</option>
                        <option value="BTVTED">BTVTED</option>
                    </select>
                </div>
            </div>

            <div class="form-section">
                <h3>Work Information</h3>
                <div class="form-group">
                    <label>Working Student:</label>
                    <select name="is_working" required>
                        <option value="N">No</option>
                        <option value="Y">Yes</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Employer:</label>
                    <input type="text" name="employer">
                </div>
                <div class="form-group">
                    <label>Position:</label>
                    <input type="text" name="position">
                </div>
                <div class="form-group">
                    <label>Working Hours:</label>
                    <input type="text" name="working_hours">
                </div>
                <div class="form-group">
                    <label>Preferred Schedule:</label>
                    <select name="preferred_schedule" required>
                        <option value="">Select Schedule</option>
                        <option value="Morning">Morning</option>
                        <option value="Afternoon">Afternoon</option>
                        <option value="Evening">Evening</option>
                    </select>
                </div>
            </div>

            <div class="form-section">
                <h3>Family Information</h3>
                <div class="form-group">
                    <label>Father's Name:</label>
                    <input type="text" name="father_name" required>
                </div>
                <div class="form-group">
                    <label>Father's Occupation:</label>
                    <input type="text" name="father_occupation">
                </div>
                <div class="form-group">
                    <label>Father's Education:</label>
                    <input type="text" name="father_education">
                </div>
                <div class="form-group">
                    <label>Father's Contact:</label>
                    <input type="text" name="father_contact">
                </div>
                <div class="form-group">
                    <label>Number of Brothers:</label>
                    <input type="number" name="num_brothers" min="0" value="0">
                </div>
                <div class="form-group">
                    <label>Mother's Name:</label>
                    <input type="text" name="mother_name" required>
                </div>
                <div class="form-group">
                    <label>Mother's Occupation:</label>
                    <input type="text" name="mother_occupation">
                </div>
                <div class="form-group">
                    <label>Mother's Education:</label>
                    <input type="text" name="mother_education">
                </div>
                <div class="form-group">
                    <label>Mother's Contact:</label>
                    <input type="text" name="mother_contact">
                </div>
                <div class="form-group">
                    <label>Number of Sisters:</label>
                    <input type="number" name="num_sisters" min="0" value="0">
                </div>
                <div class="form-group">
                    <label>Combined Family Income:</label>
                    <input type="number" name="family_income" required>
                </div>
                <div class="form-group">
                    <label>Guardian's Name:</label>
                    <input type="text" name="guardian_name" required>
                </div>
                <div class="form-group">
                    <label>Guardian's Contact:</label>
                    <input type="text" name="guardian_contact" required>
                </div>
            </div>

            <div class="buttons">
                <button type="submit" class="btn btn-submit">Add Student</button>
                <a href="dashboard.php" class="btn btn-back">Back to Dashboard</a>
            </div>
        </form>
    </div>

    <script>
        // Mobile sidebar toggle functionality
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.querySelector('.mobile-menu-toggle');
            
            if (window.innerWidth <= 768 && 
                !sidebar.contains(event.target) && 
                !toggle.contains(event.target)) {
                sidebar.classList.remove('show');
            }
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');
            if (window.innerWidth > 768) {
                sidebar.classList.remove('show');
            }
        });
    </script>
</body>
</html>
