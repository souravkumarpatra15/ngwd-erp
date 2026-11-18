# Phase G: Payroll and Payslip

## Status: Pending Implementation
## Objective: Build a proper monthly payroll module

### Verified Prerequisites (from Phases A-F):
- ✅ Employee table exists with employment_type, employment_status, department_id, designation_id
- ✅ User model with roles (intern, permanent_employee, etc.)
- ✅ PmsAuthorizationService with financial permissions (canViewPayroll, canManageEmployees)
- ✅ Joining workflow (Phase E) - employees can be created and activated
- ✅ Intern to permanent conversion (Phase F) - employment type changed from internship to permanent
- ✅ Salary structure concepts already discussed in roadmap

### Implementation Plan:
1. Salary structure configuration module
2. Earnings: Basic salary, HRA, Conveyance allowance, Special allowance, Other allowances, Incentives, Bonus
3. Deductions: PF (configurable), ESI (configurable), Professional Tax (configurable), TDS (configurable), Other authorized deductions, Leave without pay
4. Monthly payroll period processing
5. Employee-wise salary calculation
6. Payroll approval workflow
7. Payslip PDF generation
8. Salary payment status tracking
9. Payroll history and reports
10. Configurable statutory deduction rates with effective dates

### Key Features:
- **Salary Structure Configuration**: 
  - Basic salary component
  - HRA (House Rent Allowance)
  - Conveyance allowance
  - Special allowance
  - Other allowances
  - Incentives
  - Bonus

- **Configurable Deductions** (no hardcoded rates):
  - PF (Provident Fund) - configurable percentage/applicable threshold
  - ESI (Employee State Insurance) - configurable percentage/applicable threshold
  - Professional Tax - configurable percentage/applicable threshold (India-specific)
  - TDS (Tax Deducted at Source) - configurable percentage/applicable threshold
  - Other authorized deductions
  - Leave without pay

- **Monthly Payroll Processing**:
  - Select salary period (month + year)
  - Employee-wise salary calculation
  - Automatic earnings calculation from structure
  - Automatic deductions calculation
  - Gross salary = total earnings
  - Net salary = gross - deductions
  - Payment date and method tracking
  - Payment status (pending, processing, paid, failed)

- **Payslip PDF**:
  - Company details
  - Employee details (name, ID, designation, department)
  - Salary month
  - Paid days
  - Earnings breakdown
  - Deductions breakdown
  - Gross salary
  - Net salary
  - Payment date
  - Payment method/reference
  - PDF download capability

- **Payroll Approval Workflow**:
  - Designated approver (Finance Manager, Admin)
  - Pending approval queue
  - Finalization lock after approval
  - Auditable edit history for corrections

- **Payroll History & Reports**:
  - Employee-wise salary history
  - Year-wise earnings summary
  - Department-wise salary summaries
  - Pay period reports
  - Statutory compliance reports

### Important Safeguards:
- Statutory deductions are configurable (no hardcoded rates)
- Indian labour and tax rules respected ( Professional Tax, TDS as applicable)
- Effective dates for salary revisions and statutory configuration
- Payroll requires approval before finalization
- Finalized payroll must not be silently editable - any changes require audited correction process
- Never generate payslips for unpaid interns
- Interns in unpaid educational programmes must not receive fictitious salary or payslips

### Database Tables Needed (new migrations):
- payroll_periods - monthly payroll periods
- payroll_components - salary components configuration
- employee_salary_components - per-employee component values per period
- payroll_records - processed payroll records
- payroll_items - individual earning/deduction line items
- payslips - generated payslip records (with PDF path)
- payroll_approvals - approval workflow records
- salary_payments - payment transaction records

### Files to Create:
- New database migrations for payroll tables
- Payroll configuration controller/service
- Payslip PDF generation service
- Payroll approval workflow
- Payroll reports

### Workflow:
1. Administrator/configurator sets up salary structures per employee type
2. During monthly payroll period selection, system calculates:
   - Earnings from each component
   - Deductions based on configured rates
   - Gross and net salary
3. Payroll enters approval queue
4. Approver reviews and finalizes
5. Payslips generated and made available for download
6. Payment status tracked
7. Records stored for history and reports

### Validation:
- PHP syntax checks on all new controllers/models/migrations
- Salary calculation accuracy test
- Configurable deduction rate test (verify rates are not hardcoded)
- PDF payslip generation test
- Approval workflow test
- Access control test (finance role only)
- Verify no payslips generated for interns in unpaid programmes

### Next Steps (after Phase G):
- Phase H: Project and Task Management Enhancements
- Phase I: TeamPilot Feature Integration
- Phase J: Notifications and Automation
- Phase K: Role-Based Dashboards
- Phase L: Final QA and Security Audit