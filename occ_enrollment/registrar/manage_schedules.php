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
                // Normalize/collect inputs
                $subjectId = (int)($_POST['subject_id'] ?? 0);
                $professorId = (int)($_POST['professor_id'] ?? 0);
                $roomId = (int)($_POST['room_id'] ?? 0);
                $day = $_POST['day'] ?? '';
                $startTime = $_POST['start_time'] ?? '';
                $endTime = $_POST['end_time'] ?? '';
                $schoolYear = trim($_POST['school_year'] ?? '');
                $semester = (int)($_POST['semester'] ?? 0);

                // Basic validation
                if (!$subjectId || !$professorId || !$roomId || !$day || !$startTime || !$endTime || !$schoolYear || !$semester) {
                    $error = "Please complete all required fields.";
                    break;
                }
                if (strtotime($endTime) <= strtotime($startTime)) {
                    $error = "End time must be later than start time.";
                    break;
                }

                // Check room conflict (overlap on same day/term)
                $roomConflictQuery = $pdo->prepare(
                    "SELECT COUNT(*) FROM subject_schedule 
                     WHERE room_id = ? AND day = ? AND school_year = ? AND semester = ? 
                     AND NOT (end_time <= ? OR start_time >= ?)"
                );
                $roomConflictQuery->execute([$roomId, $day, $schoolYear, $semester, $startTime, $endTime]);
                $roomConflict = (int)$roomConflictQuery->fetchColumn();
                if ($roomConflict > 0) {
                    $error = "Room is already booked for the selected time.";
                    break;
                }

                // Check professor conflict (overlap on same day/term)
                $profConflictQuery = $pdo->prepare(
                    "SELECT COUNT(*) FROM subject_schedule 
                     WHERE professor_id = ? AND day = ? AND school_year = ? AND semester = ? 
                     AND NOT (end_time <= ? OR start_time >= ?)"
                );
                $profConflictQuery->execute([$professorId, $day, $schoolYear, $semester, $startTime, $endTime]);
                $profConflict = (int)$profConflictQuery->fetchColumn();
                if ($profConflict > 0) {
                    $error = "Professor already has a class during the selected time.";
                    break;
                }

                try {
                    $stmt = $pdo->prepare("INSERT INTO subject_schedule (subject_id, professor_id, room_id, day, start_time, end_time, school_year, semester) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $subjectId,
                        $professorId,
                        $roomId,
                        $day,
                        $startTime,
                        $endTime,
                        $schoolYear,
                        $semester
                    ]);
                    $message = "Schedule added successfully!";
                } catch (PDOException $e) {
                    if ($e->getCode() === '23000') {
                        $error = "A conflicting schedule already exists (room or professor overlap).";
                    } else {
                        $error = "Error adding schedule: " . $e->getMessage();
                    }
                }
                break;

            case 'edit':
                try {
                    $stmt = $pdo->prepare("UPDATE subject_schedule SET subject_id = ?, professor_id = ?, room_id = ?, day = ?, start_time = ?, end_time = ?, school_year = ?, semester = ? WHERE id = ?");
                    $stmt->execute([
                        $_POST['subject_id'],
                        $_POST['professor_id'],
                        $_POST['room_id'],
                        $_POST['day'],
                        $_POST['start_time'],
                        $_POST['end_time'],
                        $_POST['school_year'],
                        $_POST['semester'],
                        $_POST['schedule_id']
                    ]);
                    $message = "Schedule updated successfully!";
                } catch (PDOException $e) {
                    $error = "Error updating schedule: " . $e->getMessage();
                }
                break;

            case 'delete':
                try {
                    $stmt = $pdo->prepare("DELETE FROM subject_schedule WHERE id = ?");
                    $stmt->execute([$_POST['schedule_id']]);
                    $message = "Schedule deleted successfully!";
                } catch (PDOException $e) {
                    $error = "Error deleting schedule: " . $e->getMessage();
                }
                break;
        }
    }
}

