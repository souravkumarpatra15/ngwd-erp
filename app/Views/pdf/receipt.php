<?php
$baseUrl = rtrim(config('App')->baseURL, '/') . '/';

$logoUrl = !empty($settings['company_logo'])
    ? $baseUrl . ltrim($settings['company_logo'], '/')
    : '';

$sigUrl = !empty($settings['signature_image'])
    ? $baseUrl . ltrim($settings['signature_image'], '/')
    : '';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">

<style>
*{margin:0;padding:0;box-sizing:border-box}
@page{margin:0}
body{font-family:'DejaVu Sans',Arial,sans-serif;font-size:10.5px;color:#262626}

/* ---------- header ---------- */
.header{
    background:#0f5132;
    color:#fff;
    padding:24px 34px;
    display:table;
    width:100%
}

.brand{
    display:table-cell;
    vertical-align:middle
}

.logo-badge{
    display:inline-block;
    background:#fff;
    border-radius:5px;
    padding:7px 10px;
    margin-bottom:8px
}

.logo-badge img{
    height:32px;
    max-width:150px;
    display:block
}

.company-name{
    font-family:'DejaVu Serif',serif;
    font-size:15px;
    font-weight:bold;
    letter-spacing:.3px
}

.company-sub{
    font-size:9px;
    opacity:.8;
    margin-top:2px
}

.receipt-meta{
    display:table-cell;
    text-align:right;
    vertical-align:middle
}

.receipt-meta h1{
    font-family:'DejaVu Serif',serif;
    font-size:20px;
    letter-spacing:3px;
    font-weight:normal
}

.receipt-meta .num{
    font-size:11px;
    margin-top:6px;
    opacity:.9;
    letter-spacing:.4px
}

.paid-pill{
    display:inline-block;
    background:#d1e7dd;
    color:#0f5132;
    font-size:8.5px;
    font-weight:bold;
    letter-spacing:.8px;
    padding:3px 12px;
    border-radius:2px;
    margin-top:8px
}

/* ---------- body ---------- */
.body{padding:26px 34px 6px}

.amount-box{
    background:#f2f6f5;
    border:1px solid #cfe3dc;
    border-left:4px solid #0f5132;
    border-radius:4px;
    padding:20px 24px;
    text-align:center;
    margin-bottom:22px
}

.amount-box .label{
    font-size:9px;
    text-transform:uppercase;
    letter-spacing:1.5px;
    color:#0f5132;
    margin-bottom:6px;
    font-weight:bold
}

.amount-box .amount{
    font-size:30px;
    font-weight:bold;
    color:#0f5132
}

table.info-table{
    width:100%;
    border-collapse:collapse;
    margin-bottom:6px
}

.info-table td{
    padding:9px 0;
    border-bottom:1px solid #ececea;
    font-size:10.5px
}

.info-table tr:last-child td{border-bottom:none}

.info-table td:first-child{
    color:#8a8a86;
    width:40%
}

.info-table td:last-child{
    font-weight:600;
    text-align:right
}

.signature{
    margin-top:34px;
    text-align:right
}

.signature img{
    max-width:150px;
    max-height:46px;
    display:inline-block
}

.signature-line{
    width:160px;
    border-bottom:1px solid #333;
    margin-left:auto;
    margin-bottom:5px
}

.signature-text{
    font-size:9.5px;
    color:#8a8a86
}

.footer{
    text-align:center;
    margin-top:26px;
    padding:12px 34px;
    border-top:1px solid #ececea;
    font-size:8.5px;
    color:#9c9c97
}
</style>
</head>

<body>

<div class="header">
    <div class="brand">
        <?php if ($logoUrl): ?>
            <div class="logo-badge"><img src="<?= esc($logoUrl) ?>" alt="Logo"></div>
        <?php endif; ?>
        <div class="company-name"><?= esc($settings['company_name'] ?? '') ?></div>
        <div class="company-sub"><?= esc($settings['company_address'] ?? '') ?></div>
    </div>
    <div class="receipt-meta">
        <h1>RECEIPT</h1>
        <div class="num"><?= esc($payment['payment_number']) ?></div>
        <div class="paid-pill">PAYMENT RECEIVED</div>
    </div>
</div>

<div class="body">

    <div class="amount-box">
        <div class="label">Amount Paid</div>
        <div class="amount"><?= currencySymbol($payment['currency'] ?? 'INR') ?><?= number_format($payment['amount'], 2) ?></div>
    </div>

    <table class="info-table">
        <tr>
            <td>Receipt No</td>
            <td><?= esc($payment['payment_number']) ?></td>
        </tr>
        <tr>
            <td>Client</td>
            <td><?= esc($payment['client_name']) ?></td>
        </tr>
        <?php if (!empty($payment['project_name'])): ?>
        <tr>
            <td>Project</td>
            <td><?= esc($payment['project_name']) ?></td>
        </tr>
        <?php endif; ?>
        <tr>
            <td>Payment Date</td>
            <td><?= date('d/m/Y', strtotime($payment['payment_date'])) ?></td>
        </tr>
        <tr>
            <td>Payment Method</td>
            <td><?= ucwords(str_replace('_', ' ', $payment['method'])) ?></td>
        </tr>
        <?php if (!empty($payment['transaction_id'])): ?>
        <tr>
            <td>Transaction ID</td>
            <td style="font-family:monospace"><?= esc($payment['transaction_id']) ?></td>
        </tr>
        <?php endif; ?>
        <?php if (!empty($payment['notes'])): ?>
        <tr>
            <td>Notes</td>
            <td><?= esc($payment['notes']) ?></td>
        </tr>
        <?php endif; ?>
    </table>

    <?php if ($sigUrl): ?>
    <div class="signature">
        <img src="<?= esc($sigUrl) ?>" alt="Signature">
        <div class="signature-line"></div>
        <div class="signature-text">Authorized Signature</div>
    </div>
    <?php endif; ?>

</div>

<div class="footer">
    This is a computer-generated receipt and does not require a physical signature. &bull;
    <?= esc($settings['company_name'] ?? '') ?>
</div>

</body>
</html>
