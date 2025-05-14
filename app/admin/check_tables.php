<?php
require_once '../config/database.php';

try {
    $db = getDB();
    
    echo "<h2>Database Tables Check</h2>";
    
    // Check users table
    echo "<h3>Users Table</h3>";
    try {
        $query = "SELECT COUNT(*) as count FROM users WHERE role = 'lawyer'";
        $result = $db->fetchOne($query);
        echo "Total Lawyers: " . $result['count'] . "<br>";
        
        $query = "DESCRIBE users";
        $columns = $db->fetchAll($query);
        echo "<pre>Table Structure:\n";
        print_r($columns);
        echo "</pre>";
    } catch (Exception $e) {
        echo "Error with users table: " . $e->getMessage() . "<br>";
    }
    
    // Check offices table
    echo "<h3>Offices Table</h3>";
    try {
        $query = "SELECT COUNT(*) as count FROM offices";
        $result = $db->fetchOne($query);
        echo "Total Offices: " . $result['count'] . "<br>";
        
        $query = "DESCRIBE offices";
        $columns = $db->fetchAll($query);
        echo "<pre>Table Structure:\n";
        print_r($columns);
        echo "</pre>";
    } catch (Exception $e) {
        echo "Error with offices table: " . $e->getMessage() . "<br>";
    }
    
    // Check cases table
    echo "<h3>Cases Table</h3>";
    try {
        $query = "SELECT COUNT(*) as count FROM cases";
        $result = $db->fetchOne($query);
        echo "Total Cases: " . $result['count'] . "<br>";
        
        $query = "DESCRIBE cases";
        $columns = $db->fetchAll($query);
        echo "<pre>Table Structure:\n";
        print_r($columns);
        echo "</pre>";
    } catch (Exception $e) {
        echo "Error with cases table: " . $e->getMessage() . "<br>";
    }
    
    // Check lawyer_specialties table
    echo "<h3>Lawyer Specialties Table</h3>";
    try {
        $query = "SELECT COUNT(*) as count FROM lawyer_specialties";
        $result = $db->fetchOne($query);
        echo "Total Lawyer Specialties: " . $result['count'] . "<br>";
        
        $query = "DESCRIBE lawyer_specialties";
        $columns = $db->fetchAll($query);
        echo "<pre>Table Structure:\n";
        print_r($columns);
        echo "</pre>";
    } catch (Exception $e) {
        echo "Error with lawyer_specialties table: " . $e->getMessage() . "<br>";
    }
    
    // Check specialties table
    echo "<h3>Specialties Table</h3>";
    try {
        $query = "SELECT COUNT(*) as count FROM specialties";
        $result = $db->fetchOne($query);
        echo "Total Specialties: " . $result['count'] . "<br>";
        
        $query = "DESCRIBE specialties";
        $columns = $db->fetchAll($query);
        echo "<pre>Table Structure:\n";
        print_r($columns);
        echo "</pre>";
    } catch (Exception $e) {
        echo "Error with specialties table: " . $e->getMessage() . "<br>";
    }
    
    // Check the main query that's failing
    echo "<h3>Testing Main Query</h3>";
    try {
        $query = "SELECT 
            u.*, 
            o.name as office_name,
            (SELECT COUNT(*) FROM cases c WHERE c.lawyer_id = u.id) as total_cases,
            (SELECT COUNT(*) FROM cases c WHERE c.lawyer_id = u.id AND c.status = 'active') as active_cases,
            (SELECT GROUP_CONCAT(s.name SEPARATOR ',') FROM lawyer_specialties ls 
             JOIN specialties s ON ls.specialty_id = s.id 
             WHERE ls.lawyer_id = u.id) as specialties
            FROM users u 
            LEFT JOIN offices o ON u.organization_id = o.id 
            WHERE u.role = 'lawyer'
            LIMIT 1";
            
        $result = $db->fetchOne($query);
        echo "<pre>Query Result:\n";
        print_r($result);
        echo "</pre>";
    } catch (Exception $e) {
        echo "Error with main query: " . $e->getMessage() . "<br>";
    }
    
} catch (Exception $e) {
    echo "Database Error: " . $e->getMessage();
}
?> 