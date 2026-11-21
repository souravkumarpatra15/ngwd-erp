<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
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
                        <h4>Intern to Permanent Conversion Form</h4>
                        
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        
                        <div class="row gutters-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fullName" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" value="<?= $user['name'] ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="employeeId" class="form-label">Employee ID</label>
                                    <input type="text" class="form-control" value="<?= $employee['employee_id'] ?? '—' ?>" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row gutters-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="designation" class="form-label">Designation</label>
                                    <input type="text" class="form-control" 
                                      value="<?= ($employee['designation_id'] ?? null) ? 'Assigned' : 'Not assigned' ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="department" class="form-label">Department</label>
                                    <input type="text" class="form-control" 
                                      value="<?= ($employee['department_id'] ?? null) ? 'Assigned' : 'Not assigned' ?>" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row gutters-3">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="conversionRecommendation" class="form-label">Conversion Recommendation</label>
                                    <textarea class="form-control" name="conversion_recommendation" rows="5"
                                      placeholder="Enter detailed reasons for conversion, performance evaluation, strengths, and areas of improvement. This will be recorded in the employee's permanent history."></textarea>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row gutters-3">
                            <div class="col-12">
                                <p><strong>Conversion will:</strong></p>
                                <ul>
                                    <li>Change role from 'intern' to 'permanent_employee'</li>
                                    <li>Change employment_type from 'internship' to 'permanent'</li>
                                    <li>Preserve employee ID and user account</li>
                                    <li>Retain department assignment (if any)</li>
                                    <li>Retain designation assignment (if any)</li>
                                    <li>Set effective date to conversion date</li>
                                    <li>Record complete audit history</li>
                                </ul>
                            </p>
                            <hr class="my-4">
                                
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
                    </div>
                </div>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>
