<?php
session_start();
require_once '../../../config/database.php';

// Check if user is logged in and is a client
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'client') {
    header('Location: /dulas/index.php');
    exit();
}

$db = getDB();
$userId = $_SESSION['user_id'];

// Get user information
$stmt = $db->prepare("SELECT u.*, cp.* FROM users u 
                     LEFT JOIN client_profiles cp ON u.id = cp.user_id 
                     WHERE u.id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();



// Handle service selection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['service_type'])) {
    $_SESSION['selected_service'] = $_POST['service_type'];
    
    if ($_POST['service_type'] === 'pro') {
        header('Location: pro.php');
    } else {
        header('Location: complete-profile.php');
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Service - Dulas Legal Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-green: #00572d;
            --secondary-green: #1f9345;
            --accent-yellow: #f3c300;
            --sidebar-width: 250px;
        }

        body {
            min-height: 100vh;
            background-color: #f8f9fa;
        }

        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            background: var(--primary-green);
            color: white;
            padding: 1rem;
            transition: all 0.3s ease;
        }

        .sidebar-header {
            padding: 1rem 0;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header img {
            width: 80px;
            margin-bottom: 1rem;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.8rem 1rem;
            border-radius: 5px;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }

        .nav-link:hover, .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }

        .nav-link i {
            width: 25px;
        }

        /* Main Content Styles */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
        }

        /* Header Styles */
        .top-header {
            background: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-green);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        /* Service Card Styles */
        .service-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .service-card:hover {
            transform: translateY(-5px);
        }

        .service-card.free {
            border-top: 5px solid var(--accent-yellow);
        }

        .service-card.pro {
            border-top: 5px solid var(--primary-green);
        }

        .service-icon {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
        }

        .service-card.free .service-icon {
            background: #fff3e0;
            color: #ef6c00;
        }

        .service-card.pro .service-icon {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .feature-list {
            list-style: none;
            padding: 0;
            margin: 1.5rem 0;
        }

        .feature-list li {
            padding: 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .feature-list li i {
            color: var(--primary-green);
        }

        .timeline {
            position: relative;
            padding: 2rem 0;
        }

        .timeline::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            width: 2px;
            height: 100%;
            background: var(--primary-green);
            transform: translateX(-50%);
        }

        .timeline-item {
            position: relative;
            margin-bottom: 2rem;
            width: 50%;
            padding-right: 2rem;
        }

        .timeline-item:nth-child(even) {
            margin-left: 50%;
            padding-right: 0;
            padding-left: 2rem;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            top: 0;
            right: -6px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--primary-green);
        }

        .timeline-item:nth-child(even)::before {
            right: auto;
            left: -6px;
        }

        .timeline-content {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <img src="/dulas/assets/images/logo.png" alt="Dulas Logo">
            <h5>Client Portal</h5>
        </div>
        <nav class="mt-4">
            <a href="dashboard.php" class="nav-link">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="select-service.php" class="nav-link active">
                <i class="fas fa-crown"></i> Select Service
            </a>
            <a href="/dulas/logout.php" class="nav-link text-danger">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Header -->
        <div class="top-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Choose Your Service Plan</h4>
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($user['full_name'] ?? 'U', 0, 1)); ?>
                </div>
                <div>
                    <h6 class="mb-0"><?php echo htmlspecialchars($user['full_name'] ?? 'User'); ?></h6>
                    <small class="text-muted">Client</small>
                </div>
            </div>
        </div>

        <!-- Timeline -->
        <div class="timeline mb-5">
            <div class="timeline-item">
                <div class="timeline-content">
                    <h5><i class="fas fa-user-check"></i> Account Created</h5>
                    <p class="text-muted mb-0">Your account has been successfully created.</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-content">
                    <h5><i class="fas fa-crown"></i> Select Service</h5>
                    <p class="text-muted mb-0">Choose between Free and Pro service plans.</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-content">
                    <h5><i class="fas fa-user-edit"></i> Complete Profile</h5>
                    <p class="text-muted mb-0">Fill in your required information.</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-content">
                    <h5><i class="fas fa-check-circle"></i> Ready to Go</h5>
                    <p class="text-muted mb-0">Start using our legal services.</p>
                </div>
            </div>
        </div>

        <!-- Service Cards -->
        <div class="row">
            <!-- Free Service -->
            <div class="col-md-6">
                <form method="POST" action="">
                    <input type="hidden" name="service_type" value="free">
                    <div class="service-card free">
                        <div class="service-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <h3>Free Service</h3>
                        <p class="text-muted">Basic legal services for individuals</p>
                        <ul class="feature-list">
                            <li><i class="fas fa-check"></i> Basic Legal Consultation</li>
                            <li><i class="fas fa-check"></i> Document Review</li>
                            <li><i class="fas fa-check"></i> Email Support</li>
                            <li><i class="fas fa-check"></i> Basic Case Tracking</li>
                        </ul>
                        <button type="submit" class="btn btn-warning btn-lg w-100">
                            Select Free Plan
                        </button>
                    </div>
                </form>
            </div>

            <!-- Pro Service -->
            <div class="col-md-6">
                <form method="POST" action="">
                    <input type="hidden" name="service_type" value="pro">
                    <div class="service-card pro">
                        <div class="service-icon">
                            <i class="fas fa-crown"></i>
                        </div>
                        <h3>Pro Service</h3>
                        <p class="text-muted">Comprehensive legal services with premium features</p>
                        <ul class="feature-list">
                            <li><i class="fas fa-check"></i> Priority Legal Consultation</li>
                            <li><i class="fas fa-check"></i> Full Document Preparation</li>
                            <li><i class="fas fa-check"></i> 24/7 Support</li>
                            <li><i class="fas fa-check"></i> Advanced Case Management</li>
                            <li><i class="fas fa-check"></i> Court Representation</li>
                        </ul>
                        <button type="submit" class="btn btn-success btn-lg w-100">
                            Select Pro Plan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 