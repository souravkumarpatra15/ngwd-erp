<!DOCTYPE html><html><head><meta charset="UTF-8">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'DejaVu Sans',Arial,sans-serif;font-size:11px;color:#333;line-height:1.6}
.cover{background:#0d6efd;color:#fff;padding:60px 40px;min-height:250px}
.cover h1{font-size:28px;margin-bottom:8px}
.cover .sub{font-size:14px;opacity:.8}
.cover .meta{margin-top:30px;font-size:12px;opacity:.9}
.section{padding:24px 40px;border-bottom:1px solid #f0f0f0}
.section h2{font-size:14px;color:#0d6efd;text-transform:uppercase;letter-spacing:1px;margin-bottom:12px;padding-bottom:6px;border-bottom:2px solid #0d6efd}
.section p,.section ul{margin-bottom:8px;font-size:11px;line-height:1.7}
.section ul{padding-left:20px}
.price-box{background:#f8f9fa;border:1px solid #dee2e6;border-radius:8px;padding:16px;margin:10px 0;text-align:center}
.price-box .amount{font-size:28px;font-weight:bold;color:#0d6efd}
.footer{padding:20px 40px;text-align:center;font-size:9px;color:#aaa;border-top:1px solid #eee}
</style></head><body>
<div class="cover">
  <div style="font-size:12px;opacity:.8;margin-bottom:20px"><?= esc($settings['company_name'] ?? '') ?></div>
  <h1>Project Proposal</h1>
  <div class="sub"><?= esc($proposal['title']) ?></div>
  <div class="meta">
    <div>Prepared for: <strong><?= esc($proposal['client_name']) ?></strong></div>
    <div>Proposal #: <?= esc($proposal['proposal_number']) ?></div>
    <div>Valid Until: <?= !empty($proposal['valid_until']) ? date('d/m/Y',strtotime($proposal['valid_until'])) : 'N/A' ?></div>
  </div>
</div>

<?php if (!empty($proposal['introduction'])): ?>
<div class="section"><h2>Company Introduction</h2><?= nl2br(esc($proposal['introduction'])) ?></div>
<?php endif; ?>
<?php if (!empty($proposal['project_overview'])): ?>
<div class="section"><h2>Project Overview</h2><?= nl2br(esc($proposal['project_overview'])) ?></div>
<?php endif; ?>
<?php if (!empty($proposal['scope_of_work'])): ?>
<div class="section"><h2>Scope of Work</h2><?= nl2br(esc($proposal['scope_of_work'])) ?></div>
<?php endif; ?>
<?php if (!empty($proposal['deliverables'])): ?>
<div class="section"><h2>Deliverables</h2><?= nl2br(esc($proposal['deliverables'])) ?></div>
<?php endif; ?>
<?php if (!empty($proposal['timeline'])): ?>
<div class="section"><h2>Timeline</h2><?= nl2br(esc($proposal['timeline'])) ?></div>
<?php endif; ?>
<?php if (!empty($proposal['pricing']) || $proposal['total_amount'] > 0): ?>
<div class="section"><h2>Investment</h2>
  <?php if (!empty($proposal['pricing'])): ?><?= nl2br(esc($proposal['pricing'])) ?><?php endif; ?>
  <div class="price-box"><div style="color:#666;font-size:12px">Total Investment</div><div class="amount">₹<?= number_format($proposal['total_amount'],0) ?></div></div>
</div>
<?php endif; ?>
<?php if (!empty($proposal['terms'])): ?>
<div class="section"><h2>Terms & Conditions</h2><?= nl2br(esc($proposal['terms'])) ?></div>
<?php endif; ?>

<div class="footer">
  <?= esc($settings['company_name'] ?? '') ?> &bull; <?= esc($settings['company_email'] ?? '') ?> &bull; <?= esc($settings['company_phone'] ?? '') ?>
</div>
</body></html>
