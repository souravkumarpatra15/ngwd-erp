<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
  <div class="col-xl-8">
    <?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger"><ul class="mb-0">
      <?php foreach (session()->getFlashdata('errors') as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
    </ul></div>
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
                <option value="<?= $c['id'] ?>" <?= old('client_id')==$c['id']?'selected':'' ?>>
                  <?= esc($c['name']) ?><?= $c['company_name'] ? ' — '.$c['company_name'] : '' ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label small fw-semibold">Amount (₹) <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text">₹</span>
                <input type="number" name="amount" class="form-control" step="0.01" min="0.01"
                       value="<?= old('amount') ?>" required placeholder="0.00" id="amountInput">
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label small fw-semibold">Payment Method <span class="text-danger">*</span></label>
              <select name="method" class="form-select" required>
                <option value="">Select Method</option>
                <?php
                // Exact enum values from DB: razorpay, upi, bank_transfer, cash, cheque
                $methods = ['razorpay'=>'Razorpay','upi'=>'UPI','bank_transfer'=>'Bank Transfer','cash'=>'Cash','cheque'=>'Cheque'];
                foreach ($methods as $val => $lbl): ?>
                <option value="<?= $val ?>" <?= old('method')==$val?'selected':'' ?>><?= $lbl ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label small fw-semibold">Payment Date <span class="text-danger">*</span></label>
              <input type="date" name="payment_date" class="form-control"
                     value="<?= old('payment_date', date('Y-m-d')) ?>" required>
            </div>

            <div class="col-md-6">
              <label class="form-label small fw-semibold">Transaction / Reference ID</label>
              <input type="text" name="transaction_id" class="form-control"
                     value="<?= old('transaction_id') ?>" placeholder="UTR, cheque no., UPI ref…">
            </div>

            <div class="col-md-6">
              <label class="form-label small fw-semibold">Link to Invoice <span class="text-muted small">(auto-fills amount)</span></label>
              <select name="invoice_id" id="invoiceSel" class="form-select select2">
                <option value="">None — general payment</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label small fw-semibold">Link to Project</label>
              <select name="project_id" id="projectSel" class="form-select select2">
                <option value="">None</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label small fw-semibold">Link to Milestone</label>
              <select name="milestone_id" id="milestoneSel" class="form-select">
                <option value="">None — select project first</option>
              </select>
            </div>

            <div class="col-12">
              <label class="form-label small fw-semibold">Notes</label>
              <textarea name="notes" class="form-control" rows="2"
                        placeholder="Payment notes…"><?= old('notes') ?></textarea>
            </div>

          </div>
          <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-lg me-2"></i>Record Payment
            </button>
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
if (typeof $.fn.select2 !== 'undefined') $('.select2').select2({ theme:'bootstrap-5', width:'100%' });

$('#clientSel').on('change', function() {
  const cid = $(this).val();
  if (!cid) {
    $('#invoiceSel').html('<option value="">None — general payment</option>');
    $('#projectSel').html('<option value="">None</option>');
    $('#milestoneSel').html('<option value="">None — select project first</option>');
    return;
  }

  // Load outstanding invoices for this client (route exists in InvoiceController::byClient)
  $.getJSON(`${BASE}admin/invoices/by-client/${cid}`, res => {
    let opts = '<option value="">None — general payment</option>';
    (res.data || []).forEach(inv => {
      const bal = parseFloat(inv.balance_due || 0).toLocaleString('en-IN', {minimumFractionDigits:2});
      opts += `<option value="${inv.id}" data-amount="${inv.balance_due}">${inv.invoice_number} — ₹${bal} due</option>`;
    });
    $('#invoiceSel').html(opts).trigger('change.select2');
  });

  // Load projects for this client (uses existing DashboardController::ajaxProjects)
  $.getJSON(`${BASE}admin/ajax/projects/${cid}`, data => {
    let opts = '<option value="">None</option>';
    data.forEach(p => opts += `<option value="${p.id}">${p.name}</option>`);
    $('#projectSel').html(opts).trigger('change.select2');
    $('#milestoneSel').html('<option value="">None — select project first</option>');
  });
});

// Auto-fill amount when invoice selected
$('#invoiceSel').on('change', function() {
  const amt = $(this).find(':selected').data('amount');
  if (amt) $('#amountInput').val(parseFloat(amt).toFixed(2));
});

// Load milestones when project selected
$('#projectSel').on('change', function() {
  const pid = $(this).val();
  if (!pid) { $('#milestoneSel').html('<option value="">None — select project first</option>'); return; }
  $.getJSON(`${BASE}admin/milestones/by-project/${pid}`, res => {
    let opts = '<option value="">None</option>';
    (res.data || []).forEach(ms => {
      opts += `<option value="${ms.id}">${ms.title} — ₹${parseFloat(ms.amount).toLocaleString('en-IN')} (${ms.status})</option>`;
    });
    $('#milestoneSel').html(opts);
  });
});
</script>
<?= $this->endSection() ?>
