<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is a registrar
if (!isset($_SESSION['registrar_logged_in']) || $_SESSION['role'] !== 'registrar') {
    header('Location: login.php');
    exit();
}

$database = new Database();
$pdo = $database->connect();

$action = $_GET['action'] ?? 'list';
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
        try {
            // Generate unique student ID
            $year = date('Y');
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM students WHERE student_id LIKE ?");
            $stmt->execute([$year . '%']);
            $count = $stmt->fetch()['count'];
            $student_id = $year . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
            
            // Create user account first
            $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, 'student', NOW())");
            $stmt->execute([$_POST['email'], $_POST['email'], $hashed_password]);
            $user_id = $pdo->lastInsertId();
            
            // Insert student record
            $stmt = $pdo->prepare("
                INSERT INTO students (
                    student_id, user_id, lastname, firstname, middlename, course, year_level, 
                    section, current_semester, school_year, email, lrn, address, gender, 
                    date_of_birth, age, civil_status, contact_no, last_school, school_address, 
                    strand, preferred_program, father_name, father_occupation, 
                    father_contact, family_income, mother_name, mother_occupation, mother_contact, 
                    guardian_name, guardian_contact, enrollment_date
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $student_id, $user_id, $_POST['lastname'], $_POST['firstname'], $_POST['middlename'],
                $_POST['course'], $_POST['year_level'], $_POST['section'], $_POST['semester'],
                $_POST['school_year'], $_POST['email'], $_POST['lrn'], $_POST['address'],
                $_POST['gender'], $_POST['date_of_birth'], $_POST['age'], $_POST['civil_status'],
                $_POST['contact_no'], $_POST['last_school'], $_POST['school_address'], $_POST['strand'],
                $_POST['preferred_program'], $_POST['father_name'], $_POST['father_occupation'], 
                $_POST['father_contact'], $_POST['family_income'], $_POST['mother_name'],
                $_POST['mother_occupation'], $_POST['mother_contact'], $_POST['guardian_name'],
                $_POST['guardian_contact'], date('Y-m-d')
            ]);
            
            $message = "Student added successfully! Student ID: " . $student_id;
            $action = 'list';
            
        } catch (PDOException $e) {
            $error = "Error adding student: " . $e->getMessage();
        }
    }
}

// Get data for forms
$courses = [];
$sections = [];
try {
    $stmt = $pdo->query("SELECT course_code, course_name FROM courses ORDER BY course_name");
    $courses = $stmt->fetchAll();
    
    $stmt = $pdo->query("SELECT section_id, section_name, shift FROM sections ORDER BY section_name");
    $sections = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error loading data: " . $e->getMessage();
}

