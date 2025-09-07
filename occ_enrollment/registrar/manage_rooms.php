<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is a registrar
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'registrar') {
    header('Location: login.php');
    exit();
}

$database = new Database();
$pdo = $database->connect();

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
                    $stmt = $pdo->prepare("INSERT INTO rooms (room_name, capacity, room_type, building, floor) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $_POST['room_name'],
                        $_POST['capacity'],
                        $_POST['room_type'],
                        $_POST['building'],
                        $_POST['floor']
                    ]);
                    $_SESSION['flash_message'] = "Room added successfully!";
                    header('Location: manage_rooms.php');
                    exit();
                } catch (PDOException $e) {
                    $_SESSION['flash_error'] = "Error adding room: " . $e->getMessage();
                    header('Location: manage_rooms.php');
                    exit();
                }
                break;

            case 'edit':
                try {
                    $stmt = $pdo->prepare("UPDATE rooms SET room_name = ?, capacity = ?, room_type = ?, building = ?, floor = ? WHERE room_id = ?");
                    $stmt->execute([
                        $_POST['room_name'],
                        $_POST['capacity'],
                        $_POST['room_type'],
                        $_POST['building'],
                        $_POST['floor'],
                        $_POST['room_id']
                    ]);
                    $_SESSION['flash_message'] = "Room updated successfully!";
                    header('Location: manage_rooms.php');
                    exit();
                } catch (PDOException $e) {
                    $_SESSION['flash_error'] = "Error updating room: " . $e->getMessage();
                    header('Location: manage_rooms.php');
                    exit();
                }
                break;

            case 'delete':
                try {
                    $stmt = $pdo->prepare("DELETE FROM rooms WHERE room_id = ?");
                    $stmt->execute([$_POST['room_id']]);
                    $_SESSION['flash_message'] = "Room deleted successfully!";
                    header('Location: manage_rooms.php');
                    exit();
                } catch (PDOException $e) {
                    $_SESSION['flash_error'] = "Error deleting room: " . $e->getMessage();
                    header('Location: manage_rooms.php');
                    exit();
                }
                break;
        }
    }
}

// Get rooms list
try {
    $stmt = $pdo->query("SELECT * FROM rooms ORDER BY building, floor, room_name ASC");
    $rooms_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error loading rooms data: " . $e->getMessage();
    $rooms_list = [];
}

