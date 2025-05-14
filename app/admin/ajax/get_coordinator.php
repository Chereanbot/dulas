<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

try {
    $db = getDB();
    
    if (!isset($_GET['id'])) {
        throw new Exception('Coordinator ID is required');
    }
    
    $id = (int)$_GET['id'];
    
    $query = "SELECT u.*, o.name as office_name 
              FROM users u 
              LEFT JOIN offices o ON u.organization_id = o.id 
              WHERE u.id = :id AND u.role = 'paralegal'";
              
    $params = [':id' => $id];
    $coordinator = $db->fetchOne($query, $params);
    
    if (!$coordinator) {
        throw new Exception('Coordinator not found');
    }
    
    echo json_encode([
        'success' => true,
        'data' => $coordinator
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 