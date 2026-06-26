# NGWD ERP — Pending Files & Setup Guide

Complete documentation for all missing files, setup steps, and cron job configuration.

---

## 1. First-time Setup (P1 — Critical)

### Step 1 — Generate encryption key
```bash
cd /var/www/ngwd-erp
php spark key:generate
```
Copy the output and paste it into `.env`:
```
encryption.key = hex2bin(PASTE_KEY_HERE)
```

### Step 2 — Configure `.env`
```bash
cp .env.example .env
nano .env
```
Fill in:
- `database.default.password` — your MySQL password
- `app.baseURL` — your actual domain

### Step 3 — Create required writable directories
```bash
mkdir -p writable/pdfs
mkdir -p writable/uploads/avatars
chmod -R 775 writable/
chown -R www-data:www-data writable/
```

### Step 4 — Run migrations and seed
```bash
php spark migrate
php spark db:seed InitialSeeder
```

### Step 5 — First login
```
URL:      https://yourdomain.com/login
Email:    admin@ngwebd.com
Password: Admin@1234   ← change this immediately!
```
After login → go to **Admin → My Profile → Change Password**.

---

## 2. New Files Added (by Priority)

### P1 — Critical fixes
| File | Purpose |
|---|---|
| `writable/pdfs/.gitkeep` | Creates the missing `pdfs/` directory (tracked by git) |
| `.env.example` | Full environment template with comments |
| `Database/Seeds/InitialSeeder.php` | Idempotent seeder — safe to re-run, skips existing admin |

### P2 — User management & auth
| File | Purpose |
|---|---|
| `Controllers/Admin/UserManagementController.php` | Full CRUD for admin/manager users |
| `Controllers/Admin/ProfileController.php` | Admin profile edit + password change |
| `Controllers/Auth/ForgotPasswordController.php` | Forgot + reset password (token-based) |
| `Database/Migrations/2024-06-01-000001_AddPasswordResetsAndAdminRoles.php` | `password_resets` table + expanded `users.role` enum |
| `Views/admin/users/index.php` | User list with activate/deactivate/delete |
| `Views/admin/users/create.php` | Create user form |
| `Views/admin/users/edit.php` | Edit user form |
| `Views/admin/profile/index.php` | Profile + change password combined page |
| `Views/auth/forgot_password.php` | Forgot password form |
| `Views/auth/reset_password.php` | Password reset form |

### P3 — Missing controller methods
| File | What was added |
|---|---|
| `Controllers/Admin/AgreementController.php` | `delete()`, `updateStatus()` |
| `Controllers/Admin/InvoiceController.php` | `delete()`, `void()` |

### P4 — Fattened models
| File | What was added |
|---|---|
| `Models/UserModel.php` | `admins()`, `active()`, `clients()`, `findByEmail()`, `syncClientUser()`, `touchLogin()` |
| `Models/TaskModel.php` | `getAllWithDetails()`, `getWithDetails()`, `getKanbanBoard()`, `getOverdue()`, `countByStatus()` |
| `Models/TicketModel.php` | `getAllWithDetails()`, `getWithReplies()`, `countOpen()`, `countByPriority()`, `getForClient()` |
| `Models/MilestoneModel.php` | `getAllWithDetails()`, `getForProject()`, `getOverdue()`, `getProjectSummary()`, `getUpcoming()` |
| `Models/ActivityModel.php` | `getRecent()`, `getForRecord()`, `getForUser()`, `ActivityModel::log()` (static helper) |

### Docs
| File | Purpose |
|---|---|
| `docs/ROUTES_TO_ADD.php` | Exact route lines to paste into `Config/Routes.php` |
| `docs/SETUP.md` | This file |

---

## 3. Cron Job Setup

The ERP ships with three CLI commands. Add these to your server's crontab:

```bash
crontab -e -u www-data
```

Paste these lines:
```cron
# Run every day at 8:00 AM IST (02:30 UTC)
30 2 * * * /usr/bin/php /var/www/ngwd-erp/spark ngwd:renewal-reminders >> /var/log/ngwd-renewal.log 2>&1

# Run every day at 9:00 AM IST (03:30 UTC)
30 3 * * * /usr/bin/php /var/www/ngwd-erp/spark ngwd:payment-reminders >> /var/log/ngwd-payment.log 2>&1

# Run every day at 9:30 AM IST (04:00 UTC)
0 4 * * * /usr/bin/php /var/www/ngwd-erp/spark ngwd:followup-reminders >> /var/log/ngwd-followup.log 2>&1
```

### Test commands manually first:
```bash
cd /var/www/ngwd-erp
php spark ngwd:renewal-reminders
php spark ngwd:payment-reminders
php spark ngwd:followup-reminders
```

---

## 4. WhatsApp API Version Fix

`app/Services/WhatsAppService.php` uses `v17.0` which is outdated. Update line:
```php
// Change this:
$url = "https://graph.facebook.com/v17.0/{$this->phoneNumberId}/messages";

// To this (v21.0 is current as of 2025):
$url = "https://graph.facebook.com/v21.0/{$this->phoneNumberId}/messages";
```

---

## 5. Fix NotificationService Hardcoded User

`app/Services/NotificationService.php` sends all notifications to user_id = 1.

Change:
```php
public function create(int $userId, ...) {
```
The method signature already accepts `$userId` — the bug is in callers like `SendFollowUpReminders.php`:
```php
// Change this:
$ns->create(1, 'follow_up_due', ...);

// To this (pass the lead's assigned_to or use session):
$assignedTo = $l['created_by'] ?? 1;
$ns->create($assignedTo, 'follow_up_due', ...);
```

---

## 6. Database Summary

Tables created by migration:
`users`, `settings`, `leads`, `lead_activities`, `clients`, `projects`, `milestones`, `tasks`,
`agreements`, `proposals`, `invoices`, `invoice_items`, `payments`, `razorpay_orders`,
`domains`, `hostings`, `documents`, `tickets`, `ticket_replies`, `activities`, `notifications`, `ci_sessions`

New table added by pending migration:
`password_resets` (email, token, expires_at, used)
