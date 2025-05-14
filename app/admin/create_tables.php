<?php
require_once '../config/database.php';

try {
    $db = getDB();
    
    echo "<h2>Creating Missing Tables</h2>";
    
    // Create clients table
    echo "<h3>Creating Clients Table</h3>";
    try {
        $query = "CREATE TABLE IF NOT EXISTS clients (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE,
            phone VARCHAR(50),
            address TEXT,
            type ENUM('individual', 'corporate') DEFAULT 'individual',
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $db->execute($query);
        echo "Clients table created successfully<br>";
    } catch (Exception $e) {
        echo "Error creating clients table: " . $e->getMessage() . "<br>";
    }

    // Create paralegals table
    echo "<h3>Creating Paralegals Table</h3>";
    try {
        $query = "CREATE TABLE IF NOT EXISTS paralegals (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            specialization VARCHAR(100),
            experience_years INT,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )";
        $db->execute($query);
        echo "Paralegals table created successfully<br>";
    } catch (Exception $e) {
        echo "Error creating paralegals table: " . $e->getMessage() . "<br>";
    }

    // Create case_assignments table
    echo "<h3>Creating Case Assignments Table</h3>";
    try {
        $query = "CREATE TABLE IF NOT EXISTS case_assignments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            case_id INT NOT NULL,
            lawyer_id INT NOT NULL,
            paralegal_id INT,
            assigned_by INT NOT NULL,
            assigned_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            priority ENUM('LOW', 'MEDIUM', 'HIGH', 'URGENT') DEFAULT 'MEDIUM',
            status ENUM('pending', 'active', 'completed', 'rejected') DEFAULT 'pending',
            notes TEXT,
            deadline DATE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
            FOREIGN KEY (lawyer_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (paralegal_id) REFERENCES paralegals(id) ON DELETE SET NULL,
            FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE CASCADE
        )";
        $db->execute($query);
        echo "Case assignments table created successfully<br>";
    } catch (Exception $e) {
        echo "Error creating case_assignments table: " . $e->getMessage() . "<br>";
    }

    // Create cases table with updated structure
    echo "<h3>Creating Cases Table</h3>";
    try {
        $query = "CREATE TABLE IF NOT EXISTS cases (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            case_number VARCHAR(50) UNIQUE,
            type VARCHAR(100) NOT NULL,
            status ENUM('pending', 'active', 'on_hold', 'closed', 'won', 'lost') DEFAULT 'pending',
            priority ENUM('LOW', 'MEDIUM', 'HIGH', 'URGENT') DEFAULT 'MEDIUM',
            client_id INT NOT NULL,
            assigned_lawyer_id INT,
            assigned_paralegal_id INT,
            court_name VARCHAR(255),
            court_location VARCHAR(255),
            filing_date DATE,
            next_hearing_date DATE,
            estimated_completion_date DATE,
            actual_completion_date DATE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
            FOREIGN KEY (assigned_lawyer_id) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY (assigned_paralegal_id) REFERENCES paralegals(id) ON DELETE SET NULL
        )";
        $db->execute($query);
        echo "Cases table created successfully<br>";
    } catch (Exception $e) {
        echo "Error creating cases table: " . $e->getMessage() . "<br>";
    }

    // Create case_documents table
    echo "<h3>Creating Case Documents Table</h3>";
    try {
        $query = "CREATE TABLE IF NOT EXISTS case_documents (
            id INT AUTO_INCREMENT PRIMARY KEY,
            case_id INT NOT NULL,
            document_name VARCHAR(255) NOT NULL,
            document_type VARCHAR(100),
            file_path VARCHAR(255) NOT NULL,
            uploaded_by INT NOT NULL,
            upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            description TEXT,
            status ENUM('active', 'archived') DEFAULT 'active',
            FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
            FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE
        )";
        $db->execute($query);
        echo "Case documents table created successfully<br>";
    } catch (Exception $e) {
        echo "Error creating case_documents table: " . $e->getMessage() . "<br>";
    }

    // Create case_notes table
    echo "<h3>Creating Case Notes Table</h3>";
    try {
        $query = "CREATE TABLE IF NOT EXISTS case_notes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            case_id INT NOT NULL,
            user_id INT NOT NULL,
            note_text TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )";
        $db->execute($query);
        echo "Case notes table created successfully<br>";
    } catch (Exception $e) {
        echo "Error creating case_notes table: " . $e->getMessage() . "<br>";
    }

    // Verify tables were created
    echo "<h3>Verifying Tables</h3>";
    try {
        $tables = [
            'clients',
            'paralegals',
            'case_assignments',
            'cases',
            'case_documents',
            'case_notes'
        ];
        
        foreach ($tables as $table) {
            $query = "SHOW TABLES LIKE '$table'";
            $result = $db->fetchOne($query);
            echo "$table table exists: " . ($result ? "Yes" : "No") . "<br>";
            
            $query = "SELECT COUNT(*) as count FROM $table";
            $result = $db->fetchOne($query);
            echo "Number of records in $table: " . $result['count'] . "<br>";
        }
        
    } catch (Exception $e) {
        echo "Error verifying tables: " . $e->getMessage() . "<br>";
    }
    
} catch (Exception $e) {
    echo "Database Error: " . $e->getMessage();
}
?> 