<?php
require_once '../config/database.php';

try {
    // Test database connection
    $db = getDB();
    if (!$db) {
        throw new Exception("Failed to connect to database");
    }
    
    // Enable error reporting
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    
    // Verify database connection
    try {
        $test_query = "SELECT 1";
        $db->execute($test_query);
        echo "Database connection verified successfully<br>";
    } catch (Exception $e) {
        echo "Database connection test failed: " . $e->getMessage() . "<br>";
        throw $e;
    }

    // Check if offices already exist
    try {
        $existing_offices = $db->fetchAll("SELECT name FROM offices");
        if (!empty($existing_offices)) {
            echo "Found existing offices:<br>";
            foreach ($existing_offices as $office) {
                echo "- " . $office['name'] . "<br>";
            }
            echo "Skipping office insertion as offices already exist.<br>";
        } else {
            // Insert Offices
            $offices = [
                [
                    'name' => 'Dilla Office',
                    'address' => 'Dilla Main Street, SNNPR',
                    'phone' => '+251912345678',
                    'email' => 'dilla@dulas.com',
                    'status' => 'ACTIVE'
                ],
                [
                    'name' => 'Yirga Chafe Office',
                    'address' => 'Yirga Chafe Center, SNNPR',
                    'phone' => '+251912345679',
                    'email' => 'yirgachafe@dulas.com',
                    'status' => 'ACTIVE'
                ],
                [
                    'name' => 'Bule Office',
                    'address' => 'Bule Town, SNNPR',
                    'phone' => '+251912345680',
                    'email' => 'bule@dulas.com',
                    'status' => 'ACTIVE'
                ],
                [
                    'name' => 'Cheleltu Office',
                    'address' => 'Cheleltu Town, SNNPR',
                    'phone' => '+251912345681',
                    'email' => 'cheleltu@dulas.com',
                    'status' => 'ACTIVE'
                ],
                [
                    'name' => 'Yega Office',
                    'address' => 'Yega Town, SNNPR',
                    'phone' => '+251912345682',
                    'email' => 'yega@dulas.com',
                    'status' => 'ACTIVE'
                ],
                [
                    'name' => 'Onago Office',
                    'address' => 'Onago Town, SNNPR',
                    'phone' => '+251912345683',
                    'email' => 'onago@dulas.com',
                    'status' => 'ACTIVE'
                ],
                [
                    'name' => 'Guange Office',
                    'address' => 'Guange Town, SNNPR',
                    'phone' => '+251912345684',
                    'email' => 'guange@dulas.com',
                    'status' => 'ACTIVE'
                ],
                [
                    'name' => 'Sobo Office',
                    'address' => 'Sobo Town, SNNPR',
                    'phone' => '+251912345685',
                    'email' => 'sobo@dulas.com',
                    'status' => 'ACTIVE'
                ]
            ];

            // Insert offices one by one with error handling
            foreach ($offices as $office) {
                try {
                    // Debug: Print the office data
                    echo "Attempting to insert office: " . $office['name'] . "<br>";
                    echo "Data: <pre>" . print_r($office, true) . "</pre><br>";

                    $query = "INSERT INTO offices (name, address, phone, email, status) 
                             VALUES (:name, :address, :phone, :email, :status)";
                    
                    // Debug: Print the query and parameters
                    echo "Query: " . $query . "<br>";
                    echo "Parameters: <pre>" . print_r($office, true) . "</pre><br>";

                    $db->execute($query, $office);
                    echo "Successfully inserted office: " . $office['name'] . "<br>";
                } catch (Exception $e) {
                    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                        echo "Office already exists: " . $office['name'] . "<br>";
                        continue;
                    }
                    echo "Error inserting office " . $office['name'] . ": " . $e->getMessage() . "<br>";
                    echo "Error details: " . $e->getTraceAsString() . "<br>";
                    throw $e;
                }
            }
            echo "All offices processed successfully<br>";
        }
    } catch (Exception $e) {
        echo "Error processing offices: " . $e->getMessage() . "<br>";
        echo "Error details: " . $e->getTraceAsString() . "<br>";
        throw $e;
    }

    // Get Dilla office ID
    try {
        $dilla_office = $db->fetchOne("SELECT id FROM offices WHERE name = 'Dilla Office'");
        if (!$dilla_office) {
            throw new Exception("Dilla office not found after insertion");
        }
        $dilla_office_id = $dilla_office['id'];
        echo "Dilla office ID retrieved successfully: " . $dilla_office_id . "<br>";
    } catch (Exception $e) {
        echo "Error getting Dilla office ID: " . $e->getMessage() . "<br>";
        throw $e;
    }

    // Check if users already exist
    try {
        $existing_users = $db->fetchAll("SELECT email FROM users WHERE organization_id = :office_id", ['office_id' => $dilla_office_id]);
        if (!empty($existing_users)) {
            echo "Found existing users for Dilla office:<br>";
            foreach ($existing_users as $user) {
                echo "- " . $user['email'] . "<br>";
            }
            echo "Skipping user insertion as users already exist.<br>";
        } else {
            // Insert Users (Lawyers and Paralegals)
            $users = [
                // Lawyers
                [
                    'username' => 'lawyer1',
                    'email' => 'lawyer1@dulas.com',
                    'password' => password_hash('password123', PASSWORD_DEFAULT),
                    'full_name' => 'Abebe Kebede',
                    'role' => 'lawyer',
                    'organization_id' => $dilla_office_id,
                    'phone' => '+251912345678',
                    'status' => 'active'
                ],
                [
                    'username' => 'lawyer2',
                    'email' => 'lawyer2@dulas.com',
                    'password' => password_hash('password123', PASSWORD_DEFAULT),
                    'full_name' => 'Kebede Alemu',
                    'role' => 'lawyer',
                    'organization_id' => $dilla_office_id,
                    'phone' => '+251912345679',
                    'status' => 'active'
                ],
                // Paralegals
                [
                    'username' => 'paralegal1',
                    'email' => 'paralegal1@dulas.com',
                    'password' => password_hash('password123', PASSWORD_DEFAULT),
                    'full_name' => 'Tigist Worku',
                    'role' => 'paralegal',
                    'organization_id' => $dilla_office_id,
                    'phone' => '+251912345680',
                    'status' => 'active'
                ],
                [
                    'username' => 'paralegal2',
                    'email' => 'paralegal2@dulas.com',
                    'password' => password_hash('password123', PASSWORD_DEFAULT),
                    'full_name' => 'Solomon Teklu',
                    'role' => 'paralegal',
                    'organization_id' => $dilla_office_id,
                    'phone' => '+251912345681',
                    'status' => 'active'
                ],
                [
                    'username' => 'paralegal3',
                    'email' => 'paralegal3@dulas.com',
                    'password' => password_hash('password123', PASSWORD_DEFAULT),
                    'full_name' => 'Mekdes Haile',
                    'role' => 'paralegal',
                    'organization_id' => $dilla_office_id,
                    'phone' => '+251912345682',
                    'status' => 'active'
                ]
            ];

            // Insert users one by one with error handling
            foreach ($users as $user) {
                try {
                    $query = "INSERT INTO users (username, email, password, full_name, role, organization_id, phone, status) 
                             VALUES (:username, :email, :password, :full_name, :role, :organization_id, :phone, :status)";
                    $db->execute($query, $user);
                    echo "Successfully inserted user: " . $user['email'] . "<br>";
                } catch (Exception $e) {
                    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                        echo "User already exists: " . $user['email'] . "<br>";
                        continue;
                    }
                    echo "Error inserting user " . $user['email'] . ": " . $e->getMessage() . "<br>";
                    throw $e;
                }
            }
            echo "All users processed successfully<br>";
        }
    } catch (Exception $e) {
        echo "Error processing users: " . $e->getMessage() . "<br>";
        throw $e;
    }

    echo "All sample data processed successfully!";

} catch (Exception $e) {
    echo "Fatal Error: " . $e->getMessage() . "<br>";
    echo "Stack trace: " . $e->getTraceAsString();
}
?> 