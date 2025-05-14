<?php
require_once '../../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $db = getDB();
    
    // Validate required fields
    $requiredFields = ['id', 'full_name', 'email'];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
            exit;
        }
    }

    $lawyerId = $_POST['id'];
    $fullName = $_POST['full_name'];
    $email = $_POST['email'];
    $barNumber = $_POST['bar_number'] ?? null;
    $yearsExperience = $_POST['years_of_experience'] ?? 0;
    $hourlyRate = $_POST['hourly_rate'] ?? 0;
    $maxCases = $_POST['max_cases'] ?? 10;
    $status = $_POST['status'] ?? 'active';
    $specializations = $_POST['specializations'] ?? [];

    // Start transaction
    $db->beginTransaction();

    // Update user details
    $query = "UPDATE users SET 
        full_name = :full_name,
        email = :email,
        status = :status
        WHERE id = :id AND role = 'lawyer'";
    
    $db->execute($query, [
        'full_name' => $fullName,
        'email' => $email,
        'status' => $status,
        'id' => $lawyerId
    ]);

    // Update or insert lawyer profile
    $query = "INSERT INTO lawyer_profiles (
        user_id, bar_number, years_of_experience, hourly_rate, max_cases
    ) VALUES (
        :user_id, :bar_number, :years_of_experience, :hourly_rate, :max_cases
    ) ON DUPLICATE KEY UPDATE
        bar_number = VALUES(bar_number),
        years_of_experience = VALUES(years_of_experience),
        hourly_rate = VALUES(hourly_rate),
        max_cases = VALUES(max_cases)";

    $db->execute($query, [
        'user_id' => $lawyerId,
        'bar_number' => $barNumber,
        'years_of_experience' => $yearsExperience,
        'hourly_rate' => $hourlyRate,
        'max_cases' => $maxCases
    ]);

    // Get lawyer profile ID
    $profileId = $db->fetchOne(
        "SELECT id FROM lawyer_profiles WHERE user_id = :user_id",
        ['user_id' => $lawyerId]
    )['id'];

    // Update specializations
    if (!empty($specializations)) {
        // Delete existing specializations
        $db->execute(
            "DELETE FROM lawyer_specializations WHERE lawyer_id = :lawyer_id",
            ['lawyer_id' => $profileId]
        );

        // Insert new specializations
        $query = "INSERT INTO lawyer_specializations (lawyer_id, specialization) VALUES (:lawyer_id, :specialization)";
        foreach ($specializations as $spec) {
            $db->execute($query, [
                'lawyer_id' => $profileId,
                'specialization' => $spec
            ]);
        }
    }

    // Commit transaction
    $db->commit();

    echo json_encode(['success' => true, 'message' => 'Lawyer updated successfully']);

} catch (Exception $e) {
    // Rollback transaction on error
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 