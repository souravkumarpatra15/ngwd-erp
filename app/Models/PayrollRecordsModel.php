<?php
namespace App\Models;

use CodeIgniter\Model;

class PayrollRecordsModel extends Model
{
    protected $table      = 'payroll_records';
    protected $primaryKey = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'payroll_period_id', 'employee_id', 'gross_salary', 'net_salary',
        'gross_earnings', 'net_deductions', 'payment_date', 'payment_method',
        'payment_reference', 'status', 'approved_by', 'approved_at',
        'created_at', 'updated_at',
    ];

    public function forPeriod(int $periodId): array
    {
        return $this->db->table('payroll_records')
            ->select('payroll_records.*, employees.employee_id as employee_code')
            ->join('employees', 'employees.id = payroll_records.employee_id', 'left')
            ->where('payroll_period_id', $periodId)
            ->get()->getResultArray();
    }

    public function pendingApprovals(): array
    {
        return $this->where('status', 'pending')->orderBy('created_at', 'ASC')->findAll();
    }
}
