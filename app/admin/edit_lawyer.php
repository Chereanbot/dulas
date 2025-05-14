<?php
require_once '../config/database.php';
require_once 'include/header.php';
require_once 'include/sidebar.php';

$db = getDB();

// Get lawyer ID from URL
$lawyer_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get lawyer details
$lawyer_query = "SELECT u.*, 
    (SELECT GROUP_CONCAT(specialty_id) FROM lawyer_specialties WHERE lawyer_id = u.id) as specialty_ids
    FROM users u 
    WHERE u.id = :id AND u.role = 'lawyer'";
$lawyer = $db->fetchOne($lawyer_query, [':id' => $lawyer_id]);

if (!$lawyer) {
    die("Lawyer not found");
}

// Get offices for dropdown
$offices = $db->fetchAll("SELECT id, name FROM offices WHERE status = 'ACTIVE'");

// Get specialties for dropdown
$specialties = $db->fetchAll("SELECT id, name FROM specialties WHERE status = 'active'");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->beginTransaction();

        // Update lawyer details
        $update_query = "UPDATE users SET 
            full_name = :full_name,
            email = :email,
            phone = :phone,
            username = :username,
            organization_id = :organization_id,
            status = :status
            WHERE id = :id AND role = 'lawyer'";

        $params = [
            ':id' => $lawyer_id,
            ':full_name' => $_POST['full_name'],
            ':email' => $_POST['email'],
            ':phone' => $_POST['phone'],
            ':username' => $_POST['username'],
            ':organization_id' => $_POST['organization_id'],
            ':status' => $_POST['status']
        ];

        // Update password if provided
        if (!empty($_POST['password'])) {
            $update_query = str_replace('username = :username,', 'username = :username, password = :password,', $update_query);
            $params[':password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }

        $db->executeQuery($update_query, $params);

        // Update specialties
        $db->executeQuery("DELETE FROM lawyer_specialties WHERE lawyer_id = :lawyer_id", [':lawyer_id' => $lawyer_id]);
        
        if (!empty($_POST['specialties'])) {
            $specialty_values = [];
            $specialty_params = [];
            
            foreach ($_POST['specialties'] as $index => $specialty_id) {
                $specialty_values[] = "(:lawyer_id, :specialty_id_$index)";
                $specialty_params[":specialty_id_$index"] = $specialty_id;
            }
            
            $specialty_params[':lawyer_id'] = $lawyer_id;
            
            $specialty_query = "INSERT INTO lawyer_specialties (lawyer_id, specialty_id) VALUES " . 
                             implode(', ', $specialty_values);
            
            $db->executeQuery($specialty_query, $specialty_params);
        }

        $db->commit();
        $success_message = "Lawyer details updated successfully!";
        
        // Refresh lawyer data
        $lawyer = $db->fetchOne($lawyer_query, [':id' => $lawyer_id]);
    } catch (Exception $e) {
        $db->rollback();
        $error_message = "Error updating lawyer: " . $e->getMessage();
    }
}
?>

<style>
:root {
    --primary-green: #00572d;
    --secondary-green: #1f9345;
    --accent-yellow: #f3c300;
    --text-primary: #333333;
    --text-secondary: #666666;
    --background-light: #f8f9fa;
    --background-white: #ffffff;
    --border-color: #e0e0e0;
}

.content-wrapper {
    margin-left: 250px;
    padding: 20px;
    min-height: 100vh;
    background: var(--background-light);
}

.edit-form {
    background: var(--background-white);
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    padding: 25px;
    margin-top: 20px;
    border: 1px solid rgba(0,0,0,0.05);
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    font-weight: 500;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}

.form-control {
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 0.75rem 1rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: var(--primary-green);
    box-shadow: 0 0 0 0.2rem rgba(0,87,45,0.25);
}

.btn-primary {
    background: linear-gradient(45deg, var(--primary-green), var(--secondary-green));
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background: linear-gradient(45deg, var(--secondary-green), var(--primary-green));
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.btn-secondary {
    background: var(--text-secondary);
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-secondary:hover {
    background: var(--text-primary);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.alert {
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1.5rem;
}

.alert-success {
    background: rgba(31,147,69,0.1);
    border: 1px solid var(--secondary-green);
    color: var(--secondary-green);
}

.alert-danger {
    background: rgba(220,53,69,0.1);
    border: 1px solid #dc3545;
    color: #dc3545;
}

.select2-container--default .select2-selection--multiple {
    border: 1px solid var(--border-color);
    border-radius: 8px;
}

.select2-container--default.select2-container--focus .select2-selection--multiple {
    border-color: var(--primary-green);
    box-shadow: 0 0 0 0.2rem rgba(0,87,45,0.25);
}

.select2-container--default .select2-selection--multiple .select2-selection__choice {
    background: var(--primary-green);
    border: none;
    color: white;
    border-radius: 4px;
    padding: 2px 8px;
}

.select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    color: white;
    margin-right: 5px;
}

.select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
    color: var(--accent-yellow);
}
</style>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Edit Lawyer</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <div class="edit-form">
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="full_name">Full Name</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" 
                                       value="<?php echo htmlspecialchars($lawyer['full_name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($lawyer['email']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($lawyer['phone']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo htmlspecialchars($lawyer['username']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="password">New Password (leave blank to keep current)</label>
                                <input type="password" class="form-control" id="password" name="password">
                            </div>
                            <div class="form-group">
                                <label for="organization_id">Office</label>
                                <select class="form-control" id="organization_id" name="organization_id" required>
                                    <option value="">Select Office</option>
                                    <?php foreach ($offices as $office): ?>
                                        <option value="<?php echo $office['id']; ?>" 
                                                <?php echo $office['id'] == $lawyer['organization_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($office['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="specialties">Specialties</label>
                                <select class="form-control" id="specialties" name="specialties[]" multiple required>
                                    <?php 
                                    $selected_specialties = explode(',', $lawyer['specialty_ids'] ?? '');
                                    foreach ($specialties as $specialty): 
                                    ?>
                                        <option value="<?php echo $specialty['id']; ?>" 
                                                <?php echo in_array($specialty['id'], $selected_specialties) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($specialty['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="active" <?php echo $lawyer['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo $lawyer['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i>Save Changes
                            </button>
                            <a href="lawyers.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-2"></i>Back to Lawyers
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2 for specialties
    $('#specialties').select2({
        placeholder: "Select specialties",
        allowClear: true,
        theme: "classic"
    });

    // Form validation
    $('form').on('submit', function(e) {
        let isValid = true;
        const requiredFields = ['full_name', 'email', 'phone', 'username', 'organization_id', 'status'];
        
        requiredFields.forEach(field => {
            const input = $(`#${field}`);
            if (!input.val()) {
                input.addClass('is-invalid');
                isValid = false;
            } else {
                input.removeClass('is-invalid');
            }
        });

        if ($('#specialties').val().length === 0) {
            $('#specialties').addClass('is-invalid');
            isValid = false;
        } else {
            $('#specialties').removeClass('is-invalid');
        }

        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields.');
        }
    });
});
</script>

<?php require_once 'include/footer.php'; ?> 