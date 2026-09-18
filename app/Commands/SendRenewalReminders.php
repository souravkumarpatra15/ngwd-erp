<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\CronService;

class SendRenewalReminders extends BaseCommand
{
    protected $group       = 'ngwd';
    protected $name        = 'ngwd:renewal-reminders';
    protected $description = 'Send domain and hosting renewal reminders';

    public function run(array $params)
    {
        $res = (new CronService())->runJob('renewal');
        CLI::write($res['message'], $res['ran'] ? 'green' : 'yellow');
    }
}
