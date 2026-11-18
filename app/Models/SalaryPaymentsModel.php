<?php
namespace App\Models;

use CodeIgniter\Model;

class SalaryPaymentsModel extends Model
{
    protected $table      = 'salary_payments';
    protected $primaryKey = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'payroll_record_id', 'employee_id', 'payment_date', 'payment_method',
        'payment_reference', 'amount', 'status', 'transaction_id', 'created_at',
    ];

    public function forEmployee(int $employeeId): array
    {
        return $this->where('employee_id', $employeeId)
            ->orderBy('payment_date', 'DESC')
            ->findAll();
    }
}
