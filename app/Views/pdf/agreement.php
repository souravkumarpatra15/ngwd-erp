<!DOCTYPE html><html><head><meta charset="UTF-8">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'DejaVu Sans',Arial,sans-serif;font-size:11px;color:#333;line-height:1.7}
.header{background:#1a1a2e;color:#fff;padding:30px 40px}
.header h1{font-size:20px;letter-spacing:2px}
.header .sub{font-size:11px;opacity:.7;margin-top:4px}
.section{padding:20px 40px;border-bottom:1px solid #f0f0f0}
.section h2{font-size:12px;color:#1a1a2e;text-transform:uppercase;letter-spacing:1px;margin-bottom:10px;border-bottom:1px solid #dee2e6;padding-bottom:5px}
.sign-block{display:table;width:100%;margin-top:30px}
.sign-col{display:table-cell;width:50%;padding:0 20px 0 0}
.sign-col:last-child{padding:0 0 0 20px}
.sign-line{border-bottom:1px solid #333;margin-top:40px;margin-bottom:5px}
.sign-meta{font-size:10px;color:#666}
.footer{padding:15px 40px;text-align:center;font-size:9px;color:#aaa;border-top:1px solid #eee}
.signed-stamp{background:#d4edda;border:1px solid #c3e6cb;border-radius:6px;padding:10px;text-align:center;color:#155724;margin-top:10px;font-size:11px}
</style></head><body>
<div class="header">
  <div style="font-size:11px;opacity:.7;margin-bottom:8px"><?= esc($settings['company_name'] ?? '') ?></div>
  <h1>SERVICE AGREEMENT</h1>
  <div class="sub">Agreement No: <?= esc($agreement['agreement_number']) ?> &bull; <?= esc($agreement['title']) ?></div>
</div>

<?php if (!empty($agreement['client_information'])): ?>
<div class="section"><h2>Client Information</h2><?= nl2br(esc($agreement['client_information'])) ?></div>
<?php endif; ?>
<?php if (!empty($agreement['project_information'])): ?>
<div class="section"><h2>Project Information</h2><?= nl2br(esc($agreement['project_information'])) ?></div>
<?php endif; ?>
<?php if (!empty($agreement['deliverables'])): ?>
<div class="section"><h2>Deliverables</h2><?= nl2br(esc($agreement['deliverables'])) ?></div>
<?php endif; ?>
<?php if (!empty($agreement['timeline'])): ?>
<div class="section"><h2>Timeline</h2><?= nl2br(esc($agreement['timeline'])) ?></div>
<?php endif; ?>
<?php if (!empty($agreement['payment_terms'])): ?>
<div class="section"><h2>Payment Terms</h2><?= nl2br(esc($agreement['payment_terms'])) ?></div>
<?php endif; ?>
<?php if (!empty($agreement['support_terms'])): ?>
<div class="section"><h2>Support Terms</h2><?= nl2br(esc($agreement['support_terms'])) ?></div>
<?php endif; ?>
<?php if (!empty($agreement['cancellation_terms'])): ?>
<div class="section"><h2>Cancellation Policy</h2><?= nl2br(esc($agreement['cancellation_terms'])) ?></div>
<?php endif; ?>
<?php if (!empty($agreement['terms_conditions'])): ?>
<div class="section"><h2>Terms & Conditions</h2><?= nl2br(esc($agreement['terms_conditions'])) ?></div>
<?php endif; ?>

<div class="section">
  <h2>Signatures</h2>
  <?php if ($agreement['status'] === 'signed'): ?>
  <div class="signed-stamp">✔ Digitally Signed by <?= esc($agreement['client_name']) ?> on <?= date('d/m/Y H:i', strtotime($agreement['signed_at'])) ?> (IP: <?= esc($agreement['signature_ip']) ?>)</div>
  <?php endif; ?>
  <div class="sign-block" style="margin-top:20px">
    <div class="sign-col">
      <div class="sign-line"></div>
      <div class="sign-meta"><strong><?= esc($settings['company_name'] ?? '') ?></strong><br>Service Provider<br>Date: _______________</div>
    </div>
    <div class="sign-col">
      <div class="sign-line"></div>
      <div class="sign-meta"><strong><?= esc($agreement['client_name']) ?></strong><br>Client<br>Date: <?= $agreement['status']==='signed' ? date('d/m/Y',strtotime($agreement['signed_at'])) : '_______________' ?></div>
    </div>
  </div>
</div>

<div class="footer">
  <?= esc($settings['company_name'] ?? '') ?> &bull; <?= esc($settings['company_email'] ?? '') ?> &bull; <?= esc($settings['company_website'] ?? '') ?>
</div>
</body></html>
