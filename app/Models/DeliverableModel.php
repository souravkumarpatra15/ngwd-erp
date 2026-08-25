<?php
namespace App\Models;

use CodeIgniter\Model;

class DeliverableModel extends Model
{
    public const STATUSES = ['draft','in_progress','submitted','under_review','changes_requested','approved','rejected'];

    protected $table = 'deliverables';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'project_id','milestone_id','title','description','owner_id','due_date','status',
        'file_name','file_path','version','submitted_at','reviewed_at','approved_at','approved_by','created_by',
    ];

    public function getByProject(int $projectId): array
    {
        return $this->db->table($this->table)
            ->select('deliverables.*, milestones.title as milestone_title, u.name as owner_name, a.name as approved_by_name')
            ->join('milestones', 'milestones.id = deliverables.milestone_id', 'left')
            ->join('users u', 'u.id = deliverables.owner_id', 'left')
            ->join('users a', 'a.id = deliverables.approved_by', 'left')
            ->where('deliverables.project_id', $projectId)
            ->orderBy('deliverables.due_date', 'ASC')
            ->orderBy('deliverables.created_at', 'DESC')
            ->get()->getResultArray();
    }

    public function getWithDetails(int $id): ?array
    {
        return $this->db->table($this->table)
            ->select('deliverables.*, projects.name as project_name, milestones.title as milestone_title, u.name as owner_name, a.name as approved_by_name')
            ->join('projects', 'projects.id = deliverables.project_id', 'left')
            ->join('milestones', 'milestones.id = deliverables.milestone_id', 'left')
            ->join('users u', 'u.id = deliverables.owner_id', 'left')
            ->join('users a', 'a.id = deliverables.approved_by', 'left')
            ->where('deliverables.id', $id)->get()->getRowArray() ?: null;
    }
}
