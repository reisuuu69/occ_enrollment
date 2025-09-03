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
                try {
                    $stmt = $pdo->prepare("INSERT INTO faculty (professor_name, department, specialization, email, contact_number) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $_POST['professor_name'],
                        $_POST['department'],
                        $_POST['specialization'],
                        $_POST['email'],
                        $_POST['contact_number']
                    ]);
                    $message = "Faculty member added successfully!";
                } catch (PDOException $e) {
                    $error = "Error adding faculty member: " . $e->getMessage();
                }
                break;

            case 'edit':
                try {
                    $stmt = $pdo->prepare("UPDATE faculty SET professor_name = ?, department = ?, specialization = ?, email = ?, contact_number = ? WHERE id = ?");
                    $stmt->execute([
                        $_POST['professor_name'],
                        $_POST['department'],
                        $_POST['specialization'],
                        $_POST['email'],
                        $_POST['contact_number'],
                        $_POST['faculty_id']
                    ]);
                    $message = "Faculty member updated successfully!";
                } catch (PDOException $e) {
                    $error = "Error updating faculty member: " . $e->getMessage();
                }
                break;

            case 'delete':
                try {
                    $stmt = $pdo->prepare("DELETE FROM faculty WHERE id = ?");
                    $stmt->execute([$_POST['faculty_id']]);
                    $message = "Faculty member deleted successfully!";
                } catch (PDOException $e) {
                    $error = "Error deleting faculty member: " . $e->getMessage();
                }
                break;
        }
    }
}

// Get faculty list
try {
    $stmt = $pdo->query("SELECT * FROM faculty ORDER BY professor_name ASC");
    $faculty_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error loading faculty data: " . $e->getMessage();
    $faculty_list = [];
}

