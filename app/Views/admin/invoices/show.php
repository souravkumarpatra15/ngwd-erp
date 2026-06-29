<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<?php
$statusColors = ['draft'=>'secondary','sent'=>'info','paid'=>'success','partial'=>'warning','overdue'=>'danger','cancelled'=>'dark'];
$sc = $statusColors[$invoice['status']] ?? 'secondary';
$balanceDue = $invoice['balance_due'] ?? ($invoice['total'] - $invoice['paid_amount']);
?>

<!-- Top action bar -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <div class="d-flex align-items-center gap-2">
    <span class="badge bg-<?= $sc ?> fs-6"><?= ucfirst($invoice['status']) ?></span>
    <span class="text-muted small"><?= esc($invoice['invoice_number']) ?></span>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <a href="<?= base_url('admin/invoices/edit/'.$invoice['id']) ?>" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil me-1"></i>Edit</a>
    <a href="<?= base_url('admin/invoices/pdf/'.$invoice['id']) ?>" class="btn btn-sm btn-outline-secondary" target="_blank"><i class="bi bi-file-pdf me-1"></i>PDF</a>
    <button class="btn btn-sm btn-outline-primary btn-email" data-id="<?= $invoice['id'] ?>"><i class="bi bi-envelope me-1"></i>Email</button>
    <button class="btn btn-sm btn-outline-success btn-wa" data-id="<?= $invoice['id'] ?>" style="color:#25D366;border-color:#25D366"><i class="bi bi-whatsapp me-1"></i>WhatsApp</button>
    <?php if (!in_array($invoice['status'], ['paid','cancelled'])): ?>
    <button class="btn btn-sm btn-success btn-pay-link" data-id="<?= $invoice['id'] ?>"><i class="bi bi-credit-card me-1"></i>Payment Link</button>
    <button class="btn btn-sm btn-outline-danger btn-void" data-id="<?= $invoice['id'] ?>"><i class="bi bi-x-circle me-1"></i>Void</button>
    <?php endif; ?>
  </div>
</div>

