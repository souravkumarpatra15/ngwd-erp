<?php
namespace App\Models;

use CodeIgniter\Model;

class OnboardingChecklistsModel extends Model
{
    protected $table      = 'onboarding_checklists';
    protected $primaryKey = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'employee_id', 'project_id', 'user_id', 'checklist_item',
        'is_completed', 'completed_at', 'completed_by', 'order_index',
        'created_at', 'updated_at',
    ];

    public function forEmployee(int $employeeId): array
    {
        return $this->where('employee_id', $employeeId)
            ->orderBy('order_index', 'ASC')
            ->findAll();
    }

    public function completedCount(int $employeeId): int
    {
        return $this->where('employee_id', $employeeId)
            ->where('is_completed', 1)
            ->countAllResults();
    }
}
