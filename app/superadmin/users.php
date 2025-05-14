<?php
require_once('include/header.php');
require_once('include/sidebar.php');
require_once(__DIR__ . '/../config/database.php');
$db = getDB();

// Fetch users
try {
    $users = $db->fetchAll("SELECT * FROM users ORDER BY created_at DESC");
} catch (Exception $e) {
    $users = [];
}
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
.main-content {
    margin-left: 250px;
    margin-top: 60px;
    padding: 2rem 1.5rem 1.5rem 1.5rem;
    min-height: calc(100vh - 60px);
    background: var(--background);
}
.table thead th {
    background: var(--primary-green);
    color: #fff;
}
.btn-primary {
    background: var(--primary-green);
    border: none;
}
.btn-primary:hover {
    background: var(--secondary-green);
}
.btn-warning {
    background: var(--accent-yellow);
    color: #333;
    border: none;
}
.btn-warning:hover {
    background: #ffe066;
    color: #333;
}
.btn-danger {
    background: #dc3545;
    border: none;
}
.btn-danger:hover {
    background: #b52a37;
}
.status-badge {
    padding: 0.25em 0.7em;
    border-radius: 0.5em;
    font-size: 0.9em;
    font-weight: 600;
}
.status-active {
    background: var(--secondary-green);
    color: #fff;
}
.status-inactive {
    background: #ccc;
    color: #333;
}
</style>
<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0" style="color:var(--primary-green);font-weight:700;">User Management</h2>
        <button class="btn btn-primary" id="addUserBtn"><i class="fas fa-user-plus"></i> Add User</button>
    </div>
    <!-- Loading Animation -->
    <div id="loading" style="display:none;text-align:center;">
        <div style="display:inline-block;">
            <!-- @website-loading.mdc -->
            <svg width="100" height="100" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" aria-label="Loading">
              <circle cx="50" cy="50" r="35" stroke="#00572d" stroke-width="8" fill="none" stroke-linecap="round">
                <animate attributeName="stroke-dashoffset" values="0;502" dur="2s" repeatCount="indefinite" />
                <animate attributeName="stroke-dasharray" values="150.6 100.4;1 250;150.6 100.4" dur="2s" repeatCount="indefinite" />
              </circle>
              <circle cx="50" cy="50" r="25" stroke="#f3c300" stroke-width="5" fill="none" stroke-dasharray="39.3 39.3" stroke-linecap="round">
                <animateTransform attributeName="transform" type="rotate" from="0 50 50" to="360 50 50" dur="1.5s" repeatCount="indefinite" />
              </circle>
              <circle cx="50" cy="50" r="15" fill="#1f9345">
                <animate attributeName="r" values="15;12;15" dur="1.5s" repeatCount="indefinite" />
              </circle>
            </svg>
        </div>
    </div>
    <!-- User Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="usersTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Avatar</th>
                            <th>Full Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Phone</th>
                            <th>Last Login</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersTbody">
                        <?php if (count($users) === 0): ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted">No users found.</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($users as $i => $user): ?>
                        <tr>
                            <td><?php echo $i+1; ?></td>
                            <td><img src="/dutsca/assets/images/profile/<?php echo htmlspecialchars($user['profile_image'] ?? 'default-avatar.png'); ?>" style="width:36px;height:36px;border-radius:50%;object-fit:cover;"></td>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><span class="badge badge-info"><?php echo htmlspecialchars($user['role']); ?></span></td>
                            <td>
                                <?php if ($user['status'] === 'active'): ?>
                                    <span class="status-badge status-active">Active</span>
                                <?php else: ?>
                                    <span class="status-badge status-inactive"><?php echo ucfirst($user['status']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($user['phone'] ?? '-'); ?></td>
                            <td><?php echo $user['last_login'] ? date('Y-m-d H:i', strtotime($user['last_login'])) : '-'; ?></td>
                            <td>
                                <button class="btn btn-sm btn-info view-user" data-id="<?php echo $user['id']; ?>" title="View"><i class="fas fa-eye"></i></button>
                                <button class="btn btn-sm btn-warning edit-user" data-id="<?php echo $user['id']; ?>" title="Edit"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-sm btn-secondary reset-password" data-id="<?php echo $user['id']; ?>" title="Reset Password"><i class="fas fa-key"></i></button>
                                <?php if ($user['status'] === 'active'): ?>
                                    <button class="btn btn-sm btn-dark deactivate-user" data-id="<?php echo $user['id']; ?>" title="Deactivate"><i class="fas fa-user-slash"></i></button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-success activate-user" data-id="<?php echo $user['id']; ?>" title="Activate"><i class="fas fa-user-check"></i></button>
                                <?php endif; ?>
                                <button class="btn btn-sm btn-danger delete-user" data-id="<?php echo $user['id']; ?>" title="Delete"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Modals for Add/Edit/View -->
    <div id="userModals"></div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
<script>
$(document).ready(function() {
    var usersTable = $('#usersTable').DataTable();

    // Show loading on AJAX
    $(document).ajaxStart(function() {
        $('#loading').show();
    }).ajaxStop(function() {
        $('#loading').hide();
    });

    // Toast function
    function showToast(type, message) {
        Swal.fire({
            toast: true,
            position: 'bottom-end',
            icon: type,
            title: message,
            showConfirmButton: false,
            timer: 2500,
            background: '#fff',
            color: '#333',
            customClass: {popup: 'border-0'}
        });
    }

    // Helper: Refresh user table
    function refreshUsers() {
        $.post('user_actions.php', {action: 'fetch_all'}, function(res) {
            if (res.success && res.data) {
                var rows = '';
                if (res.data.length === 0) {
                    rows = '<tr><td colspan="10" class="text-center text-muted">No users found.</td></tr>';
                } else {
                    res.data.forEach(function(user, i) {
                        rows += `<tr>
                            <td>${i+1}</td>
                            <td><img src="/dutsca/assets/images/profile/${user.profile_image || 'default-avatar.png'}" style="width:36px;height:36px;border-radius:50%;object-fit:cover;"></td>
                            <td>${user.full_name}</td>
                            <td>${user.username}</td>
                            <td>${user.email}</td>
                            <td><span class="badge badge-info">${user.role}</span></td>
                            <td>${user.status === 'active' ? '<span class=\'status-badge status-active\'>Active</span>' : `<span class=\'status-badge status-inactive\'>${user.status.charAt(0).toUpperCase()+user.status.slice(1)}</span>`}</td>
                            <td>${user.phone || '-'}</td>
                            <td>${user.last_login ? user.last_login.substring(0,16).replace('T',' ') : '-'}</td>
                            <td>
                                <button class="btn btn-sm btn-info view-user" data-id="${user.id}" title="View"><i class="fas fa-eye"></i></button>
                                <button class="btn btn-sm btn-warning edit-user" data-id="${user.id}" title="Edit"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-sm btn-secondary reset-password" data-id="${user.id}" title="Reset Password"><i class="fas fa-key"></i></button>
                                ${user.status === 'active' ? `<button class='btn btn-sm btn-dark deactivate-user' data-id='${user.id}' title='Deactivate'><i class='fas fa-user-slash'></i></button>` : `<button class='btn btn-sm btn-success activate-user' data-id='${user.id}' title='Activate'><i class='fas fa-user-check'></i></button>`}
                                <button class="btn btn-sm btn-danger delete-user" data-id="${user.id}" title="Delete"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>`;
                    });
                }
                usersTable.clear().destroy();
                $('#usersTbody').html(rows);
                usersTable = $('#usersTable').DataTable();
            }
        }, 'json');
    }

    // Add User Modal
    $(document).on('click', '#addUserBtn', function() {
        var modal = `<div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content" style="border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                    <form id="addUserForm">
                        <div class="modal-header" style="background: #00572d; color: white; border-radius: 15px 15px 0 0; border: none;">
                            <h5 class="modal-title" style="font-weight: 600;"><i class="fas fa-user-plus mr-2"></i>Add New User</h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body" style="padding: 1.5rem;">
                            <div class="form-group">
                                <label style="color: #333333; font-weight: 500;">Full Name</label>
                                <input type="text" name="full_name" class="form-control" required style="border-radius: 8px; border: 1px solid #ddd;">
                            </div>
                            <div class="form-group">
                                <label style="color: #333333; font-weight: 500;">Username</label>
                                <input type="text" name="username" class="form-control" required style="border-radius: 8px; border: 1px solid #ddd;">
                            </div>
                            <div class="form-group">
                                <label style="color: #333333; font-weight: 500;">Email</label>
                                <input type="email" name="email" class="form-control" required style="border-radius: 8px; border: 1px solid #ddd;">
                            </div>
                            <div class="form-group">
                                <label style="color: #333333; font-weight: 500;">Password</label>
                                <input type="password" name="password" class="form-control" required minlength="6" style="border-radius: 8px; border: 1px solid #ddd;">
                            </div>
                            <div class="form-group">
                                <label style="color: #333333; font-weight: 500;">Role</label>
                                <select name="role" class="form-control" required style="border-radius: 8px; border: 1px solid #ddd;">
                                    <option value="">Select Role</option>
                                    <option value="superadmin">Superadmin</option>
                                    <option value="admin">Admin</option>
                                    <option value="lawyer">Lawyer</option>
                                    <option value="super_paralegal">Super Paralegal</option>
                                    <option value="paralegal">Paralegal</option>
                                    <option value="lawschool">Law School</option>
                                    <option value="client">Client</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label style="color: #333333; font-weight: 500;">Phone</label>
                                <input type="text" name="phone" class="form-control" style="border-radius: 8px; border: 1px solid #ddd;">
                            </div>
                            <div class="form-group">
                                <label style="color: #333333; font-weight: 500;">Status</label>
                                <select name="status" class="form-control" required style="border-radius: 8px; border: 1px solid #ddd;">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="suspended">Suspended</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer" style="border-top: 1px solid #eee; padding: 1rem 1.5rem;">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: 8px; padding: 0.5rem 1.5rem;">Cancel</button>
                            <button type="submit" class="btn btn-primary" style="background: #00572d; border: none; border-radius: 8px; padding: 0.5rem 1.5rem;">Add User</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>`;
        $('#userModals').html(modal);
        $('#addUserModal').modal('show');
    });
    // Add User Submit
    $(document).on('submit', '#addUserForm', function(e) {
        e.preventDefault();
        var form = $(this);
        var data = form.serializeArray();
        data.push({name:'action', value:'add_user'});
        $.post('user_actions.php', data, function(res) {
            if (res.success) {
                showToast('success', res.message);
                $('#addUserModal').modal('hide');
                refreshUsers();
            } else {
                showToast('error', res.message);
            }
        }, 'json');
    });

    // Edit User Modal
    $(document).on('click', '.edit-user', function() {
        var id = $(this).data('id');
        $.post('user_actions.php', {action:'fetch_user', id:id}, function(res) {
            if (res.success && res.data) {
                var u = res.data;
                var modal = `<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content" style="border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                            <form id="editUserForm">
                                <input type="hidden" name="id" value="${u.id}">
                                <div class="modal-header" style="background: #00572d; color: white; border-radius: 15px 15px 0 0; border: none;">
                                    <h5 class="modal-title" style="font-weight: 600;"><i class="fas fa-user-edit mr-2"></i>Edit User</h5>
                                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body" style="padding: 1.5rem;">
                                    <div class="form-group">
                                        <label style="color: #333333; font-weight: 500;">Full Name</label>
                                        <input type="text" name="full_name" class="form-control" value="${u.full_name}" required style="border-radius: 8px; border: 1px solid #ddd;">
                                    </div>
                                    <div class="form-group">
                                        <label style="color: #333333; font-weight: 500;">Username</label>
                                        <input type="text" name="username" class="form-control" value="${u.username}" required style="border-radius: 8px; border: 1px solid #ddd;">
                                    </div>
                                    <div class="form-group">
                                        <label style="color: #333333; font-weight: 500;">Email</label>
                                        <input type="email" name="email" class="form-control" value="${u.email}" required style="border-radius: 8px; border: 1px solid #ddd;">
                                    </div>
                                    <div class="form-group">
                                        <label style="color: #333333; font-weight: 500;">Role</label>
                                        <select name="role" class="form-control" required style="border-radius: 8px; border: 1px solid #ddd;">
                                            <option value="superadmin" ${u.role==='superadmin'?'selected':''}>Superadmin</option>
                                            <option value="admin" ${u.role==='admin'?'selected':''}>Admin</option>
                                            <option value="lawyer" ${u.role==='lawyer'?'selected':''}>Lawyer</option>
                                            <option value="super_paralegal" ${u.role==='super_paralegal'?'selected':''}>Super Paralegal</option>
                                            <option value="paralegal" ${u.role==='paralegal'?'selected':''}>Paralegal</option>
                                            <option value="lawschool" ${u.role==='lawschool'?'selected':''}>Law School</option>
                                            <option value="client" ${u.role==='client'?'selected':''}>Client</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label style="color: #333333; font-weight: 500;">Phone</label>
                                        <input type="text" name="phone" class="form-control" value="${u.phone||''}" style="border-radius: 8px; border: 1px solid #ddd;">
                                    </div>
                                    <div class="form-group">
                                        <label style="color: #333333; font-weight: 500;">Status</label>
                                        <select name="status" class="form-control" required style="border-radius: 8px; border: 1px solid #ddd;">
                                            <option value="active" ${u.status==='active'?'selected':''}>Active</option>
                                            <option value="inactive" ${u.status==='inactive'?'selected':''}>Inactive</option>
                                            <option value="suspended" ${u.status==='suspended'?'selected':''}>Suspended</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer" style="border-top: 1px solid #eee; padding: 1rem 1.5rem;">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: 8px; padding: 0.5rem 1.5rem;">Cancel</button>
                                    <button type="submit" class="btn btn-primary" style="background: #00572d; border: none; border-radius: 8px; padding: 0.5rem 1.5rem;">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>`;
                $('#userModals').html(modal);
                $('#editUserModal').modal('show');
            } else {
                showToast('error', res.message);
            }
        }, 'json');
    });
    // Edit User Submit
    $(document).on('submit', '#editUserForm', function(e) {
        e.preventDefault();
        var form = $(this);
        var data = form.serializeArray();
        data.push({name:'action', value:'edit_user'});
        $.post('user_actions.php', data, function(res) {
            if (res.success) {
                showToast('success', res.message);
                $('#editUserModal').modal('hide');
                refreshUsers();
            } else {
                showToast('error', res.message);
            }
        }, 'json');
    });

    // View User Modal
    $(document).on('click', '.view-user', function() {
        var id = $(this).data('id');
        $.post('user_actions.php', {action:'fetch_user', id:id}, function(res) {
            if (res.success && res.data) {
                var u = res.data;
                var modal = `<div class="modal fade" id="viewUserModal" tabindex="-1" role="dialog" aria-labelledby="viewUserModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content" style="border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                            <div class="modal-header" style="background: #00572d; color: white; border-radius: 15px 15px 0 0; border: none;">
                                <h5 class="modal-title" style="font-weight: 600;"><i class="fas fa-user mr-2"></i>User Details</h5>
                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body" style="padding: 1.5rem;">
                                <div class="text-center mb-4">
                                    <img src="/dutsca/assets/images/profile/${u.profile_image || 'default-avatar.png'}" style="width:100px;height:100px;border-radius:50%;object-fit:cover;border:3px solid #00572d;">
                                </div>
                                <div class="user-details" style="background: #f4f4f4; padding: 1.5rem; border-radius: 10px;">
                                    <div class="mb-3">
                                        <label style="color: #00572d; font-weight: 600;">Full Name</label>
                                        <p class="mb-0" style="color: #333333;">${u.full_name}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label style="color: #00572d; font-weight: 600;">Username</label>
                                        <p class="mb-0" style="color: #333333;">${u.username}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label style="color: #00572d; font-weight: 600;">Email</label>
                                        <p class="mb-0" style="color: #333333;">${u.email}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label style="color: #00572d; font-weight: 600;">Role</label>
                                        <p class="mb-0"><span class="badge" style="background: #1f9345; color: white; padding: 0.5em 1em; border-radius: 5px;">${u.role}</span></p>
                                    </div>
                                    <div class="mb-3">
                                        <label style="color: #00572d; font-weight: 600;">Status</label>
                                        <p class="mb-0">
                                            <span class="badge ${u.status === 'active' ? 'status-active' : 'status-inactive'}" style="padding: 0.5em 1em; border-radius: 5px;">
                                                ${u.status.charAt(0).toUpperCase() + u.status.slice(1)}
                                            </span>
                                        </p>
                                    </div>
                                    <div class="mb-3">
                                        <label style="color: #00572d; font-weight: 600;">Phone</label>
                                        <p class="mb-0" style="color: #333333;">${u.phone || '-'}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label style="color: #00572d; font-weight: 600;">Last Login</label>
                                        <p class="mb-0" style="color: #333333;">${u.last_login ? u.last_login.substring(0,16).replace('T',' ') : '-'}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label style="color: #00572d; font-weight: 600;">Created At</label>
                                        <p class="mb-0" style="color: #333333;">${u.created_at ? u.created_at.substring(0,16).replace('T',' ') : '-'}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer" style="border-top: 1px solid #eee; padding: 1rem 1.5rem;">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: 8px; padding: 0.5rem 1.5rem;">Close</button>
                            </div>
                        </div>
                    </div>
                </div>`;
                $('#userModals').html(modal);
                $('#viewUserModal').modal('show');
            } else {
                showToast('error', res.message);
            }
        }, 'json');
    });

    // Reset Password
    $(document).on('click', '.reset-password', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: '<span style="color: #00572d; font-weight: 600;">Reset Password</span>',
            html: `
                <div style="text-align: left; margin-bottom: 1rem;">
                    <label style="color: #333333; font-weight: 500; display: block; margin-bottom: 0.5rem;">Enter new password</label>
                    <input type="password" id="swal-input1" class="swal2-input" style="border-radius: 8px; border: 1px solid #ddd;">
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Reset Password',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#00572d',
            cancelButtonColor: '#6c757d',
            focusConfirm: false,
            customClass: {
                popup: 'animated fadeInDown',
                confirmButton: 'btn btn-primary',
                cancelButton: 'btn btn-secondary'
            },
            preConfirm: () => {
                const password = document.getElementById('swal-input1').value;
                if (!password) {
                    Swal.showValidationMessage('Please enter a password');
                } else if (password.length < 6) {
                    Swal.showValidationMessage('Password must be at least 6 characters');
                }
                return password;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('user_actions.php', {
                    action: 'reset_password',
                    id: id,
                    password: result.value
                }, function(res) {
                    if (res.success) {
                        showToast('success', res.message);
                    } else {
                        showToast('error', res.message);
                    }
                }, 'json');
            }
        });
    });

    // Activate User
    $(document).on('click', '.activate-user', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: '<span style="color: #00572d; font-weight: 600;">Activate User</span>',
            text: 'Are you sure you want to activate this user?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, activate',
            cancelButtonText: 'No, cancel',
            confirmButtonColor: '#1f9345',
            cancelButtonColor: '#6c757d',
            customClass: {
                popup: 'animated fadeInDown',
                confirmButton: 'btn btn-success',
                cancelButton: 'btn btn-secondary'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('user_actions.php', {
                    action: 'activate_user',
                    id: id
                }, function(res) {
                    if (res.success) {
                        showToast('success', res.message);
                        refreshUsers();
                    } else {
                        showToast('error', res.message);
                    }
                }, 'json');
            }
        });
    });

    // Deactivate User
    $(document).on('click', '.deactivate-user', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: '<span style="color: #00572d; font-weight: 600;">Deactivate User</span>',
            text: 'Are you sure you want to deactivate this user?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, deactivate',
            cancelButtonText: 'No, cancel',
            confirmButtonColor: '#f3c300',
            cancelButtonColor: '#6c757d',
            customClass: {
                popup: 'animated fadeInDown',
                confirmButton: 'btn btn-warning',
                cancelButton: 'btn btn-secondary'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('user_actions.php', {
                    action: 'deactivate_user',
                    id: id
                }, function(res) {
                    if (res.success) {
                        showToast('success', res.message);
                        refreshUsers();
                    } else {
                        showToast('error', res.message);
                    }
                }, 'json');
            }
        });
    });

    // Delete User
    $(document).on('click', '.delete-user', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: '<span style="color: #dc3545; font-weight: 600;">Delete User</span>',
            text: 'Are you sure you want to delete this user? This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete',
            cancelButtonText: 'No, cancel',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            customClass: {
                popup: 'animated fadeInDown',
                confirmButton: 'btn btn-danger',
                cancelButton: 'btn btn-secondary'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('user_actions.php', {
                    action: 'delete_user',
                    id: id
                }, function(res) {
                    if (res.success) {
                        showToast('success', res.message);
                        refreshUsers();
                    } else {
                        showToast('error', res.message);
                    }
                }, 'json');
            }
        });
    });
});
</script> 