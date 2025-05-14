<?php
require_once '../config/database.php';
require_once 'include/header.php';
require_once 'include/sidebar.php';

$db = getDB();

// Get lawyer ID from URL
$lawyer_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get lawyer details
$lawyer_query = "SELECT u.*, o.name as office_name,
    (SELECT GROUP_CONCAT(s.name SEPARATOR ', ') 
     FROM lawyer_specialties ls 
     JOIN specialties s ON ls.specialty_id = s.id 
     WHERE ls.lawyer_id = u.id) as specialties
    FROM users u 
    LEFT JOIN offices o ON u.organization_id = o.id 
    WHERE u.id = :id AND u.role = 'lawyer'";
$lawyer = $db->fetchOne($lawyer_query, [':id' => $lawyer_id]);

if (!$lawyer) {
    die("Lawyer not found");
}

// Initialize default values for statistics
$case_stats = [
    'total_cases' => 0,
    'active_cases' => 0,
    'closed_cases' => 0,
    'pending_cases' => 0,
    'avg_case_duration' => 0
];

// Get case statistics
try {
    $stats_query = "SELECT 
        COUNT(*) as total_cases,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_cases,
        SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed_cases,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_cases,
        AVG(CASE 
            WHEN status = 'closed' AND closed_date IS NOT NULL 
            THEN DATEDIFF(closed_date, created_at) 
            ELSE NULL 
        END) as avg_case_duration
        FROM cases 
        WHERE lawyer_id = :lawyer_id";
    $case_stats = $db->fetchOne($stats_query, [':lawyer_id' => $lawyer_id]);
} catch (Exception $e) {
    error_log("Error fetching case statistics: " . $e->getMessage());
}

// Get monthly case load
$monthly_cases = [];
try {
    $monthly_query = "SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as case_count
        FROM cases 
        WHERE lawyer_id = :lawyer_id 
        AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month ASC";
    $monthly_results = $db->fetchAll($monthly_query, [':lawyer_id' => $lawyer_id]);
    
    foreach ($monthly_results as $result) {
        $monthly_cases[$result['month']] = $result['case_count'];
    }
} catch (Exception $e) {
    error_log("Error fetching monthly cases: " . $e->getMessage());
}

// Get case types distribution
$case_types = [];
try {
    $types_query = "SELECT 
        type,
        COUNT(*) as count
        FROM cases 
        WHERE lawyer_id = :lawyer_id 
        GROUP BY type";
    $types_results = $db->fetchAll($types_query, [':lawyer_id' => $lawyer_id]);
    
    foreach ($types_results as $result) {
        $case_types[$result['type']] = $result['count'];
    }
} catch (Exception $e) {
    error_log("Error fetching case types: " . $e->getMessage());
}

// Get recent cases
$recent_cases = [];
try {
    $recent_query = "SELECT c.*, cl.name as client_name
        FROM cases c
        LEFT JOIN clients cl ON c.client_id = cl.id
        WHERE c.lawyer_id = :lawyer_id
        ORDER BY c.created_at DESC
        LIMIT 5";
    $recent_cases = $db->fetchAll($recent_query, [':lawyer_id' => $lawyer_id]);
} catch (Exception $e) {
    error_log("Error fetching recent cases: " . $e->getMessage());
}

// Get performance metrics
$performance = [
    'won_cases' => 0,
    'lost_cases' => 0,
    'success_rate' => 0
];

try {
    $performance_query = "SELECT 
        SUM(CASE WHEN outcome = 'won' THEN 1 ELSE 0 END) as won_cases,
        SUM(CASE WHEN outcome = 'lost' THEN 1 ELSE 0 END) as lost_cases
        FROM cases 
        WHERE lawyer_id = :lawyer_id 
        AND status = 'closed'";
    $performance_result = $db->fetchOne($performance_query, [':lawyer_id' => $lawyer_id]);
    
    if ($performance_result) {
        $performance['won_cases'] = $performance_result['won_cases'];
        $performance['lost_cases'] = $performance_result['lost_cases'];
        $total_closed = $performance['won_cases'] + $performance['lost_cases'];
        $performance['success_rate'] = $total_closed > 0 ? 
            round(($performance['won_cases'] / $total_closed) * 100) : 0;
    }
} catch (Exception $e) {
    error_log("Error fetching performance metrics: " . $e->getMessage());
}
?>

<style>
:root {
    --primary-green: #00572d;
    --secondary-green: #1f9345;
    --accent-yellow: #f3c300;
    --text-primary: #333333;
    --text-secondary: #666666;
    --background-light: #f8f9fa;
    --background-white: #ffffff;
    --border-color: #e0e0e0;
}

.content-wrapper {
    margin-left: 250px;
    padding: 20px;
    min-height: 100vh;
    background: var(--background-light);
}

.performance-card {
    background: var(--background-white);
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    padding: 25px;
    margin-bottom: 20px;
    border: 1px solid rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}

.performance-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px rgba(0,0,0,0.1);
}

