<?php
require_once 'database.php';

try {
    $pdo = new PDO("mysql:host=localhost", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("USE dulas");

    // Create lawyer_profiles table
    $sql = "CREATE TABLE IF NOT EXISTS lawyer_profiles (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        bar_number VARCHAR(50) UNIQUE,
        years_of_experience INT,
        education TEXT,
        bio TEXT,
        hourly_rate DECIMAL(10,2),
        consultation_fee DECIMAL(10,2),
        max_cases INT DEFAULT 10,
        current_cases INT DEFAULT 0,
        availability_status ENUM('available', 'busy', 'unavailable') DEFAULT 'available',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "Lawyer profiles table created successfully<br>";

    // Create lawyer_specializations table
    $sql = "CREATE TABLE IF NOT EXISTS lawyer_specializations (
        id INT PRIMARY KEY AUTO_INCREMENT,
        lawyer_id INT NOT NULL,
        specialization VARCHAR(100) NOT NULL,
        years_experience INT,
        certification VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (lawyer_id) REFERENCES lawyer_profiles(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "Lawyer specializations table created successfully<br>";

    // Create lawyer_workload table
    $sql = "CREATE TABLE IF NOT EXISTS lawyer_workload (
        id INT PRIMARY KEY AUTO_INCREMENT,
        lawyer_id INT NOT NULL,
        case_id INT NOT NULL,
        hours_spent DECIMAL(10,2) DEFAULT 0,
        status ENUM('active', 'completed', 'on_hold') DEFAULT 'active',
        priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
        start_date DATE,
        estimated_completion_date DATE,
        actual_completion_date DATE,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (lawyer_id) REFERENCES lawyer_profiles(id) ON DELETE CASCADE,
        FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "Lawyer workload table created successfully<br>";

    // Create lawyer_availability table
    $sql = "CREATE TABLE IF NOT EXISTS lawyer_availability (
        id INT PRIMARY KEY AUTO_INCREMENT,
        lawyer_id INT NOT NULL,
        day_of_week ENUM('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'),
        start_time TIME,
        end_time TIME,
        is_available BOOLEAN DEFAULT true,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (lawyer_id) REFERENCES lawyer_profiles(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "Lawyer availability table created successfully<br>";

    // Create lawyer_ratings table
    $sql = "CREATE TABLE IF NOT EXISTS lawyer_ratings (
        id INT PRIMARY KEY AUTO_INCREMENT,
        lawyer_id INT NOT NULL,
        client_id INT NOT NULL,
        case_id INT NOT NULL,
        rating INT CHECK (rating >= 1 AND rating <= 5),
        review TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (lawyer_id) REFERENCES lawyer_profiles(id) ON DELETE CASCADE,
        FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "Lawyer ratings table created successfully<br>";

    // Create lawyer_documents table
    $sql = "CREATE TABLE IF NOT EXISTS lawyer_documents (
        id INT PRIMARY KEY AUTO_INCREMENT,
        lawyer_id INT NOT NULL,
        document_type ENUM('certification', 'license', 'resume', 'other') NOT NULL,
        title VARCHAR(255) NOT NULL,
        file_path VARCHAR(255) NOT NULL,
        file_type VARCHAR(50),
        upload_date DATE,
        expiry_date DATE,
        status ENUM('active', 'expired', 'pending') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (lawyer_id) REFERENCES lawyer_profiles(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "Lawyer documents table created successfully<br>";

    // Create lawyer_cases table
    $sql = "CREATE TABLE IF NOT EXISTS lawyer_cases (
        id INT PRIMARY KEY AUTO_INCREMENT,
        lawyer_id INT NOT NULL,
        case_id INT NOT NULL,
        role ENUM('primary', 'secondary', 'consultant') DEFAULT 'primary',
        start_date DATE,
        end_date DATE,
        status ENUM('active', 'completed', 'transferred') DEFAULT 'active',
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (lawyer_id) REFERENCES lawyer_profiles(id) ON DELETE CASCADE,
        FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "Lawyer cases table created successfully<br>";

    // Create lawyer_billing table
    $sql = "CREATE TABLE IF NOT EXISTS lawyer_billing (
        id INT PRIMARY KEY AUTO_INCREMENT,
        lawyer_id INT NOT NULL,
        case_id INT NOT NULL,
        client_id INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        billing_type ENUM('hourly', 'fixed', 'contingency') NOT NULL,
        hours_billed DECIMAL(10,2),
        rate_per_hour DECIMAL(10,2),
        description TEXT,
        status ENUM('pending', 'paid', 'overdue') DEFAULT 'pending',
        due_date DATE,
        payment_date DATE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (lawyer_id) REFERENCES lawyer_profiles(id) ON DELETE CASCADE,
        FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
        FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "Lawyer billing table created successfully<br>";

    // Create lawyer_appointments table
    $sql = "CREATE TABLE IF NOT EXISTS lawyer_appointments (
        id INT PRIMARY KEY AUTO_INCREMENT,
        lawyer_id INT NOT NULL,
        client_id INT NOT NULL,
        case_id INT,
        appointment_type ENUM('consultation', 'meeting', 'court_hearing', 'other') NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        start_datetime DATETIME NOT NULL,
        end_datetime DATETIME NOT NULL,
        status ENUM('scheduled', 'completed', 'cancelled', 'rescheduled') DEFAULT 'scheduled',
        location VARCHAR(255),
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (lawyer_id) REFERENCES lawyer_profiles(id) ON DELETE CASCADE,
        FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE SET NULL
    )";
    $pdo->exec($sql);
    echo "Lawyer appointments table created successfully<br>";

    // Insert default specializations
    $specializations = [
        'Criminal Law',
        'Civil Law',
        'Family Law',
        'Corporate Law',
        'Intellectual Property Law',
        'Real Estate Law',
        'Immigration Law',
        'Tax Law',
        'Employment Law',
        'Environmental Law',
        'International Law',
        'Bankruptcy Law',
        'Constitutional Law',
        'Healthcare Law',
        'Entertainment Law'
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO lawyer_specializations (specialization) VALUES (?)");
    foreach ($specializations as $spec) {
        $stmt->execute([$spec]);
    }
    echo "Default specializations seeded<br>";

    echo "All lawyer-related tables created successfully!";

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>