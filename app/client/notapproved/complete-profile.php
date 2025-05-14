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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->beginTransaction();

        // Update user information
        $stmt = $db->prepare("UPDATE users SET 
            phone = ?,
            updated_at = CURRENT_TIMESTAMP
            WHERE id = ?");
        $stmt->execute([
            $_POST['phone'],
            $userId
        ]);

        // Update or insert client profile
        $stmt = $db->prepare("INSERT INTO client_profiles 
            (user_id, age, sex, family_members, health_status, region, zone, wereda, kebele, house_number, case_type, case_category)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            age = VALUES(age),
            sex = VALUES(sex),
            family_members = VALUES(family_members),
            health_status = VALUES(health_status),
            region = VALUES(region),
            zone = VALUES(zone),
            wereda = VALUES(wereda),
            kebele = VALUES(kebele),
            house_number = VALUES(house_number),
            case_type = VALUES(case_type),
            case_category = VALUES(case_category)");

        $stmt->execute([
            $userId,
            $_POST['age'],
            $_POST['sex'],
            $_POST['family_members'],
            $_POST['health_status'],
            $_POST['region'],
            $_POST['zone'],
            $_POST['wereda'],
            $_POST['kebele'],
            $_POST['house_number'],
            $_POST['case_type'],
            $_POST['case_category']
        ]);

        // Handle ID proof upload
        if (isset($_FILES['id_proof']) && $_FILES['id_proof']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../../../uploads/id_proofs/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = uniqid() . '_' . basename($_FILES['id_proof']['name']);
            $uploadFile = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['id_proof']['tmp_name'], $uploadFile)) {
                $stmt = $db->prepare("UPDATE client_profiles SET id_proof_path = ? WHERE user_id = ?");
                $stmt->execute(['uploads/id_proofs/' . $fileName, $userId]);
            }
        }

        $db->commit();
        $_SESSION['success_message'] = "Profile updated successfully!";
        header('Location: dashboard.php');
        exit();

    } catch (Exception $e) {
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

        /* Timeline Styles */
        .timeline {
            position: relative;
            padding: 2rem 0;
            margin-bottom: 3rem;
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

        /* Form Steps */
        .form-step {
            display: none;
        }

        .form-step.active {
            display: block;
        }

        .form-step-header {
            margin-bottom: 2rem;
            text-align: center;
        }

        .form-step-header i {
            font-size: 2rem;
            color: var(--primary-green);
            margin-bottom: 1rem;
        }

        /* Navigation Buttons */
        .form-navigation {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #dee2e6;
        }

        .btn-navigation {
            padding: 0.8rem 2rem;
            font-weight: 500;
        }

        .btn-navigation i {
            margin-right: 0.5rem;
        }

        /* Form Fields */
        .form-label {
            font-weight: 500;
            color: #495057;
        }

        .required-field::after {
            content: '*';
            color: #dc3545;
            margin-left: 4px;
        }

        /* Progress Bar */
        .progress {
            height: 8px;
            border-radius: 4px;
            margin-bottom: 2rem;
        }

        .progress-bar {
            background-color: var(--primary-green);
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

        <!-- Timeline -->
        <div class="timeline">
            <div class="timeline-item">
                <div class="timeline-content">
                    <h5><i class="fas fa-user-check"></i> Account Created</h5>
                    <p class="text-muted mb-0">Your account has been successfully created.</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-content">
                    <h5><i class="fas fa-crown"></i> Service Selected</h5>
                    <p class="text-muted mb-0">You've selected the <?php echo ucfirst($_SESSION['selected_service']); ?> service plan.</p>
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

        <!-- Progress Bar -->
        <div class="progress">
            <div class="progress-bar" role="progressbar" style="width: 50%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
        </div>

        <!-- Form Steps -->
        <form method="POST" action="" enctype="multipart/form-data" id="profileForm">
            <!-- Step 1: Personal Information -->
            <div class="form-step active" id="step1">
                <div class="form-step-header">
                    <i class="fas fa-user"></i>
                    <h4>Personal Information</h4>
                    <p class="text-muted">Please provide your basic personal details</p>
                </div>

                <div class="row">
                    <!-- Basic Information -->
                    <div class="col-12 mb-4">
                        <h5 class="border-bottom pb-2">Basic Information</h5>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label required-field">Phone Number</label>
                        <input type="tel" class="form-control" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label required-field">Age</label>
                        <input type="number" class="form-control" name="age" value="<?php echo htmlspecialchars($user['age'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label required-field">Sex</label>
                        <select class="form-select" name="sex" required>
                            <option value="">Select Sex</option>
                            <option value="male" <?php echo ($user['sex'] ?? '') === 'male' ? 'selected' : ''; ?>>Male</option>
                            <option value="female" <?php echo ($user['sex'] ?? '') === 'female' ? 'selected' : ''; ?>>Female</option>
                            <option value="other" <?php echo ($user['sex'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label required-field">Marital Status</label>
                        <select class="form-select" name="marital_status" required>
                            <option value="">Select Status</option>
                            <option value="single" <?php echo ($user['marital_status'] ?? '') === 'single' ? 'selected' : ''; ?>>Single</option>
                            <option value="married" <?php echo ($user['marital_status'] ?? '') === 'married' ? 'selected' : ''; ?>>Married</option>
                            <option value="divorced" <?php echo ($user['marital_status'] ?? '') === 'divorced' ? 'selected' : ''; ?>>Divorced</option>
                            <option value="widowed" <?php echo ($user['marital_status'] ?? '') === 'widowed' ? 'selected' : ''; ?>>Widowed</option>
                        </select>
                    </div>

                    <!-- Professional Information -->
                    <div class="col-12 mb-4 mt-4">
                        <h5 class="border-bottom pb-2">Professional Information</h5>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label required-field">Occupation</label>
                        <input type="text" class="form-control" name="occupation" value="<?php echo htmlspecialchars($user['occupation'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label required-field">Education Level</label>
                        <select class="form-select" name="education_level" required>
                            <option value="">Select Education Level</option>
                            <option value="none" <?php echo ($user['education_level'] ?? '') === 'none' ? 'selected' : ''; ?>>No Formal Education</option>
                            <option value="primary" <?php echo ($user['education_level'] ?? '') === 'primary' ? 'selected' : ''; ?>>Primary School</option>
                            <option value="secondary" <?php echo ($user['education_level'] ?? '') === 'secondary' ? 'selected' : ''; ?>>Secondary School</option>
                            <option value="diploma" <?php echo ($user['education_level'] ?? '') === 'diploma' ? 'selected' : ''; ?>>Diploma</option>
                            <option value="degree" <?php echo ($user['education_level'] ?? '') === 'degree' ? 'selected' : ''; ?>>Bachelor's Degree</option>
                            <option value="masters" <?php echo ($user['education_level'] ?? '') === 'masters' ? 'selected' : ''; ?>>Master's Degree</option>
                            <option value="phd" <?php echo ($user['education_level'] ?? '') === 'phd' ? 'selected' : ''; ?>>PhD</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label required-field">Income Level</label>
                        <select class="form-select" name="income_level" required>
                            <option value="">Select Income Level</option>
                            <option value="low" <?php echo ($user['income_level'] ?? '') === 'low' ? 'selected' : ''; ?>>Low</option>
                            <option value="medium" <?php echo ($user['income_level'] ?? '') === 'medium' ? 'selected' : ''; ?>>Medium</option>
                            <option value="high" <?php echo ($user['income_level'] ?? '') === 'high' ? 'selected' : ''; ?>>High</option>
                        </select>
                    </div>

                    <!-- Family Information -->
                    <div class="col-12 mb-4 mt-4">
                        <h5 class="border-bottom pb-2">Family Information</h5>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label required-field">Number of Family Members</label>
                        <input type="number" class="form-control" name="family_members" value="<?php echo htmlspecialchars($user['family_members'] ?? ''); ?>" required>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label required-field">Health Status</label>
                        <textarea class="form-control" name="health_status" rows="3" required><?php echo htmlspecialchars($user['health_status'] ?? ''); ?></textarea>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Disability Status</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="disability_status" value="1" id="disabilityStatus" <?php echo ($user['disability_status'] ?? false) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="disabilityStatus">
                                I have a disability
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3 disability-type-field" style="display: none;">
                        <label class="form-label">Type of Disability</label>
                        <input type="text" class="form-control" name="disability_type" value="<?php echo htmlspecialchars($user['disability_type'] ?? ''); ?>">
                    </div>

                    <!-- Emergency Contact -->
                    <div class="col-12 mb-4 mt-4">
                        <h5 class="border-bottom pb-2">Emergency Contact</h5>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label required-field">Contact Name</label>
                        <input type="text" class="form-control" name="emergency_contact_name" value="<?php echo htmlspecialchars($user['emergency_contact_name'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label required-field">Contact Phone</label>
                        <input type="tel" class="form-control" name="emergency_contact_phone" value="<?php echo htmlspecialchars($user['emergency_contact_phone'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label required-field">Relationship</label>
                        <input type="text" class="form-control" name="emergency_contact_relationship" value="<?php echo htmlspecialchars($user['emergency_contact_relationship'] ?? ''); ?>" required>
                    </div>

                    <!-- Communication Preferences -->
                    <div class="col-12 mb-4 mt-4">
                        <h5 class="border-bottom pb-2">Communication Preferences</h5>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label required-field">Preferred Language</label>
                        <select class="form-select" name="preferred_language" required>
                            <option value="">Select Language</option>
                            <option value="amharic" <?php echo ($user['preferred_language'] ?? '') === 'amharic' ? 'selected' : ''; ?>>Amharic</option>
                            <option value="oromo" <?php echo ($user['preferred_language'] ?? '') === 'oromo' ? 'selected' : ''; ?>>Oromo</option>
                            <option value="tigrinya" <?php echo ($user['preferred_language'] ?? '') === 'tigrinya' ? 'selected' : ''; ?>>Tigrinya</option>
                            <option value="english" <?php echo ($user['preferred_language'] ?? '') === 'english' ? 'selected' : ''; ?>>English</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label required-field">Preferred Communication Method</label>
                        <select class="form-select" name="preferred_communication_method" required>
                            <option value="">Select Method</option>
                            <option value="phone" <?php echo ($user['preferred_communication_method'] ?? '') === 'phone' ? 'selected' : ''; ?>>Phone</option>
                            <option value="email" <?php echo ($user['preferred_communication_method'] ?? '') === 'email' ? 'selected' : ''; ?>>Email</option>
                            <option value="in_person" <?php echo ($user['preferred_communication_method'] ?? '') === 'in_person' ? 'selected' : ''; ?>>In Person</option>
                        </select>
                    </div>
                </div>

                <div class="form-navigation">
                    <div></div>
                    <button type="button" class="btn btn-primary btn-navigation" onclick="nextStep(1)">
                        Next <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- Step 2: Location Information -->
            <div class="form-step" id="step2">
                <div class="form-step-header">
                    <i class="fas fa-map-marker-alt"></i>
                    <h4>Location Information</h4>
                    <p class="text-muted">Please provide your address details</p>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label required-field">Region</label>
                        <input type="text" class="form-control" name="region" value="<?php echo htmlspecialchars($user['region'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label required-field">Zone</label>
                        <input type="text" class="form-control" name="zone" value="<?php echo htmlspecialchars($user['zone'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label required-field">Wereda</label>
                        <input type="text" class="form-control" name="wereda" value="<?php echo htmlspecialchars($user['wereda'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label required-field">Kebele</label>
                        <input type="text" class="form-control" name="kebele" value="<?php echo htmlspecialchars($user['kebele'] ?? ''); ?>" required>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label required-field">House Number</label>
                        <input type="text" class="form-control" name="house_number" value="<?php echo htmlspecialchars($user['house_number'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="form-navigation">
                    <button type="button" class="btn btn-outline-primary btn-navigation" onclick="prevStep(2)">
                        <i class="fas fa-arrow-left"></i> Previous
                    </button>
                    <button type="button" class="btn btn-primary btn-navigation" onclick="nextStep(2)">
                        Next <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- Step 3: Case Information -->
            <div class="form-step" id="step3">
                <div class="form-step-header">
                    <i class="fas fa-gavel"></i>
                    <h4>Case Information</h4>
                    <p class="text-muted">Please provide details about your legal case</p>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label required-field">Case Type</label>
                        <select class="form-select" name="case_type" required>
                            <option value="">Select Case Type</option>
                            <option value="civil" <?php echo ($user['case_type'] ?? '') === 'civil' ? 'selected' : ''; ?>>Civil</option>
                            <option value="criminal" <?php echo ($user['case_type'] ?? '') === 'criminal' ? 'selected' : ''; ?>>Criminal</option>
                            <option value="family" <?php echo ($user['case_type'] ?? '') === 'family' ? 'selected' : ''; ?>>Family</option>
                            <option value="property" <?php echo ($user['case_type'] ?? '') === 'property' ? 'selected' : ''; ?>>Property</option>
                            <option value="other" <?php echo ($user['case_type'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label required-field">Case Category</label>
                        <select class="form-select" name="case_category" required>
                            <option value="">Select Case Category</option>
                            <option value="divorce" <?php echo ($user['case_category'] ?? '') === 'divorce' ? 'selected' : ''; ?>>Divorce</option>
                            <option value="inheritance" <?php echo ($user['case_category'] ?? '') === 'inheritance' ? 'selected' : ''; ?>>Inheritance</option>
                            <option value="contract" <?php echo ($user['case_category'] ?? '') === 'contract' ? 'selected' : ''; ?>>Contract</option>
                            <option value="property_dispute" <?php echo ($user['case_category'] ?? '') === 'property_dispute' ? 'selected' : ''; ?>>Property Dispute</option>
                            <option value="other" <?php echo ($user['case_category'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label required-field">ID Proof</label>
                        <input type="file" class="form-control" name="id_proof" accept=".pdf,.jpg,.jpeg,.png" required>
                        <small class="text-muted">Upload a valid ID proof (PDF, JPG, or PNG)</small>
                    </div>
                </div>

                <div class="form-navigation">
                    <button type="button" class="btn btn-outline-primary btn-navigation" onclick="prevStep(3)">
                        <i class="fas fa-arrow-left"></i> Previous
                    </button>
                    <button type="submit" class="btn btn-success btn-navigation">
                        <i class="fas fa-check"></i> Submit
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentStep = 1;
        const totalSteps = 3;

        function updateProgressBar() {
            const progress = ((currentStep - 1) / (totalSteps - 1)) * 100;
            document.querySelector('.progress-bar').style.width = `${progress}%`;
            document.querySelector('.progress-bar').setAttribute('aria-valuenow', progress);
        }

        function showStep(step) {
            document.querySelectorAll('.form-step').forEach(el => {
                el.classList.remove('active');
            });
            document.getElementById(`step${step}`).classList.add('active');
            currentStep = step;
            updateProgressBar();
        }

        function nextStep(step) {
            if (validateStep(step)) {
                showStep(step + 1);
            }
        }

        function prevStep(step) {
            showStep(step - 1);
        }

        function validateStep(step) {
            const currentStepElement = document.getElementById(`step${step}`);
            const requiredFields = currentStepElement.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            if (!isValid) {
                alert('Please fill in all required fields');
            }

            return isValid;
        }

        // Initialize progress bar
        updateProgressBar();

        // Add this to your existing JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            const disabilityCheckbox = document.getElementById('disabilityStatus');
            const disabilityTypeField = document.querySelector('.disability-type-field');

            disabilityCheckbox.addEventListener('change', function() {
                disabilityTypeField.style.display = this.checked ? 'block' : 'none';
            });

            // Trigger on page load
            if (disabilityCheckbox.checked) {
                disabilityTypeField.style.display = 'block';
            }
        });
    </script>
</body>
</html> 