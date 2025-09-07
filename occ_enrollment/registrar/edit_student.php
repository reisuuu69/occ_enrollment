<?php
session_start();
if (!isset($_SESSION['registrar_logged_in'])) {
    header("Location: login.php");
    exit();
}

require_once '../config/database.php';
$database = new Database();
$db = $database->connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $query = "UPDATE enrollees SET 
            email = :email,
            lastname = :lastname,
            firstname = :firstname,
            middlename = :middlename,
            lrn = :lrn,
            address = :address,
            gender = :gender,
            date_of_birth = :dob,
            age = :age,
            civil_status = :civil_status,
            contact_no = :contact,
            last_school = :last_school,
            school_address = :school_address,
            strand = :strand,
            preferred_program = :program,
            is_working = :is_working,
            employer = :employer,
            position = :position,
            working_hours = :working_hours,
            preferred_schedule = :preferred_schedule,
            father_name = :father_name,
            father_occupation = :father_occupation,
            father_education = :father_education,
            father_contact = :father_contact,
            num_brothers = :num_brothers,
            family_income = :family_income,
            mother_name = :mother_name,
            mother_occupation = :mother_occupation,
            mother_education = :mother_education,
            mother_contact = :mother_contact,
            num_sisters = :num_sisters,
            guardian_name = :guardian_name,
            guardian_contact = :guardian_contact
            WHERE id = :id";

        $stmt = $db->prepare($query);
        
        // Bind all parameters
        $stmt->bindParam(':id', $_POST['id']);
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
            header("Location: dashboard.php?success=update");
        } else {
            throw new Exception("Error updating student");
        }
    } catch (Exception $e) {
        header("Location: edit_student.php?id=" . $_POST['id'] . "&error=1");
    }
    exit();
}

