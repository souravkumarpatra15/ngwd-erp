<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<ul class="nav nav-tabs mb-4" id="settingsTabs">
  <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#company"><i class="bi bi-building me-1"></i>Company</a></li>
  <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#email"><i class="bi bi-envelope me-1"></i>Email</a></li>
  <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#whatsapp"><i class="bi bi-whatsapp me-1"></i>WhatsApp</a></li>
  <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#payment"><i class="bi bi-credit-card me-1"></i>Razorpay</a></li>
  <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#invoice"><i class="bi bi-receipt me-1"></i>Invoice</a></li>
</ul>
<div class="tab-content">
  <div class="tab-pane fade show active" id="company">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0"><h6 class="mb-0 fw-semibold">Company Information</h6></div>
      <div class="card-body">
        <form action="<?= base_url('admin/settings/save/company') ?>" method="POST" enctype="multipart/form-data">
          <?= csrf_field() ?>
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label small fw-semibold">Company Name</label><input type="text" name="company_name" class="form-control" value="<?= esc($settings['company_name']??'') ?>"></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Logo</label><input type="file" name="company_logo" class="form-control" accept="image/*"><?php if (!empty($settings['company_logo'])): ?><img src="<?= base_url($settings['company_logo']) ?>" height="40" class="mt-2 rounded"><?php endif; ?></div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Authorized Signature</label>
              <input type="file" name="signature_image" class="form-control" accept="image/*">
              <?php if (!empty($settings['signature_image'])): ?>
                <img src="<?= base_url($settings['signature_image']) ?>" height="40" class="mt-2 rounded bg-light p-1">
              <?php endif; ?>
              <div class="form-text">Uploaded once here — auto-applied to every proposal &amp; agreement PDF you send.</div>
            </div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Signatory Name</label><input type="text" name="signatory_name" class="form-control" value="<?= esc($settings['signatory_name']??'') ?>" placeholder="e.g. Sourav Kumar Patra"></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Signatory Title</label><input type="text" name="signatory_title" class="form-control" value="<?= esc($settings['signatory_title']??'') ?>" placeholder="e.g. Founder & Director"></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">GST Number</label><input type="text" name="company_gst" class="form-control" value="<?= esc($settings['company_gst']??'') ?>"></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">PAN Number</label><input type="text" name="company_pan" class="form-control" value="<?= esc($settings['company_pan']??'') ?>"></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Phone</label><input type="text" name="company_phone" class="form-control" value="<?= esc($settings['company_phone']??'') ?>"></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Email</label><input type="email" name="company_email" class="form-control" value="<?= esc($settings['company_email']??'') ?>"></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Website</label><input type="url" name="company_website" class="form-control" value="<?= esc($settings['company_website']??'') ?>"></div>
            <div class="col-12"><label class="form-label small fw-semibold">Address</label><textarea name="company_address" class="form-control" rows="3"><?= esc($settings['company_address']??'') ?></textarea></div>
            <div class="col-12"><button type="submit" class="btn btn-primary">Save Company Settings</button></div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="tab-pane fade" id="email">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0"><h6 class="mb-0 fw-semibold">SMTP Email Configuration</h6></div>
      <div class="card-body">
        <form action="<?= base_url('admin/settings/save/email') ?>" method="POST">
          <?= csrf_field() ?>
          <div class="row g-3">
            <div class="col-md-8"><label class="form-label small fw-semibold">SMTP Host</label><input type="text" name="smtp_host" class="form-control" value="<?= esc($settings['smtp_host']??'') ?>" placeholder="smtp.gmail.com"></div>
            <div class="col-md-4"><label class="form-label small fw-semibold">SMTP Port</label><input type="number" name="smtp_port" class="form-control" value="<?= esc($settings['smtp_port']??587) ?>"></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Username</label><input type="text" name="smtp_username" class="form-control" value="<?= esc($settings['smtp_username']??'') ?>"></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Password / App Password</label><input type="password" name="smtp_password" class="form-control" placeholder="Leave blank to keep current"></div>
            <div class="col-md-4"><label class="form-label small fw-semibold">Encryption</label><select name="smtp_encryption" class="form-select"><option value="tls" <?= ($settings['smtp_encryption']??'tls')==='tls'?'selected':'' ?>>TLS</option><option value="ssl" <?= ($settings['smtp_encryption']??'')==='ssl'?'selected':'' ?>>SSL</option></select></div>
            <div class="col-12"><button type="submit" class="btn btn-primary me-2">Save Email Settings</button></div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="tab-pane fade" id="whatsapp">
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header bg-white border-0"><h6 class="mb-0 fw-semibold">WhatsApp via MSG91</h6></div>
      <div class="card-body">
        <div class="alert alert-info small"><i class="bi bi-info-circle me-2"></i><strong>Setup:</strong> MSG91 panel → WhatsApp → integrate your number, copy the <strong>Auth Key</strong>, <strong>Integrated Number</strong> (country code, no +) and template <strong>Namespace</strong>. Session messages (text/image/video/link/buttons) work inside the 24-hour chat window; templates start new conversations.</div>
        <form action="<?= base_url('admin/settings/save/whatsapp') ?>" method="POST">
          <?= csrf_field() ?>
          <div class="row g-3">
            <div class="col-12"><label class="form-label small fw-semibold">MSG91 Auth Key *</label><input type="password" name="msg91_authkey" class="form-control" value="" placeholder="<?= !empty($settings['msg91_authkey']) ? 'Saved — leave blank to keep current' : 'Paste MSG91 auth key' ?>" autocomplete="new-password"></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Integrated Number *</label><input type="text" name="msg91_integrated_number" class="form-control" value="<?= esc($settings['msg91_integrated_number']??'') ?>" placeholder="919876543210"><div class="form-text">WhatsApp business number, digits only with country code.</div></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Template Namespace</label><input type="text" name="msg91_namespace" class="form-control" value="<?= esc($settings['msg91_namespace']??'') ?>" placeholder="338cef55_..."></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Default Template Language</label><input type="text" name="msg91_language" class="form-control" value="<?= esc($settings['msg91_language']??'en') ?>" placeholder="en"></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">API Base URL</label><input type="text" name="msg91_base_url" class="form-control" value="<?= esc($settings['msg91_base_url']??'https://api.msg91.com/api/v5/whatsapp/whatsapp-outbound-message') ?>"></div>
            <div class="col-12"><button type="submit" class="btn btn-success">Save WhatsApp Settings</button></div>
          </div>
        </form>
      </div>
    </div>
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0"><h6 class="mb-0 fw-semibold">Send Test Message</h6></div>
      <div class="card-body">
        <form id="waTestForm" class="row g-3">
          <?= csrf_field() ?>
          <div class="col-md-4"><label class="form-label small fw-semibold">To (mobile)</label><input type="text" name="to" class="form-control" placeholder="9876543210" required></div>
          <div class="col-md-4"><label class="form-label small fw-semibold">Type</label><select name="type" class="form-select" id="waTestType"><option value="text">Text</option><option value="image">Image</option><option value="video">Video</option><option value="link">Link</option><option value="buttons">Text + Buttons</option><option value="template">Template</option></select></div>
          <div class="col-md-4" id="waTestTemplateWrap" style="display:none"><label class="form-label small fw-semibold">Template name</label><input type="text" name="template" class="form-control" placeholder="order_update"></div>
          <div class="col-12"><label class="form-label small fw-semibold">Message / Caption</label><textarea name="body" class="form-control" rows="2" placeholder="Hello from NGWebD ERP (test)"></textarea></div>
          <div class="col-12" id="waTestMediaWrap" style="display:none"><label class="form-label small fw-semibold">Media / Link URL</label><input type="url" name="link" class="form-control" placeholder="https://..."></div>
          <div class="col-12"><button type="submit" class="btn btn-outline-success"><i class="bi bi-whatsapp me-1"></i>Send Test</button> <span class="text-muted small ms-2">Session types need an open 24h chat window with the number.</span></div>
        </form>
      </div>
    </div>
  </div>

  <div class="tab-pane fade" id="payment">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0"><h6 class="mb-0 fw-semibold">Razorpay Integration</h6></div>
      <div class="card-body">
        <form action="<?= base_url('admin/settings/save/payment') ?>" method="POST">
          <?= csrf_field() ?>
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label small fw-semibold">Razorpay Key ID</label><input type="text" name="razorpay_key" class="form-control" value="<?= esc($settings['razorpay_key']??'') ?>" placeholder="rzp_live_xxxxxxx"></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Razorpay Key Secret</label><input type="password" name="razorpay_secret" class="form-control" placeholder="Leave blank to keep current"></div>
            <div class="col-12"><div class="alert alert-warning small mb-0"><strong>Webhook URL:</strong> <code><?= base_url('webhook/razorpay') ?></code><br>Add in Razorpay Dashboard → Webhooks. Enable: <strong>payment.captured</strong></div></div>
            <div class="col-12"><button type="submit" class="btn btn-primary">Save Payment Settings</button></div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="tab-pane fade" id="invoice">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0"><h6 class="mb-0 fw-semibold">Invoice & Document Settings</h6></div>
      <div class="card-body">
        <form action="<?= base_url('admin/settings/save/invoice') ?>" method="POST">
          <?= csrf_field() ?>
          <div class="row g-3">
            <div class="col-md-4"><label class="form-label small fw-semibold">Invoice Prefix</label><input type="text" name="invoice_prefix" class="form-control" value="<?= esc($settings['invoice_prefix']??'NGWD') ?>"><div class="form-text">Format: PREFIX/YEAR/00001</div></div>
            <div class="col-md-4"><label class="form-label small fw-semibold">Default GST %</label><input type="number" name="tax_percent" class="form-control" value="<?= esc($settings['tax_percent']??18) ?>" step="0.01"></div>
            <div class="col-md-4"><label class="form-label small fw-semibold">Currency</label><select name="currency" class="form-select">
              <?php $globalCur = $settings['currency'] ?? 'INR'; ?>
              <?php foreach (['INR' => 'INR (₹)', 'USD' => 'USD ($)', 'EUR' => 'EUR (€)', 'GBP' => 'GBP (£)'] as $code => $label): ?>
                <option value="<?= $code ?>" <?= $globalCur === $code ? 'selected' : '' ?>><?= $label ?></option>
              <?php endforeach; ?>
            </select></div>
            <div class="col-12"><label class="form-label small fw-semibold">Default Invoice Terms</label><textarea name="invoice_terms" class="form-control" rows="3"><?= esc($settings['invoice_terms']??'') ?></textarea></div>
            <div class="col-12"><label class="form-label small fw-semibold">Default Proposal Terms</label><textarea name="proposal_terms" class="form-control" rows="3"><?= esc($settings['proposal_terms']??'') ?></textarea></div>
            <div class="col-12"><label class="form-label small fw-semibold">Default Agreement Terms & Conditions</label><textarea name="agreement_terms" class="form-control" rows="5"><?= esc($settings['agreement_terms']??'') ?></textarea></div>
            <div class="col-12"><button type="submit" class="btn btn-primary">Save Settings</button></div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
$('#waTestType').on('change', function () {
  const t = $(this).val();
  $('#waTestMediaWrap').toggle(['image', 'video', 'link'].includes(t));
  $('#waTestTemplateWrap').toggle(t === 'template');
});
$('#waTestForm').on('submit', function (e) {
  e.preventDefault();
  const form = $(this);
  form.find('input[name="csrf_test_name"]').val(getCsrfToken());
  showLoader('Sending test...');
  $.post(`<?= base_url('admin/settings/test-whatsapp') ?>`, form.serialize(), r => {
    hideLoader();
    showToast(r.message || (r.status === 'success' ? 'Sent.' : 'Failed.'), r.status);
  }, 'json').fail(() => { hideLoader(); showToast('Server error. Please try again.', 'error'); });
});
</script>
<?= $this->endSection() ?>
