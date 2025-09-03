<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once '../config/database.php';
$database = new Database();
$db = $database->connect();

// Initialize statistics with default values
$stats = [
    'enrollees' => 0,
    'pending' => 0,
    'approved' => 0,
    'students' => 0,
    'faculty' => 0,
    'users' => 0
];

$recent_applications = [];

try {
    // Check if tables exist and get statistics
    $tables_query = "SHOW TABLES LIKE 'enrollees'";
    $tables_stmt = $db->query($tables_query);
    
    if ($tables_stmt->rowCount() > 0) {
        // Total enrollees
        $enrollees_query = "SELECT COUNT(*) as total FROM enrollees";
        $enrollees_stmt = $db->query($enrollees_query);
        $stats['enrollees'] = $enrollees_stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Pending applications
        $pending_query = "SELECT COUNT(*) as total FROM enrollees WHERE status = 'Pending'";
        $pending_stmt = $db->query($pending_query);
        $stats['pending'] = $pending_stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Approved applications
        $approved_query = "SELECT COUNT(*) as total FROM enrollees WHERE status = 'Approved'";
        $approved_stmt = $db->query($approved_query);
        $stats['approved'] = $approved_stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Recent applications
        $recent_query = "SELECT * FROM enrollees ORDER BY created_at DESC LIMIT 5";
        $recent_stmt = $db->query($recent_query);
        $recent_applications = $recent_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Check if students table exists
    $students_tables_query = "SHOW TABLES LIKE 'students'";
    $students_tables_stmt = $db->query($students_tables_query);
    
    if ($students_tables_stmt->rowCount() > 0) {
        // Total students
        $students_query = "SELECT COUNT(*) as total FROM students";
        $students_stmt = $db->query($students_query);
        $stats['students'] = $students_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
    
    // Check if faculty table exists
    $faculty_tables_query = "SHOW TABLES LIKE 'faculty'";
    $faculty_tables_stmt = $db->query($faculty_tables_query);
    
    if ($faculty_tables_stmt->rowCount() > 0) {
        // Total faculty
        $faculty_query = "SELECT COUNT(*) as total FROM faculty";
        $faculty_stmt = $db->query($faculty_query);
        $stats['faculty'] = $faculty_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
    
    // Check if users table exists
    $users_tables_query = "SHOW TABLES LIKE 'users'";
    $users_tables_stmt = $db->query($users_tables_query);
    
    if ($users_tables_stmt->rowCount() > 0) {
        // Total users
        $users_query = "SELECT COUNT(*) as total FROM users";
        $users_stmt = $db->query($users_query);
        $stats['users'] = $users_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
    
} catch (PDOException $e) {
    // Handle database errors gracefully
    error_log("Database error in admin dashboard: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - OCC Enrollment System</title>
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
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .status-approved {
            background: #d4edda;
            color: #155724;
        }
        .status-rejected {
            background: #f8d7da;
            color: #721c24;
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
                        <a class="nav-link active" href="dashboard.php">
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
                            <h2 class="mb-1">Admin Dashboard</h2>
                            <p class="text-muted mb-0">Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <small class="text-muted">Logged in as</small>
                                <div class="fw-bold"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                            </div>
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <span class="text-white fw-bold"><?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-4 col-lg-2 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['enrollees']); ?></h4>
                                    <p class="mb-0 small">Total Applications</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['pending']); ?></h4>
                                    <p class="mb-0 small">Pending Review</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['approved']); ?></h4>
                                    <p class="mb-0 small">Approved</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['students']); ?></h4>
                                    <p class="mb-0 small">Enrolled Students</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-chalkboard-teacher"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['faculty']); ?></h4>
                                    <p class="mb-0 small">Faculty Members</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-user-shield"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['users']); ?></h4>
                                    <p class="mb-0 small">System Users</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Applications -->
                    <div class="table-container">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">
                                <i class="fas fa-list me-2"></i>Recent Applications
                            </h5>
                            <a href="manage_students.php" class="btn btn-primary btn-custom">
                                <i class="fas fa-eye me-2"></i>View All
                            </a>
                        </div>
                        
                        <?php if (empty($recent_applications)): ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                <p>No applications found. The system may still be setting up or no applications have been submitted yet.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Program</th>
                                            <th>Status</th>
                                            <th>Date Applied</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_applications as $app): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold"><?php echo htmlspecialchars($app['lastname'] . ', ' . $app['firstname']); ?></div>
                                            </td>
                                            <td><?php echo htmlspecialchars($app['email']); ?></td>
                                            <td>
                                                <span class="badge bg-info"><?php echo htmlspecialchars($app['preferred_program']); ?></span>
                                            </td>
                                            <td>
                                                <span class="status-badge status-<?php echo strtolower($app['status']); ?>">
                                                    <?php echo htmlspecialchars($app['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($app['created_at'])); ?></td>
                                            <td>
                                                <a href="view.php?id=<?php echo $app['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($app['status'] === 'Pending'): ?>
                                                <a href="verify_enrollment.php?id=<?php echo $app['id']; ?>" class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                                <a href="reject_application.php?id=<?php echo $app['id']; ?>" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Quick Actions -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-tasks me-2"></i>Quick Actions
                                    </h5>
                                    <div class="d-grid gap-2">
                                        <a href="manage_users.php" class="btn btn-outline-primary">
                                            <i class="fas fa-users me-2"></i>Manage Users
                                        </a>
                                        <a href="manage_faculty.php" class="btn btn-outline-success">
                                            <i class="fas fa-chalkboard-teacher me-2"></i>Manage Faculty
                                        </a>
                                        <a href="reports.php" class="btn btn-outline-info">
                                            <i class="fas fa-chart-bar me-2"></i>Generate Reports
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-info-circle me-2"></i>System Status
                                    </h5>
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <div class="border-end">
                                                <h4 class="text-success mb-1">
                                                    <i class="fas fa-check-circle"></i>
                                                </h4>
                                                <small class="text-muted">System Online</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <h4 class="text-info mb-1">
                                                <i class="fas fa-database"></i>
                                            </h4>
                                            <small class="text-muted">Database Connected</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
