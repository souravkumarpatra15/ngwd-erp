<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<?php
$sc = ['draft'=>'secondary','sent'=>'info','signed'=>'success','rejected'=>'danger'][$agreement['status']] ?? 'secondary';
?>

<div class="row g-4">
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <div>
          <span class="badge bg-<?= $sc ?> me-2"><?= ucfirst($agreement['status']) ?></span>
          <span class="text-muted small"><?= esc($agreement['agreement_number']) ?></span>
        </div>
        <div class="d-flex gap-2">
          <a href="<?= base_url('admin/agreements/edit/'.$agreement['id']) ?>" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil me-1"></i>Edit</a>
          <a href="<?= base_url('admin/agreements/pdf/'.$agreement['id']) ?>" class="btn btn-sm btn-outline-secondary" target="_blank"><i class="bi bi-file-pdf me-1"></i>PDF</a>
          <button class="btn btn-sm btn-outline-primary btn-send-email" data-id="<?= $agreement['id'] ?>"><i class="bi bi-envelope me-1"></i>Email</button>
          <button class="btn btn-sm btn-outline-success btn-send-wa" data-id="<?= $agreement['id'] ?>" style="color:#25D366;border-color:#25D366"><i class="bi bi-whatsapp me-1"></i>WA</button>
        </div>
      </div>
      <div class="card-body p-4 p-md-5">
        <h4 class="fw-bold mb-1"><?= esc($agreement['title']) ?></h4>
        <div class="text-muted small mb-4">
          <i class="bi bi-person me-1"></i><?= esc($agreement['client_name'] ?? '—') ?>
          <?php if ($agreement['project_name'] ?? null): ?> &nbsp;·&nbsp; <i class="bi bi-folder2-open me-1"></i><?= esc($agreement['project_name']) ?><?php endif; ?>
        </div>

        <?php
        $sections = [
          'client_information'  => 'Client Information',
          'project_information' => 'Project Information',
          'deliverables'        => 'Deliverables',
          'timeline'            => 'Timeline',
          'payment_terms'       => 'Payment Terms',
          'support_terms'       => 'Support & Maintenance',
          'cancellation_terms'  => 'Cancellation Terms',
          'terms_conditions'    => 'General Terms & Conditions',
        ];
        foreach ($sections as $field => $label):
          if (empty($agreement[$field])) continue;
        ?>
        <div class="mb-4">
          <h6 class="fw-semibold text-uppercase small mb-2" style="letter-spacing:.05em;color:#6c757d"><?= $label ?></h6>
          <div class="border-start border-3 ps-3" style="border-color:#dee2e6!important">
            <?= nl2br(esc($agreement[$field])) ?>
          </div>
        </div>
        <?php endforeach; ?>

        <?php if ($agreement['status'] === 'signed' && $agreement['signed_at']): ?>
        <div class="alert alert-success d-flex align-items-center gap-2 mt-4">
          <i class="bi bi-patch-check-fill fs-5"></i>
          <div>Digitally signed on <?= date('d M Y H:i', strtotime($agreement['signed_at'])) ?> · IP: <?= esc($agreement['signature_ip'] ?? '—') ?></div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold">Details</h6></div>
      <div class="card-body">
        <table class="table table-sm table-borderless mb-3">
          <tr><td class="text-muted small">Client</td><td class="fw-semibold small"><?= esc($agreement['client_name'] ?? '—') ?></td></tr>
          <tr><td class="text-muted small">Project</td><td class="small"><?= esc($agreement['project_name'] ?? '—') ?></td></tr>
          <tr><td class="text-muted small">Status</td><td><span class="badge bg-<?= $sc ?>"><?= ucfirst($agreement['status']) ?></span></td></tr>
          <tr><td class="text-muted small">Sent</td><td class="small"><?= $agreement['sent_at'] ? date('d M Y',strtotime($agreement['sent_at'])) : '—' ?></td></tr>
          <tr><td class="text-muted small">Signed</td><td class="small"><?= $agreement['signed_at'] ? date('d M Y',strtotime($agreement['signed_at'])) : '—' ?></td></tr>
          <tr><td class="text-muted small">Created</td><td class="small"><?= date('d M Y',strtotime($agreement['created_at'])) ?></td></tr>
        </table>
      </div>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold">Update Status</h6></div>
      <div class="card-body d-grid gap-2">
        <?php foreach (['draft','sent','signed','rejected'] as $s): ?>
        <button class="btn btn-sm btn-<?= $agreement['status']==$s ? '' : 'outline-' ?><?= ['draft'=>'secondary','sent'=>'info','signed'=>'success','rejected'=>'danger'][$s] ?> btn-status" data-status="<?= $s ?>">
          <?= ucfirst($s) ?>
          <?php if ($agreement['status'] === $s): ?><i class="bi bi-check ms-1"></i><?php endif; ?>
        </button>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const BASE = '<?= base_url() ?>'; const CSRF = CSRF_TOKEN;
$('.btn-status').on('click', function() {
  showLoader('Updating...');
  $.post(`${BASE}admin/agreements/status/<?= $agreement['id'] ?>`, {status:$(this).data('status'),csrf_test_name:CSRF}, res => {
    hideLoader(); showToast(res.message, res.status);
    if (res.status==='success') setTimeout(()=>location.reload(),700);
  }, 'json');
});
$('.btn-send-email').on('click', function() {
  showLoader('Sending email...');
  $.post(`${BASE}admin/agreements/send-email/${$(this).data('id')}`,{csrf_test_name:CSRF}, res => { hideLoader(); showToast(res.message,res.status); }, 'json');
});
$('.btn-send-wa').on('click', function() {
  showLoader('Sending WhatsApp...');
  $.post(`${BASE}admin/agreements/send-whatsapp/${$(this).data('id')}`,{csrf_test_name:CSRF}, res => { hideLoader(); showToast(res.message,res.status); }, 'json');
});
</script>
<?= $this->endSection() ?>
