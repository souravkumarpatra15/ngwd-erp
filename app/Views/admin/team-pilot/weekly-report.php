<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
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
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-bar"></i> Weekly Report
                            <small class="text-muted">
                                Week of <?= date('M d', strtotime($startDate)) ?> - <?= date('M d', strtotime($endDate)) ?>
                            </small>
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        
                        <?php if ($project): ?>
                        <p class="text-muted small">
                            Project: <?= $project['name'] ?> (<?= $project['project_number'] ?? 'N/A' ?>)
                        </p>
                        <?php endif; ?>

                        <!-- Report Period -->
                        <div class="mb-4">
                            <h6><i class="fas fa-calendar-week"></i> Report Period</h6>
                            <p class="card-text">
                                Start: <?= date('F j, Y, g:i a', strtotime($startDate)) ?>
                                &
                                End: <?= date('F j, Y, g:i a', strtotime($endDate)) ?>
                            </p>
                        </div>

                        <!-- Task Statistics -->
                        <?php if (!empty($taskStats)): ?>
                        <div class="row mb-4">
                            <?php foreach ($taskStats as $pid => $stats): ?>
                                <?php $projName = $projectNames[$pid] ?? ('Project ' . $pid); ?>
                            <div class="col-md-4 mb-3">
                                <div class="card text-white bg-primary o-h">
                                    <div class="card-header">
                                        <h5 class="card-title"><?= esc($projName) ?></h5>
                                    </div>
                                    <div class="card-body">
                                        <h4 class="card-title">
                                            <?= (int) ($stats['progress_percentage'] ?? 0) ?>% complete
                                        </h4>
                                        <p class="card-text">
                                            <?= (int) ($stats['total_tasks'] ?? 0) ?> tasks
                                            (<?= (int) ($stats['completed_tasks'] ?? 0) ?> done,
                                            <?= (int) ($stats['in_progress_tasks'] ?? 0) ?> in progress)
                                        </p>
                                        <?php if (!empty($stats['blocked_tasks'])): ?>
                                        <p class="card-text small">
                                            <i class="fas fa-exclamation-triangle"></i> <?= (int) $stats['blocked_tasks'] ?> blocked
                                        </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <!-- Stand-Up Summaries -->
                        <?php if (!empty($standUps)): ?>
                        <div class="mb-4">
                            <h6><i class="fas fa-comments"></i> Stand-Up Summaries <?= count($standUps) ?></h6>
                            <div class="accordion" id="standupAccordion">
                                <?php foreach ($standUps as $su): 
                                    $desc = $su['description'] ?? '';
                                    $y = $t = '';
                                    if (preg_match('/yesterday="([^"]*)"/', $desc, $m)) $y = $m[1];
                                    if (preg_match('/today="([^"]*)"/', $desc, $m)) $t = $m[1];
                                ?>
                                    <div class="card">
                                        <div class="card-header" id="standupHead-<?= $su['id'] ?>">
                                            <h2 class="mb-0">
                                                <button class="btn-link collapsed" type="button"
                                                        data-toggle="collapse"
                                                        data-target="#standupBody-<?= $su['id'] ?>">
                                                    <?= $su['user_name'] ?? 'Unknown' ?> 
                                                    <span class="text-muted small">
                                                        <?= date('M d, H:i', strtotime($su['created_at'] ?? '')) ?>
                                                    </span>
                                                </button>
                                            </h2>
                                        </div>
                                        <div id="standupBody-<?= $su['id'] ?>" 
                                             class="card-body collapse"
                                             aria-labelledby="standupHead-<?= $su['id'] ?>">
                                            <p><strong>Yesterday:</strong> <?= strlen($y) > 60 ? substr($y, 0, 60) . '...' : $y ?></p>
                                            <p><strong>Today:</strong> <?= strlen($t) > 60 ? substr($t, 0, 60) . '...' : $t ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Team Members Summary -->
                        <?php if (!empty($teamMembers)): ?>
                        <div class="mt-4">
                            <h6><i class="fas fa-users"></i> Team Members</h6>
                            <div class="row">
                                <?php foreach ($teamMembers as $tm): ?>
                                <div class="col-md-4 mb-2">
                                    <div class="card text-white bg-info small">
                                        <div class="card-body">
                                            <h5 class="card-title"><?= esc($tm['name'] ?? 'Team Member') ?></h5>
                                            <p class="card-text small">
                                                <?= esc($tm['role'] ?? 'N/A') ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>

    </div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
    // Accordion functionality for stand-ups
    document.addEventListener('DOMContentLoaded', function() {
        const accordion = document.getElementById('standupAccordion');
        if (!accordion) return;

        const buttons = accordion.querySelectorAll('.btn-link');
        buttons.forEach(button => {
            button.addEventListener('click', function() {
                this.classList.toggle('collapsed');
                const target = this.getAttribute('data-target');
                const content = document.getElementById(target.replace('#', ''));
                if (content.style.display === 'block') {
                    content.style.display = 'none';
                } else {
                    content.style.display = 'block';
                }
            });
        });
    });
    </script>
<?= $this->endSection() ?>
