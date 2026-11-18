# NGWebD ERP - Project Audit

## Overview
NGWebD ERP is a CodeIgniter 4 application (PHP 8.3, MySQL 8) serving as a business operations platform with CRM, project management, client management, and financial capabilities. It uses Bootstrap 5, jQuery, and DataTables for the frontend.

## Git Status
- **Current branch**: main
- **Local branches**: main only (no feature branches)
- **Remote branches**: origin/main
- **Uncommitted changes**: None (clean working tree)
- **Recent activity**: Multiple migration files added between June-September 2026

## Technology Stack
- **Framework**: CodeIgniter 4
- **PHP**: 8.3
- **Database**: MySQL 8
- **Frontend**: Bootstrap 5, jQuery, DataTables
- **PDF Generation**: Dompdf (via vendor)
- **Authentication**: CodeIgniter Auth with custom UserModel

## Existing Modules (Complete List)

### 1. CRM Module
- **Status**: Fully functional
- **Components**: Leads, Clients, Projects, Milestones, Proposals, Agreements, Invoices, Payments
- **Routes**: Under `/admin` group with `adminauth` filter
- **API**: Razorpay webhook, WhatsApp webhook

### 2. Task/Kanban Module
- **Status**: Fully functional
- **Components**: Tasks with kanban board, list views, assignment, comments, attachments
- **Task Statuses**: todo, in_progress, code_review, qa, client_review, blocked, done, cancelled
- **Features**: getMyWork(), getOverdue(), countByStatus(), getKanbanBoard()

### 3. Authentication & Authorization
- **Status**: Functional
- **Login**: Auth\LoginController with session management
- **Filters**: AdminAuthFilter, ClientAuthFilter
- **Authorization**: PmsAuthorizationService for module permissions
- **UserModel**: Roles (superadmin, client), department, client_id tracking

### 4. Dashboard
- **Status**: Functional (Admin dashboard)
- **Features**: Revenue tracking, project status charts, lead conversion, my work tasks
- **Role-based visibility**: Projects filtered by user role/department

### 5. Notifications
- **Status**: Functional
- **Model**: NotificationModel with getUserNotifications(), getUnreadCount(), notify()
- **Features**: Mark as read, mark all read, user-specific notifications

### 6. File Management
- **Status**: Functional
- **Components**: Documents with upload/download/delete
- **Storage**: public/uploads/, writable/uploads/, writable/pdfs/

### 7. Reporting
- **Status**: Functional
- **Components**: Revenue, leads, projects, invoices, payments, domains export

### 8. WhatsApp Integration
- **Status**: Functional
- **Components**: MSG91 integration, outgoing/incoming messages
- **Helpers**: whatsapp_helper.php

### 9. Chat System
- **Status**: Functional
- **Components**: Conversations, messages, templates, image upload
- **Controllers**: Admin\ChatController, Client\ChatController

## Database Schema Key Tables
- `users` - id, name, email, password, role, client_id, avatar, is_active
- `projects` - id, project_number, client_id, name, type, status, budget
- `milestones` - id, project_id, title, amount, due_date, status
- `tasks` - id, project_id, title, description, priority, due_date, status, assigned_to
- `notifications` - id, user_id, type, title, message, reference_id, reference_type, is_read

## Views Layout
- `admin/dashboard/index` - Main admin dashboard
- `admin/notifications/index` - Notifications page
- Various admin/client views for CRUD operations

## Key Services
- `PmsAuthorizationService` - Module permission checking

## Missing / Areas for Improvement

1. **No HRMS module** - No employee management, departments, designations
2. **No internship/ATS module** - No recruitment pipeline, application tracking
3. **No payroll module** - No salary structure, payslip generation
4. **No role-based dashboards** for HR, recruitment, project managers, interns
5. **Limited role permissions** - Only superadmin/client roles in UserModel
6. **No employee ID card generation**
7. **No automatic account creation onboarding workflow**
8. **No onboarding/joining letter generation**
9. **No dependency tracking** between tasks
10. **No daily stand-up reporting**
11. **No quick capture feature**
12. **No personal notes feature**
13. **No reminder system** beyond notifications
14. **No PWA mobile-first experience**

## Migration History
- 2024-01-01: Initial table creation (CreateAllTables)
- 2024-06-01: Password resets and admin roles
- 2026-08-07: Multi-currency support
- 2026-08-08: Milestone notes
- 2026-08-09: Project currency
- 2026-08-10: Task milestone link
- 2026-08-23: Marketing leads
- 2026-08-26: PMS memberships, task status standardization, deliverables
- 2026-08-27: Payment currency
- 2026-08-28: Staff role and department, module permissions, task extras
- 2026-09-08: Milestone due time, client project members
- 2026-09-16: Video and deliverable file uploads
- 2026-09-17: MSG91 WhatsApp settings, session base URL, phone unique relaxation
- 2026-10-08: Chat messages, WhatsApp conversations

## Authentication Flow
1. User visits `/` or `/login` → Auth\LoginController::index
2. POST to `/login` → Auth\LoginController::process
3. GET `/logout` → Auth\LoginController::logout
4. Session stores: user_id, user_name, user_role, department, client_id, client_role
5. Admin routes protected by `adminauth` filter
6. Portal routes protected by `clientauth` filter

## Success Factors
- Clean, well-structured CodeIgniter 4 codebase
- Comprehensive migration system
- Separation of admin/portal namespaces
- Existing task management with kanban
- Notification system with read/unread tracking
- Role-based project visibility
- Financial tracking (invoices, payments, payments)

## Areas Needing Attention
- HRMS and employee management completely absent
- Recruitment/ATS completely absent
- Payroll completely absent
- No intern/employee onboarding workflow
- ID card generation not implemented
- No role-based dashboards for new roles