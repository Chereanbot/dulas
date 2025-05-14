<?php
require_once '../config/database.php';
require_once 'include/header.php';
require_once 'include/sidebar.php';
$db = getDB();

// Add custom styles
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

body {
    background: var(--background);
    color: var(--text-primary);
}

.content-wrapper {
    margin-left: 250px; /* Sidebar width */
    padding: 20px;
    min-height: calc(100vh - 60px); /* Subtract header height */
    background: var(--background);
}

.content-header {
    background: #fff;
    padding: 15px 20px;
    margin-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.content-header h1 {
    color: var(--primary-green);
    margin: 0;
    font-size: 24px;
}

.stat-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s;
}

.stat-card:hover {
    transform: translateY(-2px);
}

.stat-card .icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.stat-card .icon.cases {
    background: var(--primary-green);
    color: #fff;
}

.stat-card .icon.active {
    background: var(--secondary-green);
    color: #fff;
}

.stat-card .icon.clients {
    background: var(--accent-yellow);
    color: var(--text-primary);
}

.stat-card .icon.pending {
    background: #f8f9fa;
    color: var(--text-primary);
}

.card {
    background: #fff;
    border: none;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.card-header {
    background: var(--primary-green);
    color: #fff;
    border-radius: 8px 8px 0 0 !important;
    padding: 15px 20px;
}

.card-header h3 {
    margin: 0;
    font-size: 18px;
}

.btn-primary {
    background: var(--primary-green);
    border: none;
}

.btn-primary:hover {
    background: var(--secondary-green);
}

.btn-success {
    background: var(--secondary-green);
    border: none;
}

.btn-success:hover {
    background: var(--primary-green);
}

.table thead th {
    background: var(--primary-green);
    color: #fff;
    border: none;
}

.table td {
    vertical-align: middle;
}

.badge {
    padding: 5px 10px;
    border-radius: 4px;
    font-weight: 500;
}

.badge-warning {
    background: var(--accent-yellow);
    color: var(--text-primary);
}

.badge-success {
    background: var(--secondary-green);
    color: #fff;
}

.badge-info {
    background: var(--primary-green);
    color: #fff;
}

.modal-header {
    background: var(--primary-green);
    color: #fff;
}

.modal-header .close {
    color: #fff;
    text-shadow: none;
}

.pagination .page-link {
    color: var(--primary-green);
}

.pagination .page-item.active .page-link {
    background: var(--primary-green);
    border-color: var(--primary-green);
}

/* DataTables customization */
.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: var(--primary-green) !important;
    color: #fff !important;
    border: none !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    background: var(--secondary-green) !important;
    color: #fff !important;
    border: none !important;
}

.dataTables_wrapper .dataTables_filter input {
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 5px 10px;
}

.dataTables_wrapper .dataTables_length select {
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 5px 10px;
}
</style>

<?php
// Pagination settings
$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Get total records for pagination
$total_query = "SELECT COUNT(*) as total FROM cases";
$total_result = $db->fetchOne($total_query);
$total_records = $total_result['total'];
$total_pages = ceil($total_records / $records_per_page);

// Get cases with pagination and their details
$query = "SELECT 
    c.*,
    cl.name as client_name,
    u.full_name as lawyer_name,
    p.full_name as paralegal_name,
    (SELECT COUNT(*) FROM case_documents WHERE case_id = c.id) as document_count,
    (SELECT COUNT(*) FROM case_notes WHERE case_id = c.id) as notes_count
    FROM cases c
    LEFT JOIN clients cl ON c.client_id = cl.id
    LEFT JOIN users u ON c.lawyer_id = u.id
    LEFT JOIN users p ON c.paralegal_id = p.id
    ORDER BY c.created_at DESC 
    LIMIT :offset, :limit";

$params = [
    ':offset' => $offset,
    ':limit' => $records_per_page
];

$cases = $db->fetchAll($query, $params);

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total_cases,
    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_cases,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_cases,
    SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed_cases,
    (SELECT COUNT(DISTINCT client_id) FROM cases) as total_clients,
    (SELECT COUNT(DISTINCT lawyer_id) FROM cases WHERE lawyer_id IS NOT NULL) as assigned_lawyers
    FROM cases";
