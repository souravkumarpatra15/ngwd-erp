<?php
namespace App\Models;

use CodeIgniter\Model;

class InternshipProgrammesModel extends Model
{
    protected $table      = 'internship_programs';
    protected $primaryKey = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'title', 'description', 'duration_months', 'start_date', 'end_date',
        'mode', 'weekly_hours', 'compensation_category',
        'eligibility_criteria', 'batch_size_min', 'batch_size_max',
        'application_deadline', 'status', 'public_application_url',
        'created_by', 'created_at', 'updated_at',
    ];

    /** Only programmes open for applications */
    public function openForApplication(): array
    {
        return $this->where('status', 'active')
            ->where('application_deadline >=', date('Y-m-d'))
            ->orderBy('application_deadline', 'ASC')
            ->findAll();
    }
}
