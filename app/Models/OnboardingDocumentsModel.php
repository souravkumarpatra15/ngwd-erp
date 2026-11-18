<?php
namespace App\Models;

use CodeIgniter\Model;

class OnboardingDocumentsModel extends Model
{
    protected $table      = 'onboarding_documents';
    protected $primaryKey = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'employee_id', 'document_name', 'file_path', 'file_type',
        'document_category', 'uploaded_at', 'uploaded_by', 'status',
    ];

    public function forEmployee(int $employeeId): array
    {
        return $this->where('employee_id', $employeeId)
            ->orderBy('uploaded_at', 'DESC')
            ->findAll();
    }

    public function pendingCount(int $employeeId): int
    {
        return $this->where('employee_id', $employeeId)
            ->where('status', 'pending')
            ->countAllResults();
    }
}
