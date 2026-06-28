# NGWD ERP — Cron Jobs Setup

## Commands Present (✓ complete)

All three commands are implemented and working:

| Command | File | Purpose |
|---|---|---|
| `ngwd:followup-reminders` | `app/Commands/SendFollowUpReminders.php` | Creates in-app notifications for leads with today's follow-up date |
| `ngwd:payment-reminders` | `app/Commands/SendPaymentReminders.php` | Emails + WhatsApp for overdue invoices; marks them `overdue` |
| `ngwd:renewal-reminders` | `app/Commands/SendRenewalReminders.php` | Domain + hosting expiry reminders at 30/15/7/3/1 days |

---

## Server Crontab Setup

SSH into your server and run:
```bash
sudo crontab -e -u www-data
```

Paste these lines (adjust PHP path with `which php` if needed):
```cron
# NGWD ERP Cron Jobs
# Runs daily at Indian Standard Time (IST = UTC+5:30)

# Follow-up reminders — 8:00 AM IST = 02:30 UTC
30 2 * * * /usr/bin/php /var/www/html/ngwd-erp/spark ngwd:followup-reminders >> /var/log/ngwd-followup.log 2>&1

# Payment reminders — 9:00 AM IST = 03:30 UTC
30 3 * * * /usr/bin/php /var/www/html/ngwd-erp/spark ngwd:payment-reminders >> /var/log/ngwd-payment.log 2>&1

# Renewal reminders — 9:30 AM IST = 04:00 UTC
0 4 * * * /usr/bin/php /var/www/html/ngwd-erp/spark ngwd:renewal-reminders >> /var/log/ngwd-renewal.log 2>&1
```

## Test manually first:
```bash
cd /var/www/html/ngwd-erp
php spark ngwd:followup-reminders
php spark ngwd:payment-reminders
php spark ngwd:renewal-reminders
```

## Create log files:
```bash
sudo touch /var/log/ngwd-followup.log /var/log/ngwd-payment.log /var/log/ngwd-renewal.log
sudo chown www-data:www-data /var/log/ngwd-*.log
```

## Note on NotificationService fix:
`SendFollowUpReminders.php` calls `$ns->create(1, ...)` — the `1` is hardcoded.
Replace with `0` to notify all admin users:
```php
// In SendFollowUpReminders.php — change line:
$ns->create(1,'follow_up_due',...);
// To:
$ns->create(0,'follow_up_due',...);
```
The updated `NotificationService.php` (included in this package) handles `0` by notifying all admins.
