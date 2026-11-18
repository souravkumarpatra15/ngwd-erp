<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?> - NGWebD ERP</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/custom.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin-modern.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin-app-theme.css') ?>">
</head>
<body>
    <div class="container-fluid">
        <div class="page-header">
            <h1><?= esc($title) ?></h1>
            <a href="<?= base_url('admin/employees/create') ?>" class="btn btn-primary btn-create">
                <i class="fas fa-plus"></i> Add Employee
            </a>
        </div>

        <?php if (($total ?? 0) > 0): ?>
        <div class="row gutters-sm">
            <div class="col-md-6 col-xl-3">
                <div class="info-card text white small">
                    <div class="info-card-icon bg-primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="info-card-data">
                        <div class="info-card-number h2"><?= esc($total) ?></div>
                        <div class="info-card-text">Total Employees</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="info-card text white small">
                    <div class="info-card-icon bg-success">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="info-card-data">
                        <div class="info-card-number h2"><?= esc($active) ?></div>
                        <div class="info-card-text">Active</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="info-card text white small">
                    <div class="info-card-icon bg-info">
                        <i class="fas fa-user-intern"></i>
                    </div>
                    <div class="info-card-data">
                        <div class="info-card-number h2"><?= esc($interns) ?></div>
                        <div class="info-card-text">Interns</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="info-card text white small">
                    <div class="info-card-icon bg-warning">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="info-card-data">
                        <div class="info-card-number h2"><?= esc($permanent) ?></div>
                        <div class="info-card-text">Permanent</div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Employee List</h5>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                Filter
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?= base_url('admin/employees') ?>">All Employees</a></li>
                                <?php foreach (['active', 'on_leave', 'probation', 'resigned', 'terminated', 'inactive'] as $status): ?>
                                <li><a class="dropdown-item" href="<?= base_url('admin/employees?employment_status=' . $status) ?>"><?= ucfirst($status) ?></a></li>
                                <?php endforeach; ?>
                                <?php foreach (['permanent', 'full_time', 'part_time', 'contract', 'probation', 'internship'] as $type): ?>
                                <li><a class="dropdown-item" href="<?= base_url('admin/employees?employment_type=' . $type) ?>><?= ucfirst($type) ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div table-responsive>
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Employee ID</th>
                                        <th>Name</th>
                                        <th>Department</th>
                                        <th>Designation</th>
                                        <th>Employment Type</th>
                                        <th>Status</th>
                                        <th>Joining Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i = (($pager->getCurrentPage() ?? 1) - 1) * ($pager->getPerPage() ?? 20); ?>
                                    <?php foreach ($employees as $emp): $i++; ?>
                                    <tr>
                                        <td><?= $i ?></td>
                                        <td><?= esc($emp['employee_id']) ?></td>
                                        <td>
                                            <a href="javascript:void(0)" class="employee-link" data-emp-id="<?= $emp['id'] ?>">
                                                <?= esc($emp['user_name'] ?? '—') ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?= esc($emp['department_name'] ?? '—') ?>
                                        </td>
                                        <td>
                                            <?= esc($emp['designation_title'] ?? '—') ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= 
                                                (($emp['employment_type'] === 'internship') ? 'info' : 
                                                (($emp['employment_type'] === 'permanent') ? 'success' : 'warning'))
                                            ?>">
                                                <?= ucfirst($emp['employment_type']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= 
                                                ((in_array($emp['employment_status'], ['active', 'on_leave'])) ? 'success' : 
                                                (($emp['employment_status'] === 'probation') ? 'warning' : 'danger'))
                                            ?>">
                                                <?= ucfirst($emp['employment_status']) ?>
                                            </span>
                                        </td>
                                        <td><?= $emp['joining_date'] ? date('M d, Y', strtotime($emp['joining_date'])) : '—' ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary" onclick="viewEmployee(<?= $emp['id'] ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary" onclick="editEmployee(<?= $emp['id'] ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php $auth ??= new \App\Services\PmsAuthorizationService(); ?>
                                                <?php if ($auth->canManageEmployees(session()->get('user_role'))): ?>
                                                <button type="button" class="btn btn-outline-danger" onclick="terminateEmployee(<?= $emp['id'] ?>)">
                                                    <i class="fas fa-user-times"></i>
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer small text-muted">
                            <?php $firstItem = (($pager->getCurrentPage() ?? 1) - 1) * ($pager->getPerPage() ?? 20) + 1; $lastItem = $firstItem + count($employees) - 1; ?>
                            Showing <?= $firstItem ?>-<?= max($lastItem, 0) ?> of <?= $pager->getTotal() ?> entries
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function viewEmployee(empId) {
        $.ajax({
            url: '<?= base_url("admin/employee/show") ?>/' + empId,
            type: 'GET',
            success: function(response) {
                if (response.status === 'success') {
                    const emp = response.data;
                    alert('Employee Details:\n\nEmployee ID: ' + emp.employee_id + '\nName: ' + (emp.user_name || 'N/A') + '\nDepartment: ' + (emp.department_name || 'N/A') + '\nDesignation: ' + (emp.designation_title || 'N/A') + '\nEmployment Type: ' + emp.employment_type + '\nStatus: ' + emp.employment_status + '\nJoining Date: ' + (emp.joining_date || 'N/A'));
                }
            }
        });
    }

    function editEmployee(empId) {
        window.location.href = '<?= base_url("admin/employees/edit/") ?>' + empId;
    }

    function terminateEmployee(empId) {
        if (confirm('Are you sure you want to terminate this employee? This will set the status to "terminated" and record the termination date.')) {
            $.ajax({
                url: '<?= base_url("admin/employees/delete") ?>/' + empId,
                type: 'POST',
                success: function(response) {
                    if (response.status === 'success') {
                        location.reload();
                    } else {
                        alert('Error: ' + (response.message || 'Unknown error'));
                    }
                }
            });
        }
    }
    </script>
</body>
</html>