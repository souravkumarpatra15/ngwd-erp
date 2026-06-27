<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<?php
$statusColors = ['draft'=>'secondary','sent'=>'info','accepted'=>'success','revision'=>'warning','rejected'=>'danger'];
$sc = $statusColors[$proposal['status']] ?? 'secondary';
?>

<div class="row g-4">
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <div>
          <span class="badge bg-<?= $sc ?> me-2"><?= ucfirst($proposal['status']) ?></span>
          <span class="text-muted small"><?= esc($proposal['proposal_number']) ?></span>
        </div>
        <div class="d-flex gap-2">
          <a href="<?= base_url('admin/proposals/edit/'.$proposal['id']) ?>" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil me-1"></i>Edit</a>
          <a href="<?= base_url('admin/proposals/pdf/'.$proposal['id']) ?>" class="btn btn-sm btn-outline-secondary" target="_blank"><i class="bi bi-file-pdf me-1"></i>PDF</a>
          <button class="btn btn-sm btn-outline-primary btn-email-prop" data-id="<?= $proposal['id'] ?>"><i class="bi bi-envelope me-1"></i>Email</button>
          <button class="btn btn-sm btn-outline-success btn-wa-prop" data-id="<?= $proposal['id'] ?>" style="color:#25D366;border-color:#25D366"><i class="bi bi-whatsapp me-1"></i>WA</button>
        </div>
      </div>
      <div class="card-body">
        <h5 class="fw-bold mb-1"><?= esc($proposal['title']) ?></h5>
        <div class="text-muted small mb-3"><i class="bi bi-person me-1"></i><?= esc($proposal['client_name'] ?? '—') ?></div>

        <?php if ($proposal['introduction']): ?>
        <h6 class="fw-semibold mt-3">Introduction</h6>
        <div class="text-muted"><?= nl2br(esc($proposal['introduction'])) ?></div>
        <?php endif; ?>

        <?php if ($proposal['scope_of_work']): ?>
        <h6 class="fw-semibold mt-3">Scope of Work</h6>
        <div class="text-muted"><?= nl2br(esc($proposal['scope_of_work'])) ?></div>
        <?php endif; ?>

        <?php if ($proposal['terms']): ?>
        <h6 class="fw-semibold mt-3">Terms & Conditions</h6>
        <div class="text-muted small"><?= nl2br(esc($proposal['terms'])) ?></div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold">Summary</h6></div>
      <div class="card-body">
        <table class="table table-sm table-borderless mb-0">
          <tr><td class="text-muted small">Client</td><td class="fw-semibold small"><?= esc($proposal['client_name'] ?? '—') ?></td></tr>
          <tr><td class="text-muted small">Valid Until</td><td><?= $proposal['valid_until'] && $proposal['valid_until'] !== '0000-00-00' ? date('d M Y',strtotime($proposal['valid_until'])) : '—' ?></td></tr>
          <tr><td class="text-muted small">Sent At</td><td class="small"><?= $proposal['sent_at'] ? date('d M Y',strtotime($proposal['sent_at'])) : '—' ?></td></tr>
          <tr><td colspan="2"><hr class="my-1"></td></tr>
          <tr><td class="text-muted small">Subtotal</td><td>₹<?= number_format($proposal['subtotal'] ?? $proposal['total_amount'] ?? 0, 0) ?></td></tr>
          <tr><td class="text-muted small">Tax (<?= $proposal['tax_percent'] ?? 0 ?>%)</td><td>₹<?= number_format($proposal['tax_amount'] ?? 0, 0) ?></td></tr>
          <tr><td class="fw-bold">Total</td><td class="fw-bold text-primary fs-5">₹<?= number_format($proposal['total_amount'] ?? 0, 0) ?></td></tr>
        </table>
      </div>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold">Update Status</h6></div>
      <div class="card-body">
        <div class="d-grid gap-2">
          <?php foreach (['draft','sent','accepted','revision','rejected'] as $s): ?>
          <button class="btn btn-sm btn-<?= $proposal['status']==$s ? '' : 'outline-' ?><?= $statusColors[$s] ?> btn-status" data-status="<?= $s ?>"><?= ucfirst($s) ?></button>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const BASE = '<?= base_url() ?>'; const CSRF = CSRF_TOKEN;
$('.btn-status').on('click', function() {
  const s = $(this).data('status');
  showLoader('Updating...');
  $.post(`${BASE}admin/proposals/status/<?= $proposal['id'] ?>`, {status: s, csrf_test_name: CSRF}, res => {
    hideLoader(); showToast(res.message, res.status);
    if (res.status === 'success') setTimeout(() => location.reload(), 600);
  }, 'json');
});
$('.btn-email-prop').on('click', function() {
  showLoader('Sending email...');
  $.post(`${BASE}admin/proposals/send-email/${$(this).data('id')}`, {csrf_test_name:CSRF}, res => { hideLoader(); showToast(res.message, res.status); }, 'json');
});
$('.btn-wa-prop').on('click', function() {
  showLoader('Sending WhatsApp...');
  $.post(`${BASE}admin/proposals/send-whatsapp/${$(this).data('id')}`, {csrf_test_name:CSRF}, res => { hideLoader(); showToast(res.message, res.status); }, 'json');
});
</script>
<?= $this->endSection() ?>
