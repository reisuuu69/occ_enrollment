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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_response'])) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO chatbot_responses (question_pattern, response_text, category, keywords, created_by) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $_POST['question_pattern'],
                $_POST['response_text'],
                $_POST['category'],
                $_POST['keywords'],
                $_SESSION['user_id'] ?? 1
            ]);
            $message = "Chatbot response added successfully!";
        } catch (PDOException $e) {
            $error = "Error adding response: " . $e->getMessage();
        }
    } elseif (isset($_POST['delete_response'])) {
        try {
            $stmt = $pdo->prepare("DELETE FROM chatbot_responses WHERE id = ?");
            $stmt->execute([$_POST['response_id']]);
            $message = "Chatbot response deleted successfully!";
        } catch (PDOException $e) {
            $error = "Error deleting response: " . $e->getMessage();
        }
    }
}

// Get responses
$responses = $pdo->query("
    SELECT cr.*, u.username as created_by_name
    FROM chatbot_responses cr
    LEFT JOIN users u ON cr.created_by = u.id
    ORDER BY cr.created_at DESC
")->fetchAll();

// Get statistics
$stats = $pdo->query("
    SELECT 
        COUNT(*) as total_responses,
        COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_responses,
        COUNT(DISTINCT category) as categories
    FROM chatbot_responses
")->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot Responses - Admin Dashboard</title>
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
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .stat-card .icon {
            font-size: 2.5rem;
            opacity: 0.8;
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
                        <a class="nav-link" href="assignments.php">
                            <i class="fas fa-tasks me-2"></i> Assignments
                        </a>
                        <a class="nav-link" href="reports.php">
                            <i class="fas fa-chart-bar me-2"></i> Reports
                        </a>
                        <a class="nav-link" href="audit_logs.php">
                            <i class="fas fa-history me-2"></i> Audit Logs
                        </a>
                        <a class="nav-link active" href="chatbot_responses.php">
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
                                <i class="fas fa-robot me-2"></i>Chatbot Responses
                            </h2>
                            <p class="text-muted mb-0">Manage automated chatbot responses and patterns</p>
                        </div>
                        <div>
                            <button type="button" class="btn btn-primary btn-custom" data-bs-toggle="modal" data-bs-target="#addResponseModal">
                                <i class="fas fa-plus me-2"></i>Add Response
                            </button>
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

                    <!-- Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-robot"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['total_responses']); ?></h4>
                                    <p class="mb-0 small">Total Responses</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['active_responses']); ?></h4>
                                    <p class="mb-0 small">Active Responses</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-tags"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['categories']); ?></h4>
                                    <p class="mb-0 small">Categories</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Responses Table -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-table me-2"></i>Chatbot Responses</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Pattern</th>
                                            <th>Response</th>
                                            <th>Category</th>
                                            <th>Status</th>
                                            <th>Created By</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($responses)): ?>
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">No chatbot responses found</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($responses as $response): ?>
                                                <tr>
                                                    <td>
                                                        <code><?php echo htmlspecialchars($response['question_pattern']); ?></code>
                                                    </td>
                                                    <td>
                                                        <?php echo htmlspecialchars(substr($response['response_text'], 0, 100)) . (strlen($response['response_text']) > 100 ? '...' : ''); ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info"><?php echo htmlspecialchars($response['category']); ?></span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $response['is_active'] ? 'success' : 'secondary'; ?>">
                                                            <?php echo $response['is_active'] ? 'Active' : 'Inactive'; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <small><?php echo htmlspecialchars($response['created_by_name'] ?? 'System'); ?></small>
                                                    </td>
                                                    <td>
                                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this response?')">
                                                            <input type="hidden" name="response_id" value="<?php echo $response['id']; ?>">
                                                            <button type="submit" name="delete_response" class="btn btn-sm btn-danger">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
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

    <!-- Add Response Modal -->
    <div class="modal fade" id="addResponseModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Chatbot Response</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Question Pattern *</label>
                                    <input type="text" name="question_pattern" class="form-control" required 
                                           placeholder="e.g., enrollment|enroll|how to enroll">
                                    <small class="text-muted">Use | to separate multiple patterns</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Category *</label>
                                    <input type="text" name="category" class="form-control" required 
                                           placeholder="e.g., enrollment, fees, schedule">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Response Text *</label>
                            <textarea name="response_text" class="form-control" rows="4" required 
                                      placeholder="Enter the response that will be shown to users..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keywords</label>
                            <input type="text" name="keywords" class="form-control" 
                                   placeholder="e.g., enrollment,enroll,process,form,documents,fees">
                            <small class="text-muted">Comma-separated keywords for better matching</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_response" class="btn btn-primary">Add Response</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
