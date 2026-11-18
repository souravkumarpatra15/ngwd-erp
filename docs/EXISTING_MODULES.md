# NGWebD ERP - Existing Modules Documentation

## Module Status Summary
| Module | Status | Last Updated |
|--------|--------|--------------|
| Authentication | ✅ Complete | 2026-08-28 |
| CRM (Leads/Clients) | ✅ Complete | 2026-09-17 |
| Projects | ✅ Complete | 2026-08-28 |
| Milestones | ✅ Complete | 2026-08-28 |
| Proposals | ✅ Complete | 2026-08-26 |
| Agreements | ✅ Complete | 2026-08-29 |
| Invoices & Payments | ✅ Complete | 2026-09-17 |
| Domains & Hostings | ✅ Complete | 2026-09-17 |
| Documents | ✅ Complete | 2026-08-29 |
| Tasks & Kanban | ✅ Complete | 2026-08-26 |
| Tickets | ✅ Complete | 2026-08-03 |
| Notifications | ✅ Complete | 2026-08-28 |
| Reporting | ✅ Complete | 2026-09-08 |
| WhatsApp Integration | ✅ Complete | 2026-09-17 |
| Chat System | ✅ Complete | 2026-10-08 |
| **HRMS (Employee Management)** | **🟡 In Progress** | **2026-10-15** |

## Detailed Module Descriptions (Updated)

### 1. HRMS - Employee Management (New)
**Status**: In Progress - Foundation laid, basic CRUD created
**Controllers**: Admin\EmployeeController
**Models**: EmployeeModel, EmployeeDepartmentModel, EmployeeDesignationModel
**Tables**: employees, employee_departments, employee_designations
**Routes**: /admin/employees, /admin/employees/create, /admin/employees/store, /admin/employees/edit/(:num), /admin/employees/update/(:num), /admin/employees/delete/(:num), /admin/employees/(:num)

**Features Implemented**:
- Employee creation with associated user selection
- Employee editing and status management
- Department and designation assignment
- Employment type and status tracking
- Profile photo upload
- Employee search and filtering
- Employee statistics dashboard

**Tables Created**:
- `employees` - employee records with user linkage, department, designation, employment details
- `employee_departments` - department management with head assignment
- `employee_designations` - designation titles and department mapping

**New Modules Added to Authorization**:
- PmsAuthorizationService extended with HRMS permission methods
- Role ENUM expanded to include: hr_manager, recruitment_manager, project_manager, team_leader, permanent_employee, intern, finance_manager

**Pending HRMS Features** (to be added in subsequent migrations):
- Employee document management
- Employee employment history
- Salary structure configuration
- ID card generation
- Leave management
- Attendance tracking

### 2. CRM - Leads Module
- **Status**: Fully functional (unchanged)
- **Components**: Leads, Clients, Projects, Milestones, Proposals, Agreements, Invoices, Payments

### 3. Task/Kanban Module
- **Status**: Fully functional (unchanged)
- **Components**: Tasks with kanban board, list views, assignment, comments, attachments

### 4. Authentication & Authorization
- **Status**: Functional (extended)
- **Roles**: superadmin, client, hr_manager, recruitment_manager, project_manager, team_leader, permanent_employee, intern, finance_manager
- **Filters**: AdminAuthFilter, ClientAuthFilter
- **Authorization**: PmsAuthorizationService with enhanced HRMS permissions

### 5. Dashboard
- **Status**: Functional (enhanced with HR stats)
- **Features**: Employee statistics now shown on admin dashboard

### 6. Notifications
- **Status**: Functional (unchanged)

### 7. File Management
- **Status**: Functional (extended with employee photos)
- **Storage**: writable/uploads/employee_photos/

### 8. Reporting
- **Status**: Functional (unchanged, HR stats added to dashboard)

### 9. WhatsApp Integration
- **Status**: Fully functional (unchanged)

### 10. Chat System
- **Status**: Fully functional (unchanged)