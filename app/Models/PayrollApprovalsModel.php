<?php
namespace App\Models;

use CodeIgniter\Model;

class PayrollApprovalsModel extends Model
{
    protected $table      = 'payroll_approvals';
    protected $primaryKey = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'payroll_record_id', 'employee_id', 'approver_id', 'approver_name',
        'approved_at', 'approval_type', 'approval_status', 'approval_notes',
        'created_at',
    ];

    public function pending(): array
    {
        return $this->where('approval_status', 'pending')
            ->orderBy('created_at', 'ASC')
            ->findAll();
    }
}
