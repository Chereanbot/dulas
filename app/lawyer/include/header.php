<?php
// Check if session is already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include maintenance check


// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /dulas/index.php');
    exit();
}

// Get user information
require_once(__DIR__ . '/../../config/database.php');
$db = getDB();

try {
    $user = $db->fetchOne(
        "SELECT u.*, d.name as department_name 
         FROM users u 
         LEFT JOIN departments d ON u.department_id = d.id 
         WHERE u.id = ?", 
        [$_SESSION['user_id']]
    );
    
    // Get active sessions count
    $activeSessions = $db->fetchOne(
        "SELECT COUNT(*) as count 
         FROM user_sessions 
         WHERE user_id = ? AND last_activity > DATE_SUB(NOW(), INTERVAL 30 MINUTE)",
        [$_SESSION['user_id']]
    )['count'] ?? 0;
    
    } catch (Exception $e) {
    error_log("Error fetching user data: " . $e->getMessage());
    $user = null;
    $activeSessions = 0;
}

// After fetching $user and before outputting the avatar and user info:
$profileImage = 'default-avatar.png';
$userName = 'Unknown User';
$userRole = 'Unknown Role';

if ($user) {
    $profileImage = !empty($user['profile_image']) ? $user['profile_image'] : 'default-avatar.png';
    $userName = !empty($user['full_name']) ? $user['full_name'] : 'Unknown User';
    $userRole = !empty($user['role']) ? $user['role'] : 'Unknown Role';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DUTSCA Admin Panel</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/dutsca/assets/images/favicon.png">
    
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="/dutsca/assets/css/admin.css">
    
    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>

    <style>
        :root {
            --header-height: 60px;
        --sidebar-width: 250px;
        --primary-color: #2c3e50;
        --secondary-color: #34495e;
        --accent-color: #3498db;
        --text-color: #2c3e50;
        --light-text: #95a5a6;
        --border-color: #e0e0e0;
        --success-color: #2ecc71;
        --warning-color: #f1c40f;
        --danger-color: #e74c3c;
        }

        body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f8f9fa;
        color: var(--text-color);
    }

    .header {
            position: fixed;
            top: 0;
        right: 0;
            left: var(--sidebar-width);
            height: var(--header-height);
        background: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        z-index: 1030;
            display: flex;
            align-items: center;
        padding: 0 1.5rem;
        }

    .header-left {
            display: flex;
            align-items: center;
        gap: 1rem;
    }

    .header-right {
            margin-left: auto;
            display: flex;
            align-items: center;
        gap: 1rem;
    }

    .user-dropdown {
        position: relative;
        }

    .user-dropdown-toggle {
            display: flex;
            align-items: center;
        gap: 0.5rem;
        padding: 0.5rem;
        border-radius: 0.5rem;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .user-dropdown-toggle:hover {
        background-color: #f8f9fa;
    }

    .user-avatar {
            width: 32px;
            height: 32px;
        border-radius: 50%;
            object-fit: cover;
        }

    .user-info {
        display: flex;
        flex-direction: column;
    }

    .user-name {
        font-weight: 600;
        font-size: 0.9rem;
        color: var(--text-color);
    }

    .user-role {
        font-size: 0.8rem;
        color: var(--light-text);
    }

    .dropdown-menu {
        min-width: 200px;
        padding: 0.5rem 0;
        margin: 0;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        border-radius: 0.5rem;
    }

    .dropdown-item {
        padding: 0.5rem 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--text-color);
    }

    .dropdown-item i {
        width: 1rem;
        text-align: center;
    }

    .dropdown-item:hover {
        background-color: #f8f9fa;
    }

    .dropdown-divider {
        margin: 0.5rem 0;
        border-top: 1px solid var(--border-color);
    }

    .notification-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background-color: var(--danger-color);
        color: white;
        border-radius: 50%;
        width: 18px;
        height: 18px;
        font-size: 0.7rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .session-info {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem;
        border-radius: 0.5rem;
        background-color: #f8f9fa;
        font-size: 0.9rem;
    }

    .session-info i {
        color: var(--accent-color);
    }

    .session-count {
        font-weight: 600;
        color: var(--accent-color);
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-left">
            <button class="btn btn-link" data-drawer-toggle="sidebar">
                <i class="fas fa-bars"></i>
            </button>
            <h1 class="h4 mb-0">DUTSCA Admin Panel</h1>
        </div>
        
        <div class="header-right">
            <?php if ($activeSessions > 0): ?>
            <div class="session-info">
                <i class="fas fa-sign-in-alt"></i>
                <span>Active Sessions: <span class="session-count"><?php echo $activeSessions; ?></span></span>
            </div>
                    <?php endif; ?>
            
            <div class="user-dropdown">
                <div class="user-dropdown-toggle" data-toggle="dropdown">
                    <img src="/dutsca/assets/images/profile/<?php echo htmlspecialchars($profileImage); ?>" 
                         alt="User Avatar" 
                         class="user-avatar">
                    <div class="user-info">
                        <span class="user-name"><?php echo htmlspecialchars($userName); ?></span>
                        <span class="user-role"><?php echo htmlspecialchars($userRole); ?></span>
                    </div>
                    <i class="fas fa-chevron-down ml-2"></i>
                </div>
                
                <div class="dropdown-menu dropdown-menu-right">
                    <a href="/dutsca/superadmin/profile.php" class="dropdown-item">
                        <i class="fas fa-user"></i>
                        <span>My Profile</span>
                    </a>
                    <a href="/dutsca/superadmin/user_sessions.php" class="dropdown-item">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>My Sessions</span>
                        <?php if ($activeSessions > 0): ?>
                        <span class="notification-badge"><?php echo $activeSessions; ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="/dutsca/logout.php" class="dropdown-item text-danger">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle sidebar toggle
        const menuButton = document.querySelector('[data-drawer-toggle="sidebar"]');
        const sidebar = document.querySelector('aside');
        
        menuButton?.addEventListener('click', function() {
            sidebar.classList.toggle('-translate-x-full');
        });

        // Handle dropdown menus
        const dropdownToggles = document.querySelectorAll('[data-toggle="dropdown"]');
        dropdownToggles.forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                const dropdown = this.nextElementSibling;
                dropdown.classList.toggle('show');
            });
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.user-dropdown')) {
                document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                    menu.classList.remove('show');
                });
            }
        });
    });
    </script>
