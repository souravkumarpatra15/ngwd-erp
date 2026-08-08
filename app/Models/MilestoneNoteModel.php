<?php
namespace App\Models;
use CodeIgniter\Model;

class MilestoneNoteModel extends Model
{
    protected $table      = 'milestone_notes';
    protected $primaryKey = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = ['milestone_id', 'user_id', 'message', 'is_admin'];
    protected $createdField  = 'created_at';

    public function getForMilestone(int $milestoneId): array
    {
        return $this->db->table('milestone_notes')
            ->select('milestone_notes.*, users.name as user_name')
            ->join('users', 'users.id = milestone_notes.user_id', 'left')
            ->where('milestone_id', $milestoneId)
            ->orderBy('created_at', 'ASC')
            ->get()->getResultArray();
    }
}
