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
        'due_date', 'completed_date', 'status', 'sort_order', 'is_issue',
        'notes', 'assigned_to', 'created_by',
        'estimated_hours', 'actual_hours', 'depends_on',
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
        if (!empty($filters['project_id_in']) && is_array($filters['project_id_in'])) {
            $b->whereIn('tasks.project_id', $filters['project_id_in']);
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

    public function getKanbanBoard(?int $projectId = null, ?array $visibleProjectIds = null): array
    {
        $board = array_fill_keys(self::STATUSES, []);
        $b = $this->db->table('tasks')
            ->select('tasks.*, projects.name as project_name, u.name as assigned_name, milestones.title as milestone_title')
            ->join('projects', 'projects.id = tasks.project_id', 'left')
            ->join('milestones', 'milestones.id = tasks.milestone_id', 'left')
            ->join('users u', 'u.id = tasks.assigned_to', 'left');
        if ($projectId) $b->where('tasks.project_id', $projectId);
        elseif ($visibleProjectIds !== null) $b->whereIn('tasks.project_id', $visibleProjectIds ?: [0]);
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

    // ── Task dependencies ──────────────────────────────────────
    public function getDependentTasks(int $taskId): array
    {
        return $this->db->table('tasks')
            ->select('tasks.*, t2.title as dependency_title')
            ->join('tasks t2', 't2.id = tasks.depends_on', 'left')
            ->where('tasks.depends_on', $taskId)
            ->where('tasks.deleted_at IS NULL')
            ->get()->getResultArray();
    }

    public function getBlockingTasks(int $taskId): array
    {
        return $this->db->table('tasks')
            ->select('tasks.*, projects.name as project_name')
            ->join('projects', 'projects.id = tasks.project_id', 'left')
            ->where('tasks.depends_on', $taskId)
            ->where('tasks.deleted_at IS NULL')
            ->get()->getResultArray();
    }

    // ── Task comments (simple activity-based) ──────────────────
    public function addComment(int $taskId, string $comment, int $userId, string $userName): bool
    {
        $activity = new \App\Models\ActivityModel();
        return $activity->insert([
            'user_id' => $userId,
            'module' => 'projects',
            'module_id' => $taskId,
            'action' => 'task_comment',
            'description' => $userName . ' commented: ' . substr($comment, 0, 100),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function getComments(int $taskId): array
    {
        return (new \App\Models\ActivityModel())->where('module', 'projects')
            ->where('module_id', $taskId)
            ->where('action', 'task_comment')
            ->orderBy('created_at', 'ASC')
            ->findAll();
    }

    // ── Checklists within tasks ────────────────────────────────
    public function addChecklistItem(int $taskId, string $itemText, int $order = 0): bool
    {
        // Use the new checklists table for reliable relational storage
        $result = $this->db->table('checklists')->insert([
            'task_id' => $taskId,
            'item_text' => $itemText,
            'completed' => false,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return $result !== false;
    }

    public function getChecklistItems(int $taskId): array
    {
        return $this->db->table('checklists')
            ->where('task_id', $taskId)
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    public function updateChecklistItem(int $checklistId, bool $completed): bool
    {
        return $this->db->table('checklists')
            ->where('id', $checklistId)
            ->set('completed', $completed)
            ->set('completed_at', $completed ? date('Y-m-d H:i:s') : null)
            ->update();
    }

    public function deleteChecklistItem(int $checklistId): bool
    {
        return $this->db->table('checklists')
            ->where('id', $checklistId)
            ->delete();
    }

    // ── Team tasks (tasks assigned to project members) ─────────
    public function getTeamTasks(int $projectId, ?int $memberId = null): array
    {
        $b = $this->db->table('tasks')
            ->select('tasks.*, u.name as assigned_name')
            ->join('users u', 'u.id = tasks.assigned_to', 'left')
            ->where('tasks.project_id', $projectId)
            ->where('tasks.deleted_at IS NULL');

        if ($memberId) {
            $b->where('tasks.assigned_to', $memberId);
        }

        return $b->orderBy('tasks.sort_order', 'ASC')
                 ->orderBy('tasks.due_date', 'ASC')
                 ->get()->getResultArray();
    }

    // ── Enhanced task progress (real tasks schema: status-based) ──
    public function getProgressWithHours(int $projectId): array
    {
        $tasks = $this->db->table('tasks')
            ->select('tasks.*, projects.name as project_name')
            ->join('projects', 'projects.id = tasks.project_id', 'left')
            ->where('tasks.project_id', $projectId)
            ->get()->getResultArray();

        $total = count($tasks);
        $completed = 0;
        $inProgress = 0;
        $blocked = 0;

        foreach ($tasks as $task) {
            match ($task['status']) {
                'done'          => $completed++,
                'in_progress'   => $inProgress++,
                'blocked'       => $blocked++,
                default         => null,
            };
        }

        return [
            'total_tasks'      => $total,
            'completed_tasks'  => $completed,
            'in_progress_tasks' => $inProgress,
            'blocked_tasks'    => $blocked,
            'progress_percentage' => $total ? round(($completed / $total) * 100, 0) : 0,
        ];
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
