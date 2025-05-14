<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$currentPage = basename($_SERVER['PHP_SELF']);
// Get user info for avatar
$profileImage = isset($_SESSION['profile_image']) ? $_SESSION['profile_image'] : 'default-avatar.png';
$fullName = isset($_SESSION['name']) ? $_SESSION['name'] : 'Superadmin';
?>

<style>
:root {
    --primary-green: #00572d;
    --secondary-green: #1f9345;
    --accent-yellow: #f3c300;
    --text-primary: #333333;
    --background: #f4f4f4;
    --sidebar-width: 280px;
    --sidebar-collapsed-width: 80px;
    --transition-speed: 0.3s;
}

/* Sidebar Container */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: var(--sidebar-width);
    height: 100vh;
    background: linear-gradient(180deg, var(--primary-green) 0%, #004225 100%);
    color: #fff;
    z-index: 1040;
    display: flex;
    flex-direction: column;
    box-shadow: 4px 0 15px rgba(0,0,0,0.1);
    transition: all var(--transition-speed) ease;
}

/* Sidebar Header */
.sidebar-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem;
    background: rgba(0,0,0,0.1);
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.superadmin-avatar {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    object-fit: cover;
    border: 2px solid var(--accent-yellow);
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: all var(--transition-speed) ease;
}

.superadmin-info {
    flex: 1;
    min-width: 0;
}

.superadmin-name {
    font-weight: 700;
    font-size: 1.1rem;
    color: var(--accent-yellow);
    margin-bottom: 0.2rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.superadmin-role {
    font-size: 0.85rem;
    color: rgba(255,255,255,0.7);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.superadmin-role::before {
    content: '';
    display: inline-block;
    width: 8px;
    height: 8px;
    background: var(--accent-yellow);
    border-radius: 50%;
}

/* Sidebar Menu */
.sidebar-menu {
    flex: 1;
    padding: 1.5rem 1rem;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: var(--accent-yellow) transparent;
}

.sidebar-menu::-webkit-scrollbar {
    width: 5px;
}

.sidebar-menu::-webkit-scrollbar-track {
    background: transparent;
}

.sidebar-menu::-webkit-scrollbar-thumb {
    background: var(--accent-yellow);
    border-radius: 10px;
}

.menu-section {
    margin-bottom: 1.5rem;
}

.menu-section-title {
    font-size: 0.75rem;
    text-transform: uppercase;
    color: rgba(255,255,255,0.5);
    padding: 0 1rem;
    margin-bottom: 0.5rem;
    letter-spacing: 1px;
}

.menu-link {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.8rem 1rem;
    color: rgba(255,255,255,0.8);
    text-decoration: none;
    border-radius: 10px;
    font-size: 0.95rem;
    font-weight: 500;
    transition: all var(--transition-speed) ease;
    position: relative;
    overflow: hidden;
}

.menu-link::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    width: 3px;
    background: var(--accent-yellow);
    transform: scaleY(0);
    transition: transform var(--transition-speed) ease;
}

.menu-link:hover {
    background: rgba(255,255,255,0.1);
    color: #fff;
    transform: translateX(5px);
}

.menu-link.active {
    background: var(--secondary-green);
    color: #fff;
}

.menu-link.active::before {
    transform: scaleY(1);
}

.menu-link .icon {
    font-size: 1.2rem;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all var(--transition-speed) ease;
}

.menu-link:hover .icon {
    transform: scale(1.1);
}

.menu-sub {
    margin-left: 2.5rem;
    margin-top: 0.3rem;
    position: relative;
}

.menu-sub::before {
    content: '';
    position: absolute;
    left: 12px;
    top: 0;
    bottom: 0;
    width: 1px;
    background: rgba(255,255,255,0.1);
}

.menu-sub .menu-link {
    font-size: 0.9rem;
    padding: 0.6rem 1rem;
    color: rgba(255,255,255,0.7);
    background: none;
}

.menu-sub .menu-link:hover {
    background: rgba(255,255,255,0.05);
    color: var(--accent-yellow);
}

.menu-sub .menu-link.active {
    background: var(--accent-yellow);
    color: var(--primary-green);
}

/* Sidebar Footer */
.sidebar-footer {
    padding: 1rem;
    background: rgba(0,0,0,0.1);
    border-top: 1px solid rgba(255,255,255,0.1);
}

.sidebar-footer .menu-link {
    color: #fff;
    font-weight: 600;
    background: rgba(255,255,255,0.1);
    border-radius: 8px;
    padding: 0.8rem 1rem;
    display: flex;
    align-items: center;
    gap: 0.8rem;
    transition: all var(--transition-speed) ease;
}

.sidebar-footer .menu-link:hover {
    background: #c0392b;
    color: #fff;
    transform: translateY(-2px);
}

/* Responsive Design */
@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
    }
    
    .sidebar.show {
        transform: translateX(0);
    }
}
</style>

