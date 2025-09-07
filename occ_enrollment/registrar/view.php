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
    $query = "SELECT * FROM enrollees WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
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
    <title>Student Details</title>
    
</head>`n<body>
    <div class="container">
        <a href="dashboard.php" class="back-btn">&larr; Back to Dashboard</a>
        <h2>Student Details</h2>
        
        <div class="section">
            <h3>Personal Information</h3>
            <div class="field">
                <span class="label">Name:</span>
                <?php echo $student['lastname'] . ', ' . $student['firstname'] . ' ' . $student['middlename']; ?>
            </div>
            <div class="field">
                <span class="label">Email:</span>
                <?php echo $student['email']; ?>
            </div>
            <div class="field">
                <span class="label">LRN:</span>
                <?php echo $student['lrn']; ?>
            </div>
            <div class="field">
                <span class="label">Address:</span>
                <?php echo $student['address']; ?>
            </div>
            <div class="field">
                <span class="label">Gender:</span>
                <?php echo ($student['gender'] == 'M') ? 'Male' : 'Female'; ?>
            </div>
            <div class="field">
                <span class="label">Date of Birth:</span>
                <?php echo $student['date_of_birth']; ?>
            </div>
            <div class="field">
                <span class="label">Age:</span>
                <?php echo $student['age']; ?>
            </div>
            <div class="field">
                <span class="label">Civil Status:</span>
                <?php echo $student['civil_status']; ?>
            </div>
            <div class="field">
                <span class="label">Contact Number:</span>
                <?php echo $student['contact_no']; ?>
            </div>
        </div>

        <div class="section">
            <h3>Educational Information</h3>
            <div class="field">
                <span class="label">Last School:</span>
                <?php echo $student['last_school']; ?>
            </div>
            <div class="field">
                <span class="label">School Address:</span>
                <?php echo $student['school_address']; ?>
            </div>
            <div class="field">
                <span class="label">Senior High School Strand:</span>
                <?php echo $student['strand']; ?>
            </div>
            <div class="field">
                <span class="label">Preferred Program:</span>
                <?php echo $student['preferred_program']; ?>
            </div>
        </div>

        <div class="section">
            <h3>Work Information</h3>
            <div class="field">
                <span class="label">Working Student:</span>
                <?php echo ($student['is_working'] == 'Y') ? 'Yes' : 'No'; ?>
            </div>
            <div class="field">
                <span class="label">Employer:</span>
                <?php echo $student['employer']; ?>
            </div>
            <div class="field">
                <span class="label">Position:</span>
                <?php echo $student['position']; ?>
            </div>
            <div class="field">
                <span class="label">Working Hours:</span>
                <?php echo $student['working_hours']; ?>
            </div>
            <div class="field">
                <span class="label">Preferred Schedule:</span>
                <?php echo $student['preferred_schedule']; ?>
            </div>
        </div>

        <div class="section">
            <h3>Family Information</h3>
            <div class="field">
                <span class="label">Father's Name:</span>
                <?php echo $student['father_name']; ?>
            </div>
            <div class="field">
                <span class="label">Father's Occupation:</span>
                <?php echo $student['father_occupation']; ?>
            </div>
            <div class="field">
                <span class="label">Father's Education:</span>
                <?php echo $student['father_education']; ?>
            </div>
            <div class="field">
                <span class="label">Father's Contact:</span>
                <?php echo $student['father_contact']; ?>
            </div>
            <div class="field">
                <span class="label">Number of Brothers:</span>
                <?php echo $student['num_brothers']; ?>
            </div>
            <div class="field">
                <span class="label">Mother's Name:</span>
                <?php echo $student['mother_name']; ?>
            </div>
            <div class="field">
                <span class="label">Mother's Occupation:</span>
                <?php echo $student['mother_occupation']; ?>
            </div>
            <div class="field">
                <span class="label">Mother's Education:</span>
                <?php echo $student['mother_education']; ?>
            </div>
            <div class="field">
                <span class="label">Mother's Contact:</span>
                <?php echo $student['mother_contact']; ?>
            </div>
            <div class="field">
                <span class="label">Number of Sisters:</span>
                <?php echo $student['num_sisters']; ?>
            </div>
            <div class="field">
                <span class="label">Combined Family Income:</span>
                ₱<?php echo number_format($student['family_income'], 2); ?>
            </div>
            <div class="field">
                <span class="label">Guardian's Name:</span>
                <?php echo $student['guardian_name']; ?>
            </div>
            <div class="field">
                <span class="label">Guardian's Contact:</span>
                <?php echo $student['guardian_contact']; ?>
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
