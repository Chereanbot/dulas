<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

try {
    if (!isset($_POST['id'])) {
        throw new Exception('Office ID is required');
    }

    $id = (int)$_POST['id'];
    $db = getDB();
    
    // Validate required fields
    $required_fields = ['name', 'address', 'phone', 'email', 'status'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("$field is required");
        }
    }
    
    // Prepare data for update
    $data = [
        'name' => $_POST['name'],
        'address' => $_POST['address'],
        'phone' => $_POST['phone'],
        'email' => $_POST['email'],
        'status' => $_POST['status'],
        'manager_id' => !empty($_POST['manager_id']) ? (int)$_POST['manager_id'] : null
    ];
    
    // Update office
    $query = "UPDATE offices SET 
              name = :name,
              address = :address,
              phone = :phone,
              email = :email,
              status = :status,
              manager_id = :manager_id
              WHERE id = :id";
              
    $params = array_merge($data, [':id' => $id]);
    
    $db->execute($query, $params);
    
    echo json_encode([
        'success' => true,
        'message' => 'Office updated successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 