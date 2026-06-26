<?php
namespace App\Models;

use CodeIgniter\Model;

class MilestoneModel extends Model
{
    protected $table      = 'milestones';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'project_id', 'title', 'description', 'amount',
        'due_date', 'completed_date', 'status', 'sort_order',
    ];

    // ── Milestones with project + client info ──────────────────
    public function getAllWithDetails(?int $projectId = null): array
    {
        $b = $this->db->table('milestones')
            ->select('milestones.*, projects.name as project_name, projects.budget,
                      clients.name as client_name')
            ->join('projects', 'projects.id = milestones.project_id', 'left')
            ->join('clients', 'clients.id = projects.client_id', 'left');

        if ($projectId) {
            $b->where('milestones.project_id', $projectId);
        }

        return $b->orderBy('milestones.sort_order', 'ASC')
                 ->orderBy('milestones.due_date', 'ASC')
                 ->get()->getResultArray();
    }

    // ── For a single project ───────────────────────────────────
    public function getForProject(int $projectId): array
    {
        return $this->where('project_id', $projectId)
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();
    }

    // ── Overdue milestones ─────────────────────────────────────
    public function getOverdue(): array
    {
        return $this->db->table('milestones')
            ->select('milestones.*, projects.name as project_name, clients.name as client_name')
            ->join('projects', 'projects.id = milestones.project_id', 'left')
            ->join('clients', 'clients.id = projects.client_id', 'left')
            ->where('milestones.due_date <', date('Y-m-d'))
            ->whereNotIn('milestones.status', ['completed', 'paid'])
            ->orderBy('milestones.due_date', 'ASC')
            ->get()->getResultArray();
    }

    // ── Financial summary for a project ───────────────────────
    public function getProjectSummary(int $projectId): array
    {
        $milestones = $this->getForProject($projectId);

        return [
            'total'     => array_sum(array_column($milestones, 'amount')),
            'paid'      => array_sum(array_column(array_filter($milestones, fn($m) => $m['status'] === 'paid'), 'amount')),
            'pending'   => array_sum(array_column(array_filter($milestones, fn($m) => $m['status'] === 'pending'), 'amount')),
            'completed' => count(array_filter($milestones, fn($m) => in_array($m['status'], ['completed', 'paid']))),
            'count'     => count($milestones),
        ];
    }

    // ── Upcoming milestones (within N days) ────────────────────
    public function getUpcoming(int $days = 7): array
    {
        return $this->db->table('milestones')
            ->select('milestones.*, projects.name as project_name, clients.name as client_name')
            ->join('projects', 'projects.id = milestones.project_id', 'left')
            ->join('clients', 'clients.id = projects.client_id', 'left')
            ->where('milestones.due_date >=', date('Y-m-d'))
            ->where('milestones.due_date <=', date('Y-m-d', strtotime("+{$days} days")))
            ->whereNotIn('milestones.status', ['completed', 'paid'])
            ->orderBy('milestones.due_date', 'ASC')
            ->get()->getResultArray();
    }
}
