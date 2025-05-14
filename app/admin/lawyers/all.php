<?php
require_once '../../config/database.php';
require_once '../include/header.php';
require_once '../include/sidebar.php';

// Get all lawyers with their profiles and specializations
$db = getDB();
$query = "SELECT 
    u.id, u.username, u.email, u.full_name, u.status,
    lp.bar_number, lp.years_of_experience, lp.hourly_rate,
    lp.current_cases, lp.max_cases, lp.availability_status,
    GROUP_CONCAT(DISTINCT ls.specialization) as specializations,
    COUNT(DISTINCT lw.case_id) as active_cases,
    AVG(lr.rating) as average_rating
FROM users u
LEFT JOIN lawyer_profiles lp ON u.id = lp.user_id
LEFT JOIN lawyer_specializations ls ON lp.id = ls.lawyer_id
LEFT JOIN lawyer_workload lw ON lp.id = lw.lawyer_id AND lw.status = 'active'
LEFT JOIN lawyer_ratings lr ON lp.id = lr.lawyer_id
WHERE u.role = 'lawyer'
GROUP BY u.id";

$lawyers = $db->fetchAll($query);
?>

<!-- Add Select2 CSS and JS files in the head section -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
/* Custom spacing and layout styles */
:root {
    --primary-green: #00572d;
    --secondary-green: #1f9345;
    --accent-yellow: #f3c300;
    --text-primary: #333333;
    --background: #ffffff;
    --background-light: #f4f4f4;
    --footer-dark: #1a1a1a;
}

.content-wrapper {
    margin-left: var(--sidebar-width);
    padding: calc(var(--header-height) + 20px) 20px 20px 20px;
    min-height: 100vh;
    background-color: var(--background-light);
}

