<?= $this->extend('layouts/client') ?>
<?= $this->section('content') ?>
<?php $sc = ['draft' => 'secondary', 'sent' => 'info', 'accepted' => 'success', 'revision' => 'warning', 'rejected' => 'danger'][$proposal['status']] ?? 'secondary'; ?>

<div class="row justify-content-center">
  <div class="col-lg-9">
    <div class="card border-0 shadow-sm">
      <div class="card-body p-4 p-md-5">
        <div class="d-flex justify-content-between align-items-start mb-4">
          <div>
            <h4 class="fw-bold mb-1"><?= esc($proposal['title']) ?></h4>
            <div class="text-muted small"><?= esc($proposal['proposal_number']) ?></div>
          </div>
          <span class="badge bg-<?= $sc ?> fs-6"><?= ucfirst($proposal['status']) ?></span>
        </div>

        <?php if ($proposal['introduction']): ?>
          <h6 class="fw-semibold">Overview</h6>
          <p class="text-muted"><?= nl2br(esc($proposal['introduction'])) ?></p>
        <?php endif; ?>

        <?php if ($proposal['scope_of_work']): ?>
          <h6 class="fw-semibold mt-3">Scope of Work</h6>
          <p class="text-muted"><?= nl2br(esc($proposal['scope_of_work'])) ?></p>
        <?php endif; ?>

        <div class="bg-light rounded p-3 mt-3">
          <div class="row text-center">
            <div class="col-4">
              <div class="text-muted small">Amount</div>
              <div class="fw-bold text-primary fs-5">₹<?= number_format($proposal['total_amount'] ?? 0, 0) ?></div>
            </div>
            <div class="col-4">
              <div class="text-muted small">Valid Until</div>
              <div class="fw-semibold small"><?= $proposal['valid_until'] && $proposal['valid_until'] !== '0000-00-00' ? date('d M Y', strtotime($proposal['valid_until'])) : '—' ?></div>
            </div>
            <div class="col-4">
              <div class="text-muted small">Status</div>
              <span class="badge bg-<?= $sc ?>"><?= ucfirst($proposal['status']) ?></span>
            </div>
          </div>
        </div>

        <?php if ($proposal['terms']): ?>
          <div class="mt-4 border-top pt-3">
            <h6 class="fw-semibold">Terms &amp; Conditions</h6>
            <div class="small text-muted"><?= nl2br(esc($proposal['terms'])) ?></div>
          </div>
        <?php endif; ?>

        <?php if ($proposal['status'] === 'sent'): ?>
          <div class="d-flex gap-2 mt-4 pt-3 border-top">
            <a href="#" class="btn btn-success btn-accept-prop" data-id="<?= $proposal['id'] ?>"><i class="bi bi-check-circle me-1"></i>Accept Proposal</a>
            <a href="#" class="btn btn-outline-danger btn-reject-prop" data-id="<?= $proposal['id'] ?>"><i class="bi bi-x-circle me-1"></i>Request Revision</a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
  const BASE = '<?= base_url() ?>';
  const CSRF = document.querySelector('meta[name=csrf-token]').content;

  let pendingAction = null;

  function respondToProposal(action) {
    showLoader(action === 'accept' ? 'Accepting proposal...' : 'Sending request...');
    $.post(`${BASE}portal/proposals/respond/<?= $proposal['id'] ?>`, {
      action,
      csrf_test_name: CSRF
    }, res => {
      hideLoader();
      if (res.status === 'success') {
        showToast(res.message, 'success');
        setTimeout(() => location.reload(), 900);
      } else {
        showToast(res.message, 'error');
      }
    }, 'json');
  }

  $('.btn-accept-prop').on('click', function(e) {
    e.preventDefault();
    pendingAction = 'accept';
    $('#ngConfirmTitle').text('Accept this proposal?');
    $('#ngConfirmMessage').text('The team will be notified and can move ahead with the work.');
    $('#ngConfirmYes').removeClass('btn-danger').addClass('btn-success').text('Yes, Accept');
    bootstrap.Modal.getOrCreateInstance(document.getElementById('ngConfirmModal')).show();
  });

  $('.btn-reject-prop').on('click', function(e) {
    e.preventDefault();
    pendingAction = 'revision';
    $('#ngConfirmTitle').text('Request a revision?');
    $('#ngConfirmMessage').text('We\'ll let the team know you would like some changes before moving ahead.');
    $('#ngConfirmYes').removeClass('btn-success').addClass('btn-danger').text('Yes, Request Revision');
    bootstrap.Modal.getOrCreateInstance(document.getElementById('ngConfirmModal')).show();
  });

  $('#ngConfirmYes').off('click').on('click', function() {
    if (!pendingAction) return;
    bootstrap.Modal.getInstance(document.getElementById('ngConfirmModal')).hide();
    respondToProposal(pendingAction);
    pendingAction = null;
  });
</script>
<?= $this->endSection() ?>