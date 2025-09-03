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

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                try {
                    $stmt = $pdo->prepare("INSERT INTO subjects (subject_code, subject_name, units, course_code, year_level, semester) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $_POST['subject_code'],
                        $_POST['subject_name'],
                        $_POST['units'],
                        $_POST['course_code'],
                        $_POST['year_level'],
                        $_POST['semester']
                    ]);
                    $message = "Subject added successfully!";
                } catch (PDOException $e) {
                    $error = "Error adding subject: " . $e->getMessage();
                }
                break;

            case 'edit':
                try {
                    $stmt = $pdo->prepare("UPDATE subjects SET subject_code = ?, subject_name = ?, units = ?, course_code = ?, year_level = ?, semester = ? WHERE id = ?");
                    $stmt->execute([
                        $_POST['subject_code'],
                        $_POST['subject_name'],
                        $_POST['units'],
                        $_POST['course_code'],
                        $_POST['year_level'],
                        $_POST['semester'],
                        $_POST['subject_id']
                    ]);
                    $message = "Subject updated successfully!";
                } catch (PDOException $e) {
                    $error = "Error updating subject: " . $e->getMessage();
                }
                break;

            case 'delete':
                try {
                    $stmt = $pdo->prepare("DELETE FROM subjects WHERE id = ?");
                    $stmt->execute([$_POST['subject_id']]);
                    $message = "Subject deleted successfully!";
                } catch (PDOException $e) {
                    $error = "Error deleting subject: " . $e->getMessage();
                }
                break;
        }
    }
}

// Get subjects list
try {
    $stmt = $pdo->query("SELECT * FROM subjects ORDER BY course_code, year_level, semester, subject_name ASC");
    $subjects_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error loading subjects data: " . $e->getMessage();
    $subjects_list = [];
}

