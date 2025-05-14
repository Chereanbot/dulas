<?php
require_once 'config/database.php';

try {
    // Create database if it doesn't exist
    $pdo = new PDO("mysql:host=localhost", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database
    $sql = "CREATE DATABASE IF NOT EXISTS legal_case_management";
    $pdo->exec($sql);
    echo "Database created successfully<br>";
    
    // Select the database
    $pdo->exec("USE legal_case_management");
    
    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        phone VARCHAR(20),
        role ENUM('superadmin', 'admin', 'lawyer', 'paralegal', 'super_paralegal', 'lawschool', 'client') NOT NULL,
        status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL,
        last_login_ip VARCHAR(45),
        login_attempts INT DEFAULT 0
    )";
    $pdo->exec($sql);
    echo "Users table created successfully<br>";
    
    // Create client_profiles table
    $sql = "CREATE TABLE IF NOT EXISTS client_profiles (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        age INT,
        sex ENUM('male', 'female', 'other'),
        family_members INT,
        health_status TEXT,
        region VARCHAR(100),
        zone VARCHAR(100),
        wereda VARCHAR(100),
        kebele VARCHAR(100),
        house_number VARCHAR(50),
        case_type VARCHAR(100),
        case_category VARCHAR(100),
        office_id INT,
        id_proof_path VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "Client profiles table created successfully<br>";
    
    // Create cases table
    $sql = "CREATE TABLE IF NOT EXISTS cases (
        id INT AUTO_INCREMENT PRIMARY KEY,
        client_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        status ENUM('pending', 'active', 'resolved', 'closed') DEFAULT 'pending',
        case_type VARCHAR(100),
        case_category VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "Cases table created successfully<br>";
    
    // Create case_documents table
    $sql = "CREATE TABLE IF NOT EXISTS case_documents (
        id INT AUTO_INCREMENT PRIMARY KEY,
        case_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        file_path VARCHAR(255) NOT NULL,
        document_type VARCHAR(100),
        uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "Case documents table created successfully<br>";
    
    // Create case_activities table
    $sql = "CREATE TABLE IF NOT EXISTS case_activities (
        id INT AUTO_INCREMENT PRIMARY KEY,
        case_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        activity_date DATE NOT NULL,
        activity_type VARCHAR(100),
        status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "Case activities table created successfully<br>";
    
    // Create case_notes table
    $sql = "CREATE TABLE IF NOT EXISTS case_notes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        case_id INT NOT NULL,
        note TEXT NOT NULL,
        created_by INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "Case notes table created successfully<br>";
    
    // Create offices table
    $sql = "CREATE TABLE IF NOT EXISTS offices (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        address TEXT,
        phone VARCHAR(20),
        email VARCHAR(100),
        region VARCHAR(100),
        zone VARCHAR(100),
        wereda VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "Offices table created successfully<br>";
    
    // Create user_security_logs table
    $sql = "CREATE TABLE IF NOT EXISTS user_security_logs (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        event_type VARCHAR(50) NOT NULL,
        ip_address VARCHAR(45) NOT NULL,
        user_agent TEXT,
        status VARCHAR(20) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";
    $pdo->exec($sql);
    echo "User security logs table created successfully<br>";
    
    // Insert default admin user
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, password, full_name, email, role) 
            VALUES ('admin', :password, 'System Administrator', 'admin@dulas.com', 'superadmin')
            ON DUPLICATE KEY UPDATE id=id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['password' => $password]);
    echo "Default admin user created successfully<br>";
    
    echo "<br>Database setup completed successfully!";
    
} catch(PDOException $e) {
    die("ERROR: " . $e->getMessage());
}
?> 