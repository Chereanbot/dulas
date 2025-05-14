<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

try {
    $db = getDB();
    
    if (!isset($_POST['id'])) {
        throw new Exception('Lawyer ID is required');
    }
    
    $id = (int)$_POST['id'];
    
    // Check if lawyer exists
    $check_query = "SELECT id FROM users WHERE id = :id AND role = 'lawyer'";
    $check_params = [':id' => $id];
    $exists = $db->fetchOne($check_query, $check_params);
    
    if (!$exists) {
        throw new Exception('Lawyer not found');
    }
    
    // Check if lawyer has any active cases
    $cases_query = "SELECT COUNT(*) as count FROM cases WHERE lawyer_id = :id AND status = 'active'";
    $cases_result = $db->fetchOne($cases_query, [':id' => $id]);
    
    if ($cases_result['count'] > 0) {
        throw new Exception('Cannot delete lawyer with active cases');
    }
    
    // Start transaction
    $db->beginTransaction();
    
    try {
        // Delete lawyer specialties
        $db->execute("DELETE FROM lawyer_specialties WHERE lawyer_id = :id", [':id' => $id]);
        
        // Delete lawyer
        $db->execute("DELETE FROM users WHERE id = :id AND role = 'lawyer'", [':id' => $id]);
        
        $db->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Lawyer deleted successfully'
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