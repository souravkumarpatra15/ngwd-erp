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
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <form action="<?= base_url('admin/employees/store') ?>" method="post" enctype="multipart/form-data" id="employeeForm">
                            <?= csrf_field() ?>

                            <input type="hidden" name="user_id" id="userId" required>

                            <div class="row gutters-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="employeeId" class="form-label">Employee ID</label>
                                        <input type="text" class="form-control" name="employee_id" id="employeeId" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="selectUser" class="form-label">Associated User</label>
                                        <select class="form-select" name="user_id" id="selectUser" required>
                                            <option value="">-- Select User --</option>
                                            <?php foreach ($users as $user): ?>
                                            <option value="<?= $user['id'] ?>" <?= set_select('user_id', $user['id']) ?>>
                                                <?= esc($user['name'] . ' (' . $user['email'] . ')') ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row gutters-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="departmentId" class="form-label">Department</label>
                                        <select class="form-select" name="department_id" id="departmentId">
                                            <option value="">-- Select Department --</option>
                                            <?php foreach ($departments as $dept): ?>
                                            <option value="<?= $dept['id'] ?>" <?= set_select('department_id', $dept['id']) ?>>
                                                <?= esc($dept['name']) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="designationId" class="form-label">Designation</label>
                                        <select class="form-select" name="designation_id" id="designationId">
                                            <option value="">-- Select Designation --</option>
                                            <?php foreach ($designations as $desig): ?>
                                            <option value="<?= $desig['id'] ?>" <?= set_select('designation_id', $desig['id']) ?>>
                                                <?= esc($desig['title']) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row gutters-3">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="employmentType" class="form-label">Employment Type</label>
                                        <select class="form-select" name="employment_type" id="employmentType" required>
                                            <option value="">-- Select Type --</option>
                                            <option value="permanent">Permanent</option>
                                            <option value="full_time">Full-time</option>
                                            <option value="part_time">Part-time</option>
                                            <option value="contract">Contract</option>
                                            <option value="probation">Probation</option>
                                            <option value="internship">Internship</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="employmentStatus" class="form-label">Employment Status</label>
                                        <select class="form-select" name="employment_status" id="employmentStatus" required>
                                            <option value="">-- Select Status --</option>
                                            <option value="draft">Draft</option>
                                            <option value="active">Active</option>
                                            <option value="on_leave">On Leave</option>
                                            <option value="probation">Probation</option>
                                            <option value="notice_period">Notice Period</option>
                                            <option value="resigned">Resigned</option>
                                            <option value="terminated">Terminated</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="joiningDate" class="form-label">Joining Date</label>
                                        <input type="date" class="form-control" name="joining_date" id="joiningDate" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row gutters-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="emergencyContactName" class="form-label">Emergency Contact Name</label>
                                        <input type="text" class="form-control" name="emergency_contact_name" id="emergencyContactName">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="emergencyContactPhone" class="form-label">Emergency Contact Phone</label>
                                        <input type="text" class="form-control" name="emergency_contact_phone" id="emergencyContactPhone">
                                    </div>
                                </div>
                            </div>

                            <div class="row gutters-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="addressLine1" class="form-label">Address Line 1</label>
                                        <input type="text" class="form-control" name="address_line1" id="addressLine1">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="addressLine2" class="form-label">Address Line 2</label>
                                        <input type="text" class="form-control" name="address_line2" id="addressLine2">
                                    </div>
                                </div>
                            </div>

                            <div class="row gutters-3">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="city" class="form-label">City</label>
                                        <input type="text" class="form-control" name="city" id="city">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="state" class="form-label">State</label>
                                        <input type="text" class="form-control" name="state" id="state">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="pincode" class="form-label">Pincode</label>
                                        <input type="text" class="form-control" name="pincode" id="pincode">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="profilePhoto" class="form-label">Profile Photo</label>
                                <input type="file" class="form-control" name="profile_photo" id="profilePhoto" accept="image/*">
                                <small class="text-muted">Supported: JPEG, PNG. Max size: 2MB</small>
                            </div>

                            <hr class="my-4">

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Employee
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="window.history.back()">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Populate user selector on page load
    document.getElementById('selectUser').addEventListener('change', function() {
        const userId = this.value;
        if (userId) {
            $.ajax({
                url: '<?= base_url("api/users/") ?>' + userId,
                type: 'GET',
                success: function(response) {
                    // Could populate additional fields from user record
                }
            });
        }
    });
    </script>
</body>
</html>