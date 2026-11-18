# NGWebD ERP - Database Audit

## Overview
The application uses CodeIgniter 4's database migration system with a primary migration file `2024-01-01-000001_CreateAllTables.php` that created all core tables. Additional migrations have been added incrementally since August 2026.

## Total Tables: 28

### Core Tables (from initial migration)

#### 1. users
- **Purpose**: Core user authentication and basic profile
- **Primary Key**: id (INT, auto_increment)
- **Unique**: email
- **Columns**:
  - id (INT, unsigned, AI)
  - name (VARCHAR 100)
  - email (VARCHAR 150, unique)
  - password (VARCHAR 255)
  - role (ENUM: 'superadmin', 'client', default 'client')
  - client_id (INT, unsigned, nullable)
  - avatar (VARCHAR 255, nullable)
  - is_active (TINYINT 1, default 1)
  - last_login (DATETIME, nullable)
  - remember_token (VARCHAR 100, nullable)
  - created_at (DATETIME, nullable)
  - updated_at (DATETIME, nullable)

#### 2. settings
- **Purpose**: Application-wide key-value settings
- **Primary Key**: id (INT, auto_increment)
- **Unique**: key
- **Columns**:
  - id (INT, unsigned, AI)
  - key (VARCHAR 100, unique)
  - value (TEXT, nullable)
  - group (VARCHAR 50, default 'general')
  - created_at (DATETIME, nullable)
  - updated_at (DATETIME, nullable)

#### 3. leads
- **Purpose**: Potential client leads before conversion
- **Primary Key**: id (INT, auto_increment)
- **Unique**: lead_number
- **Key indexes**: status, follow_up_date
- **Columns**:
  - id (INT, unsigned, AI)
  - lead_number (VARCHAR 20, unique)
  - name (VARCHAR 100)
  - company_name (VARCHAR 150, nullable)
  - mobile (VARCHAR 20)
  - whatsapp (VARCHAR 20, nullable)
  - email (VARCHAR 150, nullable)
  - address (TEXT, nullable)
  - source (ENUM: facebook, instagram, whatsapp, google_ads, website, phone, referral, linkedin, manual, default 'manual')
  - budget (DECIMAL 12,2, nullable)
  - requirement (TEXT, nullable)
  - notes (TEXT, nullable)
  - status (ENUM: new, contacted, follow_up, proposal_sent, negotiation, converted, lost, default 'new')
  - follow_up_date (DATE, nullable)
  - assigned_date (DATE, nullable)
  - converted_client_id (INT, unsigned, nullable)
  - created_by (INT, unsigned)
  - created_at (DATETIME, nullable)
  - updated_at (DATETIME, nullable)
  - deleted_at (DATETIME, nullable)

#### 4. clients
- **Purpose**: Customer/organization records
- **Primary Key**: id (INT, auto_increment)
- **Unique**: client_number
- **Key indexes**: client_id (via projects), is_active
- **Columns**:
  - id (INT, unsigned, AI)
  - client_number (VARCHAR 20, unique)
  - name (VARCHAR 100)
  - company_name (VARCHAR 150, nullable)
  - phone (VARCHAR 20)
  - whatsapp (VARCHAR 20, nullable)
  - email (VARCHAR 150, nullable)
  - gst_number (VARCHAR 20, nullable)
  - pan_number (VARCHAR 15, nullable)
  - address (TEXT, nullable)
  - city (VARCHAR 50, nullable)
  - state (VARCHAR 50, nullable)
  - pincode (VARCHAR 10, nullable)
  - website (VARCHAR 255, nullable)
  - notes (TEXT, nullable)
  - lead_id (INT, unsigned, nullable)
  - is_active (TINYINT 1, default 1)
  - created_by (INT, unsigned)
  - created_at (DATETIME, nullable)
  - updated_at (DATETIME, nullable)
  - deleted_at (DATETIME, nullable)

