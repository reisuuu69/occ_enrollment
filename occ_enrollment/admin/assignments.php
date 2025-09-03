<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$database = new Database();
$pdo = $database->connect();

$type = $_GET['type'] ?? 'professor';
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($type === 'professor' && isset($_POST['assign_professor'])) {
        // Assign professor to subject
        try {
            $stmt = $pdo->prepare("
                INSERT INTO subject_professor (subject_id, professor_id, school_year, semester) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $_POST['subject_id'], $_POST['professor_id'], 
                $_POST['school_year'], $_POST['semester']
            ]);
            $message = "Professor assigned to subject successfully!";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "This professor is already assigned to this subject for the specified school year and semester.";
            } else {
                $error = "Error assigning professor: " . $e->getMessage();
            }
        }
    } elseif ($type === 'section' && isset($_POST['assign_section'])) {
        // Assign student to section
        try {
            // Check section capacity
            $stmt = $pdo->prepare("
                SELECT s.max_capacity, s.current_enrollment 
                FROM sections s WHERE s.section_id = ?
            ");
            $stmt->execute([$_POST['section_id']]);
            $section = $stmt->fetch();
            
            if ($section['current_enrollment'] >= $section['max_capacity']) {
                $error = "Section is already at maximum capacity (50 students).";
            } else {
                // Check if student is already assigned to a section for this school year/semester
                $stmt = $pdo->prepare("
                    SELECT id FROM student_sections 
                    WHERE student_id = ? AND school_year = ? AND semester = ?
                ");
                $stmt->execute([$_POST['student_id'], $_POST['school_year'], $_POST['semester']]);
                
                if ($stmt->fetch()) {
                    $error = "Student is already assigned to a section for this school year and semester.";
                } else {
                    // Assign student to section
                    $stmt = $pdo->prepare("
                        INSERT INTO student_sections (student_id, section_id, school_year, semester, assigned_date) 
                        VALUES (?, ?, ?, ?, CURDATE())
                    ");
                    $stmt->execute([
                        $_POST['student_id'], $_POST['section_id'], 
                        $_POST['school_year'], $_POST['semester']
                    ]);
                    
                    // Update section enrollment count
                    $stmt = $pdo->prepare("
                        UPDATE sections SET current_enrollment = current_enrollment + 1 
                        WHERE section_id = ?
                    ");
                    $stmt->execute([$_POST['section_id']]);
                    
                    $message = "Student assigned to section successfully!";
                }
            }
        } catch (PDOException $e) {
            $error = "Error assigning student to section: " . $e->getMessage();
        }
    }
}

// Get current school year and semester
$current_year = date('Y') . '-' . (date('Y') + 1);
$current_semester = (date('n') >= 6 && date('n') <= 10) ? 1 : 2;

// Get data for forms
$subjects = [];
$professors = [];
$students = [];
$sections = [];
$assignments = [];

try {
    // Get subjects
    $stmt = $pdo->query("
        SELECT s.id, s.subject_code, s.subject_name, s.course_code, s.year_level, s.semester, c.course_name
        FROM subjects s
        JOIN courses c ON s.course_code = c.course_code
        ORDER BY s.course_code, s.year_level, s.semester
    ");
    $subjects = $stmt->fetchAll();
    
    // Get professors
    $stmt = $pdo->query("SELECT id, professor_name, department, specialization FROM faculty ORDER BY professor_name");
    $professors = $stmt->fetchAll();
    
    // Get students
    $stmt = $pdo->query("
        SELECT student_id, lastname, firstname, course, year_level, email 
        FROM students 
        ORDER BY lastname, firstname
    ");
    $students = $stmt->fetchAll();
    
    // Get sections
    $stmt = $pdo->query("
        SELECT section_id, section_name, shift, max_capacity, current_enrollment 
        FROM sections 
        ORDER BY section_name
    ");
    $sections = $stmt->fetchAll();
    
    // Get current assignments
    if ($type === 'professor') {
        $stmt = $pdo->prepare("
            SELECT sp.*, s.subject_name, s.subject_code, f.professor_name, f.department
            FROM subject_professor sp
            JOIN subjects s ON sp.subject_id = s.id
            JOIN faculty f ON sp.professor_id = f.id
            WHERE sp.school_year = ? AND sp.semester = ?
            ORDER BY s.subject_name
        ");
        $stmt->execute([$current_year, $current_semester]);
        $assignments = $stmt->fetchAll();
    } elseif ($type === 'section') {
        $stmt = $pdo->prepare("
            SELECT ss.*, s.lastname, s.firstname, s.course, sec.section_name, sec.shift
            FROM student_sections ss
            JOIN students s ON ss.student_id = s.student_id
            JOIN sections sec ON ss.section_id = sec.section_id
            WHERE ss.school_year = ? AND ss.semester = ?
            ORDER BY sec.section_name, s.lastname
        ");
        $stmt->execute([$current_year, $current_semester]);
        $assignments = $stmt->fetchAll();
    }
    
} catch (PDOException $e) {
    $error = "Error loading data: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignments - Registrar Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            margin: 5px 0;
            border-radius: 8px;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }
        .main-content {
            background: #f8f9fa;
            min-height: 100vh;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .btn-custom {
            border-radius: 25px;
            padding: 8px 20px;
            font-weight: 500;
        }
        .nav-pills .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .capacity-indicator {
            font-size: 0.8rem;
            padding: 2px 8px;
            border-radius: 12px;
        }
        .capacity-full {
            background: #dc3545;
            color: white;
        }
        .capacity-available {
            background: #28a745;
            color: white;
        }
        .capacity-warning {
            background: #ffc107;
            color: #212529;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar p-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white">Admin Panel</h4>
                        <p class="text-white-50 small">OCC Enrollment System</p>
                    </div>
                    
                    <nav class="nav flex-column">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                        </a>
                        <a class="nav-link" href="manage_students.php">
                            <i class="fas fa-user-graduate me-2"></i> Manage Students
                        </a>
                        <a class="nav-link" href="manage_faculty.php">
                            <i class="fas fa-chalkboard-teacher me-2"></i> Manage Faculty
                        </a>
                        <a class="nav-link" href="manage_course.php">
                            <i class="fas fa-book me-2"></i> Course Management
                        </a>
                        <a class="nav-link" href="manage_subjects.php">
                            <i class="fas fa-file-alt me-2"></i> Subject Management
                        </a>
                        <a class="nav-link" href="manage_sections.php">
                            <i class="fas fa-layer-group me-2"></i> Section Management
                        </a>
                        <a class="nav-link" href="manage_schedules.php">
                            <i class="fas fa-calendar-alt me-2"></i> Schedule Management
                        </a>
                        <a class="nav-link" href="manage_rooms.php">
                            <i class="fas fa-building me-2"></i> Room Management
                        </a>
                        <a class="nav-link" href="manage_users.php">
                            <i class="fas fa-users me-2"></i> Manage Users
                        </a>
                        <a class="nav-link active" href="assignments.php">
                            <i class="fas fa-tasks me-2"></i> Assignments
                        </a>
                        <a class="nav-link" href="reports.php">
                            <i class="fas fa-chart-bar me-2"></i> Reports
                        </a>
                        <a class="nav-link" href="audit_logs.php">
                            <i class="fas fa-history me-2"></i> Audit Logs
                        </a>
                        <a class="nav-link" href="chatbot_responses.php">
                            <i class="fas fa-robot me-2"></i> Chatbot Responses
                        </a>
                        <a class="nav-link" href="chatbot_conversations.php">
                            <i class="fas fa-comments me-2"></i> Chatbot Conversations
                        </a>
                        <hr class="text-white-50">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 px-4 py-3">
                <div class="main-content">
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2 class="mb-1">
                                <i class="fas fa-tasks me-2"></i>Manage Assignments
                            </h2>
                            <p class="text-muted mb-0">
                                School Year: <?php echo $current_year; ?> | Semester: <?php echo $current_semester; ?>
                            </p>
                        </div>
                        <div>
                            <a href="dashboard.php" class="btn btn-secondary btn-custom">
                                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                            </a>
                        </div>
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

                    <!-- Assignment Type Tabs -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <ul class="nav nav-pills nav-fill" id="assignmentTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link <?php echo $type === 'professor' ? 'active' : ''; ?>" 
                                            onclick="window.location.href='?type=professor'">
                                        <i class="fas fa-user-tie me-2"></i>Professor-Subject Assignments
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link <?php echo $type === 'section' ? 'active' : ''; ?>" 
                                            onclick="window.location.href='?type=section'">
                                        <i class="fas fa-layer-group me-2"></i>Student Section Assignments
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <?php if ($type === 'professor'): ?>
                        <!-- Professor-Subject Assignments -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Assign Professor to Subject</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST">
                                            <div class="mb-3">
                                                <label for="subject_id" class="form-label">Subject *</label>
                                                <select class="form-select" id="subject_id" name="subject_id" required>
                                                    <option value="">Select Subject</option>
                                                    <?php foreach ($subjects as $subject): ?>
                                                        <option value="<?php echo $subject['id']; ?>">
                                                            <?php echo htmlspecialchars($subject['subject_code'] . ' - ' . $subject['subject_name']); ?>
                                                            (<?php echo htmlspecialchars($subject['course_name'] . ' Y' . $subject['year_level'] . ' S' . $subject['semester']); ?>)
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label for="professor_id" class="form-label">Professor *</label>
                                                <select class="form-select" id="professor_id" name="professor_id" required>
                                                    <option value="">Select Professor</option>
                                                    <?php foreach ($professors as $professor): ?>
                                                        <option value="<?php echo $professor['id']; ?>">
                                                            <?php echo htmlspecialchars($professor['professor_name']); ?>
                                                            (<?php echo htmlspecialchars($professor['department']); ?>)
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label for="school_year" class="form-label">School Year *</label>
                                                    <input type="text" class="form-control" id="school_year" name="school_year" 
                                                           value="<?php echo $current_year; ?>" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="semester" class="form-label">Semester *</label>
                                                    <select class="form-select" id="semester" name="semester" required>
                                                        <option value="">Select Semester</option>
                                                        <option value="1" <?php echo $current_semester == 1 ? 'selected' : ''; ?>>1st Semester</option>
                                                        <option value="2" <?php echo $current_semester == 2 ? 'selected' : ''; ?>>2nd Semester</option>
                                                        <option value="3">Summer</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <button type="submit" name="assign_professor" class="btn btn-primary btn-custom w-100">
                                                <i class="fas fa-save me-2"></i>Assign Professor
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Current Professor-Subject Assignments</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Subject</th>
                                                        <th>Professor</th>
                                                        <th>Department</th>
                                                        <th>School Year</th>
                                                        <th>Semester</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (empty($assignments)): ?>
                                                        <tr>
                                                            <td colspan="5" class="text-center text-muted">No assignments found</td>
                                                        </tr>
                                                    <?php else: ?>
                                                        <?php foreach ($assignments as $assignment): ?>
                                                            <tr>
                                                                <td>
                                                                    <strong><?php echo htmlspecialchars($assignment['subject_code']); ?></strong><br>
                                                                    <small class="text-muted"><?php echo htmlspecialchars($assignment['subject_name']); ?></small>
                                                                </td>
                                                                <td><?php echo htmlspecialchars($assignment['professor_name']); ?></td>
                                                                <td><?php echo htmlspecialchars($assignment['department']); ?></td>
                                                                <td><?php echo htmlspecialchars($assignment['school_year']); ?></td>
                                                                <td>
                                                                    <span class="badge bg-secondary">
                                                                        <?php echo $assignment['semester'] == 1 ? '1st Sem' : ($assignment['semester'] == 2 ? '2nd Sem' : 'Summer'); ?>
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php elseif ($type === 'section'): ?>
                        <!-- Student Section Assignments -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Assign Student to Section</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST">
                                            <div class="mb-3">
                                                <label for="student_id" class="form-label">Student *</label>
                                                <select class="form-select" id="student_id" name="student_id" required>
                                                    <option value="">Select Student</option>
                                                    <?php foreach ($students as $student): ?>
                                                        <option value="<?php echo $student['student_id']; ?>">
                                                            <?php echo htmlspecialchars($student['lastname'] . ', ' . $student['firstname']); ?>
                                                            (<?php echo htmlspecialchars($student['course'] . ' Y' . $student['year_level']); ?>)
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label for="section_id" class="form-label">Section *</label>
                                                <select class="form-select" id="section_id" name="section_id" required>
                                                    <option value="">Select Section</option>
                                                    <?php foreach ($sections as $section): ?>
                                                        <?php
                                                        $capacity_class = '';
                                                        if ($section['current_enrollment'] >= $section['max_capacity']) {
                                                            $capacity_class = 'capacity-full';
                                                        } elseif ($section['current_enrollment'] >= $section['max_capacity'] * 0.8) {
                                                            $capacity_class = 'capacity-warning';
                                                        } else {
                                                            $capacity_class = 'capacity-available';
                                                        }
                                                        ?>
                                                        <option value="<?php echo $section['section_id']; ?>" 
                                                                <?php echo $section['current_enrollment'] >= $section['max_capacity'] ? 'disabled' : ''; ?>>
                                                            <?php echo htmlspecialchars($section['section_name'] . ' (' . $section['shift'] . ')'); ?>
                                                            - <?php echo $section['current_enrollment']; ?>/<?php echo $section['max_capacity']; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label for="school_year" class="form-label">School Year *</label>
                                                    <input type="text" class="form-control" id="school_year" name="school_year" 
                                                           value="<?php echo $current_year; ?>" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="semester" class="form-label">Semester *</label>
                                                    <select class="form-select" id="semester" name="semester" required>
                                                        <option value="">Select Semester</option>
                                                        <option value="1" <?php echo $current_semester == 1 ? 'selected' : ''; ?>>1st Semester</option>
                                                        <option value="2" <?php echo $current_semester == 2 ? 'selected' : ''; ?>>2nd Semester</option>
                                                        <option value="3">Summer</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <button type="submit" name="assign_section" class="btn btn-success btn-custom w-100">
                                                <i class="fas fa-save me-2"></i>Assign to Section
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <!-- Section Capacity Overview -->
                                <div class="card mt-3">
                                    <div class="card-header bg-warning text-dark">
                                        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Section Capacity</h6>
                                    </div>
                                    <div class="card-body">
                                        <?php foreach ($sections as $section): ?>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="small">
                                                    <?php echo htmlspecialchars($section['section_name'] . ' (' . $section['shift'] . ')'); ?>
                                                </span>
                                                <span class="capacity-indicator <?php 
                                                    if ($section['current_enrollment'] >= $section['max_capacity']) {
                                                        echo 'capacity-full';
                                                    } elseif ($section['current_enrollment'] >= $section['max_capacity'] * 0.8) {
                                                        echo 'capacity-warning';
                                                    } else {
                                                        echo 'capacity-available';
                                                    }
                                                ?>">
                                                    <?php echo $section['current_enrollment']; ?>/<?php echo $section['max_capacity']; ?>
                                                </span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Current Student Section Assignments</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Student</th>
                                                        <th>Course</th>
                                                        <th>Section</th>
                                                        <th>Shift</th>
                                                        <th>Assigned Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (empty($assignments)): ?>
                                                        <tr>
                                                            <td colspan="5" class="text-center text-muted">No assignments found</td>
                                                        </tr>
                                                    <?php else: ?>
                                                        <?php foreach ($assignments as $assignment): ?>
                                                            <tr>
                                                                <td>
                                                                    <strong><?php echo htmlspecialchars($assignment['lastname'] . ', ' . $assignment['firstname']); ?></strong>
                                                                </td>
                                                                <td>
                                                                    <span class="badge bg-info">
                                                                        <?php echo htmlspecialchars($assignment['course']); ?>
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <span class="badge bg-secondary">
                                                                        <?php echo htmlspecialchars($assignment['section_name']); ?>
                                                                    </span>
                                                                </td>
                                                                <td><?php echo htmlspecialchars($assignment['shift']); ?></td>
                                                                <td><?php echo date('M j, Y', strtotime($assignment['assigned_date'])); ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-select current semester
        document.addEventListener('DOMContentLoaded', function() {
            const semesterSelect = document.getElementById('semester');
            if (semesterSelect) {
                const currentSemester = <?php echo $current_semester; ?>;
                semesterSelect.value = currentSemester;
            }
        });

        // Section capacity warning
        document.getElementById('section_id')?.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.disabled) {
                alert('This section is already at maximum capacity!');
                this.value = '';
            }
        });
    </script>
</body>
</html>
