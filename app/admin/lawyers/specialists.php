<?php
require_once '../../config/database.php';
require_once '../include/header.php';
require_once '../include/sidebar.php';

// REST API: If ?json=1, return specializations as JSON
if (isset($_GET['json']) && $_GET['json'] == '1') {
    $db = getDB();
    $query = "SELECT 
        ls.specialization,
        GROUP_CONCAT(DISTINCT u.full_name) as lawyers
    FROM lawyer_specializations ls
    JOIN lawyer_profiles lp ON ls.lawyer_id = lp.id
    JOIN users u ON lp.user_id = u.id
    WHERE u.status = 'active'
    GROUP BY ls.specialization
    ORDER BY ls.specialization ASC";
    $specializations = $db->fetchAll($query);
    header('Content-Type: application/json');
    echo json_encode($specializations);
    exit;
}

// Get all specializations with lawyer counts
$db = getDB();
$query = "SELECT 
    specialization,
    COUNT(DISTINCT ls.lawyer_id) as lawyer_count,
    GROUP_CONCAT(DISTINCT u.full_name) as lawyers
FROM lawyer_specializations ls
JOIN lawyer_profiles lp ON ls.lawyer_id = lp.id
JOIN users u ON lp.user_id = u.id
WHERE u.status = 'active'
GROUP BY specialization
ORDER BY lawyer_count DESC";

$specializations = $db->fetchAll($query);

// Get all active lawyers for the add/edit form
$lawyers_query = "SELECT id, full_name FROM users WHERE role = 'lawyer' AND status = 'active' ORDER BY full_name ASC";
$lawyers = $db->fetchAll($lawyers_query);
?>

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

.specialization-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    margin-bottom: 20px;
    transition: transform 0.2s;
}

.specialization-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.specialization-header {
    background: var(--primary-green);
    color: white;
    padding: 15px 20px;
    border-radius: 8px 8px 0 0;
}

.specialization-body {
    padding: 20px;
}

.lawyer-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.lawyer-list li {
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}

.lawyer-list li:last-child {
    border-bottom: none;
}

.badge-count {
    background: var(--accent-yellow);
    color: var(--text-primary);
    padding: 5px 10px;
    border-radius: 15px;
    font-weight: 600;
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

.modal-open .select2-container {
    z-index: 9999;
}
</style>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Lawyer Specializations</h1>
                </div>
                <div class="col-sm-6">
                    <div class="float-sm-right">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addSpecializationModal">
                            <i class="fas fa-plus"></i> Add Specialization
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <?php foreach ($specializations as $spec): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="specialization-card">
                        <div class="specialization-header">
                            <h5 class="mb-0">
                                <?php echo htmlspecialchars($spec['specialization']); ?>
                                <span class="badge badge-count float-right">
                                    <?php echo $spec['lawyer_count']; ?> Lawyers
                                </span>
                            </h5>
                        </div>
                        <div class="specialization-body">
                            <ul class="lawyer-list">
                                <?php 
                                $lawyers = explode(',', $spec['lawyers']);
                                foreach ($lawyers as $lawyer): 
                                ?>
                                <li>
                                    <i class="fas fa-user-tie text-primary mr-2"></i>
                                    <?php echo htmlspecialchars($lawyer); ?>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</div>

<!-- Add Specialization Modal -->
<div class="modal fade" id="addSpecializationModal" tabindex="-1" role="dialog" aria-labelledby="addSpecializationModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addSpecializationModalLabel">Add Specialization</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addSpecializationForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="lawyer">Select Lawyer</label>
                        <select class="form-control select2" id="lawyer" name="lawyer_id" required>
                            <option value="">Select a lawyer</option>
                            <?php foreach ($lawyers as $lawyer): ?>
                            <option value="<?php echo $lawyer['id']; ?>">
                                <?php echo htmlspecialchars($lawyer['full_name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="specialization">Specialization</label>
                        <input type="text" class="form-control" id="specialization" name="specialization" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Specialization</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Specialization Modal -->
<div class="modal fade" id="editSpecializationModal" tabindex="-1" role="dialog" aria-labelledby="editSpecializationModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSpecializationModalLabel">Edit Specializations</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editSpecializationForm">
                <div class="modal-body">
                    <input type="hidden" id="edit-lawyer-id" name="lawyer_id">
                    <div class="form-group">
                        <label for="edit-specializations">Specializations</label>
                        <select class="form-control select2" id="edit-specializations" name="specializations[]" multiple>
                            <?php
                            $all_specializations = array_unique(array_column($specializations, 'specialization'));
                            foreach ($all_specializations as $spec):
                            ?>
                            <option value="<?php echo htmlspecialchars($spec); ?>">
                                <?php echo htmlspecialchars($spec); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
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

<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%',
        placeholder: 'Select options',
        allowClear: true,
        tags: true,
        tokenSeparators: [',', ' '],
        dropdownParent: $('#addSpecializationModal, #editSpecializationModal')
    });

    // Add Specialization Form Submit
    $('#addSpecializationForm').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        
        $.ajax({
            url: 'ajax/add_specialization.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#addSpecializationModal').modal('hide');
                    location.reload();
                } else {
                    alert('Error adding specialization: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                alert('Error occurred while adding specialization');
            }
        });
    });

    // Edit Specialization Form Submit
    $('#editSpecializationForm').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        
        $.ajax({
            url: 'ajax/update_specializations.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#editSpecializationModal').modal('hide');
                    location.reload();
                } else {
                    alert('Error updating specializations: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                alert('Error occurred while updating specializations');
            }
        });
    });

    // Handle Edit Button Click
    $('.edit-specialization').on('click', function() {
        const lawyerId = $(this).data('id');
        const specializations = $(this).data('specializations').split(',');
        
        $('#edit-lawyer-id').val(lawyerId);
        $('#edit-specializations').val(specializations).trigger('change');
        $('#editSpecializationModal').modal('show');
    });
});
</script>

<?php require_once '../include/footer.php'; ?> 