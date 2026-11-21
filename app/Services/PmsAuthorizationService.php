<?php

namespace App\Services;

use App\Models\DeliverableModel;
use App\Models\ProjectMemberModel;
use App\Models\ProjectModel;
use App\Models\ModulePermissionModel;
use App\Models\ClientProjectMemberModel;

/**
 * Centralized authorization rules for the PMS layer.
 *
 * This service intentionally fails closed. Controllers should call these
 * methods before mutating project/task/deliverable resources.
 */
class PmsAuthorizationService
{
    private ProjectMemberModel $members;
    private ProjectModel $projects;
    private DeliverableModel $deliverables;
    private ModulePermissionModel $modulePerms;
    private ClientProjectMemberModel $clientProjectMembers;

    public function __construct()
    {
        $this->members = new ProjectMemberModel();
        $this->projects = new ProjectModel();
        $this->deliverables = new DeliverableModel();
        $this->modulePerms = new ModulePermissionModel();
        $this->clientProjectMembers = new ClientProjectMemberModel();
    }

    public function isPrivilegedInternal(?string $role): bool
    {
        return in_array(strtolower((string) $role), ['superadmin', 'admin'], true);
    }

    /**
     * Team-productivity features (stand-up, quick capture, blockers, notes,
     * weekly reports, team activity) are open to all internal roles — any
     * logged-in internal user may view projects they are scoped to.
     */
    public function canViewProjects(?string $role): bool
    {
        $role = strtolower((string) $role);
        return $role !== '' && $role !== 'client';
    }

    // ── Internal staff departments (spec: developer/designer/qa/hr/support/
    // sales_finance permission tiers, distinct from the login-tier role) ──

    public const DEPARTMENTS = ['developer', 'designer', 'qa', 'hr', 'support', 'sales_finance'];

    /** Modules a 'staff' user sees by default with no explicit grant needed — the PMS core. */
    public const PMS_MODULES = ['dashboard', 'projects', 'tasks', 'kanban', 'milestones', 'deliverables', 'chat'];

    /** Modules a client-portal Owner always has, regardless of any explicit grants for their team. */
    public const CLIENT_OWNER_MODULES = ['milestones', 'deliverables', 'tasks', 'documents', 'invoices', 'payments', 'marketing_leads', 'client_team', 'tickets', 'chat'];

    /** Modules a client team member (non-owner, no explicit grant on file) sees by default. */
    public const CLIENT_DEFAULT_MODULES = ['milestones', 'deliverables', 'tasks', 'documents'];

    /**
     * Per-user, per-module CRUD check. Works uniformly for internal staff
     * and client-portal team members (same users table, same table of
     * overrides) — this is the one place module access is decided, so
     * every controller/view calls this instead of re-deriving its own
     * role logic.
     */
    public function hasModulePermission(int $userId, ?string $role, ?string $clientRole, string $module, string $action = 'view'): bool
    {
        $role = strtolower((string) $role);

        // Superadmin/Admin: unrestricted everywhere.
        if (in_array($role, ['superadmin', 'admin'], true)) {
            return true;
        }

        // An explicit row always wins when one exists, for anyone —
        // this is what lets an admin fine-tune a manager or a client
        // owner down from their default, not just staff/team members up.
        $explicit = $this->modulePerms->where('user_id', $userId)->where('module', $module)->first();
        if ($explicit) {
            return (bool) ($explicit['can_' . $action] ?? false);
        }

        if ($role === 'manager') {
            return true; // unchanged baseline: manager = full internal PM access
        }

        if ($role === 'staff') {
            // View access to the PMS core is the default; anything else
            // (CRM, finance, documents, tickets...) needs an explicit grant.
            return $action === 'view' && in_array($module, self::PMS_MODULES, true);
        }

        if ($role === 'client') {
            if (strtolower((string) $clientRole) === 'owner') {
                return $action === 'view' && in_array($module, self::CLIENT_OWNER_MODULES, true);
            }
            return $action === 'view' && in_array($module, self::CLIENT_DEFAULT_MODULES, true);
        }

        return false;
    }

    /**
     * Financial visibility: project budgets, invoices, payments. Per the
     * agreed model, Superadmin/Admin/Manager always see financials (a
     * Manager is functionally a project lead and needs cost context);
     * among departments, only Sales/Finance does. Everyone else — most
     * notably Developer/Designer/QA — never sees pricing anywhere.
     */
    public function canViewFinancials(?string $role, ?string $department = null): bool
    {
        if (in_array(strtolower((string) $role), ['superadmin', 'admin', 'manager'], true)) {
            return true;
        }
        return strtolower((string) $department) === 'sales_finance';
    }

