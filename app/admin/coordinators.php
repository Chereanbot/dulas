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
$total_query = "SELECT COUNT(*) as total FROM users WHERE role = 'paralegal'";
$total_result = $db->fetchOne($total_query);
$total_records = $total_result['total'];
$total_pages = ceil($total_records / $records_per_page);

// Get coordinators (paralegals) with pagination
$query = "SELECT u.*, o.name as office_name 
          FROM users u 
          LEFT JOIN offices o ON u.organization_id = o.id 
          WHERE u.role = 'paralegal'
          ORDER BY u.id DESC 
          LIMIT :offset, :limit";

$params = [
    ':offset' => $offset,
    ':limit' => $records_per_page
];

$coordinators = $db->fetchAll($query, $params);

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total_coordinators,
    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_coordinators,
    COUNT(DISTINCT email) as unique_emails,
    COUNT(DISTINCT phone) as unique_phones
    FROM users 
    WHERE role = 'paralegal'";
$stats = $db->fetchOne($stats_query);

// Get offices for assignment
$offices = $db->fetchAll("SELECT id, name FROM offices WHERE status = 'ACTIVE'");
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

.stat-card .icon.coordinators { 
    background: rgba(0,87,45,0.1); 
    color: var(--primary-green); 
}

.stat-card .icon.active { 
    background: rgba(31,147,69,0.1); 
    color: var(--secondary-green); 
}

.stat-card .icon.managed { 
    background: rgba(243,195,0,0.1); 
    color: var(--accent-yellow); 
}

