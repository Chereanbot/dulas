<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

try {
    $db = getDB();
    
    if (!isset($_POST['id'])) {
        throw new Exception('Coordinator ID is required');
    }
    
    $id = (int)$_POST['id'];
    
    // Check if coordinator exists
    $check_query = "SELECT id FROM users WHERE id = :id AND role = 'paralegal'";
    $check_params = [':id' => $id];
    $exists = $db->fetchOne($check_query, $check_params);
    
    if (!$exists) {
        throw new Exception('Coordinator not found');
    }
    
    // Delete coordinator
    $query = "DELETE FROM users WHERE id = :id AND role = 'paralegal'";
    $params = [':id' => $id];
    
    $db->execute($query, $params);
    
    echo json_encode([
        'success' => true,
        'message' => 'Coordinator deleted successfully'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 