<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\DomainModel;
use App\Models\HostingModel;
use App\Services\EmailService;
use App\Services\WhatsAppService;

class SendRenewalReminders extends BaseCommand
{
    protected $group       = 'ngwd';
    protected $name        = 'ngwd:renewal-reminders';
    protected $description = 'Send domain and hosting renewal reminders';

    public function run(array $params)
    {
        $db     = \Config\Database::connect();
        $email  = new EmailService();
        $wa     = new WhatsAppService();
        $dm     = new DomainModel();
        $hm     = new HostingModel();
        $days   = [30, 15, 7, 3, 1];
        $today  = date('Y-m-d');

        // Domains
        $domains = $db->table('domains')->select('domains.*, clients.name as client_name, clients.email as client_email, clients.whatsapp as client_whatsapp')->join('clients','clients.id = domains.client_id')->where('domains.status !=','expired')->get()->getResultArray();
        foreach ($domains as $d) {
            $left = (int) ceil((strtotime($d['expiry_date']) - time()) / 86400);
            if ($left <= 0) { $dm->update($d['id'],['status'=>'expired']); continue; }
            if ($left <= 30) $dm->update($d['id'],['status'=>'expiring_soon']);
            if (in_array($left,$days)) {
                $email->sendRenewalReminder($d,'domain');
                $msg = "⚠️ Domain Renewal Reminder\n\nDear {$d['client_name']},\nYour domain *{$d['domain_name']}* expires in *{$left} days* on {$d['expiry_date']}.\nRenewal Cost: ₹{$d['renewal_cost']}\n\nPlease contact us to renew.\nNGWebD Consulting";
                $wa->sendMessage($d['client_whatsapp'], $msg);
                $dm->update($d['id'],['last_reminder_sent'=>date('Y-m-d H:i:s')]);
                CLI::write("Domain reminder: {$d['domain_name']} ({$left} days)", 'green');
            }
        }

        // Hostings
        $hostings = $db->table('hostings')->select('hostings.*, clients.name as client_name, clients.email as client_email, clients.whatsapp as client_whatsapp')->join('clients','clients.id = hostings.client_id')->where('hostings.status !=','expired')->get()->getResultArray();
        foreach ($hostings as $h) {
            $left = (int) ceil((strtotime($h['expiry_date']) - time()) / 86400);
            if ($left <= 0) { $hm->update($h['id'],['status'=>'expired']); continue; }
            if ($left <= 30) $hm->update($h['id'],['status'=>'expiring_soon']);
            if (in_array($left,$days)) {
                $email->sendRenewalReminder($h,'hosting');
                $hm->update($h['id'],['last_reminder_sent'=>date('Y-m-d H:i:s')]);
                CLI::write("Hosting reminder: {$h['provider']} ({$left} days)", 'green');
            }
        }

        CLI::write('Renewal reminders complete!', 'green');
    }
}
