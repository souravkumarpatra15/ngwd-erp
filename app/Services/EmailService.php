<?php

namespace App\Services;

use App\Models\SettingModel;

class EmailService
{
    protected $email;
    protected $settings;

    public function __construct()
    {
        $this->settings = (new SettingModel())->getAllSettings();
        $this->email    = \Config\Services::email();
        $this->email->initialize([
            'protocol'  => 'smtp',
            'SMTPHost'  => $this->settings['smtp_host'] ?? '',
            'SMTPUser'  => $this->settings['smtp_username'] ?? '',
            'SMTPPass'  => $this->settings['smtp_password'] ?? '',
            'SMTPPort'  => (int)($this->settings['smtp_port'] ?? 587),
            'SMTPCrypto' => $this->settings['smtp_encryption'] ?? 'tls',
            'mailType'  => 'html',
            'charset'   => 'utf-8',
        ]);
    }

    public function send($to, $subject, $message, $attachment = null)
    {
        try {
            $this->email->setFrom($this->settings['company_email'] ?? 'noreply@ngwebd.com', $this->settings['company_name'] ?? 'NGWebD Consulting');
            $this->email->setTo($to);
            $this->email->setSubject($subject);
            $this->email->setMessage($this->wrap($message));
            if ($attachment) $this->email->attach($attachment);
            return $this->email->send();
        } catch (\Exception $e) {
            log_message('error', 'Email: ' . $e->getMessage());
            return false;
        }
    }

    public function sendInvoice(array $invoice, string $pdfPath)
    {
        $subject = "Invoice {$invoice['invoice_number']} from {$this->settings['company_name']}";
        $body    = "<h3>Dear {$invoice['client_name']},</h3><p>Please find your invoice <strong>{$invoice['invoice_number']}</strong> attached.</p><p>Amount: <strong>₹" . number_format($invoice['total'], 2) . "</strong><br>Due: {$invoice['due_date']}</p><p>Thank you!</p>";
        return $this->send($invoice['client_email'], $subject, $body, $pdfPath);
    }

    public function sendProposal(array $proposal, string $pdfPath)
    {
        $subject = "Proposal: {$proposal['title']}";
        $body    = "<h3>Dear {$proposal['client_name']},</h3><p>Please find our proposal for <strong>{$proposal['title']}</strong> attached.</p><p>Valid until: {$proposal['valid_until']}</p>";
        return $this->send($proposal['client_email'], $subject, $body, $pdfPath);
    }

    public function sendRenewalReminder(array $item, string $type)
    {
        $name    = $type === 'domain' ? $item['domain_name'] : ($item['provider'] . ' — ' . ($item['package'] ?? ''));
        $days    = (int)ceil((strtotime($item['expiry_date']) - time()) / 86400);
        $subject = "Renewal Reminder: $name expires in $days days";
        $body    = "<h3>Dear {$item['client_name']},</h3><p>Your <strong>$type</strong> <strong>$name</strong> expires on <strong>{$item['expiry_date']}</strong> ({$days} days).</p><p>Renewal Cost: <strong>₹{$item['renewal_cost']}</strong></p><p>Please contact us to renew.</p>";
        return $this->send($item['client_email'], $subject, $body);
    }

    public function sendClientWelcome($to, $clientName, $password)
    {
        $company = $this->settings['company_name'] ?? 'NGWebD Consulting Pvt. Ltd.';
        $portalUrl = base_url('login');

        $subject = "Welcome to {$company} Client Portal";

        $body = '
        <h2 style="margin-top:0;color:#111827;">Welcome, ' . esc($clientName) . ' 👋</h2>

        <p>Your client account has been created successfully.</p>

        <p>You can now login to your client portal using the details below:</p>

        <table width="100%" cellpadding="0" cellspacing="0" style="
            background:#f9fafb;
            border:1px solid #e5e7eb;
            border-radius:12px;
            padding:18px;
            margin:20px 0;
        ">
            <tr>
                <td style="padding:8px 0;color:#6b7280;">Login URL</td>
                <td style="padding:8px 0;text-align:right;">
                    <a href="' . $portalUrl . '">' . $portalUrl . '</a>
                </td>
            </tr>
            <tr>
                <td style="padding:8px 0;color:#6b7280;">Email</td>
                <td style="padding:8px 0;text-align:right;"><strong>' . esc($to) . '</strong></td>
            </tr>
            <tr>
                <td style="padding:8px 0;color:#6b7280;">Temporary Password</td>
                <td style="padding:8px 0;text-align:right;"><strong>' . esc($password) . '</strong></td>
            </tr>
        </table>

        <p style="text-align:center;margin:28px 0;">
            <a href="' . $portalUrl . '" style="
                background:#4f46e5;
                color:#ffffff;
                text-decoration:none;
                padding:13px 28px;
                border-radius:30px;
                font-weight:600;
                display:inline-block;
            ">
                Login to Client Portal
            </a>
        </p>

        <p style="color:#6b7280;font-size:14px;">
            For security reasons, please change your password after your first login.
        </p>

        <p>Regards,<br><strong>' . $company . ' Team</strong></p>
    ';

        return $this->send($to, $subject, $body);
    }

