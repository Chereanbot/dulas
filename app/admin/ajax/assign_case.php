<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

try {
    $db = getDB();
    
    // Validate required fields
    $required_fields = ['case_id', 'assigned_lawyer_id', 'priority'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    // Start transaction
    $db->beginTransaction();
    
    // Update case assignment
    $query = "UPDATE cases SET 
        assigned_lawyer_id = :lawyer_id,
        assigned_paralegal_id = :paralegal_id,
        priority = :priority,
        status = 'active',
        updated_at = CURRENT_TIMESTAMP
        WHERE id = :case_id";
    
    $params = [
        ':lawyer_id' => $_POST['assigned_lawyer_id'],
        ':paralegal_id' => $_POST['assigned_paralegal_id'] ?? null,
        ':priority' => $_POST['priority'],
        ':case_id' => $_POST['case_id']
    ];
    
    $db->execute($query, $params);
    
    // Create case assignment record
    $assignment_query = "INSERT INTO case_assignments (
        case_id, lawyer_id, paralegal_id, assigned_by,
        assigned_date, priority, status, notes
    ) VALUES (
        :case_id, :lawyer_id, :paralegal_id, :assigned_by,
        CURRENT_TIMESTAMP, :priority, 'active', :notes
    )";
    
    $assignment_params = [
        ':case_id' => $_POST['case_id'],
        ':lawyer_id' => $_POST['assigned_lawyer_id'],
        ':paralegal_id' => $_POST['assigned_paralegal_id'] ?? null,
        ':assigned_by' => $_SESSION['user_id'],
        ':priority' => $_POST['priority'],
        ':notes' => $_POST['notes'] ?? null
    ];
    
    $db->execute($assignment_query, $assignment_params);
    
    // Create notification for assigned lawyer
    $notification_query = "INSERT INTO notifications (
        user_id, title, message, type, reference_id, reference_type
    ) VALUES (
        :user_id, :title, :message, 'case_assignment', :case_id, 'case'
    )";
    
    $notification_params = [
        ':user_id' => $_POST['assigned_lawyer_id'],
        ':title' => 'New Case Assignment',
        ':message' => "You have been assigned to case #{$_POST['case_id']} with {$params['priority']} priority.",
        ':case_id' => $_POST['case_id']
    ];
    
    $db->execute($notification_query, $notification_params);
    
    // If paralegal is assigned, create notification for them too
    if (!empty($_POST['assigned_paralegal_id'])) {
        $notification_params[':user_id'] = $_POST['assigned_paralegal_id'];
        $notification_params[':message'] = "You have been assigned as paralegal to case #{$_POST['case_id']}.";
        $db->execute($notification_query, $notification_params);
    }
    
    // Add case note about assignment
    $note_query = "INSERT INTO case_notes (
        case_id, user_id, note_text
    ) VALUES (
        :case_id, :user_id, :note_text
    )";
    
    $note_params = [
        ':case_id' => $_POST['case_id'],
        ':user_id' => $_SESSION['user_id'],
        ':note_text' => "Case assigned to lawyer ID: {$_POST['assigned_lawyer_id']}" . 
                       (!empty($_POST['assigned_paralegal_id']) ? " and paralegal ID: {$_POST['assigned_paralegal_id']}" : "") .
                       " with priority: {$_POST['priority']}" .
                       (!empty($_POST['notes']) ? "\nNotes: {$_POST['notes']}" : "")
    ];
    
    $db->execute($note_query, $note_params);
    
    // Commit transaction
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Case assigned successfully'
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