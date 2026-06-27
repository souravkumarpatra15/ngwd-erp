<?= $this->extend('layouts/client') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 py-3 text-center">
        <h5 class="fw-bold mb-0"><i class="bi bi-credit-card me-2 text-primary"></i>Secure Payment</h5>
      </div>
      <div class="card-body p-4">
        <table class="table table-sm table-borderless mb-4">
          <tr><td class="text-muted">Invoice</td><td class="fw-semibold"><?= esc($invoice['invoice_number']) ?></td></tr>
          <tr><td class="text-muted">Project</td><td><?= esc($invoice['project_name'] ?? '—') ?></td></tr>
          <tr><td class="text-muted">Total</td><td>₹<?= number_format($invoice['total'],2) ?></td></tr>
          <tr><td class="text-muted">Already Paid</td><td class="text-success">₹<?= number_format($invoice['paid_amount']??0,2) ?></td></tr>
          <tr class="table-warning"><td class="fw-bold">Amount Due</td><td class="fw-bold fs-5">₹<?= number_format($amount_due,2) ?></td></tr>
        </table>

        <button id="rzpBtn" class="btn btn-success btn-lg w-100">
          <i class="bi bi-lock me-2"></i>Pay ₹<?= number_format($amount_due,2) ?> Now
        </button>
        <div class="text-center mt-3 text-muted small">
          <i class="bi bi-shield-check me-1"></i>Powered by Razorpay · 256-bit SSL Encrypted
        </div>
      </div>
    </div>
    <div class="text-center mt-3">
      <a href="<?= base_url('portal/invoices/'.$invoice['id']) ?>" class="text-muted small"><i class="bi bi-arrow-left me-1"></i>Back to Invoice</a>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
const options = {
  key: '<?= esc($razorpay_key) ?>',
  amount: <?= (int)($amount_due * 100) ?>,
  currency: 'INR',
  name: '<?= esc($settings['company_name'] ?? 'NGWebD') ?>',
  description: 'Payment for Invoice <?= esc($invoice['invoice_number']) ?>',
  order_id: '<?= esc($order_id) ?>',
  handler: function(response) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?= base_url('portal/payment/verify') ?>';
    [
      ['razorpay_payment_id', response.razorpay_payment_id],
      ['razorpay_order_id',   response.razorpay_order_id],
      ['razorpay_signature',  response.razorpay_signature],
      ['invoice_id',          '<?= $invoice['id'] ?>'],
      ['csrf_test_name',      document.querySelector('meta[name=csrf-token]').content],
    ].forEach(([name, value]) => {
      const input = document.createElement('input');
      input.type = 'hidden'; input.name = name; input.value = value;
      form.appendChild(input);
    });
    document.body.appendChild(form);
    form.submit();
  },
  prefill: {
    name: '<?= esc($current_user['name'] ?? '') ?>',
    email: '<?= esc($current_user['email'] ?? '') ?>',
  },
  theme: { color: '#0d6efd' }
};
document.getElementById('rzpBtn').addEventListener('click', function() {
  new Razorpay(options).open();
});
</script>
<?= $this->endSection() ?>
