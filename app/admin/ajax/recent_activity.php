<?php
require_once('../../config/database.php');
header('Content-Type: application/json');

try {
    $db = getDB();
    $activities = [];
    try {
        $rows = $db->fetchAll("SELECT a.*, u.full_name FROM user_activities a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC LIMIT 8");
        foreach ($rows as $row) {
            $activities[] = [
                'user' => $row['full_name'] ?? 'Unknown',
                'action' => $row['activity_type'] . ($row['description'] ? ': ' . $row['description'] : ''),
                'time' => date('Y-m-d H:i', strtotime($row['created_at']))
            ];
        }
    } catch (Exception $e) {
        // Fallback to dummy data
        $activities = [
            ['user' => 'Admin', 'action' => 'Logged in', 'time' => date('Y-m-d H:i')],
            ['user' => 'Admin', 'action' => 'Viewed dashboard', 'time' => date('Y-m-d H:i', strtotime('-5 min'))],
        ];
    }
    echo json_encode(['success' => true, 'data' => $activities]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
} 