<?php
require_once '../../config/database.php';
header('Content-Type: application/json');

try {
    $db = getDB();
    
    // Get active clients
    $query = "SELECT id, name, email, phone 
              FROM clients 
              WHERE status = 'active' 
              ORDER BY name ASC";
    
    $clients = $db->fetchAll($query);
    
    echo json_encode([
        'success' => true,
        'clients' => $clients
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching clients: ' . $e->getMessage()
    ]);
} 