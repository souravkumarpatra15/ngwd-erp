<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
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
                        <form action="<?= base_url('admin/employees/update/' . ($employee['id'] ?? '')) ?>" method="post" enctype="multipart/form-data" id="employeeForm">
                            <?= csrf_field() ?>

                            <input type="hidden" name="id" value="<?= $employee['id'] ?>">
                            <input type="hidden" name="original_user_id" value="<?= $employee['user_id'] ?>">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="employeeId" class="form-label">Employee ID</label>
                                        <input type="text" class="form-control" name="employee_id" id="employeeId" value="<?= esc($employee['employee_id']) ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="selectUser" class="form-label">Associated User</label>
                                        <select class="form-select" name="user_id" id="selectUser" required>
                                            <option value="">-- Select User --</option>
                                            <?php foreach ($users as $user): ?>
                                            <option value="<?= $user['id'] ?>" <?= set_select('user_id', $user['id'], $user['id'] == $employee['user_id']) ?>>
                                                <?= esc($user['name'] . ' (' . $user['email'] . ')') ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="departmentId" class="form-label">Department</label>
                                        <select class="form-select" name="department_id" id="departmentId">
                                            <option value="">-- Select Department --</option>
                                            <?php foreach ($departments as $dept): ?>
                                            <option value="<?= $dept['id'] ?>" <?= set_select('department_id', $dept['id'], $dept['id'] == $employee['department_id']) ?>>
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
                                            <option value="<?= $desig['id'] ?>" <?= set_select('designation_id', $desig['id'], $desig['id'] == $employee['designation_id']) ?>>
                                                <?= esc($desig['title']) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="employmentType" class="form-label">Employment Type</label>
                                        <select class="form-select" name="employment_type" id="employmentType" required>
                                            <option value="">-- Select Type --</option>
                                            <option value="permanent" <?= $employee['employment_type'] === 'permanent' ? 'selected' : '' ?>>Permanent</option>
                                            <option value="full_time" <?= $employee['employment_type'] === 'full_time' ? 'selected' : '' ?>>Full-time</option>
                                            <option value="part_time" <?= $employee['employment_type'] === 'part_time' ? 'selected' : '' ?>>Part-time</option>
                                            <option value="contract" <?= $employee['employment_type'] === 'contract' ? 'selected' : '' ?>>Contract</option>
                                            <option value="probation" <?= $employee['employment_type'] === 'probation' ? 'selected' : '' ?>>Probation</option>
                                            <option value="internship" <?= $employee['employment_type'] === 'internship' ? 'selected' : '' ?>>Internship</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="employmentStatus" class="form-label">Employment Status</label>
                                        <select class="form-select" name="employment_status" id="employmentStatus" required>
                                            <option value="">-- Select Status --</option>
                                            <option value="draft" <?= $employee['employment_status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                                            <option value="active" <?= $employee['employment_status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                            <option value="on_leave" <?= $employee['employment_status'] === 'on_leave' ? 'selected' : '' ?>>On Leave</option>
                                            <option value="probation" <?= $employee['employment_status'] === 'probation' ? 'selected' : '' ?>>Probation</option>
                                            <option value="notice_period" <?= $employee['employment_status'] === 'notice_period' ? 'selected' : '' ?>>Notice Period</option>
                                            <option value="resigned" <?= $employee['employment_status'] === 'resigned' ? 'selected' : '' ?>>Resigned</option>
                                            <option value="terminated" <?= $employee['employment_status'] === 'terminated' ? 'selected' : '' ?>>Terminated</option>
                                            <option value="inactive" <?= $employee['employment_status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="joiningDate" class="form-label">Joining Date</label>
                                        <input type="date" class="form-control" name="joining_date" id="joiningDate" value="<?= esc((string) ($employee['joining_date'] ?? '')) ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="emergencyContactName" class="form-label">Emergency Contact Name</label>
                                        <input type="text" class="form-control" name="emergency_contact_name" id="emergencyContactName" value="<?= esc($employee['emergency_contact_name']) ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="emergencyContactPhone" class="form-label">Emergency Contact Phone</label>
                                        <input type="text" class="form-control" name="emergency_contact_phone" id="emergencyContactPhone" value="<?= esc($employee['emergency_contact_phone']) ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="addressLine1" class="form-label">Address Line 1</label>
                                        <input type="text" class="form-control" name="address_line1" id="addressLine1" value="<?= esc($employee['address_line1']) ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="addressLine2" class="form-label">Address Line 2</label>
                                        <input type="text" class="form-control" name="address_line2" id="addressLine2" value="<?= esc($employee['address_line2']) ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="city" class="form-label">City</label>
                                        <input type="text" class="form-control" name="city" id="city" value="<?= esc($employee['city']) ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="state" class="form-label">State</label>
                                        <input type="text" class="form-control" name="state" id="state" value="<?= esc($employee['state']) ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="pincode" class="form-label">Pincode</label>
                                        <input type="text" class="form-control" name="pincode" id="pincode" value="<?= esc($employee['pincode']) ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="profilePhoto" class="form-label">Profile Photo</label>
                                <?php if ($employee['profile_photo']): ?>
                                    <div class="current-photo">
                                        <img src="<?= base_url('uploads/' . $employee['profile_photo']) ?>" alt="Current profile photo" class="img-thumbnail" style="max-height: 100px;">
                                        <input type="hidden" name="existing_photo" value="<?= $employee['profile_photo'] ?>">
                                    </div>
                                <?php else: ?>
                                    <small>No profile photo currently</small>
                                <?php endif; ?>
                                <input type="file" class="form-control" name="profile_photo" id="profilePhoto" accept="image/*">
                                <small class="text-muted">Supported: JPEG, PNG. Max size: 2MB (leave blank to keep current)</small>
                            </div>

                            <hr class="my-4">

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Employee
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
<?= $this->endSection() ?>
