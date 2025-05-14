<?php
require_once '../../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET' || !isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

try {
    $db = getDB();
    $lawyerId = $_GET['id'];

    // Get lawyer details with profile
    $query = "SELECT 
        u.id, u.username, u.email, u.full_name, u.status,
        lp.bar_number, lp.years_of_experience, lp.hourly_rate,
        lp.current_cases, lp.max_cases, lp.availability_status,
        lp.education, lp.bio
    FROM users u
    LEFT JOIN lawyer_profiles lp ON u.id = lp.user_id
    WHERE u.id = :id AND u.role = 'lawyer'";

    $lawyer = $db->fetchOne($query, ['id' => $lawyerId]);

    if (!$lawyer) {
        echo json_encode(['success' => false, 'message' => 'Lawyer not found']);
        exit;
    }

    // Get specializations
    $query = "SELECT specialization, years_experience, certification 
              FROM lawyer_specializations 
              WHERE lawyer_id = (SELECT id FROM lawyer_profiles WHERE user_id = :id)";
    $specializations = $db->fetchAll($query, ['id' => $lawyerId]);

    // Get active cases
    $query = "SELECT 
        c.case_number, c.title, c.status,
        lw.priority, lw.hours_spent,
        lw.estimated_completion_date
    FROM cases c
    JOIN lawyer_workload lw ON c.id = lw.case_id
    JOIN lawyer_profiles lp ON lw.lawyer_id = lp.id
    WHERE lp.user_id = :id AND lw.status = 'active'";
    $activeCases = $db->fetchAll($query, ['id' => $lawyerId]);

    // Get ratings
    $query = "SELECT AVG(rating) as average_rating, COUNT(*) as total_ratings
              FROM lawyer_ratings lr
              JOIN lawyer_profiles lp ON lr.lawyer_id = lp.id
              WHERE lp.user_id = :id";
    $ratings = $db->fetchOne($query, ['id' => $lawyerId]);

    // Combine all data
    $lawyer['specializations'] = $specializations;
    $lawyer['active_cases'] = $activeCases;
    $lawyer['ratings'] = $ratings;

    echo json_encode(['success' => true, 'data' => $lawyer]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 