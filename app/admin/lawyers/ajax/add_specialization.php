<?php
require_once '../../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$lawyer_id = $_POST['lawyer_id'] ?? null;
$specialization = $_POST['specialization'] ?? null;

if (!$lawyer_id || !$specialization) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    $db = getDB();
    
    // Start transaction
    $db->beginTransaction();
    
    try {
        // First check if the user is a lawyer
        $user_query = "SELECT id, full_name FROM users WHERE id = ? AND role = 'lawyer' AND status = 'active'";
        $user = $db->fetchOne($user_query, [$lawyer_id]);
        
        if (!$user) {
            throw new Exception('Invalid lawyer or lawyer not found');
        }
        
        // Get or create lawyer profile
        $profile_query = "SELECT id FROM lawyer_profiles WHERE user_id = ?";
        $profile = $db->fetchOne($profile_query, [$lawyer_id]);
        
        if (!$profile) {
            // Create lawyer profile if it doesn't exist
            $insert_profile = "INSERT INTO lawyer_profiles (user_id, max_cases, current_cases) VALUES (?, 10, 0)";
            $db->execute($insert_profile, [$lawyer_id]);
            $profile_id = $db->lastInsertId();
        } else {
            $profile_id = $profile['id'];
        }
        
        // Check if specialization already exists for this lawyer
        $check_query = "SELECT id FROM lawyer_specializations 
                       WHERE lawyer_id = ? AND specialization = ?";
        $exists = $db->fetchOne($check_query, [$profile_id, $specialization]);
        
        if ($exists) {
            throw new Exception('Specialization already exists for this lawyer');
        }
        
        // Add specialization
        $insert_query = "INSERT INTO lawyer_specializations (lawyer_id, specialization) 
                        VALUES (?, ?)";
        $db->execute($insert_query, [$profile_id, $specialization]);
        
        // Log the activity
        $activity_query = "INSERT INTO user_activities (user_id, activity_type, description) 
                          VALUES (?, 'add_specialization', ?)";
        $db->execute($activity_query, [
            $lawyer_id,
            "Added specialization: " . $specialization
        ]);
        
        $db->commit();
        echo json_encode([
            'success' => true, 
            'message' => 'Specialization added successfully',
            'lawyer_name' => $user['full_name'],
            'specialization' => $specialization
        ]);
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
} 