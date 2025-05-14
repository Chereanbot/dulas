<?php
session_start();
require_once '../../../config/database.php';

// Check if user is logged in and is a client
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'client') {
    header('Location: /dulas/index.php');
    exit();
}

// Check if service type is set
if (!isset($_SESSION['selected_service']) || $_SESSION['selected_service'] !== 'pro') {
    header('Location: select-service.php');
    exit();
}

$db = getDB();
$userId = $_SESSION['user_id'];

// Get user information
$stmt = $db->prepare("SELECT u.*, cp.* FROM users u 
                     LEFT JOIN client_profiles cp ON u.id = cp.user_id 
                     WHERE u.id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();



// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Start transaction
        $db->beginTransaction();

        // Update user's service type
        $stmt = $db->prepare("UPDATE users SET service_type = 'pro' WHERE id = ?");
        $stmt->execute([$userId]);

        // Commit transaction
        $db->commit();
        
        // Redirect to profile completion
        header('Location: complete-profile.php');
        exit();

    } catch (Exception $e) {
        // Rollback transaction on error
        $db->rollBack();
        $error_message = "Error processing payment: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pro Service - Dulas Legal Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-green: #00572d;
            --secondary-green: #1f9345;
            --accent-yellow: #f3c300;
            --sidebar-width: 250px;
        }

        body {
            min-height: 100vh;
            background-color: #f8f9fa;
        }

        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            background: var(--primary-green);
            color: white;
            padding: 1rem;
            transition: all 0.3s ease;
        }

        .sidebar-header {
            padding: 1rem 0;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header img {
            width: 80px;
            margin-bottom: 1rem;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.8rem 1rem;
            border-radius: 5px;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }

        .nav-link:hover, .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }

        .nav-link i {
            width: 25px;
        }

        /* Main Content Styles */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
        }

        /* Header Styles */
        .top-header {
            background: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-green);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        /* Pro Service Styles */
        .pro-features {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .feature-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: transform 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            background: #e8f5e9;
            color: var(--primary-green);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 1rem;
        }

        .payment-options {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .payment-option {
            border: 2px solid #dee2e6;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .payment-option:hover {
            border-color: var(--primary-green);
        }

        .payment-option.selected {
            border-color: var(--primary-green);
            background: #e8f5e9;
        }

        .timeline {
            position: relative;
            padding: 2rem 0;
        }

        .timeline::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            width: 2px;
            height: 100%;
            background: var(--primary-green);
            transform: translateX(-50%);
        }

        .timeline-item {
            position: relative;
            margin-bottom: 2rem;
            width: 50%;
            padding-right: 2rem;
        }

        .timeline-item:nth-child(even) {
            margin-left: 50%;
            padding-right: 0;
            padding-left: 2rem;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            top: 0;
            right: -6px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--primary-green);
        }

        .timeline-item:nth-child(even)::before {
            right: auto;
            left: -6px;
        }

        .timeline-content {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <img src="/dulas/assets/images/logo.png" alt="Dulas Logo">
            <h5>Client Portal</h5>
        </div>
        <nav class="mt-4">
            <a href="dashboard.php" class="nav-link">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="select-service.php" class="nav-link active">
                <i class="fas fa-crown"></i> Select Service
            </a>
            <a href="/dulas/logout.php" class="nav-link text-danger">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Header -->
        <div class="top-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Pro Service Features</h4>
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($user['full_name'] ?? 'U', 0, 1)); ?>
                </div>
                <div>
                    <h6 class="mb-0"><?php echo htmlspecialchars($user['full_name'] ?? 'User'); ?></h6>
                    <small class="text-muted">Client</small>
                </div>
            </div>
        </div>

        <!-- Timeline -->
        <div class="timeline mb-5">
            <div class="timeline-item">
                <div class="timeline-content">
                    <h5><i class="fas fa-user-check"></i> Account Created</h5>
                    <p class="text-muted mb-0">Your account has been successfully created.</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-content">
                    <h5><i class="fas fa-crown"></i> Select Service</h5>
                    <p class="text-muted mb-0">You've selected the Pro service plan.</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-content">
                    <h5><i class="fas fa-user-edit"></i> Complete Profile</h5>
                    <p class="text-muted mb-0">Fill in your required information.</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-content">
                    <h5><i class="fas fa-check-circle"></i> Ready to Go</h5>
                    <p class="text-muted mb-0">Start using our legal services.</p>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Pro Features -->
            <div class="col-md-8">
                <div class="pro-features">
                    <h3 class="mb-4">Pro Service Features</h3>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="feature-card">
                                <div class="feature-icon">
                                    <i class="fas fa-gavel"></i>
                                </div>
                                <h5>Priority Legal Consultation</h5>
                                <p class="text-muted">Get immediate access to our legal experts for consultation.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="feature-card">
                                <div class="feature-icon">
                                    <i class="fas fa-file-contract"></i>
                                </div>
                                <h5>Full Document Preparation</h5>
                                <p class="text-muted">Professional preparation of all legal documents.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="feature-card">
                                <div class="feature-icon">
                                    <i class="fas fa-headset"></i>
                                </div>
                                <h5>24/7 Support</h5>
                                <p class="text-muted">Round-the-clock support for all your legal needs.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="feature-card">
                                <div class="feature-icon">
                                    <i class="fas fa-tasks"></i>
                                </div>
                                <h5>Advanced Case Management</h5>
                                <p class="text-muted">Comprehensive tracking and management of your cases.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Options -->
            <div class="col-md-4">
                <div class="payment-options">
                    <h3 class="mb-4">Payment Options</h3>
                    <form method="POST" action="">
                        <div class="payment-option selected">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" value="bank_transfer" checked>
                                <label class="form-check-label">
                                    <h6 class="mb-1">Bank Transfer</h6>
                                    <p class="text-muted mb-0">Pay directly to our bank account</p>
                                </label>
                            </div>
                        </div>
                        <div class="payment-option">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" value="mobile_money">
                                <label class="form-check-label">
                                    <h6 class="mb-1">Mobile Money</h6>
                                    <p class="text-muted mb-0">Pay using mobile money services</p>
                                </label>
                            </div>
                        </div>
                        <div class="payment-option">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" value="cash">
                                <label class="form-check-label">
                                    <h6 class="mb-1">Cash Payment</h6>
                                    <p class="text-muted mb-0">Pay in person at our office</p>
                                </label>
                            </div>
                        </div>

                        <div class="mt-4">
                            <h5>Pro Service Fee</h5>
                            <h3 class="text-success mb-4">ETB 5,000</h3>
                            <button type="submit" class="btn btn-success btn-lg w-100">
                                Continue to Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add click handler for payment options
        document.querySelectorAll('.payment-option').forEach(option => {
            option.addEventListener('click', function() {
                // Remove selected class from all options
                document.querySelectorAll('.payment-option').forEach(opt => {
                    opt.classList.remove('selected');
                });
                // Add selected class to clicked option
                this.classList.add('selected');
                // Check the radio button
                this.querySelector('input[type="radio"]').checked = true;
            });
        });
    </script>
</body>
</html> 