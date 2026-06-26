<?= $this->extend('layouts/client') ?>
<?= $this->section('content') ?>
<div class="row g-3 mb-4">
  <div class="col-md-3 col-6">
    <div class="card border-0 shadow-sm text-center py-4">
      <div class="fs-2 fw-bold text-primary"><?= $total_projects ?></div>
      <div class="text-muted small">Total Projects</div>
    </div>
  </div>
  <div class="col-md-3 col-6">
    <div class="card border-0 shadow-sm text-center py-4">
      <div class="fs-2 fw-bold text-warning"><?= count($pending_invoices) ?></div>
      <div class="text-muted small">Pending Invoices</div>
    </div>
  </div>
  <div class="col-md-3 col-6">
    <div class="card border-0 shadow-sm text-center py-4">
      <div class="fs-2 fw-bold text-success">₹<?= number_format($total_paid,0) ?></div>
      <div class="text-muted small">Total Paid</div>
    </div>
  </div>
  <div class="col-md-3 col-6">
    <div class="card border-0 shadow-sm text-center py-4">
      <div class="fs-2 fw-bold text-info"><?= count($recent_payments) ?></div>
      <div class="text-muted small">Recent Payments</div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-md-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 py-3 d-flex justify-content-between">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-folder2-open me-2 text-primary"></i>My Projects</h6>
        <a href="<?= base_url('portal/projects') ?>" class="text-primary small">View All</a>
      </div>
      <div class="list-group list-group-flush">
        <?php if (empty($projects)): ?><div class="list-group-item text-muted text-center py-3 small">No projects yet</div><?php endif; ?>
        <?php foreach (array_slice($projects,0,5) as $p): ?>
        <a href="<?= base_url('portal/projects/'.$p['id']) ?>" class="list-group-item list-group-item-action py-2 px-3">
          <div class="d-flex justify-content-between align-items-center">
            <div><div class="fw-semibold small"><?= esc($p['name']) ?></div><div class="text-muted" style="font-size:11px"><?= ucfirst(str_replace('_',' ',$p['type'])) ?></div></div>
            <span class="badge bg-<?= projectStatusColor($p['status']) ?>"><?= ucfirst(str_replace('_',' ',$p['status'])) ?></span>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 py-3 d-flex justify-content-between">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-receipt me-2 text-warning"></i>Pending Invoices</h6>
        <a href="<?= base_url('portal/invoices') ?>" class="text-primary small">View All</a>
      </div>
      <div class="list-group list-group-flush">
        <?php if (empty($pending_invoices)): ?><div class="list-group-item text-muted text-center py-3 small">No pending invoices</div><?php endif; ?>
        <?php foreach (array_slice($pending_invoices,0,5) as $inv): ?>
        <div class="list-group-item py-2 px-3">
          <div class="d-flex justify-content-between align-items-center">
            <div><div class="fw-semibold small"><?= esc($inv['invoice_number']) ?></div><div class="text-muted" style="font-size:11px">Due: <?= date('d M Y',strtotime($inv['due_date'])) ?></div></div>
            <div class="text-end">
              <div class="fw-bold text-danger small">₹<?= number_format($inv['balance_due'],0) ?></div>
              <a href="<?= base_url('portal/pay/'.$inv['id']) ?>" class="btn btn-xs btn-success mt-1"><i class="bi bi-credit-card me-1"></i>Pay</a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
