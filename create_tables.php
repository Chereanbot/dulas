<?php
require_once 'app/config/database.php';

try {
    $db = getDB();
    
    echo "<h2>Creating Legal Case Management System Tables</h2>";
    
    // Create users table
    echo "<h3>Creating Users Table</h3>";
    try {
        $query = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) UNIQUE NOT NULL,
            phone VARCHAR(50) UNIQUE,
            password VARCHAR(255) NOT NULL,
            plain_password VARCHAR(255),
            full_name VARCHAR(255) NOT NULL,
            username VARCHAR(255) UNIQUE,
            email_verified BOOLEAN DEFAULT FALSE,
            phone_verified BOOLEAN DEFAULT FALSE,
            user_role ENUM('SUPER_ADMIN', 'ADMIN', 'LAWYER', 'COORDINATOR', 'CLIENT') DEFAULT 'CLIENT',
            status ENUM('ACTIVE', 'INACTIVE', 'SUSPENDED', 'BANNED') DEFAULT 'ACTIVE',
            is_admin BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $db->execute($query);
        echo "Users table created successfully<br>";
    } catch (Exception $e) {
        echo "Error creating users table: " . $e->getMessage() . "<br>";
    }

    // Create client_profiles table
    echo "<h3>Creating Client Profiles Table</h3>";
    try {
        $query = "CREATE TABLE IF NOT EXISTS client_profiles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL UNIQUE,
            age INT NOT NULL,
            sex ENUM('MALE', 'FEMALE', 'OTHER') NOT NULL,
            phone VARCHAR(50) NOT NULL,
            number_of_family INT NOT NULL,
            health_status ENUM('HEALTHY', 'DISABLED', 'CHRONIC_ILLNESS', 'OTHER') NOT NULL,
            region VARCHAR(255) NOT NULL,
            zone VARCHAR(255) NOT NULL,
            wereda VARCHAR(255) NOT NULL,
            kebele VARCHAR(255) NOT NULL,
            house_number VARCHAR(50),
            case_type ENUM('CIVIL', 'CRIMINAL', 'FAMILY', 'PROPERTY', 'LABOR', 'COMMERCIAL', 'CONSTITUTIONAL', 'ADMINISTRATIVE', 'OTHER') NOT NULL,
            case_category ENUM('FAMILY', 'CRIMINAL', 'CIVIL', 'PROPERTY', 'LABOR', 'COMMERCIAL', 'CONSTITUTIONAL', 'ADMINISTRATIVE', 'OTHER') NOT NULL,
            office_id INT NOT NULL,
            guidelines TEXT,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (office_id) REFERENCES offices(id) ON DELETE CASCADE
        )";
        $db->execute($query);
        echo "Client profiles table created successfully<br>";
    } catch (Exception $e) {
        echo "Error creating client_profiles table: " . $e->getMessage() . "<br>";
    }

    // Create cases table
    echo "<h3>Creating Cases Table</h3>";
    try {
        $query = "CREATE TABLE IF NOT EXISTS cases (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            status ENUM('ACTIVE', 'PENDING', 'RESOLVED', 'CANCELLED') DEFAULT 'PENDING',
            priority ENUM('LOW', 'MEDIUM', 'HIGH', 'URGENT') DEFAULT 'MEDIUM',
            category ENUM('FAMILY', 'CRIMINAL', 'CIVIL', 'PROPERTY', 'LABOR', 'COMMERCIAL', 'CONSTITUTIONAL', 'ADMINISTRATIVE', 'OTHER') NOT NULL,
            family_case_type ENUM('DIVORCE', 'CHILD_CUSTODY', 'CHILD_SUPPORT', 'ADOPTION', 'DOMESTIC_VIOLENCE', 'MARRIAGE_DISPUTE', 'ALIMONY', 'INHERITANCE', 'GUARDIANSHIP', 'PROPERTY_DIVISION', 'PRENUPTIAL_AGREEMENT', 'OTHER'),
            criminal_case_type ENUM('THEFT', 'ASSAULT', 'FRAUD', 'HOMICIDE', 'DRUG_RELATED', 'CYBERCRIME', 'DOMESTIC_VIOLENCE', 'SEXUAL_OFFENSE', 'WHITE_COLLAR', 'JUVENILE', 'TRAFFIC_VIOLATION', 'PUBLIC_ORDER', 'OTHER'),
            civil_case_type ENUM('CONTRACT_DISPUTE', 'PERSONAL_INJURY', 'DEFAMATION', 'NEGLIGENCE', 'DEBT_COLLECTION', 'CONSUMER_PROTECTION', 'CIVIL_RIGHTS', 'MEDICAL_MALPRACTICE', 'PROFESSIONAL_LIABILITY', 'INSURANCE_DISPUTE', 'OTHER'),
            property_case_type ENUM('LAND_DISPUTE', 'BOUNDARY_DISPUTE', 'TENANT_LANDLORD', 'EVICTION', 'PROPERTY_DAMAGE', 'CONSTRUCTION_DISPUTE', 'REAL_ESTATE_TRANSACTION', 'ZONING_DISPUTE', 'FORECLOSURE', 'EASEMENT', 'OTHER'),
            labor_case_type ENUM('WRONGFUL_TERMINATION', 'DISCRIMINATION', 'HARASSMENT', 'WAGE_DISPUTE', 'WORKPLACE_SAFETY', 'WORKERS_COMPENSATION', 'UNION_DISPUTE', 'BENEFITS_DISPUTE', 'CONTRACT_VIOLATION', 'UNFAIR_LABOR_PRACTICE', 'OTHER'),
            commercial_case_type ENUM('BUSINESS_CONTRACT', 'PARTNERSHIP_DISPUTE', 'INTELLECTUAL_PROPERTY', 'TRADE_SECRET', 'SECURITIES', 'ANTITRUST', 'BANKRUPTCY', 'MERGER_ACQUISITION', 'FRANCHISE_DISPUTE', 'CONSUMER_PROTECTION', 'OTHER'),
            administrative_case_type ENUM('LICENSING', 'PERMITS', 'REGULATORY_COMPLIANCE', 'TAX_DISPUTE', 'IMMIGRATION', 'SOCIAL_SECURITY', 'ENVIRONMENTAL', 'EDUCATION', 'HEALTHCARE', 'GOVERNMENT_BENEFITS', 'OTHER'),
            client_name VARCHAR(255) NOT NULL,
            client_phone VARCHAR(50) NOT NULL,
            client_address TEXT,
            region VARCHAR(255),
            zone VARCHAR(255),
            wereda VARCHAR(255) NOT NULL,
            kebele VARCHAR(255) NOT NULL,
            house_number VARCHAR(50),
            lawyer_id INT,
            client_id INT NOT NULL,
            client_request TEXT NOT NULL,
            request_details JSON,
            document_notes TEXT,
            tags JSON,
            complexity_score INT DEFAULT 0,
            risk_level INT DEFAULT 0,
            resource_intensity INT DEFAULT 0,
            stakeholder_impact INT DEFAULT 0,
            expected_resolution_date DATE,
            actual_resolution_date DATE,
            total_billable_hours FLOAT DEFAULT 0,
            document_count INT DEFAULT 0,
            total_service_hours FLOAT DEFAULT 0,
            expected_duration FLOAT DEFAULT 40,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (lawyer_id) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE
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
            title VARCHAR(255) NOT NULL,
            type VARCHAR(100) NOT NULL,
            path VARCHAR(255) NOT NULL,
            size INT NOT NULL,
            mime_type VARCHAR(100) NOT NULL,
            uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            uploaded_by INT NOT NULL,
            FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
            FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE
        )";
        $db->execute($query);
        echo "Case documents table created successfully<br>";
    } catch (Exception $e) {
        echo "Error creating case_documents table: " . $e->getMessage() . "<br>";
    }

    // Create case_activities table
    echo "<h3>Creating Case Activities Table</h3>";
    try {
        $query = "CREATE TABLE IF NOT EXISTS case_activities (
            id INT AUTO_INCREMENT PRIMARY KEY,
            case_id INT NOT NULL,
            user_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            type VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )";
        $db->execute($query);
        echo "Case activities table created successfully<br>";
    } catch (Exception $e) {
        echo "Error creating case_activities table: " . $e->getMessage() . "<br>";
    }

    // Create case_notes table
    echo "<h3>Creating Case Notes Table</h3>";
    try {
        $query = "CREATE TABLE IF NOT EXISTS case_notes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            case_id INT NOT NULL,
            content TEXT NOT NULL,
            created_by INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            is_private BOOLEAN DEFAULT FALSE,
            FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
        )";
        $db->execute($query);
        echo "Case notes table created successfully<br>";
    } catch (Exception $e) {
        echo "Error creating case_notes table: " . $e->getMessage() . "<br>";
    }

    // Create offices table
    echo "<h3>Creating Offices Table</h3>";
    try {
        $query = "CREATE TABLE IF NOT EXISTS offices (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL UNIQUE,
            location VARCHAR(255) NOT NULL,
            type ENUM('HEADQUARTERS', 'BRANCH') DEFAULT 'BRANCH',
            status ENUM('ACTIVE', 'INACTIVE', 'MAINTENANCE') DEFAULT 'ACTIVE',
            capacity INT DEFAULT 0,
            contact_email VARCHAR(255),
            contact_phone VARCHAR(50),
            address TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $db->execute($query);
        echo "Offices table created successfully<br>";
    } catch (Exception $e) {
        echo "Error creating offices table: " . $e->getMessage() . "<br>";
    }

    // Verify tables were created
    echo "<h3>Verifying Tables</h3>";
    try {
        $tables = [
            'users',
            'client_profiles',
            'cases',
            'case_documents',
            'case_activities',
            'case_notes',
            'offices'
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