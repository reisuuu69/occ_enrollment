<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once '../config/database.php';
$database = new Database();
$db = $database->connect();

$message = '';
$error = '';

// Flash messages (PRG)
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}
if (isset($_SESSION['flash_error'])) {
    $error = $_SESSION['flash_error'];
    unset($_SESSION['flash_error']);
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                try {
                    $stmt = $db->prepare("INSERT INTO courses (course_code, course_name) VALUES (?, ?)");
                    $stmt->execute([
                        $_POST['course_code'],
                        $_POST['course_name']
                    ]);
                    $_SESSION['flash_message'] = "Course added successfully!";
                    header('Location: manage_course.php');
                    exit();
                } catch (PDOException $e) {
                    $_SESSION['flash_error'] = "Error adding course: " . $e->getMessage();
                    header('Location: manage_course.php');
                    exit();
                }
                break;

            case 'edit':
                try {
                    $stmt = $db->prepare("UPDATE courses SET course_code = ?, course_name = ? WHERE id = ?");
                    $stmt->execute([
                        $_POST['course_code'],
                        $_POST['course_name'],
                        $_POST['course_id']
                    ]);
                    $_SESSION['flash_message'] = "Course updated successfully!";
                    header('Location: manage_course.php');
                    exit();
                } catch (PDOException $e) {
                    $_SESSION['flash_error'] = "Error updating course: " . $e->getMessage();
                    header('Location: manage_course.php');
                    exit();
                }
                break;

            case 'delete':
                try {
                    $stmt = $db->prepare("DELETE FROM courses WHERE id = ?");
                    $stmt->execute([$_POST['course_id']]);
                    $_SESSION['flash_message'] = "Course deleted successfully!";
                    header('Location: manage_course.php');
                    exit();
                } catch (PDOException $e) {
                    $_SESSION['flash_error'] = "Error deleting course: " . $e->getMessage();
                    header('Location: manage_course.php');
                    exit();
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
    $stats_query = "SELECT COUNT(*) as total_courses FROM courses";
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
            <h4><i class="fas fa-shield-alt"></i> Admin Panel</h4>
            <p>OCC Enrollment System</p>
        </div>
        
        <nav class="sidebar-nav">
                    
                    <nav class="sidebar-nav">
                        <div class="nav-item">
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Dashboard
                        
                </a>
            </div>
                        <div class="nav-item">
                <a class="nav-link" href="manage_students.php">
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
                <a class="nav-link active" href="manage_course.php">
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
                        <div class="nav-item">
                <a class="nav-link" href="audit_logs.php">
                    <i class="fas fa-history me-2"></i>
                    Audit Logs
                        
                </a>
            </div>
                        <div class="nav-item">
                <a class="nav-link" href="chatbot_responses.php">
                    <i class="fas fa-robot me-2"></i>
                    Chatbot Responses
                        
                </a>
            </div>
                        <div class="nav-item">
                <a class="nav-link" href="chatbot_conversations.php">
                    <i class="fas fa-comments me-2"></i>
                    Chatbot Conversations
                        
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
