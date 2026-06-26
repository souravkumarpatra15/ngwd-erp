<?= $this->extend('layouts/client') ?>
<?= $this->section('content') ?>
<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 py-3 text-center">
        <h5 class="mb-0 fw-semibold">Pay Invoice</h5>
        <div class="text-muted small"><?= esc($invoice['invoice_number']) ?></div>
      </div>
      <div class="card-body p-4">
        <div class="d-flex justify-content-between mb-2 small"><span class="text-muted">Subtotal</span><span>₹<?= number_format($invoice['subtotal'],2) ?></span></div>
        <?php if ($invoice['tax_amount'] > 0): ?>
        <div class="d-flex justify-content-between mb-2 small"><span class="text-muted">Tax</span><span>₹<?= number_format($invoice['tax_amount'],2) ?></span></div>
        <?php endif; ?>
        <hr>
        <div class="d-flex justify-content-between mb-4">
          <span class="fw-bold">Amount Due</span>
          <span class="fw-bold text-primary fs-5">₹<?= number_format($invoice['balance_due'],2) ?></span>
        </div>
        <?php if ($razorpay_order): ?>
        <button id="rzpBtn" class="btn btn-primary w-100 btn-lg">
          <i class="bi bi-shield-check me-2"></i>Pay ₹<?= number_format($invoice['balance_due'],2) ?>
        </button>
        <p class="text-center text-muted small mt-3"><i class="bi bi-lock me-1"></i>Secured by Razorpay</p>
        <?php else: ?>
        <div class="alert alert-warning small">Payment gateway not configured. Please contact us.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<?php if ($razorpay_order): ?>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
document.getElementById('rzpBtn').addEventListener('click', function() {
  const rzp = new Razorpay({
    key: '<?= esc($razorpay_key) ?>',
    amount: <?= (int)($invoice['balance_due']*100) ?>,
    currency: 'INR',
    name: '<?= esc($settings['company_name'] ?? '') ?>',
    description: 'Invoice <?= esc($invoice['invoice_number']) ?>',
    order_id: '<?= $razorpay_order['id'] ?>',
    prefill: { name:'<?= esc($invoice['client_name']) ?>', email:'<?= esc($invoice['client_email']) ?>' },
    theme: { color:'#0d6efd' },
    handler: function(res) {
      fetch('<?= base_url('portal/pay/verify') ?>', {
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-Token':'<?= csrf_hash() ?>'},
        body: JSON.stringify({razorpay_order_id:res.razorpay_order_id,razorpay_payment_id:res.razorpay_payment_id,razorpay_signature:res.razorpay_signature,invoice_id:<?= $invoice['id'] ?>})
      }).then(r=>r.json()).then(d => {
        if (d.status==='success') window.location='<?= base_url('portal/invoices/'.$invoice['id']) ?>?paid=1';
        else alert('Payment verification failed. Please contact support.');
      });
    }
  });
  rzp.open();
});
</script>
<?php endif; ?>
<?= $this->endSection() ?>
