<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<?php $colors = ['draft'=>'secondary','in_progress'=>'primary','submitted'=>'info','under_review'=>'warning','changes_requested'=>'danger','approved'=>'success','rejected'=>'dark']; ?>
<?php $actionColors = ['approved'=>'success','changes_requested'=>'danger','submitted'=>'info']; ?>

<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
  <div>
    <a href="<?= base_url('admin/deliverables?project_id='.$deliverable['project_id']) ?>" class="text-decoration-none small text-muted"><i class="bi bi-arrow-left me-1"></i>Back to Deliverables</a>
    <h5 class="mb-1 fw-bold mt-2"><?= esc($deliverable['title']) ?></h5>
    <div class="text-muted small">
      <?= esc($deliverable['project_name'] ?? '—') ?>
      <?php if (!empty($deliverable['milestone_title'])): ?> · Milestone: <?= esc($deliverable['milestone_title']) ?><?php endif; ?>
      · v<?= esc($deliverable['version']) ?>
    </div>
  </div>
  <div class="text-end">
    <span class="badge bg-<?= $colors[$deliverable['status']] ?? 'secondary' ?> fs-6 mb-2 d-inline-block" id="dStatusBadge"><?= ucwords(str_replace('_',' ',$deliverable['status'])) ?></span>
    <?php if (!empty($canManage)): ?>
    <div>
      <select class="form-select form-select-sm d-inline-block" id="dStatusSelect" style="width:180px">
        <?php foreach ($colors as $s => $c): ?><option value="<?= $s ?>" <?= $deliverable['status']===$s?'selected':'' ?>><?= ucwords(str_replace('_',' ',$s)) ?></option><?php endforeach; ?>
      </select>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php if ($deliverable['status'] === 'changes_requested'): ?>
<div class="alert alert-danger d-flex align-items-center gap-2 mb-4">
  <i class="bi bi-exclamation-triangle-fill fs-5"></i>
  <div><strong>The client requested changes on this deliverable.</strong> See their feedback in the timeline below.</div>
</div>
<?php endif; ?>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle me-2 text-primary"></i>Details</h6></div>
      <div class="card-body">
        <?php if (!empty($deliverable['description'])): ?>
        <div class="mb-3 small"><?= nl2br(esc($deliverable['description'])) ?></div>
        <?php else: ?>
        <div class="text-muted small fst-italic mb-3">No description provided.</div>
        <?php endif; ?>
        <table class="table table-sm table-borderless mb-0">
          <tr><td class="text-muted small" style="width:140px">Owner</td><td class="small fw-semibold"><?= esc($deliverable['owner_name'] ?? '—') ?></td></tr>
          <tr><td class="text-muted small">Due Date</td><td class="small"><?= $deliverable['due_date'] ? date('d M Y', strtotime($deliverable['due_date'])) : '—' ?></td></tr>
          <tr><td class="text-muted small">Submitted</td><td class="small"><?= $deliverable['submitted_at'] ? date('d M Y, h:i A', strtotime($deliverable['submitted_at'])) : '—' ?></td></tr>
          <tr><td class="text-muted small">Last Reviewed</td><td class="small"><?= $deliverable['reviewed_at'] ? date('d M Y, h:i A', strtotime($deliverable['reviewed_at'])) : '—' ?></td></tr>
          <tr><td class="text-muted small">Approved</td><td class="small"><?= $deliverable['approved_at'] ? date('d M Y, h:i A', strtotime($deliverable['approved_at'])).' by '.esc($deliverable['approved_by_name'] ?? '—') : '—' ?></td></tr>
        </table>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold"><i class="bi bi-chat-square-text me-2 text-info"></i>Client Feedback &amp; Review History</h6></div>
      <div class="card-body p-0" style="max-height:520px;overflow-y:auto">
        <?php if (empty($history)): ?>
        <div class="text-center text-muted py-5 small"><i class="bi bi-chat-square-text fs-3 d-block mb-2 opacity-25"></i>No client feedback yet.<br>This shows up as soon as the client approves or requests changes from their portal.</div>
        <?php else: ?>
        <div class="list-group list-group-flush">
          <?php foreach ($history as $h): ?>
          <div class="list-group-item px-4 py-3">
            <div class="d-flex justify-content-between align-items-start mb-1">
              <span class="badge bg-<?= $actionColors[$h['action']] ?? 'secondary' ?>"><?= ucwords(str_replace('_',' ',$h['action'])) ?></span>
              <span class="text-muted" style="font-size:11px"><?= date('d M Y, h:i A', strtotime($h['created_at'])) ?></span>
            </div>
            <div class="small fw-semibold mb-1"><?= esc($h['user_name'] ?? 'Client') ?></div>
            <?php if (!empty($h['comment'])): ?>
            <div class="small text-muted"><?= nl2br(esc($h['comment'])) ?></div>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
<?php if (!empty($canManage)): ?>
<?= $this->section('scripts') ?>
<script>
$('#dStatusSelect').on('change', function () {
  const status = $(this).val();
  const statusColors = <?= json_encode($colors) ?>;
  showLoader('Updating status...');
  $.post(`<?= base_url('admin/deliverables/status/'.$deliverable['id']) ?>`, { status, csrf_test_name: getCsrfToken() }, res => {
    hideLoader();
    if (res.status === 'success') {
      showToast(res.message, 'success');
      const badge = $('#dStatusBadge');
      badge.attr('class', 'badge bg-' + (statusColors[status] || 'secondary') + ' fs-6 mb-2 d-inline-block');
      badge.text(status.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()));
    } else {
      showToast(res.message || 'Failed to update status', 'error');
    }
  }, 'json').fail(() => { hideLoader(); showToast('Server error. Please try again.', 'error'); });
});
</script>
<?= $this->endSection() ?>
<?php endif; ?>