.content-header {
    padding: 15px 0;
    margin-bottom: 20px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.content-header h1 {
    margin: 0;
    font-size: 24px;
    font-weight: 600;
    color: #2c3e50;
}

.small-box {
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    margin-bottom: 20px;
}

.card {
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    margin-bottom: 20px;
}

.card-header {
    background-color: #fff;
    border-bottom: 1px solid #f4f6f9;
    padding: 15px 20px;
}

.card-body {
    padding: 20px;
}

.table {
    margin-bottom: 0;
}

.table th {
    border-top: none;
    background-color: #f8f9fa;
    font-weight: 600;
}

.table td {
    vertical-align: middle;
}

.btn-group {
    gap: 5px;
}

.modal-content {
    border-radius: 8px;
    border: none;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.modal-header {
    background-color: var(--primary-green);
    color: white;
    border-bottom: none;
    border-radius: 8px 8px 0 0;
    padding: 1rem 1.5rem;
}

.modal-header .close {
    color: white;
    text-shadow: none;
    opacity: 0.8;
}

.modal-header .close:hover {
    opacity: 1;
}

.modal-body {
    padding: 1.5rem;
    background-color: var(--background);
}

.modal-footer {
    background-color: var(--background-light);
    border-top: 1px solid rgba(0, 0, 0, 0.1);
    padding: 1rem 1.5rem;
    border-radius: 0 0 8px 8px;
}

.form-group {
    margin-bottom: 1.5rem;
}

.select2-container--bootstrap4 .select2-selection {
    border-radius: 4px;
}

.progress {
    height: 8px;
    border-radius: 4px;
}

.badge {
    padding: 5px 10px;
    border-radius: 4px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .content-wrapper {
        margin-left: 0;
        padding: calc(var(--header-height) + 15px) 15px 15px 15px;
    }
    
    .content-header {
        padding: 10px 0;
    }
    
    .card-body {
        padding: 15px;
    }
}

/* Form Elements */
.form-control:focus {
    border-color: var(--secondary-green);
    box-shadow: 0 0 0 0.2rem rgba(31, 147, 69, 0.25);
}

.btn-primary {
    background-color: var(--primary-green);
    border-color: var(--primary-green);
}

.btn-primary:hover {
    background-color: var(--secondary-green);
    border-color: var(--secondary-green);
}

.btn-secondary {
    background-color: var(--background-light);
    border-color: #ddd;
    color: var(--text-primary);
}

.btn-secondary:hover {
    background-color: #e9ecef;
    border-color: #ddd;
    color: var(--text-primary);
}

.btn-danger {
    background-color: #dc3545;
    border-color: #dc3545;
}

/* Table Styling */
.table th {
    background-color: var(--primary-green);
    color: white;
    font-weight: 500;
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 87, 45, 0.05);
}

/* Badge Styling */
.badge-success {
    background-color: var(--primary-green);
}

.badge-warning {
    background-color: var(--accent-yellow);
    color: var(--text-primary);
}

.badge-info {
    background-color: var(--secondary-green);
}

/* Progress Bar */
.progress-bar {
    background-color: var(--primary-green);
}

/* Select2 Customization */
.select2-container--bootstrap4 .select2-selection--multiple {
    border-color: #ddd;
}

.select2-container--bootstrap4 .select2-selection--multiple:focus {
    border-color: var(--secondary-green);
    box-shadow: 0 0 0 0.2rem rgba(31, 147, 69, 0.25);
}

.select2-container--bootstrap4 .select2-results__option--highlighted[aria-selected] {
    background-color: var(--primary-green);
}

/* Rating Stars */
.rating .fas.fa-star.text-warning {
    color: var(--accent-yellow) !important;
}

/* Action Buttons */
.btn-group .btn {
    padding: 0.375rem 0.75rem;
}

.btn-info {
    background-color: var(--secondary-green);
    border-color: var(--secondary-green);
}

.btn-info:hover {
    background-color: #1a7a3a;
    border-color: #1a7a3a;
}

/* Search Box */
.input-group .form-control {
    border-right: none;
}

.input-group .btn-default {
    background-color: var(--primary-green);
    border-color: var(--primary-green);
    color: white;
}

.input-group .btn-default:hover {
    background-color: var(--secondary-green);
    border-color: var(--secondary-green);
}

/* Enhanced Statistics Cards */
.stats-card {
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    position: relative;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: none;
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.stats-card .inner {
    position: relative;
    z-index: 1;
}

.stats-card .icon {
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 48px;
    opacity: 0.2;
    z-index: 0;
}

.stats-card h3 {
    font-size: 32px;
    font-weight: 700;
    margin: 0 0 5px 0;
    color: #fff;
}

.stats-card p {
    font-size: 16px;
    margin: 0;
    color: rgba(255, 255, 255, 0.9);
}

.stats-card .trend {
    position: absolute;
    bottom: 15px;
    right: 20px;
    font-size: 14px;
    color: rgba(255, 255, 255, 0.9);
}

.stats-card .trend i {
    margin-right: 5px;
}

.stats-card.primary {
    background: linear-gradient(45deg, var(--primary-green), #007a3d);
}

.stats-card.success {
    background: linear-gradient(45deg, var(--secondary-green), #28a745);
}

.stats-card.warning {
    background: linear-gradient(45deg, var(--accent-yellow), #ffc107);
}

.stats-card.info {
    background: linear-gradient(45deg, #17a2b8, #20c997);
}

.stats-card .stats-icon {
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 15px;
}

.stats-card .stats-icon i {
    font-size: 24px;
    color: #fff;
}

.stats-card .stats-details {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-top: 15px;
}

.stats-card .stats-value {
    font-size: 28px;
    font-weight: 700;
    color: #fff;
}

.stats-card .stats-label {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.9);
    margin-top: 5px;
}

.stats-card .stats-trend {
    display: flex;
    align-items: center;
    font-size: 14px;
    color: rgba(255, 255, 255, 0.9);
}

.stats-card .stats-trend i {
    margin-right: 5px;
}

.stats-card .stats-trend.up {
    color: #fff;
}

.stats-card .stats-trend.down {
    color: rgba(255, 255, 255, 0.7);
}

/* Select2 Customization */
.select2-container--bootstrap4 .select2-selection {
    border: 1px solid #ced4da;
    border-radius: 4px;
    min-height: 38px;
}

.select2-container--bootstrap4 .select2-selection--multiple {
    padding: 0 5px;
}

.select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice {
    background-color: var(--primary-green);
    border: none;
    color: white;
    border-radius: 3px;
    padding: 2px 8px;
    margin: 3px;
}

.select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice__remove {
    color: white;
    margin-right: 5px;
}

.select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice__remove:hover {
    color: #fff;
    opacity: 0.8;
}

.select2-container--bootstrap4 .select2-dropdown {
    border-color: #ced4da;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.select2-container--bootstrap4 .select2-results__option--highlighted[aria-selected] {
    background-color: var(--primary-green);
}

.select2-container--bootstrap4 .select2-search__field {
    border: none !important;
    padding: 6px !important;
}

.select2-container--bootstrap4 .select2-selection--multiple .select2-selection__rendered {
    padding: 0 5px;
}

/* Fix for Select2 in modals */
.modal-open .select2-container {
    z-index: 9999;
}
</style>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Lawyers Management</h1>
                </div>
                <div class="col-sm-6">
                    <div class="float-sm-right">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addLawyerModal">
                            <i class="fas fa-plus"></i> Add New Lawyer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="stats-card primary">
                        <div class="inner">
                            <div class="stats-icon">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <div class="stats-details">
                                <div>
                                    <div class="stats-value"><?php echo count($lawyers); ?></div>
                                    <div class="stats-label">Total Lawyers</div>
                                </div>
                                <div class="stats-trend up">
                                    <i class="fas fa-arrow-up"></i>
                                    <span>5%</span>
                                </div>
                            </div>
                        </div>
                        <div class="icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="stats-card success">
                        <div class="inner">
                            <div class="stats-icon">
                                <i class="fas fa-briefcase"></i>
                            </div>
                            <div class="stats-details">
                                <div>
                                    <div class="stats-value"><?php echo array_reduce($lawyers, function($carry, $lawyer) { 
                                        return $carry + ($lawyer['active_cases'] ?? 0); 
                                    }, 0); ?></div>
                                    <div class="stats-label">Active Cases</div>
                                </div>
                                <div class="stats-trend up">
                                    <i class="fas fa-arrow-up"></i>
                                    <span>12%</span>
                                </div>
                            </div>
                        </div>
                        <div class="icon">
                            <i class="fas fa-briefcase"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="stats-card warning">
                        <div class="inner">
                            <div class="stats-icon">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <div class="stats-details">
                                <div>
                                    <div class="stats-value"><?php echo array_reduce($lawyers, function($carry, $lawyer) { 
                                        return $carry + ($lawyer['current_cases'] ?? 0); 
                                    }, 0); ?></div>
                                    <div class="stats-label">Total Assigned Cases</div>
                                </div>
                                <div class="stats-trend up">
                                    <i class="fas fa-arrow-up"></i>
                                    <span>8%</span>
                                </div>
                            </div>
                        </div>
                        <div class="icon">
                            <i class="fas fa-tasks"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="stats-card info">
                        <div class="inner">
                            <div class="stats-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="stats-details">
                                <div>
                                    <div class="stats-value"><?php echo array_reduce($lawyers, function($carry, $lawyer) { 
                                        return $carry + ($lawyer['years_of_experience'] ?? 0); 
                                    }, 0); ?></div>
                                    <div class="stats-label">Total Years Experience</div>
                                </div>
                                <div class="stats-trend up">
                                    <i class="fas fa-arrow-up"></i>
                                    <span>15%</span>
                                </div>
                            </div>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lawyers List -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Lawyers List</h3>
                    <div class="card-tools">
                        <div class="input-group input-group-sm" style="width: 250px;">
                            <input type="text" name="table_search" class="form-control float-right" placeholder="Search">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-default">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Bar Number</th>
                                <th>Specializations</th>
                                <th>Experience</th>
                                <th>Cases</th>
                                <th>Rating</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lawyers as $lawyer): ?>
                            <tr>
                                <td><?php echo $lawyer['id']; ?></td>
                                <td>
                                    <div class="user-block">
                                        <span class="username"><?php echo htmlspecialchars($lawyer['full_name']); ?></span>
                                        <span class="description"><?php echo htmlspecialchars($lawyer['email']); ?></span>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($lawyer['bar_number'] ?? 'N/A'); ?></td>
                                <td>
                                    <?php 
                                    if ($lawyer['specializations']) {
                                        $specs = explode(',', $lawyer['specializations']);
                                        foreach ($specs as $spec) {
                                            echo '<span class="badge badge-info mr-1">' . htmlspecialchars($spec) . '</span>';
                                        }
                                    } else {
                                        echo 'No specializations';
                                    }
                                    ?>
                                </td>
                                <td><?php echo $lawyer['years_of_experience'] ?? '0'; ?> years</td>
                                <td>
                                    <div class="progress-group">
                                        <?php 
                                        $current = $lawyer['current_cases'] ?? 0;
                                        $max = $lawyer['max_cases'] ?? 10;
                                        $percentage = ($current / $max) * 100;
                                        ?>
                                        <span class="float-right"><b><?php echo $current; ?></b>/<?php echo $max; ?></span>
                                        <div class="progress">
                                            <div class="progress-bar bg-primary" style="width: <?php echo $percentage; ?>%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($lawyer['average_rating']): ?>
                                        <div class="rating">
                                            <?php
                                            $rating = round($lawyer['average_rating']);
                                            for ($i = 1; $i <= 5; $i++) {
                                                echo '<i class="fas fa-star ' . ($i <= $rating ? 'text-warning' : 'text-muted') . '"></i>';
                                            }
                                            ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">No ratings</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = [
                                        'active' => 'success',
                                        'inactive' => 'danger',
                                        'suspended' => 'warning'
                                    ][$lawyer['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge badge-<?php echo $statusClass; ?>">
                                        <?php echo ucfirst($lawyer['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-info btn-sm view-lawyer" data-id="<?php echo $lawyer['id']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-primary btn-sm edit-lawyer" data-id="<?php echo $lawyer['id']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm delete-lawyer" data-id="<?php echo $lawyer['id']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Add Lawyer Modal -->
<div class="modal fade" id="addLawyerModal" tabindex="-1" role="dialog" aria-labelledby="addLawyerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addLawyerModalLabel">Add New Lawyer</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addLawyerForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="form-group">
                                <label for="full_name">Full Name</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bar_number">Bar Number</label>
                                <input type="text" class="form-control" id="bar_number" name="bar_number">
                            </div>
                            <div class="form-group">
                                <label for="years_of_experience">Years of Experience</label>
                                <input type="number" class="form-control" id="years_of_experience" name="years_of_experience" min="0">
                            </div>
                            <div class="form-group">
                                <label for="hourly_rate">Hourly Rate</label>
                                <input type="number" class="form-control" id="hourly_rate" name="hourly_rate" step="0.01" min="0">
                            </div>
                            <div class="form-group">
                                <label for="max_cases">Maximum Cases</label>
                                <input type="number" class="form-control" id="max_cases" name="max_cases" min="1" value="10">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="specializations">Specializations</label>
                                <select class="form-control select2" id="specializations" name="specializations[]" multiple>
                                    <?php
                                    $specializations = $db->fetchAll("SELECT DISTINCT specialization FROM lawyer_specializations");
                                    foreach ($specializations as $spec) {
                                        echo '<option value="' . htmlspecialchars($spec['specialization']) . '">' . 
                                             htmlspecialchars($spec['specialization']) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="education">Education</label>
                                <textarea class="form-control" id="education" name="education" rows="3"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="bio">Bio</label>
                                <textarea class="form-control" id="bio" name="bio" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Lawyer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Lawyer Modal -->
<div class="modal fade" id="viewLawyerModal" tabindex="-1" role="dialog" aria-labelledby="viewLawyerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewLawyerModalLabel">Lawyer Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Personal Information</h6>
                        <table class="table table-bordered">
                            <tr>
                                <th>Full Name</th>
                                <td id="view-fullname"></td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td id="view-email"></td>
                            </tr>
                            <tr>
                                <th>Bar Number</th>
                                <td id="view-bar-number"></td>
                            </tr>
                            <tr>
                                <th>Experience</th>
                                <td id="view-experience"></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Professional Details</h6>
                        <table class="table table-bordered">
                            <tr>
                                <th>Specializations</th>
                                <td id="view-specializations"></td>
                            </tr>
                            <tr>
                                <th>Hourly Rate</th>
                                <td id="view-hourly-rate"></td>
                            </tr>
                            <tr>
                                <th>Current Cases</th>
                                <td id="view-current-cases"></td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td id="view-status"></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>Active Cases</h6>
                        <div id="view-active-cases" class="table-responsive">
                            <!-- Active cases will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="view-to-edit">Edit Lawyer</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Lawyer Modal -->
<div class="modal fade" id="editLawyerModal" tabindex="-1" role="dialog" aria-labelledby="editLawyerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editLawyerModalLabel">Edit Lawyer</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editLawyerForm">
                <div class="modal-body">
                    <input type="hidden" id="edit-lawyer-id" name="id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit-fullname">Full Name</label>
                                <input type="text" class="form-control" id="edit-fullname" name="full_name" required>
                            </div>
                            <div class="form-group">
                                <label for="edit-email">Email</label>
                                <input type="email" class="form-control" id="edit-email" name="email" required>
                            </div>
                            <div class="form-group">
                                <label for="edit-bar-number">Bar Number</label>
                                <input type="text" class="form-control" id="edit-bar-number" name="bar_number">
                            </div>
                            <div class="form-group">
                                <label for="edit-experience">Years of Experience</label>
                                <input type="number" class="form-control" id="edit-experience" name="years_of_experience" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit-specializations">Specializations</label>
                                <select class="form-control select2" id="edit-specializations" name="specializations[]" multiple>
                                    <?php
                                    $specializations = $db->fetchAll("SELECT DISTINCT specialization FROM lawyer_specializations");
                                    foreach ($specializations as $spec) {
                                        echo '<option value="' . htmlspecialchars($spec['specialization']) . '">' . 
                                             htmlspecialchars($spec['specialization']) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit-hourly-rate">Hourly Rate</label>
                                <input type="number" class="form-control" id="edit-hourly-rate" name="hourly_rate" step="0.01" min="0">
                            </div>
                            <div class="form-group">
                                <label for="edit-max-cases">Maximum Cases</label>
                                <input type="number" class="form-control" id="edit-max-cases" name="max_cases" min="1" value="10">
                            </div>
                            <div class="form-group">
                                <label for="edit-status">Status</label>
                                <select class="form-control" id="edit-status" name="status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="suspended">Suspended</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteLawyerModal" tabindex="-1" role="dialog" aria-labelledby="deleteLawyerModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteLawyerModalLabel">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Are you sure you want to delete this lawyer? This action cannot be undone.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    let currentLawyerId = null;

    // Initialize Select2 with proper configuration
    function initializeSelect2() {
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: 'Select specializations',
            allowClear: true,
            tags: true,
            tokenSeparators: [',', ' '],
            dropdownParent: $('#addLawyerModal, #editLawyerModal')
        });
    }

    // Initialize Select2 when the page loads
    initializeSelect2();

    // Re-initialize Select2 when modals are shown
    $('#addLawyerModal, #editLawyerModal').on('shown.bs.modal', function() {
        initializeSelect2();
    });

    // View Lawyer
    $('.view-lawyer').on('click', function() {
        currentLawyerId = $(this).data('id');
        console.log('Viewing lawyer:', currentLawyerId); // Debug log
        
        // Fetch lawyer data
        $.ajax({
            url: 'ajax/get_lawyer.php',
            type: 'GET',
            data: { id: currentLawyerId },
            dataType: 'json',
            success: function(response) {
                console.log('Response:', response); // Debug log
                if (response.success) {
                    const lawyer = response.data;
                    
                    // Update modal content
                    $('#view-fullname').text(lawyer.full_name);
                    $('#view-email').text(lawyer.email);
                    $('#view-bar-number').text(lawyer.bar_number || 'N/A');
                    $('#view-experience').text(lawyer.years_of_experience + ' years');
                    $('#view-specializations').html(lawyer.specializations.map(spec => 
                        `<span class="badge badge-info mr-1">${spec}</span>`
                    ).join(''));
                    $('#view-hourly-rate').text('$' + lawyer.hourly_rate);
                    $('#view-current-cases').text(lawyer.current_cases + '/' + lawyer.max_cases);
                    $('#view-status').html(getStatusBadge(lawyer.status));
                    
                    // Load active cases
                    if (lawyer.active_cases && lawyer.active_cases.length > 0) {
                        let casesHtml = '<table class="table table-bordered">';
                        casesHtml += '<thead><tr><th>Case Number</th><th>Title</th><th>Status</th><th>Priority</th></tr></thead>';
                        casesHtml += '<tbody>';
                        lawyer.active_cases.forEach(case_ => {
                            casesHtml += `<tr>
                                <td>${case_.case_number}</td>
                                <td>${case_.title}</td>
                                <td>${getStatusBadge(case_.status)}</td>
                                <td>${getPriorityBadge(case_.priority)}</td>
                            </tr>`;
                        });
                        casesHtml += '</tbody></table>';
                        $('#view-active-cases').html(casesHtml);
                    } else {
                        $('#view-active-cases').html('<p class="text-muted">No active cases</p>');
                    }
                    
                    // Show the modal
                    $('#viewLawyerModal').modal('show');
                } else {
                    alert('Error loading lawyer data: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error); // Debug log
                alert('Error occurred while loading lawyer data');
            }
        });
    });

    // Edit Lawyer
    $('.edit-lawyer').on('click', function() {
        currentLawyerId = $(this).data('id');
        console.log('Editing lawyer:', currentLawyerId); // Debug log
        
        // Fetch lawyer data
        $.ajax({
            url: 'ajax/get_lawyer.php',
            type: 'GET',
            data: { id: currentLawyerId },
            dataType: 'json',
            success: function(response) {
                console.log('Response:', response); // Debug log
                if (response.success) {
                    const lawyer = response.data;
                    
                    // Update form fields
                    $('#edit-lawyer-id').val(lawyer.id);
                    $('#edit-fullname').val(lawyer.full_name);
                    $('#edit-email').val(lawyer.email);
                    $('#edit-bar-number').val(lawyer.bar_number);
                    $('#edit-experience').val(lawyer.years_of_experience);
                    $('#edit-hourly-rate').val(lawyer.hourly_rate);
                    $('#edit-max-cases').val(lawyer.max_cases);
                    $('#edit-status').val(lawyer.status);
                    
                    // Set specializations
                    const specializations = lawyer.specializations.map(spec => spec.specialization);
                    $('#edit-specializations').val(specializations).trigger('change');
                    
                    // Show the modal
                    $('#editLawyerModal').modal('show');
                } else {
                    alert('Error loading lawyer data: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error); // Debug log
                alert('Error occurred while loading lawyer data');
            }
        });
    });

    // View to Edit transition
    $('#view-to-edit').on('click', function() {
        $('#viewLawyerModal').modal('hide');
        setTimeout(() => {
            $('.edit-lawyer[data-id="' + currentLawyerId + '"]').click();
        }, 500);
    });

    // Handle Edit Form Submit
    $('#editLawyerForm').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        
        // Get specializations from Select2
        const specializations = $('#edit-specializations').select2('data').map(item => item.text);
        formData.append('specializations', specializations);
        
        $.ajax({
            url: 'ajax/update_lawyer.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#editLawyerModal').modal('hide');
                    location.reload();
                } else {
                    alert('Error updating lawyer: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                alert('Error occurred while updating lawyer');
            }
        });
    });

    // Delete Lawyer
    $('.delete-lawyer').on('click', function() {
        currentLawyerId = $(this).data('id');
        $('#deleteLawyerModal').modal('show');
    });
    
    $('#confirmDelete').on('click', function() {
        if (currentLawyerId) {
            $.ajax({
                url: 'ajax/delete_lawyer.php',
                type: 'POST',
                data: { id: currentLawyerId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#deleteLawyerModal').modal('hide');
                        location.reload();
                    } else {
                        alert('Error deleting lawyer: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error); // Debug log
                    alert('Error occurred while deleting lawyer');
                }
            });
        }
    });

    // Update the Add Lawyer Form Submit to handle Select2
    $('#addLawyerForm').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        
        // Get specializations from Select2
        const specializations = $('#specializations').select2('data').map(item => item.text);
        formData.append('specializations', specializations);
        
        $.ajax({
            url: 'ajax/add_lawyer.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#addLawyerModal').modal('hide');
                    location.reload();
                } else {
                    alert('Error adding lawyer: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                alert('Error occurred while adding lawyer');
            }
        });
    });

    // Helper functions for badges
    function getStatusBadge(status) {
        const badges = {
            'active': '<span class="badge badge-success">Active</span>',
            'inactive': '<span class="badge badge-danger">Inactive</span>',
            'suspended': '<span class="badge badge-warning">Suspended</span>'
        };
        return badges[status] || status;
    }

    function getPriorityBadge(priority) {
        const badges = {
            'low': '<span class="badge badge-info">Low</span>',
            'medium': '<span class="badge badge-warning">Medium</span>',
            'high': '<span class="badge badge-danger">High</span>'
        };
        return badges[priority] || priority;
    }

    // Search functionality
    $('input[name="table_search"]').on('keyup', function() {
        let value = $(this).val().toLowerCase();
        $("table tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    // Add smooth transitions for modals
    $('.modal').on('show.bs.modal', function() {
        $(this).find('.modal-dialog').css({
            transform: 'scale(0.8)',
            transition: 'transform 0.3s ease-out'
        });
        setTimeout(() => {
            $(this).find('.modal-dialog').css('transform', 'scale(1)');
        }, 50);
    });

    // Add loading state to buttons
    $('form').on('submit', function() {
        $(this).find('button[type="submit"]').prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...'
        );
    });

    // Add hover effect to table rows
    $('.table tbody tr').hover(
        function() { $(this).addClass('hover'); },
        function() { $(this).removeClass('hover'); }
    );
});
</script>

<?php require_once '../include/footer.php'; ?> 