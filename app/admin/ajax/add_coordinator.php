<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

try {
    $db = getDB();
    
    // Validate required fields
    $required_fields = ['full_name', 'email', 'phone', 'username', 'password', 'organization_id'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("$field is required");
        }
    }
    
    // Check if username or email already exists
    $check_query = "SELECT COUNT(*) as count FROM users WHERE username = :username OR email = :email";
    $check_params = [
        ':username' => $_POST['username'],
        ':email' => $_POST['email']
    ];
    $exists = $db->fetchOne($check_query, $check_params);
    
    if ($exists['count'] > 0) {
        throw new Exception('Username or email already exists');
    }
    
    // Insert new coordinator
    $query = "INSERT INTO users (
        full_name, 
        email, 
        phone, 
        username, 
        password, 
        organization_id,
        role,
        status,
        created_at,
        updated_at
    ) VALUES (
        :full_name,
        :email,
        :phone,
        :username,
        :password,
        :organization_id,
        'paralegal',
        'active',
        NOW(),
        NOW()
    )";
    
    $params = [
        ':full_name' => $_POST['full_name'],
        ':email' => $_POST['email'],
        ':phone' => $_POST['phone'],
        ':username' => $_POST['username'],
        ':password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
        ':organization_id' => $_POST['organization_id']
    ];
    
    $db->execute($query, $params);
    
    echo json_encode([
        'success' => true,
        'message' => 'Coordinator added successfully'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 