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

// Handle feedback update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_feedback'])) {
    try {
        $stmt = $pdo->prepare("
            UPDATE chatbot_conversations 
            SET is_helpful = ?, feedback = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $_POST['is_helpful'],
            $_POST['feedback'],
            $_POST['conversation_id']
        ]);
        $message = "Feedback updated successfully!";
    } catch (PDOException $e) {
        $error = "Error updating feedback: " . $e->getMessage();
    }
}

// Get filters
$user_type_filter = $_GET['user_type'] ?? '';
$helpful_filter = $_GET['helpful'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$search = $_GET['search'] ?? '';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Build query
$where_conditions = [];
$params = [];

if ($user_type_filter) {
    $where_conditions[] = "cc.user_type = ?";
    $params[] = $user_type_filter;
}

if ($helpful_filter !== '') {
    $where_conditions[] = "cc.is_helpful = ?";
    $params[] = $helpful_filter;
}

if ($date_from) {
    $where_conditions[] = "DATE(cc.created_at) >= ?";
    $params[] = $date_from;
}

if ($date_to) {
    $where_conditions[] = "DATE(cc.created_at) <= ?";
    $params[] = $date_to;
}

if ($search) {
    $where_conditions[] = "(cc.message LIKE ? OR cc.response LIKE ? OR cc.feedback LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count
$count_query = "SELECT COUNT(*) FROM chatbot_conversations cc $where_clause";
$stmt = $pdo->prepare($count_query);
$stmt->execute($params);
$total_records = $stmt->fetchColumn();
$total_pages = ceil($total_records / $per_page);

// Get conversations
$query = "
    SELECT cc.*, cr.question_pattern, cr.category
    FROM chatbot_conversations cc
    LEFT JOIN chatbot_responses cr ON cc.response_id = cr.id
    $where_clause
    ORDER BY cc.created_at DESC
    LIMIT $per_page OFFSET $offset
";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$conversations = $stmt->fetchAll();

// Get unique user types for filter
$user_types = $pdo->query("SELECT DISTINCT user_type FROM chatbot_conversations ORDER BY user_type")->fetchAll(PDO::FETCH_COLUMN);

// Get statistics
$stats = $pdo->query("
    SELECT 
        COUNT(*) as total_conversations,
        COUNT(CASE WHEN is_helpful = 1 THEN 1 END) as helpful_count,
        COUNT(CASE WHEN is_helpful = 0 THEN 1 END) as not_helpful_count,
        COUNT(CASE WHEN is_helpful IS NULL THEN 1 END) as no_feedback_count,
        COUNT(DISTINCT session_id) as unique_sessions,
        COUNT(DISTINCT user_ip) as unique_users,
        COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 END) as last_24h
    FROM chatbot_conversations
")->fetch(PDO::FETCH_ASSOC);

$helpful_rate = $stats['total_conversations'] > 0 ? 
    round(($stats['helpful_count'] / $stats['total_conversations']) * 100, 1) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot Conversations - Admin Dashboard</title>
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
                <a class="nav-link active" href="chatbot_conversations.php">
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
                            <h2 class="mb-1">
                                <i class="fas fa-comments me-2"></i>Chatbot Conversations
                            </h2>
                            <p class="text-muted mb-0">Monitor and analyze chatbot interactions</p>
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

                    <!-- Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-comments"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['total_conversations']); ?></h4>
                                    <p class="mb-0 small">Total Conversations</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-thumbs-up"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo $helpful_rate; ?>%</h4>
                                    <p class="mb-0 small">Helpful Rate</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['unique_users']); ?></h4>
                                    <p class="mb-0 small">Unique Users</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['last_24h']); ?></h4>
                                    <p class="mb-0 small">Last 24 Hours</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filters</h5>
                        </div>
                        <div class="card-body">
                            <form method="GET" class="row g-3">
                                <div class="col-md-2">
                                    <label class="form-label">User Type</label>
                                    <select name="user_type" class="form-select">
                                        <option value="">All Types</option>
                                        <?php foreach ($user_types as $type): ?>
                                            <option value="<?php echo $type; ?>" <?php echo $user_type_filter === $type ? 'selected' : ''; ?>>
                                                <?php echo ucfirst($type); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Helpful</label>
                                    <select name="helpful" class="form-select">
                                        <option value="">All</option>
                                        <option value="1" <?php echo $helpful_filter === '1' ? 'selected' : ''; ?>>Yes</option>
                                        <option value="0" <?php echo $helpful_filter === '0' ? 'selected' : ''; ?>>No</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Date From</label>
                                    <input type="date" name="date_from" class="form-control" value="<?php echo $date_from; ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Date To</label>
                                    <input type="date" name="date_to" class="form-control" value="<?php echo $date_to; ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Search</label>
                                    <input type="text" name="search" class="form-control" placeholder="Search messages, responses..." value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-primary btn-custom">
                                            <i class="fas fa-search me-2"></i>Filter
                                        </button>
                                        <a href="chatbot_conversations.php" class="btn btn-secondary btn-custom">
                                            <i class="fas fa-times me-2"></i>Clear
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Conversations List -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Conversations (<?php echo number_format($total_records); ?> records)</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($conversations)): ?>
                                <div class="text-center text-muted py-5">
                                    <i class="fas fa-comments fa-3x mb-3"></i>
                                    <h5>No conversations found</h5>
                                    <p>No chatbot conversations match your current filters.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($conversations as $conv): ?>
                                    <div class="card mb-3">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="badge bg-info feedback-badge">
                                                    <?php echo ucfirst($conv['user_type']); ?>
                                                </span>
                                                <?php if ($conv['category']): ?>
                                                    <span class="badge bg-secondary feedback-badge">
                                                        <?php echo htmlspecialchars($conv['category']); ?>
                                                    </span>
                                                <?php endif; ?>
                                                <small class="text-muted ms-2">
                                                    <?php echo date('M j, Y g:i A', strtotime($conv['created_at'])); ?>
                                                </small>
                                            </div>
                                            <div>
                                                <?php if ($conv['is_helpful'] === null): ?>
                                                    <span class="badge bg-warning feedback-badge">No Feedback</span>
                                                <?php elseif ($conv['is_helpful']): ?>
                                                    <span class="badge bg-success feedback-badge">Helpful</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger feedback-badge">Not Helpful</span>
                                                <?php endif; ?>
                                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#feedbackModal<?php echo $conv['id']; ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="conversation-bubble user">
                                                <strong>User:</strong> <?php echo htmlspecialchars($conv['message']); ?>
                                            </div>
                                            <div class="conversation-bubble bot">
                                                <strong>Bot:</strong> <?php echo htmlspecialchars($conv['response']); ?>
                                            </div>
                                            <?php if ($conv['feedback']): ?>
                                                <div class="mt-2">
                                                    <small class="text-muted">
                                                        <strong>Feedback:</strong> <?php echo htmlspecialchars($conv['feedback']); ?>
                                                    </small>
                                                </div>
                                            <?php endif; ?>
                                            <div class="mt-2">
                                                <small class="text-muted">
                                                    <strong>Session:</strong> <?php echo htmlspecialchars($conv['session_id']); ?> |
                                                    <strong>IP:</strong> <?php echo htmlspecialchars($conv['user_ip']); ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                                <nav aria-label="Conversations pagination">
                                    <ul class="pagination justify-content-center">
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">Previous</a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $total_pages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">Next</a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Feedback Modals -->
    <?php foreach ($conversations as $conv): ?>
        <div class="modal fade" id="feedbackModal<?php echo $conv['id']; ?>" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Update Feedback</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="conversation_id" value="<?php echo $conv['id']; ?>">
                            
                            <div class="mb-3">
                                <label class="form-label">Was this response helpful?</label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input type="radio" name="is_helpful" value="1" class="form-check-input" 
                                               <?php echo $conv['is_helpful'] === '1' ? 'checked' : ''; ?>>
                                        <label class="form-check-label">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input type="radio" name="is_helpful" value="0" class="form-check-input" 
                                               <?php echo $conv['is_helpful'] === '0' ? 'checked' : ''; ?>>
                                        <label class="form-check-label">No</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Additional Feedback</label>
                                <textarea name="feedback" class="form-control" rows="3" 
                                          placeholder="Optional feedback about this conversation..."><?php echo htmlspecialchars($conv['feedback'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="update_feedback" class="btn btn-primary">Update Feedback</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

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
