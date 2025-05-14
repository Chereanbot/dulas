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


// Check if service type is selected
if (!isset($_SESSION['selected_service'])) {
    // Redirect to service selection if not selected
    header('Location: select-service.php');
    exit();
}

// Calculate profile completion percentage
$requiredFields = ['phone', 'age', 'sex', 'family_members', 'health_status', 
                  'region', 'zone', 'wereda', 'kebele', 'house_number',
                  'case_type', 'case_category', 'id_proof_path'];
$completedFields = 0;

foreach ($requiredFields as $field) {
    if (!empty($user[$field])) {
        $completedFields++;
    }
}

$completionPercentage = ($completedFields / count($requiredFields)) * 100;

// Initialize variables
$recentCases = [];
$upcomingActivities = [];

// Check if cases table exists before querying
try {
    // Get recent cases
    $stmt = $db->prepare("SELECT * FROM cases WHERE client_id = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->execute([$userId]);
    $recentCases = $stmt->fetchAll();

    // Get upcoming activities
    $stmt = $db->prepare("SELECT ca.*, c.title as case_title 
                         FROM case_activities ca 
                         JOIN cases c ON ca.case_id = c.id 
                         WHERE c.client_id = ? 
                         AND ca.activity_date >= CURDATE() 
                         ORDER BY ca.activity_date ASC 
                         LIMIT 5");
    $stmt->execute([$userId]);
    $upcomingActivities = $stmt->fetchAll();
} catch (PDOException $e) {
    // If tables don't exist yet, just continue with empty arrays
    $recentCases = [];
    $upcomingActivities = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Dulas Legal Management System</title>
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

        /* Card Styles */
        .dashboard-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin: 0 auto 1rem;
        }

        .stat-card.primary .stat-icon {
            background: #e8f5e9;
            color: var(--primary-green);
        }

        .stat-card.warning .stat-icon {
            background: #fff3e0;
            color: #ef6c00;
        }

        .stat-card.success .stat-icon {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .stat-card.info .stat-icon {
            background: #e3f2fd;
            color: #1976d2;
        }

        .progress {
            height: 10px;
            border-radius: 5px;
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
            <a href="dashboard.php" class="nav-link active">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="complete-profile.php" class="nav-link">
                <i class="fas fa-user-edit"></i> Complete Profile
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
            <h4 class="mb-0">Welcome to Your Dashboard</h4>
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
                    <h5><i class="fas fa-crown"></i> Service Selected</h5>
                    <p class="text-muted mb-0">You've selected the <?php echo ucfirst($_SESSION['selected_service']); ?> service plan.</p>
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

        <!-- Profile Completion -->
        <div class="dashboard-card mb-4">
            <h5 class="mb-3">Profile Completion</h5>
            <div class="progress mb-2">
                <div class="progress-bar bg-success" role="progressbar" 
                     style="width: <?php echo $completionPercentage; ?>%" 
                     aria-valuenow="<?php echo $completionPercentage; ?>" 
                     aria-valuemin="0" 
                     aria-valuemax="100">
                </div>
            </div>
            <p class="text-muted mb-0">
                <?php echo round($completionPercentage); ?>% Complete
                <?php if ($completionPercentage < 100): ?>
                    - <a href="complete-profile.php" class="text-primary">Complete your profile</a>
                <?php endif; ?>
            </p>
        </div>

        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card primary">
                    <div class="stat-icon">
                        <i class="fas fa-gavel"></i>
                    </div>
                    <h3><?php echo count($recentCases); ?></h3>
                    <p class="text-muted mb-0">Total Cases</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card warning">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3><?php echo count($upcomingActivities); ?></h3>
                    <p class="text-muted mb-0">Upcoming Activities</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card success">
                    <div class="stat-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <h3>0</h3>
                    <p class="text-muted mb-0">Documents</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card info">
                    <div class="stat-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <h3>0</h3>
                    <p class="text-muted mb-0">Messages</p>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Recent Cases -->
            <div class="col-md-6">
                <div class="dashboard-card">
                    <h5 class="mb-3">Recent Cases</h5>
                    <?php if (empty($recentCases)): ?>
                        <p class="text-muted">No cases found.</p>
                    <?php else: ?>
                        <?php foreach ($recentCases as $case): ?>
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon" style="width: 40px; height: 40px; font-size: 1rem;">
                                        <i class="fas fa-gavel"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-0"><?php echo htmlspecialchars($case['title']); ?></h6>
                                    <small class="text-muted">
                                        Status: <?php echo htmlspecialchars($case['status']); ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Upcoming Activities -->
            <div class="col-md-6">
                <div class="dashboard-card">
                    <h5 class="mb-3">Upcoming Activities</h5>
                    <?php if (empty($upcomingActivities)): ?>
                        <p class="text-muted">No upcoming activities.</p>
                    <?php else: ?>
                        <?php foreach ($upcomingActivities as $activity): ?>
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0">
                                    <div class="stat-icon" style="width: 40px; height: 40px; font-size: 1rem;">
                                        <i class="fas fa-calendar"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-0"><?php echo htmlspecialchars($activity['title']); ?></h6>
                                    <small class="text-muted">
                                        <?php echo date('M d, Y', strtotime($activity['activity_date'])); ?> - 
                                        <?php echo htmlspecialchars($activity['case_title']); ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 