// Get schedules list with joined data
try {
    $stmt = $pdo->query("
        SELECT ss.*, s.subject_name, s.subject_code, f.professor_name, r.room_name, r.building
        FROM subject_schedule ss
        JOIN subjects s ON ss.subject_id = s.id
        JOIN faculty f ON ss.professor_id = f.id
        JOIN rooms r ON ss.room_id = r.room_id
        ORDER BY ss.day, ss.start_time ASC
    ");
    $schedules_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error loading schedules data: " . $e->getMessage();
    $schedules_list = [];
}

// Get subjects for dropdown
try {
    $stmt = $pdo->query("SELECT id, subject_code, subject_name FROM subjects ORDER BY subject_name ASC");
    $subjects_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $subjects_list = [];
}

// Get faculty for dropdown
try {
    $stmt = $pdo->query("SELECT id, professor_name, department FROM faculty ORDER BY professor_name ASC");
    $faculty_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $faculty_list = [];
}

// Get rooms for dropdown
try {
    $stmt = $pdo->query("SELECT room_id, room_name, building, floor FROM rooms ORDER BY building, floor, room_name ASC");
    $rooms_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $rooms_list = [];
}

// Get statistics
try {
    $stats_query = "SELECT 
        COUNT(*) as total_schedules,
        COUNT(CASE WHEN day = 'Monday' THEN 1 END) as monday_schedules,
        COUNT(CASE WHEN day = 'Tuesday' THEN 1 END) as tuesday_schedules,
        COUNT(CASE WHEN day = 'Wednesday' THEN 1 END) as wednesday_schedules,
        COUNT(CASE WHEN day = 'Thursday' THEN 1 END) as thursday_schedules,
        COUNT(CASE WHEN day = 'Friday' THEN 1 END) as friday_schedules,
        COUNT(CASE WHEN day = 'Saturday' THEN 1 END) as saturday_schedules
    FROM subject_schedule";
    $stats_stmt = $pdo->query($stats_query);
    $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $stats = ['total_schedules' => 0, 'monday_schedules' => 0, 'tuesday_schedules' => 0, 'wednesday_schedules' => 0, 'thursday_schedules' => 0, 'friday_schedules' => 0, 'saturday_schedules' => 0];
}

// Get current school year and semester
$current_year = date('Y') . '-' . (date('Y') + 1);
$current_semester = (date('n') >= 6 && date('n') <= 10) ? 1 : 2;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Schedules - OCC Enrollment System</title>
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
            <h4><i class="fas fa-clipboard-list"></i> Registrar Panel</h4>
            <p>OCC Enrollment System</p>
        </div>
        
        <nav class="sidebar-nav">
                    <div class="text-center mb-4">
                        <h4 class="text-white">Registrar Panel</h4>
                        <p class="text-white-50 small">OCC Enrollment System</p>
                    </div>
                    
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
                <a class="nav-link active" href="manage_schedules.php">
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
                            <h2 class="mb-1">Manage Schedules</h2>
                            <p class="text-muted mb-0">Organize and manage class schedules with virtualized table</p>
                        </div>
                        <button class="btn btn-primary btn-custom" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
                            <i class="fas fa-plus me-2"></i>Add Schedule
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
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['total_schedules'] ?? 0); ?></h4>
                                    <p class="mb-0 small">Total Schedules</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-sun"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['monday_schedules'] ?? 0); ?></h4>
                                    <p class="mb-0 small">Monday</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-cloud-sun"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['tuesday_schedules'] ?? 0); ?></h4>
                                    <p class="mb-0 small">Tuesday</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-cloud"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['wednesday_schedules'] ?? 0); ?></h4>
                                    <p class="mb-0 small">Wednesday</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-cloud-rain"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['thursday_schedules'] ?? 0); ?></h4>
                                    <p class="mb-0 small">Thursday</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-moon"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['friday_schedules'] ?? 0); ?></h4>
                                    <p class="mb-0 small">Friday</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Search and Filters -->
                    <div class="search-container">
                        <h5 class="mb-3"><i class="fas fa-search me-2"></i>Search & Filters</h5>
                        <div class="filter-group">
                            <div class="filter-item">
                                <label for="searchInput" class="form-label">Search</label>
                                <input type="text" class="form-control" id="searchInput" placeholder="Search schedules...">
                            </div>
                            <div class="filter-item">
                                <label for="dayFilter" class="form-label">Day</label>
                                <select class="form-select" id="dayFilter">
                                    <option value="">All Days</option>
                                    <option value="Monday">Monday</option>
                                    <option value="Tuesday">Tuesday</option>
                                    <option value="Wednesday">Wednesday</option>
                                    <option value="Thursday">Thursday</option>
                                    <option value="Friday">Friday</option>
                                    <option value="Saturday">Saturday</option>
                                </select>
                            </div>
                            <div class="filter-item">
                                <label for="semesterFilter" class="form-label">Semester</label>
                                <select class="form-select" id="semesterFilter">
                                    <option value="">All Semesters</option>
                                    <option value="1">1st Semester</option>
                                    <option value="2">2nd Semester</option>
                                    <option value="3">Summer</option>
                                </select>
                            </div>
                            <div class="filter-item">
                                <label for="itemsPerPage" class="form-label">Items per page</label>
                                <select class="form-select" id="itemsPerPage">
                                    <option value="10">10</option>
                                    <option value="25" selected>25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Virtualized Table -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-table me-2"></i>Class Schedules
                            </h5>
                            <div class="page-info">
                                Showing <span id="startIndex">1</span> to <span id="endIndex">25</span> of <span id="totalItems"><?php echo count($schedules_list); ?></span> schedules
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="virtual-table-container">
                                <table class="virtual-table" id="schedulesTable">
                                    <thead>
                                        <tr>
                                            <th>Subject</th>
                                            <th>Professor</th>
                                            <th>Room</th>
                                            <th>Day</th>
                                            <th>Time</th>
                                            <th>School Year</th>
                                            <th>Semester</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="schedulesTableBody">
                                        <!-- Virtualized content will be populated here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="pagination-container">
                        <button class="btn btn-outline-primary" id="prevPage" disabled>
                            <i class="fas fa-chevron-left"></i> Previous
                        </button>
                        <span class="page-info">
                            Page <span id="currentPage">1</span> of <span id="totalPages">1</span>
                        </span>
                        <button class="btn btn-outline-primary" id="nextPage">
                            Next <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Schedule Modal -->
    <div class="modal fade" id="addScheduleModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Schedule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="subject_id" class="form-label">Subject *</label>
                                    <select class="form-select" id="subject_id" name="subject_id" required>
                                        <option value="">Select Subject</option>
                                        <?php foreach ($subjects_list as $subject): ?>
                                            <option value="<?php echo $subject['id']; ?>">
                                                <?php echo htmlspecialchars($subject['subject_code'] . ' - ' . $subject['subject_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="professor_id" class="form-label">Professor *</label>
                                    <select class="form-select" id="professor_id" name="professor_id" required>
                                        <option value="">Select Professor</option>
                                        <?php foreach ($faculty_list as $faculty): ?>
                                            <option value="<?php echo $faculty['id']; ?>">
                                                <?php echo htmlspecialchars($faculty['professor_name'] . ' (' . $faculty['department'] . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="room_id" class="form-label">Room *</label>
                                    <select class="form-select" id="room_id" name="room_id" required>
                                        <option value="">Select Room</option>
                                        <?php foreach ($rooms_list as $room): ?>
                                            <option value="<?php echo $room['room_id']; ?>">
                                                <?php echo htmlspecialchars($room['room_name'] . ' - ' . $room['building'] . ' Floor ' . $room['floor']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="day" class="form-label">Day *</label>
                                    <select class="form-select" id="day" name="day" required>
                                        <option value="">Select Day</option>
                                        <option value="Monday">Monday</option>
                                        <option value="Tuesday">Tuesday</option>
                                        <option value="Wednesday">Wednesday</option>
                                        <option value="Thursday">Thursday</option>
                                        <option value="Friday">Friday</option>
                                        <option value="Saturday">Saturday</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="start_time" class="form-label">Start Time *</label>
                                    <input type="time" class="form-control" id="start_time" name="start_time" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="end_time" class="form-label">End Time *</label>
                                    <input type="time" class="form-control" id="end_time" name="end_time" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="semester" class="form-label">Semester *</label>
                                    <select class="form-select" id="semester" name="semester" required>
                                        <option value="">Select Semester</option>
                                        <option value="1" <?php echo $current_semester == 1 ? 'selected' : ''; ?>>1st Semester</option>
                                        <option value="2" <?php echo $current_semester == 2 ? 'selected' : ''; ?>>2nd Semester</option>
                                        <option value="3">Summer</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="school_year" class="form-label">School Year *</label>
                            <input type="text" class="form-control" id="school_year" name="school_year" value="<?php echo $current_year; ?>" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Schedule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Schedule Modal -->
    <div class="modal fade" id="editScheduleModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Schedule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" id="edit_schedule_id" name="schedule_id">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_subject_id" class="form-label">Subject *</label>
                                    <select class="form-select" id="edit_subject_id" name="subject_id" required>
                                        <option value="">Select Subject</option>
                                        <?php foreach ($subjects_list as $subject): ?>
                                            <option value="<?php echo $subject['id']; ?>">
                                                <?php echo htmlspecialchars($subject['subject_code'] . ' - ' . $subject['subject_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_professor_id" class="form-label">Professor *</label>
                                    <select class="form-select" id="edit_professor_id" name="professor_id" required>
                                        <option value="">Select Professor</option>
                                        <?php foreach ($faculty_list as $faculty): ?>
                                            <option value="<?php echo $faculty['id']; ?>">
                                                <?php echo htmlspecialchars($faculty['professor_name'] . ' (' . $faculty['department'] . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_room_id" class="form-label">Room *</label>
                                    <select class="form-select" id="edit_room_id" name="room_id" required>
                                        <option value="">Select Room</option>
                                        <?php foreach ($rooms_list as $room): ?>
                                            <option value="<?php echo $room['room_id']; ?>">
                                                <?php echo htmlspecialchars($room['room_name'] . ' - ' . $room['building'] . ' Floor ' . $room['floor']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_day" class="form-label">Day *</label>
                                    <select class="form-select" id="edit_day" name="day" required>
                                        <option value="">Select Day</option>
                                        <option value="Monday">Monday</option>
                                        <option value="Tuesday">Tuesday</option>
                                        <option value="Wednesday">Wednesday</option>
                                        <option value="Thursday">Thursday</option>
                                        <option value="Friday">Friday</option>
                                        <option value="Saturday">Saturday</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="edit_start_time" class="form-label">Start Time *</label>
                                    <input type="time" class="form-control" id="edit_start_time" name="start_time" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="edit_end_time" class="form-label">End Time *</label>
                                    <input type="time" class="form-control" id="edit_end_time" name="end_time" required>
                                </div>
                            </div>
                            <div class="col-md-4">
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
                        
                        <div class="mb-3">
                            <label for="edit_school_year" class="form-label">School Year *</label>
                            <input type="text" class="form-control" id="edit_school_year" name="school_year" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Schedule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteScheduleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the schedule for "<strong id="delete_schedule_name"></strong>"?</p>
                    <p class="text-danger small">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" id="delete_schedule_id" name="schedule_id">
                        <button type="submit" class="btn btn-danger">Delete Schedule</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Virtualized Table Implementation
        class VirtualizedTable {
            constructor(containerId, data, itemsPerPage = 25) {
                this.container = document.getElementById(containerId);
                this.data = data;
                this.filteredData = [...data];
                this.itemsPerPage = itemsPerPage;
                this.currentPage = 1;
                this.searchTerm = '';
                this.filters = {};
                
                this.init();
            }
            
            init() {
                this.render();
                this.setupEventListeners();
            }
            
            setupEventListeners() {
                // Search functionality
                document.getElementById('searchInput').addEventListener('input', (e) => {
                    this.searchTerm = e.target.value.toLowerCase();
                    this.filterData();
                });
                
                // Filter functionality
                document.getElementById('dayFilter').addEventListener('change', (e) => {
                    this.filters.day = e.target.value;
                    this.filterData();
                });
                
                document.getElementById('semesterFilter').addEventListener('change', (e) => {
                    this.filters.semester = e.target.value;
                    this.filterData();
                });
                
                // Items per page
                document.getElementById('itemsPerPage').addEventListener('change', (e) => {
                    this.itemsPerPage = parseInt(e.target.value);
                    this.currentPage = 1;
                    this.render();
                });
                
                // Pagination
                document.getElementById('prevPage').addEventListener('click', () => {
                    if (this.currentPage > 1) {
                        this.currentPage--;
                        this.render();
                    }
                });
                
                document.getElementById('nextPage').addEventListener('click', () => {
                    if (this.currentPage < this.getTotalPages()) {
                        this.currentPage++;
                        this.render();
                    }
                });
            }
            
            filterData() {
                this.filteredData = this.data.filter(item => {
                    // Search filter
                    if (this.searchTerm) {
                        const searchableText = `${item.subject_name} ${item.subject_code} ${item.professor_name} ${item.room_name}`.toLowerCase();
                        if (!searchableText.includes(this.searchTerm)) {
                            return false;
                        }
                    }
                    
                    // Day filter
                    if (this.filters.day && item.day !== this.filters.day) {
                        return false;
                    }
                    
                    // Semester filter
                    if (this.filters.semester && item.semester != this.filters.semester) {
                        return false;
                    }
                    
                    return true;
                });
                
                this.currentPage = 1;
                this.render();
            }
            
            getTotalPages() {
                return Math.ceil(this.filteredData.length / this.itemsPerPage);
            }
            
            getPageData() {
                const startIndex = (this.currentPage - 1) * this.itemsPerPage;
                const endIndex = startIndex + this.itemsPerPage;
                return this.filteredData.slice(startIndex, endIndex);
            }
            
            render() {
                const pageData = this.getPageData();
                const tbody = document.getElementById('schedulesTableBody');
                
                tbody.innerHTML = '';
                
                if (pageData.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                <br>No schedules found matching your criteria
                            </td>
                        </tr>
                    `;
                } else {
                    pageData.forEach(schedule => {
                        const row = this.createRow(schedule);
                        tbody.appendChild(row);
                    });
                }
                
                this.updatePagination();
                this.updatePageInfo();
            }
            
            createRow(schedule) {
                const row = document.createElement('tr');
                row.className = 'schedule-item';
                
                const dayBadge = this.getDayBadge(schedule.day);
                
                row.innerHTML = `
                    <td>
                        <div>
                            <strong>${this.escapeHtml(schedule.subject_name)}</strong>
                            <br>
                            <small class="text-muted">${this.escapeHtml(schedule.subject_code)}</small>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-info">${this.escapeHtml(schedule.professor_name)}</span>
                    </td>
                    <td>
                        <span class="badge bg-secondary">${this.escapeHtml(schedule.room_name)}</span>
                        <br>
                        <small class="text-muted">${this.escapeHtml(schedule.building)}</small>
                    </td>
                    <td>
                        <span class="badge ${dayBadge} day-badge">${this.escapeHtml(schedule.day)}</span>
                    </td>
                    <td>
                        <span class="badge time-badge">
                            ${this.escapeHtml(schedule.start_time)} - ${this.escapeHtml(schedule.end_time)}
                        </span>
                    </td>
                    <td>${this.escapeHtml(schedule.school_year)}</td>
                    <td>
                        <span class="badge bg-primary">${this.escapeHtml(schedule.semester)}st Sem</span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editSchedule(${JSON.stringify(schedule).replace(/"/g, '&quot;')})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteSchedule(${schedule.id}, '${this.escapeHtml(schedule.subject_name)}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                
                return row;
            }
            
            getDayBadge(day) {
                const badges = {
                    'Monday': 'bg-primary',
                    'Tuesday': 'bg-success',
                    'Wednesday': 'bg-warning',
                    'Thursday': 'bg-info',
                    'Friday': 'bg-dark',
                    'Saturday': 'bg-secondary'
                };
                return badges[day] || 'bg-secondary';
            }
            
            updatePagination() {
                const totalPages = this.getTotalPages();
                const prevBtn = document.getElementById('prevPage');
                const nextBtn = document.getElementById('nextPage');
                
                prevBtn.disabled = this.currentPage <= 1;
                nextBtn.disabled = this.currentPage >= totalPages;
                
                document.getElementById('currentPage').textContent = this.currentPage;
                document.getElementById('totalPages').textContent = totalPages;
            }
            
            updatePageInfo() {
                const startIndex = (this.currentPage - 1) * this.itemsPerPage + 1;
                const endIndex = Math.min(this.currentPage * this.itemsPerPage, this.filteredData.length);
                
                document.getElementById('startIndex').textContent = startIndex;
                document.getElementById('endIndex').textContent = endIndex;
                document.getElementById('totalItems').textContent = this.filteredData.length;
            }
            
            escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
        }
        
        // Initialize virtualized table when page loads
        document.addEventListener('DOMContentLoaded', function() {
            const schedulesData = <?php echo json_encode($schedules_list); ?>;
            window.virtualTable = new VirtualizedTable('schedulesTable', schedulesData);
        });
        
        function editSchedule(schedule) {
            document.getElementById('edit_schedule_id').value = schedule.id;
            document.getElementById('edit_subject_id').value = schedule.subject_id;
            document.getElementById('edit_professor_id').value = schedule.professor_id;
            document.getElementById('edit_room_id').value = schedule.room_id;
            document.getElementById('edit_day').value = schedule.day;
            document.getElementById('edit_start_time').value = schedule.start_time;
            document.getElementById('edit_end_time').value = schedule.end_time;
            document.getElementById('edit_school_year').value = schedule.school_year;
            document.getElementById('edit_semester').value = schedule.semester;
            
            new bootstrap.Modal(document.getElementById('editScheduleModal')).show();
        }

        function deleteSchedule(scheduleId, scheduleName) {
            document.getElementById('delete_schedule_id').value = scheduleId;
            document.getElementById('delete_schedule_name').textContent = scheduleName;
            
            new bootstrap.Modal(document.getElementById('deleteScheduleModal')).show();
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
