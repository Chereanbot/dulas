<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

try {
    $db = getDB();
    
    // Validate required fields
    $required_fields = ['full_name', 'email', 'phone', 'username', 'password', 'organization_id', 'specialties'];
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
    
    // Start transaction
    $db->beginTransaction();
    
    try {
        // Insert new lawyer
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
            'lawyer',
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
        $lawyer_id = $db->lastInsertId();
        
        // Insert specialties
        if (!empty($_POST['specialties'])) {
            $specialty_query = "INSERT INTO lawyer_specialties (lawyer_id, specialty_id) VALUES (:lawyer_id, :specialty_id)";
            foreach ($_POST['specialties'] as $specialty_id) {
                $db->execute($specialty_query, [
                    ':lawyer_id' => $lawyer_id,
                    ':specialty_id' => $specialty_id
                ]);
            }
        }
        
        $db->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Lawyer added successfully'
        ]);
        
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 