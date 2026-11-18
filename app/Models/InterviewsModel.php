<?php
namespace App\Models;

use CodeIgniter\Model;

class InterviewsModel extends Model
{
    protected $table      = 'interviews';
    protected $primaryKey = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'application_id', 'interview_round', 'interviewer_id',
        'interviewer_name', 'interview_date', 'start_time', 'end_time',
        'timezone', 'interview_type', 'meeting_link', 'instructions',
        'internal_notes', 'interview_status', 'evaluation_score',
        'final_recommendation', 'created_at', 'updated_at',
    ];

    public function forApplication(int $applicationId): array
    {
        return $this->where('application_id', $applicationId)
            ->orderBy('interview_date', 'DESC')
            ->findAll();
    }

    public function scheduled(): array
    {
        return $this->where('interview_status', 'scheduled')
            ->orderBy('interview_date', 'ASC')
            ->findAll();
    }
}