#### 5. projects
- **Purpose**: Client projects
- **Primary Key**: id (INT, auto_increment)
- **Unique**: project_number
- **Key indexes**: client_id, status
- **Columns**:
  - id (INT, unsigned, AI)
  - project_number (VARCHAR 20, unique)
  - client_id (INT, unsigned)
  - name (VARCHAR 200)
  - type (ENUM: website, ecommerce, mobile_app, software, crm, erp, seo, digital_marketing, hosting, domain)
  - description (TEXT, nullable)
  - start_date (DATE, nullable)
  - delivery_date (DATE, nullable)
  - budget (DECIMAL 12,2, default 0)
  - advance_paid (DECIMAL 12,2, default 0)
  - total_paid (DECIMAL 12,2, default 0)
  - status (ENUM: pending, development, testing, revision, completed, on_hold, cancelled, default 'pending')
  - notes (TEXT, nullable)
  - created_by (INT, unsigned)
  - created_at (DATETIME, nullable)
  - updated_at (DATETIME, nullable)
  - deleted_at (DATETIME, nullable)

#### 6. milestones
- **Purpose**: Project milestones with payments
- **Primary Key**: id (INT, auto_increment)
- **Key indexes**: project_id
- **Columns**:
  - id (INT, unsigned, AI)
  - project_id (INT, unsigned)
  - title (VARCHAR 200)
  - description (TEXT, nullable)
  - amount (DECIMAL 12,2, default 0)
  - due_date (DATE, nullable)
  - completed_date (DATE, nullable)
  - status (ENUM: pending, in_progress, completed, paid, default 'pending')
  - sort_order (INT, default 0)
  - created_at (DATETIME, nullable)
  - updated_at (DATETIME, nullable)

#### 7. proposals
- **Purpose**: Sales proposals for clients
- **Primary Key**: id (INT, auto_increment)
- **Unique**: proposal_number
- **Key indexes**: client_id, project_id, lead_id, status
- **Columns**:
  - id (INT, unsigned, AI)
  - proposal_number (VARCHAR 20, unique)
  - client_id (INT, unsigned)
  - project_id (INT, unsigned, nullable)
  - lead_id (INT, unsigned, nullable)
  - title (VARCHAR 200)
  - introduction (LONGTEXT, nullable)
  - project_overview (LONGTEXT, nullable)
  - scope_of_work (LONGTEXT, nullable)
  - deliverables (LONGTEXT, nullable)
  - timeline (LONGTEXT, nullable)
  - pricing (LONGTEXT, nullable)
  - terms (LONGTEXT, nullable)
  - total_amount (DECIMAL 12,2, default 0)
  - valid_until (DATE, nullable)
  - status (ENUM: draft, sent, accepted, rejected, default 'draft')
  - sent_at (DATETIME, nullable)
  - accepted_at (DATETIME, nullable)
  - notes (TEXT, nullable)
  - created_by (INT, unsigned)
  - created_at (DATETIME, nullable)
  - updated_at (DATETIME, nullable)

#### 8. agreements
- **Purpose**: Signed legal agreements
- **Primary Key**: id (INT, auto_increment)
- **Unique**: agreement_number
- **Key indexes**: client_id, project_id, proposal_id
- **Columns**:
  - id (INT, unsigned, AI)
  - agreement_number (VARCHAR 20, unique)
  - client_id (INT, unsigned)
  - project_id (INT, unsigned, nullable)
  - proposal_id (INT, unsigned, nullable)
  - title (VARCHAR 200)
  - client_information (LONGTEXT, nullable)
  - project_information (LONGTEXT, nullable)
  - deliverables (LONGTEXT, nullable)
  - timeline (LONGTEXT, nullable)
  - payment_terms (LONGTEXT, nullable)
  - support_terms (LONGTEXT, nullable)
  - cancellation_terms (LONGTEXT, nullable)
  - terms_conditions (LONGTEXT, nullable)
  - status (ENUM: draft, sent, signed, rejected, default 'draft')
  - sent_at (DATETIME, nullable)
  - signed_at (DATETIME, nullable)
  - signature_ip (VARCHAR 45, nullable)
  - created_by (INT, unsigned)
  - created_at (DATETIME, nullable)
  - updated_at (DATETIME, nullable)

