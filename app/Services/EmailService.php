<?php
namespace App\Services;
use App\Models\SettingModel;

class EmailService
{
    protected $email;
    protected $settings;

    public function __construct() {
        $this->settings = (new SettingModel())->getAllSettings();
        $this->email    = \Config\Services::email();
        $this->email->initialize([
            'protocol'  => 'smtp',
            'SMTPHost'  => $this->settings['smtp_host'] ?? '',
            'SMTPUser'  => $this->settings['smtp_username'] ?? '',
            'SMTPPass'  => $this->settings['smtp_password'] ?? '',
            'SMTPPort'  => (int)($this->settings['smtp_port'] ?? 587),
            'SMTPCrypto'=> $this->settings['smtp_encryption'] ?? 'tls',
            'mailType'  => 'html',
            'charset'   => 'utf-8',
        ]);
    }

    public function send($to, $subject, $message, $attachment = null) {
        try {
            $this->email->setFrom($this->settings['company_email'] ?? 'noreply@ngwebd.com', $this->settings['company_name'] ?? 'NGWebD Consulting');
            $this->email->setTo($to);
            $this->email->setSubject($subject);
            $this->email->setMessage($this->wrap($message));
            if ($attachment) $this->email->attach($attachment);
            return $this->email->send();
        } catch (\Exception $e) { log_message('error','Email: '.$e->getMessage()); return false; }
    }

    public function sendInvoice(array $invoice, string $pdfPath) {
        $subject = "Invoice {$invoice['invoice_number']} from {$this->settings['company_name']}";
        $body    = "<h3>Dear {$invoice['client_name']},</h3><p>Please find your invoice <strong>{$invoice['invoice_number']}</strong> attached.</p><p>Amount: <strong>₹".number_format($invoice['total'],2)."</strong><br>Due: {$invoice['due_date']}</p><p>Thank you!</p>";
        return $this->send($invoice['client_email'], $subject, $body, $pdfPath);
    }

    public function sendProposal(array $proposal, string $pdfPath) {
        $subject = "Proposal: {$proposal['title']}";
        $body    = "<h3>Dear {$proposal['client_name']},</h3><p>Please find our proposal for <strong>{$proposal['title']}</strong> attached.</p><p>Valid until: {$proposal['valid_until']}</p>";
        return $this->send($proposal['client_email'], $subject, $body, $pdfPath);
    }

    public function sendRenewalReminder(array $item, string $type) {
        $name    = $type === 'domain' ? $item['domain_name'] : ($item['provider'].' — '.($item['package']??''));
        $days    = (int)ceil((strtotime($item['expiry_date']) - time()) / 86400);
        $subject = "Renewal Reminder: $name expires in $days days";
        $body    = "<h3>Dear {$item['client_name']},</h3><p>Your <strong>$type</strong> <strong>$name</strong> expires on <strong>{$item['expiry_date']}</strong> ({$days} days).</p><p>Renewal Cost: <strong>₹{$item['renewal_cost']}</strong></p><p>Please contact us to renew.</p>";
        return $this->send($item['client_email'], $subject, $body);
    }

    protected function wrap($content) {
        $co = $this->settings['company_name'] ?? 'NGWebD Consulting';
        return "<!DOCTYPE html><html><body style='font-family:Arial,sans-serif;color:#333;max-width:600px;margin:0 auto'><div style='background:#0d6efd;padding:20px;text-align:center'><h2 style='color:#fff;margin:0'>{$co}</h2></div><div style='padding:30px'>{$content}</div><div style='background:#f8f9fa;padding:15px;text-align:center;font-size:12px;color:#999'>&copy; ".date('Y')." {$co}</div></body></html>";
    }
}
