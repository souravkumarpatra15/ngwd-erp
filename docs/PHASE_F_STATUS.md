# Phase F: Intern to Permanent Employee Conversion

## Status: Pending Implementation
## Objective: Build a controlled employment conversion workflow

### Verified Prerequisites (from Phases A-E):
- ✅ Employee table exists with employment_type and employment_status fields
- ✅ Internship application pipeline with status tracking
- ✅ Offer letters with PDF generation
- ✅ Onboarding checklists created
- ✅ Joining confirmation workflow (Phase E)
- ✅ Role ENUM expanded (includes intern, permanent_employee)
- ✅ PmsAuthorizationService with permission methods

### Implementation Plan:
1. Create employment conversion controller/action
2. Intern profile display with completion evaluation
3. Conversion recommendation system
4. HR approval workflow
5. Founder/Admin approval
6. Permanent employment offer generation
7. Salary structure assignment
8. Designation assignment (retain or upgrade)
9. Department assignment retention
10. Reporting manager assignment
11. Effective joining date preservation
12. Audit history maintenance

### Key Features:
- Intern profile display with internship completion data
- Conversion recommendation (based on interview evaluations, performance, etc.)
- Two-step approval: HR → Founder/Admin
- Preserve internship history in employee record
- Update employment type from 'internship' to 'permanent' (or 'full_time')
- Assign or retain designation
- Assign or retain department
- Set or confirm reporting manager
- Set effective joining date (could be joining date or conversion date)
- Maintain complete audit history in activity log
- Generate permanent employee ID if different from intern ID
- Update user role from 'intern' to 'permanent_employee'

### Database Considerations:
- employees table already has employment_type field
- employees table already has employment_status field
- users table role ENUM includes 'intern' and 'permanent_employee'
- activity table for audit trail
- No new tables necessarily required, but could add conversion_recommendations table if needed

### Files to Create:
- app/Controllers/Admin/EmployeeConversionController.php (or add to EmployeeJoiningController)
- Any new migration if adding conversion tracking table
- Views for intern profile and conversion form

### Workflow:
1. Intern reaches 'joined' status in recruitment system
2. Administrator navigates to intern conversion page
3. Display intern profile with internship completion data
4. Provide conversion recommendation input
5. HR selects "Convert to Permanent"
6. Founder/Admin approves conversion
7. Employment type updated from 'internship' to 'permanent'
8. User role updated from 'intern' to 'permanent_employee'
9. Salary structure assigned (or set as nil for unpaid intern conversion)
10. Designation retained or updated
11. Department retained or updated
12. Reporting manager confirmed
13. Effective date set (joining date or conversion date)
14. Audit history recorded for all changes
15. Conversion confirmation issued

### Audit Trail Requirements:
- Record who initiated conversion
- Record who approved conversion
- Record conversion date
- Record employment type change
- Record all field changes (designation, department, etc.)
- Preserve original internship data references

### Validation:
- PHP syntax checks
- Form validation
- Permission checks at each step
- Audit trail verification
- No duplicate user accounts
- Proper role transition