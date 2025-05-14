<?php
require_once '../../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$lawyer_id = $_POST['lawyer_id'] ?? null;
$specializations = $_POST['specializations'] ?? [];

if (!$lawyer_id) {
    echo json_encode(['success' => false, 'message' => 'Missing lawyer ID']);
    exit;
}

try {
    $db = getDB();
    
    // Get lawyer profile ID
    $profile_query = "SELECT id FROM lawyer_profiles WHERE user_id = ?";
    $profile = $db->fetchOne($profile_query, [$lawyer_id]);
    
    if (!$profile) {
        echo json_encode(['success' => false, 'message' => 'Lawyer profile not found']);
        exit;
    }
    
    // Start transaction
    $db->beginTransaction();
    
    try {
        // Remove existing specializations
        $delete_query = "DELETE FROM lawyer_specializations WHERE lawyer_id = ?";
        $db->execute($delete_query, [$profile['id']]);
        
        // Add new specializations
        if (!empty($specializations)) {
            $insert_query = "INSERT INTO lawyer_specializations (lawyer_id, specialization) VALUES (?, ?)";
            foreach ($specializations as $specialization) {
                $db->execute($insert_query, [$profile['id'], $specialization]);
            }
        }
        
        $db->commit();
        echo json_encode(['success' => true, 'message' => 'Specializations updated successfully']);
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
} 