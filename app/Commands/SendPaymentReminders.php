<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\CronService;

class SendPaymentReminders extends BaseCommand
{
    protected $group       = 'ngwd';
    protected $name        = 'ngwd:payment-reminders';
    protected $description = 'Send payment reminders for overdue invoices';

    public function run(array $params)
    {
        $res = (new CronService())->runJob('payment');
        CLI::write($res['message'], $res['ran'] ? 'green' : 'yellow');
    }
}