#### 9. invoices
- **Purpose**: Billing invoices
- **Primary Key**: id (INT, auto_increment)
- **Unique**: invoice_number
- **Key indexes**: client_id, status
- **Columns**:
  - id (INT, unsigned, AI)
  - invoice_number (VARCHAR 30, unique)
  - client_id (INT, unsigned)
  - project_id (INT, unsigned, nullable)
  - milestone_id (INT, unsigned, nullable)
  - invoice_date (DATE, nullable)
  - due_date (DATE, nullable)
  - subtotal (DECIMAL 12,2, default 0)
  - tax_percent (DECIMAL 5,2, default 0)
  - tax_amount (DECIMAL 12,2, default 0)
  - discount (DECIMAL 12,2, default 0)
  - total (DECIMAL 12,2, default 0)
  - paid_amount (DECIMAL 12,2, default 0)
  - balance_due (DECIMAL 12,2, default 0)
  - is_gst (TINYINT 1, default 0)
  - notes (TEXT, nullable)
  - terms (TEXT, nullable)
  - status (ENUM: draft, sent, paid, partial, overdue, cancelled, default 'draft')
  - sent_at (DATETIME, nullable)
  - paid_at (DATETIME, nullable)
  - created_by (INT, unsigned)
  - created_at (DATETIME, nullable)
  - updated_at (DATETIME, nullable)

#### 10. invoice_items
- **Purpose**: Individual invoice line items
- **Primary Key**: id (INT, auto_increment)
- **Unique composite**: id + invoice_id
- **Columns**:
  - id (INT, unsigned, AI)
  - invoice_id (INT, unsigned)
  - description (VARCHAR 500)
  - quantity (DECIMAL 10,2, default 1)
  - unit_price (DECIMAL 12,2, default 0)
  - total (DECIMAL 12,2, default 0)
  - sort_order (INT, default 0)

#### 11. payments
- **Purpose**: Payment records
- **Primary Key**: id (INT, auto_increment)
- **Unique**: payment_number
- **Key indexes**: client_id
- **Columns**:
  - id (INT, unsigned, AI)
  - payment_number (VARCHAR 20, unique)
  - client_id (INT, unsigned)
  - project_id (INT, unsigned, nullable)
  - invoice_id (INT, unsigned, nullable)
  - milestone_id (INT, unsigned, nullable)
  - amount (DECIMAL 12,2)
  - method (ENUM: razorpay, upi, bank_transfer, cash, cheque)
  - transaction_id (VARCHAR 100, nullable)
  - razorpay_order_id (VARCHAR 100, nullable)
  - razorpay_payment_id (VARCHAR 100, nullable)
  - payment_date (DATE, nullable)
  - notes (TEXT, nullable)
  - status (ENUM: pending, completed, failed, refunded, default 'completed')
  - created_by (INT, unsigned)
  - created_at (DATETIME, nullable)
  - updated_at (DATETIME, nullable)

#### 12. domains
- **Purpose**: Domain name management
- **Primary Key**: id (INT, auto_increment)
- **Key indexes**: client_id, expiry_date
- **Columns**:
  - id (INT, unsigned, AI)
  - client_id (INT, unsigned)
  - project_id (INT, unsigned, nullable)
  - domain_name (VARCHAR 255)
  - registrar (VARCHAR 100, nullable)
  - registration_date (DATE, nullable)
  - expiry_date (DATE)
  - cost (DECIMAL 10,2, default 0)
  - renewal_cost (DECIMAL 10,2, default 0)
  - auto_renew (TINYINT 1, default 0)
  - notes (TEXT, nullable)
  - status (ENUM: active, expiring_soon, expired, default 'active')
  - last_reminder_sent (DATETIME, nullable)
  - created_by (INT, unsigned)
  - created_at (DATETIME, nullable)
  - updated_at (DATETIME, nullable)

#### 13. hostings
- **Purpose**: Hosting service management
- **Primary Key**: id (INT, auto_increment)
- **Key indexes**: client_id
- **Columns**:
  - id (INT, unsigned, AI)
  - client_id (INT, unsigned)
  - project_id (INT, unsigned, nullable)
  - provider (VARCHAR 100)
  - package (VARCHAR 100, nullable)
  - server_ip (VARCHAR 50, nullable)
  - server_details (TEXT, nullable)
  - username (VARCHAR 100, nullable)
  - control_panel_url (VARCHAR 255, nullable)
  - purchase_date (DATE, nullable)
  - expiry_date (DATE)
  - cost (DECIMAL 10,2, default 0)
  - renewal_cost (DECIMAL 10,2, default 0)
  - notes (TEXT, nullable)
  - status (ENUM: active, expiring_soon, expired, default 'active')
  - last_reminder_sent (DATETIME, nullable)
  - created_by (INT, unsigned)
  - created_at (DATETIME, nullable)
  - updated_at (DATETIME, nullable)

