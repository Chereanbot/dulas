<?php
require_once '../../config/database.php';
header('Content-Type: application/json');

try {
    $db = getDB();
    
    // Validate required fields
    $required_fields = ['title', 'case_number', 'type', 'client_id'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    // Check if case number already exists
    $check_query = "SELECT id FROM cases WHERE case_number = :case_number";
    $existing_case = $db->fetchOne($check_query, [':case_number' => $_POST['case_number']]);
    if ($existing_case) {
        throw new Exception("A case with this case number already exists.");
    }
    
    // Start transaction
    $db->beginTransaction();
    
    // Insert case
    $query = "INSERT INTO cases (
        title, case_number, type, client_id, description,
        court_name, court_location, filing_date, next_hearing_date,
        status, priority, created_at
    ) VALUES (
        :title, :case_number, :type, :client_id, :description,
        :court_name, :court_location, :filing_date, :next_hearing_date,
        'pending', 'MEDIUM', CURRENT_TIMESTAMP
    )";
    
    $params = [
        ':title' => $_POST['title'],
        ':case_number' => $_POST['case_number'],
        ':type' => $_POST['type'],
        ':client_id' => $_POST['client_id'],
        ':description' => $_POST['description'] ?? null,
        ':court_name' => $_POST['court_name'] ?? null,
        ':court_location' => $_POST['court_location'] ?? null,
        ':filing_date' => $_POST['filing_date'] ?? null,
        ':next_hearing_date' => $_POST['next_hearing_date'] ?? null
    ];
    
    $db->execute($query, $params);
    $case_id = $db->lastInsertId();
    
    // Create initial case note
    if (!empty($_POST['description'])) {
        $note_query = "INSERT INTO case_notes (case_id, user_id, note_text) VALUES (:case_id, :user_id, :note_text)";
        $note_params = [
            ':case_id' => $case_id,
            ':user_id' => $_SESSION['user_id'],
            ':note_text' => "Case created: " . $_POST['description']
        ];
        $db->execute($note_query, $note_params);
    }
    
    // Commit transaction
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Case created successfully',
        'case_id' => $case_id
    ]);
    
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 