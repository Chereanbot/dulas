<?php
require_once('include/header.php');
require_once('include/sidebar.php');
require_once(__DIR__ . '/../config/database.php');
$db = getDB();

// Fetch all roles and their permissions
try {
    $roles = $db->fetchAll("SELECT * FROM roles ORDER BY role_name ASC");
    $permissions = $db->fetchAll("SELECT * FROM permissions ORDER BY module ASC, permission_name ASC");
} catch (Exception $e) {
    $roles = [];
    $permissions = [];
}

// Fetch role permissions mapping (by role_id and permission_id)
$rolePermissions = [];
try {
    $mappings = $db->fetchAll("SELECT * FROM role_permissions");
    foreach ($mappings as $mapping) {
        if (isset($mapping['role_id']) && isset($mapping['permission_id'])) {
            if (!isset($rolePermissions[$mapping['role_id']])) {
                $rolePermissions[$mapping['role_id']] = [];
            }
            $rolePermissions[$mapping['role_id']][] = $mapping['permission_id'];
        }
    }
} catch (Exception $e) {
    $rolePermissions = [];
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

.main-content {
    margin-left: 280px;
    margin-top: 60px;
    padding: 2rem 1.5rem 1.5rem 1.5rem;
    min-height: calc(100vh - 60px);
    background: var(--background);
}

.role-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
    border: 1px solid rgba(0,0,0,0.05);
}

