<?php
require_once '../../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

try {
    $db = getDB();
    $lawyerId = $_POST['id'];

    // Start transaction
    $db->beginTransaction();

    // Check if lawyer has active cases
    $query = "SELECT COUNT(*) as active_cases 
              FROM lawyer_workload lw
              JOIN lawyer_profiles lp ON lw.lawyer_id = lp.id
              WHERE lp.user_id = :id AND lw.status = 'active'";
    
    $activeCases = $db->fetchOne($query, ['id' => $lawyerId])['active_cases'];

    if ($activeCases > 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'Cannot delete lawyer with active cases. Please reassign or close the cases first.'
        ]);
        exit;
    }

    // Get lawyer profile ID
    $profileId = $db->fetchOne(
        "SELECT id FROM lawyer_profiles WHERE user_id = :user_id",
        ['user_id' => $lawyerId]
    )['id'];

    // Delete related records
    $db->execute(
        "DELETE FROM lawyer_ratings WHERE lawyer_id = :lawyer_id",
        ['lawyer_id' => $profileId]
    );

    $db->execute(
        "DELETE FROM lawyer_specializations WHERE lawyer_id = :lawyer_id",
        ['lawyer_id' => $profileId]
    );

    $db->execute(
        "DELETE FROM lawyer_workload WHERE lawyer_id = :lawyer_id",
        ['lawyer_id' => $profileId]
    );

    $db->execute(
        "DELETE FROM lawyer_availability WHERE lawyer_id = :lawyer_id",
        ['lawyer_id' => $profileId]
    );

    // Delete lawyer profile
    $db->execute(
        "DELETE FROM lawyer_profiles WHERE id = :id",
        ['id' => $profileId]
    );

    // Delete user record
    $db->execute(
        "DELETE FROM users WHERE id = :id AND role = 'lawyer'",
        ['id' => $lawyerId]
    );

    // Commit transaction
    $db->commit();

    echo json_encode(['success' => true, 'message' => 'Lawyer deleted successfully']);

} catch (Exception $e) {
    // Rollback transaction on error
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 