    protected function wrap($content)
    {
        $company = $this->settings['company_name'] ?? 'NGWebD Consulting Pvt. Ltd.';
        $website = $this->settings['company_website'] ?? 'https://ngwebd.com';
        $email   = $this->settings['company_email'] ?? 'info@ngwebd.com';
        $phone   = $this->settings['company_phone'] ?? '+91 9593026451';
        $address = $this->settings['company_address'] ?? 'Kolkata, West Bengal, India';
        $logo    = !empty($this->settings['company_logo'])
            ? base_url($this->settings['company_logo'])
            : base_url('assets/images/logo/logo.png');
        $year    = date('Y');

        return '
        <!DOCTYPE html>
        <html lang="en">
        <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <meta name="color-scheme" content="light">
        <title>' . $company . '</title>
        <style>
            @media only screen and (max-width:600px){
                .wrap-card{ width:100% !important; border-radius:0 !important; }
                .wrap-pad{ padding-left:20px !important; padding-right:20px !important; }
            }
        </style>
        </head>
    
        <body style="margin:0;padding:0;background:#eef1f6;font-family:-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#374151;">
    
        <table width="100%" cellpadding="0" cellspacing="0" bgcolor="#eef1f6">
        <tr>
        <td align="center" style="padding:32px 12px;">
    
        <table width="600" cellpadding="0" cellspacing="0" class="wrap-card" style="
        max-width:600px;
        background:#ffffff;
        border-radius:18px;
        overflow:hidden;
        box-shadow:0 8px 28px rgba(17,24,39,.08);
        ">
    
        <!-- Header -->
        <tr>
        <td align="center" style="
        background-color:#4f46e5;
        background-image:linear-gradient(135deg,#4f46e5 0%,#0ea5e9 100%);
        padding:36px 24px 30px;
        ">
    
        <table cellpadding="0" cellspacing="0" align="center">
        <tr>
        <td align="center" style="
        background:#ffffff;
        width:72px;
        height:72px;
        border-radius:18px;
        box-shadow:0 6px 16px rgba(0,0,0,.15);
        ">
        <img src="' . $logo . '" alt="' . $company . '" style="max-height:42px;max-width:42px;display:block;margin:15px auto;">
        </td>
        </tr>
        </table>
    
        <div style="
        font-size:23px;
        font-weight:700;
        color:#ffffff;
        margin-top:18px;
        letter-spacing:.2px;
        ">
        ' . $company . '
        </div>
    
        <div style="
        display:inline-block;
        margin-top:10px;
        padding:5px 14px;
        background:rgba(255,255,255,.16);
        border-radius:20px;
        font-size:11px;
        font-weight:600;
        letter-spacing:1px;
        text-transform:uppercase;
        color:#e0ecff;
        ">
        We Build Your Digital Future
        </div>
    
        </td>
        </tr>
    
        <!-- Body -->
        <tr>
        <td class="wrap-pad" style="
        padding:34px 32px 8px;
        font-size:15.5px;
        line-height:1.75;
        color:#374151;
        ">
    
        ' . $content . '
    
        </td>
        </tr>
    
        <!-- Button -->
        <tr>
        <td align="center" class="wrap-pad" style="padding:22px 32px 30px;">
    
        <a href="' . $website . '" style="
        background-color:#4f46e5;
        background-image:linear-gradient(135deg,#4f46e5 0%,#0ea5e9 100%);
        color:#ffffff;
        text-decoration:none;
        padding:13px 34px;
        display:inline-block;
        border-radius:30px;
        font-weight:600;
        font-size:14px;
        box-shadow:0 6px 16px rgba(79,70,229,.3);
        ">
        Visit Website &rarr;
        </a>
    
        </td>
        </tr>
    
        <!-- Divider -->
        <tr>
        <td class="wrap-pad" style="padding:0 32px;">
        <hr style="border:none;border-top:1px solid #eef0f3;margin:0;">
        </td>
        </tr>
    
        <!-- Contact -->
        <tr>
        <td class="wrap-pad" style="padding:26px 32px 10px;">
    
        <table width="100%" cellpadding="0" cellspacing="0">
    
        <tr>
        <td style="font-size:13px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:#9ca3af;padding-bottom:14px;">
        Get in Touch
        </td>
        </tr>
    
        <tr>
        <td style="padding-bottom:10px;">
        <table cellpadding="0" cellspacing="0">
        <tr>
        <td style="width:30px;height:30px;background:#eef2ff;border-radius:50%;text-align:center;font-size:14px;line-height:30px;">📧</td>
        <td style="padding-left:10px;font-size:14px;color:#374151;">' . $email . '</td>
        </tr>
        </table>
        </td>
        </tr>
    
        <tr>
        <td style="padding-bottom:10px;">
        <table cellpadding="0" cellspacing="0">
        <tr>
        <td style="width:30px;height:30px;background:#ecfdf5;border-radius:50%;text-align:center;font-size:14px;line-height:30px;">📞</td>
        <td style="padding-left:10px;font-size:14px;color:#374151;">' . $phone . '</td>
        </tr>
        </table>
        </td>
        </tr>
    
        <tr>
        <td>
        <table cellpadding="0" cellspacing="0">
        <tr>
        <td style="width:30px;height:30px;background:#fdf4ff;border-radius:50%;text-align:center;font-size:14px;line-height:30px;">📍</td>
        <td style="padding-left:10px;font-size:14px;color:#374151;">' . $address . '</td>
        </tr>
        </table>
        </td>
        </tr>
    
        </table>
    
        </td>
        </tr>
    
        <!-- Footer -->
        <tr>
        <td style="
        background:#f9fafb;
        padding:22px 28px;
        text-align:center;
        font-size:12px;
        line-height:1.7;
        color:#9ca3af;
        border-top:1px solid #f1f2f4;
        ">
    
        <strong style="color:#4b5563;">' . $company . '</strong><br>
    
        ' . $address . '<br><br>
    
        &copy; ' . $year . ' ' . $company . '. All rights reserved.<br>
    
        <span style="color:#b5bac3;">
        This is an automated email. Please do not reply.
        </span>
    
        </td>
        </tr>
    
        </table>
    
        </td>
        </tr>
        </table>
    
        </body>
        </html>';
    }
}
