<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

try {
    $db = getDB();
    
    // Check database connection
    $connection_check = $db->fetchOne("SELECT 1");
    if (!$connection_check) {
        throw new Exception("Database connection failed");
    }
    
    // Check tables
    $tables = [
        'cases' => "SHOW TABLES LIKE 'cases'",
        'users' => "SHOW TABLES LIKE 'users'"
    ];
    
    $table_status = [];
    foreach ($tables as $table => $query) {
        $result = $db->fetchOne($query);
        $table_status[$table] = $result ? true : false;
    }
    
    // Check case table structure
    $case_columns = [];
    if ($table_status['cases']) {
        $columns = $db->fetchAll("SHOW COLUMNS FROM cases");
        foreach ($columns as $column) {
            $case_columns[] = $column['Field'];
        }
    }
    
    echo json_encode([
        'success' => true,
        'connection' => true,
        'tables' => $table_status,
        'case_columns' => $case_columns
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
} 