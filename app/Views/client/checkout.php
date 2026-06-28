<?= $this->extend('layouts/client') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
  <div class="col-md-6 col-lg-5">

    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 py-3 text-center">
        <h5 class="fw-bold mb-0"><i class="bi bi-shield-lock me-2 text-success"></i>Secure Payment</h5>
        <div class="text-muted small mt-1">Powered by Razorpay · 256-bit SSL</div>
      </div>

      <div class="card-body p-4">
        <!-- Invoice summary -->
        <div class="bg-light rounded p-3 mb-4">
          <div class="d-flex justify-content-between align-items-center mb-2 small">
            <span class="text-muted">Invoice</span>
            <span class="fw-semibold"><?= esc($invoice['invoice_number']) ?></span>
          </div>
          <?php if ($invoice['project_name'] ?? null): ?>
          <div class="d-flex justify-content-between small mb-2">
            <span class="text-muted">Project</span>
            <span><?= esc($invoice['project_name']) ?></span>
          </div>
          <?php endif; ?>
          <div class="d-flex justify-content-between small mb-2">
            <span class="text-muted">Invoice Total</span>
            <span>₹<?= number_format($invoice['total'], 2) ?></span>
          </div>
          <?php if ((float)($invoice['paid_amount'] ?? 0) > 0): ?>
          <div class="d-flex justify-content-between small mb-2">
            <span class="text-muted">Already Paid</span>
            <span class="text-success">-₹<?= number_format($invoice['paid_amount'], 2) ?></span>
          </div>
          <?php endif; ?>
          <hr class="my-2">
          <div class="d-flex justify-content-between align-items-center">
            <span class="fw-bold">Amount Due</span>
            <span class="fw-bold text-primary fs-5">₹<?= number_format($invoice['balance_due'], 2) ?></span>
          </div>
        </div>

        <?php if ($razorpay_order && isset($razorpay_order['id'])): ?>
          <button id="rzpBtn" class="btn btn-success btn-lg w-100 mb-3">
            <i class="bi bi-credit-card me-2"></i>
            Pay ₹<?= number_format($invoice['balance_due'], 2) ?> Now
          </button>
          <div class="text-center text-muted" style="font-size:12px">
            <i class="bi bi-shield-check me-1"></i>
            UPI · Cards · Net Banking · Wallets accepted
          </div>
        <?php else: ?>
          <div class="alert alert-warning text-center mb-0">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Payment gateway not configured yet.<br>
            <small>Please contact <?= esc($settings['company_name'] ?? 'NGWebD') ?> to arrange payment.</small>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="text-center mt-3">
      <a href="<?= base_url('portal/invoices/'.$invoice['id']) ?>" class="text-muted small">
        <i class="bi bi-arrow-left me-1"></i>Back to Invoice
      </a>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<?php if ($razorpay_order && isset($razorpay_order['id'])): ?>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
(function() {
  const rzpOptions = {
    key:         '<?= esc($razorpay_key) ?>',
    amount:      <?= (int) round($invoice['balance_due'] * 100) ?>,
    currency:    'INR',
    name:        '<?= esc($settings['company_name'] ?? 'NGWebD Consulting') ?>',
    description: 'Invoice <?= esc($invoice['invoice_number']) ?>',
    order_id:    '<?= esc($razorpay_order['id']) ?>',
    prefill: {
      name:    '<?= esc(session()->get('user_name') ?? '') ?>',
      email:   '<?= esc($invoice['client_email'] ?? '') ?>',
      contact: '',
    },
    theme: { color: '#0d6efd' },

    handler: function(response) {
      const btn = document.getElementById('rzpBtn');
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Verifying payment…';

      fetch('<?= base_url('portal/pay/verify') ?>', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          razorpay_order_id:   response.razorpay_order_id,
          razorpay_payment_id: response.razorpay_payment_id,
          razorpay_signature:  response.razorpay_signature,
          invoice_id:          <?= (int) $invoice['id'] ?>,
        }),
      })
      .then(r => r.json())
      .then(res => {
        if (res.status === 'success') {
          // Redirect to invoice detail with success flash
          window.location.href = '<?= base_url('portal/invoices/'.$invoice['id']) ?>?paid=1';
        } else {
          btn.disabled = false;
          btn.innerHTML = '<i class="bi bi-credit-card me-2"></i>Pay ₹<?= number_format($invoice['balance_due'], 2) ?> Now';
          alert('Verification failed: ' + res.message + '\nPlease contact support with your payment ID: ' + response.razorpay_payment_id);
        }
      })
      .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-credit-card me-2"></i>Pay ₹<?= number_format($invoice['balance_due'], 2) ?> Now';
        alert('Network error during verification. Please contact support with payment ID: ' + response.razorpay_payment_id);
        console.error(err);
      });
    },

    modal: {
      ondismiss: function() {
        document.getElementById('rzpBtn').disabled = false;
      }
    }
  };

  document.getElementById('rzpBtn').addEventListener('click', function() {
    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Opening…';
    try {
      new Razorpay(rzpOptions).open();
    } catch(e) {
      this.disabled = false;
      this.innerHTML = '<i class="bi bi-credit-card me-2"></i>Pay ₹<?= number_format($invoice['balance_due'], 2) ?> Now';
      alert('Could not open Razorpay. Please check your internet connection.');
    }
  });
})();
</script>
<?php endif; ?>
<?= $this->endSection() ?>
