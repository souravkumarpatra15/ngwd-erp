<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="row g-4 justify-content-center">
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <div>
          <span class="badge bg-success me-2">Completed</span>
          <span class="text-muted small"><?= esc($payment['payment_number']) ?></span>
        </div>
        <a href="<?= base_url('admin/payments/receipt/'.$payment['id']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-receipt me-1"></i>Receipt</a>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <div class="mb-3">
              <div class="text-muted small">Client</div>
              <div class="fw-semibold"><?= esc($payment['client_name'] ?? '—') ?></div>
            </div>
            <div class="mb-3">
              <div class="text-muted small">Project</div>
              <div><?= $payment['project_name'] ? esc($payment['project_name']) : '—' ?></div>
            </div>
            <div class="mb-3">
              <div class="text-muted small">Invoice</div>
              <div><?= $payment['invoice_number'] ? '<a href="'.base_url('admin/invoices/').($payment['invoice_id']).'">'.esc($payment['invoice_number']).'</a>' : '—' ?></div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <div class="text-muted small">Amount</div>
              <div class="fs-4 fw-bold text-success">₹<?= number_format($payment['amount'], 2) ?></div>
            </div>
            <div class="mb-3">
              <div class="text-muted small">Method</div>
              <div><?= ucwords(str_replace('_',' ',$payment['method'] ?? '—')) ?></div>
            </div>
            <div class="mb-3">
              <div class="text-muted small">Transaction ID</div>
              <div class="font-monospace small"><?= esc($payment['transaction_id'] ?? '—') ?></div>
            </div>
          </div>
        </div>
        <?php if ($payment['notes']): ?>
        <hr>
        <div class="text-muted small">Notes</div>
        <div><?= nl2br(esc($payment['notes'])) ?></div>
        <?php endif; ?>
      </div>
      <div class="card-footer bg-white text-muted small">
        Payment Date: <?= $payment['payment_date'] ? date('d M Y',strtotime($payment['payment_date'])) : '—' ?>
        · Recorded on: <?= date('d M Y H:i',strtotime($payment['created_at'])) ?>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