<div class="row g-4">
  <!-- Invoice body -->
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm">
      <div class="card-body p-4 p-md-5">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-start mb-5">
          <div>
            <h4 class="fw-bold mb-1">TAX INVOICE</h4>
            <div class="text-muted small"><?= esc($invoice['invoice_number']) ?></div>
            <?php if ($invoice['is_gst']): ?><span class="badge bg-success badge-sm mt-1">GST Invoice</span><?php endif; ?>
            <div class="mt-2">
              <span class="badge bg-light text-dark border"><i class="bi bi-tag me-1"></i><?= esc(\App\Models\InvoiceModel::forLabel($invoice)) ?></span>
            </div>
          </div>
          <div class="text-end">
            <div class="fw-bold"><?= esc($settings['company_name'] ?? '') ?></div>
            <div class="text-muted small"><?= nl2br(esc($settings['company_address'] ?? '')) ?></div>
            <?php if ($settings['company_gst'] ?? null): ?><div class="small">GSTIN: <?= esc($settings['company_gst']) ?></div><?php endif; ?>
          </div>
        </div>

        <!-- Bill To / Dates -->
        <div class="row mb-4">
          <div class="col-6">
            <div class="text-muted small fw-semibold mb-1 text-uppercase" style="letter-spacing:.05em">Bill To</div>
            <div class="fw-bold"><?= esc($invoice['client_name'] ?? '—') ?></div>
            <?php if ($invoice['client_address'] ?? null): ?><div class="small text-muted"><?= nl2br(esc($invoice['client_address'])) ?></div><?php endif; ?>
            <?php if ($invoice['client_gst'] ?? null): ?><div class="small text-muted">GSTIN: <?= esc($invoice['client_gst']) ?></div><?php endif; ?>
            <?php if ($invoice['client_email'] ?? null): ?><div class="small text-muted"><?= esc($invoice['client_email']) ?></div><?php endif; ?>
          </div>
          <div class="col-6 text-end">
            <table class="table table-sm table-borderless ms-auto mb-0" style="max-width:220px">
              <tr><td class="text-muted small pe-3">Invoice Date</td><td class="fw-semibold small"><?= date('d M Y',strtotime($invoice['invoice_date'])) ?></td></tr>
              <tr><td class="text-muted small pe-3">Due Date</td>
                <td class="fw-semibold small <?= strtotime($invoice['due_date'])<time() && $invoice['status']!=='paid' ? 'text-danger' : '' ?>">
                  <?= date('d M Y',strtotime($invoice['due_date'])) ?>
                </td>
              </tr>
              <?php if ($invoice['project_name'] ?? null): ?>
              <tr><td class="text-muted small pe-3">Project</td><td class="small"><?= esc($invoice['project_name']) ?></td></tr>
              <?php endif; ?>
            </table>
          </div>
        </div>

        <!-- Line Items -->
        <table class="table table-sm mb-0">
          <thead class="table-light">
            <tr>
              <th class="py-2">#</th>
              <th>Description</th>
              <th class="text-end">Qty</th>
              <th class="text-end">Rate</th>
              <th class="text-end">Amount</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $i => $item): ?>
            <tr>
              <td class="text-muted small"><?= $i+1 ?></td>
              <td><?= esc($item['description']) ?></td>
              <td class="text-end small"><?= $item['quantity'] ?></td>
              <td class="text-end small">₹<?= number_format($item['unit_price'],2) ?></td>
              <td class="text-end fw-semibold">₹<?= number_format($item['total'],2) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr><td colspan="4" class="text-end text-muted small border-0 pt-2">Subtotal</td><td class="text-end border-0 pt-2">₹<?= number_format($invoice['subtotal'],2) ?></td></tr>
            <?php if (($invoice['tax_amount'] ?? 0) > 0): ?>
            <tr><td colspan="4" class="text-end text-muted small border-0">GST (<?= $invoice['tax_percent'] ?>%)</td><td class="text-end border-0">₹<?= number_format($invoice['tax_amount'],2) ?></td></tr>
            <?php endif; ?>
            <?php if (($invoice['discount'] ?? 0) > 0): ?>
            <tr><td colspan="4" class="text-end text-muted small border-0">Discount</td><td class="text-end border-0 text-danger">-₹<?= number_format($invoice['discount'],2) ?></td></tr>
            <?php endif; ?>
            <tr class="table-light"><td colspan="4" class="text-end fw-bold border-0">Total</td><td class="text-end fw-bold fs-5 border-0">₹<?= number_format($invoice['total'],2) ?></td></tr>
            <?php if (($invoice['paid_amount'] ?? 0) > 0): ?>
            <tr><td colspan="4" class="text-end text-muted small border-0">Amount Paid</td><td class="text-end text-success border-0">-₹<?= number_format($invoice['paid_amount'],2) ?></td></tr>
            <tr class="table-warning"><td colspan="4" class="text-end fw-bold border-0">Balance Due</td><td class="text-end fw-bold text-danger border-0">₹<?= number_format($balanceDue,2) ?></td></tr>
            <?php endif; ?>
          </tfoot>
        </table>

        <?php if ($invoice['notes'] ?? null): ?>
        <div class="mt-4 border-top pt-3">
          <div class="text-muted small fw-semibold mb-1">Notes</div>
          <div class="small"><?= nl2br(esc($invoice['notes'])) ?></div>
        </div>
        <?php endif; ?>

        <?php if ($invoice['terms'] ?? null): ?>
        <div class="mt-3 border-top pt-3">
          <div class="text-muted small fw-semibold mb-1">Terms &amp; Conditions</div>
          <div class="small text-muted"><?= nl2br(esc($invoice['terms'])) ?></div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Sidebar -->
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold">Summary</h6></div>
      <div class="card-body">
        <table class="table table-sm table-borderless mb-0">
          <tr><td class="text-muted small">Total</td><td class="fw-bold text-primary">₹<?= number_format($invoice['total'],2) ?></td></tr>
          <tr><td class="text-muted small">Paid</td><td class="text-success fw-semibold">₹<?= number_format($invoice['paid_amount'],2) ?></td></tr>
          <tr><td class="text-muted small">Balance</td><td class="text-danger fw-bold">₹<?= number_format($balanceDue,2) ?></td></tr>
          <tr><td colspan="2"><hr class="my-1"></td></tr>
          <tr><td class="text-muted small">Status</td><td><span class="badge bg-<?= $sc ?>"><?= ucfirst($invoice['status']) ?></span></td></tr>
          <tr><td class="text-muted small">Client</td><td class="small"><?= esc($invoice['client_name'] ?? '—') ?></td></tr>
          <tr><td class="text-muted small">Project</td><td class="small"><?= esc($invoice['project_name'] ?? '—') ?></td></tr>
        </table>
      </div>
    </div>

    <!-- Razorpay payment link modal trigger -->
    <div id="payLinkResult" class="card border-0 shadow-sm d-none mb-3">
      <div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold">Payment Link Generated</h6></div>
      <div class="card-body">
        <div class="small text-muted mb-2">Share this link with the client:</div>
        <div class="input-group">
          <input type="text" class="form-control form-control-sm" id="payLinkUrl" readonly>
          <button class="btn btn-outline-secondary btn-sm" onclick="navigator.clipboard.writeText(document.getElementById('payLinkUrl').value).then(()=>showToast('Copied!','success'))">Copy</button>
        </div>
      </div>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold">Quick Actions</h6></div>
      <div class="card-body d-grid gap-2">
        <a href="<?= base_url('admin/invoices/edit/'.$invoice['id']) ?>" class="btn btn-outline-warning btn-sm"><i class="bi bi-pencil me-2"></i>Edit Invoice</a>
        <a href="<?= base_url('admin/invoices/pdf/'.$invoice['id']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="bi bi-file-pdf me-2"></i>Download PDF</a>
        <a href="<?= base_url('admin/invoices') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-2"></i>Back to Invoices</a>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const BASE = '<?= base_url() ?>'; const CSRF = CSRF_TOKEN;

