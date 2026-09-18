<?php

namespace App\Services;

use App\Models\DomainModel;
use App\Models\HostingModel;
use App\Models\SettingModel;

/**
 * Code-driven cron — no server crontab / cPanel cron required.
 *
 * How it fires (any one is enough):
 *  - Admin panel auto-triggers GET admin/cron/run in the background on page loads.
 *  - CLI still works: php spark ngwd:followup-reminders (etc.).
 *  - Any external pinger can hit admin/cron/run while an admin session exists,
 *    or use the spark commands from SSH.
 *
 * Each job runs at most once per calendar day (IST), after its scheduled time.
 * State lives in the settings table (cron_last_*, cron_*_enabled) — no migration.
 * Overlaps are prevented with a file lock in writable/.
 */
class CronService
{
    public const JOBS = [
        'followup' => ['time' => '08:00', 'label' => 'Follow-up reminders'],
        'payment'  => ['time' => '09:00', 'label' => 'Payment reminders'],
        'renewal'  => ['time' => '09:30', 'label' => 'Renewal reminders'],
    ];

    protected SettingModel $settings;

    public function __construct()
    {
        $this->settings = new SettingModel();
    }

    /**
     * Run every job that is due. Returns per-job results:
     * ['followup' => ['ran' => bool, 'sent' => int, 'message' => string], ...]
     */
    public function runDue(): array
    {
        $out = [];
        foreach (array_keys(self::JOBS) as $job) {
            $out[$job] = $this->isDue($job) ? $this->runJob($job) : $this->skipResult($job);
        }
        return $out;
    }

    /** Force-run one job regardless of schedule (used by spark commands). */
    public function runJob(string $job): array
    {
        if (!isset(self::JOBS[$job])) return ['ran' => false, 'sent' => 0, 'message' => 'Unknown job.'];
        $lock = $this->acquireLock($job);
        if ($lock === null) return ['ran' => false, 'sent' => 0, 'message' => 'Skipped: another run in progress.'];
        try {
            @set_time_limit(0);
            $sent = match ($job) {
                'followup' => $this->runFollowups(),
                'payment'  => $this->runPayments(),
                'renewal'  => $this->runRenewals(),
            };
            $now = date('Y-m-d H:i:s');
            $this->settings->saveSetting('cron_last_' . $job, $now);
            $this->settings->saveSetting('cron_last_' . $job . '_result', (string)$sent);
            return ['ran' => true, 'sent' => $sent, 'message' => self::JOBS[$job]['label'] . ': ' . $sent . ' sent.'];
        } catch (\Throwable $e) {
            log_message('error', 'Cron job {job} failed: {message}', ['job' => $job, 'message' => $e->getMessage()]);
            return ['ran' => false, 'sent' => 0, 'message' => 'Failed: ' . $e->getMessage()];
        } finally {
            $this->releaseLock($job, $lock);
        }
    }

    /** Last-run info for visibility, e.g. on the dashboard. */
    public function status(): array
    {
        $all = $this->settings->getAllSettings();
        $out = [];
        foreach (self::JOBS as $job => $meta) {
            $out[$job] = [
                'label'   => $meta['label'],
                'time'    => $meta['time'],
                'enabled' => ($all['cron_' . $job . '_enabled'] ?? '1') === '1',
                'last'    => $all['cron_last_' . $job] ?? null,
                'result'  => $all['cron_last_' . $job . '_result'] ?? null,
            ];
        }
        return $out;
    }

    // ── Scheduling ──────────────────────────────────────────────

    protected function isDue(string $job): bool
    {
        $all = $this->settings->getAllSettings();
        if (($all['cron_' . $job . '_enabled'] ?? '1') !== '1') return false;
        if (date('H:i') < self::JOBS[$job]['time']) return false; // scheduled time not reached (IST)
        $last = substr((string)($all['cron_last_' . $job] ?? ''), 0, 10);
        return $last !== date('Y-m-d'); // already ran today
    }

    protected function skipResult(string $job): array
    {
        $all = $this->settings->getAllSettings();
        $last = $all['cron_last_' . $job] ?? null;
        return ['ran' => false, 'sent' => 0, 'message' => $last ? 'Already ran at ' . $last . '.' : 'Not due yet (runs after ' . self::JOBS[$job]['time'] . ' IST).'];
    }

    // ── Locking ─────────────────────────────────────────────────

    /** @return resource|null */
    protected function acquireLock(string $job)
    {
        $file = rtrim(WRITEPATH, '/\\') . DIRECTORY_SEPARATOR . 'cron_' . $job . '.lock';
        $fh = @fopen($file, 'c');
        if ($fh === false) return null;
        if (!flock($fh, LOCK_EX | LOCK_NB)) { fclose($fh); return null; }
        return $fh;
    }

    /** @param resource|null $fh */
    protected function releaseLock(string $job, $fh): void
    {
        if ($fh === null) return;
        flock($fh, LOCK_UN);
        fclose($fh);
        @unlink(rtrim(WRITEPATH, '/\\') . DIRECTORY_SEPARATOR . 'cron_' . $job . '.lock');
    }

    // ── Job bodies (single source of truth; spark commands delegate here) ──

