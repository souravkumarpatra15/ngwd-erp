# NGWD ERP Fix Package — Deployment Guide

## Files and Where to Place Them

### Controllers (→ app/Controllers/)

| File in zip | Destination |
|---|---|
| `Controllers/Admin/AgreementController.php` | `app/Controllers/Admin/AgreementController.php` |
| `Controllers/Admin/InvoiceController.php` | `app/Controllers/Admin/InvoiceController.php` |
| `Controllers/Admin/InvoiceModel.php` | `app/Models/InvoiceModel.php` ← note: Models folder |
| `Controllers/Admin/DocumentController.php` | `app/Controllers/Admin/DocumentController.php` |
| `Controllers/Admin/PaymentController.php` | `app/Controllers/Admin/PaymentController.php` |
| `Controllers/Admin/MilestoneController.php` | `app/Controllers/Admin/MilestoneController.php` |
| `Controllers/Client/PaymentController.php` | `app/Controllers/Client/PaymentController.php` |
| `Controllers/NotificationService.php` | `app/Services/NotificationService.php` ← note: Services folder |

### Views (→ app/Views/)

| File in zip | Destination |
|---|---|
| `views/admin/agreements/index.php` | `app/Views/admin/agreements/index.php` |
| `views/admin/agreements/edit.php` | `app/Views/admin/agreements/edit.php` |
| `views/admin/documents/index.php` | `app/Views/admin/documents/index.php` |
| `views/admin/payments/create.php` | `app/Views/admin/payments/create.php` |
| `views/client/checkout.php` | `app/Views/client/payments/checkout.php` |

### Routes (→ app/Config/Routes.php)

Open `app/Config/Routes.php` and add these 3 lines inside the admin group,
after the existing invoice routes:

```php
$routes->get('invoices/by-client/(:num)', 'Admin\InvoiceController::byClient/$1');
$routes->get('milestones/by-project/(:num)', 'Admin\MilestoneController::byProject/$1');
$routes->get('payments/milestones-by-project/(:num)', 'Admin\PaymentController::milestonesByProject/$1');
```

---

## What Each Fix Does

### AgreementController + agreements/index.php + agreements/edit.php
- `index()` was returning a 10-line stub with placeholder text. Now passes full list with client + project names joined.
- `edit.php` was also a 10-line stub. Now renders all 8 agreement section fields.
- Project dropdown in create/edit is dynamic — changes when client is selected.

### InvoiceController + InvoiceModel
- `balance_due` was missing from `InvoiceModel::allowedFields` so writes were silently ignored.
- `store()` now correctly calculates and saves `balance_due = total - paid_amount`.
- `update()` now recalculates `balance_due` after saving so it stays in sync.
- Line items use correct column names: `unit_price` + `total` (not `rate` + `amount`).
- Added `byClient()` AJAX method for payment create dropdown.

### DocumentController + documents/index.php
- `index()` wasn't passing `$clients` to the view — upload modal client dropdown was always empty.
- Now joins client + project names for the table display.
- Upload modal has dynamic project dropdown (changes by selected client).
- Category options now match the exact DB enum values.

### Admin/PaymentController
- `method` enum in store() validation now matches DB exactly: `razorpay, upi, bank_transfer, cash, cheque` (no `neft/rtgs/other`).
- After recording payment: updates invoice `paid_amount` + `balance_due` + `status`; updates project `total_paid`; marks milestone `paid`.
- Added `milestonesByProject()` AJAX for the milestone dropdown.

### payments/create.php
- Invoice dropdown now loads correctly via `admin/invoices/by-client/{id}`.
- Selecting an invoice auto-fills the amount field with the balance due.
- Milestone dropdown is dynamic by project.
- Method options match DB enum exactly.

### Client/PaymentController
- `verify()` now reads `balance_due` directly from DB (correct column).
- Inserts payment with all correct field names including `razorpay_order_id` + `razorpay_payment_id`.
- Updates invoice `balance_due` to 0 after successful payment.
- Updates project `total_paid`.
- Marks milestone `paid` if linked.

### checkout.php (client)
- Uses `$razorpay_order['id']` correctly (was using undefined `$order_id`).
- `fetch()` POST sends JSON to `portal/pay/verify` (no CSRF header needed — CI4 excludes JSON content-type from CSRF).
- Button is disabled while Razorpay is opening/verifying.
- On success, redirects to invoice detail page.

### NotificationService
- `create(1, ...)` was hardcoded to always notify user_id=1.
- Now accepts any `$userId`. Pass `0` to notify all admins/superadmins.
- See `CRON_SETUP.md` for how to update the cron commands to use `0`.

---

## Cron Jobs
See `CRON_SETUP.md` for the exact crontab lines to add.
All 3 commands are already implemented and working — just need to be added to crontab.
