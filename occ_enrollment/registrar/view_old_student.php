<?php
session_start();
if (!isset($_SESSION['registrar_logged_in'])) {
    header("Location: login.php");
    exit();
}

require_once '../config/database.php';
$database = new Database();
$db = $database->connect();

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "SELECT * FROM old_students WHERE student_id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$student) {
        header("Location: old_students.php");
        exit();
    }
} else {
    header("Location: old_students.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Student Details</title>
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
        .section {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
        }
        .field {
            margin-bottom: 10px;
            padding: 5px 0;
        }
        .label {
            font-weight: bold;
            display: inline-block;
            width: 150px;
        }
        .btn-back {
            display: inline-block;
            padding: 10px 20px;
            background-color: #666;
            color: white;
            text-decoration: none;
            border-radius: 3px;
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
        <a href="old_students.php" class="btn-back">← Back to List</a>
        <h2>Student Details</h2>

        <div class="section">
            <h3>Student Information</h3>
            <div class="field">
                <span class="label">Student ID:</span>
                <?php echo htmlspecialchars($student['student_id']); ?>
            </div>
            <div class="field">
                <span class="label">Name:</span>
                <?php echo htmlspecialchars($student['lastname'] . ', ' . $student['firstname'] . ' ' . $student['middlename']); ?>
            </div>
            <div class="field">
                <span class="label">Course:</span>
                <?php echo htmlspecialchars($student['course']); ?>
            </div>
            <div class="field">
                <span class="label">Year Level:</span>
                <?php echo htmlspecialchars($student['year_level']); ?>
            </div>
            <div class="field">
                <span class="label">Section:</span>
                <?php echo htmlspecialchars($student['section']); ?>
            </div>
            <div class="field">
                <span class="label">Units Earned:</span>
                <?php echo htmlspecialchars($student['units_earned']); ?>
            </div>
            <div class="field">
                <span class="label">GPA:</span>
                <?php echo htmlspecialchars($student['gpa']); ?>
            </div>
            <div class="field">
                <span class="label">Status:</span>
                <?php echo htmlspecialchars($student['status']); ?>
            </div>
        </div>

        <div class="section">
            <h3>Personal Information</h3>
            <div class="field">
                <span class="label">Email:</span>
                <?php echo htmlspecialchars($student['email']); ?>
            </div>
            <div class="field">
                <span class="label">Contact Number:</span>
                <?php echo htmlspecialchars($student['contact_no']); ?>
            </div>
            <div class="field">
                <span class="label">Address:</span>
                <?php echo htmlspecialchars($student['address']); ?>
            </div>
            <div class="field">
                <span class="label">Gender:</span>
                <?php echo ($student['gender'] == 'M') ? 'Male' : 'Female'; ?>
            </div>
            <div class="field">
                <span class="label">Guardian Name:</span>
                <?php echo htmlspecialchars($student['guardian_name']); ?>
            </div>
            <div class="field">
                <span class="label">Guardian Contact:</span>
                <?php echo htmlspecialchars($student['guardian_contact']); ?>
            </div>
        </div>

        <div class="section">
            <h3>Current Semester Information</h3>
            <div class="field">
                <span class="label">Semester:</span>
                <?php echo htmlspecialchars($student['current_semester']); ?>
            </div>
            <div class="field">
                <span class="label">School Year:</span>
                <?php echo htmlspecialchars($student['school_year']); ?>
            </div>
        </div>
    </div>
</body>
</html>
