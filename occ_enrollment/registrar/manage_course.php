<?php
session_start();
if (!isset($_SESSION['registrar_logged_in']) || $_SESSION['role'] !== 'registrar') {
    header("Location: login.php");
    exit();
}

require_once '../config/database.php';
$database = new Database();
$db = $database->connect();

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                try {
                    $stmt = $db->prepare("INSERT INTO courses (course_code, course_name, description, units, department) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $_POST['course_code'],
                        $_POST['course_name'],
                        $_POST['description'],
                        $_POST['units'],
                        $_POST['department']
                    ]);
                    $message = "Course added successfully!";
                } catch (PDOException $e) {
                    $error = "Error adding course: " . $e->getMessage();
                }
                break;

            case 'edit':
                try {
                    $stmt = $db->prepare("UPDATE courses SET course_code = ?, course_name = ?, description = ?, units = ?, department = ? WHERE id = ?");
                    $stmt->execute([
                        $_POST['course_code'],
                        $_POST['course_name'],
                        $_POST['description'],
                        $_POST['units'],
                        $_POST['department'],
                        $_POST['course_id']
                    ]);
                    $message = "Course updated successfully!";
                } catch (PDOException $e) {
                    $error = "Error updating course: " . $e->getMessage();
                }
                break;

            case 'delete':
                try {
                    $stmt = $db->prepare("DELETE FROM courses WHERE id = ?");
                    $stmt->execute([$_POST['course_id']]);
                    $message = "Course deleted successfully!";
                } catch (PDOException $e) {
                    $error = "Error deleting course: " . $e->getMessage();
                }
                break;
        }
    }
}

// Get courses list
try {
    $stmt = $db->query("SELECT * FROM courses ORDER BY course_name ASC");
    $courses_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error loading courses data: " . $e->getMessage();
    $courses_list = [];
}

// Get statistics
try {
    $stats_query = "SELECT 
        COUNT(*) as total_courses,
        COUNT(CASE WHEN department = 'Computer Science' THEN 1 END) as cs_courses,
        COUNT(CASE WHEN department = 'Information Technology' THEN 1 END) as it_courses,
        COUNT(CASE WHEN department = 'Education' THEN 1 END) as edu_courses,
        COUNT(CASE WHEN department = 'Business' THEN 1 END) as business_courses
    FROM courses";
    $stats_stmt = $db->query($stats_query);
    $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $stats = ['total_courses' => 0, 'cs_courses' => 0, 'it_courses' => 0, 'edu_courses' => 0, 'business_courses' => 0];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Management - OCC Enrollment System</title>
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
        .table-container {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
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
                        <a class="nav-link active" href="manage_course.php">
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
                            <h2 class="mb-1">Course Management</h2>
                            <p class="text-muted mb-0">Manage academic courses and programs</p>
                        </div>
                        <button class="btn btn-primary btn-custom" data-bs-toggle="modal" data-bs-target="#addCourseModal">
                            <i class="fas fa-plus me-2"></i>Add New Course
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
                                    <h4 class="mb-1"><?php echo number_format($stats['total_courses'] ?? 0); ?></h4>
                                    <p class="mb-0 small">Total Courses</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-laptop-code"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['cs_courses'] ?? 0); ?></h4>
                                    <p class="mb-0 small">Computer Science</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-network-wired"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['it_courses'] ?? 0); ?></h4>
                                    <p class="mb-0 small">Information Tech</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-graduation-cap"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['edu_courses'] ?? 0); ?></h4>
                                    <p class="mb-0 small">Education</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-briefcase"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['business_courses'] ?? 0); ?></h4>
                                    <p class="mb-0 small">Business</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Courses Table -->
                    <div class="table-container">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">
                                <i class="fas fa-list me-2"></i>Courses List
                            </h5>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Course Code</th>
                                        <th>Course Name</th>
                                        <th>Description</th>
                                        <th>Units</th>
                                        <th>Department</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($courses_list)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                                <br>No courses found
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($courses_list as $course): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-primary"><?php echo htmlspecialchars($course['course_code']); ?></span>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($course['course_name']); ?></strong>
                                            </td>
                                            <td><?php echo htmlspecialchars($course['description'] ?? 'No description'); ?></td>
                                            <td>
                                                <span class="badge bg-info"><?php echo htmlspecialchars($course['units'] ?? 'N/A'); ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary"><?php echo htmlspecialchars($course['department'] ?? 'General'); ?></span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary me-1" 
                                                        onclick="editCourse(<?php echo htmlspecialchars(json_encode($course)); ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" 
                                                        onclick="deleteCourse(<?php echo $course['id']; ?>, '<?php echo htmlspecialchars($course['course_name']); ?>')">
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

    <!-- Add Course Modal -->
    <div class="modal fade" id="addCourseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Course</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="mb-3">
                            <label for="course_code" class="form-label">Course Code *</label>
                            <input type="text" class="form-control" id="course_code" name="course_code" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="course_name" class="form-label">Course Name *</label>
                            <input type="text" class="form-control" id="course_name" name="course_name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="units" class="form-label">Units</label>
                            <input type="number" class="form-control" id="units" name="units" min="1" max="6">
                        </div>
                        
                        <div class="mb-3">
                            <label for="department" class="form-label">Department</label>
                            <select class="form-select" id="department" name="department">
                                <option value="">Select Department</option>
                                <option value="Computer Science">Computer Science</option>
                                <option value="Information Technology">Information Technology</option>
                                <option value="Education">Education</option>
                                <option value="Business">Business</option>
                                <option value="Arts and Sciences">Arts and Sciences</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Course</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Course Modal -->
    <div class="modal fade" id="editCourseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Course</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="course_id" id="edit_course_id">
                        
                        <div class="mb-3">
                            <label for="edit_course_code" class="form-label">Course Code *</label>
                            <input type="text" class="form-control" id="edit_course_code" name="course_code" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_course_name" class="form-label">Course Name *</label>
                            <input type="text" class="form-control" id="edit_course_name" name="course_name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_units" class="form-label">Units</label>
                            <input type="number" class="form-control" id="edit_units" name="units" min="1" max="6">
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_department" class="form-label">Department</label>
                            <select class="form-select" id="edit_department" name="department">
                                <option value="">Select Department</option>
                                <option value="Computer Science">Computer Science</option>
                                <option value="Information Technology">Information Technology</option>
                                <option value="Education">Education</option>
                                <option value="Business">Business</option>
                                <option value="Arts and Sciences">Arts and Sciences</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Course</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteCourseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong id="delete_course_name"></strong>?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <form method="POST">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="course_id" id="delete_course_id">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editCourse(course) {
            document.getElementById('edit_course_id').value = course.id;
            document.getElementById('edit_course_code').value = course.course_code;
            document.getElementById('edit_course_name').value = course.course_name;
            document.getElementById('edit_description').value = course.description || '';
            document.getElementById('edit_units').value = course.units || '';
            document.getElementById('edit_department').value = course.department || '';
            
            new bootstrap.Modal(document.getElementById('editCourseModal')).show();
        }

        function deleteCourse(id, name) {
            document.getElementById('delete_course_id').value = id;
            document.getElementById('delete_course_name').textContent = name;
            
            new bootstrap.Modal(document.getElementById('deleteCourseModal')).show();
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
