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
            
            <?php if ($projectId): ?>
                <a href="<?= base_url('admin/projects/' . $projectId) ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Projects
                </a>
            <?php else: ?>
                <a href="<?= base_url('admin/projects') ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Projects
                </a>
            <?php endif; ?>
        </div>

        <?php if ($projectId): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-exclamation-triangle text-warning"></i> Blockers
                            <span class="text-muted float-right small">
                                <?= count($blockers ?? []) ?> active blocker(s)
                            </span>
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        
                        <?php if (empty($blockers)): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i>
                                No active blockers found.
                            </div>
                        <?php else: ?>
                            
                            <div class="table-responsive">
                                <table class="table table-bordereless">
                                    <thead>
                                        <tr>
                                            <th>Task</th>
                                            <th>Assigned To</th>
                                            <th>Due Date</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($blockers as $b): ?>
                                        <tr>
                                            <td>
                                                <strong><?= esc($b['task_title']) ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    ID: #<?= $b['task_id'] ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?= $b['assigned_to'] ? 'User ' . $b['assigned_to'] : 'Unassigned' ?>
                                            </td>
                                            <td><?= $b['due_date'] ? date('M d, Y', strtotime($b['due_date'])) : 'No due date' ?></td>
                                            <td>
                                                <span class="badge badge-danger">
                                                    <i class="fas fa-exclamation-triangle"></i> Blocked
                                                </span>
                                            </td>
                                            <td>
                                                <form action="<?= base_url('team-pilot/blockers/resolve/' . $b['task_id']) ?>" 
                                                      method="post"
                                                      style="display:inline;">
                                                    <?= csrf_field() ?>
                                                    <button type="submit"
                                                            class="btn btn-sm btn-success">
                                                        <i class="fas fa-check"></i> Resolve
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                        <?php endif; ?>

                        <!-- Resolve Blocker Form -->
                        <div class="mt-4">
                            <h6><i class="fas fa-keyboard"></i> Mark Blocker as Resolved</h6>
                            <p class="text-muted small">
                                Enter task ID to resolve, or use the resolve button above on individual tasks.
                            </p>
                            <form action="<?= base_url('admin/team-pilot/blockers/resolve') ?>" 
                                  method="post"
                                  style="max-width: 400px;">
                                <?= csrf_field() ?>
                                
                                <div class="mb-3">
                                    <input type="text" name="resolve_task_id"
                                           class="form-control"
                                           placeholder="Task ID to resolve"
                                           required>
                                </div>
                                
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check"></i> Resolve Blocker
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            Select a project from the left to view blockers, or use the stand-up page to report new blockers.
        </div>
        <?php endif; ?>

    </div>

    <script>
    // Validate resolve-by-ID form before submission (posts to the real endpoint)
    document.addEventListener('DOMContentLoaded', function() {
        const resolveForm = document.querySelector('form[action*="blockers/resolve"]');
        if (!resolveForm || !resolveForm.elements['resolve_task_id']) return;

        resolveForm.addEventListener('submit', function(e) {
            const taskId = resolveForm.elements['resolve_task_id'].value.trim();
            if (!taskId) {
                e.preventDefault();
                alert('Task ID is required.');
            }
        });
    });
    </script>
</body>
</html>