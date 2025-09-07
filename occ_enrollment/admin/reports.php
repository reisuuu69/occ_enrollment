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

// Get current school year and semester
$current_year = date('Y') . '-' . (date('Y') + 1);
$current_semester = (date('n') >= 6 && date('n') <= 10) ? 1 : 2;

// Get comprehensive statistics
try {
    // Overall statistics
    $overall_stats = $pdo->query("SELECT 
        (SELECT COUNT(*) FROM students) as total_students,
        (SELECT COUNT(*) FROM faculty) as total_faculty,
        (SELECT COUNT(*) FROM subjects) as total_subjects,
        (SELECT COUNT(*) FROM sections) as total_sections,
        (SELECT COUNT(*) FROM rooms) as total_rooms,
        (SELECT COUNT(*) FROM old_students) as total_old_students
    ")->fetch(PDO::FETCH_ASSOC);

    // Course-wise enrollment
    $course_enrollment = $pdo->query("SELECT 
        course,
        COUNT(*) as student_count,
        ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM students)), 2) as percentage
    FROM students 
    GROUP BY course 
    ORDER BY student_count DESC")->fetchAll(PDO::FETCH_ASSOC);

    // Year level distribution
    $year_level_dist = $pdo->query("SELECT 
        year_level,
        COUNT(*) as student_count
    FROM students 
    GROUP BY year_level 
    ORDER BY year_level")->fetchAll(PDO::FETCH_ASSOC);

    // Department-wise faculty
    $faculty_dept = $pdo->query("SELECT 
        department,
        COUNT(*) as faculty_count
    FROM faculty 
    GROUP BY department 
    ORDER BY faculty_count DESC")->fetchAll(PDO::FETCH_ASSOC);

    // Section capacity analysis
    $section_capacity = $pdo->query("SELECT 
        s.section_name,
        s.course,
        s.year_level,
        s.max_students,
        s.current_students,
        ROUND((s.current_students * 100.0 / s.max_students), 2) as occupancy_rate
    FROM sections s
    ORDER BY occupancy_rate DESC")->fetchAll(PDO::FETCH_ASSOC);

    // Recent enrollments
    $recent_enrollments = $pdo->query("SELECT 
        s.firstname,
        s.lastname,
        s.course,
        s.year_level,
        s.enrollment_date
    FROM students s
    ORDER BY s.enrollment_date DESC
    LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);

    // Room utilization
    $room_utilization = $pdo->query("SELECT 
        r.room_number,
        r.room_name,
        r.room_type,
        r.capacity,
        COUNT(ss.id) as scheduled_classes
    FROM rooms r
    LEFT JOIN subject_schedule ss ON r.id = ss.room_id
    GROUP BY r.id
    ORDER BY scheduled_classes DESC")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Error loading reports data: " . $e->getMessage();
    $overall_stats = [];
    $course_enrollment = [];
    $year_level_dist = [];
    $faculty_dept = [];
    $section_capacity = [];
    $recent_enrollments = [];
    $room_utilization = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - OCC Enrollment System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../includes/modern-dashboard.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
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
                <a class="nav-link active" href="reports.php">
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
                            <h2 class="mb-1">Reports & Analytics</h2>
                            <p class="text-muted mb-0">Comprehensive enrollment system reports</p>
                        </div>
                        <div class="text-end">
                            <p class="text-muted mb-0">School Year: <?php echo $current_year; ?></p>
                            <p class="text-muted mb-0">Semester: <?php echo $current_semester; ?></p>
                        </div>
                    </div>

                    <!-- Overall Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-4 col-lg-2 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($overall_stats['total_students'] ?? 0); ?></h4>
                                    <p class="mb-0 small">Total Students</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-chalkboard-teacher"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($overall_stats['total_faculty'] ?? 0); ?></h4>
                                    <p class="mb-0 small">Total Faculty</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-book"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($overall_stats['total_subjects'] ?? 0); ?></h4>
                                    <p class="mb-0 small">Total Subjects</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-layer-group"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($overall_stats['total_sections'] ?? 0); ?></h4>
                                    <p class="mb-0 small">Total Sections</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($overall_stats['total_rooms'] ?? 0); ?></h4>
                                    <p class="mb-0 small">Total Rooms</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-history"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($overall_stats['total_old_students'] ?? 0); ?></h4>
                                    <p class="mb-0 small">Old Students</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Row -->
                    <div class="row mb-4">
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Course Enrollment Distribution</h5>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="courseChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Year Level Distribution</h5>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="yearLevelChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Faculty Department Chart -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0"><i class="fas fa-chart-doughnut me-2"></i>Faculty by Department</h5>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="facultyChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Capacity Analysis -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-warning text-white">
                                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Section Capacity Analysis</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-light">
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
                                                <?php foreach ($section_capacity as $section): ?>
                                                    <tr>
                                                        <td><strong><?php echo htmlspecialchars($section['section_name']); ?></strong></td>
                                                        <td><span class="badge bg-info"><?php echo htmlspecialchars($section['course']); ?></span></td>
                                                        <td><span class="badge bg-warning"><?php echo htmlspecialchars($section['year_level']); ?>st Year</span></td>
                                                        <td><?php echo htmlspecialchars($section['max_students']); ?></td>
                                                        <td><?php echo htmlspecialchars($section['current_students']); ?></td>
                                                        <td>
                                                            <div class="progress">
                                                                <div class="progress-bar bg-<?php echo $section['occupancy_rate'] >= 90 ? 'danger' : ($section['occupancy_rate'] >= 75 ? 'warning' : 'success'); ?>" 
                                                                     style="width: <?php echo $section['occupancy_rate']; ?>%">
                                                                    <?php echo $section['occupancy_rate']; ?>%
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <?php
                                                            $status_badge = match(true) {
                                                                $section['occupancy_rate'] >= 90 => 'bg-danger',
                                                                $section['occupancy_rate'] >= 75 => 'bg-warning',
                                                                default => 'bg-success'
                                                            };
                                                            $status_text = match(true) {
                                                                $section['occupancy_rate'] >= 90 => 'Full',
                                                                $section['occupancy_rate'] >= 75 => 'Nearly Full',
                                                                default => 'Available'
                                                            };
                                                            ?>
                                                            <span class="badge <?php echo $status_badge; ?>"><?php echo $status_text; ?></span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Enrollments & Room Utilization -->
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Recent Enrollments</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Student Name</th>
                                                    <th>Course</th>
                                                    <th>Year</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($recent_enrollments as $enrollment): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($enrollment['firstname'] . ' ' . $enrollment['lastname']); ?></td>
                                                        <td><span class="badge bg-info"><?php echo htmlspecialchars($enrollment['course']); ?></span></td>
                                                        <td><span class="badge bg-warning"><?php echo htmlspecialchars($enrollment['year_level']); ?>st Year</span></td>
                                                        <td><?php echo date('M j, Y', strtotime($enrollment['enrollment_date'])); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0"><i class="fas fa-building me-2"></i>Room Utilization</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Room</th>
                                                    <th>Type</th>
                                                    <th>Capacity</th>
                                                    <th>Classes</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($room_utilization as $room): ?>
                                                    <tr>
                                                        <td>
                                                            <strong><?php echo htmlspecialchars($room['room_number']); ?></strong><br>
                                                            <small class="text-muted"><?php echo htmlspecialchars($room['room_name']); ?></small>
                                                        </td>
                                                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($room['room_type']); ?></span></td>
                                                        <td><?php echo htmlspecialchars($room['capacity']); ?></td>
                                                        <td>
                                                            <span class="badge bg-<?php echo $room['scheduled_classes'] > 0 ? 'success' : 'secondary'; ?>">
                                                                <?php echo htmlspecialchars($room['scheduled_classes']); ?> classes
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
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
    <script>
        // Course Enrollment Chart
        const courseCtx = document.getElementById('courseChart').getContext('2d');
        new Chart(courseCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($course_enrollment, 'course')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($course_enrollment, 'student_count')); ?>,
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Year Level Chart
        const yearCtx = document.getElementById('yearLevelChart').getContext('2d');
        new Chart(yearCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_map(function($year) { return $year['year_level'] . 'st Year'; }, $year_level_dist)); ?>,
                datasets: [{
                    label: 'Number of Students',
                    data: <?php echo json_encode(array_column($year_level_dist, 'student_count')); ?>,
                    backgroundColor: '#36A2EB',
                    borderColor: '#2693e6',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Faculty Department Chart
        const facultyCtx = document.getElementById('facultyChart').getContext('2d');
        new Chart(facultyCtx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode(array_column($faculty_dept, 'department')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($faculty_dept, 'faculty_count')); ?>,
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
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
