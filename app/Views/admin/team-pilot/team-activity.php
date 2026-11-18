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
            <a href="<?= base_url('admin/projects') ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Projects
            </a>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-exchange-alt"></i> Team Activity
                            <small class="text-muted float-right">
                                Last <?= $days ?> days
                            </small>
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        
                        <?php if (empty($activities)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                No activity found for the selected period.
                            </div>
                        <?php else: ?>
                            
                            <!-- Activity Summary by Module -->
                            <div class="mb-4">
                                <h6>Activity by Module</h6>
                                <div class="row">
                                    <div class="col-4 small">
                                        <strong>Projects</strong><br>
                                        <?= count($grouped['projects'] ?? []) ?> entries
                                    </div>
                                    <div class="col-4 small">
                                        <strong>HRMS</strong><br>
                                        <?= count($grouped['hrms'] ?? []) ?> entries
                                    </div>
                                    <div class="col-4 small">
                                        <strong>Recruitment</strong><br>
                                        <?= count($grouped['recruitment'] ?? []) ?> entries
                                    </div>
                                </div>
                            </div>

                            <!-- Activity List -->
                            <div class="table-responsive small">
                                <table class="table table-bordereless">
                                    <thead>
                                        <tr>
                                            <th>Time</th>
                                            <th>Module</th>
                                            <th>Action</th>
                                            <th>Description</th>
                                            <th>User</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_reverse($activities) as $a): 
                                            $module = $a['module'] ?? 'unknown';
                                            $action = $a['action'] ?? 'unknown';
                                            $desc = $a['description'] ?? '';
                                            $user = $a['user_name'] ?? 'Unknown';
                                            $time = date('M d H:i', strtotime($a['created_at'] ?? ''));
                                        ?>
                                            <tr>
                                                <td><?= $time ?></td>
                                                <td>
                                                    <span class="badge badge-info badge-sm">
                                                        <?= ucfirst($module) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-primary badge-sm">
                                                        <?= ucfirst($action) ?>
                                                    </span>
                                                </td>
                                                <td><?= strlen($desc) > 60 ? substr($desc, 0, 60) . '...' : $desc ?></td>
                                                <td><?= $user ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                        <?php endif; ?>

                        <!-- Quick Stats -->
                        <div class="mt-4">
                            <h6>Activity Summary</h6>
                            <div class="row">
                                <div class="col-3 small">
                                    <strong>Total Activities</strong><br>
                                    <?= count($activities) ?>
                                </div>
                                <div class="col-3 small">
                                    <strong>Projects</strong><br>
                                    <?= count($grouped['projects'] ?? []) ?>
                                </div>
                                <div class="col-3 small">
                                    <strong>HRMS</strong><br>
                                    <?= count($grouped['hrms'] ?? []) ?>
                                </div>
                                <div class="col-3 small">
                                    <strong>Recruitment</strong><br>
                                    <?= count($grouped['recruitment'] ?? []) ?>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
    // Simple highlight for recent activity
    document.addEventListener('DOMContentLoaded', function() {
        // Could add periodic refresh or real-time updates here
    });
    </script>
</body>
</html>