// Get students list
$students = [];
if ($action === 'list') {
    try {
        $stmt = $pdo->query("
            SELECT s.*, c.course_name, sec.section_name, sec.shift
            FROM students s
            LEFT JOIN courses c ON s.course = c.course_code
            LEFT JOIN sections sec ON s.section_id = sec.section_id
            ORDER BY s.lastname, s.firstname
        ");
        $students = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error = "Error loading students: " . $e->getMessage();
    }
}

// Get statistics
try {
    $stats_query = "SELECT 
        COUNT(*) as total_students,
        COUNT(CASE WHEN year_level = '1st Year' THEN 1 END) as first_year,
        COUNT(CASE WHEN year_level = '2nd Year' THEN 1 END) as second_year,
        COUNT(CASE WHEN year_level = '3rd Year' THEN 1 END) as third_year,
        COUNT(CASE WHEN year_level = '4th Year' THEN 1 END) as fourth_year
    FROM students";
    $stats_stmt = $pdo->query($stats_query);
    $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $stats = ['total_students' => 0, 'first_year' => 0, 'second_year' => 0, 'third_year' => 0, 'fourth_year' => 0];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - OCC Enrollment System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../includes/modern-dashboard.css" rel="stylesheet">

</head>`n<body>
    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h4><i class="fas fa-clipboard-list"></i> Registrar Panel</h4>
            <p>OCC Enrollment System</p>
        </div>
        
        <nav class="sidebar-nav">
                    <div class="text-center mb-4">
                        <h4 class="text-white">Registrar Panel</h4>
                        <p class="text-white-50 small">OCC Enrollment System</p>
                    </div>
                    
                    <nav class="sidebar-nav">
                        <div class="nav-item">
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Dashboard
                        
                </a>
            </div>
                        <div class="nav-item">
                <a class="nav-link active" href="manage_students.php">
                    <i class="fas fa-user-graduate me-2"></i>
                    Manage Students
                        
                </a>
            </div>
                        <div class="nav-item">
                <a class="nav-link" href="manage_faculty.php">
                    <i class="fas fa-chalkboard-teacher me-2"></i>
                    Manage Faculty
                        
                </a>
            </div>
                        <div class="nav-item">
                <a class="nav-link" href="manage_course.php">
                    <i class="fas fa-book me-2"></i>
                    Course Management
                        
                </a>
            </div>
                        <div class="nav-item">
                <a class="nav-link" href="manage_subjects.php">
                    <i class="fas fa-file-alt me-2"></i>
                    Subject Management
                        
                </a>
            </div>
                        <div class="nav-item">
                <a class="nav-link" href="manage_sections.php">
                    <i class="fas fa-layer-group me-2"></i>
                    Section Management
                        
                </a>
            </div>
                        <div class="nav-item">
                <a class="nav-link" href="manage_schedules.php">
                    <i class="fas fa-calendar-alt me-2"></i>
                    Schedule Management
                        
                </a>
            </div>
                        <div class="nav-item">
                <a class="nav-link" href="manage_rooms.php">
                    <i class="fas fa-building me-2"></i>
                    Room Management
                        
                </a>
            </div>
                        <div class="nav-item">
                <a class="nav-link" href="manage_users.php">
                    <i class="fas fa-users me-2"></i>
                    Manage Users
                        
                </a>
            </div>
                        <div class="nav-item">
                <a class="nav-link" href="assignments.php">
                    <i class="fas fa-tasks me-2"></i>
                    Assignments
                        
                </a>
            </div>
                        <div class="nav-item">
                <a class="nav-link" href="reports.php">
                    <i class="fas fa-chart-bar me-2"></i>
                    Reports
                        
                </a>
            </div>
                        
                        <hr class="text-white-50">
                        <div class="nav-item">
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i>
                    Logout
                        
                </a>
            </div>
                    </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2 class="mb-1">Student Management</h2>
                            <p class="text-muted mb-0">Manage enrolled students and their information</p>
                        </div>
                        <a href="?action=add" class="btn btn-primary btn-custom">
                            <i class="fas fa-plus me-2"></i>Add New Student
                        </a>
                    </div>

                    <!-- Messages -->
                    <?php if ($message): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-4 col-lg-2 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['total_students'] ?? 0); ?></h4>
                                    <p class="mb-0 small">Total Students</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['first_year'] ?? 0); ?></h4>
                                    <p class="mb-0 small">1st Year</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['second_year'] ?? 0); ?></h4>
                                    <p class="mb-0 small">2nd Year</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['third_year'] ?? 0); ?></h4>
                                    <p class="mb-0 small">3rd Year</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['fourth_year'] ?? 0); ?></h4>
                                    <p class="mb-0 small">4th Year</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if ($action === 'list'): ?>
                        <!-- Students Table -->
                        <div class="table-container">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">
                                    <i class="fas fa-list me-2"></i>Students List
                                </h5>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Student ID</th>
                                            <th>Name</th>
                                            <th>Course</th>
                                            <th>Year Level</th>
                                            <th>Section</th>
                                            <th>Email</th>
                                            <th>Contact</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($students)): ?>
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-4">
                                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                                    <br>No students found
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($students as $student): ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-primary"><?php echo htmlspecialchars($student['student_id']); ?></span>
                                                </td>
                                                <td>
                                                    <div class="fw-bold"><?php echo htmlspecialchars($student['lastname'] . ', ' . $student['firstname']); ?></div>
                                                    <small class="text-muted"><?php echo htmlspecialchars($student['middlename']); ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info"><?php echo htmlspecialchars($student['course_name'] ?? $student['course']); ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-warning"><?php echo htmlspecialchars($student['year_level']); ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($student['section_name'] ?? $student['section']); ?></span>
                                                </td>
                                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                                <td><?php echo htmlspecialchars($student['contact_no']); ?></td>
                                                <td>
                                                    <a href="view.php?id=<?php echo $student['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="edit_student.php?id=<?php echo $student['id']; ?>" class="btn btn-sm btn-outline-warning">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="delete_student.php?id=<?php echo $student['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

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