<aside class="sidebar">
    <div class="sidebar-header">
        <img src="/dutsca/assets/images/profile/<?php echo htmlspecialchars($profileImage); ?>" class="superadmin-avatar" alt="Superadmin Avatar">
        <div class="superadmin-info">
            <div class="superadmin-name"><?php echo htmlspecialchars($fullName); ?></div>
            <div class="superadmin-role">Superadmin</div>
        </div>
    </div>
    
    <nav class="sidebar-menu">
        <div class="menu-section">
            <div class="menu-section-title">Main</div>
            <a href="dashboard.php" class="menu-link<?php echo $currentPage === 'dashboard.php' ? ' active' : ''; ?>">
                <span class="icon">🏠</span> Dashboard
            </a>
        </div>

        <div class="menu-section">
            <div class="menu-section-title">User Management</div>
            <div class="menu-sub">
                <a href="add_user.php" class="menu-link<?php echo $currentPage === 'add_user.php' ? ' active' : ''; ?>">
                    <span class="icon">➕</span> Add New User
                </a>
                <a href="users.php" class="menu-link<?php echo $currentPage === 'users.php' ? ' active' : ''; ?>">
                    <span class="icon">📋</span> All Users List
                </a>
                <a href="role_assignment.php" class="menu-link<?php echo $currentPage === 'role_assignment.php' ? ' active' : ''; ?>">
                    <span class="icon">🧑‍⚖️</span> Role Management
                </a>
            </div>
        </div>

        <div class="menu-section">
            <div class="menu-section-title">System</div>
            <a href="settings.php" class="menu-link<?php echo $currentPage === 'settings.php' ? ' active' : ''; ?>">
                <span class="icon">⚙️</span> System Settings
            </a>
            <a href="backup.php" class="menu-link<?php echo $currentPage === 'backup.php' ? ' active' : ''; ?>">
                <span class="icon">💾</span> Backup & Restore
            </a>
            <a href="security_logs.php" class="menu-link<?php echo $currentPage === 'security_logs.php' ? ' active' : ''; ?>">
                <span class="icon">🔒</span> Security Logs
            </a>
        </div>

        <div class="menu-section">
            <div class="menu-section-title">Reports & Management</div>
            <a href="global_reports.php" class="menu-link<?php echo $currentPage === 'global_reports.php' ? ' active' : ''; ?>">
                <span class="icon">📊</span> Global Reports
            </a>
            <a href="announcements.php" class="menu-link<?php echo $currentPage === 'announcements.php' ? ' active' : ''; ?>">
                <span class="icon">💬</span> Announcements
            </a>
            <a href="legal_centers.php" class="menu-link<?php echo $currentPage === 'legal_centers.php' ? ' active' : ''; ?>">
                <span class="icon">🏛️</span> Legal Centers
            </a>
            <a href="system_config.php" class="menu-link<?php echo $currentPage === 'system_config.php' ? ' active' : ''; ?>">
                <span class="icon">🛠️</span> System Config
            </a>
        </div>
    </nav>

    <div class="sidebar-footer">
        <a href="/dulas/app/superadmin/logout.php" class="menu-link">
            <span class="icon">🚪</span> Logout
        </a>
    </div>
</aside>

<div class="md:ml-64">
    <!-- Main content goes here -->
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add hover effect to menu items
    const menuLinks = document.querySelectorAll('.menu-link');
    menuLinks.forEach(link => {
        link.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(5px)';
        });
        link.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
        });
    });

    // Mobile sidebar toggle
    const toggleSidebar = document.createElement('button');
    toggleSidebar.className = 'sidebar-toggle';
    toggleSidebar.innerHTML = '☰';
    document.body.appendChild(toggleSidebar);

    toggleSidebar.addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('show');
    });
});
</script>

<?php
?>
