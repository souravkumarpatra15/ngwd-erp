<?php
namespace App\Models;

use CodeIgniter\Model;

class EmployeeSalaryComponentsModel extends Model
{
    protected $table      = 'employee_salary_components';
    protected $primaryKey = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'employee_id', 'payroll_period_id', 'component_id',
        'value', 'effective_from', 'created_at', 'updated_at',
    ];

    public function forEmployee(int $employeeId): array
    {
        return $this->db->table('employee_salary_components')
            ->select('employee_salary_components.*, payroll_components.component_name, payroll_components.component_type')
            ->join('payroll_components', 'payroll_components.id = employee_salary_components.component_id', 'left')
            ->where('employee_id', $employeeId)
            ->orderBy('payroll_components.sort_order', 'ASC')
            ->get()->getResultArray();
    }
}
