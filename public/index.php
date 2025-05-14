<?php
// Include configuration
require_once '../config/database.php';

// Start session
session_start();

// Set default timezone
date_default_timezone_set('UTC');

// Basic routing
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Header
include '../includes/header.php';

// Main content
switch($page) {
    case 'home':
        include '../src/pages/home.php';
        break;
    default:
        include '../src/pages/404.php';
        break;
}

// Footer
include '../includes/footer.php';
?> 