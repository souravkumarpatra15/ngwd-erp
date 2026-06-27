<?= $this->extend('layouts/client') ?>
<?= $this->section('content') ?>

<?php if (empty($payments)): ?>
<div class="text-center text-muted py-5">
  <i class="bi bi-cash-stack fs-2 d-block mb-2 opacity-25"></i>No payments yet.
</div>
<?php else: ?>
<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr><th>Payment #</th><th>Project</th><th>Invoice</th><th>Amount</th><th>Method</th><th>Date</th></tr>
      </thead>
      <tbody>
        <?php foreach ($payments as $p): ?>
        <tr>
          <td class="fw-semibold small"><?= esc($p['payment_number']) ?></td>
          <td class="small text-muted"><?= esc($p['project_name'] ?? '—') ?></td>
          <td class="small text-muted"><?= esc($p['invoice_number'] ?? '—') ?></td>
          <td class="fw-bold text-success">₹<?= number_format($p['amount'],2) ?></td>
          <td><span class="badge bg-light text-dark border small"><?= ucwords(str_replace('_',' ',$p['method']??'')) ?></span></td>
          <td class="small"><?= date('d M Y',strtotime($p['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot class="table-light">
        <tr>
          <td colspan="3" class="text-end fw-bold">Total Paid</td>
          <td class="fw-bold text-success">₹<?= number_format(array_sum(array_column($payments,'amount')),2) ?></td>
          <td colspan="2"></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
