<?php
session_start();
require_once 'app\config\database.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    $db = getDB();
    $userId = $_SESSION['user_id'];
    
    // Check if this is a first-time user
    $stmt = $db->prepare("SELECT u.*, cp.id as profile_id FROM users u 
                         LEFT JOIN client_profiles cp ON u.id = cp.user_id 
                         WHERE u.id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    // If user is a client and doesn't have a profile, redirect to not approved dashboard
    if ($user['user_role'] === 'client' && !$user['profile_id']) {
        header('Location: /dulas/app/client/notapproved/dashboard.php');
        exit();
    }

    // Regular role-based redirection
    switch ($user['user_role']) {
        case 'superadmin':
            header('Location: /dulas/app/superadmin/dashboard.php');
            break;
        case 'admin':
            header('Location: /dulas/app/admin/dashboard.php');
            break;
        case 'lawyer':
            header('Location: /dulas/app/lawyer/dashboard.php');
            break;
        case 'paralegal':
            header('Location: /dulas/app/paralegal/dashboard.php');
            break;
        case 'super_paralegal':
            header('Location: /dulas/app/super_paralegal/dashboard.php');
            break;
        case 'lawschool':
            header('Location: /dulas/app/lawschool/dashboard.php');
            break;
        case 'client':
            header('Location: /dulas/app/client/dashboard.php');
            break;
        default:
            header('Location: /login.php');
    }
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $db = $database->getConnection();

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        try {
            $stmt = $db->prepare("SELECT u.*, cp.id as profile_id FROM users u 
                                LEFT JOIN client_profiles cp ON u.id = cp.user_id 
                                WHERE u.username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                if ($user['status'] === 'active') {
                    // Log successful login
                    $logStmt = $db->prepare("INSERT INTO user_security_logs (user_id, event_type, ip_address, user_agent, status) VALUES (?, 'login', ?, ?, 'success')");
                    $logStmt->execute([$user['id'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]);

                    // Update last login
                    $updateStmt = $db->prepare("UPDATE users SET last_login = NOW(), last_login_ip = ?, login_attempts = 0 WHERE id = ?");
                    $updateStmt->execute([$_SERVER['REMOTE_ADDR'], $user['id']]);

                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['user_name'] = $user['full_name'];
                    $_SESSION['user_role'] = $user['role'];

                    // Check if this is a first-time client user
                    if ($user['role'] === 'client' && !$user['profile_id']) {
                        header('Location: /dulas/app/client/notapproved/dashboard.php');
                        exit();
                    }

                    // Regular role-based redirection
                    switch ($user['role']) {
                        case 'superadmin':
                            header('Location: /dulas/app/superadmin/dashboard.php');
                            break;
                        case 'admin':
                            header('Location: /dulas/app/admin/dashboard.php');
                            break;
                        case 'lawyer':
                            header('Location: /dulas/app/lawyer/dashboard.php');
                            break;
                        case 'paralegal':
                            header('Location: /dulas/app/paralegal/dashboard.php');
                            break;
                        case 'super_paralegal':
                            header('Location: /dulas/app/super_paralegal/dashboard.php');
                            break;
                        case 'lawschool':
                            header('Location: /dulas/app/lawschool/dashboard.php');
                            break;
                        case 'client':
                            header('Location: /dulas/app/client/dashboard.php');
                            break;
                    }
                    exit();
                } else {
                    $error = 'Your account is not active. Please contact support.';
                }
            } else {
                // Log failed login attempt
                if ($user) {
                    $logStmt = $db->prepare("INSERT INTO user_security_logs (user_id, event_type, ip_address, user_agent, status) VALUES (?, 'login', ?, ?, 'failed')");
                    $logStmt->execute([$user['id'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]);

                    // Update login attempts
                    $updateStmt = $db->prepare("UPDATE users SET login_attempts = login_attempts + 1 WHERE id = ?");
                    $updateStmt->execute([$user['id']]);
                }
                $error = 'Invalid username or password';
            }
        } catch (PDOException $e) {
            $error = 'An error occurred. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Dulas Legal Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-green: #00572d;
            --secondary-green: #1f9345;
            --accent-yellow: #f3c300;
            --text-primary: #333333;
            --background: #ffffff;
            --footer-dark: #1a1a1a;
        }

        body {
            background-color: var(--background);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--secondary-green) 100%);
        }

        .login-card {
            background: var(--background);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            width: 100%;
            max-width: 400px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header img {
            width: 120px;
            margin-bottom: 1rem;
        }

        .login-header h1 {
            color: var(--primary-green);
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: var(--text-primary);
            opacity: 0.8;
        }

        .form-control {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            border: 2px solid #e0e0e0;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-green);
            box-shadow: 0 0 0 0.2rem rgba(0, 87, 45, 0.25);
        }

        .input-group-text {
            background-color: transparent;
            border: 2px solid #e0e0e0;
            border-right: none;
        }

        .btn-login {
            background-color: var(--primary-green);
            border: none;
            border-radius: 8px;
            padding: 0.75rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            background-color: var(--secondary-green);
            transform: translateY(-2px);
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .loading-spinner {
            width: 100px;
            height: 100px;
        }

        .alert {
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .alert-danger {
            background-color: #fff3f3;
            border-color: #ffcdd2;
            color: #d32f2f;
        }

        .alert-success {
            background-color: #f1f8e9;
            border-color: #dcedc8;
            color: #388e3c;
        }

        .password-toggle {
            cursor: pointer;
            color: var(--text-primary);
            opacity: 0.7;
            transition: opacity 0.3s ease;
        }

        .password-toggle:hover {
            opacity: 1;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <img src="/assets/images/logo.png" alt="Dulas Logo">
                <h1>Welcome Back</h1>
                <p>Please login to your account</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="loginForm">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-user"></i>
                        </span>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <span class="input-group-text password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner">
            <svg width="100" height="100" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" aria-label="Loading">
                <circle cx="50" cy="50" r="35" stroke="#00572d" stroke-width="8" fill="none" stroke-linecap="round">
                    <animate attributeName="stroke-dashoffset" values="0;502" dur="2s" repeatCount="indefinite" />
                    <animate attributeName="stroke-dasharray" values="150.6 100.4;1 250;150.6 100.4" dur="2s" repeatCount="indefinite" />
                </circle>
                <circle cx="50" cy="50" r="25" stroke="#f3c300" stroke-width="5" fill="none" stroke-dasharray="39.3 39.3" stroke-linecap="round">
                    <animateTransform attributeName="transform" type="rotate" from="0 50 50" to="360 50 50" dur="1.5s" repeatCount="indefinite" />
                </circle>
                <circle cx="50" cy="50" r="15" fill="#1f9345">
                    <animate attributeName="r" values="15;12;15" dur="1.5s" repeatCount="indefinite" />
                </circle>
            </svg>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const icon = document.querySelector('.password-toggle i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        document.getElementById('loginForm').addEventListener('submit', function() {
            document.getElementById('loadingOverlay').style.display = 'flex';
        });
    </script>
</body>
</html>
