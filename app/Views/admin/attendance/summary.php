<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<div class="container mt-3">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-bar-chart-2 me-1"></i> Monthly Attendance Summary
                            <span class="text-muted float-end small">Month: <?= $month ?? date('F Y') ?></span>
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        
                        <div class="row g-3">
                            <div class="col-6">
                                <h4><strong><?= $summary['percentage'] ?>%</strong> Attendance</h4>
                                <p class="text-muted">Present / Total Working Days</p>
                            </div>
                            <div class="col-6 text-end">
                                <h4><strong><?= $summary['present_days'] ?></strong> / <?= $summary['working_days'] ?></h4>
                                <p class="text-muted">Present Days</p>
                            </div>
                        </div>

                        <hr class="my-3">

                        <div class="row g-3">
                            <div class="col-4 small">
                                <p class="fw-bold text-primary"><i class="bi bi-check-circle"></i> <?= $summary['present_days'] ?></p>
                                <p class="text-muted">Present</p>
                            </div>
                            <div class="col-4 small">
                                <p class="fw-bold text-danger"><i class="bi bi-x-circle"></i> <?= $summary['absent_days'] ?></p>
                                <p class="text-muted">Absent</p>
                            </div>
                            <div class="col-4 small">
                                <p class="fw-bold text-warning"><i class="bi bi-slash-circle"></i> <?= ($statusCounts['half_day'] ?? 0) ?></p>
                                <p class="text-muted">Half Day</p>
                            </div>
                        </div>

                        <div class="row g-3 my-3">
                            <div class="col-4 small">
                                <p class="fw-bold text-info"><i class="bi bi-hourglass-half"></i> <?= ($statusCounts['leave'] ?? 0) ?></p>
                                <p class="text-muted">Leave</p>
                            </div>
                            <div class="col-4 small">
                                <p class="fw-bold text-success"><i class="bi bi-tree-deciduous"></i> <?= ($statusCounts['holiday'] ?? 0) ?></p>
                                <p class="text-muted">Holiday</p>
                            </div>
                            <div class="col-4 small">
                                <p class="fw-bold text-purple"><i class="bi bi-arrow-repeat"></i> <?= ($statusCounts['week_off'] ?? 0) ?></p>
                                <p class="text-muted">Weekly Off</p>
                            </div>
                        </div>

                        <hr class="my-3">

                        <div class="row g-3">
                            <div class="col-12">
                                <p><strong>Total Hours Worked:</strong> <?= $summary['total_hours'] ?></p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>
