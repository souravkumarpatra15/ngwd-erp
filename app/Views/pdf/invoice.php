<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box
    }

    @page {
      margin: 0
    }

    body {
      font-family: 'DejaVu Sans', Arial, sans-serif;
      font-size: 10.5px;
      color: #262626
    }

    /* ---------- header ---------- */
    .header {
      background: #0b4a45;
      color: #fff;
      padding: 22px 32px;
      display: table;
      width: 100%
    }

    .brand {
      display: table-cell;
      vertical-align: middle
    }

    .brand-table {
      border-collapse: collapse
    }

    .brand-table td {
      vertical-align: middle;
      padding: 0
    }

    .logo-cell {
      padding-right: 14px
    }

    .logo-img {
      height: 42px;
      width: auto;
      max-width: 120px;
      display: block
    }

    .company-name {
      font-family: 'DejaVu Serif', serif;
      font-size: 16px;
      font-weight: bold;
      letter-spacing: .3px;
      margin-bottom: 4px
    }

    .company-sub {
      font-size: 9.5px;
      line-height: 1.55;
      opacity: .82;
      max-width: 270px
    }

    .company-sub p {
      margin: 0
    }

    .inv-meta {
      display: table-cell;
      text-align: right;
      vertical-align: middle
    }

    .inv-meta h1 {
      font-family: 'DejaVu Serif', serif;
      font-size: 20px;
      letter-spacing: 3px;
      font-weight: normal;
      opacity: .95
    }

    .inv-meta .num {
      font-size: 11.5px;
      margin-top: 6px;
      letter-spacing: .4px;
      opacity: .9
    }

    .status {
      display: inline-block;
      padding: 3px 13px;
      border-radius: 2px;
      font-size: 8.5px;
      font-weight: bold;
      letter-spacing: .8px;
      margin-top: 9px;
      color: #fff
    }

    /* ---------- body ---------- */
    .body {
      padding: 22px 32px 4px
    }

    .bill-row {
      display: table;
      width: 100%;
      margin-bottom: 18px
    }

    .bill-to,
    .bill-info {
      display: table-cell;
      vertical-align: top;
      width: 50%
    }

    .bill-to h4,
    .bill-info h4 {
      font-size: 8.5px;
      text-transform: uppercase;
      letter-spacing: 1.2px;
      color: #0b4a45;
      margin-bottom: 6px;
      font-weight: bold
    }

    .bill-to strong {
      font-size: 12px
    }

    .bill-info {
      text-align: right
    }

    .bill-info table {
      margin-left: auto;
      border-collapse: collapse
    }

    .bill-info td {
      padding: 2px 0 2px 14px;
      font-size: 10.5px
    }

    .bill-info td:first-child {
      color: #8a8a86
    }

    table.items {
      width: 100%;
      border-collapse: collapse;
      margin: 4px 0 16px
    }

    table.items thead th {
      background: #f2f6f5;
      color: #0b4a45;
      padding: 8px 10px;
      text-align: left;
      font-size: 9px;
      text-transform: uppercase;
      letter-spacing: .6px;
      border-bottom: 1.5px solid #0b4a45
    }

    table.items tbody td {
      padding: 8px 10px;
      border-bottom: 1px solid #ececea;
      font-size: 10.5px
    }

    table.items tbody tr:last-child td {
      border-bottom: none
    }

    .text-right {
      text-align: right
    }

    .totals-table {
      width: 240px;
      float: right;
      border-collapse: collapse;
      margin-top: 2px
    }

    .totals-table td {
      padding: 5px 0;
      font-size: 10.5px
    }

    .totals-table .divider td {
      border-top: 1px solid #ddd9cf;
      padding-top: 8px
    }

    .totals-table .highlight-cell {
      padding-top: 8px
    }

    .totals-box {
      width: 100%;
      border-collapse: collapse;
      background: #f2f6f5;
      border-radius: 2px
    }

    .totals-box td {
      padding: 9px 10px
    }

    .totals-box .label {
      font-weight: bold;
      color: #0b4a45;
      font-size: 10.5px
    }

    .totals-box .amount {
      font-weight: bold;
      color: #0b4a45;
      font-size: 14px;
      text-align: right
    }

    .notes {
      margin-top: 28px;
      clear: both;
      font-size: 10px;
      line-height: 1.6;
      color: #444
    }

    .notes strong {
      color: #0b4a45
    }

    .footer {
      margin-top: 20px;
      padding: 12px 32px;
      border-top: 1px solid #ececea;
      text-align: center;
      font-size: 8.5px;
      color: #9c9c97
    }

    .clearfix::after {
      content: '';
      display: table;
      clear: both
    }
  </style>
</head>

