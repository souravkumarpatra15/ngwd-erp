<?php
namespace App\Models;

use CodeIgniter\Model;

class TaskModel extends Model
{
    protected $table      = 'tasks';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'project_id', 'title', 'description', 'priority',
        'due_date', 'completed_date', 'status', 'sort_order',
        'notes', 'assigned_to', 'created_by',
    ];

    // ── Rich query: tasks with project + assignee info ─────────
    public function getAllWithDetails(array $filters = []): array
    {
        $b = $this->db->table('tasks')
            ->select('tasks.*, projects.name as project_name, projects.id as project_id,
                      u.name as assigned_name')
            ->join('projects', 'projects.id = tasks.project_id', 'left')
            ->join('users u', 'u.id = tasks.assigned_to', 'left');

        if (!empty($filters['project_id'])) {
            $b->where('tasks.project_id', $filters['project_id']);
        }
        if (!empty($filters['status'])) {
            $b->where('tasks.status', $filters['status']);
        }
        if (!empty($filters['priority'])) {
            $b->where('tasks.priority', $filters['priority']);
        }
        if (!empty($filters['assigned_to'])) {
            $b->where('tasks.assigned_to', $filters['assigned_to']);
        }
        if (!empty($filters['due_before'])) {
            $b->where('tasks.due_date <=', $filters['due_before']);
        }

        return $b->orderBy('tasks.sort_order', 'ASC')
                 ->orderBy('tasks.due_date', 'ASC')
                 ->get()->getResultArray();
    }

    // ── Single task with all related info ─────────────────────
    public function getWithDetails(int $id): ?array
    {
        return $this->db->table('tasks')
            ->select('tasks.*, projects.name as project_name, clients.name as client_name,
                      u.name as assigned_name, u.email as assigned_email')
            ->join('projects', 'projects.id = tasks.project_id', 'left')
            ->join('clients', 'clients.id = projects.client_id', 'left')
            ->join('users u', 'u.id = tasks.assigned_to', 'left')
            ->where('tasks.id', $id)
            ->get()->getRowArray() ?: null;
    }

    // ── Kanban grouped by status ───────────────────────────────
    public function getKanbanBoard(?int $projectId = null): array
    {
        $statuses = ['todo', 'in_progress', 'review', 'done'];
        $board    = [];

        foreach ($statuses as $status) {
            $b = $this->db->table('tasks')
                ->select('tasks.*, projects.name as project_name, u.name as assigned_name')
                ->join('projects', 'projects.id = tasks.project_id', 'left')
                ->join('users u', 'u.id = tasks.assigned_to', 'left')
                ->where('tasks.status', $status);

            if ($projectId) {
                $b->where('tasks.project_id', $projectId);
            }

            $board[$status] = $b->orderBy('tasks.sort_order', 'ASC')->get()->getResultArray();
        }

        return $board;
    }

    // ── Overdue tasks ──────────────────────────────────────────
    public function getOverdue(): array
    {
        return $this->db->table('tasks')
            ->select('tasks.*, projects.name as project_name, clients.name as client_name')
            ->join('projects', 'projects.id = tasks.project_id', 'left')
            ->join('clients', 'clients.id = projects.client_id', 'left')
            ->where('tasks.due_date <', date('Y-m-d'))
            ->whereNotIn('tasks.status', ['done', 'cancelled'])
            ->orderBy('tasks.due_date', 'ASC')
            ->get()->getResultArray();
    }

    // ── Counts ─────────────────────────────────────────────────
    public function countByStatus(): array
    {
        $rows = $this->select('status, COUNT(*) as cnt')
                     ->groupBy('status')
                     ->get()->getResultArray();

        return array_column($rows, 'cnt', 'status');
    }
}
