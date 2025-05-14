<?php
require_once('database.php');

function checkMaintenanceMode() {
    $db = getDB();
    
    try {
        // Get current maintenance status
        $maintenance = $db->fetchOne(
            "SELECT * FROM system_maintenance 
             WHERE is_maintenance_mode = TRUE 
             AND (end_time IS NULL OR end_time > NOW())
             ORDER BY id DESC LIMIT 1"
        );

        if ($maintenance) {
            // If user is not superadmin, show maintenance page
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
                // Check if current page is not the maintenance page
                $currentPage = basename($_SERVER['PHP_SELF']);
                if ($currentPage !== 'maintenance.php') {
                    header('Location: /dulas/maintenance.php');
                    exit();
                }
            }
        }
    } catch (Exception $e) {
        error_log("Error checking maintenance mode: " . $e->getMessage());
    }
}

// Call the function
checkMaintenanceMode(); 