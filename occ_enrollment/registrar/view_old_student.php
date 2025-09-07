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
    
</head>`n<body>
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
