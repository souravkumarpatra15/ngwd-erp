<?php
namespace App\Models;

use CodeIgniter\Model;

class DeliverableApprovalModel extends Model
{
    protected $table = 'deliverable_approvals';
    protected $primaryKey = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = ['deliverable_id','project_id','user_id','action','comment','created_at'];

    public function history(int $deliverableId): array
    {
        return $this->db->table($this->table)
            ->select('deliverable_approvals.*, users.name as user_name')
            ->join('users','users.id = deliverable_approvals.user_id','left')
            ->where('deliverable_id',$deliverableId)
            ->orderBy('created_at','DESC')
            ->get()->getResultArray();
    }
}