.role-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.role-header {
    background: var(--primary-green);
    color: white;
    padding: 1.5rem;
    border-radius: 15px 15px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.role-title {
    font-size: 1.2rem;
    font-weight: 600;
    margin: 0;
}

.role-description {
    color: rgba(255,255,255,0.8);
    font-size: 0.9rem;
    margin-top: 0.5rem;
}

.permission-group {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.permission-group-title {
    color: var(--primary-green);
    font-weight: 600;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.permission-item {
    display: flex;
    align-items: center;
    padding: 0.5rem;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.permission-item:hover {
    background: rgba(0,0,0,0.02);
}

.permission-checkbox {
    width: 20px;
    height: 20px;
    border-radius: 4px;
    border: 2px solid var(--primary-green);
    margin-right: 1rem;
    cursor: pointer;
    position: relative;
    transition: all 0.2s ease;
}

.permission-checkbox.checked {
    background: var(--primary-green);
}

.permission-checkbox.checked::after {
    content: '✓';
    position: absolute;
    color: white;
    font-size: 14px;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.permission-name {
    flex: 1;
    font-size: 0.95rem;
    color: var(--text-primary);
}

.permission-description {
    font-size: 0.85rem;
    color: #666;
    margin-top: 0.2rem;
}

.btn-primary {
    background: var(--primary-green);
    border: none;
    padding: 0.6rem 1.2rem;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn-primary:hover {
    background: var(--secondary-green);
    transform: translateY(-1px);
}

.btn-secondary {
    background: #6c757d;
    border: none;
    padding: 0.6rem 1.2rem;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn-secondary:hover {
    background: #5a6268;
    transform: translateY(-1px);
}

.modal-content {
    border: none;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.modal-header {
    background: var(--primary-green);
    color: white;
    border-radius: 15px 15px 0 0;
    border: none;
    padding: 1.5rem;
}

.modal-title {
    font-weight: 600;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    border-top: 1px solid #eee;
    padding: 1rem 1.5rem;
}

.form-control {
    border-radius: 8px;
    border: 1px solid #ddd;
    padding: 0.6rem 1rem;
}

.form-control:focus {
    border-color: var(--primary-green);
    box-shadow: 0 0 0 0.2rem rgba(0,87,45,0.25);
}

.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255,255,255,0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.loading-spinner {
    width: 50px;
    height: 50px;
    border: 3px solid #f3f3f3;
    border-top: 3px solid var(--primary-green);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.toast-container {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 9999;
}

.toast {
    background: white;
    border-radius: 8px;
    padding: 1rem;
    margin-top: 0.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from { transform: translateX(100%); }
    to { transform: translateX(0); }
}

.toast.success {
    border-left: 4px solid var(--primary-green);
}

.toast.error {
    border-left: 4px solid #dc3545;
}

.toast-icon {
    font-size: 1.2rem;
}

.toast.success .toast-icon {
    color: var(--primary-green);
}

.toast.error .toast-icon {
    color: #dc3545;
}
</style>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0" style="color:var(--primary-green);font-weight:700;">Role Management</h2>
        <button class="btn btn-primary" id="addRoleBtn">
            <i class="fas fa-plus"></i> Add New Role
        </button>
    </div>

    <div class="row" id="rolesContainer">
        <?php foreach ($roles as $role): ?>
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="role-card">
                <div class="role-header">
                    <div>
                        <h3 class="role-title"><?php echo htmlspecialchars($role['role_name']); ?></h3>
                        <div class="role-description"><?php echo htmlspecialchars($role['description']); ?></div>
                    </div>
                    <div class="role-actions">
                        <button class="btn btn-light btn-sm edit-role" data-id="<?php echo $role['id']; ?>">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-light btn-sm delete-role" data-id="<?php echo $role['id']; ?>">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="p-3">
                    <?php
                    $groupedPermissions = [];
                    foreach ($permissions as $permission) {
                        $groupedPermissions[$permission['module']][] = $permission;
                    }
                    foreach ($groupedPermissions as $module => $modulePermissions):
                    ?>
                    <div class="permission-group">
                        <div class="permission-group-title">
                            <i class="fas fa-folder"></i> <?php echo htmlspecialchars($module); ?>
                        </div>
                        <?php foreach ($modulePermissions as $permission): ?>
                        <div class="permission-item">
                            <div class="permission-checkbox <?php echo in_array($permission['id'], $rolePermissions[$role['id']] ?? []) ? 'checked' : ''; ?>"
                                 data-role-id="<?php echo $role['id']; ?>"
                                 data-permission-id="<?php echo $permission['id']; ?>">
                            </div>
                            <div>
                                <div class="permission-name"><?php echo htmlspecialchars($permission['permission_name']); ?></div>
                                <div class="permission-description"><?php echo htmlspecialchars($permission['description']); ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Add/Edit Role Modal -->
<div class="modal fade" id="roleModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="roleForm">
                <input type="hidden" name="id" id="roleId">
                <div class="modal-header">
                    <h5 class="modal-title" id="roleModalTitle">Add New Role</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Role Name</label>
                        <input type="text" class="form-control" name="role_name" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" style="display: none;">
    <div class="loading-spinner"></div>
</div>

<!-- Toast Container -->
<div class="toast-container"></div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Show loading overlay
    function showLoading() {
        $('.loading-overlay').fadeIn();
    }

    // Hide loading overlay
    function hideLoading() {
        $('.loading-overlay').fadeOut();
    }

    // Show toast message
    function showToast(type, message) {
        const toast = $(`
            <div class="toast ${type}">
                <div class="toast-icon">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                </div>
                <div class="toast-message">${message}</div>
            </div>
        `);
        $('.toast-container').append(toast);
        setTimeout(() => toast.remove(), 3000);
    }

    // Add Role
    $('#addRoleBtn').click(function() {
        $('#roleModalTitle').text('Add New Role');
        $('#roleForm')[0].reset();
        $('#roleId').val('');
        $('#roleModal').modal('show');
    });

    // Edit Role
    $('.edit-role').click(function() {
        const id = $(this).data('id');
        showLoading();
        $.post('role_actions.php', {action: 'fetch_role', id: id}, function(res) {
            hideLoading();
            if (res.success) {
                $('#roleModalTitle').text('Edit Role');
                $('#roleId').val(res.data.id);
                $('input[name="role_name"]').val(res.data.role_name);
                $('textarea[name="description"]').val(res.data.description);
                $('#roleModal').modal('show');
            } else {
                showToast('error', res.message);
            }
        }, 'json');
    });

    // Delete Role
    $('.delete-role').click(function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Delete Role',
            text: 'Are you sure you want to delete this role? This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete',
            cancelButtonText: 'No, cancel',
            confirmButtonColor: '#dc3545'
        }).then((result) => {
            if (result.isConfirmed) {
                showLoading();
                $.post('role_actions.php', {action: 'delete_role', id: id}, function(res) {
                    hideLoading();
                    if (res.success) {
                        showToast('success', res.message);
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showToast('error', res.message);
                    }
                }, 'json');
            }
        });
    });

    // Save Role
    $('#roleForm').submit(function(e) {
        e.preventDefault();
        const formData = $(this).serializeArray();
        formData.push({name: 'action', value: $('#roleId').val() ? 'edit_role' : 'add_role'});
        
        showLoading();
        $.post('role_actions.php', formData, function(res) {
            hideLoading();
            if (res.success) {
                showToast('success', res.message);
                $('#roleModal').modal('hide');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast('error', res.message);
            }
        }, 'json');
    });

    // Toggle Permission
    $('.permission-checkbox').click(function() {
        const $checkbox = $(this);
        const roleId = $checkbox.data('role-id');
        const permissionId = $checkbox.data('permission-id');
        const isChecked = $checkbox.hasClass('checked');

        showLoading();
        $.post('role_actions.php', {
            action: isChecked ? 'remove_permission' : 'add_permission',
            role_id: roleId,
            permission_id: permissionId
        }, function(res) {
            hideLoading();
            if (res.success) {
                $checkbox.toggleClass('checked');
                showToast('success', res.message);
            } else {
                showToast('error', res.message);
            }
        }, 'json');
    });
});
</script> 