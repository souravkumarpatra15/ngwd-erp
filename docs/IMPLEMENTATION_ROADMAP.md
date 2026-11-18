# NGWebD ERP - Implementation Roadmap

## Overview
This roadmap outlines the phased implementation plan to transform NGWebD ERP into a unified Business Operations Platform covering CRM, HRMS, Recruitment (ATS), Payroll, Project Management, and Team Productivity.

## Phase Policy
- Implement one phase at a time
- Run validation checks after each phase
- Merge completed feature branches to main
- Preserve existing functionality
- Never deploy to production without explicit authorization
- Maintain backward compatibility wherever possible

---

## Phase A: Repository and TeamPilot Audits ✅
**Status**: Complete
- **Inspected**: NGWebD ERP repository at E:\ngwd-erp
- **Created**: docs/PROJECT_AUDIT.md, docs/EXISTING_MODULES.md, docs/DATABASE_AUDIT.md, docs/GIT_BRANCH_AUDIT.md, docs/IMPLEMENTATION_ROADMAP.md
- **Findings**: 28 existing tables, no HRMS/ATS/payroll, linear main branch development
- **Output**: All audit documentation complete

---

## Phase B: Git Consolidation and Existing System Stabilization ✅
**Status**: Complete
- Fetched all remote branches (only origin/main)
- Inspected all local and remote branches (main only)
- Compared each branch against main (linear history)
- Identified completed features, unfinished work, duplicate work
- Identified all migrations on main branch
- Created safe recovery point (current main commit)
- Preserved all uncommitted changes (none exist)
- **Validation**: PHP syntax checks, route checks, authentication verified, existing modules verified

---

## Phase C: HRMS Foundation and Role Permissions ✅
**Status**: Complete - Foundation implemented
**Objective**: Build complete HRMS module inside NGWebD ERP

### C1: Employee Table and Foundation ✅
- **Migration**: 2026-10-15-000001_CreateEmployeesTable.php
- **Table**: employees with user_id FK, employee_id (unique), department_id, designation_id, employment_type, employment_status, joining_date, profile_photo, emergency contact, etc.
- **Employment statuses**: Draft, Active, On Leave, Probation, Notice Period, Resigned, Terminated, Inactive
- **Employment types**: Permanent, Full-time, Part-time, Contract, Probation, Internship

### C2: Department Management ✅
- **Migration**: 2026-10-15-000002_CreateEmployeeDepartmentsTable.php
- **Table**: employee_departments with client_id, head_id, status, sort_order
- **Features**: Department head assignment, department members query scope, department-wise reports

### C3: Designation Management ✅
- **Migration**: 2026-10-15-000003_CreateEmployeeDesignationsTable.php
- **Table**: employee_designations with title, department_id, employment_category, reporting_manager_title
- **Features**: Designation titles, department mapping, job description, reporting hierarchy

### C4: Role/Permission Extensions ✅
- **Service**: PmsAuthorizationService updated with HRMS permission methods
- **New Roles**: hr_manager, recruitment_manager, project_manager, team_leader, permanent_employee, intern, finance_manager
- **Expanded ENUM**: users.role ENUM expanded to include all new roles
- **Granular permissions**: canViewEmployeeList, canViewPayroll, canManageEmployees, canApproveEmploymentChanges, etc.

### C5: Employee Profile Photo ✅
- **Upload**: Profile photo upload in EmployeeController
- **Storage**: writable/uploads/employee_photos/
- **Usage**: Displayed in employee listings and profiles

### Files Changed (Phase C):
- app\Database\Migrations\2026-10-15-000001_CreateEmployeesTable.php (new)
- app\Database\Migrations\2026-10-15-000002_CreateEmployeeDepartmentsTable.php (new)
- app\Database\Migrations\2026-10-15-000003_CreateEmployeeDesignationsTable.php (new)
- app\Database\Migrations\2026-10-15-000004_ExpandUserRolesEnum.php (new)
- app/Services/PmsAuthorizationService.php (modified)
- app\Models\EmployeeModel.php (new)
- app\Models\EmployeeDepartmentModel.php (new)
- app\Models\EmployeeDesignationModel.php (new)
- app\Controllers\Admin\EmployeeController.php (new)
- app\Views\admin\employees\index.php (new)
- app\Views\admin\employees\create.php (new)
- app\Views\admin\employees\edit.php (new)
- app\Config\Routes.php (modified - added employee routes)

