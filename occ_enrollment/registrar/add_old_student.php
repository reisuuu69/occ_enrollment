<?php
session_start();
if (!isset($_SESSION['registrar_logged_in'])) {
    header("Location: login.php");
    exit();
}

require_once '../config/database.php';
$database = new Database();
$db = $database->connect();

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Validate required fields
        $required_fields = [
            'student_id', 'lastname', 'firstname', 'course', 'year_level', 'section',
            'current_semester', 'school_year', 'email', 'lrn', 'address', 'gender',
            'date_of_birth', 'age', 'civil_status', 'contact_no', 'last_school',
            'school_address', 'strand', 'preferred_program', 'preferred_schedule',
            'father_name', 'family_income', 'mother_name', 'guardian_name',
            'guardian_contact', 'username', 'password'
        ];

        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Field '$field' is required.");
            }
        }

        // Check if student_id already exists
        $check_query = "SELECT student_id FROM old_students WHERE student_id = ?";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->execute([$_POST['student_id']]);
        
        if ($check_stmt->fetch()) {
            throw new Exception("Student ID already exists.");
        }

        // Check if username already exists
        $check_username = "SELECT username FROM old_students WHERE username = ?";
        $check_username_stmt = $db->prepare($check_username);
        $check_username_stmt->execute([$_POST['username']]);
        
        if ($check_username_stmt->fetch()) {
            throw new Exception("Username already exists.");
        }

        // Hash password
        $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // Prepare insert query
        $query = "INSERT INTO old_students (
            student_id, lastname, firstname, middlename, course, year_level, section,
            current_semester, school_year, units_earned, gpa, status, email, lrn, address,
            gender, date_of_birth, age, civil_status, contact_no, last_school,
            school_address, strand, preferred_program, is_working, employer, position,
            working_hours, preferred_schedule, father_name, father_occupation,
            father_contact, num_brothers, family_income, mother_name, mother_occupation,
            mother_contact, num_sisters, guardian_name, guardian_contact, username, password
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
        )";

        $stmt = $db->prepare($query);
        $stmt->execute([
            $_POST['student_id'],
            $_POST['lastname'],
            $_POST['firstname'],
            $_POST['middlename'] ?? '',
            $_POST['course'],
            $_POST['year_level'],
            $_POST['section'],
            $_POST['current_semester'],
            $_POST['school_year'],
            $_POST['units_earned'] ?? 0,
            $_POST['gpa'] ?? null,
            $_POST['status'] ?? 'Regular',
            $_POST['email'],
            $_POST['lrn'],
            $_POST['address'],
            $_POST['gender'],
            $_POST['date_of_birth'],
            $_POST['age'],
            $_POST['civil_status'],
            $_POST['contact_no'],
            $_POST['last_school'],
            $_POST['school_address'],
            $_POST['strand'],
            $_POST['preferred_program'],
            $_POST['is_working'] ?? 'N',
            $_POST['employer'] ?? '',
            $_POST['position'] ?? '',
            $_POST['working_hours'] ?? '',
            $_POST['preferred_schedule'],
            $_POST['father_name'],
            $_POST['father_occupation'] ?? '',
            $_POST['father_contact'] ?? '',
            $_POST['num_brothers'] ?? 0,
            $_POST['family_income'],
            $_POST['mother_name'],
            $_POST['mother_occupation'] ?? '',
            $_POST['mother_contact'] ?? '',
            $_POST['num_sisters'] ?? 0,
            $_POST['guardian_name'],
            $_POST['guardian_contact'],
            $_POST['username'],
            $hashed_password
        ]);

        $message = "Old student added successfully!";
        
        // Clear form data after successful submission
        $_POST = array();
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get courses for dropdown
$courses_query = "SELECT course_code, course_name FROM courses ORDER BY course_code";
$courses_stmt = $db->query($courses_query);
$courses = $courses_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Old Student</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; display: flex; }
        .sidenav {
            width: 250px;
            height: 100vh;
            background: #333;
            padding: 20px 0;
            position: fixed;
        }
        .sidenav h2 {
            color: white;
            text-align: center;
            margin-bottom: 20px;
            padding: 0 20px;
        }
        .sidenav a {
            display: block;
            color: white;
            padding: 15px 20px;
            text-decoration: none;
            transition: 0.3s;
        }
        .sidenav a:hover { background: #444; }
        .sidenav a.active { background: #4CAF50; }
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
        }
        .form-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .form-section {
            background: #f9f9f9;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .form-section h3 {
            margin-bottom: 15px;
            color: #333;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 5px;
        }
        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
        }
        .form-group {
            flex: 1;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .form-group textarea {
            height: 80px;
            resize: vertical;
        }
        .required {
            color: red;
        }
        .btn {
            padding: 10px 20px;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            margin-right: 10px;
        }
        .btn-primary { background-color: #4CAF50; }
        .btn-secondary { background-color: #666; }
        .btn:hover { opacity: 0.8; }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="sidenav">
        <h2>OCC Registrar</h2>
        <a href="dashboard.php">Enrollment List</a>
        <a href="old_students.php" class="active">Old Students</a>
        <a href="faculty_list.php">Faculty</a>
        <a href="subject_list.php">Subjects</a>
        <a href="course_list.php">Courses</a>
        <a href="logout.php" style="margin-top: auto;">Logout</a>
    </div>

    <div class="main-content">
        <div class="header">
            <h2>Add Old Student</h2>
            <a href="old_students.php" class="btn btn-secondary">Back to List</a>
        </div>

        <?php if ($message): ?>
            <div class="message success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" action="">
                <!-- Basic Information -->
                <div class="form-section">
                    <h3>Basic Information</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="student_id">Student ID <span class="required">*</span></label>
                            <input type="text" id="student_id" name="student_id" value="<?php echo htmlspecialchars($_POST['student_id'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="lrn">LRN <span class="required">*</span></label>
                            <input type="text" id="lrn" name="lrn" value="<?php echo htmlspecialchars($_POST['lrn'] ?? ''); ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="lastname">Last Name <span class="required">*</span></label>
                            <input type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars($_POST['lastname'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="firstname">First Name <span class="required">*</span></label>
                            <input type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars($_POST['firstname'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="middlename">Middle Name</label>
                            <input type="text" id="middlename" name="middlename" value="<?php echo htmlspecialchars($_POST['middlename'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="gender">Gender <span class="required">*</span></label>
                            <select id="gender" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="M" <?php echo ($_POST['gender'] ?? '') === 'M' ? 'selected' : ''; ?>>Male</option>
                                <option value="F" <?php echo ($_POST['gender'] ?? '') === 'F' ? 'selected' : ''; ?>>Female</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="date_of_birth">Date of Birth <span class="required">*</span></label>
                            <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($_POST['date_of_birth'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="age">Age <span class="required">*</span></label>
                            <input type="number" id="age" name="age" min="15" max="100" value="<?php echo htmlspecialchars($_POST['age'] ?? ''); ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="civil_status">Civil Status <span class="required">*</span></label>
                            <select id="civil_status" name="civil_status" required>
                                <option value="">Select Civil Status</option>
                                <option value="Single" <?php echo ($_POST['civil_status'] ?? '') === 'Single' ? 'selected' : ''; ?>>Single</option>
                                <option value="Married" <?php echo ($_POST['civil_status'] ?? '') === 'Married' ? 'selected' : ''; ?>>Married</option>
                                <option value="Widowed" <?php echo ($_POST['civil_status'] ?? '') === 'Widowed' ? 'selected' : ''; ?>>Widowed</option>
                                <option value="Divorced" <?php echo ($_POST['civil_status'] ?? '') === 'Divorced' ? 'selected' : ''; ?>>Divorced</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="contact_no">Contact Number <span class="required">*</span></label>
                            <input type="text" id="contact_no" name="contact_no" value="<?php echo htmlspecialchars($_POST['contact_no'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email <span class="required">*</span></label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="address">Address <span class="required">*</span></label>
                        <textarea id="address" name="address" required><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                    </div>
                </div>

                <!-- Academic Information -->
                <div class="form-section">
                    <h3>Academic Information</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="course">Course <span class="required">*</span></label>
                            <select id="course" name="course" required>
                                <option value="">Select Course</option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?php echo htmlspecialchars($course['course_code']); ?>" 
                                            <?php echo ($_POST['course'] ?? '') === $course['course_code'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="year_level">Year Level <span class="required">*</span></label>
                            <select id="year_level" name="year_level" required>
                                <option value="">Select Year Level</option>
                                <option value="1" <?php echo ($_POST['year_level'] ?? '') === '1' ? 'selected' : ''; ?>>1st Year</option>
                                <option value="2" <?php echo ($_POST['year_level'] ?? '') === '2' ? 'selected' : ''; ?>>2nd Year</option>
                                <option value="3" <?php echo ($_POST['year_level'] ?? '') === '3' ? 'selected' : ''; ?>>3rd Year</option>
                                <option value="4" <?php echo ($_POST['year_level'] ?? '') === '4' ? 'selected' : ''; ?>>4th Year</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="section">Section <span class="required">*</span></label>
                            <input type="text" id="section" name="section" value="<?php echo htmlspecialchars($_POST['section'] ?? ''); ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="current_semester">Current Semester <span class="required">*</span></label>
                            <select id="current_semester" name="current_semester" required>
                                <option value="">Select Semester</option>
                                <option value="1" <?php echo ($_POST['current_semester'] ?? '') === '1' ? 'selected' : ''; ?>>1st Semester</option>
                                <option value="2" <?php echo ($_POST['current_semester'] ?? '') === '2' ? 'selected' : ''; ?>>2nd Semester</option>
                                <option value="3" <?php echo ($_POST['current_semester'] ?? '') === '3' ? 'selected' : ''; ?>>Summer</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="school_year">School Year <span class="required">*</span></label>
                            <input type="text" id="school_year" name="school_year" placeholder="e.g., 2023-2024" value="<?php echo htmlspecialchars($_POST['school_year'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="units_earned">Units Earned</label>
                            <input type="number" id="units_earned" name="units_earned" min="0" value="<?php echo htmlspecialchars($_POST['units_earned'] ?? '0'); ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="gpa">GPA</label>
                            <input type="number" id="gpa" name="gpa" min="1.0" max="4.0" step="0.01" value="<?php echo htmlspecialchars($_POST['gpa'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="Regular" <?php echo ($_POST['status'] ?? 'Regular') === 'Regular' ? 'selected' : ''; ?>>Regular</option>
                                <option value="Irregular" <?php echo ($_POST['status'] ?? '') === 'Irregular' ? 'selected' : ''; ?>>Irregular</option>
                                <option value="Transferee" <?php echo ($_POST['status'] ?? '') === 'Transferee' ? 'selected' : ''; ?>>Transferee</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Previous School Information -->
                <div class="form-section">
                    <h3>Previous School Information</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="last_school">Last School Attended <span class="required">*</span></label>
                            <input type="text" id="last_school" name="last_school" value="<?php echo htmlspecialchars($_POST['last_school'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="strand">Strand <span class="required">*</span></label>
                            <select id="strand" name="strand" required>
                                <option value="">Select Strand</option>
                                <option value="STEM" <?php echo ($_POST['strand'] ?? '') === 'STEM' ? 'selected' : ''; ?>>STEM</option>
                                <option value="HUMSS" <?php echo ($_POST['strand'] ?? '') === 'HUMSS' ? 'selected' : ''; ?>>HUMSS</option>
                                <option value="ABM" <?php echo ($_POST['strand'] ?? '') === 'ABM' ? 'selected' : ''; ?>>ABM</option>
                                <option value="GAS" <?php echo ($_POST['strand'] ?? '') === 'GAS' ? 'selected' : ''; ?>>GAS</option>
                                <option value="TVL" <?php echo ($_POST['strand'] ?? '') === 'TVL' ? 'selected' : ''; ?>>TVL</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="school_address">School Address <span class="required">*</span></label>
                        <textarea id="school_address" name="school_address" required><?php echo htmlspecialchars($_POST['school_address'] ?? ''); ?></textarea>
                    </div>
                </div>

                <!-- Work Information -->
                <div class="form-section">
                    <h3>Work Information</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="is_working">Currently Working</label>
                            <select id="is_working" name="is_working">
                                <option value="N" <?php echo ($_POST['is_working'] ?? 'N') === 'N' ? 'selected' : ''; ?>>No</option>
                                <option value="Y" <?php echo ($_POST['is_working'] ?? '') === 'Y' ? 'selected' : ''; ?>>Yes</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="preferred_program">Preferred Program <span class="required">*</span></label>
                            <select id="preferred_program" name="preferred_program" required>
                                <option value="">Select Program</option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?php echo htmlspecialchars($course['course_code']); ?>" 
                                            <?php echo ($_POST['preferred_program'] ?? '') === $course['course_code'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($course['course_code']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="preferred_schedule">Preferred Schedule <span class="required">*</span></label>
                            <select id="preferred_schedule" name="preferred_schedule" required>
                                <option value="">Select Schedule</option>
                                <option value="Morning" <?php echo ($_POST['preferred_schedule'] ?? '') === 'Morning' ? 'selected' : ''; ?>>Morning</option>
                                <option value="Afternoon" <?php echo ($_POST['preferred_schedule'] ?? '') === 'Afternoon' ? 'selected' : ''; ?>>Afternoon</option>
                                <option value="Evening" <?php echo ($_POST['preferred_schedule'] ?? '') === 'Evening' ? 'selected' : ''; ?>>Evening</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="employer">Employer</label>
                            <input type="text" id="employer" name="employer" value="<?php echo htmlspecialchars($_POST['employer'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="position">Position</label>
                            <input type="text" id="position" name="position" value="<?php echo htmlspecialchars($_POST['position'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="working_hours">Working Hours</label>
                            <input type="text" id="working_hours" name="working_hours" value="<?php echo htmlspecialchars($_POST['working_hours'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <!-- Family Information -->
                <div class="form-section">
                    <h3>Family Information</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="father_name">Father's Name <span class="required">*</span></label>
                            <input type="text" id="father_name" name="father_name" value="<?php echo htmlspecialchars($_POST['father_name'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="father_occupation">Father's Occupation</label>
                            <input type="text" id="father_occupation" name="father_occupation" value="<?php echo htmlspecialchars($_POST['father_occupation'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="father_contact">Father's Contact</label>
                            <input type="text" id="father_contact" name="father_contact" value="<?php echo htmlspecialchars($_POST['father_contact'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="mother_name">Mother's Name <span class="required">*</span></label>
                            <input type="text" id="mother_name" name="mother_name" value="<?php echo htmlspecialchars($_POST['mother_name'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="mother_occupation">Mother's Occupation</label>
                            <input type="text" id="mother_occupation" name="mother_occupation" value="<?php echo htmlspecialchars($_POST['mother_occupation'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="mother_contact">Mother's Contact</label>
                            <input type="text" id="mother_contact" name="mother_contact" value="<?php echo htmlspecialchars($_POST['mother_contact'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="num_brothers">Number of Brothers</label>
                            <input type="number" id="num_brothers" name="num_brothers" min="0" value="<?php echo htmlspecialchars($_POST['num_brothers'] ?? '0'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="num_sisters">Number of Sisters</label>
                            <input type="number" id="num_sisters" name="num_sisters" min="0" value="<?php echo htmlspecialchars($_POST['num_sisters'] ?? '0'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="family_income">Monthly Family Income <span class="required">*</span></label>
                            <input type="number" id="family_income" name="family_income" min="0" step="0.01" value="<?php echo htmlspecialchars($_POST['family_income'] ?? ''); ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="guardian_name">Guardian's Name <span class="required">*</span></label>
                            <input type="text" id="guardian_name" name="guardian_name" value="<?php echo htmlspecialchars($_POST['guardian_name'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="guardian_contact">Guardian's Contact <span class="required">*</span></label>
                            <input type="text" id="guardian_contact" name="guardian_contact" value="<?php echo htmlspecialchars($_POST['guardian_contact'] ?? ''); ?>" required>
                        </div>
                    </div>
                </div>

                <!-- Account Information -->
                <div class="form-section">
                    <h3>Account Information</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="username">Username <span class="required">*</span></label>
                            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password <span class="required">*</span></label>
                            <input type="password" id="password" name="password" required>
                        </div>
                    </div>
                </div>

                <div style="text-align: center; margin-top: 20px;">
                    <button type="submit" class="btn btn-primary">Add Old Student</button>
                    <a href="old_students.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Auto-calculate age based on date of birth
        document.getElementById('date_of_birth').addEventListener('change', function() {
            const birthDate = new Date(this.value);
            const today = new Date();
            const age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            
            document.getElementById('age').value = age;
        });
    </script>
</body>
</html>
