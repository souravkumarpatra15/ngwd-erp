<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="fs-4 fw-bold text-success"><?= count(array_filter($domains, fn($d) => $d['status'] === 'active')) ?></div>
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
  <a href="<?= base_url('admin/domains/create') ?>" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add Domain</a>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <table id="domainsTable" class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Domain</th>
          <th>Client</th>
          <th>Registrar</th>
          <th>Expiry</th>
          <th>Renewal</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($domains as $d): ?>
          <?php
          $sc = ['active' => 'success', 'expiring_soon' => 'warning', 'expired' => 'danger'][$d['status']] ?? 'secondary';
          $days = $d['expiry_date'] ? ceil((strtotime($d['expiry_date']) - time()) / 86400) : null;
          ?>
          <tr>
            <td>
              <div class="fw-semibold"><?= esc($d['domain_name']) ?></div>
              <?php if ($d['domain_type'] ?? null): ?><div class="text-muted small"><?= esc($d['domain_type']) ?></div><?php endif; ?>
            </td>
            <td class="small"><?= esc($d['client_name'] ?? '—') ?></td>
            <td class="small text-muted"><?= esc($d['registrar'] ?? '—') ?></td>
            <td class="small">
              <?= $d['expiry_date'] && $d['expiry_date'] !== '0000-00-00' ? date('d M Y', strtotime($d['expiry_date'])) : '—' ?>
              <?php if ($days !== null && $days <= 30): ?>
                <div class="text-<?= $days <= 0 ? 'danger' : 'warning' ?> fw-semibold" style="font-size:11px"><?= $days <= 0 ? 'Expired' : "{$days}d left" ?></div>
              <?php endif; ?>
            </td>
            <td class="small fw-semibold">₹<?= number_format($d['renewal_cost'] ?? 0, 0) ?></td>
            <td><span class="badge bg-<?= $sc ?>"><?= ucwords(str_replace('_', ' ', $d['status'])) ?></span></td>
            <td>
              <div class="d-flex gap-1">
                <a href="<?= base_url('admin/domains/edit/' . $d['id']) ?>" class="btn btn-xs btn-outline-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                <a href="<?= base_url('admin/invoices/create?type=domain&client_id=' . $d['client_id'] . '&domain_id=' . $d['id']) ?>" class="btn btn-xs btn-outline-primary" title="Create Renewal Invoice"><i class="bi bi-receipt"></i></a>
                <button class="btn btn-xs btn-outline-info btn-remind-domain" data-id="<?= $d['id'] ?>" title="Send Reminder"><i class="bi bi-bell"></i></button>
                <button class="btn btn-xs btn-outline-danger btn-del-domain"
                  data-id="<?= $d['id'] ?>"
                  data-confirm-title="Delete Domain?"
                  data-confirm="Delete domain '<?= esc($d['domain_name']) ?>'?"
                  data-confirm-yes="Yes, Delete"
                  title="Delete"><i class="bi bi-trash"></i></button>
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
  $('#domainsTable').DataTable({
    pageLength: 25,
    order: [
      [3, 'asc']
    ]
  });

  $(document).on('click', '.btn-remind-domain', function() {
    const id = $(this).data('id');
    showLoader('Sending reminder...');
    $.post(`${BASE}admin/domains/remind/${id}`, {
      csrf_test_name: CSRF
    }, res => {
      hideLoader();
      showToast(res.message, res.status);
    }, 'json');
  });

  let delId = null;
  $(document).on('click', '.btn-del-domain', function() {
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
    $.post(`${BASE}admin/domains/delete/${delId}`, {
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