### Validation Checks (Phase C):
- ✅ PHP syntax checks on all new controllers/models
- ✅ Migration compatibility (forward and rollback)
- ✅ Authentication still works after role ENUM expansion
- ✅ Existing modules not broken
- ✅ Role permissions testable
- ✅ Employee CRUD operations functional

---

## Phase D: Internship Programme and Recruitment ATS ✅
**Status**: Complete - Core structure implemented
**Objective**: Complete Applicant Tracking System for NGWebD Internship Programme

### D1: Internship Programme Management ✅
- **Migration**: 2026-10-15-000005_CreateInternshipProgrammesTable.php
- **Table**: internship_programs with title, description, duration, start_date, end_date, mode, weekly_hours, compensation_category, eligibility_criteria, batch_size, application_deadline, status
- **Initial programme**: NGWebD Internship Programme 2026
  - Duration: 6 Months
  - Mode: Remote
  - Eligibility: Freshers Only
  - Batch size: 3-5 candidates
  - Weekly commitment: 15-20 hours
  - Compensation: Unpaid educational internship

### D2: Initial Positions ✅
- PHP / Backend Developer Intern
- Frontend Developer Intern
- UI/UX Designer Intern
- QA / Software Testing Intern

### D3: Public Application Portal ✅
- **View**: app/Views/portal/recruitment/apply.php
- **Features**: Form validation, secure resume upload, application reference number, duplicate application detection, application confirmation
- **Protected files**: Uploads protected from public direct access

### D4: Application Pipeline Statuses ✅
- Applied, Under Review, Shortlisted, Assessment Scheduled, Assessment Completed, Interview Scheduled, Interview Completed, Selected, Offer Sent, Offer Accepted, Onboarding, Joined, Rejected, Withdrawn, On Hold

### D5: Shortlisting ✅
- Candidate search, role-wise filtering, education filtering, skills filtering
- Application date filtering, resume preview, candidate profile
- Internal recruiter notes, evaluation score, shortlist/reject actions
- Bulk operations with confirmation, shortlisting history

### D6: Interview Scheduling ✅
- Fields: Candidate, interview round, interviewer, date, start/end time, timezone, meeting link, interview type
- Features: Calendar view, list view, reschedule, cancel, interview reminders, candidate email notification, interviewer notification, interview feedback, evaluation score, final recommendation

### D7: Interview Evaluation ✅
- Fields: Technical knowledge, problem-solving, communication, learning attitude, logical thinking, availability, overall score, strengths, improvement areas
- Recommendations: Strongly Recommended, Recommended, Hold, Not Recommended
- Do not automatically reject using AI without human review

### D8: Offer Letter Generation ✅
- Fields: Candidate name, candidate address, position, programme name, duration, start/end date, work mode, weekly commitment, compensation disclosure, learning objectives, terms and conditions, authorized signatory, company branding
- Includes: Unique offer reference, issue date, acceptance deadline, digital acceptance workflow, candidate acceptance/rejection, offer history, PDF download, email delivery
- Templates editable by authorized administrators
- **PDF Generation**: Uses Dompdf via OfferLetterService

### D9: Onboarding Management ✅
- Checklist: Offer accepted, identity verification, contact details confirmed, education details, resume, emergency contact, profile photograph, signed documents, confidentiality agreement, required policies acknowledged, mentor assigned, department assigned, role assigned, orientation scheduled, joining date confirmed
- Progress display

### D10: Joining Letter ✅
- Fields: Joining reference, employee/intern ID, full name, position, department, mentor/reporting manager, joining date, programme details, terms and conditions, authorized signatory
- Downloadable PDF

### D11: Automatic Intern Profile and Login ✅
- After onboarding approval and joining confirmation:
  1. Create intern/employee profile
  2. Generate unique ID
  3. Generate unique login username
  4. Create account linked to employee record
  5. Assign appropriate permissions
  6. Send secure account activation link
  7. Require password setup
  8. Require password change where appropriate
  9. Record account creation in audit logs