#### 14. documents
- **Purpose**: File attachments across the application
- **Primary Key**: id (INT, auto_increment)
- **Key indexes**: client_id
- **Columns**:
  - id (INT, unsigned, AI)
  - client_id (INT, unsigned, nullable)
  - project_id (INT, unsigned, nullable)
  - category (ENUM: proposal, agreement, invoice, contract, screenshot, client_file, project_file, other, default 'other')
  - title (VARCHAR 200)
  - file_name (VARCHAR 255)
  - file_path (VARCHAR 500)
  - file_size (INT, nullable)
  - file_type (VARCHAR 50, nullable)
  - reference_id (INT, unsigned, nullable)
  - reference_type (VARCHAR 50, nullable)
  - notes (TEXT, nullable)
  - created_by (INT, unsigned)
  - created_at (DATETIME, nullable)
  - updated_at (DATETIME, nullable)

#### 15. tasks
- **Purpose**: Project tasks/Kanban
- **Primary Key**: id (INT, auto_increment)
- **Key indexes**: project_id
- **Columns**:
  - id (INT, unsigned, AI)
  - project_id (INT, unsigned, nullable)
  - title (VARCHAR 200)
  - description (TEXT, nullable)
  - priority (ENUM: low, medium, high, urgent, default 'medium')
  - due_date (DATE, nullable)
  - completed_date (DATE, nullable)
  - status (ENUM: todo, in_progress, review, completed, hold, default 'todo')
  - sort_order (INT, default 0)
  - notes (TEXT, nullable)
  - assigned_to (INT, unsigned, nullable)
  - created_by (INT, unsigned)
  - created_at (DATETIME, nullable)
  - updated_at (DATETIME, nullable)

#### 16. tickets
- **Purpose**: Support/customer tickets
- **Primary Key**: id (INT, auto_increment)
- **Unique**: ticket_number
- **Key indexes**: client_id
- **Columns**:
  - id (INT, unsigned, AI)
  - ticket_number (VARCHAR 20, unique)
  - client_id (INT, unsigned)
  - project_id (INT, unsigned, nullable)
  - subject (VARCHAR 200)
  - description (TEXT, nullable)
  - priority (ENUM: low, medium, high, urgent, default 'medium')
  - status (ENUM: open, in_progress, closed, default 'open')
  - closed_at (DATETIME, nullable)
  - created_at (DATETIME, nullable)
  - updated_at (DATETIME, nullable)

#### 17. ticket_replies
- **Purpose**: Individual ticket response messages
- **Primary Key**: id (INT, auto_increment)
- **Key indexes**: ticket_id
- **Columns**:
  - id (INT, unsigned, AI)
  - ticket_id (INT, unsigned)
  - user_id (INT, unsigned)
  - message (LONGTEXT)
  - attachment (VARCHAR 500, nullable)
  - is_admin (TINYINT 1, default 0)
  - created_at (DATETIME, nullable)

#### 18. activities
- **Purpose**: General activity log
- **Primary Key**: id (INT, auto_increment)
- **Key indexes**: user_id, module
- **Columns**:
  - id (INT, unsigned, AI)
  - user_id (INT, unsigned)
  - module (VARCHAR 50)
  - module_id (INT, unsigned, nullable)
  - action (VARCHAR 100)
  - description (TEXT, nullable)
  - ip_address (VARCHAR 45, nullable)
  - created_at (DATETIME, nullable)

#### 19. lead_activities
- **Purpose**: Lead-specific activity tracking
- **Primary Key**: id (INT, auto_increment)
- **Key indexes**: lead_id
- **Columns**:
  - id (INT, unsigned, AI)
  - lead_id (INT, unsigned)
  - user_id (INT, unsigned)
  - action (VARCHAR 100)
  - notes (TEXT, nullable)
  - follow_up_date (DATE, nullable)
  - created_at (DATETIME, nullable)

