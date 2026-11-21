<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
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
                            <i class="fas fa-sticky-note"></i> Notes
                            <span class="text-muted float-right small">
                                <?= count($notes ?? []) ?> note(s)
                            </span>
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        
                        <?php if (empty($notes)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                No notes yet. Use the form below to add a note.
                            </div>
                        <?php else: ?>
                            
                            <div class="table-responsive small">
                                <table class="table table-bordereless mb-0">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>User</th>
                                            <th>Note</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_reverse($notes ?? []) as $note): 
                                            $desc = $note['description'] ?? '';
                                            // Extract note text from description
                                            $noteText = $desc;
                                            if (preg_match('/quick capture note: (.+)/i', $desc, $m)) {
                                                $noteText = $m[1];
                                            }
                                        ?>
                                            <tr>
                                                <td><?= date('M d, H:i', strtotime($note['created_at'] ?? '')) ?></td>
                                                <td><?= esc($note['user_name'] ?? 'Unknown') ?></td>
                                                <td><?= strlen($noteText) > 80 ? substr($noteText, 0, 80) . '...' : $noteText ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                        <?php endif; ?>

                        <!-- Add Note Form -->
                        <div class="mt-4">
                            <h6><i class="fas fa-sticky-note"></i> Add Note</h6>
                            <form action="<?= base_url('admin/team-pilot/notes/save/' . $projectId) ?>" 
                                  method="post"
                                  style="max-width: 500px;">
                                <?= csrf_field() ?>
                                
                                <div class="mb-3">
                                    <textarea name="description" class="form-control"
                                              rows="3"
                                              placeholder="Enter your note..."></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Add Note
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
        <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            Select a project from the left to view notes, or use quick capture to add notes.
        </div>
        <?php endif; ?>

    </div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
    // Validate note form before submission (form posts to the real endpoint)
    document.addEventListener('DOMContentLoaded', function() {
        const noteForm = document.querySelector('form[action*="team-pilot/notes/save"]');
        if (!noteForm) return;

        noteForm.addEventListener('submit', function(e) {
            const desc = noteForm.elements['description'].value.trim();
            if (!desc) {
                e.preventDefault();
                alert('Note description is required.');
            }
        });
    });
    </script>
<?= $this->endSection() ?>
