<?= $this->extend('layouts/client') ?>
<?= $this->section('content') ?>
<?php $sc = ['draft'=>'secondary','sent'=>'info','partial'=>'warning','paid'=>'success','overdue'=>'danger','cancelled'=>'dark'][$invoice['status']] ?? 'secondary'; ?>

<div class="row g-4 justify-content-center">
  <div class="col-lg-9">
    <div class="card border-0 shadow-sm">
      <div class="card-body p-4 p-md-5">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-start mb-4">
          <div>
            <h4 class="fw-bold mb-1">Invoice</h4>
            <div class="text-muted small"><?= esc($invoice['invoice_number']) ?></div>
            <div class="mt-2">
              <span class="badge bg-light text-dark border"><i class="bi bi-tag me-1"></i><?= esc(\App\Models\InvoiceModel::forLabel($invoice)) ?></span>
            </div>
          </div>
          <span class="badge bg-<?= $sc ?> fs-6"><?= ucfirst($invoice['status']) ?></span>
        </div>

        <!-- Bill to / From -->
        <div class="row mb-4">
          <div class="col-6">
            <div class="text-muted small mb-1">Bill To</div>
            <div class="fw-bold"><?= esc($invoice['client_name'] ?? '—') ?></div>
            <?php if ($invoice['client_address'] ?? null): ?>
            <div class="small text-muted"><?= nl2br(esc($invoice['client_address'])) ?></div>
            <?php endif; ?>
            <?php if ($invoice['client_gst'] ?? null): ?>
            <div class="small text-muted">GST: <?= esc($invoice['client_gst']) ?></div>
            <?php endif; ?>
          </div>
          <div class="col-6 text-end">
            <div class="text-muted small mb-1">Invoice Date</div>
            <div class="small"><?= date('d M Y',strtotime($invoice['invoice_date'])) ?></div>
            <div class="text-muted small mt-2">Due Date</div>
            <div class="small fw-semibold <?= strtotime($invoice['due_date']) < time() && $invoice['status'] !== 'paid' ? 'text-danger' : '' ?>">
              <?= date('d M Y',strtotime($invoice['due_date'])) ?>
            </div>
            <?php if ($invoice['project_name'] ?? null): ?>
            <div class="text-muted small mt-2">Project</div>
            <div class="small"><?= esc($invoice['project_name']) ?></div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Line items -->
        <table class="table table-sm mb-3">
          <thead class="table-light">
            <tr><th>Description</th><th class="text-end">Qty</th><th class="text-end">Rate</th><th class="text-end">Amount</th></tr>
          </thead>
          <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
              <td><?= esc($item['description']) ?></td>
              <td class="text-end small"><?= $item['quantity'] ?></td>
              <td class="text-end small">₹<?= number_format($item['unit_price'],2) ?></td>
              <td class="text-end fw-semibold">₹<?= number_format($item['total'],2) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr><td colspan="3" class="text-end text-muted small">Subtotal</td><td class="text-end">₹<?= number_format($invoice['subtotal']??$invoice['total'],2) ?></td></tr>
            <?php if (($invoice['tax_amount']??0) > 0): ?>
            <tr><td colspan="3" class="text-end text-muted small">GST (<?= $invoice['tax_percent']??0 ?>%)</td><td class="text-end">₹<?= number_format($invoice['tax_amount'],2) ?></td></tr>
            <?php endif; ?>
            <?php if (($invoice['discount']??0) > 0): ?>
            <tr><td colspan="3" class="text-end text-muted small">Discount</td><td class="text-end text-danger">-₹<?= number_format($invoice['discount'],2) ?></td></tr>
            <?php endif; ?>
            <tr class="table-light"><td colspan="3" class="text-end fw-bold">Total</td><td class="text-end fw-bold fs-5">₹<?= number_format($invoice['total'],2) ?></td></tr>
            <?php if (($invoice['paid_amount']??0) > 0): ?>
            <tr><td colspan="3" class="text-end text-muted small">Paid</td><td class="text-end text-success">-₹<?= number_format($invoice['paid_amount'],2) ?></td></tr>
            <tr class="table-warning"><td colspan="3" class="text-end fw-bold">Balance Due</td><td class="text-end fw-bold text-danger">₹<?= number_format(($invoice['total']-($invoice['paid_amount']??0)),2) ?></td></tr>
            <?php endif; ?>
          </tfoot>
        </table>

        <?php if ($invoice['notes'] ?? null): ?>
        <div class="border-top pt-3">
          <div class="text-muted small mb-1">Notes</div>
          <div class="small"><?= nl2br(esc($invoice['notes'])) ?></div>
        </div>
        <?php endif; ?>
        <?php if ($invoice['terms'] ?? null): ?>
        <div class="border-top pt-3 mt-3">
          <div class="text-muted small mb-1">Terms &amp; Conditions</div>
          <div class="small text-muted"><?= nl2br(esc($invoice['terms'])) ?></div>
        </div>
        <?php endif; ?>
      </div>
      <?php if (!in_array($invoice['status'],['paid','cancelled'])): ?>
      <div class="card-footer bg-white border-0 px-4 pb-4 text-center">
        <a href="<?= base_url('portal/pay/'.$invoice['id']) ?>" class="btn btn-success btn-lg">
          <i class="bi bi-credit-card me-2"></i>Pay ₹<?= number_format($invoice['total']-($invoice['paid_amount']??0),2) ?> Now
        </a>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