#### 20. notifications
- **Purpose**: User notification system
- **Primary Key**: id (INT, auto_increment)
- **Key indexes**: user_id
- **Columns**:
  - id (INT, unsigned, AI)
  - user_id (INT, unsigned)
  - type (VARCHAR 50)
  - title (VARCHAR 200)
  - message (TEXT, nullable)
  - reference_id (INT, unsigned, nullable)
  - reference_type (VARCHAR 50, nullable)
  - is_read (TINYINT 1, default 0)
  - read_at (DATETIME, nullable)
  - created_at (DATETIME, nullable)

#### 21. razorpay_orders
- **Purpose**: Razorpay payment order tracking
- **Primary Key**: id (INT, auto_increment)
- **Unique**: order_id
- **Key indexes**: entity_type, entity_id, client_id
- **Columns**:
  - id (INT, unsigned, AI)
  - order_id (VARCHAR 100, unique)
  - entity_type (ENUM: invoice, milestone, renewal)
  - entity_id (INT, unsigned)
  - client_id (INT, unsigned)
  - amount (DECIMAL 12,2)
  - currency (VARCHAR 10, default 'INR')
  - status (ENUM: created, paid, failed, expired, default 'created')
  - payment_id (VARCHAR 100, nullable)
  - created_at (DATETIME, nullable)
  - updated_at (DATETIME, nullable)

#### 22. ci_sessions
- **Purpose**: CodeIgniter session storage
- **Primary Key**: composite (id, timestamp)
- **Key indexes**: id, timestamp
- **Columns**:
  - id (VARCHAR 128, not null)
  - ip_address (VARCHAR 45, not null)
  - timestamp (INT, unsigned, default 0, not null)
  - data (BLOB, not null)

#### 23. lead_activities (duplicate entry - appears in both activities and lead_activities)
*Note: Both `activities` and `lead_activities` tables exist with similar structures for general vs lead-specific activity tracking.*

### Incremental Migrations (August - October 2026)

#### 24. Module Permissions
- **Migration**: 2026-08-28-000001_AddModulePermissionsAndTaskExtras.php
- **Purpose**: Extended task model and added module permission system

#### 25. Staff Role and Department
- **Migration**: 2026-08-28-000001_AddStaffRoleAndDepartment.php
- **Purpose**: Added department field to users (extension of existing role system)

#### 26. PMS Memberships
- **Migration**: 2026-08-26-000001_AddPmsMemberships.php
- **Purpose**: Project member management (many-to-many between projects and users)

#### 27. PMS Task Status Standardization
- **Migration**: 2026-08-26-000002_StandardizePmsTaskStatuses.php
- **Purpose**: Standardized task statuses across PMS

#### 28. PMS Deliverables
- **Migration**: 2026-08-26-000003_AddPmsDeliverables.php
- **Purpose**: Deliverable tracking within project management

#### 29. PMS Task Collaboration
- **Migration**: 2026-08-26-000004_AddPmsTaskCollaboration.php
- **Purpose**: Task collaboration features (comments, mentions, etc.)

#### 30. Deliverable Approvals
- **Migration**: 2026-09-16-000001_CreateDeliverableApprovals.php
- **Purpose**: Approval workflow for deliverables

#### 31. Deliverable Files
- **Migration**: 2026-09-16-000001_AddVideoAndDeliverableFileUploads.php
- **Purpose**: Video and file upload support for deliverables

#### 32. Payment Currency
- **Migration**: 2026-08-27-000001_AddPaymentCurrency.php
- **Purpose**: Multi-currency support for payments

#### 33. Multi-currency Support
- **Migration**: 2026-08-07-000001_AddMultiCurrencySupport.php
- **Purpose**: Currency fields across financial tables

#### 34. Milestone Due Time
- **Migration**: 2026-09-08-000001_AddMilestoneDueTime.php
- **Purpose**: Added time component to milestone due dates

#### 35. Client Project Members
- **Migration**: 2026-09-08-000002_CreateClientProjectMembers.php
- **Purpose**: Project member tracking per client

#### 36. Video and File Uploads
- **Migration**: 2026-09-16-000001_AddVideoAndDeliverableFileUploads.php
- **Purpose**: Enhanced file upload capabilities