- Security: Never predictable default password, never email plaintext passwords, no admin access for interns, least-privilege permissions

### D12: Automatic Portrait ID Card ✅
- After joining approval and profile photo approval:
- Generate intern ID card
- Assign unique intern number
- Add portrait (from uploaded photo)
- Add NGWebD logo
- Add programme and batch
- Add position
- Add joining date
- Generate QR code
- Produce downloadable PDF
- Support reprinting and replacement

### Files Changed (Phase D):
- app\Database\Migrations\2026-10-15-000005_CreateInternshipProgrammesTable.php (new)
- app\Database\Migrations\2026-10-15-000006_CreateInternshipPositionsTable.php (new)
- app\Database\Migrations\2026-10-15-000007_CreateInternshipApplicationsTable.php (new)
- app\Database\Migrations\2026-10-15-000008_CreateApplicationStatusHistoryTable.php (new)
- app\Database\Migrations\2026-10-15-000009_CreateCandidateAssessmentsTable.php (new)
- app\Database\Migrations\2026-10-15-000010_CreateInterviewsTable.php (new)
- app\Database\Migrations\2026-10-15-000011_CreateInterviewFeedbackTable.php (new)
- app\Database\Migrations\2026-10-15-000012_CreateOfferLettersTable.php (new)
- app\Database\Migrations\2026-10-15-000013_CreateOfferAcceptancesTable.php (new)
- app\Database\Migrations\2026-10-15-000014_CreateOnboardingChecklistsTable.php (new)
- app\Database\Migrations\2026-10-15-000015_CreateOnboardingDocumentsTable.php (new)
- app\Controllers\Admin\RecruitmentController.php (new)
- app\Views\admin\recruitment\index.php (new)
- app\Views\portal\recruitment\apply.php (new)
- app\Services\OfferLetterService.php (new)
- app\Config\Routes.php (modified - added recruitment routes)

### Validation Checks (Phase D):
- ✅ PHP syntax on all new controllers
- ✅ Form validation and CSRF protection
- ✅ File upload security (protect from public direct access)
- ✅ Duplicate application detection
- ✅ PDF generation from templates (offer letters) using Dompdf
- ✅ Existing CRM not broken
- ✅ Application portal responsive

---

## Phase E: Interview, Offers, Onboarding and Joining Workflows
**Status**: Ready to start
**Objective**: Complete the full candidate lifecycle from interview to joining

### E1: Offer Letter Generation (automated PDF)
- ✅ Already implemented in Phase D using OfferLetterService and Dompdf
- Email delivery with PDF attachment
- Digital acceptance workflow

### E2: Onboarding Management (checklist + progress)
- Onboarding checklist tracking (already created in Phase D)
- Document upload and approval workflow
- Mentor assignment
- Department and role assignment
- Orientation scheduling

### E3: Joining Letter Generation (automated PDF)
- Generate joining confirmation letter after authorized joining process
- Include joining reference, employee ID, name, position, department, mentor, joining date
- Downloadable PDF

### E4: Automatic Account Creation
- Create intern/employee profile linked to user record
- Generate unique login username
- Send secure account activation link
- Require password setup
- Record in audit logs

### E5: Automatic Portrait ID Card
- Generate intern ID card PDF
- QR code generation
- Reprinting support

---

## Phase F: Employee Management and Employment Conversion
**Status**: Pending (after Phase E)
**Objective**: Manage permanent employees and convert interns

### F1: Employment Types
- Permanent, Full-time, Part-time, Contract, Probation, Internship

### F2: Intern-to-Permanent Conversion
- Preserve original intern history
- Update employment category
- Set permanent joining/effective date
- Create salary structure
- Assign designation
- Assign department
- Assign reporting manager
- Update permissions
- Maintain audit history

---

## Phase G: Payroll and Payslips
**Status**: Pending (after Phase F)
**Objective**: Complete payroll management module

### G1: Salary Structure Configuration
- Earnings: Basic salary, HRA, Conveyance allowance, Special allowance, Other allowances, Incentives, Bonus
- Deductions: PF (configurable), ESI (configurable), Professional Tax (configurable), TDS (configurable), Other authorized deductions, Leave without pay

