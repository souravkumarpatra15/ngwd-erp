<?php

namespace App\Models;

use CodeIgniter\Model;

class TaskCollaborationModel extends Model
{
    protected $table = 'task_comments';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['task_id', 'user_id', 'body', 'is_internal'];

    public function comments(int $taskId): array
    {
        return $this->db->table('task_comments c')
            ->select('c.*, u.name as user_name, u.email as user_email')
            ->join('users u', 'u.id = c.user_id', 'left')
            ->where('c.task_id', $taskId)
            ->orderBy('c.created_at', 'ASC')
            ->get()->getResultArray();
    }

    public function subtasks(int $taskId): array
    {
        return $this->db->table('task_subtasks s')
            ->select('s.*, u.name as creator_name')
            ->join('users u', 'u.id = s.created_by', 'left')
            ->where('s.task_id', $taskId)
            ->orderBy('s.sort_order', 'ASC')->orderBy('s.id', 'ASC')
            ->get()->getResultArray();
    }

    public function activities(int $taskId): array
    {
        return $this->db->table('task_activities a')
            ->select('a.*, u.name as user_name')
            ->join('users u', 'u.id = a.user_id', 'left')
            ->where('a.task_id', $taskId)
            ->orderBy('a.created_at', 'DESC')->orderBy('a.id', 'DESC')
            ->get()->getResultArray();
    }

    public function log(int $taskId, ?int $userId, string $action, $oldValue = null, $newValue = null): void
    {
        $this->db->table('task_activities')->insert([
            'task_id' => $taskId,
            'user_id' => $userId,
            'action' => $action,
            'old_value' => $oldValue === null ? null : (is_scalar($oldValue) ? (string)$oldValue : json_encode($oldValue)),
            'new_value' => $newValue === null ? null : (is_scalar($newValue) ? (string)$newValue : json_encode($newValue)),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
