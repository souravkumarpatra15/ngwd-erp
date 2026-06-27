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
        <label class="form-label small fw-semibold">Month</label>
        <select name="month" class="form-select form-select-sm">
          <option value="">All Months</option>
          <?php for ($m=1;$m<=12;$m++): ?>
          <option value="<?= $m ?>" <?= $month==$m?'selected':'' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
      </div>
      <div class="col-md-4 text-end">
        <a href="<?= base_url('admin/reports/export/revenue/excel?year='.$year.'&month='.$month) ?>" class="btn btn-outline-success btn-sm"><i class="bi bi-file-earmark-excel me-1"></i>Excel</a>
        <a href="<?= base_url('admin/reports/export/revenue/csv?year='.$year.'&month='.$month) ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-filetype-csv me-1"></i>CSV</a>
      </div>
    </form>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card border-0 shadow-sm text-center py-4">
      <div class="fs-3 fw-bold text-success">₹<?= number_format($total_revenue, 0) ?></div>
      <div class="text-muted small">Total Revenue</div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card border-0 shadow-sm text-center py-4">
      <div class="fs-3 fw-bold text-primary"><?= count($payments) ?></div>
      <div class="text-muted small">Total Payments</div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card border-0 shadow-sm text-center py-4">
      <div class="fs-3 fw-bold text-info">₹<?= count($payments) > 0 ? number_format($total_revenue / count($payments), 0) : 0 ?></div>
      <div class="text-muted small">Avg. Payment</div>
    </div>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr><th>Payment #</th><th>Client</th><th>Project</th><th>Amount</th><th>Method</th><th>Date</th></tr>
      </thead>
      <tbody>
        <?php if (empty($payments)): ?>
        <tr><td colspan="6" class="text-center text-muted py-5">No payments found for this period</td></tr>
        <?php else: ?>
        <?php foreach ($payments as $p): ?>
        <tr>
          <td><a href="<?= base_url('admin/payments/'.($p['id']??'')) ?>" class="fw-semibold text-decoration-none small"><?= esc($p['payment_number']) ?></a></td>
          <td class="small"><?= esc($p['client_name'] ?? '—') ?></td>
          <td class="small text-muted"><?= esc($p['project_name'] ?? '—') ?></td>
          <td class="fw-bold text-success">₹<?= number_format($p['amount'], 2) ?></td>
          <td><span class="badge bg-light text-dark border small"><?= ucwords(str_replace('_',' ',$p['method']??'')) ?></span></td>
          <td class="small text-muted"><?= $p['payment_date'] ? date('d M Y',strtotime($p['payment_date'])) : '—' ?></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
      <?php if (!empty($payments)): ?>
      <tfoot class="table-light">
        <tr><td colspan="3" class="text-end fw-bold">Total</td><td class="fw-bold text-success">₹<?= number_format($total_revenue, 2) ?></td><td colspan="2"></td></tr>
      </tfoot>
      <?php endif; ?>
    </table>
  </div>
</div>

<?= $this->endSection() ?>
