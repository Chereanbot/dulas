<?php
session_start();
require_once '../../config/database.php';

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

// Check if user exists
if (!$user) {
    // Redirect to login if user not found
    session_destroy();
    header('Location: /dulas/index.php');
    exit();
}

// Get user's cases
$stmt = $db->prepare("SELECT c.*, 
                     (SELECT COUNT(*) FROM case_documents WHERE case_id = c.id) as document_count,
                     (SELECT COUNT(*) FROM case_activities WHERE case_id = c.id) as activity_count
                     FROM cases c 
                     WHERE c.client_id = ? 
                     ORDER BY c.created_at DESC 
                     LIMIT 5");
$stmt->execute([$userId]);
$recentCases = $stmt->fetchAll();

// Get upcoming activities
$stmt = $db->prepare("SELECT ca.*, c.title as case_title 
                     FROM case_activities ca 
                     JOIN cases c ON ca.case_id = c.id 
                     WHERE c.client_id = ? 
                     AND ca.created_at >= CURDATE() 
                     ORDER BY ca.created_at ASC 
                     LIMIT 5");
$stmt->execute([$userId]);
$upcomingActivities = $stmt->fetchAll();

// Get case statistics
$stmt = $db->prepare("SELECT 
                     COUNT(*) as total_cases,
                     SUM(CASE WHEN status = 'ACTIVE' THEN 1 ELSE 0 END) as active_cases,
                     SUM(CASE WHEN status = 'PENDING' THEN 1 ELSE 0 END) as pending_cases,
                     SUM(CASE WHEN status = 'RESOLVED' THEN 1 ELSE 0 END) as resolved_cases
                     FROM cases 
                     WHERE client_id = ?");
$stmt->execute([$userId]);
$caseStats = $stmt->fetch();

// Initialize case stats if null
$caseStats = array_merge([
    'total_cases' => 0,
    'active_cases' => 0,
    'pending_cases' => 0,
    'resolved_cases' => 0
], $caseStats ?: []);

// Get user's full name safely
$userFullName = $user['full_name'] ?? 'User';
$userInitial = !empty($userFullName) ? strtoupper(substr($userFullName, 0, 1)) : 'U';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard - Dulas Legal Management System</title>
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
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .activity-item {
            padding: 1rem;
            border-left: 3px solid var(--primary-green);
            margin-bottom: 1rem;
            background: white;
            border-radius: 5px;
        }

        .case-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .case-status {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
        }

        .status-active {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .status-pending {
            background: #fff3e0;
            color: #ef6c00;
        }

        .status-resolved {
            background: #e3f2fd;
            color: #1976d2;
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
            <a href="cases.php" class="nav-link">
                <i class="fas fa-gavel"></i> My Cases
            </a>
            <a href="documents.php" class="nav-link">
                <i class="fas fa-file-alt"></i> Documents
            </a>
            <a href="messages.php" class="nav-link">
                <i class="fas fa-envelope"></i> Messages
            </a>
            <a href="profile.php" class="nav-link">
                <i class="fas fa-user"></i> Profile
            </a>
            <a href="settings.php" class="nav-link">
                <i class="fas fa-cog"></i> Settings
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
            <h4 class="mb-0">Dashboard</h4>
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo $userInitial; ?>
                </div>
                <div>
                    <h6 class="mb-0"><?php echo htmlspecialchars($userFullName); ?></h6>
                    <small class="text-muted">Client</small>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #e8f5e9; color: #2e7d32;">
                        <i class="fas fa-gavel"></i>
                    </div>
                    <h3><?php echo $caseStats['total_cases']; ?></h3>
                    <p class="text-muted mb-0">Total Cases</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #fff3e0; color: #ef6c00;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3><?php echo $caseStats['active_cases']; ?></h3>
                    <p class="text-muted mb-0">Active Cases</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #e3f2fd; color: #1976d2;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3><?php echo $caseStats['resolved_cases']; ?></h3>
                    <p class="text-muted mb-0">Resolved Cases</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #fce4ec; color: #c2185b;">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <h3><?php echo array_sum(array_column($recentCases, 'document_count')); ?></h3>
                    <p class="text-muted mb-0">Total Documents</p>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Recent Cases -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Recent Cases</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentCases)): ?>
                            <p class="text-muted">No cases found.</p>
                        <?php else: ?>
                            <?php foreach ($recentCases as $case): ?>
                                <div class="case-card">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($case['title'] ?? ''); ?></h6>
                                            <p class="text-muted mb-2"><?php echo htmlspecialchars($case['description'] ?? ''); ?></p>
                                            <div class="d-flex gap-3">
                                                <span class="case-status status-<?php echo strtolower($case['status'] ?? 'pending'); ?>">
                                                    <?php echo $case['status'] ?? 'PENDING'; ?>
                                                </span>
                                                <small class="text-muted">
                                                    <i class="fas fa-file-alt me-1"></i>
                                                    <?php echo $case['document_count'] ?? 0; ?> Documents
                                                </small>
                                                <small class="text-muted">
                                                    <i class="fas fa-tasks me-1"></i>
                                                    <?php echo $case['activity_count'] ?? 0; ?> Activities
                                                </small>
                                            </div>
                                        </div>
                                        <a href="view-case.php?id=<?php echo $case['id']; ?>" class="btn btn-sm btn-primary">
                                            View Details
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Upcoming Activities -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Upcoming Activities</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($upcomingActivities)): ?>
                            <p class="text-muted">No upcoming activities.</p>
                        <?php else: ?>
                            <?php foreach ($upcomingActivities as $activity): ?>
                                <div class="activity-item">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($activity['title'] ?? ''); ?></h6>
                                    <p class="text-muted mb-1"><?php echo htmlspecialchars($activity['case_title'] ?? ''); ?></p>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?php echo date('M d, Y', strtotime($activity['created_at'] ?? 'now')); ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 