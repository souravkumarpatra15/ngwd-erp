<?php
namespace App\Models;

use CodeIgniter\Model;

class ClientProjectMemberModel extends Model
{
    protected $table = 'client_project_members';
    protected $primaryKey = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = ['user_id', 'project_id', 'created_at'];

    public function projectIdsForUser(int $userId): array
    {
        $rows = $this->select('project_id')->where('user_id', $userId)->findAll();
        return array_values(array_unique(array_map(static fn($r) => (int) $r['project_id'], $rows)));
    }

    public function isAssigned(int $userId, int $projectId): bool
    {
        return (bool) $this->where('user_id', $userId)->where('project_id', $projectId)->first();
    }

    /** Replace a user's full project assignment set in one call. */
    public function setAssignments(int $userId, array $projectIds): void
    {
        $this->where('user_id', $userId)->delete();
        $now = date('Y-m-d H:i:s');
        $rows = [];
        foreach (array_unique(array_map('intval', $projectIds)) as $pid) {
            if ($pid > 0) $rows[] = ['user_id' => $userId, 'project_id' => $pid, 'created_at' => $now];
        }
        if ($rows) $this->insertBatch($rows);
    }
}
