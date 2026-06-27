<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<?php
$methods = [
    'razorpay',
    'upi',
    'bank_transfer',
    'cash',
    'cheque',
    'neft',
    'rtgs',
    'other'
];
?>

<div class="row justify-content-center">
  <div class="col-xl-8">
    <?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach (session()->getFlashdata('errors') as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold">Record Payment</h6></div>
      <div class="card-body">
        <form action="<?= base_url('admin/payments/store') ?>" method="POST">
          <?= csrf_field() ?>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Client <span class="text-danger">*</span></label>
              <select name="client_id" id="clientSel" class="form-select select2" required>
                <option value="">Select Client</option>
                <?php foreach ($clients as $c): ?>
                <option value="<?= $c['id'] ?>" <?= old('client_id')==$c['id']?'selected':'' ?>><?= esc($c['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Amount <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text">₹</span>
                <input type="number" name="amount" class="form-control" step="0.01" min="0.01" value="<?= old('amount') ?>" required placeholder="0.00">
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Payment Method <span class="text-danger">*</span></label>
              <select name="method" class="form-select" required>
                <option value="">Select Method</option>
                <?php foreach ($methods as $m): ?>
                <option value="<?= $m ?>" <?= old('method')==$m?'selected':'' ?>><?= ucwords(str_replace('_',' ',$m)) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Payment Date <span class="text-danger">*</span></label>
              <input type="date" name="payment_date" class="form-control" value="<?= old('payment_date', date('Y-m-d')) ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Transaction / Reference ID</label>
              <input type="text" name="transaction_id" class="form-control" value="<?= old('transaction_id') ?>" placeholder="UTR, cheque no., etc.">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Link to Invoice</label>
              <select name="invoice_id" id="invoiceSel" class="form-select select2">
                <option value="">None (general payment)</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Link to Project</label>
              <select name="project_id" id="projectSel" class="form-select select2">
                <option value="">None</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Milestone</label>
              <select name="milestone_id" id="milestoneSel" class="form-select">
                <option value="">None</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label small fw-semibold">Notes</label>
              <textarea name="notes" class="form-control" rows="2" placeholder="Payment notes..."><?= old('notes') ?></textarea>
            </div>
          </div>
          <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-2"></i>Record Payment</button>
            <a href="<?= base_url('admin/payments') ?>" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const BASE = '<?= base_url() ?>'; const CSRF = CSRF_TOKEN;
if (typeof $.fn.select2 !== 'undefined') $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });

$('#clientSel').on('change', function() {
  const cid = $(this).val();
  if (!cid) return;
  // Load invoices for this client
  $.getJSON(`${BASE}admin/invoices/by-client/${cid}`, res => {
    $('#invoiceSel').empty().append('<option value="">None (general payment)</option>');
    (res.data||[]).forEach(inv => $('#invoiceSel').append(`<option value="${inv.id}">${inv.invoice_number} — ₹${parseFloat(inv.balance_due||0).toLocaleString('en-IN')} due</option>`));
  });
  // Load projects for this client
  $.getJSON(`${BASE}admin/projects/by-client/${cid}`, res => {
    $('#projectSel').empty().append('<option value="">None</option>');
    (res.data||[]).forEach(p => $('#projectSel').append(`<option value="${p.id}">${p.name}</option>`));
  });
});
</script>
<?= $this->endSection() ?>
