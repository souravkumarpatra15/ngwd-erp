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
            
            <?php if ($project): ?>
                <a href="<?= base_url('admin/projects/' . $project['id']) ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to <?= $project['name'] ?>
                </a>
            <?php else: ?>
                <a href="<?= base_url('admin/projects') ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Projects
                </a>
            <?php endif; ?>
            
            <?php if ($project && $auth->canViewProjects(session()->get('user_role'))): ?>
                <a href="<?= base_url('admin/team-pilot/quick-capture/' . $project['id']) ?>"
                   class="btn btn-primary ml-2">
                    <i class="fas fa-plus"></i> Quick Capture
                </a>
            <?php endif; ?>
        </div>

        <?php if ($project): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-comments mr-2"></i> Daily Stand-Up
                            <small class="text-muted"><?= date('F j, Y') ?></small>
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        
                        <!-- Yesterday's Work -->
                        <div class="mb-4">
                            <h6><i class="fas fa-yesterday text-primary"></i> Yesterday's Work</h6>
                            <p class="card-text mb-0" id="yesterday-text">
                                <?= isset($yesterday) ? nl2br(esc($yesterday)) : 'No entry yet' ?>
                            </p>
                        </div>

                        <!-- Today's Plan -->
                        <div class="mb-4">
                            <h6><i class="fas fa-today text-success"></i> Today's Plan</h6>
                            <p class="card-text mb-0" id="today-text">
                                <?= isset($today) ? nl2br(esc($today)) : 'No entry yet' ?>
                            </p>
                        </div>

                        <!-- Blockers -->
                        <div class="mb-4">
                            <h6><i class="fas fa-exclamation-triangle text-warning"></i> Blockers</h6>
                            <p class="card-text mb-0" id="blockers-text">
                                <?= isset($blockers) ? nl2br(esc($blockers)) : 'No blockers reported' ?>
                            </p>
                        </div>

                        <!-- My Tasks Context -->
                        <?php if (!empty($myTasks)): ?>
                        <div class="mt-4">
                            <h6><i class="fas fa-tasks"></i> Your Tasks for <?= $project['name'] ?></h6>
                            <div class="row">
                                <?php foreach ($myTasks as $task): ?>
                                <div class="col-md-6 mb-2">
                                    <span class="badge badge-primary">
                                        <?= esc($task['title']) ?>
                                        <?php if ($task['estimated_hours']): ?>
                                            <span class="badge badge-secondary"> <?= $task['estimated_hours'] ?>h</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Stand-Up Form -->
                        <div class="mt-4">
                            <h6><i class="fas fa-edit"></i> Submit Stand-Up</h6>
                            <form action="<?= base_url('admin/team-pilot/stand-up/submit' . ($projectId ? '/' . $projectId : '')) ?>" 
                                  method="post" 
                                  id="standupForm">
                                <?= csrf_field() ?>
                                
                                <div class="mb-3">
                                    <textarea name="yesterday" class="form-control" 
                                              rows="3"
                                              placeholder="What did you do yesterday?"></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <textarea name="today" class="form-control"
                                              rows="3"
                                              placeholder="What will you do today?"></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <textarea name="blockers" class="form-control"
                                              rows="3"
                                              placeholder="Are there any blockers?"></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Submit Stand-Up
                                </button>
                                <button type="button" class="btn btn-outline-secondary"
                                        onclick="window.history.back()">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Team Stand-Ups History -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-history"></i> Team Stand-Up History
                            <span class="text-muted float-right small">
                                <?= count($teamStandUps ?? []) ?> entries
                            </span>
                        </h5>
                    </div>
                    <div class="card-body p-3">
                        <?php if (empty($teamStandUps)): ?>
                            <p class="text-muted small">No stand-up entries yet.</p>
                        <?php else: ?>
                            <div class="table-responsive small">
                                <table class="table table-bordereless mb-0">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Date</th>
                                            <th>Yesterday</th>
                                            <th>Today</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($teamStandUps ?? [], 0, 10) as $su): 
                                            $desc = $su['description'] ?? '';
                                            // Extract simple fields from description
                                            $y = $t = $b = '';
                                            if (preg_match('/yesterday="([^"]*)"/', $desc, $m)) $y = $m[1];
                                            if (preg_match('/today="([^"]*)"/', $desc, $m)) $t = $m[1];
                                            if (preg_match('/blockers="([^"]*)"/', $desc, $m)) $b = $m[1];
                                        ?>
                                            <tr>
                                                <td><?= esc($su['user_name'] ?? 'Unknown') ?></td>
                                                <td><?= date('M d, H:i', strtotime($su['created_at'] ?? '')) ?></td>
                                                <td><?= strlen($y) > 50 ? substr($y, 0, 50) . '...' : $y ?></td>
                                                <td><?= strlen($t) > 50 ? substr($t, 0, 50) . '...' : $t ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <script>
    // JS helper mirroring PHP's nl2br
    function nl2br(text) {
        return String(text).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/\n/g, '<br>');
    }

    // Form validation + live preview
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('standupForm');
        if (!form) return;

        form.addEventListener('submit', function(e) {
            const y = form.elements['yesterday'].value.trim();
            const t = form.elements['today'].value.trim();
            const b = form.elements['blockers'].value.trim();

            if (!y && !t) {
                e.preventDefault();
                alert('Please provide at least your yesterday\'s work or today\'s plan.');
                return;
            }

            // Live preview (submission proceeds normally)
            document.getElementById('yesterday-text').innerHTML = nl2br(y) || 'No entry';
            document.getElementById('today-text').innerHTML = nl2br(t) || 'No entry';
            document.getElementById('blockers-text').innerHTML = b ? nl2br(b) : 'No blockers reported';
        });
    });
    </script>
</body>
</html>