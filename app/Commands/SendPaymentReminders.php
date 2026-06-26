<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\EmailService;
use App\Services\WhatsAppService;

class SendPaymentReminders extends BaseCommand
{
    protected $group       = 'ngwd';
    protected $name        = 'ngwd:payment-reminders';
    protected $description = 'Send payment reminders for overdue invoices';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        $invoices = $db->table('invoices')->select('invoices.*, clients.name as client_name, clients.email as client_email, clients.whatsapp as client_whatsapp')->join('clients','clients.id = invoices.client_id','left')->where('invoices.status','overdue')->orWhere('invoices.due_date <',date('Y-m-d'))->where('invoices.status !=','paid')->get()->getResultArray();

        $email = new EmailService();
        $wa    = new WhatsAppService();

        foreach ($invoices as $inv) {
            $days = (int) ceil((time() - strtotime($inv['due_date'])) / 86400);
            // Update to overdue
            $db->table('invoices')->where('id',$inv['id'])->update(['status'=>'overdue']);

            $subject = "Payment Reminder: Invoice {$inv['invoice_number']} is Overdue";
            $body    = "<p>Dear {$inv['client_name']},</p><p>Invoice <strong>{$inv['invoice_number']}</strong> for <strong>₹".number_format($inv['balance_due'],2)."</strong> was due on {$inv['due_date']} ({$days} days ago).</p><p>Please make payment at your earliest convenience.</p>";
            $email->send($inv['client_email'], $subject, $body);

            $msg = "⚠️ Payment Reminder\n\nDear {$inv['client_name']},\nInvoice *{$inv['invoice_number']}* for ₹".number_format($inv['balance_due'],2)." was due on {$inv['due_date']}.\n\nPlease make payment ASAP.\nNGWebD Consulting";
            $wa->sendMessage($inv['client_whatsapp'], $msg);
            CLI::write("Payment reminder: {$inv['invoice_number']}", 'yellow');
        }
        CLI::write(count($invoices).' payment reminders sent', 'green');
    }
}
