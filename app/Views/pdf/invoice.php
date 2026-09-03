<?php
$baseUrl = rtrim(config('App')->baseURL, '/') . '/';

$logoUrl = !empty($settings['company_logo'])
  ? $baseUrl . ltrim($settings['company_logo'], '/')
  : '';

$sigUrl = !empty($settings['signature_image'])
  ? $baseUrl . ltrim($settings['signature_image'], '/')
  : '';

$accent = '#0b4a45';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box }
@page { margin:0 }
body { font-family:'DejaVu Sans', Arial, sans-serif; font-size:10.5px; color:#2b2b2b }

/* ---------- top accent bar ---------- */
.accent-bar { height:7px; background:<?= $accent ?> }

/* ---------- header ---------- */
.header { padding:26px 36px 20px; display:table; width:100% }
.header-left { display:table-cell; vertical-align:top; width:60% }
.header-right { display:table-cell; vertical-align:top; width:40%; text-align:right }

.logo-img { height:38px; width:auto; max-width:150px; display:block; margin-bottom:10px }

.company-name { font-family:'DejaVu Serif', serif; font-size:16px; font-weight:bold; color:<?= $accent ?>; letter-spacing:.2px; margin-bottom:5px }
.company-detail { font-size:9px; line-height:1.6; color:#767672 }
.company-detail p { margin:0 }

.doc-title { font-family:'DejaVu Serif', serif; font-size:26px; letter-spacing:3px; color:<?= $accent ?>; font-weight:normal }
.doc-number { font-size:11px; color:#767672; margin-top:6px; letter-spacing:.3px }

.status-pill { display:inline-block; padding:4px 14px; border-radius:20px; font-size:8.5px; font-weight:bold; letter-spacing:.8px; color:#fff; margin-top:10px }

.header-divider { border-bottom:2px solid <?= $accent ?>; margin:0 36px }

/* ---------- body ---------- */
.body { padding:22px 36px 4px }

.info-cards { display:table; width:100%; margin:0 -16px 20px; border-collapse:separate; border-spacing:16px 0 }
.info-card { display:table-cell; width:50%; vertical-align:top; background:#f7f8f7; border-radius:6px; padding:14px 18px }
.info-card-right { padding-left:18px }

.info-label { font-size:8px; text-transform:uppercase; letter-spacing:1.2px; color:<?= $accent ?>; font-weight:bold; margin-bottom:7px }
.info-card strong { font-size:12px; display:block; margin-bottom:2px }
.info-card .muted { color:#767672; font-size:9.5px; line-height:1.5 }

.meta-row { display:table; width:100%; margin-bottom:4px }
.meta-row .k { display:table-cell; color:#767672; font-size:9.5px; padding:2px 0 }
.meta-row .v { display:table-cell; text-align:right; font-size:9.5px; font-weight:600; padding:2px 0 }

table.items { width:100%; border-collapse:collapse; margin:6px 0 18px }
table.items thead th { border-bottom:2px solid <?= $accent ?>; color:<?= $accent ?>; padding:8px 10px; text-align:left; font-size:8.5px; text-transform:uppercase; letter-spacing:.6px }
table.items tbody td { padding:9px 10px; border-bottom:1px solid #ececea; font-size:10.5px }
table.items tbody tr:nth-child(even) td { background:#fafbfa }
table.items tbody tr:last-child td { border-bottom:1px solid #ececea }
.text-right { text-align:right }

.totals-wrap { width:270px; float:right }
table.totals-table { width:100%; border-collapse:collapse }
.totals-table td { padding:5px 0; font-size:10.5px }
.totals-table td:first-child { color:#767672 }
.totals-table td:last-child { text-align:right }
.totals-table .divider td { border-top:1px solid #ddd9cf; padding-top:9px }

.balance-box { width:100%; border-collapse:collapse; background:<?= $accent ?>; border-radius:5px; margin-top:6px }
.balance-box td { padding:11px 14px }
.balance-box .label { color:#fff; opacity:.85; font-weight:bold; font-size:9.5px; text-transform:uppercase; letter-spacing:.6px }
.balance-box .amount { color:#fff; font-weight:bold; font-size:16px; text-align:right }

.paid-row td { color:#1f7a5c !important; font-weight:600 }

.clearfix::after { content:''; display:table; clear:both }

.notes { margin-top:30px; clear:both; font-size:9.5px; line-height:1.65; color:#4a4a4a }
.notes strong { color:<?= $accent ?> }
.notes p { margin-bottom:8px }

.sign-area { margin-top:26px; clear:both; text-align:right }
.sign-area img { max-width:150px; max-height:44px; display:inline-block }
.sign-line { width:170px; border-bottom:1px solid #333; margin-left:auto; margin-bottom:5px }
.sign-text { font-size:9px; color:#8a8a86 }

.footer { margin-top:22px; padding:14px 36px; border-top:1px solid #ececea; text-align:center; font-size:8.5px; color:#9c9c97 }
.footer strong { color:#767672 }
</style>
</head>

<body>

<div class="accent-bar"></div>

<div class="header">
  <div class="header-left">
    <?php if ($logoUrl): ?><img class="logo-img" src="<?= esc($logoUrl) ?>" alt="<?= esc($settings['company_name'] ?? 'Logo') ?>"><?php endif; ?>
    <div class="company-name"><?= esc($settings['company_name'] ?? '') ?></div>
    <div class="company-detail">
      <p><?= nl2br(esc($settings['company_address'] ?? '')) ?></p>
      <?php if (!empty($settings['company_gst'])): ?><p>GSTIN: <?= esc($settings['company_gst']) ?><?php if (!empty($settings['company_pan'])): ?> &nbsp;·&nbsp; PAN: <?= esc($settings['company_pan']) ?><?php endif; ?></p><?php endif; ?>
      <p><?= esc($settings['company_phone'] ?? '') ?><?= !empty($settings['company_phone']) && !empty($settings['company_email']) ? ' · ' : '' ?><?= esc($settings['company_email'] ?? '') ?></p>
    </div>
  </div>
  <div class="header-right">
    <div class="doc-title"><?= $invoice['is_gst'] ? 'TAX INVOICE' : 'INVOICE' ?></div>
    <div class="doc-number">#<?= esc($invoice['invoice_number']) ?></div>
    <?php
      $sc = ['draft' => '#8a8a86', 'sent' => '#2f6f8f', 'paid' => '#1f7a5c', 'partial' => '#b07d27', 'overdue' => '#b3403a'];
      $bg = $sc[$invoice['status']] ?? '#8a8a86';
    ?>
    <div class="status-pill" style="background:<?= $bg ?>"><?= strtoupper($invoice['status']) ?></div>
  </div>
</div>

<div class="header-divider"></div>

<div class="body">

  <div class="info-cards">
    <div class="info-card">
      <div class="info-label">Billed To</div>
      <strong><?= esc($invoice['client_name']) ?></strong>
      <div class="muted">
        <?php if (!empty($invoice['client_address'])): ?><?= nl2br(esc($invoice['client_address'])) ?><br><?php endif; ?>
        <?php if (!empty($invoice['client_gst'])): ?>GSTIN: <?= esc($invoice['client_gst']) ?><br><?php endif; ?>
        <?= esc($invoice['client_email'] ?? '') ?>
      </div>
    </div>
    <div class="info-card info-card-right">
      <div class="info-label">Invoice Details</div>
      <div class="meta-row"><span class="k">Invoice Date</span><span class="v"><?= date('d M Y', strtotime($invoice['invoice_date'])) ?></span></div>
      <div class="meta-row"><span class="k">Due Date</span><span class="v"><?= date('d M Y', strtotime($invoice['due_date'])) ?></span></div>
      <div class="meta-row"><span class="k">For</span><span class="v"><?= esc(\App\Models\InvoiceModel::forLabel($invoice)) ?></span></div>
    </div>
  </div>

  <table class="items">
    <thead>
      <tr>
        <th style="width:28px">#</th>
        <th>Description</th>
        <th class="text-right" style="width:50px">Qty</th>
        <th class="text-right" style="width:90px">Rate</th>
        <th class="text-right" style="width:100px">Amount</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($items as $i => $item): ?>
        <tr>
          <td style="color:#a8a8a4"><?= $i + 1 ?></td>
          <td><?= esc($item['description']) ?></td>
          <td class="text-right"><?= $item['quantity'] ?></td>
          <td class="text-right"><?= currencySymbol($invoice['currency'] ?? 'INR') ?><?= number_format($item['unit_price'], 2) ?></td>
          <td class="text-right"><?= currencySymbol($invoice['currency'] ?? 'INR') ?><?= number_format($item['total'], 2) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <div class="clearfix">
    <div class="totals-wrap">
      <table class="totals-table">
        <tr>
          <td>Subtotal</td>
          <td><?= currencySymbol($invoice['currency'] ?? 'INR') ?><?= number_format($invoice['subtotal'], 2) ?></td>
        </tr>
        <?php if ($invoice['tax_amount'] > 0): ?>
          <tr>
            <td>GST (<?= $invoice['tax_percent'] ?>%)</td>
            <td><?= currencySymbol($invoice['currency'] ?? 'INR') ?><?= number_format($invoice['tax_amount'], 2) ?></td>
          </tr>
        <?php endif; ?>
        <?php if ($invoice['discount'] > 0): ?>
          <tr>
            <td style="color:#b3403a">Discount</td>
            <td style="color:#b3403a">-<?= currencySymbol($invoice['currency'] ?? 'INR') ?><?= number_format($invoice['discount'], 2) ?></td>
          </tr>
        <?php endif; ?>
        <tr class="divider"><td></td><td></td></tr>

        <?php if ($invoice['paid_amount'] > 0): ?>
          <tr>
            <td style="font-weight:bold;color:#2b2b2b">Total</td>
            <td style="font-weight:bold;color:#2b2b2b"><?= currencySymbol($invoice['currency'] ?? 'INR') ?><?= number_format($invoice['total'], 2) ?></td>
          </tr>
          <tr class="paid-row">
            <td>Paid</td>
            <td><?= currencySymbol($invoice['currency'] ?? 'INR') ?><?= number_format($invoice['paid_amount'], 2) ?></td>
          </tr>
          <tr>
            <td colspan="2" style="padding-top:8px">
              <table class="balance-box">
                <tr>
                  <td class="label">Balance Due</td>
                  <td class="amount"><?= currencySymbol($invoice['currency'] ?? 'INR') ?><?= number_format($invoice['balance_due'], 2) ?></td>
                </tr>
              </table>
            </td>
          </tr>
        <?php else: ?>
          <tr>
            <td colspan="2" style="padding-top:8px">
              <table class="balance-box">
                <tr>
                  <td class="label">Total Due</td>
                  <td class="amount"><?= currencySymbol($invoice['currency'] ?? 'INR') ?><?= number_format($invoice['total'], 2) ?></td>
                </tr>
              </table>
            </td>
          </tr>
        <?php endif; ?>
      </table>
    </div>
  </div>

  <?php if ($invoice['notes'] || $invoice['terms']): ?>
    <div class="notes">
      <?php if ($invoice['notes']): ?><p><strong>Notes:</strong> <?= nl2br(esc($invoice['notes'])) ?></p><?php endif; ?>
      <?php if ($invoice['terms']): ?><p><strong>Terms:</strong> <?= nl2br(esc($invoice['terms'])) ?></p><?php endif; ?>
    </div>
  <?php endif; ?>

  <?php if ($sigUrl): ?>
    <div class="sign-area">
      <img src="<?= esc($sigUrl) ?>" alt="Signature">
      <div class="sign-line"></div>
      <div class="sign-text"><?= esc($settings['signatory_name'] ?? ($settings['company_name'] ?? 'Authorized Signatory')) ?><?php if (!empty($settings['signatory_title'])): ?> · <?= esc($settings['signatory_title']) ?><?php endif; ?></div>
    </div>
  <?php endif; ?>

</div>

<div class="footer">
  Thank you for your business! &bull; <strong><?= esc($settings['company_name'] ?? '') ?></strong> &bull; <?= esc($settings['company_website'] ?? '') ?>
</div>

</body>
</html>
