<?php
require_once '../../config/database.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $db = getDB();
    $id = (int)$_POST['id'];
    
    // First check if user exists
    $check_query = "SELECT id FROM users WHERE id = :id";
    $check_result = $db->fetchOne($check_query, [':id' => $id]);
    
    if (!$check_result) {
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
        exit;
    }
    
    // Delete the user
    $delete_query = "DELETE FROM users WHERE id = :id";
    try {
        $db->execute($delete_query, [':id' => $id]);
        echo json_encode([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error deleting user: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request'
    ]);
} 