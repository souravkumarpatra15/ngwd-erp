<?php
namespace App\Models;

use CodeIgniter\Model;

class ProjectMemberModel extends Model
{
    protected $table = 'project_members';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'project_id', 'user_id', 'project_role', 'access_level',
        'is_active', 'joined_at',
    ];

    /** Return active project members with user details. */
    public function getByProject(int $projectId): array
    {
        return $this->db->table($this->table)
            ->select('project_members.*, users.name, users.email, users.avatar, users.role as user_role')
            ->join('users', 'users.id = project_members.user_id', 'inner')
            ->where('project_members.project_id', $projectId)
            ->where('project_members.is_active', 1)
            ->orderBy('project_members.project_role', 'ASC')
            ->orderBy('users.name', 'ASC')
            ->get()->getResultArray();
    }

    /** Check whether a user is an active member of a project. */
    public function isMember(int $projectId, int $userId): bool
    {
        return $this->where([
            'project_id' => $projectId,
            'user_id' => $userId,
            'is_active' => 1,
        ])->countAllResults() > 0;
    }

    /** Add or reactivate a project member without creating duplicates. */
    public function addMember(int $projectId, int $userId, string $projectRole = 'member', string $accessLevel = 'edit'): int
    {
        $existing = $this->where('project_id', $projectId)->where('user_id', $userId)->first();
        $data = [
            'project_role' => $projectRole,
            'access_level' => $accessLevel,
            'is_active' => 1,
            'joined_at' => $existing['joined_at'] ?? date('Y-m-d H:i:s'),
        ];

        if ($existing) {
            $this->update($existing['id'], $data);
            return (int) $existing['id'];
        }

        $this->insert(array_merge($data, [
            'project_id' => $projectId,
            'user_id' => $userId,
        ]));
        return (int) $this->getInsertID();
    }

    /** Deactivate a member rather than deleting historical membership. */
    public function removeMember(int $projectId, int $userId): bool
    {
        return (bool) $this->where('project_id', $projectId)
            ->where('user_id', $userId)
            ->set(['is_active' => 0])
            ->update();
    }
}
