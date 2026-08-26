<?php

namespace App\Services;

use App\Models\ProjectMemberModel;

/**
 * Centralized authorization rules for the PMS layer.
 *
 * This service intentionally fails closed. Controllers should call these
 * methods before mutating project/task/deliverable resources.
 */
class PmsAuthorizationService
{
    private ProjectMemberModel $members;

    public function __construct()
    {
        $this->members = new ProjectMemberModel();
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

        $project = $this->members->db->table('projects')
            ->select('id')
            ->where('id', $projectId)
            ->where('client_id', $clientId)
            ->where('deleted_at IS NULL')
            ->get()
            ->getRowArray();

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

        $row = $this->members->db->table('deliverables d')
            ->select('d.id')
            ->join('projects p', 'p.id = d.project_id', 'inner')
            ->where('d.id', $deliverableId)
            ->where('p.client_id', $clientId)
            ->where('p.deleted_at IS NULL')
            ->get()
            ->getRowArray();

        return !empty($row);
    }
}
