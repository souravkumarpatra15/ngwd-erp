<?php
namespace App\Models;

use CodeIgniter\Model;

class PayslipsModel extends Model
{
    protected $table      = 'payslips';
    protected $primaryKey = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'payroll_record_id', 'employee_id', 'payroll_period_id',
        'employee_name', 'employee_id_display', 'designation', 'department',
        'salary_month', 'paid_days', 'basic_salary', 'hra', 'conveyance',
        'special_allowance', 'other_allowances', 'incentives', 'bonus',
        'gross_earnings', 'pf', 'ESI', 'professional_tax', 'tds',
        'other_deductions', 'leave_with_pay', 'gross_deductions',
        'net_salary', 'payment_date', 'payment_method', 'payment_reference',
        'status', 'generated_at', 'approved_at', 'created_at', 'updated_at',
    ];

    public function forEmployee(int $employeeId): array
    {
        return $this->where('employee_id', $employeeId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }
}