    /**
     * Client contact visibility (name/email/phone) on project-facing
     * screens. Developer/Designer/QA see that a project belongs to "a
     * client" but never who that client is — this was an explicit
     * decision, not a guess. Applied to every non-elevated department by
     * the same rule for consistency, since none of HR/Support/QA/Designer/
     * Developer were asked to see client contact details either.
     */
    public function canViewClientContact(?string $role, ?string $department = null): bool
    {
        if (in_array(strtolower((string) $role), ['superadmin', 'admin', 'manager'], true)) {
            return true;
        }
        return strtolower((string) $department) === 'sales_finance';
    }

    public function isProjectManager(?string $role, int $userId, int $projectId): bool
    {
        if ($this->isPrivilegedInternal($role)) {
            return true;
        }

        return in_array(strtolower((string) $role), ['manager', 'project_manager'], true)
            && $this->members->isMember($projectId, $userId);
    }

    public function canManageProjectTeam(?string $role, int $userId, int $projectId): bool
    {
        return $this->isPrivilegedInternal($role)
            || $this->isProjectManager($role, $userId, $projectId);
    }

    public function canEditProject(?string $role, int $userId, int $projectId): bool
    {
        if ($this->isPrivilegedInternal($role)) {
            return true;
        }

        if ($this->isProjectManager($role, $userId, $projectId)) {
            return true;
        }

        $member = $this->members->where('project_id', $projectId)
            ->where('user_id', $userId)
            ->where('is_active', 1)
            ->first();

        return $member && in_array(strtolower((string) ($member['access_level'] ?? '')), ['edit', 'manage'], true);
    }

    public function canManageTask(?string $role, int $userId, int $projectId): bool
    {
        if ($this->isPrivilegedInternal($role) || $this->isProjectManager($role, $userId, $projectId)) {
            return true;
        }

        $member = $this->members->where('project_id', $projectId)
            ->where('user_id', $userId)
            ->where('is_active', 1)
            ->first();

        return $member && in_array(strtolower((string) ($member['access_level'] ?? '')), ['edit', 'manage'], true);
    }

    public function canViewProject(int $userId, int $projectId): bool
    {
        return $this->members->isMember($projectId, $userId);
    }

    /**
     * Returns null when the role has unrestricted org-wide visibility
     * (superadmin/admin), or an array of project ids the user may see
     * otherwise — active project membership only. Callers should treat
     * null as "no WHERE filter needed" and an array (even empty) as
     * "filter to exactly these ids".
     */
    public function getVisibleProjectIds(?string $role, int $userId): ?array
    {
        if ($this->isPrivilegedInternal($role)) {
            return null;
        }

        $rows = $this->members->select('project_id')
            ->where('user_id', $userId)
            ->where('is_active', 1)
            ->findAll();

        return array_values(array_unique(array_map(static fn($r) => (int) $r['project_id'], $rows)));
    }

    /** Combines the project-visibility check with the existing membership rule. */
    public function canViewProjectScoped(?string $role, int $userId, int $projectId): bool
    {
        if ($this->isPrivilegedInternal($role)) {
            return true;
        }
        return $this->members->isMember($projectId, $userId);
    }

    /**
     * Client-side tenant boundary. A client may access only projects whose
     * client_id matches the authenticated client identity.
     */
    public function canClientAccessProject(int $clientId, int $projectId): bool
    {
        if ($clientId <= 0 || $projectId <= 0) {
            return false;
        }

        $project = $this->projects->select('id')
            ->where('id', $projectId)
            ->where('client_id', $clientId)
            ->where('deleted_at IS NULL')
            ->first();

        return !empty($project);
    }

    /**
     * Resolve a deliverable's project and apply the same client tenant rule.
     */
    public function canClientAccessDeliverable(int $clientId, int $deliverableId): bool
    {
        if ($clientId <= 0 || $deliverableId <= 0) {
            return false;
        }

        $row = $this->deliverables->select('deliverables.id')
            ->join('projects', 'projects.id = deliverables.project_id', 'inner')
            ->where('deliverables.id', $deliverableId)
            ->where('projects.client_id', $clientId)
            ->where('projects.deleted_at IS NULL')
            ->first();

        return !empty($row);
    }

    // ── Client-role permission model ──────────────────────────────
    // Client portal roles form a simple hierarchy: viewer < member < manager < owner.
    // These helpers gate actions per the client-user permission model, independent
    // of the tenant boundary checks above (both must pass).

