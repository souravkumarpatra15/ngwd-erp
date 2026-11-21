<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<div class="container mt-3">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-calendar3-week me-1"></i> Attendance Calendar
                            <small class="text-muted float-end">Month: <?= $month ?></small>
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        
                        <div class="table-responsive small">
                            <table class="table table-borderless">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Day</th>
                                        <th>Status</th>
                                        <th>Check-in</th>
                                        <th>Check-out</th>
                                        <th>Duration</th>
                                        <th>Work Mode</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($attendances as $a): ?>
                                        <tr>
                                            <td><?= date('M d', strtotime($a['date'])) ?></td>
                                            <td><?= date('D', strtotime($a['date'])) ?></td>
                                            <td>
                                                <span class="status-<?= strtolower($a['status'] ?? 'pending') ?>">
                                                    <?= ucfirst(str_replace('_', ' ', (string)($a['status'] ?? 'N/A'))) ?>
                                                </span>
                                            </td>
                                            <td><?= $a['check_in'] ? date('h:i A', strtotime($a['check_in'])) : '-' ?></td>
                                            <td><?= $a['check_out'] ? date('h:i A', strtotime($a['check_out'])) : '-' ?></td>
                                            <td><?= round((float)($a['hours_worked'] ?? 0), 1) ?> hrs</td>
                                            <td><?= ($a['work_from_home'] ?? 0) ? 'Remote' : 'Office' ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if (empty($attendances)): ?>
                            <div class="alert alert-info mt-3">
                                <i class="bi bi-calendar3-empty"></i> No attendance records for this month.
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>
