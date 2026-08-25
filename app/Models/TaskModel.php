<?php
namespace App\Models;

use CodeIgniter\Model;

class TaskModel extends Model
{
    public const STATUSES = ['todo','in_progress','code_review','qa','client_review','blocked','done','cancelled'];

    protected $table      = 'tasks';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'project_id', 'milestone_id', 'title', 'description', 'priority',
        'due_date', 'completed_date', 'status', 'sort_order',
        'notes', 'assigned_to', 'created_by',
    ];

    public function getAllWithDetails(array $filters = []): array
    {
        $b = $this->db->table('tasks')
            ->select('tasks.*, projects.name as project_name, projects.id as project_id,
                      u.name as assigned_name, milestones.title as milestone_title')
            ->join('projects', 'projects.id = tasks.project_id', 'left')
            ->join('milestones', 'milestones.id = tasks.milestone_id', 'left')
            ->join('users u', 'u.id = tasks.assigned_to', 'left');

        foreach (['project_id','milestone_id','status','priority','assigned_to'] as $key) {
            if (!empty($filters[$key])) $b->where('tasks.' . $key, $filters[$key]);
        }
        if (!empty($filters['due_before'])) $b->where('tasks.due_date <=', $filters['due_before']);
        if (!empty($filters['search'])) $b->groupStart()->like('tasks.title', $filters['search'])->orLike('tasks.description', $filters['search'])->groupEnd();

        return $b->orderBy('tasks.sort_order', 'ASC')->orderBy('tasks.due_date', 'ASC')->get()->getResultArray();
    }

    public function getWithDetails(int $id): ?array
    {
        return $this->db->table('tasks')
            ->select('tasks.*, projects.name as project_name, clients.name as client_name,
                      u.name as assigned_name, u.email as assigned_email, milestones.title as milestone_title')
            ->join('projects', 'projects.id = tasks.project_id', 'left')
            ->join('clients', 'clients.id = projects.client_id', 'left')
            ->join('users u', 'u.id = tasks.assigned_to', 'left')
            ->join('milestones', 'milestones.id = tasks.milestone_id', 'left')
            ->where('tasks.id', $id)->get()->getRowArray() ?: null;
    }

    public function getKanbanBoard(?int $projectId = null): array
    {
        $board = array_fill_keys(self::STATUSES, []);
        $b = $this->db->table('tasks')
            ->select('tasks.*, projects.name as project_name, u.name as assigned_name, milestones.title as milestone_title')
            ->join('projects', 'projects.id = tasks.project_id', 'left')
            ->join('milestones', 'milestones.id = tasks.milestone_id', 'left')
            ->join('users u', 'u.id = tasks.assigned_to', 'left');
        if ($projectId) $b->where('tasks.project_id', $projectId);
        $tasks = $b->orderBy('tasks.sort_order', 'ASC')->orderBy('tasks.due_date', 'ASC')->get()->getResultArray();
        foreach ($tasks as $task) {
            if (isset($board[$task['status']])) $board[$task['status']][] = $task;
        }
        return $board;
    }

    public function getOverdue(): array
    {
        return $this->db->table('tasks')
            ->select('tasks.*, projects.name as project_name, clients.name as client_name')
            ->join('projects', 'projects.id = tasks.project_id', 'left')
            ->join('clients', 'clients.id = projects.client_id', 'left')
            ->where('tasks.due_date <', date('Y-m-d'))
            ->whereNotIn('tasks.status', ['done', 'cancelled'])
            ->orderBy('tasks.due_date', 'ASC')->get()->getResultArray();
    }

    public function countByStatus(): array
    {
        $rows = $this->select('status, COUNT(*) as cnt')->groupBy('status')->get()->getResultArray();
        return array_column($rows, 'cnt', 'status');
    }
}
