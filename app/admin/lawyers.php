<?php
require_once '../config/database.php';
require_once 'include/header.php';
require_once 'include/sidebar.php';
require_once 'include/footer.php';

$db = getDB();

// Pagination settings
$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Get total records for pagination
$total_query = "SELECT COUNT(*) as total FROM users WHERE role = 'lawyer'";
$total_result = $db->fetchOne($total_query);
$total_records = $total_result['total'];
$total_pages = ceil($total_records / $records_per_page);

// Get lawyers with pagination and their details
$query = "SELECT 
    u.*, 
    o.name as office_name,
    (SELECT COUNT(*) FROM cases c WHERE c.lawyer_id = u.id) as total_cases,
    (SELECT COUNT(*) FROM cases c WHERE c.lawyer_id = u.id AND c.status = 'active') as active_cases,
    (SELECT GROUP_CONCAT(s.name SEPARATOR ',') FROM lawyer_specialties ls 
     JOIN specialties s ON ls.specialty_id = s.id 
     WHERE ls.lawyer_id = u.id) as specialties
    FROM users u 
    LEFT JOIN offices o ON u.organization_id = o.id 
    WHERE u.role = 'lawyer'
    ORDER BY u.id DESC 
    LIMIT :offset, :limit";

$params = [
    ':offset' => $offset,
    ':limit' => $records_per_page
];

$lawyers = $db->fetchAll($query, $params);

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total_lawyers,
    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_lawyers,
    (SELECT COUNT(*) FROM cases WHERE status = 'active') as total_active_cases,
    (SELECT COUNT(DISTINCT lawyer_id) FROM cases WHERE status = 'active') as lawyers_with_cases,
    (SELECT COUNT(*) FROM lawyer_specialties) as total_specialties
    FROM users 
    WHERE role = 'lawyer'";
$stats = $db->fetchOne($stats_query);

// Get offices for assignment
$offices = $db->fetchAll("SELECT id, name FROM offices WHERE status = 'ACTIVE'");

// Get specialties for assignment
$specialties = $db->fetchAll("SELECT id, name FROM specialties WHERE status = 'active'");
?>

