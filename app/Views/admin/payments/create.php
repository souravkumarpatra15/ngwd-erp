<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
  <div class="col-xl-8">
    <?php if (session()->getFlashdata('errors')): ?><div class="alert alert-danger">
        <ul class="mb-0"><?php foreach (session()->getFlashdata('errors') as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?></ul>
      </div><?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?><div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 py-3">
        <h6 class="mb-0 fw-semibold">Record Payment</h6>
      </div>
      <div class="card-body">
        <form action="<?= base_url('admin/payments/store') ?>" method="POST"><?= csrf_field() ?><div class="row g-3">
            <div class="col-md-6"><label class="form-label small fw-semibold">Client <span class="text-danger">*</span></label><select name="client_id" id="clientSel" class="form-select select2" required>
                <option value="">Select Client</option><?php foreach ($clients as $c): ?><option value="<?= $c['id'] ?>" <?= old('client_id') == $c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?><?= $c['company_name'] ? ' — ' . esc($c['company_name']) : '' ?></option><?php endforeach; ?>
              </select></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Link to Invoice</label><select name="invoice_id" id="invoiceSel" class="form-select select2">
                <option value="">None — general payment</option>
              </select>
              <div class="form-text">Invoice currency and outstanding balance are applied automatically.</div>
            </div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Currency <span class="text-danger">*</span></label><select name="currency" id="currencySel" class="form-select" required><?php $currencies = ['INR' => '₹ — Indian Rupee', 'USD' => '$ — US Dollar', 'EUR' => '€ — Euro', 'GBP' => '£ — British Pound', 'AED' => 'د.إ — UAE Dirham', 'CAD' => 'C$ — Canadian Dollar', 'AUD' => 'A$ — Australian Dollar', 'SGD' => 'S$ — Singapore Dollar'];
                                                                                                                                                                                                        $oldCurrency = strtoupper((string)old('currency', 'INR'));
                                                                                                                                                                                                        foreach ($currencies as $code => $label): ?><option value="<?= $code ?>" <?= $oldCurrency === $code ? 'selected' : '' ?>><?= esc($label) ?></option><?php endforeach; ?></select>
              <div class="form-text" id="currencyHelp">Select the currency for a general payment.</div>
            </div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Amount <span class="text-danger">*</span></label>
              <div class="input-group"><span class="input-group-text" id="paySymbol">₹</span><input type="number" name="amount" class="form-control" step="0.01" min="0.01" value="<?= old('amount') ?>" required placeholder="0.00" id="amountInput"></div>
            </div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Payment Method <span class="text-danger">*</span></label><select name="method" class="form-select" required>
                <option value="">Select Method</option><?php $methods = ['razorpay' => 'Razorpay', 'upi' => 'UPI', 'bank_transfer' => 'Bank Transfer', 'cash' => 'Cash', 'cheque' => 'Cheque'];
                                                        foreach ($methods as $val => $lbl): ?><option value="<?= $val ?>" <?= old('method') == $val ? 'selected' : '' ?>><?= $lbl ?></option><?php endforeach; ?>
              </select></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Payment Date <span class="text-danger">*</span></label><input type="date" name="payment_date" class="form-control" value="<?= old('payment_date', date('Y-m-d')) ?>" required></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Transaction / Reference ID</label><input type="text" name="transaction_id" class="form-control" value="<?= old('transaction_id') ?>" placeholder="UTR, cheque no., UPI ref…"></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Link to Project</label><select name="project_id" id="projectSel" class="form-select select2">
                <option value="">None</option>
              </select></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Link to Milestone</label><select name="milestone_id" id="milestoneSel" class="form-select">
                <option value="">None — select project first</option>
              </select></div>
            <div class="col-12"><label class="form-label small fw-semibold">Notes</label><textarea name="notes" class="form-control" rows="2" placeholder="Payment notes…"><?= esc(old('notes')) ?></textarea></div>
          </div>
          <div class="d-flex gap-2 mt-4"><button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-2"></i>Record Payment</button><a href="<?= base_url('admin/payments') ?>" class="btn btn-outline-secondary">Cancel</a></div>
        </form>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?><script>
  const BASE = '<?= base_url() ?>';
  const CSRF = CSRF_TOKEN;
  const symbols = {
    INR: '₹',
    USD: '$',
    EUR: '€',
    GBP: '£',
    AED: 'د.إ',
    CAD: 'C$',
    AUD: 'A$',
    SGD: 'S$'
  };

  function setCurrency(code, locked) {
    code = String(code || 'INR').toUpperCase();
    if (!symbols[code]) code = 'INR';
    $('#currencySel').val(code).prop('disabled', !!locked);
    $('#paySymbol').text(symbols[code]);
    $('#currencyHelp').text(locked ? 'Currency is locked to the selected invoice/milestone.' : 'Select the currency for a general payment.');
  }
  if (typeof $.fn.select2 !== 'undefined') $('.select2').select2({
    theme: 'bootstrap-5',
    width: '100%'
  });
  setCurrency($('#currencySel').val() || 'INR', false);
  $('#currencySel').on('change', function() {
    setCurrency($(this).val(), !!$('#invoiceSel').val() || !!$('#milestoneSel').val());
  });
  $('#clientSel').on('change', function() {
    const cid = $(this).val();
    $('#invoiceSel').html('<option value="">None — general payment</option>').trigger('change.select2');
    $('#projectSel').html('<option value="">None</option>').trigger('change.select2');
    $('#milestoneSel').html('<option value="">None — select project first</option>');
    setCurrency($('#currencySel').val() || 'INR', false);
    if (!cid) return;
    $.getJSON(`${BASE}admin/ajax/invoices/${cid}`, res => {
      let opts = '<option value="">None — general payment</option>';
      (res.data || []).forEach(inv => {
        const cur = String(inv.currency || 'INR').toUpperCase();
        const bal = parseFloat(inv.balance_due || 0).toLocaleString('en-IN', {
          minimumFractionDigits: 2
        });
        opts += `<option value="${inv.id}" data-amount="${inv.balance_due}" data-currency="${cur}">${inv.invoice_number} — ${symbols[cur]||cur+' '}${bal} due</option>`;
      });
      $('#invoiceSel').html(opts).trigger('change.select2');
    });
    $.getJSON(`${BASE}admin/ajax/projects/${cid}`, data => {
      let opts = '<option value="">None</option>';
      (data || []).forEach(p => opts += `<option value="${p.id}">${p.name}</option>`);
      $('#projectSel').html(opts).trigger('change.select2');
    });
  });
  $('#invoiceSel').on('change', function() {
    const opt = $(this).find(':selected'),
      id = $(this).val(),
      amt = opt.data('amount'),
      cur = String(opt.data('currency') || '').toUpperCase();
    if (id) {
      if (amt !== undefined && amt !== null && amt !== '') $('#amountInput').val(parseFloat(amt).toFixed(2));
      setCurrency(cur || 'INR', true);
    } else {
      setCurrency($('#currencySel').val() || 'INR', !!$('#milestoneSel').val());
    }
  });
  $('#projectSel').on('change', function() {
    const pid = $(this).val();
    if (!pid) {
      $('#milestoneSel').html('<option value="">None — select project first</option>');
      if (!$('#invoiceSel').val()) setCurrency($('#currencySel').val() || 'INR', false);
      return;
    }
    $.getJSON(`${BASE}admin/milestones/by-project/${pid}`, res => {
      let opts = '<option value="">None</option>';
      (res.data || []).forEach(ms => {
        const cur = String(ms.currency || 'INR').toUpperCase();
        opts += `<option value="${ms.id}" data-currency="${cur}">${ms.title} — ${symbols[cur]||cur+' '}${parseFloat(ms.amount).toLocaleString('en-IN')} (${ms.status})</option>`;
      });
      $('#milestoneSel').html(opts);
    });
  });
  $('#milestoneSel').on('change', function() {
    const cur = String($(this).find(':selected').data('currency') || '').toUpperCase();
    if (cur && !$('#invoiceSel').val()) setCurrency(cur, true);
    else if (!$('#invoiceSel').val()) setCurrency($('#currencySel').val() || 'INR', false);
  });
  $('form').on('submit', function() {
    $('#currencySel').prop('disabled', false);
  });
</script><?= $this->endSection() ?>