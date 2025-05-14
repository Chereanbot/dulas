<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

try {
    if (!isset($_POST['id'])) {
        throw new Exception('Office ID is required');
    }

    $id = (int)$_POST['id'];
    $db = getDB();
    
    // Check if office exists
    $check_query = "SELECT id FROM offices WHERE id = :id";
    $office = $db->fetchOne($check_query, [':id' => $id]);
    
    if (!$office) {
        throw new Exception('Office not found');
    }
    
    // Delete office
    $query = "DELETE FROM offices WHERE id = :id";
    $db->execute($query, [':id' => $id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Office deleted successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 