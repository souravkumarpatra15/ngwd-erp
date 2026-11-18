<?php
namespace App\Models;

use CodeIgniter\Model;

class InternshipPositionsModel extends Model
{
    protected $table      = 'internship_positions';
    protected $primaryKey = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'program_id', 'title', 'description', 'required_skills',
        'education_requirement', 'experience_years', 'stipend_category',
        'working_hours_per_week', 'location', 'status',
        'created_at', 'updated_at',
    ];

    public function forProgram(int $programId): array
    {
        return $this->where('program_id', $programId)
            ->orderBy('created_at', 'ASC')
            ->findAll();
    }

    public function open(): array
    {
        return $this->where('status', 'open')
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }
}
