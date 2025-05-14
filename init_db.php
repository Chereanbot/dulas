<?php
// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'dulas';

try {
    // Create connection
    $conn = new mysqli($host, $username, $password);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Create database if not exists
    $sql = "CREATE DATABASE IF NOT EXISTS $database";
    if ($conn->query($sql) === TRUE) {
        echo "Database created successfully or already exists\n";
    } else {
        throw new Exception("Error creating database: " . $conn->error);
    }
    
    // Select the database
    $conn->select_db($database);
    
    // Create user_roles table
    $sql = "CREATE TABLE IF NOT EXISTS user_roles (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(50) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table user_roles created successfully\n";
        
        // Insert default roles
        $roles = ['SUPER_ADMIN', 'ADMIN', 'LAWYER', 'COORDINATOR', 'CLIENT'];
        foreach ($roles as $role) {
            $sql = "INSERT IGNORE INTO user_roles (name) VALUES ('$role')";
            $conn->query($sql);
        }
    } else {
        throw new Exception("Error creating user_roles table: " . $conn->error);
    }
    
    // Create user_statuses table
    $sql = "CREATE TABLE IF NOT EXISTS user_statuses (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(50) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table user_statuses created successfully\n";
        
        // Insert default statuses
        $statuses = ['ACTIVE', 'INACTIVE', 'SUSPENDED', 'BANNED'];
        foreach ($statuses as $status) {
            $sql = "INSERT IGNORE INTO user_statuses (name) VALUES ('$status')";
            $conn->query($sql);
        }
    } else {
        throw new Exception("Error creating user_statuses table: " . $conn->error);
    }
    
    // Create roles table
    $sql = "CREATE TABLE IF NOT EXISTS roles (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(50) NOT NULL UNIQUE,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table roles created successfully\n";
    } else {
        throw new Exception("Error creating roles table: " . $conn->error);
    }
    
    // Create permissions table
    $sql = "CREATE TABLE IF NOT EXISTS permissions (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(50) NOT NULL UNIQUE,
        description TEXT,
        module VARCHAR(50) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table permissions created successfully\n";
    } else {
        throw new Exception("Error creating permissions table: " . $conn->error);
    }
    
    // Create role_permissions table
    $sql = "CREATE TABLE IF NOT EXISTS role_permissions (
        id INT PRIMARY KEY AUTO_INCREMENT,
        role_id INT NOT NULL,
        permission_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (role_id) REFERENCES roles(id),
        FOREIGN KEY (permission_id) REFERENCES permissions(id),
        UNIQUE KEY unique_role_permission (role_id, permission_id)
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table role_permissions created successfully\n";
    } else {
        throw new Exception("Error creating role_permissions table: " . $conn->error);
    }
    
    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        email VARCHAR(255) NOT NULL UNIQUE,
        phone VARCHAR(20) UNIQUE,
        password VARCHAR(255) NOT NULL,
        plain_password VARCHAR(255),
        full_name VARCHAR(255) NOT NULL,
        username VARCHAR(50) UNIQUE,
        email_verified BOOLEAN DEFAULT FALSE,
        phone_verified BOOLEAN DEFAULT FALSE,
        user_role_id INT,
        status_id INT,
        is_admin BOOLEAN DEFAULT FALSE,
        role_id INT,
        last_seen DATETIME,
        is_online BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_role_id) REFERENCES user_roles(id),
        FOREIGN KEY (status_id) REFERENCES user_statuses(id),
        FOREIGN KEY (role_id) REFERENCES roles(id)
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table users created successfully\n";
    } else {
        throw new Exception("Error creating users table: " . $conn->error);
    }
    
    // Create notification_types table
    $sql = "CREATE TABLE IF NOT EXISTS notification_types (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(50) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table notification_types created successfully\n";
        
        // Insert default notification types
        $types = [
            'SERVICE_REQUEST',
            'DOCUMENT_UPLOAD',
            'PAYMENT',
            'APPOINTMENT',
            'CHAT_MESSAGE',
            'SYSTEM_UPDATE',
            'TASK_ASSIGNED',
            'DEADLINE_REMINDER',
            'STATUS_UPDATE',
            'VERIFICATION',
            'NEW_MESSAGE',
            'MENTION',
            'REPLY',
            'REACTION',
            'THREAD_UPDATE',
            'FOLLOW_UP'
        ];
        
        foreach ($types as $type) {
            $sql = "INSERT IGNORE INTO notification_types (name) VALUES ('$type')";
            $conn->query($sql);
        }
    } else {
        throw new Exception("Error creating notification_types table: " . $conn->error);
    }
    
    // Create notification_priorities table
    $sql = "CREATE TABLE IF NOT EXISTS notification_priorities (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(50) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table notification_priorities created successfully\n";
        
        // Insert default priorities
        $priorities = ['LOW', 'NORMAL', 'HIGH', 'URGENT'];
        foreach ($priorities as $priority) {
            $sql = "INSERT IGNORE INTO notification_priorities (name) VALUES ('$priority')";
            $conn->query($sql);
        }
    } else {
        throw new Exception("Error creating notification_priorities table: " . $conn->error);
    }
    
    // Create notification_statuses table
    $sql = "CREATE TABLE IF NOT EXISTS notification_statuses (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(50) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table notification_statuses created successfully\n";
        
        // Insert default statuses
        $statuses = ['UNREAD', 'READ', 'PENDING', 'COMPLETED', 'DISMISSED'];
        foreach ($statuses as $status) {
            $sql = "INSERT IGNORE INTO notification_statuses (name) VALUES ('$status')";
            $conn->query($sql);
        }
    } else {
        throw new Exception("Error creating notification_statuses table: " . $conn->error);
    }
    
    // Create notifications table
    $sql = "CREATE TABLE IF NOT EXISTS notifications (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        title VARCHAR(255),
        message TEXT NOT NULL,
        type_id INT NOT NULL,
        priority_id INT NOT NULL,
        status_id INT NOT NULL,
        link VARCHAR(255),
        metadata JSON,
        read_at DATETIME,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        expires_at DATETIME,
        recipient_id INT,
        case_id INT,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (type_id) REFERENCES notification_types(id),
        FOREIGN KEY (priority_id) REFERENCES notification_priorities(id),
        FOREIGN KEY (status_id) REFERENCES notification_statuses(id),
        FOREIGN KEY (recipient_id) REFERENCES users(id)
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table notifications created successfully\n";
    } else {
        throw new Exception("Error creating notifications table: " . $conn->error);
    }
    
    // Create notification_preferences table
    $sql = "CREATE TABLE IF NOT EXISTS notification_preferences (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        type_id INT NOT NULL,
        email BOOLEAN DEFAULT TRUE,
        sms BOOLEAN DEFAULT TRUE,
        push BOOLEAN DEFAULT TRUE,
        in_app BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (type_id) REFERENCES notification_types(id),
        UNIQUE KEY unique_user_type (user_id, type_id)
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table notification_preferences created successfully\n";
    } else {
        throw new Exception("Error creating notification_preferences table: " . $conn->error);
    }
    
    // Create OTP verifications table
    $sql = "CREATE TABLE IF NOT EXISTS otp_verifications (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        otp VARCHAR(10) NOT NULL,
        type ENUM('EMAIL', 'PHONE') NOT NULL,
        expires_at DATETIME NOT NULL,
        verified BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table otp_verifications created successfully\n";
    } else {
        throw new Exception("Error creating otp_verifications table: " . $conn->error);
    }

    // Create activities table
    $sql = "CREATE TABLE IF NOT EXISTS activities (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        action VARCHAR(255) NOT NULL,
        details JSON,
        ip_address VARCHAR(45),
        user_agent TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table activities created successfully\n";
    } else {
        throw new Exception("Error creating activities table: " . $conn->error);
    }

    // Create documents table
    $sql = "CREATE TABLE IF NOT EXISTS documents (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        type ENUM('IDENTIFICATION', 'RESIDENCE_PROOF', 'BIRTH_CERTIFICATE', 'MARRIAGE_CERTIFICATE', 
                 'DEATH_CERTIFICATE', 'PROPERTY_DEED', 'TAX_DOCUMENT', 'BUSINESS_LICENSE', 
                 'PERMIT', 'CONTRACT', 'LEGAL_NOTICE', 'COMPLAINT', 'APPLICATION', 'OTHER') NOT NULL,
        status ENUM('PENDING', 'APPROVED', 'REJECTED', 'ARCHIVED', 'EXPIRED', 'ACTIVE') DEFAULT 'PENDING',
        path VARCHAR(255) NOT NULL,
        size INT NOT NULL,
        mime_type VARCHAR(100) NOT NULL,
        uploaded_by INT NOT NULL,
        kebele_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (uploaded_by) REFERENCES users(id)
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table documents created successfully\n";
    } else {
        throw new Exception("Error creating documents table: " . $conn->error);
    }

    // Create cases table
    $sql = "CREATE TABLE IF NOT EXISTS cases (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        status ENUM('ACTIVE', 'PENDING', 'RESOLVED', 'CANCELLED') DEFAULT 'PENDING',
        priority ENUM('LOW', 'MEDIUM', 'HIGH', 'URGENT') DEFAULT 'MEDIUM',
        category ENUM('FAMILY', 'CRIMINAL', 'CIVIL', 'PROPERTY', 'LABOR', 'COMMERCIAL', 
                     'CONSTITUTIONAL', 'ADMINISTRATIVE', 'OTHER') NOT NULL,
        client_name VARCHAR(255) NOT NULL,
        client_phone VARCHAR(20) NOT NULL,
        client_address TEXT,
        region VARCHAR(100),
        zone VARCHAR(100),
        wereda VARCHAR(100) NOT NULL,
        kebele VARCHAR(100) NOT NULL,
        house_number VARCHAR(50),
        assigned_lawyer_id INT,
        client_id INT NOT NULL,
        client_request TEXT NOT NULL,
        request_details JSON,
        document_notes TEXT,
        tags JSON,
        complexity_score INT DEFAULT 0,
        risk_level INT DEFAULT 0,
        resource_intensity INT DEFAULT 0,
        stakeholder_impact INT DEFAULT 0,
        expected_resolution_date DATETIME,
        actual_resolution_date DATETIME,
        total_billable_hours FLOAT DEFAULT 0,
        document_count INT DEFAULT 0,
        total_service_hours FLOAT DEFAULT 0,
        expected_duration FLOAT DEFAULT 40,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        resolved_at DATETIME,
        FOREIGN KEY (assigned_lawyer_id) REFERENCES users(id),
        FOREIGN KEY (client_id) REFERENCES users(id)
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table cases created successfully\n";
    } else {
        throw new Exception("Error creating cases table: " . $conn->error);
    }

    // Create case assignments table
    $sql = "CREATE TABLE IF NOT EXISTS case_assignments (
        id INT PRIMARY KEY AUTO_INCREMENT,
        case_id INT NOT NULL,
        assigned_by_id INT NOT NULL,
        assigned_to_id INT NOT NULL,
        status ENUM('PENDING', 'ACCEPTED', 'REJECTED', 'COMPLETED') DEFAULT 'PENDING',
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (case_id) REFERENCES cases(id),
        FOREIGN KEY (assigned_by_id) REFERENCES users(id),
        FOREIGN KEY (assigned_to_id) REFERENCES users(id)
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table case_assignments created successfully\n";
    } else {
        throw new Exception("Error creating case_assignments table: " . $conn->error);
    }

    // Create lawyer profiles table
    $sql = "CREATE TABLE IF NOT EXISTS lawyer_profiles (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL UNIQUE,
        experience INT NOT NULL,
        rating FLOAT,
        case_load INT DEFAULT 0,
        availability BOOLEAN DEFAULT TRUE,
        office_id INT NOT NULL,
        years_of_practice INT DEFAULT 0,
        bar_admission_date DATE,
        primary_jurisdiction VARCHAR(100),
        languages JSON,
        certifications JSON,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table lawyer_profiles created successfully\n";
    } else {
        throw new Exception("Error creating lawyer_profiles table: " . $conn->error);
    }

    // Create coordinator profiles table
    $sql = "CREATE TABLE IF NOT EXISTS coordinators (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL UNIQUE,
        type ENUM('FULL_TIME', 'PART_TIME') NOT NULL,
        office_id INT NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE,
        specialties JSON,
        status ENUM('PENDING', 'ACTIVE', 'INACTIVE', 'SUSPENDED') DEFAULT 'PENDING',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table coordinators created successfully\n";
    } else {
        throw new Exception("Error creating coordinators table: " . $conn->error);
    }

    // Create service requests table
    $sql = "CREATE TABLE IF NOT EXISTS service_requests (
        id INT PRIMARY KEY AUTO_INCREMENT,
        client_id INT NOT NULL,
        package_id INT NOT NULL,
        status ENUM('PENDING', 'APPROVED', 'REJECTED', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED', 'ON_HOLD') DEFAULT 'PENDING',
        priority ENUM('LOW', 'MEDIUM', 'HIGH', 'URGENT') DEFAULT 'MEDIUM',
        assigned_lawyer_id INT,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        requirements JSON,
        submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        approved_at DATETIME,
        completed_at DATETIME,
        progress INT DEFAULT 0,
        current_stage VARCHAR(100),
        next_action VARCHAR(255),
        quoted_price DECIMAL(10,2),
        final_price DECIMAL(10,2),
        payment_status ENUM('PENDING', 'PROCESSING', 'COMPLETED', 'FAILED', 'REFUNDED', 'CANCELLED', 'PAID', 'WAIVED') DEFAULT 'PENDING',
        tags JSON,
        metadata JSON,
        FOREIGN KEY (client_id) REFERENCES users(id),
        FOREIGN KEY (assigned_lawyer_id) REFERENCES users(id)
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table service_requests created successfully\n";
    } else {
        throw new Exception("Error creating service_requests table: " . $conn->error);
    }

    // Create block records table
    $sql = "CREATE TABLE IF NOT EXISTS block_records (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        action ENUM('block', 'ban') NOT NULL,
        reason TEXT NOT NULL,
        expires_at DATETIME,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table block_records created successfully\n";
    } else {
        throw new Exception("Error creating block_records table: " . $conn->error);
    }

    // Create ratings table
    $sql = "CREATE TABLE IF NOT EXISTS ratings (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        rating FLOAT NOT NULL,
        comment TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table ratings created successfully\n";
    } else {
        throw new Exception("Error creating ratings table: " . $conn->error);
    }

    // Create security logs table
    $sql = "CREATE TABLE IF NOT EXISTS security_logs (
        id INT PRIMARY KEY AUTO_INCREMENT,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        event_type VARCHAR(100) NOT NULL,
        severity VARCHAR(50) NOT NULL,
        description TEXT NOT NULL,
        ip_address VARCHAR(45) NOT NULL,
        user_id INT,
        status VARCHAR(50) NOT NULL,
        details JSON,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table security_logs created successfully\n";
    } else {
        throw new Exception("Error creating security_logs table: " . $conn->error);
    }

    // Create sessions table
    $sql = "CREATE TABLE IF NOT EXISTS sessions (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        token VARCHAR(255) NOT NULL UNIQUE,
        active BOOLEAN DEFAULT TRUE,
        user_agent TEXT,
        last_ip_address VARCHAR(45),
        location JSON,
        expires_at DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table sessions created successfully\n";
    } else {
        throw new Exception("Error creating sessions table: " . $conn->error);
    }

    // Create verification requests table
    $sql = "CREATE TABLE IF NOT EXISTS verification_requests (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        document_type VARCHAR(100) NOT NULL,
        document_number VARCHAR(100) NOT NULL,
        document_url VARCHAR(255) NOT NULL,
        status VARCHAR(50) DEFAULT 'pending',
        submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        reviewed_at DATETIME,
        reviewed_by_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (reviewed_by_id) REFERENCES users(id)
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table verification_requests created successfully\n";
    } else {
        throw new Exception("Error creating verification_requests table: " . $conn->error);
    }

    // Create audit logs table
    $sql = "CREATE TABLE IF NOT EXISTS audit_logs (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT,
        action VARCHAR(255) NOT NULL,
        details TEXT,
        ip_address VARCHAR(45),
        user_agent TEXT,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table audit_logs created successfully\n";
    } else {
        throw new Exception("Error creating audit_logs table: " . $conn->error);
    }

    // Add indexes to users table
    $sql = "CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)";
    $conn->query($sql);
    
    $sql = "CREATE INDEX IF NOT EXISTS idx_users_phone ON users(phone)";
    $conn->query($sql);
    
    $sql = "CREATE INDEX IF NOT EXISTS idx_users_username ON users(username)";
    $conn->query($sql);

    echo "Additional tables and indexes created successfully!";

    // Create messages table
    $sql = "CREATE TABLE IF NOT EXISTS messages (
        id INT PRIMARY KEY AUTO_INCREMENT,
        sender_id INT NOT NULL,
        recipient_id INT NOT NULL,
        content TEXT NOT NULL,
        type ENUM('TEXT', 'IMAGE', 'FILE', 'AUDIO', 'VIDEO') DEFAULT 'TEXT',
        status ENUM('SENT', 'DELIVERED', 'READ', 'FAILED') DEFAULT 'SENT',
        metadata JSON,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (sender_id) REFERENCES users(id),
        FOREIGN KEY (recipient_id) REFERENCES users(id)
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table messages created successfully\n";
    } else {
        throw new Exception("Error creating messages table: " . $conn->error);
    }

    // Create message threads table
    $sql = "CREATE TABLE IF NOT EXISTS message_threads (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(255),
        type ENUM('DIRECT', 'GROUP') DEFAULT 'DIRECT',
        created_by INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(id)
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table message_threads created successfully\n";
    } else {
        throw new Exception("Error creating message_threads table: " . $conn->error);
    }

    // Create thread participants table
    $sql = "CREATE TABLE IF NOT EXISTS thread_participants (
        id INT PRIMARY KEY AUTO_INCREMENT,
        thread_id INT NOT NULL,
        user_id INT NOT NULL,
        role ENUM('ADMIN', 'MEMBER') DEFAULT 'MEMBER',
        joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_read_at DATETIME,
        FOREIGN KEY (thread_id) REFERENCES message_threads(id),
        FOREIGN KEY (user_id) REFERENCES users(id),
        UNIQUE KEY unique_thread_user (thread_id, user_id)
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table thread_participants created successfully\n";
    } else {
        throw new Exception("Error creating thread_participants table: " . $conn->error);
    }

    // Create message reactions table
    $sql = "CREATE TABLE IF NOT EXISTS message_reactions (
        id INT PRIMARY KEY AUTO_INCREMENT,
        message_id INT NOT NULL,
        user_id INT NOT NULL,
        reaction VARCHAR(50) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (message_id) REFERENCES messages(id),
        FOREIGN KEY (user_id) REFERENCES users(id),
        UNIQUE KEY unique_message_user (message_id, user_id)
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table message_reactions created successfully\n";
    } else {
        throw new Exception("Error creating message_reactions table: " . $conn->error);
    }

    // Create typing status table
    $sql = "CREATE TABLE IF NOT EXISTS typing_status (
        id INT PRIMARY KEY AUTO_INCREMENT,
        thread_id INT NOT NULL,
        user_id INT NOT NULL,
        is_typing BOOLEAN DEFAULT FALSE,
        last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (thread_id) REFERENCES message_threads(id),
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table typing_status created successfully\n";
    } else {
        throw new Exception("Error creating typing_status table: " . $conn->error);
    }

    // Create events table
    $sql = "CREATE TABLE IF NOT EXISTS events (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        start_time DATETIME NOT NULL,
        end_time DATETIME NOT NULL,
        location VARCHAR(255),
        type ENUM('COURT_HEARING', 'CLIENT_MEETING', 'DEADLINE', 'TASK', 'OTHER') NOT NULL,
        status ENUM('SCHEDULED', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED') DEFAULT 'SCHEDULED',
        priority ENUM('LOW', 'MEDIUM', 'HIGH', 'URGENT') DEFAULT 'MEDIUM',
        created_by INT NOT NULL,
        case_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(id),
        FOREIGN KEY (case_id) REFERENCES cases(id)
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table events created successfully\n";
    } else {
        throw new Exception("Error creating events table: " . $conn->error);
    }

    // Create event participants table
    $sql = "CREATE TABLE IF NOT EXISTS event_participants (
        id INT PRIMARY KEY AUTO_INCREMENT,
        event_id INT NOT NULL,
        user_id INT NOT NULL,
        role ENUM('ORGANIZER', 'ATTENDEE', 'GUEST') DEFAULT 'ATTENDEE',
        status ENUM('PENDING', 'ACCEPTED', 'DECLINED', 'TENTATIVE') DEFAULT 'PENDING',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (event_id) REFERENCES events(id),
        FOREIGN KEY (user_id) REFERENCES users(id),
        UNIQUE KEY unique_event_user (event_id, user_id)
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table event_participants created successfully\n";
    } else {
        throw new Exception("Error creating event_participants table: " . $conn->error);
    }

    // Create legal resources table
    $sql = "CREATE TABLE IF NOT EXISTS legal_resources (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        type ENUM('DOCUMENT', 'TEMPLATE', 'GUIDE', 'REFERENCE', 'OTHER') NOT NULL,
        category VARCHAR(100),
        tags JSON,
        file_path VARCHAR(255),
        file_type VARCHAR(100),
        file_size INT,
        created_by INT NOT NULL,
        status ENUM('DRAFT', 'PUBLISHED', 'ARCHIVED') DEFAULT 'DRAFT',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(id)
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table legal_resources created successfully\n";
    } else {
        throw new Exception("Error creating legal_resources table: " . $conn->error);
    }

    // Create resource shares table
    $sql = "CREATE TABLE IF NOT EXISTS resource_shares (
        id INT PRIMARY KEY AUTO_INCREMENT,
        resource_id INT NOT NULL,
        shared_by_id INT NOT NULL,
        shared_with_id INT NOT NULL,
        permission_level ENUM('VIEW', 'EDIT', 'ADMIN') DEFAULT 'VIEW',
        expires_at DATETIME,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (resource_id) REFERENCES legal_resources(id),
        FOREIGN KEY (shared_by_id) REFERENCES users(id),
        FOREIGN KEY (shared_with_id) REFERENCES users(id)
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table resource_shares created successfully\n";
    } else {
        throw new Exception("Error creating resource_shares table: " . $conn->error);
    }

    // Create resource analytics table
    $sql = "CREATE TABLE IF NOT EXISTS resource_analytics (
        id INT PRIMARY KEY AUTO_INCREMENT,
        resource_id INT NOT NULL,
        user_id INT NOT NULL,
        action ENUM('VIEW', 'DOWNLOAD', 'SHARE', 'EDIT') NOT NULL,
        metadata JSON,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (resource_id) REFERENCES legal_resources(id),
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table resource_analytics created successfully\n";
    } else {
        throw new Exception("Error creating resource_analytics table: " . $conn->error);
    }

    // Create workload metrics table
    $sql = "CREATE TABLE IF NOT EXISTS workload_metrics (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        date DATE NOT NULL,
        total_cases INT DEFAULT 0,
        active_cases INT DEFAULT 0,
        completed_cases INT DEFAULT 0,
        total_hours FLOAT DEFAULT 0,
        billable_hours FLOAT DEFAULT 0,
        efficiency_score FLOAT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        UNIQUE KEY unique_user_date (user_id, date)
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table workload_metrics created successfully\n";
    } else {
        throw new Exception("Error creating workload_metrics table: " . $conn->error);
    }

    // Create work assignments table
    $sql = "CREATE TABLE IF NOT EXISTS work_assignments (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        case_id INT,
        service_request_id INT,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        status ENUM('PENDING', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED') DEFAULT 'PENDING',
        priority ENUM('LOW', 'MEDIUM', 'HIGH', 'URGENT') DEFAULT 'MEDIUM',
        start_date DATE,
        due_date DATE,
        estimated_hours FLOAT,
        actual_hours FLOAT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (case_id) REFERENCES cases(id),
        FOREIGN KEY (service_request_id) REFERENCES service_requests(id)
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table work_assignments created successfully\n";
    } else {
        throw new Exception("Error creating work_assignments table: " . $conn->error);
    }

    // Create work schedules table
    $sql = "CREATE TABLE IF NOT EXISTS work_schedules (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        day_of_week ENUM('MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY', 'SUNDAY') NOT NULL,
        start_time TIME NOT NULL,
        end_time TIME NOT NULL,
        is_available BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table work_schedules created successfully\n";
    } else {
        throw new Exception("Error creating work_schedules table: " . $conn->error);
    }

    // Create teaching schedules table
    $sql = "CREATE TABLE IF NOT EXISTS teaching_schedules (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        start_time DATETIME NOT NULL,
        end_time DATETIME NOT NULL,
        location VARCHAR(255),
        max_participants INT,
        current_participants INT DEFAULT 0,
        status ENUM('SCHEDULED', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED') DEFAULT 'SCHEDULED',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table teaching_schedules created successfully\n";
    } else {
        throw new Exception("Error creating teaching_schedules table: " . $conn->error);
    }

    // Create teaching metrics table
    $sql = "CREATE TABLE IF NOT EXISTS teaching_metrics (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        schedule_id INT NOT NULL,
        total_sessions INT DEFAULT 0,
        total_participants INT DEFAULT 0,
        average_rating FLOAT DEFAULT 0,
        feedback_count INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (schedule_id) REFERENCES teaching_schedules(id)
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table teaching_metrics created successfully\n";
    } else {
        throw new Exception("Error creating teaching_metrics table: " . $conn->error);
    }

    // Create backups table
    $sql = "CREATE TABLE IF NOT EXISTS backups (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        backup_type ENUM('FULL', 'INCREMENTAL', 'DIFFERENTIAL') NOT NULL,
        file_path VARCHAR(255) NOT NULL,
        size INT NOT NULL,
        status ENUM('PENDING', 'IN_PROGRESS', 'COMPLETED', 'FAILED') DEFAULT 'PENDING',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        completed_at DATETIME,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table backups created successfully\n";
    } else {
        throw new Exception("Error creating backups table: " . $conn->error);
    }

    // Create agent notifications table
    $sql = "CREATE TABLE IF NOT EXISTS agent_notifications (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        type ENUM('INFO', 'WARNING', 'ERROR', 'SUCCESS') DEFAULT 'INFO',
        status ENUM('UNREAD', 'READ', 'ARCHIVED') DEFAULT 'UNREAD',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table agent_notifications created successfully\n";
    } else {
        throw new Exception("Error creating agent_notifications table: " . $conn->error);
    }

    // Create agent chats table
    $sql = "CREATE TABLE IF NOT EXISTS agent_chats (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        agent_id VARCHAR(100) NOT NULL,
        session_id VARCHAR(255) NOT NULL,
        status ENUM('ACTIVE', 'CLOSED', 'TRANSFERRED') DEFAULT 'ACTIVE',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table agent_chats created successfully\n";
    } else {
        throw new Exception("Error creating agent_chats table: " . $conn->error);
    }

    // Create appointments table
    $sql = "CREATE TABLE IF NOT EXISTS appointments (
        id INT PRIMARY KEY AUTO_INCREMENT,
        client_id INT NOT NULL,
        coordinator_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        start_time DATETIME NOT NULL,
        end_time DATETIME NOT NULL,
        status ENUM('SCHEDULED', 'CONFIRMED', 'CANCELLED', 'COMPLETED', 'NO_SHOW') DEFAULT 'SCHEDULED',
        type ENUM('INITIAL_CONSULTATION', 'FOLLOW_UP', 'DOCUMENT_REVIEW', 'COURT_HEARING', 'OTHER') NOT NULL,
        location VARCHAR(255),
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (client_id) REFERENCES users(id),
        FOREIGN KEY (coordinator_id) REFERENCES users(id)
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table appointments created successfully\n";
    } else {
        throw new Exception("Error creating appointments table: " . $conn->error);
    }

    // Create client profiles table
    $sql = "CREATE TABLE IF NOT EXISTS client_profiles (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL UNIQUE,
        date_of_birth DATE,
        gender ENUM('MALE', 'FEMALE', 'OTHER'),
        address TEXT,
        city VARCHAR(100),
        state VARCHAR(100),
        country VARCHAR(100),
        postal_code VARCHAR(20),
        preferred_contact_method ENUM('EMAIL', 'PHONE', 'SMS') DEFAULT 'EMAIL',
        preferred_language VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table client_profiles created successfully\n";
    } else {
        throw new Exception("Error creating client_profiles table: " . $conn->error);
    }

    // Create coordinator history table
    $sql = "CREATE TABLE IF NOT EXISTS coordinator_history (
        id INT PRIMARY KEY AUTO_INCREMENT,
        coordinator_id INT NOT NULL,
        client_id INT NOT NULL,
        lawyer_id INT NOT NULL,
        action VARCHAR(255) NOT NULL,
        details TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (coordinator_id) REFERENCES users(id),
        FOREIGN KEY (client_id) REFERENCES users(id),
        FOREIGN KEY (lawyer_id) REFERENCES users(id)
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table coordinator_history created successfully\n";
    } else {
        throw new Exception("Error creating coordinator_history table: " . $conn->error);
    }

    // Create offices table
    $sql = "CREATE TABLE IF NOT EXISTS offices (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL UNIQUE,
        location VARCHAR(255) NOT NULL,
        type ENUM('HEADQUARTERS', 'BRANCH', 'FIELD_OFFICE', 'SPECIALIZED') DEFAULT 'BRANCH',
        status ENUM('ACTIVE', 'INACTIVE', 'SUSPENDED', 'CLOSED') DEFAULT 'ACTIVE',
        capacity INT DEFAULT 0,
        contact_email VARCHAR(255),
        contact_phone VARCHAR(20),
        address TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    try {
        $conn->query($sql);
        echo "Offices table created successfully\n";
    } catch (Exception $e) {
        echo "Error creating offices table: " . $e->getMessage() . "\n";
    }

    // Create office_performances table
    $sql = "CREATE TABLE IF NOT EXISTS office_performances (
        id INT PRIMARY KEY AUTO_INCREMENT,
        office_id INT NOT NULL,
        metric_name VARCHAR(100) NOT NULL,
        metric_value FLOAT NOT NULL,
        period_start DATE NOT NULL,
        period_end DATE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (office_id) REFERENCES offices(id) ON DELETE CASCADE
    )";

    try {
        $conn->query($sql);
        echo "Office performances table created successfully\n";
    } catch (Exception $e) {
        echo "Error creating office_performances table: " . $e->getMessage() . "\n";
    }

    // Create office_templates table
    $sql = "CREATE TABLE IF NOT EXISTS office_templates (
        id INT PRIMARY KEY AUTO_INCREMENT,
        office_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        type VARCHAR(50) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (office_id) REFERENCES offices(id) ON DELETE CASCADE
    )";

    try {
        $conn->query($sql);
        echo "Office templates table created successfully\n";
    } catch (Exception $e) {
        echo "Error creating office_templates table: " . $e->getMessage() . "\n";
    }

    // Add office_id to existing tables
    $tables_to_update = [
        'lawyer_profiles' => 'office_id',
        'cases' => 'office_id',
        'client_profiles' => 'office_id',
        'legal_resources' => 'office_id'
    ];

    foreach ($tables_to_update as $table => $column) {
        $sql = "ALTER TABLE $table ADD COLUMN IF NOT EXISTS $column INT,
                ADD FOREIGN KEY ($column) REFERENCES offices(id) ON DELETE SET NULL";
        try {
            $conn->query($sql);
            echo "Added $column to $table table successfully\n";
        } catch (Exception $e) {
            echo "Error adding $column to $table table: " . $e->getMessage() . "\n";
        }
    }

    // Create paralegals table
    $sql = "CREATE TABLE IF NOT EXISTS paralegals (
        id INT PRIMARY KEY AUTO_INCREMENT,
        office_id INT NOT NULL,
        user_id INT NOT NULL,
        specialization VARCHAR(100),
        status ENUM('ACTIVE', 'INACTIVE') DEFAULT 'ACTIVE',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (office_id) REFERENCES offices(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";

    try {
        $conn->query($sql);
        echo "Paralegals table created successfully\n";
    } catch (Exception $e) {
        echo "Error creating paralegals table: " . $e->getMessage() . "\n";
    }

    echo "All remaining tables created successfully!";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
