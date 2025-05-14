<?php
require_once('../../config/database.php');
header('Content-Type: application/json');

try {
    $db = getDB();
    // Try to fetch real data, fallback to dummy if tables don't exist
    $totalUsers = 0;
    $activeCases = 0;
    $openTasks = 0;
    $pendingBills = 0;
    try {
        $totalUsers = $db->fetchOne('SELECT COUNT(*) as cnt FROM users')['cnt'] ?? 0;
    } catch (Exception $e) {}
    try {
        $activeCases = $db->fetchOne("SELECT COUNT(*) as cnt FROM cases WHERE status = 'open' OR status = 'pending'")['cnt'] ?? 0;
    } catch (Exception $e) {}
    try {
        $openTasks = $db->fetchOne("SELECT COUNT(*) as cnt FROM tasks WHERE status = 'pending' OR status = 'in_progress'")['cnt'] ?? 0;
    } catch (Exception $e) {}
    try {
        $pendingBills = $db->fetchOne("SELECT COUNT(*) as cnt FROM billing WHERE status = 'pending'")['cnt'] ?? 0;
    } catch (Exception $e) {}
    echo json_encode([
        'success' => true,
        'data' => [
            'total_users' => $totalUsers,
            'active_cases' => $activeCases,
            'open_tasks' => $openTasks,
            'pending_bills' => $pendingBills
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
} 