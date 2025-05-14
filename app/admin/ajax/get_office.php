<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('Office ID is required');
    }

    $id = (int)$_GET['id'];
    
    $db = getDB();
    
    // Get office details with manager information
    $query = "SELECT o.*, u.full_name as manager_name 
              FROM offices o 
              LEFT JOIN users u ON o.manager_id = u.id 
              WHERE o.id = :id";
              
    $office = $db->fetchOne($query, [':id' => $id]);
    
    if (!$office) {
        throw new Exception('Office not found');
    }
    
    echo json_encode([
        'success' => true,
        'data' => $office
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 