#### 37. MSG91 WhatsApp Settings
- **Migration**: 2026-09-17-000001_AddMsg91WhatsappSettings.php
- **Purpose**: WhatsApp configuration settings

#### 38. MSG91 Session Base URL
- **Migration**: 2026-09-17-000002_AddMsg91SessionBaseUrl.php
- **Purpose**: MSG91 API session configuration

#### 39. Chat Messages
- **Migration**: 2026-10-08-000001_CreateChatMessagesTable.php
- **Purpose**: Chat message storage

#### 40. WhatsApp Conversations
- **Migration**: 2026-10-08-000002_CreateWhatsappConversations.php
- **Purpose**: WhatsApp conversation tracking

#### 41. WhatsPhone Unique
- **Migration**: 2026-10-08-000004_RelaxWhatsappPhoneUnique.php
- **Purpose**: Relaxed unique constraint on WhatsApp phone numbers

## Database Observations

### Strengths
- Well-structured migration history with clear versioning
- Proper use of foreign key concepts (implicit through application logic)
- ENUM types for status fields ensure data consistency
- Timestamps on all critical tables (created_at, updated_at)
- Soft deletes (deleted_at) on CRM tables (leads, clients, projects)
- Unique constraints on important identifier fields (lead_number, client_number, project_number, proposal_number, agreement_number, invoice_number, payment_number, ticket_number, order_id, email)
- Proper indexing on foreign key columns (client_id, project_id, assigned_to)

### Gaps / Missing Tables for HRMS/ATS
The following tables are NOT present and need to be created for the new features:

**HRMS Tables (planned)**:
- employees (or employee records)
- employee_departments
- employee_designations
- employee_documents
- employee_employment_history
- employee_salary_structures
- employee_id_cards

**ATS/Recruitment Tables (planned)**:
- internship_programmes
- internship_positions
- internship_applications
- application_status_history
- candidate_assessments
- interviews
- interview_feedback
- offer_letters
- offer_acceptances
- onboarding_checklists
- onboarding_documents

**Payroll Tables (planned)**:
- payroll_periods
- payroll_components
- employee_salary_components
- payroll_records
- payroll_items
- payslips
- payroll_approvals
- salary_payments

**Productivity Tables (planned)**:
- daily_standups
- personal_notes
- reminders
- activity_logs (supplementary to activities table)

### Index Recommendations
1. Add indexes on `employees.user_id` if employee table references users
2. Add indexes on `internship_applications.email` for duplicate detection
3. Add composite indexes on `payroll_records` for employee + period queries
4. Add index on `offer_letters.candidate_id` for quick lookups
5. Consider full-text search on `employees.notes` or `employee_documents.notes`

### Foreign Key Relationships (enforced in application logic)
- clients.id → projects.client_id
- projects.id → milestones.project_id
- projects.id → proposals.project_id
- projects.id → invoices.project_id
- projects.id → payments.project_id
- projects.id → domains.project_id (nullable)
- projects.id → hostings.project_id (nullable)
- projects.id → documents.project_id (nullable)
- projects.id → tasks.project_id
- leads.id → clients.lead_id (nullable)
- milestones.project_id → projects.id (implied)
- invoices.milestone_id → milestones.id (nullable)
- invoice_items.invoice_id → invoices.id
- payments.invoice_id → invoices.id (nullable)
- payments.milestone_id → milestones.id (nullable)
- tasks.assigned_to → users.id (nullable)
- tasks.project_id → projects.id (nullable)
- notifications.user_id → users.id
- notifications.reference_id → related table id (generic reference)
- razorpay_orders.client_id → clients.id
- razorpay_orders.entity_id → related table id

### Table Collation/Engine
- All tables appear to use InnoDB (implied by CodeIgniter defaults with foreign key support possible)
- Timestamp handling follows CodeIgniter conventions
- Soft delete pattern used on CRM tables (leads, clients, projects)

## Summary
The database is mature and well-structured for the existing CRM/financial functionality. 28 tables cover CRM, project management, billing, and basic user management. New tables are needed for HRMS, recruitment/ATS, payroll, and enhanced productivity features. The migration system is active with 20+ incremental migrations since the initial setup.