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
          <option value="<?= $m ?>" <?= ($month??'')==$m?'selected':'' ?>><?= date('F',mktime(0,0,0,$m,1)) ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small fw-semibold">Method</label>
        <select name="method" class="form-select form-select-sm">
          <option value="">All</option>
          <?php foreach (['cash','bank_transfer','upi','razorpay','cheque','neft','rtgs'] as $m): ?>
          <option value="<?= $m ?>" <?= ($method??'')==$m?'selected':'' ?>><?= ucwords(str_replace('_',' ',$m)) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-primary btn-sm w-100 mt-4">Filter</button>
      </div>
      <div class="col-md-12 text-end">
        <a href="<?= base_url('admin/reports/export/payments/excel?year='.$year.'&month='.($month??'').'&method='.($method??'')) ?>" class="btn btn-outline-success btn-sm"><i class="bi bi-file-earmark-excel me-1"></i>Excel</a>
        <a href="<?= base_url('admin/reports/export/payments/csv?year='.$year.'&month='.($month??'').'&method='.($method??'')) ?>" class="btn btn-outline-secondary btn-sm ms-1"><i class="bi bi-filetype-csv me-1"></i>CSV</a>
      </div>
    </form>
  </div>
</div>

<!-- Summary -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="fs-3 fw-bold text-success">₹<?= number_format($total_amount, 0) ?></div>
      <div class="text-muted small">Total Collected</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="fs-3 fw-bold text-primary"><?= count($payments) ?></div>
      <div class="text-muted small">Transactions</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="fs-3 fw-bold text-info">₹<?= count($payments) > 0 ? number_format($total_amount/count($payments),0) : 0 ?></div>
      <div class="text-muted small">Avg. Payment</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <?php $topMethod = $method_breakdown ? array_key_first($method_breakdown) : '—'; ?>
      <div class="fs-5 fw-bold text-warning mt-1"><?= ucwords(str_replace('_',' ',$topMethod)) ?></div>
      <div class="text-muted small">Top Method</div>
    </div>
  </div>
</div>

<!-- Method breakdown -->
<?php if (!empty($method_breakdown)): ?>
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold">By Payment Method</h6></div>
  <div class="card-body">
    <div class="row g-3">
      <?php foreach ($method_breakdown as $mth => $amt): ?>
      <div class="col-md-3 col-6">
        <div class="border rounded p-3 text-center">
          <div class="fw-bold text-success">₹<?= number_format($amt,0) ?></div>
          <div class="text-muted small"><?= ucwords(str_replace('_',' ',$mth)) ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr><th>Payment #</th><th>Client</th><th>Invoice</th><th>Amount</th><th>Method</th><th>Ref / UTR</th><th>Date</th></tr>
      </thead>
      <tbody>
        <?php if (empty($payments)): ?>
        <tr><td colspan="7" class="text-center text-muted py-5">No payments found.</td></tr>
        <?php else: ?>
        <?php foreach ($payments as $p): ?>
        <tr>
          <td class="fw-semibold small"><?= esc($p['payment_number']) ?></td>
          <td class="small"><?= esc($p['client_name'] ?? '—') ?></td>
          <td class="small text-muted"><?= esc($p['invoice_number'] ?? '—') ?></td>
          <td class="fw-bold text-success">₹<?= number_format($p['amount'],2) ?></td>
          <td><span class="badge bg-light text-dark border small"><?= ucwords(str_replace('_',' ',$p['method']??'')) ?></span></td>
          <td class="font-monospace small text-muted"><?= esc($p['transaction_id'] ?? '—') ?></td>
          <td class="small"><?= $p['payment_date'] ? date('d M Y',strtotime($p['payment_date'])) : date('d M Y',strtotime($p['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
      <?php if (!empty($payments)): ?>
      <tfoot class="table-light">
        <tr><td colspan="3" class="text-end fw-bold">Total</td><td class="fw-bold text-success">₹<?= number_format($total_amount,2) ?></td><td colspan="3"></td></tr>
      </tfoot>
      <?php endif; ?>
    </table>
  </div>
</div>

<?= $this->endSection() ?>
