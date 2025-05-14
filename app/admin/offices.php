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
$total_query = "SELECT COUNT(*) as total FROM offices";
$total_result = $db->fetchOne($total_query);
$total_records = $total_result['total'];
$total_pages = ceil($total_records / $records_per_page);

// Get offices with pagination
$query = "SELECT o.* 
          FROM offices o 
          ORDER BY o.id DESC 
          LIMIT :offset, :limit";

$params = [
    ':offset' => $offset,
    ':limit' => $records_per_page
];

$offices = $db->fetchAll($query, $params);

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total_offices,
    SUM(CASE WHEN status = 'ACTIVE' THEN 1 ELSE 0 END) as active_offices,
    COUNT(DISTINCT email) as unique_emails,
    COUNT(DISTINCT phone) as unique_phones
    FROM offices";
$stats = $db->fetchOne($stats_query);

// Get potential managers (users with lawyer role)
$managers = $db->fetchAll("SELECT id, full_name FROM users WHERE role = 'lawyer'");
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

.stat-card .icon.offices { 
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
                    <h1 class="m-0">Offices Management</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Add Office Button -->
            <div class="row mb-4">
                <div class="col-12">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addOfficeModal">
                        <i class="fas fa-plus"></i> Add New Office
                    </button>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card p-3">
                        <div class="d-flex align-items-center">
                            <div class="icon offices mr-3">
                                <i class="fas fa-building"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 text-muted">Total Offices</h6>
                                <h3 class="mb-0"><?php echo $stats['total_offices']; ?></h3>
                                <small class="text-success">
                                    <i class="fas fa-chart-line"></i> All Locations
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
                                <h6 class="mb-0 text-muted">Active Offices</h6>
                                <h3 class="mb-0"><?php echo $stats['active_offices']; ?></h3>
                                <small class="text-success">
                                    <i class="fas fa-percentage"></i> 
                                    <?php echo round(($stats['active_offices'] / $stats['total_offices']) * 100); ?>% Active
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
                    <input type="text" class="form-control border-0" name="table_search" placeholder="Search offices...">
                    <div class="input-group-append">
                        <button class="btn btn-link text-muted">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Offices Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">All Offices</h3>
                        </div>
                        <div class="card-body">
                            <table id="officesTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Address</th>
                                        <th>Phone</th>
                                        <th>Email</th>
                                        <th>Manager</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($offices as $office): ?>
                                    <tr>
                                        <td><?php echo $office['id']; ?></td>
                                        <td><?php echo htmlspecialchars($office['name']); ?></td>
                                        <td><?php echo htmlspecialchars($office['address']); ?></td>
                                        <td><?php echo htmlspecialchars($office['phone']); ?></td>
                                        <td><?php echo htmlspecialchars($office['email']); ?></td>
                                        <td><?php echo htmlspecialchars($office['manager_name'] ?? 'Not Assigned'); ?></td>
                                        <td>
                                            <?php if ($office['status'] == 'ACTIVE'): ?>
                                                <span class="badge badge-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($office['created_at'])); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info" onclick="viewOffice(<?php echo $office['id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-primary" onclick="editOffice(<?php echo $office['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteOffice(<?php echo $office['id']; ?>)">
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

<!-- View Office Modal -->
<div class="modal fade" id="viewOfficeModal" tabindex="-1" role="dialog" aria-labelledby="viewOfficeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="viewOfficeModalLabel">
                    <i class="fas fa-building text-primary mr-2"></i>
                    Office Details
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
                                <label class="font-weight-bold text-primary">Office Name</label>
                                <p id="view-name" class="form-control-static"></p>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold text-primary">Status</label>
                                <p id="view-status" class="form-control-static"></p>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold text-primary">Address</label>
                                <p id="view-address" class="form-control-static"></p>
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
                                        <label class="font-weight-bold text-primary">Office ID</label>
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
                    <i class="fas fa-edit mr-1"></i> Edit Office
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Office Modal -->
<div class="modal fade" id="editOfficeModal" tabindex="-1" role="dialog" aria-labelledby="editOfficeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editOfficeModalLabel">Edit Office</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editOfficeForm">
                <div class="modal-body">
                    <input type="hidden" id="edit-office-id" name="id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit-name">Office Name</label>
                                <input type="text" class="form-control" id="edit-name" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="edit-address">Address</label>
                                <textarea class="form-control" id="edit-address" name="address" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="edit-phone">Phone</label>
                                <input type="tel" class="form-control" id="edit-phone" name="phone" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit-email">Email</label>
                                <input type="email" class="form-control" id="edit-email" name="email" required>
                            </div>
                            <div class="form-group">
                                <label for="edit-manager">Manager</label>
                                <select class="form-control" id="edit-manager" name="manager_id">
                                    <option value="">Select Manager</option>
                                    <?php
                                    foreach ($managers as $manager) {
                                        echo "<option value='{$manager['id']}'>{$manager['full_name']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit-status">Status</label>
                                <select class="form-control" id="edit-status" name="status" required>
                                    <option value="ACTIVE">Active</option>
                                    <option value="INACTIVE">Inactive</option>
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
                <p>Are you sure you want to delete this office?</p>
                <p class="text-danger"><small>This action cannot be undone.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Office Modal -->
<div class="modal fade" id="addOfficeModal" tabindex="-1" role="dialog" aria-labelledby="addOfficeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addOfficeModalLabel">Add New Office</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addOfficeForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Office Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="location">Location</label>
                        <input type="text" class="form-control" id="location" name="location" required>
                    </div>
                    <div class="form-group">
                        <label for="type">Type</label>
                        <select class="form-control" id="type" name="type" required>
                            <option value="BRANCH">Branch</option>
                            <option value="HEADQUARTERS">Headquarters</option>
                            <option value="FIELD_OFFICE">Field Office</option>
                            <option value="SPECIALIZED">Specialized</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="capacity">Capacity</label>
                        <input type="number" class="form-control" id="capacity" name="capacity" value="0">
                    </div>
                    <div class="form-group">
                        <label for="contact_email">Contact Email</label>
                        <input type="email" class="form-control" id="contact_email" name="contact_email">
                    </div>
                    <div class="form-group">
                        <label for="contact_phone">Contact Phone</label>
                        <input type="tel" class="form-control" id="contact_phone" name="contact_phone">
                    </div>
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Office</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Define utility functions at the top level
function getStatusBadge(status) {
    const badges = {
        'ACTIVE': '<span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i>Active</span>',
        'INACTIVE': '<span class="badge badge-danger"><i class="fas fa-times-circle mr-1"></i>Inactive</span>',
        'SUSPENDED': '<span class="badge badge-warning"><i class="fas fa-pause-circle mr-1"></i>Suspended</span>',
        'CLOSED': '<span class="badge badge-secondary"><i class="fas fa-times-circle mr-1"></i>Closed</span>'
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
    let currentOfficeId = null;

    // View Office
    function viewOffice(id) {
        $.get('ajax/get_office.php', { id: id }, function(response) {
            if (response.success) {
                const office = response.data;
                
                // Basic Information
                $('#view-name').text(office.name);
                $('#view-status').html(getStatusBadge(office.status));
                $('#view-address').text(office.address);
                
                // Contact Information
                $('#view-email-link span').text(office.email);
                $('#view-email-link').attr('href', 'mailto:' + office.email);
                $('#view-phone-link span').text(office.phone);
                $('#view-phone-link').attr('href', 'tel:' + office.phone);
                
                // Additional Information
                $('#view-created').text(formatDate(office.created_at));
                $('#view-updated').text(formatDate(office.updated_at));
                $('#view-id').text(office.id);
                
                // Show the modal
                $('#viewOfficeModal').modal('show');
            } else {
                alert('Error loading office data: ' + response.message);
            }
        });
    }

    // Make viewOffice function globally available
    window.viewOffice = viewOffice;

    // View to Edit transition
    $('#view-to-edit').click(function() {
        $('#viewOfficeModal').modal('hide');
        const officeId = $('#view-id').text();
        editOffice(officeId);
    });

    // Edit Office
    function editOffice(id) {
        currentOfficeId = id;
        
        // Fetch office data
        $.ajax({
            url: 'ajax/get_office.php',
            type: 'GET',
            data: { id: id },
            success: function(response) {
                if (response.success) {
                    const office = response.data;
                    $('#edit-office-id').val(office.id);
                    $('#edit-name').val(office.name);
                    $('#edit-address').val(office.address);
                    $('#edit-phone').val(office.phone);
                    $('#edit-email').val(office.email);
                    $('#edit-manager').val(office.manager_id);
                    $('#edit-status').val(office.status);
                    $('#editOfficeModal').modal('show');
                } else {
                    alert('Error loading office data: ' + response.message);
                }
            },
            error: function() {
                alert('Error occurred while loading office data');
            }
        });
    }

    // Make editOffice function globally available
    window.editOffice = editOffice;

    // Delete Office
    function deleteOffice(id) {
        currentOfficeId = id;
        $('#deleteModal').modal('show');
    }

    // Make deleteOffice function globally available
    window.deleteOffice = deleteOffice;

    // Handle Delete Confirmation
    $('#confirmDelete').click(function() {
        if (currentOfficeId) {
            $.ajax({
                url: 'ajax/delete_office.php',
                type: 'POST',
                data: { id: currentOfficeId },
                success: function(response) {
                    if (response.success) {
                        $('#deleteModal').modal('hide');
                        location.reload();
                    } else {
                        alert('Error deleting office: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error occurred while deleting office');
                }
            });
        }
    });

    // Search functionality
    $('input[name="table_search"]').on('keyup', function() {
        let value = $(this).val().toLowerCase();
        $("table tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    // Initialize DataTable
    $('#officesTable').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true
    });

    // Handle Add Office Form Submit
    $('#addOfficeForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'ajax/add_office.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    $('#addOfficeModal').modal('hide');
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