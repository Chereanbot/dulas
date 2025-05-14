<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

try {
    $db = getDB();
    
    if (!isset($_GET['id'])) {
        throw new Exception('Lawyer ID is required');
    }
    
    $id = (int)$_GET['id'];
    
    $query = "SELECT 
        u.*, 
        o.name as office_name,
        (SELECT COUNT(*) FROM cases c WHERE c.lawyer_id = u.id) as total_cases,
        (SELECT COUNT(*) FROM cases c WHERE c.lawyer_id = u.id AND c.status = 'active') as active_cases,
        (SELECT GROUP_CONCAT(s.name) FROM lawyer_specialties ls 
         JOIN specialties s ON ls.specialty_id = s.id 
         WHERE ls.lawyer_id = u.id) as specialties
        FROM users u 
        LEFT JOIN offices o ON u.organization_id = o.id 
        WHERE u.id = :id AND u.role = 'lawyer'";
              
    $params = [':id' => $id];
    $lawyer = $db->fetchOne($query, $params);
    
    if (!$lawyer) {
        throw new Exception('Lawyer not found');
    }
    
    echo json_encode([
        'success' => true,
        'data' => $lawyer
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 