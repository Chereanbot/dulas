<?php
require_once(__DIR__ . '/../config/database.php');
session_start();
header('Content-Type: application/json');

function response($success, $message, $data = []) {
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    response(false, 'Invalid request method.');
}

$action = $_POST['action'] ?? '';
$db = getDB();

// CSRF token check (optional, add your own logic)
// if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
//     response(false, 'Invalid CSRF token.');
// }

if ($action === 'fetch_all') {
    try {
        $users = $db->fetchAll("SELECT * FROM users ORDER BY created_at DESC");
        response(true, 'Users fetched successfully.', $users);
    } catch (Exception $e) {
        response(false, 'Error fetching users: ' . $e->getMessage());
    }
}

if ($action === 'fetch_user') {
    $id = intval($_POST['id'] ?? 0);
    $user = $db->fetchOne('SELECT * FROM users WHERE id = ?', [$id]);
    if ($user) {
        response(true, 'User fetched.', $user);
    } else {
        response(false, 'User not found.');
    }
}

if ($action === 'add_user') {
    $fields = ['username','email','full_name','role','phone','status'];
    foreach ($fields as $f) {
        if (empty($_POST[$f])) response(false, 'Missing field: ' . $f);
    }
    $password = $_POST['password'] ?? '';
    if (strlen($password) < 6) response(false, 'Password must be at least 6 characters.');
    $hash = password_hash($password, PASSWORD_DEFAULT);
    try {
        $db->execute('INSERT INTO users (username, password, email, full_name, role, phone, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())',
            [$_POST['username'], $hash, $_POST['email'], $_POST['full_name'], $_POST['role'], $_POST['phone'], $_POST['status']]);
        response(true, 'User added successfully.');
    } catch (Exception $e) {
        response(false, 'Error adding user: ' . $e->getMessage());
    }
}

if ($action === 'edit_user') {
    $id = intval($_POST['id'] ?? 0);
    $fields = ['username','email','full_name','role','phone','status'];
    foreach ($fields as $f) {
        if (empty($_POST[$f])) response(false, 'Missing field: ' . $f);
    }
    try {
        $db->execute('UPDATE users SET username=?, email=?, full_name=?, role=?, phone=?, status=? WHERE id=?',
            [$_POST['username'], $_POST['email'], $_POST['full_name'], $_POST['role'], $_POST['phone'], $_POST['status'], $id]);
        response(true, 'User updated successfully.');
    } catch (Exception $e) {
        response(false, 'Error updating user: ' . $e->getMessage());
    }
}

if ($action === 'reset_password') {
    $id = intval($_POST['id'] ?? 0);
    $password = $_POST['password'] ?? '';
    if (strlen($password) < 6) response(false, 'Password must be at least 6 characters.');
    $hash = password_hash($password, PASSWORD_DEFAULT);
    try {
        $db->execute('UPDATE users SET password = ? WHERE id = ?', [$hash, $id]);
        response(true, 'Password reset successfully.');
    } catch (Exception $e) {
        response(false, 'Error resetting password: ' . $e->getMessage());
    }
}

if ($action === 'activate_user') {
    $id = intval($_POST['id'] ?? 0);
    try {
        $db->execute('UPDATE users SET status = "active" WHERE id = ?', [$id]);
        response(true, 'User activated successfully.');
    } catch (Exception $e) {
        response(false, 'Error activating user: ' . $e->getMessage());
    }
}

if ($action === 'deactivate_user') {
    $id = intval($_POST['id'] ?? 0);
    try {
        $db->execute('UPDATE users SET status = "inactive" WHERE id = ?', [$id]);
        response(true, 'User deactivated successfully.');
    } catch (Exception $e) {
        response(false, 'Error deactivating user: ' . $e->getMessage());
    }
}

if ($action === 'delete_user') {
    $id = intval($_POST['id'] ?? 0);
    try {
        $db->execute('DELETE FROM users WHERE id = ?', [$id]);
        response(true, 'User deleted successfully.');
    } catch (Exception $e) {
        response(false, 'Error deleting user: ' . $e->getMessage());
    }
}

response(false, 'Unknown action.'); 