$stats = $db->fetchOne($stats_query);

// Get available lawyers for assignment
$lawyers = $db->fetchAll("SELECT id, full_name as name FROM users WHERE role = 'lawyer' AND status = 'active'");

// Get available paralegals for assignment
$paralegals = $db->fetchAll("SELECT p.id, u.full_name as name 
                            FROM paralegals p 
                            JOIN users u ON p.user_id = u.id 
                            WHERE p.status = 'active'");
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Case Management</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Add Case Button -->
            <div class="row mb-4">
                <div class="col-12">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addCaseModal">
                        <i class="fas fa-plus"></i> Add New Case
                    </button>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card p-3">
                        <div class="d-flex align-items-center">
                            <div class="icon cases mr-3">
                                <i class="fas fa-gavel"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 text-muted">Total Cases</h6>
                                <h3 class="mb-0"><?php echo $stats['total_cases']; ?></h3>
                                <small class="text-info">
                                    <i class="fas fa-chart-line"></i> All Cases
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
                                <h6 class="mb-0 text-muted">Active Cases</h6>
                                <h3 class="mb-0"><?php echo $stats['active_cases']; ?></h3>
                                <small class="text-success">
                                    <i class="fas fa-percentage"></i> 
                                    <?php echo $stats['total_cases'] > 0 ? round(($stats['active_cases'] / $stats['total_cases']) * 100) : 0; ?>% Active
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card p-3">
                        <div class="d-flex align-items-center">
                            <div class="icon clients mr-3">
                                <i class="fas fa-users"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 text-muted">Total Clients</h6>
                                <h3 class="mb-0"><?php echo $stats['total_clients']; ?></h3>
                                <small class="text-info">
                                    <i class="fas fa-user-tie"></i> 
                                    <?php echo $stats['assigned_lawyers']; ?> Lawyers Assigned
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card p-3">
                        <div class="d-flex align-items-center">
                            <div class="icon pending mr-3">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 text-muted">Pending Cases</h6>
                                <h3 class="mb-0"><?php echo $stats['pending_cases']; ?></h3>
                                <small class="text-warning">
                                    <i class="fas fa-hourglass-half"></i> Awaiting Action
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cases Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">All Cases</h3>
                        </div>
                        <div class="card-body">
                            <table id="casesTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Case Number</th>
                                        <th>Title</th>
                                        <th>Client</th>
                                        <th>Lawyer</th>
                                        <th>Paralegal</th>
                                        <th>Status</th>
                                        <th>Priority</th>
                                        <th>Next Hearing</th>
                                        <th>Documents</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cases as $case): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($case['case_number']); ?></td>
                                        <td><?php echo htmlspecialchars($case['title']); ?></td>
                                        <td><?php echo htmlspecialchars($case['client_name']); ?></td>
                                        <td><?php echo htmlspecialchars($case['lawyer_name'] ?? 'Not Assigned'); ?></td>
                                        <td><?php echo htmlspecialchars($case['paralegal_name'] ?? 'Not Assigned'); ?></td>
                                        <td>
                                            <?php
                                            $status_badges = [
                                                'pending' => 'warning',
                                                'active' => 'success',
                                                'on_hold' => 'info',
                                                'closed' => 'secondary',
                                                'won' => 'success',
                                                'lost' => 'danger'
                                            ];
                                            $badge_class = $status_badges[$case['status']] ?? 'secondary';
                                            ?>
                                            <span class="badge badge-<?php echo $badge_class; ?>">
                                                <?php echo ucfirst($case['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $priority_badges = [
                                                'LOW' => 'info',
                                                'MEDIUM' => 'primary',
                                                'HIGH' => 'warning',
                                                'URGENT' => 'danger'
                                            ];
                                            $badge_class = $priority_badges[$case['priority']] ?? 'secondary';
                                            ?>
                                            <span class="badge badge-<?php echo $badge_class; ?>">
                                                <?php echo $case['priority']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                            echo $case['next_hearing_date'] 
                                                ? date('M d, Y', strtotime($case['next_hearing_date']))
                                                : 'Not Scheduled';
                                            ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">
                                                <?php echo $case['document_count']; ?> Docs
                                            </span>
                                            <span class="badge badge-secondary">
                                                <?php echo $case['notes_count']; ?> Notes
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-info" onclick="viewCase(<?php echo $case['id']; ?>)">
                                                    <i class="fas fa-eye"></i> View
                                                </button>
                                                <button type="button" class="btn btn-sm btn-primary" onclick="editCase(<?php echo $case['id']; ?>)">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <button type="button" class="btn btn-sm btn-success" onclick="assignCase(<?php echo $case['id']; ?>)">
                                                    <i class="fas fa-user-plus"></i> Assign
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

<!-- Add Case Modal -->
<div class="modal fade" id="addCaseModal" tabindex="-1" role="dialog" aria-labelledby="addCaseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCaseModalLabel">Add New Case</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addCaseForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="title">Case Title</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            <div class="form-group">
                                <label for="case_number">Case Number</label>
                                <input type="text" class="form-control" id="case_number" name="case_number" required>
                            </div>
                            <div class="form-group">
                                <label for="type">Case Type</label>
                                <select class="form-control" id="type" name="type" required>
                                    <option value="">Select Type</option>
                                    <option value="Criminal">Criminal</option>
                                    <option value="Civil">Civil</option>
                                    <option value="Family">Family</option>
                                    <option value="Corporate">Corporate</option>
                                    <option value="Real Estate">Real Estate</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="client_id">Client</label>
                                <select class="form-control" id="client_id" name="client_id" required>
                                    <option value="">Select Client</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="court_name">Court Name</label>
                                <input type="text" class="form-control" id="court_name" name="court_name">
                            </div>
                            <div class="form-group">
                                <label for="court_location">Court Location</label>
                                <input type="text" class="form-control" id="court_location" name="court_location">
                            </div>
                            <div class="form-group">
                                <label for="filing_date">Filing Date</label>
                                <input type="date" class="form-control" id="filing_date" name="filing_date">
                            </div>
                            <div class="form-group">
                                <label for="next_hearing_date">Next Hearing Date</label>
                                <input type="date" class="form-control" id="next_hearing_date" name="next_hearing_date">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="description">Case Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Case</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Case Modal -->
<div class="modal fade" id="assignCaseModal" tabindex="-1" role="dialog" aria-labelledby="assignCaseModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignCaseModalLabel">Assign Case</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="assignCaseForm">
                <div class="modal-body">
                    <input type="hidden" id="case_id" name="case_id">
                    <div class="form-group">
                        <label for="assigned_lawyer_id">Assign Lawyer</label>
                        <select class="form-control" id="assigned_lawyer_id" name="assigned_lawyer_id" required>
                            <option value="">Select Lawyer</option>
                            <?php foreach ($lawyers as $lawyer): ?>
                                <option value="<?php echo $lawyer['id']; ?>"><?php echo htmlspecialchars($lawyer['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="assigned_paralegal_id">Assign Paralegal</label>
                        <select class="form-control" id="assigned_paralegal_id" name="assigned_paralegal_id">
                            <option value="">Select Paralegal</option>
                            <?php foreach ($paralegals as $paralegal): ?>
                                <option value="<?php echo $paralegal['id']; ?>"><?php echo htmlspecialchars($paralegal['name']); ?></option>
                            <?php endforeach; ?>
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
                    <div class="form-group">
                        <label for="notes">Assignment Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
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
    $('#casesTable').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true
    });

    // Load clients for the add case form
    $.ajax({
        url: 'ajax/get_clients.php',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                const select = $('#client_id');
                response.clients.forEach(function(client) {
                    select.append(`<option value="${client.id}">${client.name}</option>`);
                });
            }
        }
    });

    // Handle Add Case Form Submit
    $('#addCaseForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'ajax/add_case.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    $('#addCaseModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Case has been added successfully.',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'Error adding case'
                    });
                }
            }
        });
    });

    // Handle Assign Case Form Submit
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
            }
        });
    });
});

// Function to view case details
function viewCase(id) {
    window.location.href = `view_case.php?id=${id}`;
}

// Function to edit case
function editCase(id) {
    window.location.href = `edit_case.php?id=${id}`;
}

// Function to assign case
function assignCase(id) {
    $('#case_id').val(id);
    $('#assignCaseModal').modal('show');
}
</script>

<?php require_once 'include/footer.php'; ?> 