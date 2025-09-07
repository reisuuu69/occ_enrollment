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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../includes/modern-dashboard.css" rel="stylesheet">
</head>
<body>
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
            <div class="nav-item">
                <a class="nav-link active" href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
            </div>
            <div class="nav-item">
                <a class="nav-link" href="manage_students.php">
                    <i class="fas fa-user-graduate"></i>
                    Manage Students
                </a>
            </div>
            <div class="nav-item">
                <a class="nav-link" href="manage_faculty.php">
                    <i class="fas fa-chalkboard-teacher"></i>
                    Manage Faculty
                </a>
            </div>
            <div class="nav-item">
                <a class="nav-link" href="manage_course.php">
                    <i class="fas fa-book"></i>
                    Course Management
                </a>
            </div>
            <div class="nav-item">
                <a class="nav-link" href="manage_subjects.php">
                    <i class="fas fa-file-alt"></i>
                    Subject Management
                </a>
            </div>
            <div class="nav-item">
                <a class="nav-link" href="manage_sections.php">
                    <i class="fas fa-layer-group"></i>
                    Section Management
                </a>
            </div>
            <div class="nav-item">
                <a class="nav-link" href="manage_schedules.php">
                    <i class="fas fa-calendar-alt"></i>
                    Schedule Management
                </a>
            </div>
            <div class="nav-item">
                <a class="nav-link" href="manage_rooms.php">
                    <i class="fas fa-building"></i>
                    Room Management
                </a>
            </div>
            <div class="nav-item">
                <a class="nav-link" href="manage_users.php">
                    <i class="fas fa-users"></i>
                    Manage Users
                </a>
            </div>
            <div class="nav-item">
                <a class="nav-link" href="assignments.php">
                    <i class="fas fa-tasks"></i>
                    Assignments
                </a>
            </div>
            <div class="nav-item">
                <a class="nav-link" href="reports.php">
                    <i class="fas fa-chart-bar"></i>
                    Reports
                </a>
            </div>
            <div class="nav-item">
                <a class="nav-link" href="audit_logs.php">
                    <i class="fas fa-history"></i>
                    Audit Logs
                </a>
            </div>
            <div class="nav-item">
                <a class="nav-link" href="chatbot_responses.php">
                    <i class="fas fa-robot"></i>
                    Chatbot Responses
                </a>
            </div>
            <div class="nav-item">
                <a class="nav-link" href="chatbot_conversations.php">
                    <i class="fas fa-comments"></i>
                    Chatbot Conversations
                </a>
            </div>
            <div class="nav-divider"></div>
            <div class="nav-item">
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="dashboard-title">Admin Dashboard</h1>
                    <p class="dashboard-subtitle">Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
                </div>
                <div class="user-info">
                    <div class="text-right">
                        <small class="text-muted">Logged in as</small>
                        <div class="fw-bold"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                    </div>
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['enrollees']); ?></div>
                <div class="stat-label">Total Applications</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['pending']); ?></div>
                <div class="stat-label">Pending Review</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['approved']); ?></div>
                <div class="stat-label">Approved</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['students']); ?></div>
                <div class="stat-label">Enrolled Students</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['faculty']); ?></div>
                <div class="stat-label">Faculty Members</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['users']); ?></div>
                <div class="stat-label">System Users</div>
            </div>
        </div>

        <!-- Dashboard Modules -->
        <div class="dashboard-grid">
            <!-- Faculty by Department Module -->
            <div class="module-card faculty-department">
                <div class="module-header">
                    <div class="module-icon faculty-department">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <h3 class="module-title">Faculty by Department</h3>
                        <p class="module-subtitle">Department-wise faculty distribution</p>
                    </div>
                </div>
                <div class="table-container">
                    <div class="empty-state">
                        <i class="fas fa-chart-pie"></i>
                        <h3>Faculty Analytics</h3>
                        <p>Faculty distribution data will be displayed here</p>
                    </div>
                </div>
            </div>

            <!-- Section Capacity Analysis Module -->
            <div class="module-card section-capacity">
                <div class="module-header">
                    <div class="module-icon section-capacity">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div>
                        <h3 class="module-title">Section Capacity Analysis</h3>
                        <p class="module-subtitle">Current section enrollment status</p>
                    </div>
                </div>
                <div class="table-container">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th>Section</th>
                                <th>Course</th>
                                <th>Year Level</th>
                                <th>Capacity</th>
                                <th>Enrolled</th>
                                <th>Occupancy Rate</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="7" class="text-center">
                                    <div class="empty-state">
                                        <i class="fas fa-table"></i>
                                        <h3>No Data Available</h3>
                                        <p>Section capacity data will be displayed here</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Enrollments Module -->
            <div class="module-card recent-enrollments">
                <div class="module-header">
                    <div class="module-icon recent-enrollments">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <h3 class="module-title">Recent Enrollments</h3>
                        <p class="module-subtitle">Latest student enrollments</p>
                    </div>
                </div>
                <div class="table-container">
                    <?php if (empty($recent_applications)): ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <h3>No Applications</h3>
                            <p>No applications found. The system may still be setting up or no applications have been submitted yet.</p>
                        </div>
                    <?php else: ?>
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Course</th>
                                    <th>Year</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_applications as $app): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($app['lastname'] . ', ' . $app['firstname']); ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?php echo htmlspecialchars($app['preferred_program']); ?></span>
                                    </td>
                                    <td>1st Year</td>
                                    <td><?php echo date('M d, Y', strtotime($app['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Room Utilization Module -->
            <div class="module-card room-utilization">
                <div class="module-header">
                    <div class="module-icon room-utilization">
                        <i class="fas fa-building"></i>
                    </div>
                    <div>
                        <h3 class="module-title">Room Utilization</h3>
                        <p class="module-subtitle">Current room usage statistics</p>
                    </div>
                </div>
                <div class="table-container">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th>Room</th>
                                <th>Type</th>
                                <th>Capacity</th>
                                <th>Classes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="4" class="text-center">
                                    <div class="empty-state">
                                        <i class="fas fa-door-open"></i>
                                        <h3>No Data Available</h3>
                                        <p>Room utilization data will be displayed here</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
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

        // Add fade-in animation to cards
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.module-card, .stat-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease-out';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>