// Get statistics
try {
    $stats_query = "SELECT 
        COUNT(*) as total_rooms,
        COUNT(CASE WHEN room_type = 'Classroom' THEN 1 END) as classrooms,
        COUNT(CASE WHEN room_type = 'Laboratory' THEN 1 END) as laboratories,
        COUNT(CASE WHEN room_type = 'Computer Lab' THEN 1 END) as computer_labs,
        COUNT(CASE WHEN room_type = 'Conference Room' THEN 1 END) as conference_rooms,
        SUM(capacity) as total_capacity
    FROM rooms";
    $stats_stmt = $pdo->query($stats_query);
    $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $stats = ['total_rooms' => 0, 'classrooms' => 0, 'laboratories' => 0, 'computer_labs' => 0, 'conference_rooms' => 0, 'total_capacity' => 0];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Rooms - OCC Enrollment System</title>
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
                <a class="nav-link" href="manage_schedules.php">
                    <i class="fas fa-calendar-alt me-2"></i>
                    Schedule Management
                        
                </a>
            </div>
                        <div class="nav-item">
                <a class="nav-link active" href="manage_rooms.php">
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
                            <h2 class="mb-1">Room Management</h2>
                            <p class="text-muted mb-0">Manage classrooms and facilities</p>
                        </div>
                        <button class="btn btn-primary btn-custom" data-bs-toggle="modal" data-bs-target="#addRoomModal">
                            <i class="fas fa-plus me-2"></i>Add New Room
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
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['total_rooms'] ?? 0); ?></h4>
                                    <p class="mb-0 small">Total Rooms</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-chalkboard"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['classrooms'] ?? 0); ?></h4>
                                    <p class="mb-0 small">Classrooms</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-flask"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['laboratories'] ?? 0); ?></h4>
                                    <p class="mb-0 small">Laboratories</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-laptop"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['computer_labs'] ?? 0); ?></h4>
                                    <p class="mb-0 small">Computer Labs</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['conference_rooms'] ?? 0); ?></h4>
                                    <p class="mb-0 small">Conference</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Capacity Card -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h3 class="mb-0">Total Capacity: <?php echo number_format($stats['total_capacity'] ?? 0); ?> Students</h3>
                                    <p class="mb-0">Combined capacity across all rooms</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rooms Table -->
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Rooms List</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Room Name/Number</th>
                                            <th>Type</th>
                                            <th>Capacity</th>
                                            <th>Building</th>
                                            <th>Floor</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($rooms_list)): ?>
                                            <tr>
                                                <td colspan="7" class="text-center text-muted">No rooms found</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($rooms_list as $room): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($room['room_id']); ?></td>
                                                                                                    <td>
                                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($room['room_name']); ?></span>
                                                </td>
                                                    <td>
                                                        <?php
                                                        $type_badge = match($room['room_type']) {
                                                            'Classroom' => 'bg-primary',
                                                            'Laboratory' => 'bg-success',
                                                            'Computer Lab' => 'bg-info',
                                                            'Conference Room' => 'bg-warning',
                                                            default => 'bg-secondary'
                                                        };
                                                        ?>
                                                        <span class="badge <?php echo $type_badge; ?>"><?php echo htmlspecialchars($room['room_type']); ?></span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-success"><?php echo htmlspecialchars($room['capacity']); ?> students</span>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($room['building']); ?></td>
                                                    <td>
                                                        <span class="badge bg-dark"><?php echo htmlspecialchars($room['floor']); ?> Floor</span>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-primary me-1" 
                                                                onclick="editRoom(<?php echo htmlspecialchars(json_encode($room)); ?>)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-danger" 
                                                                onclick="deleteRoom(<?php echo $room['room_id']; ?>, '<?php echo htmlspecialchars($room['room_name']); ?>')">
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

    <!-- Add Room Modal -->
    <div class="modal fade" id="addRoomModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Room</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="mb-3">
                            <label for="room_name" class="form-label">Room Name/Number *</label>
                            <input type="text" class="form-control" id="room_name" name="room_name" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="capacity" class="form-label">Capacity *</label>
                                    <input type="number" class="form-control" id="capacity" name="capacity" min="10" max="100" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="room_type" class="form-label">Room Type *</label>
                                    <select class="form-select" id="room_type" name="room_type" required>
                                        <option value="">Select Type</option>
                                        <option value="Classroom">Classroom</option>
                                        <option value="Laboratory">Laboratory</option>
                                        <option value="Computer Lab">Computer Lab</option>
                                        <option value="Conference Room">Conference Room</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="building" class="form-label">Building *</label>
                                    <input type="text" class="form-control" id="building" name="building" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="floor" class="form-label">Floor *</label>
                                    <input type="number" class="form-control" id="floor" name="floor" min="1" max="10" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Room</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Room Modal -->
    <div class="modal fade" id="editRoomModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Room</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="room_id" id="edit_room_id">
                        
                        <div class="mb-3">
                            <label for="edit_room_name" class="form-label">Room Name/Number *</label>
                            <input type="text" class="form-control" id="edit_room_name" name="room_name" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_capacity" class="form-label">Capacity *</label>
                                    <input type="number" class="form-control" id="edit_capacity" name="capacity" min="10" max="100" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_room_type" class="form-label">Room Type *</label>
                                    <select class="form-select" id="edit_room_type" name="room_type" required>
                                        <option value="">Select Type</option>
                                        <option value="Classroom">Classroom</option>
                                        <option value="Laboratory">Laboratory</option>
                                        <option value="Computer Lab">Computer Lab</option>
                                        <option value="Conference Room">Conference Room</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_building" class="form-label">Building *</label>
                                    <input type="text" class="form-control" id="edit_building" name="building" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_floor" class="form-label">Floor *</label>
                                    <input type="number" class="form-control" id="edit_floor" name="floor" min="1" max="10" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Room</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteRoomModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong id="delete_room_name"></strong>?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <form method="POST">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="room_id" id="delete_room_id">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editRoom(room) {
            document.getElementById('edit_room_id').value = room.room_id;
            document.getElementById('edit_room_name').value = room.room_name;
            document.getElementById('edit_capacity').value = room.capacity;
            document.getElementById('edit_room_type').value = room.room_type;
            document.getElementById('edit_building').value = room.building;
            document.getElementById('edit_floor').value = room.floor;
            
            new bootstrap.Modal(document.getElementById('editRoomModal')).show();
        }

        function deleteRoom(id, name) {
            document.getElementById('delete_room_id').value = id;
            document.getElementById('delete_room_name').textContent = name;
            
            new bootstrap.Modal(document.getElementById('deleteRoomModal')).show();
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
