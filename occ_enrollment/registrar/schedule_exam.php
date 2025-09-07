<?php
session_start();
if (!isset($_SESSION['registrar_logged_in'])) {
    header("Location: login.php");
    exit();
}

require_once '../config/database.php';
require_once '../includes/EmailHelper_Test.php';

$database = new Database();
$db = $database->connect();
$emailHelper = new EmailHelper_Test($db);

$message = '';
$error = '';

// Get verified enrollees who don't have exam schedules yet
$query = "SELECT e.* FROM enrollees e 
          LEFT JOIN entrance_exam_schedules ex ON e.id = ex.enrollee_id 
          WHERE ex.id IS NULL 
          ORDER BY e.created_at DESC";
$stmt = $db->query($query);
$enrollees = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle exam scheduling
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['schedule_exam'])) {
    try {
        $enrolleeId = $_POST['enrollee_id'];
        $examDate = $_POST['exam_date'];
        $examTime = $_POST['exam_time'];
        $examVenue = $_POST['exam_venue'] ?? 'OCC Campus';
        
        // Insert exam schedule
        $insertQuery = "INSERT INTO entrance_exam_schedules (enrollee_id, exam_date, exam_time, exam_venue, scheduled_by) VALUES (?, ?, ?, ?, ?)";
        $insertStmt = $db->prepare($insertQuery);
        $insertStmt->execute([$enrolleeId, $examDate, $examTime, $examVenue, $_SESSION['registrar_id'] ?? 1]);
        
        // Get enrollee details for email
        $enrolleeQuery = "SELECT * FROM enrollees WHERE id = ?";
        $enrolleeStmt = $db->prepare($enrolleeQuery);
        $enrolleeStmt->execute([$enrolleeId]);
        $enrollee = $enrolleeStmt->fetch(PDO::FETCH_ASSOC);
        
        // Send email notification
        $emailSent = $emailHelper->sendEntranceExamNotification(
            $enrollee['email'],
            $enrollee['firstname'],
            $enrollee['lastname'],
            $examDate,
            $examTime,
            $examVenue
        );
        
        if ($emailSent) {
            $message = "Entrance exam scheduled successfully! Email notification sent to " . $enrollee['email'];
        } else {
            $message = "Entrance exam scheduled successfully, but email notification failed to send.";
        }
        
        // Refresh the page to show updated list
        header("Location: schedule_exam.php?status=success");
        exit();
        
    } catch (Exception $e) {
        $error = "An error occurred while scheduling the exam: " . $e->getMessage();
    }
}

// Get existing exam schedules
$schedulesQuery = "SELECT ex.*, e.firstname, e.lastname, e.email, e.preferred_program 
                   FROM entrance_exam_schedules ex 
                   JOIN enrollees e ON ex.enrollee_id = e.id 
                   ORDER BY ex.exam_date, ex.exam_time";
$schedulesStmt = $db->query($schedulesQuery);
$schedules = $schedulesStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Entrance Exam - OCC Enrollment System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../includes/modern-dashboard.css" rel="stylesheet">

