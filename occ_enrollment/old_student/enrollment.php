<?php
session_start();
if (!isset($_SESSION['student_logged_in'])) {
    header("Location: login.php");
    exit();
}

require_once '../config/database.php';
$database = new Database();
$db = $database->connect();

$query = "SELECT * FROM old_students WHERE student_id = :student_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':student_id', $_SESSION['student_id']);
$stmt->execute();
$student = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment - OCC Enrollment System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
        }

        .sidebar {
            width: 280px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 2px 0 20px rgba(0, 0, 0, 0.1);
            padding: 2rem 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-header {
            text-align: center;
            padding: 0 2rem 2rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .sidebar-header h2 {
            color: #333;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .sidebar-header p {
            color: #666;
            font-size: 0.9rem;
        }

        .nav-menu {
            list-style: none;
            padding: 0 1rem;
        }

        .nav-item {
            margin-bottom: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            color: #555;
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .nav-link:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            transform: translateX(5px);
        }

        .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .nav-link i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
        }

        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
            overflow-y: auto;
        }

        .page-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .page-title {
            color: #333;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            color: #666;
            font-size: 1rem;
        }

        .welcome-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .welcome-card h2 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }

        .welcome-card p {
            opacity: 0.9;
            font-size: 1rem;
        }

        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .content-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid rgba(0, 0, 0, 0.05);
        }

        .card-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.5rem;
            color: white;
        }

        .card-icon.primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .card-icon.success {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .card-icon.warning {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }

        .card-icon.info {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
        }

        .card-title {
            color: #333;
            font-size: 1.3rem;
            font-weight: 600;
        }

        .enrollment-status {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .enrollment-status h3 {
            font-size: 1.8rem;
            margin-bottom: 1rem;
        }

        .enrollment-status p {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            opacity: 0.9;
        }

        .status-badge {
            display: inline-block;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            margin: 1rem 0;
        }

        .status-not-enrolled {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .status-enrolled {
            background: rgba(76, 175, 80, 0.2);
            color: #2e7d32;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            padding: 1rem 2rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            margin-top: 1rem;
        }

        .btn-primary {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
        }

        .btn-primary:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .info-item {
            padding: 1rem;
            background: rgba(102, 126, 234, 0.05);
            border-radius: 12px;
            border-left: 4px solid #667eea;
        }

        .info-label {
            font-size: 0.8rem;
            color: #666;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }

        .info-value {
            font-size: 1rem;
            color: #333;
            font-weight: 600;
        }

        .enrollment-steps {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .steps-header {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .steps-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.5rem;
            color: white;
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }

        .steps-title {
            color: #333;
            font-size: 1.3rem;
            font-weight: 600;
        }

        .step-item {
            display: flex;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .step-item:last-child {
            border-bottom: none;
        }

        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 1rem;
        }

        .step-content h4 {
            color: #333;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .step-content p {
            color: #666;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .main-content {
                margin-left: 0;
            }

            .content-grid {
                grid-template-columns: 1fr;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h2><i class="fas fa-user-graduate"></i> Student Portal</h2>
            <p>Welcome back, <?php echo htmlspecialchars($student['firstname']); ?>!</p>
        </div>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link">
                    <i class="fas fa-user"></i>
                    Profile
                </a>
            </li>
            <li class="nav-item">
                <a href="checklist.php" class="nav-link">
                    <i class="fas fa-clipboard-check"></i>
                    Documents Checklist
                </a>
            </li>
            <li class="nav-item">
                <a href="enrollment.php" class="nav-link active">
                    <i class="fas fa-graduation-cap"></i>
                    Enrollment
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
            <h1 class="page-title">Enrollment</h1>
            <p class="page-subtitle">Manage your course enrollment and academic status</p>
        </div>

        <div class="enrollment-status">
            <h3>Current Enrollment Status</h3>
            <p>Student ID: <?php echo htmlspecialchars($student['student_id']); ?></p>
            <p>Name: <?php echo htmlspecialchars($student['firstname'] . ' ' . $student['lastname']); ?></p>
            <p>Course: <?php echo htmlspecialchars($student['course']); ?></p>
            <span class="status-badge status-not-enrolled">Not Yet Enrolled</span>
            <br>
            <a href="#" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Start Enrollment
            </a>
        </div>

        <div class="content-grid">
            <div class="content-card">
                <div class="card-header">
                    <div class="card-icon primary">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h3 class="card-title">Academic Information</h3>
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Course</div>
                        <div class="info-value"><?php echo htmlspecialchars($student['course']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Year Level</div>
                        <div class="info-value"><?php echo htmlspecialchars($student['year_level']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Section</div>
                        <div class="info-value"><?php echo htmlspecialchars($student['section']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Current Semester</div>
                        <div class="info-value"><?php echo htmlspecialchars($student['current_semester']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">School Year</div>
                        <div class="info-value"><?php echo htmlspecialchars($student['school_year']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Units Earned</div>
                        <div class="info-value"><?php echo htmlspecialchars($student['units_earned']); ?></div>
                    </div>
                </div>
            </div>

            <div class="content-card">
                <div class="card-header">
                    <div class="card-icon success">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3 class="card-title">Enrollment Period</h3>
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Current Semester</div>
                        <div class="info-value">1st Semester</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">School Year</div>
                        <div class="info-value">2024-2025</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Enrollment Start</div>
                        <div class="info-value">June 1, 2024</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Enrollment End</div>
                        <div class="info-value">June 30, 2024</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Classes Start</div>
                        <div class="info-value">August 1, 2024</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="enrollment-steps">
            <div class="steps-header">
                <div class="steps-icon">
                    <i class="fas fa-list-ol"></i>
                </div>
                <h3 class="steps-title">Enrollment Process</h3>
            </div>
            <div class="step-item">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h4>Complete Documents Checklist</h4>
                    <p>Ensure all required documents are submitted and verified</p>
                </div>
            </div>
            <div class="step-item">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h4>Pay Enrollment Fees</h4>
                    <p>Complete payment of tuition and other fees</p>
                </div>
            </div>
            <div class="step-item">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h4>Course Registration</h4>
                    <p>Register for subjects and schedule</p>
                </div>
            </div>
            <div class="step-item">
                <div class="step-number">4</div>
                <div class="step-content">
                    <h4>ID Card Processing</h4>
                    <p>Get your student ID card</p>
                </div>
            </div>
            <div class="step-item">
                <div class="step-number">5</div>
                <div class="step-content">
                    <h4>Orientation</h4>
                    <p>Attend student orientation program</p>
                </div>
            </div>
        </div>

        <div class="content-card">
            <div class="card-header">
                <div class="card-icon info">
                    <i class="fas fa-info-circle"></i>
                </div>
                <h3 class="card-title">Important Reminders</h3>
            </div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Deadline</div>
                    <div class="info-value">June 30, 2024</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Office Hours</div>
                    <div class="info-value">8:00 AM - 5:00 PM</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Contact</div>
                    <div class="info-value">Registrar's Office</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Requirements</div>
                    <div class="info-value">Complete Checklist</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