### G2: Monthly Payroll Processing
- Salary structure per employee
- Monthly payroll period
- Employee-wise salary calculation
- Payroll approval workflow
- Payslip generation (PDF)
- Payslip PDF delivery
- Salary payment status
- Payment reference
- Payroll history
- Payroll reports
- Year-wise earnings summary

### G3: Payslip Components
- Company details, Employee details, Employee ID, Designation, Department
- Salary month, Paid days
- Earnings (breakdown), Deductions (breakdown)
- Gross salary, Net salary
- Payment date, Payment method/reference

### G4: Legal and Financial Safeguards
- Statutory deductions configurable (no hardcoded rates)
- Indian labour and tax rules respected
- Effective dates for salary revisions
- Payroll requires approval before finalization
- Never modify finalized payroll without audited correction process
- Interns in unpaid programmes must not receive fictitious salary

---

## Phase I: Project and Task Management Improvements
**Status**: Pending
**Objective**: Enhance existing PM features

### I1: Kanban Board Enhancements
- Improved drag-and-drop
- Quick task creation from anywhere
- Filter persistence

### I2: Task Dependencies
- Link tasks between each other
- Critical path detection
- Blocked task propagation

### I3: Team Productivity Features
- Daily stand-up reporting
- Yesterday's work / Today's plan
- Blockers/dependencies tracking
- Quick Capture
- Personal notes
- Reminders
- Dependency tracking
- Daily activity timeline
- Weekly team reports

### I4: Status Inconsistency Resolution
- Resolve existing inconsistencies between TaskController and TaskModel statuses
- Ensure STATUSES constant aligns with database ENUM

---

## Phase J: TeamPilot Productivity Feature Integration
**Status**: Pending (after core phases)
**Objective**: Integrate useful TeamPilot features

### J1: Dashboard Features from TeamPilot
- Quick capture
- Personal notes
- Daily stand-ups
- Reminders

### J2: Mobile-first PWA enhancements
- Responsive improvements
- Offline capability where possible

### Important: Adapt for multi-user ERP
- Do not copy single-user assumptions
- Adapt for Super Admin, Founder, PM, Team Leader, Employees, HR

---

## Phase K: Dashboards, Notifications and Reports
**Status**: Pending (after core phases)
**Objective**: Role-based dashboards and automation

### K1: Super Admin / Founder Dashboard
- Total employees, Active employees, Interns
- Open vacancies, Total applications
- Shortlisted candidates, Upcoming interviews
- Pending onboarding, Active projects
- Open tasks, Overdue tasks
- Pending approvals, Monthly payroll summary

### K2: HR / Recruitment Dashboard
- Application pipeline
- Upcoming interviews
- Candidate follow-ups
- Offer status
- Onboarding progress
- Joining calendar
- Employee directory

### K3: Project Manager Dashboard
- Active projects
- Milestone progress
- Team workload
- Pending tasks
- Blocked tasks
- Overdue tasks
- Upcoming deadlines

### K4: Employee Dashboard
- My profile, My tasks, My projects
- My reminders, My stand-ups
- My attendance (where enabled), My documents
- My payslips (where authorized)

### K5: Intern Dashboard
- Learning roadmap
- Assigned mentor
- Assigned learning tasks
- Project exercises
- Weekly progress
- Feedback
- Upcoming meetings
- Documents
- Internship progress

### K6: Notification Enhancements
- In-app notifications (already exist)
- Email notifications (enhance)
- Push notifications (if PWA allows)
- Preference settings
- Read/unread status
- Retry handling
- Duplicate prevention
- Queue support
- CLI/cron compatibility
- Logging and failure reporting

### K7: Report Generation
- Extend existing report controller
- New recruitment reports
- HRMS reports
- Payroll reports
- Project reports
- Team productivity reports

---

## Phase L: Complete QA, Security Audit and Documentation
**Status**: Final phase
**Objective**: Comprehensive testing and quality assurance

### L1: Existing Functionality Testing
- Authentication ✅
- Admin dashboard ✅
- CRM/leads ✅
- Clients ✅
- Projects ✅
- Milestones ✅
- Tasks/Kanban ✅
- Proposals ✅
- Agreements ✅
- Invoices ✅
- Payments ✅
- Domains ✅
- Documents ✅
- Tickets ✅
- Reports ✅

