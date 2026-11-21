<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<div class="container-fluid">
        <div class="page-header">
            <h1><?= esc($title) ?></h1>
            <a href="<?= base_url('admin/recruitment') ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Recruitment
            </a>
        </div>

        <?php if ($offer): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h4>Joining Confirmation - <?= $application['applicant_name'] ?></h4>
                        
                        <div class="alert alert-info">
                            <strong>Programme:</strong> <?= $program['title'] ?? 'NGWebD Internship Programme' ?><br>
                            <strong>Position:</strong> <?= $application['preferred_role'] ?? 'N/A' ?><br>
                            <strong>Applied:</strong> <?= $application['applied_at'] ? date('M d, Y', strtotime($application['applied_at'])) : 'N/A' ?>
                        </div>

                        <form action="<?= base_url('admin/employee/join/process/' . $offer['id']) ?>" method="post">
                            <?= csrf_field() ?>

                            <input type="hidden" name="offer_id" value="<?= $offer['id'] ?>">

                            <div class="row gutters-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="confirmJoin" class="form-label">Confirm Joining</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="joinDecision" id="confirmYes" value="yes" required>
                                            <label class="form-check-label" for="confirmYes">Yes, the candidate will join</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="joinDecision" id="confirmNo" value="no">
                                            <label class="form-check-label" for="confirmNo">No, candidate will not join</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="adminNotes" class="form-label">Administrator Notes</label>
                                        <textarea class="form-control" name="admin_notes" rows="3" placeholder="Any notes about this joining decision"></textarea>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="row gutters-3">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-check"></i> Process Joining
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
        <?php elseif ($noOffer): ?>
        <div class="alert alert-warning">
            <h4>No Offer Letter Found</h4>
            <p>No offer letter is associated with this application. Please generate an offer letter first before processing joining.</p>
            <a href="<?= base_url('recruitment/offer/generate/' . ($application ? $application['id'] : '')) ?>" class="btn btn-secondary mt-2">Generate Offer Letter</a>
        </div>
        <?php else: ?>
        <div class="alert alert-danger">
            <h4>Offer Not Found</h4>
            <p>The offer letter could not be found. Please verify the offer ID.</p>
            <a href="<?= base_url('admin/recruitment') ?>" class="btn btn-secondary mt-2">Back to Recruitment</a>
        </div>
        <?php endif; ?>
    </div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
    // Form validation
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        if (!form) return;

        form.addEventListener('submit', function(e) {
            const selected = document.querySelector('input[name="joinDecision"]:checked');
            if (!selected) {
                e.preventDefault();
                alert('Please select whether the candidate will join or not.');
            }
        });
    });
    </script>
<?= $this->endSection() ?>