    protected function runFollowups(): int
    {
        $db    = \Config\Database::connect();
        $leads = $db->table('leads')->where('follow_up_date', date('Y-m-d'))->whereNotIn('status', ['converted', 'lost'])->where('deleted_at IS NULL')->get()->getResultArray();
        $ns = new NotificationService();
        $wa = new WhatsAppNotificationService();
        $n = 0;
        foreach ($leads as $l) {
            $ns->create(0, 'follow_up_due', 'Follow-Up Due', "Follow up with {$l['name']} ({$l['mobile']})", (int)$l['id'], 'lead');
            try {
                $phone = trim((string)($l['whatsapp'] ?? '')) !== '' ? (string)$l['whatsapp'] : (string)($l['mobile'] ?? '');
                if ($phone !== '') $wa->leadFollowup($phone, (string)$l['name'], date('d M Y', strtotime((string)$l['follow_up_date'])));
            } catch (\Throwable $e) { log_message('error', 'leadFollowup WA failed: ' . $e->getMessage()); }
            $n++;
        }
        return $n;
    }

    protected function runPayments(): int
    {
        $db = \Config\Database::connect();
        $invoices = $db->table('invoices')
            ->select('invoices.*, clients.name as client_name, clients.email as client_email, clients.whatsapp as client_whatsapp')
            ->join('clients', 'clients.id = invoices.client_id', 'left')
            ->groupStart()
                ->where('invoices.status', 'overdue')
                ->orWhere('invoices.due_date <', date('Y-m-d'))
            ->groupEnd()
            ->where('invoices.status !=', 'paid')
            ->where('invoices.balance_due >', 0)
            ->get()->getResultArray();

        $email = new EmailService();
        $wa    = new WhatsAppService();
        $tpl   = new WhatsAppNotificationService();
        $n = 0;
        foreach ($invoices as $inv) {
            $days = max(1, (int) ceil((time() - strtotime((string)$inv['due_date'])) / 86400));
            $currency = strtoupper((string) ($inv['currency'] ?? 'INR'));
            $amount = $currency . ' ' . number_format((float) $inv['balance_due'], 2);

            $db->table('invoices')->where('id', $inv['id'])->where('status !=', 'paid')->update(['status' => 'overdue']);

            $subject = "Payment Reminder: Invoice {$inv['invoice_number']} is Overdue";
            $body = "<p>Dear " . htmlspecialchars((string) $inv['client_name'], ENT_QUOTES, 'UTF-8') . ",</p>"
                . "<p>Invoice <strong>" . htmlspecialchars((string) $inv['invoice_number'], ENT_QUOTES, 'UTF-8') . "</strong> for <strong>{$amount}</strong> was due on "
                . htmlspecialchars((string) $inv['due_date'], ENT_QUOTES, 'UTF-8') . " ({$days} days ago).</p>"
                . "<p>Please make payment at your earliest convenience.</p>";
            $email->send($inv['client_email'], $subject, $body);

            $msg = "⚠️ Payment Reminder\n\nDear {$inv['client_name']},\nInvoice *{$inv['invoice_number']}* for {$amount} was due on {$inv['due_date']}.\n\nPlease make payment ASAP.\nNGWebD Consulting";
            $wa->sendMessage((string)($inv['client_whatsapp'] ?? ''), $msg);
            try {
                if (trim((string)($inv['client_whatsapp'] ?? '')) !== '') {
                    $tpl->invoiceOverdue((string)$inv['client_whatsapp'], (string)($inv['client_name'] ?? ''), (string)$inv['invoice_number'], (string)$amount);
                }
            } catch (\Throwable $e) { log_message('error', 'invoiceOverdue WA failed: ' . $e->getMessage()); }
            $n++;
        }
        return $n;
    }

    protected function runRenewals(): int
    {
        $db    = \Config\Database::connect();
        $email = new EmailService();
        $wa    = new WhatsAppService();
        $dm    = new DomainModel();
        $hm    = new HostingModel();
        $days  = [30, 15, 7, 3, 1];
        $n = 0;

        $domains = $db->table('domains')->select('domains.*, clients.name as client_name, clients.email as client_email, clients.whatsapp as client_whatsapp')->join('clients', 'clients.id = domains.client_id')->where('domains.status !=', 'expired')->get()->getResultArray();
        foreach ($domains as $d) {
            $left = (int) ceil((strtotime((string)$d['expiry_date']) - time()) / 86400);
            if ($left <= 0) { $dm->update($d['id'], ['status' => 'expired']); continue; }
            if ($left <= 30) $dm->update($d['id'], ['status' => 'expiring_soon']);
            if (in_array($left, $days)) {
                $email->sendRenewalReminder($d, 'domain');
                $msg = "⚠️ Domain Renewal Reminder\n\nDear {$d['client_name']},\nYour domain *{$d['domain_name']}* expires in *{$left} days* on {$d['expiry_date']}.\nRenewal Cost: ₹{$d['renewal_cost']}\n\nPlease contact us to renew.\nNGWebD Consulting";
                $wa->sendMessage((string)($d['client_whatsapp'] ?? ''), $msg);
                $dm->update($d['id'], ['last_reminder_sent' => date('Y-m-d H:i:s')]);
                $n++;
            }
        }

        $hostings = $db->table('hostings')->select('hostings.*, clients.name as client_name, clients.email as client_email, clients.whatsapp as client_whatsapp')->join('clients', 'clients.id = hostings.client_id')->where('hostings.status !=', 'expired')->get()->getResultArray();
        foreach ($hostings as $h) {
            $left = (int) ceil((strtotime((string)$h['expiry_date']) - time()) / 86400);
            if ($left <= 0) { $hm->update($h['id'], ['status' => 'expired']); continue; }
            if ($left <= 30) $hm->update($h['id'], ['status' => 'expiring_soon']);
            if (in_array($left, $days)) {
                $email->sendRenewalReminder($h, 'hosting');
                $hm->update($h['id'], ['last_reminder_sent' => date('Y-m-d H:i:s')]);
                $n++;
            }
        }
        return $n;
    }
}
