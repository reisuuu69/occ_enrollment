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

// Handle filters
$user_type_filter = $_GET['user_type'] ?? '';
$action_filter = $_GET['action'] ?? '';
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
    $where_conditions[] = "al.user_type = ?";
    $params[] = $user_type_filter;
}

if ($action_filter) {
    $where_conditions[] = "al.action = ?";
    $params[] = $action_filter;
}

if ($date_from) {
    $where_conditions[] = "DATE(al.created_at) >= ?";
    $params[] = $date_from;
}

if ($date_to) {
    $where_conditions[] = "DATE(al.created_at) <= ?";
    $params[] = $date_to;
}

if ($search) {
    $where_conditions[] = "(al.action LIKE ? OR al.table_name LIKE ? OR al.ip_address LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count
$count_query = "SELECT COUNT(*) FROM audit_logs al $where_clause";
$stmt = $pdo->prepare($count_query);
$stmt->execute($params);
$total_records = $stmt->fetchColumn();
$total_pages = ceil($total_records / $per_page);

// Get audit logs
$query = "
    SELECT al.*, u.username, u.email
    FROM audit_logs al
    LEFT JOIN users u ON al.user_id = u.id
    $where_clause
    ORDER BY al.created_at DESC
    LIMIT $per_page OFFSET $offset
";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$audit_logs = $stmt->fetchAll();

// Get unique user types and actions for filters
$user_types = $pdo->query("SELECT DISTINCT user_type FROM audit_logs ORDER BY user_type")->fetchAll(PDO::FETCH_COLUMN);
$actions = $pdo->query("SELECT DISTINCT action FROM audit_logs ORDER BY action")->fetchAll(PDO::FETCH_COLUMN);

// Get statistics
$stats = $pdo->query("
    SELECT 
        COUNT(*) as total_logs,
        COUNT(DISTINCT user_id) as unique_users,
        COUNT(DISTINCT DATE(created_at)) as active_days,
        COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 END) as last_24h
    FROM audit_logs
")->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Logs - Admin Dashboard</title>
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
        .action-badge {
            font-size: 0.75rem;
            padding: 4px 8px;
        }
        .table-responsive {
            border-radius: 15px;
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
                        <a class="nav-link active" href="audit_logs.php">
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
                                <i class="fas fa-history me-2"></i>Audit Logs
                            </h2>
                            <p class="text-muted mb-0">System activity monitoring and tracking</p>
                        </div>
                        <div>
                            <a href="dashboard.php" class="btn btn-secondary btn-custom">
                                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                            </a>
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-list"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['total_logs']); ?></h4>
                                    <p class="mb-0 small">Total Logs</p>
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
                                        <i class="fas fa-calendar"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['active_days']); ?></h4>
                                    <p class="mb-0 small">Active Days</p>
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
                                    <label class="form-label">Action</label>
                                    <select name="action" class="form-select">
                                        <option value="">All Actions</option>
                                        <?php foreach ($actions as $action): ?>
                                            <option value="<?php echo $action; ?>" <?php echo $action_filter === $action ? 'selected' : ''; ?>>
                                                <?php echo $action; ?>
                                            </option>
                                        <?php endforeach; ?>
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
                                <div class="col-md-2">
                                    <label class="form-label">Search</label>
                                    <input type="text" name="search" class="form-control" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-primary btn-custom">
                                            <i class="fas fa-search me-2"></i>Filter
                                        </button>
                                        <a href="audit_logs.php" class="btn btn-secondary btn-custom">
                                            <i class="fas fa-times me-2"></i>Clear
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Audit Logs Table -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-table me-2"></i>Audit Logs (<?php echo number_format($total_records); ?> records)</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date/Time</th>
                                            <th>User</th>
                                            <th>User Type</th>
                                            <th>Action</th>
                                            <th>Table</th>
                                            <th>Record ID</th>
                                            <th>IP Address</th>
                                            <th>Details</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($audit_logs)): ?>
                                            <tr>
                                                <td colspan="8" class="text-center text-muted">No audit logs found</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($audit_logs as $log): ?>
                                                <tr>
                                                    <td>
                                                        <small><?php echo date('M j, Y', strtotime($log['created_at'])); ?></small><br>
                                                        <small class="text-muted"><?php echo date('g:i A', strtotime($log['created_at'])); ?></small>
                                                    </td>
                                                    <td>
                                                        <?php if ($log['username']): ?>
                                                            <strong><?php echo htmlspecialchars($log['username']); ?></strong><br>
                                                            <small class="text-muted"><?php echo htmlspecialchars($log['email']); ?></small>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info action-badge">
                                                            <?php echo ucfirst($log['user_type']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $action_color = 'secondary';
                                                        switch ($log['action']) {
                                                            case 'LOGIN': $action_color = 'success'; break;
                                                            case 'LOGOUT': $action_color = 'warning'; break;
                                                            case 'CREATE': $action_color = 'primary'; break;
                                                            case 'UPDATE': $action_color = 'info'; break;
                                                            case 'DELETE': $action_color = 'danger'; break;
                                                            case 'VIEW': $action_color = 'secondary'; break;
                                                        }
                                                        ?>
                                                        <span class="badge bg-<?php echo $action_color; ?> action-badge">
                                                            <?php echo $log['action']; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if ($log['table_name']): ?>
                                                            <code><?php echo htmlspecialchars($log['table_name']); ?></code>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($log['record_id']): ?>
                                                            <span class="badge bg-light text-dark"><?php echo $log['record_id']; ?></span>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted"><?php echo htmlspecialchars($log['ip_address']); ?></small>
                                                    </td>
                                                    <td>
                                                        <?php if ($log['old_values'] || $log['new_values']): ?>
                                                            <button type="button" class="btn btn-sm btn-outline-info" 
                                                                    data-bs-toggle="modal" 
                                                                    data-bs-target="#detailsModal<?php echo $log['id']; ?>">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                                <nav aria-label="Audit logs pagination">
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

    <!-- Details Modals -->
    <?php foreach ($audit_logs as $log): ?>
        <?php if ($log['old_values'] || $log['new_values']): ?>
            <div class="modal fade" id="detailsModal<?php echo $log['id']; ?>" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Audit Log Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <?php if ($log['old_values']): ?>
                                    <div class="col-md-6">
                                        <h6>Old Values</h6>
                                        <pre class="bg-light p-3 rounded"><code><?php echo htmlspecialchars($log['old_values']); ?></code></pre>
                                    </div>
                                <?php endif; ?>
                                <?php if ($log['new_values']): ?>
                                    <div class="col-md-6">
                                        <h6>New Values</h6>
                                        <pre class="bg-light p-3 rounded"><code><?php echo htmlspecialchars($log['new_values']); ?></code></pre>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
