<?php
require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/../config/auth.php');

// Ensure user is logged in and has superadmin access
if (!isLoggedIn() || !hasRole('superadmin')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$db = getDB();
$action = $_POST['action'] ?? '';

function sendResponse($success, $message, $data = null) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

try {
    switch ($action) {
        case 'add_role':
            if (empty($_POST['role_name'])) {
                sendResponse(false, 'Role name is required');
            }

            // Check if role already exists
            $existing = $db->fetchOne("SELECT id FROM roles WHERE role_name = ?", [$_POST['role_name']]);
            if ($existing) {
                sendResponse(false, 'A role with this name already exists');
            }

            $db->insert('roles', [
                'role_name' => $_POST['role_name'],
                'description' => $_POST['description'] ?? '',
                'created_at' => date('Y-m-d H:i:s')
            ]);

            sendResponse(true, 'Role added successfully');
            break;

        case 'edit_role':
            if (empty($_POST['id']) || empty($_POST['role_name'])) {
                sendResponse(false, 'Role ID and name are required');
            }

            // Check if role exists
            $role = $db->fetchOne("SELECT id FROM roles WHERE id = ?", [$_POST['id']]);
            if (!$role) {
                sendResponse(false, 'Role not found');
            }

            // Check if new name conflicts with existing role
            $existing = $db->fetchOne(
                "SELECT id FROM roles WHERE role_name = ? AND id != ?",
                [$_POST['role_name'], $_POST['id']]
            );
            if ($existing) {
                sendResponse(false, 'A role with this name already exists');
            }

            $db->update('roles', [
                'role_name' => $_POST['role_name'],
                'description' => $_POST['description'] ?? '',
                'updated_at' => date('Y-m-d H:i:s')
            ], ['id' => $_POST['id']]);

            sendResponse(true, 'Role updated successfully');
            break;

        case 'delete_role':
            if (empty($_POST['id'])) {
                sendResponse(false, 'Role ID is required');
            }

            // Check if role exists
            $role = $db->fetchOne("SELECT id FROM roles WHERE id = ?", [$_POST['id']]);
            if (!$role) {
                sendResponse(false, 'Role not found');
            }

            // Check if role is assigned to any users
            $users = $db->fetchOne("SELECT COUNT(*) as count FROM users WHERE role_id = ?", [$_POST['id']]);
            if ($users && $users['count'] > 0) {
                sendResponse(false, 'Cannot delete role that is assigned to users');
            }

            // Begin transaction
            $db->beginTransaction();

            try {
                // Delete role permissions
                $db->delete('role_permissions', ['role_id' => $_POST['id']]);
                // Delete role
                $db->delete('roles', ['id' => $_POST['id']]);
                $db->commit();
                sendResponse(true, 'Role deleted successfully');
            } catch (Exception $e) {
                $db->rollBack();
                sendResponse(false, 'Error deleting role: ' . $e->getMessage());
            }
            break;

        case 'fetch_role':
            if (empty($_POST['id'])) {
                sendResponse(false, 'Role ID is required');
            }

            $role = $db->fetchOne("SELECT * FROM roles WHERE id = ?", [$_POST['id']]);
            if (!$role) {
                sendResponse(false, 'Role not found');
            }

            sendResponse(true, 'Role fetched successfully', $role);
            break;

        case 'add_permission':
            if (empty($_POST['role_id']) || empty($_POST['permission_id'])) {
                sendResponse(false, 'Role ID and Permission ID are required');
            }

            // Check if role exists
            $role = $db->fetchOne("SELECT id FROM roles WHERE id = ?", [$_POST['role_id']]);
            if (!$role) {
                sendResponse(false, 'Role not found');
            }

            // Check if permission exists
            $permission = $db->fetchOne("SELECT id FROM permissions WHERE id = ?", [$_POST['permission_id']]);
            if (!$permission) {
                sendResponse(false, 'Permission not found');
            }

            // Check if permission is already assigned
            $existing = $db->fetchOne(
                "SELECT id FROM role_permissions WHERE role_id = ? AND permission_id = ?",
                [$_POST['role_id'], $_POST['permission_id']]
            );
            if ($existing) {
                sendResponse(false, 'Permission is already assigned to this role');
            }

            $db->insert('role_permissions', [
                'role_id' => $_POST['role_id'],
                'permission_id' => $_POST['permission_id'],
                'created_at' => date('Y-m-d H:i:s')
            ]);

            sendResponse(true, 'Permission added successfully');
            break;

        case 'remove_permission':
            if (empty($_POST['role_id']) || empty($_POST['permission_id'])) {
                sendResponse(false, 'Role ID and Permission ID are required');
            }

            // Check if role exists
            $role = $db->fetchOne("SELECT id FROM roles WHERE id = ?", [$_POST['role_id']]);
            if (!$role) {
                sendResponse(false, 'Role not found');
            }

            // Check if permission exists
            $permission = $db->fetchOne("SELECT id FROM permissions WHERE id = ?", [$_POST['permission_id']]);
            if (!$permission) {
                sendResponse(false, 'Permission not found');
            }

            // Check if permission is assigned
            $existing = $db->fetchOne(
                "SELECT id FROM role_permissions WHERE role_id = ? AND permission_id = ?",
                [$_POST['role_id'], $_POST['permission_id']]
            );
            if (!$existing) {
                sendResponse(false, 'Permission is not assigned to this role');
            }

            $db->delete('role_permissions', [
                'role_id' => $_POST['role_id'],
                'permission_id' => $_POST['permission_id']
            ]);

            sendResponse(true, 'Permission removed successfully');
            break;

        default:
            sendResponse(false, 'Invalid action');
    }
} catch (Exception $e) {
    sendResponse(false, 'Error: ' . $e->getMessage());
} 