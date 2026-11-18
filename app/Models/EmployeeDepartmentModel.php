<?php
namespace App\Models;

use CodeIgniter\Model;

class EmployeeDepartmentModel extends Model
{
    protected $table      = 'employee_departments';
    protected $primaryKey = 'id';
    protected $useTimestamps  = true;
    protected $allowedFields = [
        'client_id', 'name', 'code', 'head_id', 'description',
        'status', 'sort_order',
    ];

    /** Get active departments for a client (null client returns all active) */
    public function activeForClient(?int $clientId = null)
    {
        $q = $this->where('status', 'active');
        if ($clientId !== null) {
            $q->where('client_id', $clientId);
        }
        return $q;
    }

    /** Get department with head and member count */
    public function getWithMemberCount(int $departmentId = null): array
    {
        $b = $this->db->table('employee_departments')
            ->select('employee_departments.*, users.name as head_name, users.email as head_email,
                       (SELECT COUNT(*) FROM employees WHERE department_id = employee_departments.id AND employment_status = "active") as member_count')
            ->join('users', 'users.id = employee_departments.head_id', 'left');

        if ($departmentId !== null) {
            $b->where('employee_departments.id', $departmentId);
        }

        return $b->orderBy('employee_departments.name', 'ASC')->get()->getResultArray();
    }
}