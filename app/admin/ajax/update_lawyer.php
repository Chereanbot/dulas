<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

try {
    $db = getDB();
    
    // Validate required fields
    $required_fields = ['id', 'full_name', 'email', 'phone', 'username', 'organization_id', 'status', 'specialties'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("$field is required");
        }
    }
    
    // Check if lawyer exists
    $check_query = "SELECT id FROM users WHERE id = :id AND role = 'lawyer'";
    $check_params = [':id' => $_POST['id']];
    $exists = $db->fetchOne($check_query, $check_params);
    
    if (!$exists) {
        throw new Exception('Lawyer not found');
    }
    
    // Check if username or email already exists for other users
    $check_duplicate_query = "SELECT COUNT(*) as count FROM users 
                             WHERE (username = :username OR email = :email) 
                             AND id != :id";
    $check_duplicate_params = [
        ':username' => $_POST['username'],
        ':email' => $_POST['email'],
        ':id' => $_POST['id']
    ];
    $duplicate = $db->fetchOne($check_duplicate_query, $check_duplicate_params);
    
    if ($duplicate['count'] > 0) {
        throw new Exception('Username or email already exists for another user');
    }
    
    // Start transaction
    $db->beginTransaction();
    
    try {
        // Build update query
        $update_fields = [
            'full_name = :full_name',
            'email = :email',
            'phone = :phone',
            'username = :username',
            'organization_id = :organization_id',
            'status = :status',
            'updated_at = NOW()'
        ];
        
        $params = [
            ':id' => $_POST['id'],
            ':full_name' => $_POST['full_name'],
            ':email' => $_POST['email'],
            ':phone' => $_POST['phone'],
            ':username' => $_POST['username'],
            ':organization_id' => $_POST['organization_id'],
            ':status' => $_POST['status']
        ];
        
        // Add password update if provided
        if (!empty($_POST['password'])) {
            $update_fields[] = 'password = :password';
            $params[':password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }
        
        $query = "UPDATE users SET " . implode(', ', $update_fields) . " WHERE id = :id";
        $db->execute($query, $params);
        
        // Update specialties
        // First, delete existing specialties
        $db->execute("DELETE FROM lawyer_specialties WHERE lawyer_id = :lawyer_id", [':lawyer_id' => $_POST['id']]);
        
        // Then, insert new specialties
        if (!empty($_POST['specialties'])) {
            $specialty_query = "INSERT INTO lawyer_specialties (lawyer_id, specialty_id) VALUES (:lawyer_id, :specialty_id)";
            foreach ($_POST['specialties'] as $specialty_id) {
                $db->execute($specialty_query, [
                    ':lawyer_id' => $_POST['id'],
                    ':specialty_id' => $specialty_id
                ]);
            }
        }
        
        $db->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Lawyer updated successfully'
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