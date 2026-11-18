<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance - NGWebD ERP</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css"
          integrity="sha512-...", crossorigin="anonymous">
    <style>
        .work-mode-badge { font-size: 0.85rem; }
        .duration-badge { font-size: 0.8rem; }
        .status-present { color: #198754; font-weight: bold; }
        .status-absent { color: #dc3545; font-weight: bold; }
        .status-half { color: #ffc107; font-weight: bold; }
        .status-leave { color: #6c757d; font-weight: bold; }
        .status-holiday { color: #17a2b8; font-weight: bold; }
        .status-week-off { color: #6f42c1; font-weight: bold; }
        .status-pending { color: #fd7e14; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container-fluid mt-3">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-clock me-2"></i> Attendance Workspace
                            <?php if (session()->get('user_role') === 'intern' || session()->get('user_role') === 'permanent_employee'): ?>
                                <small class="text-muted ms-2">My Attendance</small>
                            <?php endif; ?>
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        
                        <?php if (session()->get('user_role') === 'intern' || session()->get('user_role') === 'permanent_employee'): ?>
                        <!-- Employee Attendance Card -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <h6><i class="bi bi-person me-1"></i> Employee</h6>
                                <p class="fw-bold mb-1" id="employee-name"><?= $employee['name'] ?? 'N/A' ?></p>
                                <p class="text-muted small" id="employee-id"><?= $employee['employee_id'] ?? 'N/A' ?></p>
                            </div>
                        </div>

                        <!-- Today's Status Card -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="card border rounded-pshadow" style="background: #f8f9fa;">
                                    <div class="card-body p-3">
                                        <h6><i class="bi bi-calendar-event me-1"></i> Today</h6>
                                        <div class="row">
                                            <div class="col-6">
                                                <p><strong>Status:</strong> <span id="today-status" class="status-pending"><?= $todayStatus['status'] ?? 'Pending' ?></span></p>
                                                <p><i class="bi bi-clock me-1"></i> <span id="duration">0</span> min</p>
                                            </div>
                                            <div class="col-6">
                                                <p><i class="bi bi-door-open me-1"></i> <span id="check-in-time">Not checked in</span></p>
                                                <p><i class="bi bi-door-closed me-1"></i> <span id="check-out-time">Not checked out</span></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Check-in/Check-out Buttons -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <?php if (session()->get('user_role') === 'intern' || session()->get('user_role') === 'permanent_employee'): ?>
                                <div class="text-center">
                                    <!-- Check-in Button -->
                                    <button id="checkInBtn" class="btn btn-success w-100 mb-2" disabled>
                                        <i class="bi bi-door-open me-2"></i> Check In
                                    </button>
                                    <!-- Check-out Button -->
                                    <button id="checkOutBtn" class="btn btn-primary w-100" disabled>
                                        <i class="bi bi-door-closed me-2"></i> Check Out
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Monthly Summary -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="card border">
                                    <div class="card-body">
                                        <h6 class="card-title"><i class="bi bi-bar-chart me-1"></i> Monthly Summary</h6>
                                        <div class="row">
                                            <div class="col-4">
                                                <p><strong>Present</strong> <span id="month-present">0</span></p>
                                            </div>
                                            <div class="col-4">
                                                <p><strong>Absent</strong> <span id="month-absent">0</span></p>
                                            </div>
                                            <div class="col-4">
                                                <p><strong>%</strong> <span id="month-percentage">0%</span></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance History Table -->
        <div class="row mt-3">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="card-title mb-0"><i class="bi bi-clock-history me-1"></i> Attendance History</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive small">
                            <table class="table table-bordereless mb-0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Check-in</th>
                                        <th>Check-out</th>
                                        <th>Duration</th>
                                        <th>Work Mode</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $attendances = $attendanceModel->thisMonth($employeeId); ?>
                                    <?php foreach (array_reverse($attendances) as $a): ?>
                                        <tr>
                                            <td><?= date('M d, Y', strtotime($a['date'])) ?></td>
                                            <td>
                                                <span class="status-<?= strtolower((string)($a['status'] ?? 'pending')) ?>">
                                                    <?= ucfirst(str_replace('_', ' ', (string)($a['status'] ?? 'N/A'))) ?>
                                                </span>
                                            </td>
                                            <td><?= $a['check_in'] ? date('h:i A', strtotime($a['check_in'])) : '—' ?></td>
                                            <td><?= $a['check_out'] ? date('h:i A', strtotime($a['check_out'])) : '—' ?></td>
                                            <td><?= round((float)($a['hours_worked'] ?? 0), 1) ?> hrs</td>
                                            <td><?= ($a['work_from_home'] ?? 0) ? 'Remote' : 'Office' ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"
            integrity="sha512-...", crossorigin="anonymous"></script>
    <script>
    // Attendance workspace initialization
    document.addEventListener('DOMContentLoaded', function() {
        // Check if we're on the employee workspace
        const employeeId = <?= json_encode($employeeId ?? 0) ?>;
        
        // Initialize check-in/out functionality
        const checkInBtn = document.getElementById('checkInBtn');
        const checkOutBtn = document.getElementById('checkOutBtn');
        
        if (checkInBtn) {
            // Fetch today's status and enable/disable buttons
            fetch('<?= base_url('admin/attendance/get-today-status/') ?>' + <?= json_encode($employeeId ?? null) ?>)
                .then(res => res.json())
                .then(data => {
                    const payload = data.data || data;
                    const status = payload.status;
                    const checkInTime = payload.check_in;
                    const checkOutTime = payload.check_out;
                    const duration = Math.round((parseFloat(payload.hours_worked) || 0) * 60);
                    
                    // Update UI
                    document.getElementById('today-status').textContent = status || 'Not checked in';
                    document.getElementById('duration').textContent = duration;
                    
                    if (!checkInTime) {
                        checkInBtn.disabled = false;
                        checkInBtn.parentElement.style.display = 'block';
                        checkOutBtn.disabled = true;
                    } else if (!checkOutTime) {
                        checkInBtn.disabled = true;
                        checkOutBtn.disabled = false;
                    } else {
                        checkInBtn.disabled = true;
                        checkOutBtn.disabled = true;
                    }
                    
                    document.getElementById('check-in-time').textContent = checkInTime ? checkInTime : 'Not checked in';
                    document.getElementById('check-out-time').textContent = checkOutTime ? checkOutTime : 'Not checked out';
                })
                .catch(err => console.error('Error fetching attendance status:', err));
        }
        
        // Check-in handler
        if (checkInBtn) {
            checkInBtn.addEventListener('click', function() {
                fetch('<?= base_url('admin/attendance/check-in') ?>', {
                    method: 'POST',
                    headers: {
                        '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Refresh status
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(err => console.error('Error:', err));
            });
        }
        
        // Check-out handler
        if (checkOutBtn) {
            checkOutBtn.addEventListener('click', function() {
                fetch('<?= base_url('admin/attendance/check-out') ?>', {
                    method: 'POST',
                    headers: {
                        '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(err => console.error('Error:', err));
            });
        }
    });
    </script>
</body>
</html>