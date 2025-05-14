<?php
require_once('include/header.php');
require_once('include/sidebar.php');
?>
<style>
:root {
    --primary-green: #00572d;
    --secondary-green: #1f9345;
    --accent-yellow: #f3c300;
    --text-primary: #333333;
    --background: #f4f4f4;
    --footer-dark: #1a1a1a;
}
.admin-dashboard-main {
    margin-left: 280px;
    margin-top: 60px;
    padding: 2rem 1.5rem 1.5rem 1.5rem;
    min-height: calc(100vh - 60px);
    background: var(--background);
}
.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}
.dashboard-title {
    color: var(--primary-green);
    font-weight: 700;
    font-size: 2rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.dashboard-title i {
    color: var(--accent-yellow);
}
.dashboard-cards {
    display: flex;
    flex-wrap: wrap;
    gap: 2rem;
    margin-bottom: 2.5rem;
}
.dashboard-card {
    flex: 1 1 220px;
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.07);
    padding: 2rem 1.5rem;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    min-width: 220px;
    transition: box-shadow 0.2s;
    border-left: 6px solid var(--primary-green);
}
.dashboard-card .card-icon {
    font-size: 2.2rem;
    margin-bottom: 0.5rem;
    color: var(--secondary-green);
}
.dashboard-card .card-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.2rem;
}
.dashboard-card .card-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary-green);
}
.dashboard-card .card-link {
    margin-top: 0.7rem;
    color: var(--accent-yellow);
    font-size: 0.95rem;
    text-decoration: none;
    font-weight: 500;
}
.dashboard-card .card-link:hover {
    text-decoration: underline;
}
.dashboard-section {
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.07);
    padding: 2rem 1.5rem;
    margin-bottom: 2rem;
}
.dashboard-section-title {
    color: var(--primary-green);
    font-weight: 600;
    font-size: 1.2rem;
    margin-bottom: 1.2rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.quick-actions {
    display: flex;
    gap: 1.5rem;
    flex-wrap: wrap;
}
.quick-action-btn {
    background: var(--secondary-green);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 0.8rem 1.5rem;
    font-size: 1rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.7rem;
    transition: background 0.2s, transform 0.2s;
    box-shadow: 0 1px 4px rgba(0,0,0,0.07);
    cursor: pointer;
}
.quick-action-btn:hover {
    background: var(--primary-green);
    transform: translateY(-2px) scale(1.03);
}
@media (max-width: 900px) {
    .dashboard-cards { flex-direction: column; gap: 1.2rem; }
    .admin-dashboard-main { margin-left: 0; }
}
</style>
<div class="admin-dashboard-main">
    <div class="dashboard-header">
        <div class="dashboard-title">
            <i class="fas fa-tachometer-alt"></i> Admin Dashboard
        </div>
        <div class="quick-actions">
            <button class="quick-action-btn" onclick="location.href='users.php'">
                <i class="fas fa-users"></i> Manage Users
            </button>
            <button class="quick-action-btn" onclick="location.href='cases.php'">
                <i class="fas fa-briefcase"></i> View Cases
            </button>
            <button class="quick-action-btn" onclick="location.href='tasks.php'">
                <i class="fas fa-tasks"></i> Tasks
            </button>
            <button class="quick-action-btn" onclick="location.href='billing.php'">
                <i class="fas fa-file-invoice-dollar"></i> Billing
            </button>
        </div>
    </div>
    <div class="dashboard-cards">
        <div class="dashboard-card">
            <div class="card-icon"><i class="fas fa-users"></i></div>
            <div class="card-title">Total Users</div>
            <div class="card-value" id="totalUsers">--</div>
            <a href="users.php" class="card-link">View all users</a>
        </div>
        <div class="dashboard-card">
            <div class="card-icon"><i class="fas fa-briefcase"></i></div>
            <div class="card-title">Active Cases</div>
            <div class="card-value" id="activeCases">--</div>
            <a href="cases.php" class="card-link">View cases</a>
        </div>
        <div class="dashboard-card">
            <div class="card-icon"><i class="fas fa-tasks"></i></div>
            <div class="card-title">Open Tasks</div>
            <div class="card-value" id="openTasks">--</div>
            <a href="tasks.php" class="card-link">View tasks</a>
        </div>
        <div class="dashboard-card">
            <div class="card-icon"><i class="fas fa-file-invoice-dollar"></i></div>
            <div class="card-title">Pending Bills</div>
            <div class="card-value" id="pendingBills">--</div>
            <a href="billing.php" class="card-link">View billing</a>
        </div>
    </div>
    <div class="dashboard-section">
        <div class="dashboard-section-title"><i class="fas fa-history"></i> Recent Activity</div>
        <div id="recentActivity">
            <div style="color:#888;">Loading recent activity...</div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
// Example: Fetch dashboard stats and recent activity (replace with your AJAX endpoints)
document.addEventListener('DOMContentLoaded', function() {
    axios.get('ajax/dashboard_stats.php').then(function(res) {
        if(res.data && res.data.success) {
            document.getElementById('totalUsers').textContent = res.data.data.total_users;
            document.getElementById('activeCases').textContent = res.data.data.active_cases;
            document.getElementById('openTasks').textContent = res.data.data.open_tasks;
            document.getElementById('pendingBills').textContent = res.data.data.pending_bills;
        }
    });
    axios.get('ajax/recent_activity.php').then(function(res) {
        if(res.data && res.data.success && res.data.data.length > 0) {
            let html = '<ul style="list-style:none;padding:0;margin:0;">';
            res.data.data.forEach(function(item) {
                html += `<li style="margin-bottom:1.1rem;padding-bottom:0.8rem;border-bottom:1px solid #eee;">
                    <span style="color:var(--primary-green);font-weight:600;">${item.user}</span>
                    <span style="color:#888;">${item.action}</span>
                    <span style="float:right;color:#aaa;font-size:0.95em;">${item.time}</span>
                </li>`;
            });
            html += '</ul>';
            document.getElementById('recentActivity').innerHTML = html;
        } else {
            document.getElementById('recentActivity').innerHTML = '<div style="color:#888;">No recent activity.</div>';
        }
    });
});
</script>
<?php require_once('include/footer.php'); ?> 