<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

try {
    $db = getDB();
    
    // Check if cases table exists
    $check_cases = $db->fetchOne("SHOW TABLES LIKE 'cases'");
    if (!$check_cases) {
        throw new Exception("Cases table is not found in the database. Please run the database initialization script.");
    }
    
    // Check if users table exists
    $check_users = $db->fetchOne("SHOW TABLES LIKE 'users'");
    if (!$check_users) {
        throw new Exception("Users table is not found in the database. Please run the database initialization script.");
    }
    
    // Get cases that are not assigned to any lawyer
    $query = "SELECT c.id, c.title, c.status, c.assigned_lawyer_id, 
                     u.full_name as client_name
              FROM cases c 
              LEFT JOIN users u ON c.client_id = u.id 
              WHERE c.assigned_lawyer_id IS NULL 
              AND c.status IN ('PENDING', 'ACTIVE')
              ORDER BY c.created_at DESC";
    
    try {
        $cases = $db->fetchAll($query);
        
        if ($cases === false) {
            throw new Exception("No cases found in the database.");
        }
        
        // Format the response data
        $formatted_cases = array_map(function($case) {
            return [
                'id' => $case['id'],
                'title' => $case['title'],
                'client_name' => $case['client_name'] ?? 'Unknown Client',
                'status' => $case['status']
            ];
        }, $cases);
        
        echo json_encode([
            'success' => true,
            'cases' => $formatted_cases,
            'count' => count($cases)
        ]);
        
    } catch (Exception $e) {
        throw new Exception("Error fetching cases: " . $e->getMessage());
    }
    
} catch (Exception $e) {
    error_log("Error in get_available_cases.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error_type' => 'database',
        'title' => 'Database Error',
        'message' => $e->getMessage(),
        'details' => 'Please check if the database is properly configured and all required tables exist.'
    ]);
} 