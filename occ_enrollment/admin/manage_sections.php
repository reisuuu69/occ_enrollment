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

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                try {
                    $stmt = $pdo->prepare("INSERT INTO sections (section_name, shift, max_capacity, current_enrollment) VALUES (?, ?, ?, ?)");
                    $stmt->execute([
                        $_POST['section_name'],
                        $_POST['shift'],
                        $_POST['max_capacity'],
                        0 // Start with 0 current enrollment
                    ]);
                    $message = "Section added successfully!";
                } catch (PDOException $e) {
                    $error = "Error adding section: " . $e->getMessage();
                }
                break;

            case 'edit':
                try {
                    $stmt = $pdo->prepare("UPDATE sections SET section_name = ?, shift = ?, max_capacity = ? WHERE section_id = ?");
                    $stmt->execute([
                        $_POST['section_name'],
                        $_POST['shift'],
                        $_POST['max_capacity'],
                        $_POST['section_id']
                    ]);
                    $message = "Section updated successfully!";
                } catch (PDOException $e) {
                    $error = "Error updating section: " . $e->getMessage();
                }
                break;

            case 'delete':
                try {
                    $stmt = $pdo->prepare("DELETE FROM sections WHERE section_id = ?");
                    $stmt->execute([$_POST['section_id']]);
                    $message = "Section deleted successfully!";
                } catch (PDOException $e) {
                    $error = "Error deleting section: " . $e->getMessage();
                }
                break;
        }
    }
}

// Get sections list
try {
    $stmt = $pdo->query("SELECT * FROM sections ORDER BY section_name ASC");
    $sections_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error loading sections data: " . $e->getMessage();
    $sections_list = [];
}

// Get statistics
try {
    $stats_query = "SELECT 
        COUNT(*) as total_sections,
        COUNT(CASE WHEN shift = 'Morning' THEN 1 END) as morning_sections,
        COUNT(CASE WHEN shift = 'Afternoon' THEN 1 END) as afternoon_sections,
        COUNT(CASE WHEN shift = 'Evening' THEN 1 END) as evening_sections,
        SUM(max_capacity) as total_capacity,
        SUM(current_enrollment) as total_enrolled
    FROM sections";
    $stats_stmt = $pdo->query($stats_query);
    $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $stats = ['total_sections' => 0, 'morning_sections' => 0, 'afternoon_sections' => 0, 'evening_sections' => 0, 'total_capacity' => 0, 'total_enrolled' => 0];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Sections - OCC Enrollment System</title>
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
                        <a class="nav-link active" href="manage_sections.php">
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
                            <h2 class="mb-1">Manage Sections</h2>
                            <p class="text-muted mb-0">Organize and manage student sections</p>
                        </div>
                        <button class="btn btn-primary btn-custom" data-bs-toggle="modal" data-bs-target="#addSectionModal">
                            <i class="fas fa-plus me-2"></i>Add Section
                        </button>
                    </div>

                    <!-- Alerts -->
                    <?php if ($message): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-4 col-lg-2 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-layer-group"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['total_sections'] ?? 0); ?></h4>
                                    <p class="mb-0 small">Total Sections</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-sun"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['morning_sections'] ?? 0); ?></h4>
                                    <p class="mb-0 small">Morning Sections</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-cloud-sun"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['afternoon_sections'] ?? 0); ?></h4>
                                    <p class="mb-0 small">Afternoon Sections</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-moon"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['evening_sections'] ?? 0); ?></h4>
                                    <p class="mb-0 small">Evening Sections</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['total_capacity'] ?? 0); ?></h4>
                                    <p class="mb-0 small">Total Capacity</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-user-check"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['total_enrolled'] ?? 0); ?></h4>
                                    <p class="mb-0 small">Total Enrolled</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sections Table -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-list me-2"></i>Sections List
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Section Name</th>
                                            <th>Shift</th>
                                            <th>Max Capacity</th>
                                            <th>Current Enrollment</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($sections_list)): ?>
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">No sections found</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($sections_list as $section): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($section['section_id']); ?></td>
                                                    <td>
                                                        <span class="badge bg-primary"><?php echo htmlspecialchars($section['section_name']); ?></span>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $shift_badge = match($section['shift']) {
                                                            'Morning' => 'bg-warning',
                                                            'Afternoon' => 'bg-info',
                                                            'Evening' => 'bg-dark',
                                                            default => 'bg-secondary'
                                                        };
                                                        ?>
                                                        <span class="badge <?php echo $shift_badge; ?>"><?php echo htmlspecialchars($section['shift']); ?></span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-success"><?php echo htmlspecialchars($section['max_capacity']); ?> students</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info"><?php echo htmlspecialchars($section['current_enrollment']); ?> students</span>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-primary me-1" 
                                                                onclick="editSection(<?php echo htmlspecialchars(json_encode($section)); ?>)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-danger" 
                                                                onclick="deleteSection(<?php echo $section['section_id']; ?>, '<?php echo htmlspecialchars($section['section_name']); ?>')">
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

    <!-- Add Section Modal -->
    <div class="modal fade" id="addSectionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Section</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="mb-3">
                            <label for="section_name" class="form-label">Section Name *</label>
                            <input type="text" class="form-control" id="section_name" name="section_name" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="shift" class="form-label">Shift *</label>
                                    <select class="form-select" id="shift" name="shift" required>
                                        <option value="">Select Shift</option>
                                        <option value="Morning">Morning</option>
                                        <option value="Afternoon">Afternoon</option>
                                        <option value="Evening">Evening</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="max_capacity" class="form-label">Max Capacity *</label>
                                    <input type="number" class="form-control" id="max_capacity" name="max_capacity" min="10" max="100" value="50" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Section</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Section Modal -->
    <div class="modal fade" id="editSectionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Section</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" id="edit_section_id" name="section_id">
                        
                        <div class="mb-3">
                            <label for="edit_section_name" class="form-label">Section Name *</label>
                            <input type="text" class="form-control" id="edit_section_name" name="section_name" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_shift" class="form-label">Shift *</label>
                                    <select class="form-select" id="edit_shift" name="shift" required>
                                        <option value="">Select Shift</option>
                                        <option value="Morning">Morning</option>
                                        <option value="Afternoon">Afternoon</option>
                                        <option value="Evening">Evening</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_max_capacity" class="form-label">Max Capacity *</label>
                                    <input type="number" class="form-control" id="edit_max_capacity" name="max_capacity" min="10" max="100" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Section</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteSectionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the section "<strong id="delete_section_name"></strong>"?</p>
                    <p class="text-danger small">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" id="delete_section_id" name="section_id">
                        <button type="submit" class="btn btn-danger">Delete Section</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editSection(section) {
            document.getElementById('edit_section_id').value = section.section_id;
            document.getElementById('edit_section_name').value = section.section_name;
            document.getElementById('edit_shift').value = section.shift;
            document.getElementById('edit_max_capacity').value = section.max_capacity;
            
            new bootstrap.Modal(document.getElementById('editSectionModal')).show();
        }

        function deleteSection(sectionId, sectionName) {
            document.getElementById('delete_section_id').value = sectionId;
            document.getElementById('delete_section_name').textContent = sectionName;
            
            new bootstrap.Modal(document.getElementById('deleteSectionModal')).show();
        }
    </script>
</body>
</html>
