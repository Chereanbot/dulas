<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<style>
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: var(--sidebar-width);
    height: 100vh;
    background: var(--primary-color);
    color: white;
    z-index: 1040;
    display: flex;
    flex-direction: column;
}

.sidebar-header {
    height: var(--header-height);
    padding: 0 1.5rem;
    display: flex;
    align-items: center;
    background: rgba(0, 0, 0, 0.1);
}

.sidebar-brand {
    color: white;
    font-size: 1.25rem;
    font-weight: 600;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.sidebar-brand:hover {
    color: var(--accent-color);
}

.sidebar-brand img {
    height: 32px;
    width: auto;
}

.sidebar-menu {
    flex: 1;
    overflow-y: auto;
    padding: 1rem 0;
}

.nav-section {
    padding: 0.75rem 1.5rem 0.5rem;
    font-size: 0.75rem;
    text-transform: uppercase;
    color: var(--accent-color);
    font-weight: 600;
    letter-spacing: 0.5px;
}

.nav-item {
    padding: 0.25rem 1rem;
}

.nav-link {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    border-radius: 0.5rem;
    transition: all 0.3s ease;
}

.nav-link i {
    width: 1.5rem;
    font-size: 1.1rem;
    margin-right: 0.75rem;
    text-align: center;
}

.nav-link:hover {
    background: var(--secondary-color);
    color: white;
}

.nav-link.active {
    background: var(--accent-color);
    color: var(--primary-color);
}

.nav-link.active i {
    color: var(--primary-color);
}

.sidebar-footer {
    padding: 1rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar-footer .nav-link {
    color: rgba(255, 255, 255, 0.8);
}

.sidebar-footer .nav-link:hover {
    background: var(--secondary-color);
    color: white;
}

/* New styles for submenu */
.nav-submenu {
    padding-left: 2.5rem;
    display: none;
}

.nav-submenu.show {
    display: block;
}

.nav-submenu .nav-link {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
}

.nav-link[data-toggle="collapse"] {
    position: relative;
}

.nav-link[data-toggle="collapse"]::after {
    content: '\f107';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    position: absolute;
    right: 1rem;
    transition: transform 0.3s;
}

.nav-link[data-toggle="collapse"][aria-expanded="true"]::after {
    transform: rotate(180deg);
}
</style>

<aside class="sidebar">
    <div class="sidebar-header">
        <a href="/dulas/app/superadmin/dashboard.php" class="sidebar-brand">
            <img src="../assets/images/logo.png" alt="DULAS Logo">
            <span>DULAS</span>
        </a>
    </div>
    
    <div class="sidebar-menu">
        <div class="nav-section">Main Menu</div>
        <div class="nav-item">
            <a href="/dulas/app/superadmin/dashboard.php" class="nav-link <?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
        </div>

        <div class="nav-section">User Management</div>
        <div class="nav-item">
            <a href="/dulas/app/superadmin/users.php" class="nav-link <?php echo $currentPage === 'users.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i>
                <span>Users</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="/dulas/app/superadmin/user_sessions.php" class="nav-link <?php echo $currentPage === 'user_sessions.php' ? 'active' : ''; ?>">
                <i class="fas fa-sign-in-alt"></i>
                <span>User Sessions</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="/dulas/app/superadmin/user_permissions.php" class="nav-link <?php echo $currentPage === 'user_permissions.php' ? 'active' : ''; ?>">
                <i class="fas fa-user-shield"></i>
                <span>Permissions</span>
            </a>
        </div>

        <div class="nav-section">Security</div>
        <div class="nav-item">
            <a href="/dulas/app/superadmin/security.php" class="nav-link <?php echo $currentPage === 'security.php' ? 'active' : ''; ?>">
                <i class="fas fa-shield-alt"></i>
                <span>Security Settings</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="/dulas/app/superadmin/data_encryption.php" class="nav-link <?php echo $currentPage === 'data_encryption.php' ? 'active' : ''; ?>">
                <i class="fas fa-lock"></i>
                <span>Data Encryption</span>
            </a>
        </div>

        <div class="nav-section">System</div>
        <div class="nav-item">
            <a href="/dulas/app/superadmin/status.php" class="nav-link <?php echo $currentPage === 'status.php' ? 'active' : ''; ?>">
                <i class="fas fa-server"></i>
                <span>System Status</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="/dulas/app/superadmin/backup.php" class="nav-link <?php echo $currentPage === 'backup.php' ? 'active' : ''; ?>">
                <i class="fas fa-database"></i>
                <span>Backup & Recovery</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="/dulas/app/superadmin/logs.php" class="nav-link <?php echo $currentPage === 'logs.php' ? 'active' : ''; ?>">
                <i class="fas fa-history"></i>
                <span>System Logs</span>
            </a>
        </div>

        <div class="nav-section">Reports</div>
        <div class="nav-item">
            <a href="/dulas/app/superadmin/activity_reports.php" class="nav-link <?php echo $currentPage === 'activity_reports.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i>
                <span>Activity Reports</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="/dulas/app/superadmin/system_reports.php" class="nav-link <?php echo $currentPage === 'system_reports.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar"></i>
                <span>System Reports</span>
            </a>
        </div>

        <div class="nav-section">Settings</div>
        <div class="nav-item">
            <a href="/dulas/app/superadmin/settings.php" class="nav-link <?php echo $currentPage === 'settings.php' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i>
                <span>System Settings</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="/dulas/app/superadmin/profile.php" class="nav-link <?php echo $currentPage === 'profile.php' ? 'active' : ''; ?>">
                <i class="fas fa-user-circle"></i>
                <span>My Profile</span>
            </a>
        </div>
    </div>

    <div class="sidebar-footer">
        <a href="/dulas/logout.php" class="nav-link">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</aside>

<div class="md:ml-64">
    <!-- Main content goes here -->
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle submenu toggles
    const submenuToggles = document.querySelectorAll('[data-toggle="collapse"]');
    submenuToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('data-target'));
            if (target) {
                target.classList.toggle('show');
                this.setAttribute('aria-expanded', 
                    this.getAttribute('aria-expanded') === 'true' ? 'false' : 'true'
                );
            }
        });
    });
});
</script>

<?php
?>
