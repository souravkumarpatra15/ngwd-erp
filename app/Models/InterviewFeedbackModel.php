<?php
namespace App\Models;

use CodeIgniter\Model;

class InterviewFeedbackModel extends Model
{
    protected $table      = 'interview_feedback';
    protected $primaryKey = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'interview_id', 'reviewer_id', 'reviewer_name',
        'technical_knowledge', 'problem_solving', 'communication',
        'learning_attitude', 'logical_thinking', 'availability',
        'overall_score', 'strengths', 'improvement_areas',
        'recommendation', 'created_at',
    ];

    public function forInterview(int $interviewId): array
    {
        return $this->where('interview_id', $interviewId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }
}
