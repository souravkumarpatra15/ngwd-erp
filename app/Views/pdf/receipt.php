<!DOCTYPE html><html><head><meta charset="UTF-8">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'DejaVu Sans',Arial,sans-serif;font-size:12px;color:#333}
.header{background:#198754;color:#fff;padding:24px 30px;display:table;width:100%}
.logo{display:table-cell;vertical-align:middle}
.logo h2{font-size:16px;margin-bottom:3px}
.logo p{font-size:10px;opacity:.8}
.receipt-meta{display:table-cell;text-align:right;vertical-align:middle}
.receipt-meta h1{font-size:20px;letter-spacing:2px}
.receipt-meta p{font-size:11px;opacity:.85;margin-top:4px}
.body{padding:24px 30px}
.info-table{width:100%;border-collapse:collapse;margin-bottom:20px}
.info-table td{padding:8px 12px;border-bottom:1px solid #f0f0f0;font-size:11px}
.info-table td:first-child{color:#666;width:40%}
.info-table td:last-child{font-weight:600}
.amount-box{background:#d1e7dd;border:1px solid #a3cfbb;border-radius:10px;padding:24px;text-align:center;margin:20px 0}
.amount-box .label{font-size:12px;color:#0a3622;margin-bottom:6px}
.amount-box .amount{font-size:32px;font-weight:bold;color:#0a3622}
.footer{text-align:center;margin-top:30px;padding-top:15px;border-top:1px solid #eee;font-size:10px;color:#aaa}
</style></head><body>
<div class="header">
  <div class="logo"><h2><?= esc($settings['company_name'] ?? '') ?></h2><p><?= esc($settings['company_address'] ?? '') ?></p></div>
  <div class="receipt-meta"><h1>RECEIPT</h1><p><?= esc($payment['payment_number']) ?></p></div>
</div>
<div class="body">
  <div class="amount-box">
    <div class="label">Payment Received</div>
    <div class="amount">₹<?= number_format($payment['amount'],2) ?></div>
  </div>
  <table class="info-table">
    <tr><td>Receipt No</td><td><?= esc($payment['payment_number']) ?></td></tr>
    <tr><td>Client</td><td><?= esc($payment['client_name']) ?></td></tr>
    <?php if (!empty($payment['project_name'])): ?><tr><td>Project</td><td><?= esc($payment['project_name']) ?></td></tr><?php endif; ?>
    <tr><td>Payment Date</td><td><?= date('d/m/Y',strtotime($payment['payment_date'])) ?></td></tr>
    <tr><td>Payment Method</td><td><?= ucwords(str_replace('_',' ',$payment['method'])) ?></td></tr>
    <?php if (!empty($payment['transaction_id'])): ?><tr><td>Transaction ID</td><td><?= esc($payment['transaction_id']) ?></td></tr><?php endif; ?>
    <?php if (!empty($payment['notes'])): ?><tr><td>Notes</td><td><?= esc($payment['notes']) ?></td></tr><?php endif; ?>
  </table>
</div>
<div class="footer">This is a computer-generated receipt. &bull; <?= esc($settings['company_name'] ?? '') ?></div>
</body></html>
