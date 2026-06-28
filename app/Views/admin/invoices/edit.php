<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<form action="<?= base_url('admin/invoices/update/'.$invoice['id']) ?>" method="POST" id="invoiceForm">
  <?= csrf_field() ?>
  <div class="row g-4">
    <div class="col-md-8">

      <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
          <h6 class="mb-0 fw-semibold">Edit Invoice</h6>
          <span class="badge bg-secondary"><?= esc($invoice['invoice_number']) ?></span>
        </div>
        <div class="card-body row g-3">
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Client</label>
            <select name="client_id" class="form-select select2" required>
              <?php foreach ($clients as $c): ?>
              <option value="<?= $c['id'] ?>" <?= $invoice['client_id']==$c['id']?'selected':'' ?>>
                <?= esc($c['name']) ?><?= $c['company_name'] ? ' — '.$c['company_name'] : '' ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Project</label>
            <select name="project_id" class="form-select" id="projectSelect">
              <option value="">— No Project —</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Invoice Date <span class="text-danger">*</span></label>
            <input type="date" name="invoice_date" class="form-control" value="<?= $invoice['invoice_date'] ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Due Date <span class="text-danger">*</span></label>
            <input type="date" name="due_date" class="form-control" value="<?= $invoice['due_date'] ?>" required>
          </div>
          <div class="col-md-4">
            <label class="form-label small fw-semibold">GST Invoice?</label>
            <div class="form-check form-switch mt-2">
              <input class="form-check-input" type="checkbox" name="is_gst" id="isGst" value="1" <?= $invoice['is_gst']?'checked':'' ?>>
              <label class="form-check-label" for="isGst">Include GST</label>
            </div>
          </div>
          <div class="col-md-4">
            <label class="form-label small fw-semibold">Tax %</label>
            <input type="number" name="tax_percent" id="taxPercent" class="form-control" value="<?= $invoice['tax_percent'] ?>" step="0.01">
          </div>
          <div class="col-md-4">
            <label class="form-label small fw-semibold">Discount (₹)</label>
            <input type="number" name="discount" id="discount" class="form-control" value="<?= $invoice['discount'] ?? 0 ?>" step="0.01">
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
              <?php foreach ($items as $i => $item): ?>
              <tr class="item-row">
                <td><input type="text" name="items[<?= $i ?>][description]" class="form-control form-control-sm" value="<?= esc($item['description']) ?>" required></td>
                <td><input type="number" name="items[<?= $i ?>][quantity]" class="form-control form-control-sm item-qty" value="<?= $item['quantity'] ?>" min="0.01" step="0.01" required></td>
                <td><input type="number" name="items[<?= $i ?>][unit_price]" class="form-control form-control-sm item-price" value="<?= $item['unit_price'] ?>" step="0.01" required></td>
                <td><input type="text" class="form-control form-control-sm item-total bg-light" value="<?= number_format($item['total'],2) ?>" readonly></td>
                <td><button type="button" class="btn btn-xs btn-outline-danger btn-remove-item"><i class="bi bi-trash"></i></button></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="card border-0 shadow-sm">
        <div class="card-body row g-3">
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Notes</label>
            <textarea name="notes" class="form-control" rows="3"><?= esc($invoice['notes'] ?? '') ?></textarea>
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Terms & Conditions</label>
            <textarea name="terms" class="form-control" rows="3"><?= esc($invoice['terms'] ?? '') ?></textarea>
          </div>
        </div>
      </div>

    </div>

    <div class="col-md-4">
      <div class="card border-0 shadow-sm sticky-top" style="top:80px">
        <div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold">Invoice Summary</h6></div>
        <div class="card-body">
          <div class="d-flex justify-content-between mb-2"><span class="text-muted small">Subtotal</span><span id="summSubtotal">₹0.00</span></div>
          <div class="d-flex justify-content-between mb-2"><span class="text-muted small">Tax</span><span id="summTax">₹0.00</span></div>
          <div class="d-flex justify-content-between mb-2"><span class="text-muted small">Discount</span><span id="summDiscount" class="text-danger">-₹0.00</span></div>
          <hr>
          <div class="d-flex justify-content-between mb-4"><span class="fw-bold">Total</span><span id="summTotal" class="fw-bold text-primary fs-5">₹0.00</span></div>
          <input type="hidden" name="subtotal" id="subtotalInput" value="0">
          <input type="hidden" name="tax_amount" id="taxAmountInput" value="0">
          <input type="hidden" name="total" id="totalInput" value="0">
          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Save Changes</button>
            <a href="<?= base_url('admin/invoices/'.$invoice['id']) ?>" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
let itemIdx = <?= count($items) ?>;

function recalc() {
  let sub = 0;
  $('.item-row').each(function() {
    const qty = parseFloat($(this).find('.item-qty').val()) || 0;
    const pr  = parseFloat($(this).find('.item-price').val()) || 0;
    const t = qty * pr;
    $(this).find('.item-total').val(t.toFixed(2));
    sub += t;
  });
  const tax  = sub * (parseFloat($('#taxPercent').val()) || 0) / 100;
  const disc = parseFloat($('#discount').val()) || 0;
  const tot  = sub + tax - disc;
  $('#summSubtotal').text('₹'+sub.toLocaleString('en-IN',{minimumFractionDigits:2}));
  $('#summTax').text('₹'+tax.toLocaleString('en-IN',{minimumFractionDigits:2}));
  $('#summDiscount').text('-₹'+disc.toLocaleString('en-IN',{minimumFractionDigits:2}));
  $('#summTotal').text('₹'+tot.toLocaleString('en-IN',{minimumFractionDigits:2}));
  $('#subtotalInput').val(sub.toFixed(2));
  $('#taxAmountInput').val(tax.toFixed(2));
  $('#totalInput').val(tot.toFixed(2));
}
$(document).on('input', '.item-qty,.item-price,#taxPercent,#discount', recalc);
$('#addItem').on('click', function() {
  const row = `<tr class="item-row">
    <td><input type="text" name="items[${itemIdx}][description]" class="form-control form-control-sm" required></td>
    <td><input type="number" name="items[${itemIdx}][quantity]" class="form-control form-control-sm item-qty" value="1" min="0.01" step="0.01" required></td>
    <td><input type="number" name="items[${itemIdx}][unit_price]" class="form-control form-control-sm item-price" value="0" step="0.01" required></td>
    <td><input type="text" class="form-control form-control-sm item-total bg-light" value="0.00" readonly></td>
    <td><button type="button" class="btn btn-xs btn-outline-danger btn-remove-item"><i class="bi bi-trash"></i></button></td>
  </tr>`;
  $('#itemsBody').append(row); itemIdx++;
});
$(document).on('click', '.btn-remove-item', function() {
  if ($('.item-row').length > 1) { $(this).closest('tr').remove(); recalc(); }
});
// Load projects for current client
const currentProjectId = '<?= $invoice['project_id'] ?>';
$.get('<?= base_url('admin/ajax/projects/') ?><?= $invoice['client_id'] ?>', data => {
  let opts = '<option value="">— No Project —</option>';
  data.forEach(p => opts += `<option value="${p.id}" ${p.id==currentProjectId?'selected':''}>${p.name}</option>`);
  $('#projectSelect').html(opts);
});
if (typeof $.fn.select2 !== 'undefined') $('.select2').select2({ theme:'bootstrap-5', width:'100%' });
recalc();
</script>
<?= $this->endSection() ?>