<body>

  <div class="header">
    <div class="brand">
      <table class="brand-table">
        <tr>
          <td>
            <div class="company-name"><?= esc($settings['company_name'] ?? '') ?></div>
            <div class="company-sub">
              <p><?= nl2br(esc($settings['company_address'] ?? '')) ?></p>
              <?php if (!empty($settings['company_gst'])): ?><p>GSTIN: <?= esc($settings['company_gst']) ?></p><?php endif; ?>
              <p><?= esc($settings['company_phone'] ?? '') ?> &bull; <?= esc($settings['company_email'] ?? '') ?></p>
            </div>
          </td>
        </tr>
      </table>
    </div>
    <div class="inv-meta">
      <h1><?= $invoice['is_gst'] ? 'TAX INVOICE' : 'INVOICE' ?></h1>
      <div class="num"><?= esc($invoice['invoice_number']) ?></div>
      <?php
      $sc = ['draft' => '#8a8a86', 'sent' => '#2f6f8f', 'paid' => '#1f7a5c', 'partial' => '#b07d27', 'overdue' => '#b3403a'];
      $bg = $sc[$invoice['status']] ?? '#8a8a86';
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
          <tr>
            <td>Invoice No:</td>
            <td><strong><?= esc($invoice['invoice_number']) ?></strong></td>
          </tr>
          <tr>
            <td>Invoice Date:</td>
            <td><?= date('d/m/Y', strtotime($invoice['invoice_date'])) ?></td>
          </tr>
          <tr>
            <td>Due Date:</td>
            <td><?= date('d/m/Y', strtotime($invoice['due_date'])) ?></td>
          </tr>
          <?php if (!empty($invoice['project_name'])): ?><tr>
              <td>Project:</td>
              <td><?= esc($invoice['project_name']) ?></td>
            </tr><?php endif; ?>
        </table>
      </div>
    </div>

    <table class="items">
      <thead>
        <tr>
          <th>#</th>
          <th>Description</th>
          <th class="text-right">Qty</th>
          <th class="text-right">Rate</th>
          <th class="text-right">Amount</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $i => $item): ?>
          <tr>
            <td style="color:#999"><?= $i + 1 ?></td>
            <td><?= esc($item['description']) ?></td>
            <td class="text-right"><?= $item['quantity'] ?></td>
            <td class="text-right">₹<?= number_format($item['unit_price'], 2) ?></td>
            <td class="text-right">₹<?= number_format($item['total'], 2) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="clearfix">
      <table class="totals-table">
        <tr>
          <td style="color:#767672">Subtotal</td>
          <td class="text-right">₹<?= number_format($invoice['subtotal'], 2) ?></td>
        </tr>
        <?php if ($invoice['tax_amount'] > 0): ?>
          <tr>
            <td style="color:#767672">GST (<?= $invoice['tax_percent'] ?>%)</td>
            <td class="text-right">₹<?= number_format($invoice['tax_amount'], 2) ?></td>
          </tr>
        <?php endif; ?>
        <?php if ($invoice['discount'] > 0): ?>
          <tr>
            <td style="color:#b3403a">Discount</td>
            <td class="text-right" style="color:#b3403a">-₹<?= number_format($invoice['discount'], 2) ?></td>
          </tr>
        <?php endif; ?>
        <tr class="divider">
          <td></td>
          <td></td>
        </tr>

        <?php if ($invoice['paid_amount'] > 0): ?>
          <tr>
            <td style="font-weight:bold">Total</td>
            <td class="text-right" style="font-weight:bold">₹<?= number_format($invoice['total'], 2) ?></td>
          </tr>
          <tr>
            <td style="color:#1f7a5c">Paid</td>
            <td class="text-right" style="color:#1f7a5c">₹<?= number_format($invoice['paid_amount'], 2) ?></td>
          </tr>
          <tr>
            <td colspan="2" class="highlight-cell">
              <table class="totals-box">
                <tr>
                  <td class="label">BALANCE DUE</td>
                  <td class="amount">₹<?= number_format($invoice['balance_due'], 2) ?></td>
                </tr>
              </table>
            </td>
          </tr>
        <?php else: ?>
          <tr>
            <td colspan="2" class="highlight-cell">
              <table class="totals-box">
                <tr>
                  <td class="label">TOTAL</td>
                  <td class="amount">₹<?= number_format($invoice['total'], 2) ?></td>
                </tr>
              </table>
            </td>
          </tr>
        <?php endif; ?>
      </table>
    </div>

    <?php if ($invoice['notes'] || $invoice['terms']): ?>
      <div class="notes">
        <?php if ($invoice['notes']): ?><p><strong>Notes:</strong> <?= nl2br(esc($invoice['notes'])) ?></p><?php endif; ?>
        <?php if ($invoice['terms']): ?><p style="margin-top:8px"><strong>Terms:</strong> <?= nl2br(esc($invoice['terms'])) ?></p><?php endif; ?>
      </div>
    <?php endif; ?>
  </div>

  <div class="footer">
    Thank you for your business! &bull; <?= esc($settings['company_name'] ?? '') ?> &bull; <?= esc($settings['company_website'] ?? '') ?>
  </div>
</body>

</html>