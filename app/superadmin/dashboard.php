<?php
// Include header
require_once('include/header.php');

// Include sidebar
require_once('include/sidebar.php');

// Database connection
require_once('../config/database.php');
$db = getDB();

// Get system statistics
try {
    // Total users count
    $totalUsers = $db->fetchOne("SELECT COUNT(*) as count FROM users")['count'] ?? 0;
    
    // Active sessions count
    $activeSessions = $db->fetchOne("SELECT COUNT(*) as count FROM user_sessions WHERE last_activity > DATE_SUB(NOW(), INTERVAL 30 MINUTE)")['count'] ?? 0;
    
    // Get recent user sessions
    $recentSessions = $db->fetchAll(
        "SELECT us.*, u.username, u.full_name, u.role 
         FROM user_sessions us 
         JOIN users u ON us.user_id = u.id 
         ORDER BY us.last_activity DESC 
         LIMIT 5"
    );
    
    // Get system status
    $systemStatus = [
        'database' => true,
        'server' => true,
        'backup' => true
    ];
    
} catch (Exception $e) {
    error_log("Error fetching dashboard data: " . $e->getMessage());
    $totalUsers = 0;
    $activeSessions = 0;
    $recentSessions = [];
    $systemStatus = [
        'database' => false,
        'server' => false,
        'backup' => false
    ];
}
?>
<style>
:root {
    --sidebar-width: 250px;
    --header-height: 60px;
}
body {
    margin: 0;
    padding: 0;
    background: #f4f4f4;
}
.header {
    position: fixed;
    top: 0;
    left: var(--sidebar-width);
    right: 0;
    height: var(--header-height);
    z-index: 1001;
    background: #fff;
}
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: var(--sidebar-width);
    height: 100vh;
    z-index: 1000;
    background: #00572d;
    color: #fff;
}
.main-content {
    margin-left: var(--sidebar-width);
    margin-top: var(--header-height);
    padding: 2rem 1.5rem 1.5rem 1.5rem;
    min-height: calc(100vh - var(--header-height));
    background: #f4f4f4;
}
</style>
<div class="main-content">
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Dashboard</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?php echo $totalUsers; ?></h3>
                            <p>Total Users</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <a href="users.php" class="small-box-footer">
                            More info <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?php echo $activeSessions; ?></h3>
                            <p>Active Sessions</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-sign-in-alt"></i>
                        </div>
                        <a href="user_sessions.php" class="small-box-footer">
                            More info <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>System</h3>
                            <p>Status</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-server"></i>
                        </div>
                        <a href="status.php" class="small-box-footer">
                            More info <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>Reports</h3>
                            <p>View Reports</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <a href="activity_reports.php" class="small-box-footer">
                            More info <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content Row -->
            <div class="row">
                <!-- Recent Sessions -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-sign-in-alt mr-1"></i>
                                Recent User Sessions
                            </h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Role</th>
                                            <th>Device</th>
                                            <th>Last Activity</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentSessions as $session): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="../assets/images/profile/<?php echo htmlspecialchars($session['profile_image'] ?? 'default-avatar.png'); ?>" 
                                                         class="img-circle mr-2" 
                                                         style="width: 32px; height: 32px; object-fit: cover;">
                                                    <div>
                                                        <div class="font-weight-bold"><?php echo htmlspecialchars($session['full_name']); ?></div>
                                                        <small class="text-muted"><?php echo htmlspecialchars($session['username']); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-info"><?php echo htmlspecialchars($session['role']); ?></span>
                                            </td>
                                            <td>
                                                <small>
                                                    <?php 
                                                    $deviceInfo = json_decode($session['device_info'], true);
                                                    echo htmlspecialchars($deviceInfo['browser'] ?? 'Unknown Browser') . ' on ' . 
                                                         htmlspecialchars($deviceInfo['os'] ?? 'Unknown OS');
                                                    ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php 
                                                $lastActivity = new DateTime($session['last_activity']);
                                                $now = new DateTime();
                                                $interval = $now->diff($lastActivity);
                                                
                                                if ($interval->d > 0) {
                                                    echo $interval->d . ' day' . ($interval->d > 1 ? 's' : '') . ' ago';
                                                } elseif ($interval->h > 0) {
                                                    echo $interval->h . ' hour' . ($interval->h > 1 ? 's' : '') . ' ago';
                                                } elseif ($interval->i > 0) {
                                                    echo $interval->i . ' min' . ($interval->i > 1 ? 's' : '') . ' ago';
                                                } else {
                                                    echo 'Just now';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer text-center">
                            <a href="user_sessions.php" class="text-primary">View All Sessions</a>
                        </div>
                    </div>
                </div>

                <!-- System Status -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-server mr-1"></i>
                                System Status
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="system-status">
                                <div class="status-item mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>Database Status</span>
                                        <span class="badge badge-<?php echo $systemStatus['database'] ? 'success' : 'danger'; ?>">
                                            <?php echo $systemStatus['database'] ? 'Online' : 'Offline'; ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="status-item mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>Server Status</span>
                                        <span class="badge badge-<?php echo $systemStatus['server'] ? 'success' : 'danger'; ?>">
                                            <?php echo $systemStatus['server'] ? 'Online' : 'Offline'; ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="status-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>Backup Status</span>
                                        <span class="badge badge-<?php echo $systemStatus['backup'] ? 'success' : 'danger'; ?>">
                                            <?php echo $systemStatus['backup'] ? 'Up to Date' : 'Needs Update'; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-center">
                            <a href="status.php" class="text-primary">View Detailed Status</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<style>
/* Additional styles for dashboard */
.small-box {
    position: relative;
    display: block;
    margin-bottom: 20px;
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    border-radius: 0.25rem;
    background: #fff;
    color: #333;
}

.small-box > .inner {
    padding: 10px;
}

.small-box h3 {
    font-size: 2.2rem;
    font-weight: 700;
    margin: 0;
    white-space: nowrap;
    padding: 0;
}

.small-box p {
    font-size: 1rem;
    margin: 0;
}

.small-box .icon {
    color: rgba(0,0,0,.15);
    z-index: 0;
    position: absolute;
    right: 10px;
    top: 10px;
    font-size: 70px;
}

.small-box-footer {
    position: relative;
    text-align: center;
    padding: 3px 0;
    color: rgba(255,255,255,.8);
    display: block;
    z-index: 10;
    background: rgba(0,0,0,.1);
    text-decoration: none;
}

.bg-info {
    background-color: #17a2b8 !important;
    color: #fff !important;
}

.bg-success {
    background-color: #28a745 !important;
    color: #fff !important;
}

.bg-warning {
    background-color: #ffc107 !important;
    color: #1f2d3d !important;
}

.bg-danger {
    background-color: #dc3545 !important;
    color: #fff !important;
}

.card {
    position: relative;
    display: flex;
    flex-direction: column;
    min-width: 0;
    word-wrap: break-word;
    background-color: #fff;
    background-clip: border-box;
    border: 0 solid rgba(0,0,0,.125);
    border-radius: 0.25rem;
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    margin-bottom: 1rem;
}

.card-header {
    background-color: rgba(0,0,0,.03);
    border-bottom: 1px solid rgba(0,0,0,.125);
    padding: 0.75rem 1.25rem;
}

.card-body {
    flex: 1 1 auto;
    min-height: 1px;
    padding: 1.25rem;
}

.card-footer {
    padding: 0.75rem 1.25rem;
    background-color: rgba(0,0,0,.03);
    border-top: 1px solid rgba(0,0,0,.125);
}

.table {
    width: 100%;
    margin-bottom: 1rem;
    color: #212529;
    border-collapse: collapse;
}

.table th,
.table td {
    padding: 0.75rem;
    vertical-align: top;
    border-top: 1px solid #dee2e6;
}

.table thead th {
    vertical-align: bottom;
    border-bottom: 2px solid #dee2e6;
}

.badge {
    display: inline-block;
    padding: 0.25em 0.4em;
    font-size: 75%;
    font-weight: 700;
    line-height: 1;
    text-align: center;
    white-space: nowrap;
    vertical-align: baseline;
    border-radius: 0.25rem;
}

.badge-success {
    color: #fff;
    background-color: #28a745;
}

.badge-danger {
    color: #fff;
    background-color: #dc3545;
}

.badge-info {
    color: #fff;
    background-color: #17a2b8;
}

.status-item {
    padding: 10px;
    border-bottom: 1px solid #eee;
}

.status-item:last-child {
    border-bottom: none;
}

.img-circle {
    border-radius: 50%;
}
</style>

<?php
// Include footer if exists
if (file_exists('include/footer.php')) {
    require_once('include/footer.php');
}
?> 