// Fetch student data
if (isset($_GET['id'])) {
    $stmt = $db->prepare("SELECT * FROM enrollees WHERE id = :id");
    $stmt->bindParam(':id', $_GET['id']);
    $stmt->execute();
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$student) {
        header("Location: dashboard.php");
        exit();
    }
} else {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student</title>
    
</head>`n<body>
    <div class="container">
        <h2>Edit Student Information</h2>
        <form method="POST">
            <input type="hidden" name="id" value="<?php echo $student['id']; ?>">
            
            <div class="form-section">
                <h3>Personal Information</h3>
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" value="<?php echo $student['email']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Last Name:</label>
                    <input type="text" name="lastname" value="<?php echo $student['lastname']; ?>" required>
                </div>
                <div class="form-group">
                    <label>First Name:</label>
                    <input type="text" name="firstname" value="<?php echo $student['firstname']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Middle Name:</label>
                    <input type="text" name="middlename" value="<?php echo $student['middlename']; ?>">
                </div>
                <div class="form-group">
                    <label>LRN:</label>
                    <input type="text" name="lrn" value="<?php echo $student['lrn']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Address:</label>
                    <input type="text" name="address" value="<?php echo $student['address']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Gender:</label>
                    <select name="gender" required>
                        <option value="M" <?php echo ($student['gender'] == 'M') ? 'selected' : ''; ?>>Male</option>
                        <option value="F" <?php echo ($student['gender'] == 'F') ? 'selected' : ''; ?>>Female</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Date of Birth:</label>
                    <input type="date" name="dob" value="<?php echo $student['date_of_birth']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Age:</label>
                    <input type="number" name="age" value="<?php echo $student['age']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Civil Status:</label>
                    <select name="civil_status" required>
                        <option value="Single" <?php echo ($student['civil_status'] == 'Single') ? 'selected' : ''; ?>>Single</option>
                        <option value="Married" <?php echo ($student['civil_status'] == 'Married') ? 'selected' : ''; ?>>Married</option>
                        <option value="Widowed" <?php echo ($student['civil_status'] == 'Widowed') ? 'selected' : ''; ?>>Widowed</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Contact Number:</label>
                    <input type="text" name="contact" value="<?php echo $student['contact_no']; ?>" required>
                </div>
            </div>

            <div class="form-section">
                <h3>Educational Information</h3>
                <div class="form-group">
                    <label>Last School Attended:</label>
                    <input type="text" name="last_school" value="<?php echo $student['last_school']; ?>" required>
                </div>
                <div class="form-group">
                    <label>School Address:</label>
                    <input type="text" name="school_address" value="<?php echo $student['school_address']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Senior High School Strand:</label>
                    <input type="text" name="strand" value="<?php echo $student['strand']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Preferred Program:</label>
                    <select name="program" required>
                        <option value="BSE" <?php echo ($student['preferred_program'] == 'BSE') ? 'selected' : ''; ?>>BSE</option>
                        <option value="BSIS" <?php echo ($student['preferred_program'] == 'BSIS') ? 'selected' : ''; ?>>BSIS</option>
                        <option value="BTVTED" <?php echo ($student['preferred_program'] == 'BTVTED') ? 'selected' : ''; ?>>BTVTED</option>
                    </select>
                </div>
            </div>

            <div class="form-section">
                <h3>Work Information</h3>
                <div class="form-group">
                    <label>Working Student:</label>
                    <select name="is_working" required>
                        <option value="Y" <?php echo ($student['is_working'] == 'Y') ? 'selected' : ''; ?>>Yes</option>
                        <option value="N" <?php echo ($student['is_working'] == 'N') ? 'selected' : ''; ?>>No</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Employer:</label>
                    <input type="text" name="employer" value="<?php echo $student['employer']; ?>">
                </div>
                <div class="form-group">
                    <label>Position:</label>
                    <input type="text" name="position" value="<?php echo $student['position']; ?>">
                </div>
                <div class="form-group">
                    <label>Working Hours:</label>
                    <input type="text" name="working_hours" value="<?php echo $student['working_hours']; ?>">
                </div>
                <div class="form-group">
                    <label>Preferred Schedule:</label>
                    <select name="preferred_schedule" required>
                        <option value="Morning" <?php echo ($student['preferred_schedule'] == 'Morning') ? 'selected' : ''; ?>>Morning</option>
                        <option value="Afternoon" <?php echo ($student['preferred_schedule'] == 'Afternoon') ? 'selected' : ''; ?>>Afternoon</option>
                        <option value="Evening" <?php echo ($student['preferred_schedule'] == 'Evening') ? 'selected' : ''; ?>>Evening</option>
                    </select>
                </div>
            </div>

            <div class="form-section">
                <h3>Family Information</h3>
                <div class="form-group">
                    <label>Father's Name:</label>
                    <input type="text" name="father_name" value="<?php echo $student['father_name']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Father's Occupation:</label>
                    <input type="text" name="father_occupation" value="<?php echo $student['father_occupation']; ?>">
                </div>
                <div class="form-group">
                    <label>Father's Education:</label>
                    <input type="text" name="father_education" value="<?php echo $student['father_education']; ?>">
                </div>
                <div class="form-group">
                    <label>Father's Contact:</label>
                    <input type="text" name="father_contact" value="<?php echo $student['father_contact']; ?>">
                </div>
                <div class="form-group">
                    <label>Number of Brothers:</label>
                    <input type="number" name="num_brothers" value="<?php echo $student['num_brothers']; ?>" min="0">
                </div>
                <div class="form-group">
                    <label>Mother's Name:</label>
                    <input type="text" name="mother_name" value="<?php echo $student['mother_name']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Mother's Occupation:</label>
                    <input type="text" name="mother_occupation" value="<?php echo $student['mother_occupation']; ?>">
                </div>
                <div class="form-group">
                    <label>Mother's Education:</label>
                    <input type="text" name="mother_education" value="<?php echo $student['mother_education']; ?>">
                </div>
                <div class="form-group">
                    <label>Mother's Contact:</label>
                    <input type="text" name="mother_contact" value="<?php echo $student['mother_contact']; ?>">
                </div>
                <div class="form-group">
                    <label>Number of Sisters:</label>
                    <input type="number" name="num_sisters" value="<?php echo $student['num_sisters']; ?>" min="0">
                </div>
                <div class="form-group">
                    <label>Combined Family Income:</label>
                    <input type="number" name="family_income" value="<?php echo $student['family_income']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Guardian's Name:</label>
                    <input type="text" name="guardian_name" value="<?php echo $student['guardian_name']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Guardian's Contact:</label>
                    <input type="text" name="guardian_contact" value="<?php echo $student['guardian_contact']; ?>" required>
                </div>
            </div>

            <div class="buttons">
                <button type="submit" class="btn btn-submit">Update Student</button>
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
