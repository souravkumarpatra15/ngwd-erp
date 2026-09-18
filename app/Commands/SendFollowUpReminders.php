<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\CronService;

class SendFollowUpReminders extends BaseCommand
{
    protected $group       = 'ngwd';
    protected $name        = 'ngwd:followup-reminders';
    protected $description = 'Send follow-up reminders for leads';

    public function run(array $params)
    {
        $res = (new CronService())->runJob('followup');
        CLI::write($res['message'], $res['ran'] ? 'green' : 'yellow');
    }
}
