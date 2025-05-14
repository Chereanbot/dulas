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
    $requiredFields = ['username', 'email', 'full_name', 'password'];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
            exit;
        }
    }

    // Extract and sanitize input
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $fullName = trim($_POST['full_name']);
    $password = $_POST['password'];
    $barNumber = $_POST['bar_number'] ?? null;
    $yearsExperience = $_POST['years_of_experience'] ?? 0;
    $hourlyRate = $_POST['hourly_rate'] ?? 0;
    $maxCases = $_POST['max_cases'] ?? 10;
    $specializations = $_POST['specializations'] ?? [];
    $education = $_POST['education'] ?? '';
    $bio = $_POST['bio'] ?? '';

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }

    // Check if username or email already exists
    $existingUser = $db->fetchOne(
        "SELECT id FROM users WHERE username = :username OR email = :email",
        ['username' => $username, 'email' => $email]
    );

    if ($existingUser) {
        echo json_encode(['success' => false, 'message' => 'Username or email already exists']);
        exit;
    }

    // Start transaction
    $db->beginTransaction();

    // Insert user record
    $query = "INSERT INTO users (username, password, email, full_name, role, status) 
              VALUES (:username, :password, :email, :full_name, 'lawyer', 'active')";
    
    $db->execute($query, [
        'username' => $username,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'email' => $email,
        'full_name' => $fullName
    ]);

    $userId = $db->lastInsertId();

    // Insert lawyer profile
    $query = "INSERT INTO lawyer_profiles (
        user_id, bar_number, years_of_experience, hourly_rate, max_cases,
        education, bio, availability_status
    ) VALUES (
        :user_id, :bar_number, :years_of_experience, :hourly_rate, :max_cases,
        :education, :bio, 'available'
    )";

    $db->execute($query, [
        'user_id' => $userId,
        'bar_number' => $barNumber,
        'years_of_experience' => $yearsExperience,
        'hourly_rate' => $hourlyRate,
        'max_cases' => $maxCases,
        'education' => $education,
        'bio' => $bio
    ]);

    $profileId = $db->lastInsertId();

    // Insert specializations
    if (!empty($specializations)) {
        $query = "INSERT INTO lawyer_specializations (lawyer_id, specialization) VALUES (:lawyer_id, :specialization)";
        foreach ($specializations as $spec) {
            $db->execute($query, [
                'lawyer_id' => $profileId,
                'specialization' => $spec
            ]);
        }
    }

    // Set default availability
    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
    $query = "INSERT INTO lawyer_availability (lawyer_id, day_of_week, start_time, end_time) 
              VALUES (:lawyer_id, :day, '09:00:00', '17:00:00')";
    
    foreach ($days as $day) {
        $db->execute($query, [
            'lawyer_id' => $profileId,
            'day' => $day
        ]);
    }

    // Commit transaction
    $db->commit();

    echo json_encode(['success' => true, 'message' => 'Lawyer added successfully']);

} catch (Exception $e) {
    // Rollback transaction on error
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 