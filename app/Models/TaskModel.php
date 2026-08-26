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

    /**
     * Personal task breakdown for one internal user (spec §29 "My Tasks"
     * for PM/Developer dashboards). No separate PM/Developer role exists
     * on users.role — this works for any internal user based on task
     * assignment rather than inventing a role that isn't in the schema.
     */
    public function getMyWork(int $userId): array
    {
        $base = fn() => $this->db->table('tasks')
            ->select('tasks.*, projects.name as project_name')
            ->join('projects', 'projects.id = tasks.project_id', 'left')
            ->where('tasks.assigned_to', $userId)
            ->whereNotIn('tasks.status', ['done', 'cancelled']);

        return [
            'today'    => $base()->where('tasks.due_date', date('Y-m-d'))->orderBy('tasks.priority', 'ASC')->get()->getResultArray(),
            'overdue'  => $base()->where('tasks.due_date <', date('Y-m-d'))->orderBy('tasks.due_date', 'ASC')->get()->getResultArray(),
            'upcoming' => $base()->where('tasks.due_date >', date('Y-m-d'))->where('tasks.due_date <=', date('Y-m-d', strtotime('+7 days')))->orderBy('tasks.due_date', 'ASC')->get()->getResultArray(),
            'blocked'  => $base()->where('tasks.status', 'blocked')->get()->getResultArray(),
            'review'   => $base()->whereIn('tasks.status', ['code_review', 'qa', 'client_review'])->get()->getResultArray(),
            'total_open' => $base()->countAllResults(),
        ];
    }
}
