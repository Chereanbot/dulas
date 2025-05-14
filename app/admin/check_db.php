<?php
require_once '../config/database.php';

try {
    $db = getDB();
    
    echo "<h2>Database Connection Test</h2>";
    echo "Connection successful!<br><br>";
    
    echo "<h2>Checking Required Tables</h2>";
    
    $required_tables = [
        'cases',
        'clients',
        'users',
        'paralegals',
        'case_assignments',
        'case_documents',
        'case_notes',
        'notifications'
    ];
    
    foreach ($required_tables as $table) {
        echo "<h3>Checking table: $table</h3>";
        
        // Check if table exists
        $query = "SHOW TABLES LIKE '$table'";
        $result = $db->fetchOne($query);
        
        if ($result) {
            echo "Table exists: Yes<br>";
            
            // Get table structure
            $query = "DESCRIBE $table";
            $columns = $db->fetchAll($query);
            
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
            
            foreach ($columns as $column) {
                echo "<tr>";
                echo "<td>" . $column['Field'] . "</td>";
                echo "<td>" . $column['Type'] . "</td>";
                echo "<td>" . $column['Null'] . "</td>";
                echo "<td>" . $column['Key'] . "</td>";
                echo "<td>" . $column['Default'] . "</td>";
                echo "<td>" . $column['Extra'] . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
            
            // Get row count
            $query = "SELECT COUNT(*) as count FROM $table";
            $result = $db->fetchOne($query);
            echo "Number of records: " . $result['count'] . "<br>";
            
        } else {
            echo "Table exists: No<br>";
            echo "This table needs to be created.<br>";
        }
        
        echo "<hr>";
    }
    
} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "Error message: " . $e->getMessage() . "<br>";
    echo "Stack trace:<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} 