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
            <a href="<?= base_url('admin/employees') ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Employees
            </a>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h4>Intern to Permanent Conversion</h4>
                        
                        <div class="alert alert-info">
                            <strong>Employee:</strong> <?= $employee['employee_id'] ?? 'N/A' ?><br>
                            <strong>Name:</strong> <?= $user['name'] ?? 'N/A' ?><br>
                            <strong>Current Role:</strong> <?= $user['role'] ?? 'N/A' ?><br>
                            <strong>Employment Type:</strong> <?= $employee['employment_type'] ?? 'N/A' ?><br>
                            <strong>Department:</strong> <?= $employee['department_id'] ? 'Assigned' : 'Not assigned' ?><br>
                            <strong>Designation:</th> <?= $employee['designation_id'] ? 'Assigned' : 'Not assigned' ?>
                        </div>

                        <?php if ($isIntern): ?>
                        <div class="alert alert-warning">
                            This employee is currently an intern (employment_type: internship). 
                            Converting will change their role to permanent_employee and employment_type to permanent.
                        </div>
                        
                        <form action="<?= base_url('admin/employee/conversion/process/' . $user['id']) ?>" method="post">
                            <?= csrf_field() ?>
                            
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            
                            <div class="row gutters-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="conversionRecommendation" class="form-label">Conversion Recommendation</label>
                                        <textarea class="form-control" name="conversion_recommendation" rows="3" 
                                          placeholder="Enter reasons for conversion and performance evaluation"></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="reportingManager" class="form-label">Reporting Manager</label>
                                        <input type="text" class="form-control" name="reporting_manager" 
                                          placeholder="Name of reporting manager">
                                    </div>
                                </div>
                            </div>
                            
                            <hr class="my-4">

                            <div class="row gutters-3">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-convert"></i> Convert to Permanent Employee
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="window.history.back()">
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
                                </div>
                            </div>
                        </form>
                        <?php else: ?>
                        <div class="alert alert-success">
                            <h4>Not an Intern</h4>
                            <p>This employee is not currently in an intern status and cannot be converted.</h4>
                            <p>Current employment type: <?= $employee['employment_type'] ?></p>
                            <a href="<?= base_url('admin/employees') ?>" class="btn btn-secondary mt-2">Back to Employees</a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>