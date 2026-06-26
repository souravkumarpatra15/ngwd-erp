<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\NotificationService;

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
            $ns->create(1,'follow_up_due','Follow-Up Due',"Follow up with {$l['name']} ({$l['mobile']})",$l['id'],'lead');
            CLI::write("Follow-up: {$l['name']}", 'yellow');
        }
        CLI::write(count($leads).' follow-up reminders created', 'green');
    }
}
