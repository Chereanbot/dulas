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
            --primary-green: #00572d;
            --secondary-green: #1f9345;
            --accent-yellow: #f3c300;
            --text-primary: #333333;
            --text-secondary: #666666;
            --background: #f4f4f4;
            --border-color: #e0e0e0;
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--background);
            color: var(--text-primary);
        }

        .header {
            position: fixed;
            top: 0;
            right: 0;
            left: var(--sidebar-width);
            height: var(--header-height);
            background: white;
            box-shadow: var(--shadow-sm);
            z-index: 1030;
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            transition: var(--transition);
        }

        @media (max-width: 768px) {
            .header {
                left: 0;
            }
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .menu-toggle {
            background: none;
            border: none;
            color: var(--primary-green);
            font-size: 1.25rem;
            padding: 0.5rem;
            cursor: pointer;
            transition: var(--transition);
        }

        .menu-toggle:hover {
            color: var(--secondary-green);
            transform: scale(1.1);
        }

        .header-title {
            color: var(--primary-green);
            font-weight: 700;
            font-size: 1.25rem;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .header-title i {
            color: var(--accent-yellow);
        }

        .header-right {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .session-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            background: rgba(31, 147, 69, 0.1);
            color: var(--secondary-green);
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .session-info:hover {
            background: rgba(31, 147, 69, 0.15);
        }

        .session-info i {
            font-size: 1rem;
        }

        .session-count {
            font-weight: 600;
        }

        .user-dropdown {
            position: relative;
        }

        .user-dropdown-toggle {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem;
            border-radius: 8px;
            cursor: pointer;
            transition: var(--transition);
        }

        .user-dropdown-toggle:hover {
            background: rgba(0, 87, 45, 0.05);
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary-green);
            transition: var(--transition);
        }

        .user-dropdown-toggle:hover .user-avatar {
            transform: scale(1.05);
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--text-primary);
        }

        .user-role {
            font-size: 0.8rem;
            color: var(--text-secondary);
        }

        .dropdown-menu {
            min-width: 220px;
            padding: 0.5rem;
            margin: 0.5rem 0 0;
            border: none;
            box-shadow: var(--shadow-md);
            border-radius: 12px;
            animation: dropdownFade 0.2s ease;
        }

        @keyframes dropdownFade {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dropdown-item {
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--text-primary);
            border-radius: 8px;
            transition: var(--transition);
        }

        .dropdown-item i {
            width: 1.25rem;
            text-align: center;
            color: var(--primary-green);
        }

        .dropdown-item:hover {
            background: rgba(0, 87, 45, 0.05);
            color: var(--primary-green);
        }

        .dropdown-divider {
            margin: 0.5rem 0;
            border-top: 1px solid var(--border-color);
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--accent-yellow);
            color: var(--text-primary);
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            border: 2px solid white;
        }

        @media (max-width: 576px) {
            .header {
                padding: 0 1rem;
            }

            .header-title {
                font-size: 1.1rem;
            }

            .session-info {
                display: none;
            }

            .user-info {
                display: none;
            }

            .user-dropdown-toggle {
                padding: 0.25rem;
            }

            .user-avatar {
                width: 32px;
                height: 32px;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-left">
            <button class="menu-toggle" data-drawer-toggle="sidebar">
                <i class="fas fa-bars"></i>
            </button>
            <h1 class="header-title">
                <i class="fas fa-gavel"></i>
                DUTSCA Admin Panel
            </h1>
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
                    <img src="/dulas/assets/images/profile/<?php echo htmlspecialchars($profileImage); ?>" 
                         alt="User Avatar" 
                         class="user-avatar">
                    <div class="user-info">
                        <span class="user-name"><?php echo htmlspecialchars($userName); ?></span>
                        <span class="user-role"><?php echo htmlspecialchars($userRole); ?></span>
                    </div>
                    <i class="fas fa-chevron-down"></i>
                </div>
                
                <div class="dropdown-menu dropdown-menu-right">
                    <a href="/dulas/app/superadmin/profile.php" class="dropdown-item">
                        <i class="fas fa-user"></i>
                        <span>My Profile</span>
                    </a>
                    <a href="/dulas/app/superadmin/user_sessions.php" class="dropdown-item">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>My Sessions</span>
                        <?php if ($activeSessions > 0): ?>
                        <span class="notification-badge"><?php echo $activeSessions; ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="/dulas/app/logout.php" class="dropdown-item text-danger">
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
        const header = document.querySelector('.header');
        
        menuButton?.addEventListener('click', function() {
            sidebar.classList.toggle('-translate-x-full');
            header.classList.toggle('sidebar-collapsed');
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

        // Handle responsive behavior
        function handleResponsive() {
            const width = window.innerWidth;
            if (width <= 768) {
                header.classList.add('sidebar-collapsed');
            } else {
                header.classList.remove('sidebar-collapsed');
            }
        }

        window.addEventListener('resize', handleResponsive);
        handleResponsive();
    });
    </script>
