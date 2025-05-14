<?php
require_once '../../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$lawyer_id = $_POST['lawyer_id'] ?? null;
$case_id = $_POST['case_id'] ?? null;

if (!$lawyer_id || !$case_id) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    $db = getDB();
    
    // Start transaction
    $db->beginTransaction();
    
    try {
        // Get lawyer profile ID
        $profile_query = "SELECT id, max_cases, current_cases FROM lawyer_profiles WHERE user_id = ?";
        $profile = $db->fetchOne($profile_query, [$lawyer_id]);
        
        if (!$profile) {
            throw new Exception('Lawyer profile not found');
        }
        
        // Check if lawyer has reached max cases
        if ($profile['current_cases'] >= $profile['max_cases']) {
            throw new Exception('Lawyer has reached maximum case limit');
        }
        
        // Check if case is already assigned
        $check_query = "SELECT id FROM lawyer_workload 
                       WHERE case_id = ? AND status = 'active'";
        $exists = $db->fetchOne($check_query, [$case_id]);
        
        if ($exists) {
            throw new Exception('Case is already assigned to a lawyer');
        }
        
        // Assign case
        $insert_query = "INSERT INTO lawyer_workload (lawyer_id, case_id, status, assigned_at) 
                        VALUES (?, ?, 'active', NOW())";
        $db->execute($insert_query, [$profile['id'], $case_id]);
        
        // Update lawyer's current cases count
        $update_query = "UPDATE lawyer_profiles 
                        SET current_cases = current_cases + 1 
                        WHERE id = ?";
        $db->execute($update_query, [$profile['id']]);
        
        $db->commit();
        echo json_encode(['success' => true, 'message' => 'Case assigned successfully']);
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
} 