</head>`n<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h2><i class="fas fa-graduation-cap"></i> OCC Registrar</h2>
            <p>Enrollment Management System</p>
        </div>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="old_students.php" class="nav-link">
                    <i class="fas fa-users"></i>
                    Old Students
                </a>
            </li>
            <li class="nav-item">
                <a href="faculty_list.php" class="nav-link">
                    <i class="fas fa-chalkboard-teacher"></i>
                    Faculty
                </a>
            </li>
            <li class="nav-item">
                <a href="schedule_exam.php" class="nav-link active">
                    <i class="fas fa-calendar-alt"></i>
                    Schedule Exam
                </a>
            </li>
            <li class="nav-item">
                <a href="subject_list.php" class="nav-link">
                    <i class="fas fa-book"></i>
                    Subjects
                </a>
            </li>
            <li class="nav-item">
                <a href="course_list.php" class="nav-link">
                    <i class="fas fa-certificate"></i>
                    Courses
                </a>
            </li>
            <li class="nav-item" style="margin-top: 2rem;">
                <a href="logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </li>
        </ul>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">Schedule Entrance Exam</h1>
            <p class="page-subtitle">Schedule entrance exams for verified enrollees</p>
        </div>

        <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            <div class="message success">
                <i class="fas fa-check-circle"></i>
                Entrance exam scheduled successfully! Email notification sent to the enrollee.
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="message error">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">Pending Exam Schedules</h2>
                <span class="badge"><?php echo count($enrollees); ?> enrollees</span>
            </div>
            
            <?php if (empty($enrollees)): ?>
                <p style="text-align: center; color: #666; padding: 2rem;">
                    <i class="fas fa-check-circle" style="font-size: 3rem; color: #28a745; margin-bottom: 1rem;"></i><br>
                    All verified enrollees have been scheduled for entrance exams.
                </p>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Program</th>
                                <th>Contact</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($enrollees as $enrollee): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($enrollee['firstname'] . ' ' . $enrollee['lastname']); ?></strong></td>
                                <td><?php echo htmlspecialchars($enrollee['email']); ?></td>
                                <td><?php echo htmlspecialchars($enrollee['preferred_program']); ?></td>
                                <td><?php echo htmlspecialchars($enrollee['contact_no']); ?></td>
                                <td>
                                    <button onclick="openScheduleModal(<?php echo $enrollee['id']; ?>, '<?php echo htmlspecialchars($enrollee['firstname'] . ' ' . $enrollee['lastname']); ?>')" class="btn btn-primary">
                                        <i class="fas fa-calendar-plus"></i>
                                        Schedule Exam
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">Scheduled Exams</h2>
            </div>
            
            <?php if (empty($schedules)): ?>
                <p style="text-align: center; color: #666; padding: 2rem;">
                    <i class="fas fa-calendar" style="font-size: 3rem; color: #667eea; margin-bottom: 1rem;"></i><br>
                    No exams have been scheduled yet.
                </p>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Program</th>
                                <th>Exam Date</th>
                                <th>Exam Time</th>
                                <th>Venue</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($schedules as $schedule): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($schedule['firstname'] . ' ' . $schedule['lastname']); ?></strong></td>
                                <td><?php echo htmlspecialchars($schedule['preferred_program']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($schedule['exam_date'])); ?></td>
                                <td><?php echo date('h:i A', strtotime($schedule['exam_time'])); ?></td>
                                <td><?php echo htmlspecialchars($schedule['exam_venue']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $schedule['status']; ?>">
                                        <?php echo ucfirst($schedule['status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Schedule Exam Modal -->
    <div id="scheduleModal" class="modal">
        <div class="modal-content">
            <h3 style="margin-bottom: 1.5rem; color: #333;">Schedule Entrance Exam</h3>
            <form method="POST">
                <input type="hidden" id="enrollee_id" name="enrollee_id">
                
                <div class="form-group">
                    <label for="enrollee_name">Enrollee Name</label>
                    <input type="text" id="enrollee_name" class="form-control" readonly>
                </div>
                
                <div class="form-group">
                    <label for="exam_date">Exam Date</label>
                    <input type="date" id="exam_date" name="exam_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <div class="form-group">
                    <label for="exam_time">Exam Time</label>
                    <input type="time" id="exam_time" name="exam_time" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="exam_venue">Venue</label>
                    <input type="text" id="exam_venue" name="exam_venue" class="form-control" value="OCC Campus" required>
                </div>
                
                <div style="text-align: right; margin-top: 2rem;">
                    <button type="button" onclick="closeScheduleModal()" class="btn btn-secondary" style="margin-right: 1rem;">
                        Cancel
                    </button>
                    <button type="submit" name="schedule_exam" class="btn btn-primary">
                        <i class="fas fa-calendar-check"></i>
                        Schedule Exam
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openScheduleModal(enrolleeId, enrolleeName) {
        document.getElementById('enrollee_id').value = enrolleeId;
        document.getElementById('enrollee_name').value = enrolleeName;
        document.getElementById('scheduleModal').style.display = 'block';
    }

    function closeScheduleModal() {
        document.getElementById('scheduleModal').style.display = 'none';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        var modal = document.getElementById('scheduleModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
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
