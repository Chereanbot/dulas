<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<style>
:root {
    --primary-color: #00572d;
    --secondary-color: #1f9345;
    --accent-color: #f3c300;
    --text-color: #333333;
    --background-color: #ffffff;
    --footer-color: #1a1a1a;
}

.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: var(--sidebar-width);
    height: 100vh;
    background: var(--primary-color);
    color: var(--background-color);
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
    color: var(--background-color);
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
    color: var(--background-color);
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
    background: var(--footer-color);
}

.sidebar-footer .nav-link {
    color: rgba(255, 255, 255, 0.8);
}

.sidebar-footer .nav-link:hover {
    background: var(--secondary-color);
    color: var(--background-color);
}

/* New styles for submenu */
.nav-item.has-submenu {
    position: relative;
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

.nav-submenu {
    padding-left: 2.5rem;
    display: none;
    background: rgba(0, 0, 0, 0.1);
}

.nav-submenu.show {
    display: block;
}

.nav-submenu .nav-link {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
}

.nav-submenu .nav-link:hover {
    background: var(--secondary-color);
}

.nav-submenu .nav-link.active {
    background: var(--accent-color);
    color: var(--primary-color);
}
</style>

<aside class="sidebar">
    <div class="sidebar-header">
        <a href="/dulas/app/admin/dashboard.php" class="sidebar-brand">
            <img src="../assets/images/logo.png" alt="DULAS Logo">
            <span>DULAS</span>
        </a>
    </div>
    
    <div class="sidebar-menu">
        <div class="nav-section">Main Menu</div>
        <div class="nav-item">
            <a href="/dulas/app/admin/dashboard.php" class="nav-link <?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
        </div>

        <div class="nav-section">User Management</div>
        <div class="nav-item">
            <a href="/dulas/app/admin/users.php" class="nav-link <?php echo $currentPage === 'users.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i>
                <span>Users</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="/dulas/app/admin/messages.php" class="nav-link <?php echo $currentPage === 'messages.php' ? 'active' : ''; ?>">
                <i class="fas fa-envelope"></i>
                <span>Messages</span>
            </a>
        </div>
        

        <div class="nav-section">Lawyer Management</div>
        <div class="nav-item">
            <a href="/dulas/app/admin/lawyers.php" class="nav-link <?php echo $currentPage === 'lawyers.php' ? 'active' : ''; ?>">
                <i class="fas fa-user-tie"></i>
                <span>Lawyers</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="/dulas/app/admin/lawyer_performance.php" class="nav-link <?php echo $currentPage === 'lawyer_performance.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i>
                <span>Performance</span>
            </a>
        </div>

        <div class="nav-section">Case Management</div>
        <div class="nav-item has-submenu">
            <a href="#caseSubmenu" class="nav-link" data-toggle="collapse" role="button" aria-expanded="false" aria-controls="caseSubmenu">
                <i class="fas fa-gavel"></i>
                <span>Cases</span>
            </a>
            <div class="nav-submenu collapse" id="caseSubmenu">
                <a href="/dulas/app/admin/cases/active.php" class="nav-link <?php echo $currentPage === 'active.php' ? 'active' : ''; ?>">
                    <i class="fas fa-file-alt"></i>
                    <span>Active Cases</span>
                </a>
                <a href="/dulas/app/admin/cases/assignments.php" class="nav-link <?php echo $currentPage === 'assignments.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user-plus"></i>
                    <span>Case Assignment</span>
                </a>
                <a href="/dulas/app/admin/cases/priority.php" class="nav-link <?php echo $currentPage === 'priority.php' ? 'active' : ''; ?>">
                    <i class="fas fa-flag"></i>
                    <span>Priority</span>
                </a>
            </div>
        </div>

        <div class="nav-section">Office Management</div>
        <div class="nav-item">
            <a href="/dulas/app/admin/offices.php" class="nav-link <?php echo $currentPage === 'offices.php' ? 'active' : ''; ?>">
                <i class="fas fa-building"></i>
                <span>Offices</span>
            </a>
        </div>
        <div class="nav-section">Coordinator Management</div>
        <div class="nav-item">
            <a href="/dulas/app/admin/coordinators.php" class="nav-link <?php echo $currentPage === 'coordinators.php' ? 'active' : ''; ?>">
                <i class="fas fa-building"></i>
                <span>Coordinators</span>
            </a>
        </div>

        <div class="nav-section">Client Services</div>
        <div class="nav-item">
            <a href="/dulas/app/admin/service_requests.php" class="nav-link <?php echo $currentPage === 'service_requests.php' ? 'active' : ''; ?>">
                <i class="fas fa-hand-holding-usd"></i>
                <span>Service Requests</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="/dulas/app/admin/packages.php" class="nav-link <?php echo $currentPage === 'packages.php' ? 'active' : ''; ?>">
                <i class="fas fa-box"></i>
                <span>Service Packages</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="/dulas/app/admin/fees.php" class="nav-link <?php echo $currentPage === 'fees.php' ? 'active' : ''; ?>">
                <i class="fas fa-money-bill-wave"></i>
                <span>Fee Structure</span>
            </a>
        </div>

        <div class="nav-section">Reports</div>
        <div class="nav-item">
            <a href="/dulas/app/admin/custom_reports.php" class="nav-link <?php echo $currentPage === 'custom_reports.php' ? 'active' : ''; ?>">
                <i class="fas fa-file-alt"></i>
                <span>Custom Reports</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="/dulas/app/admin/statistics.php" class="nav-link <?php echo $currentPage === 'statistics.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar"></i>
                <span>Statistics</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="/dulas/app/admin/financial_reports.php" class="nav-link <?php echo $currentPage === 'financial_reports.php' ? 'active' : ''; ?>">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Financial Reports</span>
            </a>
        </div>

        <div class="nav-section">Settings</div>
        <div class="nav-item">
            <a href="/dulas/app/admin/settings.php" class="nav-link <?php echo $currentPage === 'settings.php' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i>
                <span>System Settings</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="/dulas/app/admin/profile.php" class="nav-link <?php echo $currentPage === 'profile.php' ? 'active' : ''; ?>">
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
            const target = document.querySelector(this.getAttribute('href'));
            
            // Close other open submenus
            submenuToggles.forEach(otherToggle => {
                if (otherToggle !== toggle) {
                    const otherTarget = document.querySelector(otherToggle.getAttribute('href'));
                    if (otherTarget.classList.contains('show')) {
                        otherTarget.classList.remove('show');
                        otherToggle.setAttribute('aria-expanded', 'false');
                    }
                }
            });

            // Toggle current submenu
            if (target) {
                target.classList.toggle('show');
                this.setAttribute('aria-expanded', 
                    this.getAttribute('aria-expanded') === 'true' ? 'false' : 'true'
                );
            }
        });
    });

    // Keep submenu open if current page is in it
    const currentPage = '<?php echo $currentPage; ?>';
    const activeSubmenu = document.querySelector(`.nav-submenu a[href*="${currentPage}"]`);
    if (activeSubmenu) {
        const submenu = activeSubmenu.closest('.nav-submenu');
        const toggle = document.querySelector(`[href="#${submenu.id}"]`);
        if (submenu && toggle) {
            submenu.classList.add('show');
            toggle.setAttribute('aria-expanded', 'true');
        }
    }
});
</script>

<?php
?>