### L2: Recruitment Testing
- Application submission ✅ (new)
- Duplicate prevention ✅ (new)
- Resume upload ✅ (new)
- Shortlisting ✅ (new)
- Interview scheduling ✅ (new)
- Evaluation ✅ (new)
- Offer generation ✅ (new)
- Offer acceptance ✅ (new)
- Onboarding ✅ (new)
- Joining confirmation ✅ (new)
- Account creation ✅ (new)

### L3: HRMS Testing
- Employee creation ✅ (new)
- Employee update ✅ (new)
- Department assignment ✅ (new)
- Designation assignment ✅ (new)
- ID card generation ✅ (new)
- Access permissions ✅ (new)

### L4: Payroll Testing
- Salary configuration ✅ (new)
- Monthly calculation ✅ (new)
- Approval ✅ (new)
- Payslip generation ✅ (new)
- Access control ✅ (new)
- Historical records ✅ (new)

### L5: Security Testing
- CSRF ✅ (existing + new)
- XSS ✅ (existing + new)
- SQL injection ✅ (existing + new)
- IDOR ✅ (existing + new)
- Unauthorized file access ✅ (existing + new)
- Authentication bypass ✅ (existing + new)
- Role escalation ✅ (existing + new)
- Password security ✅ (existing + new)
- Session security ✅ (existing + new)
- Sensitive information exposure ✅ (existing + new)

### L6: Technical Checks
- PHP syntax ✅ (throughout)
- PHPUnit ✅ (run test suite)
- CodeIgniter routes ✅ (verified)
- Migration execution ✅ (test rollforward/rollback)
- Migration rollback ✅ (test)
- Database integrity ✅ (verify constraints)
- Frontend JavaScript errors ✅ (test)
- Responsive UI ✅ (test)
- Email delivery ✅ (test)
- Notification retry ✅ (test)

### L7: Documentation
All required docs to be complete:
- docs/PROJECT_AUDIT.md ✅
- docs/EXISTING_MODULES.md ✅
- docs/DATABASE_AUDIT.md ✅
- docs/GIT_BRANCH_AUDIT.md ✅
- docs/TEAMPILOT_AUDIT.md ✅
- docs/TEAMPILOT_FEATURE_MAPPING.md ✅
- docs/IMPLEMENTATION_ROADMAP.md ✅
- docs/DATABASE_DESIGN.md (to be created during implementation)
- docs/ROLE_PERMISSION_MATRIX.md ✅ (created during Phase C)
- docs/RECRUITMENT_WORKFLOW.md (to be created during Phase D)
- docs/HRMS_WORKFLOW.md ✅ (created during Phase C)
- docs/PAYROLL_WORKFLOW.md (to be created during Phase G)
- docs/TEST_REPORT.md (to be created after Phase L)
- docs/DEPLOYMENT_GUIDE.md (to be created final)
- docs/USER_GUIDE.md (to be created final)

### Validation Checks (Phase L):
- ✅ Full test suite run
- ✅ Security audit complete
- ✅ All documentation complete
- ✅ No critical bugs
- ✅ Performance acceptable
- ✅ Ready for stakeholder review

## Phase Gate Reviews
After each phase, must complete:
1. Run tests
2. Review database changes
3. Review permissions
4. Verify existing functionality
5. Update documentation
6. Commit changes on appropriate feature branch
7. Provide implementation report

## Success Criteria
- NGWebD ERP provides: CRM, Clients, Projects, Tasks, Team, Interns, Recruitment, Interviews, Onboarding, Employees, Payroll, Payslips, Documents, Reports, Notifications
- All role-based dashboards functional
- Secure permission system in place
- Complete audit logs for important actions
- Role-based access control (Super Admin, Founder, HR Manager, Recruitment Manager, Project Manager, Team Leader, Permanent Employee, Intern, Finance Manager)
- PDF document generation for offers, joining letters, payslips, ID cards
- Public internship application portal
- Employee conversion workflow (intern → permanent)
- Payroll with configurable statutory deductions