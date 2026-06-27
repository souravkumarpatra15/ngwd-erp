<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<form action="<?= base_url('admin/invoices/store') ?>" method="POST" id="invoiceForm">
  <?= csrf_field() ?>
  <div class="row g-4">
    <div class="col-md-8">
      <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white border-0 py-3">
          <h6 class="mb-0 fw-semibold">Invoice Details</h6>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Client <span class="text-danger">*</span></label>
              <select name="client_id" class="form-select select2" id="clientSelect" required>
                <option value="">Select Client</option>
                <?php foreach ($clients as $c): ?>
                  <option value="<?= $c['id'] ?>"><?= esc($c['name']) ?> <?= $c['company_name'] ? '— ' . esc($c['company_name']) : '' ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Project</label>
              <select name="project_id" class="form-select" id="projectSelect">
                <option value="">Select Client first</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Invoice Date <span class="text-danger">*</span></label>
              <input type="date" name="invoice_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Due Date <span class="text-danger">*</span></label>
              <input type="date" name="due_date" class="form-control" value="<?= date('Y-m-d', strtotime('+15 days')) ?>" required>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold">GST Invoice?</label>
              <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" name="is_gst" id="isGst" value="1" checked>
                <label class="form-check-label" for="isGst">Include GST</label>
              </div>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold">Tax %</label>
              <input type="number" name="tax_percent" id="taxPercent" class="form-control" value="<?= $default_tax ?? 18 ?>" step="0.01">
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold">Discount (₹)</label>
              <input type="number" name="discount" id="discount" class="form-control" value="0" step="0.01">
            </div>
          </div>
        </div>
      </div>

      <!-- Line Items -->
      <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between">
          <h6 class="mb-0 fw-semibold">Line Items</h6>
          <button type="button" class="btn btn-sm btn-outline-primary" id="addItem"><i class="bi bi-plus me-1"></i>Add Item</button>
        </div>
        <div class="card-body p-0">
          <table class="table mb-0" id="itemsTable">
            <thead class="table-light">
              <tr>
                <th>Description</th>
                <th style="width:80px">Qty</th>
                <th style="width:120px">Rate (₹)</th>
                <th style="width:120px">Total</th>
                <th style="width:40px"></th>
              </tr>
            </thead>
            <tbody id="itemsBody">
              <tr class="item-row">
                <td><input type="text" name="items[0][description]" class="form-control form-control-sm" placeholder="Service description" required></td>
                <td><input type="number" name="items[0][quantity]" class="form-control form-control-sm item-qty" value="1" min="1" step="0.01" required></td>
                <td><input type="number" name="items[0][unit_price]" class="form-control form-control-sm item-price" value="0" step="0.01" required></td>
                <td><input type="text" class="form-control form-control-sm item-total bg-light" value="0.00" readonly></td>
                <td><button type="button" class="btn btn-xs btn-outline-danger btn-remove-item"><i class="bi bi-trash"></i></button></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Notes & Terms -->
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label small fw-semibold">Notes</label><textarea name="notes" class="form-control" rows="3" placeholder="Internal notes or message to client"></textarea></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Terms & Conditions</label><textarea name="terms" class="form-control" rows="3"><?= esc($default_terms ?? '') ?></textarea></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Summary -->
    <div class="col-md-4">
      <div class="card border-0 shadow-sm sticky-top" style="top:80px">
        <div class="card-header bg-white border-0 py-3">
          <h6 class="mb-0 fw-semibold">Invoice Summary</h6>
        </div>
        <div class="card-body">
          <div class="d-flex justify-content-between mb-2"><span class="text-muted small">Subtotal</span><span id="summSubtotal" class="fw-semibold">₹0.00</span></div>
          <div class="d-flex justify-content-between mb-2"><span class="text-muted small">Tax</span><span id="summTax" class="fw-semibold">₹0.00</span></div>
          <div class="d-flex justify-content-between mb-2"><span class="text-muted small">Discount</span><span id="summDiscount" class="text-danger fw-semibold">-₹0.00</span></div>
          <hr>
          <div class="d-flex justify-content-between mb-4"><span class="fw-bold">Total</span><span id="summTotal" class="fw-bold text-primary fs-5">₹0.00</span></div>
          <input type="hidden" name="subtotal" id="subtotalInput" value="0">
          <input type="hidden" name="tax_amount" id="taxAmountInput" value="0">
          <input type="hidden" name="total" id="totalInput" value="0">
          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Save Invoice</button>
            <a href="<?= base_url('admin/invoices') ?>" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
  let itemIdx = 1;

  function recalc() {
    let sub = 0;
    $('.item-row').each(function() {
      const qty = parseFloat($(this).find('.item-qty').val()) || 0;
      const pr = parseFloat($(this).find('.item-price').val()) || 0;
      const t = qty * pr;
      $(this).find('.item-total').val(t.toFixed(2));
      sub += t;
    });
    const tax = sub * (parseFloat($('#taxPercent').val()) || 0) / 100;
    const disc = parseFloat($('#discount').val()) || 0;
    const tot = sub + tax - disc;
    $('#summSubtotal').text('₹' + sub.toLocaleString('en-IN', {
      minimumFractionDigits: 2
    }));
    $('#summTax').text('₹' + tax.toLocaleString('en-IN', {
      minimumFractionDigits: 2
    }));
    $('#summDiscount').text('-₹' + disc.toLocaleString('en-IN', {
      minimumFractionDigits: 2
    }));
    $('#summTotal').text('₹' + tot.toLocaleString('en-IN', {
      minimumFractionDigits: 2
    }));
  }
  $(document).on('input', '.item-qty,.item-price,#taxPercent,#discount', recalc);
  $('#addItem').on('click', function() {
    const row = `<tr class="item-row">
    <td><input type="text" name="items[${itemIdx}][description]" class="form-control form-control-sm" required></td>
    <td><input type="number" name="items[${itemIdx}][quantity]" class="form-control form-control-sm item-qty" value="1" min="1" step="0.01" required></td>
    <td><input type="number" name="items[${itemIdx}][unit_price]" class="form-control form-control-sm item-price" value="0" step="0.01" required></td>
    <td><input type="text" class="form-control form-control-sm item-total bg-light" value="0.00" readonly></td>
    <td><button type="button" class="btn btn-xs btn-outline-danger btn-remove-item"><i class="bi bi-trash"></i></button></td>
  </tr>`;
    $('#itemsBody').append(row);
    itemIdx++;
  });
  $(document).on('click', '.btn-remove-item', function() {
    if ($('.item-row').length > 1) {
      $(this).closest('tr').remove();
      recalc();
    }
  });
  $('#clientSelect').on('change', function() {
    const cid = $(this).val();
    if (!cid) return;
    $.get(`<?= base_url('admin/ajax/projects/') ?>${cid}`, data => {
      let opts = '<option value="">— No Project —</option>';
      data.forEach(p => opts += `<option value="${p.id}">${p.name}</option>`);
      $('#projectSelect').html(opts);
    });
  });
  $('.select2').select2({
    theme: 'bootstrap-5',
    width: '100%'
  });
  recalc();
</script>
<?= $this->endSection() ?>