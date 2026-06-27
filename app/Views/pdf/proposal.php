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

    body {
      font-family: 'DejaVu Sans', Arial, sans-serif;
      font-size: 11px;
      color: #2d2d2d;
      line-height: 1.55
    }

    /* ---------- Cover ---------- */
    .cover {
      background: #0b3d91;
      color: #fff;
      padding: 46px 46px 32px;
      border-bottom: 5px solid #f4b400
    }

    .cover img.logo {
      height: 36px;
      margin-bottom: 20px;
      display: block
    }

    .cover .brand {
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: 3px;
      opacity: .7;
      margin-bottom: 20px
    }

    .cover .accent-dash {
      width: 42px;
      height: 3px;
      background: #f4b400;
      margin-bottom: 12px
    }

    .cover h1 {
      font-size: 28px;
      font-weight: 700;
      letter-spacing: .3px;
      margin-bottom: 6px
    }

    .cover .sub {
      font-size: 13px;
      font-weight: 300;
      opacity: .85;
      margin-bottom: 26px
    }

    .cover table.meta {
      width: 100%;
      border-collapse: collapse
    }

    .cover table.meta td {
      padding: 0 22px;
      vertical-align: top;
      border-left: 1px solid rgba(255, 255, 255, .25)
    }

    .cover table.meta td.first {
      padding-left: 0;
      border-left: none
    }

    .cover .meta-label {
      display: block;
      font-size: 9px;
      text-transform: uppercase;
      letter-spacing: 1.5px;
      opacity: .6;
      margin-bottom: 3px
    }

    .cover .meta-value {
      font-size: 12px;
      font-weight: 700
    }

    /* ---------- Sections ---------- */
    .section {
      padding: 16px 40px;
      border-bottom: 1px solid #eef0f2
    }

    .section:last-of-type {
      border-bottom: none
    }

    .sec-table {
      width: 100%;
      border-collapse: collapse
    }

    .sec-table td.num-col {
      width: 40px;
      vertical-align: top;
      padding-top: 1px
    }

    .num-badge {
      width: 28px;
      height: 28px;
      border-radius: 50%;
      background: #0b3d91;
      color: #f4b400;
      font-size: 11px;
      font-weight: 700;
      text-align: center;
      line-height: 28px
    }

    .sec-table td.body-col {
      vertical-align: top;
      padding-left: 6px
    }

    .section h2 {
      font-size: 12.5px;
      color: #0b3d91;
      text-transform: uppercase;
      letter-spacing: 1.5px;
      font-weight: 700;
      margin-bottom: 8px
    }

    .section h2 span {
      display: inline-block;
      border-bottom: 2px solid #f4b400;
      padding-bottom: 4px
    }

    .section p,
    .section ul {
      margin-bottom: 5px;
      font-size: 11px;
      line-height: 1.6;
      color: #444
    }

    .section ul {
      padding-left: 18px
    }

    /* ---------- Investment ---------- */
    .price-box {
      background: #0b3d91;
      border-radius: 8px;
      padding: 18px;
      margin: 10px 0 2px;
      text-align: center;
      color: #fff
    }

    .price-box .label {
      font-size: 9px;
      text-transform: uppercase;
      letter-spacing: 2px;
      opacity: .7;
      margin-bottom: 5px
    }

    .price-box .amount {
      font-size: 30px;
      font-weight: 700;
      color: #f4b400
    }

    /* ---------- Footer ---------- */
    .footer {
      padding: 14px 40px;
      text-align: center;
      border-top: 1px solid #eef0f2
    }

    .footer img.logo-sm {
      height: 16px;
      margin-bottom: 6px
    }

    .footer .line1 {
      font-size: 9px;
      color: #888;
      letter-spacing: .3px;
      margin-bottom: 4px
    }

    .footer .line2 {
      font-size: 8px;
      color: #bbb;
      font-style: italic
    }
  </style>
</head>