// Get statistics
try {
    $stats_query = "SELECT 
        COUNT(*) as total_faculty,
        COUNT(CASE WHEN department = 'Computer Science' THEN 1 END) as cs_faculty,
        COUNT(CASE WHEN department = 'Information Technology' THEN 1 END) as it_faculty,
        COUNT(CASE WHEN department = 'Education' THEN 1 END) as edu_faculty,
        COUNT(CASE WHEN department = 'Technology' THEN 1 END) as tech_faculty
    FROM faculty";
    $stats_stmt = $pdo->query($stats_query);
    $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $stats = ['total_faculty' => 0, 'cs_faculty' => 0, 'it_faculty' => 0, 'edu_faculty' => 0, 'tech_faculty' => 0];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Faculty - OCC Enrollment System</title>
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
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar p-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white">Registrar Panel</h4>
                        <p class="text-white-50 small">OCC Enrollment System</p>
                    </div>
                    
                    <nav class="nav flex-column">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                        </a>
                        <a class="nav-link" href="manage_students.php">
                            <i class="fas fa-user-graduate me-2"></i> Manage Students
                        </a>
                        <a class="nav-link active" href="manage_faculty.php">
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
                            <h2 class="mb-1">Faculty Management</h2>
                            <p class="text-muted mb-0">Manage faculty members and their information</p>
                        </div>
                        <button class="btn btn-primary btn-custom" data-bs-toggle="modal" data-bs-target="#addFacultyModal">
                            <i class="fas fa-plus me-2"></i>Add New Faculty
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
                                        <i class="fas fa-chalkboard-teacher"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['total_faculty'] ?? 0); ?></h4>
                                    <p class="mb-0 small">Total Faculty</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-laptop-code"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['cs_faculty'] ?? 0); ?></h4>
                                    <p class="mb-0 small">Computer Science</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-network-wired"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['it_faculty'] ?? 0); ?></h4>
                                    <p class="mb-0 small">Information Tech</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-graduation-cap"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['edu_faculty'] ?? 0); ?></h4>
                                    <p class="mb-0 small">Education</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="icon mb-2">
                                        <i class="fas fa-cogs"></i>
                                    </div>
                                    <h4 class="mb-1"><?php echo number_format($stats['tech_faculty'] ?? 0); ?></h4>
                                    <p class="mb-0 small">Technology</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Add this after the statistics cards and before the Faculty Table -->
                    <div class="row mb-3">
                <div class="col-md-4">
                <input type="text" class="form-control" id="searchInput" placeholder="Search faculty by name..." onkeyup="filterTable()">
            </div>
            <div class="col-md-3">
                <select class="form-select" id="departmentFilter" onchange="filterTable()">
                                <option value="">All Departments</option>
                                <option value="Computer Science">Computer Science</option>
                                <option value="Information Technology">Information Technology</option>
                                <option value="Education">Education</option>
                                <option value="Technology">Technology</option>
                            <option value="Business">Business</option>
                        </select>
                    </div>
                </div>

                    <!-- Faculty Table -->
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Faculty List</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Department</th>
                                            <th>Specialization</th>
                                            <th>Email</th>
                                            <th>Contact</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="facultyTableBody">
                                        <?php if (empty($faculty_list)): ?>
                                            <tr>
                                                <td colspan="8" class="text-center text-muted">No faculty members found</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($faculty_list as $faculty): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($faculty['id']); ?></td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($faculty['professor_name']); ?></strong>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info"><?php echo htmlspecialchars($faculty['department']); ?></span>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($faculty['specialization']); ?></td>
                                                    <td>
                                                        <?php if ($faculty['email']): ?>
                                                            <a href="mailto:<?php echo htmlspecialchars($faculty['email']); ?>">
                                                                <?php echo htmlspecialchars($faculty['email']); ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="text-muted">No email</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($faculty['contact_number']): ?>
                                                            <a href="tel:<?php echo htmlspecialchars($faculty['contact_number']); ?>">
                                                                <?php echo htmlspecialchars($faculty['contact_number']); ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="text-muted">No contact</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo date('M j, Y', strtotime($faculty['created_at'])); ?></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-primary me-1" 
                                                                onclick="editFaculty(<?php echo htmlspecialchars(json_encode($faculty)); ?>)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-danger" 
                                                                onclick="deleteFaculty(<?php echo $faculty['id']; ?>, '<?php echo htmlspecialchars($faculty['professor_name']); ?>')">
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

    <!-- Add Faculty Modal -->
    <div class="modal fade" id="addFacultyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Faculty Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="mb-3">
                            <label for="professor_name" class="form-label">Professor Name *</label>
                            <input type="text" class="form-control" id="professor_name" name="professor_name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="department" class="form-label">Department *</label>
                            <select class="form-select" id="department" name="department" required>
                                <option value="">Select Department</option>
                                <option value="Computer Science">Computer Science</option>
                                <option value="Information Technology">Information Technology</option>
                                <option value="Education">Education</option>
                                <option value="Technology">Technology</option>
                                <option value="Business">Business</option>
                                <option value="Arts and Sciences">Arts and Sciences</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="specialization" class="form-label">Specialization *</label>
                            <input type="text" class="form-control" id="specialization" name="specialization" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                        
                        <div class="mb-3">
                            <label for="contact_number" class="form-label">Contact Number</label>
                            <input type="text" class="form-control" id="contact_number" name="contact_number" placeholder="09XXXXXXXXX">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Faculty</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Faculty Modal -->
    <div class="modal fade" id="editFacultyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Faculty Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="faculty_id" id="edit_faculty_id">
                        
                        <div class="mb-3">
                            <label for="edit_professor_name" class="form-label">Professor Name *</label>
                            <input type="text" class="form-control" id="edit_professor_name" name="professor_name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_department" class="form-label">Department *</label>
                            <select class="form-select" id="edit_department" name="department" required>
                                <option value="">Select Department</option>
                                <option value="Computer Science">Computer Science</option>
                                <option value="Information Technology">Information Technology</option>
                                <option value="Education">Education</option>
                                <option value="Technology">Technology</option>
                                <option value="Business">Business</option>
                                <option value="Arts and Sciences">Arts and Sciences</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_specialization" class="form-label">Specialization *</label>
                            <input type="text" class="form-control" id="edit_specialization" name="specialization" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email">
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_contact_number" class="form-label">Contact Number</label>
                            <input type="text" class="form-control" id="edit_contact_number" name="contact_number" placeholder="09XXXXXXXXX">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Faculty</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteFacultyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong id="delete_faculty_name"></strong>?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <form method="POST">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="faculty_id" id="delete_faculty_id">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Virtualized Table Implementation for Faculty
        class VirtualizedFacultyTable {
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
                const searchInput = document.getElementById('searchInput');
                if (searchInput) {
                    searchInput.addEventListener('input', (e) => {
                        this.searchTerm = e.target.value.toLowerCase();
                        this.filterData();
                    });
                }
                
                // Filter functionality
                const departmentFilter = document.getElementById('departmentFilter');
                if (departmentFilter) {
                    departmentFilter.addEventListener('change', (e) => {
                        this.filters.department = e.target.value;
                        this.filterData();
                    });
                }
                
                const specializationFilter = document.getElementById('specializationFilter');
                if (specializationFilter) {
                    specializationFilter.addEventListener('change', (e) => {
                        this.filters.specialization = e.target.value;
                        this.filterData();
                    });
                }
                
                // Items per page
                const itemsPerPageSelect = document.getElementById('itemsPerPage');
                if (itemsPerPageSelect) {
                    itemsPerPageSelect.addEventListener('change', (e) => {
                        this.itemsPerPage = parseInt(e.target.value);
                        this.currentPage = 1;
                        this.render();
                    });
                }
                
                // Pagination
                const prevBtn = document.getElementById('prevPage');
                if (prevBtn) {
                    prevBtn.addEventListener('click', () => {
                        if (this.currentPage > 1) {
                            this.currentPage--;
                            this.render();
                        }
                    });
                }
                
                const nextBtn = document.getElementById('nextPage');
                if (nextBtn) {
                    nextBtn.addEventListener('click', () => {
                        if (this.currentPage < this.getTotalPages()) {
                            this.currentPage++;
                            this.render();
                        }
                    });
                }
            }
            
            filterData() {
                this.filteredData = this.data.filter(item => {
                    // Search filter
                    if (this.searchTerm) {
                        const searchableText = `${item.professor_name} ${item.department} ${item.specialization} ${item.email}`.toLowerCase();
                        if (!searchableText.includes(this.searchTerm)) {
                            return false;
                        }
                    }
                    
                    // Department filter
                    if (this.filters.department && item.department !== this.filters.department) {
                        return false;
                    }
                    
                    // Specialization filter
                    if (this.filters.specialization && item.specialization !== this.filters.specialization) {
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
                const tbody = document.getElementById('facultyTableBody');
                
                if (!tbody) return;
                
                tbody.innerHTML = '';
                
                if (pageData.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                <br>No faculty members found matching your criteria
                            </td>
                        </tr>
                    `;
                } else {
                    pageData.forEach(faculty => {
                        const row = this.createRow(faculty);
                        tbody.appendChild(row);
                    });
                }
                
                this.updatePagination();
                this.updatePageInfo();
            }
            
            createRow(faculty) {
                const row = document.createElement('tr');
                row.className = 'faculty-item';
                
                row.innerHTML = `
                    <td>${this.escapeHtml(faculty.id)}</td>
                    <td>
                        <strong>${this.escapeHtml(faculty.professor_name)}</strong>
                    </td>
                    <td>
                        <span class="badge bg-info">${this.escapeHtml(faculty.department)}</span>
                    </td>
                    <td>${this.escapeHtml(faculty.specialization)}</td>
                    <td>
                        ${faculty.email ? 
                            `<a href="mailto:${this.escapeHtml(faculty.email)}">${this.escapeHtml(faculty.email)}</a>` : 
                            '<span class="text-muted">No email</span>'
                        }
                    </td>
                    <td>
                        ${faculty.contact_number ? 
                            `<a href="tel:${this.escapeHtml(faculty.contact_number)}">${this.escapeHtml(faculty.contact_number)}</a>` : 
                            '<span class="text-muted">No contact</span>'
                        }
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editFaculty(${JSON.stringify(faculty).replace(/"/g, '&quot;')})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteFaculty(${faculty.id}, '${this.escapeHtml(faculty.professor_name)}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                
                return row;
            }
            
            updatePagination() {
                const totalPages = this.getTotalPages();
                const prevBtn = document.getElementById('prevPage');
                const nextBtn = document.getElementById('nextPage');
                
                if (prevBtn) prevBtn.disabled = this.currentPage <= 1;
                if (nextBtn) nextBtn.disabled = this.currentPage >= totalPages;
                
                const currentPageSpan = document.getElementById('currentPage');
                const totalPagesSpan = document.getElementById('totalPages');
                if (currentPageSpan) currentPageSpan.textContent = this.currentPage;
                if (totalPagesSpan) totalPagesSpan.textContent = totalPages;
            }
            
            updatePageInfo() {
                const startIndex = (this.currentPage - 1) * this.itemsPerPage + 1;
                const endIndex = Math.min(this.currentPage * this.itemsPerPage, this.filteredData.length);
                
                const startIndexSpan = document.getElementById('startIndex');
                const endIndexSpan = document.getElementById('endIndex');
                const totalItemsSpan = document.getElementById('totalItems');
                
                if (startIndexSpan) startIndexSpan.textContent = startIndex;
                if (endIndexSpan) endIndexSpan.textContent = endIndex;
                if (totalItemsSpan) totalItemsSpan.textContent = this.filteredData.length;
            }
            
            escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
        }
        
        // Initialize virtualized table when page loads
        document.addEventListener('DOMContentLoaded', function() {
            const facultyData = <?php echo json_encode($faculty_list); ?>;
            window.virtualTable = new VirtualizedFacultyTable('facultyTable', facultyData);
        });
        
        // Simple search and filter functionality
        function filterTable() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const departmentFilter = document.getElementById('departmentFilter').value;
            
            const table = document.querySelector('table');
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const name = row.cells[1].textContent.toLowerCase();
                const department = row.cells[2].textContent;
                
                let showRow = true;
                
                // Search filter
                if (searchTerm && !name.includes(searchTerm)) {
                    showRow = false;
                }
                
                // Department filter
                if (departmentFilter && department !== departmentFilter) {
                    showRow = false;
                }
                
                row.style.display = showRow ? '' : 'none';
            });
        }
        
        function editFaculty(faculty) {
            document.getElementById('edit_faculty_id').value = faculty.id;
            document.getElementById('edit_professor_name').value = faculty.professor_name;
            document.getElementById('edit_department').value = faculty.department;
            document.getElementById('edit_specialization').value = faculty.specialization;
            document.getElementById('edit_email').value = faculty.email || '';
            document.getElementById('edit_contact_number').value = faculty.contact_number || '';
            
            new bootstrap.Modal(document.getElementById('editFacultyModal')).show();
        }

        function deleteFaculty(id, name) {
            document.getElementById('delete_faculty_id').value = id;
            document.getElementById('delete_faculty_name').textContent = name;
            
            new bootstrap.Modal(document.getElementById('deleteFacultyModal')).show();
        }

        // Auto-refresh page after successful operations
        <?php if ($message || $error): ?>
        setTimeout(function() {
            location.reload();
        }, 2000);
        <?php endif; ?>
    </script>
</body>
</html>