.performance-card h3 {
    color: var(--text-primary);
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
    font-weight: 600;
}

.stat-card {
    background: var(--background-white);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    border: 1px solid rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.stat-card .icon {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    margin-bottom: 15px;
}

.stat-card .icon.cases { 
    background: rgba(0,87,45,0.1); 
    color: var(--primary-green); 
}

.stat-card .icon.active { 
    background: rgba(31,147,69,0.1); 
    color: var(--secondary-green); 
}

.stat-card .icon.closed { 
    background: rgba(243,195,0,0.1); 
    color: var(--accent-yellow); 
}

.stat-card .icon.pending { 
    background: rgba(108,117,125,0.1); 
    color: var(--text-secondary); 
}

.stat-card h4 {
    font-size: 24px;
    font-weight: 600;
    margin: 10px 0;
    color: var(--text-primary);
}

.stat-card p {
    color: var(--text-secondary);
    margin: 0;
    font-size: 14px;
}

.nav-tabs {
    border-bottom: 2px solid var(--border-color);
    margin-bottom: 20px;
}

.nav-tabs .nav-link {
    border: none;
    color: var(--text-secondary);
    padding: 12px 20px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.nav-tabs .nav-link:hover {
    color: var(--primary-green);
    border: none;
}

.nav-tabs .nav-link.active {
    color: var(--primary-green);
    border: none;
    border-bottom: 2px solid var(--primary-green);
    margin-bottom: -2px;
}

.case-item {
    padding: 15px;
    border-radius: 8px;
    background: var(--background-light);
    margin-bottom: 10px;
    transition: all 0.3s ease;
}

.case-item:hover {
    background: var(--background-white);
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.case-item h5 {
    margin: 0;
    font-size: 16px;
    color: var(--text-primary);
}

.case-item p {
    margin: 5px 0 0;
    font-size: 14px;
    color: var(--text-secondary);
}

.badge {
    padding: 5px 10px;
    border-radius: 20px;
    font-weight: 500;
    font-size: 12px;
}

.badge-success { background: rgba(31,147,69,0.1); color: var(--secondary-green); }
.badge-warning { background: rgba(243,195,0,0.1); color: var(--accent-yellow); }
.badge-danger { background: rgba(220,53,69,0.1); color: #dc3545; }
.badge-info { background: rgba(0,87,45,0.1); color: var(--primary-green); }

.progress {
    height: 8px;
    border-radius: 4px;
    background: var(--background-light);
    margin-top: 5px;
}

.progress-bar {
    background: linear-gradient(90deg, var(--primary-green), var(--secondary-green));
    border-radius: 4px;
}

.chart-container {
    position: relative;
    margin: 20px 0;
    height: 300px;
}

@media (max-width: 768px) {
    .content-wrapper {
        margin-left: 0;
        padding: 15px;
    }
    
    .stat-card {
        margin-bottom: 15px;
    }
}
</style>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Lawyer Performance</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Lawyer Info Card -->
            <div class="performance-card">
                <div class="row">
                    <div class="col-md-8">
                        <h2><?php echo htmlspecialchars($lawyer['full_name']); ?></h2>
                        <p class="text-muted">
                            <i class="fas fa-building mr-2"></i><?php echo htmlspecialchars($lawyer['office_name'] ?? 'No Office Assigned'); ?>
                        </p>
                        <p class="text-muted">
                            <i class="fas fa-certificate mr-2"></i><?php echo htmlspecialchars($lawyer['specialties'] ?? 'No Specialties'); ?>
                        </p>
                    </div>
                    <div class="col-md-4 text-right">
                        <span class="badge <?php echo $lawyer['status'] == 'active' ? 'badge-success' : 'badge-danger'; ?>">
                            <?php echo ucfirst($lawyer['status']); ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card">
                        <div class="icon cases">
                            <i class="fas fa-gavel"></i>
                        </div>
                        <h4><?php echo $case_stats['total_cases']; ?></h4>
                        <p>Total Cases</p>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card">
                        <div class="icon active">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h4><?php echo $case_stats['active_cases']; ?></h4>
                        <p>Active Cases</p>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card">
                        <div class="icon closed">
                            <i class="fas fa-flag-checkered"></i>
                        </div>
                        <h4><?php echo $case_stats['closed_cases']; ?></h4>
                        <p>Closed Cases</p>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card">
                        <div class="icon pending">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h4><?php echo $case_stats['pending_cases']; ?></h4>
                        <p>Pending Cases</p>
                    </div>
                </div>
            </div>

            <!-- Performance Tabs -->
            <div class="performance-card">
                <ul class="nav nav-tabs" id="performanceTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="workload-tab" data-toggle="tab" href="#workload" role="tab">
                            <i class="fas fa-chart-line mr-2"></i>Workload
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="cases-tab" data-toggle="tab" href="#cases" role="tab">
                            <i class="fas fa-gavel mr-2"></i>Cases
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="performance-tab" data-toggle="tab" href="#performance" role="tab">
                            <i class="fas fa-trophy mr-2"></i>Performance
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="recent-tab" data-toggle="tab" href="#recent" role="tab">
                            <i class="fas fa-history mr-2"></i>Recent Activity
                        </a>
                    </li>
                </ul>

                <div class="tab-content" id="performanceTabsContent">
                    <!-- Workload Tab -->
                    <div class="tab-pane fade show active" id="workload" role="tabpanel">
                        <div class="chart-container">
                            <canvas id="monthlyCasesChart"></canvas>
                        </div>
                        <div class="row mt-4">
                            <div class="col-md-4">
                                <div class="stat-card">
                                    <h5>Current Workload</h5>
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar" 
                                             style="width: <?php echo min(($case_stats['active_cases'] / 10) * 100, 100); ?>%">
                                        </div>
                                    </div>
                                    <small class="text-muted mt-2 d-block">
                                        <?php echo $case_stats['active_cases']; ?> active cases
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stat-card">
                                    <h5>Average Case Duration</h5>
                                    <h4><?php echo round($case_stats['avg_case_duration']); ?> days</h4>
                                    <small class="text-muted">For closed cases</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stat-card">
                                    <h5>Case Distribution</h5>
                                    <div class="progress mb-2">
                                        <div class="progress-bar bg-success" role="progressbar" 
                                             style="width: <?php echo $case_stats['total_cases'] > 0 ? 
                                             ($case_stats['active_cases'] / $case_stats['total_cases'] * 100) : 0; ?>%">
                                        </div>
                                    </div>
                                    <small class="text-muted">Active: <?php echo $case_stats['active_cases']; ?></small>
                                    <div class="progress mb-2">
                                        <div class="progress-bar bg-warning" role="progressbar" 
                                             style="width: <?php echo $case_stats['total_cases'] > 0 ? 
                                             ($case_stats['pending_cases'] / $case_stats['total_cases'] * 100) : 0; ?>%">
                                        </div>
                                    </div>
                                    <small class="text-muted">Pending: <?php echo $case_stats['pending_cases']; ?></small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cases Tab -->
                    <div class="tab-pane fade" id="cases" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="chart-container">
                                    <canvas id="caseTypesChart"></canvas>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h5>Case Type Distribution</h5>
                                <?php foreach ($case_types as $type => $count): ?>
                                <div class="case-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5><?php echo ucfirst($type); ?></h5>
                                        <span class="badge badge-info"><?php echo $count; ?> cases</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar" 
                                             style="width: <?php echo ($count / $case_stats['total_cases'] * 100); ?>%">
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Tab -->
                    <div class="tab-pane fade" id="performance" role="tabpanel">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="stat-card">
                                    <h5>Success Rate</h5>
                                    <h4><?php echo $performance['success_rate']; ?>%</h4>
                                    <small class="text-muted">Based on closed cases</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stat-card">
                                    <h5>Won Cases</h5>
                                    <h4><?php echo $performance['won_cases']; ?></h4>
                                    <small class="text-muted">Successfully closed</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stat-card">
                                    <h5>Lost Cases</h5>
                                    <h4><?php echo $performance['lost_cases']; ?></h4>
                                    <small class="text-muted">Unsuccessfully closed</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity Tab -->
                    <div class="tab-pane fade" id="recent" role="tabpanel">
                        <h5>Recent Cases</h5>
                        <?php foreach ($recent_cases as $case): ?>
                        <div class="case-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5><?php echo htmlspecialchars($case['title']); ?></h5>
                                <span class="badge badge-<?php 
                                    echo $case['status'] == 'active' ? 'success' : 
                                        ($case['status'] == 'pending' ? 'warning' : 'info'); 
                                ?>">
                                    <?php echo ucfirst($case['status']); ?>
                                </span>
                            </div>
                            <p>
                                <i class="fas fa-user mr-2"></i><?php echo htmlspecialchars($case['client_name']); ?>
                                <span class="ml-3">
                                    <i class="fas fa-calendar mr-2"></i>
                                    <?php echo date('M d, Y', strtotime($case['created_at'])); ?>
                                </span>
                            </p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Monthly Cases Chart
    const monthlyCtx = document.getElementById('monthlyCasesChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_keys($monthly_cases)); ?>,
            datasets: [{
                label: 'Cases',
                data: <?php echo json_encode(array_values($monthly_cases)); ?>,
                borderColor: '#00572d',
                backgroundColor: 'rgba(0,87,45,0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Monthly Case Load'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // Case Types Chart
    const typesCtx = document.getElementById('caseTypesChart').getContext('2d');
    new Chart(typesCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_keys($case_types)); ?>,
            datasets: [{
                data: <?php echo json_encode(array_values($case_types)); ?>,
                backgroundColor: [
                    '#00572d',
                    '#1f9345',
                    '#f3c300',
                    '#6c757d'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Case Types Distribution'
                }
            }
        }
    });
});
</script>

<?php require_once 'include/footer.php'; ?> 