// Get statistics
try {
    $stats_query = "SELECT 
        COUNT(*) as total_subjects,
        COUNT(CASE WHEN course_code = 'BSCS' THEN 1 END) as bscs_subjects,
        COUNT(CASE WHEN course_code = 'BSIT' THEN 1 END) as bsit_subjects,
        COUNT(CASE WHEN course_code = 'BSE' THEN 1 END) as bse_subjects,
        COUNT(CASE WHEN course_code = 'BSA' THEN 1 END) as bsa_subjects,
        COUNT(CASE WHEN course_code = 'BSME' THEN 1 END) as bsme_subjects,
        SUM(units) as total_units
    FROM subjects";
    $stats_stmt = $pdo->query($stats_query);
    $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $stats = ['total_subjects' => 0, 'bscs_subjects' => 0, 'bsit_subjects' => 0, 'bse_subjects' => 0, 'bsa_subjects' => 0, 'bsme_subjects' => 0, 'total_units' => 0];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Subjects - OCC Enrollment System</title>
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
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-2px);
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .stat-card .icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
        .btn-custom {
            border-radius: 25px;
            padding: 8px 20px;
            font-weight: 500;
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
                        <h4 class="text-white">Registrar Panel</h4>
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
                        <a class="nav-link active" href="manage_subjects.php">
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
                        <a class="nav-link" href="assignments.php">
                            <i class="fas fa-tasks me-2"></i> Assignments
                        </a>
                        <a class="nav-link" href="reports.php">
                            <i class="fas fa-chart-bar me-2"></i> Reports
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
                            <h2 class="mb-1">Subject Management</h2>
                            <p class="text-muted mb-0">Manage subjects and course offerings</p>
                        </div>
                        <button class="btn btn-primary btn-custom" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
                            <i class="fas fa-plus me-2"></i>Add New Subject
                        </button>
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
                                        <i class="fas fa-book"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['total_subjects'] ?? 0); ?></h4>
                                    <p class="mb-0 small">Total Subjects</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-laptop-code"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['bscs_subjects'] ?? 0); ?></h4>
                                    <p class="mb-0 small">BSCS Subjects</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-network-wired"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['bsit_subjects'] ?? 0); ?></h4>
                                    <p class="mb-0 small">BSIT Subjects</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-graduation-cap"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['bse_subjects'] ?? 0); ?></h4>
                                    <p class="mb-0 small">BSE Subjects</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-calculator"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['bsa_subjects'] ?? 0); ?></h4>
                                    <p class="mb-0 small">BSA Subjects</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-cogs"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['bsme_subjects'] ?? 0); ?></h4>
                                    <p class="mb-0 small">BSME Subjects</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Units Card -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h3 class="mb-0">Total Units: <?php echo number_format($stats['total_units'] ?? 0); ?></h3>
                                    <p class="mb-0">Combined units across all subjects</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Subjects Table -->
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Subjects List</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Code</th>
                                            <th>Subject Name</th>
                                            <th>Units</th>
                                            <th>Course</th>
                                            <th>Year Level</th>
                                            <th>Semester</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($subjects_list)): ?>
                                            <tr>
                                                <td colspan="8" class="text-center text-muted">No subjects found</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($subjects_list as $subject): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($subject['id']); ?></td>
                                                    <td>
                                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($subject['subject_code']); ?></span>
                                                    </td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($subject['subject_name']); ?></strong>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-success"><?php echo htmlspecialchars($subject['units']); ?> units</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info"><?php echo htmlspecialchars($subject['course_code']); ?></span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-warning"><?php echo htmlspecialchars($subject['year_level']); ?>st Year</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-primary"><?php echo htmlspecialchars($subject['semester']); ?>st Sem</span>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-primary me-1" 
                                                                onclick="editSubject(<?php echo htmlspecialchars(json_encode($subject)); ?>)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-danger" 
                                                                onclick="deleteSubject(<?php echo $subject['id']; ?>, '<?php echo htmlspecialchars($subject['subject_name']); ?>')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
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
        </div>
    </div>

    <!-- Add Subject Modal -->
    <div class="modal fade" id="addSubjectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Subject</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="mb-3">
                            <label for="subject_code" class="form-label">Subject Code *</label>
                            <input type="text" class="form-control" id="subject_code" name="subject_code" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="subject_name" class="form-label">Subject Name *</label>
                            <input type="text" class="form-control" id="subject_name" name="subject_name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="units" class="form-label">Units *</label>
                            <input type="number" class="form-control" id="units" name="units" min="1" max="6" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="course_code" class="form-label">Course *</label>
                            <select class="form-select" id="course_code" name="course_code" required>
                                <option value="">Select Course</option>
                                <option value="BSCS">BSCS - Computer Science</option>
                                <option value="BSIT">BSIT - Information Technology</option>
                                <option value="BSE">BSE - Secondary Education</option>
                                <option value="BSA">BSA - Accountancy</option>
                                <option value="BSME">BSME - Mechanical Engineering</option>
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="year_level" class="form-label">Year Level *</label>
                                    <select class="form-select" id="year_level" name="year_level" required>
                                        <option value="">Select Year</option>
                                        <option value="1">1st Year</option>
                                        <option value="2">2nd Year</option>
                                        <option value="3">3rd Year</option>
                                        <option value="4">4th Year</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="semester" class="form-label">Semester *</label>
                                    <select class="form-select" id="semester" name="semester" required>
                                        <option value="">Select Semester</option>
                                        <option value="1">1st Semester</option>
                                        <option value="2">2nd Semester</option>
                                        <option value="3">Summer</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Subject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Subject Modal -->
    <div class="modal fade" id="editSubjectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Subject</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="subject_id" id="edit_subject_id">
                        
                        <div class="mb-3">
                            <label for="edit_subject_code" class="form-label">Subject Code *</label>
                            <input type="text" class="form-control" id="edit_subject_code" name="subject_code" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_subject_name" class="form-label">Subject Name *</label>
                            <input type="text" class="form-control" id="edit_subject_name" name="subject_name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_units" class="form-label">Units *</label>
                            <input type="number" class="form-control" id="edit_units" name="units" min="1" max="6" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_course_code" class="form-label">Course *</label>
                            <select class="form-select" id="edit_course_code" name="course_code" required>
                                <option value="">Select Course</option>
                                <option value="BSCS">BSCS - Computer Science</option>
                                <option value="BSIT">BSIT - Information Technology</option>
                                <option value="BSE">BSE - Secondary Education</option>
                                <option value="BSA">BSA - Accountancy</option>
                                <option value="BSME">BSME - Mechanical Engineering</option>
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_year_level" class="form-label">Year Level *</label>
                                    <select class="form-select" id="edit_year_level" name="year_level" required>
                                        <option value="">Select Year</option>
                                        <option value="1">1st Year</option>
                                        <option value="2">2nd Year</option>
                                        <option value="3">3rd Year</option>
                                        <option value="4">4th Year</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_semester" class="form-label">Semester *</label>
                                    <select class="form-select" id="edit_semester" name="semester" required>
                                        <option value="">Select Semester</option>
                                        <option value="1">1st Semester</option>
                                        <option value="2">2nd Semester</option>
                                        <option value="3">Summer</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Subject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteSubjectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong id="delete_subject_name"></strong>?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <form method="POST">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="subject_id" id="delete_subject_id">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editSubject(subject) {
            document.getElementById('edit_subject_id').value = subject.id;
            document.getElementById('edit_subject_code').value = subject.subject_code;
            document.getElementById('edit_subject_name').value = subject.subject_name;
            document.getElementById('edit_units').value = subject.units;
            document.getElementById('edit_course_code').value = subject.course_code;
            document.getElementById('edit_year_level').value = subject.year_level;
            document.getElementById('edit_semester').value = subject.semester;
            
            new bootstrap.Modal(document.getElementById('editSubjectModal')).show();
        }

        function deleteSubject(id, name) {
            document.getElementById('delete_subject_id').value = id;
            document.getElementById('delete_subject_name').textContent = name;
            
            new bootstrap.Modal(document.getElementById('deleteSubjectModal')).show();
        }

        // Auto-refresh page after successful operations
        <?php if ($message || $error): ?>
        setTimeout(function() {
            location.reload();
        }, 2000);
        <?php endif; ?>
    </script>
</body>
</html>
