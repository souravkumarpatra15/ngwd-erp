<?php

namespace App\Services;

use App\Models\DeliverableModel;
use App\Models\ProjectMemberModel;
use App\Models\ProjectModel;

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

    public function __construct()
    {
        $this->members = new ProjectMemberModel();
        $this->projects = new ProjectModel();
        $this->deliverables = new DeliverableModel();
    }

    public function isPrivilegedInternal(?string $role): bool
    {
        return in_array(strtolower((string) $role), ['superadmin', 'admin'], true);
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
}
