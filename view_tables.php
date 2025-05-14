<?php
// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'dulas';

try {
    // Create connection
    $conn = new mysqli($host, $username, $password, $database);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Get all tables
    $tables = array();
    $result = $conn->query("SHOW TABLES");
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }

    // HTML header
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Database Table Structures</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 20px;
                background-color: #f5f5f5;
            }
            .table-container {
                margin-bottom: 30px;
                background-color: white;
                padding: 20px;
                border-radius: 5px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            h2 {
                color: #2c3e50;
                border-bottom: 2px solid #3498db;
                padding-bottom: 10px;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 10px;
            }
            th, td {
                padding: 12px;
                text-align: left;
                border-bottom: 1px solid #ddd;
            }
            th {
                background-color: #f8f9fa;
                color: #2c3e50;
            }
            tr:hover {
                background-color: #f5f5f5;
            }
            .field-name {
                color: #2980b9;
                font-weight: bold;
            }
            .field-type {
                color: #27ae60;
            }
            .field-null {
                color: #e74c3c;
            }
            .field-key {
                color: #8e44ad;
            }
            .field-default {
                color: #f39c12;
            }
            .field-extra {
                color: #7f8c8d;
            }
        </style>
    </head>
    <body>
        <h1>Database Table Structures</h1>";

    // For each table, get its structure
    foreach ($tables as $table) {
        echo "<div class='table-container'>";
        echo "<h2>Table: $table</h2>";
        
        // Get table structure
        $result = $conn->query("DESCRIBE $table");
        
        echo "<table>";
        echo "<tr>
                <th>Field</th>
                <th>Type</th>
                <th>Null</th>
                <th>Key</th>
                <th>Default</th>
                <th>Extra</th>
              </tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td class='field-name'>" . $row['Field'] . "</td>";
            echo "<td class='field-type'>" . $row['Type'] . "</td>";
            echo "<td class='field-null'>" . $row['Null'] . "</td>";
            echo "<td class='field-key'>" . $row['Key'] . "</td>";
            echo "<td class='field-default'>" . ($row['Default'] ?? 'NULL') . "</td>";
            echo "<td class='field-extra'>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        // Get foreign keys
        $result = $conn->query("
            SELECT 
                COLUMN_NAME,
                REFERENCED_TABLE_NAME,
                REFERENCED_COLUMN_NAME
            FROM
                INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE
                TABLE_SCHEMA = '$database'
                AND TABLE_NAME = '$table'
                AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        if ($result->num_rows > 0) {
            echo "<h3>Foreign Keys:</h3>";
            echo "<table>";
            echo "<tr>
                    <th>Column</th>
                    <th>References Table</th>
                    <th>References Column</th>
                  </tr>";
            
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['COLUMN_NAME'] . "</td>";
                echo "<td>" . $row['REFERENCED_TABLE_NAME'] . "</td>";
                echo "<td>" . $row['REFERENCED_COLUMN_NAME'] . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        }
        
        echo "</div>";
    }

    echo "</body></html>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?> 