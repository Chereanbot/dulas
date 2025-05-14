<?php
require_once 'database.php';

try {
    // Create database connection without database name
    $pdo = new PDO("mysql:host=localhost", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create database if not exists
    $sql = "CREATE DATABASE IF NOT EXISTS dulas";
    $pdo->exec($sql);
    echo "Database created successfully<br>";

    // Select the database
    $pdo->exec("USE dulas");

    // Create users table with enhanced fields
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        role ENUM('client', 'lawyer', 'paralegal', 'super_paralegal', 'lawschool', 'admin', 'superadmin') NOT NULL,
        organization_id INT,
        phone VARCHAR(20),
        address TEXT,
        profile_image VARCHAR(255),
        bio TEXT,
        status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
        email_verified BOOLEAN DEFAULT FALSE,
        verification_token VARCHAR(100),
        reset_token VARCHAR(100),
        reset_token_expiry DATETIME,
        last_login DATETIME,
        last_login_ip VARCHAR(45),
        login_attempts INT DEFAULT 0,
        locked_until DATETIME,
        two_factor_enabled BOOLEAN DEFAULT FALSE,
        two_factor_secret VARCHAR(32),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "Users table created successfully<br>";

    // Create default users for each role
    $defaultPassword = password_hash('cherinet', PASSWORD_DEFAULT);
    $defaultUsers = [
        [
            'username' => 'cherinet',
            'email' => 'cherinet@dulas.com',
            'full_name' => 'Cherinet Administrator',
            'role' => 'superadmin'
        ],
        [
            'username' => 'chere',
            'email' => 'chere@dulas.com',
            'full_name' => 'Chere Admin',
            'role' => 'admin'
        ],
        [
            'username' => 'cherean',
            'email' => 'cherean@dulas.com',
            'full_name' => 'Cherean Lawyer',
            'role' => 'lawyer'
        ],
        [
            'username' => 'cher',
            'email' => 'cher@dulas.com',
            'full_name' => 'Cher Super Paralegal',
            'role' => 'super_paralegal'
        ],
        [
            'username' => 'che',
            'email' => 'che@dulas.com',
            'full_name' => 'Che Paralegal',
            'role' => 'paralegal'
        ],
        [
            'username' => 'client',
            'email' => 'client@dulas.com',
            'full_name' => 'Test Client',
            'role' => 'client'
        ]
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, password, email, full_name, role, status, email_verified) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($defaultUsers as $user) {
        $stmt->execute([
            $user['username'],
            $defaultPassword,
            $user['email'],
            $user['full_name'],
            $user['role'],
            'active',
            true
        ]);
        echo "Default user {$user['username']} created successfully<br>";
    }

    // Create user_sessions table
    $sql = "CREATE TABLE IF NOT EXISTS user_sessions (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        session_token VARCHAR(255) NOT NULL,
        device_info TEXT,
        ip_address VARCHAR(45),
        user_agent TEXT,
        last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "User sessions table created successfully<br>";

    // Create user_activities table
    $sql = "CREATE TABLE IF NOT EXISTS user_activities (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        activity_type VARCHAR(50) NOT NULL,
        description TEXT,
        ip_address VARCHAR(45),
        user_agent TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "User activities table created successfully<br>";

    // Create user_preferences table
    $sql = "CREATE TABLE IF NOT EXISTS user_preferences (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL UNIQUE,
        theme VARCHAR(20) DEFAULT 'light',
        language VARCHAR(10) DEFAULT 'en',
        timezone VARCHAR(50) DEFAULT 'UTC',
        notification_settings JSON,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "User preferences table created successfully<br>";

    // Create role_permissions table
    $sql = "CREATE TABLE IF NOT EXISTS role_permissions (
        id INT PRIMARY KEY AUTO_INCREMENT,
        role VARCHAR(50) NOT NULL,
        permission VARCHAR(100) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_role_permission (role, permission)
    )";
    $pdo->exec($sql);
    echo "Role permissions table created successfully<br>";

    // Create user_roles_history table
    $sql = "CREATE TABLE IF NOT EXISTS user_roles_history (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        previous_role VARCHAR(50) NOT NULL,
        new_role VARCHAR(50) NOT NULL,
        changed_by INT NOT NULL,
        reason TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (changed_by) REFERENCES users(id)
    )";
    $pdo->exec($sql);
    echo "User roles history table created successfully<br>";

    // Create user_security_logs table
    $sql = "CREATE TABLE IF NOT EXISTS user_security_logs (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        event_type ENUM('login', 'logout', 'password_change', 'email_change', 'role_change', 'security_settings_change') NOT NULL,
        ip_address VARCHAR(45),
        user_agent TEXT,
        status ENUM('success', 'failed', 'blocked') NOT NULL,
        details TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "User security logs table created successfully<br>";

    // --- ROLES & PERMISSIONS SYSTEM ---
    // 1. Create roles table
    $sql = "CREATE TABLE IF NOT EXISTS roles (
        id INT PRIMARY KEY AUTO_INCREMENT,
        role_name VARCHAR(50) UNIQUE NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "Roles table created successfully<br>";

    // 2. Create permissions table
    $sql = "CREATE TABLE IF NOT EXISTS permissions (
        id INT PRIMARY KEY AUTO_INCREMENT,
        permission_name VARCHAR(100) UNIQUE NOT NULL,
        module VARCHAR(50),
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "Permissions table created successfully<br>";

    // 3. Create role_permissions table (mapping by IDs)
    $sql = "CREATE TABLE IF NOT EXISTS role_permissions (
        id INT PRIMARY KEY AUTO_INCREMENT,
        role_id INT NOT NULL,
        permission_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_role_permission (role_id, permission_id),
        FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
        FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "Role-Permissions mapping table created successfully<br>";

    // Insert roles
    $rolesData = [
        ['client', 'Client user'],
        ['lawyer', 'Lawyer user'],
        ['paralegal', 'Paralegal user'],
        ['super_paralegal', 'Super Paralegal user'],
        ['lawschool', 'Law School user'],
        ['admin', 'Admin user'],
        ['superadmin', 'Superadmin user']
    ];
    $stmt = $pdo->prepare("INSERT IGNORE INTO roles (role_name, description) VALUES (?, ?)");
    foreach ($rolesData as $r) {
        $stmt->execute($r);
    }
    echo "Roles seeded<br>";

    // Insert permissions
    $permissionsData = [
        // Dashboard
        ['view_dashboard', 'Dashboard', 'Access to dashboard and overview statistics'],
        ['view_analytics', 'Dashboard', 'Access to detailed analytics and reports'],
        // Case Management
        ['view_cases', 'Cases', 'View case listings and details'],
        ['create_cases', 'Cases', 'Create new cases'],
        ['edit_cases', 'Cases', 'Edit existing cases'],
        ['delete_cases', 'Cases', 'Delete cases'],
        ['assign_cases', 'Cases', 'Assign cases to team members'],
        ['close_cases', 'Cases', 'Close or archive cases'],
        ['view_case_history', 'Cases', 'View case history and audit logs'],
        // Document Management
        ['view_documents', 'Documents', 'View case documents'],
        ['upload_documents', 'Documents', 'Upload new documents'],
        ['edit_documents', 'Documents', 'Edit document details'],
        ['delete_documents', 'Documents', 'Delete documents'],
        ['download_documents', 'Documents', 'Download documents'],
        ['share_documents', 'Documents', 'Share documents with others'],
        // Calendar
        ['view_calendar', 'Calendar', 'View calendar events'],
        ['create_events', 'Calendar', 'Create calendar events'],
        ['edit_events', 'Calendar', 'Edit calendar events'],
        ['delete_events', 'Calendar', 'Delete calendar events'],
        ['manage_hearings', 'Calendar', 'Manage court hearings'],
        // Tasks
        ['view_tasks', 'Tasks', 'View tasks'],
        ['create_tasks', 'Tasks', 'Create new tasks'],
        ['edit_tasks', 'Tasks', 'Edit existing tasks'],
        ['delete_tasks', 'Tasks', 'Delete tasks'],
        ['assign_tasks', 'Tasks', 'Assign tasks to team members'],
        ['complete_tasks', 'Tasks', 'Mark tasks as complete'],
        // Billing
        ['view_billing', 'Billing', 'View billing information'],
        ['create_invoices', 'Billing', 'Create new invoices'],
        ['edit_invoices', 'Billing', 'Edit existing invoices'],
        ['delete_invoices', 'Billing', 'Delete invoices'],
        ['process_payments', 'Billing', 'Process payments'],
        ['view_payment_history', 'Billing', 'View payment history'],
        // User Management
        ['view_users', 'Users', 'View user listings'],
        ['create_users', 'Users', 'Create new users'],
        ['edit_users', 'Users', 'Edit user details'],
        ['delete_users', 'Users', 'Delete users'],
        ['manage_roles', 'Users', 'Manage user roles and permissions'],
        ['view_user_activity', 'Users', 'View user activity logs'],
        // Organization
        ['view_organizations', 'Organizations', 'View organization listings'],
        ['create_organizations', 'Organizations', 'Create new organizations'],
        ['edit_organizations', 'Organizations', 'Edit organization details'],
        ['delete_organizations', 'Organizations', 'Delete organizations'],
        // System Settings
        ['view_settings', 'Settings', 'View system settings'],
        ['edit_settings', 'Settings', 'Edit system settings'],
        ['manage_backups', 'Settings', 'Manage system backups'],
        ['view_audit_logs', 'Settings', 'View system audit logs'],
        // Communication
        ['send_notifications', 'Communication', 'Send system notifications'],
        ['manage_templates', 'Communication', 'Manage email and document templates'],
        ['view_messages', 'Communication', 'View internal messages'],
        ['send_messages', 'Communication', 'Send internal messages'],
        // Reports
        ['view_reports', 'Reports', 'View system reports'],
        ['generate_reports', 'Reports', 'Generate custom reports'],
        ['export_reports', 'Reports', 'Export reports to different formats'],
        // Security
        ['manage_security', 'Security', 'Manage security settings'],
        ['view_security_logs', 'Security', 'View security logs'],
        ['manage_2fa', 'Security', 'Manage two-factor authentication'],
        ['manage_api_keys', 'Security', 'Manage API keys and integrations']
    ];
    $stmt = $pdo->prepare("INSERT IGNORE INTO permissions (permission_name, module, description) VALUES (?, ?, ?)");
    foreach ($permissionsData as $p) {
        $stmt->execute($p);
    }
    echo "Permissions seeded<br>";

    // Map roles to permissions
    $roleMap = [
        'superadmin' => 'all',
        'admin' => [
            // All except some security features
            'view_dashboard','view_analytics','view_cases','create_cases','edit_cases','delete_cases','assign_cases','close_cases','view_case_history',
            'view_documents','upload_documents','edit_documents','delete_documents','download_documents','share_documents',
            'view_calendar','create_events','edit_events','delete_events','manage_hearings',
            'view_tasks','create_tasks','edit_tasks','delete_tasks','assign_tasks','complete_tasks',
            'view_billing','create_invoices','edit_invoices','delete_invoices','process_payments','view_payment_history',
            'view_users','create_users','edit_users','delete_users','manage_roles','view_user_activity',
            'view_organizations','create_organizations','edit_organizations','delete_organizations',
            'view_settings','edit_settings','view_audit_logs',
            'send_notifications','manage_templates','view_messages','send_messages',
            'view_reports','generate_reports','export_reports',
            'manage_security','view_security_logs','manage_2fa'
        ],
        'lawyer' => [
            'view_dashboard','view_analytics','view_cases','create_cases','edit_cases','assign_cases','close_cases','view_case_history',
            'view_documents','upload_documents','edit_documents','download_documents','share_documents',
            'view_calendar','create_events','edit_events','manage_hearings',
            'view_tasks','create_tasks','edit_tasks','assign_tasks','complete_tasks',
            'view_billing','create_invoices','edit_invoices','process_payments','view_payment_history',
            'view_users','view_organizations','send_notifications','view_messages','send_messages',
            'view_reports','generate_reports','export_reports'
        ],
        'super_paralegal' => [
            'view_dashboard','view_analytics','view_cases','create_cases','edit_cases','assign_cases','view_case_history',
            'view_documents','upload_documents','edit_documents','download_documents','share_documents',
            'view_calendar','create_events','edit_events','manage_hearings',
            'view_tasks','create_tasks','edit_tasks','assign_tasks','complete_tasks',
            'view_billing','view_payment_history','view_users','view_organizations','send_notifications','view_messages','send_messages',
            'view_reports','generate_reports','export_reports'
        ],
        'paralegal' => [
            'view_dashboard','view_cases','view_documents','upload_documents','download_documents','view_calendar','create_events','view_tasks','create_tasks','complete_tasks','view_billing','view_payment_history','view_messages','send_messages','view_reports'
        ],
        'lawschool' => [
            'view_dashboard','view_cases','view_documents','download_documents','view_calendar','view_tasks','view_reports','view_messages','send_messages'
        ],
        'client' => [
            'view_dashboard','view_cases','view_documents','download_documents','view_calendar','view_tasks','view_billing','view_payment_history','view_messages','send_messages'
        ]
    ];
    // Get all roles and permissions from DB
    $roles = $pdo->query("SELECT id, role_name FROM roles")->fetchAll(PDO::FETCH_KEY_PAIR);
    $permissions = $pdo->query("SELECT id, permission_name FROM permissions")->fetchAll(PDO::FETCH_KEY_PAIR);
    $stmt = $pdo->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
    foreach ($roleMap as $roleName => $perms) {
        $roleId = $roles[$roleName] ?? null;
        if (!$roleId) continue;
        if ($perms === 'all') {
            foreach ($permissions as $permId) {
                $stmt->execute([$roleId, $permId]);
            }
        } else {
            foreach ($perms as $permName) {
                $permId = array_search($permName, $permissions);
                if ($permId) $stmt->execute([$roleId, $permId]);
            }
        }
    }
    echo "Role-permission mappings seeded<br>";

    // Create organizations table
    $sql = "CREATE TABLE IF NOT EXISTS organizations (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        type ENUM('law_firm', 'law_school', 'legal_department') NOT NULL,
        address TEXT,
        phone VARCHAR(20),
        email VARCHAR(100),
        website VARCHAR(100),
        subscription_status ENUM('active', 'inactive', 'expired') DEFAULT 'inactive',
        subscription_end_date DATE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "Organizations table created successfully<br>";

    // Create cases table
    $sql = "CREATE TABLE IF NOT EXISTS cases (
        id INT PRIMARY KEY AUTO_INCREMENT,
        case_number VARCHAR(50) UNIQUE NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        case_type VARCHAR(100),
        status ENUM('open', 'pending', 'closed') DEFAULT 'open',
        client_id INT,
        lawyer_id INT,
        paralegal_id INT,
        court_name VARCHAR(100),
        filing_date DATE,
        next_hearing_date DATE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (client_id) REFERENCES users(id),
        FOREIGN KEY (lawyer_id) REFERENCES users(id),
        FOREIGN KEY (paralegal_id) REFERENCES users(id)
    )";
    $pdo->exec($sql);
    echo "Cases table created successfully<br>";

    // Create documents table
    $sql = "CREATE TABLE IF NOT EXISTS documents (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        file_path VARCHAR(255) NOT NULL,
        file_type VARCHAR(50),
        case_id INT,
        uploaded_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (case_id) REFERENCES cases(id),
        FOREIGN KEY (uploaded_by) REFERENCES users(id)
    )";
    $pdo->exec($sql);
    echo "Documents table created successfully<br>";

    // Create calendar_events table
    $sql = "CREATE TABLE IF NOT EXISTS calendar_events (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        event_type ENUM('hearing', 'meeting', 'deadline', 'other') NOT NULL,
        start_datetime DATETIME NOT NULL,
        end_datetime DATETIME NOT NULL,
        case_id INT,
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (case_id) REFERENCES cases(id),
        FOREIGN KEY (created_by) REFERENCES users(id)
    )";
    $pdo->exec($sql);
    echo "Calendar events table created successfully<br>";

    // Create billing table
    $sql = "CREATE TABLE IF NOT EXISTS billing (
        id INT PRIMARY KEY AUTO_INCREMENT,
        case_id INT,
        client_id INT,
        amount DECIMAL(10,2) NOT NULL,
        description TEXT,
        status ENUM('pending', 'paid', 'overdue') DEFAULT 'pending',
        due_date DATE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (case_id) REFERENCES cases(id),
        FOREIGN KEY (client_id) REFERENCES users(id)
    )";
    $pdo->exec($sql);
    echo "Billing table created successfully<br>";

    // Create tasks table
    $sql = "CREATE TABLE IF NOT EXISTS tasks (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        case_id INT,
        assigned_to INT,
        assigned_by INT,
        priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
        status ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending',
        due_date DATE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (case_id) REFERENCES cases(id),
        FOREIGN KEY (assigned_to) REFERENCES users(id),
        FOREIGN KEY (assigned_by) REFERENCES users(id)
    )";
    $pdo->exec($sql);
    echo "Tasks table created successfully<br>";

    // Create notifications table
    $sql = "CREATE TABLE IF NOT EXISTS notifications (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT,
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        type ENUM('info', 'warning', 'success', 'error') DEFAULT 'info',
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";
    $pdo->exec($sql);
    echo "Notifications table created successfully<br>";

    // Create audit_logs table
    $sql = "CREATE TABLE IF NOT EXISTS audit_logs (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT,
        action VARCHAR(255) NOT NULL,
        entity_type VARCHAR(50),
        entity_id INT,
        details TEXT,
        ip_address VARCHAR(45),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";
    $pdo->exec($sql);
    echo "Audit logs table created successfully<br>";

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

    echo "All tables and initial data created successfully!";

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 