<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="card border-0 shadow-sm mb-4">
  <div class="card-body">
    <form method="GET" class="row g-3 align-items-end">
      <div class="col-md-3">
        <label class="form-label small fw-semibold">Year</label>
        <select name="year" class="form-select form-select-sm">
          <?php for ($y = date('Y'); $y >= date('Y')-4; $y--): ?>
          <option value="<?= $y ?>" <?= $year==$y?'selected':'' ?>><?= $y ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-semibold">Status</label>
        <select name="status" class="form-select form-select-sm">
          <option value="">All</option>
          <?php foreach (['draft','sent','paid','partial','overdue','cancelled'] as $s): ?>
          <option value="<?= $s ?>" <?= ($status??'')==$s?'selected':'' ?>><?= ucfirst($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
      </div>
      <div class="col-md-4 text-end">
        <a href="<?= base_url('admin/reports/export/invoices/excel?year='.$year.'&status='.($status??'')) ?>" class="btn btn-outline-success btn-sm"><i class="bi bi-file-earmark-excel me-1"></i>Excel</a>
        <a href="<?= base_url('admin/reports/export/invoices/csv?year='.$year.'&status='.($status??'')) ?>" class="btn btn-outline-secondary btn-sm ms-1"><i class="bi bi-filetype-csv me-1"></i>CSV</a>
      </div>
    </form>
  </div>
</div>

<!-- Summary -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="fs-3 fw-bold text-primary">₹<?= number_format($total_billed, 0) ?></div>
      <div class="text-muted small">Total Billed</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="fs-3 fw-bold text-success">₹<?= number_format($total_collected, 0) ?></div>
      <div class="text-muted small">Collected</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="fs-3 fw-bold text-danger">₹<?= number_format($total_outstanding, 0) ?></div>
      <div class="text-muted small">Outstanding</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="fs-3 fw-bold text-warning"><?= $overdue_count ?></div>
      <div class="text-muted small">Overdue</div>
    </div>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr><th>Invoice #</th><th>Client</th><th>Project</th><th>Date</th><th>Due</th><th>Total</th><th>Paid</th><th>Balance</th><th>Status</th></tr>
      </thead>
      <tbody>
        <?php if (empty($invoices)): ?>
        <tr><td colspan="9" class="text-center text-muted py-5">No invoices found.</td></tr>
        <?php else: ?>
        <?php
        $sc = ['draft'=>'secondary','sent'=>'info','paid'=>'success','partial'=>'warning','overdue'=>'danger','cancelled'=>'dark'];
        foreach ($invoices as $inv):
          $bal = $inv['balance_due'] ?? ($inv['total'] - $inv['paid_amount']);
          $isOverdue = strtotime($inv['due_date']) < time() && !in_array($inv['status'],['paid','cancelled']);
        ?>
        <tr>
          <td><a href="<?= base_url('admin/invoices/'.$inv['id']) ?>" class="fw-semibold text-decoration-none small"><?= esc($inv['invoice_number']) ?></a></td>
          <td class="small"><?= esc($inv['client_name'] ?? '—') ?></td>
          <td class="small text-muted"><?= esc($inv['project_name'] ?? '—') ?></td>
          <td class="small"><?= date('d M Y',strtotime($inv['invoice_date'])) ?></td>
          <td class="small <?= $isOverdue ? 'text-danger fw-semibold' : '' ?>"><?= date('d M Y',strtotime($inv['due_date'])) ?></td>
          <td class="fw-semibold small">₹<?= number_format($inv['total'],0) ?></td>
          <td class="small text-success">₹<?= number_format($inv['paid_amount'],0) ?></td>
          <td class="small <?= $bal > 0 ? 'text-danger fw-semibold' : 'text-success' ?>">₹<?= number_format($bal,0) ?></td>
          <td><span class="badge bg-<?= $sc[$inv['status']] ?? 'secondary' ?>"><?= ucfirst($inv['status']) ?></span></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
      <?php if (!empty($invoices)): ?>
      <tfoot class="table-light">
        <tr>
          <td colspan="5" class="text-end fw-bold small">Totals</td>
          <td class="fw-bold small">₹<?= number_format($total_billed,0) ?></td>
          <td class="fw-bold small text-success">₹<?= number_format($total_collected,0) ?></td>
          <td class="fw-bold small text-danger">₹<?= number_format($total_outstanding,0) ?></td>
          <td></td>
        </tr>
      </tfoot>
      <?php endif; ?>
    </table>
  </div>
</div>

<?= $this->endSection() ?>