<body>

  <div class="cover">
    <?php if (!empty($settings['company_logo'])): ?>
      <img class="logo" src="<?= esc($settings['company_logo']) ?>" alt="logo">
    <?php else: ?>
      <div class="brand"><?= esc($settings['company_name'] ?? '') ?></div>
    <?php endif; ?>
    <div class="accent-dash"></div>
    <h1>Project Proposal</h1>
    <div class="sub"><?= esc($proposal['title']) ?></div>
    <table class="meta">
      <tr>
        <td class="first">
          <span class="meta-label">Prepared For</span>
          <span class="meta-value"><?= esc($proposal['client_name']) ?></span>
        </td>
        <td>
          <span class="meta-label">Proposal #</span>
          <span class="meta-value"><?= esc($proposal['proposal_number']) ?></span>
        </td>
        <td>
          <span class="meta-label">Valid Until</span>
          <span class="meta-value"><?= !empty($proposal['valid_until']) ? date('d/m/Y', strtotime($proposal['valid_until'])) : 'N/A' ?></span>
        </td>
      </tr>
    </table>
  </div>

  <?php $sec = 0; ?>

  <?php if (!empty($proposal['introduction'])): $sec++; ?>
    <div class="section">
      <table class="sec-table">
        <tr>
          <td class="num-col">
            <div class="num-badge"><?= sprintf('%02d', $sec) ?></div>
          </td>
          <td class="body-col">
            <h2><span>Company Introduction</span></h2>
            <?= nl2br(esc($proposal['introduction'])) ?>
          </td>
        </tr>
      </table>
    </div>
  <?php endif; ?>

  <?php if (!empty($proposal['project_overview'])): $sec++; ?>
    <div class="section">
      <table class="sec-table">
        <tr>
          <td class="num-col">
            <div class="num-badge"><?= sprintf('%02d', $sec) ?></div>
          </td>
          <td class="body-col">
            <h2><span>Project Overview</span></h2>
            <?= nl2br(esc($proposal['project_overview'])) ?>
          </td>
        </tr>
      </table>
    </div>
  <?php endif; ?>

  <?php if (!empty($proposal['scope_of_work'])): $sec++; ?>
    <div class="section">
      <table class="sec-table">
        <tr>
          <td class="num-col">
            <div class="num-badge"><?= sprintf('%02d', $sec) ?></div>
          </td>
          <td class="body-col">
            <h2><span>Scope of Work</span></h2>
            <?= nl2br(esc($proposal['scope_of_work'])) ?>
          </td>
        </tr>
      </table>
    </div>
  <?php endif; ?>

  <?php if (!empty($proposal['deliverables'])): $sec++; ?>
    <div class="section">
      <table class="sec-table">
        <tr>
          <td class="num-col">
            <div class="num-badge"><?= sprintf('%02d', $sec) ?></div>
          </td>
          <td class="body-col">
            <h2><span>Deliverables</span></h2>
            <?= nl2br(esc($proposal['deliverables'])) ?>
          </td>
        </tr>
      </table>
    </div>
  <?php endif; ?>

  <?php if (!empty($proposal['timeline'])): $sec++; ?>
    <div class="section">
      <table class="sec-table">
        <tr>
          <td class="num-col">
            <div class="num-badge"><?= sprintf('%02d', $sec) ?></div>
          </td>
          <td class="body-col">
            <h2><span>Timeline</span></h2>
            <?= nl2br(esc($proposal['timeline'])) ?>
          </td>
        </tr>
      </table>
    </div>
  <?php endif; ?>

  <?php if (!empty($proposal['pricing']) || !empty($proposal['total_amount'])): $sec++; ?>
    <div class="section">
      <table class="sec-table">
        <tr>
          <td class="num-col">
            <div class="num-badge"><?= sprintf('%02d', $sec) ?></div>
          </td>
          <td class="body-col">
            <h2><span>Investment</span></h2>
            <?php if (!empty($proposal['pricing'])): ?><?= nl2br(esc($proposal['pricing'])) ?><?php endif; ?>
            <?php if (!empty($proposal['total_amount'])): ?>
              <div class="price-box">
                <div class="label">Total Investment</div>
                <div class="amount">₹<?= number_format($proposal['total_amount'], 0) ?></div>
              </div>
            <?php endif; ?>
          </td>
        </tr>
      </table>
    </div>
  <?php endif; ?>

  <?php if (!empty($proposal['terms'])): $sec++; ?>
    <div class="section">
      <table class="sec-table">
        <tr>
          <td class="num-col">
            <div class="num-badge"><?= sprintf('%02d', $sec) ?></div>
          </td>
          <td class="body-col">
            <h2><span>Terms &amp; Conditions</span></h2>
            <?= nl2br(esc($proposal['terms'])) ?>
          </td>
        </tr>
      </table>
    </div>
  <?php endif; ?>

  <div class="footer">
    <?php if (!empty($settings['company_logo'])): ?>
      <img class="logo-sm" src="<?= esc($settings['company_logo']) ?>" alt="logo">
    <?php endif; ?>
    <div class="line1"><?= esc($settings['company_name'] ?? '') ?> &bull; <?= esc($settings['company_email'] ?? '') ?> &bull; <?= esc($settings['company_phone'] ?? '') ?></div>
    <div class="line2">This proposal is confidential and intended solely for the recipient named above.</div>
  </div>

</body>

</html>