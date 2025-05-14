<?php
require_once '../../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$lawyer_id = $_POST['lawyer_id'] ?? null;
$case_title = $_POST['case_title'] ?? null;

if (!$lawyer_id || !$case_title) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    $db = getDB();
    
    // Start transaction
    $db->beginTransaction();
    
    try {
        // Get lawyer profile ID
        $profile_query = "SELECT id FROM lawyer_profiles WHERE user_id = ?";
        $profile = $db->fetchOne($profile_query, [$lawyer_id]);
        
        if (!$profile) {
            throw new Exception('Lawyer profile not found');
        }
        
        // Get case ID
        $case_query = "SELECT id FROM cases WHERE title = ?";
        $case = $db->fetchOne($case_query, [$case_title]);
        
        if (!$case) {
            throw new Exception('Case not found');
        }
        
        // Remove case assignment
        $delete_query = "DELETE FROM lawyer_workload 
                        WHERE lawyer_id = ? AND case_id = ? AND status = 'active'";
        $result = $db->execute($delete_query, [$profile['id'], $case['id']]);
        
        if ($result->rowCount() === 0) {
            throw new Exception('Case assignment not found');
        }
        
        // Update lawyer's current cases count
        $update_query = "UPDATE lawyer_profiles 
                        SET current_cases = current_cases - 1 
                        WHERE id = ?";
        $db->execute($update_query, [$profile['id']]);
        
        $db->commit();
        echo json_encode(['success' => true, 'message' => 'Case removed successfully']);
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
} 