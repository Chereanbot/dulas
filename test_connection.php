<?php
require_once 'app/config/database.php';

try {
    $database = new Database();
    
    // Test the connection
    if ($database->testConnection()) {
        echo "Database connection successful!<br>";
        
        // Try to get a connection and test a simple query
        $conn = $database->getConnection();
        $stmt = $conn->query("SELECT VERSION() as version");
        $result = $stmt->fetch();
        
        echo "MySQL Version: " . $result['version'] . "<br>";
        echo "Database name: dulas_db<br>";
        echo "Connection status: Connected<br>";
        
        // Test if we can create tables
        echo "<br>Testing table creation permissions...<br>";
        try {
            $conn->exec("CREATE TABLE IF NOT EXISTS test_table (id INT)");
            echo "Table creation test: Successful<br>";
            $conn->exec("DROP TABLE IF EXISTS test_table");
        } catch (PDOException $e) {
            echo "Table creation test: Failed - " . $e->getMessage() . "<br>";
        }
    } else {
        echo "Database connection failed!<br>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
    
    // Additional troubleshooting information
    echo "<br>Troubleshooting Steps:<br>";
    echo "1. Check if MySQL server is running<br>";
    echo "2. Verify database credentials in app/config/database.php<br>";
    echo "3. Make sure the database 'dulas_db' exists<br>";
    echo "4. Check if PHP PDO MySQL extension is installed<br>";
    
    // Check PHP configuration
    echo "<br>PHP Configuration:<br>";
    echo "PHP Version: " . phpversion() . "<br>";
    echo "PDO Drivers: " . implode(", ", PDO::getAvailableDrivers()) . "<br>";
    echo "MySQL Extension: " . (extension_loaded('pdo_mysql') ? 'Loaded' : 'Not Loaded') . "<br>";
    
    // Check MySQL connection without database
    try {
        $testConn = new PDO("mysql:host=localhost", "root", "");
        echo "MySQL server is running and accessible<br>";
    } catch (PDOException $e) {
        echo "MySQL server connection failed: " . $e->getMessage() . "<br>";
    }
}
?> 