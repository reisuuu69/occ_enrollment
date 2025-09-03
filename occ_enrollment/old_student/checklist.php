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
    <title>Documents Checklist - OCC Enrollment System</title>
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

        .card-title {
            color: #333;
            font-size: 1.3rem;
            font-weight: 600;
        }

        .checklist-grid {
            display: grid;
            gap: 1rem;
        }

        .checklist-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.5rem;
            background: rgba(102, 126, 234, 0.05);
            border-radius: 12px;
            border-left: 4px solid #667eea;
            transition: all 0.3s ease;
        }

        .checklist-item:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.1);
        }

        .checklist-info {
            display: flex;
            align-items: center;
        }

        .checklist-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.2rem;
            color: white;
        }

        .checklist-icon.document {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .checklist-icon.form {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .checklist-icon.certificate {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }

        .checklist-icon.picture {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
        }

        .checklist-details h3 {
            color: #333;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .checklist-details p {
            color: #666;
            font-size: 0.9rem;
        }

        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background: rgba(255, 152, 0, 0.2);
            color: #e65100;
        }

        .status-submitted {
            background: rgba(76, 175, 80, 0.2);
            color: #2e7d32;
        }

        .status-verified {
            background: rgba(33, 150, 243, 0.2);
            color: #1565c0;
        }

        .progress-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .progress-header {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .progress-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.5rem;
            color: white;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .progress-title {
            color: #333;
            font-size: 1.3rem;
            font-weight: 600;
        }

        .progress-bar {
            width: 100%;
            height: 12px;
            background: rgba(102, 126, 234, 0.1);
            border-radius: 6px;
            overflow: hidden;
            margin-bottom: 1rem;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 6px;
            transition: width 0.5s ease;
        }

        .progress-text {
            text-align: center;
            color: #666;
            font-weight: 600;
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
                <a href="checklist.php" class="nav-link active">
                    <i class="fas fa-clipboard-check"></i>
                    Documents Checklist
                </a>
            </li>
            <li class="nav-item">
                <a href="enrollment.php" class="nav-link">
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
            <h1 class="page-title">Documents Checklist</h1>
            <p class="page-subtitle">Track your required documents for enrollment</p>
        </div>

        <div class="welcome-card">
            <h2>Document Requirements</h2>
            <p>Complete all required documents to proceed with your enrollment</p>
        </div>

        <div class="progress-section">
            <div class="progress-header">
                <div class="progress-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3 class="progress-title">Completion Progress</h3>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: 0%;"></div>
            </div>
            <div class="progress-text">0 of 5 documents completed</div>
        </div>

        <div class="content-grid">
            <div class="content-card">
                <div class="card-header">
                    <div class="card-icon primary">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <h3 class="card-title">Required Documents</h3>
                </div>
                <div class="checklist-grid">
                    <div class="checklist-item">
                        <div class="checklist-info">
                            <div class="checklist-icon form">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div class="checklist-details">
                                <h3>Form 137</h3>
                                <p>Permanent Record from previous school</p>
                            </div>
                        </div>
                        <span class="status-badge status-pending">Pending</span>
                    </div>

                    <div class="checklist-item">
                        <div class="checklist-info">
                            <div class="checklist-icon form">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div class="checklist-details">
                                <h3>Form 138 (Report Card)</h3>
                                <p>Latest report card from previous school</p>
                            </div>
                        </div>
                        <span class="status-badge status-pending">Pending</span>
                    </div>

                    <div class="checklist-item">
                        <div class="checklist-info">
                            <div class="checklist-icon certificate">
                                <i class="fas fa-certificate"></i>
                            </div>
                            <div class="checklist-details">
                                <h3>Birth Certificate</h3>
                                <p>NSO/PSA authenticated birth certificate</p>
                            </div>
                        </div>
                        <span class="status-badge status-pending">Pending</span>
                    </div>

                    <div class="checklist-item">
                        <div class="checklist-info">
                            <div class="checklist-icon certificate">
                                <i class="fas fa-award"></i>
                            </div>
                            <div class="checklist-details">
                                <h3>Good Moral Certificate</h3>
                                <p>Certificate of good moral character</p>
                            </div>
                        </div>
                        <span class="status-badge status-pending">Pending</span>
                    </div>

                    <div class="checklist-item">
                        <div class="checklist-info">
                            <div class="checklist-icon picture">
                                <i class="fas fa-image"></i>
                            </div>
                            <div class="checklist-details">
                                <h3>2x2 ID Picture</h3>
                                <p>Recent 2x2 colored ID picture</p>
                            </div>
                        </div>
                        <span class="status-badge status-pending">Pending</span>
                    </div>
                </div>
            </div>

            <div class="content-card">
                <div class="card-header">
                    <div class="card-icon success">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <h3 class="card-title">Important Notes</h3>
                </div>
                <div class="checklist-grid">
                    <div class="checklist-item">
                        <div class="checklist-info">
                            <div class="checklist-icon warning">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="checklist-details">
                                <h3>Document Submission</h3>
                                <p>Submit all documents to the Registrar's Office</p>
                            </div>
                        </div>
                    </div>

                    <div class="checklist-item">
                        <div class="checklist-info">
                            <div class="checklist-icon warning">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="checklist-details">
                                <h3>Processing Time</h3>
                                <p>Documents are processed within 3-5 working days</p>
                            </div>
                        </div>
                    </div>

                    <div class="checklist-item">
                        <div class="checklist-info">
                            <div class="checklist-icon warning">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="checklist-details">
                                <h3>Contact Information</h3>
                                <p>For inquiries, contact the Registrar's Office</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Update progress bar based on completed documents
        function updateProgress() {
            const totalDocuments = 5;
            const completedDocuments = 0; // This would come from database in real implementation
            
            const progressFill = document.querySelector('.progress-fill');
            const progressText = document.querySelector('.progress-text');
            
            const percentage = (completedDocuments / totalDocuments) * 100;
            progressFill.style.width = percentage + '%';
            progressText.textContent = `${completedDocuments} of ${totalDocuments} documents completed`;
        }

        // Initialize progress on page load
        document.addEventListener('DOMContentLoaded', updateProgress);
    </script>
</body>
</html>
