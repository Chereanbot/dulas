<?php
session_start();
require_once '../../../config/database.php';

// Check if user is logged in and is a client
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'client') {
    header('Location: /dulas/index.php');
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

// Check if user exists


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Start transaction
        $db->beginTransaction();

        // Update user information
        $stmt = $db->prepare("UPDATE users SET 
            phone = ?,
            age = ?,
            sex = ?,
            family_members = ?,
            health_status = ?
            WHERE id = ?");
        
        $stmt->execute([
            $_POST['phone'],
            $_POST['age'],
            $_POST['sex'],
            $_POST['family_members'],
            $_POST['health_status'],
            $userId
        ]);

        // Handle file upload
        $idProofPath = null;
        if (isset($_FILES['id_proof']) && $_FILES['id_proof']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../../../uploads/id_proofs/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = uniqid() . '_' . basename($_FILES['id_proof']['name']);
            $uploadFile = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['id_proof']['tmp_name'], $uploadFile)) {
                $idProofPath = 'uploads/id_proofs/' . $fileName;
            }
        }

        // Insert or update client profile
        $stmt = $db->prepare("INSERT INTO client_profiles (
            user_id, region, zone, wereda, kebele, house_number,
            case_type, case_category, id_proof_path, guidelines,
            notes, created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ON DUPLICATE KEY UPDATE
            region = VALUES(region),
            zone = VALUES(zone),
            wereda = VALUES(wereda),
            kebele = VALUES(kebele),
            house_number = VALUES(house_number),
            case_type = VALUES(case_type),
            case_category = VALUES(case_category),
            id_proof_path = COALESCE(VALUES(id_proof_path), id_proof_path),
            guidelines = VALUES(guidelines),
            notes = VALUES(notes),
            updated_at = NOW()");

        $stmt->execute([
            $userId,
            $_POST['region'],
            $_POST['zone'],
            $_POST['wereda'],
            $_POST['kebele'],
            $_POST['house_number'],
            $_POST['case_type'],
            $_POST['case_category'],
            $idProofPath,
            $_POST['guidelines'],
            $_POST['notes']
        ]);

        // Commit transaction
        $db->commit();
        
        // Redirect to dashboard with success message
        $_SESSION['success_message'] = "Profile updated successfully!";
        header('Location: dashboard.php');
        exit();

    } catch (Exception $e) {
        // Rollback transaction on error
        $db->rollBack();
        $error_message = "Error updating profile: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Profile - Dulas Legal Management System</title>
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

        /* Form Styles */
        .form-section {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .form-section h5 {
            color: var(--primary-green);
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--primary-green);
        }

        .required-field::after {
            content: " *";
            color: red;
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
            <a href="complete-profile.php" class="nav-link active">
                <i class="fas fa-user-edit"></i> Complete Profile
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
            <h4 class="mb-0">Complete Your Profile</h4>
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

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <!-- Personal Information -->
            <div class="form-section">
                <h5><i class="fas fa-user"></i> Personal Information</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label required-field">Phone Number</label>
                        <input type="tel" class="form-control" name="phone" required
                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label required-field">Age</label>
                        <input type="number" class="form-control" name="age" required
                               value="<?php echo htmlspecialchars($user['age'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label required-field">Sex</label>
                        <select class="form-select" name="sex" required>
                            <option value="">Select Sex</option>
                            <option value="M" <?php echo ($user['sex'] ?? '') === 'M' ? 'selected' : ''; ?>>Male</option>
                            <option value="F" <?php echo ($user['sex'] ?? '') === 'F' ? 'selected' : ''; ?>>Female</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label required-field">Number of Family Members</label>
                        <input type="number" class="form-control" name="family_members" required
                               value="<?php echo htmlspecialchars($user['family_members'] ?? ''); ?>">
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label required-field">Health Status</label>
                        <textarea class="form-control" name="health_status" rows="3" required><?php echo htmlspecialchars($user['health_status'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Location Information -->
            <div class="form-section">
                <h5><i class="fas fa-map-marker-alt"></i> Location Information</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label required-field">Region</label>
                        <input type="text" class="form-control" name="region" required
                               value="<?php echo htmlspecialchars($user['region'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label required-field">Zone</label>
                        <input type="text" class="form-control" name="zone" required
                               value="<?php echo htmlspecialchars($user['zone'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label required-field">Wereda</label>
                        <input type="text" class="form-control" name="wereda" required
                               value="<?php echo htmlspecialchars($user['wereda'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label required-field">Kebele</label>
                        <input type="text" class="form-control" name="kebele" required
                               value="<?php echo htmlspecialchars($user['kebele'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label required-field">House Number</label>
                        <input type="text" class="form-control" name="house_number" required
                               value="<?php echo htmlspecialchars($user['house_number'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <!-- Case Information -->
            <div class="form-section">
                <h5><i class="fas fa-gavel"></i> Case Information</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label required-field">Case Type</label>
                        <select class="form-select" name="case_type" required>
                            <option value="">Select Case Type</option>
                            <option value="CIVIL" <?php echo ($user['case_type'] ?? '') === 'CIVIL' ? 'selected' : ''; ?>>Civil</option>
                            <option value="CRIMINAL" <?php echo ($user['case_type'] ?? '') === 'CRIMINAL' ? 'selected' : ''; ?>>Criminal</option>
                            <option value="FAMILY" <?php echo ($user['case_type'] ?? '') === 'FAMILY' ? 'selected' : ''; ?>>Family</option>
                            <option value="LABOR" <?php echo ($user['case_type'] ?? '') === 'LABOR' ? 'selected' : ''; ?>>Labor</option>
                            <option value="PROPERTY" <?php echo ($user['case_type'] ?? '') === 'PROPERTY' ? 'selected' : ''; ?>>Property</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label required-field">Case Category</label>
                        <select class="form-select" name="case_category" required>
                            <option value="">Select Case Category</option>
                            <option value="CONSULTATION" <?php echo ($user['case_category'] ?? '') === 'CONSULTATION' ? 'selected' : ''; ?>>Consultation</option>
                            <option value="REPRESENTATION" <?php echo ($user['case_category'] ?? '') === 'REPRESENTATION' ? 'selected' : ''; ?>>Representation</option>
                            <option value="DOCUMENT_PREPARATION" <?php echo ($user['case_category'] ?? '') === 'DOCUMENT_PREPARATION' ? 'selected' : ''; ?>>Document Preparation</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Required Documents -->
            <div class="form-section">
                <h5><i class="fas fa-file-alt"></i> Required Documents</h5>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label required-field">ID Proof</label>
                        <input type="file" class="form-control" name="id_proof" accept=".pdf,.jpg,.jpeg,.png" required>
                        <small class="text-muted">Upload a clear copy of your ID (PDF, JPG, or PNG)</small>
                        <?php if (!empty($user['id_proof_path'])): ?>
                            <div class="mt-2">
                                <a href="/dulas/<?php echo htmlspecialchars($user['id_proof_path']); ?>" target="_blank" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> View Current ID Proof
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="form-section">
                <h5><i class="fas fa-info-circle"></i> Additional Information</h5>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Guidelines</label>
                        <textarea class="form-control" name="guidelines" rows="3"><?php echo htmlspecialchars($user['guidelines'] ?? ''); ?></textarea>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="3"><?php echo htmlspecialchars($user['notes'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="text-end mb-4">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Save Profile
                </button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 