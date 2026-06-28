<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="card border-0 shadow-sm mb-4">
  <div class="card-body">
    <form method="GET" class="row g-3 align-items-end">
      <div class="col-md-3">
        <label class="form-label small fw-semibold">Expiry Within (Days)</label>
        <select name="days" class="form-select form-select-sm">
          <?php foreach ([30,60,90,180,365] as $d): ?>
          <option value="<?= $d ?>" <?= ($days??30)==$d?'selected':'' ?>><?= $d ?> days</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-semibold">Type</label>
        <select name="type" class="form-select form-select-sm">
          <option value="all" <?= ($type??'all')==='all'?'selected':'' ?>>Domains + Hostings</option>
          <option value="domain" <?= ($type??'')==='domain'?'selected':'' ?>>Domains Only</option>
          <option value="hosting" <?= ($type??'')==='hosting'?'selected':'' ?>>Hostings Only</option>
        </select>
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-primary btn-sm w-100 mt-4">Filter</button>
      </div>
      <div class="col-md-4 text-end mt-auto">
        <a href="<?= base_url('admin/reports/export/domains/excel?days='.($days??30).'&type='.($type??'all')) ?>" class="btn btn-outline-success btn-sm"><i class="bi bi-file-earmark-excel me-1"></i>Excel</a>
      </div>
    </form>
  </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="fs-3 fw-bold text-danger"><?= $expired_count ?></div>
      <div class="text-muted small">Already Expired</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="fs-3 fw-bold text-warning"><?= $expiring_soon_count ?></div>
      <div class="text-muted small">Expiring in 30 Days</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="fs-3 fw-bold text-success"><?= $active_count ?></div>
      <div class="text-muted small">Active</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="fs-3 fw-bold text-primary">₹<?= number_format($total_renewal_cost, 0) ?></div>
      <div class="text-muted small">Renewal Cost (Period)</div>
    </div>
  </div>
</div>

<!-- Domains table -->
<?php if (!in_array($type??'all', ['hosting'])): ?>
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
    <h6 class="mb-0 fw-semibold"><i class="bi bi-globe me-2 text-primary"></i>Domains</h6>
    <span class="badge bg-secondary"><?= count($domains) ?></span>
  </div>
  <div class="card-body p-0">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr><th>Domain</th><th>Client</th><th>Registrar</th><th>Expiry</th><th>Days Left</th><th>Renewal</th><th>Status</th><th></th></tr>
      </thead>
      <tbody>
        <?php if (empty($domains)): ?>
        <tr><td colspan="8" class="text-center text-muted py-4">No domains found.</td></tr>
        <?php else: ?>
        <?php foreach ($domains as $d):
          $daysLeft = $d['expiry_date'] ? ceil((strtotime($d['expiry_date'])-time())/86400) : null;
          $sc = $daysLeft <= 0 ? 'danger' : ($daysLeft <= 30 ? 'warning' : 'success');
        ?>
        <tr>
          <td class="fw-semibold small"><?= esc($d['domain_name']) ?></td>
          <td class="small text-muted"><?= esc($d['client_name'] ?? '—') ?></td>
          <td class="small text-muted"><?= esc($d['registrar'] ?? '—') ?></td>
          <td class="small"><?= $d['expiry_date'] && $d['expiry_date']!=='0000-00-00' ? date('d M Y',strtotime($d['expiry_date'])) : '—' ?></td>
          <td><span class="badge bg-<?= $sc ?>"><?= $daysLeft !== null ? ($daysLeft <= 0 ? 'Expired' : "{$daysLeft}d") : '—' ?></span></td>
          <td class="small fw-semibold">₹<?= number_format($d['renewal_cost'] ?? 0, 0) ?></td>
          <td><span class="badge bg-<?= ['active'=>'success','expiring_soon'=>'warning','expired'=>'danger'][$d['status']] ?? 'secondary' ?>"><?= ucwords(str_replace('_',' ',$d['status'])) ?></span></td>
          <td>
            <button class="btn btn-xs btn-outline-info" onclick="sendReminder('domain',<?= $d['id'] ?>)" title="Send renewal reminder"><i class="bi bi-bell"></i></button>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<!-- Hostings table -->
<?php if (!in_array($type??'all', ['domain'])): ?>
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
    <h6 class="mb-0 fw-semibold"><i class="bi bi-server me-2 text-warning"></i>Hostings</h6>
    <span class="badge bg-secondary"><?= count($hostings) ?></span>
  </div>
  <div class="card-body p-0">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr><th>Provider</th><th>Client</th><th>Package</th><th>Server IP</th><th>Expiry</th><th>Days Left</th><th>Renewal</th><th></th></tr>
      </thead>
      <tbody>
        <?php if (empty($hostings)): ?>
        <tr><td colspan="8" class="text-center text-muted py-4">No hostings found.</td></tr>
        <?php else: ?>
        <?php foreach ($hostings as $h):
          $daysLeft = $h['expiry_date'] ? ceil((strtotime($h['expiry_date'])-time())/86400) : null;
          $sc = $daysLeft <= 0 ? 'danger' : ($daysLeft <= 30 ? 'warning' : 'success');
        ?>
        <tr>
          <td class="fw-semibold small"><?= esc($h['provider']) ?></td>
          <td class="small text-muted"><?= esc($h['client_name'] ?? '—') ?></td>
          <td class="small text-muted"><?= esc($h['package'] ?? '—') ?></td>
          <td class="font-monospace small text-muted"><?= esc($h['server_ip'] ?? '—') ?></td>
          <td class="small"><?= $h['expiry_date'] && $h['expiry_date']!=='0000-00-00' ? date('d M Y',strtotime($h['expiry_date'])) : '—' ?></td>
          <td><span class="badge bg-<?= $sc ?>"><?= $daysLeft !== null ? ($daysLeft <= 0 ? 'Expired' : "{$daysLeft}d") : '—' ?></span></td>
          <td class="small fw-semibold">₹<?= number_format($h['renewal_cost'] ?? 0, 0) ?></td>
          <td>
            <button class="btn btn-xs btn-outline-info" onclick="sendReminder('hosting',<?= $h['id'] ?>)" title="Send renewal reminder"><i class="bi bi-bell"></i></button>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const BASE = '<?= base_url() ?>'; const CSRF = CSRF_TOKEN;
function sendReminder(type, id) {
  showLoader('Sending reminder...');
  $.post(`${BASE}admin/${type}s/remind/${id}`, {csrf_test_name: CSRF}, res => {
    hideLoader(); showToast(res.message, res.status);
  }, 'json');
}
</script>
<?= $this->endSection() ?>
