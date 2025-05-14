<?php
// Include header
require_once('include/header.php');

// Include sidebar
require_once('include/sidebar.php');

// Database connection
require_once('../config/database.php');
$db = getDB();

// Handle session termination if requested
if (isset($_POST['terminate_session']) && isset($_POST['session_id'])) {
    try {
        $sessionId = $_POST['session_id'];
        $db->execute("DELETE FROM user_sessions WHERE id = ?", [$sessionId]);
        $_SESSION['success_message'] = "Session terminated successfully.";
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error terminating session: " . $e->getMessage();
    }
    header('Location: user_sessions.php');
    exit();
}

// Get all active sessions
try {
    $sessions = $db->fetchAll(
        "SELECT us.*, u.username, u.full_name, u.role, u.profile_image 
         FROM user_sessions us 
         JOIN users u ON us.user_id = u.id 
         ORDER BY us.last_activity DESC"
    );
} catch (Exception $e) {
    error_log("Error fetching sessions: " . $e->getMessage());
    $sessions = [];
}
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">User Sessions</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['success_message'];
                    unset($_SESSION['success_message']);
                    ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['error_message'];
                    unset($_SESSION['error_message']);
                    ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Active User Sessions</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="sessionsTable">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th>Device Info</th>
                                    <th>IP Address</th>
                                    <th>Last Activity</th>
                                    <th>Session Duration</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sessions as $session): ?>
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
                                        <?php 
                                        $deviceInfo = json_decode($session['device_info'], true);
                                        if ($deviceInfo): ?>
                                            <div>
                                                <strong>Browser:</strong> <?php echo htmlspecialchars($deviceInfo['browser'] ?? 'Unknown'); ?>
                                            </div>
                                            <div>
                                                <strong>OS:</strong> <?php echo htmlspecialchars($deviceInfo['os'] ?? 'Unknown'); ?>
                                            </div>
                                            <div>
                                                <strong>Device:</strong> <?php echo htmlspecialchars($deviceInfo['device'] ?? 'Unknown'); ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">No device info</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($session['ip_address']); ?></td>
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
                                    <td>
                                        <?php 
                                        $created = new DateTime($session['created_at']);
                                        $duration = $now->diff($created);
                                        echo $duration->format('%d days, %h hours, %i minutes');
                                        ?>
                                    </td>
                                    <td>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to terminate this session?');">
                                            <input type="hidden" name="session_id" value="<?php echo $session['id']; ?>">
                                            <button type="submit" name="terminate_session" class="btn btn-danger btn-sm">
                                                <i class="fas fa-times"></i> Terminate
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#sessionsTable').DataTable({
        "order": [[4, "desc"]], // Sort by last activity by default
        "pageLength": 25,
        "responsive": true
    });
});
</script>

<style>
.img-circle {
    border-radius: 50%;
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

.badge-info {
    color: #fff;
    background-color: #17a2b8;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    line-height: 1.5;
    border-radius: 0.2rem;
}

.alert {
    position: relative;
    padding: 0.75rem 1.25rem;
    margin-bottom: 1rem;
    border: 1px solid transparent;
    border-radius: 0.25rem;
}

.alert-success {
    color: #155724;
    background-color: #d4edda;
    border-color: #c3e6cb;
}

.alert-danger {
    color: #721c24;
    background-color: #f8d7da;
    border-color: #f5c6cb;
}

.alert-dismissible {
    padding-right: 4rem;
}

.alert-dismissible .close {
    position: absolute;
    top: 0;
    right: 0;
    padding: 0.75rem 1.25rem;
    color: inherit;
}
</style>

<?php
// Include footer if exists
if (file_exists('include/footer.php')) {
    require_once('include/footer.php');
}
?> 