    private const CLIENT_ROLE_RANK = ['viewer' => 1, 'member' => 2, 'manager' => 3, 'owner' => 4];

    private function clientRoleRank(?string $clientRole): int
    {
        return self::CLIENT_ROLE_RANK[strtolower((string) $clientRole)] ?? 0;
    }

    /** Viewers can look but not touch: no comments, no uploads, no approvals. */
    public function clientCanComment(?string $clientRole): bool
    {
        return $this->clientRoleRank($clientRole) >= self::CLIENT_ROLE_RANK['member'];
    }

    /** Only Manager and Owner can approve or request changes on a deliverable. */
    public function clientCanApproveDeliverable(?string $clientRole): bool
    {
        return $this->clientRoleRank($clientRole) >= self::CLIENT_ROLE_RANK['manager'];
    }

    /** Only the Client Owner manages who else from their org can log in. */
    public function clientCanManageUsers(?string $clientRole): bool
    {
        return $this->clientRoleRank($clientRole) >= self::CLIENT_ROLE_RANK['owner'];
    }

    /** Manager and Owner may view the org's user list read-only; below that, hidden entirely. */
    public function clientCanViewUsers(?string $clientRole): bool
    {
        return $this->clientRoleRank($clientRole) >= self::CLIENT_ROLE_RANK['manager'];
    }

    // ── Project-wise access for client team members ───────────────
    // Owner/Manager always see every project belonging to their org — they
    // run the account. Member/Viewer only see projects an Owner/Manager has
    // explicitly assigned them to, via client_project_members.

    /** Owner/Manager manage which projects a Member/Viewer can see. */
    public function clientCanAssignProjects(?string $clientRole): bool
    {
        return $this->clientRoleRank($clientRole) >= self::CLIENT_ROLE_RANK['manager'];
    }

    /**
     * Returns null when the client user has org-wide project visibility
     * (owner/manager), or an array of project ids otherwise (even empty —
     * meaning "no projects assigned yet"). Callers treat null as "no WHERE
     * filter needed" and an array as "filter to exactly these ids".
     */
    public function getClientVisibleProjectIds(int $userId, ?string $clientRole): ?array
    {
        if ($this->clientRoleRank($clientRole) >= self::CLIENT_ROLE_RANK['manager']) {
            return null;
        }
        return $this->clientProjectMembers->projectIdsForUser($userId);
    }

    /** Combines the client tenant boundary with the per-user project assignment. */
    public function canClientUserAccessProject(int $userId, ?string $clientRole, int $clientId, int $projectId): bool
    {
        if (!$this->canClientAccessProject($clientId, $projectId)) {
            return false;
        }
        $visible = $this->getClientVisibleProjectIds($userId, $clientRole);
        return $visible === null || in_array($projectId, $visible, true);
    }

    // ── HRMS Permission Methods ──────────────────────────────────────────────

    /** Superadmin/Admin: unrestricted access to all HRMS modules. */
    public function hasHrmsPermission(int $userId, ?string $role, ?string $clientRole, string $action = 'view'): bool
    {
        $role = strtolower((string) $role);

        // Superadmin/Admin: unrestricted everywhere.
        if (in_array($role, ['superadmin', 'admin'], true)) {
            return true;
        }

        // Check explicit module permissions first
        $explicit = $this->modulePerms->where('user_id', $userId)->where('module', 'hrms')->first();
        if ($explicit) {
            return (bool) ($explicit['can_' . $action] ?? false);
        }

        // HR Manager has full HRMS access
        if ($role === 'hr_manager') {
            return true;
        }

        // Recruitment Manager has hiring/recruitment access
        if ($role === 'recruitment_manager') {
            return in_array($action, ['view', 'create', 'update', 'delete', 'approve'], true);
        }

        // Project Manager limited HR access (employee lookup only)
        if ($role === 'project_manager') {
            return $action === 'view';
        }

        // Team Leader limited HR access
        if ($role === 'team_leader') {
            return $action === 'view' || $action === 'assign';
        }

        // Permanent Employee can view own record
        if ($role === 'permanent_employee') {
            return $action === 'view';
        }

        // Intern can view own record
        if ($role === 'intern') {
            return $action === 'view';
        }

        return false;
    }

    /** Can view employee list (HR/Finance/Manager) */
    public function canViewEmployeeList(?string $role, ?string $department = null): bool
    {
        $role = strtolower((string) $role);
        if (in_array($role, ['superadmin', 'admin', 'manager', 'hr_manager'], true)) {
            return true;
        }
        return strtolower((string) $department) === 'hr';
    }

