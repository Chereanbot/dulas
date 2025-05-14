<?php
require_once '../config/database.php';
require_once 'include/header.php';
require_once 'include/sidebar.php';

$db = getDB();

// Pagination settings
$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Get total records for pagination
$total_query = "SELECT COUNT(*) as total FROM users";
$total_result = $db->fetchOne($total_query);
$total_records = $total_result['total'];
$total_pages = ceil($total_records / $records_per_page);

// Get users with pagination
$query = "SELECT u.*, r.role_name 
          FROM users u 
          LEFT JOIN roles r ON u.role = r.role_name 
          ORDER BY u.created_at DESC 
          LIMIT :offset, :limit";

$params = [
    ':offset' => $offset,
    ':limit' => $records_per_page
];

$users = $db->fetchAll($query, $params);

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total_users,
    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_users,
    SUM(CASE WHEN role = 'lawyer' THEN 1 ELSE 0 END) as total_lawyers,
    SUM(CASE WHEN role = 'client' THEN 1 ELSE 0 END) as total_clients
    FROM users";
$stats = $db->fetchOne($stats_query);
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
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
    margin-bottom: 20px;
}

.stat-card:hover {
    transform: translateY(-5px);
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

.stat-card .icon.users { background: rgba(0,87,45,0.1); color: var(--primary-green); }
.stat-card .icon.active { background: rgba(31,147,69,0.1); color: var(--secondary-green); }
.stat-card .icon.lawyers { background: rgba(243,195,0,0.1); color: var(--accent-yellow); }
.stat-card .icon.clients { background: rgba(0,87,45,0.1); color: var(--primary-green); }

.table-container {
    background: var(--background-white);
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 20px;
    margin-top: 20px;
}

.table {
    margin-bottom: 0;
}

.table thead th {
    background: var(--background-light);
    color: var(--text-primary);
    font-weight: 600;
    border-bottom: 2px solid var(--border-color);
    padding: 12px;
}

.table tbody td {
    padding: 12px;
    vertical-align: middle;
    border-bottom: 1px solid var(--border-color);
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
    border-radius: 20px;
    padding: 0.5rem 1rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.btn-primary {
    background: var(--primary-green);
    border-color: var(--primary-green);
}

.btn-primary:hover {
    background: var(--secondary-green);
    border-color: var(--secondary-green);
}

.btn-group .btn {
    padding: 0.375rem 0.75rem;
}

.pagination .page-link {
    color: var(--primary-green);
    padding: 0.5rem 0.75rem;
}

.pagination .page-item.active .page-link {
    background: var(--primary-green);
    border-color: var(--primary-green);
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
</style>

<div class="content-wrapper">
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">User Management</h1>
            <a href="add_user.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New User
            </a>
        </div>

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-xl-3 col-md-6">
                <div class="stat-card p-3">
                    <div class="d-flex align-items-center">
                        <div class="icon users mr-3">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 text-muted">Total Users</h6>
                            <h3 class="mb-0"><?php echo $stats['total_users']; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card p-3">
                    <div class="d-flex align-items-center">
                        <div class="icon active mr-3">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 text-muted">Active Users</h6>
                            <h3 class="mb-0"><?php echo $stats['active_users']; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card p-3">
                    <div class="d-flex align-items-center">
                        <div class="icon lawyers mr-3">
                            <i class="fas fa-gavel"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 text-muted">Total Lawyers</h6>
                            <h3 class="mb-0"><?php echo $stats['total_lawyers']; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card p-3">
                    <div class="d-flex align-items-center">
                        <div class="icon clients mr-3">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 text-muted">Total Clients</h6>
                            <h3 class="mb-0"><?php echo $stats['total_clients']; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Box -->
        <div class="search-box">
            <div class="input-group">
                <input type="text" class="form-control border-0" name="table_search" placeholder="Search users...">
                <div class="input-group-append">
                    <button class="btn btn-link text-muted">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="table-container">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="badge badge-info">
                                    <?php echo htmlspecialchars($user['role']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($user['status'] == 'active'): ?>
                                    <span class="badge badge-success">Active</span>
                                <?php elseif ($user['status'] == 'inactive'): ?>
                                    <span class="badge badge-danger">Inactive</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Suspended</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td class="table-actions">
                                <div class="btn-group">
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-primary view-user" 
                                            data-id="<?php echo $user['id']; ?>"
                                            title="View">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-success edit-user" 
                                            data-id="<?php echo $user['id']; ?>"
                                            title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-danger delete-user" 
                                            data-id="<?php echo $user['id']; ?>"
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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
    </div>
</div>

<!-- View User Modal -->
<div class="modal fade" id="viewUserModal" tabindex="-1" role="dialog" aria-labelledby="viewUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewUserModalLabel">User Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Full Name</label>
                            <p id="view-fullname" class="form-control-static"></p>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Username</label>
                            <p id="view-username" class="form-control-static"></p>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Email</label>
                            <p id="view-email" class="form-control-static"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Role</label>
                            <p id="view-role" class="form-control-static"></p>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Status</label>
                            <p id="view-status" class="form-control-static"></p>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Created At</label>
                            <p id="view-created" class="form-control-static"></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="view-to-edit">Edit User</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editUserForm">
                <div class="modal-body">
                    <input type="hidden" id="edit-user-id" name="id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit-fullname">Full Name</label>
                                <input type="text" class="form-control" id="edit-fullname" name="full_name" required>
                            </div>
                            <div class="form-group">
                                <label for="edit-username">Username</label>
                                <input type="text" class="form-control" id="edit-username" name="username" required>
                            </div>
                            <div class="form-group">
                                <label for="edit-email">Email</label>
                                <input type="email" class="form-control" id="edit-email" name="email" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit-role">Role</label>
                                <select class="form-control" id="edit-role" name="role" required>
                                    <option value="client">Client</option>
                                    <option value="lawyer">Lawyer</option>
                                    <option value="paralegal">Paralegal</option>
                                    <option value="super_paralegal">Super Paralegal</option>
                                    <option value="lawschool">Law School</option>
                                    <option value="admin">Admin</option>
                                    <option value="superadmin">Super Admin</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit-status">Status</label>
                                <select class="form-control" id="edit-status" name="status" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="suspended">Suspended</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit-password">New Password (leave blank to keep current)</label>
                                <input type="password" class="form-control" id="edit-password" name="password">
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
                <p>Are you sure you want to delete this user?</p>
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
$(document).ready(function() {
    let currentUserId = null;

    // View User
    $('.view-user').click(function() {
        currentUserId = $(this).data('id');
        
        // Fetch user data
        $.ajax({
            url: 'ajax/get_user.php',
            type: 'GET',
            data: { id: currentUserId },
            success: function(response) {
                if (response.success) {
                    const user = response.data;
                    $('#view-fullname').text(user.full_name);
                    $('#view-username').text(user.username);
                    $('#view-email').text(user.email);
                    $('#view-role').text(user.role);
                    $('#view-status').html(getStatusBadge(user.status));
                    $('#view-created').text(new Date(user.created_at).toLocaleDateString());
                    $('#viewUserModal').modal('show');
                } else {
                    alert('Error loading user data: ' + response.message);
                }
            },
            error: function() {
                alert('Error occurred while loading user data');
            }
        });
    });

    // View to Edit transition
    $('#view-to-edit').click(function() {
        $('#viewUserModal').modal('hide');
        $('.edit-user[data-id="' + currentUserId + '"]').click();
    });

    // Edit User
    $('.edit-user').click(function() {
        currentUserId = $(this).data('id');
        
        // Fetch user data
        $.ajax({
            url: 'ajax/get_user.php',
            type: 'GET',
            data: { id: currentUserId },
            success: function(response) {
                if (response.success) {
                    const user = response.data;
                    $('#edit-user-id').val(user.id);
                    $('#edit-fullname').val(user.full_name);
                    $('#edit-username').val(user.username);
                    $('#edit-email').val(user.email);
                    $('#edit-role').val(user.role);
                    $('#edit-status').val(user.status);
                    $('#edit-password').val('');
                    $('#editUserModal').modal('show');
                } else {
                    alert('Error loading user data: ' + response.message);
                }
            },
            error: function() {
                alert('Error occurred while loading user data');
            }
        });
    });

    // Handle Edit Form Submit
    $('#editUserForm').submit(function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        
        $.ajax({
            url: 'ajax/update_user.php',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#editUserModal').modal('hide');
                    location.reload();
                } else {
                    alert('Error updating user: ' + response.message);
                }
            },
            error: function() {
                alert('Error occurred while updating user');
            }
        });
    });

    // Delete User
    $('.delete-user').click(function() {
        currentUserId = $(this).data('id');
        $('#deleteModal').modal('show');
    });
    
    $('#confirmDelete').click(function() {
        if (currentUserId) {
            $.ajax({
                url: 'ajax/delete_user.php',
                type: 'POST',
                data: { id: currentUserId },
                success: function(response) {
                    if (response.success) {
                        $('#deleteModal').modal('hide');
                        location.reload();
                    } else {
                        alert('Error deleting user: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error occurred while deleting user');
                }
            });
        }
    });

    // Helper function for status badge
    function getStatusBadge(status) {
        const badges = {
            'active': '<span class="badge badge-success">Active</span>',
            'inactive': '<span class="badge badge-danger">Inactive</span>',
            'suspended': '<span class="badge badge-warning">Suspended</span>'
        };
        return badges[status] || status;
    }

    // Search functionality
    $('input[name="table_search"]').on('keyup', function() {
        let value = $(this).val().toLowerCase();
        $("table tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});
</script>

<?php require_once 'include/footer.php'; ?> 