<?= $this->extend('layouts/client') ?>
<?= $this->section('content') ?>

<?php
function ngwd_wa_link($num) {
  $n = preg_replace('/[^0-9]/', '', (string) $num);
  if ($n === '') return '';
  if (strlen($n) === 10) $n = '91' . $n;
  return 'https://wa.me/' . $n;
}
$statusColors = ['new' => 'primary', 'contacted' => 'info', 'interested' => 'success', 'not_interested' => 'warning', 'converted' => 'success', 'junk' => 'danger'];
?>

<div class="d-flex flex-wrap gap-2 mb-3 justify-content-between align-items-center">
  <div class="d-flex flex-wrap gap-2">
    <span class="badge bg-light text-dark border">Total: <?= array_sum($counts) ?></span>
    <?php foreach ($counts as $st => $c): ?>
      <span class="badge bg-<?= $statusColors[$st] ?? 'secondary' ?>-subtle text-<?= $statusColors[$st] ?? 'secondary' ?> border"><?= ucwords(str_replace('_', ' ', $st)) ?>: <?= $c ?></span>
    <?php endforeach; ?>
  </div>
  <form method="GET" class="d-flex gap-2">
    <select name="project_id" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width:160px">
      <option value="">All Projects</option>
      <?php foreach ($projects as $p): ?>
        <option value="<?= $p['id'] ?>" <?= $filter_project_id == $p['id'] ? 'selected' : '' ?>><?= esc($p['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="width:150px">
      <option value="">All Status</option>
      <?php foreach (['new','contacted','interested','not_interested','converted','junk'] as $s): ?>
        <option value="<?= $s ?>" <?= $filter_status === $s ? 'selected' : '' ?>><?= ucwords(str_replace('_', ' ', $s)) ?></option>
      <?php endforeach; ?>
    </select>
  </form>
</div>

<?php if (empty($leads)): ?>
<div class="text-center text-muted py-5">
  <i class="bi bi-megaphone fs-2 d-block mb-2 opacity-25"></i>No leads yet. Leads from your ad campaigns will appear here.
</div>
<?php else: ?>
<div class="row g-3">
  <?php foreach ($leads as $l): ?>
  <div class="col-md-6 col-lg-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <div>
            <div class="fw-semibold"><?= esc($l['name']) ?></div>
            <?php if (!empty($l['city'])): ?><div class="text-muted small"><i class="bi bi-geo-alt me-1"></i><?= esc($l['city']) ?></div><?php endif; ?>
          </div>
          <span class="badge bg-<?= $statusColors[$l['status']] ?? 'secondary' ?>"><?= ucwords(str_replace('_', ' ', $l['status'])) ?></span>
        </div>

        <div class="small text-muted mb-2">
          <span class="badge bg-light text-dark border me-1"><?= ucwords(str_replace('_', ' ', $l['platform'])) ?></span>
          <?php if (!empty($l['campaign_name'])): ?><?= esc($l['campaign_name']) ?><?php endif; ?>
        </div>

        <?php if (!empty($l['requirement'])): ?>
          <p class="small mb-2"><?= esc($l['requirement']) ?></p>
        <?php endif; ?>

        <div class="d-flex gap-2 mb-2">
          <?php if (!empty($l['phone'])): ?>
            <a href="tel:<?= esc($l['phone']) ?>" class="btn btn-xs btn-outline-primary flex-fill"><i class="bi bi-telephone me-1"></i>Call</a>
          <?php endif; ?>
          <?php $wa = ngwd_wa_link($l['whatsapp'] ?: $l['phone']); if ($wa): ?>
            <a href="<?= esc($wa) ?>" target="_blank" rel="noopener" class="btn btn-xs btn-outline-success flex-fill"><i class="bi bi-whatsapp me-1"></i>WhatsApp</a>
          <?php endif; ?>
          <?php if (!empty($l['email'])): ?>
            <a href="mailto:<?= esc($l['email']) ?>" class="btn btn-xs btn-outline-secondary flex-fill"><i class="bi bi-envelope me-1"></i>Mail</a>
          <?php endif; ?>
        </div>

        <div class="d-flex justify-content-between align-items-center">
          <span class="text-muted" style="font-size:11px"><?= !empty($l['lead_date']) ? date('d M Y', strtotime($l['lead_date'])) : '' ?></span>
          <select class="form-select form-select-sm w-auto lead-status-select" data-id="<?= $l['id'] ?>" style="font-size:12px">
            <?php foreach (['new','contacted','interested','not_interested','converted','junk'] as $s): ?>
              <option value="<?= $s ?>" <?= $l['status'] === $s ? 'selected' : '' ?>><?= ucwords(str_replace('_', ' ', $s)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  $(document).on('change', '.lead-status-select', function() {
    const id = $(this).data('id');
    const status = $(this).val();
    $.post(`<?= base_url('portal/marketing-leads/status/') ?>${id}`, { status, csrf_test_name: getCsrfToken() }, res => {
      if (res.status === 'success') showToast('Status updated', 'success');
      else showToast(res.message || 'Update failed', 'error');
    }, 'json').fail(() => showToast('Server error. Please try again.', 'error'));
  });
</script>
<?= $this->endSection() ?>
