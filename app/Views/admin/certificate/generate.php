<?php
/**
 * Printable internship certificate (A4 landscape).
 * Rendered by Admin\CertificateController::printCertificate — all
 * variables are guaranteed by the controller; QR is a data URI.
 *
 * @var string $internName        Intern full name
 * @var string $internDesignation Role / programme title
 * @var string $department        Department or 'N/A'
 * @var string $startDate         Programme start date (formatted)
 * @var string $endDate           Programme end date (formatted)
 * @var string $completionDate    Certificate issue date (formatted)
 * @var string $certNumber        Certificate number
 * @var string $finalScore        Final evaluation or 'N/A'
 * @var string $verifyUrl         Public verification URL
 * @var string|null $qrDataUri    base64 PNG data URI (null = fetch failed)
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate <?= esc($certNumber) ?> - NGWebD ERP</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Georgia, 'Times New Roman', serif;
            background: #f1f5f9;
        }
        .toolbar {
            background: #1e293b;
            color: #fff;
            padding: 12px 24px;
            text-align: center;
        }
        .toolbar button {
            font: 14px/1 Arial, sans-serif;
            padding: 10px 28px;
            margin: 0 6px;
            border: 0;
            border-radius: 6px;
            cursor: pointer;
            background: #3b82f6;
            color: #fff;
        }
        .toolbar button.secondary { background: #64748b; }
        .certificate-container {
            min-height: 1123px;   /* A4 landscape @96dpi */
            width: 1587px;        /* A4 landscape width  */
            max-width: 100%;
            margin: 0 auto;
            padding: 48px 56px;
            background: #fffdf7;
            border: 14px double #2d3748;
        }
        .ngwebd-logo {
            width: 180px;
            margin: 0 auto 24px;
            display: block;
        }
        .certificate-heading {
            text-align: center;
            font-size: 40px;
            font-weight: bold;
            color: #2d3748;
            margin: 24px 0 8px;
            letter-spacing: 2px;
        }
        .certificate-subheading {
            text-align: center;
            font-size: 16px;
            color: #718096;
            margin-bottom: 32px;
        }
        .certificates-table {
            width: 86%;
            margin: 0 auto;
            border-collapse: collapse;
        }
        .certificate-detail {
            padding: 12px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .detail-label {
            font-family: Arial, sans-serif;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #4a5568;
            display: block;
            margin-bottom: 5px;
        }
        .detail-value {
            color: #1a202c;
            font-size: 19px;
        }
        .cert-body {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 40px;
        }
        .cert-body .left { flex: 1; }
        .qr-section {
            text-align: center;
            margin-top: 24px;
            min-width: 190px;
        }
        .qr-section img { width: 150px; height: 150px; }
        .qr-fallback {
            width: 150px; height: 150px;
            background: #f7fafc;
            border: 2px solid #2d3748;
            border-radius: 6px;
            display: flex; align-items: center; justify-content: center;
            font-family: Arial, sans-serif; font-size: 11px; color: #718096;
            padding: 8px; word-break: break-all; text-align: center;
        }
        .qr-caption {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #718096;
            margin-top: 8px;
        }
        .footer {
            margin-top: 48px;
            text-align: center;
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #718096;
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
        }
        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .certificate-container {
                width: 100%;
                min-height: auto;
                margin: 0;
                border-width: 10px;
            }
            @page { size: A4 landscape; margin: 0; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button onclick="window.print()">🖨️ Print / Save as PDF</button>
        <button class="secondary" onclick="window.close()">Close</button>
    </div>

    <div class="certificate-container">
        <img src="<?= base_url('assets/images/company_logo.png') ?>" alt="NGWebD Logo" class="ngwebd-logo">

        <h1 class="certificate-heading">Certificate of Internship Completion</h1>
        <p class="certificate-subheading">This is to certify that</p>

        <div class="cert-body">
            <div class="left">
                <p style="text-align:center; font-size:30px; font-weight:bold; color:#1a202c; margin-bottom:24px; border-bottom:2px solid #b7791f; display:inline-block; padding:0 24px 8px;">
                    <?= esc($internName) ?>
                </p>

                <table class="certificates-table">
                    <tr class="certificate-detail">
                        <td><span class="detail-label">Internship Designation</span><span class="detail-value"><?= esc($internDesignation) ?></span></td>
                    </tr>
                    <tr class="certificate-detail">
                        <td><span class="detail-label">Department</span><span class="detail-value"><?= esc($department) ?></span></td>
                    </tr>
                    <tr class="certificate-detail">
                        <td><span class="detail-label">Internship Duration</span><span class="detail-value"><?= esc($startDate) ?> &nbsp;–&nbsp; <?= esc($endDate) ?></span></td>
                    </tr>
                    <tr class="certificate-detail">
                        <td><span class="detail-label">Completion Date</span><span class="detail-value"><?= esc($completionDate) ?></span></td>
                    </tr>
                    <tr class="certificate-detail">
                        <td><span class="detail-label">Final Evaluation</span><span class="detail-value"><?= esc($finalScore) ?></span></td>
                    </tr>
                    <tr class="certificate-detail">
                        <td><span class="detail-label">Certificate Number</span><span class="detail-value"><?= esc($certNumber) ?></span></td>
                    </tr>
                </table>
            </div>

            <div class="qr-section">
                <?php if (!empty($qrDataUri)): ?>
                    <img src="<?= $qrDataUri ?>" alt="Scan to verify">
                <?php else: ?>
                    <div class="qr-fallback"><?= esc($verifyUrl) ?></div>
                <?php endif; ?>
                <?php $verifyHost = parse_url($verifyUrl, PHP_URL_HOST); ?>
                <p class="qr-caption">Scan to verify<br><?= esc($verifyHost ?: 'our website') ?></p>
            </div>
        </div>

        <div class="footer">
            <p>
                <strong>NGWebD Consulting Pvt. Ltd.</strong><br>
                This certificate is issued by NGWebD Consulting Pvt. Ltd.<br>
                For any verification, please visit: <?= esc($verifyUrl) ?>
            </p>
        </div>
    </div>
</body>
</html>
