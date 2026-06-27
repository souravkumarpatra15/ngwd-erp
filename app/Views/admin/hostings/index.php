<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="fs-4 fw-bold text-success"><?= count(array_filter($hostings, fn($h) => $h['status'] === 'active')) ?></div>
      <div class="text-muted small">Active</div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="fs-4 fw-bold text-warning"><?= $expiring_soon ?></div>
      <div class="text-muted small">Expiring in 30 Days</div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="fs-4 fw-bold text-danger"><?= $expired ?></div>
      <div class="text-muted small">Expired</div>
    </div>
  </div>
</div>

<div class="d-flex justify-content-end mb-3">
  <a href="<?= base_url('admin/hostings/create') ?>" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg me-1"></i>Add Hosting
  </a>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <table id="hostingsTable" class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Server / Domain</th>
          <th>Client</th>
          <th>Provider</th>
          <th>Plan</th>
          <th>Expiry</th>
          <th>Renewal</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($hostings as $h): ?>
          <?php
          $sc = ['active' => 'success', 'expiring_soon' => 'warning', 'expired' => 'danger'][$h['status']] ?? 'secondary';
          $days = $h['expiry_date'] ? ceil((strtotime($h['expiry_date']) - time()) / 86400) : null;
          ?>
          <tr>
            <td>
              <div class="fw-semibold"><?= esc($h['server_name'] ?? $h['domain_name'] ?? '—') ?></div>
              <?php if (!empty($h['ip_address'])): ?>
                <div class="text-muted font-monospace" style="font-size:11px"><?= esc($h['ip_address']) ?></div>
              <?php endif; ?>
            </td>
            <td class="small"><?= esc($h['client_name'] ?? '—') ?></td>
            <td class="small text-muted"><?= esc($h['provider'] ?? '—') ?></td>
            <td class="small"><?= esc($h['plan'] ?? '—') ?></td>
            <td class="small">
              <?= $h['expiry_date'] && $h['expiry_date'] !== '0000-00-00' ? date('d M Y', strtotime($h['expiry_date'])) : '—' ?>
              <?php if ($days !== null && $days <= 30): ?>
                <div class="text-<?= $days <= 0 ? 'danger' : 'warning' ?> fw-semibold" style="font-size:11px">
                  <?= $days <= 0 ? 'Expired' : "{$days}d left" ?>
                </div>
              <?php endif; ?>
            </td>
            <td class="small fw-semibold">₹<?= number_format($h['renewal_cost'] ?? 0, 0) ?></td>
            <td><span class="badge bg-<?= $sc ?>"><?= ucwords(str_replace('_', ' ', $h['status'])) ?></span></td>
            <td>
              <div class="d-flex gap-1">
                <a href="<?= base_url('admin/hostings/edit/' . $h['id']) ?>" class="btn btn-xs btn-outline-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                <button class="btn btn-xs btn-outline-info btn-remind-hosting" data-id="<?= $h['id'] ?>" title="Send Reminder"><i class="bi bi-bell"></i></button>
                <button class="btn btn-xs btn-outline-danger btn-del-hosting"
                  data-id="<?= $h['id'] ?>"
                  data-confirm-title="Delete Hosting?"
                  data-confirm="Delete hosting record '<?= esc($h['server_name'] ?? $h['provider'] ?? '') ?>'?"
                  data-confirm-yes="Yes, Delete"><i class="bi bi-trash"></i></button>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
  const BASE = '<?= base_url() ?>';
  const CSRF = CSRF_TOKEN;
  $('#hostingsTable').DataTable({
    pageLength: 25,
    order: [
      [4, 'asc']
    ]
  });

  $(document).on('click', '.btn-remind-hosting', function() {
    showLoader('Sending reminder...');
    $.post(`${BASE}admin/hostings/remind/${$(this).data('id')}`, {
      csrf_test_name: CSRF
    }, res => {
      hideLoader();
      showToast(res.message, res.status);
    }, 'json');
  });

  let delId = null;
  $(document).on('click', '.btn-del-hosting', function() {
    delId = $(this).data('id');
    $('#ngConfirmTitle').text($(this).data('confirm-title'));
    $('#ngConfirmMessage').text($(this).data('confirm'));
    $('#ngConfirmYes').text($(this).data('confirm-yes'));
    bootstrap.Modal.getOrCreateInstance(document.getElementById('ngConfirmModal')).show();
  });
  $('#ngConfirmYes').off('click').on('click', function() {
    if (!delId) return;
    bootstrap.Modal.getInstance(document.getElementById('ngConfirmModal')).hide();
    showLoader('Deleting...');
    $.post(`${BASE}admin/hostings/delete/${delId}`, {
      csrf_test_name: CSRF
    }, res => {
      hideLoader();
      showToast(res.message, res.status);
      if (res.status === 'success') location.reload();
    }, 'json');
    delId = null;
  });
</script>
<?= $this->endSection() ?>