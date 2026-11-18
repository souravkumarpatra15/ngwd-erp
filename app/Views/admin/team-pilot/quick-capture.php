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
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-lightbulb"></i> Quick Capture
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        
                        <?php if ($project): ?>
                        <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                        <input type="hidden" name="assigned_to" value="<?= $userId ?>">
                        <?php endif; ?>

                        <form action="<?= base_url('admin/team-pilot/submit-quick-capture') ?>" 
                              method="post"
                              id="quickCaptureForm">
                            <?= csrf_field() ?>
                            
                            <div class="mb-3">
                                <label for="quick-title" class="form-label">
                                    <i class="fas fa-heading"></i> Title <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="title" id="quick-title"
                                       class="form-control" 
                                       required
                                       placeholder="Enter a title for this task or note">
                            </div>
                            
                            <div class="mb-3">
                                <label for="quick-description" class="form-label">
                                    <i class="fas fa-align-left"></i> Description
                                </label>
                                <textarea name="description" id="quick-description"
                                          class="form-control"
                                          rows="3"
                                          placeholder="Enter description (optional)"></textarea>
                            </div>
                            
                            <input type="hidden" name="type" value="task">
                            <input type="hidden" name="priority" value="medium">
                            
                            <?php if ($dueDate = isset($dueDate) ? $dueDate : ''): ?>
                            <div class="mb-3">
                                <label for="quick-due-date" class="form-label">
                                    <i class="fas fa-calendar"></i> Due Date
                                </label>
                                <input type="date" name="due_date" id="quick-due-date"
                                       class="form-control">
                            </div>
                            <?php endif; ?>

                            <input type="hidden" name="user_id" value="<?= $userId ?>">

                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Capture
                                </button>
                                <button type="button" class="btn btn-outline-secondary"
                                        onclick="window.history.back()">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('quickCaptureForm');
        if (!form) return;

        form.addEventListener('submit', function(e) {
            const title = form.elements['title'].value.trim();
            if (!title) {
                e.preventDefault();
                alert('Title is required.');
                return;
            }
            // Show success message
            alert('Quick capture submitted successfully!');
        });
    });
    </script>
</body>
</html>