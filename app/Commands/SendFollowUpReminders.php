<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\NotificationService;
use App\Services\WhatsAppNotificationService;

class SendFollowUpReminders extends BaseCommand
{
    protected $group       = 'ngwd';
    protected $name        = 'ngwd:followup-reminders';
    protected $description = 'Send follow-up reminders for leads';

    public function run(array $params)
    {
        $db    = \Config\Database::connect();
        $leads = $db->table('leads')->where('follow_up_date',date('Y-m-d'))->whereNotIn('status',['converted','lost'])->where('deleted_at IS NULL')->get()->getResultArray();
        $ns    = new NotificationService();
        foreach ($leads as $l) {
            $ns->create(0,'follow_up_due','Follow-Up Due',"Follow up with {$l['name']} ({$l['mobile']})",$l['id'],'lead');
            // WhatsApp template: erp_lead_followup (best effort)
            try {
                $waPhone = trim((string)($l['whatsapp'] ?? '')) !== '' ? (string)$l['whatsapp'] : (string)($l['mobile'] ?? '');
                if ($waPhone !== '') (new WhatsAppNotificationService())->leadFollowup($waPhone, (string)$l['name'], date('d M Y', strtotime((string)$l['follow_up_date'])));
            } catch (\Throwable $e) { log_message('error', 'leadFollowup WA failed: ' . $e->getMessage()); }
            CLI::write("Follow-up: {$l['name']}", 'yellow');
        }
        CLI::write(count($leads).' follow-up reminders created', 'green');
    }
}
