<?php
require_once '../../config/database.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = getDB();
    
    // Get and validate input
    $id = (int)$_POST['id'];
    $full_name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $status = $_POST['status'];
    $password = trim($_POST['password'] ?? '');
    
    // Validate required fields
    if (empty($full_name) || empty($username) || empty($email) || empty($role) || empty($status)) {
        echo json_encode([
            'success' => false,
            'message' => 'All fields are required'
        ]);
        exit;
    }
    
    try {
        // Check if user exists
        $check_query = "SELECT id FROM users WHERE id = :id";
        $user = $db->fetchOne($check_query, [':id' => $id]);
        
        if (!$user) {
            echo json_encode([
                'success' => false,
                'message' => 'User not found'
            ]);
            exit;
        }
        
        // Check if username is already taken by another user
        $username_check = "SELECT id FROM users WHERE username = :username AND id != :id";
        $existing_username = $db->fetchOne($username_check, [
            ':username' => $username,
            ':id' => $id
        ]);
        
        if ($existing_username) {
            echo json_encode([
                'success' => false,
                'message' => 'Username is already taken'
            ]);
            exit;
        }
        
        // Check if email is already taken by another user
        $email_check = "SELECT id FROM users WHERE email = :email AND id != :id";
        $existing_email = $db->fetchOne($email_check, [
            ':email' => $email,
            ':id' => $id
        ]);
        
        if ($existing_email) {
            echo json_encode([
                'success' => false,
                'message' => 'Email is already taken'
            ]);
            exit;
        }
        
        // Prepare update query
        $update_fields = [
            'full_name' => $full_name,
            'username' => $username,
            'email' => $email,
            'role' => $role,
            'status' => $status
        ];
        
        // Add password update if provided
        if (!empty($password)) {
            $update_fields['password'] = password_hash($password, PASSWORD_DEFAULT);
        }
        
        // Build the update query
        $update_query = "UPDATE users SET ";
        $params = [];
        
        foreach ($update_fields as $field => $value) {
            $update_query .= "$field = :$field, ";
            $params[":$field"] = $value;
        }
        
        $update_query = rtrim($update_query, ', ');
        $update_query .= " WHERE id = :id";
        $params[':id'] = $id;
        
        // Execute update
        $db->execute($update_query, $params);
        
        echo json_encode([
            'success' => true,
            'message' => 'User updated successfully'
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error updating user: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
} 