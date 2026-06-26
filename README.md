# NGWebD Consulting ERP System
## Production-Ready ERP built with CodeIgniter 4

---

## Quick Setup (5 Steps)

### Step 1 — Upload & Install
```bash
cd /var/www/html
# Upload/clone the project folder as ngwd-erp
cd ngwd-erp
composer install
```

### Step 2 — Configure
```bash
cp env .env
nano .env
# Fill in: database credentials, SMTP, Razorpay, WhatsApp
```

### Step 3 — Database
```bash
# Create MySQL database: ngwd_erp
php spark key:generate
php spark migrate
php spark db:seed InitialSeeder
```

### Step 4 — Permissions
```bash
chmod -R 755 writable/
chmod -R 755 public/uploads/
mkdir -p public/uploads
mkdir -p writable/pdfs
```

### Step 5 — Web Server

**Nginx:**
```nginx
server {
    listen 80;
    server_name erp.yourdomain.com;
    root /var/www/html/ngwd-erp/public;
    index index.php;
    location / { try_files $uri $uri/ /index.php?$query_string; }
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    client_max_body_size 50M;
}
```

**Apache:** Already configured via `public/.htaccess`

---

## Default Login
- URL: http://yourdomain.com/login
- Email: admin@ngwebdconsulting.com
- Password: **Admin@123**
- ⚠️ Change password immediately after first login!

---

## Cron Jobs (add to crontab: `crontab -e`)
```
# Follow-up reminders — daily 8 AM
0 8 * * * /usr/bin/php /var/www/html/ngwd-erp/spark ngwd:followup-reminders

# Renewal reminders — daily 9 AM
0 9 * * * /usr/bin/php /var/www/html/ngwd-erp/spark ngwd:renewal-reminders

# Payment reminders — daily 10 AM
0 10 * * * /usr/bin/php /var/www/html/ngwd-erp/spark ngwd:payment-reminders
```

---

## Razorpay Webhook
Add this URL in Razorpay Dashboard → Settings → Webhooks:
```
https://erp.yourdomain.com/webhook/razorpay
```
Enable event: **payment.captured**

---

## Module Overview

| Module | URL | Description |
|--------|-----|-------------|
| Dashboard | /admin/dashboard | Overview stats & charts |
| Leads | /admin/leads | Lead CRM with follow-ups |
| Clients | /admin/clients | Client profiles |
| Projects | /admin/projects | Project management |
| Milestones | /admin/milestones | Payment milestones |
| Proposals | /admin/proposals | PDF proposals |
| Agreements | /admin/agreements | Legal agreements |
| Invoices | /admin/invoices | GST/Non-GST invoices |
| Payments | /admin/payments | Payment tracking |
| Domains | /admin/domains | Domain renewals |
| Hosting | /admin/hostings | Hosting renewals |
| Documents | /admin/documents | File storage |
| Tasks | /admin/tasks | Task & Kanban |
| Tickets | /admin/tickets | Support tickets |
| Reports | /admin/reports | All reports + export |
| Settings | /admin/settings | System configuration |
| Client Portal | /portal/dashboard | Client self-service |

---

## Tech Stack
- **Backend:** CodeIgniter 4, PHP 8.3, MySQL 8
- **Frontend:** Bootstrap 5, jQuery, DataTables, Chart.js, Select2
- **PDF:** DomPDF
- **Payments:** Razorpay
- **Email:** SMTP (Gmail/any provider)
- **WhatsApp:** Meta Cloud API

---

## File Structure
```
ngwd-erp/
├── app/
│   ├── Config/          # Routes, Filters
│   ├── Controllers/     # Auth, Admin, Client, Api
│   ├── Models/          # 18 Models
│   ├── Views/           # Admin + Client + PDF views
│   ├── Services/        # Email, WhatsApp, PDF, Payment
│   ├── Filters/         # Auth middleware
│   ├── Commands/        # Cron job commands
│   ├── Helpers/         # ERP helper functions
│   └── Database/        # Migrations + Seeds
├── public/
│   ├── assets/          # CSS, JS, Images
│   ├── uploads/         # File uploads
│   └── index.php
├── writable/
│   ├── pdfs/            # Generated PDFs
│   └── logs/
├── .env                 # Configuration (DO NOT commit)
├── composer.json
└── README.md
```

---

## Security Checklist
- [ ] Change default admin password
- [ ] Set `CI_ENVIRONMENT = production` in `.env`
- [ ] Configure SSL/HTTPS
- [ ] Keep `.env` out of version control
- [ ] Set up automated database backups
- [ ] Configure firewall (allow only 80, 443, 22)

---

© 2024 NGWebD Consulting Pvt. Ltd.
# ngwd-erp