<!-- Add the script section after all PHP includes -->
<script>
// Define all functions in the global scope
const lawyerFunctions = {
    viewPerformance: function(id) {
        window.location.href = `lawyer_performance.php?id=${id}`;
    },
    
    editLawyer: function(id) {
        window.location.href = `edit_lawyer.php?id=${id}`;
    },
    
    deleteLawyer: function(id) {
        $('#deleteModal').modal('show');
        $('#confirmDelete').data('lawyer-id', id);
    },
    
    getStatusBadge: function(status) {
        const badges = {
            'active': '<span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i>Active</span>',
            'inactive': '<span class="badge badge-danger"><i class="fas fa-times-circle mr-1"></i>Inactive</span>',
            'suspended': '<span class="badge badge-warning"><i class="fas fa-pause-circle mr-1"></i>Suspended</span>'
        };
        return badges[status] || `<span class="badge badge-info">${status}</span>`;
    },
    
    formatDate: function(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    },
    
    assignCase: function(id) {
        $('#lawyer_id').val(id);
        // Fetch available cases
        lawyerFunctions.loadAvailableCases();
    },
    
    // Function to load available cases
    loadAvailableCases: function() {
        $.ajax({
            url: 'ajax/get_available_cases.php',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const select = $('#case_id');
                    select.empty();
                    select.append('<option value="">Select a case</option>');
                    
                    response.cases.forEach(function(case_) {
                        select.append(`<option value="${case_.id}">${case_.title} - ${case_.client_name}</option>`);
                    });
                } else {
                    // Show error in a proper error panel
                    Swal.fire({
                        icon: 'error',
                        title: response.title || 'Error',
                        html: `
                            <div class="alert alert-danger">
                                <h5>${response.message}</h5>
                                ${response.details ? `<p class="mt-2">${response.details}</p>` : ''}
                            </div>
                        `,
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function(xhr, status, error) {
                // Show error in a proper error panel
                Swal.fire({
                    icon: 'error',
                    title: 'System Error',
                    html: `
                        <div class="alert alert-danger">
                            <h5>Failed to load cases</h5>
                            <p class="mt-2">Please try again later or contact system administrator if the problem persists.</p>
                        </div>
                    `,
                    confirmButtonText: 'OK'
                });
            }
        });
    }
};

// Make functions globally available
window.viewPerformance = lawyerFunctions.viewPerformance;
window.editLawyer = lawyerFunctions.editLawyer;
window.deleteLawyer = lawyerFunctions.deleteLawyer;
</script>

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

.stat-card {
    background: var(--background-white);
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    border: 1px solid rgba(0,0,0,0.05);
    margin-bottom: 20px;
    position: relative;
    overflow: hidden;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px rgba(0,0,0,0.1);
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(90deg, var(--primary-green), var(--secondary-green));
}

.stat-card .icon {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    transition: all 0.3s ease;
}

.stat-card:hover .icon {
    transform: scale(1.1);
}

.stat-card .icon.lawyers { 
    background: rgba(0,87,45,0.1); 
    color: var(--primary-green); 
}

.stat-card .icon.active { 
    background: rgba(31,147,69,0.1); 
    color: var(--secondary-green); 
}

.stat-card .icon.cases { 
    background: rgba(243,195,0,0.1); 
    color: var(--accent-yellow); 
}

.stat-card .icon.specialties { 
    background: rgba(0,87,45,0.1); 
    color: var(--primary-green); 
}

.stat-card h3 {
    font-size: 24px;
    font-weight: 600;
    margin: 5px 0;
    color: var(--text-primary);
}

.stat-card h6 {
    font-size: 14px;
    font-weight: 500;
    color: var(--text-secondary);
}

.stat-card small {
    font-size: 12px;
    display: block;
    margin-top: 5px;
}

.stat-card small i {
    margin-right: 4px;
}

.table-container {
    background: var(--background-white);
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    padding: 25px;
    margin-top: 20px;
    border: 1px solid rgba(0,0,0,0.05);
}

.table thead th {
    background: var(--background-light);
    color: var(--text-primary);
    font-weight: 600;
    border-bottom: 2px solid var(--border-color);
    padding: 15px;
    text-transform: uppercase;
    font-size: 13px;
    letter-spacing: 0.5px;
}

.table tbody td {
    padding: 15px;
    vertical-align: middle;
    border-bottom: 1px solid var(--border-color);
    font-size: 14px;
}

.table tbody tr:hover {
    background-color: rgba(0,87,45,0.02);
}

.badge {
    padding: 0.5em 1em;
    border-radius: 20px;
    font-weight: 500;
}

.badge-success { background: rgba(31,147,69,0.1); color: var(--secondary-green); }
.badge-danger { background: rgba(220,53,69,0.1); color: #dc3545; }
.badge-warning { background: rgba(243,195,0,0.1); color: var(--accent-yellow); }
.badge-info { background: rgba(0,87,45,0.1); color: var(--primary-green); }

.search-box {
    background: var(--background-white);
    border-radius: 12px;
    padding: 15px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    margin-bottom: 25px;
    border: 1px solid rgba(0,0,0,0.05);
}

.search-box .form-control {
    border: none;
    padding: 12px 20px;
    font-size: 14px;
    background: var(--background-light);
    border-radius: 8px;
}

.search-box .btn-link {
    color: var(--primary-green);
    padding: 12px 20px;
}

.search-box .btn-link:hover {
    color: var(--secondary-green);
}

.btn-primary {
    background: linear-gradient(45deg, var(--primary-green), var(--secondary-green));
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background: linear-gradient(45deg, var(--secondary-green), var(--primary-green));
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.btn-group .btn {
    padding: 0.375rem 0.75rem;
}

.pagination {
    margin-top: 30px;
}

.pagination .page-link {
    color: var(--primary-green);
    padding: 8px 16px;
    border-radius: 8px;
    margin: 0 3px;
    border: 1px solid var(--border-color);
    transition: all 0.3s ease;
}

.pagination .page-item.active .page-link {
    background: linear-gradient(45deg, var(--primary-green), var(--secondary-green));
    border-color: transparent;
}

.pagination .page-link:hover {
    background: var(--background-light);
    color: var(--secondary-green);
}

.table-actions .btn {
    padding: 0.25rem 0.5rem;
    margin: 0 2px;
}

.specialty-tag {
    display: inline-block;
    padding: 4px 8px;
    margin: 2px;
    background: rgba(0,87,45,0.1);
    color: var(--primary-green);
    border-radius: 4px;
    font-size: 12px;
}

.workload-indicator {
    width: 100%;
    height: 6px;
    background: #eee;
    border-radius: 3px;
    overflow: hidden;
    margin-top: 5px;
}

.workload-bar {
    height: 100%;
    background: linear-gradient(90deg, var(--primary-green), var(--secondary-green));
    border-radius: 3px;
    transition: width 0.3s ease;
}

.workload-text {
    font-size: 12px;
    color: var(--text-secondary);
    margin-top: 2px;
}

@media (max-width: 768px) {
    .content-wrapper {
        margin-left: 0;
        padding: 15px;
    }
    
    .table-responsive {
        border: 0;
    }
}

.info-card {
    background: #fff;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    border: 1px solid rgba(0,0,0,0.05);
}

.info-card h6 {
    font-size: 14px;
    font-weight: 600;
    color: #666;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    font-size: 13px;
    margin-bottom: 5px;
    display: block;
}

.form-control-static {
    font-size: 15px;
    color: #333;
    margin: 0;
    padding: 8px 0;
}

.form-control-static a {
    text-decoration: none;
}

.form-control-static a:hover {
    text-decoration: underline;
}

.modal-header {
    border-bottom: 1px solid #eee;
}

.modal-footer {
    border-top: 1px solid #eee;
}

.modal-title i {
    font-size: 20px;
}

.btn i {
    font-size: 14px;
}
</style>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Lawyers Management</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Add Lawyer Button -->
            <div class="row mb-4">
                <div class="col-12">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addLawyerModal">
                        <i class="fas fa-plus"></i> Add New Lawyer
                    </button>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card p-3">
                        <div class="d-flex align-items-center">
                            <div class="icon lawyers mr-3">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 text-muted">Total Lawyers</h6>
                                <h3 class="mb-0"><?php echo $stats['total_lawyers']; ?></h3>
                                <small class="text-success">
                                    <i class="fas fa-chart-line"></i> All Attorneys
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card p-3">
                        <div class="d-flex align-items-center">
                            <div class="icon active mr-3">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 text-muted">Active Lawyers</h6>
                                <h3 class="mb-0"><?php echo $stats['active_lawyers']; ?></h3>
                                <small class="text-success">
                                    <i class="fas fa-percentage"></i> 
                                    <?php echo round(($stats['active_lawyers'] / $stats['total_lawyers']) * 100); ?>% Active
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card p-3">
                        <div class="d-flex align-items-center">
                            <div class="icon cases mr-3">
                                <i class="fas fa-gavel"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 text-muted">Active Cases</h6>
                                <h3 class="mb-0"><?php echo $stats['total_active_cases']; ?></h3>
                                <small class="text-info">
                                    <i class="fas fa-users"></i> 
                                    <?php echo $stats['lawyers_with_cases']; ?> Lawyers Assigned
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card p-3">
                        <div class="d-flex align-items-center">
                            <div class="icon specialties mr-3">
                                <i class="fas fa-certificate"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 text-muted">Specialties</h6>
                                <h3 class="mb-0"><?php echo $stats['total_specialties']; ?></h3>
                                <small class="text-info">
                                    <i class="fas fa-tags"></i> Total Specializations
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search Box -->
            <div class="search-box">
                <div class="input-group">
                    <input type="text" class="form-control border-0" name="table_search" placeholder="Search lawyers...">
                    <div class="input-group-append">
                        <button class="btn btn-link text-muted">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Lawyers Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">All Lawyers</h3>
                        </div>
                        <div class="card-body">
                            <table id="lawyersTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Office</th>
                                        <th>Specialties</th>
                                        <th>Cases</th>
                                        <th>Workload</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($lawyers as $lawyer): ?>
                                    <tr>
                                        <td><?php echo $lawyer['id']; ?></td>
                                        <td><?php echo htmlspecialchars($lawyer['full_name'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($lawyer['email'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($lawyer['phone'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($lawyer['office_name'] ?? 'Not Assigned'); ?></td>
                                        <td>
                                            <?php 
                                            if ($lawyer['specialties']) {
                                                $specialties = explode(',', $lawyer['specialties']);
                                                foreach ($specialties as $specialty) {
                                                    echo '<span class="specialty-tag">' . htmlspecialchars($specialty) . '</span>';
                                                }
                                            } else {
                                                echo '<span class="text-muted">No specialties</span>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-info"><?php echo $lawyer['total_cases']; ?> Total</span>
                                            <span class="badge badge-success"><?php echo $lawyer['active_cases']; ?> Active</span>
                                        </td>
                                        <td>
                                            <?php 
                                            $workload = ($lawyer['active_cases'] / 10) * 100; // Assuming 10 cases is 100% workload
                                            $workload = min($workload, 100);
                                            ?>
                                            <div class="workload-indicator">
                                                <div class="workload-bar" style="width: <?php echo $workload; ?>%"></div>
                                            </div>
                                            <div class="workload-text">
                                                <?php echo round($workload); ?>% Load
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($lawyer['status'] == 'active'): ?>
                                                <span class="badge badge-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-info" onclick="lawyerFunctions.viewPerformance(<?php echo $lawyer['id']; ?>)">
                                                    <i class="fas fa-chart-line"></i> Performance
                                                </button>
                                                <button type="button" class="btn btn-sm btn-primary" onclick="lawyerFunctions.editLawyer(<?php echo $lawyer['id']; ?>)">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <button type="button" class="btn btn-sm btn-success" onclick="lawyerFunctions.assignCase(<?php echo $lawyer['id']; ?>)">
                                                    <i class="fas fa-gavel"></i> Assign Case
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </section>
</div>

<!-- View Lawyer Modal -->
<div class="modal fade" id="viewLawyerModal" tabindex="-1" role="dialog" aria-labelledby="viewLawyerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="viewLawyerModalLabel">
                    <i class="fas fa-user-tie text-primary mr-2"></i>
                    Lawyer Details
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-card mb-4">
                            <h6 class="text-muted mb-3">Basic Information</h6>
                            <div class="form-group">
                                <label class="font-weight-bold text-primary">Full Name</label>
                                <p id="view-name" class="form-control-static"></p>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold text-primary">Status</label>
                                <p id="view-status" class="form-control-static"></p>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold text-primary">Office</label>
                                <p id="view-office" class="form-control-static"></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-card mb-4">
                            <h6 class="text-muted mb-3">Contact Information</h6>
                            <div class="form-group">
                                <label class="font-weight-bold text-primary">Email</label>
                                <p id="view-email" class="form-control-static">
                                    <a href="#" id="view-email-link" class="text-primary">
                                        <i class="fas fa-envelope mr-1"></i>
                                        <span></span>
                                    </a>
                                </p>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold text-primary">Phone</label>
                                <p id="view-phone" class="form-control-static">
                                    <a href="#" id="view-phone-link" class="text-primary">
                                        <i class="fas fa-phone mr-1"></i>
                                        <span></span>
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="info-card">
                            <h6 class="text-muted mb-3">Case Information</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-primary">Total Cases</label>
                                        <p id="view-total-cases" class="form-control-static"></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-primary">Active Cases</label>
                                        <p id="view-active-cases" class="form-control-static"></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-primary">Workload</label>
                                        <p id="view-workload" class="form-control-static"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="info-card">
                            <h6 class="text-muted mb-3">Specialties</h6>
                            <div id="view-specialties" class="form-control-static"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Close
                </button>
                <button type="button" class="btn btn-primary" id="view-to-edit">
                    <i class="fas fa-edit mr-1"></i> Edit Lawyer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add Lawyer Modal -->
<div class="modal fade" id="addLawyerModal" tabindex="-1" role="dialog" aria-labelledby="addLawyerModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addLawyerModalLabel">Add New Lawyer</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addLawyerForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="full_name">Full Name</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="form-group">
                                <label for="organization_id">Office</label>
                                <select class="form-control" id="organization_id" name="organization_id" required>
                                    <option value="">Select Office</option>
                                    <?php foreach ($offices as $office): ?>
                                        <option value="<?php echo $office['id']; ?>"><?php echo htmlspecialchars($office['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="specialties">Specialties</label>
                                <select class="form-control" id="specialties" name="specialties[]" multiple required>
                                    <?php foreach ($specialties as $specialty): ?>
                                        <option value="<?php echo $specialty['id']; ?>"><?php echo htmlspecialchars($specialty['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Lawyer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Lawyer Modal -->
<div class="modal fade" id="editLawyerModal" tabindex="-1" role="dialog" aria-labelledby="editLawyerModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editLawyerModalLabel">Edit Lawyer</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editLawyerForm">
                <div class="modal-body">
                    <input type="hidden" id="edit-id" name="id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit-full_name">Full Name</label>
                                <input type="text" class="form-control" id="edit-full_name" name="full_name" required>
                            </div>
                            <div class="form-group">
                                <label for="edit-email">Email</label>
                                <input type="email" class="form-control" id="edit-email" name="email" required>
                            </div>
                            <div class="form-group">
                                <label for="edit-phone">Phone</label>
                                <input type="tel" class="form-control" id="edit-phone" name="phone" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit-username">Username</label>
                                <input type="text" class="form-control" id="edit-username" name="username" required>
                            </div>
                            <div class="form-group">
                                <label for="edit-password">New Password (leave blank to keep current)</label>
                                <input type="password" class="form-control" id="edit-password" name="password">
                            </div>
                            <div class="form-group">
                                <label for="edit-organization_id">Office</label>
                                <select class="form-control" id="edit-organization_id" name="organization_id" required>
                                    <option value="">Select Office</option>
                                    <?php foreach ($offices as $office): ?>
                                        <option value="<?php echo $office['id']; ?>"><?php echo htmlspecialchars($office['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit-specialties">Specialties</label>
                                <select class="form-control" id="edit-specialties" name="specialties[]" multiple required>
                                    <?php foreach ($specialties as $specialty): ?>
                                        <option value="<?php echo $specialty['id']; ?>"><?php echo htmlspecialchars($specialty['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit-status">Status</label>
                                <select class="form-control" id="edit-status" name="status" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this lawyer?</p>
                <p class="text-danger"><small>This action cannot be undone.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Case Assignment Modal -->
<div class="modal fade" id="assignCaseModal" tabindex="-1" role="dialog" aria-labelledby="assignCaseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignCaseModalLabel">Assign Case to Lawyer</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="assignCaseForm">
                <div class="modal-body">
                    <input type="hidden" id="lawyer_id" name="lawyer_id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="case_id">Select Case</label>
                                <select class="form-control" id="case_id" name="case_id" required>
                                    <option value="">Select a case</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="priority">Priority Level</label>
                                <select class="form-control" id="priority" name="priority" required>
                                    <option value="LOW">Low</option>
                                    <option value="MEDIUM">Medium</option>
                                    <option value="HIGH">High</option>
                                    <option value="URGENT">Urgent</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="notes">Assignment Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="4"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="deadline">Expected Completion Date</label>
                                <input type="date" class="form-control" id="deadline" name="deadline" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign Case</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#lawyersTable').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true
    });

    // Handle Delete Confirmation
    $('#confirmDelete').click(function() {
        const lawyerId = $(this).data('lawyer-id');
        if (lawyerId) {
            $.ajax({
                url: 'ajax/delete_lawyer.php',
                type: 'POST',
                data: { id: lawyerId },
                success: function(response) {
                    if (response.success) {
                        $('#deleteModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Lawyer has been deleted successfully.',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message || 'Error deleting lawyer'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'An error occurred while deleting the lawyer'
                    });
                }
            });
        }
    });

    // Handle Add Lawyer Form Submit
    $('#addLawyerForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'ajax/add_lawyer.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    $('#addLawyerModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Lawyer has been added successfully.',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'Error adding lawyer'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'An error occurred while adding the lawyer'
                });
            }
        });
    });

    // Handle Edit Lawyer Form Submit
    $('#editLawyerForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'ajax/update_lawyer.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    $('#editLawyerModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Lawyer has been updated successfully.',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'Error updating lawyer'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'An error occurred while updating the lawyer'
                });
            }
        });
    });

    // Handle Case Assignment Form Submit
    $('#assignCaseForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'ajax/assign_case.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    $('#assignCaseModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Case has been assigned successfully.',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'Error assigning case'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'An error occurred while assigning the case'
                });
            }
        });
    });
});
</script>

<?php require_once 'include/footer.php'; ?> 