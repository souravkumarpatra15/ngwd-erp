<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\CronService;

/**
 * Code-driven cron trigger. Hit by the admin panel itself in the background
 * (see layouts/admin.php) so no server crontab or cPanel cron is needed.
 * CronService guarantees each job runs at most once per day.
 */
class CronController extends BaseController
{
    public function run()
    {
        $results = (new CronService())->runDue();
        return $this->response->setJSON(['status' => 'success', 'jobs' => $results]);
    }

    public function status()
    {
        return $this->response->setJSON(['status' => 'success', 'jobs' => (new CronService())->status()]);
    }
}