    /** Can view salary/payroll information */
    public function canViewPayroll(?string $role, ?string $department = null): bool
    {
        if (in_array(strtolower((string) $role), ['superadmin', 'admin', 'finance_manager', 'manager'], true)) {
            return true;
        }
        return strtolower((string) $department) === 'hr' || strtolower((string) $department) === 'sales_finance';
    }

    /** Can manage employee records (hire, update, terminate) */
    public function canManageEmployees(?string $role): bool
    {
        return in_array(strtolower((string) $role), ['superadmin', 'admin', 'hr_manager'], true);
    }

    /** Can approve leave and employment status changes */
    public function canApproveEmploymentChanges(?string $role): bool
    {
        return in_array(strtolower((string) $role), ['superadmin', 'admin', 'hr_manager'], true);
    }

    /** Can view department dashboards */
    public function canViewDepartmentDashboard(?string $role, ?string $department = null): bool
    {
        if (in_array(strtolower((string) $role), ['superadmin', 'admin', 'manager'], true)) {
            return true;
        }
        return strtolower((string) $department) === 'hr';
    }

    /** Can view own employee profile */
    public function canViewOwnEmployee(?string $role): bool
    {
        return in_array(strtolower((string) $role), ['permanent_employee', 'intern', 'hr_manager', 'admin', 'superadmin'], true);
    }

    /** Can edit own employee profile */
    public function canEditOwnEmployee(?string $role): bool
    {
        return in_array(strtolower((string) $role), ['permanent_employee', 'hr_manager', 'admin', 'superadmin'], true);
    }

    /** Can manage departments (create, update, deactivate) */
    public function canManageDepartments(?string $role): bool
    {
        return in_array(strtolower((string) $role), ['superadmin', 'admin', 'hr_manager'], true);
    }

    /** Can manage designations (create, update) */
    public function canManageDesignations(?string $role): bool
    {
        return in_array(strtolower((string) $role), ['superadmin', 'admin', 'hr_manager'], true);
    }

    /** Can view recruitment/applications */
    public function canViewRecruitment(?string $role): bool
    {
        return in_array(strtolower((string) $role), ['superadmin', 'admin', 'hr_manager', 'recruitment_manager'], true);
    }

    /** Can manage interview scheduling */
    public function canManageInterviews(?string $role): bool
    {
        return in_array(strtolower((string) $role), ['superadmin', 'admin', 'hr_manager', 'recruitment_manager', 'project_manager'], true);
    }

    /** Can issue/send offers */
    public function canIssueOffers(?string $role): bool
    {
        return in_array(strtolower((string) $role), ['superadmin', 'admin', 'hr_manager'], true);
    }

    /** Can view offer letters */
    public function canViewOffers(?string $role): bool
    {
        return in_array(strtolower((string) $role), ['superadmin', 'admin', 'hr_manager', 'recruitment_manager', 'project_manager'], true);
    }

    /** Can view onboarding progress */
    public function canViewOnboarding(?string $role): bool
    {
        return in_array(strtolower((string) $role), ['superadmin', 'admin', 'hr_manager', 'project_manager', 'team_leader'], true);
    }

    /** Can view attendance records */
    public function canViewAttendance(?string $role): bool
    {
        return in_array(strtolower((string) $role), ['superadmin', 'admin', 'hr_manager', 'project_manager', 'team_leader', 'permanent_employee', 'intern'], true);
    }

    /** Can manage attendance (check in/out, corrections, viewing others) */
    public function canManageAttendance(?string $role): bool
    {
        return in_array(strtolower((string) $role), ['superadmin', 'admin', 'hr_manager'], true);
    }

    /** Can view certificates */
    public function canViewCertificates(?string $role): bool
    {
        return in_array(strtolower((string) $role), ['superadmin', 'admin', 'hr_manager', 'finance_manager'], true);
    }

    /** Can manage certificates (generate, revoke) */
    public function canManageCertificates(?string $role): bool
    {
        return in_array(strtolower((string) $role), ['superadmin', 'admin', 'hr_manager'], true);
    }

    /** Can access payslip information */
    public function canViewPayslips(?string $role, ?string $department = null): bool
    {
        if (in_array(strtolower((string) $role), ['superadmin', 'admin', 'finance_manager'], true)) {
            return true;
        }
        return strtolower((string) $department) === 'hr' || strtolower((string) $department) === 'sales_finance';
    }
}
