<?php
require_once '../../config/database.php';
require_once '../include/header.php';
require_once '../include/sidebar.php';

// Get all lawyers with their workload information
$db = getDB();
$query = "SELECT 
    u.id, u.full_name, u.email,
    lp.id as profile_id,
    lp.max_cases,
    lp.current_cases,
    COUNT(DISTINCT lw.case_id) as active_cases,
    GROUP_CONCAT(DISTINCT c.title) as case_titles,
    GROUP_CONCAT(DISTINCT c.priority) as case_priorities,
    GROUP_CONCAT(DISTINCT c.status) as case_statuses
FROM users u
JOIN lawyer_profiles lp ON u.id = lp.user_id
LEFT JOIN lawyer_workload lw ON lp.id = lw.lawyer_id AND lw.status = 'active'
LEFT JOIN cases c ON lw.case_id = c.id
WHERE u.role = 'lawyer' AND u.status = 'active'
GROUP BY u.id";

$lawyers = $db->fetchAll($query);

// Get all active cases for assignment
$cases_query = "SELECT 
    c.id, c.title, c.case_number, c.priority, c.status,
    c.created_at, c.due_date
FROM cases c
LEFT JOIN lawyer_workload lw ON c.id = lw.case_id AND lw.status = 'active'
WHERE c.status != 'closed' AND lw.id IS NULL
ORDER BY c.priority DESC, c.created_at ASC";

$available_cases = $db->fetchAll($cases_query);
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

.workload-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    margin-bottom: 20px;
    transition: transform 0.2s;
}

.workload-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.workload-header {
    background: var(--primary-green);
    color: white;
    padding: 15px 20px;
    border-radius: 8px 8px 0 0;
}

.workload-body {
    padding: 20px;
}

.case-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.case-list li {
    padding: 10px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.case-list li:last-child {
    border-bottom: none;
}

.progress {
    height: 8px;
    margin-top: 5px;
}

.badge-priority {
    padding: 5px 10px;
    border-radius: 15px;
    font-weight: 600;
}

.badge-priority.high {
    background-color: #dc3545;
    color: white;
}

.badge-priority.medium {
    background-color: var(--accent-yellow);
    color: var(--text-primary);
}

.badge-priority.low {
    background-color: var(--secondary-green);
    color: white;
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
                    <h1 class="m-0">Lawyer Workload Management</h1>
                </div>
                <div class="col-sm-6">
                    <div class="float-sm-right">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#assignCaseModal">
                            <i class="fas fa-plus"></i> Assign Case
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <?php foreach ($lawyers as $lawyer): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="workload-card">
                        <div class="workload-header">
                            <h5 class="mb-0">
                                <?php echo htmlspecialchars($lawyer['full_name']); ?>
                                <span class="badge badge-count float-right">
                                    <?php echo $lawyer['active_cases']; ?>/<?php echo $lawyer['max_cases']; ?> Cases
                                </span>
                            </h5>
                        </div>
                        <div class="workload-body">
                            <div class="progress-group mb-3">
                                <span class="float-right">
                                    <b><?php echo $lawyer['active_cases']; ?></b>/<?php echo $lawyer['max_cases']; ?>
                                </span>
                                <div class="progress">
                                    <?php 
                                    $percentage = ($lawyer['active_cases'] / $lawyer['max_cases']) * 100;
                                    $color = $percentage > 80 ? 'danger' : ($percentage > 60 ? 'warning' : 'success');
                                    ?>
                                    <div class="progress-bar bg-<?php echo $color; ?>" 
                                         style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                            </div>
                            
                            <?php if ($lawyer['case_titles']): ?>
                            <ul class="case-list">
                                <?php 
                                $titles = explode(',', $lawyer['case_titles']);
                                $priorities = explode(',', $lawyer['case_priorities']);
                                $statuses = explode(',', $lawyer['case_statuses']);
                                
                                for ($i = 0; $i < count($titles); $i++):
                                    $priority = $priorities[$i] ?? 'medium';
                                    $status = $statuses[$i] ?? 'active';
                                ?>
                                <li>
                                    <div>
                                        <strong><?php echo htmlspecialchars($titles[$i]); ?></strong>
                                        <div>
                                            <span class="badge badge-priority <?php echo $priority; ?>">
                                                <?php echo ucfirst($priority); ?>
                                            </span>
                                            <span class="badge badge-info">
                                                <?php echo ucfirst($status); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-danger remove-case" 
                                            data-lawyer-id="<?php echo $lawyer['id']; ?>"
                                            data-case-title="<?php echo htmlspecialchars($titles[$i]); ?>">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </li>
                                <?php endfor; ?>
                            </ul>
                            <?php else: ?>
                            <p class="text-muted text-center">No active cases</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</div>

<!-- Assign Case Modal -->
<div class="modal fade" id="assignCaseModal" tabindex="-1" role="dialog" aria-labelledby="assignCaseModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignCaseModalLabel">Assign Case to Lawyer</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="assignCaseForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="lawyer">Select Lawyer</label>
                        <select class="form-control select2" id="lawyer" name="lawyer_id" required>
                            <option value="">Select a lawyer</option>
                            <?php foreach ($lawyers as $lawyer): ?>
                            <?php if ($lawyer['active_cases'] < $lawyer['max_cases']): ?>
                            <option value="<?php echo $lawyer['id']; ?>">
                                <?php echo htmlspecialchars($lawyer['full_name']); ?>
                                (<?php echo $lawyer['active_cases']; ?>/<?php echo $lawyer['max_cases']; ?> cases)
                            </option>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="case">Select Case</label>
                        <select class="form-control select2" id="case" name="case_id" required>
                            <option value="">Select a case</option>
                            <?php foreach ($available_cases as $case): ?>
                            <option value="<?php echo $case['id']; ?>">
                                <?php echo htmlspecialchars($case['title']); ?>
                                (<?php echo $case['case_number']; ?> - <?php echo ucfirst($case['priority']); ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign Case</button>
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
        dropdownParent: $('#assignCaseModal')
    });

    // Assign Case Form Submit
    $('#assignCaseForm').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        
        $.ajax({
            url: 'ajax/assign_case.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#assignCaseModal').modal('hide');
                    location.reload();
                } else {
                    alert('Error assigning case: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                alert('Error occurred while assigning case');
            }
        });
    });

    // Remove Case
    $('.remove-case').on('click', function() {
        if (confirm('Are you sure you want to remove this case from the lawyer?')) {
            const lawyerId = $(this).data('lawyer-id');
            const caseTitle = $(this).data('case-title');
            
            $.ajax({
                url: 'ajax/remove_case.php',
                type: 'POST',
                data: {
                    lawyer_id: lawyerId,
                    case_title: caseTitle
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error removing case: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('Error occurred while removing case');
                }
            });
        }
    });
});
</script>

<?php require_once '../include/footer.php'; ?> 