.stat-card .icon.managers { 
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
                    <h1 class="m-0">Coordinators Management</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Add Coordinator Button -->
            <div class="row mb-4">
                <div class="col-12">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addCoordinatorModal">
                        <i class="fas fa-plus"></i> Add New Coordinator
                    </button>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card p-3">
                        <div class="d-flex align-items-center">
                            <div class="icon coordinators mr-3">
                                <i class="fas fa-users"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 text-muted">Total Coordinators</h6>
                                <h3 class="mb-0"><?php echo $stats['total_coordinators']; ?></h3>
                                <small class="text-success">
                                    <i class="fas fa-chart-line"></i> All Paralegals
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
                                <h6 class="mb-0 text-muted">Active Coordinators</h6>
                                <h3 class="mb-0"><?php echo $stats['active_coordinators']; ?></h3>
                                <small class="text-success">
                                    <i class="fas fa-percentage"></i> 
                                    <?php echo round(($stats['active_coordinators'] / $stats['total_coordinators']) * 100); ?>% Active
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card p-3">
                        <div class="d-flex align-items-center">
                            <div class="icon managed mr-3">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 text-muted">Unique Emails</h6>
                                <h3 class="mb-0"><?php echo $stats['unique_emails']; ?></h3>
                                <small class="text-info">
                                    <i class="fas fa-check"></i> Verified Contacts
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card p-3">
                        <div class="d-flex align-items-center">
                            <div class="icon managers mr-3">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 text-muted">Unique Phones</h6>
                                <h3 class="mb-0"><?php echo $stats['unique_phones']; ?></h3>
                                <small class="text-info">
                                    <i class="fas fa-check"></i> Contact Numbers
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search Box -->
            <div class="search-box">
                <div class="input-group">
                    <input type="text" class="form-control border-0" name="table_search" placeholder="Search coordinators...">
                    <div class="input-group-append">
                        <button class="btn btn-link text-muted">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Coordinators Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">All Coordinators</h3>
                        </div>
                        <div class="card-body">
                            <table id="coordinatorsTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Office</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($coordinators as $coordinator): ?>
                                    <tr>
                                        <td><?php echo $coordinator['id']; ?></td>
                                        <td><?php echo htmlspecialchars($coordinator['full_name'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($coordinator['email'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($coordinator['phone'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($coordinator['office_name'] ?? 'Not Assigned'); ?></td>
                                        <td>
                                            <?php if ($coordinator['status'] == 'active'): ?>
                                                <span class="badge badge-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($coordinator['created_at'])); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info" onclick="viewCoordinator(<?php echo $coordinator['id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-primary" onclick="editCoordinator(<?php echo $coordinator['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteCoordinator(<?php echo $coordinator['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
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

<!-- View Coordinator Modal -->
<div class="modal fade" id="viewCoordinatorModal" tabindex="-1" role="dialog" aria-labelledby="viewCoordinatorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="viewCoordinatorModalLabel">
                    <i class="fas fa-user text-primary mr-2"></i>
                    Coordinator Details
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
                            <h6 class="text-muted mb-3">Additional Information</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-primary">Created Date</label>
                                        <p id="view-created" class="form-control-static"></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-primary">Last Updated</label>
                                        <p id="view-updated" class="form-control-static"></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-primary">Coordinator ID</label>
                                        <p id="view-id" class="form-control-static"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Close
                </button>
                <button type="button" class="btn btn-primary" id="view-to-edit">
                    <i class="fas fa-edit mr-1"></i> Edit Coordinator
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add Coordinator Modal -->
<div class="modal fade" id="addCoordinatorModal" tabindex="-1" role="dialog" aria-labelledby="addCoordinatorModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCoordinatorModalLabel">Add New Coordinator</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addCoordinatorForm">
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
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Coordinator</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Coordinator Modal -->
<div class="modal fade" id="editCoordinatorModal" tabindex="-1" role="dialog" aria-labelledby="editCoordinatorModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCoordinatorModalLabel">Edit Coordinator</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editCoordinatorForm">
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
                <p>Are you sure you want to delete this coordinator?</p>
                <p class="text-danger"><small>This action cannot be undone.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
// Define utility functions at the top level
function getStatusBadge(status) {
    const badges = {
        'active': '<span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i>Active</span>',
        'inactive': '<span class="badge badge-danger"><i class="fas fa-times-circle mr-1"></i>Inactive</span>',
        'suspended': '<span class="badge badge-warning"><i class="fas fa-pause-circle mr-1"></i>Suspended</span>'
    };
    return badges[status] || `<span class="badge badge-info">${status}</span>`;
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

$(document).ready(function() {
    let currentCoordinatorId = null;

    // View Coordinator
    function viewCoordinator(id) {
        $.get('ajax/get_coordinator.php', { id: id }, function(response) {
            if (response.success) {
                const coordinator = response.data;
                
                // Basic Information
                $('#view-name').text(coordinator.full_name);
                $('#view-status').html(getStatusBadge(coordinator.status));
                $('#view-office').text(coordinator.office_name || 'Not Assigned');
                
                // Contact Information
                $('#view-email-link span').text(coordinator.email);
                $('#view-email-link').attr('href', 'mailto:' + coordinator.email);
                $('#view-phone-link span').text(coordinator.phone);
                $('#view-phone-link').attr('href', 'tel:' + coordinator.phone);
                
                // Additional Information
                $('#view-created').text(formatDate(coordinator.created_at));
                $('#view-updated').text(formatDate(coordinator.updated_at));
                $('#view-id').text(coordinator.id);
                
                // Show the modal
                $('#viewCoordinatorModal').modal('show');
            } else {
                alert('Error loading coordinator data: ' + response.message);
            }
        });
    }

    // Make viewCoordinator function globally available
    window.viewCoordinator = viewCoordinator;

    // View to Edit transition
    $('#view-to-edit').click(function() {
        $('#viewCoordinatorModal').modal('hide');
        const coordinatorId = $('#view-id').text();
        editCoordinator(coordinatorId);
    });

    // Edit Coordinator
    function editCoordinator(id) {
        currentCoordinatorId = id;
        
        // Fetch coordinator data
        $.ajax({
            url: 'ajax/get_coordinator.php',
            type: 'GET',
            data: { id: id },
            success: function(response) {
                if (response.success) {
                    const coordinator = response.data;
                    $('#edit-id').val(coordinator.id);
                    $('#edit-full_name').val(coordinator.full_name);
                    $('#edit-email').val(coordinator.email);
                    $('#edit-phone').val(coordinator.phone);
                    $('#edit-username').val(coordinator.username);
                    $('#edit-organization_id').val(coordinator.organization_id);
                    $('#edit-status').val(coordinator.status);
                    $('#editCoordinatorModal').modal('show');
                } else {
                    alert('Error loading coordinator data: ' + response.message);
                }
            },
            error: function() {
                alert('Error occurred while loading coordinator data');
            }
        });
    }

    // Make editCoordinator function globally available
    window.editCoordinator = editCoordinator;

    // Delete Coordinator
    function deleteCoordinator(id) {
        currentCoordinatorId = id;
        $('#deleteModal').modal('show');
    }

    // Make deleteCoordinator function globally available
    window.deleteCoordinator = deleteCoordinator;

    // Handle Delete Confirmation
    $('#confirmDelete').click(function() {
        if (currentCoordinatorId) {
            $.ajax({
                url: 'ajax/delete_coordinator.php',
                type: 'POST',
                data: { id: currentCoordinatorId },
                success: function(response) {
                    if (response.success) {
                        $('#deleteModal').modal('hide');
                        location.reload();
                    } else {
                        alert('Error deleting coordinator: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error occurred while deleting coordinator');
                }
            });
        }
    });

    // Initialize DataTable
    $('#coordinatorsTable').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true
    });

    // Handle Add Coordinator Form Submit
    $('#addCoordinatorForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'ajax/add_coordinator.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    $('#addCoordinatorModal').modal('hide');
                    location.reload();
                } else {
                    alert(response.message);
                }
            }
        });
    });

    // Handle Edit Coordinator Form Submit
    $('#editCoordinatorForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'ajax/update_coordinator.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    $('#editCoordinatorModal').modal('hide');
                    location.reload();
                } else {
                    alert(response.message);
                }
            }
        });
    });
});
</script>

<?php require_once 'include/footer.php'; ?> 