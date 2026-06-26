<!DOCTYPE html><html><head><meta charset="UTF-8">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'DejaVu Sans',Arial,sans-serif;font-size:11px;color:#333}
.header{background:#0d6efd;color:#fff;padding:20px 30px;display:table;width:100%}
.logo{display:table-cell;vertical-align:top}
.logo h2{font-size:18px;margin-bottom:3px}
.logo p{font-size:10px;opacity:.8;line-height:1.5}
.inv-meta{display:table-cell;text-align:right;vertical-align:top}
.inv-meta h1{font-size:24px;letter-spacing:2px;opacity:.9}
.inv-meta .num{font-size:13px;margin-top:4px}
.status{display:inline-block;padding:3px 12px;border-radius:20px;font-size:9px;font-weight:bold;margin-top:6px}
.body{padding:24px 30px}
.bill-row{display:table;width:100%;margin-bottom:20px}
.bill-to,.bill-info{display:table-cell;vertical-align:top;width:50%}
.bill-to h4,.bill-info h4{font-size:10px;text-transform:uppercase;letter-spacing:1px;color:#999;margin-bottom:6px}
.bill-info{text-align:right}
.bill-info table{margin-left:auto}
.bill-info td{padding:2px 6px;font-size:11px}
.bill-info td:first-child{color:#999}
table.items{width:100%;border-collapse:collapse;margin:16px 0}
table.items thead th{background:#f8f9fa;padding:8px 10px;text-align:left;font-size:10px;text-transform:uppercase;letter-spacing:.5px;border-bottom:2px solid #dee2e6}
table.items tbody td{padding:8px 10px;border-bottom:1px solid #f0f0f0}
table.items tbody tr:last-child td{border-bottom:none}
.text-right{text-align:right}
.totals-table{width:260px;float:right;margin-top:10px}
.totals-table td{padding:5px 8px;font-size:11px}
.totals-table .divider td{border-top:1px solid #dee2e6;padding-top:8px}
.totals-table .grand td{font-weight:bold;font-size:13px;color:#0d6efd}
.footer{margin-top:30px;padding-top:15px;border-top:1px solid #eee;text-align:center;font-size:9px;color:#aaa}
.clearfix::after{content:'';display:table;clear:both}
</style></head><body>
<div class="header">
  <div class="logo">
    <h2><?= esc($settings['company_name'] ?? '') ?></h2>
    <p><?= nl2br(esc($settings['company_address'] ?? '')) ?></p>
    <?php if (!empty($settings['company_gst'])): ?><p>GSTIN: <?= esc($settings['company_gst']) ?></p><?php endif; ?>
    <p><?= esc($settings['company_phone'] ?? '') ?> &bull; <?= esc($settings['company_email'] ?? '') ?></p>
  </div>
  <div class="inv-meta">
    <h1><?= $invoice['is_gst'] ? 'TAX INVOICE' : 'INVOICE' ?></h1>
    <div class="num"><?= esc($invoice['invoice_number']) ?></div>
    <?php
    $sc = ['draft'=>'#6c757d','sent'=>'#0dcaf0','paid'=>'#198754','partial'=>'#ffc107','overdue'=>'#dc3545'];
    $bg = $sc[$invoice['status']] ?? '#6c757d';
    ?>
    <div class="status" style="background:<?= $bg ?>"><?= strtoupper($invoice['status']) ?></div>
  </div>
</div>

<div class="body">
  <div class="bill-row">
    <div class="bill-to">
      <h4>Bill To</h4>
      <strong><?= esc($invoice['client_name']) ?></strong><br>
      <?php if (!empty($invoice['client_address'])): ?><?= nl2br(esc($invoice['client_address'])) ?><br><?php endif; ?>
      <?php if (!empty($invoice['client_gst'])): ?><span style="color:#666">GSTIN: <?= esc($invoice['client_gst']) ?></span><br><?php endif; ?>
      <?php if (!empty($invoice['client_email'])): ?><?= esc($invoice['client_email']) ?><?php endif; ?>
    </div>
    <div class="bill-info">
      <h4>Invoice Details</h4>
      <table>
        <tr><td>Invoice No:</td><td><strong><?= esc($invoice['invoice_number']) ?></strong></td></tr>
        <tr><td>Invoice Date:</td><td><?= date('d/m/Y',strtotime($invoice['invoice_date'])) ?></td></tr>
        <tr><td>Due Date:</td><td><?= date('d/m/Y',strtotime($invoice['due_date'])) ?></td></tr>
        <?php if (!empty($invoice['project_name'])): ?><tr><td>Project:</td><td><?= esc($invoice['project_name']) ?></td></tr><?php endif; ?>
      </table>
    </div>
  </div>

  <table class="items">
    <thead><tr><th>#</th><th>Description</th><th class="text-right">Qty</th><th class="text-right">Rate</th><th class="text-right">Amount</th></tr></thead>
    <tbody>
    <?php foreach ($items as $i => $item): ?>
    <tr>
      <td style="color:#999"><?= $i+1 ?></td>
      <td><?= esc($item['description']) ?></td>
      <td class="text-right"><?= $item['quantity'] ?></td>
      <td class="text-right">₹<?= number_format($item['unit_price'],2) ?></td>
      <td class="text-right">₹<?= number_format($item['total'],2) ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>

  <div class="clearfix">
    <table class="totals-table">
      <tr><td style="color:#666">Subtotal</td><td class="text-right">₹<?= number_format($invoice['subtotal'],2) ?></td></tr>
      <?php if ($invoice['tax_amount'] > 0): ?>
      <tr><td style="color:#666">GST (<?= $invoice['tax_percent'] ?>%)</td><td class="text-right">₹<?= number_format($invoice['tax_amount'],2) ?></td></tr>
      <?php endif; ?>
      <?php if ($invoice['discount'] > 0): ?>
      <tr><td style="color:#dc3545">Discount</td><td class="text-right" style="color:#dc3545">-₹<?= number_format($invoice['discount'],2) ?></td></tr>
      <?php endif; ?>
      <tr class="divider"><td></td><td></td></tr>
      <tr class="grand"><td>Total</td><td class="text-right">₹<?= number_format($invoice['total'],2) ?></td></tr>
      <?php if ($invoice['paid_amount'] > 0): ?>
      <tr><td style="color:#198754">Paid</td><td class="text-right" style="color:#198754">₹<?= number_format($invoice['paid_amount'],2) ?></td></tr>
      <tr class="grand"><td style="color:#dc3545">Balance Due</td><td class="text-right" style="color:#dc3545">₹<?= number_format($invoice['balance_due'],2) ?></td></tr>
      <?php endif; ?>
    </table>
  </div>

  <?php if ($invoice['notes'] || $invoice['terms']): ?>
  <div style="margin-top:40px;clear:both">
    <?php if ($invoice['notes']): ?><p><strong>Notes:</strong> <?= nl2br(esc($invoice['notes'])) ?></p><?php endif; ?>
    <?php if ($invoice['terms']): ?><p style="margin-top:8px"><strong>Terms:</strong> <?= nl2br(esc($invoice['terms'])) ?></p><?php endif; ?>
  </div>
  <?php endif; ?>
</div>

<div class="footer">
  Thank you for your business! &bull; <?= esc($settings['company_name'] ?? '') ?> &bull; <?= esc($settings['company_website'] ?? '') ?>
</div>
</body></html>
