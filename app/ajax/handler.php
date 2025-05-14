<?php
session_start();
require_once '../config/database.php';

// Check if request is AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    die('Direct access not permitted');
}

// Get the action from POST data
$action = $_POST['action'] ?? '';

// Check user authentication
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit;
}

// Get user role
$user_role = $_SESSION['user_role'] ?? '';

// Handle different actions based on user role and action type
switch ($action) {
    case 'get_user_data':
        // Get user data
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $user_data]);
        break;

    case 'update_profile':
        // Update user profile
        $user_id = $_SESSION['user_id'];
        $data = $_POST['data'] ?? [];
        // Add your profile update logic here
        echo json_encode(['status' => 'success', 'message' => 'Profile updated']);
        break;

    case 'get_notifications':
        // Get user notifications
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
        $stmt->execute([$user_id]);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $notifications]);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}
?> 