$('.btn-email').on('click', function() {
  showLoader('Sending email...');
  $.post(`${BASE}admin/invoices/send-email/${$(this).data('id')}`, {csrf_test_name:CSRF}, res => {
    hideLoader(); showToast(res.message, res.status);
  }, 'json');
});
$('.btn-wa').on('click', function() {
  showLoader('Sending WhatsApp...');
  $.post(`${BASE}admin/invoices/send-whatsapp/${$(this).data('id')}`, {csrf_test_name:CSRF}, res => {
    hideLoader(); showToast(res.message, res.status);
  }, 'json');
});
$('.btn-pay-link').on('click', function() {
  showLoader('Creating Razorpay order...');
  $.post(`${BASE}admin/invoices/payment-link/${$(this).data('id')}`, {csrf_test_name:CSRF}, res => {
    hideLoader();
    if (res.status === 'success' && res.data) {
      const url = `${BASE}portal/pay/<?= $invoice['id'] ?>`;
      document.getElementById('payLinkUrl').value = url;
      document.getElementById('payLinkResult').classList.remove('d-none');
      showToast('Payment link ready!', 'success');
    } else {
      showToast(res.message, res.status);
    }
  }, 'json');
});
$('.btn-void').on('click', function() {
  $('#ngConfirmTitle').text('Void Invoice?');
  $('#ngConfirmMessage').text('This will cancel the invoice. This cannot be undone.');
  $('#ngConfirmYes').text('Yes, Void Invoice');
  bootstrap.Modal.getOrCreateInstance(document.getElementById('ngConfirmModal')).show();
  $('#ngConfirmYes').off('click').on('click', function() {
    bootstrap.Modal.getInstance(document.getElementById('ngConfirmModal')).hide();
    showLoader('Voiding...');
    $.post(`${BASE}admin/invoices/void/<?= $invoice['id'] ?>`, {csrf_test_name:CSRF}, res => {
      hideLoader(); showToast(res.message, res.status);
      if (res.status === 'success') setTimeout(() => location.reload(), 700);
    }, 'json');
  });
});
</script>
<?= $this->endSection() ?>
