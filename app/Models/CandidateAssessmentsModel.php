<?php
namespace App\Models;

use CodeIgniter\Model;

class CandidateAssessmentsModel extends Model
{
    protected $table      = 'candidate_assessments';
    protected $primaryKey = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'application_id', 'technical_knowledge', 'problem_solving',
        'communication', 'learning_attitude', 'logical_thinking',
        'availability', 'overall_score', 'strengths', 'improvement_areas',
        'recommendation', 'assessed_by', 'assessed_at',
        'created_at', 'updated_at',
    ];

    public function forApplication(int $applicationId): array
    {
        return $this->where('application_id', $applicationId)
            ->orderBy('assessed_at', 'DESC')
            ->findAll();
    }
}
